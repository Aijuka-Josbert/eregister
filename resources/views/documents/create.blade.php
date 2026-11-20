@extends('layouts.app')

@section('title', "Register a document — LMO Records Registry")

@section('content')
<div class="lmo-ph">
    <div>
        <h1>Register a document</h1>
        <p>Scan it, write down its details, save. It goes on the register straight away.</p>
    </div>
    <span class="lmo-ref-badge" title="Assigned automatically on save">Next ID: {{ $nextInternalPreview }}</span>
</div>

<!-- Real-time Duplicate Detection Alert -->
<div id="duplicateWarningBox" class="lmo-alert warn d-none" role="alert">
    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
    <div>
        <div class="fw-semibold mb-1">A similar record is already on the register:</div>
        <ul id="duplicateWarningList" class="mb-0 ps-3"></ul>
    </div>
</div>

<form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="lmo-reg-grid">
        <!-- Step 1: Scan the document -->
        <section class="lmo-panel">
            <header>
                <div>
                    <div class="lmo-step">Step 1</div>
                    <h2>Scan the document</h2>
                </div>
            </header>
            <div class="lmo-panel-body">
                <div id="documentDropzone" class="lmo-dropzone">
                    <i class="bi bi-upload fs-4" style="color: var(--sage2);"></i>
                    <p>
                        <strong style="color: var(--fg);">Drop scans here</strong><br>
                        Photos or PDF files. Several pages are fine.
                    </p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <button type="button" id="startCameraStreamBtn" class="btn-lmo-sage">
                            <i class="bi bi-camera"></i> Scan with camera
                        </button>
                        <button type="button" class="lmo-btn" onclick="document.getElementById('documentFilesInput').click()">
                            <i class="bi bi-file-earmark"></i> Choose files
                        </button>
                    </div>
                    <input
                        type="file"
                        id="documentFilesInput"
                        name="files[]"
                        multiple
                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                        class="d-none"
                    >
                    <input
                        type="file"
                        id="mobileNativeCameraInput"
                        accept="image/jpeg,image/png"
                        capture="environment"
                        multiple
                        class="d-none"
                    >
                </div>

                <!-- Live Camera Stream Panel (only shown when user opens camera) -->
                <div id="liveCameraPanel" class="mt-3 d-none">
                    <div class="ratio ratio-4x3 bg-dark rounded overflow-hidden mb-2">
                        <video id="cameraVideoPreview" playsinline muted></video>
                    </div>
                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" id="snapCameraPageBtn" class="btn-lmo-sage sm">
                            <i class="bi bi-camera-fill"></i> Capture page
                        </button>
                        <button type="button" id="stopCameraStreamBtn" class="lmo-btn sm danger">
                            Close camera
                        </button>
                    </div>
                </div>

                <!-- Captured Camera Pages & Selected Files List -->
                <div id="capturedPagesContainer" class="row g-2 mt-2"></div>
                <div id="hiddenCameraInputs"></div>
                <div id="selectedFilesPreviewList" class="mt-2">
                    <p class="muted mb-0 mt-2" style="font-size: 13.5px;">
                        No scan added yet. You can also save now and add the scan later.
                    </p>
                </div>
            </div>
        </section>

        <!-- Step 2: Document details (matching LMO Records Registry.html — clean & uncrowded) -->
        <section class="lmo-panel">
            <header>
                <div>
                    <div class="lmo-step">Step 2</div>
                    <h2>Document details</h2>
                </div>
            </header>
            <div class="lmo-panel-body">
                <div class="lmo-fgrid">
                    <div class="lmo-fld">
                        <label for="category_id">Category <i class="req">*</i></label>
                        <select id="category_id" name="category_id" class="form-select" required data-event-toggle>
                            <option value="">Select a category</option>
                            @foreach($categories as $cat)
                                <option
                                    value="{{ $cat->id }}"
                                    data-slug="{{ $cat->slug }}"
                                    {{ (int) old('category_id', request('category_id')) === $cat->id ? 'selected' : '' }}
                                >
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lmo-fld">
                        <label for="date_received">Date received <i class="req">*</i></label>
                        <input
                            type="date"
                            id="date_received"
                            name="date_received"
                            value="{{ old('date_received', now()->toDateString()) }}"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="lmo-fld full">
                        <label for="organization">Name of organisation <i class="req">*</i></label>
                        <input
                            type="text"
                            id="organization"
                            name="organization"
                            value="{{ old('organization') }}"
                            class="form-control"
                            autocomplete="off"
                            placeholder="Where the document came from"
                            required
                        >
                    </div>

                    <div class="lmo-fld">
                        <label for="sender">From</label>
                        <input
                            type="text"
                            id="sender"
                            name="sender"
                            value="{{ old('sender') }}"
                            class="form-control"
                            autocomplete="off"
                            placeholder="Name and title of sender"
                        >
                    </div>

                    <div class="lmo-fld">
                        <label for="recipient">Addressed to</label>
                        <input
                            type="text"
                            id="recipient"
                            name="recipient"
                            value="{{ old('recipient', 'Lord Mayor') }}"
                            class="form-control"
                            autocomplete="off"
                            placeholder="Who the document is for"
                        >
                    </div>

                    <div class="lmo-fld full">
                        <label for="title">Subject <i class="req">*</i></label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            class="form-control"
                            autocomplete="off"
                            placeholder="What the document is about"
                            required
                        >
                    </div>

                    <div class="lmo-fld">
                        <label for="official_reference">Reference number</label>
                        <input
                            type="text"
                            id="official_reference"
                            name="official_reference"
                            value="{{ old('official_reference') }}"
                            class="form-control mono"
                            autocomplete="off"
                            placeholder="As printed on the document"
                        >
                    </div>

                    <div class="lmo-fld">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="registered" {{ old('status', 'registered') === 'registered' ? 'selected' : '' }}>Received</option>
                            <option value="in_review" {{ old('status') === 'in_review' ? 'selected' : '' }}>With officer</option>
                            <option value="actioned" {{ old('status') === 'actioned' ? 'selected' : '' }}>Action taken</option>
                        </select>
                    </div>

                    <!-- Event Date (shown automatically for Invitations) -->
                    <div class="lmo-fld full" id="eventDateWrap" hidden>
                        <label for="event_date">Event date</label>
                        <input
                            type="date"
                            id="event_date"
                            name="event_date"
                            value="{{ old('event_date') }}"
                            class="form-control"
                        >
                    </div>

                    <div class="lmo-fld full">
                        <label for="description">Notes</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="form-control"
                            placeholder="Anything else worth knowing"
                        >{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Optional Advanced Classification (collapsed by default to keep registration simple) -->
                <details class="lmo-more-options">
                    <summary>More options (office, department, confidentiality, date issued)</summary>
                    <div class="lmo-fgrid mt-3">
                        <div class="lmo-fld">
                            <label for="office_id">Office</label>
                            <select id="office_id" name="office_id" class="form-select">
                                @foreach($offices as $off)
                                    <option value="{{ $off->id }}" {{ (int) old('office_id', auth()->user()->office_id) === $off->id ? 'selected' : '' }}>
                                        {{ $off->code }} — {{ $off->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lmo-fld">
                            <label for="department_id">Department</label>
                            <select id="department_id" name="department_id" class="form-select">
                                <option value="">General Registry</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (int) old('department_id', auth()->user()->department_id) === $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lmo-fld">
                            <label for="confidentiality_level">Access level</label>
                            <select id="confidentiality_level" name="confidentiality_level" class="form-select">
                                <option value="internal" {{ old('confidentiality_level', 'internal') === 'internal' ? 'selected' : '' }}>Internal (Standard staff)</option>
                                <option value="public" {{ old('confidentiality_level') === 'public' ? 'selected' : '' }}>Public</option>
                                <option value="confidential" {{ old('confidentiality_level') === 'confidential' ? 'selected' : '' }}>Confidential</option>
                                <option value="strictly_confidential" {{ old('confidentiality_level') === 'strictly_confidential' ? 'selected' : '' }}>Strictly Confidential</option>
                            </select>
                        </div>

                        <div class="lmo-fld">
                            <label for="direction">Direction</label>
                            <select id="direction" name="direction" class="form-select">
                                <option value="incoming" {{ old('direction', 'incoming') === 'incoming' ? 'selected' : '' }}>Incoming</option>
                                <option value="outgoing" {{ old('direction') === 'outgoing' ? 'selected' : '' }}>Outgoing</option>
                                <option value="internal" {{ old('direction') === 'internal' ? 'selected' : '' }}>Internal</option>
                            </select>
                        </div>

                        <div class="lmo-fld">
                            <label for="date_issued">Date printed on document</label>
                            <input type="date" id="date_issued" name="date_issued" value="{{ old('date_issued') }}" class="form-control">
                        </div>

                        <div class="lmo-fld">
                            <label for="tags">Tags (comma-separated)</label>
                            <input type="text" id="tags" name="tags" value="{{ old('tags') }}" class="form-control" placeholder="e.g. Budget, Urgent">
                        </div>
                    </div>
                </details>

                <div class="d-flex flex-wrap gap-2 align-items-center mt-4">
                    <button type="submit" class="btn-lmo-primary">
                        <i class="bi bi-check2"></i> Save to register
                    </button>
                    <a href="{{ route('documents.index') }}" class="lmo-btn ghost">Cancel</a>
                </div>
            </div>
        </section>
    </div>
</form>
@endsection
