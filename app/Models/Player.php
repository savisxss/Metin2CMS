<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Player extends Model
{
    use HasFactory, LogsActivity;

    protected $connection = 'player';
    protected $table = 'player';
    protected $primaryKey = 'id';

    protected $fillable = [
        'account_id',
        'name',
        'level',
        'exp',
        'job',
        'skill_group',
        'alignment',
        'gold',
        'stat_point',
        'skill_point',
        'quickslot',
        'ip',
        'part_main',
        'part_base',
        'part_hair',
        'part_sash',
        'x',
        'y',
        'z',
        'map_index',
        'exit_x',
        'exit_y',
        'exit_map_index',
        'hp',
        'mp',
        'stamina',
        'random_hp',
        'random_sp',
        'playtime',
        'sub_skill_point',
        'stat_reset_count',
        'horse_hp',
        'horse_stamina',
        'horse_level',
        'horse_hp_droptime',
        'horse_riding',
        'horse_skill_point',
        'empire',
        'guild_id',
        'last_play',
    ];

    protected $casts = [
        'last_play' => 'datetime',
        'horse_hp_droptime' => 'datetime',
        'horse_riding' => 'boolean',
    ];

    public $timestamps = false;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['level', 'gold', 'guild_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function guild()
    {
        return $this->belongsTo(Guild::class, 'guild_id')->on('player');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'owner_id')->on('player');
    }

    // Accessors & Mutators
    public function getJobNameAttribute()
    {
        $jobs = [
            0 => 'Warrior (M)',
            1 => 'Ninja (F)', 
            2 => 'Sura (M)',
            3 => 'Shaman (F)',
            4 => 'Warrior (F)',
            5 => 'Ninja (M)',
            6 => 'Sura (F)',
            7 => 'Shaman (M)',
            8 => 'Lycan (M)',
            9 => 'Lycan (F)',
        ];

        return $jobs[$this->job] ?? 'Unknown';
    }

    public function getEmpireNameAttribute()
    {
        $empires = [
            1 => 'Shinsoo',
            2 => 'Chunjo', 
            3 => 'Jinno',
        ];

        return $empires[$this->empire] ?? 'Unknown';
    }

    public function getFormattedGoldAttribute()
    {
        return number_format($this->gold);
    }

    public function getPlaytimeHoursAttribute()
    {
        return round($this->playtime / 3600, 1);
    }

    public function getRankAttribute()
    {
        return Player::where('level', '>', $this->level)
                    ->orWhere(function ($query) {
                        $query->where('level', $this->level)
                              ->where('exp', '>', $this->exp);
                    })
                    ->count() + 1;
    }

    // Methods
    public function isOnline()
    {
        return $this->last_play && $this->last_play->isAfter(now()->subMinutes(5));
    }

    public function getSkillGroup()
    {
        return $this->skill_group == 1 ? 'M' : 'G';
    }

    public function hasHorse()
    {
        return $this->horse_level > 0;
    }

    public function isInGuild()
    {
        return $this->guild_id > 0;
    }

    public function canLevelUp()
    {
        $expTable = $this->getExpTable();
        return isset($expTable[$this->level + 1]) && $this->exp >= $expTable[$this->level + 1];
    }

    private function getExpTable()
    {
        // Simplified exp table - should be loaded from config
        $baseExp = 300;
        $expTable = [];
        
        for ($i = 1; $i <= 120; $i++) {
            $expTable[$i] = $baseExp * pow(1.1, $i);
        }
        
        return $expTable;
    }

    // Scopes
    public function scopeOnline($query)
    {
        return $query->where('last_play', '>', now()->subMinutes(5));
    }

    public function scopeByEmpire($query, $empire)
    {
        return $query->where('empire', $empire);
    }

    public function scopeByJob($query, $job)
    {
        return $query->where('job', $job);
    }

    public function scopeInGuild($query)
    {
        return $query->where('guild_id', '>', 0);
    }

    public function scopeTopLevel($query, $limit = 10)
    {
        return $query->orderBy('level', 'desc')
                    ->orderBy('exp', 'desc')
                    ->limit($limit);
    }

    public function scopeTopGold($query, $limit = 10)
    {
        return $query->orderBy('gold', 'desc')->limit($limit);
    }
}