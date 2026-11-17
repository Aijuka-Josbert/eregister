<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentActivityLog;
use App\Models\DocumentCategory;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $baseQuery = Document::query()->visibleTo($user);

        $stats = [
            'total_records' => (clone $baseQuery)->count(),
            'active_records' => (clone $baseQuery)->where('is_archived', false)->count(),
            'received_today' => (clone $baseQuery)->whereDate('date_received', now()->toDateString())->count(),
            'monthly_registrations' => (clone $baseQuery)
                ->whereYear('date_received', now()->year)
                ->whereMonth('date_received', now()->month)
                ->count(),
            'invitations_count' => (clone $baseQuery)
                ->where(function ($q) {
                    $q->where('document_type', 'invitation')
                        ->orWhereHas('category', fn ($cq) => $cq->where('slug', 'invitation'));
                })
                ->count(),
            'archived_records' => (clone $baseQuery)->where('is_archived', true)->count(),
            'pending_records' => (clone $baseQuery)->whereIn('status', ['draft', 'in_review'])->where('is_archived', false)->count(),
            'ocr_failures' => (clone $baseQuery)->where('ocr_status', 'failed')->count(),
            'ocr_completed' => (clone $baseQuery)->where('ocr_status', 'completed')->count(),
        ];

        $categoryBreakdown = DocumentCategory::query()
            ->where('is_active', true)
            ->withCount(['documents' => fn ($q) => $q->visibleTo($user)])
            ->orderByDesc('documents_count')
            ->get();

        $officeBreakdown = Office::query()
            ->where('is_active', true)
            ->withCount(['documents' => fn ($q) => $q->visibleTo($user)])
            ->orderByDesc('documents_count')
            ->get();

        $recentDocuments = (clone $baseQuery)
            ->with(['category', 'office', 'department', 'registrar'])
            ->latest('registered_at')
            ->limit(8)
            ->get();

        $upcomingInvitations = (clone $baseQuery)
            ->with(['category', 'office'])
            ->where('is_archived', false)
            ->where(function ($q) {
                $q->where('document_type', 'invitation')
                    ->orWhereHas('category', fn ($cq) => $cq->where('slug', 'invitation'));
            })
            ->orderByDesc('date_received')
            ->limit(5)
            ->get();

        $recentActivity = DocumentActivityLog::query()
            ->with(['user', 'document'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('office_id', $user->office_id))
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'categoryBreakdown',
            'officeBreakdown',
            'recentDocuments',
            'upcomingInvitations',
            'recentActivity'
        ));
    }
}
