<?php
// app/Models/Article.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'article_id';

    protected $fillable = [
        'title',
        'source_url',
        'image_url',
        'category',
        'summary',
        'content',
        'author',
        'source_name',
        'published_at',
        'created_by',
        'is_published',
        'sort_order',
        'is_featured',
        'view_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured'  => 'boolean',
        'published_at' => 'datetime',
        'view_count'   => 'integer',
        'sort_order'   => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    // ========================================
    // HELPERS
    // ========================================

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * All available categories across the system.
     */
    public static function categories(): array
    {
        return [
            'Heart Health',
            'Nutrition',
            'Sleep',
            'Mental Health',
            'Exercise',
            'Hydration',
            'Diabetes',
            'Respiratory',
            'General',
        ];
    }
}