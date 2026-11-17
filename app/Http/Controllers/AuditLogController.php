<?php

namespace App\Http\Controllers;

use App\Models\DocumentActivityLog;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        Gate::authorize('viewAny', DocumentActivityLog::class);

        $query = DocumentActivityLog::query()
            ->with(['user.role', 'document', 'office'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('office_id', $user->office_id));

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', $term)
                    ->orWhere('action', 'like', $term)
                    ->orWhere('ip_address', 'like', $term)
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhereHas('document', fn ($dq) => $dq->where('internal_reference', 'like', $term)->orWhere('official_reference', 'like', $term));
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->latest('created_at')->paginate(25)->withQueryString();

        $distinctActions = DocumentActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $offices = Office::orderBy('name')->get(['id', 'code', 'name']);

        $securitySummary = [
            'total_events' => DocumentActivityLog::count(),
            'critical_events' => DocumentActivityLog::where('severity', 'critical')->count(),
            'warning_events' => DocumentActivityLog::where('severity', 'warning')->count(),
            'confidential_views' => DocumentActivityLog::where('action', 'document.viewed_confidential')->count(),
        ];

        return view('reports.audit', compact(
            'logs',
            'distinctActions',
            'users',
            'offices',
            'securitySummary'
        ));
    }
}
