<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stream_health_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stream_source_id')->constrained()->cascadeOnDelete();
            $table->boolean('was_healthy');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('content_type', 128)->nullable();
            $table->string('failure_reason', 500)->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['stream_source_id', 'checked_at']);
            $table->index(['was_healthy', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_health_checks');
    }
};
