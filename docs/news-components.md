# Documentación Técnica: Componentes de Noticias

Este documento describe la arquitectura y uso de los componentes creados para el listado y detalle de noticias en la aplicación Erasmus+ Centro (Murcia).

---

## Índice

1. [Arquitectura General](#arquitectura-general)
2. [Modelo NewsPost y Media Library](#modelo-newspost-y-media-library)
3. [Componentes Livewire](#componentes-livewire)
4. [Rutas](#rutas)
5. [Seeders](#seeders)
6. [Guía de Uso](#guía-de-uso)
7. [Tests](#tests)

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                     Layout Público                               │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Public Nav                                ││
│  │        [Noticias] activo cuando routeIs('noticias.*')       ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │              Livewire Component                              ││
│  │                                                              ││
│  │  News\Index          News\Show                               ││
│  │  ┌──────────────┐        ┌──────────────┐                 ││
│  │  │ x-ui.search  │        │ x-ui.bread   │                 ││
│  │  │ x-ui.section │        │ x-ui.section │                 ││
│  │  │ news-card    │        │ news-card    │                 ││
│  │  │ x-ui.empty   │        │ call-card    │                 ││
│  │  └──────────────┘        └──────────────┘                 ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                      Footer                                  ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

---

## Modelo NewsPost y Media Library

### Configuración de Media Library

El modelo `NewsPost` utiliza Spatie Media Library para gestionar imágenes y archivos multimedia.

**Ubicación:** `app/Models/NewsPost.php`

**Trait e Interfaz:**
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class NewsPost extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

**Colecciones de medios configuradas:**

| Colección | Tipo | Descripción |
|-----------|------|-------------|
| `featured` | Imagen (single file) | Imagen destacada de la noticia |
| `gallery` | Imágenes (múltiples) | Galería de imágenes |
| `videos` | Videos | Videos asociados |
| `audio` | Audio | Archivos de audio/podcasts |

**Tipos MIME aceptados:**
- **Imágenes:** `image/jpeg`, `image/png`, `image/webp`, `image/gif`
- **Videos:** `video/mp4`, `video/webm`, `video/ogg`
- **Audio:** `audio/mpeg`, `audio/mp3`, `audio/wav`, `audio/ogg`

**Conversiones de imágenes:**

| Conversión | Dimensiones | Descripción |
|------------|-------------|-------------|
| `thumbnail` | 300x300px | Miniatura pequeña |
| `medium` | 800x600px | Tamaño medio |
| `large` | 1200x900px | Tamaño grande |

**Uso en código:**
```php
// Obtener URL de imagen destacada
$imageUrl = $newsPost->getFirstMediaUrl('featured');

// Obtener URL de conversión
$thumbnailUrl = $newsPost->getFirstMediaUrl('featured', 'thumbnail');

// Añadir imagen desde URL
$newsPost->addMediaFromUrl($url)->toMediaCollection('featured');

// Añadir imagen desde archivo
$newsPost->addMediaFromRequest('image')->toMediaCollection('featured');
```

---

## Componentes Livewire

### News\Index

Listado público de noticias con filtros avanzados y búsqueda.

**Ubicación:** `app/Livewire/Public/News/Index.php`

**Propiedades públicas con URL binding:**

```php
#[Url(as: 'q')] public string $search = '';
#[Url(as: 'programa')] public string $program = '';
#[Url(as: 'ano')] public string $academicYear = '';
#[Url(as: 'etiquetas')] public string $tags = ''; // Comma-separated tag IDs
```

**Computed Properties:**

```php
#[Computed]
public function availablePrograms(): Collection
{
    // Programas activos ordenados por order y name
}

#[Computed]
public function availableAcademicYears(): Collection
{
    // Años académicos ordenados por year desc
}

#[Computed]
public function availableTags(): Collection
{
    // Todas las etiquetas ordenadas por name
}

#[Computed]
public function selectedTagIds(): array
{
    // Convierte string de tags (comma-separated) a array de IDs
}

#[Computed]
public function stats(): array
{
    return [
        'total' => (int) Total de noticias publicadas,
        'this_month' => (int) Noticias publicadas este mes,
        'this_year' => (int) Noticias publicadas este año,
    ];
}

#[Computed]
public function news(): LengthAwarePaginator
{
    // Retorna noticias filtradas y paginadas (12 por página)
    // Solo muestra: status = 'publicado' AND published_at IS NOT NULL
    // Orden: published_at desc (más recientes primero)
    // Eager loading: program, academicYear, author, tags
}
```

**Métodos públicos:**

| Método | Descripción |
|--------|-------------|
| `resetFilters()` | Limpia todos los filtros |
| `updatedSearch()` | Reset de paginación al cambiar búsqueda |
| `updatedProgram()` | Reset de paginación al cambiar programa |
| `updatedAcademicYear()` | Reset de paginación al cambiar año |
| `updatedTags()` | Reset de paginación al cambiar etiquetas |
| `toggleTag(int $tagId)` | Añade/elimina una etiqueta del filtro |
| `removeTag(int $tagId)` | Elimina una etiqueta específica del filtro |

**Vista:** `resources/views/livewire/public/news/index.blade.php`

**Secciones:**
1. Hero section con estadísticas (total, este mes, este año)
2. Barra de filtros (búsqueda, programa, año académico)
3. Filtro de etiquetas (chips clickeables)
4. Badges de filtros activos con opción de eliminar
5. Grid de noticias (3 columnas en desktop, responsive)
6. Paginación
7. CTA final

**Filtros disponibles:**
- **Búsqueda:** Título, excerpt, content
- **Programa:** Select con programas activos
- **Año académico:** Select con años disponibles
- **Etiquetas:** Chips clickeables (múltiples selección)

**Características especiales:**
- Filtro de etiquetas como chips interactivos (más visual que un select)
- Primera noticia destacada (variante 'featured')
- Resto de noticias con variante 'default'
- Imágenes destacadas desde Media Library

---

### News\Show

Detalle público de una noticia con información completa.

**Ubicación:** `app/Livewire/Public/News/Show.php`

**Propiedad pública:**

```php
public NewsPost $newsPost;
```

**Validación en mount:**
- Solo muestra noticias con `status = 'publicado'`
- Requiere `published_at IS NOT NULL`
- Retorna 404 si no cumple condiciones

**Computed Properties:**

```php
#[Computed]
public function featuredImage(): ?string
{
    // URL de la imagen destacada desde Media Library
    return $this->newsPost->getFirstMediaUrl('featured');
}

#[Computed]
public function relatedNews(): Collection
{
    // Noticias relacionadas:
    // - Mismo programa (si aplica)
    // - Al menos una etiqueta común (si aplica)
    // - Excluye la noticia actual
    // - Ordenadas por published_at desc
    // - Límite: 3
}

#[Computed]
public function relatedCalls(): Collection
{
    // Convocatorias relacionadas del mismo programa
    // Solo si la noticia tiene programa asociado
    // Solo abiertas/cerradas y publicadas
    // Orden: abiertas primero, luego por published_at desc
    // Límite: 3
}
```

**Vista:** `resources/views/livewire/public/news/show.blade.php`

**Secciones:**
1. Hero con imagen destacada (si existe) o gradiente Erasmus+
2. Breadcrumbs
3. Badges (programa, año académico, etiquetas - máx 3)
4. Meta información (fecha, autor, ubicación)
5. Excerpt destacado (si existe)
6. Contenido HTML renderizado
7. Información adicional (ubicación, entidad de acogida, tipo y categoría de movilidad)
8. Etiquetas completas
9. Noticias relacionadas (si existen)
10. Convocatorias relacionadas (si existen y la noticia tiene programa)
11. CTA final

**Características especiales:**
- Hero adaptativo: imagen destacada a pantalla completa o gradiente según disponibilidad
- Contenido HTML renderizado con clases prose de Tailwind
- Cards de información adicional con iconos
- Secciones relacionadas solo se muestran si hay contenido

---

## Rutas

**Archivo:** `routes/web.php`

```php
// Rutas públicas de noticias
Route::get('/noticias', News\Index::class)->name('noticias.index');
Route::get('/noticias/{newsPost:slug}', News\Show::class)->name('noticias.show');
```

**Ejemplos de URLs:**

| URL | Descripción |
|-----|-------------|
| `/noticias` | Listado de todas las noticias publicadas |
| `/noticias?programa=1` | Filtrado por programa |
| `/noticias?ano=1` | Filtrado por año académico |
| `/noticias?etiquetas=1,2,3` | Filtrado por etiquetas (múltiples) |
| `/noticias?q=experiencia` | Búsqueda por "experiencia" |
| `/noticias/experiencia-erasmus-alemania` | Detalle de noticia |

**Actualizaciones en otros componentes:**
- `news-card.blade.php`: Usa `route('noticias.show', $newsPost)` cuando está disponible
- `public-nav.blade.php`: Enlace actualizado a `route('noticias.index')`
- `calls/show.blade.php`: Enlaces a noticias relacionadas

---

## Seeders

### NewsTagSeeder

**Archivo:** `database/seeders/NewsTagSeeder.php`

Crea 30 etiquetas relacionadas con Erasmus+ para categorizar noticias.

**Etiquetas creadas:**
- Movilidad Estudiantil, Movilidad Personal
- Formación Profesional, Educación Superior
- FCT, Job Shadowing, Intercambio, Curso de Formación
- Experiencia Internacional, Europa, Erasmus+
- KA1, KA2, KA3
- Prácticas, Estudios, Desarrollo Profesional
- Idiomas, Cultura, Innovación, Sostenibilidad
- Inclusión, Digital, Verde
- Testimonio, Éxito, Colaboración, Networking
- Internacionalización, Buenas Prácticas

**Características:**
- Usa `updateOrCreate` con slug para evitar duplicados
- Nombres en español
- Slugs generados automáticamente

**Ejecutar:**
```bash
php artisan db:seed --class=NewsTagSeeder
```

---

### NewsPostSeeder

**Archivo:** `database/seeders/NewsPostSeeder.php`

Crea noticias publicadas variadas y realistas.

**Características:**
- 23 noticias publicadas creadas
- 8 noticias destacadas con títulos específicos
- 15 noticias adicionales con títulos variados
- Datos realistas:
  - Diferentes programas (algunas sin programa)
  - Diferentes años académicos
  - Países y ciudades europeas
  - Tipos y categorías de movilidad variados
  - Autores asignados
  - Fechas de publicación distribuidas en los últimos 180 días
  - Contenido generado dinámicamente
- Asigna 2-5 etiquetas aleatorias a cada noticia
- Todas con estado 'publicado' y `published_at` establecido

**Estructura de datos:**
- Títulos realistas y descriptivos
- Excerpts generados
- Contenido HTML con párrafos variados
- Ubicaciones europeas (país y ciudad)
- Entidades de acogida
- Tipos de movilidad: alumnado, personal
- Categorías: FCT, job_shadowing, intercambio, curso, otro

**Ejecutar:**
```bash
php artisan db:seed --class=NewsPostSeeder
```

**Nota:** El seeder no añade imágenes destacadas automáticamente. Para añadirlas en desarrollo, usar Media Library después de crear las noticias o usar URLs de placeholder en las vistas.

---

## Guía de Uso

### Obtener imagen destacada de una noticia

```php
$newsPost = NewsPost::find(1);

// Obtener URL de imagen destacada
$imageUrl = $newsPost->getFirstMediaUrl('featured');

// Obtener URL de conversión
$thumbnailUrl = $newsPost->getFirstMediaUrl('featured', 'thumbnail');
$mediumUrl = $newsPost->getFirstMediaUrl('featured', 'medium');
$largeUrl = $newsPost->getFirstMediaUrl('featured', 'large');

// Verificar si tiene imagen
if ($newsPost->hasMedia('featured')) {
    // Tiene imagen destacada
}
```

### Añadir imagen destacada a una noticia

```php
$newsPost = NewsPost::find(1);

// Desde archivo subido
$newsPost->addMediaFromRequest('image')
    ->toMediaCollection('featured');

// Desde URL
$newsPost->addMediaFromUrl('https://example.com/image.jpg')
    ->toMediaCollection('featured');

// Desde path local
$newsPost->addMediaFromPath('/path/to/image.jpg')
    ->toMediaCollection('featured');
```

### Filtrar noticias por etiquetas

```php
// En un componente Livewire
public function toggleTag(int $tagId): void
{
    $tagIds = $this->selectedTagIds();
    
    if (in_array($tagId, $tagIds, true)) {
        $tagIds = array_values(array_diff($tagIds, [$tagId]));
    } else {
        $tagIds[] = $tagId;
    }
    
    $this->tags = ! empty($tagIds) ? implode(',', $tagIds) : '';
    $this->resetPage();
}
```

### Obtener noticias relacionadas

```php
$newsPost = NewsPost::find(1);

// Noticias del mismo programa
$related = NewsPost::query()
    ->where('id', '!=', $newsPost->id)
    ->where('program_id', $newsPost->program_id)
    ->where('status', 'publicado')
    ->whereNotNull('published_at')
    ->orderBy('published_at', 'desc')
    ->limit(3)
    ->get();

// Noticias con tags comunes
$tagIds = $newsPost->tags->pluck('id')->toArray();
$related = NewsPost::query()
    ->where('id', '!=', $newsPost->id)
    ->whereHas('tags', fn ($q) => $q->whereIn('news_tags.id', $tagIds))
    ->where('status', 'publicado')
    ->whereNotNull('published_at')
    ->orderBy('published_at', 'desc')
    ->limit(3)
    ->get();
```

### Usar el componente news-card

```blade
{{-- Con modelo completo --}}
<x-content.news-card 
    :news="$newsPost" 
    :imageUrl="$newsPost->getFirstMediaUrl('featured')"
    variant="featured"
    :showProgram="true"
    :showAuthor="false"
    :showDate="true"
/>

{{-- Con datos individuales --}}
<x-content.news-card 
    title="Mi Noticia"
    excerpt="Resumen de la noticia"
    :publishedAt="now()"
    :program="$program"
    imageUrl="https://example.com/image.jpg"
    href="{{ route('noticias.show', $slug) }}"
/>
```

**Variantes disponibles:**
- `default` - Tarjeta estándar con imagen arriba
- `featured` - Tarjeta grande destacada
- `horizontal` - Imagen a la izquierda, contenido a la derecha
- `compact` - Versión compacta sin imagen grande

---

## Tests

### IndexTest

**Archivo:** `tests/Feature/Livewire/Public/News/IndexTest.php`

**Tests incluidos (18 tests, 33 assertions):**
- Renderizado de la página
- Solo muestra noticias publicadas
- Búsqueda por título
- Búsqueda por excerpt
- Filtros: programa, año académico, etiquetas
- Toggle de etiquetas (on/off)
- Eliminar etiqueta del filtro
- Reset de filtros
- Empty state cuando no hay resultados
- Estadísticas correctas
- Paginación
- SEO y breadcrumbs
- Enlaces a detalle
- Ordenamiento por fecha (más recientes primero)

**Ejecutar:**
```bash
php artisan test tests/Feature/Livewire/Public/News/IndexTest.php
```

---

### ShowTest

**Archivo:** `tests/Feature/Livewire/Public/News/ShowTest.php`

**Tests incluidos (18 tests, 35 assertions):**
- Renderizado con noticia válida
- 404 para noticias no publicadas
- 404 para noticias sin published_at
- Información de la noticia
- Información del autor
- Información de ubicación
- Entidad de acogida
- Tipo y categoría de movilidad
- Etiquetas
- Noticias relacionadas (mismo programa, tags comunes)
- Convocatorias relacionadas
- Excluye noticia actual de relacionadas
- Limita noticias relacionadas a 3
- SEO y breadcrumbs
- Fecha de publicación
- Manejo de noticias sin excerpt
- Manejo de noticias sin ubicación

**Ejecutar:**
```bash
php artisan test tests/Feature/Livewire/Public/News/ShowTest.php
```

**Resultado total:**
- 38 tests pasando
- 68 assertions exitosas
- Código formateado con Pint

---

## Características Destacadas

### Filtros Avanzados

- **Búsqueda en tiempo real** con debounce de 300ms
- **Filtros múltiples combinables** (programa, año, etiquetas)
- **Filtro de etiquetas interactivo** con chips clickeables
- **Estado en URL** para compartir búsquedas
- **Reset automático de paginación** al cambiar filtros

### Diseño Responsive

- **Grid adaptativo:** 1 columna móvil, 2 tablet, 3 desktop
- **Hero adaptativo:** Imagen destacada o gradiente según disponibilidad
- **Filtros responsive:** Se adaptan a diferentes tamaños de pantalla
- **Dark mode completo** en todos los componentes

### Performance

- **Eager loading** para evitar N+1 queries
- **Paginación eficiente** (12 por página)
- **Computed properties** para optimizar cálculos
- **Lazy loading** de imágenes

### SEO

- **Meta tags dinámicos** en componentes Livewire
- **URLs amigables** con slugs
- **Breadcrumbs** para navegación contextual
- **Contenido estructurado** con HTML semántico

---

## Integración con Otros Componentes

### En Home

Las noticias aparecen en la página principal usando el componente `news-card`:

```blade
@foreach($news as $newsPost)
    <x-content.news-card 
        :news="$newsPost" 
        :imageUrl="$newsPost->getFirstMediaUrl('featured')"
    />
@endforeach
```

### En Calls\Show

Las noticias relacionadas aparecen en el detalle de convocatorias:

```blade
@foreach($this->relatedNews as $news)
    <x-content.news-card :news="$news" :showProgram="false" />
@endforeach
```

### En Programs\Show

Las noticias relacionadas aparecen en el detalle de programas usando el mismo componente.

---

## Notas de Implementación

### Media Library

- Las imágenes destacadas se almacenan en la colección `featured`
- Las conversiones se generan automáticamente al subir imágenes
- Para producción, configurar el disco de almacenamiento apropiado
- Las URLs de Media Library son públicas por defecto

### Filtros de Etiquetas

- El filtro de etiquetas usa un string con IDs separados por comas
- Se convierte a array en la computed property `selectedTagIds()`
- Los chips son clickeables y muestran estado visual (seleccionado/no seleccionado)
- Se puede seleccionar múltiples etiquetas simultáneamente

### Contenido HTML

- El contenido de las noticias se renderiza con clases prose de Tailwind
- Se usa `nl2br(e($content))` para renderizar HTML de forma segura
- El contenido puede incluir HTML básico (párrafos, listas, enlaces)

### Noticias Relacionadas

- Prioriza noticias del mismo programa
- Si hay etiquetas, prioriza noticias con tags comunes
- Si no hay programa, busca solo por tags comunes
- Siempre excluye la noticia actual
- Limita a 3 noticias relacionadas

---

**Fecha de Creación**: Diciembre 2025  
**Versión**: 1.0  
**Estado**: ✅ Completado y documentado
