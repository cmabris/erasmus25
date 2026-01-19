# CRUD de Auditoría y Logs en Panel de Administración

Documentación técnica del sistema completo de visualización de logs de auditoría en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El sistema de Auditoría y Logs permite a los administradores visualizar y consultar todos los registros de actividad del sistema. Utiliza **Spatie Laravel Activitylog v4** para el logging automático y manual de eventos, proporcionando un historial completo y detallado de todas las acciones realizadas en la aplicación.

## Características Principales

- ✅ **Visualización Completa**: Listado y detalle de todos los logs de actividad
- ✅ **Logging Automático**: Registro automático de eventos (created, updated, deleted) en modelos con trait `LogsActivity`
- ✅ **Logging Manual**: Registro de acciones especiales (publish, unpublish, restore, etc.) mediante helper `activity()`
- ✅ **Filtros Avanzados**: Búsqueda, filtro por modelo, usuario, acción, log name y rango de fechas
- ✅ **Visualización de Cambios**: Tabla comparativa mostrando valores anteriores y nuevos
- ✅ **Exportación**: Exportación de logs a Excel/CSV con filtros aplicados
- ✅ **Autorización**: Solo admin y super-admin pueden ver logs
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 85 tests pasando (185 assertions)

---

## Tecnología Utilizada

### Spatie Laravel Activitylog v4

**Librería**: `spatie/laravel-activitylog` v4  
**Documentación**: https://spatie.be/docs/laravel-activitylog/v4/introduction

**Características principales:**
- Logging automático mediante trait `LogsActivity`
- Logging manual mediante helper `activity()`
- Relaciones polimórficas (`causer` y `subject`)
- Propiedades personalizadas en JSON
- Múltiples logs por nombre
- Batch logging

### Estructura de Datos

**Tabla**: `activity_log`

**Campos principales:**
- `id` - Identificador único
- `log_name` - Nombre del log (para múltiples logs)
- `description` - Descripción de la acción (ej: "created", "updated", "published")
- `subject_id` + `subject_type` - Modelo sobre el que se realizó la actividad (polimórfico)
- `causer_id` + `causer_type` - Usuario/entidad que causó la actividad (polimórfico)
- `properties` - JSON con cambios y datos adicionales:
  - `attributes` - Valores nuevos
  - `old` - Valores anteriores
  - Propiedades personalizadas (IP, user agent, etc.)
- `created_at`, `updated_at` - Timestamps

**Relaciones:**
- `causer()` - MorphTo (polimórfico, puede ser User u otro modelo)
- `subject()` - MorphTo (polimórfico, el modelo afectado)

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\AuditLogs\Index`
- **Vista**: `resources/views/livewire/admin/audit-logs/index.blade.php`
- **Ruta**: `/admin/auditoria` (nombre: `admin.audit-logs.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'buscar')]
public string $search = '';

#[Url(as: 'modelo')]
public string $filterModel = '';

#[Url(as: 'usuario')]
public string $filterCauser = '';

#[Url(as: 'accion')]
public string $filterDescription = '';

#[Url(as: 'log')]
public string $filterLogName = '';

#[Url(as: 'desde')]
public ?string $filterDateFrom = null;

#[Url(as: 'hasta')]
public ?string $filterDateTo = null;

#[Url(as: 'ordenar')]
public string $sortField = 'created_at';

#[Url(as: 'direccion')]
public string $sortDirection = 'desc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;
```

**Métodos Principales:**

- `activities()` - Computed property con paginación, filtros y ordenación
  - Eager loading: `with(['causer', 'subject'])`
  - Filtro por búsqueda (description, subject_type)
  - Filtro por modelo (subject_type)
  - Filtro por usuario/causer
  - Filtro por descripción/acción
  - Filtro por log name
  - Filtro por rango de fechas
  - Ordenación por campo y dirección
  - Paginación configurable
- `sortBy($field)` - Ordenación por campo
- `resetFilters()` - Resetear todos los filtros
- `export()` - Exportar logs a Excel con filtros aplicados
- `getModelDisplayName($modelType)` - Obtener nombre legible del modelo
- `getDescriptionDisplayName($description)` - Obtener nombre legible de la acción
- `getDescriptionBadgeVariant($description)` - Obtener variante de badge según acción
- `getSubjectUrl($modelType, $modelId)` - Generar URL al subject si existe ruta
- `getSubjectTitle($subject)` - Obtener título del subject
- `formatChangesSummary($properties)` - Formatear resumen de cambios

**Características:**
- Filtros persistentes en URL
- Búsqueda en tiempo real
- Ordenación por múltiples campos
- Paginación configurable
- Exportación a Excel con filtros aplicados

---

### 2. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\AuditLogs\Show`
- **Vista**: `resources/views/livewire/admin/audit-logs/show.blade.php`
- **Ruta**: `/admin/auditoria/{activity}` (nombre: `admin.audit-logs.show`)

**Propiedades Públicas:**

```php
public Activity $activity;
```

**Métodos Principales:**

- `getModelDisplayName($modelType)` - Obtener nombre legible del modelo
- `getDescriptionDisplayName($description)` - Obtener nombre legible de la acción
- `getDescriptionBadgeVariant($description)` - Obtener variante de badge según acción
- `getSubjectUrl($modelType, $modelId)` - Generar URL al subject si existe ruta
- `getSubjectTitle($subject)` - Obtener título del subject
- `getChangesFromProperties($properties)` - Extraer cambios de propiedades (old vs attributes)
- `getIpAddress($properties)` - Extraer IP address de propiedades
- `getUserAgent($properties)` - Extraer user agent de propiedades
- `getCustomProperties($properties)` - Obtener propiedades personalizadas (excluyendo old/attributes)
- `hasChanges($properties)` - Verificar si hay cambios registrados

**Características:**
- Visualización completa de información del log
- Tabla comparativa de cambios (old vs new)
- Propiedades personalizadas expandibles
- Información técnica (IP, user agent)
- Enlaces a modelos relacionados

---

## Exportación de Datos

### AuditLogsExport

**Ubicación**: `app/Exports/AuditLogsExport.php`

**Características:**
- Implementa concerns de Laravel Excel:
  - `FromCollection` - Para obtener datos
  - `WithHeadings` - Para encabezados
  - `WithMapping` - Para formatear filas
  - `WithTitle` - Para nombre de hoja
  - `WithStyles` - Para estilos (headers en negrita)
- Aplica los mismos filtros que el componente Index
- Formatea datos para Excel:
  - Fechas en formato legible
  - Nombres de modelos y acciones traducidos
  - Resumen de cambios formateado
  - Información de usuario completa

**Uso:**
```php
Excel::download(new AuditLogsExport($filters), 'audit-logs-2026-01-13.xlsx');
```

---

## Configuración de Logging Automático

### Modelos con Logging Automático

Los siguientes modelos tienen logging automático configurado mediante el trait `LogsActivity`:

1. **Program** - Logs: `code`, `name`, `description`, `is_active`, `order`
2. **Call** - Logs: `program_id`, `academic_year_id`, `title`, `type`, `modality`, `number_of_places`, `destinations`, `estimated_start_date`, `estimated_end_date`, `status`, `published_at`, `closed_at`
3. **NewsPost** - Logs: campos principales
4. **Document** - Logs: `category_id`, `program_id`, `academic_year_id`, `title`, `description`, `document_type`, `version`, `is_active`
5. **ErasmusEvent** - Logs: campos principales
6. **AcademicYear** - Logs: `year`, `start_date`, `end_date`, `is_current`

**Ejemplo de configuración:**
```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Program extends Model
{
    use LogsActivity;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'name', 'description', 'is_active', 'order'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at', 'slug']);
    }
}
```

**Opciones disponibles:**
- `logOnly(['field1', 'field2'])` - Solo loguear campos específicos
- `logAll()` - Loguear todos los campos
- `logOnlyDirty()` - Solo loguear campos que cambiaron
- `logExcept(['field1'])` - Excluir campos
- `dontLogIfAttributesChangedOnly(['updated_at'])` - No loguear si solo cambiaron estos campos

---

## Logging Manual

### Acciones con Logging Manual

Las siguientes acciones tienen logging manual implementado:

1. **Call::publish()** - Publicación de convocatoria
2. **Call::restore()** - Restauración de convocatoria
3. **NewsPost::publish()** - Publicación de noticia
4. **NewsPost::unpublish()** - Despublicación de noticia
5. **NewsPost::restore()** - Restauración de noticia
6. **Resolution::publish()** - Publicación de resolución
7. **Resolution::unpublish()** - Despublicación de resolución
8. **Resolution::restore()** - Restauración de resolución
9. **User::assignRoles()** - Asignación de roles a usuario

**Ejemplo de logging manual:**
```php
activity()
    ->performedOn($call)
    ->causedBy(auth()->user())
    ->withProperties([
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'old_status' => $oldStatus,
        'new_status' => 'abierta',
        'published_at' => $call->published_at?->toIso8601String(),
    ])
    ->log('published');
```

**Propiedades comunes en logging manual:**
- `ip_address` - IP del usuario
- `user_agent` - User agent del navegador
- `old_status` / `new_status` - Estados anteriores y nuevos
- `old_roles` / `new_roles` - Roles anteriores y nuevos (para asignación de roles)
- Cualquier otra propiedad contextual relevante

---

## Autorización

### ActivityPolicy

**Ubicación**: `app/Policies/ActivityPolicy.php`

**Métodos:**
- `viewAny()` - Ver listado de logs (solo admin y super-admin)
- `view()` - Ver detalle de un log (solo admin y super-admin)

**Permisos requeridos:**
- No hay permisos específicos, se controla por rol:
  - **Super Admin**: Acceso total
  - **Admin**: Acceso total
  - **Editor**: Sin acceso
  - **Viewer**: Sin acceso

**Registro:**
- Registrado automáticamente mediante convención de nombres de Laravel

---

## Filtros Disponibles

### 1. Búsqueda General
- Busca en `description` y `subject_type`
- Búsqueda case-insensitive
- Búsqueda en tiempo real con debounce

### 2. Filtro por Modelo
- Filtra por `subject_type` (Program, Call, NewsPost, Document, etc.)
- Lista desplegable con modelos disponibles
- Caché de modelos para mejor rendimiento

### 3. Filtro por Usuario/Causer
- Filtra por usuario que realizó la acción
- Búsqueda por nombre o email
- Muestra avatar y nombre del usuario

### 4. Filtro por Acción/Descripción
- Filtra por tipo de acción (created, updated, deleted, published, etc.)
- Lista desplegable con acciones disponibles
- Caché de descripciones para mejor rendimiento

### 5. Filtro por Log Name
- Filtra por nombre de log (si se usan múltiples logs)
- Útil para separar logs de diferentes contextos

### 6. Filtro por Rango de Fechas
- Filtro desde fecha (inclusive)
- Filtro hasta fecha (inclusive)
- Validación de fechas
- Formato: DD/MM/YYYY

---

## Visualización de Cambios

### Formato de Cambios

Los cambios se extraen de `properties.old` y `properties.attributes`:

**Estructura:**
```json
{
  "old": {
    "name": "Programa Antiguo",
    "is_active": false
  },
  "attributes": {
    "name": "Programa Nuevo",
    "is_active": true
  }
}
```

**Visualización:**
- Tabla comparativa mostrando:
  - Campo modificado
  - Valor anterior (desde `old`)
  - Valor nuevo (desde `attributes`)
  - Diferencia destacada visualmente
- Formateo especial para:
  - Valores booleanos (Sí/No)
  - Valores null (vacío)
  - Arrays y objetos JSON (formateados)
  - Fechas (formato legible)

**Mensaje cuando no hay cambios:**
- Se muestra "No se registraron cambios en este log" cuando no hay diferencias

---

## Propiedades Personalizadas

### Propiedades Comunes

Las siguientes propiedades personalizadas se registran automáticamente en logging manual:

1. **IP Address** (`ip_address`)
   - IP del usuario que realizó la acción
   - Extraída de `request()->ip()`

2. **User Agent** (`user_agent`)
   - User agent del navegador
   - Extraída de `request()->userAgent()`

3. **Estados** (`old_status`, `new_status`)
   - Estados anteriores y nuevos en cambios de estado
   - Usado en publish/unpublish

4. **Roles** (`old_roles`, `new_roles`)
   - Roles anteriores y nuevos en asignación de roles
   - Arrays de nombres de roles

5. **Fechas** (`published_at`, etc.)
   - Fechas relevantes en formato ISO8601
   - Usado para tracking de publicaciones

### Visualización

- Las propiedades personalizadas se muestran en una sección expandible
- Se excluyen automáticamente `old` y `attributes` (ya mostrados en cambios)
- Formateo inteligente según tipo de dato
- JSON expandible para vista técnica completa

---

## Optimizaciones de Rendimiento

### Índices de Base de Datos

La tabla `activity_log` tiene los siguientes índices optimizados:

1. **Índice compuesto para subject** (`subject_type`, `subject_id`)
   - Optimiza búsquedas por modelo afectado
   - Nombre: `activity_log_subject_index`

2. **Índice compuesto para causer** (`causer_type`, `causer_id`)
   - Optimiza búsquedas por usuario
   - Nombre: `activity_log_causer_index`

3. **Índice en created_at**
   - Optimiza ordenación y filtros de fecha
   - Nombre: `activity_log_created_at_index`

4. **Índice en description**
   - Optimiza búsquedas por tipo de acción
   - Nombre: `activity_log_description_index`

5. **Índice en log_name**
   - Optimiza filtros por nombre de log
   - Incluido por defecto por Spatie

### Eager Loading

Todas las consultas utilizan eager loading para evitar N+1 queries:

```php
Activity::query()
    ->with(['causer', 'subject'])
    ->where(...)
    ->get();
```

### Caché

Se utiliza caché para datos frecuentemente accedidos:

- Lista de modelos disponibles para filtro
- Lista de descripciones disponibles para filtro
- Lista de usuarios para filtro de causer

**TTL**: 5 minutos (300 segundos)

### Paginación

- Paginación configurable (15, 25, 50, 100 por página)
- Persistencia en URL
- Optimización de consultas con límites

---

## Tests

### Cobertura de Tests

**Total**: 143 tests pasando (~98.7% cobertura de líneas)

#### 1. ActivityPolicy Tests (10 tests)
- `tests/Feature/Policies/ActivityPolicyTest.php`
- Verificación de autorización por rol:
  - Super Admin: acceso total
  - Admin: acceso total
  - Editor: sin acceso
  - Viewer: sin acceso
  - Usuario sin rol: sin acceso

#### 2. Index Component Tests (39 tests)
- `tests/Feature/Livewire/Admin/AuditLogs/IndexTest.php`
- Cobertura: 98.88% (176/178 líneas)
  - Autorización (5 tests)
  - Listado (4 tests)
  - Filtros (6 tests)
  - Ordenación (2 tests)
  - Exportación (3 tests)
  - **Edge Cases (19 tests) - *añadidos en paso 3.8.4***:
    - `sortBy` - ordenar y toggle dirección
    - `resetFilters` - resetear todos los filtros
    - `getModelDisplayName` - todos los modelos mapeados, null, unknown
    - `getDescriptionDisplayName` - todos los tipos
    - `getDescriptionBadgeVariant` - success, info, danger, neutral
    - `getSubjectUrl` - null params, unknown model, URL válida
    - `getSubjectTitle` - null, title, name, fallback
    - `formatChangesSummary` - null, sin cambios, con cambios, más de 3 cambios

#### 3. Show Component Tests (66 tests)
- `tests/Feature/Livewire/Admin/AuditLogs/ShowTest.php`
- Cobertura: 98.52% (133/135 líneas)
  - Autorización (5 tests)
  - Visualización (8 tests)
  - Cambios (5 tests)
  - Propiedades personalizadas (3 tests)
  - IP y User Agent (2 tests)
  - Métodos helper (4 tests)
  - **Edge Cases (39 tests) - *añadidos en paso 3.8.4***:
    - `getModelDisplayName` - todos los modelos mapeados, null, unknown
    - `getDescriptionDisplayName` - todas las descripciones
    - `getDescriptionBadgeVariant` - todos los variantes
    - `getSubjectUrl` - null, unknown, mapped models
    - `getSubjectTitle` - null, title, name, fallback
    - `formatValueForDisplay` - null, boolean, array, string largo/corto
    - `formatJsonForDisplay` - array, JSON válido/inválido
    - `parseUserAgent` - null, Chrome, Firefox, Mobile, Linux, Android
    - `hasChanges` - con y sin cambios
    - `getCustomProperties` - null, exclusión de props sistema
    - `getChangesFromProperties` - Collection input, exclusión unchanged
    - `getIpAddress/getUserAgent` - Collection input, alternative keys

**Nota:** Las líneas no cubiertas (~2%) son bloques try-catch defensivos en `getSubjectUrl()` que manejan excepciones inesperadas al generar rutas.

**Actualizado:** Enero 2026 (paso 3.8.4)

#### 4. Automatic Logging Tests (15 tests)
- `tests/Feature/ActivityLog/AutomaticLoggingTest.php`
- Cobertura:
  - Logging en 6 modelos (Program, Call, NewsPost, Document, ErasmusEvent, AcademicYear)
  - Verificación de campos logueados (3 tests)
  - Verificación de relaciones causer y subject (3 tests)

#### 5. Manual Logging Tests (13 tests)
- `tests/Feature/ActivityLog/ManualLoggingTest.php`
- Cobertura:
  - Acciones especiales (publish, unpublish, restore) para Call, NewsPost, Resolution (6 tests)
  - Asignación de roles (2 tests)
  - Propiedades personalizadas (IP, User Agent, contexto) (3 tests)

---

## Integración con Sistema Existente

### Componentes Actualizados

1. **Admin\Dashboard**
   - Actualizado para usar `Activity` en lugar de `AuditLog`
   - Carga actividades recientes desde `activity_log`
   - Muestra información formateada

2. **Admin\Users\Show**
   - Actualizado para usar `Activity` en lugar de `AuditLog`
   - Muestra actividades del usuario como causer
   - Estadísticas basadas en `Activity`

3. **Admin\Users\Index**
   - Actualizado para usar `Activity` en lugar de `AuditLog`
   - Contador de actividades basado en `Activity`

### Componente UI Reutilizable

**Componente**: `x-ui.audit-log-entry`

**Ubicación**: `resources/views/components/ui/audit-log-entry.blade.php`

**Características:**
- Soporta tanto `AuditLog` (legacy) como `Activity` (nuevo)
- Detección automática del tipo
- Formateo inteligente de cambios
- Visualización de información del modelo
- Enlaces a modelos relacionados

**Uso:**
```blade
<x-ui.audit-log-entry :log="$activity" />
```

---

## Eliminación del Sistema Legacy

### Tabla `audit_logs` Eliminada

**Migración**: `2026_01_13_153229_drop_audit_logs_table.php`

**Estado**: ✅ Ejecutada

**Archivos eliminados:**
- `app/Models/AuditLog.php` - Modelo obsoleto
- `database/factories/AuditLogFactory.php` - Factory obsoleta
- `tests/Feature/Models/AuditLogTest.php` - Tests obsoletos (6 tests)

**Código actualizado:**
- `app/Models/User.php` - Eliminada relación `auditLogs()`
- Todos los componentes actualizados para usar `Activity`

---

## Exportación de Datos

### Laravel Excel Integration

**Librería**: `maatwebsite/excel` v3.1  
**Documentación**: https://docs.laravel-excel.com/3.1/getting-started/

**Características:**
- Exportación a formato XLSX
- Aplicación de filtros del componente Index
- Formateo de datos para Excel:
  - Headers en negrita
  - Fechas formateadas
  - Nombres traducidos
  - Resumen de cambios formateado

**Columnas exportadas:**
1. ID
2. Fecha/Hora
3. Usuario
4. Email Usuario
5. Acción
6. Modelo
7. ID Registro
8. Registro
9. Log Name
10. Cambios

**Uso:**
```php
// Desde el componente Index
public function export()
{
    $this->authorize('viewAny', Activity::class);
    
    $filters = [
        'search' => $this->search,
        'filterModel' => $this->filterModel,
        // ... otros filtros
    ];
    
    $fileName = 'audit-logs-' . now()->format('Y-m-d-His') . '.xlsx';
    
    return Excel::download(new AuditLogsExport($filters), $fileName);
}
```

---

## Rutas

### Rutas de Auditoría

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/auditoria', \App\Livewire\Admin\AuditLogs\Index::class)
        ->name('audit-logs.index');
    
    Route::get('/auditoria/{activity}', \App\Livewire\Admin\AuditLogs\Show::class)
        ->name('audit-logs.show');
});
```

**Middleware:**
- `auth` - Requiere autenticación
- `verified` - Requiere email verificado
- Autorización mediante `ActivityPolicy`

---

## Navegación

### Integración en Sidebar

**Ubicación**: `resources/views/components/layouts/admin/sidebar.blade.php`

**Sección**: Sistema

**Enlace:**
```blade
@can('viewAny', \Spatie\Activitylog\Models\Activity::class)
    <x-ui.sidebar-link
        :href="route('admin.audit-logs.index')"
        :active="request()->routeIs('admin.audit-logs.*')"
        icon="document-text"
    >
        {{ __('common.nav.audit_logs') }}
    </x-ui.sidebar-link>
@endcan
```

---

## Internacionalización

### Traducciones

**Archivos de traducción:**
- `lang/es/common.php`
- `lang/en/common.php`

**Claves principales:**
- `common.nav.audit_logs` - "Auditoría y Logs" / "Audit Logs"
- `common.admin.audit_logs.*` - Traducciones específicas del módulo

**Modelos y acciones traducidos:**
- Nombres de modelos: Program → "Programa", Call → "Convocatoria", etc.
- Nombres de acciones: created → "Creado", updated → "Actualizado", etc.

---

## Consideraciones de Seguridad

1. **Autorización Estricta**
   - Solo admin y super-admin pueden ver logs
   - No hay permisos de edición o eliminación desde la interfaz
   - Los logs son inmutables

2. **Datos Sensibles**
   - Se excluyen campos sensibles del logging automático
   - No se registran passwords, tokens, etc.
   - Configuración por modelo mediante `logOnly()` o `logExcept()`

3. **Limpieza Automática**
   - Configurar retención de logs según políticas
   - Comando Artisan para limpieza periódica (opcional)

4. **IP y User Agent**
   - Se registran para auditoría de seguridad
   - Solo visible para administradores

---

## Mejoras de UX

### Características Implementadas

1. **Tooltips Informativos**
   - Información sobre filtros
   - Ayuda contextual

2. **Indicadores de Carga**
   - Loading states durante búsquedas
   - Skeleton loaders

3. **Mensajes Informativos**
   - Estado vacío cuando no hay logs
   - Mensajes de confirmación

4. **Exportación**
   - Botón de exportación visible
   - Feedback visual durante exportación

5. **Filtros Rápidos**
   - Filtros por período predefinidos (opcional)
   - Filtros guardados (opcional)

6. **Dark Mode**
   - Soporte completo para dark mode
   - Colores adaptativos

---

## Estructura de Archivos

```
app/
├── Exports/
│   └── AuditLogsExport.php
├── Livewire/
│   └── Admin/
│       └── AuditLogs/
│           ├── Index.php
│           └── Show.php
└── Policies/
    └── ActivityPolicy.php

database/
└── migrations/
    ├── 2026_01_13_152731_create_activity_log_table.php
    ├── 2026_01_13_152732_add_event_column_to_activity_log_table.php
    ├── 2026_01_13_152733_add_batch_uuid_column_to_activity_log_table.php
    ├── 2026_01_13_160601_add_indexes_to_activity_log_table.php
    └── 2026_01_13_153229_drop_audit_logs_table.php

resources/
└── views/
    └── livewire/
        └── admin/
            └── audit-logs/
                ├── index.blade.php
                └── show.blade.php

tests/
└── Feature/
    ├── ActivityLog/
    │   ├── AutomaticLoggingTest.php
    │   └── ManualLoggingTest.php
    ├── Livewire/
    │   └── Admin/
    │       └── AuditLogs/
    │           ├── IndexTest.php
    │           └── ShowTest.php
    └── Policies/
        └── ActivityPolicyTest.php
```

---

## Referencias

- [Plan Detallado](pasos/paso-3.5.14-plan.md) - Plan completo paso a paso
- [Resumen Ejecutivo](pasos/paso-3.5.14-resumen.md) - Resumen ejecutivo del desarrollo
- [Análisis de Eliminación](pasos/paso-3.5.14-audit-logs-eliminacion.md) - Análisis de eliminación de tabla legacy
- [Documentación Spatie Activitylog](https://spatie.be/docs/laravel-activitylog/v4/introduction)
- [Documentación Laravel Excel](https://docs.laravel-excel.com/3.1/getting-started/)

---

**Fecha de Creación**: Enero 2026  
**Última Actualización**: Enero 2026  
**Estado**: ✅ Completado - 85 tests pasando (185 assertions), 2277 tests totales pasando
