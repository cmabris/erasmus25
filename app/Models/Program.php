<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Program extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProgramFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;
    use Translatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'slug',
        'description',
        'is_active',
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
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($program) {
            if (empty($program->slug)) {
                $program->slug = Str::slug($program->name);
            }
        });
    }

    /**
     * Get the calls for the program.
     */
    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    /**
     * Get the news posts for the program.
     */
    public function newsPosts(): HasMany
    {
        return $this->hasMany(NewsPost::class);
    }

    /**
     * Register media collections for the program.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    /**
     * Register media conversions for the program.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Thumbnail conversion
        $this->addMediaConversion('thumbnail')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('image');

        // Medium conversion
        $this->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('image');

        // Large conversion
        $this->addMediaConversion('large')
            ->width(1200)
            ->height(900)
            ->sharpen(10)
            ->performOnCollections('image');
    }

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'name', 'description', 'is_active', 'order'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at', 'slug']);
    }

    /**
     * Cache key for active programs.
     */
    public const CACHE_KEY_ACTIVE = 'programs.active';

    /**
     * Cache TTL in seconds (1 hour).
     */
    public const CACHE_TTL = 3600;

    /**
     * Get cached list of active programs.
     * Used in filters across multiple components.
     */
    public static function getCachedActive(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE, self::CACHE_TTL, function () {
            return static::query()
                ->where('is_active', true)
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear the programs cache.
     * Call this when programs are created, updated, or deleted.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVE);
    }

    /**
     * Boot the model and register cache invalidation.
     */
    protected static function booted(): void
    {
        // Clear cache when a program is saved or deleted
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }
}
