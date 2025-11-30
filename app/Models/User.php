<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Master\Sales;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SALES = 'sales';
    public const ROLE_ACCOUNTING = 'accounting';

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar_path',
        'password',
        'role',
        'permissions',
        'is_active',
        'deactivated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    public function avatarUrl(): string
    {
        // Baca langsung dari attributes agar aman meskipun kolom tidak di-select
        $avatarPath = $this->attributes['avatar_path'] ?? null;

        if (!empty($avatarPath)) {
            return asset('storage/' . $avatarPath);
        }

        // Tidak ada avatar tersimpan -> kembalikan string kosong
        return '';
    }

    /**
     * @return BelongsToMany<Menu, self>
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class)->withTimestamps();
    }

    /**
     * Optional link to master Sales record (matched by email).
     */
    public function salesProfile(): HasOne
    {
        return $this->hasOne(Sales::class, 'email', 'email');
    }

    /**
     * Get users who should receive payment request notifications (finance team).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function getFinanceTeamUsers()
    {
        // Get users with finance-related roles or specific user IDs
        // You can customize this based on your role structure
        // For now, we'll get users with role 'super_admin' or 'finance' or 'admin'
        // You can also configure specific user IDs in config if needed
        $financeRoles = config('notifications.finance_team_roles', ['super_admin', 'finance', 'admin']);

        return static::whereIn('role', $financeRoles)->get();
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function hasRole(string ...$roles): bool
    {
        return collect($roles)->contains(fn (string $role): bool => $this->role === $role);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get permissions for this user's role.
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        $map = config('permissions.role_permissions', []);
        $custom = is_array($this->permissions) ? $this->permissions : null;

        if ($this->isSuperAdmin()) {
            return collect($map)
                ->flatten()
                ->unique()
                ->values()
                ->all();
        }

        if ($custom !== null) {
            return collect($custom)->unique()->values()->all();
        }

        return $map[$this->role] ?? [];
    }

    public function hasPermission(string ...$permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $userPermissions = $this->permissions();

        return collect($permissions)->contains(
            fn (string $permission): bool => in_array($permission, $userPermissions, true)
        );
    }

    public function canAccessMenu(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        /** @var Collection<int, Menu>|null $menus */
        $menus = $this->relationLoaded('menus') ? $this->menus : $this->menus()->get();

        return $menus->contains(fn (Menu $menu): bool => $menu->slug === $slug);
    }

    /**
     * @return array<string, string>
     */
    public static function availableRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_SALES => 'Sales',
            self::ROLE_ACCOUNTING => 'Accounting',
        ];
    }
}
