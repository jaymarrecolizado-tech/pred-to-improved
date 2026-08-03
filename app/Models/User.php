<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\FilamentUser;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasAvatar, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar_url',
        'signature',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function travelWorkflows()
    {
        return $this->hasMany(TravelWorkflow::class);
    }

    public function travelOrders()
    {
        return $this->hasMany(TravelOrder::class);
    }

    public function approvals()
    {
        return $this->hasMany(TravelApproval::class, 'approver_id');
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature ? Storage::url($this->signature) : null;
    }

    /**
     * Determines if the user has general admin-level access.
     * Includes admin, super_admin, and hr roles.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'hr']);
    }

    /**
     * Determines if the user is a regular employee.
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Determines if the user is the Super Admin (MISS).
     * Has full system authority including settings management.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Determines if the user is an HR officer.
     * Has authority to override TO codes during approval.
     */
    public function isHR(): bool
    {
        return $this->role === 'hr';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');
        return $this->$avatarColumn ? Storage::url($this->$avatarColumn) : null;
    }

    /**
     * Restricts panel access to @dict.gov.ph email addresses only.
     * Non-government emails cannot log in regardless of role.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return str_ends_with($this->email, '@dict.gov.ph');
    }
}