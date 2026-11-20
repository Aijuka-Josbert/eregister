@extends('layouts.app')

@section('title', "Categories, Offices & Departments — LMO Records Registry")

@section('content')
@php
    $canManage = auth()->user()->hasRole(['system_admin', 'records_manager']);
    $totalDepartments = $offices->sum(fn ($o) => $o->departments->count());
@endphp

<div class="lmo-ph">
    <div>
        <h1>Categories</h1>
        <p>Document categories, offices, and departments organized together in one place.</p>
    </div>
</div>

<!-- Unified Tabs: Document Categories | Offices & Departments -->
<div class="lmo-tabs" role="tablist">
    <a href="{{ route('categories.index', ['tab' => 'categories']) }}"
       class="lmo-tab {{ $activeTab === 'categories' ? 'active' : '' }}"
       role="tab"
       aria-selected="{{ $activeTab === 'categories' ? 'true' : 'false' }}">
        <i class="bi bi-tags"></i>
        <span>Document categories</span>
        <span class="n">{{ $categories->count() }}</span>
    </a>
    <a href="{{ route('categories.index', ['tab' => 'offices']) }}"
       class="lmo-tab {{ $activeTab === 'offices' ? 'active' : '' }}"
       role="tab"
       aria-selected="{{ $activeTab === 'offices' ? 'true' : 'false' }}">
        <i class="bi bi-building"></i>
        <span>Offices &amp; departments</span>
        <span class="n">{{ $offices->count() }} / {{ $totalDepartments }}</span>
    </a>
</div>

@if($activeTab === 'categories')
    <!-- TAB 1: Document Categories -->
    <div class="lmo-cols">
        <section class="lmo-panel">
            <header>
                <h2>Document categories</h2>
                <span class="muted" style="font-size: 13px;">Click any category to open its records</span>
            </header>
            <div class="lmo-rows">
                @forelse($categories as $cat)
                    <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}" class="lmo-row compact">
                        <span class="d">{{ $cat->retention_years }} yr{{ $cat->retention_years === 1 ? '' : 's' }}</span>
                        <span class="doc-main">
                            <span class="s d-inline-flex align-items-center gap-2">
                                <span class="lmo-legend-dot" style="background: {{ $cat->safeColor() }};"></span>
                                {{ $cat->name }}
                            </span>
                            <span class="o">{{ $cat->description ?? 'Standard filing category' }}</span>
                        </span>
                        <span class="text-end">
                            <span class="lmo-chip cat">{{ $cat->documents_count }} record{{ $cat->documents_count === 1 ? '' : 's' }}</span>
                        </span>
                    </a>
                @empty
                    <div class="lmo-empty">
                        <h3>No categories configured</h3>
                        <p class="mb-0">Add your first document category to start organizing the register.</p>
                    </div>
                @endforelse
            </div>
        </section>

        @if($canManage)
            <section class="lmo-panel">
                <header>
                    <h2>Add a category</h2>
                </header>
                <div class="lmo-panel-body">
                    <form action="{{ route('categories.store') }}" method="POST" class="d-flex flex-column gap-3">
                        @csrf
                        <div class="lmo-fld">
                            <label for="cat-name">Category name <i class="req">*</i></label>
                            <input type="text" id="cat-name" name="name" class="form-control" placeholder="e.g. Invitations, Diplomatic Protocol" required>
                        </div>
                        <div class="lmo-fgrid">
                            <div class="lmo-fld">
                                <label for="cat-color">Accent tone <i class="req">*</i></label>
                                <select id="cat-color" name="color_code" class="form-select" required>
                                    <option value="#8E1C2A">Crimson (#8E1C2A)</option>
                                    <option value="#6A1421">Deep Burgundy (#6A1421)</option>
                                    <option value="#3E5A43" selected>Deep Sage (#3E5A43)</option>
                                    <option value="#7F9B7D">Soft Sage (#7F9B7D)</option>
                                    <option value="#8A6D3B">Warm Ochre (#8A6D3B)</option>
                                    <option value="#5D6659">Stone Grey (#5D6659)</option>
                                </select>
                            </div>
                            <div class="lmo-fld">
                                <label for="cat-retention">Retention (years) <i class="req">*</i></label>
                                <input type="number" id="cat-retention" name="retention_years" value="5" min="1" max="50" class="form-control" required>
                            </div>
                        </div>
                        <div class="lmo-fld">
                            <label for="cat-desc">Short description</label>
                            <textarea id="cat-desc" name="description" rows="2" class="form-control" placeholder="What kinds of documents belong here"></textarea>
                        </div>
                        <div>
                            <button type="submit" class="btn-lmo-primary w-100">Create category</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif
    </div>
@else
    <!-- TAB 2: Offices & Departments (Grouped under Categories) -->
    <div class="lmo-cols">
        <div class="d-flex flex-column gap-3" style="min-width: 0;">
            @forelse($offices as $office)
                <section class="lmo-panel">
                    <header>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="lmo-ref-badge">{{ $office->code }}</span>
                            <h2>{{ $office->name }}</h2>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="lmo-chip cat">{{ $office->users_count }} staff</span>
                            <a href="{{ route('documents.index', ['office_id' => $office->id]) }}" class="lmo-chip st-officer text-decoration-none">
                                {{ $office->documents_count }} record{{ $office->documents_count === 1 ? '' : 's' }} &rarr;
                            </a>
                        </div>
                    </header>
                    <div class="lmo-panel-body">
                        @if($office->location || $office->description)
                            <p class="muted mb-3" style="font-size: 14px;">
                                @if($office->location)
                                    <i class="bi bi-geo-alt me-1"></i>{{ $office->location }}
                                @endif
                                @if($office->location && $office->description) &middot; @endif
                                {{ $office->description }}
                            </p>
                        @endif

                        <div class="muted mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">
                            Departments &amp; units ({{ $office->departments->count() }})
                        </div>

                        @if($office->departments->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($office->departments as $dept)
                                    <a href="{{ route('documents.index', ['office_id' => $office->id, 'department_id' => $dept->id]) }}"
                                       class="lmo-btn sm" style="text-decoration: none;">
                                        <span class="mono" style="font-size: 12px;">{{ $dept->code }}</span>
                                        <span>{{ $dept->name }}</span>
                                        <span class="muted mono" style="font-size: 11.5px;">({{ $dept->documents_count }})</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="muted mb-0" style="font-size: 13.5px;">No departments added to this office yet.</p>
                        @endif
                    </div>
                </section>
            @empty
                <section class="lmo-panel">
                    <div class="lmo-empty">
                        <h3>No offices configured</h3>
                        <p class="mb-0">Add an office to organize records and staff access.</p>
                    </div>
                </section>
            @endforelse
        </div>

        @if($canManage)
            <div class="d-flex flex-column gap-4" style="min-width: 0;">
                <section class="lmo-panel">
                    <header>
                        <h2>Add an office</h2>
                    </header>
                    <div class="lmo-panel-body">
                        <form action="{{ route('offices.store') }}" method="POST" class="d-flex flex-column gap-3">
                            @csrf
                            <div class="lmo-fgrid">
                                <div class="lmo-fld">
                                    <label for="off-code">Prefix code <i class="req">*</i></label>
                                    <input type="text" id="off-code" name="code" class="form-control mono" placeholder="e.g. EDL" required>
                                </div>
                                <div class="lmo-fld">
                                    <label for="off-loc">Location</label>
                                    <input type="text" id="off-loc" name="location" class="form-control" placeholder="City Hall, 3rd Floor">
                                </div>
                            </div>
                            <div class="lmo-fld">
                                <label for="off-name">Office name <i class="req">*</i></label>
                                <input type="text" id="off-name" name="name" class="form-control" placeholder="e.g. Executive Director Liaison" required>
                            </div>
                            <div>
                                <button type="submit" class="btn-lmo-primary w-100">Create office</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="lmo-panel">
                    <header>
                        <h2>Add a department</h2>
                    </header>
                    <div class="lmo-panel-body">
                        <form action="{{ route('departments.store') }}" method="POST" class="d-flex flex-column gap-3">
                            @csrf
                            <div class="lmo-fld">
                                <label for="dept-office">Parent office <i class="req">*</i></label>
                                <select id="dept-office" name="office_id" class="form-select" required>
                                    @foreach($offices as $off)
                                        <option value="{{ $off->id }}">{{ $off->code }} — {{ $off->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="lmo-fgrid">
                                <div class="lmo-fld">
                                    <label for="dept-code">Code <i class="req">*</i></label>
                                    <input type="text" id="dept-code" name="code" class="form-control mono" placeholder="e.g. FIN" required>
                                </div>
                                <div class="lmo-fld">
                                    <label for="dept-name">Department name <i class="req">*</i></label>
                                    <input type="text" id="dept-name" name="name" class="form-control" placeholder="Finance & Budget" required>
                                </div>
                            </div>
                            <div>
                                <button type="submit" class="btn-lmo-sage w-100">Add department</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        @endif
    </div>
@endif
@endsection
