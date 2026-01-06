# Plan de Desarrollo: Paso 3.5.10 - Gestión de Usuarios y Roles en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Usuarios y Roles en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Usuarios y Roles en el panel de administración con:
- Listado moderno con tabla interactiva
- Formularios de creación y edición
- Gestión de roles y permisos
- Vista de actividad del usuario (audit logs)
- **SoftDeletes**: Los usuarios nunca se eliminan permanentemente, solo se marcan como eliminados
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones críticas
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos de Desarrollo (15 Pasos)

### **Fase 1: Preparación Base**

#### **Paso 1: Implementar SoftDeletes en User**
- [ ] Crear migración para añadir columna `deleted_at` a la tabla `users`
- [ ] Ejecutar migración
- [ ] Actualizar modelo `User` para usar el trait `SoftDeletes`
- [ ] Verificar que las relaciones funcionen correctamente con SoftDeletes
- [ ] Actualizar factory si es necesario
- [ ] **Nota importante**: Un usuario no puede eliminarse a sí mismo (ya está en UserPolicy)

#### **Paso 2: Actualizar FormRequests con Autorización**
- [ ] Actualizar `StoreUserRequest`:
  - Añadir autorización con `UserPolicy::create()`
  - Añadir mensajes de error personalizados en español e inglés
  - Verificar validación de `email` único
  - Validación de `password` con confirmación
- [ ] Actualizar `UpdateUserRequest`:
  - Añadir autorización con `UserPolicy::update()`
  - Añadir mensajes de error personalizados
  - Validación de `email` único (ignorando el usuario actual)
  - Validación de `password` opcional (solo si se proporciona)
- [ ] Actualizar `AssignRoleRequest`:
  - Añadir autorización con `UserPolicy::assignRoles()`
  - Añadir mensajes de error personalizados
  - Validar que los roles existan en `Roles::all()`
- [ ] Verificar que `UserPolicy` tenga todos los métodos necesarios (ya existe)

---

### **Fase 2: Estructura Base y Listado** (MVP)

#### **Paso 3: Componente Index (Listado)**
- [ ] Crear componente Livewire `Admin\Users\Index`
- [ ] Implementar propiedades públicas:
  - `string $search = ''` - Búsqueda (con `#[Url]`)
  - `string $filterRole = ''` - Filtro por rol (con `#[Url]`)
  - `string $sortField = 'created_at'` - Campo de ordenación (con `#[Url]`)
  - `string $sortDirection = 'desc'` - Dirección de ordenación (con `#[Url]`)
  - `string $showDeleted = '0'` - Filtro de eliminados (con `#[Url]`)
  - `int $perPage = 15` - Elementos por página (con `#[Url]`)
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `?int $userToDelete = null` - ID de usuario a eliminar
  - `bool $showRestoreModal = false` - Modal de confirmación de restauración
  - `?int $userToRestore = null` - ID de usuario a restaurar
  - `bool $showForceDeleteModal = false` - Modal de confirmación de eliminación permanente
  - `?int $userToForceDelete = null` - ID de usuario a eliminar permanentemente
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `users()` - Computed property con paginación, filtros y ordenación
    - Eager load: `roles`, `permissions`
    - Filtro por búsqueda (nombre, email)
    - Filtro por rol
    - Filtro de eliminados
    - Ordenación
    - `withCount(['auditLogs'])` para mostrar actividad
  - `sortBy($field)` - Ordenación
  - `confirmDelete($userId)` - Confirmar eliminación
  - `delete()` - Eliminar con SoftDeletes (validar que no sea el usuario actual)
  - `confirmRestore($userId)` - Confirmar restauración
  - `restore()` - Restaurar usuario eliminado
  - `confirmForceDelete($userId)` - Confirmar eliminación permanente
  - `forceDelete()` - Eliminar permanentemente (solo super-admin, validar relaciones)
  - `resetFilters()` - Resetear filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedFilterRole()` - Resetear página al cambiar filtro
  - `updatedShowDeleted()` - Resetear página al cambiar filtro
  - `canCreate()` - Verificar si puede crear
  - `canViewDeleted()` - Verificar si puede ver eliminados
  - `canDeleteUser($user)` - Verificar si puede eliminar (no es el usuario actual)
  - `roles()` - Computed property para obtener todos los roles disponibles
  - `render()` - Renderizado con paginación
- [ ] Implementar autorización con `UserPolicy`
- [ ] Crear vista `livewire/admin/users/index.blade.php`:
  - Header con título y botón crear
  - Breadcrumbs
  - Filtros: búsqueda, filtro por rol, mostrar eliminados, reset
  - Tabla responsive con columnas:
    - Avatar/Iniciales del usuario
    - Nombre
    - Email
    - Roles (badges con colores)
    - Actividad (número de acciones en audit_logs)
    - Fecha de creación
    - Último acceso (opcional, si hay campo `last_login_at`)
    - Acciones
  - Modales de confirmación (eliminar, restaurar, force delete)
  - Paginación
  - Estado vacío
  - Loading states

---

### **Fase 3: Creación y Edición**

#### **Paso 4: Componente Create (Crear)**
- [ ] Crear componente Livewire `Admin\Users\Create`
- [ ] Implementar propiedades públicas:
  - `string $name = ''` - Nombre del usuario
  - `string $email = ''` - Email del usuario
  - `string $password = ''` - Contraseña
  - `string $password_confirmation = ''` - Confirmación de contraseña
  - `array $selectedRoles = []` - Roles seleccionados
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `store()` - Guardar nuevo usuario usando `StoreUserRequest`
    - Crear usuario
    - Asignar roles si se proporcionaron
    - Disparar evento de éxito
    - Redirigir a index o show
  - `roles()` - Computed property para obtener todos los roles disponibles
- [ ] Crear vista `livewire/admin/users/create.blade.php`:
  - Header con título y botón volver
  - Breadcrumbs
  - Formulario con campos:
    - Nombre (requerido)
    - Email (requerido, único)
    - Contraseña (requerida, con confirmación)
    - Selección de roles (checkboxes o multi-select)
      - Mostrar todos los roles disponibles con descripciones
      - Permitir selección múltiple
  - Botones: Guardar, Cancelar
  - Validación en tiempo real
  - Mensajes de error

#### **Paso 5: Componente Edit (Editar)**
- [ ] Crear componente Livewire `Admin\Users\Edit`
- [ ] Implementar propiedades públicas:
  - `User $user` - Usuario a editar
  - `string $name = ''` - Nombre del usuario
  - `string $email = ''` - Email del usuario
  - `string $password = ''` - Nueva contraseña (opcional)
  - `string $password_confirmation = ''` - Confirmación de contraseña
  - `array $selectedRoles = []` - Roles seleccionados
- [ ] Implementar métodos:
  - `mount(User $user)` - Inicialización con autorización y carga de datos
    - Cargar datos del usuario
    - Cargar roles actuales del usuario
  - `update()` - Actualizar usuario usando `UpdateUserRequest`
    - Actualizar datos básicos
    - Actualizar contraseña solo si se proporcionó
    - Sincronizar roles usando `AssignRoleRequest` (si tiene permisos)
    - Disparar evento de éxito
    - Redirigir a index o show
  - `roles()` - Computed property para obtener todos los roles disponibles
  - `canAssignRoles()` - Verificar si puede asignar roles (no es el usuario actual)
- [ ] Crear vista `livewire/admin/users/edit.blade.php`:
  - Header con título y botón volver
  - Breadcrumbs
  - Formulario con campos:
    - Nombre (requerido)
    - Email (requerido, único, ignorando el usuario actual)
    - Contraseña (opcional, con confirmación)
      - Mostrar mensaje: "Dejar en blanco para mantener la contraseña actual"
    - Selección de roles (checkboxes o multi-select)
      - Mostrar roles actuales marcados
      - Permitir modificar (solo si no es el usuario actual)
      - Mostrar advertencia si intenta modificar sus propios roles
  - Información adicional:
    - Fecha de creación
    - Última actualización
    - Email verificado (si aplica)
    - 2FA habilitado (si aplica)
  - Botones: Guardar, Cancelar
  - Validación en tiempo real
  - Mensajes de error

---

### **Fase 4: Vista de Detalle y Gestión de Roles**

#### **Paso 6: Componente Show (Vista Detalle)**
- [ ] Crear componente Livewire `Admin\Users\Show`
- [ ] Implementar propiedades públicas:
  - `User $user` - Usuario a mostrar
  - `int $auditLogsPerPage = 10` - Elementos por página en audit logs
- [ ] Implementar métodos:
  - `mount(User $user)` - Inicialización con autorización
  - `auditLogs()` - Computed property con paginación de audit logs del usuario
    - Ordenar por fecha descendente
    - Eager load: `model` (polimórfico)
  - `canEdit()` - Verificar si puede editar
  - `canDelete()` - Verificar si puede eliminar
  - `canAssignRoles()` - Verificar si puede asignar roles
- [ ] Crear vista `livewire/admin/users/show.blade.php`:
  - Header con título y botones de acción (editar, eliminar)
  - Breadcrumbs
  - Secciones:
    1. **Información Personal**:
       - Avatar/Iniciales
       - Nombre
       - Email
       - Email verificado (badge)
       - 2FA habilitado (badge)
       - Fecha de creación
       - Última actualización
    2. **Roles y Permisos**:
       - Lista de roles asignados (badges con colores)
       - Lista de permisos directos (si los hay)
       - Botón para editar roles (si tiene permisos)
    3. **Actividad Reciente** (Audit Logs):
       - Tabla con acciones recientes
       - Columnas: Fecha, Acción, Modelo, Cambios (JSON formateado)
       - Paginación
       - Enlaces a los modelos afectados (si aplica)
    4. **Estadísticas** (opcional):
       - Total de acciones realizadas
       - Acciones por tipo
       - Última actividad
  - Modales de confirmación (eliminar, force delete)

#### **Paso 7: Componente para Gestión de Roles (Modal o Página Separada)**
- [ ] Crear componente Livewire `Admin\Users\AssignRoles` (o integrar en Edit)
- [ ] Implementar propiedades públicas:
  - `User $user` - Usuario al que asignar roles
  - `array $selectedRoles = []` - Roles seleccionados
- [ ] Implementar métodos:
  - `mount(User $user)` - Inicialización con autorización
    - Cargar roles actuales del usuario
  - `assignRoles()` - Asignar roles usando `AssignRoleRequest`
    - Sincronizar roles del usuario
    - Disparar evento de éxito
    - Cerrar modal o redirigir
  - `roles()` - Computed property para obtener todos los roles disponibles
- [ ] Crear vista (modal o página):
  - Lista de roles disponibles con checkboxes
  - Descripción de cada rol
  - Mostrar roles actuales marcados
  - Botones: Guardar, Cancelar
  - Validación

---

### **Fase 5: Rutas y Navegación**

#### **Paso 8: Configurar Rutas**
- [ ] Añadir rutas en `routes/web.php` dentro del grupo `admin`:
  ```php
  // Rutas de Usuarios
  Route::get('/usuarios', \App\Livewire\Admin\Users\Index::class)->name('users.index');
  Route::get('/usuarios/crear', \App\Livewire\Admin\Users\Create::class)->name('users.create');
  Route::get('/usuarios/{user}', \App\Livewire\Admin\Users\Show::class)->name('users.show');
  Route::get('/usuarios/{user}/editar', \App\Livewire\Admin\Users\Edit::class)->name('users.edit');
  ```
- [ ] Verificar que las rutas estén protegidas con middleware `auth` y `verified`
- [ ] Probar navegación entre rutas

#### **Paso 9: Integrar en Navegación**
- [ ] Añadir enlace a "Usuarios" en el sidebar de administración
- [ ] Verificar que solo se muestre si el usuario tiene permisos `users.view`
- [ ] Añadir icono apropiado (usuario o usuarios)
- [ ] Verificar breadcrumbs en todas las vistas

---

### **Fase 6: Optimizaciones y Mejoras**

#### **Paso 10: Optimizaciones de Consultas**
- [ ] Implementar eager loading en Index:
  - `with(['roles', 'permissions'])`
  - `withCount(['auditLogs'])`
- [ ] Implementar índices en base de datos si es necesario:
  - `users.email` (ya debería existir)
  - `users.deleted_at` (para SoftDeletes)
- [ ] Optimizar consulta de audit logs en Show:
  - Limitar resultados iniciales
  - Paginación eficiente

#### **Paso 11: Componentes UI Reutilizables**
- [ ] Crear componente para mostrar avatar/iniciales del usuario
- [ ] Crear componente para mostrar roles (badges con colores)
- [ ] Crear componente para mostrar permisos
- [ ] Crear componente para mostrar actividad (audit log entry)
- [ ] Verificar que los componentes sean responsive y accesibles

---

### **Fase 7: Testing**

#### **Paso 12: Tests de Componentes Livewire**
- [ ] Crear test `tests/Feature/Livewire/Admin/Users/IndexTest.php`:
  - Test de autorización (solo usuarios con permisos pueden ver)
  - Test de listado de usuarios
  - Test de búsqueda
  - Test de filtro por rol
  - Test de ordenación
  - Test de paginación
  - Test de eliminación (SoftDelete)
  - Test de restauración
  - Test de force delete (solo super-admin)
  - Test de que un usuario no puede eliminarse a sí mismo
- [ ] Crear test `tests/Feature/Livewire/Admin/Users/CreateTest.php`:
  - Test de autorización
  - Test de creación de usuario
  - Test de asignación de roles
  - Test de validación de campos
- [ ] Crear test `tests/Feature/Livewire/Admin/Users/EditTest.php`:
  - Test de autorización
  - Test de actualización de usuario
  - Test de actualización de contraseña
  - Test de actualización de roles
  - Test de que un usuario no puede modificar sus propios roles
  - Test de validación
- [ ] Crear test `tests/Feature/Livewire/Admin/Users/ShowTest.php`:
  - Test de autorización
  - Test de visualización de información
  - Test de visualización de roles
  - Test de visualización de audit logs
  - Test de paginación de audit logs

#### **Paso 13: Tests de FormRequests**
- [ ] Crear test `tests/Feature/Http/Requests/StoreUserRequestTest.php`:
  - Test de autorización
  - Test de validación de campos requeridos
  - Test de validación de email único
  - Test de validación de contraseña
- [ ] Crear test `tests/Feature/Http/Requests/UpdateUserRequestTest.php`:
  - Test de autorización
  - Test de validación de campos
  - Test de validación de email único (ignorando usuario actual)
  - Test de contraseña opcional
- [ ] Crear test `tests/Feature/Http/Requests/AssignRoleRequestTest.php`:
  - Test de autorización
  - Test de validación de roles
  - Test de validación de roles válidos

---

### **Fase 8: Documentación y Finalización**

#### **Paso 14: Documentación**
- [ ] Actualizar `docs/planificacion_pasos.md` marcando el paso 3.5.10 como completado
- [ ] Crear resumen ejecutivo del desarrollo (similar a `paso-3.5.6-resumen.md`)
- [ ] Documentar funcionalidades implementadas
- [ ] Documentar decisiones técnicas importantes

#### **Paso 15: Revisión Final y Ajustes** ✅
- [x] Ejecutar `vendor/bin/pint --dirty` para formatear código
- [x] Ejecutar todos los tests relacionados (172 tests pasados)
- [x] Verificar que no haya errores de linting
- [x] Revisar accesibilidad (WCAG) - Estructura semántica con h1, breadcrumbs, labels
- [x] Verificar responsive design - Uso de clases sm:, lg:, grid responsive
- [x] Verificar rutas configuradas correctamente (4 rutas)
- [x] Verificar que los permisos funcionen correctamente (cubierto en tests)
- [x] Verificar que los roles se asignen correctamente (cubierto en tests)

---

## 🔧 Consideraciones Técnicas

### **SoftDeletes en User**
- Los usuarios nunca se eliminan permanentemente por defecto
- Solo super-admin puede realizar `forceDelete()`
- Antes de `forceDelete()`, validar que no existan relaciones críticas:
  - Audit logs (opcional, puede mantener el user_id como null)
  - Otras relaciones si las hay
- Un usuario no puede eliminarse a sí mismo (ya implementado en UserPolicy)

### **Gestión de Roles**
- Usar Spatie Permission para asignar roles
- Los roles se asignan usando `$user->syncRoles($roles)`
- Validar que los roles existan usando `Roles::all()`
- Un usuario no puede modificar sus propios roles (ya implementado en UserPolicy)

### **Audit Logs**
- Mostrar actividad del usuario desde la tabla `audit_logs`
- Filtrar por `user_id`
- Mostrar información del modelo afectado (polimórfico)
- Formatear JSON de cambios de forma legible

### **Seguridad**
- Validar siempre autorización con `UserPolicy`
- Verificar que un usuario no pueda eliminarse a sí mismo
- Verificar que un usuario no pueda modificar sus propios roles
- Validar permisos en cada acción

### **UX/UI**
- Usar Flux UI para componentes consistentes
- Mostrar avatares/iniciales para identificación visual
- Usar badges con colores para roles
- Mostrar estados de carga durante operaciones
- Mostrar mensajes de éxito/error claros
- Implementar modales de confirmación para acciones destructivas

---

## 📝 Notas Adicionales

1. **FormRequests Existentes**: Los FormRequests ya existen, solo necesitan actualizarse con autorización y mensajes personalizados.

2. **UserPolicy Existente**: La UserPolicy ya existe y tiene todos los métodos necesarios, incluyendo `assignRoles()`.

3. **Spatie Permission**: El modelo User ya usa el trait `HasRoles` de Spatie Permission.

4. **Audit Logs**: El modelo `AuditLog` ya existe y tiene relación con `User`.

5. **Roles**: Los roles están definidos en `App\Support\Roles` con constantes.

6. **Permisos**: Los permisos están definidos en `App\Support\Permissions`.

---

## ✅ Criterios de Aceptación

- [ ] Todos los componentes Livewire están creados y funcionan correctamente
- [ ] Los FormRequests tienen autorización y validación completa
- [ ] SoftDeletes está implementado en User
- [ ] La gestión de roles funciona correctamente
- [ ] Los audit logs se muestran correctamente
- [ ] Las rutas están configuradas y funcionan
- [ ] La navegación está integrada
- [ ] Todos los tests pasan
- [ ] El código está formateado con Pint
- [ ] No hay errores de linting
- [ ] La documentación está actualizada

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Pendiente de implementación

