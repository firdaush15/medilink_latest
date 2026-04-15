<?php
// database/migrations/2026_03_30_000001_create_articles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id('article_id');

            // Core content
            $table->string('title');
            $table->string('source_url')->nullable()->comment('Original URL pasted by admin');
            $table->string('image_url')->nullable()->comment('Cover image URL');
            $table->string('category')->default('General');
            $table->text('summary')->nullable()->comment('Short description shown on card');
            $table->longText('content')->nullable()->comment('Full article body');

            // Metadata
            $table->string('author')->nullable();
            $table->string('source_name')->nullable()->comment('e.g. WebMD, Healthline');
            $table->timestamp('published_at')->nullable();

            // Admin controls
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0)->comment('Lower = shown first');
            $table->boolean('is_featured')->default(false);

            // Analytics
            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'sort_order']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};