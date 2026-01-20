# Plan de Trabajo: Pasos 3.9.4 y 3.9.5 - Paginación y SEO

## 📋 Resumen Ejecutivo

**Objetivo**: Completar las optimizaciones de paginación SEO-friendly y implementar todas las mejoras de SEO necesarias para la aplicación.

**Estado Inicial**:
- ✅ Paginación Livewire implementada en todos los listados públicos
- ✅ Lazy loading de imágenes implementado (Paso 3.9.3)
- ✅ URLs amigables con slugs
- ✅ Title y description básicos en layouts
- ⏳ Faltan meta tags Open Graph y Twitter Cards
- ⏳ Falta sitemap.xml dinámico
- ⏳ robots.txt básico sin referencia a sitemap

---

## 🔍 Análisis del Estado Actual

### Paginación (3.9.4)

| Componente | WithPagination | URL Params | Controles Vista |
|------------|----------------|------------|-----------------|
| `News/Index` | ✅ | ✅ (`q`, `programa`, `ano`, `etiquetas`) | Verificar |
| `Calls/Index` | ✅ | ✅ (`q`, `programa`, `ano`, `tipo`, `modalidad`, `estado`) | Verificar |
| `Programs/Index` | ✅ | ✅ (`q`, `tipo`, `activos`) | Verificar |
| `Documents/Index` | ✅ | ✅ (`q`, `categoria`, `programa`, `ano`, `tipo`) | Verificar |
| `Events/Index` | ✅ | ✅ (`q`, `programa`, `tipo`, `desde`, `hasta`, `pasados`) | Verificar |

### SEO Actual

| Elemento | Estado | Ubicación |
|----------|--------|-----------|
| `<title>` | ✅ | `partials/head.blade.php` |
| `<meta description>` | ✅ | `layouts/public.blade.php` |
| `<meta viewport>` | ✅ | `partials/head.blade.php` |
| `<link rel="icon">` | ✅ | `partials/head.blade.php` |
| Open Graph | ❌ | - |
| Twitter Cards | ❌ | - |
| Canonical URL | ❌ | - |
| JSON-LD | ❌ | - |
| Sitemap.xml | ❌ | - |
| robots.txt | ⚠️ Básico | `public/robots.txt` |

---

## 📝 Plan de Implementación

### Fase 1: Mejoras de Paginación SEO (3.9.4)

#### 1.1 Verificar controles de paginación en vistas

Verificar que todas las vistas de listado público muestren correctamente los controles de paginación de Livewire.

**Archivos a verificar**:
- `resources/views/livewire/public/news/index.blade.php`
- `resources/views/livewire/public/calls/index.blade.php`
- `resources/views/livewire/public/programs/index.blade.php`
- `resources/views/livewire/public/documents/index.blade.php`
- `resources/views/livewire/public/events/index.blade.php`

#### 1.2 Añadir rel="prev" y rel="next" para SEO

Crear un componente que añada las etiquetas `<link rel="prev">` y `<link rel="next">` en el `<head>` para mejorar el SEO de páginas paginadas.

**Nuevo archivo**:
- `resources/views/components/seo/pagination-links.blade.php`

```blade
@props(['paginator' => null])

@if($paginator && $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    @if($paginator->previousPageUrl())
        <link rel="prev" href="{{ $paginator->previousPageUrl() }}">
    @endif
    @if($paginator->nextPageUrl())
        <link rel="next" href="{{ $paginator->nextPageUrl() }}">
    @endif
@endif
```

---

### Fase 2: Componente SEO Meta Tags (3.9.5)

#### 2.1 Crear componente principal de SEO

Crear un componente reutilizable para manejar todos los meta tags de SEO.

**Nuevo archivo**: `resources/views/components/seo/meta.blade.php`

```blade
@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
    'siteName' => null,
    'locale' => null,
    'article' => null, // Para noticias: ['published_time', 'modified_time', 'author', 'section', 'tag']
])

@php
    $title = $title ?? config('app.name');
    $description = $description ?? __('Portal de gestión de movilidades Erasmus+ para alumnado y personal docente.');
    $url = $url ?? request()->url();
    $siteName = $siteName ?? config('app.name');
    $locale = $locale ?? app()->getLocale();
    $image = $image ?? asset('images/og-default.jpg');
@endphp

{{-- Basic Meta --}}
<meta name="description" content="{{ Str::limit($description, 160) }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $url }}">

{{-- Open Graph --}}
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ Str::limit($description, 200) }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ str_replace('-', '_', $locale) }}">
<meta property="og:type" content="{{ $type }}">
@if($image)
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:alt" content="{{ $title }}">
@endif

{{-- Article specific (for news) --}}
@if($type === 'article' && $article)
    @if(isset($article['published_time']))
    <meta property="article:published_time" content="{{ $article['published_time'] }}">
    @endif
    @if(isset($article['modified_time']))
    <meta property="article:modified_time" content="{{ $article['modified_time'] }}">
    @endif
    @if(isset($article['author']))
    <meta property="article:author" content="{{ $article['author'] }}">
    @endif
    @if(isset($article['section']))
    <meta property="article:section" content="{{ $article['section'] }}">
    @endif
    @if(isset($article['tags']) && is_array($article['tags']))
        @foreach($article['tags'] as $tag)
    <meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endif
@endif

{{-- Twitter Cards --}}
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ Str::limit($title, 70) }}">
<meta name="twitter:description" content="{{ Str::limit($description, 200) }}">
@if($image)
<meta name="twitter:image" content="{{ $image }}">
<meta name="twitter:image:alt" content="{{ $title }}">
@endif
```

#### 2.2 Actualizar layout público

Modificar `components/layouts/public.blade.php` para usar el nuevo componente SEO.

**Archivo a modificar**: `resources/views/components/layouts/public.blade.php`

```blade
@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'article' => null,
    'transparentNav' => false,
    'simpleFooter' => false,
])
```

#### 2.3 Actualizar partials/head.blade.php

Integrar el componente SEO en el head.

---

### Fase 3: Actualizar Componentes Show para SEO

#### 3.1 News/Show.php

Añadir datos para Open Graph image y article metadata.

```php
public function render(): View
{
    $excerpt = $this->newsPost->excerpt
        ? Str::limit(strip_tags($this->newsPost->excerpt), 160)
        : __('Noticia sobre movilidad internacional Erasmus+.');

    return view('livewire.public.news.show')
        ->layout('components.layouts.public', [
            'title' => $this->newsPost->title.' - Noticias Erasmus+',
            'description' => $excerpt,
            'image' => $this->featuredImage,
            'type' => 'article',
            'article' => [
                'published_time' => $this->newsPost->published_at?->toIso8601String(),
                'modified_time' => $this->newsPost->updated_at?->toIso8601String(),
                'author' => $this->newsPost->author?->name,
                'section' => $this->newsPost->program?->name ?? 'Erasmus+',
                'tags' => $this->newsPost->tags->pluck('name')->toArray(),
            ],
        ]);
}
```

#### 3.2 Calls/Show.php

```php
public function render(): View
{
    return view('livewire.public.calls.show')
        ->layout('components.layouts.public', [
            'title' => $this->call->title.' - Convocatorias Erasmus+',
            'description' => $this->call->requirements 
                ? Str::limit(strip_tags($this->call->requirements), 160) 
                : __('Convocatoria de movilidad internacional Erasmus+.'),
            'type' => 'website',
        ]);
}
```

#### 3.3 Programs/Show.php

```php
// Añadir imagen del programa si existe
'image' => $this->program->getFirstMediaUrl('image', 'large'),
```

#### 3.4 Documents/Show.php y Events/Show.php

Aplicar el mismo patrón.

---

### Fase 4: Sitemap.xml Dinámico (3.9.5)

#### 4.1 Opción A: Usar spatie/laravel-sitemap

```bash
composer require spatie/laravel-sitemap
```

#### 4.2 Opción B: Crear generación manual (recomendada para control total)

**Nuevo archivo**: `app/Http/Controllers/SitemapController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\Document;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $content = view('sitemap.index', [
            'programs' => Program::where('is_active', true)->get(),
            'calls' => Call::whereIn('status', ['abierta', 'cerrada'])
                ->whereNotNull('published_at')
                ->get(),
            'news' => NewsPost::where('status', 'publicado')
                ->whereNotNull('published_at')
                ->get(),
            'documents' => Document::where('is_active', true)->get(),
            'events' => ErasmusEvent::where('is_public', true)
                ->where('start_date', '>=', now()->subMonths(3))
                ->get(),
        ])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
```

**Nueva ruta** en `routes/web.php`:

```php
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
```

**Nueva vista**: `resources/views/sitemap/index.blade.php`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- Páginas estáticas --}}
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('programas.index') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('convocatorias.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('noticias.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('documentos.index') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('eventos.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('calendario') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>

    {{-- Programas --}}
    @foreach($programs as $program)
    <url>
        <loc>{{ route('programas.show', $program) }}</loc>
        <lastmod>{{ $program->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Convocatorias --}}
    @foreach($calls as $call)
    <url>
        <loc>{{ route('convocatorias.show', $call) }}</loc>
        <lastmod>{{ $call->updated_at->toW3cString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Noticias --}}
    @foreach($news as $newsPost)
    <url>
        <loc>{{ route('noticias.show', $newsPost) }}</loc>
        <lastmod>{{ $newsPost->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Documentos --}}
    @foreach($documents as $document)
    <url>
        <loc>{{ route('documentos.show', $document) }}</loc>
        <lastmod>{{ $document->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

    {{-- Eventos --}}
    @foreach($events as $event)
    <url>
        <loc>{{ route('eventos.show', $event) }}</loc>
        <lastmod>{{ $event->updated_at->toW3cString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach
</urlset>
```

---

### Fase 5: Actualizar robots.txt (3.9.5)

**Archivo a modificar**: `public/robots.txt`

```txt
User-agent: *
Allow: /

# Sitemap
Sitemap: https://erasmus25.test/sitemap.xml

# Disallow admin and auth routes
Disallow: /admin
Disallow: /admin/
Disallow: /login
Disallow: /register
Disallow: /password
Disallow: /settings
Disallow: /dashboard
Disallow: /notificaciones

# Disallow internal routes
Disallow: /livewire/
Disallow: /_debugbar/
```

---

### Fase 6: JSON-LD / Datos Estructurados (Opcional)

#### 6.1 Componente JSON-LD base

**Nuevo archivo**: `resources/views/components/seo/json-ld.blade.php`

```blade
@props(['data'])

<script type="application/ld+json">
{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
```

#### 6.2 JSON-LD para Organization (Home)

```php
$organization = [
    '@context' => 'https://schema.org',
    '@type' => 'EducationalOrganization',
    'name' => config('app.name'),
    'url' => route('home'),
    'logo' => asset('images/logo.png'),
    'description' => __('Portal de gestión de movilidades Erasmus+'),
];
```

#### 6.3 JSON-LD para Article (Noticias)

```php
$articleSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $newsPost->title,
    'image' => $this->featuredImage,
    'datePublished' => $newsPost->published_at->toIso8601String(),
    'dateModified' => $newsPost->updated_at->toIso8601String(),
    'author' => [
        '@type' => 'Person',
        'name' => $newsPost->author?->name,
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => config('app.name'),
    ],
];
```

---

### Fase 7: Tests

#### 7.1 Tests de SEO Meta Tags

**Nuevo archivo**: `tests/Feature/SEO/MetaTagsTest.php`

```php
<?php

use App\Models\NewsPost;
use App\Models\Program;

it('includes open graph meta tags on home page', function () {
    $this->get(route('home'))
        ->assertSee('og:title', false)
        ->assertSee('og:description', false)
        ->assertSee('og:url', false)
        ->assertSee('twitter:card', false);
});

it('includes article meta tags on news detail page', function () {
    $news = NewsPost::factory()->published()->create();
    
    $this->get(route('noticias.show', $news))
        ->assertSee('og:type" content="article"', false)
        ->assertSee('article:published_time', false);
});

it('includes canonical url on all pages', function () {
    $this->get(route('home'))
        ->assertSee('rel="canonical"', false);
});
```

#### 7.2 Tests de Sitemap

**Nuevo archivo**: `tests/Feature/SEO/SitemapTest.php`

```php
<?php

use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;

it('generates valid sitemap xml', function () {
    Program::factory()->active()->create();
    NewsPost::factory()->published()->create();
    Call::factory()->published()->create();
    
    $response = $this->get('/sitemap.xml');
    
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<?xml version="1.0"', false)
        ->assertSee('<urlset', false)
        ->assertSee(route('home'), false);
});

it('includes all public pages in sitemap', function () {
    $program = Program::factory()->active()->create();
    $news = NewsPost::factory()->published()->create();
    
    $this->get('/sitemap.xml')
        ->assertSee(route('programas.show', $program), false)
        ->assertSee(route('noticias.show', $news), false);
});

it('excludes admin pages from sitemap', function () {
    $this->get('/sitemap.xml')
        ->assertDontSee('/admin', false);
});
```

#### 7.3 Tests de robots.txt

```php
it('robots.txt includes sitemap reference', function () {
    $this->get('/robots.txt')
        ->assertSee('Sitemap:', false);
});

it('robots.txt disallows admin routes', function () {
    $this->get('/robots.txt')
        ->assertSee('Disallow: /admin', false);
});
```

---

## ✅ Checklist de Implementación

### Fase 1: Paginación SEO
- [ ] Verificar controles de paginación en todas las vistas públicas
- [ ] Crear componente `seo/pagination-links.blade.php`
- [ ] Integrar rel="prev"/rel="next" en layouts paginados

### Fase 2: Componente SEO ✅ COMPLETADA
- [x] Crear componente `seo/meta.blade.php`
- [x] Crear componente `seo/json-ld.blade.php`
- [x] Actualizar `layouts/public.blade.php` con nuevos props (title, description, image, type, article, jsonLd, noindex)
- [x] Actualizar `partials/head.blade.php`

### Fase 3: Actualizar Componentes Show ✅ COMPLETADA
- [x] `News/Show.php` - añadir OG image y article metadata (published_time, modified_time, author, section, tags)
- [x] `Calls/Show.php` - ya tenía description mejorada
- [x] `Programs/Show.php` - añadir OG image (programImage computed property)
- [x] `Documents/Show.php` - añadir preview image
- [x] `Events/Show.php` - añadir event image
- [x] `Home.php` - añadir JSON-LD Organization schema

### Fase 4: Sitemap ✅ COMPLETADA
- [x] Crear `SitemapController.php` (con caché de 1 hora)
- [x] Crear vista `sitemap/index.blade.php`
- [x] Añadir ruta `/sitemap.xml`
- [x] Verificar que excluye rutas de admin
- [x] Tests: 15 tests (52 assertions) en `SitemapTest.php`

### Fase 5: robots.txt ✅ COMPLETADA
- [x] Actualizar con referencia a sitemap
- [x] Añadir reglas de exclusión para admin, auth, settings, livewire, etc.
- [x] Permitir newsletter/suscribir, bloquear tokens de verificación/baja
- [x] Tests: 9 tests (12 assertions) en `RobotsTxtTest.php`

### Fase 6: JSON-LD (Opcional) ✅ PARCIALMENTE COMPLETADA
- [x] Crear componente `seo/json-ld.blade.php`
- [x] Añadir Organization schema en Home
- [ ] Añadir Article schema en News/Show
- [ ] Añadir Event schema en Events/Show

### Fase 7: Tests ✅ COMPLETADA
- [x] `tests/Feature/SEO/MetaTagsTest.php` - 13 tests, 53 assertions
- [x] `tests/Feature/SEO/SitemapTest.php` - 15 tests, 52 assertions
- [x] `tests/Feature/SEO/RobotsTxtTest.php` - 9 tests, 15 assertions
- [x] Ejecutar suite completa de tests - **3,867 tests pasando (8,793 assertions)**

### Fase 8: Imagen OG por defecto
- [ ] Crear imagen `public/images/og-default.jpg` (1200x630px)

---

## 📊 Métricas de Éxito

1. **Todas las páginas públicas** tienen meta tags Open Graph y Twitter Cards
2. **Sitemap.xml** incluye todas las páginas públicas dinámicas
3. **robots.txt** correctamente configurado con exclusiones
4. **Tests pasando**: 100% de tests de SEO
5. **Google Search Console**: Sitemap validado sin errores

---

## ⚠️ Consideraciones Importantes

### Imagen OG por Defecto
- Crear una imagen de 1200x630px para usar cuando no hay imagen específica
- Formato JPG para mejor compatibilidad
- Incluir logo y nombre del sitio

### Caché del Sitemap
- Considerar cachear el sitemap para evitar regeneración en cada request
- Invalidar caché cuando se publique/actualice contenido

### Validación
- Usar [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/) para validar OG tags
- Usar [Twitter Card Validator](https://cards-dev.twitter.com/validator) para validar Twitter Cards
- Usar [Google Rich Results Test](https://search.google.com/test/rich-results) para JSON-LD

---

## 📅 Orden de Implementación Recomendado

1. **Componente SEO meta** (base para todo lo demás)
2. **Actualizar layout público**
3. **Actualizar componentes Show** con metadatos
4. **Crear sitemap.xml**
5. **Actualizar robots.txt**
6. **Crear imagen OG por defecto**
7. **Tests**
8. (Opcional) JSON-LD

---

**Fecha de creación**: Enero 2026
**Paso previo completado**: 3.9.3 (Optimización de Imágenes) ✅
