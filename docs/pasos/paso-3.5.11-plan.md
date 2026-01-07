# Plan Detallado: Paso 3.5.11 - Gestión de Roles y Permisos

## Objetivo

Implementar un CRUD completo y moderno para la gestión de Roles y Permisos en el panel de administración, permitiendo a los super-administradores crear, editar y gestionar roles, así como asignar permisos a cada rol.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar roles
- ✅ **Gestión de Permisos**: Asignar y revocar permisos a roles
- ✅ **Visualización de Usuarios**: Ver qué usuarios tienen cada rol
- ✅ **Validación de Roles del Sistema**: Proteger los 4 roles principales del sistema
- ✅ **Interfaz Moderna**: Componentes Flux UI con diseño responsive
- ✅ **Autorización**: Solo super-admin puede gestionar roles
- ✅ **Tests Completos**: Cobertura completa de funcionalidades

## Consideraciones Importantes

1. **Roles del Sistema**: Los 4 roles principales (`super-admin`, `admin`, `editor`, `viewer`) NO deben poder eliminarse, pero sí pueden editarse sus permisos.

2. **Sin SoftDeletes**: Los roles de Spatie Permission NO tienen SoftDeletes. Si un rol tiene usuarios asignados, no se puede eliminar directamente.

3. **Permisos**: Los permisos se organizan por módulo (programs, calls, news, documents, events, users) y se muestran agrupados.

4. **Validación**: Un rol debe tener un nombre único y válido según las constantes de `Roles::all()`.

## Estructura de Archivos a Crear

```
app/
├── Http/
│   └── Requests/
│       ├── StoreRoleRequest.php
│       └── UpdateRoleRequest.php
├── Livewire/
│   └── Admin/
│       └── Roles/
│           ├── Index.php
│           ├── Create.php
│           ├── Edit.php
│           └── Show.php
└── Policies/
    └── RolePolicy.php

resources/
└── views/
    └── livewire/
        └── admin/
            └── roles/
                ├── index.blade.php
                ├── create.blade.php
                ├── edit.blade.php
                └── show.blade.php

tests/
└── Feature/
    └── Livewire/
        └── Admin/
            └── Roles/
                ├── IndexTest.php
                ├── CreateTest.php
                ├── EditTest.php
                └── ShowTest.php
```

---

## Fase 1: Form Requests y Policy

### Paso 1.1: Crear StoreRoleRequest

**Archivo**: `app/Http/Requests/StoreRoleRequest.php`

**Funcionalidad**:
- Validar nombre del rol (requerido, único, debe estar en Roles::all())
- Validar permisos (array opcional, cada permiso debe existir en Permissions::all())
- Autorización: solo super-admin puede crear roles

**Reglas de validación**:
- `name`: required, string, max:255, unique:roles,name, in:Roles::all()
- `permissions`: nullable, array
- `permissions.*`: string, exists:permissions,name

### Paso 1.2: Crear UpdateRoleRequest

**Archivo**: `app/Http/Requests/UpdateRoleRequest.php`

**Funcionalidad**:
- Validar nombre del rol (requerido, único excepto el rol actual, debe estar en Roles::all())
- Validar permisos (array opcional)
- Autorización: solo super-admin puede actualizar roles
- Validar que los roles del sistema no puedan cambiar su nombre

**Reglas de validación**:
- `name`: required, string, max:255, unique:roles,name,{role->id}, in:Roles::all()
- `permissions`: nullable, array
- `permissions.*`: string, exists:permissions,name

### Paso 1.3: Crear RolePolicy

**Archivo**: `app/Policies/RolePolicy.php`

**Funcionalidad**:
- Autorizar acciones sobre roles (solo super-admin)
- Métodos: `viewAny()`, `view()`, `create()`, `update()`, `delete()`
- Validar que los roles del sistema no puedan eliminarse

**Nota**: Como Spatie Permission no tiene un modelo Role con SoftDeletes, usaremos el modelo `Spatie\Permission\Models\Role` directamente.

---

## Fase 2: Componente Index (Listado)

### Paso 2.1: Crear componente Livewire Index

**Archivo**: `app/Livewire/Admin/Roles/Index.php`

**Propiedades**:
- `search` (string): Búsqueda por nombre
- `sortField` (string): Campo de ordenación (name, users_count, permissions_count)
- `sortDirection` (string): Dirección de ordenación (asc/desc)
- `perPage` (int): Elementos por página
- Modales para confirmación de eliminación

**Métodos principales**:
- `roles()` (computed): Listado paginado con eager loading de usuarios y permisos
- `sortBy()`: Cambiar ordenación
- `confirmDelete()`: Confirmar eliminación
- `delete()`: Eliminar rol (validar que no tenga usuarios)
- `canDeleteRole()`: Verificar si un rol puede eliminarse
- `getRoleDisplayName()`: Obtener nombre traducido del rol
- `getRoleBadgeVariant()`: Obtener variante de badge según rol

**Características**:
- Búsqueda por nombre
- Ordenación por nombre, número de usuarios, número de permisos
- Mostrar contador de usuarios y permisos por rol
- Validar que roles del sistema no puedan eliminarse
- Validar que roles con usuarios no puedan eliminarse

### Paso 2.2: Crear vista Index

**Archivo**: `resources/views/livewire/admin/roles/index.blade.php`

**Componentes**:
- Tabla con columnas: Nombre, Usuarios, Permisos, Acciones
- Filtros: Búsqueda, ordenación
- Botones: Crear rol, Ver, Editar, Eliminar
- Modales de confirmación de eliminación
- Badges para mostrar roles del sistema
- Mensajes de estado vacío

---

## Fase 3: Componente Create (Crear)

### Paso 3.1: Crear componente Livewire Create

**Archivo**: `app/Livewire/Admin/Roles/Create.php`

**Propiedades**:
- `name` (string): Nombre del rol
- `selectedPermissions` (array): Permisos seleccionados

**Métodos principales**:
- `availablePermissions()` (computed): Obtener todos los permisos agrupados por módulo
- `store()`: Crear rol y asignar permisos
- `getPermissionDisplayName()`: Obtener nombre traducido del permiso
- `getModuleDisplayName()`: Obtener nombre traducido del módulo

**Características**:
- Formulario con campo nombre
- Selección de permisos agrupados por módulo (checkboxes)
- Validación en tiempo real del nombre
- Botón para seleccionar todos los permisos de un módulo

### Paso 3.2: Crear vista Create

**Archivo**: `resources/views/livewire/admin/roles/create.blade.php`

**Componentes**:
- Formulario con campo nombre
- Secciones agrupadas por módulo para permisos
- Checkboxes para cada permiso
- Botones: "Seleccionar todos" por módulo
- Botones: Guardar, Cancelar

---

## Fase 4: Componente Edit (Editar)

### Paso 4.1: Crear componente Livewire Edit

**Archivo**: `app/Livewire/Admin/Roles/Edit.php`

**Propiedades**:
- `role` (Role): Rol a editar
- `name` (string): Nombre del rol
- `selectedPermissions` (array): Permisos seleccionados

**Métodos principales**:
- `mount()`: Cargar datos del rol
- `availablePermissions()` (computed): Obtener todos los permisos agrupados por módulo
- `update()`: Actualizar rol y sincronizar permisos
- `isSystemRole()`: Verificar si es un rol del sistema
- `canChangeName()`: Verificar si se puede cambiar el nombre

**Características**:
- Formulario pre-rellenado con datos del rol
- Campo nombre deshabilitado si es rol del sistema
- Selección de permisos con valores actuales marcados
- Validación de que roles del sistema no puedan cambiar nombre

### Paso 4.2: Crear vista Edit

**Archivo**: `resources/views/livewire/admin/roles/edit.blade.php`

**Componentes**:
- Formulario similar a Create pero con datos precargados
- Indicador visual si es rol del sistema
- Mensaje informativo sobre restricciones de roles del sistema

---

## Fase 5: Componente Show (Detalle)

### Paso 5.1: Crear componente Livewire Show

**Archivo**: `app/Livewire/Admin/Roles/Show.php`

**Propiedades**:
- `role` (Role): Rol a mostrar
- `usersPerPage` (int): Usuarios por página

**Métodos principales**:
- `mount()`: Cargar rol con relaciones
- `users()` (computed): Listado paginado de usuarios con este rol
- `permissions()` (computed): Permisos del rol agrupados por módulo
- `isSystemRole()`: Verificar si es rol del sistema
- `canDelete()`: Verificar si puede eliminarse

**Características**:
- Información del rol (nombre, fecha de creación)
- Listado de permisos agrupados por módulo
- Listado paginado de usuarios con este rol
- Botones: Editar, Eliminar (si aplica)
- Indicadores visuales para roles del sistema

### Paso 5.2: Crear vista Show

**Archivo**: `resources/views/livewire/admin/roles/show.blade.php`

**Componentes**:
- Card con información del rol
- Sección de permisos agrupados por módulo
- Tabla de usuarios con este rol (paginada)
- Botones de acción: Editar, Eliminar
- Breadcrumbs

---

## Fase 6: Rutas y Navegación

### Paso 6.1: Agregar rutas

**Archivo**: `routes/web.php`

Agregar dentro del grupo `admin`:

```php
// Rutas de Roles
Route::get('/roles', \App\Livewire\Admin\Roles\Index::class)->name('roles.index');
Route::get('/roles/crear', \App\Livewire\Admin\Roles\Create::class)->name('roles.create');
Route::get('/roles/{role}', \App\Livewire\Admin\Roles\Show::class)->name('roles.show');
Route::get('/roles/{role}/editar', \App\Livewire\Admin\Roles\Edit::class)->name('roles.edit');
```

### Paso 6.2: Agregar a navegación

**Archivo**: `resources/views/components/nav/admin-nav.blade.php` (o donde esté la navegación)

Agregar enlace a roles en el menú de administración, solo visible para super-admin.

---

## Fase 7: Tests

### Paso 7.1: Tests de Form Requests

**Archivos**:
- `tests/Feature/Http/Requests/StoreRoleRequestTest.php`
- `tests/Feature/Http/Requests/UpdateRoleRequestTest.php`

**Casos a probar**:
- Validación de nombre requerido
- Validación de nombre único
- Validación de nombre en Roles::all()
- Validación de permisos existentes
- Autorización (solo super-admin)

### Paso 7.2: Tests de Policy

**Archivo**: `tests/Feature/Policies/RolePolicyTest.php`

**Casos a probar**:
- Super-admin puede hacer todo
- Otros roles no pueden gestionar roles
- Roles del sistema no pueden eliminarse

### Paso 7.3: Tests de Componentes Livewire

**Archivos**:
- `tests/Feature/Livewire/Admin/Roles/IndexTest.php`
- `tests/Feature/Livewire/Admin/Roles/CreateTest.php`
- `tests/Feature/Livewire/Admin/Roles/EditTest.php`
- `tests/Feature/Livewire/Admin/Roles/ShowTest.php`

**Casos a probar**:
- Autorización (solo super-admin)
- Crear rol con permisos
- Editar rol y permisos
- Eliminar rol (validar usuarios)
- No eliminar roles del sistema
- Búsqueda y filtrado
- Paginación

---

## Fase 8: Documentación

### Paso 8.1: Crear documentación técnica

**Archivo**: `docs/admin-roles-crud.md`

Documentar:
- Descripción general
- Características principales
- Componentes Livewire
- Form Requests
- Policy
- Rutas
- Tests

### Paso 8.2: Actualizar planificación

**Archivo**: `docs/planificacion_pasos.md`

Marcar el paso 3.5.11 como completado.

---

## Orden de Implementación Recomendado

1. **Fase 1**: Form Requests y Policy (base de validación y autorización)
2. **Fase 2**: Componente Index (listado básico)
3. **Fase 3**: Componente Create (crear roles)
4. **Fase 4**: Componente Edit (editar roles)
5. **Fase 5**: Componente Show (detalle de roles)
6. **Fase 6**: Rutas y navegación
7. **Fase 7**: Tests completos
8. **Fase 8**: Documentación

---

## Notas Técnicas

1. **Modelo Role**: Usar `Spatie\Permission\Models\Role` directamente.

2. **Permisos Agrupados**: Los permisos se mostrarán agrupados por módulo usando `Permissions::byModule()`.

3. **Validación de Eliminación**: 
   - Roles del sistema: NO pueden eliminarse
   - Roles con usuarios: NO pueden eliminarse (verificar con `$role->users()->count()`)

4. **Caché de Permisos**: Después de modificar roles/permisos, limpiar caché con `app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions()`.

5. **Traducciones**: Usar las traducciones existentes y agregar nuevas si es necesario en `lang/es/common.php` y `lang/en/common.php`.

6. **Componentes Reutilizables**: Reutilizar componentes UI existentes como badges, modales, tablas.

---

## Validaciones Especiales

1. **Roles del Sistema**: Los 4 roles principales (`super-admin`, `admin`, `editor`, `viewer`) no pueden:
   - Eliminarse
   - Cambiar su nombre (en Edit)

2. **Roles con Usuarios**: Un rol que tiene usuarios asignados no puede eliminarse.

3. **Permisos**: Solo se pueden asignar permisos que existan en la base de datos.

4. **Nombre Único**: El nombre del rol debe ser único en la tabla `roles`.

---

## Mejoras Futuras (Opcional)

1. **Duplicar Rol**: Crear un nuevo rol basado en uno existente.
2. **Exportar/Importar Roles**: Exportar configuración de roles a JSON.
3. **Historial de Cambios**: Registrar cambios en roles y permisos en audit logs.
4. **Permisos Personalizados**: Permitir crear permisos personalizados desde la interfaz.

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan completado - Listo para implementación

