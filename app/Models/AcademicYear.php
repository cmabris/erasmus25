<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AcademicYear extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicYearFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'is_current',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /**
     * Get the calls for the academic year.
     */
    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    /**
     * Get the news posts for the academic year.
     */
    public function newsPosts(): HasMany
    {
        return $this->hasMany(NewsPost::class);
    }

    /**
     * Get the documents for the academic year.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Cache key for the current academic year.
     */
    public const CACHE_KEY_CURRENT = 'academic_year.current';

    /**
     * Cache key for all academic years.
     */
    public const CACHE_KEY_ALL = 'academic_years.all';

    /**
     * Cache TTL for the current academic year (24 hours).
     */
    public const CACHE_TTL_CURRENT = 86400;

    /**
     * Cache TTL for all academic years (1 hour).
     */
    public const CACHE_TTL = 3600;

    /**
     * Scope to get the current academic year.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Get the current academic year (cached).
     */
    public static function getCurrent(): ?self
    {
        return Cache::remember(self::CACHE_KEY_CURRENT, self::CACHE_TTL_CURRENT, function () {
            return static::where('is_current', true)->first();
        });
    }

    /**
     * Get cached list of all academic years (ordered by year desc).
     * Used in filters across multiple components.
     */
    public static function getCachedAll(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL, function () {
            return static::query()
                ->orderBy('year', 'desc')
                ->get();
        });
    }

    /**
     * Clear all academic year caches.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_CURRENT);
        Cache::forget(self::CACHE_KEY_ALL);
    }

    /**
     * Clear the current academic year cache.
     *
     * @deprecated Use clearCache() instead
     */
    public static function clearCurrentCache(): void
    {
        self::clearCache();
    }

    /**
     * Mark this academic year as current and unmark others.
     */
    public function markAsCurrent(): bool
    {
        // Unmark all other current academic years
        static::where('is_current', true)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        // Mark this one as current
        $result = $this->update(['is_current' => true]);

        // Clear cache when current year changes
        static::clearCurrentCache();

        return $result;
    }

    /**
     * Unmark this academic year as current.
     */
    public function unmarkAsCurrent(): bool
    {
        $result = $this->update(['is_current' => false]);

        // Clear cache when current year changes
        static::clearCurrentCache();

        return $result;
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Handle cascade delete and null on delete when soft deleting
        static::deleting(function ($academicYear) {
            // If this is a force delete (hard delete), let database constraints handle it
            if ($academicYear->isForceDeleting()) {
                return;
            }

            // For soft delete, manually handle relationships
            // Delete calls in cascade (hard delete)
            $academicYear->calls()->each(function ($call) {
                $call->forceDelete();
            });

            // Delete news posts in cascade (hard delete)
            $academicYear->newsPosts()->each(function ($newsPost) {
                $newsPost->forceDelete();
            });

            // Set academic_year_id to null for documents (nullOnDelete)
            $academicYear->documents()->update(['academic_year_id' => null]);
        });

        // Clear cache when academic year is created, updated, or deleted
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
        static::restored(fn () => self::clearCache());
    }

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['year', 'start_date', 'end_date', 'is_current'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }
}
