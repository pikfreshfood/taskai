<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taskai_app_updates', function (Blueprint $table) {
            $table->id();
            $table->string('version', 80)->unique();
            $table->text('download_url')->nullable();
            $table->text('release_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_required')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taskai_app_updates');
    }
};
