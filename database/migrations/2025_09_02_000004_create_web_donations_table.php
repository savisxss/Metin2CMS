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
        Schema::create('web_donations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('payment_method'); // paypal, stripe, coinbase, etc
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->integer('coins_amount');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->json('payment_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['account_id']);
            $table->index(['status']);
            $table->index(['transaction_id']);
            $table->index(['created_at']);
        });

        Schema::create('web_donation_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->integer('coins');
            $table->integer('bonus_coins')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['is_featured']);
            $table->index(['sort_order']);
        });

        // Insert default donation packages
        DB::table('web_donation_packages')->insert([
            [
                'name' => 'Starter Pack',
                'description' => 'Perfect for new players',
                'price' => 5.00,
                'currency' => 'USD',
                'coins' => 500,
                'bonus_coins' => 0,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium Pack',
                'description' => 'Most popular choice',
                'price' => 10.00,
                'currency' => 'USD',
                'coins' => 1000,
                'bonus_coins' => 100,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ultimate Pack',
                'description' => 'Maximum value',
                'price' => 25.00,
                'currency' => 'USD',
                'coins' => 2500,
                'bonus_coins' => 500,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
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
        Schema::dropIfExists('web_donations');
        Schema::dropIfExists('web_donation_packages');
    }
};