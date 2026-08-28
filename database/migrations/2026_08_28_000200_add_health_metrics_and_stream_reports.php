<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stream_sources', function (Blueprint $table): void {
            $table->unsignedInteger('response_time_ms')->nullable()->after('last_successful_at');
            $table->unsignedSmallInteger('http_status')->nullable()->after('response_time_ms');
            $table->string('content_type', 128)->nullable()->after('http_status');
            $table->string('failure_reason', 500)->nullable()->after('content_type');
            $table->index(['status', 'failure_count', 'last_checked_at']);
        });

        Schema::create('stream_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stream_source_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 40)->default('not_playing');
            $table->string('details', 500)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['stream_source_id', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_reports');
        Schema::table('stream_sources', function (Blueprint $table): void {
            $table->dropIndex(['status', 'failure_count', 'last_checked_at']);
            $table->dropColumn(['response_time_ms', 'http_status', 'content_type', 'failure_reason']);
        });
    }
};
