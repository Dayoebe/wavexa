<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editorial_placements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('collection', 24);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['media_id', 'collection']);
            $table->index(['collection', 'starts_at', 'ends_at', 'position'], 'editorial_schedule_position_index');
        });

        Schema::create('country_promotions', function (Blueprint $table): void {
            $table->foreignId('country_id')->primary()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['starts_at', 'ends_at', 'position'], 'country_promotion_schedule_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_promotions');
        Schema::dropIfExists('editorial_placements');
    }
};
