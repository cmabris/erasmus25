# CRUD de Categorías de Documentos en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Categorías de Documentos en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Categorías de Documentos permite a los administradores gestionar completamente las categorías disponibles para los documentos desde el panel de administración. Incluye funcionalidades como SoftDeletes, validación de relaciones antes de eliminación permanente, generación automática de slugs, gestión de orden, y visualización de documentos asociados.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar categorías
- ✅ **SoftDeletes**: Las categorías nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones con documentos
- ✅ **Generación Automática de Slug**: El slug se genera automáticamente desde el nombre
- ✅ **Validación de Relaciones**: No se puede eliminar una categoría si tiene documentos asociados
- ✅ **Gestión de Orden**: Campo `order` para controlar la visualización
- ✅ **Búsqueda y Filtros**: Búsqueda por nombre, slug o descripción; filtro de eliminados
- ✅ **Ordenación**: Ordenación por nombre u orden en dirección ascendente/descendente
- ✅ **Autorización**: Control de acceso mediante `DocumentCategoryPolicy` (usa permisos del módulo `documents.*`)
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 111 tests pasando (Index: 25, Create: 18, Edit: 21, Show: 15, FormRequests: 32)

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\DocumentCategories\Index`
- **Vista**: `resources/views/livewire/admin/document-categories/index.blade.php`
- **Ruta**: `/admin/categorias` (nombre: `admin.document-categories.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'eliminados')]
public string $showDeleted = '0';

#[Url(as: 'ordenar')]
public string $sortField = 'order';

#[Url(as: 'direccion')]
public string $sortDirection = 'asc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public ?int $documentCategoryToDelete = null;
public bool $showRestoreModal = false;
public ?int $documentCategoryToRestore = null;
public bool $showForceDeleteModal = false;
public ?int $documentCategoryToForceDelete = null;
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de acceso mediante `DocumentCategoryPolicy::viewAny()`.

#### `documentCategories()` (Computed)
Retorna la lista paginada de categorías con filtros aplicados.

**Filtros aplicados:**
- Por estado eliminado (`showDeleted`: '0' = no eliminados, '1' = solo eliminados)
- Por búsqueda (nombre, slug o descripción)

**Ordenación:**
- Por campo configurable (`sortField`: 'name' u 'order') y dirección (`sortDirection`: 'asc' o 'desc')
- Orden secundario por ID ascendente

**Eager Loading:**
- `withCount(['documents'])` para mostrar contador de documentos asociados y validar relaciones antes de eliminar

#### `sortBy(string $field)`
Cambia el campo de ordenación. Si es el mismo campo, alterna la dirección.

#### `confirmDelete(int $documentCategoryId)`
Abre el modal de confirmación para eliminar una categoría.

#### `delete()`
Elimina una categoría usando SoftDeletes. Verifica que no tenga documentos asociados antes de eliminar.

#### `confirmRestore(int $documentCategoryId)`
Abre el modal de confirmación para restaurar una categoría eliminada.

#### `restore()`
Restaura una categoría eliminada. Requiere permiso `DOCUMENTS_DELETE`.

#### `confirmForceDelete(int $documentCategoryId)`
Abre el modal de confirmación para eliminar permanentemente una categoría.

#### `forceDelete()`
Elimina permanentemente una categoría. Solo disponible para super-admin y solo si no tiene documentos asociados.

#### `resetFilters()`
Resetea todos los filtros a sus valores por defecto.

#### `canCreate()`
Verifica si el usuario puede crear categorías mediante `DocumentCategoryPolicy::create()`.

#### `canViewDeleted()`
Verifica si el usuario puede ver categorías eliminadas mediante `DocumentCategoryPolicy::viewAny()`.

#### `canDeleteDocumentCategory(DocumentCategory $documentCategory)`
Verifica si una categoría puede ser eliminada (no tiene documentos asociados).

**Vista:**

La vista incluye:
- Header con título, descripción y botón "Crear Categoría" (condicional)
- Breadcrumbs
- Barra de búsqueda y filtro "Mostrar Eliminados"
- Tabla responsive con columnas:
  - Orden
  - Nombre
  - Slug
  - Descripción (truncada)
  - Documentos asociados (contador)
  - Fecha de creación
  - Acciones (ver, editar, eliminar, restaurar, force delete)
- Estados de carga con `wire:loading`
- Estado vacío con acción para crear primera categoría
- Modales de confirmación para eliminar, restaurar y force delete
- Notificaciones toast para éxito/error

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\DocumentCategories\Create`
- **Vista**: `resources/views/livewire/admin/document-categories/create.blade.php`
- **Ruta**: `/admin/categorias/crear` (nombre: `admin.document-categories.create`)

**Propiedades Públicas:**

```php
public string $name = '';
public string $slug = '';
public ?string $description = null;
public ?int $order = 0;
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de acceso mediante `DocumentCategoryPolicy::create()`.

#### `updatedName()`
Genera automáticamente el slug desde el nombre cuando el nombre cambia, solo si el slug está vacío.

#### `updatedSlug()`
Valida el slug en tiempo real cuando cambia, verificando que sea único.

#### `store()`
Crea una nueva categoría usando `StoreDocumentCategoryRequest` para validación. Genera el slug automáticamente si no se proporciona. Establece `order` a 0 si es null. Despacha evento de éxito y redirige al listado.

**Vista:**

La vista incluye:
- Breadcrumbs
- Formulario con campos:
  - Nombre (requerido, con validación en tiempo real)
  - Slug (opcional, con validación en tiempo real y tooltip)
  - Descripción (opcional, textarea)
  - Orden (opcional, número entero)
- Botones de acción (Guardar, Cancelar)
- Validación visual de errores
- Tooltips informativos

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\DocumentCategories\Edit`
- **Vista**: `resources/views/livewire/admin/document-categories/edit.blade.php`
- **Ruta**: `/admin/categorias/{document_category}/editar` (nombre: `admin.document-categories.edit`)

**Propiedades Públicas:**

```php
public DocumentCategory $documentCategory;
public string $name = '';
public string $slug = '';
public ?string $description = null;
public ?int $order = null;
```

**Métodos Principales:**

#### `mount(DocumentCategory $document_category)`
Inicializa el componente, verifica permisos mediante `DocumentCategoryPolicy::update()`, y carga los datos de la categoría.

#### `updatedName()`
Genera automáticamente el slug desde el nombre cuando el nombre cambia, solo si el slug está vacío o coincide con el slug del nombre original.

#### `updatedSlug()`
Valida el slug en tiempo real cuando cambia, verificando que sea único (ignorando el registro actual).

#### `update()`
Actualiza la categoría usando `UpdateDocumentCategoryRequest` para validación. Construye manualmente el array de datos para asegurar que todos los campos estén presentes (usando valores del modelo si Livewire no los envió). Valida usando `Validator::make()` directamente para asegurar que todos los campos se validen correctamente. Despacha evento de éxito y redirige al listado.

**Vista:**

La vista incluye:
- Breadcrumbs
- Información adicional (fechas de creación/actualización, documentos asociados)
- Formulario pre-rellenado con campos:
  - Nombre (requerido, con validación en tiempo real)
  - Slug (opcional, con validación en tiempo real y tooltip)
  - Descripción (opcional, textarea)
  - Orden (opcional, número entero)
- Botones de acción (Guardar, Cancelar)
- Validación visual de errores
- Tooltips informativos

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\DocumentCategories\Show`
- **Vista**: `resources/views/livewire/admin/document-categories/show.blade.php`
- **Ruta**: `/admin/categorias/{document_category}` (nombre: `admin.document-categories.show`)

**Propiedades Públicas:**

```php
public DocumentCategory $documentCategory;
public bool $showDeleteModal = false;
public bool $showRestoreModal = false;
public bool $showForceDeleteModal = false;
```

**Métodos Principales:**

#### `mount(DocumentCategory $document_category)`
Inicializa el componente, verifica permisos mediante `DocumentCategoryPolicy::view()`, y carga la categoría con documentos asociados (últimos 10) y contador.

#### `statistics()` (Computed)
Retorna estadísticas de la categoría:
- `total_documents`: Número total de documentos asociados

#### `delete()`
Elimina la categoría usando SoftDeletes. Verifica que no tenga documentos asociados antes de eliminar. Si tiene relaciones, muestra error y no permite la eliminación.

#### `restore()`
Restaura una categoría eliminada. Requiere permiso `DOCUMENTS_DELETE`. Refresca el modelo después de restaurar.

#### `forceDelete()`
Elimina permanentemente la categoría. Solo disponible para super-admin. Verifica que no tenga documentos asociados antes de eliminar. Si tiene relaciones, muestra error y no permite la eliminación.

#### `canDelete()`
Verifica si la categoría puede ser eliminada (tiene permiso y no tiene documentos asociados).

#### `hasRelationships()` (Computed)
Verifica si la categoría tiene documentos asociados.

**Vista:**

La vista incluye:
- Breadcrumbs
- Tarjetas de información:
  - Información general (nombre, slug, descripción, orden)
  - Estadísticas (total de documentos)
  - Metadatos (fechas de creación/actualización)
- Listado de documentos asociados (últimos 10) con enlaces
- Botones de acción (Editar, Eliminar, Restaurar, Force Delete) según permisos
- Modales de confirmación para eliminar, restaurar y force delete
- Notificaciones toast para éxito/error

---

## Form Requests

### StoreDocumentCategoryRequest

**Ubicación:** `app/Http/Requests/StoreDocumentCategoryRequest.php`

**Autorización:**
- Verifica permiso `create` en `DocumentCategory` mediante `DocumentCategoryPolicy::create()`

**Reglas de Validación:**

```php
'name' => ['required', 'string', 'max:255', Rule::unique('document_categories', 'name')],
'slug' => ['nullable', 'string', 'max:255', Rule::unique('document_categories', 'slug')],
'description' => ['nullable', 'string'],
'order' => ['nullable', 'integer'],
```

**Mensajes Personalizados:**
- Todos los mensajes están en español e inglés mediante `__()`
- Mensajes específicos para cada regla de validación

---

### UpdateDocumentCategoryRequest

**Ubicación:** `app/Http/Requests/UpdateDocumentCategoryRequest.php`

**Autorización:**
- Verifica permiso `update` en el `DocumentCategory` específico mediante `DocumentCategoryPolicy::update()`
- Obtiene el `DocumentCategory` desde el parámetro de ruta `document_category`

**Reglas de Validación:**

```php
'name' => ['required', 'string', 'max:255', Rule::unique('document_categories', 'name')->ignore($documentCategoryId)],
'slug' => ['nullable', 'string', 'max:255', Rule::unique('document_categories', 'slug')->ignore($documentCategoryId)],
'description' => ['nullable', 'string'],
'order' => ['nullable', 'integer'],
```

**Mensajes Personalizados:**
- Todos los mensajes están en español e inglés mediante `__()`
- Mensajes específicos para cada regla de validación

---

## Modelo DocumentCategory

**Ubicación:** `app/Models/DocumentCategory.php`

**Traits:**
- `HasFactory`
- `SoftDeletes`

**Fillable:**
```php
protected $fillable = [
    'name',
    'slug',
    'description',
    'order',
];
```

**Casts:**
```php
protected function casts(): array
{
    return [
        'order' => 'integer',
    ];
}
```

**Relaciones:**
- `documents()`: `HasMany` con `Document`

**Eventos del Modelo:**
- `creating`: Genera automáticamente el slug desde el nombre si está vacío

---

## Migraciones

### Añadir SoftDeletes

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_add_soft_deletes_to_document_categories_table.php`

```php
Schema::table('document_categories', function (Blueprint $table) {
    $table->softDeletes();
    $table->index('deleted_at', 'document_categories_deleted_at_index');
});
```

### Añadir Índices

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_add_indexes_to_document_categories_table.php`

```php
Schema::table('document_categories', function (Blueprint $table) {
    $table->index('name', 'document_categories_name_index');
    $table->index('order', 'document_categories_order_index');
});
```

---

## Rutas

**Ubicación:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('document-categories', \App\Livewire\Admin\DocumentCategories\Index::class)
        ->only(['index'])
        ->names(['index' => 'document-categories.index']);
    
    Route::get('categorias/crear', \App\Livewire\Admin\DocumentCategories\Create::class)
        ->name('document-categories.create');
    
    Route::get('categorias/{document_category}/editar', \App\Livewire\Admin\DocumentCategories\Edit::class)
        ->name('document-categories.edit');
    
    Route::get('categorias/{document_category}', \App\Livewire\Admin\DocumentCategories\Show::class)
        ->name('document-categories.show');
});
```

---

## Navegación

**Ubicación:** `resources/views/components/layouts/app/sidebar.blade.php`

Se añadió un nuevo elemento de navegación en el grupo "Contenido":

```blade
@can('viewAny', \App\Models\DocumentCategory::class)
    <x-ui.sidebar.item
        :href="route('admin.document-categories.index')"
        :active="request()->routeIs('admin.document-categories.*')"
        icon="folder"
    >
        {{ __('common.nav.document_categories') }}
    </x-ui.sidebar.item>
@endcan
```

**Traducciones:**
- `lang/es/common.php`: `'document_categories' => 'Categorías de Documentos'`
- `lang/en/common.php`: `'document_categories' => 'Document Categories'`

---

## Tests

### Tests de Componentes Livewire

**Ubicación:** `tests/Feature/Livewire/Admin/DocumentCategories/`

#### IndexTest (25 tests)
- Tests de autorización
- Tests de listado con datos
- Tests de búsqueda
- Tests de ordenación
- Tests de filtros (eliminados)
- Tests de paginación
- Tests de soft delete, restauración y force delete
- Tests de validación de relaciones
- Tests de helper methods

#### CreateTest (18 tests)
- Tests de autorización
- Tests de creación exitosa (con y sin campos opcionales)
- Tests de eventos y redirección
- Tests de validación (campos requeridos, longitud máxima, unicidad)
- Tests de generación automática de slug

#### EditTest (21 tests)
- Tests de autorización
- Tests de carga de datos
- Tests de actualización exitosa (completa y parcial)
- Tests de validación (campos requeridos, longitud máxima, unicidad ignorando registro actual)
- Tests de generación automática de slug

#### ShowTest (15 tests)
- Tests de autorización
- Tests de visualización de detalles y estadísticas
- Tests de documentos asociados
- Tests de acciones (eliminar, restaurar, force delete)
- Tests de validación de relaciones

### Tests de FormRequests

**Ubicación:** `tests/Feature/Http/Requests/`

#### StoreDocumentCategoryRequestTest (16 tests)
- Tests de validación de campos requeridos
- Tests de validación de tipos de datos
- Tests de validación de longitud máxima
- Tests de validación de unicidad
- Tests de campos nullable
- Tests de mensajes personalizados

#### UpdateDocumentCategoryRequestTest (16 tests)
- Tests de validación de campos requeridos
- Tests de validación de tipos de datos
- Tests de validación de longitud máxima
- Tests de validación de unicidad ignorando registro actual
- Tests de permitir mantener el mismo name/slug
- Tests de campos nullable
- Tests de mensajes personalizados

### Tests de Modelos

**Ubicación:** `tests/Feature/Models/DocumentCategoryTest.php`

Se actualizaron los tests para reflejar el comportamiento correcto con SoftDeletes:
- `it('cannot be deleted when it has associated documents')` - Verifica que no se puede eliminar si hay documentos
- `it('deletes documents in cascade when category is force deleted')` - Verifica que `forceDelete()` activa `cascadeOnDelete()`

**Total:** 111 tests pasando

---

## Características Especiales

### SoftDeletes y Cascade Delete

- **Soft Delete (`delete()`)**: No activa el `cascadeOnDelete()` de la base de datos. Los documentos asociados NO se eliminan.
- **Force Delete (`forceDelete()`)**: Activa el `cascadeOnDelete()` de la base de datos. Los documentos asociados SÍ se eliminan.
- **Validación de Relaciones**: Antes de eliminar (soft o force), se verifica que no haya documentos asociados. Si hay relaciones, se muestra un error y no se permite la eliminación.

### Generación Automática de Slug

- El slug se genera automáticamente desde el nombre usando `Str::slug()` cuando:
  - Se crea una nueva categoría y el slug está vacío
  - Se edita una categoría y el slug está vacío o coincide con el slug del nombre original
- El usuario puede editar el slug manualmente si lo desea
- El slug se valida para asegurar que sea único

### Campo Order

- El campo `order` permite controlar el orden de visualización de las categorías
- Es opcional (puede ser null, pero se establece a 0 por defecto en la base de datos)
- El listado se ordena por `order` ascendente por defecto

### Validación en Tiempo Real

- Los campos `name` y `slug` tienen validación en tiempo real cuando cambian
- Los errores de validación se muestran inmediatamente sin necesidad de enviar el formulario

---

## Notas Importantes

### Autorización

- Las categorías usan los permisos del módulo `documents.*`:
  - `DOCUMENTS_VIEW`: Ver categorías
  - `DOCUMENTS_CREATE`: Crear categorías
  - `DOCUMENTS_EDIT`: Editar categorías
  - `DOCUMENTS_DELETE`: Eliminar/restaurar categorías
- El rol `super-admin` tiene acceso total
- El rol `admin` tiene acceso total
- El rol `editor` puede ver, crear y editar
- El rol `viewer` solo puede ver

### Factory

**Ubicación:** `database/factories/DocumentCategoryFactory.php`

La factory genera nombres y slugs únicos para evitar conflictos en tests paralelos:

```php
$name = fake()->unique()->word();
return [
    'name' => $name,
    'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(3),
    'description' => fake()->optional()->paragraph(),
    'order' => fake()->numberBetween(0, 10),
];
```

---

**Fecha de Creación**: Enero 2026  
**Estado**: ✅ Completado - 111 tests pasando

