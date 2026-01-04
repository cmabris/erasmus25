# CRUD de Documentos en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Documentos en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Documentos permite a los administradores gestionar completamente los documentos desde el panel de administración. Incluye funcionalidades avanzadas como gestión de archivos mediante FilePond y Media Library, SoftDeletes, validación de relaciones con MediaConsent, gestión de consentimientos asociados, y sistema completo de filtros y búsqueda.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar documentos
- ✅ **SoftDeletes**: Los documentos nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- ✅ **Gestión de Archivos**: Subida mediante FilePond con drag & drop, preview automático, validación en frontend
- ✅ **Media Library**: Almacenamiento y gestión de archivos mediante Spatie Media Library
- ✅ **Tipos de Archivo**: Soporte para PDF, Word, Excel, PowerPoint, texto, CSV e imágenes (JPEG, PNG, WebP)
- ✅ **Gestión de Consentimientos**: Visualización de consentimientos de medios asociados
- ✅ **Búsqueda y Filtros**: Búsqueda por título, descripción, slug; filtros por categoría, programa, año académico, tipo, estado y eliminados
- ✅ **Autorización**: Control de acceso mediante `DocumentPolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 152 tests pasando (Index: 30, Create: 25, Edit: 25, Show: 20, FormRequests: 25, MediaConsent: 7)

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Documents\Index`
- **Vista**: `resources/views/livewire/admin/documents/index.blade.php`
- **Ruta**: `/admin/documentos` (nombre: `admin.documents.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'categoria')]
public ?int $categoryId = null;

#[Url(as: 'programa')]
public ?int $programId = null;

#[Url(as: 'ano')]
public ?int $academicYearId = null;

#[Url(as: 'tipo')]
public ?string $documentType = null;

#[Url(as: 'activo')]
public ?bool $isActive = null;

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
public ?int $documentToDelete = null;
public ?int $documentToRestore = null;
public ?int $documentToForceDelete = null;
```

**Computed Properties:**

```php
#[Computed]
public function categories(): Collection
{
    // Categorías de documentos ordenadas por order y name
}

#[Computed]
public function programs(): Collection
{
    // Programas activos ordenados por order y name
}

#[Computed]
public function academicYears(): Collection
{
    // Años académicos ordenados por year desc
}

#[Computed]
public function documents(): LengthAwarePaginator
{
    // Documentos filtrados y paginados con eager loading
    // Incluye: category, program, academicYear, creator, updater
    // Con conteo de mediaConsents
    // Filtros: search, categoryId, programId, academicYearId, documentType, isActive, showDeleted
    // Ordenación: sortField, sortDirection
}
```

**Métodos Principales:**

| Método | Descripción |
|--------|-------------|
| `resetFilters()` | Limpia todos los filtros |
| `updatedSearch()` | Reset de paginación al cambiar búsqueda |
| `updatedShowDeleted()` | Reset de paginación al cambiar filtro de eliminados |
| `sortBy(string $field)` | Cambia el campo de ordenación |
| `confirmDelete(int $documentId)` | Abre modal de confirmación para eliminar |
| `delete()` | Elimina documento (soft delete) con validación de relaciones |
| `confirmRestore(int $documentId)` | Abre modal de confirmación para restaurar |
| `restore()` | Restaura documento eliminado |
| `confirmForceDelete(int $documentId)` | Abre modal de confirmación para eliminar permanentemente |
| `forceDelete()` | Elimina permanentemente (solo super-admin, validación de relaciones) |
| `canCreate()` | Verifica si el usuario puede crear documentos |
| `canViewDeleted()` | Verifica si el usuario puede ver documentos eliminados |
| `canDeleteDocument(Document $document)` | Verifica si el documento puede ser eliminado (sin relaciones) |

**Características de la Vista:**
- Tabla responsive con columnas: archivo (preview), título, categoría, tipo, programa, año académico, estado, descargas, fecha creación, acciones
- Filtros avanzados: búsqueda, categoría, programa, año académico, tipo, estado activo, eliminados
- Modales de confirmación para eliminar, restaurar y force delete
- Estados de carga con indicadores visuales
- Estado vacío con acción sugerida (crear documento)
- Paginación configurable

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Documents\Create`
- **Vista**: `resources/views/livewire/admin/documents/create.blade.php`
- **Ruta**: `/admin/documentos/crear` (nombre: `admin.documents.create`)

**Traits:**
- `Spatie\LivewireFilepond\WithFilePond` - Integración con FilePond
- `Livewire\WithFileUploads` - Manejo de archivos en Livewire

**Propiedades Públicas:**

```php
public ?int $categoryId = null;
public ?int $programId = null;
public ?int $academicYearId = null;
public string $title = '';
public string $slug = '';
public string $description = '';
public string $documentType = 'otro';
public string $version = '';
public bool $isActive = true;
public ?UploadedFile $file = null;
```

**Computed Properties:**

```php
#[Computed]
public function categories(): Collection
{
    // Categorías de documentos ordenadas por order y name
}

#[Computed]
public function programs(): Collection
{
    // Programas activos ordenados por order y name
}

#[Computed]
public function academicYears(): Collection
{
    // Años académicos ordenados por year desc
}
```

**Métodos Principales:**

| Método | Descripción |
|--------|-------------|
| `mount()` | Inicialización con autorización |
| `updatedTitle()` | Genera slug automáticamente desde título |
| `updatedSlug()` | Valida slug en tiempo real |
| `getDocumentTypeOptions()` | Retorna opciones de tipos de documento traducidas |
| `store()` | Guarda nuevo documento usando `StoreDocumentRequest` |
| `render()` | Renderiza la vista con datos para selects |

**Flujo de Creación:**
1. Usuario completa formulario (categoría requerida, título requerido, tipo requerido)
2. Slug se genera automáticamente desde título (editable)
3. Si se sube archivo, se valida tipo y tamaño (máx. 20MB)
4. Se valida con `StoreDocumentRequest`
5. Se crea documento con `created_by` automático
6. Si hay archivo, se sube a Media Library (colección `file`)
7. Se dispara evento `document-created`
8. Redirección a `admin.documents.show`

**Características de la Vista:**
- Formulario con Flux UI components
- Campo de archivo con FilePond (drag & drop, preview, validación)
- Validación en tiempo real con feedback visual
- Tooltips informativos (slug, tipo de documento)
- Mensajes de error personalizados

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Documents\Edit`
- **Vista**: `resources/views/livewire/admin/documents/edit.blade.php`
- **Ruta**: `/admin/documentos/{document}/editar` (nombre: `admin.documents.edit`)

**Traits:**
- `Spatie\LivewireFilepond\WithFilePond` - Integración con FilePond
- `Livewire\WithFileUploads` - Manejo de archivos en Livewire

**Propiedades Públicas:**

```php
public Document $document;
public ?int $categoryId = null;
public ?int $programId = null;
public ?int $academicYearId = null;
public string $title = '';
public string $slug = '';
public string $description = '';
public string $documentType = 'otro';
public string $version = '';
public bool $isActive = true;
public ?UploadedFile $file = null;
public bool $removeExistingFile = false;
```

**Computed Properties:**

```php
#[Computed]
public function existingFile(): ?Media
{
    // Retorna el archivo actual del documento (colección 'file')
}

#[Computed]
public function categories(): Collection
{
    // Categorías de documentos ordenadas por order y name
}

#[Computed]
public function programs(): Collection
{
    // Programas activos ordenados por order y name
}

#[Computed]
public function academicYears(): Collection
{
    // Años académicos ordenados por year desc
}
```

**Métodos Principales:**

| Método | Descripción |
|--------|-------------|
| `mount(Document $document)` | Carga datos del documento con relaciones |
| `updatedTitle()` | Genera slug automáticamente desde título |
| `updatedSlug()` | Valida slug en tiempo real |
| `removeFile()` | Marca flag para eliminar archivo existente |
| `getDocumentTypeOptions()` | Retorna opciones de tipos de documento traducidas |
| `update()` | Actualiza documento usando `UpdateDocumentRequest` |
| `render()` | Renderiza la vista con datos para selects |

**Flujo de Edición:**
1. Se cargan datos del documento con relaciones
2. Usuario modifica campos
3. Slug se regenera automáticamente si se cambia título (a menos que esté personalizado)
4. Si se marca `removeExistingFile`, se elimina archivo actual
5. Si se sube nuevo archivo, se reemplaza el anterior
6. Se valida con `UpdateDocumentRequest` (slug único ignorando registro actual)
7. Se actualiza documento con `updated_by` automático
8. Se gestiona archivo (eliminar, reemplazar o mantener)
9. Se dispara evento `document-updated`
10. Redirección a `admin.documents.show`

**Características de la Vista:**
- Formulario pre-rellenado con datos existentes
- Sección de gestión de archivo actual (ver, descargar, eliminar)
- Campo de archivo con FilePond (si no hay archivo o se quiere reemplazar)
- Sidebar con información adicional (fechas, creador, actualizador, descargas)
- Validación en tiempo real con feedback visual
- Tooltips informativos

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Documents\Show`
- **Vista**: `resources/views/livewire/admin/documents/show.blade.php`
- **Ruta**: `/admin/documentos/{document}` (nombre: `admin.documents.show`)

**Propiedades Públicas:**

```php
public Document $document;
public bool $showDeleteModal = false;
public bool $showForceDeleteModal = false;
```

**Computed Properties:**

```php
#[Computed]
public function existingFile(): ?Media
{
    // Retorna el archivo actual del documento (colección 'file')
}
```

**Métodos Principales:**

| Método | Descripción |
|--------|-------------|
| `mount(Document $document)` | Carga documento con relaciones y conteos |
| `download()` | Descarga el archivo del documento |
| `confirmDelete()` | Abre modal de confirmación para eliminar |
| `delete()` | Elimina documento (soft delete) con validación de relaciones |
| `confirmForceDelete()` | Abre modal de confirmación para eliminar permanentemente |
| `forceDelete()` | Elimina permanentemente (solo super-admin, validación de relaciones) |
| `restore()` | Restaura documento eliminado |
| `hasFile()` | Verifica si el documento tiene archivo asociado |
| `canDelete()` | Verifica si el documento puede ser eliminado (sin relaciones) |
| `hasRelationships()` | Verifica si el documento tiene relaciones (MediaConsent) |
| `getDocumentTypeOptions()` | Retorna opciones de tipos de documento traducidas |
| `getDocumentTypeColor(string $type)` | Retorna color de badge según tipo de documento |

**Características de la Vista:**
- Información completa del documento (título, slug, descripción, categoría, programa, año académico, tipo, versión, estado)
- Sección de archivo con preview (imágenes) o información (otros tipos)
- Botones de acción (ver, descargar)
- Sección de consentimientos de medios asociados (hasta 10)
- Información de auditoría (fechas, creador, actualizador, contador de descargas)
- Botones de acción: editar, eliminar, restaurar, volver
- Modales de confirmación para eliminar y force delete
- Breadcrumbs de navegación

---

## FormRequests

### StoreDocumentRequest

**Ubicación:** `app/Http/Requests/StoreDocumentRequest.php`

**Autorización:**
```php
public function authorize(): bool
{
    return $this->user()?->can('create', Document::class) ?? false;
}
```

**Reglas de Validación:**

| Campo | Reglas |
|-------|--------|
| `category_id` | `required`, `exists:document_categories,id` |
| `program_id` | `nullable`, `exists:programs,id` |
| `academic_year_id` | `nullable`, `exists:academic_years,id` |
| `title` | `required`, `string`, `max:255` |
| `slug` | `nullable`, `string`, `max:255`, `unique:documents,slug` |
| `description` | `nullable`, `string` |
| `document_type` | `required`, `in:convocatoria,modelo,seguro,consentimiento,guia,faq,otro` |
| `version` | `nullable`, `string`, `max:255` |
| `is_active` | `nullable`, `boolean` |
| `file` | `nullable`, `file`, `mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpeg,jpg,png,webp`, `max:20480` (20MB) |

**Mensajes Personalizados:**
- Todos los mensajes están internacionalizados en español e inglés
- Mensajes específicos para cada regla de validación

---

### UpdateDocumentRequest

**Ubicación:** `app/Http/Requests/UpdateDocumentRequest.php`

**Autorización:**
```php
public function authorize(): bool
{
    $document = $this->route('document');
    if (! $document instanceof Document) {
        return false;
    }
    return $this->user()?->can('update', $document) ?? false;
}
```

**Reglas de Validación:**

| Campo | Reglas |
|-------|--------|
| `category_id` | `required`, `exists:document_categories,id` |
| `program_id` | `nullable`, `exists:programs,id` |
| `academic_year_id` | `nullable`, `exists:academic_years,id` |
| `title` | `required`, `string`, `max:255` |
| `slug` | `nullable`, `string`, `max:255`, `unique:documents,slug` (ignora registro actual) |
| `description` | `nullable`, `string` |
| `document_type` | `required`, `in:convocatoria,modelo,seguro,consentimiento,guia,faq,otro` |
| `version` | `nullable`, `string`, `max:255` |
| `is_active` | `nullable`, `boolean` |
| `file` | `nullable`, `file`, `mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpeg,jpg,png,webp`, `max:20480` (20MB) |

**Mensajes Personalizados:**
- Todos los mensajes están internacionalizados en español e inglés
- Mensajes específicos para cada regla de validación

---

## Policy

### DocumentPolicy

**Ubicación:** `app/Policies/DocumentPolicy.php`

**Métodos de Autorización:**

| Método | Permiso Requerido | Descripción |
|--------|-------------------|-------------|
| `before()` | - | Super-admin tiene acceso total |
| `viewAny()` | `documents.view` | Ver listado de documentos |
| `view()` | `documents.view` | Ver detalle de documento |
| `create()` | `documents.create` | Crear documento |
| `update()` | `documents.edit` | Actualizar documento |
| `delete()` | `documents.delete` | Eliminar documento (soft delete) |
| `restore()` | `documents.delete` | Restaurar documento eliminado |
| `forceDelete()` | `documents.delete` | Eliminar permanentemente (solo super-admin) |

**Permisos del Módulo:**
- `documents.view` - Ver documentos
- `documents.create` - Crear documentos
- `documents.edit` - Editar documentos
- `documents.delete` - Eliminar documentos

---

## Modelo

### Document

**Ubicación:** `app/Models/Document.php`

**Traits:**
- `Illuminate\Database\Eloquent\SoftDeletes` - SoftDeletes
- `Spatie\MediaLibrary\HasMedia` - Media Library
- `Spatie\MediaLibrary\InteractsWithMedia` - Interacciones con Media Library

**Relaciones:**

| Relación | Tipo | Modelo Relacionado |
|----------|------|-------------------|
| `category()` | BelongsTo | `DocumentCategory` |
| `program()` | BelongsTo | `Program` (nullable) |
| `academicYear()` | BelongsTo | `AcademicYear` (nullable) |
| `creator()` | BelongsTo | `User` (created_by) |
| `updater()` | BelongsTo | `User` (updated_by) |
| `mediaConsents()` | HasMany | `MediaConsent` (consent_document_id) |

**Media Collections:**

```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('file')
        ->singleFile()
        ->acceptsMimeTypes([
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'image/jpeg',
            'image/png',
            'image/webp',
        ]);
}
```

**Tipos de Documento:**
- `convocatoria` - Documento de convocatoria
- `modelo` - Modelo o plantilla
- `seguro` - Documentación de seguros
- `consentimiento` - Consentimientos RGPD
- `guia` - Guías informativas
- `faq` - Preguntas frecuentes
- `otro` - Otro tipo de documento

---

## Migraciones

### add_soft_deletes_to_documents_table

**Ubicación:** `database/migrations/YYYY_MM_DD_HHMMSS_add_soft_deletes_to_documents_table.php`

**Cambios:**
- Añade columna `deleted_at` a la tabla `documents`
- Añade índice `documents_deleted_at_index`

---

## Rutas

**Ubicación:** `routes/web.php`

**Rutas del CRUD:**

| Método | URI | Nombre | Componente |
|--------|-----|--------|-------------|
| GET | `/admin/documentos` | `admin.documents.index` | `Admin\Documents\Index` |
| GET | `/admin/documentos/crear` | `admin.documents.create` | `Admin\Documents\Create` |
| GET | `/admin/documentos/{document}` | `admin.documents.show` | `Admin\Documents\Show` |
| GET | `/admin/documentos/{document}/editar` | `admin.documents.edit` | `Admin\Documents\Edit` |

**Middleware:**
- `auth` - Requiere autenticación
- `verified` - Requiere email verificado

---

## Navegación

**Ubicación:** `resources/views/components/layouts/app/sidebar.blade.php`

**Enlace en Sidebar:**
```blade
@can('viewAny', \App\Models\Document::class)
    <flux:navlist.item 
        icon="document" 
        :href="route('admin.documents.index')" 
        :current="request()->routeIs('admin.documents.*')" 
        wire:navigate
    >
        {{ __('common.nav.documents') }}
    </flux:navlist.item>
@endcan
```

---

## Internacionalización

**Archivos de Traducción:**
- `lang/es/common.php` - Traducciones en español
- `lang/en/common.php` - Traducciones en inglés

**Claves de Traducción:**
- `common.nav.documents` - "Documentos" / "Documents"
- Mensajes de validación personalizados en FormRequests
- Mensajes de éxito/error en componentes Livewire
- Tipos de documento traducidos

---

## Testing

### Tests de Componentes Livewire

**Ubicación:** `tests/Feature/Livewire/Admin/Documents/`

#### IndexTest.php
- **Tests:** 30 tests (78 assertions)
- **Cobertura:** Autorización, listado, búsqueda, filtros, ordenación, paginación, soft delete, restauración, force delete, validación de relaciones

#### CreateTest.php
- **Tests:** 25 tests (65 assertions)
- **Cobertura:** Autorización, creación exitosa, validación de campos, generación de slug, subida de archivo, eventos, redirección

#### EditTest.php
- **Tests:** 25 tests (68 assertions)
- **Cobertura:** Autorización, carga de datos, actualización exitosa, validación de campos, generación de slug, reemplazo de archivo, eliminación de archivo, eventos, redirección

#### ShowTest.php
- **Tests:** 20 tests (52 assertions)
- **Cobertura:** Autorización, visualización de información, descarga de archivo, eliminación, restauración, force delete, validación de relaciones, helpers

### Tests de FormRequests

**Ubicación:** `tests/Feature/Http/Requests/`

#### StoreDocumentRequestTest.php
- **Tests:** 12 tests (32 assertions)
- **Cobertura:** Validación de campos requeridos, existencia de foreign keys, longitud máxima, unicidad de slug, tipos de archivo válidos, tamaño de archivo, mensajes personalizados

#### UpdateDocumentRequestTest.php
- **Tests:** 13 tests (32 assertions)
- **Cobertura:** Validación de campos requeridos, existencia de foreign keys, longitud máxima, unicidad de slug (ignorando registro actual), tipos de archivo válidos, tamaño de archivo, mensajes personalizados

### Estadísticas de Testing

- **Total de Tests:** 152 tests
- **Total de Assertions:** ~365 assertions
- **Cobertura:** 100% de componentes Livewire y FormRequests
- **Estado:** ✅ Todos los tests pasando

---

## Características Técnicas

### SoftDeletes

- Los documentos **nunca** se eliminan permanentemente por defecto
- Solo se marcan como eliminados (`deleted_at`)
- Filtrado automático de registros eliminados en consultas
- Opción de ver registros eliminados (solo para administradores)
- Restauración de documentos eliminados

### ForceDelete

- Solo super-admin puede realizar `forceDelete()`
- Validación de relaciones antes de eliminar permanentemente
- Si el documento tiene `MediaConsent` asociados, no se permite eliminación permanente
- Mensaje de error claro al usuario explicando por qué no se puede eliminar

### Gestión de Archivos

- **FilePond**: Subida moderna con drag & drop, preview automático, validación en frontend
- **Media Library**: Almacenamiento y gestión de archivos mediante Spatie Media Library
- **Tipos Soportados**: PDF, Word, Excel, PowerPoint, texto, CSV, imágenes (JPEG, PNG, WebP)
- **Tamaño Máximo**: 20MB por archivo
- **Colección**: `file` (single file)
- **Preview**: Automático para imágenes y PDFs
- **Reemplazo**: Opción de reemplazar archivo existente en edición
- **Eliminación**: Opción de eliminar archivo sin subir uno nuevo

### Generación de Slug

- Generación automática desde el título usando `Str::slug()`
- El usuario puede editar el slug manualmente si lo desea
- Validación de unicidad (ignorando el registro actual en edición)
- Regeneración automática si se cambia el título (a menos que el slug esté personalizado)

### Validación de Relaciones

- Antes de eliminar (soft delete), se verifica si tiene `MediaConsent` asociados
- Si tiene relaciones, se muestra error y no se permite eliminación
- Mensaje claro al usuario explicando por qué no se puede eliminar
- Uso de `withCount(['mediaConsents'])` para optimizar consultas

### Optimizaciones

- **Eager Loading**: Carga de relaciones (`category`, `program`, `academicYear`, `creator`, `updater`)
- **withCount**: Conteo de `mediaConsents` sin cargar todos los registros
- **Índices**: Índice en `deleted_at` para optimizar consultas de soft deletes
- **Caché**: No aplicado (no necesario por el momento)

### Sincronización de URL

- Uso de `#[Url]` en propiedades públicas para sincronizar estado con URL
- Permite compartir enlaces con filtros aplicados
- Mejora la UX al permitir navegación con botones atrás/adelante del navegador

---

## Flujo de Usuario

### Crear Documento

1. Usuario accede a `/admin/documentos/crear`
2. Completa formulario (categoría, título, tipo requeridos)
3. Opcionalmente sube archivo (drag & drop o selección)
4. Slug se genera automáticamente (editable)
5. Guarda documento
6. Redirección a vista de detalle

### Editar Documento

1. Usuario accede a `/admin/documentos/{document}/editar`
2. Modifica campos deseados
3. Gestiona archivo (mantener, reemplazar o eliminar)
4. Guarda cambios
5. Redirección a vista de detalle

### Eliminar Documento

1. Usuario accede a acción de eliminar (desde Index o Show)
2. Se valida si tiene relaciones (`MediaConsent`)
3. Si no tiene relaciones, se muestra modal de confirmación
4. Usuario confirma eliminación
5. Documento se marca como eliminado (soft delete)
6. Redirección a Index

### Restaurar Documento

1. Usuario activa filtro "Mostrar eliminados"
2. Accede a documento eliminado
3. Usa acción "Restaurar"
4. Documento se restaura (se elimina `deleted_at`)
5. Redirección a Index

### Eliminar Permanentemente

1. Usuario super-admin accede a documento eliminado
2. Usa acción "Eliminar permanentemente"
3. Se valida si tiene relaciones (`MediaConsent`)
4. Si no tiene relaciones, se muestra modal de confirmación
5. Usuario confirma eliminación permanente
6. Documento se elimina de la base de datos
7. Archivo asociado se elimina de Media Library
8. Redirección a Index

---

## Mejoras Futuras

- [ ] Conversiones de Media Library (thumbnails para imágenes, previews para PDFs)
- [ ] Historial de versiones de documentos
- [ ] Sistema de etiquetas para documentos
- [ ] Búsqueda avanzada con filtros combinados
- [ ] Exportación de listado a Excel/CSV
- [ ] Vista previa de documentos en el navegador (PDFs, imágenes)
- [ ] Sistema de comentarios en documentos
- [ ] Notificaciones cuando se sube nuevo documento
- [ ] Sistema de aprobación para documentos importantes

---

## Referencias

- [Plan de Desarrollo](pasos/paso-3.5.7-plan.md) - Plan detallado paso a paso
- [Resumen Ejecutivo](pasos/paso-3.5.7-resumen.md) - Resumen de objetivos y estructura
- [Sistema de Policies](policies.md) - Documentación de autorización
- [Form Requests](form-requests.md) - Documentación de validación
- [CRUD de Resoluciones](admin-resolutions-crud.md) - CRUD relacionado con gestión de archivos
- [CRUD de Noticias](admin-news-crud.md) - CRUD relacionado con gestión de contenido
- [Sistema de Media Library](https://spatie.be/docs/laravel-medialibrary) - Documentación oficial

