<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'web_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'account_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Methods
    public function hasLinkedAccount()
    {
        return !is_null($this->account_id);
    }

    public function canAccessAdmin()
    {
        return $this->hasAnyRole(['admin', 'moderator']);
    }

    public function isOnline()
    {
        return $this->account?->isOnline() ?? false;
    }
}