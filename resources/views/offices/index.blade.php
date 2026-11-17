@extends('layouts.app')

@section('title', 'Offices & Departments — LMO DRMS')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            <i class="bi bi-building me-1"></i> Institutional Offices &amp; Internal Departments
        </h1>
        <p class="text-muted small mb-0">
            Multi-office architecture enforcing data separation between the Lord Mayor's Office and partner directorates.
        </p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        @foreach($offices as $office)
            <div class="lmo-card mb-3">
                <div class="lmo-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary me-2">{{ $office->code }}</span>
                        <strong>{{ $office->name }}</strong>
                        @if($office->location)
                            <span class="small text-muted ms-2"><i class="bi bi-geo-alt"></i> {{ $office->location }}</span>
                        @endif
                    </div>
                    <div class="small">
                        <span class="badge bg-light text-dark border">{{ $office->users_count }} Staff</span>
                        <span class="badge bg-light text-dark border">{{ $office->documents_count }} Records</span>
                    </div>
                </div>
                <div class="p-3 small">
                    @if($office->description)
                        <p class="text-muted mb-2">{{ $office->description }}</p>
                    @endif
                    <div class="fw-semibold mb-2">Departments / Units:</div>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($office->departments as $dept)
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <strong>{{ $dept->code }}</strong>: {{ $dept->name }}
                            </span>
                        @empty
                            <span class="text-muted">No sub-departments configured.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(auth()->user()->hasRole(['system_admin', 'records_manager']))
    <div class="col-12 col-lg-4">
        <div class="lmo-card mb-4">
            <div class="lmo-card-header">Register Additional Office</div>
            <div class="p-3 small">
                <form action="{{ route('offices.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Office Prefix Code *</label>
                        <input type="text" name="code" class="form-control form-control-sm font-monospace" placeholder="e.g. EDL" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Office Name *</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Executive Director Liaison" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Building / Floor Location</label>
                        <input type="text" name="location" class="form-control form-control-sm" placeholder="e.g. City Hall, 3rd Floor">
                    </div>
                    <button type="submit" class="btn btn-sm btn-lmo-primary w-100 mt-2">Create Office</button>
                </form>
            </div>
        </div>

        <div class="lmo-card">
            <div class="lmo-card-header">Add Department / Unit</div>
            <div class="p-3 small">
                <form action="{{ route('departments.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Parent Office *</label>
                        <select name="office_id" class="form-select form-select-sm" required>
                            @foreach($offices as $off)
                                <option value="{{ $off->id }}">{{ $off->code }} — {{ $off->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Department Code *</label>
                        <input type="text" name="code" class="form-control form-control-sm font-monospace" placeholder="e.g. FIN" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Department Name *</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Finance & Budget Unit" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100 mt-2">Add Department</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
