<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ErasmusEvent extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ErasmusEventFactory> */
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
        'call_id',
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'location',
        'is_public',
        'is_all_day',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_public' => 'boolean',
            'is_all_day' => 'boolean',
        ];
    }

    /**
     * Get the program that owns the event.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the call that owns the event.
     */
    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    /**
     * Get the user who created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include public events.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>=', now()->startOfDay());
    }

    /**
     * Scope a query to only include past events.
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('start_date', '<', now()->startOfDay());
    }

    /**
     * Scope a query to only include events for a specific date.
     */
    public function scopeForDate(Builder $query, Carbon|string $date): Builder
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $query->whereDate('start_date', $date);
    }

    /**
     * Scope a query to only include events for a specific month.
     */
    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('start_date', $year)
            ->whereMonth('start_date', $month);
    }

    /**
     * Scope a query to only include events for a specific program.
     */
    public function scopeForProgram(Builder $query, int $programId): Builder
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope a query to only include events for a specific call.
     */
    public function scopeForCall(Builder $query, int $callId): Builder
    {
        return $query->where('call_id', $callId);
    }

    /**
     * Scope a query to only include events of a specific type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope a query to only include events in a date range.
     */
    public function scopeInDateRange(Builder $query, Carbon|string $from, Carbon|string $to): Builder
    {
        $from = $from instanceof Carbon ? $from : Carbon::parse($from);
        $to = $to instanceof Carbon ? $to : Carbon::parse($to);

        return $query->whereBetween('start_date', [
            $from->startOfDay(),
            $to->endOfDay(),
        ]);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if the event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_date->isFuture();
    }

    /**
     * Check if the event is today.
     */
    public function isToday(): bool
    {
        return $this->start_date->isToday();
    }

    /**
     * Check if the event is past.
     */
    public function isPast(): bool
    {
        return $this->start_date->isPast() && ! $this->isToday();
    }

    /**
     * Get the duration of the event in hours.
     */
    public function duration(): ?float
    {
        if (! $this->end_date) {
            return null;
        }

        return $this->start_date->diffInHours($this->end_date, true);
    }

    /**
     * Check if the event is all day (no specific time).
     */
    public function isAllDay(): bool
    {
        // First check the is_all_day field if it exists
        if (isset($this->is_all_day)) {
            return (bool) $this->is_all_day;
        }

        // Fallback to checking times if field doesn't exist
        return $this->start_date->format('H:i') === '00:00' &&
            (! $this->end_date || $this->end_date->format('H:i') === '00:00');
    }

    /**
     * Get formatted date range string.
     */
    public function getFormattedDateRangeAttribute(): string
    {
        $start = $this->start_date->translatedFormat('d F Y');
        $startTime = $this->start_date->format('H:i');

        if ($this->end_date) {
            $end = $this->end_date->translatedFormat('d F Y');
            $endTime = $this->end_date->format('H:i');

            if ($this->start_date->isSameDay($this->end_date)) {
                return "{$start} de {$startTime} a {$endTime}";
            }

            return "Del {$start} ({$startTime}) al {$end} ({$endTime})";
        }

        return "{$start} a las {$startTime}";
    }

    // ============================================
    // MEDIA LIBRARY
    // ============================================

    /**
     * Register media collections for the event.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    /**
     * Register media conversions for the event.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Thumbnail conversion
        $this->addMediaConversion('thumbnail')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('images');

        // Medium conversion
        $this->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('images');

        // Large conversion
        $this->addMediaConversion('large')
            ->width(1200)
            ->height(900)
            ->sharpen(10)
            ->performOnCollections('images');
    }

    // ============================================
    // MEDIA SOFT DELETE METHODS
    // ============================================

    /**
     * Get the first media item from the images collection, excluding soft-deleted ones.
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
        // Use the trait's media() relationship to get all media
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
     * Soft delete a specific media item by ID (mark as deleted without removing the file).
     */
    public function softDeleteMediaById(int $mediaId): bool
    {
        // Get all media including soft-deleted ones
        $allMedia = $this->getMediaWithDeleted('images');
        $media = $allMedia->firstWhere('id', $mediaId);

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
     * Restore a soft-deleted media item by ID.
     */
    public function restoreMediaById(int $mediaId): bool
    {
        // Get all media including soft-deleted ones
        $allMedia = $this->getMediaWithDeleted('images');
        $media = $allMedia->firstWhere('id', $mediaId);

        if (! $media) {
            return false;
        }

        // Check if it's actually soft-deleted
        if (! $this->isMediaSoftDeleted($media)) {
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
     * Force delete a specific media item by ID (permanent deletion).
     */
    public function forceDeleteMediaById(int $mediaId): bool
    {
        // Get all media including soft-deleted ones
        $allMedia = $this->getMediaWithDeleted('images');
        $media = $allMedia->firstWhere('id', $mediaId);

        if (! $media) {
            return false;
        }

        // Permanently delete the media item and its file
        $media->delete();

        return true;
    }

    /**
     * Get all soft-deleted media items from the images collection.
     */
    public function getSoftDeletedImages(): \Illuminate\Support\Collection
    {
        // Get all media including soft-deleted ones
        $allMedia = $this->getMediaWithDeleted('images');

        // Filter only soft-deleted ones
        return $allMedia->filter(fn ($item) => $this->isMediaSoftDeleted($item));
    }

    /**
     * Check if there are any soft-deleted images.
     */
    public function hasSoftDeletedImages(): bool
    {
        return $this->getSoftDeletedImages()->isNotEmpty();
    }

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'program_id',
                'call_id',
                'title',
                'description',
                'event_type',
                'start_date',
                'end_date',
                'location',
                'is_public',
                'is_all_day',
            ])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at', 'created_by']);
    }
}
