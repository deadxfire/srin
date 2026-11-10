<?php

namespace Model;

use Core\Model;

/**
 * ============================================================================
 * Account Model (Primary User & Identity Model for Srin Engine)
 * ============================================================================
 * Table: users
 * Represents authenticated accounts in your application.
 * ============================================================================
 */
class Account extends Model
{
    /**
     * Database table associated with the model.
     */
    protected static string $table = 'users';

    /**
     * Attributes permitted for mass-assignment.
     *
     * @var array<string>
     */
    protected array $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * Attributes guarded from mass-assignment.
     *
     * @var array<string>
     */
    protected array $guarded = [
        'id',
        'role',
        'is_active',
    ];

    /**
     * Attributes hidden from array and JSON serialization.
     *
     * @var array<string>
     */
    protected array $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Check if this account possesses administrator privileges.
     */
    public function isAdmin(): bool
    {
        return ($this->role ?? '') === 'admin';
    }

    /**
     * Check if this account possesses a specific role.
     */
    public function hasRole(string $role): bool
    {
        return strtolower((string) ($this->role ?? '')) === strtolower(trim($role));
    }

    /**
     * Generate 1-2 character initials for profile avatars.
     */
    public function initials(): string
    {
        $name = trim((string) ($this->name ?? 'User'));
        $parts = explode(' ', $name);
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 1));
    }
}

