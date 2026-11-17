@extends('layouts.app')

@section('title', 'Register / Scan Document — LMO Digital Records Management System')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            <i class="bi bi-file-earmark-plus me-1"></i> Register &amp; Scan Official Document
        </h1>
        <p class="text-muted small mb-0">
            Preserve the sender's official reference number verbatim, attach scanned PDFs or capture pages using your mobile camera, and queue automatic OCR indexing.
        </p>
    </div>
    <div>
        <span class="lmo-ref-badge">
            Next Tracking ID: {{ $nextInternalPreview }}
        </span>
    </div>
</div>

<!-- Real-time Duplicate Detection Alert -->
<div id="duplicateWarningBox" class="alert alert-warning d-none shadow-sm" role="alert">
    <div class="fw-bold small mb-1">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Potential Duplicate Document Detected in Register:
    </div>
    <ul id="duplicateWarningList" class="mb-0 small ps-3"></ul>
</div>

<form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        <!-- Left Column: Document Metadata -->
        <div class="col-12 col-lg-7">
            <div class="lmo-card mb-4">
                <div class="lmo-card-header d-flex justify-content-between align-items-center">
                    <span>1. Official Reference &amp; Core Identification</span>
                    <span class="badge bg-light text-dark border">All Official References Preserved Verbatim</span>
                </div>
                <div class="p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="official_reference" class="form-label fw-semibold small">
                                Official Reference Number (As printed on document)
                            </label>
                            <input
                                type="text"
                                id="official_reference"
                                name="official_reference"
                                value="{{ old('official_reference') }}"
                                class="form-control font-monospace"
                                placeholder="e.g. KCCA/ED/2026/104 or MoLG/ADM/09/26"
                            >
                            <div class="form-text" style="font-size: 0.74rem;">
                                Leave blank if unnumbered. Preserved exactly as written on the letter.
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="direction" class="form-label fw-semibold small">Record Direction *</label>
                            <select id="direction" name="direction" class="form-select" required>
                                <option value="incoming" {{ old('direction', 'incoming') === 'incoming' ? 'selected' : '' }}>Incoming Correspondence</option>
                                <option value="outgoing" {{ old('direction') === 'outgoing' ? 'selected' : '' }}>Outgoing Correspondence</option>
                                <option value="internal" {{ old('direction') === 'internal' ? 'selected' : '' }}>Internal Office Memorandum</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="title" class="form-label fw-semibold small">Subject / Document Title *</label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                class="form-control"
                                placeholder="e.g. Official Invitation to the Kampala City Infrastructure Summit 2026"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="organization" class="form-label fw-semibold small">Originating / External Organization *</label>
                            <input
                                type="text"
                                id="organization"
                                name="organization"
                                value="{{ old('organization') }}"
                                class="form-control"
                                placeholder="e.g. Ministry of Kampala Capital City & Metropolitan Affairs"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="sender" class="form-label fw-semibold small">Sender Name &amp; Title</label>
                            <input
                                type="text"
                                id="sender"
                                name="sender"
                                value="{{ old('sender') }}"
                                class="form-control"
                                placeholder="e.g. Hon. Permanent Secretary / Executive Director"
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="recipient" class="form-label fw-semibold small">Addressed Recipient</label>
                            <input
                                type="text"
                                id="recipient"
                                name="recipient"
                                value="{{ old('recipient', 'The Right Honourable Lord Mayor') }}"
                                class="form-control"
                            >
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="date_issued" class="form-label fw-semibold small">Date Issued on Document</label>
                            <input
                                type="date"
                                id="date_issued"
                                name="date_issued"
                                value="{{ old('date_issued') }}"
                                class="form-control"
                            >
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="date_received" class="form-label fw-semibold small">Date Received *</label>
                            <input
                                type="date"
                                id="date_received"
                                name="date_received"
                                value="{{ old('date_received', now()->toDateString()) }}"
                                class="form-control"
                                required
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="lmo-card">
                <div class="lmo-card-header">
                    2. Classification, Office Assignment &amp; Confidentiality
                </div>
                <div class="p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="category_id" class="form-label fw-semibold small">Document Category *</label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (int) old('category_id') === $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="document_type" class="form-label fw-semibold small">Document Type *</label>
                            <select id="document_type" name="document_type" class="form-select" required>
                                <option value="letter" {{ old('document_type', 'letter') === 'letter' ? 'selected' : '' }}>Official Letter</option>
                                <option value="invitation" {{ old('document_type') === 'invitation' ? 'selected' : '' }}>Official Invitation (Card / Letter)</option>
                                <option value="memo" {{ old('document_type') === 'memo' ? 'selected' : '' }}>Internal Memorandum</option>
                                <option value="report" {{ old('document_type') === 'report' ? 'selected' : '' }}>Technical / Financial Report</option>
                                <option value="legal_notice" {{ old('document_type') === 'legal_notice' ? 'selected' : '' }}>Legal Notice / Court Document</option>
                                <option value="petition" {{ old('document_type') === 'petition' ? 'selected' : '' }}>Citizen / Public Petition</option>
                                <option value="circular" {{ old('document_type') === 'circular' ? 'selected' : '' }}>Government Circular / Directive</option>
                                <option value="other" {{ old('document_type') === 'other' ? 'selected' : '' }}>Other Record</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="office_id" class="form-label fw-semibold small">Owning Office *</label>
                            <select id="office_id" name="office_id" class="form-select" required>
                                @foreach($offices as $off)
                                    <option value="{{ $off->id }}" {{ (int) old('office_id', auth()->user()->office_id) === $off->id ? 'selected' : '' }}>
                                        {{ $off->code }} — {{ $off->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="department_id" class="form-label fw-semibold small">Handling Department / Unit</label>
                            <select id="department_id" name="department_id" class="form-select">
                                <option value="">-- General Registry --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (int) old('department_id', auth()->user()->department_id) === $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="confidentiality_level" class="form-label fw-semibold small">Confidentiality Classification *</label>
                            <select id="confidentiality_level" name="confidentiality_level" class="form-select" required>
                                <option value="internal" {{ old('confidentiality_level', 'internal') === 'internal' ? 'selected' : '' }}>Internal (Standard Office Staff)</option>
                                <option value="public" {{ old('confidentiality_level') === 'public' ? 'selected' : '' }}>Public Record</option>
                                <option value="confidential" {{ old('confidentiality_level') === 'confidential' ? 'selected' : '' }}>Confidential (Restricted Access)</option>
                                <option value="strictly_confidential" {{ old('confidentiality_level') === 'strictly_confidential' ? 'selected' : '' }}>Strictly Confidential (Executive Only)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="event_date" class="form-label fw-semibold small">Event Date &amp; Time (For Invitations)</label>
                            <input
                                type="datetime-local"
                                id="event_date"
                                name="event_date"
                                value="{{ old('event_date') }}"
                                class="form-control"
                            >
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="tags" class="form-label fw-semibold small">Tags (Comma-separated)</label>
                            <input
                                type="text"
                                id="tags"
                                name="tags"
                                value="{{ old('tags') }}"
                                class="form-control"
                                placeholder="e.g. Invitation, VIP, Budget FY2026, Council"
                            >
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold small">Executive Summary / Registration Notes</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                class="form-control"
                                placeholder="Brief summary of the document contents, enclosures, or urgent instructions..."
                            >{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Scanning, Mobile Camera Capture & File Attachments -->
        <div class="col-12 col-lg-5">
            <div class="lmo-card mb-4">
                <div class="lmo-card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-camera me-1"></i> 3. Mobile Camera Scan &amp; File Upload</span>
                    <span class="badge bg-success">Private Storage</span>
                </div>
                <div class="p-3 p-md-4">
                    <!-- Mobile Camera Capture Controls -->
                    <div class="p-3 rounded border mb-3" style="background-color: #F8FAFC;">
                        <div class="fw-semibold small mb-2" style="color: var(--lmo-deep-navy);">
                            <i class="bi bi-phone-flip me-1"></i> Option A: Smartphone / Tablet Multi-Page Camera Scan
                        </div>
                        <p class="text-muted small mb-2">
                            Photograph physical document pages directly using your phone camera or webcam. You can capture multiple pages in sequence.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" id="startCameraStreamBtn" class="btn btn-sm btn-lmo-primary">
                                <i class="bi bi-camera-video me-1"></i> Open Live Scanner
                            </button>
                            <label for="mobileNativeCameraInput" class="btn btn-sm btn-outline-primary mb-0">
                                <i class="bi bi-camera me-1"></i> Capture Photo Page
                            </label>
                            <input
                                type="file"
                                id="mobileNativeCameraInput"
                                accept="image/jpeg,image/png"
                                capture="environment"
                                multiple
                                class="d-none"
                            >
                        </div>

                        <!-- Live Video Stream Panel -->
                        <div id="liveCameraPanel" class="mt-3 d-none">
                            <div class="ratio ratio-4x3 bg-dark rounded overflow-hidden mb-2">
                                <video id="cameraVideoPreview" playsinline muted></video>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" id="snapCameraPageBtn" class="btn btn-sm btn-success fw-semibold">
                                    <i class="bi bi-camera-fill me-1"></i> Snap Page
                                </button>
                                <button type="button" id="stopCameraStreamBtn" class="btn btn-sm btn-outline-danger">
                                    Close Camera
                                </button>
                            </div>
                        </div>

                        <!-- Captured Pages Grid -->
                        <div id="capturedPagesContainer" class="row g-2 mt-2"></div>
                        <div id="hiddenCameraInputs"></div>
                    </div>

                    <!-- Desktop / Scanner File Dropzone -->
                    <div class="mb-3">
                        <div class="fw-semibold small mb-2" style="color: var(--lmo-deep-navy);">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Option B: Upload Scanned PDFs or Images (Drag &amp; Drop)
                        </div>
                        <div id="documentDropzone" class="lmo-dropzone" onclick="document.getElementById('documentFilesInput').click()">
                            <i class="bi bi-file-earmark-pdf fs-2" style="color: var(--lmo-civic-blue);"></i>
                            <div class="fw-semibold mt-2">Click to select files or drag &amp; drop here</div>
                            <div class="small text-muted mt-1">
                                Supports PDF, JPEG, PNG (Up to {{ env('DOCUMENT_MAX_UPLOAD_MB', 25) }} MB per file, multiple files allowed)
                            </div>
                            <input
                                type="file"
                                id="documentFilesInput"
                                name="files[]"
                                multiple
                                accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                class="d-none"
                            >
                        </div>
                        <div id="selectedFilesPreviewList" class="mt-2 small"></div>
                    </div>

                    <div class="alert alert-light border small mb-0">
                        <div class="fw-semibold text-dark mb-1"><i class="bi bi-cpu me-1"></i> Automatic OCR &amp; Integrity Protection</div>
                        <ul class="mb-0 ps-3 text-muted" style="font-size: 0.78rem;">
                            <li>All files are verified by magic-byte header and hashed with <code>SHA-256</code>.</li>
                            <li>Originals are stored in private storage and never overwritten.</li>
                            <li>Tesseract &amp; Poppler OCR automatically extracts searchable text upon registration.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-lmo-primary btn-lg fw-semibold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Complete Registration &amp; Run OCR
                </button>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection
