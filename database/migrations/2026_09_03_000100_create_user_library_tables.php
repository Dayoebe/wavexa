<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'media_id']);
        });

        Schema::create('user_playback_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('play_count')->default(1);
            $table->timestamp('last_played_at');
            $table->timestamps();
            $table->unique(['user_id', 'media_id']);
            $table->index(['user_id', 'last_played_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_playback_history');
        Schema::dropIfExists('user_favorites');
    }
};
