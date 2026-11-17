<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $users = User::with(['office', 'department', 'role'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->input('q')).'%';
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('employee_number', 'like', $term);
            })
            ->when($request->filled('role_id'), fn ($q) => $q->where('role_id', (int) $request->input('role_id')))
            ->when($request->filled('office_id'), fn ($q) => $q->where('office_id', (int) $request->input('office_id')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::with('permissions')->orderBy('id')->get();
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'offices', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'employee_number' => ['nullable', 'string', 'max:50', 'unique:users,employee_number'],
            'title' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role_id' => ['required', 'exists:roles,id'],
            'office_id' => ['required', 'exists:offices,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_number' => $validated['employee_number'] ?? null,
            'title' => $validated['title'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role_id' => (int) $validated['role_id'],
            'office_id' => (int) $validated['office_id'],
            'department_id' => ! empty($validated['department_id']) ? (int) $validated['department_id'] : null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $this->auditService->log(
            'user.created',
            "Created staff account for {$newUser->name} ({$newUser->email}) with role {$newUser->role?->name}",
            null,
            ['created_user_id' => $newUser->id, 'role' => $newUser->role?->slug, 'office_id' => $newUser->office_id],
            'info',
            $request->user()
        );

        return back()->with('success', "Staff account created for {$newUser->name}.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'employee_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_number')->ignore($user->id)],
            'title' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role_id' => ['required', 'exists:roles,id'],
            'office_id' => ['required', 'exists:offices,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $oldRole = $user->role?->slug;
        $oldOffice = $user->office_id;

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_number' => $validated['employee_number'] ?? null,
            'title' => $validated['title'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role_id' => (int) $validated['role_id'],
            'office_id' => (int) $validated['office_id'],
            'department_id' => ! empty($validated['department_id']) ? (int) $validated['department_id'] : null,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->refresh();

        $this->auditService->log(
            'user.updated',
            "Updated staff account/permissions for {$user->name} ({$user->email})",
            null,
            [
                'target_user_id' => $user->id,
                'old_role' => $oldRole,
                'new_role' => $user->role?->slug,
                'old_office_id' => $oldOffice,
                'new_office_id' => $user->office_id,
            ],
            $oldRole !== $user->role?->slug ? 'warning' : 'info',
            $request->user()
        );

        return back()->with('success', "Staff profile and permissions updated for {$user->name}.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('toggleActive', $user);

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $stateLabel = $user->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            "user.{$stateLabel}",
            "Staff account {$user->name} ({$user->email}) was {$stateLabel}.",
            null,
            ['target_user_id' => $user->id, 'is_active' => $user->is_active],
            'warning',
            $request->user()
        );

        return back()->with('success', "Account for {$user->name} has been {$stateLabel}.");
    }
}
