<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('taskai_upgraded_at')->nullable()->after('remember_token');
        });

        Schema::create('taskai_devices', function (Blueprint $table) {
            $table->id();
            $table->string('serial_hash', 64)->unique();
            $table->string('serial_hint', 16)->nullable();
            $table->foreignId('last_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_usage_seconds', 12, 3)->default(0);
            $table->unsignedInteger('free_usage_limit_seconds')->default(86400);
            $table->timestamp('upgraded_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('taskai_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('device_serial_hash', 64)->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('taskai_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('taskai_device_id')->nullable()->constrained('taskai_devices')->nullOnDelete();
            $table->string('reference')->unique();
            $table->unsignedInteger('amount');
            $table->string('currency', 8)->default('NGN');
            $table->string('status')->default('pending');
            $table->text('authorization_url')->nullable();
            $table->json('paystack_data')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taskai_payments');
        Schema::dropIfExists('taskai_api_tokens');
        Schema::dropIfExists('taskai_devices');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('taskai_upgraded_at');
        });
    }
};
