# CRUD de Convocatorias en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Convocatorias en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Convocatorias permite a los administradores gestionar completamente las convocatorias Erasmus+ desde el panel de administración. Incluye funcionalidades avanzadas como gestión de estados, publicación, gestión de fases y resoluciones, SoftDeletes, validación de relaciones antes de eliminación permanente, y campos dinámicos para destinos y baremo.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar convocatorias
- ✅ **SoftDeletes**: Las convocatorias nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- ✅ **Gestión de Estados**: Cambio de estado con validación de transiciones (borrador → abierta → cerrada → archivada)
- ✅ **Publicación**: Establecer `published_at` al cambiar estado a "abierta"
- ✅ **Campos Dinámicos**: Gestión dinámica de destinos y tabla de baremo
- ✅ **Gestión de Fases**: Visualización y marcado de fase actual
- ✅ **Gestión de Resoluciones**: Visualización y publicación de resoluciones
- ✅ **Búsqueda y Filtros**: Búsqueda por título, filtros por programa, año académico, tipo, modalidad y estado
- ✅ **Autorización**: Control de acceso mediante `CallPolicy`
- ✅ **Validación en Tiempo Real**: Validación de campos clave mientras el usuario escribe
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Index`
- **Vista**: `resources/views/livewire/admin/calls/index.blade.php`
- **Ruta**: `/admin/convocatorias` (nombre: `admin.calls.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'buscar')]
public string $search = '';

#[Url(as: 'programa')]
public ?string $filterProgram = null;

#[Url(as: 'anio')]
public ?string $filterAcademicYear = null;

#[Url(as: 'tipo')]
public ?string $filterType = null;

#[Url(as: 'estado')]
public ?string $filterStatus = null;

#[Url(as: 'eliminados')]
public string $showDeleted = '0';

#[Url(as: 'ordenar')]
public string $sortField = 'created_at';

#[Url(as: 'direccion')]
public string $sortDirection = 'desc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public ?int $callToDelete = null;
public bool $showRestoreModal = false;
public ?int $callToRestore = null;
public bool $showForceDeleteModal = false;
public ?int $callToForceDelete = null;
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de acceso mediante `CallPolicy::viewAny()`.

#### `calls()` (Computed)
Retorna la lista paginada de convocatorias con filtros aplicados.

**Filtros aplicados:**
- Por estado eliminado (`showDeleted`: '0' = no eliminados, '1' = solo eliminados)
- Por programa (`filterProgram`)
- Por año académico (`filterAcademicYear`)
- Por tipo (`filterType`: 'alumnado' o 'personal')
- Por estado (`filterStatus`)
- Por búsqueda (título)

**Ordenación:**
- Por campo configurable (`sortField`) y dirección (`sortDirection`)
- Por defecto: `created_at` descendente

**Eager Loading:**
- `with(['program', 'academicYear', 'creator', 'updater'])`
- `withCount(['phases', 'applications', 'resolutions'])` para mostrar contadores

#### `sortBy(string $field)`
Cambia el campo de ordenación. Si es el mismo campo, alterna la dirección.

#### `changeStatus(int $callId, string $status)`
Cambia el estado de una convocatoria. Si cambia a "abierta", establece `published_at` automáticamente.

#### `publish(int $callId)`
Publica una convocatoria estableciendo estado a "abierta" y `published_at` a la fecha actual.

#### `confirmDelete(int $callId)`
Abre el modal de confirmación para eliminar una convocatoria.

#### `delete()`
Elimina una convocatoria usando SoftDeletes. Verifica que no tenga relaciones antes de eliminar usando `withCount()`.

#### `confirmRestore(int $callId)`
Abre el modal de confirmación para restaurar una convocatoria eliminada.

#### `restore()`
Restaura una convocatoria eliminada. Requiere permiso `CALLS_DELETE`.

#### `confirmForceDelete(int $callId)`
Abre el modal de confirmación para eliminar permanentemente una convocatoria.

#### `forceDelete()`
Elimina permanentemente una convocatoria. Solo disponible para super-admin. Verifica que no tenga relaciones antes de eliminar.

#### `canDelete()` (Computed)
Verifica si una convocatoria puede ser eliminada (no tiene relaciones).

#### `canForceDelete()` (Computed)
Verifica si una convocatoria puede ser eliminada permanentemente (solo super-admin y sin relaciones).

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Create`
- **Vista**: `resources/views/livewire/admin/calls/create.blade.php`
- **Ruta**: `/admin/convocatorias/crear` (nombre: `admin.calls.create`)

**Propiedades Públicas:**

```php
public ?int $program_id = null;
public ?int $academic_year_id = null;
public string $title = '';
public string $slug = '';
public string $type = 'alumnado';
public string $modality = 'corta';
public int $number_of_places = 1;
public array $destinations = [''];
public ?string $estimated_start_date = null;
public ?string $estimated_end_date = null;
public ?string $requirements = null;
public ?string $documentation = null;
public ?string $selection_criteria = null;
public array $scoringTable = [
    ['concept' => '', 'max_points' => 0, 'description' => ''],
];
public string $status = 'borrador';
public string $newDestination = '';
public array $newScoringItem = [
    'concept' => '',
    'max_points' => 0,
    'description' => '',
];
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de acceso mediante `CallPolicy::create()`.

#### `updatedTitle()`
Genera automáticamente el slug cuando se actualiza el título.

#### `addDestination()`
Añade un nuevo destino a la lista. Si hay un destino vacío al final, lo reemplaza.

#### `removeDestination(int $index)`
Elimina un destino de la lista. Mantiene al menos un destino vacío.

#### `addScoringItem()`
Añade un nuevo elemento a la tabla de baremo.

#### `removeScoringItem(int $index)`
Elimina un elemento de la tabla de baremo. Mantiene al menos un elemento vacío.

#### `store()`
Crea una nueva convocatoria. Valida datos usando `StoreCallRequest`, filtra destinos y elementos de baremo vacíos, y establece `created_by` al usuario actual.

**Validación:**
- Usa `Validator::make()` con reglas de `StoreCallRequest`
- Mapea `scoringTable` (camelCase) a `scoring_table` (snake_case) para validación

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Edit`
- **Vista**: `resources/views/livewire/admin/calls/edit.blade.php`
- **Ruta**: `/admin/convocatorias/{call}/editar` (nombre: `admin.calls.edit`)

**Propiedades Públicas:**

Similar a `Create`, pero con datos precargados desde el modelo `Call`.

**Métodos Principales:**

#### `mount(Call $call)`
Inicializa el componente con los datos de la convocatoria existente. Verifica permisos mediante `CallPolicy::update()`.

**Normalización de datos:**
- Convierte formato legacy de `scoring_table` (asociativo: `{"idioma": 30}`) a formato nuevo (array de objetos: `[{"concept": "idioma", "max_points": 30, "description": ""}]`)

#### `update()`
Actualiza la convocatoria. Similar a `store()` pero usa `UpdateCallRequest` y establece `updated_by` al usuario actual.

**Validación:**
- Usa reglas de validación directamente (no crea FormRequest completo)
- Actualiza regla de `slug` para ignorar el call actual usando `Rule::unique()->ignore($this->call->id)`

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Calls\Show`
- **Vista**: `resources/views/livewire/admin/calls/show.blade.php`
- **Ruta**: `/admin/convocatorias/{call}` (nombre: `admin.calls.show`)

**Propiedades Públicas:**

```php
public Call $call;
public bool $showDeleteModal = false;
public bool $showRestoreModal = false;
```

**Métodos Principales:**

#### `mount(Call $call)`
Inicializa el componente con la convocatoria. Verifica permisos mediante `CallPolicy::view()`.

**Eager Loading:**
- `with(['program', 'academicYear', 'creator', 'updater', 'phases', 'resolutions'])`
- `withCount(['phases', 'applications', 'resolutions'])`

#### `changeStatus(string $status)`
Cambia el estado de la convocatoria. Si cambia a "abierta", establece `published_at` automáticamente.

#### `publish()`
Publica la convocatoria estableciendo estado a "abierta" y `published_at` a la fecha actual.

#### `markPhaseAsCurrent(int $phaseId)`
Marca una fase como actual. Desmarca automáticamente otras fases de la misma convocatoria.

#### `publishResolution(int $resolutionId)`
Publica una resolución estableciendo `published_at` a la fecha actual.

#### `delete()`
Elimina la convocatoria usando SoftDeletes. Verifica que no tenga relaciones antes de eliminar.

#### `restore()`
Restaura la convocatoria eliminada.

#### `canDelete()` (Computed)
Verifica si la convocatoria puede ser eliminada (no tiene relaciones).

#### `canForceDelete()` (Computed)
Verifica si la convocatoria puede ser eliminada permanentemente (solo super-admin y sin relaciones).

#### `getValidStatusTransitions()` (Helper)
Retorna las transiciones de estado válidas para la convocatoria actual.

#### `getStatusDescription()` (Helper)
Retorna una descripción del estado actual de la convocatoria.

---

## FormRequests

### StoreCallRequest

**Ubicación:** `app/Http/Requests/StoreCallRequest.php`

**Autorización:**
- Verifica permiso `CALLS_CREATE` mediante `CallPolicy::create()`

**Reglas de Validación:**

```php
'program_id' => ['required', 'exists:programs,id'],
'academic_year_id' => ['required', 'exists:academic_years,id'],
'title' => ['required', 'string', 'max:255'],
'slug' => ['nullable', 'string', 'max:255', Rule::unique('calls', 'slug')],
'type' => ['required', Rule::in(['alumnado', 'personal'])],
'modality' => ['required', Rule::in(['corta', 'larga'])],
'number_of_places' => ['required', 'integer', 'min:1'],
'destinations' => ['required', 'array', 'min:1'],
'destinations.*' => ['required', 'string', 'max:255'],
'estimated_start_date' => ['nullable', 'date'],
'estimated_end_date' => ['nullable', 'date', 'after:estimated_start_date'],
'scoring_table' => ['nullable', 'array'],
'status' => ['nullable', Rule::in(['borrador', 'abierta', 'cerrada', 'en_baremacion', 'resuelta', 'archivada'])],
```

**Mensajes Personalizados:**
- Todos los mensajes están en español e inglés
- Mensajes específicos para cada regla de validación

---

### UpdateCallRequest

**Ubicación:** `app/Http/Requests/UpdateCallRequest.php`

**Autorización:**
- Verifica permiso `CALLS_EDIT` mediante `CallPolicy::update()`
- Obtiene el call desde `route('call')` para verificar permisos específicos

**Reglas de Validación:**

Similar a `StoreCallRequest`, pero con regla de `slug` que ignora el call actual:

```php
'slug' => ['nullable', 'string', 'max:255', Rule::unique('calls', 'slug')->ignore($callId)],
```

---

## Modelo Call

### Relaciones

```php
public function program(): BelongsTo
public function academicYear(): BelongsTo
public function creator(): BelongsTo
public function updater(): BelongsTo
public function phases(): HasMany
public function applications(): HasMany
public function resolutions(): HasMany
public function events(): HasMany
```

### Eventos del Modelo

#### `deleting`
Maneja cascadas de eliminación cuando se elimina un Call:

- **Soft Delete**: Elimina físicamente `phases`, `applications` y `resolutions` (no tienen SoftDeletes)
- **Soft Delete**: Establece `call_id` a `null` en `events`
- **Force Delete**: Mismo comportamiento que soft delete

### SoftDeletes

El modelo `Call` usa el trait `SoftDeletes` de Laravel, lo que permite:
- Eliminar convocatorias sin perder datos (`deleted_at` se marca)
- Restaurar convocatorias eliminadas
- Force delete solo para super-admin y solo si no hay relaciones

---

## Estados de Convocatoria

### Estados Disponibles

- **borrador**: Convocatoria en preparación, no visible públicamente
- **abierta**: Convocatoria abierta para solicitudes, visible públicamente
- **cerrada**: Convocatoria cerrada, no acepta nuevas solicitudes
- **en_baremacion**: Convocatoria en proceso de baremación
- **resuelta**: Convocatoria con resolución publicada
- **archivada**: Convocatoria archivada

### Transiciones de Estado

Las transiciones válidas se validan mediante el método `getValidStatusTransitions()` en el modelo `Call`:

- Desde **borrador**: puede cambiar a cualquier estado
- Desde **abierta**: puede cambiar a cerrada, en_baremacion, archivada
- Desde **cerrada**: puede cambiar a abierta, en_baremacion, archivada
- Desde **en_baremacion**: puede cambiar a resuelta, archivada
- Desde **resuelta**: puede cambiar a archivada
- Desde **archivada**: no puede cambiar a otro estado

### Publicación

Cuando una convocatoria cambia a estado "abierta", se establece automáticamente `published_at` a la fecha y hora actual. Esto ocurre en:
- `Index::changeStatus()` cuando se cambia a "abierta"
- `Index::publish()`
- `Show::changeStatus()` cuando se cambia a "abierta"
- `Show::publish()`

---

## Campos Dinámicos

### Destinos

Los destinos se gestionan como un array dinámico:

- **Añadir destino**: El usuario escribe en `newDestination` y hace clic en "Añadir"
- **Eliminar destino**: Botón para eliminar cada destino individual
- **Validación**: Al menos un destino es requerido
- **Filtrado**: Los destinos vacíos se filtran antes de guardar

### Tabla de Baremo

La tabla de baremo se gestiona como un array de objetos:

```php
[
    ['concept' => 'Expediente', 'max_points' => 40, 'description' => 'Nota media'],
    ['concept' => 'Idioma', 'max_points' => 30, 'description' => 'Nivel de idioma'],
]
```

- **Añadir elemento**: El usuario completa `newScoringItem` y hace clic en "Añadir"
- **Eliminar elemento**: Botón para eliminar cada elemento individual
- **Validación**: Los elementos vacíos se filtran antes de guardar
- **Formato legacy**: El componente `Edit` convierte automáticamente el formato antiguo (`{"idioma": 30}`) al nuevo formato

---

## Rutas

### Rutas Definidas

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/convocatorias', Index::class)->name('calls.index');
    Route::get('/convocatorias/crear', Create::class)->name('calls.create');
    Route::get('/convocatorias/{call}', Show::class)->name('calls.show');
    Route::get('/convocatorias/{call}/editar', Edit::class)->name('calls.edit');
});
```

### Middleware

- `auth`: Requiere autenticación
- `verified`: Requiere verificación de email (si está habilitada)

### Autorización

Cada ruta verifica permisos mediante `CallPolicy`:
- `index`: `CallPolicy::viewAny()`
- `create`: `CallPolicy::create()`
- `show`: `CallPolicy::view()`
- `edit`: `CallPolicy::update()`

---

## Navegación

### Sidebar

El enlace a Convocatorias se encuentra en el grupo "Gestión" del sidebar:

```blade
<x-ui.sidebar.group name="Gestión">
    <x-ui.sidebar.item href="{{ route('admin.calls.index') }}" icon="document-text">
        {{ __('Convocatorias') }}
    </x-ui.sidebar.item>
</x-ui.sidebar.group>
```

### Breadcrumbs

Cada vista incluye breadcrumbs para navegación:

- **Index**: Panel → Convocatorias
- **Create**: Panel → Convocatorias → Crear
- **Edit**: Panel → Convocatorias → [Título] → Editar
- **Show**: Panel → Convocatorias → [Título]

---

## Traducciones

### Archivos de Traducción

- `lang/es/common.php`
- `lang/en/common.php`

### Claves Principales

```php
'calls' => 'Convocatorias',
'call' => 'Convocatoria',
'create_call' => 'Crear Convocatoria',
'edit_call' => 'Editar Convocatoria',
'call_details' => 'Detalles de la Convocatoria',
// ... más traducciones
```

---

## Testing

### Tests de Componentes Livewire

**Ubicación:** `tests/Feature/Livewire/Admin/Calls/`

#### IndexTest.php (23 tests)
- Autorización (3 tests)
- Listado (5 tests)
- Filtrado (5 tests)
- Ordenación (2 tests)
- Acciones (8 tests)

#### CreateTest.php (19 tests)
- Autorización (3 tests)
- Creación exitosa (4 tests)
- Validación (6 tests)
- Campos dinámicos (6 tests)

#### EditTest.php (10 tests)
- Autorización (3 tests)
- Actualización exitosa (3 tests)
- Validación (3 tests)
- Conversión de formato legacy (1 test)

#### ShowTest.php (12 tests)
- Autorización (2 tests)
- Visualización (4 tests)
- Acciones (6 tests)

### Tests de FormRequests

**Ubicación:** `tests/Feature/Http/Requests/`

#### StoreCallRequestTest.php (7 tests)
- Reglas de validación (6 tests)
- Mensajes personalizados (1 test)

#### UpdateCallRequestTest.php (3 tests)
- Reglas de validación (2 tests)
- Mensajes personalizados (1 test)

### Cobertura Total

- **74 tests** pasando
- **163 assertions** exitosas
- Todos los tests ejecutándose correctamente en paralelo

---

## Optimizaciones Implementadas

### Eager Loading

En `Index` y `Show`:
- `with(['program', 'academicYear', 'creator', 'updater'])`
- `withCount(['phases', 'applications', 'resolutions'])`

### Optimización de Verificación de Relaciones

Antes de eliminar, se usa `withCount()` para verificar relaciones en una sola consulta:

```php
$call->loadCount(['phases', 'applications', 'resolutions']);
if ($call->phases_count > 0 || $call->applications_count > 0 || $call->resolutions_count > 0) {
    // No se puede eliminar
}
```

### Validación en Tiempo Real

Los campos clave tienen validación en tiempo real usando `#[Validate]` y métodos `updated*()`:
- `title` → genera `slug` automáticamente
- `slug` → valida unicidad
- `program_id`, `academic_year_id` → valida existencia
- `number_of_places` → valida mínimo 1
- `estimated_start_date`, `estimated_end_date` → valida que end_date sea posterior

---

## Problemas Resueltos

### 1. Error de Tipo en Edit Component

**Problema:** `TypeError - Unsupported operand types: string + int` al editar convocatorias con formato legacy de `scoring_table`.

**Solución:**
- Normalización de datos en `Edit::mount()` para convertir formato legacy (`{"idioma": 30}`) a formato nuevo (`[{"concept": "idioma", "max_points": 30, "description": ""}]`)
- Uso de `$loop->iteration` en lugar de `$index + 1` en la vista Blade

### 2. Validación de scoring_table

**Problema:** Livewire no encontraba la propiedad `scoring_table` para validación porque se usa `scoringTable` (camelCase) en el componente.

**Solución:**
- Mapeo de datos antes de validar en `Create::store()` y `Edit::update()`
- Uso de `Validator::make()` directamente con array de datos mapeado

### 3. Cascadas de Eliminación

**Problema:** Las foreign keys con `cascadeOnDelete()` no se activan automáticamente con SoftDeletes.

**Solución:**
- Evento `deleting` en el modelo `Call` para manejar cascadas manualmente
- Eliminación física de `phases`, `applications` y `resolutions` cuando se elimina un Call
- Establecimiento de `call_id` a `null` en `events` cuando se elimina un Call

---

## Estructura de Archivos

```
app/Livewire/Admin/Calls/
├── Index.php          # Listado con filtros avanzados
├── Create.php         # Crear convocatoria
├── Edit.php           # Editar convocatoria
└── Show.php           # Vista detalle con fases y resoluciones

resources/views/livewire/admin/calls/
├── index.blade.php    # Vista del listado
├── create.blade.php   # Formulario de creación
├── edit.blade.php     # Formulario de edición
└── show.blade.php     # Vista de detalle

app/Http/Requests/
├── StoreCallRequest.php   # Validación de creación
└── UpdateCallRequest.php # Validación de actualización

tests/Feature/Livewire/Admin/Calls/
├── IndexTest.php      # Tests del listado
├── CreateTest.php      # Tests de creación
├── EditTest.php       # Tests de edición
└── ShowTest.php       # Tests de detalle

tests/Feature/Http/Requests/
├── StoreCallRequestTest.php   # Tests de validación de creación
└── UpdateCallRequestTest.php  # Tests de validación de actualización
```

---

## Próximos Pasos

Según la planificación (`planificacion_pasos.md`), los siguientes pasos son:

### Paso 3.5.4.1: Gestión completa de fases de convocatorias (CRUD)
- Crear componentes Livewire para gestión completa de fases
- Rutas anidadas bajo `/admin/convocatorias/{call}/fases`
- Funcionalidades: crear, editar, eliminar, reordenar, marcar como actual

### Paso 3.5.4.2: Gestión completa de resoluciones (CRUD)
- Crear componentes Livewire para gestión completa de resoluciones
- Rutas anidadas bajo `/admin/convocatorias/{call}/resoluciones`
- Funcionalidades: crear, editar, eliminar, publicar, subir PDFs

---

**Fecha de Creación**: Diciembre 2025  
**Última Actualización**: Diciembre 2025  
**Estado**: ✅ Completado

