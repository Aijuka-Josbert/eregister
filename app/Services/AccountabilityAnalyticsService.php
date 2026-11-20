<?php

namespace App\Services;

use App\Models\DocumentActivityLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccountabilityAnalyticsService
{
    /**
     * Build accountability summary charts (donut/pie slices, staff bar chart, 14-day trend, severity breakdown).
     *
     * @return array<string, mixed>
     */
    public function buildForUser(User $user): array
    {
        $scoped = DocumentActivityLog::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('office_id', $user->office_id));

        $totalEvents = (clone $scoped)->count();
        $criticalEvents = (clone $scoped)->where('severity', 'critical')->count();
        $warningEvents = (clone $scoped)->where('severity', 'warning')->count();
        $infoEvents = max(0, $totalEvents - $criticalEvents - $warningEvents);
        $confidentialViews = (clone $scoped)->where('action', 'document.viewed_confidential')->count();
        $uniqueDocsHandled = (clone $scoped)->whereNotNull('document_id')->distinct('document_id')->count('document_id');
        $activeStaffCount = (clone $scoped)->whereNotNull('user_id')->distinct('user_id')->count('user_id');

        // 1. Group raw actions into clean accountability buckets for Donut/Pie Chart (LMO Palette — ZERO blue)
        $rawActions = (clone $scoped)
            ->select('action', DB::raw('COUNT(*) as total'))
            ->groupBy('action')
            ->pluck('total', 'action');

        $buckets = [
            'registrations' => ['label' => 'Registrations & Scans', 'color' => '#8E1C2A', 'count' => 0],
            'views' => ['label' => 'Views & Inspections', 'color' => '#3E5A43', 'count' => 0],
            'downloads' => ['label' => 'Downloads & Exports', 'color' => '#7F9B7D', 'count' => 0],
            'updates' => ['label' => 'Edits, Status & Routing', 'color' => '#8A6D3B', 'count' => 0],
            'archiving' => ['label' => 'Archiving & Governance', 'color' => '#6A1421', 'count' => 0],
            'security' => ['label' => 'Sign-ins & Security Alerts', 'color' => '#5D6659', 'count' => 0],
        ];

        foreach ($rawActions as $action => $count) {
            $cnt = (int) $count;
            $act = (string) $action;

            if (str_contains($act, 'registered') || str_contains($act, 'file_uploaded') || str_contains($act, 'upload')) {
                $buckets['registrations']['count'] += $cnt;
            } elseif (str_contains($act, 'viewed') || str_contains($act, 'preview')) {
                $buckets['views']['count'] += $cnt;
            } elseif (str_contains($act, 'download') || str_contains($act, 'export')) {
                $buckets['downloads']['count'] += $cnt;
            } elseif (str_contains($act, 'updated') || str_contains($act, 'status') || str_contains($act, 'assigned') || str_contains($act, 'ocr')) {
                $buckets['updates']['count'] += $cnt;
            } elseif (str_contains($act, 'archive') || str_contains($act, 'restore') || str_contains($act, 'category') || str_contains($act, 'office') || str_contains($act, 'department') || str_contains($act, 'user') || str_contains($act, 'backup')) {
                $buckets['archiving']['count'] += $cnt;
            } else {
                $buckets['security']['count'] += $cnt;
            }
        }

        // Compute SVG donut arc dasharray/dashoffset offsets (circumference for r=54 is 339.292)
        $circumference = 2 * M_PI * 54;
        $actionBreakdown = [];
        $runningOffset = 0.0;

        foreach ($buckets as $key => $bucket) {
            if ($bucket['count'] <= 0) {
                continue;
            }
            $pct = $totalEvents > 0 ? round(($bucket['count'] / $totalEvents) * 100, 1) : 0.0;
            $dash = $totalEvents > 0 ? ($bucket['count'] / $totalEvents) * $circumference : 0.0;

            $actionBreakdown[] = [
                'key' => $key,
                'label' => $bucket['label'],
                'color' => $bucket['color'],
                'count' => $bucket['count'],
                'percent' => $pct,
                'dash' => round($dash, 2),
                'offset' => round($runningOffset, 2),
            ];

            $runningOffset += $dash;
        }

        // 2. Staff Accountability Leaderboard (Top 8 handlers)
        $staffRows = (clone $scoped)
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $userMap = User::with('role')
            ->whereIn('id', $staffRows->pluck('user_id'))
            ->get()
            ->keyBy('id');

        $maxStaff = max(1, (int) ($staffRows->first()?->total ?? 1));
        $staffActivity = [];
        foreach ($staffRows as $row) {
            $u = $userMap->get($row->user_id);
            $cnt = (int) $row->total;
            $staffActivity[] = [
                'user_id' => $row->user_id,
                'name' => $u?->name ?? 'Staff Member',
                'role' => $u?->role?->name ?? 'Staff',
                'count' => $cnt,
                'width_pct' => (int) round(($cnt / $maxStaff) * 100),
            ];
        }

        // 3. 14-Day Activity Trend Graph (Normal vs Warning/Critical)
        $startDay = now()->subDays(13)->startOfDay();
        $recentLogs = (clone $scoped)
            ->where('created_at', '>=', $startDay)
            ->get(['created_at', 'severity']);

        $byDate = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $key = $d->format('Y-m-d');
            $byDate[$key] = [
                'date' => $key,
                'label' => $d->format('d M'),
                'short' => $d->format('d'),
                'normal' => 0,
                'alerts' => 0,
                'total' => 0,
            ];
        }

        foreach ($recentLogs as $entry) {
            $dayKey = Carbon::parse($entry->created_at)->format('Y-m-d');
            if (! isset($byDate[$dayKey])) {
                continue;
            }
            if (in_array($entry->severity, ['warning', 'critical'], true)) {
                $byDate[$dayKey]['alerts']++;
            } else {
                $byDate[$dayKey]['normal']++;
            }
            $byDate[$dayKey]['total']++;
        }

        $dailyTotals = array_values(array_map(fn ($d) => $d['total'], $byDate));
        $maxDaily = max(1, ...$dailyTotals);
        $dailyTrend = array_values(array_map(function ($d) use ($maxDaily) {
            $d['total_pct'] = $d['total'] > 0 ? max(8, (int) round(($d['total'] / $maxDaily) * 100)) : 0;
            $d['alert_pct'] = $d['total'] > 0 ? (int) round(($d['alerts'] / $d['total']) * 100) : 0;

            return $d;
        }, $byDate));

        // 4. Severity Breakdown
        $severityBreakdown = [
            [
                'label' => 'Normal Operations',
                'count' => $infoEvents,
                'percent' => $totalEvents > 0 ? round(($infoEvents / $totalEvents) * 100, 1) : 0.0,
                'color' => '#7F9B7D',
            ],
            [
                'label' => 'Sensitive / Confidential',
                'count' => $warningEvents,
                'percent' => $totalEvents > 0 ? round(($warningEvents / $totalEvents) * 100, 1) : 0.0,
                'color' => '#8A6D3B',
            ],
            [
                'label' => 'Critical / Access Denied',
                'count' => $criticalEvents,
                'percent' => $totalEvents > 0 ? round(($criticalEvents / $totalEvents) * 100, 1) : 0.0,
                'color' => '#8E1C2A',
            ],
        ];

        return [
            'summary' => [
                'total_events' => $totalEvents,
                'critical_events' => $criticalEvents,
                'warning_events' => $warningEvents,
                'info_events' => $infoEvents,
                'confidential_views' => $confidentialViews,
                'unique_docs_handled' => $uniqueDocsHandled,
                'active_staff_count' => $activeStaffCount,
            ],
            'circumference' => round($circumference, 2),
            'actionBreakdown' => $actionBreakdown,
            'staffActivity' => $staffActivity,
            'dailyTrend' => $dailyTrend,
            'severityBreakdown' => $severityBreakdown,
        ];
    }
}
