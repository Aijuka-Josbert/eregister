<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'internal_reference',
        'official_reference',
        'title',
        'description',
        'organization',
        'sender',
        'recipient',
        'direction',
        'document_type',
        'event_date',
        'date_issued',
        'date_received',
        'category_id',
        'office_id',
        'department_id',
        'confidentiality_level',
        'status',
        'primary_file_path',
        'file_count',
        'ocr_status',
        'ocr_text',
        'ocr_error',
        'ocr_processed_at',
        'registered_by',
        'registered_at',
        'is_archived',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'date_issued' => 'date',
            'date_received' => 'date',
            'ocr_processed_at' => 'datetime',
            'registered_at' => 'datetime',
            'is_archived' => 'boolean',
            'archived_at' => 'datetime',
            'file_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Document $document) {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
            if (empty($document->registered_at)) {
                $document->registered_at = now();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DocumentFile::class)->orderBy('page_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DocumentTag::class, 'document_tag_pivot');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(DocumentActivityLog::class)->latest('created_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DocumentAssignment::class)->latest();
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class)->latest();
    }

    public function archives(): HasMany
    {
        return $this->hasMany(DocumentArchive::class)->latest('archived_at');
    }

    /**
     * Scope documents visible to the given authenticated user based on office and confidentiality rules.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            // Office separation: user belongs to the document's office OR is assigned to the document
            $q->where('office_id', $user->office_id)
                ->orWhereHas('assignments', fn (Builder $aq) => $aq->where('assigned_to_user_id', $user->id));
        })->where(function (Builder $q) use ($user) {
            // Confidentiality level enforcement
            if ($user->hasPermission('documents.view_strictly_confidential') || $user->hasRole(['office_manager', 'records_manager'])) {
                return;
            }

            if ($user->hasPermission('documents.view_confidential')) {
                $q->whereIn('confidentiality_level', ['public', 'internal', 'confidential'])
                    ->orWhere('registered_by', $user->id)
                    ->orWhereHas('assignments', fn (Builder $aq) => $aq->where('assigned_to_user_id', $user->id));

                return;
            }

            // Standard Records Officer / Read-Only User
            $q->whereIn('confidentiality_level', ['public', 'internal'])
                ->orWhere('registered_by', $user->id)
                ->orWhereHas('assignments', fn (Builder $aq) => $aq->where('assigned_to_user_id', $user->id));
        });
    }
}
