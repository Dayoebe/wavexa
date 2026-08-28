<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radio_stations', function (Blueprint $table): void {
            $table->string('source_state')->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('source_vote_count')->default(0)->index();
            $table->unsignedInteger('source_click_count')->default(0);
            $table->integer('source_click_trend')->default(0);
            $table->timestamp('source_changed_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('radio_stations', function (Blueprint $table): void {
            $table->dropIndex(['source_state']);
            $table->dropIndex(['source_vote_count']);
            $table->dropIndex(['source_changed_at']);
            $table->dropColumn([
                'source_state', 'latitude', 'longitude', 'source_vote_count',
                'source_click_count', 'source_click_trend', 'source_changed_at',
            ]);
        });
    }
};
