<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property bool $is_admin
 * @property \Illuminate\Support\Carbon|null $banned_at
 * @property string|null $ban_reason
 * @property bool $is_active
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_admin',
        'banned_at',
        'ban_reason',
        'is_active',
        'isActive',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'banned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function approvedCampaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'approved_by');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

     public const ROLE_USER = 'user';
     public const ROLE_ADMIN = 'admin';

     /**
      * Check if user is an admin
      */
     public function isAdmin(): bool
     {
         return $this->role === self::ROLE_ADMIN;
     }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }
}
