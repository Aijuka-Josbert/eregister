<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Document|null $document */
        $document = $this->route('document');

        return $this->user() !== null && $document !== null && $this->user()->can('update', $document);
    }

    public function rules(): array
    {
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
            'office_id' => ['required', 'integer', 'exists:offices,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'confidentiality_level' => ['required', Rule::in(['public', 'internal', 'confidential', 'strictly_confidential'])],
            'status' => ['required', Rule::in(['draft', 'registered', 'in_review', 'actioned'])],
            'tags' => ['nullable', 'string', 'max:500'],
        ];
    }
}
