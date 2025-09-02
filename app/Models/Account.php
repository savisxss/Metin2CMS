<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Account extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $connection = 'mysql';
    protected $table = 'account';
    protected $primaryKey = 'id';

    protected $fillable = [
        'login',
        'password',
        'email',
        'social_id',
        'status',
        'create_time',
        'last_play',
        'coins',
        'cash',
        'real_name',
        'question1',
        'answer1',
        'question2', 
        'answer2',
        'is_testtesttest',
        'securitycode',
        'newsletter',
        'empire',
        'name_checked',
        'availDt',
        'mileage',
        'gold_expire',
        'silver_expire',
        'safebox_expire',
        'autoloot_expire',
        'fish_mind_expire',
        'marriage_fast_expire',
        'money_drop_rate_expire',
        'create_time',
        'last_play',
        'channel',
        'lang',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'securitycode',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'create_time' => 'datetime',
        'last_play' => 'datetime',
        'availDt' => 'datetime',
        'gold_expire' => 'datetime',
        'silver_expire' => 'datetime',
        'safebox_expire' => 'datetime',
        'autoloot_expire' => 'datetime',
        'fish_mind_expire' => 'datetime',
        'marriage_fast_expire' => 'datetime',
        'money_drop_rate_expire' => 'datetime',
        'is_testtesttest' => 'boolean',
        'newsletter' => 'boolean',
        'name_checked' => 'boolean',
    ];

    public $timestamps = false;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'coins', 'cash', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function players()
    {
        return $this->hasMany(Player::class, 'account_id');
    }

    public function safebox()
    {
        return $this->hasOne(Safebox::class, 'account_id');
    }

    // Accessors & Mutators
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $this->hashPassword($value);
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    // Methods
    public function hashPassword($password)
    {
        return '*' . strtoupper(sha1(sha1($password, true)));
    }

    public function isOnline()
    {
        return $this->players()->where('last_play', '>', now()->subMinutes(5))->exists();
    }

    public function getTotalPlayTime()
    {
        return $this->players()->sum('playtime');
    }

    public function getEmpireName()
    {
        $empires = [
            1 => 'Shinsoo',
            2 => 'Chunjo',
            3 => 'Jinno',
        ];

        return $empires[$this->empire] ?? 'Unknown';
    }

    public function isBanned()
    {
        return $this->status !== 'OK';
    }

    public function isActive()
    {
        return $this->status === 'OK' && (!$this->availDt || $this->availDt->isPast());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'OK')
                    ->where(function ($q) {
                        $q->whereNull('availDt')
                          ->orWhere('availDt', '<=', now());
                    });
    }

    public function scopeOnline($query)
    {
        return $query->whereHas('players', function ($q) {
            $q->where('last_play', '>', now()->subMinutes(5));
        });
    }

    public function scopeByEmpire($query, $empire)
    {
        return $query->where('empire', $empire);
    }
}