<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Guild extends Model
{
    use HasFactory, LogsActivity;

    protected $connection = 'player';
    protected $table = 'guild';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'sp',
        'master',
        'level',
        'exp',
        'skill_point',
        'skill',
        'win',
        'draw',
        'loss',
        'ladder_point',
        'gold',
    ];

    protected $casts = [
        'skill' => 'array',
    ];

    public $timestamps = false;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['level', 'exp', 'gold', 'ladder_point'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function members()
    {
        return $this->hasMany(Player::class, 'guild_id')->on('player');
    }

    public function master()
    {
        return $this->belongsTo(Player::class, 'master', 'id')->on('player');
    }

    public function grades()
    {
        return $this->hasMany(GuildGrade::class, 'guild_id')->on('player');
    }

    // Accessors
    public function getFormattedGoldAttribute()
    {
        return number_format($this->gold);
    }

    public function getMemberCountAttribute()
    {
        return $this->members()->count();
    }

    public function getOnlineMemberCountAttribute()
    {
        return $this->members()->online()->count();
    }

    public function getWinRateAttribute()
    {
        $total = $this->win + $this->draw + $this->loss;
        return $total > 0 ? round(($this->win / $total) * 100, 1) : 0;
    }

    public function getRankAttribute()
    {
        return Guild::where('ladder_point', '>', $this->ladder_point)
                   ->orWhere(function ($query) {
                       $query->where('ladder_point', $this->ladder_point)
                             ->where('level', '>', $this->level);
                   })
                   ->count() + 1;
    }

    public function getAverageLevelAttribute()
    {
        return round($this->members()->avg('level'), 1);
    }

    // Methods
    public function hasSkill($skillId)
    {
        $skills = is_array($this->skill) ? $this->skill : [];
        return isset($skills[$skillId]) && $skills[$skillId] > 0;
    }

    public function getSkillLevel($skillId)
    {
        $skills = is_array($this->skill) ? $this->skill : [];
        return $skills[$skillId] ?? 0;
    }

    public function canLevelUp()
    {
        $expTable = $this->getExpTable();
        return isset($expTable[$this->level + 1]) && $this->exp >= $expTable[$this->level + 1];
    }

    public function isActive()
    {
        return $this->members()->online()->exists();
    }

    private function getExpTable()
    {
        // Simplified guild exp table
        $baseExp = 10000;
        $expTable = [];
        
        for ($i = 1; $i <= 20; $i++) {
            $expTable[$i] = $baseExp * pow(1.5, $i);
        }
        
        return $expTable;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereHas('members', function ($q) {
            $q->online();
        });
    }

    public function scopeTopLevel($query, $limit = 10)
    {
        return $query->orderBy('level', 'desc')
                    ->orderBy('exp', 'desc')
                    ->limit($limit);
    }

    public function scopeTopLadder($query, $limit = 10)
    {
        return $query->orderBy('ladder_point', 'desc')
                    ->orderBy('level', 'desc')
                    ->limit($limit);
    }

    public function scopeByMasterName($query, $name)
    {
        return $query->whereHas('master', function ($q) use ($name) {
            $q->where('name', 'like', "%{$name}%");
        });
    }
}

class GuildGrade extends Model
{
    protected $connection = 'player';
    protected $table = 'guild_grade';
    protected $primaryKey = 'guild_id';

    protected $fillable = [
        'guild_id',
        'grade',
        'name',
        'auth_flag',
    ];

    public $timestamps = false;
    public $incrementing = false;

    public function guild()
    {
        return $this->belongsTo(Guild::class, 'guild_id')->on('player');
    }
}