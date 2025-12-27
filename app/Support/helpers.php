<?php

use App\Models\Language;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

if (! function_exists('getCurrentLanguage')) {
    /**
     * Obtener el idioma actual de la aplicación.
     */
    function getCurrentLanguage(): ?Language
    {
        $code = App::getLocale();

        try {
            return Language::where('code', $code)
                ->where('is_active', true)
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (! function_exists('getCurrentLanguageCode')) {
    /**
     * Obtener el código del idioma actual.
     */
    function getCurrentLanguageCode(): string
    {
        return App::getLocale();
    }
}

if (! function_exists('setLanguage')) {
    /**
     * Establecer el idioma de la aplicación.
     *
     * @param  string  $code  Código del idioma (ej: 'es', 'en')
     * @param  bool  $persist  Si se debe persistir en sesión y cookie
     */
    function setLanguage(string $code, bool $persist = true): bool
    {
        try {
            // Verificar que el idioma existe y está activo
            $language = Language::where('code', $code)
                ->where('is_active', true)
                ->first();

            if (! $language) {
                return false;
            }

            // Establecer en la aplicación
            App::setLocale($code);

            // Persistir en sesión y cookie si se solicita
            if ($persist) {
                Session::put('locale', $code);

                // La cookie se establecerá en la respuesta HTTP
                // usando Cookie::queue() o en el componente Livewire
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (! function_exists('getAvailableLanguages')) {
    /**
     * Obtener lista de idiomas disponibles.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getAvailableLanguages()
    {
        try {
            return Language::where('is_active', true)
                ->orderBy('is_default', 'desc')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            return collect([]);
        }
    }
}

if (! function_exists('isLanguageAvailable')) {
    /**
     * Verificar si un idioma está disponible.
     *
     * @param  string  $code  Código del idioma
     */
    function isLanguageAvailable(string $code): bool
    {
        try {
            return Language::where('code', $code)
                ->where('is_active', true)
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (! function_exists('getDefaultLanguage')) {
    /**
     * Obtener el idioma por defecto.
     */
    function getDefaultLanguage(): ?Language
    {
        try {
            return Language::where('is_default', true)
                ->where('is_active', true)
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (! function_exists('trans_model')) {
    /**
     * Obtener traducción de un campo de un modelo desde la tabla translations.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model  Modelo con traducciones
     * @param  string  $field  Campo a traducir
     * @param  string|null  $locale  Código del idioma (null = idioma actual)
     */
    function trans_model($model, string $field, ?string $locale = null): ?string
    {
        if (! $model || ! $model->exists) {
            return null;
        }

        $locale = $locale ?? getCurrentLanguageCode();

        try {
            $translation = \App\Models\Translation::where('translatable_type', get_class($model))
                ->where('translatable_id', $model->id)
                ->where('field', $field)
                ->whereHas('language', function ($query) use ($locale) {
                    $query->where('code', $locale)->where('is_active', true);
                })
                ->first();

            return $translation?->value;
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (! function_exists('trans_route')) {
    /**
     * Generar URL de ruta manteniendo el locale actual.
     *
     * @param  string  $name  Nombre de la ruta
     * @param  array  $parameters  Parámetros de la ruta
     * @param  bool  $absolute  URL absoluta
     */
    function trans_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        // Por ahora, simplemente usar route() normal
        // En el futuro se podría añadir prefijo de locale si se implementa
        return route($name, $parameters, $absolute);
    }
}

if (! function_exists('format_number')) {
    /**
     * Formatear número según el locale actual.
     *
     * @param  int|float  $number  Número a formatear
     * @param  int  $decimals  Número de decimales
     */
    function format_number(int|float $number, int $decimals = 0): string
    {
        $locale = getCurrentLanguageCode();

        // Si NumberFormatter no está disponible, usar number_format como fallback
        if (! class_exists(\NumberFormatter::class)) {
            return number_format($number, $decimals,
                $locale === 'es' ? ',' : '.',
                $locale === 'es' ? '.' : ','
            );
        }

        try {
            // Configurar formato según el locale
            $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
            $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
            $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

            return $formatter->format($number);
        } catch (\Exception $e) {
            // Fallback a number_format si hay algún error
            return number_format($number, $decimals,
                $locale === 'es' ? ',' : '.',
                $locale === 'es' ? '.' : ','
            );
        }
    }
}

if (! function_exists('format_date')) {
    /**
     * Formatear fecha según el locale actual.
     *
     * @param  \Carbon\Carbon|\DateTime|string  $date  Fecha a formatear
     * @param  string  $format  Formato de fecha (por defecto según locale)
     */
    function format_date(\Carbon\Carbon|\DateTime|string $date, ?string $format = null): string
    {
        if (! $date instanceof \Carbon\Carbon) {
            $date = \Carbon\Carbon::parse($date);
        }

        $locale = getCurrentLanguageCode();

        // Formatos por defecto según locale
        $defaultFormats = [
            'es' => 'd/m/Y',
            'en' => 'm/d/Y',
        ];

        $format = $format ?? $defaultFormats[$locale] ?? 'Y-m-d';

        return $date->translatedFormat($format);
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Formatear fecha y hora según el locale actual.
     *
     * @param  \Carbon\Carbon|\DateTime|string  $date  Fecha a formatear
     * @param  string  $dateFormat  Formato de fecha (por defecto según locale)
     * @param  string  $timeFormat  Formato de hora (por defecto 'H:i')
     */
    function format_datetime(\Carbon\Carbon|\DateTime|string $date, ?string $dateFormat = null, string $timeFormat = 'H:i'): string
    {
        if (! $date instanceof \Carbon\Carbon) {
            $date = \Carbon\Carbon::parse($date);
        }

        $locale = getCurrentLanguageCode();

        // Formatos por defecto según locale
        $defaultFormats = [
            'es' => 'd/m/Y',
            'en' => 'm/d/Y',
        ];

        $dateFormat = $dateFormat ?? $defaultFormats[$locale] ?? 'Y-m-d';

        return $date->translatedFormat("{$dateFormat} {$timeFormat}");
    }
}
