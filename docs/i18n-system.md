# Sistema de Internacionalización (i18n)

Este documento describe el sistema completo de internacionalización implementado en la aplicación "Erasmus+ Centro (Murcia)".

---

## Tabla de Contenidos

1. [Arquitectura del Sistema](#arquitectura-del-sistema)
2. [Componentes Principales](#componentes-principales)
3. [Uso del Sistema](#uso-del-sistema)
4. [Añadir Nuevos Idiomas](#añadir-nuevos-idiomas)
5. [Traducciones Estáticas](#traducciones-estáticas)
6. [Traducciones Dinámicas](#traducciones-dinámicas)
7. [Ejemplos de Uso](#ejemplos-de-uso)
8. [Mejores Prácticas](#mejores-prácticas)

---

## Arquitectura del Sistema

El sistema de internacionalización está compuesto por los siguientes componentes:

### 1. Modelos de Base de Datos

- **`Language`**: Almacena los idiomas disponibles en el sistema
- **`Translation`**: Almacena traducciones dinámicas de modelos (sistema polimórfico)

### 2. Middleware

- **`SetLocale`**: Detecta y establece el idioma de la aplicación en cada petición

### 3. Componentes Livewire

- **`Language\Switcher`**: Componente para cambiar idioma desde el frontend

### 4. Helpers y Traits

- **Helpers globales**: Funciones helper para gestionar idiomas y traducciones
- **Trait `Translatable`**: Facilita traducciones dinámicas en modelos

### 5. Archivos de Traducción

- Archivos PHP en `lang/{locale}/` para traducciones estáticas

---

## Componentes Principales

### Middleware SetLocale

**Ubicación**: `app/Http/Middleware/SetLocale.php`

**Funcionalidad**:
- Detecta el idioma desde múltiples fuentes (prioridad):
  1. Sesión (`session('locale')`)
  2. Cookie (`cookie('locale')`)
  3. Header HTTP `Accept-Language`
  4. Idioma por defecto de la aplicación

**Registro**: Registrado automáticamente en `bootstrap/app.php` como middleware web.

### Componente Language Switcher

**Ubicación**: `app/Livewire/Language/Switcher.php`

**Variantes disponibles**:
- `dropdown`: Dropdown con lista de idiomas (recomendado para desktop)
- `buttons`: Botones pequeños con código de idioma (ES, EN)
- `select`: Select nativo (recomendado para móviles)

**Uso en vistas**:
```blade
<livewire:language.switcher variant="dropdown" size="md" />
```

### Helpers Globales

**Ubicación**: `app/Support/helpers.php`

**Funciones disponibles**:

- `getCurrentLanguage()`: Obtiene el modelo `Language` del idioma actual
- `getCurrentLanguageCode()`: Obtiene el código del idioma actual (ej: 'es', 'en')
- `setLanguage($code, $persist)`: Establece el idioma y opcionalmente lo persiste
- `getAvailableLanguages()`: Lista todos los idiomas activos
- `isLanguageAvailable($code)`: Verifica si un idioma está disponible
- `getDefaultLanguage()`: Obtiene el idioma por defecto
- `trans_model($model, $field, $locale)`: Obtiene traducción dinámica de un modelo
- `trans_route($name, $params)`: Genera URL de ruta manteniendo locale

### Trait Translatable

**Ubicación**: `app/Models/Concerns/Translatable.php`

**Métodos disponibles**:

- `translate($field, $locale)`: Obtiene traducción de un campo
- `setTranslation($field, $locale, $value)`: Establece traducción
- `hasTranslation($field, $locale)`: Verifica si existe traducción
- `translations($locale)`: Obtiene todas las traducciones de un locale
- `translateOr($field, $fallback, $locale)`: Traducción con fallback
- `deleteTranslation($field, $locale)`: Elimina traducción específica
- `deleteTranslations()`: Elimina todas las traducciones

---

## Uso del Sistema

### Cambiar Idioma desde el Frontend

El componente `Language\Switcher` está integrado automáticamente en:
- Navegación pública (`components/nav/public-nav.blade.php`)
- Header de administración (`components/layouts/app/header.blade.php`)

El cambio de idioma se persiste automáticamente en sesión y cookie.

### Traducciones Estáticas en Vistas

Usar la función `__()` de Laravel:

```blade
{{ __('common.actions.view_more') }}
{{ __('common.messages.loading') }}
{{ __('Programas') }}
```

### Traducciones Dinámicas en Modelos

1. **Aplicar el trait al modelo**:
```php
use App\Models\Concerns\Translatable;

class Program extends Model
{
    use Translatable;
    
    // ...
}
```

2. **Usar en código**:
```php
$program->translate('title', 'en');
$program->setTranslation('title', 'en', 'Erasmus+ Program');
```

3. **Usar en vistas**:
```blade
{{ trans_model($program, 'title') }}
{{ $program->translate('description') }}
```

---

## Añadir Nuevos Idiomas

### Paso 1: Crear registro en base de datos

Ejecutar seeder o crear manualmente:

```php
Language::create([
    'code' => 'fr',
    'name' => 'Français',
    'is_default' => false,
    'is_active' => true,
]);
```

### Paso 2: Crear archivos de traducción

Crear directorio y archivos en `lang/fr/`:

```bash
mkdir -p lang/fr
cp lang/es/common.php lang/fr/common.php
# Editar lang/fr/common.php con traducciones al francés
```

### Paso 3: Actualizar seeder (opcional)

Añadir el nuevo idioma al `LanguagesSeeder`:

```php
$languages = [
    // ... idiomas existentes
    [
        'code' => 'fr',
        'name' => 'Français',
        'is_default' => false,
        'is_active' => true,
    ],
];
```

### Paso 4: Probar

El nuevo idioma aparecerá automáticamente en el selector de idioma.

---

## Traducciones Estáticas

### Estructura de Archivos

```
lang/
├── es/
│   ├── auth.php
│   ├── common.php
│   ├── pagination.php
│   ├── passwords.php
│   └── validation.php
└── en/
    ├── auth.php
    ├── common.php
    ├── pagination.php
    ├── passwords.php
    └── validation.php
```

### Archivo common.php

Contiene traducciones comunes organizadas por categorías:

- `nav`: Navegación
- `actions`: Botones y acciones
- `messages`: Mensajes comunes
- `status`: Estados
- `forms`: Formularios
- `language`: Idioma
- `breadcrumbs`: Migas de pan
- `pagination`: Paginación
- `filters`: Filtros
- `time`: Tiempo
- `footer`: Footer

### Uso en Vistas

```blade
{{ __('common.actions.view_more') }}
{{ __('common.messages.loading') }}
{{ __('common.nav.programs') }}
```

---

## Traducciones Dinámicas

### Sistema Polimórfico

Las traducciones dinámicas se almacenan en la tabla `translations` con relación polimórfica:

```php
Translation::create([
    'translatable_type' => 'App\Models\Program',
    'translatable_id' => $program->id,
    'language_id' => $language->id,
    'field' => 'title',
    'value' => 'Programa Erasmus+',
]);
```

### Aplicar Trait a Modelo

```php
use App\Models\Concerns\Translatable;

class Program extends Model
{
    use Translatable;
}
```

### Establecer Traducciones

```php
$program->setTranslation('title', 'es', 'Programa Erasmus+');
$program->setTranslation('title', 'en', 'Erasmus+ Program');
$program->setTranslation('description', 'es', 'Descripción en español');
```

### Obtener Traducciones

```php
// Usar idioma actual
$title = $program->translate('title');

// Especificar idioma
$titleEn = $program->translate('title', 'en');

// Con fallback
$title = $program->translateOr('title', $program->title, 'en');
```

### En Vistas Blade

```blade
{{-- Helper global --}}
{{ trans_model($program, 'title') }}

{{-- Método del modelo --}}
{{ $program->translate('description') }}

{{-- Con fallback --}}
{{ $program->translateOr('title', $program->title) }}
```

---

## Ejemplos de Uso

### Ejemplo 1: Componente con Traducciones Estáticas

```blade
<div>
    <h2>{{ __('common.nav.programs') }}</h2>
    <button>{{ __('common.actions.view_more') }}</button>
    @if($programs->isEmpty())
        <p>{{ __('common.messages.no_data') }}</p>
    @endif
</div>
```

### Ejemplo 2: Modelo con Traducciones Dinámicas

```php
// En el modelo
class Program extends Model
{
    use Translatable;
    
    public function getTitleAttribute($value)
    {
        return $this->translateOr('title', $value);
    }
}

// En el controlador
$program = Program::find(1);
$title = $program->title; // Usa traducción si existe, sino el valor original

// En la vista
{{ $program->title }}
{{ trans_model($program, 'description') }}
```

### Ejemplo 3: Cambiar Idioma Programáticamente

```php
// Establecer idioma
setLanguage('en', persist: true);

// Obtener idioma actual
$currentLang = getCurrentLanguage();
$code = getCurrentLanguageCode();

// Listar idiomas disponibles
$languages = getAvailableLanguages();
```

---

## Mejores Prácticas

### 1. Organización de Traducciones

- Usar archivos `common.php` para textos reutilizables
- Agrupar traducciones por contexto (nav, actions, messages, etc.)
- Mantener consistencia en las claves de traducción

### 2. Traducciones Dinámicas

- Usar el trait `Translatable` solo en modelos que realmente necesiten traducciones
- Siempre proporcionar un fallback cuando se obtienen traducciones
- Validar que el idioma existe antes de establecer traducciones

### 3. Performance

- El middleware `SetLocale` se ejecuta en cada petición, pero es eficiente
- Las traducciones dinámicas se pueden cachear si es necesario
- Usar eager loading para evitar consultas N+1 al obtener traducciones

### 4. Testing

- Siempre incluir tests para componentes de idioma
- Verificar que las traducciones se muestran correctamente
- Probar cambio de idioma y persistencia

### 5. Accesibilidad

- El componente Language Switcher incluye atributos ARIA apropiados
- Los textos traducidos deben mantener el contexto semántico
- Verificar que los cambios de idioma no rompen el layout

---

## Troubleshooting

### El idioma no cambia

1. Verificar que el idioma existe y está activo en la base de datos
2. Verificar que el middleware `SetLocale` está registrado
3. Limpiar caché: `php artisan cache:clear`
4. Verificar sesión y cookies del navegador

### Las traducciones no se muestran

1. Verificar que el archivo de traducción existe en `lang/{locale}/`
2. Verificar la sintaxis del archivo PHP
3. Limpiar caché de vistas: `php artisan view:clear`
4. Verificar que se está usando la función `__()` correctamente

### Traducciones dinámicas no funcionan

1. Verificar que el modelo usa el trait `Translatable`
2. Verificar que existe la relación `translations()` en el modelo
3. Verificar que los datos existen en la tabla `translations`
4. Verificar que el `language_id` corresponde a un idioma activo

---

## Referencias

- [Documentación Laravel - Localization](https://laravel.com/docs/localization)
- [Laravel Livewire - Components](https://livewire.laravel.com/docs/components)
- [Planificación del Paso 3.4.8](pasos/paso-3.4.8-plan.md)

---

## Implementación Completa (Paso 3.4.8)

### Archivos Creados/Modificados

#### Middleware y Helpers
- `app/Http/Middleware/SetLocale.php` - Middleware para detectar y establecer locale
- `app/Support/helpers.php` - Funciones helper globales para gestión de idiomas
- `app/Models/Concerns/Translatable.php` - Trait para traducciones dinámicas en modelos

#### Componentes Livewire
- `app/Livewire/Language/Switcher.php` - Componente para cambiar idioma
- `resources/views/livewire/language/switcher.blade.php` - Vista del componente switcher

#### Archivos de Traducción
- `lang/es/common.php` - ~500+ claves de traducción en español
- `lang/en/common.php` - ~500+ claves de traducción en inglés

#### Vistas Actualizadas (18 archivos)
**Vistas de detalle públicas:**
- `resources/views/livewire/public/news/show.blade.php`
- `resources/views/livewire/public/documents/show.blade.php`
- `resources/views/livewire/public/events/show.blade.php`

**Vistas de listado públicas:**
- `resources/views/livewire/public/events/index.blade.php`
- `resources/views/livewire/public/events/calendar.blade.php`

**Componentes de contenido:**
- `resources/views/components/content/event-card.blade.php`
- `resources/views/components/content/document-card.blade.php`
- `resources/views/components/content/news-card.blade.php`
- `resources/views/components/content/call-phase-timeline.blade.php`
- `resources/views/components/content/call-card.blade.php`
- `resources/views/components/content/program-card.blade.php`
- `resources/views/components/content/resolution-card.blade.php`

**Newsletter:**
- `resources/views/livewire/public/newsletter/subscribe.blade.php`
- `resources/views/livewire/public/newsletter/unsubscribe.blade.php`
- `resources/views/livewire/public/newsletter/verify.blade.php`
- `resources/views/emails/newsletter/verification.blade.php`

**Componentes UI:**
- `resources/views/components/nav/public-nav.blade.php`
- `resources/views/components/footer.blade.php`
- `resources/views/components/ui/breadcrumbs.blade.php`
- `resources/views/components/ui/search-input.blade.php`
- `resources/views/components/ui/empty-state.blade.php`

**Otras vistas:**
- `resources/views/livewire/public/programs/index.blade.php`
- `resources/views/livewire/public/calls/index.blade.php`
- `resources/views/livewire/public/news/index.blade.php`
- `resources/views/livewire/public/documents/index.blade.php`
- `resources/views/livewire/public/home.blade.php`
- `resources/views/components/layouts/app/header.blade.php`

### Estadísticas de Internacionalización

- **Total de archivos actualizados**: 18 archivos
- **Total de strings traducidos**: ~200 strings literales convertidos a claves de traducción
- **Claves de traducción creadas**: ~500+ claves organizadas en categorías
- **Idiomas soportados**: Español (es) y Inglés (en)
- **Componentes internacionalizados**: 100% de vistas públicas

### Categorías de Traducción en common.php

- `nav` - Navegación (home, programs, calls, news, documents, calendar, etc.)
- `actions` - Acciones y botones (view_more, read_more, download, subscribe, etc.)
- `messages` - Mensajes comunes (loading, no_data, success, error, etc.)
- `status` - Estados (active, inactive, open, closed, etc.)
- `forms` - Formularios (search_placeholder, labels, etc.)
- `language` - Idioma (change, select, current, available)
- `breadcrumbs` - Migas de pan
- `pagination` - Paginación
- `filters` - Filtros (all, clear, active, etc.)
- `time` - Tiempo (this_month, this_year, etc.)
- `footer` - Footer
- `home` - Página de inicio
- `programs` - Programas
- `calls` - Convocatorias
- `news` - Noticias
- `documents` - Documentos
- `events` - Eventos
- `call_phases` - Fases de convocatorias
- `call_status` - Estados de convocatorias
- `call_types` - Tipos de convocatorias
- `call_modalities` - Modalidades
- `document_types` - Tipos de documentos
- `program_status` - Estados de programas
- `newsletter` - Newsletter (suscription, verification, unsubscribe, etc.)
- `resolution` - Resoluciones

### Tests Implementados

- `tests/Feature/Middleware/SetLocaleTest.php` - Tests del middleware
- `tests/Feature/Livewire/Language/SwitcherTest.php` - Tests del componente switcher
- `tests/Feature/Helpers/TranslationHelpersTest.php` - Tests de helpers

---

**Última actualización**: Diciembre 2025

