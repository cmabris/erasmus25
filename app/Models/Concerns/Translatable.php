<?php

namespace App\Models\Concerns;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Translatable
{
    /**
     * Boot the translatable trait.
     */
    public static function bootTranslatable(): void
    {
        // Se puede añadir lógica de inicialización si es necesario
    }

    /**
     * Get all translations for this model.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get translation for a specific field and locale.
     *
     * @param  string  $field  Field name to translate
     * @param  string|null  $locale  Locale code (null = current locale)
     * @return string|null
     */
    public function translate(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? getCurrentLanguageCode();

        $translation = $this->translations()
            ->where('field', $field)
            ->whereHas('language', function ($query) use ($locale) {
                $query->where('code', $locale)->where('is_active', true);
            })
            ->first();

        return $translation?->value;
    }

    /**
     * Get all translations for a specific locale.
     *
     * @param  string|null  $locale  Locale code (null = current locale)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function translations(?string $locale = null)
    {
        $locale = $locale ?? getCurrentLanguageCode();

        return $this->translations()
            ->whereHas('language', function ($query) use ($locale) {
                $query->where('code', $locale)->where('is_active', true);
            })
            ->get()
            ->keyBy('field');
    }

    /**
     * Set translation for a specific field and locale.
     *
     * @param  string  $field  Field name
     * @param  string  $locale  Locale code
     * @param  string  $value  Translation value
     * @return \App\Models\Translation
     */
    public function setTranslation(string $field, string $locale, string $value): Translation
    {
        $language = Language::where('code', $locale)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->translations()->updateOrCreate(
            [
                'language_id' => $language->id,
                'field' => $field,
            ],
            [
                'value' => $value,
            ]
        );
    }

    /**
     * Check if translation exists for a specific field and locale.
     *
     * @param  string  $field  Field name
     * @param  string|null  $locale  Locale code (null = current locale)
     * @return bool
     */
    public function hasTranslation(string $field, ?string $locale = null): bool
    {
        $locale = $locale ?? getCurrentLanguageCode();

        return $this->translations()
            ->where('field', $field)
            ->whereHas('language', function ($query) use ($locale) {
                $query->where('code', $locale)->where('is_active', true);
            })
            ->exists();
    }

    /**
     * Get translated attribute (accessor helper).
     * Usage: $model->translated_title or $model->getTranslatedTitleAttribute()
     *
     * @param  string  $field  Field name
     * @return string|null
     */
    public function getTranslatedAttribute(string $field): ?string
    {
        return $this->translate($field);
    }

    /**
     * Delete all translations for this model.
     *
     * @return void
     */
    public function deleteTranslations(): void
    {
        $this->translations()->delete();
    }

    /**
     * Delete translation for a specific field and locale.
     *
     * @param  string  $field  Field name
     * @param  string|null  $locale  Locale code (null = all locales)
     * @return void
     */
    public function deleteTranslation(string $field, ?string $locale = null): void
    {
        $query = $this->translations()->where('field', $field);

        if ($locale) {
            $query->whereHas('language', function ($q) use ($locale) {
                $q->where('code', $locale);
            });
        }

        $query->delete();
    }

    /**
     * Get translation with fallback to original value.
     *
     * @param  string  $field  Field name
     * @param  mixed  $fallback  Fallback value (usually from model attribute)
     * @param  string|null  $locale  Locale code (null = current locale)
     * @return mixed
     */
    public function translateOr(string $field, $fallback, ?string $locale = null)
    {
        $translation = $this->translate($field, $locale);

        return $translation ?? $fallback;
    }
}

