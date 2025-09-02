<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('web_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, array
            $table->text('description')->nullable();
            $table->string('group')->default('general');
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['group']);
            $table->index(['is_public']);
        });

        // Insert default settings
        DB::table('web_settings')->insert([
            [
                'key' => 'site_name',
                'value' => 'Metin2 CMS',
                'type' => 'string',
                'description' => 'Website name',
                'group' => 'general',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_description',
                'value' => 'Best Metin2 Private Server',
                'type' => 'string',
                'description' => 'Website description',
                'group' => 'general',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'server_rates',
                'value' => '{"exp": 10, "yang": 10, "drop": 10}',
                'type' => 'json',
                'description' => 'Server rates configuration',
                'group' => 'server',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable maintenance mode',
                'group' => 'general',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'registration_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable user registration',
                'group' => 'auth',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_settings');
    }
};