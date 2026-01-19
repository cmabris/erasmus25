# CRUD de Etiquetas de Noticias en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Etiquetas de Noticias en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Etiquetas de Noticias permite a los administradores gestionar completamente las etiquetas disponibles para las noticias desde el panel de administración. Incluye funcionalidades como SoftDeletes, validación de relaciones antes de eliminación permanente, generación automática de slugs, y gestión de noticias asociadas.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar etiquetas
- ✅ **SoftDeletes**: Las etiquetas nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones con noticias
- ✅ **Generación Automática de Slug**: El slug se genera automáticamente desde el nombre
- ✅ **Validación de Relaciones**: No se puede eliminar una etiqueta si tiene noticias asociadas
- ✅ **Búsqueda y Filtros**: Búsqueda por nombre o slug, filtro de eliminados
- ✅ **Ordenación**: Ordenación por nombre o slug en dirección ascendente/descendente
- ✅ **Autorización**: Control de acceso mediante `NewsTagPolicy` (usa permisos del módulo `news.*`)
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 103 tests pasando con 100% de cobertura de líneas

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\NewsTags\Index`
- **Vista**: `resources/views/livewire/admin/news-tags/index.blade.php`
- **Ruta**: `/admin/etiquetas` (nombre: `admin.news-tags.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'eliminados')]
public string $showDeleted = '0';

#[Url(as: 'ordenar')]
public string $sortField = 'name';

#[Url(as: 'direccion')]
public string $sortDirection = 'asc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public ?int $newsTagToDelete = null;
public bool $showRestoreModal = false;
public ?int $newsTagToRestore = null;
public bool $showForceDeleteModal = false;
public ?int $newsTagToForceDelete = null;
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de acceso mediante `NewsTagPolicy::viewAny()`.

#### `newsTags()` (Computed)
Retorna la lista paginada de etiquetas con filtros aplicados.

**Filtros aplicados:**
- Por estado eliminado (`showDeleted`: '0' = no eliminados, '1' = solo eliminados)
- Por búsqueda (nombre o slug)

**Ordenación:**
- Por campo configurable (`sortField`: 'name' o 'slug') y dirección (`sortDirection`: 'asc' o 'desc')
- Orden secundario por ID ascendente

**Eager Loading:**
- `withCount(['newsPosts'])` para mostrar contador de noticias asociadas y validar relaciones antes de eliminar

#### `sortBy(string $field)`
Cambia el campo de ordenación. Si es el mismo campo, alterna la dirección.

#### `confirmDelete(int $newsTagId)`
Abre el modal de confirmación para eliminar una etiqueta.

#### `delete()`
Elimina una etiqueta usando SoftDeletes. Verifica que no tenga noticias asociadas antes de eliminar.

#### `confirmRestore(int $newsTagId)`
Abre el modal de confirmación para restaurar una etiqueta eliminada.

#### `restore()`
Restaura una etiqueta eliminada. Requiere permiso `NEWS_DELETE`.

#### `confirmForceDelete(int $newsTagId)`
Abre el modal de confirmación para eliminar permanentemente una etiqueta.

#### `forceDelete()`
Elimina permanentemente una etiqueta. Solo disponible para super-admin y solo si no tiene noticias asociadas.

#### `resetFilters()`
Resetea todos los filtros a sus valores por defecto.

#### `canCreate()`
Verifica si el usuario puede crear etiquetas mediante `NewsTagPolicy::create()`.

#### `canViewDeleted()`
Verifica si el usuario puede ver etiquetas eliminadas mediante `NewsTagPolicy::viewAny()`.

#### `canDeleteNewsTag(NewsTag $newsTag)`
Verifica si una etiqueta puede ser eliminada (no tiene noticias asociadas).

**Vista:**

La vista incluye:
- Header con título, descripción y botón "Crear Etiqueta" (condicional)
- Breadcrumbs
- Barra de búsqueda y filtro "Mostrar Eliminados"
- Tabla responsive con columnas:
  - Nombre
  - Slug
  - Noticias asociadas (contador)
  - Fecha de creación
  - Acciones (ver, editar, eliminar, restaurar, force delete)
- Estados de carga con `wire:loading`
- Estado vacío con acción para crear primera etiqueta
- Modales de confirmación para eliminar, restaurar y force delete
- Notificaciones toast para éxito/error

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\NewsTags\Create`
- **Vista**: `resources/views/livewire/admin/news-tags/create.blade.php`
- **Ruta**: `/admin/etiquetas/crear` (nombre: `admin.news-tags.create`)

**Propiedades Públicas:**

```php
public string $name = '';
public string $slug = '';
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de acceso mediante `NewsTagPolicy::create()`.

#### `updatedName()`
Genera automáticamente el slug desde el nombre cuando el nombre cambia, solo si el slug está vacío o coincide con el slug del nombre original.

#### `updatedSlug()`
Valida el slug en tiempo real cuando cambia, verificando que sea único.

#### `store()`
Valida los datos usando `StoreNewsTagRequest` y crea la nueva etiqueta. Redirige al listado después de crear.

**Vista:**

La vista incluye:
- Header con título, descripción y botón "Volver"
- Breadcrumbs
- Formulario con campos:
  - Nombre (requerido, máximo 255 caracteres, único)
  - Slug (opcional, máximo 255 caracteres, único, se genera automáticamente)
- Validación en tiempo real
- Botones "Guardar" y "Cancelar"

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\NewsTags\Edit`
- **Vista**: `resources/views/livewire/admin/news-tags/edit.blade.php`
- **Ruta**: `/admin/etiquetas/{news_tag}/editar` (nombre: `admin.news-tags.edit`)

**Propiedades Públicas:**

```php
public NewsTag $newsTag;
public string $name = '';
public string $slug = '';
```

**Métodos Principales:**

#### `mount(NewsTag $news_tag)`
Inicializa el componente, carga los datos de la etiqueta y verifica permisos mediante `NewsTagPolicy::update()`.

#### `updatedName()`
Genera automáticamente el slug desde el nombre cuando el nombre cambia, solo si el slug está vacío o coincide con el slug del nombre original.

#### `updatedSlug()`
Valida el slug en tiempo real cuando cambia, verificando que sea único (ignorando el registro actual).

#### `update()`
Valida los datos usando `UpdateNewsTagRequest` y actualiza la etiqueta. Despacha el evento `news-tag-updated` y redirige al listado.

**Vista:**

La vista incluye:
- Header con título, descripción y botón "Volver"
- Breadcrumbs
- Formulario con campos:
  - Nombre (requerido, máximo 255 caracteres, único ignorando el registro actual)
  - Slug (opcional, máximo 255 caracteres, único ignorando el registro actual)
- Información adicional:
  - Fecha de creación
  - Fecha de última actualización
  - Número de noticias asociadas
- Validación en tiempo real
- Botones "Guardar" y "Cancelar"

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\NewsTags\Show`
- **Vista**: `resources/views/livewire/admin/news-tags/show.blade.php`
- **Ruta**: `/admin/etiquetas/{news_tag}` (nombre: `admin.news-tags.show`)

**Propiedades Públicas:**

```php
public NewsTag $newsTag;
```

**Métodos Principales:**

#### `mount(NewsTag $news_tag)`
Inicializa el componente, carga la etiqueta con sus relaciones y verifica permisos mediante `NewsTagPolicy::view()`.

**Eager Loading:**
- `with(['newsPosts'])` para cargar noticias asociadas
- `loadCount(['newsPosts'])` para contar noticias asociadas

#### `statistics()` (Computed)
Retorna estadísticas de la etiqueta:
- Total de noticias asociadas

#### `delete()`
Elimina la etiqueta usando SoftDeletes. Verifica que no tenga noticias asociadas antes de eliminar.

#### `restore()`
Restaura la etiqueta eliminada. Requiere permiso `NEWS_DELETE`.

#### `forceDelete()`
Elimina permanentemente la etiqueta. Solo disponible para super-admin y solo si no tiene noticias asociadas.

#### `canDelete()` (Computed)
Verifica si la etiqueta puede ser eliminada (no tiene noticias asociadas).

#### `hasRelationships()` (Computed)
Verifica si la etiqueta tiene noticias asociadas.

**Vista:**

La vista incluye:
- Header con nombre de la etiqueta, badge de estado (activa/eliminada) y botones de acción (editar, eliminar, restaurar, force delete, volver)
- Breadcrumbs
- Tarjeta de estadísticas:
  - Total de noticias asociadas
- Sección "Noticias Relacionadas":
  - Lista de hasta 10 noticias asociadas con enlaces
  - Enlace para ver todas las noticias (si hay más de 10)
- Tarjeta de detalles:
  - Nombre
  - Slug
  - Fecha de creación
  - Fecha de última actualización
- Modales de confirmación para eliminar, restaurar y force delete
- Notificaciones toast para éxito/error

---

## Form Requests

### StoreNewsTagRequest

**Ubicación:** `app/Http/Requests/StoreNewsTagRequest.php`

**Autorización:**
- Verifica que el usuario tenga permiso `NEWS_CREATE` mediante `NewsTagPolicy::create()`

**Reglas de Validación:**
```php
'name' => ['required', 'string', 'max:255', Rule::unique('news_tags', 'name')],
'slug' => ['nullable', 'string', 'max:255', Rule::unique('news_tags', 'slug')],
```

**Mensajes Personalizados:**
- Todos los mensajes están internacionalizados en español e inglés
- Mensajes específicos para cada regla de validación

---

### UpdateNewsTagRequest

**Ubicación:** `app/Http/Requests/UpdateNewsTagRequest.php`

**Autorización:**
- Verifica que el usuario tenga permiso `NEWS_EDIT` mediante `NewsTagPolicy::update()`
- Obtiene el `NewsTag` desde el route parameter (soporta route model binding)

**Reglas de Validación:**
```php
'name' => ['required', 'string', 'max:255', Rule::unique('news_tags', 'name')->ignore($newsTagId)],
'slug' => ['nullable', 'string', 'max:255', Rule::unique('news_tags', 'slug')->ignore($newsTagId)],
```

**Mensajes Personalizados:**
- Todos los mensajes están internacionalizados en español e inglés
- Mensajes específicos para cada regla de validación

---

## Policy

### NewsTagPolicy

**Ubicación:** `app/Policies/NewsTagPolicy.php`

**Métodos:**

- `before(User $user, string $ability)`: Concede acceso total a super-admin
- `viewAny(User $user)`: Verifica permiso `NEWS_VIEW`
- `view(User $user, NewsTag $newsTag)`: Verifica permiso `NEWS_VIEW`
- `create(User $user)`: Verifica permiso `NEWS_CREATE`
- `update(User $user, NewsTag $newsTag)`: Verifica permiso `NEWS_EDIT`
- `delete(User $user, NewsTag $newsTag)`: Verifica permiso `NEWS_DELETE`
- `restore(User $user, NewsTag $newsTag)`: Verifica permiso `NEWS_DELETE`
- `forceDelete(User $user, NewsTag $newsTag)`: Verifica permiso `NEWS_DELETE`

**Nota:** Las etiquetas de noticias utilizan los mismos permisos del módulo `news.*` porque son sub-entidades de las noticias.

---

## Modelo

### NewsTag

**Ubicación:** `app/Models/NewsTag.php`

**Traits:**
- `HasFactory`
- `SoftDeletes`

**Propiedades:**
```php
protected $fillable = [
    'name',
    'slug',
];
```

**Relaciones:**
- `newsPosts()`: `BelongsToMany` con `NewsPost` (tabla pivot: `news_post_tag`)

**Eventos del Modelo:**
- `creating`: Genera automáticamente el slug desde el nombre si no se proporciona

---

## Migraciones

### Add SoftDeletes to News Tags

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_add_soft_deletes_to_news_tags_table.php`

**Cambios:**
- Añade columna `deleted_at` (timestamp nullable)
- Añade índice `news_tags_deleted_at_index` para optimizar consultas de eliminados

---

## Rutas

**Ubicación:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Rutas de Etiquetas de Noticias
    Route::get('/etiquetas', \App\Livewire\Admin\NewsTags\Index::class)->name('news-tags.index');
    Route::get('/etiquetas/crear', \App\Livewire\Admin\NewsTags\Create::class)->name('news-tags.create');
    Route::get('/etiquetas/{news_tag}', \App\Livewire\Admin\NewsTags\Show::class)->name('news-tags.show');
    Route::get('/etiquetas/{news_tag}/editar', \App\Livewire\Admin\NewsTags\Edit::class)->name('news-tags.edit');
});
```

---

## Navegación

**Ubicación:** `resources/views/components/layouts/app/sidebar.blade.php`

Se añadió un nuevo elemento de navegación en el grupo "Contenido":

```blade
<flux:navlist.item 
    icon="tag" 
    :href="route('admin.news-tags.index')" 
    :current="request()->routeIs('admin.news-tags.*')" 
    wire:navigate
>
    {{ __('common.nav.news_tags') }}
</flux:navlist.item>
```

**Condición:** Solo visible si el usuario tiene permiso `NEWS_VIEW` mediante `NewsTagPolicy::viewAny()`.

---

## Internacionalización

### Español (`lang/es/common.php`)

```php
'nav' => [
    'news_tags' => 'Etiquetas de Noticias',
],

'create_tag' => 'Crear Etiqueta',
'edit_tag' => 'Editar Etiqueta',

'no_news_tags' => 'No hay etiquetas de noticias',
'no_news_tags_description' => 'No se encontraron etiquetas de noticias que coincidan con los filtros aplicados.',
'cannot_delete_tag_with_news' => 'No se puede eliminar la etiqueta porque tiene noticias asociadas.',
'cannot_force_delete_tag_with_news' => 'No se puede eliminar permanentemente la etiqueta porque tiene noticias asociadas.',
```

### Inglés (`lang/en/common.php`)

```php
'nav' => [
    'news_tags' => 'News Tags',
],

'create_tag' => 'Create Tag',
'edit_tag' => 'Edit Tag',

'no_news_tags' => 'No news tags',
'no_news_tags_description' => 'No news tags found matching the applied filters.',
'cannot_delete_tag_with_news' => 'Cannot delete tag because it has associated news posts.',
'cannot_force_delete_tag_with_news' => 'Cannot permanently delete tag because it has associated news posts.',
```

---

## Testing

### Tests Implementados

**Ubicación:** `tests/Feature/Livewire/Admin/NewsTags/`

#### IndexTest.php
- ✅ Tests de autorización (403 para usuarios sin permisos)
- ✅ Tests de listado (paginación, búsqueda, ordenación, filtros)
- ✅ Tests de acciones (eliminar, restaurar, force delete)
- ✅ Tests de helpers (`canCreate`, `canDeleteNewsTag`, `resetFilters`)
- ✅ Tests de edge cases (early returns cuando IDs son null) - *añadidos en paso 3.8.4*
- **Total:** 36 tests (100% cobertura)

#### CreateTest.php
- ✅ Tests de autorización
- ✅ Tests de creación exitosa
- ✅ Tests de validación (campos requeridos, longitud máxima, unicidad)
- ✅ Tests de generación automática de slug
- ✅ Tests de validación de slug en tiempo real - *añadidos en paso 3.8.4*
- **Total:** 14 tests (100% cobertura)

#### EditTest.php
- ✅ Tests de autorización
- ✅ Tests de carga de datos
- ✅ Tests de actualización exitosa
- ✅ Tests de validación (campos requeridos, longitud máxima, unicidad ignorando registro actual)
- ✅ Tests de generación automática de slug
- ✅ Tests de eventos (`news-tag-updated`)
- ✅ Tests de preservación de slug personalizado - *añadidos en paso 3.8.4*
- **Total:** 20 tests (100% cobertura)

#### ShowTest.php
- ✅ Tests de autorización
- ✅ Tests de visualización
- ✅ Tests de estadísticas
- ✅ Tests de eliminación (soft delete, restore, force delete)
- ✅ Tests de validación de relaciones
- ✅ Tests de computed properties
- **Total:** 33 tests (100% cobertura) - *componente añadido en paso 3.8.4*

**Total General:** 103 tests (100% cobertura de líneas - 179/179)

**Actualizado:** Enero 2026 (paso 3.8.4)

---

## Características Técnicas

### SoftDeletes

- Todas las etiquetas utilizan SoftDeletes
- Las etiquetas eliminadas se pueden restaurar
- Solo super-admin puede eliminar permanentemente (force delete)
- Se valida que no haya noticias asociadas antes de force delete

### Validación de Relaciones

Antes de eliminar o hacer force delete, se verifica que la etiqueta no tenga noticias asociadas:

```php
if ($newsTag->news_posts_count > 0) {
    // No se puede eliminar
}
```

### Generación Automática de Slug

El slug se genera automáticamente desde el nombre:
- En el modelo: evento `creating` genera el slug si está vacío
- En Livewire: método `updatedName()` regenera el slug si está vacío o coincide con el slug del nombre original
- El usuario puede sobrescribir el slug manualmente

### Optimización de Consultas

- Uso de `withCount(['newsPosts'])` para evitar N+1 queries
- Índices en columnas de búsqueda y ordenación
- Eager loading de relaciones cuando es necesario

### URL Query Parameters

Los filtros y ordenación se sincronizan con la URL usando el atributo `#[Url]` de Livewire:
- `?q=busqueda` - Búsqueda
- `?eliminados=1` - Mostrar eliminados
- `?ordenar=name&direccion=asc` - Ordenación
- `?por-pagina=15` - Elementos por página

---

## Flujo de Usuario

### Crear una Etiqueta

1. Usuario accede a `/admin/etiquetas`
2. Hace clic en "Crear Etiqueta"
3. Completa el formulario (nombre es obligatorio, slug se genera automáticamente)
4. Guarda y es redirigido al listado

### Editar una Etiqueta

1. Usuario accede a `/admin/etiquetas`
2. Hace clic en "Editar" en una etiqueta
3. Modifica el nombre o slug
4. Guarda y es redirigido al listado
5. Se despacha evento `news-tag-updated` para notificaciones

### Eliminar una Etiqueta

1. Usuario accede a `/admin/etiquetas`
2. Hace clic en "Eliminar" en una etiqueta
3. Se abre modal de confirmación
4. Si la etiqueta tiene noticias asociadas, se muestra mensaje de error
5. Si no tiene relaciones, se elimina (soft delete)

### Restaurar una Etiqueta

1. Usuario accede a `/admin/etiquetas`
2. Activa el filtro "Mostrar Eliminados"
3. Hace clic en "Restaurar" en una etiqueta eliminada
4. Se restaura la etiqueta

### Force Delete (Solo Super-Admin)

1. Usuario super-admin accede a `/admin/etiquetas`
2. Activa el filtro "Mostrar Eliminados"
3. Hace clic en "Eliminar Permanentemente" en una etiqueta eliminada
4. Se abre modal de confirmación
5. Si la etiqueta tiene noticias asociadas, se muestra mensaje de error
6. Si no tiene relaciones, se elimina permanentemente

---

## Mejoras Futuras

- [ ] Añadir estadísticas de uso de etiquetas (noticias más etiquetadas)
- [ ] Añadir sugerencias de etiquetas similares al crear/editar
- [ ] Añadir historial de cambios (auditoría)
- [ ] Añadir exportación de etiquetas a CSV/Excel
- [ ] Añadir importación masiva de etiquetas

---

## Referencias

- [Plan de Desarrollo](pasos/paso-3.5.6-plan.md) - Plan detallado paso a paso
- [Resumen Ejecutivo](pasos/paso-3.5.6-resumen.md) - Resumen de objetivos y estructura
- [CRUD de Noticias](admin-news-crud.md) - CRUD relacionado de Noticias
- [Sistema de Policies](policies.md) - Documentación de autorización
- [Form Requests](form-requests.md) - Documentación de validación

