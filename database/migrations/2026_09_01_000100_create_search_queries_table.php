<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_queries', function (Blueprint $table): void {
            $table->id();
            $table->string('query', 100);
            $table->string('normalized_query', 100);
            $table->string('context', 32)->default('global');
            $table->unsignedInteger('results_count')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('ip_hash', 64)->nullable();
            $table->timestamp('searched_at');
            $table->timestamps();
            $table->index(['normalized_query', 'searched_at']);
            $table->index(['results_count', 'searched_at']);
            $table->index(['context', 'searched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};
