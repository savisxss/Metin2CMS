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
        Schema::create('web_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('type'); // coins, items, gold, etc
            $table->json('reward_data'); // {"coins": 100} or {"items": [{"vnum": 123, "count": 1}]}
            $table->integer('max_uses')->default(1);
            $table->integer('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['code']);
            $table->index(['is_active']);
            $table->index(['expires_at']);
        });

        Schema::create('web_voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id');
            $table->unsignedBigInteger('account_id');
            $table->string('ip_address', 45);
            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->foreign('voucher_id')->references('id')->on('web_vouchers')->onDelete('cascade');
            $table->index(['voucher_id']);
            $table->index(['account_id']);
            $table->unique(['voucher_id', 'account_id']); // Prevent double redemption
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_voucher_redemptions');
        Schema::dropIfExists('web_vouchers');
    }
};