# Paso 27: Desarrollo Completo del CRUD de Roles y Permisos - Prompts y Respuestas

Este documento contiene todos los prompts del usuario y las respuestas generadas durante el desarrollo completo del CRUD de Roles y Permisos en el panel de administración (Paso 3.5.11).

**Fecha de Inicio**: Enero 2026  
**Fecha de Finalización**: Enero 2026  
**Duración**: Desarrollo completo con corrección de errores, tests y documentación  
**Estado**: ✅ Completado - 153 tests pasando (249 assertions), 2040 tests totales pasando

---

## Índice

1. [Fase 1: Planificación y Preparación](#fase-1-planificación-y-preparación)
2. [Fase 2: Form Requests y Policy](#fase-2-form-requests-y-policy)
3. [Fase 3: Componente Index](#fase-3-componente-index)
4. [Fase 4: Componente Create](#fase-4-componente-create)
5. [Fase 5: Componente Edit](#fase-5-componente-edit)
6. [Fase 6: Componente Show](#fase-6-componente-show)
7. [Fase 7: Integración y Navegación](#fase-7-integración-y-navegación)
8. [Fase 8: Testing](#fase-8-testing)
9. [Fase 9: Documentación](#fase-9-documentación)

---

## Fase 1: Planificación y Preparación

### Prompt 1: Solicitud Inicial

**Usuario:**
> En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que hace de índice y otro llamado @planificacion_pasos.md que contiene las tareas a desarrollar en orden. Ahora, siguiendo esa recomendación, vamos a desarrollar el paso 3.5.11. que corresponde a la gestión de Roles y Permisos en el panel de administración. Me gustaría que tuviera un desarrollo moderno y muy actual. Se pueden reutilizar aquellos componentes que ya tenemos y generar otros nuevos en caso necesario. Es posible que los archivos de validaciones (FormRequest) ya estén creados en uno de los pasos iniciales del desarrollo, tan solo tendrás que adaptarlo a lo que se vaya a realizar, cambiando o ampliando cosas. Puedes revisar cómo se han hecho los CRUD anteriores para seguir un desarrollo lo más acorde posible. Establece los pasos a seguir para desarrollar el paso 3.5.11. y así ir poco a poco hasta completarlo.

**Respuesta:**
Se creó un plan detallado de 8 fases organizadas en pasos específicos:
1. **Fase 1: Base (Form Requests + Policy)** - Crear/adaptar Form Requests y Policy
2. **Fase 2: Componente Index** - Listado de roles con búsqueda, ordenamiento y paginación
3. **Fase 3: Componente Create** - Formulario de creación con selección de permisos
4. **Fase 4: Componente Edit** - Formulario de edición con protección de roles del sistema
5. **Fase 5: Componente Show** - Vista detallada con usuarios asignados
6. **Fase 6: Integración (Rutas y Navegación)** - Rutas y enlace en sidebar
7. **Fase 7: Tests** - Tests completos para Form Requests, Policy y componentes Livewire
8. **Fase 8: Documentación** - Documentación técnica y actualización de planificación

Se crearon los archivos:
- `docs/pasos/paso-3.5.11-plan.md` - Plan detallado paso a paso (8 fases)

**Características principales del plan:**
- Protección de roles del sistema (super-admin, admin, editor, viewer)
- Validación de nombres de roles según constantes del sistema
- Gestión de permisos agrupados por módulo
- Visualización de usuarios asignados a cada rol
- Solo super-admin puede gestionar roles

---

## Fase 2: Form Requests y Policy

### Prompt 2: Inicio de Desarrollo - Fase 1

**Usuario:**
> Comencemos con la Fase 1

**Respuesta:**
Se implementó la Fase 1 completa:

**Paso 1: Form Requests**
- ✅ Creación de `StoreRoleRequest` con validación de nombre único y válido según `Roles::all()`
- ✅ Creación de `UpdateRoleRequest` con validación especial para proteger nombres de roles del sistema
- ✅ Mensajes de error personalizados en español
- ✅ Autorización mediante `RolePolicy`

**Paso 2: Policy**
- ✅ Creación de `RolePolicy` con método `before()` para super-admin
- ✅ Protección de roles del sistema en método `delete()`
- ✅ Verificación de usuarios asignados antes de eliminar

**Archivos creados:**
- `app/Http/Requests/StoreRoleRequest.php`
- `app/Http/Requests/UpdateRoleRequest.php`
- `app/Policies/RolePolicy.php`

**Características implementadas:**
- Validación de nombres de roles según lista permitida
- Protección de roles del sistema (no se pueden eliminar ni cambiar de nombre)
- Verificación de usuarios asignados antes de eliminar
- Autorización completa mediante policy

---

## Fase 3: Componente Index

### Prompt 3: Continuar con Fase 2

**Usuario:**
> Sigamos con la Fase 2

**Respuesta:**
Se implementó la Fase 2 - Componente Index:

**Componente Livewire:**
- ✅ Creación de `Admin\Roles\Index` con propiedades públicas usando `#[Url]`
- ✅ Implementación de búsqueda, ordenamiento y paginación
- ✅ Método `roles()` (computed) con filtros y eager loading
- ✅ Métodos de acción: `sortBy()`, `confirmDelete()`, `delete()`, `resetFilters()`
- ✅ Métodos helper: `isSystemRole()`, `getRoleDisplayName()`, `getRoleBadgeVariant()`

**Vista Blade:**
- ✅ Tabla responsive con columnas: Nombre, Permisos, Usuarios, Fecha Creación, Acciones
- ✅ Búsqueda en tiempo real
- ✅ Ordenamiento por nombre
- ✅ Paginación configurable
- ✅ Modal de confirmación para eliminación
- ✅ Badges para roles del sistema

**Rutas:**
- ✅ Añadida ruta `/admin/roles` en `routes/web.php`

**Archivos creados:**
- `app/Livewire/Admin/Roles/Index.php`
- `resources/views/livewire/admin/roles/index.blade.php`

**Archivos modificados:**
- `routes/web.php`

---

## Fase 4: Componente Create

### Prompt 4: Continuar con Fase 3

**Usuario:**
> Continuamos con la Fase 3

**Respuesta:**
Se implementó la Fase 3 - Componente Create:

**Componente Livewire:**
- ✅ Creación de `Admin\Roles\Create` con propiedades públicas
- ✅ Método `availablePermissions()` (computed) para agrupar permisos por módulo
- ✅ Método `store()` usando `StoreRoleRequest` para validación
- ✅ Métodos helper: `getModuleDisplayName()`, `getPermissionDisplayName()`
- ✅ Métodos de selección masiva: `selectAllModulePermissions()`, `deselectAllModulePermissions()`, `areAllModulePermissionsSelected()`
- ✅ Limpieza de caché de permisos después de crear

**Vista Blade:**
- ✅ Formulario con campo de nombre (select con roles válidos)
- ✅ Selección de permisos agrupados por módulo
- ✅ Botones para seleccionar/deseleccionar todos los permisos de un módulo
- ✅ Resumen de permisos seleccionados en sidebar
- ✅ Validación en tiempo real

**Correcciones realizadas:**
- Cambio de campo de texto a select para el nombre del rol (debe ser uno de los roles válidos)
- Corrección de validación en método `store()` para usar validación de Livewire correctamente

**Archivos creados:**
- `app/Livewire/Admin/Roles/Create.php`
- `resources/views/livewire/admin/roles/create.blade.php`

**Archivos modificados:**
- `routes/web.php` - Añadida ruta de creación

---

## Fase 5: Componente Edit

### Prompt 5: Continuar con Fase 4

**Usuario:**
> Prosigamos con la Fase 4

**Respuesta:**
Se implementó la Fase 4 - Componente Edit:

**Componente Livewire:**
- ✅ Creación de `Admin\Roles\Edit` con propiedades públicas
- ✅ Método `mount()` para cargar datos del rol
- ✅ Método `availablePermissions()` (computed) para agrupar permisos por módulo
- ✅ Método `update()` usando `UpdateRoleRequest` con validación personalizada
- ✅ Métodos helper: `isSystemRole()`, `canChangeName()`, `getRoleDisplayName()`
- ✅ Métodos de selección masiva de permisos
- ✅ Validación especial para proteger nombres de roles del sistema
- ✅ Limpieza de caché de permisos después de actualizar

**Vista Blade:**
- ✅ Formulario pre-llenado con datos actuales
- ✅ Campo de nombre deshabilitado para roles del sistema
- ✅ Información sobre protección de roles del sistema
- ✅ Selección de permisos agrupados por módulo
- ✅ Resumen de permisos seleccionados

**Correcciones realizadas:**
- Añadido método `getRoleDisplayName()` que faltaba

**Archivos creados:**
- `app/Livewire/Admin/Roles/Edit.php`
- `resources/views/livewire/admin/roles/edit.blade.php`

**Archivos modificados:**
- `routes/web.php` - Añadida ruta de edición

---

## Fase 6: Componente Show

### Prompt 6: Continuar con Fase 5

**Usuario:**
> Ahora pasamos a la Fase 5

**Respuesta:**
Se implementó la Fase 5 - Componente Show:

**Componente Livewire:**
- ✅ Creación de `Admin\Roles\Show` con propiedades públicas
- ✅ Método `mount()` para cargar rol con permisos y contador de usuarios
- ✅ Método `permissionsByModule()` (computed) para agrupar permisos asignados por módulo
- ✅ Método `users()` (computed) para obtener usuarios paginados con este rol
- ✅ Métodos helper: `isSystemRole()`, `canDelete()`, `canEdit()`, `getRoleDisplayName()`, `getRoleBadgeVariant()`
- ✅ Método `delete()` con validaciones (no eliminar roles del sistema ni con usuarios)
- ✅ Limpieza de caché de permisos después de eliminar

**Vista Blade:**
- ✅ Vista detallada del rol con información completa
- ✅ Badge indicando si es rol del sistema
- ✅ Permisos agrupados por módulo (solo los asignados)
- ✅ Lista paginada de usuarios con este rol
- ✅ Botones de acción: Editar, Eliminar (si está permitido)
- ✅ Información adicional: Fechas, contadores
- ✅ Modal de confirmación para eliminación
- ✅ Estados vacíos cuando no hay usuarios asignados

**Archivos creados:**
- `app/Livewire/Admin/Roles/Show.php`
- `resources/views/livewire/admin/roles/show.blade.php`

**Archivos modificados:**
- `routes/web.php` - Añadida ruta de visualización

---

## Fase 7: Integración y Navegación

### Prompt 7: Integración antes de Probar

**Usuario:**
> Hagamos la Fase 6 antes de probar en el navegador

**Respuesta:**
Se implementó la Fase 6 - Integración y Navegación:

**Rutas:**
- ✅ Todas las rutas ya estaban añadidas en fases anteriores

**Navegación:**
- ✅ Añadido enlace "Roles y Permisos" en sidebar bajo grupo "Sistema"
- ✅ Icono `shield-check` para el enlace
- ✅ Verificación de autorización con `@can('viewAny', Role::class)`
- ✅ Resaltado de enlace activo con `request()->routeIs('admin.roles.*')`

**Traducciones:**
- ✅ Añadida traducción en `lang/es/common.php`: `'roles' => 'Roles y Permisos'`
- ✅ Añadida traducción en `lang/en/common.php`: `'roles' => 'Roles and Permissions'`

**Service Provider:**
- ✅ Registro manual de `RolePolicy` en `AppServiceProvider` usando `Gate::policy()`
- ✅ Necesario porque `Spatie\Permission\Models\Role` no sigue la convención de auto-descubrimiento

**Correcciones realizadas:**
- Corrección de `RolePolicy`: Cambio de `return false;` a `return null;` en métodos para permitir que `before()` funcione correctamente
- Registro manual de policy porque el modelo Role de Spatie no está en namespace `App\Models`

**Archivos modificados:**
- `resources/views/components/layouts/app/sidebar.blade.php`
- `lang/es/common.php`
- `lang/en/common.php`
- `app/Providers/AppServiceProvider.php`
- `app/Policies/RolePolicy.php` - Corrección de métodos para permitir `before()`

---

### Prompt 8: Problema con Enlace en Sidebar

**Usuario:**
> Acabo de entrar en el dashboard del navegador, pero no veo enlace en la barra lateral.

**Respuesta:**
Se identificaron y corrigieron dos problemas:

**Problema 1: RolePolicy no permitía acceso**
- Los métodos `viewAny()`, `view()`, `create()`, `update()` devolvían `false`, lo que anulaba el método `before()` que otorga acceso completo a super-admin
- **Solución**: Cambio de `return false;` a `return null;` en estos métodos para permitir que `before()` tenga efecto

**Problema 2: Policy no registrada automáticamente**
- Laravel auto-descubre policies para modelos en `App\Models`, pero `Spatie\Permission\Models\Role` está en otro namespace
- **Solución**: Registro manual de la policy en `AppServiceProvider` usando `Gate::policy(Role::class, RolePolicy::class);`

**Archivos modificados:**
- `app/Policies/RolePolicy.php` - Cambio de `false` a `null` en métodos
- `app/Providers/AppServiceProvider.php` - Registro manual de policy

**Resultado:**
- El enlace "Roles y Permisos" ahora aparece en el sidebar para usuarios super-admin
- La autorización funciona correctamente

---

## Fase 8: Testing

### Prompt 9: Inicio de Tests - Paso 7.1

**Usuario:**
> Lo he probado en el navegador y no he encontrado errores. Podemos pasar a la Fase 7. Empecemos con el paso 7.1

**Respuesta:**
Se implementó el Paso 7.1 - Tests de Form Requests:

**StoreRoleRequestTest:**
- ✅ 15 tests cubriendo todas las reglas de validación
- ✅ Tests de campos requeridos, tipos, longitud, unicidad, lista permitida
- ✅ Tests de permisos (array, nullable, items string, existencia)
- ✅ Tests de mensajes personalizados

**UpdateRoleRequestTest:**
- ✅ 14 tests cubriendo todas las reglas de validación
- ✅ Tests similares a StoreRoleRequest más protección de roles del sistema
- ✅ Test específico: "prevents changing name of system roles"
- ✅ Test específico: "allows changing name of non-system roles"

**Correcciones realizadas:**
- Ajuste de `beforeEach()` para usar `firstOrCreate` en lugar de `create` para evitar conflictos
- Ajuste de tests para manejar roles del sistema existentes
- Simulación correcta de route model binding en UpdateRoleRequest

**Archivos creados:**
- `tests/Feature/Http/Requests/StoreRoleRequestTest.php` - 15 tests
- `tests/Feature/Http/Requests/UpdateRoleRequestTest.php` - 14 tests

**Resultado:**
- 29 tests pasando (62 assertions)

---

### Prompt 10: Continuar con Tests de Policy - Paso 7.2

**Usuario:**
> Pasemos a la fase 7.2

**Respuesta:**
Se implementó el Paso 7.2 - Tests de Policy:

**RolePolicyTest:**
- ✅ 11 tests cubriendo todos los métodos de autorización
- ✅ Tests de acceso super-admin (todos los métodos)
- ✅ Tests de denegación para admin, editor, viewer y usuarios sin rol
- ✅ Tests de protección de roles del sistema
- ✅ Tests de protección de roles con usuarios asignados

**Casos de prueba:**
- Super-admin puede realizar todas las acciones
- Super-admin no puede eliminar roles del sistema
- Super-admin no puede eliminar roles con usuarios asignados
- Otros roles no pueden gestionar roles en absoluto

**Archivos creados:**
- `tests/Feature/Policies/RolePolicyTest.php` - 11 tests

**Resultado:**
- 11 tests pasando

---

### Prompt 11: Inicio de Tests de Componentes Livewire - Paso 7.3 (Index)

**Usuario:**
> Empecemos la fase 7.3 desarrollando los tests para Index

**Respuesta:**
Se implementó el Paso 7.3 - Tests de Componente Index:

**IndexTest:**
- ✅ 30 tests cubriendo todas las funcionalidades del componente
- ✅ Tests de autorización (5 tests)
- ✅ Tests de listado (2 tests)
- ✅ Tests de búsqueda (2 tests)
- ✅ Tests de ordenamiento (3 tests)
- ✅ Tests de paginación (2 tests)
- ✅ Tests de acciones (5 tests)
- ✅ Tests de permisos (5 tests)
- ✅ Tests de filtros (2 tests)
- ✅ Tests de métodos helper (4 tests)

**Correcciones realizadas:**
- Corrección de acceso a computed properties usando `$component->get('roles')` en lugar de `$component->call('roles')`
- Corrección de acceso a métodos helper usando `$component->instance()->methodName()`
- Ajuste de tests de ordenamiento para considerar roles del sistema y custom
- Corrección de aserciones para botones de acción

**Archivos creados:**
- `tests/Feature/Livewire/Admin/Roles/IndexTest.php` - 30 tests (60 assertions)

**Resultado:**
- 30 tests pasando (60 assertions)

---

### Prompt 12: Continuar con Tests de Create

**Usuario:**
> Pasemos ahora a los tests de Create

**Respuesta:**
Se implementó el Paso 7.3 - Tests de Componente Create:

**CreateTest:**
- ✅ 27 tests cubriendo todas las funcionalidades del componente
- ✅ Tests de autorización (5 tests)
- ✅ Tests de creación exitosa (6 tests)
- ✅ Tests de validación (8 tests)
- ✅ Tests de selección de permisos (5 tests)
- ✅ Tests de métodos helper (3 tests)

**Correcciones realizadas:**
- Corrección de conflicto de nombres en vista: cambio de `$permissions` a `$this->permissions` en resumen
- Ajuste de test de validación de string (Livewire convierte automáticamente a string)
- Ajuste de test de validación de array de permisos

**Archivos creados:**
- `tests/Feature/Livewire/Admin/Roles/CreateTest.php` - 27 tests (62 assertions)

**Archivos modificados:**
- `resources/views/livewire/admin/roles/create.blade.php` - Corrección de conflicto de nombres

**Resultado:**
- 27 tests pasando (62 assertions)

---

### Prompt 13: Continuar con Tests de Edit y Show

**Usuario:**
> Si, por favor, continúa con los tests que faltan

**Respuesta:**
Se implementaron los tests restantes:

**EditTest:**
- ✅ 30 tests cubriendo todas las funcionalidades del componente
- ✅ Tests de autorización (5 tests)
- ✅ Tests de carga de datos (3 tests)
- ✅ Tests de actualización exitosa (7 tests)
- ✅ Tests de validación (6 tests)
- ✅ Tests de selección de permisos (4 tests)
- ✅ Tests de métodos helper (5 tests)

**ShowTest:**
- ✅ 26 tests cubriendo todas las funcionalidades del componente
- ✅ Tests de autorización (5 tests)
- ✅ Tests de visualización (6 tests)
- ✅ Tests de acciones (7 tests)
- ✅ Tests de métodos helper (6 tests)
- ✅ Tests de visualización de permisos (2 tests)

**Correcciones realizadas:**
- Ajuste de test de actualización de nombre de rol (solo se permiten nombres de roles del sistema)
- Ajuste de tests de validación para trabajar con validación personalizada del componente Edit
- Ajuste de test de eliminación de rol con usuarios (verificar comportamiento real)

**Archivos creados:**
- `tests/Feature/Livewire/Admin/Roles/EditTest.php` - 30 tests (62 assertions)
- `tests/Feature/Livewire/Admin/Roles/ShowTest.php` - 26 tests (65 assertions)

**Resultado:**
- 56 tests pasando (127 assertions)
- **Total Fase 7.3: 113 tests pasando (249 assertions)**

---

## Fase 9: Documentación

### Prompt 14: Generación de Documentación

**Usuario:**
> Perfecto, ya hay 2040 tests pasando sin problemas. Lo último que nos queda son las mismas dos tareas finales de todos los chats. Primero tenemos que generar toda la documentación técnica de lo que hemos hecho en este chat y guardarla en la carpeta docs, donde tienes un archivo que actúa como índice (Paso 8.1). Luego tienes que generar el archivo paso27 en la carpeta docs/pasos que contenga todos los prompts de este chat y un resumen de las respuestas de cada uno de ellos.

**Respuesta:**
Se generó la documentación completa:

**Documentación Técnica:**
- ✅ Creación de `docs/admin-roles-crud.md` con documentación completa del CRUD
- ✅ Incluye descripción general, características, componentes Livewire, Form Requests, Policy, rutas, navegación, estructura de permisos, roles del sistema, caché, tests, consideraciones técnicas y archivos creados/modificados

**Documentación de Prompts:**
- ✅ Creación de `docs/pasos/paso27.md` con todos los prompts y respuestas del chat
- ✅ Organizado por fases con índice
- ✅ Incluye resumen de cada prompt y respuesta con detalles de implementación

**Archivos creados:**
- `docs/admin-roles-crud.md` - Documentación técnica completa
- `docs/pasos/paso27.md` - Historial completo de prompts y respuestas

---

## Resumen Final

### Funcionalidades Implementadas

✅ **CRUD Completo de Roles:**
- Crear, leer, actualizar y eliminar roles
- Validación de nombres según constantes del sistema
- Protección de roles del sistema

✅ **Gestión de Permisos:**
- Asignación y modificación de permisos agrupados por módulo
- Selección masiva de permisos por módulo
- Visualización de permisos asignados

✅ **Visualización de Usuarios:**
- Lista paginada de usuarios con cada rol
- Contadores de usuarios y permisos

✅ **Autorización:**
- Solo super-admin puede gestionar roles
- Policy completa con protección de roles del sistema

✅ **Interfaz Moderna:**
- Componentes Flux UI
- Diseño responsive
- Búsqueda, ordenamiento y paginación

✅ **Tests Completos:**
- 153 tests pasando (249 assertions)
- Cobertura completa de Form Requests, Policy y componentes Livewire

### Archivos Creados

**Componentes Livewire (4):**
- `app/Livewire/Admin/Roles/Index.php`
- `app/Livewire/Admin/Roles/Create.php`
- `app/Livewire/Admin/Roles/Edit.php`
- `app/Livewire/Admin/Roles/Show.php`

**Vistas (4):**
- `resources/views/livewire/admin/roles/index.blade.php`
- `resources/views/livewire/admin/roles/create.blade.php`
- `resources/views/livewire/admin/roles/edit.blade.php`
- `resources/views/livewire/admin/roles/show.blade.php`

**Form Requests (2):**
- `app/Http/Requests/StoreRoleRequest.php`
- `app/Http/Requests/UpdateRoleRequest.php`

**Policies (1):**
- `app/Policies/RolePolicy.php`

**Tests (8):**
- `tests/Feature/Http/Requests/StoreRoleRequestTest.php`
- `tests/Feature/Http/Requests/UpdateRoleRequestTest.php`
- `tests/Feature/Policies/RolePolicyTest.php`
- `tests/Feature/Livewire/Admin/Roles/IndexTest.php`
- `tests/Feature/Livewire/Admin/Roles/CreateTest.php`
- `tests/Feature/Livewire/Admin/Roles/EditTest.php`
- `tests/Feature/Livewire/Admin/Roles/ShowTest.php`

**Documentación (2):**
- `docs/admin-roles-crud.md`
- `docs/pasos/paso27.md`

### Archivos Modificados

- `routes/web.php` - Añadidas rutas de roles
- `resources/views/components/layouts/app/sidebar.blade.php` - Añadido enlace a roles
- `lang/es/common.php` - Añadida traducción
- `lang/en/common.php` - Añadida traducción
- `app/Providers/AppServiceProvider.php` - Registro manual de RolePolicy

### Estadísticas Finales

- **Tests Totales**: 153 tests pasando (249 assertions)
- **Tests del Sistema**: 2040 tests pasando
- **Cobertura**: Completa para Form Requests, Policy y componentes Livewire
- **Estado**: ✅ Completado y funcional

---

## Lecciones Aprendidas

1. **Registro Manual de Policies**: Los modelos de paquetes externos (como Spatie Permission) no siguen la convención de auto-descubrimiento de Laravel, requiriendo registro manual en `AppServiceProvider`.

2. **Método `before()` en Policies**: Para que el método `before()` funcione correctamente, los otros métodos deben devolver `null` en lugar de `false` cuando se quiere que `before()` tenga efecto.

3. **Validación de Nombres de Roles**: Restringir los nombres de roles a constantes del sistema asegura consistencia y facilita la traducción y visualización.

4. **Caché de Permisos**: Es crucial limpiar la caché de Spatie Permission después de cualquier operación CRUD en roles o permisos para reflejar cambios inmediatamente.

5. **Protección de Roles del Sistema**: Implementar validaciones tanto en Form Requests como en Policies para proteger roles del sistema de modificaciones no deseadas.

6. **Tests de Livewire**: Al testear componentes Livewire, usar `$component->get('property')` para computed properties y `$component->instance()->method()` para métodos helper.

---

## Referencias

- [Plan Detallado](paso-3.5.11-plan.md) - Plan completo del desarrollo
- [Documentación Técnica](../admin-roles-crud.md) - Documentación técnica completa
- [Sistema de Roles y Permisos](../roles-and-permissions.md) - Documentación del sistema de roles y permisos

