<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_reference',
        'type',
        'storage_disk',
        'file_path',
        'file_size',
        'sha256_hash',
        'is_encrypted',
        'status',
        'notes',
        'triggered_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_encrypted' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function triggerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
