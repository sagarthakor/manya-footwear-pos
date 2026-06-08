<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool      { return in_array($this->role, ['super_admin', 'admin']); }

    public function isManager(): bool
    {
        if (in_array($this->role, ['super_admin', 'admin', 'manager'])) return true;
        // Custom roles: manager-level access if they have any product/report/sales permission
        return $this->hasAnyPermission(['view products', 'view reports', 'view purchases', 'create grn', 'manage purchases']);
    }

    public function isCashier(): bool    { return true; }

    public function isCustomRole(): bool
    {
        return !in_array($this->role, ['super_admin', 'admin', 'manager', 'cashier']);
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'manager'     => 'Manager',
            'cashier'     => 'Cashier',
            default       => $this->role,
        };
    }
}
