# CRUD de Eventos Erasmus+ en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Eventos Erasmus+ en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Eventos Erasmus+ permite a los administradores gestionar completamente los eventos desde el panel de administración. Incluye funcionalidades avanzadas como gestión de imágenes mediante FilePond y Media Library, SoftDeletes con gestión personalizada de imágenes, vista de calendario interactiva (mes/semana/día), asociación con programas y convocatorias, y sistema completo de filtros y búsqueda.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar eventos
- ✅ **SoftDeletes**: Los eventos nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo super-admin puede eliminar permanentemente
- ✅ **Gestión de Imágenes**: Subida mediante FilePond con drag & drop, preview automático, validación en frontend
- ✅ **Soft Delete de Imágenes**: Gestión personalizada de soft deletes para imágenes usando `custom_properties`
- ✅ **Media Library**: Almacenamiento y gestión de imágenes mediante Spatie Media Library
- ✅ **Vista de Calendario**: Vista interactiva de calendario con navegación (mes, semana, día)
- ✅ **Vista de Lista**: Listado tradicional con tabla interactiva
- ✅ **Asociación con Programas y Convocatorias**: Vinculación opcional con programas y convocatorias
- ✅ **Búsqueda y Filtros**: Búsqueda por título y descripción; filtros por programa, convocatoria, tipo de evento, fecha y eliminados
- ✅ **Ordenación**: Ordenación por diferentes campos (título, fecha, tipo, etc.)
- ✅ **Autorización**: Control de acceso mediante `ErasmusEventPolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 135 tests pasando (Index: 41, Create: 37, Edit: 27, Show: 20, Integration: 10)

---

## Componentes Livewire

### 1. Index (Listado y Calendario)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Events\Index`
- **Vista**: `resources/views/livewire/admin/events/index.blade.php`
- **Ruta**: `/admin/eventos` (nombre: `admin.events.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'vista')]
public string $viewMode = 'list'; // 'list' o 'calendar'

#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'ordenar')]
public string $sortField = 'start_date';

#[Url(as: 'direccion')]
public string $sortDirection = 'desc';

#[Url(as: 'eliminados')]
public string $showDeleted = '0';

#[Url(as: 'programa')]
public ?int $programFilter = null;

#[Url(as: 'convocatoria')]
public ?int $callFilter = null;

#[Url(as: 'tipo')]
public string $eventTypeFilter = '';

#[Url(as: 'fecha')]
public string $dateFilter = '';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

#[Url(as: 'fecha-calendario')]
public string $currentDate = '';

#[Url(as: 'vista-calendario')]
public string $calendarView = 'month'; // 'month', 'week', 'day'
```

**Computed Properties:**

```php
#[Computed]
public function events(): LengthAwarePaginator
{
    // Eventos filtrados y paginados con eager loading
    // Incluye: program, call, creator, media
}

#[Computed]
public function calendarEvents(): Collection
{
    // Eventos para vista de calendario según el modo (mes/semana/día)
}

#[Computed]
public function eventsByDate(): array
{
    // Eventos agrupados por fecha para el calendario
}

#[Computed]
public function calendarDays(): array
{
    // Días del mes con eventos para vista mensual
}

#[Computed]
public function weekDays(): array
{
    // Días de la semana con eventos para vista semanal
}

#[Computed]
public function dayEvents(): Collection
{
    // Eventos del día para vista diaria
}

#[Computed]
public function availablePrograms(): Collection
{
    // Programas disponibles para filtros
}

#[Computed]
public function availableCalls(): Collection
{
    // Convocatorias disponibles (filtradas por programa si está seleccionado)
}

#[Computed]
public function eventTypes(): array
{
    // Tipos de evento disponibles
}
```

**Métodos Principales:**

- `sortBy(string $field)`: Ordenar por campo
- `resetFilters()`: Resetear todos los filtros
- `goToToday()`: Ir a la fecha actual
- `previousMonth()`, `nextMonth()`: Navegar meses
- `previousWeek()`, `nextWeek()`: Navegar semanas
- `previousDay()`, `nextDay()`: Navegar días
- `changeCalendarView(string $view)`: Cambiar vista de calendario
- `changeViewMode(string $mode)`: Cambiar entre lista y calendario
- `delete()`, `restore()`, `forceDelete()`: Gestión de eliminación

**Vista de Calendario:**

El componente incluye una vista de calendario completa con:
- **Vista Mensual**: Calendario completo con eventos agrupados por día
- **Vista Semanal**: Vista de semana con eventos por día
- **Vista Diaria**: Vista de un día con todos los eventos
- **Navegación**: Botones para navegar entre períodos
- **Filtros**: Los filtros se mantienen al navegar el calendario

---

### 2. Create (Crear Evento)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Events\Create`
- **Vista**: `resources/views/livewire/admin/events/create.blade.php`
- **Ruta**: `/admin/eventos/crear` (nombre: `admin.events.create`)

**Propiedades Públicas:**

```php
public ?int $program_id = null;
public ?int $call_id = null;
public string $title = '';
public string $description = '';
public string $event_type = '';
public string $start_date = '';
public string $end_date = '';
public string $location = '';
public bool $is_public = true;
public bool $is_all_day = false;
public array $images = [];
```

**Computed Properties:**

```php
#[Computed]
public function availablePrograms(): Collection
{
    // Programas disponibles
}

#[Computed]
public function availableCalls(): Collection
{
    // Convocatorias disponibles (filtradas por programa)
}

#[Computed]
public function eventTypes(): array
{
    // Tipos de evento disponibles
}
```

**Métodos Principales:**

- `store()`: Crear nuevo evento
- `validateUploadedFile(string $response)`: Validar archivo subido (Filepond)
- `getComponentRules()`: Obtener reglas de validación filtradas para el componente
- `updatedProgramId()`, `updatedCallId()`, `updatedTitle()`, etc.: Validación en tiempo real

**Características Especiales:**

- **Validación en Tiempo Real**: Validación de campos mientras el usuario escribe
- **FilePond Integration**: Subida de múltiples imágenes con drag & drop
- **Auto-ajuste de Fechas**: Si la fecha de fin es anterior a la de inicio, se ajusta automáticamente
- **Modo Todo el Día**: Checkbox para marcar eventos de todo el día (ajusta horas a 00:00)
- **Asociación Contextual**: Puede recibir `program_id` y `call_id` como parámetros de ruta

---

### 3. Edit (Editar Evento)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Events\Edit`
- **Vista**: `resources/views/livewire/admin/events/edit.blade.php`
- **Ruta**: `/admin/eventos/{event}/editar` (nombre: `admin.events.edit`)

**Propiedades Públicas:**

```php
public ErasmusEvent $event;
public ?int $program_id = null;
public ?int $call_id = null;
public string $title = '';
public string $description = '';
public string $event_type = '';
public string $start_date = '';
public string $end_date = '';
public string $location = '';
public bool $is_public = true;
public bool $is_all_day = false;
public array $images = [];
public array $imagesToDelete = [];
public ?int $imageToDelete = null;
public bool $showDeleteImageModal = false;
public ?int $imageToForceDelete = null;
public bool $showForceDeleteImageModal = false;
```

**Computed Properties:**

```php
#[Computed]
public function existingImages(): Collection
{
    // Imágenes existentes (excluyendo soft-deleted)
}

#[Computed]
public function deletedImages(): Collection
{
    // Imágenes soft-deleted que se pueden restaurar
}
```

**Métodos Principales:**

- `update()`: Actualizar evento
- `deleteImage()`: Soft delete de imagen
- `restoreImage(int $mediaId)`: Restaurar imagen eliminada
- `forceDeleteImage()`: Eliminar imagen permanentemente
- `validateUploadedFile(string $response)`: Validar archivo subido (Filepond)
- `getComponentRules()`: Obtener reglas de validación filtradas

**Gestión de Imágenes:**

El componente incluye gestión completa de imágenes:
- **Ver Imágenes Existentes**: Muestra todas las imágenes del evento
- **Subir Nuevas Imágenes**: Mediante FilePond
- **Soft Delete**: Eliminar imágenes (se pueden restaurar)
- **Restaurar**: Restaurar imágenes eliminadas
- **Force Delete**: Eliminar permanentemente (solo super-admin)

---

### 4. Show (Ver Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Events\Show`
- **Vista**: `resources/views/livewire/admin/events/show.blade.php`
- **Ruta**: `/admin/eventos/{event}` (nombre: `admin.events.show`)

**Propiedades Públicas:**

```php
public ErasmusEvent $event;
public bool $showDeleteModal = false;
public bool $showRestoreModal = false;
public bool $showForceDeleteModal = false;
```

**Computed Properties:**

```php
#[Computed]
public function images(): Collection
{
    // Todas las imágenes del evento
}

#[Computed]
public function featuredImageUrl(): ?string
{
    // URL de la imagen destacada (large conversion)
}

#[Computed]
public function hasImages(): bool
{
    // Verificar si tiene imágenes
}

#[Computed]
public function statistics(): array
{
    // Estadísticas del evento (duración, todo el día, número de imágenes)
}
```

**Métodos Principales:**

- `togglePublic()`: Cambiar visibilidad público/privado
- `delete()`: Eliminar evento (soft delete)
- `restore()`: Restaurar evento eliminado
- `forceDelete()`: Eliminar permanentemente
- `getEventTypeConfig(string $eventType)`: Configuración de badge por tipo
- `getEventStatusConfig()`: Configuración de badge por estado

**Información Mostrada:**

- **Datos del Evento**: Título, descripción, tipo, fechas, ubicación
- **Asociaciones**: Programa y convocatoria (si están asociados)
- **Imágenes**: Galería de imágenes con conversiones
- **Estadísticas**: Duración, si es todo el día, número de imágenes
- **Badges**: Badges de tipo de evento y estado (próximo, hoy, pasado, eliminado)
- **Creador**: Información del usuario que creó el evento
- **Acciones**: Toggle visibilidad, eliminar, restaurar, force delete

---

## FormRequests

### StoreErasmusEventRequest

**Ubicación:** `app/Http/Requests/StoreErasmusEventRequest.php`

**Reglas de Validación:**

| Campo | Reglas |
|-------|--------|
| `program_id` | `nullable`, `exists:programs,id` |
| `call_id` | `nullable`, `exists:calls,id` |
| `title` | `required`, `string`, `max:255` |
| `description` | `nullable`, `string` |
| `event_type` | `required`, `in:apertura,cierre,entrevista,publicacion_provisional,publicacion_definitivo,reunion_informativa,otro` |
| `start_date` | `required`, `date` |
| `end_date` | `nullable`, `date`, `after:start_date` |
| `location` | `nullable`, `string`, `max:255` |
| `is_public` | `nullable`, `boolean` |
| `created_by` | `nullable`, `exists:users,id` |
| `images` | `nullable`, `array` |
| `images.*` | `image`, `mimes:jpeg,png,jpg,webp,gif`, `max:5120` |

**Validaciones Personalizadas:**

- Si `call_id` y `program_id` están presentes, valida que `call_id` pertenezca a `program_id`

**Mensajes Personalizados:**
- Todos los mensajes están internacionalizados en español
- Mensajes específicos para cada regla de validación

---

### UpdateErasmusEventRequest

**Ubicación:** `app/Http/Requests/UpdateErasmusEventRequest.php`

**Reglas de Validación:**

Igual que `StoreErasmusEventRequest` (ver tabla anterior).

**Validaciones Personalizadas:**

- Si `call_id` y `program_id` están presentes, valida que `call_id` pertenezca a `program_id`

**Mensajes Personalizados:**
- Todos los mensajes están internacionalizados en español
- Mensajes específicos para cada regla de validación

---

## Policy

### ErasmusEventPolicy

**Ubicación:** `app/Policies/ErasmusEventPolicy.php`

**Métodos de Autorización:**

| Método | Permiso Requerido | Descripción |
|--------|-------------------|-------------|
| `before()` | - | Super-admin tiene acceso total |
| `viewAny()` | `events.view` | Ver listado de eventos |
| `view()` | `events.view` | Ver detalle de evento |
| `create()` | `events.create` | Crear evento |
| `update()` | `events.edit` | Actualizar evento |
| `delete()` | `events.delete` | Eliminar evento (soft delete) |
| `restore()` | `events.delete` | Restaurar evento eliminado |
| `forceDelete()` | `events.delete` | Eliminar permanentemente (solo super-admin) |

**Permisos del Módulo:**
- `events.view` - Ver eventos
- `events.create` - Crear eventos
- `events.edit` - Editar eventos
- `events.delete` - Eliminar eventos

---

## Modelo

### ErasmusEvent

**Ubicación:** `app/Models/ErasmusEvent.php`

**Traits:**
- `Illuminate\Database\Eloquent\SoftDeletes` - SoftDeletes
- `Spatie\MediaLibrary\HasMedia` - Media Library
- `Spatie\MediaLibrary\InteractsWithMedia` - Interacciones con Media Library

**Relaciones:**

| Relación | Tipo | Modelo Relacionado |
|----------|------|-------------------|
| `program()` | BelongsTo | `Program` (nullable) |
| `call()` | BelongsTo | `Call` (nullable) |
| `creator()` | BelongsTo | `User` (created_by) |

**Media Collections:**

```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('images')
        ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif']);
}

public function registerMediaConversions(Media $media = null): void
{
    $this->addMediaConversion('thumbnail')
        ->width(150)
        ->height(150)
        ->sharpen(10);

    $this->addMediaConversion('medium')
        ->width(500)
        ->height(500);

    $this->addMediaConversion('large')
        ->width(1200)
        ->height(1200);
}
```

**Scopes:**

- `scopePublic(Builder $query)`: Solo eventos públicos
- `scopeUpcoming(Builder $query)`: Solo eventos futuros
- `scopePast(Builder $query)`: Solo eventos pasados
- `scopeForProgram(Builder $query, int $programId)`: Filtrar por programa
- `scopeForCall(Builder $query, int $callId)`: Filtrar por convocatoria
- `scopeByType(Builder $query, string $type)`: Filtrar por tipo
- `scopeForDate(Builder $query, Carbon $date)`: Filtrar por fecha
- `scopeForMonth(Builder $query, int $year, int $month)`: Filtrar por mes
- `scopeInDateRange(Builder $query, Carbon $start, Carbon $end)`: Filtrar por rango de fechas

**Métodos Helper:**

- `isUpcoming()`: Verificar si el evento es futuro
- `isToday()`: Verificar si el evento es hoy
- `isPast()`: Verificar si el evento es pasado
- `isAllDay()`: Verificar si el evento es de todo el día
- `duration()`: Calcular duración en horas
- `getEventTypeLabel()`: Obtener etiqueta del tipo de evento

**Gestión de Soft Deletes para Imágenes:**

El modelo incluye métodos personalizados para gestionar soft deletes de imágenes usando `custom_properties`:

- `isMediaSoftDeleted(Media $media)`: Verificar si una imagen está soft-deleted
- `getMedia(string $collectionName)`: Obtener imágenes (excluye soft-deleted)
- `getFirstMedia(string $collectionName)`: Obtener primera imagen (excluye soft-deleted)
- `hasMedia(string $collectionName)`: Verificar si tiene imágenes (excluye soft-deleted)
- `getMediaWithDeleted(string $collectionName)`: Obtener todas las imágenes (incluye soft-deleted)
- `softDeleteMediaById(int $mediaId)`: Soft delete de imagen
- `restoreMediaById(int $mediaId)`: Restaurar imagen
- `forceDeleteMediaById(int $mediaId)`: Eliminar imagen permanentemente
- `getSoftDeletedImages()`: Obtener imágenes soft-deleted
- `hasSoftDeletedImages()`: Verificar si tiene imágenes soft-deleted

---

## Rutas

**Ubicación:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('events', \App\Livewire\Admin\Events\Index::class)
        ->only(['index'])
        ->parameters(['events' => 'event']);
    
    Route::get('eventos/crear', \App\Livewire\Admin\Events\Create::class)
        ->name('events.create');
    
    Route::get('eventos/{event}/editar', \App\Livewire\Admin\Events\Edit::class)
        ->name('events.edit');
    
    Route::get('eventos/{event}', \App\Livewire\Admin\Events\Show::class)
        ->name('events.show');
});
```

---

## Traducciones

Todas las traducciones están en:
- `lang/es/common.php` - Sección `events`
- `lang/en/common.php` - Sección `events`

**Claves principales:**
- `events.titles.*` - Títulos de páginas
- `events.actions.*` - Acciones
- `events.types.*` - Tipos de evento
- `events.fields.*` - Campos del formulario
- `events.messages.*` - Mensajes de éxito/error
- `events.filters.*` - Filtros
- `events.empty.*` - Estados vacíos

---

## Testing

### Cobertura de Tests

**Archivos de Test:**
- `tests/Feature/Livewire/Admin/Events/IndexTest.php` - 41 tests
- `tests/Feature/Livewire/Admin/Events/CreateTest.php` - 37 tests
- `tests/Feature/Livewire/Admin/Events/EditTest.php` - 27 tests
- `tests/Feature/Livewire/Admin/Events/ShowTest.php` - 20 tests
- `tests/Feature/Livewire/Admin/Events/IntegrationTest.php` - 10 tests

**Total:** 135 tests, 332 assertions

### Áreas Cubiertas

- ✅ Autorización (acceso según permisos)
- ✅ CRUD completo (crear, leer, actualizar, eliminar)
- ✅ Validación (campos requeridos y reglas personalizadas)
- ✅ Filtrado (por programa, convocatoria, tipo, fecha)
- ✅ Búsqueda (por título y descripción)
- ✅ Ordenación (por diferentes campos)
- ✅ SoftDeletes (eliminación, restauración, eliminación permanente)
- ✅ Vista de Calendario (mes, semana, día)
- ✅ Navegación de Calendario (anterior, siguiente, hoy)
- ✅ Gestión de Imágenes (subida, soft delete, restaurar, force delete)
- ✅ Validación en Tiempo Real
- ✅ Asociación con Programas y Convocatorias
- ✅ Tests de Integración (flujos completos)

---

## Notas Técnicas

### SoftDeletes de Imágenes

Las imágenes no se eliminan permanentemente por defecto. Se usa `custom_properties` en la tabla `media` para marcar imágenes como eliminadas:

```php
// Soft delete
$media->setCustomProperty('deleted_at', now()->toDateTimeString());
$media->save();

// Restaurar
$media->forgetCustomProperty('deleted_at');
$media->save();
```

### Validación en Tiempo Real

Los componentes `Create` y `Edit` usan `getComponentRules()` para filtrar las reglas del FormRequest y solo validar propiedades del componente:

```php
protected function getComponentRules(): array
{
    $allRules = (new StoreErasmusEventRequest)->rules();
    $componentProperties = ['program_id', 'call_id', 'title', ...];
    return array_intersect_key($allRules, array_flip($componentProperties));
}
```

### FilePond Integration

Los componentes usan `Spatie\LivewireFilepond\WithFilePond` para gestionar la subida de imágenes:
- Validación en frontend mediante `validateUploadedFile()`
- Soporte para múltiples imágenes
- Drag & drop
- Preview automático

---

## Mejoras Futuras

- [ ] Exportar eventos a calendario (iCal)
- [ ] Notificaciones por email para eventos próximos
- [ ] Integración con Google Calendar
- [ ] Vista de calendario pública
- [ ] Filtros avanzados por rango de fechas
- [ ] Búsqueda por ubicación

