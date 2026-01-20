# Paso 48: Implementación de los Pasos 3.9.4 y 3.9.5 - Paginación y SEO

**Fecha**: Enero 2026  
**Objetivo**: Implementar mejoras de SEO completas incluyendo meta tags Open Graph, Twitter Cards, sitemap.xml dinámico y robots.txt mejorado

---

## Resumen de Resultados

### Logros Principales
- ✅ Componente SEO reutilizable (`x-seo.meta`) con Open Graph, Twitter Cards y canonical URL
- ✅ Componente JSON-LD (`x-seo.json-ld`) para datos estructurados
- ✅ Sitemap.xml dinámico con caché de 1 hora
- ✅ robots.txt completo con exclusiones apropiadas
- ✅ Article metadata para noticias
- ✅ JSON-LD Organization en página principal
- ✅ Computed properties para OG images en componentes Show
- ✅ **Tests finales: 3,867 pasando (8,793 assertions)**

### Archivos Creados
- `resources/views/components/seo/meta.blade.php`
- `resources/views/components/seo/json-ld.blade.php`
- `app/Http/Controllers/SitemapController.php`
- `resources/views/sitemap/index.blade.php`
- `tests/Feature/SEO/MetaTagsTest.php`
- `tests/Feature/SEO/SitemapTest.php`
- `tests/Feature/SEO/RobotsTxtTest.php`
- `tests/fixtures/test-image.jpg`

### Archivos Modificados
- `resources/views/components/layouts/public.blade.php`
- `resources/views/partials/head.blade.php`
- `app/Livewire/Public/News/Show.php`
- `app/Livewire/Public/Programs/Show.php`
- `app/Livewire/Public/Documents/Show.php`
- `app/Livewire/Public/Events/Show.php`
- `app/Livewire/Public/Home.php`
- `routes/web.php`
- `public/robots.txt`
- `docs/pasos/paso-3.9.4-3.9.5-plan.md`
- `docs/planificacion_pasos.md`

---

## Prompts y Resultados

### Prompt 1: Solicitud de Plan de Trabajo

**Usuario:**
> En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que hace de índice y otro llamado @planificacion_pasos.md que contiene las tareas a desarrollar en orden. Estamos en situación de continuar con el desarrollo del Paso 3.9. el cual se compone de varios pasos parciales. Vamos a seguir con el desarrollo del paso 3.9.4. y 3.9.5. que corresponde a la Paginación, Lazy Loading y SEO. Antes de comenzar quiero que revises los archivos necesarios para la tarea y cuya documentación está en la carpeta docs y me generes un plan de trabajo.

**Resultado:**
- Revisión completa del estado actual de la aplicación
- Análisis de paginación existente (todos los componentes públicos ya usan `WithPagination`)
- Análisis de SEO actual (solo title y description básicos)
- Identificación de elementos faltantes:
  - Open Graph tags
  - Twitter Cards
  - Sitemap.xml
  - robots.txt mejorado
  - Canonical URLs
  - JSON-LD
- Creación del plan de trabajo en `docs/pasos/paso-3.9.4-3.9.5-plan.md` con:
  - 8 fases de implementación
  - Checklist detallado
  - Ejemplos de código
  - Priorización recomendada

---

### Prompt 2: Aprobación del Plan

**Usuario:**
> Sí

**Resultado:**
- Plan de trabajo creado y guardado en `docs/pasos/paso-3.9.4-3.9.5-plan.md`
- Confirmación para proceder con la implementación

---

### Prompt 3: Inicio de Fase 2 (Componente SEO)

**Usuario:**
> Vale, seguimos tu recomendación y empezamos por la Fase 2

**Resultado:**

#### Fase 2.1: Creación de componentes SEO

**Archivo `resources/views/components/seo/meta.blade.php`:**
```blade
@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
    'siteName' => null,
    'locale' => null,
    'article' => null,
    'noindex' => false,
])
```

Características:
- Meta description con límite de 160 caracteres
- Canonical URL automática
- Open Graph completo (title, description, image, url, type, locale, site_name)
- Twitter Cards (card, title, description, image)
- Soporte para Article metadata (published_time, modified_time, author, section, tags)
- Prop `noindex` para páginas que no deben indexarse

**Archivo `resources/views/components/seo/json-ld.blade.php`:**
```blade
@props(['data'])

@if($data)
<script type="application/ld+json">
{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endif
```

#### Fase 2.2: Actualización del layout público

**Archivo `resources/views/components/layouts/public.blade.php`:**

Nuevos props añadidos:
- `image` - URL de imagen para OG/Twitter
- `type` - Tipo de página (website, article)
- `article` - Array con metadata de artículo
- `jsonLd` - Datos estructurados JSON-LD
- `noindex` - Flag para no indexar

#### Fase 2.3: Actualización de componentes Show

**`app/Livewire/Public/News/Show.php`:**
```php
return view('livewire.public.news.show')
    ->layout('components.layouts.public', [
        'title' => $this->newsPost->title.' - Noticias Erasmus+',
        'description' => $description,
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
```

**`app/Livewire/Public/Programs/Show.php`:**
- Añadida computed property `programImage()` para OG image

**`app/Livewire/Public/Documents/Show.php`:**
- Añadida computed property `previewImage()` para OG image

**`app/Livewire/Public/Events/Show.php`:**
- Añadida computed property `eventImage()` para OG image

**`app/Livewire/Public/Home.php`:**
- Añadido método `getOrganizationJsonLd()` con schema.org Organization
- JSON-LD incluido en el layout

#### Fase 2.4: Tests de Meta Tags

**Archivo `tests/Feature/SEO/MetaTagsTest.php`:**

13 tests implementados:
1. `it includes open graph meta tags on home page`
2. `it includes twitter card meta tags on home page`
3. `it includes canonical url on home page`
4. `it includes json-ld structured data on home page`
5. `it includes article meta tags on news detail page`
6. `it includes og image on news detail page with featured image`
7. `it includes program og image when available`
8. `it includes meta description on all public pages`
9. `it includes canonical url on paginated pages`
10. `it truncates description to 160 characters`
11. `it sets og type to website for index pages`
12. `it sets og type to article for news posts`
13. `it includes article tags for news with tags`

**Resultado de tests:** 13 pasando (53 assertions)

---

### Prompt 4: Continuación con Sitemap y robots.txt

(Implícito tras completar Fase 2)

**Resultado:**

#### Fase 4: Sitemap.xml

**Archivo `app/Http/Controllers/SitemapController.php`:**
```php
class SitemapController extends Controller
{
    private const CACHE_TTL = 3600; // 1 hora

    public function index(): Response
    {
        $content = Cache::remember('sitemap.xml', self::CACHE_TTL, function () {
            return view('sitemap.index', [
                'programs' => Program::where('is_active', true)->get(),
                'calls' => Call::whereIn('status', ['abierta', 'cerrada'])
                    ->whereNotNull('published_at')->get(),
                'news' => NewsPost::where('status', 'publicado')
                    ->whereNotNull('published_at')->get(),
                'documents' => Document::where('is_active', true)->get(),
                'events' => ErasmusEvent::where('is_public', true)
                    ->where('start_date', '>=', now()->subMonths(3))->get(),
            ])->render();
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
```

**Archivo `resources/views/sitemap/index.blade.php`:**

Incluye:
- Páginas estáticas (home, índices, calendario, búsqueda)
- Programas activos
- Convocatorias publicadas (con mayor prioridad para abiertas)
- Noticias publicadas
- Documentos activos
- Eventos públicos recientes (últimos 3 meses)

**Ruta añadida en `routes/web.php`:**
```php
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
```

**Tests en `tests/Feature/SEO/SitemapTest.php`:**

15 tests implementados:
1. `it returns valid xml response`
2. `it includes xml declaration`
3. `it includes urlset with sitemap schema`
4. `it includes static pages`
5. `it includes active programs`
6. `it includes published calls`
7. `it includes published news`
8. `it includes active documents`
9. `it includes recent public events`
10. `it includes lastmod dates`
11. `it includes priority values`
12. `it includes changefreq values`
13. `it does not include admin routes`
14. `it does not include auth routes`
15. `it gives higher priority to open calls than closed`

**Resultado de tests:** 15 pasando (52 assertions)

#### Fase 5: robots.txt

**Archivo `public/robots.txt`:**
```txt
# Robots.txt for Erasmus+ Portal
User-agent: *
Allow: /

# Sitemap location
Sitemap: https://erasmus25.test/sitemap.xml

# Disallow admin and authentication routes
Disallow: /admin
Disallow: /admin/
Disallow: /login
Disallow: /register
Disallow: /password
Disallow: /forgot-password
Disallow: /reset-password
Disallow: /two-factor-challenge
Disallow: /settings
Disallow: /settings/
Disallow: /dashboard
Disallow: /notificaciones

# Disallow internal/technical routes
Disallow: /livewire/
Disallow: /_debugbar/
Disallow: /sanctum/
Disallow: /api/

# Disallow user-specific pages
Disallow: /email/verify

# Allow search engines to index newsletter subscribe page
Allow: /newsletter/suscribir

# Disallow newsletter verification and unsubscribe with tokens
Disallow: /newsletter/verificar/
Disallow: /newsletter/baja/
```

**Tests en `tests/Feature/SEO/RobotsTxtTest.php`:**

9 tests implementados:
1. `it robots.txt file exists`
2. `it includes sitemap reference`
3. `it allows all user agents`
4. `it disallows admin routes`
5. `it disallows auth routes`
6. `it disallows settings routes`
7. `it disallows livewire internal routes`
8. `it allows newsletter subscribe page`
9. `it disallows newsletter verification and unsubscribe tokens`

**Resultado de tests:** 9 pasando (15 assertions)

---

## Configuraciones Implementadas

### Componente SEO Meta

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| title | string | config('app.name') | Título de la página |
| description | string | Descripción genérica | Meta description |
| image | string | og-default.jpg | URL de imagen OG |
| url | string | request()->url() | URL canónica |
| type | string | 'website' | Tipo OG (website, article) |
| article | array | null | Metadata de artículo |
| noindex | bool | false | Excluir de indexación |

### Sitemap.xml Priority

| Contenido | Priority | Changefreq |
|-----------|----------|------------|
| Home | 1.0 | daily |
| Convocatorias abiertas | 0.9 | weekly |
| Índices (programas, convocatorias, noticias) | 0.9 | daily/weekly |
| Programas | 0.8 | monthly |
| Documentos, Eventos | 0.6 | weekly/monthly |
| Noticias | 0.7 | monthly |

### JSON-LD Organization Schema

```php
[
    '@context' => 'https://schema.org',
    '@type' => 'EducationalOrganization',
    'name' => config('app.name'),
    'url' => route('home'),
    'logo' => config('app.url').'/images/logo.png',
    'description' => __('Portal de gestión de movilidades Erasmus+'),
]
```

---

## Comandos Ejecutados

```bash
# Crear directorio de componentes SEO
mkdir -p resources/views/components/seo

# Crear directorio de sitemap
mkdir -p resources/views/sitemap

# Crear directorio de tests SEO
mkdir -p tests/Feature/SEO

# Crear directorio de imágenes públicas
mkdir -p public/images

# Crear directorio de fixtures para tests
mkdir -p tests/fixtures

# Crear imagen de prueba con PHP GD
php -r "
\$img = imagecreatetruecolor(100, 100);
\$blue = imagecolorallocate(\$img, 0, 0, 255);
imagefill(\$img, 0, 0, \$blue);
imagejpeg(\$img, 'tests/fixtures/test-image.jpg', 90);
"

# Formatear código
vendor/bin/pint --dirty

# Ejecutar tests de SEO
php artisan test tests/Feature/SEO/ --parallel

# Ejecutar suite completa
php artisan test --parallel
```

---

## Métricas Finales

| Métrica | Valor |
|---------|-------|
| Tests totales | 3,867 |
| Assertions | 8,793 |
| Tests SEO nuevos | 37 |
| Assertions SEO | 120 |
| Archivos creados | 8 |
| Archivos modificados | 11 |

---

## Funcionalidades SEO Implementadas

### Meta Tags

| Tag | Páginas | Descripción |
|-----|---------|-------------|
| `og:title` | Todas | Título optimizado para compartir |
| `og:description` | Todas | Descripción truncada a 200 chars |
| `og:image` | Todas (con imagen) | Imagen destacada o default |
| `og:url` | Todas | URL canónica |
| `og:type` | Todas | website o article |
| `og:locale` | Todas | es o en |
| `og:site_name` | Todas | Nombre de la aplicación |
| `twitter:card` | Todas | summary_large_image |
| `twitter:title` | Todas | Título truncado a 70 chars |
| `twitter:description` | Todas | Descripción truncada |
| `twitter:image` | Todas (con imagen) | Misma que OG |
| `canonical` | Todas | URL canónica |
| `article:*` | Noticias | Metadata específica |

### Sitemap.xml

- Generación dinámica basada en contenido de BD
- Caché de 1 hora para rendimiento
- Método `clearCache()` para invalidación manual
- Incluye todos los contenidos públicos activos
- Excluye automáticamente contenido borrador/inactivo

### robots.txt

- Permite indexación general
- Referencia al sitemap
- Exclusiones completas para rutas sensibles
- Protección de tokens de newsletter

---

## Tareas Pendientes (Opcionales)

1. **Imagen OG por defecto**: Crear `public/images/og-default.jpg` (1200x630px) con logo y branding
2. **JSON-LD Article**: Añadir schema.org Article en `News/Show`
3. **JSON-LD Event**: Añadir schema.org Event en `Events/Show`
4. **Validación**: Probar con Facebook Debugger, Twitter Card Validator y Google Rich Results Test
5. **rel="prev"/rel="next"**: Implementar para páginas paginadas (opcional, ya no recomendado por Google)

---

**Próximo paso sugerido**: Paso 3.10 (Documentación Final) o implementación de imagen OG por defecto
