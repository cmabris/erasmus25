# CRUD de Noticias en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Noticias en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Noticias permite a los administradores gestionar completamente las noticias desde el panel de administración. Incluye funcionalidades avanzadas como editor de texto enriquecido (Tiptap), gestión avanzada de imágenes con soft delete, gestión de etiquetas, publicación/despublicación, SoftDeletes y validación de relaciones antes de eliminación permanente.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar noticias
- ✅ **SoftDeletes**: Las noticias nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- ✅ **Editor de Texto Enriquecido**: Tiptap (ProseMirror) con extensiones avanzadas
- ✅ **Gestión Avanzada de Imágenes**: Subida, preview, soft delete, restauración y eliminación permanente
- ✅ **Gestión de Etiquetas**: Relación many-to-many con creación desde formulario
- ✅ **Publicación/Despublicación**: Control de estados y fechas de publicación
- ✅ **Búsqueda y Filtros**: Búsqueda por título, excerpt, contenido; filtros por programa, año académico, estado y eliminados
- ✅ **Autorización**: Control de acceso mediante `NewsPostPolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 1231 tests pasando con cobertura completa

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\News\Index`
- **Vista**: `resources/views/livewire/admin/news/index.blade.php`
- **Ruta**: `/admin/noticias` (nombre: `admin.news.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'programa')]
public ?int $programId = null;

#[Url(as: 'ano')]
public ?int $academicYearId = null;

#[Url(as: 'estado')]
public string $status = '';

#[Url(as: 'eliminados')]
public string $showDeleted = '0';

#[Url(as: 'ordenar')]
public string $sortField = 'created_at';

#[Url(as: 'direccion')]
public string $sortDirection = 'desc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public bool $showRestoreModal = false;
public bool $showForceDeleteModal = false;
public ?int $newsPostToDelete = null;
public ?int $newsPostToRestore = null;
public ?int $newsPostToForceDelete = null;
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
public function programs(): LengthAwarePaginator
{
    // Noticias filtradas y paginadas con eager loading
    // Incluye: program, academicYear, author, tags
    // Con conteo de tags
}
```

**Métodos Principales:**

| Método | Descripción |
|--------|-------------|
| `resetFilters()` | Limpia todos los filtros |
| `updatedSearch()` | Reset de paginación al cambiar búsqueda |
| `confirmDelete(int $newsPostId)` | Abre modal de confirmación para eliminar |
| `delete()` | Elimina noticia (soft delete) |
| `confirmRestore(int $newsPostId)` | Abre modal de confirmación para restaurar |
| `restore()` | Restaura noticia eliminada |
| `confirmForceDelete(int $newsPostId)` | Abre modal de confirmación para eliminación permanente |
| `forceDelete()` | Elimina noticia permanentemente (solo super-admin) |
| `togglePublish(int $newsPostId)` | Publica/despublica noticia |
| `toggleUnpublish(int $newsPostId)` | Despublica noticia |

**Características:**
- Búsqueda en título, excerpt y contenido
- Filtros por programa, año académico, estado y eliminados
- Ordenación por columnas (título, fecha creación, fecha publicación)
- Paginación configurable (15, 30, 50, 100)
- Acciones: ver, editar, eliminar, restaurar, publicar/despublicar
- Thumbnails de imágenes destacadas
- Badges de estado y etiquetas

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\News\Create`
- **Vista**: `resources/views/livewire/admin/news/create.blade.php`
- **Ruta**: `/admin/noticias/crear` (nombre: `admin.news.create`)

**Propiedades Públicas:**

```php
public ?int $program_id = null;
public int $academic_year_id = 0;
public string $title = '';
public string $slug = '';
public ?string $excerpt = null;
public string $content = '';
public ?string $country = null;
public ?string $city = null;
public ?string $host_entity = null;
public ?string $mobility_type = null;
public ?string $mobility_category = null;
public string $status = 'borrador';
public ?string $published_at = null;
public array $selectedTags = [];
public $featuredImage = null;
public bool $showCreateTagModal = false;
public string $newTagName = '';
public string $newTagSlug = '';
```

**Computed Properties:**

```php
#[Computed]
public function availablePrograms(): Collection
{
    // Programas activos
}

#[Computed]
public function availableAcademicYears(): Collection
{
    // Años académicos
}

#[Computed]
public function availableTags(): Collection
{
    // Todas las etiquetas ordenadas por nombre
}
```

**Métodos Principales:**

| Método | Descripción |
|--------|-------------|
| `updatedTitle()` | Genera slug automáticamente desde título |
| `updatedNewTagName()` | Genera slug automáticamente desde nombre de etiqueta |
| `updatedFeaturedImage()` | Valida imagen en tiempo real |
| `createTag()` | Crea nueva etiqueta desde modal |
| `store()` | Guarda nueva noticia con validación completa |

**Características:**
- Validación en tiempo real
- Generación automática de slug
- Editor de texto enriquecido (Tiptap)
- Subida de imágenes con FilePond
- Selección múltiple de etiquetas
- Creación de etiquetas desde formulario
- Validación de imágenes (tipo, tamaño máximo 5MB)

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\News\Edit`
- **Vista**: `resources/views/livewire/admin/news/edit.blade.php`
- **Ruta**: `/admin/noticias/{newsPost}/editar` (nombre: `admin.news.edit`)

**Propiedades Públicas:**

```php
public NewsPost $newsPost;
// ... (mismas propiedades que Create)

public ?string $featuredImageUrl = null;
public bool $removeFeaturedImage = false;
public bool $showSelectImageModal = false;
public ?int $selectedImageId = null;
public bool $showForceDeleteImageModal = false;
public ?int $imageToForceDelete = null;
```

**Métodos Principales:**

| Método | Descripción |
|--------|-------------|
| `mount(NewsPost $newsPost)` | Precarga datos de la noticia |
| `hasExistingFeaturedImage()` | Verifica si hay imagen destacada |
| `hasSoftDeletedFeaturedImages()` | Verifica si hay imágenes eliminadas |
| `getAvailableImagesProperty()` | Obtiene todas las imágenes (actuales y eliminadas) |
| `openSelectImageModal()` | Abre modal de selección de imágenes |
| `selectImage()` | Selecciona/restaura imagen desde modal |
| `restoreFeaturedImage()` | Restaura imagen eliminada |
| `confirmForceDeleteImage(int $imageId)` | Abre modal de confirmación para eliminación permanente |
| `forceDeleteImage()` | Elimina imagen permanentemente |
| `toggleRemoveFeaturedImage()` | Marca imagen para eliminación |
| `update()` | Actualiza noticia con validación completa |

**Características:**
- Precarga de datos existentes
- Gestión avanzada de imágenes:
  - Ver imagen actual
  - Reemplazar imagen
  - Eliminar imagen (soft delete)
  - Seleccionar/restaurar desde modal
  - Eliminar permanentemente
- Editor de texto enriquecido (Tiptap)
- Actualización de etiquetas
- Validación de slug único (ignorando el actual)

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\News\Show`
- **Vista**: `resources/views/livewire/admin/news/show.blade.php`
- **Ruta**: `/admin/noticias/{newsPost}` (nombre: `admin.news.show`)

**Propiedades Públicas:**

```php
public NewsPost $newsPost;
public bool $showDeleteModal = false;
public bool $showRestoreModal = false;
public bool $showForceDeleteModal = false;
```

**Computed Properties:**

```php
#[Computed]
public function hasFeaturedImage(): bool
{
    // Verifica si tiene imagen destacada
}

#[Computed]
public function getFeaturedImageUrl(string $conversion = ''): ?string
{
    // Obtiene URL de imagen destacada con conversión
}
```

**Métodos Principales:**

| Método | Descripción |
|--------|-------------|
| `togglePublish()` | Publica/despublica noticia |
| `toggleUnpublish()` | Despublica noticia |
| `confirmDelete()` | Abre modal de confirmación para eliminar |
| `delete()` | Elimina noticia (soft delete) |
| `confirmRestore()` | Abre modal de confirmación para restaurar |
| `restore()` | Restaura noticia eliminada |
| `confirmForceDelete()` | Abre modal de confirmación para eliminación permanente |
| `forceDelete()` | Elimina noticia permanentemente (solo super-admin) |

**Características:**
- Vista completa de información
- Imagen destacada con conversiones (large, medium, thumbnail)
- Contenido HTML renderizado con clases prose
- Información de auditoría (creado por, revisado por, fechas)
- Acciones: editar, eliminar, restaurar, publicar/despublicar
- Breadcrumbs para navegación

---

## Editor de Texto Enriquecido (Tiptap)

### Configuración

**Ubicación:** `resources/js/app.js`

**Extensiones Instaladas:**
- `@tiptap/starter-kit` - Extensiones básicas
- `@tiptap/extension-link` - Enlaces
- `@tiptap/extension-image` - Imágenes
- `@tiptap/extension-placeholder` - Placeholder
- `@tiptap/extension-youtube` - Videos de YouTube
- `@tiptap/extension-table` - Tablas (con filas, columnas, celdas)
- `@tiptap/extension-text-align` - Alineación de texto
- `@tiptap/extension-blockquote` - Citas
- `@tiptap/extension-horizontal-rule` - Líneas horizontales

**Componente Blade:** `resources/views/components/tiptap-editor.blade.php`

**Características:**
- Sincronización bidireccional con Livewire usando `$wire.entangle()`
- Toolbar completo con múltiples opciones
- Soporte para tablas con menú desplegable
- Inserción de imágenes y videos de YouTube
- Formato de texto (negrita, cursiva, tachado)
- Encabezados (H1, H2, H3)
- Listas (con viñetas y numeradas)
- Enlaces (insertar y quitar)
- Alineación de texto (izquierda, centro, derecha)
- Citas y líneas horizontales
- Deshacer/Rehacer

---

## Gestión Avanzada de Imágenes

### Soft Delete para Media

El modelo `NewsPost` implementa soft delete para imágenes usando `custom_properties` de Media Library.

**Métodos en NewsPost:**

```php
// Soft delete (marca como eliminada sin borrar archivo)
public function softDeleteFeaturedImage(): bool

// Restaurar imagen eliminada
public function restoreFeaturedImage(): bool

// Eliminar permanentemente (borra archivo y registro)
public function forceDeleteFeaturedImage(): bool

// Verificar si una imagen está soft-deleted
public function isMediaSoftDeleted(Media $media): bool

// Obtener todas las imágenes eliminadas
public function getSoftDeletedFeaturedImages(): Collection

// Verificar si hay imágenes eliminadas
public function hasSoftDeletedFeaturedImages(): bool

// Obtener todas las imágenes (incluyendo eliminadas)
public function getMediaWithDeleted(string $collectionName = 'default'): Collection
```

**Métodos Sobrescritos:**

```php
// Excluye imágenes soft-deleted por defecto
public function getFirstMedia(string $collectionName = 'default'): ?Media
public function hasMedia(string $collectionName = 'default'): bool
public function getMedia(string $collectionName = 'default'): Collection
```

### Conversiones de Imágenes

**Configuración en NewsPost:**

```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumbnail')
        ->width(300)
        ->height(300)
        ->sharpen(10)
        ->performOnCollections('featured');

    $this->addMediaConversion('medium')
        ->width(800)
        ->height(600)
        ->sharpen(10)
        ->performOnCollections('featured');

    $this->addMediaConversion('large')
        ->width(1200)
        ->height(900)
        ->sharpen(10)
        ->performOnCollections('featured');
}
```

**Uso:**
- `thumbnail`: Para listados (Index)
- `medium`: Para previews y modales
- `large`: Para vista de detalle (Show)

### FilePond Integration

**Trait:** `Spatie\LivewireFilepond\WithFilePond`

**Componente Blade:** `<x-filepond::upload>`

**Características:**
- Validación en frontend (tipo, tamaño)
- Preview de imagen
- Drag & drop
- Localización en español
- Integración con Livewire

---

## Gestión de Etiquetas

### Relación Many-to-Many

**Modelo NewsPost:**
```php
public function tags(): BelongsToMany
{
    return $this->belongsToMany(NewsTag::class, 'news_post_tag')
        ->withTimestamps();
}
```

**Modelo NewsTag:**
```php
public function newsPosts(): BelongsToMany
{
    return $this->belongsToMany(NewsPost::class, 'news_post_tag')
        ->withTimestamps();
}
```

### Creación desde Formulario

Los componentes `Create` y `Edit` permiten crear nuevas etiquetas desde un modal:

```php
public function createTag(): void
{
    $this->authorize('create', NewsTag::class);
    
    // Validación y creación
    $tag = NewsTag::create([
        'name' => $this->newTagName,
        'slug' => $this->newTagSlug,
    ]);
    
    // Actualizar lista de etiquetas disponibles
    $this->selectedTags[] = $tag->id;
}
```

---

## Publicación/Despublicación

### Estados Disponibles

- `borrador` - Borrador, no visible públicamente
- `en_revision` - En revisión
- `publicado` - Publicado y visible
- `archivado` - Archivado

### Lógica de Publicación

```php
public function togglePublish(int $newsPostId): void
{
    $this->authorize('publish', $newsPost);
    
    if ($newsPost->status === 'publicado') {
        $newsPost->update([
            'status' => 'borrador',
            'published_at' => null,
        ]);
    } else {
        $newsPost->update([
            'status' => 'publicado',
            'published_at' => now(),
        ]);
    }
}
```

---

## Autorización

### NewsPostPolicy

**Ubicación:** `app/Policies/NewsPostPolicy.php`

**Métodos:**
- `viewAny()` - Ver listado
- `view()` - Ver detalle
- `create()` - Crear noticia
- `update()` - Actualizar noticia
- `delete()` - Eliminar noticia (soft delete)
- `restore()` - Restaurar noticia
- `forceDelete()` - Eliminar permanentemente (solo super-admin)
- `publish()` - Publicar/despublicar

**Permisos Requeridos:**
- `news.view` - Ver noticias
- `news.create` - Crear noticias
- `news.edit` - Editar noticias
- `news.delete` - Eliminar noticias
- `news.publish` - Publicar/despublicar noticias

---

## Tests

### Cobertura Completa

**Archivos de Tests:**
- `tests/Feature/Livewire/Admin/News/IndexTest.php` - 32 tests
- `tests/Feature/Livewire/Admin/News/CreateTest.php` - 28 tests
- `tests/Feature/Livewire/Admin/News/EditTest.php` - 42 tests (incluye gestión de imágenes)
- `tests/Feature/Livewire/Admin/News/ShowTest.php` - 20 tests

**Total:** 122 tests específicos de News + 1231 tests totales del proyecto

### Tests de Gestión de Imágenes

**Incluidos en EditTest:**
- Subida de nueva imagen
- Eliminación de imagen existente
- Reemplazo de imagen
- Soft delete de imagen
- Restauración de imagen eliminada
- Selección de imagen desde modal
- Eliminación permanente de imagen
- Visualización de imágenes en modal

---

## Rutas

**Archivo:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/noticias', Index::class)->name('news.index');
    Route::get('/noticias/crear', Create::class)->name('news.create');
    Route::get('/noticias/{newsPost}/editar', Edit::class)->name('news.edit');
    Route::get('/noticias/{newsPost}', Show::class)->name('news.show');
});
```

---

## Navegación

**Sidebar:** `resources/views/components/layouts/app/sidebar.blade.php`

```blade
<x-ui.sidebar.item
    href="{{ route('admin.news.index') }}"
    :active="request()->routeIs('admin.news.*')"
    icon="newspaper"
>
    {{ __('Noticias') }}
</x-ui.sidebar.item>
```

---

## Validación

### StoreNewsPostRequest

**Ubicación:** `app/Http/Requests/StoreNewsPostRequest.php`

**Reglas:**
- `program_id`: nullable, exists:programs,id
- `academic_year_id`: required, exists:academic_years,id
- `title`: required, string, max:255
- `slug`: nullable, string, max:255, unique:news_posts,slug
- `excerpt`: nullable, string
- `content`: required, string
- `featured_image`: nullable, image, mimes:jpeg,png,jpg,webp,gif, max:5120
- `tags`: nullable, array
- `tags.*`: required, exists:news_tags,id

### UpdateNewsPostRequest

Similar a `StoreNewsPostRequest` pero con validación de slug único ignorando el registro actual.

---

## Características Destacadas

### Performance

- **Eager Loading**: `with(['program', 'academicYear', 'author', 'tags'])`
- **Lazy Loading**: Imágenes con `loading="lazy"`
- **Computed Properties**: Optimización de consultas
- **Paginación Eficiente**: 15 por página por defecto

### UX/UI

- **Feedback Visual**: Loading states, notificaciones
- **Modales de Confirmación**: Para acciones destructivas
- **Validación en Tiempo Real**: Feedback inmediato
- **Responsive Design**: Adaptativo a todos los dispositivos
- **Dark Mode**: Soporte completo

### Seguridad

- **Autorización**: Policies para todas las acciones
- **Validación**: FormRequests con reglas completas
- **Sanitización**: HTML renderizado de forma segura
- **Soft Deletes**: Protección contra eliminación accidental

---

## Comandos Útiles

### Regenerar Conversiones de Imágenes

```bash
# Regenerar todas las conversiones
php artisan media-library:regenerate

# Regenerar solo conversiones faltantes
php artisan media-library:regenerate --only-missing

# Regenerar conversiones específicas
php artisan media-library:regenerate --only=thumbnail --only=medium

# Regenerar para un modelo específico
php artisan media-library:regenerate "App\Models\NewsPost"
```

### Ejecutar Tests

```bash
# Todos los tests de News
php artisan test tests/Feature/Livewire/Admin/News/

# Test específico
php artisan test tests/Feature/Livewire/Admin/News/EditTest.php

# Con filtro
php artisan test --filter="Admin News Edit - Image Management"
```

---

## Notas de Implementación

### Tiptap Integration

- Usa `Alpine.data()` para encapsular la lógica del editor
- Sincronización con Livewire mediante `$wire.entangle()`
- Manejo de errores "mismatched transaction" resuelto
- Extensiones configuradas para evitar conflictos

### Image Soft Delete

- Implementado usando `custom_properties['deleted_at']`
- Los métodos estándar de Media Library excluyen imágenes eliminadas
- Métodos especiales para acceder a todas las imágenes (incluyendo eliminadas)
- Eliminación permanente solo desde modal de confirmación

### FilePond

- Integración con Spatie Livewire-FilePond
- Validación en frontend y backend
- Localización en español
- Preview automático de imágenes

---

**Fecha de Creación**: Diciembre 2025  
**Fecha de Finalización**: Enero 2026  
**Versión**: 1.0  
**Estado**: ✅ Completado - 1231 tests pasando

