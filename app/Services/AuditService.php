<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Record an append-only audit log entry.
     */
    public function log(
        string $action,
        string $description,
        ?Document $document = null,
        array $properties = [],
        string $severity = 'info',
        ?User $actor = null,
        ?Request $request = null
    ): DocumentActivityLog {
        /** @var User|null $user */
        $user = $actor ?? Auth::user();
        $req = $request ?? request();

        return DocumentActivityLog::create([
            'document_id' => $document?->id,
            'user_id' => $user?->id,
            'office_id' => $document?->office_id ?? $user?->office_id,
            'action' => $action,
            'severity' => $severity,
            'description' => $description,
            'properties' => empty($properties) ? null : $properties,
            'ip_address' => $req?->ip() ?? '127.0.0.1',
            'user_agent' => substr((string) ($req?->userAgent() ?? 'CLI'), 0, 500),
            'created_at' => now(),
        ]);
    }
}
