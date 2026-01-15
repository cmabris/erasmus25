# CRUD de Resoluciones en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Resoluciones en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Resoluciones permite a los administradores gestionar completamente las resoluciones asociadas a las convocatorias y fases Erasmus+ desde el panel de administración. Las resoluciones están anidadas bajo sus convocatorias padre, reflejando la relación jerárquica entre estos recursos.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar resoluciones
- ✅ **Rutas Anidadas**: Las resoluciones están bajo `/admin/convocatorias/{call}/resoluciones`
- ✅ **SoftDeletes**: Las resoluciones nunca se eliminan permanentemente por defecto
- ✅ **Gestión de PDFs**: Subida y gestión de archivos PDF mediante Laravel Media Library y FilePond
- ✅ **Publicación**: Sistema de publicación/despublicación de resoluciones
- ✅ **Tipos de Resolución**: Gestión de tipos (provisional, definitivo, alegaciones)
- ✅ **Validación de Relaciones**: Validación de que la fase pertenece a la convocatoria
- ✅ **Búsqueda y Filtros**: Búsqueda por título/descripción, filtros por tipo, estado de publicación y fase
- ✅ **Autorización**: Control de acceso mediante `ResolutionPolicy`
- ✅ **Validación en Tiempo Real**: Validación de campos clave mientras el usuario escribe
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Optimizaciones**: Eager loading, consultas optimizadas, estados de carga

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Resolutions\Index`
- **Vista**: `resources/views/livewire/admin/calls/resolutions/index.blade.php`
- **Ruta**: `/admin/convocatorias/{call}/resoluciones` (nombre: `admin.calls.resolutions.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'tipo')]
public string $filterType = '';

#[Url(as: 'publicada')]
public string $filterPublished = '';

#[Url(as: 'fase')]
public string $filterPhase = '';

#[Url(as: 'eliminados')]
public string $showDeleted = '0';

#[Url(as: 'ordenar')]
public string $sortField = 'official_date';

#[Url(as: 'direccion')]
public string $sortDirection = 'desc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;
```

**Métodos Principales:**

- `resolutions()` - Computed property que retorna las resoluciones paginadas y filtradas
- `sortBy($field)` - Cambiar ordenación
- `publish($resolutionId)` - Publicar resolución
- `unpublish($resolutionId)` - Despublicar resolución
- `confirmDelete($resolutionId)` - Confirmar eliminación
- `delete()` - Eliminar resolución (soft delete)
- `confirmRestore($resolutionId)` - Confirmar restauración
- `restore()` - Restaurar resolución eliminada
- `confirmForceDelete($resolutionId)` - Confirmar eliminación permanente
- `forceDelete()` - Eliminar resolución permanentemente
- `resetFilters()` - Limpiar todos los filtros
- `hasPdf($resolution)` - Verificar si la resolución tiene PDF (optimizado con eager loading)
- `getTypeColor($type)` - Obtener color del badge según tipo
- `getTypeOptions()` - Obtener opciones de tipos de resolución

**Características:**

- Búsqueda por título y descripción
- Filtros por tipo de resolución, estado de publicación, fase y mostrar eliminados
- Ordenación por campo configurable (title, official_date, created_at)
- Paginación configurable (15 por defecto)
- Tabla responsive con Flux UI
- Botones de acción: ver, editar, eliminar, restaurar, publicar/despublicar
- Modales de confirmación para acciones destructivas
- Estado vacío con componente `x-ui.empty-state`
- Breadcrumbs con `x-ui.breadcrumbs`
- Eager loading de relaciones (`call`, `callPhase`, `creator`, `media`) para evitar N+1 queries
- Indicadores de carga con `wire:loading`
- Card informativo con datos de la convocatoria

---

#### `export()`
Exporta las resoluciones filtradas de la convocatoria a Excel.

**Autorización:**
- Verifica permiso `CALLS_VIEW` mediante `ResolutionPolicy::viewAny()`

**Funcionalidad:**
- Recolecta todos los filtros actuales del componente, incluyendo el `call_id` (obligatorio)
- Genera nombre de archivo con slug de convocatoria y timestamp: `resoluciones-{slug-convocatoria}-YYYY-MM-DD-HHMMSS.xlsx`
- Utiliza `ResolutionsExport` para generar el archivo Excel
- Aplica los mismos filtros que el listado (búsqueda, tipo, publicado, fase, eliminados, ordenación)
- Solo exporta resoluciones de la convocatoria actual

**Uso:**
El botón de exportación está disponible en la vista cuando el usuario tiene permisos para ver resoluciones eliminadas (`canViewDeleted()`).

**Formato del Archivo:**
- Formato: XLSX (Excel 2007+)
- Columnas: ID, Título, Convocatoria, Fase, Tipo, Descripción, Procedimiento de Evaluación, Fecha Oficial, Publicado, Fechas, Creador, etc.
- Estilos: Encabezados en negrita
- Traducciones: Datos traducidos al idioma actual del usuario
- Truncado: Descripciones y procedimientos truncados a 200 caracteres para legibilidad

**Ver también:** [Sistema de Exportación](exports-system.md)

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Resolutions\Create`
- **Vista**: `resources/views/livewire/admin/calls/resolutions/create.blade.php`
- **Ruta**: `/admin/convocatorias/{call}/resoluciones/crear` (nombre: `admin.calls.resolutions.create`)

**Propiedades Públicas:**

```php
public Call $call;
public int $call_id;
public ?int $call_phase_id = null;
public string $type = 'provisional';
public string $title = '';
public ?string $description = null;
public ?string $evaluation_procedure = null;
public ?string $official_date = null;
public ?string $published_at = null;
public ?UploadedFile $pdfFile = null;
```

**Métodos Principales:**

- `mount(Call $call, ?int $call_phase_id = null)` - Inicializar componente
- `updatedCallPhaseId()` - Validar que la fase pertenece a la convocatoria
- `save()` - Guardar nueva resolución
- `callPhases()` - Computed property con fases de la convocatoria
- `getTypeOptions()` - Obtener opciones de tipos de resolución

**Características:**

- Formulario completo con validación en tiempo real
- Select de fase con validación de pertenencia a convocatoria
- Select de tipo de resolución (provisional, definitivo, alegaciones)
- Campos de texto para título, descripción y procedimiento de evaluación
- Campos de fecha para fecha oficial y fecha de publicación
- Integración con FilePond para subida de PDFs
- Validación de archivo PDF (tipo y tamaño máximo 10MB)
- Establecimiento automático de `created_by` con usuario autenticado
- Guardado de PDF en colección 'resolutions' de Media Library
- Notificaciones toast de éxito/error
- Sidebar con información de la convocatoria
- Breadcrumbs con navegación completa

**Validaciones:**

- `call_phase_id`: Requerido, debe existir y pertenecer a la convocatoria
- `type`: Requerido, debe ser uno de: provisional, definitivo, alegaciones
- `title`: Requerido, máximo 255 caracteres
- `official_date`: Requerido, debe ser una fecha válida
- `published_at`: Opcional, debe ser una fecha válida
- `pdfFile`: Opcional, debe ser PDF, máximo 10MB

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Resolutions\Edit`
- **Vista**: `resources/views/livewire/admin/calls/resolutions/edit.blade.php`
- **Ruta**: `/admin/convocatorias/{call}/resoluciones/{resolution}/editar` (nombre: `admin.calls.resolutions.edit`)

**Propiedades Públicas:**

```php
public Call $call;
public Resolution $resolution;
public int $call_id;
public ?int $call_phase_id = null;
public string $type = 'provisional';
public string $title = '';
public ?string $description = null;
public ?string $evaluation_procedure = null;
public ?string $official_date = null;
public ?string $published_at = null;
public ?UploadedFile $pdfFile = null;
public bool $removeExistingPdf = false;
```

**Métodos Principales:**

- `mount(Call $call, Resolution $resolution)` - Inicializar componente con datos existentes
- `updatedCallPhaseId()` - Validar que la fase pertenece a la convocatoria
- `removePdf()` - Marcar PDF existente para eliminación
- `update()` - Actualizar resolución
- `callPhases()` - Computed property con fases de la convocatoria
- `existingPdf()` - Computed property con PDF existente
- `getTypeOptions()` - Obtener opciones de tipos de resolución

**Características:**

- Formulario pre-rellenado con datos existentes
- Validación en tiempo real de cambios
- Gestión de PDF existente: visualización, descarga y eliminación
- Opción de reemplazar PDF existente con uno nuevo
- Validación de archivo PDF (tipo y tamaño máximo 10MB)
- Notificaciones toast de éxito/error
- Sidebar con información de la resolución y acciones
- Breadcrumbs con navegación completa

**Gestión de PDFs:**

- Si hay PDF existente: muestra opciones para ver, descargar o eliminar
- Si no hay PDF o se elimina: muestra componente FilePond para subir nuevo PDF
- Al subir nuevo PDF: elimina automáticamente el PDF anterior

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Resolutions\Show`
- **Vista**: `resources/views/livewire/admin/calls/resolutions/show.blade.php`
- **Ruta**: `/admin/convocatorias/{call}/resoluciones/{resolution}` (nombre: `admin.calls.resolutions.show`)

**Propiedades Públicas:**

```php
public Call $call;
public Resolution $resolution;
public bool $showDeleteModal = false;
public bool $showRestoreModal = false;
public bool $showForceDeleteModal = false;
```

**Métodos Principales:**

- `mount(Call $call, Resolution $resolution)` - Inicializar componente
- `publish()` - Publicar resolución
- `unpublish()` - Despublicar resolución
- `delete()` - Eliminar resolución (soft delete)
- `restore()` - Restaurar resolución eliminada
- `forceDelete()` - Eliminar resolución permanentemente
- `getTypeColor($type)` - Obtener color del badge según tipo
- `getTypeLabel($type)` - Obtener etiqueta traducida del tipo
- `existingPdf()` - Computed property con PDF existente
- `hasPdf()` - Verificar si la resolución tiene PDF

**Características:**

- Vista detallada de la resolución con toda su información
- Badges de estado (tipo, publicado/borrador, eliminado)
- Botones de acción: editar, eliminar, restaurar, publicar/despublicar
- Sección de PDF con visualización y descarga
- Información de la convocatoria y fase asociada
- Información del creador
- Sidebar con detalles adicionales y acciones rápidas
- Modales de confirmación para acciones destructivas
- Breadcrumbs con navegación completa
- Eager loading de todas las relaciones para evitar N+1 queries

---

## Rutas

Todas las rutas están anidadas bajo `/admin/convocatorias/{call}/resoluciones`:

```php
Route::prefix('convocatorias/{call}')->group(function () {
    Route::get('resoluciones', Index::class)
        ->name('admin.calls.resolutions.index');
    
    Route::get('resoluciones/crear', Create::class)
        ->name('admin.calls.resolutions.create');
    
    Route::get('resoluciones/{resolution}', Show::class)
        ->name('admin.calls.resolutions.show');
    
    Route::get('resoluciones/{resolution}/editar', Edit::class)
        ->name('admin.calls.resolutions.edit');
});
```

**Nota:** La ruta `show` debe estar antes de `edit` para evitar conflictos de matching.

---

## Modelo Resolution

### Relaciones

```php
// BelongsTo
$resolution->call()           // Convocatoria padre
$resolution->callPhase()      // Fase asociada
$resolution->creator()       // Usuario creador
```

### Traits

- `SoftDeletes` - Eliminación suave
- `InteractsWithMedia` - Gestión de archivos multimedia (Spatie Media Library)

### Casts

```php
protected function casts(): array
{
    return [
        'official_date' => 'date',
        'published_at' => 'datetime',
    ];
}
```

### Media Collections

```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('resolutions')
        ->singleFile()
        ->acceptsMimeTypes(['application/pdf']);
}
```

---

## Form Requests

### StoreResolutionRequest

**Ubicación:** `app/Http/Requests/StoreResolutionRequest.php`

**Reglas de Validación:**

- `call_id`: Requerido, debe existir en tabla `calls`
- `call_phase_id`: Requerido, debe existir en tabla `call_phases` y pertenecer a `call_id`
- `type`: Requerido, debe ser uno de: `provisional`, `definitivo`, `alegaciones`
- `title`: Requerido, string, máximo 255 caracteres
- `description`: Opcional, string
- `evaluation_procedure`: Opcional, string
- `official_date`: Requerido, fecha válida
- `published_at`: Opcional, fecha válida
- `pdfFile`: Opcional, archivo PDF, máximo 10MB

**Validación Personalizada:**

- Validación custom de que `call_phase_id` pertenece a `call_id` mediante closure

**Mensajes Personalizados:**

- Todos los mensajes de error están traducidos en español e inglés
- Mensajes específicos para cada regla de validación

### UpdateResolutionRequest

**Ubicación:** `app/Http/Requests/UpdateResolutionRequest.php`

**Reglas de Validación:**

- Mismas reglas que `StoreResolutionRequest`
- `call_id` y `call_phase_id` se validan en el contexto de la resolución existente

---

## Policy: ResolutionPolicy

**Ubicación:** `app/Policies/ResolutionPolicy.php`

**Métodos de Autorización:**

- `viewAny()` - Ver listado (requiere `calls.view`)
- `view()` - Ver detalle (requiere `calls.view`)
- `create()` - Crear (requiere `calls.create`)
- `update()` - Actualizar (requiere `calls.edit`)
- `delete()` - Eliminar (requiere `calls.delete`)
- `publish()` - Publicar/despublicar (requiere `calls.publish`)
- `restore()` - Restaurar (requiere `calls.delete`)
- `forceDelete()` - Eliminar permanentemente (requiere `calls.delete`)

**Nota:** Las resoluciones utilizan los mismos permisos que las convocatorias (`calls.*`) ya que son entidades hijas.

---

## Integración con FilePond

### Configuración

**Dependencias NPM:**
```json
{
  "filepond": "^4.x",
  "filepond-plugin-file-validate-type": "^1.x",
  "filepond-plugin-file-validate-size": "^1.x"
}
```

**Dependencias Composer:**
```json
{
  "spatie/livewire-filepond": "^1.x"
}
```

**Configuración en `resources/js/app.js`:**

```javascript
import * as FilePond from 'filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import 'filepond/dist/filepond.min.css';

FilePond.registerPlugin(FilePondPluginFileValidateType, FilePondPluginFileValidateSize);
window.LivewireFilePond = FilePond;
```

### Uso en Componentes Livewire

**Traits necesarios:**
```php
use Spatie\LivewireFilepond\WithFilePond;
use Livewire\WithFileUploads;
```

**En la vista:**
```blade
<x-filepond::upload 
    wire:model="pdfFile"
    accepted-file-types="application/pdf"
    max-file-size="10MB"
    label="PDF de la Resolución"
/>
```

---

## Optimizaciones Implementadas

### 1. Eager Loading

- Todas las consultas utilizan eager loading para evitar N+1 queries
- Relaciones cargadas: `call`, `callPhase`, `creator`, `media`
- Selección específica de columnas para reducir datos transferidos

### 2. Optimización de Media Library

- Media cargada mediante eager loading en consultas principales
- Método `hasPdf()` optimizado para usar media cargada cuando está disponible
- Fallback a consulta solo si no está cargada

### 3. Consultas Optimizadas

- Uso de `withCount()` cuando es necesario (aunque no aplica directamente a resoluciones)
- Filtros aplicados a nivel de base de datos
- Índices en columnas de búsqueda y filtrado

---

## Validación de Relaciones

### Antes de Eliminar

Las resoluciones no tienen relaciones críticas que impidan su eliminación. Son entidades hijas de Calls y CallPhases, por lo que pueden eliminarse de forma segura.

**Nota:** Si en el futuro se añaden relaciones importantes (por ejemplo, notificaciones, audit logs), se deben validar antes de permitir la eliminación permanente.

---

## Mejoras de UX

### 1. Estados de Carga

- Indicadores de carga con `wire:loading` y `wire:target` en todas las acciones
- Botones deshabilitados durante operaciones asíncronas
- Spinners y mensajes de "Cargando..." donde corresponde

### 2. Confirmaciones

- Modales de confirmación para todas las acciones destructivas
- Mensajes claros sobre las consecuencias de cada acción
- Botones de cancelar y confirmar en todos los modales

### 3. Notificaciones Toast

- Notificaciones toast para todas las operaciones (crear, actualizar, eliminar, publicar, etc.)
- Mensajes consistentes con títulos y descripciones
- Auto-cierre después de 5 segundos

### 4. Mensajes de Error

- Validaciones con mensajes personalizados en FormRequests
- Mensajes de error claros y descriptivos
- Internacionalización completa (ES/EN)

---

## Testing

### Cobertura de Tests

**Archivos de Test:**
- `tests/Feature/Livewire/Admin/Calls/Resolutions/IndexTest.php` - 17 tests
- `tests/Feature/Livewire/Admin/Calls/Resolutions/CreateTest.php` - 14 tests
- `tests/Feature/Livewire/Admin/Calls/Resolutions/EditTest.php` - 11 tests
- `tests/Feature/Livewire/Admin/Calls/Resolutions/ShowTest.php` - 10 tests

**Total:** 68 tests, 151 assertions

### Áreas Cubiertas

- ✅ Autorización (acceso según permisos)
- ✅ CRUD completo (crear, leer, actualizar, eliminar)
- ✅ Validación (campos requeridos y reglas personalizadas)
- ✅ Filtrado (por tipo, estado de publicación, fase)
- ✅ Búsqueda (por título y descripción)
- ✅ Ordenación (por diferentes campos)
- ✅ SoftDeletes (eliminación, restauración, eliminación permanente)
- ✅ Publicación (publicar/despublicar resoluciones)
- ✅ PDFs (subida, actualización y eliminación de archivos PDF)

---

## Integración con Vista Show de Convocatorias

Las resoluciones están integradas en la vista Show de Convocatorias (`app/Livewire/Admin/Calls/Show.php`):

- Botón "Gestionar Resoluciones" que enlaza al índice de resoluciones
- Botón "Añadir Resolución" que enlaza al formulario de creación
- Lista de resoluciones con acciones rápidas (publicar/despublicar)
- Badges de estado y tipo de resolución

---

## Traducciones

Todas las traducciones están en:
- `lang/es/common.php` - Sección `resolutions`
- `lang/en/common.php` - Sección `resolutions`

**Claves principales:**
- `resolutions.titles.*` - Títulos de páginas
- `resolutions.actions.*` - Acciones
- `resolutions.statuses.*` - Estados
- `resolutions.types.*` - Tipos de resolución
- `resolutions.fields.*` - Campos del formulario
- `resolutions.messages.*` - Mensajes de éxito/error
- `resolutions.filters.*` - Filtros
- `resolutions.empty.*` - Estados vacíos
- `resolutions.confirmations.*` - Confirmaciones

---

## Archivos Creados/Modificados

### Archivos Nuevos

1. `app/Livewire/Admin/Calls/Resolutions/Index.php`
2. `app/Livewire/Admin/Calls/Resolutions/Create.php`
3. `app/Livewire/Admin/Calls/Resolutions/Edit.php`
4. `app/Livewire/Admin/Calls/Resolutions/Show.php`
5. `resources/views/livewire/admin/calls/resolutions/index.blade.php`
6. `resources/views/livewire/admin/calls/resolutions/create.blade.php`
7. `resources/views/livewire/admin/calls/resolutions/edit.blade.php`
8. `resources/views/livewire/admin/calls/resolutions/show.blade.php`
9. `database/migrations/2025_12_30_175118_add_soft_deletes_to_resolutions_table.php`
10. `tests/Feature/Livewire/Admin/Calls/Resolutions/IndexTest.php`
11. `tests/Feature/Livewire/Admin/Calls/Resolutions/CreateTest.php`
12. `tests/Feature/Livewire/Admin/Calls/Resolutions/EditTest.php`
13. `tests/Feature/Livewire/Admin/Calls/Resolutions/ShowTest.php`

### Archivos Modificados

1. `app/Models/Resolution.php` - Agregado SoftDeletes y Media Library
2. `app/Http/Requests/StoreResolutionRequest.php` - Validaciones actualizadas
3. `app/Http/Requests/UpdateResolutionRequest.php` - Validaciones actualizadas
4. `routes/web.php` - Rutas anidadas agregadas
5. `resources/views/livewire/admin/calls/show.blade.php` - Integración de resoluciones
6. `composer.json` - Dependencia `spatie/livewire-filepond` agregada
7. `package.json` - Dependencias NPM de FilePond agregadas
8. `resources/js/app.js` - Configuración de FilePond agregada
9. `lang/es/common.php` - Traducciones agregadas
10. `lang/en/common.php` - Traducciones agregadas

---

## Notas Técnicas

### SoftDeletes

- Las resoluciones utilizan SoftDeletes para eliminación suave
- La columna `deleted_at` se añadió mediante migración
- Las resoluciones eliminadas no aparecen en listados por defecto
- Opción de mostrar eliminadas mediante filtro

### Media Library

- Los PDFs se almacenan en la colección `resolutions`
- Solo se permite un PDF por resolución (`singleFile()`)
- Solo se aceptan archivos PDF (`acceptsMimeTypes(['application/pdf'])`)
- Tamaño máximo: 10MB

### FilePond

- Integración mediante `spatie/livewire-filepond`
- Validación de tipo y tamaño en cliente y servidor
- Interfaz moderna y accesible para subida de archivos
- Soporte para arrastrar y soltar

### Fecha de Publicación

- Campo opcional que permite establecer cuándo se publicó la resolución
- Solo se muestra fecha (sin hora) en todas las vistas
- Formato de entrada: `date` (no `datetime-local`)
- Formato de visualización: `d/m/Y`

---

## Próximas Mejoras Posibles

1. **Historial de Cambios**: Implementar auditoría de cambios en resoluciones
2. **Notificaciones**: Notificar a usuarios cuando se publica una resolución
3. **Versiones de PDF**: Mantener historial de versiones de PDFs
4. **Exportación**: Exportar listado de resoluciones a PDF/Excel
5. **Búsqueda Avanzada**: Búsqueda full-text en contenido de PDFs
6. **Relaciones Adicionales**: Validar relaciones antes de eliminar si se añaden en el futuro

---

## Referencias

- [Laravel Livewire 3 Documentation](https://livewire.laravel.com/docs)
- [Flux UI Documentation](https://flux.laravel.com/docs)
- [Spatie Media Library Documentation](https://spatie.be/docs/laravel-medialibrary)
- [Spatie Livewire FilePond Documentation](https://github.com/spatie/livewire-filepond)
- [FilePond Documentation](https://pqina.nl/filepond/docs/)

