<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('taskai_upgrade_expires_at')->nullable()->after('taskai_upgraded_at');
        });

        Schema::table('taskai_payments', function (Blueprint $table) {
            $table->string('plan_code')->nullable()->after('currency');
            $table->string('plan_name')->nullable()->after('plan_code');
            $table->unsignedInteger('duration_days')->nullable()->after('plan_name');
        });
    }

    public function down(): void
    {
        Schema::table('taskai_payments', function (Blueprint $table) {
            $table->dropColumn(['plan_code', 'plan_name', 'duration_days']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('taskai_upgrade_expires_at');
        });
    }
};
