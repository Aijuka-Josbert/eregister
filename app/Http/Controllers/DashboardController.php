<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use App\Services\AccountabilityAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected AccountabilityAnalyticsService $analytics
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $baseQuery = Document::query()->visibleTo($user);
        $activeQuery = (clone $baseQuery)->where('is_archived', false);

        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();

        $stats = [
            'received_today' => (clone $activeQuery)->whereDate('date_received', $today)->count(),
            'received_week' => (clone $activeQuery)->whereDate('date_received', '>=', $weekStart)->count(),
            'waiting_action' => (clone $activeQuery)->whereIn('status', ['draft', 'registered', 'in_review'])->count(),
            'missing_scan' => (clone $activeQuery)->where('file_count', 0)->count(),
            'total_active' => (clone $activeQuery)->count(),
            'archived_records' => (clone $baseQuery)->where('is_archived', true)->count(),
        ];

        $recentDocuments = (clone $activeQuery)
            ->with(['category', 'office'])
            ->latest('registered_at')
            ->limit(7)
            ->get();

        $missingScanDocuments = (clone $activeQuery)
            ->with(['category'])
            ->where('file_count', 0)
            ->latest('registered_at')
            ->limit(5)
            ->get();

        $categoryBreakdown = DocumentCategory::query()
            ->where('is_active', true)
            ->withCount(['documents' => fn ($q) => $q->visibleTo($user)->where('is_archived', false)])
            ->orderByDesc('documents_count')
            ->orderBy('name')
            ->get();

        $accountability = $this->analytics->buildForUser($user);

        return view('dashboard.index', compact(
            'stats',
            'recentDocuments',
            'missingScanDocuments',
            'categoryBreakdown',
            'accountability'
        ));
    }
}
