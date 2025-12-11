<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();

            $table->string('external_id')->nullable();
            $table->string('url')->unique();
            $table->string('author')->nullable()->index();
            $table->string('canonical_url')->nullable()->index();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('language', 8)->nullable();
            $table->string('image_url')->nullable();
            $table->string('normalized_hash')->index();
            $table->json('ingestion_metadata')->nullable();
            $table->unique(['source_id', 'external_id'], 'idx_articles_source_external');
            $table->index(['source_id', 'published_at'], 'idx_articles_source_published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
