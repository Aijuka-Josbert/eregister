@extends('layouts.app')

@section('title', 'Staff Accounts & Role Permissions — LMO DRMS')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            <i class="bi bi-people me-1"></i> Staff Accounts &amp; Role-Based Access Control
        </h1>
        <p class="text-muted small mb-0">
            Manage authorized personnel, office assignments, role permissions, and account activation status.
        </p>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Staff Table & Role Matrix -->
    <div class="col-12 col-xl-8">
        <div class="lmo-card mb-4">
            <div class="lmo-card-header d-flex justify-content-between align-items-center">
                <span>Authorized Personnel ({{ $users->total() }})</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-lmo mb-0">
                    <thead>
                        <tr>
                            <th>Staff Name &amp; Title</th>
                            <th>Role</th>
                            <th>Office / Dept</th>
                            <th>Status</th>
                            <th>Last Sign-In</th>
                            @can('create', \App\Models\User::class)
                                <th class="text-end">Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $u->name }}</div>
                                    <div class="small text-muted">{{ $u->email }} @if($u->title) &bull; {{ $u->title }} @endif</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                        {{ $u->role?->name }}
                                    </span>
                                </td>
                                <td class="small">
                                    <strong>{{ $u->office?->code ?? 'GLOBAL' }}</strong>
                                    <div class="text-muted">{{ $u->department?->name ?? 'All Units' }}</div>
                                </td>
                                <td>
                                    @if($u->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Deactivated</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $u->last_login_at?->diffForHumans() ?? 'Never' }}
                                </td>
                                @can('create', \App\Models\User::class)
                                    <td class="text-end">
                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('users.toggle-active', $u) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                    {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="small text-muted">Current Session</span>
                                        @endif
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Configurable Role & Permission Matrix -->
        <div class="lmo-card">
            <div class="lmo-card-header">
                <i class="bi bi-key me-2"></i>Institutional Role &amp; Permission Matrix
            </div>
            <div class="p-3 small">
                @foreach($roles as $role)
                    <div class="border rounded p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $role->name }} (<code>{{ $role->slug }}</code>)</strong>
                            <span class="text-muted">{{ $role->description }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            @foreach($role->permissions as $perm)
                                <span class="badge bg-white text-dark border" style="font-size: 0.68rem;">{{ $perm->slug }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column: Provision New Staff Account -->
    @can('create', \App\Models\User::class)
    <div class="col-12 col-xl-4">
        <div class="lmo-card">
            <div class="lmo-card-header">
                <i class="bi bi-person-plus me-1"></i> Provision New Staff Account
            </div>
            <div class="p-3 small">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Full Name *</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Sarah Namukasa" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Official Email *</label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="snamukasa@lmo.go.ug" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Employee ID</label>
                            <input type="text" name="employee_number" class="form-control form-control-sm" placeholder="LMO-EMP-024">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Job Title</label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Records Assistant">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Assigned Role *</label>
                        <select name="role_id" class="form-select form-select-sm" required>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Assigned Office *</label>
                        <select name="office_id" class="form-select form-select-sm" required>
                            @foreach($offices as $off)
                                <option value="{{ $off->id }}">{{ $off->code }} — {{ $off->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Department / Unit</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">-- General Office --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Initial Password (min 8 chars) *</label>
                        <input type="password" name="password" class="form-control form-control-sm" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-sm btn-lmo-primary w-100">Create Staff Account</button>
                </form>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
