<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taskai_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taskai_app_update_id')->nullable()->constrained('taskai_app_updates')->nullOnDelete();
            $table->string('source', 80)->default('web');
            $table->string('ip_hash', 64)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taskai_downloads');
    }
};
