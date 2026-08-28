<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_media', function (Blueprint $table): void {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->primary(['category_id', 'media_id']);
        });

        Schema::create('genre_media', function (Blueprint $table): void {
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->primary(['genre_id', 'media_id']);
        });

        Schema::create('language_media', function (Blueprint $table): void {
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->primary(['language_id', 'media_id']);
            $table->index(['media_id', 'is_primary']);
        });

        Schema::create('media_artworks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('kind', 24);
            $table->string('url', 2048)->nullable();
            $table->string('disk')->nullable();
            $table->string('path', 2048)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['media_id', 'kind', 'is_primary']);
        });

        Schema::create('media_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('source_provider_id')->constrained()->restrictOnDelete();
            $table->string('external_identifier', 1024)->nullable();
            $table->char('external_identifier_hash', 64)->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->timestamp('imported_at');
            $table->timestamp('last_synchronized_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['source_provider_id', 'external_identifier_hash']);
            $table->index(['media_id', 'source_provider_id']);
        });

        Schema::create('stream_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('source_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->text('url');
            $table->text('resolved_url')->nullable();
            $table->char('url_hash', 64);
            $table->string('protocol', 24);
            $table->string('format', 32);
            $table->string('codec', 32)->nullable();
            $table->unsignedInteger('bitrate_kbps')->nullable();
            $table->string('status', 24)->default('unknown');
            $table->string('verification_status', 24)->default('unverified');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_successful_at')->nullable();
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['media_id', 'url_hash']);
            $table->index(['status', 'last_checked_at']);
            $table->index(['media_id', 'verification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_sources');
        Schema::dropIfExists('media_sources');
        Schema::dropIfExists('media_artworks');
        Schema::dropIfExists('language_media');
        Schema::dropIfExists('genre_media');
        Schema::dropIfExists('category_media');
    }
};
