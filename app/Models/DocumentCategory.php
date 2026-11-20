<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'color_code',
        'retention_years',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'retention_years' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    /**
     * Return a non-blue LMO palette color, automatically remapping legacy blue codes.
     */
    public function safeColor(): string
    {
        $raw = strtoupper(trim((string) $this->color_code));

        $legacyMap = [
            '#245B91' => '#3E5A43',
            '#142B4A' => '#6A1421',
            '#3283B8' => '#7F9B7D',
            '#0D6EFD' => '#8E1C2A',
            '#1D4ED8' => '#8E1C2A',
            '#2563EB' => '#3E5A43',
            '#C5A15B' => '#8A6D3B',
            '#C74646' => '#8E1C2A',
            '#21865B' => '#3E5A43',
        ];

        if (isset($legacyMap[$raw])) {
            return $legacyMap[$raw];
        }

        // Guard against any custom bluish hex (#RRGGBB where B significantly exceeds R and G)
        if (preg_match('/^#([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/', $raw, $m)) {
            $r = hexdec($m[1]);
            $g = hexdec($m[2]);
            $b = hexdec($m[3]);
            if ($b > $r + 25 && $b > $g + 15) {
                return '#3E5A43';
            }

            return $raw;
        }

        return '#5D6659';
    }
}
