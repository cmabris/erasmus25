# Sistema de Roles y Permisos

Este documento describe la estructura de roles y permisos implementada en la aplicación Erasmus+ Centro (Murcia).

## Roles del Sistema

La aplicación define cuatro roles principales, organizados por nivel de acceso:

### 1. Super Administrador (`super-admin`)

**Descripción**: Acceso total al sistema.

**Permisos**:
- Todos los permisos del sistema
- Gestión completa de usuarios, roles y permisos
- Acceso a todas las funcionalidades administrativas

**Uso**: Usuarios con control total sobre la aplicación.

### 2. Administrador (`admin`)

**Descripción**: Gestión completa de contenido y convocatorias.

**Permisos**:
- `programs.*` - Gestión completa de programas
- `calls.*` - Gestión completa de convocatorias (incluye publicar)
- `news.*` - Gestión completa de noticias (incluye publicar)
- `documents.*` - Gestión completa de documentos
- `events.*` - Gestión completa de eventos

**Restricciones**:
- No tiene acceso a la gestión de usuarios (`users.*`)

**Uso**: Personal administrativo que gestiona el contenido del portal.

### 3. Editor (`editor`)

**Descripción**: Creación y edición de contenido.

**Permisos**:
- `programs.view`, `programs.create`, `programs.edit`
- `calls.view`, `calls.create`, `calls.edit`
- `news.view`, `news.create`, `news.edit`
- `documents.view`, `documents.create`, `documents.edit`
- `events.view`, `events.create`, `events.edit`

**Restricciones**:
- No puede eliminar contenido (`delete`)
- No puede publicar contenido (`publish`)
- No tiene acceso a gestión de usuarios

**Uso**: Personal que crea y edita contenido pero requiere aprobación para publicar.

### 4. Viewer (`viewer`)

**Descripción**: Solo lectura.

**Permisos**:
- `programs.view`
- `calls.view`
- `news.view`
- `documents.view`
- `events.view`

**Restricciones**:
- Solo puede ver contenido, no puede crear, editar o eliminar

**Uso**: Personal que necesita consultar información sin capacidad de modificación.

---

## Permisos por Módulo

Los permisos están organizados por módulo funcional. Cada módulo tiene permisos específicos:

### Programas (`programs`)

- `programs.view` - Ver listados y detalles de programas
- `programs.create` - Crear nuevos programas
- `programs.edit` - Editar programas existentes
- `programs.delete` - Eliminar programas
- `programs.*` - Todos los permisos de programas

### Convocatorias (`calls`)

- `calls.view` - Ver listados y detalles de convocatorias
- `calls.create` - Crear nuevas convocatorias
- `calls.edit` - Editar convocatorias existentes
- `calls.delete` - Eliminar convocatorias
- `calls.publish` - Publicar convocatorias (cambiar estado a publicado)
- `calls.*` - Todos los permisos de convocatorias

### Noticias (`news`)

- `news.view` - Ver listados y detalles de noticias
- `news.create` - Crear nuevas noticias
- `news.edit` - Editar noticias existentes
- `news.delete` - Eliminar noticias
- `news.publish` - Publicar noticias (cambiar estado a publicado)
- `news.*` - Todos los permisos de noticias

### Documentos (`documents`)

- `documents.view` - Ver listados y detalles de documentos
- `documents.create` - Crear nuevos documentos
- `documents.edit` - Editar documentos existentes
- `documents.delete` - Eliminar documentos
- `documents.*` - Todos los permisos de documentos

### Eventos (`events`)

- `events.view` - Ver listados y detalles de eventos
- `events.create` - Crear nuevos eventos
- `events.edit` - Editar eventos existentes
- `events.delete` - Eliminar eventos
- `events.*` - Todos los permisos de eventos

### Usuarios (`users`)

- `users.view` - Ver listados y detalles de usuarios
- `users.create` - Crear nuevos usuarios
- `users.edit` - Editar usuarios existentes
- `users.delete` - Eliminar usuarios
- `users.*` - Todos los permisos de usuarios

**Nota**: Solo el rol `super-admin` tiene acceso a la gestión de usuarios.

---

## Uso de Constantes

Para facilitar el uso de roles y permisos en el código, se han creado clases de constantes:

### Roles

```php
use App\Support\Roles;

// Obtener todos los roles
$roles = Roles::all();

// Verificar si un rol es administrativo
if (Roles::isAdministrative($userRole)) {
    // ...
}

// Usar constantes directamente
$user->assignRole(Roles::SUPER_ADMIN);
```

### Permisos

```php
use App\Support\Permissions;

// Verificar permisos
if ($user->can(Permissions::CALLS_PUBLISH)) {
    // ...
}

// Obtener permisos por módulo
$programPermissions = Permissions::byModule()['programs'];

// Obtener permisos de solo lectura
$viewPermissions = Permissions::viewOnly();
```

---

## Implementación Técnica

### Seeder

Los roles y permisos se crean mediante el seeder `RolesAndPermissionsSeeder`, que:

1. Resetea la caché de permisos de Spatie Permission
2. Crea todos los permisos definidos
3. Crea los cuatro roles principales
4. Asigna permisos a cada rol según su nivel de acceso

### Base de Datos

El sistema utiliza el paquete [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) que crea las siguientes tablas:

- `roles` - Almacena los roles
- `permissions` - Almacena los permisos
- `model_has_roles` - Relación muchos a muchos entre modelos y roles
- `model_has_permissions` - Relación muchos a muchos entre modelos y permisos
- `role_has_permissions` - Relación muchos a muchos entre roles y permisos

### Modelo User

El modelo `User` utiliza el trait `HasRoles` de Spatie Permission, que proporciona métodos como:

- `hasRole($role)` - Verificar si tiene un rol
- `hasPermissionTo($permission)` - Verificar si tiene un permiso
- `assignRole($role)` - Asignar un rol
- `givePermissionTo($permission)` - Asignar un permiso directo
- `getAllPermissions()` - Obtener todos los permisos (directos y por rol)

---

## Ejemplos de Uso

### En Policies

```php
public function update(User $user, Program $program): bool
{
    return $user->can(Permissions::PROGRAMS_EDIT);
}
```

### En Middleware

El paquete Spatie Laravel Permission proporciona middleware incorporado que está registrado en `bootstrap/app.php`:

```php
// Un solo permiso
Route::middleware(['auth', 'permission:' . Permissions::CALLS_PUBLISH])
    ->group(function () {
        // Rutas que requieren permiso de publicar convocatorias
    });

// Múltiples permisos con OR (el usuario necesita al menos uno)
Route::middleware(['auth', 'permission:' . Permissions::CALLS_VIEW . '|' . Permissions::CALLS_CREATE])
    ->group(function () {
        // Rutas que requieren ver O crear convocatorias
    });

// Múltiples permisos con AND (el usuario necesita todos)
Route::middleware(['auth', 'permission:' . Permissions::CALLS_VIEW, 'permission:' . Permissions::CALLS_PUBLISH])
    ->group(function () {
        // Rutas que requieren ver Y publicar convocatorias
    });

// También disponible: middleware de roles
Route::middleware(['auth', 'role:' . Roles::ADMIN])
    ->group(function () {
        // Rutas solo para administradores
    });

// O combinación de rol o permiso
Route::middleware(['auth', 'role_or_permission:' . Roles::ADMIN . '|' . Permissions::CALLS_PUBLISH])
    ->group(function () {
        // Rutas para administradores O usuarios con permiso de publicar
    });
```

### En Blade

```blade
@can(Permissions::CALLS_PUBLISH)
    <button>Publicar Convocatoria</button>
@endcan
```

### En Livewire

```php
public function publish(): void
{
    $this->authorize(Permissions::CALLS_PUBLISH);
    
    // Lógica de publicación
}
```

---

## Notas Importantes

1. **Middleware**: La aplicación utiliza el middleware incorporado de Spatie Laravel Permission (`PermissionMiddleware`, `RoleMiddleware`, `RoleOrPermissionMiddleware`), registrado en `bootstrap/app.php` con los alias `permission`, `role` y `role_or_permission`.

2. **Caché**: Spatie Permission cachea los permisos por defecto. Si se modifican roles o permisos, es necesario limpiar la caché con `php artisan permission:cache-reset`.

3. **Wildcards**: Los permisos con `.*` (ej: `programs.*`) otorgan todos los permisos del módulo. Sin embargo, Spatie Permission requiere que `enable_wildcard_permission` esté habilitado en la configuración para que funcionen correctamente.

4. **Guard**: Todos los roles y permisos utilizan el guard `web` por defecto.

5. **Usuario Administrador Inicial**: El seeder `AdminUserSeeder` crea un usuario administrador con email `admin@erasmus-murcia.es` y contraseña `password` con rol `super-admin`.

---

**Fecha de Creación**: Diciembre 2025  
**Última Actualización**: Diciembre 2025
