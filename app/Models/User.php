<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'office_id',
        'department_id',
        'role_id',
        'employee_number',
        'name',
        'title',
        'email',
        'phone',
        'password',
        'is_active',
        'mfa_enabled',
        'mfa_secret',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'mfa_enabled' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function registeredDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'registered_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DocumentAssignment::class, 'assigned_to_user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(DocumentActivityLog::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (! $this->role) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($this->role->slug, $roles, true);
        }

        return $this->role->slug === $roles;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('system_admin');
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if (! $this->is_active || ! $this->role) {
            return false;
        }

        return $this->role->hasPermission($permissionSlug);
    }
}
