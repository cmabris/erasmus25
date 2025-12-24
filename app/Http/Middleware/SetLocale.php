<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prioridad 1: Idioma desde sesión
        $locale = Session::get('locale');

        // Prioridad 2: Idioma desde cookie
        if (! $locale) {
            $locale = $request->cookie('locale');
        }

        // Prioridad 3: Idioma desde header Accept-Language
        if (! $locale) {
            $locale = $this->getLocaleFromHeader($request);
        }

        // Prioridad 4: Idioma por defecto desde base de datos o configuración
        if (! $locale) {
            $locale = $this->getDefaultLocale();
        }

        // Validar que el idioma existe y está activo
        $locale = $this->validateLocale($locale);

        // Establecer el locale en la aplicación
        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Obtener idioma desde el header Accept-Language.
     */
    protected function getLocaleFromHeader(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (! $acceptLanguage) {
            return null;
        }

        // Parsear Accept-Language header (ej: "es-ES,es;q=0.9,en;q=0.8")
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', trim($lang));
            $code = strtolower(trim($parts[0]));
            $quality = isset($parts[1]) ? (float) str_replace('q=', '', $parts[1]) : 1.0;
            $languages[$code] = $quality;
        }

        // Ordenar por calidad (mayor a menor)
        arsort($languages);

        // Buscar el primer idioma disponible
        foreach (array_keys($languages) as $code) {
            // Extraer código de 2 letras (es-ES -> es)
            $code = substr($code, 0, 2);
            if ($this->isLocaleAvailable($code)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Obtener idioma por defecto desde base de datos o configuración.
     */
    protected function getDefaultLocale(): string
    {
        try {
            // Intentar obtener desde base de datos
            $defaultLanguage = \App\Models\Language::where('is_default', true)
                ->where('is_active', true)
                ->first();

            if ($defaultLanguage) {
                return $defaultLanguage->code;
            }
        } catch (\Exception $e) {
            // Si hay error (ej: migraciones no ejecutadas), usar configuración
        }

        // Fallback a configuración
        return config('app.locale', 'es');
    }

    /**
     * Validar que el locale existe y está activo.
     */
    protected function validateLocale(?string $locale): string
    {
        if (! $locale) {
            return $this->getDefaultLocale();
        }

        try {
            // Verificar que el idioma existe y está activo
            $language = \App\Models\Language::where('code', $locale)
                ->where('is_active', true)
                ->first();

            if ($language) {
                return $locale;
            }
        } catch (\Exception $e) {
            // Si hay error, usar configuración
        }

        // Si no es válido, usar idioma por defecto
        return $this->getDefaultLocale();
    }

    /**
     * Verificar si un locale está disponible.
     */
    protected function isLocaleAvailable(string $code): bool
    {
        try {
            return \App\Models\Language::where('code', $code)
                ->where('is_active', true)
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}

