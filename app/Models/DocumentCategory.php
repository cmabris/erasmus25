<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DocumentCategory extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentCategoryFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * Cache key for all categories.
     */
    public const CACHE_KEY_ALL = 'document_categories.all';

    /**
     * Cache TTL in seconds (1 hour).
     */
    public const CACHE_TTL = 3600;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Boot the model and register cache invalidation.
     */
    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }

    /**
     * Get the documents for the category.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    /**
     * Get cached list of all categories (ordered by order, then name).
     * Used in filters across multiple components.
     */
    public static function getCachedAll(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL, function () {
            return static::query()
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear the document categories cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
    }
}
