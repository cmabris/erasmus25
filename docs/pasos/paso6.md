# Paso 6: Policies y Autorización (Paso 3.3 de la Planificación)

Este documento contiene todos los prompts utilizados y resultados obtenidos en el desarrollo del Paso 6, que corresponde a la creación de Policies para autorización de la aplicación "Erasmus+ Centro (Murcia)".

---

## Prompt 1: Desarrollo del Paso 3.3.1 - Creación de Policies Principales

**Prompt:**
> "En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que hace de índice y otro llamado @planificacion_pasos.md que contiene las tareas a desarrollar en orden. Ahora, siguiendo esa recomendación, vamos a desarrollar el paso 3.3 que corresponde a la creación de las policies. Empecemos con el desarrollo del apartado 3.3.1 y cuando acabes, lo reviso para dar el visto bueno, antes de pasar a la siguiente tarea"

**Resultado:**
- Se revisó la planificación y la documentación de roles y permisos
- Se crearon 11 Policies usando `php artisan make:policy`:
  - `ProgramPolicy` - Autorización para programas (permisos programs.*)
  - `AcademicYearPolicy` - Autorización para años académicos (basado en roles)
  - `CallPolicy` - Autorización para convocatorias (permisos calls.* + publish)
  - `CallPhasePolicy` - Autorización para fases (permisos calls.*)
  - `ResolutionPolicy` - Autorización para resoluciones (permisos calls.* + publish)
  - `NewsPostPolicy` - Autorización para noticias (permisos news.* + publish)
  - `NewsTagPolicy` - Autorización para etiquetas (permisos news.*)
  - `DocumentPolicy` - Autorización para documentos (permisos documents.*)
  - `DocumentCategoryPolicy` - Autorización para categorías (permisos documents.*)
  - `ErasmusEventPolicy` - Autorización para eventos (permisos events.*)
  - `UserPolicy` - Autorización para usuarios (permisos users.* + lógica especial)

**Archivos generados:**
- `app/Policies/ProgramPolicy.php`
- `app/Policies/AcademicYearPolicy.php`
- `app/Policies/CallPolicy.php`
- `app/Policies/CallPhasePolicy.php`
- `app/Policies/ResolutionPolicy.php`
- `app/Policies/NewsPostPolicy.php`
- `app/Policies/NewsTagPolicy.php`
- `app/Policies/DocumentPolicy.php`
- `app/Policies/DocumentCategoryPolicy.php`
- `app/Policies/ErasmusEventPolicy.php`
- `app/Policies/UserPolicy.php`

---

## Prompt 2: Verificación del Paso 3.3.2

**Prompt:**
> "Pasemos al punto 3.3.2 si todavía no está hecho"

**Resultado:**
- Se verificó que el paso 3.3.2 (Métodos de Policy a Implementar) ya estaba completado como parte del 3.3.1
- Cada policy incluye todos los métodos estándar:
  - `before()` - Pre-autorización para super-admin
  - `viewAny()` - Ver listado
  - `view()` - Ver detalle
  - `create()` - Crear nuevo
  - `update()` - Actualizar existente
  - `delete()` - Eliminar
  - `restore()` - Restaurar
  - `forceDelete()` - Eliminación permanente
- Métodos especiales implementados:
  - `publish()` en CallPolicy, ResolutionPolicy, NewsPostPolicy
  - `assignRoles()` en UserPolicy

---

## Prompt 3: Decisión de Postergar Tests

**Prompt:**
> "Vamos a dejar el punto 3.3.3 para mañana, que hoy ya no da tiempo."

**Resultado:**
- Se acordó continuar con los tests de policies al día siguiente
- Se generó un resumen del trabajo completado

---

## Prompt 4: Desarrollo del Paso 3.3.3 - Tests de Policies

**Prompt:**
> "Buenos días, revisa este chat para ponerte al día y vamos a continuar desarrollando la aplicación en el paso 3.3.3 creando los tests de Policies."

**Resultado:**
- Se revisó la estructura de tests existente para mantener consistencia
- Se creó la carpeta `tests/Feature/Policies/`
- Se crearon 11 archivos de test con 80 tests totales:
  - `ProgramPolicyTest.php` (9 tests)
  - `AcademicYearPolicyTest.php` (5 tests)
  - `CallPolicyTest.php` (7 tests)
  - `CallPhasePolicyTest.php` (5 tests)
  - `ResolutionPolicyTest.php` (6 tests)
  - `NewsPostPolicyTest.php` (7 tests)
  - `NewsTagPolicyTest.php` (5 tests)
  - `DocumentPolicyTest.php` (7 tests)
  - `DocumentCategoryPolicyTest.php` (5 tests)
  - `ErasmusEventPolicyTest.php` (7 tests)
  - `UserPolicyTest.php` (17 tests)

**Estructura de tests:**
Cada archivo de test verifica:
- Acceso de super-admin (acceso total via método `before()`)
- Acceso de admin (permisos completos del módulo)
- Acceso de editor (ver, crear, editar - sin delete ni publish)
- Acceso de viewer (solo ver)
- Acceso sin rol (denegado)
- Permisos directos (verificación granular)

**Archivos generados:**
- `tests/Feature/Policies/ProgramPolicyTest.php`
- `tests/Feature/Policies/AcademicYearPolicyTest.php`
- `tests/Feature/Policies/CallPolicyTest.php`
- `tests/Feature/Policies/CallPhasePolicyTest.php`
- `tests/Feature/Policies/ResolutionPolicyTest.php`
- `tests/Feature/Policies/NewsPostPolicyTest.php`
- `tests/Feature/Policies/NewsTagPolicyTest.php`
- `tests/Feature/Policies/DocumentPolicyTest.php`
- `tests/Feature/Policies/DocumentCategoryPolicyTest.php`
- `tests/Feature/Policies/ErasmusEventPolicyTest.php`
- `tests/Feature/Policies/UserPolicyTest.php`

**Corrección aplicada:**
- Se modificó `ResolutionPolicyTest.php` para evitar conflictos de unicidad en los factories, creando Program y AcademicYear con valores únicos en el `beforeEach()`

**Resultado de tests:**
```
Tests: 290 passed (926 assertions)
```
(80 nuevos tests de policies + 210 tests anteriores)

---

## Resumen del Paso 6

### Policies Creadas (11 total)

| Policy | Modelo | Permisos Base | Métodos Especiales |
|--------|--------|---------------|-------------------|
| ProgramPolicy | Program | programs.* | - |
| AcademicYearPolicy | AcademicYear | Basado en roles | - |
| CallPolicy | Call | calls.* | publish() |
| CallPhasePolicy | CallPhase | calls.* | - |
| ResolutionPolicy | Resolution | calls.* | publish() |
| NewsPostPolicy | NewsPost | news.* | publish() |
| NewsTagPolicy | NewsTag | news.* | - |
| DocumentPolicy | Document | documents.* | - |
| DocumentCategoryPolicy | DocumentCategory | documents.* | - |
| ErasmusEventPolicy | ErasmusEvent | events.* | - |
| UserPolicy | User | users.* | assignRoles() |

### Tests Creados (80 total)

| Test File | Tests | Assertions |
|-----------|-------|------------|
| ProgramPolicyTest.php | 9 | 45 |
| AcademicYearPolicyTest.php | 5 | 35 |
| CallPolicyTest.php | 7 | 48 |
| CallPhasePolicyTest.php | 5 | 35 |
| ResolutionPolicyTest.php | 6 | 42 |
| NewsPostPolicyTest.php | 7 | 48 |
| NewsTagPolicyTest.php | 5 | 35 |
| DocumentPolicyTest.php | 7 | 47 |
| DocumentCategoryPolicyTest.php | 5 | 35 |
| ErasmusEventPolicyTest.php | 7 | 42 |
| UserPolicyTest.php | 17 | 35 |
| **Total** | **80** | **447** |

### Características Implementadas

#### Método `before()` - Pre-autorización
- El rol `super-admin` tiene acceso total automático a todas las acciones
- Devuelve `true` para super-admin, `null` para otros (continúa la autorización normal)

#### Métodos Estándar
- `viewAny()` - Ver listados (requiere permiso `.view`)
- `view()` - Ver detalle (requiere permiso `.view`)
- `create()` - Crear nuevo (requiere permiso `.create`)
- `update()` - Actualizar existente (requiere permiso `.edit`)
- `delete()` - Eliminar (requiere permiso `.delete`)
- `restore()` - Restaurar (requiere permiso `.delete`)
- `forceDelete()` - Eliminación permanente (requiere permiso `.delete`)

#### Métodos Especiales
- `publish()` - En Call, Resolution y NewsPost (requiere permiso `.publish`)
- `assignRoles()` - En UserPolicy (requiere permiso `users.edit`, no aplicable a uno mismo)

#### Lógica Especial en UserPolicy
- Un usuario siempre puede ver y editar su propio perfil
- Un usuario NO puede eliminarse a sí mismo
- Un usuario NO puede modificar sus propios roles

#### AcademicYearPolicy - Basada en Roles
- No tiene permisos específicos definidos
- Cualquier usuario autenticado puede ver años académicos
- Solo admin y super-admin pueden crear, editar y eliminar

### Auto-Discovery de Policies
Laravel detecta automáticamente las policies gracias a la convención de nombres:
- `Program` → `ProgramPolicy`
- `User` → `UserPolicy`
- etc.

### Cobertura de Tests por Rol

| Rol | Permisos Verificados |
|-----|---------------------|
| super-admin | Acceso total (via before()) |
| admin | Todos los permisos del módulo + publish |
| editor | view, create, edit (sin delete ni publish) |
| viewer | Solo view |
| Sin rol | Acceso denegado (excepto perfil propio en UserPolicy) |

### Siguiente Paso

Paso 3.4 de la planificación: Área Pública (Front-office)

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: ✅ Completado
