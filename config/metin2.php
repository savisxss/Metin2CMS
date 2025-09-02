<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for various cache durations and settings used throughout
    | the Metin2 CMS application.
    |
    */

    'cache' => [
        'ttl' => [
            // Short-lived cache (5 minutes)
            'short' => 300,
            
            // Medium-lived cache (15 minutes)
            'medium' => 900,
            
            // Long-lived cache (1 hour)
            'long' => 3600,
            
            // Very long-lived cache (6 hours)
            'very_long' => 21600,
        ],
        
        'tags' => [
            'server' => 'server_data',
            'players' => 'player_data',
            'guilds' => 'guild_data',
            'news' => 'news_data',
            'rankings' => 'ranking_data',
        ],
        
        // Enable/disable cache warming on boot
        'warm_on_boot' => env('CACHE_WARM_ON_BOOT', true),
        
        // Cache response middleware settings
        'response' => [
            'enabled' => env('CACHE_RESPONSES', true),
            'key_prefix' => 'response:',
            'vary_headers' => ['Accept-Language', 'X-Requested-With'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Various performance-related configurations.
    |
    */

    'performance' => [
        // Database query optimization
        'eager_load_relations' => true,
        
        // API pagination limits
        'pagination' => [
            'default_per_page' => 20,
            'max_per_page' => 100,
        ],
        
        // Rankings cache settings
        'rankings' => [
            'cache_duration' => 600, // 10 minutes
            'max_items' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Game Server Settings
    |--------------------------------------------------------------------------
    |
    | Settings related to the game server integration.
    |
    */

    'game' => [
        'max_level' => 120,
        'empires' => [
            1 => 'Shinsoo',
            2 => 'Chunjo', 
            3 => 'Jinno',
        ],
        'jobs' => [
            0 => 'Warrior (M)',
            1 => 'Assassin (F)',
            2 => 'Sura (M)',
            3 => 'Shaman (F)',
            4 => 'Warrior (F)',
            5 => 'Assassin (M)',
            6 => 'Sura (F)',
            7 => 'Shaman (M)',
            8 => 'Lycan (M)',
            9 => 'Lycan (F)',
        ],
    ],
];