# CRUD de Usuarios y Roles en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Usuarios y Roles en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Usuarios y Roles permite a los administradores gestionar completamente los usuarios del sistema desde el panel de administración. Incluye funcionalidades avanzadas como gestión de roles mediante Spatie Permission, visualización de audit logs con estadísticas, SoftDeletes, validación de seguridad (usuario no puede eliminarse a sí mismo ni modificar sus propios roles), y componentes UI reutilizables.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar usuarios
- ✅ **SoftDeletes**: Los usuarios nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo super-admin puede eliminar permanentemente
- ✅ **Gestión de Roles**: Asignación y modificación de roles mediante Spatie Permission
- ✅ **Audit Logs**: Visualización de actividad del usuario con paginación y estadísticas
- ✅ **Validación de Seguridad**: Un usuario no puede eliminarse a sí mismo ni modificar sus propios roles
- ✅ **Búsqueda y Filtros**: Búsqueda por nombre/email, filtro por rol, visualización de eliminados
- ✅ **Autorización**: Control de acceso mediante `UserPolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Componentes Reutilizables**: 4 componentes UI nuevos para usuarios y audit logs
- ✅ **Tests Completos**: 172 tests pasando (397 assertions)

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Users\Index`
- **Vista**: `resources/views/livewire/admin/users/index.blade.php`
- **Ruta**: `/admin/usuarios` (nombre: `admin.users.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'buscar')]
public string $search = '';

#[Url(as: 'rol')]
public string $filterRole = '';

#[Url(as: 'ordenar')]
public string $sortField = 'created_at';

#[Url(as: 'direccion')]
public string $sortDirection = 'desc';

#[Url(as: 'eliminados')]
public string $showDeleted = '0';

#[Url(as: 'por_pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public ?int $userToDelete = null;
public bool $showRestoreModal = false;
public ?int $userToRestore = null;
public bool $showForceDeleteModal = false;
public ?int $userToForceDelete = null;
```

**Métodos Principales:**

- `users()` - Computed property con paginación, filtros y ordenación
  - Eager loading: `with(['roles'])`, `withCount(['auditLogs'])`
  - Filtro por búsqueda (nombre, email)
  - Filtro por rol
  - Filtro de eliminados
  - Ordenación
- `sortBy($field)` - Ordenación
- `confirmDelete($userId)` - Confirmar eliminación
- `delete()` - Eliminar con SoftDeletes (validar que no sea el usuario actual)
- `confirmRestore($userId)` - Confirmar restauración
- `restore()` - Restaurar usuario eliminado
- `confirmForceDelete($userId)` - Confirmar eliminación permanente
- `forceDelete()` - Eliminar permanentemente (solo super-admin)
- `resetFilters()` - Resetear filtros
- `canCreate()` - Verificar si puede crear
- `canViewDeleted()` - Verificar si puede ver eliminados
- `canDeleteUser($user)` - Verificar si puede eliminar (no es el usuario actual)

**Características:**

- Tabla responsive con columnas: Avatar, Nombre, Email, Roles, Actividad, Fecha Creación, Acciones
- Búsqueda en tiempo real con debounce
- Filtro por rol con dropdown
- Toggle para mostrar eliminados
- Ordenación por diferentes campos
- Paginación configurable
- Modales de confirmación para acciones destructivas
- Estados de carga y vacío

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Users\Create`
- **Vista**: `resources/views/livewire/admin/users/create.blade.php`
- **Ruta**: `/admin/usuarios/crear` (nombre: `admin.users.create`)

**Propiedades Públicas:**

```php
public string $name = '';
public string $email = '';
public string $password = '';
public string $password_confirmation = '';
public array $roles = [];
```

**Métodos Principales:**

- `mount()` - Inicialización con autorización
- `availableRoles()` - Computed property para obtener todos los roles disponibles
- `store()` - Guardar nuevo usuario usando `StoreUserRequest`
  - Crear usuario
  - Asignar roles si se proporcionaron usando `syncRoles()`
  - Disparar evento de éxito
  - Redirigir a index
- `getRoleDisplayName($roleName)` - Obtener nombre traducido del rol
- `getRoleDescription($roleName)` - Obtener descripción del rol
- `getRoleBadgeVariant($roleName)` - Obtener variante de badge para el rol

**Características:**

- Formulario con validación en tiempo real
- Campos: Nombre, Email, Contraseña, Confirmación
- Selección de roles mediante checkboxes
- Descripción de cada rol disponible
- Validación de roles permitidos
- Mensajes de error personalizados

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Users\Edit`
- **Vista**: `resources/views/livewire/admin/users/edit.blade.php`
- **Ruta**: `/admin/usuarios/{user}/editar` (nombre: `admin.users.edit`)

**Propiedades Públicas:**

```php
public User $user;
public string $name = '';
public string $email = '';
public string $password = '';
public string $password_confirmation = '';
public array $selectedRoles = [];
```

**Métodos Principales:**

- `mount(User $user)` - Carga de datos del usuario y roles actuales
- `roles()` - Computed property para obtener todos los roles disponibles
- `update()` - Actualizar usuario usando `UpdateUserRequest`
  - Actualizar datos básicos
  - Actualizar contraseña solo si se proporcionó
  - Sincronizar roles usando `AssignRoleRequest` (si tiene permisos)
  - Disparar evento de éxito
  - Redirigir a index
- `canAssignRoles()` - Verificar si puede asignar roles (no es el usuario actual)
- Helpers de roles: `getRoleDisplayName()`, `getRoleDescription()`, `getRoleBadgeVariant()`

**Características:**

- Formulario pre-rellenado con datos existentes
- Contraseña opcional (solo si se proporciona)
- Modificación de roles (excepto si es el usuario actual)
- Validación en tiempo real
- Mensaje: "Dejar en blanco para mantener la contraseña actual"
- Información adicional del usuario (fecha creación, última actualización)

---

### 4. Show (Vista Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Users\Show`
- **Vista**: `resources/views/livewire/admin/users/show.blade.php`
- **Ruta**: `/admin/usuarios/{user}` (nombre: `admin.users.show`)

**Propiedades Públicas:**

```php
public User $user;
public int $auditLogsPerPage = 10;
public bool $showDeleteModal = false;
public bool $showRestoreModal = false;
public bool $showForceDeleteModal = false;
public bool $showAssignRolesModal = false;
public array $selectedRoles = [];
```

**Métodos Principales:**

- `mount(User $user)` - Carga de usuario con relaciones (`roles`, `permissions`, `auditLogs_count`)
- `auditLogs()` - Computed property con paginación de audit logs
  - Ordenar por fecha descendente
  - Eager load: `model` (polimórfico)
- `statistics()` - Computed property con estadísticas:
  - Total de acciones
  - Acciones por tipo
  - Última actividad
- `delete()`, `restore()`, `forceDelete()` - Acciones de eliminación
- `openAssignRolesModal()`, `assignRoles()` - Gestión de roles
- Helpers: `canEdit()`, `canDelete()`, `canAssignRoles()`
- Helpers de visualización: `getRoleDisplayName()`, `getActionDisplayName()`, `getModelDisplayName()`, `formatChanges()`

**Características:**

- Información completa del usuario
- Listado de roles y permisos con badges
- Estadísticas de actividad
- Audit logs paginados con información del modelo afectado
- Modal para asignar roles
- Acciones de eliminación y restauración
- Formateo de cambios JSON de forma legible
- Enlaces a modelos afectados (si aplica)

---

## Componentes UI Reutilizables

### `x-ui.user-avatar`

Componente para mostrar avatar o iniciales del usuario con diferentes tamaños.

**Props:**
- `user` - Instancia del modelo User (requerido)
- `size` - Tamaño del avatar: `xs`, `sm`, `md`, `lg`, `xl` (default: `md`)
- `showName` - Mostrar nombre del usuario (default: `false`)
- `showEmail` - Mostrar email del usuario (default: `false`)

**Uso:**
```blade
<x-ui.user-avatar :user="$user" size="sm" />
<x-ui.user-avatar :user="$user" size="lg" :show-name="true" :show-email="true" />
```

**Características:**
- Genera iniciales automáticamente desde el nombre del usuario
- Colores consistentes basados en el nombre
- Soporte para dark mode
- Responsive

---

### `x-ui.user-roles`

Componente para mostrar roles del usuario con badges de colores.

**Props:**
- `user` - Instancia del modelo User (requerido)
- `size` - Tamaño de los badges: `xs`, `sm`, `md`, `lg` (default: `sm`)
- `showEmpty` - Mostrar mensaje si no hay roles (default: `false`)

**Uso:**
```blade
<x-ui.user-roles :user="$user" size="sm" />
<x-ui.user-roles :user="$user" size="sm" :show-empty="true" />
```

**Características:**
- Badges con colores distintos para cada rol
- Nombres traducidos de roles
- Soporte para múltiples roles
- Mensaje cuando no hay roles asignados

---

### `x-ui.user-permissions`

Componente para mostrar permisos directos del usuario.

**Props:**
- `user` - Instancia del modelo User (requerido)
- `size` - Tamaño de los badges: `xs`, `sm`, `md`, `lg` (default: `sm`)
- `limit` - Límite de permisos a mostrar (default: `null` - todos)
- `showEmpty` - Mostrar mensaje si no hay permisos (default: `false`)

**Uso:**
```blade
<x-ui.user-permissions :user="$user" size="sm" />
<x-ui.user-permissions :user="$user" size="sm" :limit="5" :show-empty="true" />
```

**Características:**
- Muestra solo permisos directos (no a través de roles)
- Límite opcional de permisos a mostrar
- Badge adicional con contador si hay más permisos
- Mensaje cuando no hay permisos directos

---

### `x-ui.audit-log-entry`

Componente para mostrar una entrada de audit log formateada.

**Props:**
- `log` - Instancia del modelo AuditLog (requerido)
- `compact` - Modo compacto (default: `false`)

**Uso:**
```blade
<x-ui.audit-log-entry :log="$log" />
<x-ui.audit-log-entry :log="$log" :compact="true" />
```

**Características:**
- Badge con color según el tipo de acción
- Nombre del modelo afectado
- Título del modelo con enlace (si aplica)
- Formateo de cambios JSON de forma legible
- Fecha formateada (d/m/Y H:i) y relativa (diffForHumans)
- Modo compacto para listas

---

## FormRequests

### StoreUserRequest

**Ubicación:** `app/Http/Requests/StoreUserRequest.php`

**Reglas de Validación:**

```php
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
'password' => ['required', 'string', Password::defaults(), 'confirmed'],
'roles' => ['nullable', 'array'],
'roles.*' => ['string', Rule::in(\App\Support\Roles::all())],
```

**Autorización:**
- Usa `UserPolicy::create()`

**Mensajes Personalizados:**
- Mensajes en español e inglés para todos los campos

---

### UpdateUserRequest

**Ubicación:** `app/Http/Requests/UpdateUserRequest.php`

**Reglas de Validación:**

```php
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
'roles' => ['nullable', 'array'],
'roles.*' => ['string', Rule::in(\App\Support\Roles::all())],
```

**Autorización:**
- Usa `UserPolicy::update()`

**Características:**
- Contraseña opcional (solo si se proporciona)
- Email único ignorando el usuario actual

---

### AssignRoleRequest

**Ubicación:** `app/Http/Requests/AssignRoleRequest.php`

**Reglas de Validación:**

```php
'roles' => ['required', 'array', 'min:1'],
'roles.*' => ['string', Rule::in(\App\Support\Roles::all())],
```

**Autorización:**
- Usa `UserPolicy::assignRoles()`

**Características:**
- Requiere al menos un rol
- Valida que los roles existan en `Roles::all()`

---

## Modelo User

### SoftDeletes

El modelo `User` implementa `SoftDeletes`:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;
    // ...
}
```

**Migración:**
- `2026_01_05_124910_add_soft_deletes_to_users_table.php`

### Relaciones

```php
public function auditLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(AuditLog::class);
}
```

### Métodos Helper

```php
public function initials(): string
{
    return Str::of($this->name)
        ->explode(' ')
        ->take(2)
        ->map(fn ($word) => Str::substr($word, 0, 1))
        ->implode('');
}
```

---

## Optimizaciones

### Índices de Base de Datos

**Migración:** `2026_01_05_132245_add_indexes_to_users_table.php`

Índices creados:
- `users_deleted_at_index` - Para SoftDeletes
- `users_name_index` - Para búsqueda por nombre
- `users_deleted_at_name_index` - Combinado para filtros
- `users_name_email_index` - Combinado para búsqueda

### Eager Loading

En `Index`:
```php
User::query()
    ->with(['roles'])
    ->withCount(['auditLogs'])
    // ...
```

En `Show`:
```php
AuditLog::query()
    ->where('user_id', $this->user->id)
    ->with('model')
    // ...
```

---

## Seguridad

### Restricciones Implementadas

1. **Usuario no puede eliminarse a sí mismo**
   - Implementado en `UserPolicy::delete()`
   - Verificado en componentes `Index` y `Show`

2. **Usuario no puede modificar sus propios roles**
   - Implementado en `UserPolicy::assignRoles()`
   - Verificado en componentes `Edit` y `Show`

3. **Autorización en cada acción**
   - Todos los métodos verifican permisos mediante `UserPolicy`
   - FormRequests validan autorización

### Permisos Requeridos

- `users.view` - Ver listado y detalles
- `users.create` - Crear usuarios
- `users.edit` - Editar usuarios
- `users.delete` - Eliminar usuarios (soft delete)
- `users.restore` - Restaurar usuarios eliminados
- `users.forceDelete` - Eliminar permanentemente (solo super-admin)
- `users.assignRoles` - Asignar roles

---

## Testing

### Tests Implementados

**Componentes Livewire:**
- `IndexTest.php`: 32 tests (74 assertions)
- `CreateTest.php`: 28 tests (68 assertions)
- `EditTest.php`: 32 tests (74 assertions)
- `ShowTest.php`: 37 tests (75 assertions)

**FormRequests:**
- `StoreUserRequestTest.php`: 20 tests
- `UpdateUserRequestTest.php`: 13 tests
- `AssignRoleRequestTest.php`: 10 tests

**Total: 172 tests pasando (397 assertions)**

### Cobertura

- ✅ Autorización (redirige no autenticados, verifica permisos)
- ✅ CRUD completo (crear, leer, actualizar, eliminar)
- ✅ Búsqueda y filtros
- ✅ Ordenación y paginación
- ✅ Soft delete y restore
- ✅ Force delete (solo super-admin)
- ✅ Gestión de roles
- ✅ Validación de campos
- ✅ Restricciones de seguridad
- ✅ Audit logs y estadísticas

---

## Rutas

```php
// Rutas de Usuarios
Route::get('/usuarios', \App\Livewire\Admin\Users\Index::class)->name('users.index');
Route::get('/usuarios/crear', \App\Livewire\Admin\Users\Create::class)->name('users.create');
Route::get('/usuarios/{user}', \App\Livewire\Admin\Users\Show::class)->name('users.show');
Route::get('/usuarios/{user}/editar', \App\Livewire\Admin\Users\Edit::class)->name('users.edit');
```

Todas las rutas están protegidas con middleware `auth` y `verified`.

---

## Navegación

El enlace "Usuarios" se añade al sidebar de administración:

```blade
@can('viewAny', \App\Models\User::class)
    <flux:navlist.group :heading="__('common.admin.nav.system')" class="grid">
        <flux:navlist.item icon="user-group" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>{{ __('common.nav.users') }}</flux:navlist.item>
    </flux:navlist.group>
@endcan
```

---

## Traducciones

**Español (`lang/es/common.php`):**
```php
'nav' => [
    'users' => 'Usuarios',
],
'admin' => [
    'nav' => [
        'system' => 'Sistema',
    ],
],
```

**Inglés (`lang/en/common.php`):**
```php
'nav' => [
    'users' => 'Users',
],
'admin' => [
    'nav' => [
        'system' => 'System',
    ],
],
```

---

## Notas Importantes

### SoftDeletes
- Los usuarios **nunca** se eliminan permanentemente por defecto
- Solo se marcan como eliminados (`deleted_at`)
- Solo super-admin puede realizar `forceDelete()`
- Un usuario no puede eliminarse a sí mismo

### Gestión de Roles
- Los roles se asignan usando `$user->syncRoles($roles)` de Spatie Permission
- Validar que los roles existan usando `Roles::all()`
- Un usuario no puede modificar sus propios roles

### Audit Logs
- Mostrar actividad del usuario desde la tabla `audit_logs`
- Filtrar por `user_id`
- Mostrar información del modelo afectado (polimórfico)
- Formatear JSON de cambios de forma legible

### Seguridad
- Validar siempre autorización con `UserPolicy`
- Verificar que un usuario no pueda eliminarse a sí mismo
- Verificar que un usuario no pueda modificar sus propios roles
- Validar permisos en cada acción

---

## Importación de Usuarios

### Descripción

El sistema de importación permite importar múltiples usuarios desde archivos Excel (.xlsx, .xls) o CSV (.csv) de manera masiva. Incluye generación automática de contraseñas, asignación de roles y modo dry-run para validar archivos sin guardar.

### Componente Import

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Users\Import`
- **Vista**: `resources/views/livewire/admin/users/import.blade.php`
- **Ruta**: `/admin/usuarios/importar` (nombre: `admin.users.import`)

**Características:**
- ✅ Subida de archivos mediante FilePond
- ✅ Descarga de plantilla Excel con ejemplos
- ✅ Modo dry-run (validar sin guardar)
- ✅ Generación automática de contraseñas
- ✅ Asignación automática de roles
- ✅ Tabla de usuarios con contraseñas generadas
- ✅ Opción para enviar emails (pendiente de implementar)
- ✅ Autorización automática (requiere permiso `users.create`)

**Uso:**
1. Acceder a `/admin/usuarios/importar`
2. Descargar la plantilla Excel para ver el formato requerido
3. Completar la plantilla con los datos a importar
4. Subir el archivo completado
5. Opcionalmente activar "Modo de prueba" para validar sin guardar
6. Hacer clic en "Importar"
7. Revisar los resultados, errores (si los hay) y contraseñas generadas

**Formato del Archivo:**
- Primera fila: Encabezados (no modificar)
- Filas siguientes: Datos de usuarios
- Columnas requeridas: Nombre, Email
- Columnas opcionales: Contraseña (se genera si está vacío), Roles (separados por comas)
- Ver `docs/imports-system.md` para detalles completos del formato

**Validaciones:**
- Nombre es obligatorio
- Email es obligatorio, debe ser único y formato válido
- Contraseña debe cumplir reglas de seguridad (se genera automáticamente si está vacío)
- Roles deben existir en el sistema (roles inválidos se filtran automáticamente)

**Generación de Contraseñas:**
- Si la contraseña está vacía, se genera automáticamente una de 12 caracteres
- Las contraseñas generadas se muestran en una tabla después de la importación
- Las contraseñas se hashean automáticamente antes de guardar

**Asignación de Roles:**
- Los roles se pueden especificar separados por comas (ej: `admin,editor`)
- También se aceptan separados por punto y coma (`;`)
- Los roles inválidos se filtran automáticamente
- Si todos los roles son inválidos, el usuario se crea sin roles

**Manejo de Errores:**
- El sistema continúa procesando aunque haya errores
- Todos los errores se reportan al finalizar
- Cada error incluye número de fila y mensajes específicos

**Botón de Importación:**
El botón "Importar" está disponible en la página de listado (`/admin/usuarios`) con el icono de flecha hacia arriba.

---

**Fecha de Creación**: Enero 2026  
**Última Actualización**: Enero 2026  
**Estado**: ✅ Completado - 172 tests pasando (397 assertions)

