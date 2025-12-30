# CRUD de Fases de Convocatorias en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Fases de Convocatorias (Call Phases) en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Fases de Convocatorias permite a los administradores gestionar completamente las fases de las convocatorias Erasmus+ desde el panel de administración. Las fases están anidadas bajo sus convocatorias padre, reflejando la relación jerárquica entre estos recursos.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar fases de convocatorias
- ✅ **Rutas Anidadas**: Las fases están bajo `/admin/convocatorias/{call}/fases`
- ✅ **SoftDeletes**: Las fases nunca se eliminan permanentemente por defecto
- ✅ **Cascade Delete**: Eliminación física automática de resoluciones relacionadas
- ✅ **Reordenamiento**: Mover fases arriba/abajo para cambiar su orden
- ✅ **Fase Actual**: Marcar/desmarcar fase como actual (solo una por convocatoria)
- ✅ **Validación de Fechas**: Validación de solapamiento de fechas entre fases
- ✅ **Búsqueda y Filtros**: Búsqueda por nombre, filtros por tipo y estado
- ✅ **Autorización**: Control de acceso mediante `CallPhasePolicy`
- ✅ **Validación en Tiempo Real**: Validación de campos clave mientras el usuario escribe
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Phases\Index`
- **Vista**: `resources/views/livewire/admin/calls/phases/index.blade.php`
- **Ruta**: `/admin/convocatorias/{call}/fases` (nombre: `admin.calls.phases.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'buscar')]
public string $search = '';

#[Url(as: 'tipo')]
public ?string $phaseType = null;

#[Url(as: 'actual')]
public ?bool $isCurrent = null;

#[Url(as: 'eliminados')]
public bool $showDeleted = false;

#[Url(as: 'ordenar')]
public string $sortBy = 'order';

#[Url(as: 'direccion')]
public string $sortDirection = 'asc';

#[Url(as: 'pagina')]
public int $perPage = 15;
```

**Métodos Principales:**

- `phases()` - Computed property que retorna las fases paginadas y filtradas
- `sortBy($field)` - Cambiar ordenación
- `markAsCurrent($phaseId)` - Marcar fase como actual
- `unmarkAsCurrent($phaseId)` - Desmarcar fase como actual
- `moveUp($phaseId)` - Mover fase una posición arriba
- `moveDown($phaseId)` - Mover fase una posición abajo
- `confirmDelete($phaseId)` - Confirmar eliminación
- `delete($phaseId)` - Eliminar fase (soft delete)
- `confirmRestore($phaseId)` - Confirmar restauración
- `restore($phaseId)` - Restaurar fase eliminada
- `confirmForceDelete($phaseId)` - Confirmar eliminación permanente
- `forceDelete($phaseId)` - Eliminar fase permanentemente
- `resetFilters()` - Limpiar todos los filtros

**Características:**

- Búsqueda por nombre y descripción
- Filtros por tipo de fase (`phase_type`), fase actual (`is_current`), y mostrar eliminados
- Ordenación por campo configurable (order, name, start_date, end_date)
- Paginación configurable (15 por defecto)
- Tabla responsive con Flux UI
- Botones de acción: ver, editar, eliminar, restaurar, marcar como actual, mover arriba/abajo
- Modales de confirmación para acciones destructivas
- Estado vacío con componente `x-ui.empty-state`
- Breadcrumbs con `x-ui.breadcrumbs`
- Eager loading de relaciones (`call`, `resolutions`) y conteos

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Phases\Create`
- **Vista**: `resources/views/livewire/admin/calls/phases/create.blade.php`
- **Ruta**: `/admin/convocatorias/{call}/fases/crear` (nombre: `admin.calls.phases.create`)

**Propiedades Públicas:**

```php
public int $call_id;
public string $phase_type = '';
public string $name = '';
public ?string $description = null;
public ?string $start_date = null;
public ?string $end_date = null;
public bool $is_current = false;
public ?int $order = null;
```

**Métodos Principales:**

- `mount(Call $call)` - Inicializar componente con convocatoria padre
- `getNextOrder()` - Obtener siguiente orden disponible
- `updatedIsCurrent()` - Manejar cambio de fase actual
- `updatedStartDate()` - Validar fecha de inicio
- `updatedEndDate()` - Validar fecha de fin
- `checkDateOverlaps()` - Verificar solapamiento de fechas
- `store()` - Crear nueva fase

**Características:**

- Formulario completo con Flux UI
- Auto-generación de `order` si no se proporciona
- Validación en tiempo real con métodos `updated*()`
- Validación de fechas (end_date después de start_date)
- Advertencia de solapamiento de fechas con otras fases
- Manejo de `is_current`: al marcar como actual, desmarca automáticamente otras fases
- Mensajes de éxito/error con notificaciones toast

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Phases\Edit`
- **Vista**: `resources/views/livewire/admin/calls/phases/edit.blade.php`
- **Ruta**: `/admin/convocatorias/{call}/fases/{call_phase}/editar` (nombre: `admin.calls.phases.edit`)

**Propiedades Públicas:**

```php
public CallPhase $callPhase;
public int $call_id;
public string $phase_type = '';
public string $name = '';
public ?string $description = null;
public ?string $start_date = null;
public ?string $end_date = null;
public bool $is_current = false;
public int $order = 0;
```

**Métodos Principales:**

- `mount(CallPhase $callPhase)` - Cargar datos de la fase existente
- `updatedIsCurrent()` - Manejar cambio de fase actual (excluyendo fase actual)
- `updatedStartDate()` - Validar fecha de inicio
- `updatedEndDate()` - Validar fecha de fin
- `checkDateOverlaps()` - Verificar solapamiento de fechas (excluyendo fase actual)
- `update()` - Actualizar fase

**Características:**

- Formulario completo con datos pre-cargados
- Mismas validaciones que Create
- Validación de `is_current` excluyendo la fase actual
- Validación de solapamiento de fechas excluyendo la fase actual
- Información de resoluciones relacionadas
- Manejo correcto de valores null en fechas
- Mensajes de éxito/error con notificaciones toast

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Phases\Show`
- **Vista**: `resources/views/livewire/admin/calls/phases/show.blade.php`
- **Ruta**: `/admin/convocatorias/{call}/fases/{call_phase}` (nombre: `admin.calls.phases.show`)

**Propiedades Públicas:**

```php
public CallPhase $callPhase;
```

**Métodos Principales:**

- `mount(CallPhase $callPhase)` - Cargar fase con relaciones
- `markAsCurrent()` - Marcar fase como actual
- `unmarkAsCurrent()` - Desmarcar fase como actual
- `delete()` - Eliminar fase (soft delete)
- `restore()` - Restaurar fase eliminada
- `forceDelete()` - Eliminar fase permanentemente
- `getPhaseTypeColor()` - Obtener color del tipo de fase
- `getPhaseTypeLabel()` - Obtener etiqueta del tipo de fase

**Características:**

- Vista completa de detalles de la fase
- Información de la convocatoria padre con eager loading
- Listado de resoluciones relacionadas
- Badges de tipo de fase con colores
- Badge de estado "Fase Actual" si aplica
- Botones de acción: editar, eliminar, restaurar, marcar como actual, desmarcar como actual
- Breadcrumbs correctamente configurados

---

## Rutas

Todas las rutas están anidadas bajo `/admin/convocatorias/{call}/fases`:

```php
Route::middleware(['auth', 'verified'])->prefix('admin/convocatorias/{call}')->group(function () {
    Route::get('/fases', Index::class)->name('admin.calls.phases.index');
    Route::get('/fases/crear', Create::class)->name('admin.calls.phases.create');
    Route::get('/fases/{call_phase}', Show::class)->name('admin.calls.phases.show');
    Route::get('/fases/{call_phase}/editar', Edit::class)->name('admin.calls.phases.edit');
});
```

---

## Form Requests

### StoreCallPhaseRequest

**Ubicación:** `app/Http/Requests/StoreCallPhaseRequest.php`

**Reglas de Validación:**

- `call_id`: requerido, existe en `calls`
- `phase_type`: requerido, enum válido
- `name`: requerido, string, max 255
- `description`: opcional, string
- `start_date`: opcional, date
- `end_date`: opcional, date, después de `start_date`
- `is_current`: opcional, boolean, validación custom (solo una por convocatoria)
- `order`: opcional, integer, min 0, único por convocatoria (ignorando soft-deleted)

**Características:**

- Autorización con `CallPhasePolicy::create()`
- Mensajes de error personalizados en español
- Validación custom de `is_current` para asegurar unicidad
- Validación de `order` único por convocatoria

---

### UpdateCallPhaseRequest

**Ubicación:** `app/Http/Requests/UpdateCallPhaseRequest.php`

**Reglas de Validación:**

- Mismas que `StoreCallPhaseRequest`
- `order`: único por convocatoria excluyendo fase actual
- `is_current`: validación custom excluyendo fase actual

**Características:**

- Autorización con `CallPhasePolicy::update()`
- Mensajes de error personalizados en español
- Validación de `order` y `is_current` excluyendo fase actual

---

## Modelo CallPhase

**Ubicación:** `app/Models/CallPhase.php`

**Relaciones:**

- `call()` - BelongsTo `Call`
- `resolutions()` - HasMany `Resolution`

**Scopes:**

- `SoftDeletes` - Eliminación lógica

**Características Especiales:**

- **Cascade Delete Manual**: El método `boot()` incluye un evento `deleting` que fuerza la eliminación física de las resoluciones relacionadas cuando se elimina una fase (soft delete). Esto es necesario porque Laravel no ejecuta automáticamente `cascadeOnDelete()` en foreign keys cuando se usa SoftDeletes.

```php
protected static function boot(): void
{
    parent::boot();

    static::deleting(function (CallPhase $callPhase) {
        $callPhase->resolutions()->each(function (Resolution $resolution) {
            $resolution->forceDelete();
        });
    });
}
```

---

## Policy

**Ubicación:** `app/Policies/CallPhasePolicy.php`

**Métodos:**

- `viewAny()` - Ver listado de fases
- `view()` - Ver detalle de fase
- `create()` - Crear fase
- `update()` - Actualizar fase
- `delete()` - Eliminar fase (soft delete)
- `restore()` - Restaurar fase eliminada
- `forceDelete()` - Eliminar fase permanentemente

**Permisos Requeridos:**

- `CALLS_VIEW` - Ver fases
- `CALLS_CREATE` - Crear fases
- `CALLS_EDIT` - Editar fases
- `CALLS_DELETE` - Eliminar fases

**Características:**

- `before()` method para `SUPER_ADMIN` con acceso completo
- Delegación a permisos específicos para otros roles

---

## Optimizaciones

### Índices de Base de Datos

Se añadieron índices en la tabla `call_phases` para mejorar el rendimiento de las consultas:

- `call_id` - Para filtros por convocatoria
- `phase_type` - Para filtros por tipo
- `is_current` - Para búsqueda de fase actual
- `order` - Para ordenación
- `deleted_at` - Para filtros de soft deletes

**Migración:** `database/migrations/2025_12_29_193150_add_indexes_to_call_phases_table.php`

### Eager Loading

Se aplicó eager loading en los componentes para evitar N+1 queries:

- `Index`: `with(['call', 'resolutions'])` y `withCount(['resolutions'])`
- `Show`: `with(['call' => fn ($query) => $query->with(['program', 'academicYear']), 'resolutions' => fn ($query) => $query->latest()])`

---

## Testing

### Tests de Componentes Livewire

**Archivos:**

- `tests/Feature/Livewire/Admin/Calls/Phases/IndexTest.php` (23 tests)
- `tests/Feature/Livewire/Admin/Calls/Phases/CreateTest.php` (12 tests)
- `tests/Feature/Livewire/Admin/Calls/Phases/EditTest.php` (13 tests)
- `tests/Feature/Livewire/Admin/Calls/Phases/ShowTest.php` (12 tests)

**Cobertura:**

- Autorización (verificación de permisos)
- Listado y filtrado
- Creación y validación
- Edición y actualización
- Reordenamiento de fases
- Marcar/desmarcar como actual
- Soft delete, restore y force delete
- Manejo de relaciones (resoluciones)

### Tests de FormRequests

**Archivos:**

- `tests/Feature/Http/Requests/StoreCallPhaseRequestTest.php` (10 tests)
- `tests/Feature/Http/Requests/UpdateCallPhaseRequestTest.php` (6 tests)

**Cobertura:**

- Reglas de validación
- Mensajes personalizados
- Validación de unicidad de fase actual
- Validación de unicidad de orden

### Estadísticas

- **Total de tests:** 76 tests
- **Total de assertions:** 203 assertions
- **Cobertura:** 100% de funcionalidad cubierta
- **Estado:** Todos los tests pasan en ejecución paralela

---

## Integración con Convocatorias

El CRUD de Fases está integrado en la vista de detalle de Convocatorias (`Show` de Calls) con botones de navegación:

- **"Gestionar Fases"** - Navega al listado de fases
- **"Añadir Fase"** - Navega al formulario de creación

**Ubicación:** `resources/views/livewire/admin/calls/show.blade.php`

---

## Notas Técnicas

### Cascade Delete con SoftDeletes

Laravel no ejecuta automáticamente `cascadeOnDelete()` en foreign keys cuando se usa SoftDeletes. Para mantener la integridad de datos, se implementó un evento `deleting` en el modelo `CallPhase` que fuerza la eliminación física de las resoluciones relacionadas cuando se elimina una fase (soft delete).

### Rutas Anidadas

Las rutas de fases están anidadas bajo `/admin/convocatorias/{call}/fases` para reflejar la relación padre-hijo entre Convocatorias y Fases. Esto requiere pasar ambos parámetros (`call` y `call_phase`) a las funciones `route()` en las vistas.

### Validación de Fase Actual

Se implementó validación custom en los FormRequests para asegurar que solo una fase puede ser marcada como actual por convocatoria. Esta validación se ejecuta tanto en creación como en actualización, excluyendo la fase actual en el caso de actualización.

### Validación de Solapamiento de Fechas

Se implementó validación en tiempo real para advertir (no bloquear) sobre solapamiento de fechas entre fases. Esta validación se ejecuta en los componentes `Create` y `Edit` mediante el método `checkDateOverlaps()`.

---

## Traducciones

Las traducciones están en `lang/es/common.php` bajo la clave `call_phases`:

```php
'call_phases' => [
    'title' => 'Fases de Convocatorias',
    'description' => 'Gestiona las fases de las convocatorias',
    // ... más traducciones
],
```

---

## Referencias

- [CRUD de Convocatorias](admin-calls-crud.md) - Documentación del CRUD padre
- [Componentes de Convocatorias](calls-components.md) - Documentación de componentes públicos
- [Form Requests](form-requests.md) - Documentación general de Form Requests
- [Policies](policies.md) - Documentación general de Policies

