<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class SearchService
{
    /**
     * Execute an authorized search across metadata and OCR-extracted text.
     *
     * @param  array<string, mixed>  $filters
     */
    public function search(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Document::query()
            ->with(['category', 'office', 'department', 'registrar', 'tags'])
            ->visibleTo($user);

        // Active vs Archived filter
        $includeArchived = filter_var($filters['include_archived'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $onlyArchived = filter_var($filters['only_archived'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($onlyArchived) {
            $query->where('is_archived', true);
        } elseif (! $includeArchived) {
            $query->where('is_archived', false);
        }

        // Keyword search across metadata, tags, and OCR text
        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $driver = Schema::getConnection()->getDriverName();
            $likeTerm = '%'.$keyword.'%';

            $query->where(function (Builder $q) use ($keyword, $likeTerm, $driver) {
                $q->where('internal_reference', 'like', $likeTerm)
                    ->orWhere('official_reference', 'like', $likeTerm)
                    ->orWhere('title', 'like', $likeTerm)
                    ->orWhere('description', 'like', $likeTerm)
                    ->orWhere('organization', 'like', $likeTerm)
                    ->orWhere('sender', 'like', $likeTerm)
                    ->orWhere('recipient', 'like', $likeTerm)
                    ->orWhere('ocr_text', 'like', $likeTerm)
                    ->orWhereHas('tags', fn (Builder $tq) => $tq->where('name', 'like', $likeTerm));

                // Leverage MariaDB/MySQL FULLTEXT index when keyword has >= 3 characters
                if (in_array($driver, ['mysql', 'mariadb'], true) && strlen($keyword) >= 3) {
                    $safeFt = preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $keyword);
                    if (trim((string) $safeFt) !== '') {
                        $q->orWhereRaw(
                            'MATCH(official_reference, title, description, organization, sender, recipient, ocr_text) AGAINST(? IN NATURAL LANGUAGE MODE)',
                            [$safeFt]
                        );
                    }
                }
            });
        }

        // Exact & Range Filters
        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['office_id'])) {
            $query->where('office_id', (int) $filters['office_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', (int) $filters['department_id']);
        }

        if (! empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (! empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
        }

        if (! empty($filters['confidentiality_level'])) {
            $query->where('confidentiality_level', $filters['confidentiality_level']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['ocr_status'])) {
            $query->where('ocr_status', $filters['ocr_status']);
        }

        if (($filters['scan'] ?? '') === 'with') {
            $query->where('file_count', '>', 0);
        } elseif (($filters['scan'] ?? '') === 'missing') {
            $query->where('file_count', '=', 0);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('date_received', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('date_received', '<=', $filters['date_to']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'date_received';
        $sortDir = strtolower((string) ($filters['sort_dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['date_received', 'date_issued', 'registered_at', 'title', 'internal_reference', 'organization'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir)->orderByDesc('id');
        } else {
            $query->orderByDesc('date_received')->orderByDesc('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Highlight matching search terms safely in HTML output.
     */
    public static function highlight(?string $text, ?string $keyword, int $snippetLength = 240): string
    {
        $escaped = e((string) $text);
        $term = trim((string) $keyword);

        if ($term === '' || $escaped === '') {
            return \Illuminate\Support\Str::limit($escaped, $snippetLength);
        }

        $pos = stripos($escaped, e($term));
        if ($pos !== false && strlen($escaped) > $snippetLength) {
            $start = max(0, $pos - 60);
            $prefix = $start > 0 ? '...' : '';
            $escaped = $prefix.substr($escaped, $start, $snippetLength).'...';
        } else {
            $escaped = \Illuminate\Support\Str::limit($escaped, $snippetLength);
        }

        $pattern = '/('.preg_quote(e($term), '/').')/i';

        return (string) preg_replace($pattern, '<mark class="lmo-highlight">$1</mark>', $escaped);
    }
}
