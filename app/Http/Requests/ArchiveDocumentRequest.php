<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

class ArchiveDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Document|null $document */
        $document = $this->route('document');

        return $this->user() !== null && $document !== null && $this->user()->can('archive', $document);
    }

    public function rules(): array
    {
        return [
            'archive_reason' => ['required', 'string', 'min:5', 'max:1000'],
            'archive_location_code' => ['nullable', 'string', 'max:80'],
            'retention_until' => ['nullable', 'date', 'after:today'],
        ];
    }
}
