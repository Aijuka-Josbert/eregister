<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Office;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $offices = Office::with(['departments'])
            ->withCount(['users', 'documents'])
            ->orderBy('name')
            ->get();

        return view('offices.index', compact('offices'));
    }

    public function storeOffice(Request $request): RedirectResponse
    {
        if (! $request->user()->hasRole(['system_admin', 'records_manager'])) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:offices,code'],
            'name' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $office = Office::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        $this->auditService->log(
            'office.created',
            "Created institutional office {$office->code} - {$office->name}",
            null,
            ['office_id' => $office->id],
            'info',
            $request->user()
        );

        return back()->with('success', "Office {$office->name} ({$office->code}) created.");
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        if (! $request->user()->hasRole(['system_admin', 'records_manager'])) {
            abort(403);
        }

        $validated = $request->validate([
            'office_id' => ['required', 'exists:offices,id'],
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $dept = Department::create([
            'office_id' => (int) $validated['office_id'],
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        $this->auditService->log(
            'department.created',
            "Created department {$dept->code} ({$dept->name})",
            null,
            ['department_id' => $dept->id],
            'info',
            $request->user()
        );

        return back()->with('success', "Department {$dept->name} created.");
    }
}
