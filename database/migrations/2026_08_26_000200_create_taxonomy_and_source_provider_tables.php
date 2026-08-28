<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('genres', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->char('iso_639_1', 2)->nullable()->unique();
            $table->char('iso_639_3', 3)->unique();
            $table->timestamps();
        });

        Schema::create('source_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('website_url', 2048)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_providers');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('categories');
    }
};
