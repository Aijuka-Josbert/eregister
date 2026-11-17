<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\OcrService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDocumentOcrJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 180;

    public function __construct(
        public int $documentId
    ) {}

    public function handle(OcrService $ocrService): void
    {
        $document = Document::find($this->documentId);
        if (! $document) {
            return;
        }

        $ocrService->processDocument($document);
    }
}
