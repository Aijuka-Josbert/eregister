<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->hasPermission('documents.view');
    }

    /**
     * Determine whether the user can view a specific document.
     */
    public function view(User $user, Document $document): bool
    {
        if (! $user->is_active || ! $user->hasPermission('documents.view')) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Check office separation or explicit assignment
        $isAssigned = $document->assignments()
            ->where('assigned_to_user_id', $user->id)
            ->exists();

        if ($document->office_id !== $user->office_id && ! $isAssigned) {
            return false;
        }

        // Check confidentiality clearance
        return match ($document->confidentiality_level) {
            'public', 'internal' => true,
            'confidential' => $user->hasPermission('documents.view_confidential')
                || $user->hasRole(['office_manager', 'records_manager'])
                || $document->registered_by === $user->id
                || $isAssigned,
            'strictly_confidential' => $user->hasPermission('documents.view_strictly_confidential')
                || $user->hasRole(['office_manager', 'records_manager'])
                || $isAssigned,
            default => false,
        };
    }

    /**
     * Determine whether the user can register a new document.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasPermission('documents.create');
    }

    /**
     * Determine whether the user can update metadata of the document.
     */
    public function update(User $user, Document $document): bool
    {
        if (! $user->is_active || $document->is_archived) {
            return false;
        }

        if (! $this->view($user, $document)) {
            return false;
        }

        if ($user->isAdmin() || $user->hasRole('records_manager')) {
            return true;
        }

        return $user->hasPermission('documents.edit') && $document->registered_by === $user->id;
    }

    /**
     * Determine whether the user can download or preview document files.
     */
    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document) && $user->hasPermission('documents.download');
    }

    /**
     * Determine whether the user can archive the document.
     */
    public function archive(User $user, Document $document): bool
    {
        if (! $user->is_active || $document->is_archived) {
            return false;
        }

        return $this->view($user, $document) && $user->hasPermission('documents.archive');
    }

    /**
     * Determine whether the user can restore an archived document.
     */
    public function restore(User $user, Document $document): bool
    {
        if (! $user->is_active || ! $document->is_archived) {
            return false;
        }

        return $this->view($user, $document) && $user->hasPermission('documents.restore');
    }

    /**
     * Determine whether the user can trigger or retry OCR on the document.
     */
    public function retryOcr(User $user, Document $document): bool
    {
        return $this->view($user, $document) && $user->hasPermission('ocr.retry');
    }

    /**
     * Determine whether the user can assign/route the document.
     */
    public function assign(User $user, Document $document): bool
    {
        return $this->view($user, $document) && $user->hasPermission('documents.assign');
    }
}
