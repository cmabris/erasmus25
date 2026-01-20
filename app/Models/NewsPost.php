<?php

namespace App\Models;

use App\Livewire\Public\Home;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class NewsPost extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\NewsPostFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'academic_year_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'country',
        'city',
        'host_entity',
        'mobility_type',
        'mobility_category',
        'status',
        'published_at',
        'author_id',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($newsPost) {
            if (empty($newsPost->slug)) {
                $newsPost->slug = Str::slug($newsPost->title);
            }
        });
    }

    /**
     * Get the program that owns the news post.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the academic year that owns the news post.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the author of the news post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the user who reviewed the news post.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the tags for the news post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NewsTag::class, 'news_post_tag');
    }

    /**
     * Register media collections for the news post.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('videos')
            ->acceptsMimeTypes(['video/mp4', 'video/webm', 'video/ogg']);

        $this->addMediaCollection('audio')
            ->acceptsMimeTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg']);
    }

    /**
     * Register media conversions for the news post.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Thumbnail - for cards and listings
        $this->addMediaConversion('thumbnail')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->quality(85)
            ->format('webp')
            ->nonQueued()
            ->performOnCollections('featured', 'gallery');

        // Medium - for intermediate views and related news
        $this->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->quality(85)
            ->format('webp')
            ->performOnCollections('featured', 'gallery');

        // Large - for detail views and hero images
        $this->addMediaConversion('large')
            ->width(1200)
            ->height(900)
            ->sharpen(10)
            ->quality(85)
            ->format('webp')
            ->performOnCollections('featured', 'gallery');

        // Hero - for full-width hero sections (news detail page)
        $this->addMediaConversion('hero')
            ->width(1920)
            ->height(1080)
            ->sharpen(5)
            ->quality(85)
            ->format('webp')
            ->performOnCollections('featured');
    }

    /**
     * Get the first media item from the featured collection, excluding soft-deleted ones.
     */
    public function getFirstMedia(string $collectionName = 'default', array $filters = []): ?Media
    {
        $media = $this->getMedia($collectionName, $filters)->first();

        return $media;
    }

    /**
     * Check if the model has media in the given collection, excluding soft-deleted ones.
     */
    public function hasMedia(string $collectionName = 'default'): bool
    {
        return $this->getMedia($collectionName)->isNotEmpty();
    }

    /**
     * Get all media items from the given collection, excluding soft-deleted ones.
     */
    public function getMedia(string $collectionName = 'default', callable|array $filters = []): \Illuminate\Support\Collection
    {
        // Use the trait's getMedia method to get all media
        $allMedia = $this->media()
            ->where('collection_name', $collectionName)
            ->get();

        // Apply filters if provided (basic implementation)
        if (! empty($filters)) {
            if (is_callable($filters)) {
                $allMedia = $allMedia->filter($filters);
            } elseif (is_array($filters)) {
                foreach ($filters as $key => $value) {
                    $allMedia = $allMedia->where($key, $value);
                }
            }
        }

        // Filter out soft-deleted media
        return $allMedia->reject(fn ($item) => $this->isMediaSoftDeleted($item));
    }

    /**
     * Get all media items from the given collection, including soft-deleted ones.
     */
    public function getMediaWithDeleted(string $collectionName = 'default', callable|array $filters = []): \Illuminate\Support\Collection
    {
        // Get all media without filtering soft-deleted ones
        $allMedia = $this->media()
            ->where('collection_name', $collectionName)
            ->get();

        // Apply filters if provided
        if (! empty($filters)) {
            if (is_callable($filters)) {
                $allMedia = $allMedia->filter($filters);
            } elseif (is_array($filters)) {
                foreach ($filters as $key => $value) {
                    $allMedia = $allMedia->where($key, $value);
                }
            }
        }

        return $allMedia;
    }

    /**
     * Check if a media item is soft-deleted.
     */
    public function isMediaSoftDeleted(Media $media): bool
    {
        $customProperties = $media->custom_properties ?? [];

        return isset($customProperties['deleted_at']) && $customProperties['deleted_at'] !== null;
    }

    /**
     * Soft delete the featured image (mark as deleted without removing the file).
     */
    public function softDeleteFeaturedImage(): bool
    {
        $media = $this->getFirstMedia('featured');

        if (! $media) {
            return false;
        }

        // Mark as deleted using custom_properties
        $customProperties = $media->custom_properties ?? [];
        $customProperties['deleted_at'] = now()->toIso8601String();
        $media->custom_properties = $customProperties;
        $media->save();

        return true;
    }

    /**
     * Restore a soft-deleted featured image.
     */
    public function restoreFeaturedImage(): bool
    {
        // Get all media including soft-deleted ones
        $media = $this->getMediaWithDeleted('featured')
            ->first(fn ($item) => $this->isMediaSoftDeleted($item));

        if (! $media) {
            return false;
        }

        // Remove deleted_at from custom_properties
        $customProperties = $media->custom_properties ?? [];
        unset($customProperties['deleted_at']);
        $media->custom_properties = $customProperties;
        $media->save();

        return true;
    }

    /**
     * Force delete the featured image (permanent deletion).
     */
    public function forceDeleteFeaturedImage(): bool
    {
        $media = $this->getFirstMedia('featured');

        if (! $media) {
            return false;
        }

        // Permanently delete the media item and its file
        $media->delete();

        return true;
    }

    /**
     * Force delete a specific media item by ID (permanent deletion).
     */
    public function forceDeleteMediaById(int $mediaId): bool
    {
        // Get all media including soft-deleted ones
        $allMedia = $this->getMediaWithDeleted('featured');
        $media = $allMedia->firstWhere('id', $mediaId);

        if (! $media) {
            return false;
        }

        // Permanently delete the media item and its file
        $media->delete();

        return true;
    }

    /**
     * Get all soft-deleted media items from the featured collection.
     */
    public function getSoftDeletedFeaturedImages(): \Illuminate\Support\Collection
    {
        // Get all media including soft-deleted ones
        $allMedia = $this->getMediaWithDeleted('featured');

        // Filter only soft-deleted ones
        return $allMedia->filter(fn ($item) => $this->isMediaSoftDeleted($item));
    }

    /**
     * Check if there are any soft-deleted featured images.
     */
    public function hasSoftDeletedFeaturedImages(): bool
    {
        return $this->getSoftDeletedFeaturedImages()->isNotEmpty();
    }

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'program_id',
                'academic_year_id',
                'title',
                'excerpt',
                'status',
                'published_at',
                'country',
                'city',
                'host_entity',
                'mobility_type',
                'mobility_category',
            ])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at', 'slug', 'author_id', 'reviewed_by', 'reviewed_at']);
    }

    /**
     * Clear home page cache when news status or publication changes.
     */
    protected static function booted(): void
    {
        static::saved(function (self $newsPost) {
            if ($newsPost->wasChanged(['status', 'published_at'])) {
                Home::clearCache();
            }
        });

        static::deleted(fn () => Home::clearCache());
    }
}
