# Documentación Técnica: Componentes de Documentos

Este documento describe la arquitectura y uso de los componentes creados para el listado y detalle de documentos en la aplicación Erasmus+ Centro (Murcia).

---

## Índice

1. [Arquitectura General](#arquitectura-general)
2. [Modelo Document y Media Library](#modelo-document-y-media-library)
3. [Componentes Livewire](#componentes-livewire)
4. [Componente Document Card](#componente-document-card)
5. [Rutas](#rutas)
6. [Seeders](#seeders)
7. [Guía de Uso](#guía-de-uso)
8. [Tests](#tests)

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                     Layout Público                               │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Public Nav                                ││
│  │        [Documentos] activo cuando routeIs('documentos.*')     ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │              Livewire Component                              ││
│  │                                                              ││
│  │  Documents\Index          Documents\Show                     ││
│  │  ┌──────────────┐        ┌──────────────┐                 ││
│  │  │ x-ui.search  │        │ x-ui.bread   │                 ││
│  │  │ x-ui.section │        │ x-ui.section │                 ││
│  │  │ document-card │        │ document-card│                 ││
│  │  │ x-ui.empty   │        │ call-card    │                 ││
│  │  └──────────────┘        └──────────────┘                 ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                      Footer                                  ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

---

## Modelo Document y Media Library

### Configuración de Media Library

El modelo `Document` utiliza Spatie Media Library para gestionar archivos de documentos.

**Ubicación:** `app/Models/Document.php`

**Trait e Interfaz:**
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

**Colección de medios configurada:**

| Colección | Tipo | Descripción |
|-----------|------|-------------|
| `file` | Archivo (single file) | Archivo del documento (PDF, Word, Excel, etc.) |

**Tipos MIME aceptados:**
- `application/pdf`
- `application/msword`
- `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
- `application/vnd.ms-excel`
- `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- `application/vnd.ms-powerpoint`
- `application/vnd.openxmlformats-officedocument.presentationml.presentation`
- `text/plain`
- `text/csv`
- `image/jpeg`
- `image/png`
- `image/webp`

---

## Componentes Livewire

### Documents\Index

Listado público de documentos con filtros avanzados y búsqueda.

**Ubicación:** `app/Livewire/Public/Documents/Index.php`

**Propiedades públicas con URL binding:**

```php
#[Url(as: 'q')] public string $search = '';
#[Url(as: 'categoria')] public string $category = '';
#[Url(as: 'programa')] public string $program = '';
#[Url(as: 'ano')] public string $academicYear = '';
#[Url(as: 'tipo')] public string $documentType = '';
```

**Computed Properties:**

```php
#[Computed]
public function availableCategories(): Collection
{
    // Retorna categorías ordenadas por order y name
}

#[Computed]
public function availablePrograms(): Collection
{
    // Retorna programas activos ordenados por order y name
}

#[Computed]
public function availableAcademicYears(): Collection
{
    // Retorna años académicos ordenados por year desc
}

#[Computed]
public function availableDocumentTypes(): array
{
    // Retorna array asociativo de tipos de documento con sus etiquetas traducidas
    // ['convocatoria' => 'Convocatoria', 'modelo' => 'Modelo', ...]
}

#[Computed]
public function stats(): array
{
    return [
        'total' => (int) Total de documentos activos,
        'categories' => (int) Categorías con documentos,
        'total_downloads' => (int) Total de descargas,
    ];
}

#[Computed]
public function documents(): LengthAwarePaginator
{
    // Retorna documentos filtrados y paginados (12 por página)
    // Solo muestra: is_active = true
    // Orden: created_at desc (más recientes primero)
    // Eager loading: category, program, academicYear, creator
}
```

**Métodos públicos:**

| Método | Descripción |
|--------|-------------|
| `resetFilters()` | Limpia todos los filtros |
| `updatedSearch()` | Reset de paginación al cambiar búsqueda |
| `updatedCategory()` | Reset de paginación al cambiar categoría |
| `updatedProgram()` | Reset de paginación al cambiar programa |
| `updatedAcademicYear()` | Reset de paginación al cambiar año |
| `updatedDocumentType()` | Reset de paginación al cambiar tipo |

**Vista:** `resources/views/livewire/public/documents/index.blade.php`

**Secciones:**
1. Hero section con estadísticas (total, categorías, descargas totales)
2. Barra de filtros (búsqueda, categoría, programa, año académico, tipo)
3. Badges de filtros activos con opción de eliminar
4. Grid de documentos (3 columnas en desktop, responsive)
5. Paginación
6. CTA final

**Filtros disponibles:**
- **Búsqueda:** Título, descripción
- **Categoría:** Select con categorías disponibles
- **Programa:** Select con programas activos
- **Año académico:** Select con años disponibles
- **Tipo de documento:** Select con tipos disponibles (convocatoria, modelo, seguro, consentimiento, guía, FAQ, otro)

**Características especiales:**
- Primera tarjeta destacada (variante 'featured')
- Resto de tarjetas con variante 'default'
- Iconos según tipo de documento
- Contador de descargas visible

---

### Documents\Show

Detalle público de un documento con información completa.

**Ubicación:** `app/Livewire/Public/Documents/Show.php`

**Propiedad pública:**

```php
public Document $document;
```

**Validación en mount:**
- Solo muestra documentos con `is_active = true`
- Retorna 404 si no cumple condiciones

**Computed Properties:**

```php
#[Computed]
public function fileUrl(): ?string
{
    // URL del archivo desde Media Library
    return $this->document->getFirstMediaUrl('file');
}

#[Computed]
public function fileSize(): ?string
{
    // Tamaño del archivo formateado (KB, MB, etc.)
}

#[Computed]
public function fileMimeType(): ?string
{
    // Tipo MIME del archivo
}

#[Computed]
public function fileExtension(): ?string
{
    // Extensión del archivo
}

#[Computed]
public function fileName(): ?string
{
    // Nombre del archivo
}

#[Computed]
public function hasMediaConsent(): bool
{
    // Verifica si tiene consentimientos asociados
}

#[Computed]
public function mediaConsents(): Collection
{
    // Consentimientos de medios asociados (si aplica)
}

#[Computed]
public function relatedDocuments(): Collection
{
    // Documentos relacionados:
    // - Si el documento tiene categoría: muestra documentos de la misma categoría
    // - Si el documento no tiene categoría pero tiene programa: muestra documentos del mismo programa
    // - Excluye el documento actual
    // - Ordenados por created_at desc
    // - Límite: 3
}

#[Computed]
public function relatedCalls(): Collection
{
    // Convocatorias relacionadas del mismo programa
    // Solo si el documento tiene programa asociado
    // Solo abiertas/cerradas y publicadas
    // Orden: abiertas primero, luego por published_at desc
    // Límite: 3
}

#[Computed]
public function documentTypeConfig(): array
{
    // Configuración visual según tipo de documento
    // icon, color, label
}
```

**Métodos públicos:**

| Método | Tipo de Retorno | Descripción |
|--------|-----------------|-------------|
| `download()` | `BinaryFileResponse` | Descarga el archivo e incrementa el contador de descargas |

**Vista:** `resources/views/livewire/public/documents/show.blade.php`

**Secciones:**
1. Hero con gradiente Erasmus+ e icono según tipo de documento
2. Breadcrumbs
3. Badges (categoría, programa, año académico, tipo de documento)
4. Meta información (fecha creación, creador, contador de descargas)
5. Descripción completa
6. Información del archivo:
   - Nombre del archivo
   - Tamaño
   - Tipo MIME
   - Versión (si aplica)
   - Botón de descarga destacado
7. Información de consentimiento (si aplica):
   - Aviso si requiere consentimiento
   - Lista de consentimientos asociados (si hay)
8. Documentos relacionados (si existen)
9. Convocatorias relacionadas (si existen y el documento tiene programa)
10. CTA final

**Características especiales:**
- Hero adaptativo con icono según tipo de documento
- Información detallada del archivo
- Descarga funcional con incremento de contador
- Visualización de consentimientos cuando aplique
- Secciones relacionadas solo se muestran si hay contenido

---

## Componente Document Card

Componente reutilizable para mostrar documentos en formato card.

**Ubicación:** `resources/views/components/content/document-card.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `document` | Document\|null | `null` | Modelo Document |
| `title` | string\|null | `null` | Título del documento |
| `slug` | string\|null | `null` | Slug del documento |
| `description` | string\|null | `null` | Descripción |
| `category` | DocumentCategory\|null | `null` | Categoría |
| `program` | Program\|null | `null` | Programa |
| `academicYear` | AcademicYear\|null | `null` | Año académico |
| `documentType` | string\|null | `null` | Tipo de documento |
| `downloadCount` | int | `0` | Contador de descargas |
| `createdAt` | Carbon\|null | `null` | Fecha de creación |
| `updatedAt` | Carbon\|null | `null` | Fecha de actualización |
| `href` | string\|null | `null` | URL personalizada |
| `variant` | string | `'default'` | Variante: default, compact, featured, horizontal |
| `showCategory` | bool | `true` | Mostrar categoría |
| `showProgram` | bool | `true` | Mostrar programa |
| `showDownloadCount` | bool | `true` | Mostrar contador de descargas |
| `showDocumentType` | bool | `true` | Mostrar tipo de documento |

**Variantes:**

- **default**: Card estándar con icono y información básica
- **compact**: Card compacto con icono pequeño
- **featured**: Card destacado grande con icono grande
- **horizontal**: Card horizontal con icono a la izquierda

**Iconos por tipo de documento:**

| Tipo | Icono | Color |
|------|-------|-------|
| convocatoria | document-text | primary |
| modelo | document-duplicate | info |
| seguro | shield-check | success |
| consentimiento | clipboard-document-check | warning |
| guia | book-open | info |
| faq | question-mark-circle | info |
| otro | document | neutral |

---

## Rutas

**Archivo:** `routes/web.php`

```php
// Rutas públicas de documentos
Route::get('/documentos', Documents\Index::class)->name('documentos.index');
Route::get('/documentos/{document:slug}', Documents\Show::class)->name('documentos.show');
```

**Rutas disponibles:**

| Ruta | Método | Nombre | Descripción |
|------|--------|--------|-------------|
| `/documentos` | GET | `documentos.index` | Listado de documentos |
| `/documentos/{slug}` | GET | `documentos.show` | Detalle de documento |

**Route Model Binding:**
- Usa `slug` para la resolución del modelo
- Retorna 404 si el documento no existe o está inactivo

---

## Seeders

### DocumentsSeeder

**Ubicación:** `database/seeders/DocumentsSeeder.php`

**Características:**
- Crea 40+ documentos variados
- Diferentes categorías (Convocatorias, Modelos, Seguros, Consentimientos, Guías, FAQ, Otros)
- Diferentes programas (algunos sin programa)
- Diferentes años académicos (algunos sin año)
- Diferentes tipos de documento
- Archivos asociados mediante Media Library (70% de documentos tienen archivo)
- Contadores de descargas variados
- Algunos documentos inactivos (no se mostrarán en público)

**Estructura de datos:**
- Títulos realistas y descriptivos
- Descripciones generadas
- Versiones cuando aplica
- Creadores asignados
- Fechas de creación distribuidas

**Ejecutar:**
```bash
php artisan db:seed --class=DocumentsSeeder
```

**Nota:** El seeder crea archivos temporales para asociarlos mediante Media Library. Los archivos se limpian después de asociarlos.

---

## Guía de Uso

### Obtener archivo de un documento

```php
$document = Document::find(1);

// Obtener URL del archivo
$fileUrl = $document->getFirstMediaUrl('file');

// Obtener path del archivo
$filePath = $document->getFirstMediaPath('file');

// Verificar si tiene archivo
if ($document->hasMedia('file')) {
    // Tiene archivo asociado
}

// Obtener media
$media = $document->getFirstMedia('file');
$fileName = $media->file_name;
$fileSize = $media->size;
$mimeType = $media->mime_type;
```

### Añadir archivo a un documento

```php
$document = Document::find(1);

// Desde archivo subido
$document->addMediaFromRequest('file')
    ->usingName('Nombre del Documento')
    ->usingFileName('documento.pdf')
    ->toMediaCollection('file');

// Desde path local
$document->addMediaFromPath('/path/to/file.pdf')
    ->usingName('Nombre del Documento')
    ->usingFileName('documento.pdf')
    ->toMediaCollection('file');

// Desde URL
$document->addMediaFromUrl('https://example.com/documento.pdf')
    ->usingName('Nombre del Documento')
    ->usingFileName('documento.pdf')
    ->toMediaCollection('file');
```

### Filtrar documentos

```php
// En un componente Livewire
public function documents(): LengthAwarePaginator
{
    return Document::query()
        ->with(['category', 'program', 'academicYear', 'creator'])
        ->where('is_active', true)
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        })
        ->when($this->category, fn ($query) => $query->where('category_id', $this->category))
        ->when($this->program, fn ($query) => $query->where('program_id', $this->program))
        ->when($this->academicYear, fn ($query) => $query->where('academic_year_id', $this->academicYear))
        ->when($this->documentType, fn ($query) => $query->where('document_type', $this->documentType))
        ->orderBy('created_at', 'desc')
        ->paginate(12);
}
```

### Descargar documento

```php
// En el componente Show
use Symfony\Component\HttpFoundation\BinaryFileResponse;

public function download(): BinaryFileResponse
{
    $media = $this->document->getFirstMedia('file');

    if (! $media) {
        abort(404, __('Archivo no encontrado'));
    }

    // Incrementar contador de descargas
    $this->document->increment('download_count');

    // Retornar respuesta de descarga
    return response()->download(
        $media->getPath(),
        $media->file_name,
        [
            'Content-Type' => $media->mime_type,
        ]
    );
}
```

---

## Tests

### IndexTest

**Ubicación:** `tests/Feature/Livewire/Public/Documents/IndexTest.php`

**Tests incluidos:**
- Renderizado del componente
- Solo muestra documentos activos
- Búsqueda por título
- Búsqueda por descripción
- Filtro por categoría
- Filtro por programa
- Filtro por año académico
- Filtro por tipo de documento
- Reset de filtros
- Empty state
- Estadísticas
- Paginación
- Reset de paginación al cambiar filtros
- SEO title y description
- Breadcrumbs
- Enlaces a página de detalle
- Orden por fecha de creación descendente

### ShowTest

**Ubicación:** `tests/Feature/Livewire/Public/Documents/ShowTest.php`

**Tests incluidos:**
- Renderizado del componente
- Muestra información del documento
- Muestra información del creador
- Muestra contador de descargas
- Muestra badge de tipo de documento
- Muestra versión cuando está disponible
- Retorna 404 para documentos inactivos
- Muestra información del archivo cuando está adjunto
- Muestra mensaje cuando no hay archivo
- Incrementa contador de descargas al descargar
- Muestra documentos relacionados de la misma categoría
- Muestra documentos relacionados del mismo programa
- Muestra convocatorias relacionadas cuando aplica
- No muestra convocatorias relacionadas cuando no hay programa
- Excluye documento actual de documentos relacionados
- SEO title y description
- Breadcrumbs
- Maneja documentos sin descripción
- Maneja documentos sin programa
- Maneja documentos sin año académico
- Limita documentos relacionados a 3
- Retorna 404 al intentar descargar archivo inexistente
- Formatea tamaño de archivo correctamente

### DocumentsRoutesTest

**Ubicación:** `tests/Feature/Routes/DocumentsRoutesTest.php`

**Tests incluidos:**
- Acceso a ruta de listado
- Acceso a ruta de detalle con slug
- Retorna 404 para documento inactivo
- Retorna 404 para slug inexistente
- Usa slug para route model binding

---

## Notas Importantes

### Media Library
- Los archivos se almacenan en la colección `file`
- Solo un archivo por documento (singleFile)
- Los archivos se almacenan en el disco configurado en `config/filesystems.php`

### Seguridad
- Solo se muestran documentos activos (`is_active = true`)
- La descarga incrementa el contador automáticamente
- Los documentos inactivos retornan 404

### Performance
- Eager loading de relaciones: `category`, `program`, `academicYear`, `creator`
- Paginación de 12 documentos por página
- Índices en base de datos: `['category_id', 'program_id', 'is_active']`

### Diseño
- Diseño responsive (móvil, tablet, desktop)
- Soporte para dark mode
- Iconos según tipo de documento
- Badges de color según tipo

### Accesibilidad
- Etiquetas semánticas correctas
- Textos alternativos para iconos
- Navegación por teclado
- Contraste adecuado

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: ✅ Completado - Implementación finalizada

