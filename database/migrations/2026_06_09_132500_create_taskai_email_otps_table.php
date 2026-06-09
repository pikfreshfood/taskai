<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taskai_email_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email', 190);
            $table->string('purpose', 40);
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->unique(['email', 'purpose']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taskai_email_otps');
    }
};
