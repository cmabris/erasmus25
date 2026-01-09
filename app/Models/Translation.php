<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    /** @use HasFactory<\Database\Factories\TranslationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'language_id',
        'field',
        'value',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Clear cache when translations are created, updated, or deleted
        // This ensures dropdowns show up-to-date data
        static::saved(function () {
            static::clearTranslationCaches();
        });

        static::deleted(function () {
            static::clearTranslationCaches();
        });
    }

    /**
     * Clear all translation-related caches.
     */
    protected static function clearTranslationCaches(): void
    {
        Cache::forget('translations.active_languages');
        Cache::forget('translations.active_programs');
        Cache::forget('translations.all_settings');
    }

    /**
     * Get the language that owns the translation.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the parent model (polymorphic).
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
