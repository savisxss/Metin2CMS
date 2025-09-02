<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $connection = 'player';
    protected $table = 'item';
    protected $primaryKey = 'id';

    protected $fillable = [
        'owner_id',
        'window',
        'pos',
        'vnum',
        'count',
        'serial',
        'bind',
        'socket0',
        'socket1',
        'socket2',
        'socket3',
        'socket4',
        'socket5',
        'attrtype0',
        'attrvalue0',
        'attrtype1',
        'attrvalue1',
        'attrtype2',
        'attrvalue2',
        'attrtype3',
        'attrvalue3',
        'attrtype4',
        'attrvalue4',
        'attrtype5',
        'attrvalue5',
        'attrtype6',
        'attrvalue6',
        'transmutation',
        'changelook',
        'seal_date',
    ];

    protected $casts = [
        'seal_date' => 'datetime',
        'bind' => 'boolean',
    ];

    public $timestamps = false;

    // Constants for item windows
    const WINDOW_INVENTORY = 'INVENTORY';
    const WINDOW_EQUIPMENT = 'EQUIPMENT';
    const WINDOW_DRAGON_SOUL = 'DRAGON_SOUL';
    const WINDOW_SAFEBOX = 'SAFEBOX';
    const WINDOW_MALL = 'MALL';

    // Relationships
    public function owner()
    {
        return $this->belongsTo(Player::class, 'owner_id')->on('player');
    }

    // Accessors
    public function getItemProtoAttribute()
    {
        return ItemProto::where('vnum', $this->vnum)->first();
    }

    public function getNameAttribute()
    {
        return $this->itemProto?->name ?? "Item {$this->vnum}";
    }

    public function getTypeAttribute()
    {
        return $this->itemProto?->type ?? 0;
    }

    public function getSubTypeAttribute()
    {
        return $this->itemProto?->subtype ?? 0;
    }

    public function getSockets()
    {
        return [
            $this->socket0,
            $this->socket1,
            $this->socket2,
            $this->socket3,
            $this->socket4,
            $this->socket5,
        ];
    }

    public function getAttributes()
    {
        $attributes = [];
        
        for ($i = 0; $i <= 6; $i++) {
            $typeKey = "attrtype{$i}";
            $valueKey = "attrvalue{$i}";
            
            if ($this->$typeKey > 0 && $this->$valueKey > 0) {
                $attributes[] = [
                    'type' => $this->$typeKey,
                    'value' => $this->$valueKey,
                ];
            }
        }
        
        return $attributes;
    }

    public function getUpgrade()
    {
        // For weapons and armor, socket0 typically contains upgrade level
        if (in_array($this->type, [1, 2]) && $this->socket0 > 0) {
            return min($this->socket0, 9);
        }
        
        return 0;
    }

    // Methods
    public function isEquipped()
    {
        return $this->window === self::WINDOW_EQUIPMENT;
    }

    public function isInInventory()
    {
        return $this->window === self::WINDOW_INVENTORY;
    }

    public function isInSafebox()
    {
        return $this->window === self::WINDOW_SAFEBOX;
    }

    public function isBound()
    {
        return $this->bind;
    }

    public function isSealed()
    {
        return $this->seal_date && $this->seal_date->isFuture();
    }

    public function hasTransmutation()
    {
        return $this->transmutation > 0;
    }

    public function hasChangeLook()
    {
        return $this->changelook > 0;
    }

    // Scopes
    public function scopeEquipped($query)
    {
        return $query->where('window', self::WINDOW_EQUIPMENT);
    }

    public function scopeInInventory($query)
    {
        return $query->where('window', self::WINDOW_INVENTORY);
    }

    public function scopeInSafebox($query)
    {
        return $query->where('window', self::WINDOW_SAFEBOX);
    }

    public function scopeByVnum($query, $vnum)
    {
        return $query->where('vnum', $vnum);
    }

    public function scopeByType($query, $type)
    {
        return $query->whereHas('itemProto', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }
}

class ItemProto extends Model
{
    protected $connection = 'player';
    protected $table = 'item_proto';
    protected $primaryKey = 'vnum';

    protected $fillable = [
        'vnum',
        'name',
        'locale_name',
        'type',
        'subtype',
        'weight',
        'size',
        'antiflag',
        'flag',
        'wearflag',
        'immuneflag',
        'gold',
        'shop_buy_price',
        'refined_vnum',
        'refine_set',
        'magic_pct',
        'socket_pct',
        'addon_type',
    ];

    public $timestamps = false;
    public $incrementing = false;

    public function items()
    {
        return $this->hasMany(Item::class, 'vnum', 'vnum')->on('player');
    }
}