<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', \App\Models\Document::class);
    }

    public function rules(): array
    {
        $maxKb = ((int) env('DOCUMENT_MAX_UPLOAD_MB', 25)) * 1024;

        return [
            'official_reference' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'organization' => ['required', 'string', 'min:2', 'max:200'],
            'sender' => ['nullable', 'string', 'max:200'],
            'recipient' => ['nullable', 'string', 'max:200'],
            'direction' => ['required', Rule::in(['incoming', 'outgoing', 'internal'])],
            'document_type' => ['required', Rule::in(['letter', 'invitation', 'memo', 'report', 'legal_notice', 'petition', 'circular', 'other'])],
            'event_date' => ['nullable', 'date'],
            'date_issued' => ['nullable', 'date'],
            'date_received' => ['required', 'date'],
            'category_id' => ['required', 'integer', 'exists:document_categories,id'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'confidentiality_level' => ['required', Rule::in(['public', 'internal', 'confidential', 'strictly_confidential'])],
            'status' => ['nullable', Rule::in(['draft', 'registered', 'in_review', 'actioned'])],
            'tags' => ['nullable', 'string', 'max:500'],
            'capture_source' => ['nullable', Rule::in(['upload', 'camera_scan', 'external_scanner'])],
            'files' => ['nullable', 'array', 'max:20'],
            'files.*' => ['file', 'mimes:pdf,jpg,jpeg,png', "max:{$maxKb}"],
            'camera_captures' => ['nullable', 'array', 'max:20'],
            'camera_captures.*' => ['nullable', 'string'],
        ];
    }
}
