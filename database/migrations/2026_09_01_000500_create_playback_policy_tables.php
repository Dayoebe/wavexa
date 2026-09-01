<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playback_formats', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 32)->unique();
            $table->string('label');
            $table->string('media_kind', 16)->default('both');
            $table->string('mime_type')->nullable();
            $table->boolean('uses_hls')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
        Schema::create('stream_geo_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stream_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 16);
            $table->timestamps();
            $table->unique(['stream_source_id', 'country_id']);
            $table->index(['stream_source_id', 'mode']);
        });
        Schema::create('rights_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_source_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 16)->default('pending');
            $table->string('note', 500)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('playback_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('label');
            $table->string('message', 500);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('playback_formats')->insert([
            ['key' => 'mp3', 'label' => 'MP3 audio', 'media_kind' => 'audio', 'mime_type' => 'audio/mpeg', 'uses_hls' => false, 'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'aac', 'label' => 'AAC audio', 'media_kind' => 'audio', 'mime_type' => 'audio/aac', 'uses_hls' => false, 'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'hls', 'label' => 'HTTP Live Streaming', 'media_kind' => 'both', 'mime_type' => 'application/vnd.apple.mpegurl', 'uses_hls' => true, 'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'mp4', 'label' => 'MP4 video', 'media_kind' => 'video', 'mime_type' => 'video/mp4', 'uses_hls' => false, 'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'webm', 'label' => 'WebM video', 'media_kind' => 'video', 'mime_type' => 'video/webm', 'uses_hls' => false, 'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'm4a', 'label' => 'M4A audio', 'media_kind' => 'audio', 'mime_type' => 'audio/mp4', 'uses_hls' => false, 'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ogg', 'label' => 'Ogg audio', 'media_kind' => 'audio', 'mime_type' => 'audio/ogg', 'uses_hls' => false, 'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('playback_messages')->insert(collect([
            'connecting' => ['Connecting', 'Connecting to the provider…'], 'buffering' => ['Buffering', 'Stabilizing the live stream…'],
            'offline' => ['Unavailable', 'Every available source is currently unavailable.'], 'geoblocked' => ['Location restricted', 'This stream is not available in your location.'],
            'mixed_content' => ['Insecure stream', 'This HTTP source is blocked on a secure page.'], 'unsupported' => ['Unsupported format', 'This stream format is not supported for playback.'],
            'rights_restricted' => ['Rights restricted', 'This source is unavailable while its distribution rights are restricted.'],
        ])->map(fn ($item, $key) => ['key' => $key, 'label' => $item[0], 'message' => $item[1], 'is_active' => true, 'created_at' => $now, 'updated_at' => $now])->values()->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('playback_messages');
        Schema::dropIfExists('rights_reviews');
        Schema::dropIfExists('stream_geo_rules');
        Schema::dropIfExists('playback_formats');
    }
};
