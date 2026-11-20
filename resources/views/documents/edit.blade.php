@extends('layouts.app')

@section('title', "Edit details — {$document->internal_reference}")

@section('content')
<div class="lmo-ph">
    <div>
        <h1>Edit details</h1>
        <p>{{ $document->internal_reference }} &middot; Changes are logged automatically in the handling history.</p>
    </div>
    <a href="{{ route('documents.show', $document) }}" class="lmo-btn sm">&larr; Back to record</a>
</div>

<section class="lmo-panel" style="max-width: 820px;">
    <header>
        <h2>Document details</h2>
        <span class="lmo-ref-badge">{{ $document->internal_reference }}</span>
    </header>
    <div class="lmo-panel-body">
        <form action="{{ route('documents.update', $document) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="lmo-fgrid">
                <div class="lmo-fld">
                    <label for="category_id">Category <i class="req">*</i></label>
                    <select id="category_id" name="category_id" class="form-select" required data-event-toggle>
                        @foreach($categories as $cat)
                            <option
                                value="{{ $cat->id }}"
                                data-slug="{{ $cat->slug }}"
                                {{ (int) old('category_id', $document->category_id) === $cat->id ? 'selected' : '' }}
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
                        value="{{ old('date_received', $document->date_received?->format('Y-m-d')) }}"
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
                        value="{{ old('organization', $document->organization) }}"
                        class="form-control"
                        required
                    >
                </div>

                <div class="lmo-fld">
                    <label for="sender">From</label>
                    <input
                        type="text"
                        id="sender"
                        name="sender"
                        value="{{ old('sender', $document->sender) }}"
                        class="form-control"
                    >
                </div>

                <div class="lmo-fld">
                    <label for="recipient">Addressed to</label>
                    <input
                        type="text"
                        id="recipient"
                        name="recipient"
                        value="{{ old('recipient', $document->recipient) }}"
                        class="form-control"
                    >
                </div>

                <div class="lmo-fld full">
                    <label for="title">Subject <i class="req">*</i></label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $document->title) }}"
                        class="form-control"
                        required
                    >
                </div>

                <div class="lmo-fld">
                    <label for="official_reference">Reference number</label>
                    <input
                        type="text"
                        id="official_reference"
                        name="official_reference"
                        value="{{ old('official_reference', $document->official_reference) }}"
                        class="form-control mono"
                    >
                </div>

                <div class="lmo-fld">
                    <label for="status">Status <i class="req">*</i></label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="registered" {{ old('status', $document->status) === 'registered' ? 'selected' : '' }}>Received</option>
                        <option value="in_review" {{ old('status', $document->status) === 'in_review' ? 'selected' : '' }}>With officer</option>
                        <option value="actioned" {{ old('status', $document->status) === 'actioned' ? 'selected' : '' }}>Action taken</option>
                        <option value="draft" {{ old('status', $document->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <div class="lmo-fld full" id="eventDateWrap" {{ $document->event_date || str_contains(strtolower((string) $document->category?->slug), 'invit') ? '' : 'hidden' }}>
                    <label for="event_date">Event date</label>
                    <input
                        type="date"
                        id="event_date"
                        name="event_date"
                        value="{{ old('event_date', $document->event_date?->format('Y-m-d')) }}"
                        class="form-control"
                    >
                </div>

                <div class="lmo-fld full">
                    <label for="description">Notes</label>
                    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $document->description) }}</textarea>
                </div>
            </div>

            <details class="lmo-more-options">
                <summary>More options (office, department, confidentiality, date issued)</summary>
                <div class="lmo-fgrid mt-3">
                    <div class="lmo-fld">
                        <label for="office_id">Office</label>
                        <select id="office_id" name="office_id" class="form-select">
                            @foreach($offices as $off)
                                <option value="{{ $off->id }}" {{ (int) old('office_id', $document->office_id) === $off->id ? 'selected' : '' }}>
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
                                <option value="{{ $dept->id }}" {{ (int) old('department_id', $document->department_id) === $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lmo-fld">
                        <label for="confidentiality_level">Access level</label>
                        <select id="confidentiality_level" name="confidentiality_level" class="form-select">
                            <option value="public" {{ old('confidentiality_level', $document->confidentiality_level) === 'public' ? 'selected' : '' }}>Public</option>
                            <option value="internal" {{ old('confidentiality_level', $document->confidentiality_level) === 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="confidential" {{ old('confidentiality_level', $document->confidentiality_level) === 'confidential' ? 'selected' : '' }}>Confidential</option>
                            <option value="strictly_confidential" {{ old('confidentiality_level', $document->confidentiality_level) === 'strictly_confidential' ? 'selected' : '' }}>Strictly Confidential</option>
                        </select>
                    </div>

                    <div class="lmo-fld">
                        <label for="direction">Direction</label>
                        <select id="direction" name="direction" class="form-select">
                            <option value="incoming" {{ old('direction', $document->direction) === 'incoming' ? 'selected' : '' }}>Incoming</option>
                            <option value="outgoing" {{ old('direction', $document->direction) === 'outgoing' ? 'selected' : '' }}>Outgoing</option>
                            <option value="internal" {{ old('direction', $document->direction) === 'internal' ? 'selected' : '' }}>Internal</option>
                        </select>
                    </div>

                    <div class="lmo-fld">
                        <label for="date_issued">Date issued</label>
                        <input type="date" id="date_issued" name="date_issued" value="{{ old('date_issued', $document->date_issued?->format('Y-m-d')) }}" class="form-control">
                    </div>

                    <div class="lmo-fld">
                        <label for="tags">Tags</label>
                        <input type="text" id="tags" name="tags" value="{{ old('tags', $document->tags->pluck('name')->implode(', ')) }}" class="form-control">
                    </div>
                </div>
            </details>

            <div class="d-flex flex-wrap gap-2 align-items-center mt-4">
                <button type="submit" class="btn-lmo-primary">
                    <i class="bi bi-check2"></i> Save changes
                </button>
                <a href="{{ route('documents.show', $document) }}" class="lmo-btn">Cancel</a>
            </div>
        </form>
    </div>
</section>
@endsection
