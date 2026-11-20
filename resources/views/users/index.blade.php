@extends('layouts.app')

@section('title', "Staff & Access — LMO Records Registry")

@section('content')
<div class="lmo-ph">
    <div>
        <h1>Staff &amp; access</h1>
        <p>Manage authorised officers, office assignments, and role permissions.</p>
    </div>
</div>

<div class="lmo-cols">
    <!-- Left Column: Staff Directory & Roles -->
    <div class="d-flex flex-column gap-4" style="min-width: 0;">
        <section class="lmo-panel">
            <header>
                <h2>Authorised staff</h2>
                <span class="muted" style="font-size: 13px;">{{ $users->total() }} accounts</span>
            </header>
            <div class="table-responsive">
                <table class="table table-hover table-lmo">
                    <thead>
                        <tr>
                            <th>Staff member</th>
                            <th>Role</th>
                            <th>Office</th>
                            <th>Status</th>
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
                                    <div class="muted" style="font-size: 12.5px;">{{ $u->email }}@if($u->title) &middot; {{ $u->title }}@endif</div>
                                </td>
                                <td>
                                    <span class="lmo-chip st-officer">{{ $u->role?->name }}</span>
                                </td>
                                <td style="font-size: 13.5px;">
                                    <span class="mono fw-semibold">{{ $u->office?->code ?? 'LMO' }}</span>
                                    <div class="muted" style="font-size: 12px;">{{ $u->department?->name ?? 'All units' }}</div>
                                </td>
                                <td>
                                    @if($u->is_active)
                                        <span class="lmo-chip st-action">Active</span>
                                    @else
                                        <span class="lmo-chip st-received">Deactivated</span>
                                    @endif
                                </td>
                                @can('create', \App\Models\User::class)
                                    <td class="text-end">
                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('users.toggle-active', $u) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="lmo-btn sm {{ $u->is_active ? 'danger' : 'sage' }}">
                                                    {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="muted" style="font-size: 12.5px;">You</span>
                                        @endif
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="lmo-panel">
            <header>
                <h2>Roles &amp; permissions</h2>
            </header>
            <div class="lmo-panel-body d-flex flex-column gap-2">
                @foreach($roles as $role)
                    <div class="p-3 rounded" style="background: var(--bg); border: 1px solid var(--line);">
                        <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2">
                            <strong>{{ $role->name }}</strong>
                            <span class="muted" style="font-size: 13px;">{{ $role->description }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            @foreach($role->permissions as $perm)
                                <span class="lmo-chip cat" style="font-size: 11px;">{{ $perm->slug }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Right Column: Provision New Account -->
    @can('create', \App\Models\User::class)
        <section class="lmo-panel">
            <header>
                <h2>Add staff member</h2>
            </header>
            <div class="lmo-panel-body">
                <form action="{{ route('users.store') }}" method="POST" class="d-flex flex-column gap-3">
                    @csrf
                    <div class="lmo-fld">
                        <label>Full name <i class="req">*</i></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Sarah Namukasa" required>
                    </div>
                    <div class="lmo-fld">
                        <label>Official email <i class="req">*</i></label>
                        <input type="email" name="email" class="form-control" placeholder="snamukasa@lmo.go.ug" required>
                    </div>
                    <div class="lmo-fgrid">
                        <div class="lmo-fld">
                            <label>Employee ID</label>
                            <input type="text" name="employee_number" class="form-control mono" placeholder="LMO-EMP-024">
                        </div>
                        <div class="lmo-fld">
                            <label>Job title</label>
                            <input type="text" name="title" class="form-control" placeholder="Records Officer">
                        </div>
                    </div>
                    <div class="lmo-fld">
                        <label>Role <i class="req">*</i></label>
                        <select name="role_id" class="form-select" required>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lmo-fld">
                        <label>Office <i class="req">*</i></label>
                        <select name="office_id" class="form-select" required>
                            @foreach($offices as $off)
                                <option value="{{ $off->id }}">{{ $off->code }} — {{ $off->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lmo-fld">
                        <label>Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">General Office</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lmo-fld">
                        <label>Initial password (min 8 chars) <i class="req">*</i></label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <button type="submit" class="btn-lmo-primary w-100">Create account</button>
                </form>
            </div>
        </section>
    @endcan
</div>
@endsection
