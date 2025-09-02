<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\WebSetting;
use Illuminate\Support\Collection;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register settings service
        $this->app->singleton('settings', function () {
            return new class {
                private $cache = [];
                
                public function get($key, $default = null)
                {
                    if (!isset($this->cache[$key])) {
                        try {
                            $setting = WebSetting::where('key', $key)->first();
                            $this->cache[$key] = $setting ? $this->castValue($setting->value, $setting->type) : $default;
                        } catch (\Exception $e) {
                            return $default;
                        }
                    }
                    
                    return $this->cache[$key];
                }
                
                public function set($key, $value, $type = 'string')
                {
                    WebSetting::updateOrCreate(
                        ['key' => $key],
                        [
                            'value' => $this->prepareValue($value, $type),
                            'type' => $type
                        ]
                    );
                    
                    $this->cache[$key] = $value;
                }
                
                public function getPublic()
                {
                    try {
                        return WebSetting::where('is_public', true)
                            ->get()
                            ->mapWithKeys(function ($setting) {
                                return [$setting->key => $this->castValue($setting->value, $setting->type)];
                            });
                    } catch (\Exception $e) {
                        return collect();
                    }
                }
                
                private function castValue($value, $type)
                {
                    switch ($type) {
                        case 'boolean':
                            return (bool) $value;
                        case 'integer':
                            return (int) $value;
                        case 'json':
                        case 'array':
                            return json_decode($value, true);
                        default:
                            return $value;
                    }
                }
                
                private function prepareValue($value, $type)
                {
                    switch ($type) {
                        case 'boolean':
                            return $value ? '1' : '0';
                        case 'json':
                        case 'array':
                            return json_encode($value);
                        default:
                            return (string) $value;
                    }
                }
            };
        });
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Register model observers
        \App\Models\Player::observe(\App\Observers\PlayerObserver::class);
        \App\Models\Guild::observe(\App\Observers\GuildObserver::class);
    }
}

class WebSetting extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'web_settings';
    
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];
}