# CRUD de Roles y Permisos en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Roles y Permisos en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Roles y Permisos permite a los super-administradores gestionar completamente los roles del sistema y sus permisos asociados desde el panel de administración. Incluye funcionalidades avanzadas como gestión de permisos mediante Spatie Permission, visualización de usuarios asignados a cada rol, protección de roles del sistema, validación de nombres de roles, y componentes UI modernos con Flux UI.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar roles
- ✅ **Gestión de Permisos**: Asignación y modificación de permisos agrupados por módulo
- ✅ **Visualización de Usuarios**: Ver qué usuarios tienen cada rol asignado
- ✅ **Protección de Roles del Sistema**: Los 4 roles principales no pueden eliminarse ni cambiar de nombre
- ✅ **Validación de Nombres**: Solo se permiten nombres de roles válidos según constantes del sistema
- ✅ **Búsqueda y Filtros**: Búsqueda por nombre, ordenamiento, paginación
- ✅ **Autorización**: Solo super-admin puede gestionar roles mediante `RolePolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 153 tests pasando (249 assertions)

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Roles\Index`
- **Vista**: `resources/views/livewire/admin/roles/index.blade.php`
- **Ruta**: `/admin/roles` (nombre: `admin.roles.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'ordenar')]
public string $sortField = 'name';

#[Url(as: 'direccion')]
public string $sortDirection = 'asc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public ?int $roleToDelete = null;
```

**Métodos Principales:**

- `roles()` - Computed property con paginación, filtros y ordenación
  - Eager loading: `with(['permissions'])`, `withCount(['users'])`
  - Filtro por búsqueda (nombre)
  - Ordenación por nombre
  - Paginación configurable
- `sortBy($field)` - Ordenación con toggle de dirección
- `confirmDelete($roleId)` - Confirmar eliminación
- `delete()` - Eliminar rol (validar que no sea rol del sistema ni tenga usuarios)
- `resetFilters()` - Resetear filtros y paginación
- `updatedSearch()` - Resetear paginación al buscar
- `canCreate()` - Verificar si puede crear
- `canDeleteRole($role)` - Verificar si puede eliminar (no es rol del sistema, no tiene usuarios)
- `isSystemRole($role)` - Verificar si es rol del sistema
- `getRoleDisplayName($roleName)` - Obtener nombre traducido del rol
- `getRoleBadgeVariant($roleName)` - Obtener variante de badge para el rol

**Características:**

- Tabla responsive con columnas: Nombre, Permisos, Usuarios, Fecha Creación, Acciones
- Búsqueda en tiempo real con debounce
- Ordenación por nombre (ascendente/descendente)
- Paginación configurable
- Modal de confirmación para eliminación
- Estados de carga y vacío
- Badges para identificar roles del sistema
- Contadores de permisos y usuarios

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Roles\Create`
- **Vista**: `resources/views/livewire/admin/roles/create.blade.php`
- **Ruta**: `/admin/roles/crear` (nombre: `admin.roles.create`)

**Propiedades Públicas:**

```php
public string $name = '';
public array $permissions = [];
```

**Métodos Principales:**

- `mount()` - Inicialización con autorización
- `availablePermissions()` - Computed property para obtener permisos agrupados por módulo
- `store()` - Guardar nuevo rol usando `StoreRoleRequest`
  - Crear rol
  - Asignar permisos si se proporcionaron usando `syncPermissions()`
  - Limpiar caché de permisos
  - Disparar evento de éxito
  - Redirigir a index
- `getModuleDisplayName($module)` - Obtener nombre traducido del módulo
- `getPermissionDisplayName($permissionName)` - Obtener nombre traducido del permiso
- `selectAllModulePermissions($module)` - Seleccionar todos los permisos de un módulo
- `deselectAllModulePermissions($module)` - Deseleccionar todos los permisos de un módulo
- `areAllModulePermissionsSelected($module)` - Verificar si todos los permisos de un módulo están seleccionados

**Características:**

- Formulario con validación en tiempo real
- Campo de nombre: Select con roles válidos del sistema
- Selección de permisos agrupados por módulo (programs, calls, news, documents, events, users)
- Botones para seleccionar/deseleccionar todos los permisos de un módulo
- Resumen de permisos seleccionados en sidebar
- Validación de nombre único y válido
- Validación de permisos existentes
- Mensajes de error personalizados

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Roles\Edit`
- **Vista**: `resources/views/livewire/admin/roles/edit.blade.php`
- **Ruta**: `/admin/roles/{role}/editar` (nombre: `admin.roles.edit`)

**Propiedades Públicas:**

```php
public Role $role;
public string $name = '';
public array $permissions = [];
```

**Métodos Principales:**

- `mount(Role $role)` - Inicialización con autorización y carga de datos
  - Cargar rol con permisos
  - Pre-llenar nombre y permisos seleccionados
- `availablePermissions()` - Computed property para obtener permisos agrupados por módulo
- `isSystemRole()` - Verificar si es rol del sistema
- `canChangeName()` - Verificar si puede cambiar el nombre (no es rol del sistema)
- `update()` - Actualizar rol usando `UpdateRoleRequest`
  - Validación personalizada (incluye verificación de nombre de rol del sistema)
  - Actualizar nombre solo si no es rol del sistema o si es el mismo
  - Sincronizar permisos usando `syncPermissions()`
  - Limpiar caché de permisos
  - Disparar evento de éxito
  - Redirigir a index
- `getRoleDisplayName($roleName)` - Obtener nombre traducido del rol
- `getModuleDisplayName($module)` - Obtener nombre traducido del módulo
- `getPermissionDisplayName($permissionName)` - Obtener nombre traducido del permiso
- `selectAllModulePermissions($module)` - Seleccionar todos los permisos de un módulo
- `deselectAllModulePermissions($module)` - Deseleccionar todos los permisos de un módulo
- `areAllModulePermissionsSelected($module)` - Verificar si todos los permisos de un módulo están seleccionados

**Características:**

- Formulario pre-llenado con datos actuales
- Campo de nombre deshabilitado para roles del sistema
- Información sobre protección de roles del sistema
- Selección de permisos agrupados por módulo
- Botones para seleccionar/deseleccionar todos los permisos de un módulo
- Resumen de permisos seleccionados en sidebar
- Validación de nombre único (excluyendo el rol actual)
- Validación de que roles del sistema no pueden cambiar de nombre
- Mensajes de error personalizados

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Roles\Show`
- **Vista**: `resources/views/livewire/admin/roles/show.blade.php`
- **Ruta**: `/admin/roles/{role}` (nombre: `admin.roles.show`)

**Propiedades Públicas:**

```php
public Role $role;
public int $usersPerPage = 10;
public bool $showDeleteModal = false;
```

**Métodos Principales:**

- `mount(Role $role)` - Inicialización con autorización y carga de datos
  - Cargar rol con permisos y contador de usuarios
- `permissionsByModule()` - Computed property para obtener permisos agrupados por módulo
  - Solo muestra permisos asignados al rol
- `users()` - Computed property para obtener usuarios paginados con este rol
  - Eager loading: `with(['roles', 'permissions'])`
  - Ordenación por nombre y fecha de creación
  - Paginación configurable
- `isSystemRole()` - Verificar si es rol del sistema
- `canDelete()` - Verificar si puede eliminar (no es rol del sistema, no tiene usuarios, tiene permiso)
- `canEdit()` - Verificar si puede editar
- `confirmDelete()` - Confirmar eliminación
- `delete()` - Eliminar rol (validar que no sea rol del sistema ni tenga usuarios)
  - Limpiar caché de permisos
  - Disparar evento de éxito o error
  - Redirigir a index
- `getRoleDisplayName($roleName)` - Obtener nombre traducido del rol
- `getRoleBadgeVariant($roleName)` - Obtener variante de badge para el rol
- `getModuleDisplayName($module)` - Obtener nombre traducido del módulo
- `getPermissionDisplayName($permissionName)` - Obtener nombre traducido del permiso

**Características:**

- Vista detallada del rol con información completa
- Badge indicando si es rol del sistema
- Permisos agrupados por módulo (solo los asignados)
- Lista paginada de usuarios con este rol
- Botones de acción: Editar, Eliminar (si está permitido)
- Información adicional: Fecha de creación, fecha de actualización, total de permisos, total de usuarios
- Modal de confirmación para eliminación
- Estados vacíos cuando no hay usuarios asignados

---

## Form Requests

### StoreRoleRequest

**Ubicación:** `app/Http/Requests/StoreRoleRequest.php`

**Reglas de Validación:**

```php
'name' => [
    'required',
    'string',
    'max:255',
    Rule::unique('roles', 'name'),
    Rule::in(Roles::all()), // Solo roles válidos del sistema
],
'permissions' => ['nullable', 'array'],
'permissions.*' => ['string', Rule::exists('permissions', 'name')],
```

**Mensajes Personalizados:**

- Nombre requerido, único, válido según lista de roles permitidos
- Permisos deben ser array y existir en la base de datos

**Autorización:**

- Verifica que el usuario tenga permiso `create` en `Role::class` mediante `RolePolicy`

---

### UpdateRoleRequest

**Ubicación:** `app/Http/Requests/UpdateRoleRequest.php`

**Reglas de Validación:**

```php
'name' => [
    'required',
    'string',
    'max:255',
    Rule::unique('roles', 'name')->ignore($roleId),
    Rule::in(Roles::all()),
    // Validación personalizada: roles del sistema no pueden cambiar de nombre
    function ($attribute, $value, $fail) use ($role, $isSystemRole) {
        if ($isSystemRole && $value !== $role->name) {
            $fail(__('No se puede cambiar el nombre de un rol del sistema.'));
        }
    },
],
'permissions' => ['nullable', 'array'],
'permissions.*' => ['string', Rule::exists('permissions', 'name')],
```

**Mensajes Personalizados:**

- Nombre requerido, único (excluyendo el rol actual), válido según lista de roles permitidos
- Validación especial para roles del sistema
- Permisos deben ser array y existir en la base de datos

**Autorización:**

- Verifica que el usuario tenga permiso `update` en el rol específico mediante `RolePolicy`

---

## Policy

### RolePolicy

**Ubicación:** `app/Policies/RolePolicy.php`

**Registro Manual:**

La policy se registra manualmente en `AppServiceProvider` porque el modelo `Spatie\Permission\Models\Role` no sigue la convención de auto-descubrimiento de Laravel:

```php
Gate::policy(Role::class, RolePolicy::class);
```

**Métodos de Autorización:**

- `before($user, $ability)` - Super-admin tiene acceso completo a todas las acciones
- `viewAny($user)` - Solo super-admin puede ver el listado
- `view($user, $role)` - Solo super-admin puede ver un rol
- `create($user)` - Solo super-admin puede crear roles
- `update($user, $role)` - Solo super-admin puede actualizar roles
- `delete($user, $role)` - Solo super-admin puede eliminar, pero:
  - No puede eliminar roles del sistema
  - No puede eliminar roles con usuarios asignados

**Protección de Roles del Sistema:**

Los 4 roles principales (`super-admin`, `admin`, `editor`, `viewer`) están protegidos:
- No se pueden eliminar
- No se puede cambiar su nombre (validado en `UpdateRoleRequest`)

---

## Rutas

**Ubicación:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Rutas de Roles y Permisos
    Route::get('/roles', \App\Livewire\Admin\Roles\Index::class)->name('roles.index');
    Route::get('/roles/crear', \App\Livewire\Admin\Roles\Create::class)->name('roles.create');
    Route::get('/roles/{role}', \App\Livewire\Admin\Roles\Show::class)->name('roles.show');
    Route::get('/roles/{role}/editar', \App\Livewire\Admin\Roles\Edit::class)->name('roles.edit');
});
```

**Middleware:**
- `auth` - Usuario autenticado
- `verified` - Email verificado

**Autorización:**
- Verificada en cada componente mediante `authorize()` en `mount()`

---

## Navegación

**Ubicación:** `resources/views/components/layouts/app/sidebar.blade.php`

**Integración en Sidebar:**

```blade
@can('viewAny', \Spatie\Permission\Models\Role::class)
    <flux:navlist.item 
        icon="shield-check" 
        :href="route('admin.roles.index')" 
        :current="request()->routeIs('admin.roles.*')" 
        wire:navigate
    >
        {{ __('common.nav.roles') }}
    </flux:navlist.item>
@endcan
```

**Traducciones:**

- `lang/es/common.php`: `'roles' => 'Roles y Permisos'`
- `lang/en/common.php`: `'roles' => 'Roles and Permissions'`

---

## Estructura de Permisos

Los permisos están organizados por módulo:

- **Programas** (`programs.*`): view, create, edit, delete
- **Convocatorias** (`calls.*`): view, create, edit, delete, publish
- **Noticias** (`news.*`): view, create, edit, delete, publish
- **Documentos** (`documents.*`): view, create, edit, delete
- **Eventos** (`events.*`): view, create, edit, delete
- **Usuarios** (`users.*`): view, create, edit, delete, *

Los permisos se definen en `App\Support\Permissions` y se agrupan mediante `Permissions::byModule()`.

---

## Roles del Sistema

Los 4 roles principales están definidos en `App\Support\Roles`:

- `super-admin` - Super Administrador (acceso completo)
- `admin` - Administrador
- `editor` - Editor
- `viewer` - Visualizador (solo lectura)

Estos roles:
- No se pueden eliminar
- No se puede cambiar su nombre
- Sí se pueden modificar sus permisos

---

## Caché de Permisos

Spatie Permission utiliza caché para optimizar las consultas de permisos. Después de cualquier operación CRUD en roles o permisos, se limpia la caché:

```php
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

Esto se hace automáticamente en:
- `Create::store()` - Después de crear un rol
- `Edit::update()` - Después de actualizar un rol
- `Index::delete()` - Después de eliminar un rol
- `Show::delete()` - Después de eliminar un rol

---

## Tests

### Cobertura de Tests

**Total: 153 tests pasando (249 assertions)**

#### Form Requests (29 tests)
- `tests/Feature/Http/Requests/StoreRoleRequestTest.php` - 15 tests
- `tests/Feature/Http/Requests/UpdateRoleRequestTest.php` - 14 tests

#### Policies (11 tests)
- `tests/Feature/Policies/RolePolicyTest.php` - 11 tests

#### Componentes Livewire (113 tests)
- `tests/Feature/Livewire/Admin/Roles/IndexTest.php` - 30 tests (60 assertions)
- `tests/Feature/Livewire/Admin/Roles/CreateTest.php` - 27 tests (62 assertions)
- `tests/Feature/Livewire/Admin/Roles/EditTest.php` - 30 tests (62 assertions)
- `tests/Feature/Livewire/Admin/Roles/ShowTest.php` - 26 tests (65 assertions)

### Casos de Prueba Cubiertos

**Autorización:**
- Acceso denegado para usuarios no autenticados
- Acceso permitido solo para super-admin
- Acceso denegado para admin, editor, viewer y usuarios sin rol

**Validación:**
- Campos requeridos
- Tipos de datos
- Longitud máxima
- Unicidad de nombres
- Nombres válidos según lista permitida
- Permisos existentes en base de datos
- Protección de roles del sistema

**Funcionalidad:**
- Crear, leer, actualizar, eliminar roles
- Asignar y revocar permisos
- Búsqueda y filtrado
- Ordenamiento
- Paginación
- Selección masiva de permisos por módulo
- Visualización de usuarios asignados

**Protección:**
- Roles del sistema no se pueden eliminar
- Roles del sistema no pueden cambiar de nombre
- Roles con usuarios no se pueden eliminar

---

## Consideraciones Técnicas

### Modelo Role de Spatie

El sistema utiliza el modelo `Spatie\Permission\Models\Role` de Spatie Laravel Permission. Este modelo:
- No tiene SoftDeletes
- No sigue la convención de auto-descubrimiento de policies de Laravel
- Requiere registro manual de la policy en `AppServiceProvider`

### Validación de Nombres de Roles

Los nombres de roles están restringidos a los valores definidos en `App\Support\Roles::all()`. Esto asegura:
- Consistencia en el sistema
- Facilita la traducción y visualización
- Previene errores de tipeo

### Agrupación de Permisos

Los permisos se agrupan por módulo para facilitar:
- La selección masiva
- La visualización organizada
- La comprensión de la estructura de permisos

### Optimizaciones

- Eager loading de relaciones (`with()`, `withCount()`)
- Caché de permisos de Spatie Permission
- Computed properties de Livewire para optimizar consultas
- Paginación para listas grandes

---

## Archivos Creados/Modificados

### Archivos Nuevos

**Componentes Livewire:**
- `app/Livewire/Admin/Roles/Index.php`
- `app/Livewire/Admin/Roles/Create.php`
- `app/Livewire/Admin/Roles/Edit.php`
- `app/Livewire/Admin/Roles/Show.php`

**Vistas:**
- `resources/views/livewire/admin/roles/index.blade.php`
- `resources/views/livewire/admin/roles/create.blade.php`
- `resources/views/livewire/admin/roles/edit.blade.php`
- `resources/views/livewire/admin/roles/show.blade.php`

**Form Requests:**
- `app/Http/Requests/StoreRoleRequest.php`
- `app/Http/Requests/UpdateRoleRequest.php`

**Policies:**
- `app/Policies/RolePolicy.php`

**Tests:**
- `tests/Feature/Http/Requests/StoreRoleRequestTest.php`
- `tests/Feature/Http/Requests/UpdateRoleRequestTest.php`
- `tests/Feature/Policies/RolePolicyTest.php`
- `tests/Feature/Livewire/Admin/Roles/IndexTest.php`
- `tests/Feature/Livewire/Admin/Roles/CreateTest.php`
- `tests/Feature/Livewire/Admin/Roles/EditTest.php`
- `tests/Feature/Livewire/Admin/Roles/ShowTest.php`

### Archivos Modificados

**Rutas:**
- `routes/web.php` - Añadidas rutas de roles

**Navegación:**
- `resources/views/components/layouts/app/sidebar.blade.php` - Añadido enlace a roles

**Traducciones:**
- `lang/es/common.php` - Añadida traducción de "Roles y Permisos"
- `lang/en/common.php` - Añadida traducción de "Roles and Permissions"

**Service Provider:**
- `app/Providers/AppServiceProvider.php` - Registro manual de `RolePolicy`

---

## Referencias

- [Sistema de Roles y Permisos](roles-and-permissions.md) - Documentación completa del sistema de roles y permisos
- [Sistema de Policies](policies.md) - Documentación de todas las policies
- [Form Requests](form-requests.md) - Documentación de validaciones

