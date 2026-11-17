<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentArchive extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'archived_by',
        'archive_reason',
        'archive_location_code',
        'retention_until',
        'archived_at',
        'restored_by',
        'restore_reason',
        'restored_at',
    ];

    protected function casts(): array
    {
        return [
            'retention_until' => 'date',
            'archived_at' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}
