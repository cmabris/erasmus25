# CRUD de Años Académicos en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Años Académicos en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Años Académicos permite a los administradores gestionar completamente los años académicos desde el panel de administración. Incluye funcionalidades avanzadas como gestión del "año actual", SoftDeletes, validación de relaciones antes de eliminación permanente, y optimizaciones de rendimiento con caché e índices de base de datos.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar años académicos
- ✅ **SoftDeletes**: Los años académicos nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo administradores pueden eliminar permanentemente, y solo si no hay relaciones
- ✅ **Gestión de "Año Actual"**: Solo un año académico puede estar marcado como actual a la vez
- ✅ **Búsqueda Optimizada**: Búsqueda exacta para formato YYYY-YYYY, búsqueda parcial para otros términos
- ✅ **Caché**: Sistema de caché para el año académico actual (24 horas TTL)
- ✅ **Índices de Base de Datos**: Optimización de consultas con índices en `is_current` y `deleted_at`
- ✅ **Autorización**: Control de acceso mediante `AcademicYearPolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Validación en Tiempo Real**: Validación inmediata de campos con feedback visual

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\AcademicYears\Index`
- **Vista**: `resources/views/livewire/admin/academic-years/index.blade.php`
- **Ruta**: `/admin/anios-academicos` (nombre: `admin.academic-years.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'eliminados')]
public string $showDeleted = '0';

#[Url(as: 'ordenar')]
public string $sortField = 'year';

#[Url(as: 'direccion')]
public string $sortDirection = 'desc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public ?int $academicYearToDelete = null;
public bool $showRestoreModal = false;
public ?int $academicYearToRestore = null;
public bool $showForceDeleteModal = false;
public ?int $academicYearToForceDelete = null;
```

**Métodos Principales:**

- `mount()`: Inicialización del componente con autorización
- `academicYears()`: Propiedad computada que retorna los años académicos paginados con eager loading de relaciones
- `sortBy(string $field)`: Ordena por campo específico, alterna dirección si es el mismo campo
- `toggleCurrent(int $academicYearId)`: Marca/desmarca un año como actual (solo uno puede ser actual)
- `confirmDelete(int $academicYearId)`: Abre modal de confirmación de eliminación
- `delete()`: Elimina un año académico (soft delete) si no tiene relaciones
- `confirmRestore(int $academicYearId)`: Abre modal de confirmación de restauración
- `restore()`: Restaura un año académico eliminado
- `confirmForceDelete(int $academicYearId)`: Abre modal de confirmación de eliminación permanente
- `forceDelete()`: Elimina permanentemente un año académico si no tiene relaciones
- `resetFilters()`: Resetea todos los filtros a valores por defecto
- `updatedSearch()`: Resetea paginación cuando cambia la búsqueda
- `updatedShowDeleted()`: Resetea paginación cuando cambia el filtro de eliminados
- `canCreate()`: Verifica si el usuario puede crear años académicos
- `canViewDeleted()`: Verifica si el usuario puede ver años eliminados
- `canDeleteAcademicYear(AcademicYear $academicYear)`: Verifica si se puede eliminar un año (sin relaciones)

**Características Especiales:**

- **Búsqueda Optimizada**: Si el término de búsqueda coincide con el formato `YYYY-YYYY`, realiza búsqueda exacta en la columna `year`. En caso contrario, realiza búsqueda `LIKE` en `year`, `start_date` y `end_date`.
- **Eager Loading**: Utiliza `withCount()` para cargar conteos de relaciones (`calls`, `newsPosts`, `documents`) sin N+1 queries.
- **Validación de Relaciones**: Antes de eliminar, verifica si existen relaciones activas.

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\AcademicYears\Create`
- **Vista**: `resources/views/livewire/admin/academic-years/create.blade.php`
- **Ruta**: `/admin/anios-academicos/crear` (nombre: `admin.academic-years.create`)

**Propiedades Públicas:**

```php
public string $year = '';
public ?string $start_date = null;
public ?string $end_date = null;
public bool $is_current = false;
```

**Métodos Principales:**

- `mount()`: Inicialización con autorización
- `updatedYear()`: Validación en tiempo real del formato de año (YYYY-YYYY)
- `updatedStartDate()`: Validación en tiempo real de fecha de inicio
- `updatedEndDate()`: Validación en tiempo real de fecha de fin y verificación de que sea posterior a `start_date`
- `updatedIsCurrent()`: Si se marca como actual, desmarca automáticamente otros años académicos como actuales
- `store()`: Crea un nuevo año académico usando `StoreAcademicYearRequest` para validación

**Características Especiales:**

- **Validación en Tiempo Real**: Los campos se validan mientras el usuario escribe usando `wire:model.live.blur`
- **Gestión de "Año Actual"**: Si se marca como actual, automáticamente desmarca otros años usando el método `markAsCurrent()` del modelo
- **Feedback Visual**: Muestra advertencia si ya existe un año marcado como actual

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\AcademicYears\Edit`
- **Vista**: `resources/views/livewire/admin/academic-years/edit.blade.php`
- **Ruta**: `/admin/anios-academicos/{academic_year}/editar` (nombre: `admin.academic-years.edit`)

**Propiedades Públicas:**

```php
public AcademicYear $academicYear;
public string $year = '';
public ?string $start_date = null;
public ?string $end_date = null;
public bool $is_current = false;
```

**Métodos Principales:**

- `mount(AcademicYear $academic_year)`: Carga los datos del año académico y autoriza
- `updatedYear()`: Validación en tiempo real del formato de año
- `updatedStartDate()`: Validación en tiempo real de fecha de inicio
- `updatedEndDate()`: Validación en tiempo real de fecha de fin
- `updatedIsCurrent()`: Si se marca como actual, desmarca otros años (excluyendo el actual)
- `update()`: Actualiza el año académico usando `UpdateAcademicYearRequest` para validación

**Características Especiales:**

- **Precarga de Datos**: Los datos del año académico se cargan automáticamente mediante route model binding
- **Validación de Unicidad**: La validación del año ignora el registro actual para permitir mantener el mismo año
- **Información de Relaciones**: Muestra advertencia si el año académico tiene relaciones (calls, news, documents)

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\AcademicYears\Show`
- **Vista**: `resources/views/livewire/admin/academic-years/show.blade.php`
- **Ruta**: `/admin/anios-academicos/{academic_year}` (nombre: `admin.academic-years.show`)

**Propiedades Públicas:**

```php
public AcademicYear $academicYear;
public ?int $academicYearId = null;
public bool $confirmingDelete = false;
public bool $confirmingRestore = false;
public bool $confirmingForceDelete = false;
```

**Propiedades Computadas:**

- `statistics()`: Retorna array con conteos de relaciones (`total_calls`, `total_news`, `total_documents`)
- `hasRelationships()`: Retorna `true` si el año académico tiene alguna relación
- `editUrl()`: Genera la URL de edición usando el ID del año académico

**Métodos Principales:**

- `mount(AcademicYear $academic_year)`: Carga el año académico con eager loading de relaciones y conteos
- `toggleCurrent()`: Marca/desmarca el año como actual usando `markAsCurrent()` o `unmarkAsCurrent()`
- `delete()`: Elimina el año académico (soft delete) si no tiene relaciones
- `restore()`: Restaura el año académico eliminado
- `forceDelete()`: Elimina permanentemente el año académico si no tiene relaciones

**Características Especiales:**

- **Eager Loading Optimizado**: Utiliza `loadCount()` para cargar conteos de relaciones y `load()` con límites para cargar las últimas 5 entidades de cada tipo
- **Visualización de Relaciones**: Muestra las últimas 5 convocatorias, noticias y documentos relacionados
- **Estadísticas**: Muestra conteos totales de relaciones en cards informativos
- **Formateo de Fechas**: Utiliza null-safe operator (`?->`) para manejar fechas nulas

---

## Modelo AcademicYear

**Ubicación:** `app/Models/AcademicYear.php`

**Traits:**
- `HasFactory`: Para factories de testing
- `SoftDeletes`: Para eliminación suave

**Relaciones:**

```php
public function calls(): HasMany
public function newsPosts(): HasMany
public function documents(): HasMany
```

**Métodos Estáticos:**

- `getCurrent(): ?self`: Obtiene el año académico actual desde caché (TTL: 24 horas)
- `clearCurrentCache(): void`: Limpia la caché del año académico actual

**Métodos de Instancia:**

- `markAsCurrent(): void`: Marca este año como actual y desmarca otros
- `unmarkAsCurrent(): void`: Desmarca este año como actual

**Scopes:**

- `scopeCurrent(Builder $query): Builder`: Filtra solo el año académico actual

**Eventos de Eloquent:**

El modelo implementa eventos en `boot()` para:
- **Soft Delete**: Cuando se hace soft delete, elimina en cascada `Calls` y `NewsPosts` (hard delete), y pone a `null` el `academic_year_id` de `Documents`
- **Cache Invalidation**: Limpia la caché del año actual cuando:
  - Se actualiza el campo `is_current`
  - Se elimina un año académico
  - Se restaura un año académico

**Constantes:**

```php
private const CACHE_KEY_CURRENT = 'academic_year.current';
private const CACHE_TTL_CURRENT = 86400; // 24 horas
```

---

## Política de Autorización

**Ubicación:** `app/Policies/AcademicYearPolicy.php`

**Reglas de Autorización:**

- `viewAny(User $user)`: Todos los usuarios autenticados pueden ver el listado
- `view(User $user, AcademicYear $academicYear)`: Todos los usuarios autenticados pueden ver detalles
- `create(User $user)`: Solo administradores pueden crear
- `update(User $user, AcademicYear $academicYear)`: Solo administradores pueden editar
- `delete(User $user, AcademicYear $academicYear)`: Solo administradores pueden eliminar
- `restore(User $user, AcademicYear $academicYear)`: Solo administradores pueden restaurar
- `forceDelete(User $user, AcademicYear $academicYear)`: Solo administradores pueden eliminar permanentemente

**Pre-autorización:**

- Si el usuario es `SUPER_ADMIN`, se concede acceso total automáticamente

---

## Form Requests

### StoreAcademicYearRequest

**Ubicación:** `app/Http/Requests/StoreAcademicYearRequest.php`

**Reglas de Validación:**

```php
'year' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/', 'unique:academic_years,year'],
'start_date' => ['required', 'date'],
'end_date' => ['required', 'date', 'after:start_date'],
'is_current' => ['sometimes', 'boolean'],
```

**Mensajes Personalizados:**

- Validación de formato de año
- Validación de fechas
- Validación de unicidad

### UpdateAcademicYearRequest

**Ubicación:** `app/Http/Requests/UpdateAcademicYearRequest.php`

**Reglas de Validación:**

Similar a `StoreAcademicYearRequest`, pero con:
- `'year' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/', 'unique:academic_years,year,' . $this->academic_year->id]`

La regla de unicidad ignora el registro actual para permitir mantener el mismo año.

---

## Rutas

**Ubicación:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/anios-academicos', \App\Livewire\Admin\AcademicYears\Index::class)
        ->name('academic-years.index');
    
    Route::get('/anios-academicos/crear', \App\Livewire\Admin\AcademicYears\Create::class)
        ->name('academic-years.create');
    
    Route::get('/anios-academicos/{academic_year}', \App\Livewire\Admin\AcademicYears\Show::class)
        ->name('academic-years.show');
    
    Route::get('/anios-academicos/{academic_year}/editar', \App\Livewire\Admin\AcademicYears\Edit::class)
        ->name('academic-years.edit');
});
```

---

## Migraciones

### 1. Crear Tabla AcademicYears

**Archivo:** `database/migrations/2025_12_12_193647_create_academic_years_table.php`

```php
Schema::create('academic_years', function (Blueprint $table) {
    $table->id();
    $table->string('year')->unique();
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_current')->default(false);
    $table->timestamps();
});
```

### 2. Agregar SoftDeletes

**Archivo:** `database/migrations/2025_12_28_173037_add_soft_deletes_to_academic_years_table.php`

```php
Schema::table('academic_years', function (Blueprint $table) {
    $table->softDeletes();
});
```

### 3. Agregar Índices

**Archivo:** `database/migrations/2025_12_28_185528_add_indexes_to_academic_years_table.php`

```php
Schema::table('academic_years', function (Blueprint $table) {
    $table->index('is_current', 'academic_years_is_current_index');
    $table->index('deleted_at', 'academic_years_deleted_at_index');
});
```

**Propósito de los Índices:**

- `is_current`: Optimiza consultas para encontrar el año académico actual
- `deleted_at`: Optimiza consultas de soft deletes y filtros de eliminados

---

## Traducciones

### Español (`lang/es/common.php`)

```php
'nav' => [
    'academic_years' => 'Años Académicos',
    // ...
],

'messages' => [
    'created_successfully' => 'Año académico creado correctamente',
    'updated_successfully' => 'Año académico actualizado correctamente',
    'deleted_successfully' => 'Año académico eliminado correctamente',
    'restored_successfully' => 'Año académico restaurado correctamente',
    'permanently_deleted_successfully' => 'Año académico eliminado permanentemente',
    'marked_as_current' => 'Año académico marcado como actual',
    'unmarked_as_current' => 'Año académico desmarcado como actual',
    // ...
],

'errors' => [
    'cannot_delete_with_relations' => 'No se puede eliminar el año académico porque tiene relaciones asociadas',
    // ...
],

'academic_years' => [
    'year' => 'Año Académico',
    'start_date' => 'Fecha de Inicio',
    'end_date' => 'Fecha de Fin',
    'is_current' => 'Año Actual',
    'current_year' => 'Año Actual',
    'statistics' => 'Estadísticas',
    'total_calls' => 'Total Convocatorias',
    'total_news' => 'Total Noticias',
    'total_documents' => 'Total Documentos',
    'related_calls' => 'Convocatorias Relacionadas',
    'related_news' => 'Noticias Relacionadas',
    'related_documents' => 'Documentos Relacionados',
    // ...
],
```

### Inglés (`lang/en/common.php`)

Traducciones equivalentes en inglés.

---

## Tests

### Cobertura de Tests

**Total:** 61 tests pasando (149 aserciones)

### Estructura de Tests

#### IndexTest (`tests/Feature/Livewire/Admin/AcademicYears/IndexTest.php`)

- ✅ Autorización (3 tests)
- ✅ Listado (3 tests)
- ✅ Búsqueda (3 tests)
- ✅ Ordenación (3 tests)
- ✅ Filtros (2 tests)
- ✅ Paginación (2 tests)
- ✅ Toggle Current (2 tests)
- ✅ Soft Delete (2 tests)
- ✅ Restore (1 test)
- ✅ Force Delete (2 tests)

**Total:** 23 tests

#### CreateTest (`tests/Feature/Livewire/Admin/AcademicYears/CreateTest.php`)

- ✅ Autorización (3 tests)
- ✅ Creación Exitosa (3 tests)
- ✅ Validación (7 tests)

**Total:** 13 tests

#### EditTest (`tests/Feature/Livewire/Admin/AcademicYears/EditTest.php`)

- ✅ Autorización (3 tests)
- ✅ Actualización Exitosa (3 tests)
- ✅ Validación (4 tests)

**Total:** 10 tests

#### ShowTest (`tests/Feature/Livewire/Admin/AcademicYears/ShowTest.php`)

- ✅ Autorización (3 tests)
- ✅ Visualización (4 tests)
- ✅ Toggle Current (3 tests)
- ✅ Soft Delete (2 tests)
- ✅ Restore (1 test)
- ✅ Force Delete (2 tests)

**Total:** 15 tests

### Tests de Modelo

**Archivo:** `tests/Feature/Models/AcademicYearTest.php`

- ✅ Relaciones (3 tests)
- ✅ Cascade Delete (2 tests)
- ✅ NullOnDelete (1 test)
- ✅ Otros (2 tests)

**Total:** 8 tests

---

## Optimizaciones Implementadas

### 1. Eager Loading

**Problema:** N+1 queries al cargar conteos de relaciones.

**Solución:** Uso de `withCount()` y `loadCount()` en lugar de accesores que ejecutan `count()`.

**Ejemplo:**

```php
// Antes (N+1 queries)
$academicYear->calls()->count();
$academicYear->newsPosts()->count();
$academicYear->documents()->count();

// Después (1 query con agregación)
$academicYear->loadCount(['calls', 'newsPosts', 'documents']);
$academicYear->calls_count; // Acceso directo al conteo
```

### 2. Caché del Año Actual

**Problema:** Consultas repetidas para obtener el año académico actual.

**Solución:** Sistema de caché con TTL de 24 horas.

**Implementación:**

```php
public static function getCurrent(): ?self
{
    return Cache::remember(self::CACHE_KEY_CURRENT, self::CACHE_TTL_CURRENT, function () {
        return static::where('is_current', true)->first();
    });
}
```

**Invalidación Automática:**

- Cuando se actualiza `is_current`
- Cuando se elimina un año académico
- Cuando se restaura un año académico

### 3. Índices de Base de Datos

**Índices Agregados:**

- `is_current`: Para consultas del año actual
- `deleted_at`: Para consultas de soft deletes

**Impacto:** Mejora significativa en consultas que filtran por estos campos.

### 4. Búsqueda Optimizada

**Problema:** Búsqueda genérica con `LIKE` en múltiples columnas.

**Solución:** Detección de formato `YYYY-YYYY` para búsqueda exacta.

**Implementación:**

```php
if (preg_match('/^\d{4}-\d{4}$/', $this->search)) {
    $query->where('year', $this->search); // Búsqueda exacta
} else {
    // Búsqueda LIKE en múltiples columnas
    $query->where(function ($q) {
        $q->where('year', 'like', "%{$this->search}%")
            ->orWhere('start_date', 'like', "%{$this->search}%")
            ->orWhere('end_date', 'like', "%{$this->search}%");
    });
}
```

---

## Funcionalidades Especiales

### Gestión del "Año Actual"

Solo un año académico puede estar marcado como `is_current = true` a la vez. Cuando se marca un año como actual:

1. Se desmarcan automáticamente todos los demás años académicos
2. Se limpia la caché del año actual
3. Se muestra feedback visual al usuario

**Implementación:**

```php
public function markAsCurrent(): void
{
    self::where('is_current', true)
        ->where('id', '!=', $this->id)
        ->update(['is_current' => false]);

    $this->update(['is_current' => true]);
    self::clearCurrentCache();
}
```

### Manejo de Relaciones en Soft Delete

Cuando se hace soft delete de un `AcademicYear`:

- **Calls**: Se eliminan permanentemente (hard delete) en cascada
- **NewsPosts**: Se eliminan permanentemente (hard delete) en cascada
- **Documents**: Se pone `academic_year_id` a `null` (nullOnDelete)

**Implementación:**

```php
static::deleting(function ($academicYear) {
    if ($academicYear->isForceDeleting()) {
        return; // Dejar que las restricciones de BD manejen el force delete
    }

    // Para soft delete, manejar relaciones manualmente
    $academicYear->calls()->each(fn ($call) => $call->forceDelete());
    $academicYear->newsPosts()->each(fn ($newsPost) => $newsPost->forceDelete());
    $academicYear->documents()->update(['academic_year_id' => null]);
});
```

### Validación de Relaciones Antes de Eliminar

Antes de permitir la eliminación (soft o force delete), se verifica si existen relaciones activas:

```php
$hasRelations = $academicYear->calls()->exists()
    || $academicYear->newsPosts()->exists()
    || $academicYear->documents()->exists();

if ($hasRelations) {
    // Mostrar error y prevenir eliminación
}
```

---

## Guía de Uso

### Crear un Año Académico

1. Navegar a `/admin/anios-academicos`
2. Hacer clic en "Crear Año Académico"
3. Completar el formulario:
   - **Año**: Formato `YYYY-YYYY` (ej: `2024-2025`)
   - **Fecha de Inicio**: Fecha de inicio del año académico
   - **Fecha de Fin**: Fecha de fin (debe ser posterior a la fecha de inicio)
   - **Año Actual**: Marcar si este es el año académico actual
4. Hacer clic en "Guardar"

### Editar un Año Académico

1. Desde el listado, hacer clic en el botón "Editar" del año deseado
2. Modificar los campos necesarios
3. Hacer clic en "Actualizar"

### Marcar como Año Actual

**Desde el Listado:**
- Hacer clic en el icono de estrella del año deseado

**Desde la Vista de Detalle:**
- Hacer clic en el botón "Marcar como Actual" o "Desmarcar como Actual"

**Nota:** Al marcar un año como actual, automáticamente se desmarca el anterior.

### Eliminar un Año Académico

1. Desde el listado o vista de detalle, hacer clic en "Eliminar"
2. Confirmar la eliminación en el modal
3. El año se marca como eliminado (soft delete) y puede restaurarse

**Restricción:** No se puede eliminar si tiene relaciones activas (calls, news, documents).

### Restaurar un Año Académico Eliminado

1. Activar el filtro "Mostrar eliminados" en el listado
2. Hacer clic en "Restaurar" del año deseado
3. Confirmar la restauración

### Eliminar Permanentemente

1. Activar el filtro "Mostrar eliminados"
2. Hacer clic en "Eliminar Permanentemente"
3. Confirmar la eliminación

**Restricción:** Solo se puede eliminar permanentemente si no tiene relaciones activas.

---

## Notas Técnicas

### Route Model Binding

Los componentes `Show` y `Edit` utilizan route model binding con el parámetro `{academic_year}` (snake_case). El método `mount()` debe recibir el parámetro con el mismo nombre:

```php
public function mount(AcademicYear $academic_year): void
{
    // ...
}
```

### Serialización de Modelos en Livewire

Los modelos Eloquent con relaciones cargadas se serializan correctamente en Livewire. Sin embargo, para generar URLs en Blade, es recomendable almacenar el ID por separado:

```php
public ?int $academicYearId = null;

public function mount(AcademicYear $academic_year): void
{
    $this->academicYearId = $academic_year->id;
    $this->academicYear = $academic_year->load([...]);
}
```

### Formateo de Fechas Nulas

En las vistas Blade, utilizar el null-safe operator para manejar fechas que pueden ser `null`:

```blade
{{ $academicYear->start_date?->format('d/m/Y') ?? '-' }}
```

### Eventos de Livewire

Los componentes disparan eventos para notificaciones:

- `academic-year-created`
- `academic-year-updated`
- `academic-year-deleted`
- `academic-year-restored`
- `academic-year-force-deleted`
- `academic-year-delete-error`
- `academic-year-force-delete-error`

---

## Mejoras Futuras

### Posibles Mejoras

1. **Exportación**: Exportar listado de años académicos a Excel/PDF
2. **Duplicación**: Duplicar un año académico con todas sus relaciones
3. **Validación de Solapamiento**: Validar que las fechas de años académicos no se solapen
4. **Historial de Cambios**: Registrar cambios en el año actual
5. **Dashboard**: Mostrar estadísticas del año académico actual en el dashboard
6. **API**: Endpoints REST para consultar años académicos
7. **Filtros Avanzados**: Filtrar por rango de fechas
8. **Bulk Actions**: Acciones masivas (marcar varios como actuales, eliminar varios, etc.)

---

## Archivos Creados/Modificados

### Componentes Livewire

- ✅ `app/Livewire/Admin/AcademicYears/Index.php`
- ✅ `app/Livewire/Admin/AcademicYears/Create.php`
- ✅ `app/Livewire/Admin/AcademicYears/Edit.php`
- ✅ `app/Livewire/Admin/AcademicYears/Show.php`

### Vistas Blade

- ✅ `resources/views/livewire/admin/academic-years/index.blade.php`
- ✅ `resources/views/livewire/admin/academic-years/create.blade.php`
- ✅ `resources/views/livewire/admin/academic-years/edit.blade.php`
- ✅ `resources/views/livewire/admin/academic-years/show.blade.php`

### Modelo y Política

- ✅ `app/Models/AcademicYear.php` (modificado)
- ✅ `app/Policies/AcademicYearPolicy.php` (ya existía)

### Form Requests

- ✅ `app/Http/Requests/StoreAcademicYearRequest.php` (adaptado)
- ✅ `app/Http/Requests/UpdateAcademicYearRequest.php` (adaptado)

### Migraciones

- ✅ `database/migrations/2025_12_28_173037_add_soft_deletes_to_academic_years_table.php`
- ✅ `database/migrations/2025_12_28_185528_add_indexes_to_academic_years_table.php`

### Rutas

- ✅ `routes/web.php` (modificado)

### Traducciones

- ✅ `lang/es/common.php` (modificado)
- ✅ `lang/en/common.php` (modificado)

### Navegación

- ✅ `resources/views/components/layouts/app/sidebar.blade.php` (modificado)

### Tests

- ✅ `tests/Feature/Livewire/Admin/AcademicYears/IndexTest.php`
- ✅ `tests/Feature/Livewire/Admin/AcademicYears/CreateTest.php`
- ✅ `tests/Feature/Livewire/Admin/AcademicYears/EditTest.php`
- ✅ `tests/Feature/Livewire/Admin/AcademicYears/ShowTest.php`

---

## Referencias

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Livewire 3 Documentation](https://livewire.laravel.com/docs)
- [Flux UI Documentation](https://flux.laravel.com/docs)
- [Laravel SoftDeletes](https://laravel.com/docs/12.x/eloquent#soft-deleting)
- [Laravel Caching](https://laravel.com/docs/12.x/cache)

---

**Última actualización:** 28 de diciembre de 2025

