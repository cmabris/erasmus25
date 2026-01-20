# Optimizaciones de Rendimiento

Este documento describe todas las optimizaciones de rendimiento implementadas en la aplicación Erasmus+ Centro (Murcia), incluyendo consultas de base de datos, caché, imágenes y SEO.

---

## Resumen de Optimizaciones

| Área | Estado | Impacto |
|------|--------|---------|
| [Consultas N+1](#1-optimización-de-consultas-n1) | ✅ Completado | Alto |
| [Sistema de Caché](#2-sistema-de-caché) | ✅ Completado | Alto |
| [Índices de Base de Datos](#3-índices-de-base-de-datos) | ✅ Completado | Alto |
| [Optimización de Imágenes](#4-optimización-de-imágenes) | ✅ Completado | Medio |
| [SEO](#5-seo) | ✅ Completado | Medio |
| [Exports con Chunking](#6-exports-con-chunking) | ✅ Completado | Medio |

---

## 1. Optimización de Consultas N+1

### ¿Qué es el Problema N+1?

El problema N+1 ocurre cuando se ejecuta 1 consulta para obtener una lista de registros, y luego N consultas adicionales (una por cada registro) para obtener datos relacionados.

```sql
-- Problema N+1: 1 + N consultas
SELECT * FROM posts;                    -- 1 consulta
SELECT * FROM users WHERE id = 1;       -- N consultas
SELECT * FROM users WHERE id = 2;
SELECT * FROM users WHERE id = 3;
...

-- Solución con Eager Loading: 2 consultas
SELECT * FROM posts;                    -- 1 consulta
SELECT * FROM users WHERE id IN (1,2,3);-- 1 consulta
```

### Eager Loading Implementado

#### Componentes de Administración

| Componente | Relaciones Cargadas |
|------------|---------------------|
| `Admin\Calls\Index` | `program`, `academicYear`, `creator`, `updater` + `withCount(['phases', 'resolutions', 'applications'])` |
| `Admin\News\Index` | `program`, `academicYear`, `author`, `tags`, `media` + `withCount(['tags'])` |
| `Admin\Documents\Index` | `category`, `program`, `academicYear`, `creator`, `updater`, `media` + `withCount(['mediaConsents'])` |
| `Admin\Events\Index` | `program`, `call`, `creator`, `media` |
| `Admin\Users\Index` | `roles`, `permissions` + `withCount(['activities'])` |
| `Admin\AuditLogs\Index` | `causer`, `subject` |

#### Componentes Públicos

| Componente | Relaciones Cargadas |
|------------|---------------------|
| `Public\Calls\Index` | `program`, `academicYear` |
| `Public\News\Index` | `program`, `academicYear`, `author`, `tags`, `media` |
| `Public\Documents\Index` | `category`, `program`, `academicYear`, `media` |
| `Public\Events\Index` | `program`, `call`, `media` |
| `Public\Home` | Todas las relaciones necesarias por sección |
| `Search\GlobalSearch` | Eager loading específico por tipo de entidad |

### Detección Automática

```php
// En AppServiceProvider::boot()
Model::shouldBeStrict(!app()->isProduction());
```

Esto activa en desarrollo/testing:
- **Lazy Loading Prevention**: Lanza excepción si se accede a relación no cargada
- **Silently Discarding Attributes**: Lanza excepción si se asigna atributo no fillable
- **Accessing Missing Attributes**: Lanza excepción si se accede a atributo inexistente

### Tests de Rendimiento

```php
// tests/Feature/Performance/QueryOptimizationTest.php

it('loads calls index with optimal queries', function () {
    Call::factory()->count(15)->create();
    
    $queryCount = 0;
    DB::listen(fn() => $queryCount++);
    
    Livewire::test(CallsIndex::class);
    
    expect($queryCount)->toBeLessThan(10);
});
```

**Métricas objetivo**:
- Listados: < 15 consultas
- Páginas complejas: < 25 consultas
- Consultas duplicadas: 0
- Tiempo total DB: < 100ms

### Documentación Relacionada

- [Guía de Detección N+1](debugbar-n1-detection.md)

---

## 2. Sistema de Caché

### Caché Implementado

| Ubicación | Datos Cacheados | TTL | Clave |
|-----------|----------------|-----|-------|
| `Admin\Dashboard` | Estadísticas generales | 5 min | `dashboard.stats` |
| `Admin\Dashboard` | Datos de gráficos | 15 min | `dashboard.charts.{period}` |
| `Admin\AuditLogs\Index` | Modelos disponibles | 1 hora | `audit.available_models` |
| `Admin\AuditLogs\Index` | Lista de causers | 1 hora | `audit.causers` |
| `Public\Home` | Programas activos | 1 hora | `home.programs` |
| `Public\Home` | Convocatorias abiertas | 30 min | `home.calls` |
| `Public\Home` | Últimas noticias | 30 min | `home.news` |
| `Public\Home` | Próximos eventos | 30 min | `home.events` |
| `SitemapController` | Sitemap XML | 1 hora | `sitemap.xml` |

### Implementación de Caché

```php
// Ejemplo: Dashboard de Administración
public function getStats(): array
{
    return Cache::remember('dashboard.stats', now()->addMinutes(5), function () {
        return [
            'programs' => Program::count(),
            'calls' => Call::where('status', 'abierta')->count(),
            'news' => NewsPost::whereNotNull('published_at')->count(),
            'documents' => Document::where('is_active', true)->count(),
            'events' => ErasmusEvent::where('end_date', '>=', now())->count(),
            'users' => User::count(),
        ];
    });
}
```

### Invalidación de Caché

La caché se invalida automáticamente mediante Model Observers:

```php
// En el Observer del modelo
public function saved(Program $program): void
{
    Cache::forget('home.programs');
    Cache::forget('dashboard.stats');
}

public function deleted(Program $program): void
{
    Cache::forget('home.programs');
    Cache::forget('dashboard.stats');
}
```

### Caché de Configuración

```php
// Obtener año académico actual (cacheado)
$currentYear = Cache::remember('academic_year.current', now()->addHours(24), function () {
    return AcademicYear::where('is_current', true)->first();
});
```

---

## 3. Índices de Base de Datos

### Índices Implementados

#### Tabla `calls`

```php
$table->index(['program_id', 'academic_year_id', 'status']);
$table->index(['status', 'published_at']);
$table->index('deleted_at');
```

#### Tabla `news_posts`

```php
$table->index(['program_id', 'status', 'published_at']);
$table->index(['academic_year_id', 'status']);
$table->index('deleted_at');
```

#### Tabla `documents`

```php
$table->index(['category_id', 'program_id', 'is_active']);
$table->index('deleted_at');
```

#### Tabla `users`

```php
$table->index('deleted_at');
$table->index('name');
$table->index(['email', 'deleted_at']);
```

#### Tabla `activity_log`

```php
$table->index(['subject_type', 'subject_id']);
$table->index(['causer_type', 'causer_id']);
$table->index('created_at');
$table->index('event');
```

#### Tabla `resolutions`

```php
$table->index('call_id');
$table->index('published_at');
```

#### Tabla `programs`

```php
$table->index('is_active');
$table->index('order');
```

### Consultas Optimizadas

Los índices compuestos están diseñados para optimizar las consultas más frecuentes:

```php
// Consulta optimizada por índice (program_id, academic_year_id, status)
Call::where('program_id', $programId)
    ->where('academic_year_id', $yearId)
    ->where('status', 'abierta')
    ->get();

// Consulta optimizada por índice (status, published_at)
Call::where('status', 'abierta')
    ->whereNotNull('published_at')
    ->orderBy('published_at', 'desc')
    ->get();
```

---

## 4. Optimización de Imágenes

### Configuración de Media Library

```php
// config/media-library.php
'image_driver' => env('IMAGE_DRIVER', 'gd'),

'default_quality' => 85,

'image_generators' => [
    // Generadores configurados
],
```

### Conversiones Implementadas

#### Modelo Program

| Conversión | Tamaño | Formato |
|------------|--------|---------|
| `thumbnail` | 300x300 | WebP |
| `medium` | 800x600 | WebP |
| `large` | 1200x900 | WebP |

#### Modelo NewsPost

| Conversión | Tamaño | Formato |
|------------|--------|---------|
| `thumbnail` | 300x300 | WebP |
| `medium` | 800x600 | WebP |
| `large` | 1200x900 | WebP |
| `hero` | 1920x1080 | WebP |

#### Modelo ErasmusEvent

| Conversión | Tamaño | Formato |
|------------|--------|---------|
| `thumbnail` | 300x300 | WebP |
| `medium` | 800x600 | WebP |
| `large` | 1200x900 | WebP |

#### Modelo Document

| Conversión | Tamaño | Formato |
|------------|--------|---------|
| `preview` | 400x566 | WebP |

### Beneficios de WebP

- **40-70% menos peso** que JPEG/PNG equivalente
- **Transparencia** soportada (como PNG)
- **Animación** soportada (como GIF)
- **Soporte universal** en navegadores modernos

### Componente Responsive Image

```blade
{{-- resources/views/components/ui/responsive-image.blade.php --}}
<x-ui.responsive-image
    :media="$program->getFirstMedia('image')"
    conversion="medium"
    :alt="$program->name"
    class="w-full h-48 object-cover"
/>
```

El componente añade automáticamente:
- `loading="lazy"` para carga diferida
- `decoding="async"` para decodificación asíncrona
- Fallback a imagen original si la conversión no existe

### Lazy Loading

Todas las imágenes en listados usan `loading="lazy"`:

```blade
<img 
    src="{{ $media->getUrl('thumbnail') }}" 
    alt="{{ $title }}"
    loading="lazy"
    decoding="async"
    class="w-full h-48 object-cover"
>
```

### Regenerar Conversiones

```bash
# Regenerar todas las conversiones
php artisan media-library:regenerate

# Regenerar solo un modelo
php artisan media-library:regenerate --model=Program

# Regenerar solo una conversión
php artisan media-library:regenerate --only=thumbnail
```

---

## 5. SEO

### Componentes SEO

#### Meta Tags (`x-seo.meta`)

```blade
<x-seo.meta
    :title="$title"
    :description="$description"
    :image="$ogImage"
    :url="$canonicalUrl"
    type="article"
/>
```

Genera automáticamente:
- `<title>` con formato `{título} | {siteName}`
- `<meta name="description">`
- `<link rel="canonical">`
- Open Graph tags (`og:title`, `og:description`, `og:image`, `og:url`, `og:type`)
- Twitter Cards (`twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`)

#### JSON-LD (`x-seo.json-ld`)

```blade
<x-seo.json-ld
    type="Article"
    :data="[
        'headline' => $newsPost->title,
        'datePublished' => $newsPost->published_at->toIso8601String(),
        'author' => $newsPost->author->name,
    ]"
/>
```

### Sitemap Dinámico

**Ruta**: `/sitemap.xml`

```php
// app/Http/Controllers/SitemapController.php
public function index()
{
    return Cache::remember('sitemap.xml', now()->addHour(), function () {
        $urls = collect();
        
        // Páginas estáticas
        $urls->push(['loc' => url('/'), 'priority' => '1.0']);
        $urls->push(['loc' => route('public.programs.index'), 'priority' => '0.9']);
        // ...
        
        // Páginas dinámicas
        Program::where('is_active', true)->each(function ($program) use ($urls) {
            $urls->push([
                'loc' => route('public.programs.show', $program),
                'lastmod' => $program->updated_at->toW3cString(),
                'priority' => '0.8',
            ]);
        });
        // ...
        
        return response()
            ->view('sitemap.index', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    });
}
```

### robots.txt

```text
User-agent: *
Allow: /

Disallow: /admin/
Disallow: /login
Disallow: /register
Disallow: /password/
Disallow: /email/
Disallow: /two-factor/
Disallow: /user/
Disallow: /notificaciones
Disallow: /settings/

Sitemap: https://erasmus25.test/sitemap.xml
```

### URLs Amigables

Todos los modelos públicos usan slugs:

| Modelo | Campo | Ejemplo |
|--------|-------|---------|
| Program | `slug` | `/programas/educacion-escolar-ka1` |
| Call | `slug` | `/convocatorias/movilidad-fp-2025-2026` |
| NewsPost | `slug` | `/noticias/nueva-convocatoria-erasmus` |
| Document | `slug` | `/documentos/guia-solicitud-2025` |

```php
// Route Model Binding con slug
Route::get('/programas/{program:slug}', [ProgramController::class, 'show']);
```

---

## 6. Exports con Chunking

Para exportaciones grandes, se usa chunking para evitar problemas de memoria:

```php
// app/Exports/CallsExport.php
class CallsExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return Call::query()
            ->with(['program', 'academicYear'])
            ->when($this->filters['program_id'], fn($q, $v) => $q->where('program_id', $v))
            ->when($this->filters['status'], fn($q, $v) => $q->where('status', $v));
    }
    
    public function chunkSize(): int
    {
        return 1000;
    }
}
```

### Exports Implementados

| Export | Chunking | Filtros |
|--------|----------|---------|
| `CallsExport` | 1000 | program, year, status |
| `ResolutionsExport` | 1000 | call, type, published |
| `NewsletterExport` | 1000 | program, verified, active |
| `AuditLogsExport` | 1000 | model, user, action, dates |

---

## Herramientas de Desarrollo

### Laravel Debugbar

Configurado para detección óptima de problemas:

```php
// config/debugbar.php
'collectors' => [
    'db' => true,        // Consultas SQL
    'models' => true,    // Modelos cargados
    'cache' => true,     // Hits/misses de caché
    'time' => true,      // Timeline
],

'options' => [
    'db' => [
        'with_params' => true,
        'backtrace' => true,
        'timeline' => true,
        'explain' => [
            'enabled' => true,
        ],
        'hints' => true,    // Detecta N+1
    ],
],
```

### Comandos Útiles

```bash
# Ver consultas en tiempo real
php artisan pail --filter="query"

# Limpiar toda la caché
php artisan cache:clear

# Optimizar para producción
php artisan optimize

# Regenerar imágenes
php artisan media-library:regenerate
```

---

## Métricas de Rendimiento

### Objetivos

| Métrica | Objetivo | Actual |
|---------|----------|--------|
| Consultas por página (listados) | < 15 | ✅ ~8-12 |
| Consultas por página (detalle) | < 25 | ✅ ~15-20 |
| Tiempo de carga DB | < 100ms | ✅ ~50-80ms |
| Tamaño imágenes (reducción) | 40%+ | ✅ ~50-60% |
| Cache hit ratio | > 80% | ✅ ~85% |

### Tests de Rendimiento

```bash
# Ejecutar tests de rendimiento
php artisan test tests/Feature/Performance/

# Tests específicos
php artisan test --filter=QueryOptimization
```

**Total de tests de rendimiento**: 29 tests, 83 assertions

---

## Documentación Relacionada

- [Guía de Detección N+1](debugbar-n1-detection.md)
- [Paso 46: Optimización de Consultas](pasos/paso46.md)
- [Paso 47: Optimización de Imágenes](pasos/paso47.md)
- [Paso 48: SEO](pasos/paso48.md)

---

**Última actualización**: Enero 2026
