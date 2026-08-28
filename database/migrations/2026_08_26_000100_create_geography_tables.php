<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->char('iso_alpha_2', 2)->unique();
            $table->char('iso_alpha_3', 3)->unique();
            $table->char('iso_numeric', 3)->nullable()->unique();
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('administrative_areas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('administrative_areas')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'code']);
            $table->index(['country_id', 'name']);
        });

        Schema::create('cities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->foreignId('administrative_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();

            $table->index(['country_id', 'name']);
            $table->index(['administrative_area_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('administrative_areas');
        Schema::dropIfExists('countries');
    }
};
