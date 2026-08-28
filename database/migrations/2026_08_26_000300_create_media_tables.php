<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 32);
            $table->string('status', 24)->default('draft');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('administrative_area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['type', 'slug']);
            $table->index(['type', 'status']);
        });

        Schema::create('radio_stations', function (Blueprint $table): void {
            $table->foreignId('media_id')->primary()->constrained('media')->cascadeOnDelete();
            $table->string('call_sign')->nullable();
            $table->decimal('frequency', 8, 3)->nullable();
            $table->string('frequency_unit', 8)->nullable();
            $table->timestamps();
        });

        Schema::create('tv_channels', function (Blueprint $table): void {
            $table->foreignId('media_id')->primary()->constrained('media')->cascadeOnDelete();
            $table->string('call_sign')->nullable();
            $table->timestamps();
        });

        Schema::create('podcasts', function (Blueprint $table): void {
            $table->foreignId('media_id')->primary()->constrained('media')->cascadeOnDelete();
            $table->string('feed_url', 2048)->nullable();
            $table->char('feed_url_hash', 64)->nullable()->unique();
            $table->string('author')->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();
        });

        Schema::create('podcast_episodes', function (Blueprint $table): void {
            $table->foreignId('media_id')->primary()->constrained('media')->cascadeOnDelete();
            $table->unsignedBigInteger('podcast_id');
            $table->string('guid', 1024)->nullable();
            $table->char('guid_hash', 64)->nullable();
            $table->unsignedInteger('season_number')->nullable();
            $table->unsignedInteger('episode_number')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_explicit')->nullable();
            $table->timestamps();

            $table->foreign('podcast_id')->references('media_id')->on('podcasts')->cascadeOnDelete();
            $table->unique(['podcast_id', 'guid_hash']);
            $table->index(['podcast_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podcast_episodes');
        Schema::dropIfExists('podcasts');
        Schema::dropIfExists('tv_channels');
        Schema::dropIfExists('radio_stations');
        Schema::dropIfExists('media');
    }
};
