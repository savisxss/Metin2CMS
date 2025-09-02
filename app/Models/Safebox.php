<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Safebox extends Model
{
    use HasFactory;

    protected $connection = 'player';
    protected $table = 'safebox';
    protected $primaryKey = 'account_id';

    protected $fillable = [
        'account_id',
        'size',
        'password',
        'gold',
    ];

    public $timestamps = false;
    public $incrementing = false;

    // Relationships
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'owner_id', 'account_id')
                    ->where('window', Item::WINDOW_SAFEBOX)
                    ->on('player');
    }

    // Accessors
    public function getFormattedGoldAttribute()
    {
        return number_format($this->gold);
    }

    public function getItemCountAttribute()
    {
        return $this->items()->count();
    }

    public function getUsedSlotsAttribute()
    {
        return $this->items()->count();
    }

    public function getFreeSlotsAttribute()
    {
        return $this->size - $this->getUsedSlotsAttribute();
    }

    public function getUsagePercentageAttribute()
    {
        return $this->size > 0 ? round(($this->getUsedSlotsAttribute() / $this->size) * 100, 1) : 0;
    }

    // Methods
    public function hasPassword()
    {
        return !empty($this->password) && $this->password !== '000000';
    }

    public function isFull()
    {
        return $this->getUsedSlotsAttribute() >= $this->size;
    }

    public function canAddItems($count = 1)
    {
        return $this->getFreeSlotsAttribute() >= $count;
    }

    public function verifyPassword($password)
    {
        return $this->password === $password;
    }

    // Scopes
    public function scopeWithPassword($query)
    {
        return $query->where('password', '!=', '000000')
                    ->whereNotNull('password');
    }

    public function scopeWithoutPassword($query)
    {
        return $query->where(function ($q) {
            $q->where('password', '000000')
              ->orWhereNull('password');
        });
    }
}