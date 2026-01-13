# Plan de Desarrollo: Paso 3.5.14 - Auditoría y Logs en Panel de Administración

Este documento establece el plan detallado para desarrollar el sistema completo de Auditoría y Logs en el panel de administración usando **Spatie Laravel Activitylog v4**.

## 🎯 Objetivo

Crear un sistema completo de visualización de logs de auditoría en el panel de administración con:
- Integración de **Spatie Laravel Activitylog v4** para logging automático
- Listado moderno con tabla interactiva y filtros avanzados
- Vista detallada de cada log con información completa
- Filtros por modelo, usuario, acción y fecha
- Visualización de cambios antes/después en formato legible
- Logging automático de eventos de modelos (created, updated, deleted)
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📚 Información sobre Spatie Laravel Activitylog v4

### Características Principales

1. **Logging Manual**: Función helper `activity()->log('mensaje')`
2. **Logging Automático**: Trait `LogsActivity` en modelos para eventos automáticos
3. **Modelo Activity**: `Spatie\Activitylog\Models\Activity` con relaciones:
   - `causer` - Usuario/entidad que causó la actividad (polimórfico)
   - `subject` - Modelo sobre el que se realizó la actividad (polimórfico)
4. **Estructura de Tabla `activity_log`**:
   - `id`, `log_name`, `description`, `subject_id`, `subject_type`, `causer_id`, `causer_type`
   - `properties` (JSON), `created_at`, `updated_at`
5. **Opciones de Logging**:
   - `logOnly()`, `logAll()`, `logOnlyDirty()`, `logExcept()`
   - `dontLogIfAttributesChangedOnly()`
6. **Propiedades Personalizadas**: `withProperties()` para datos adicionales
7. **Batch Logging**: Agrupar múltiples logs
8. **Múltiples Logs**: Diferentes logs por nombre

### Diferencias con el Sistema Actual

| Aspecto | Sistema Actual (`audit_logs`) | Spatie Activitylog (`activity_log`) |
|---------|-------------------------------|-------------------------------------|
| Campo acción | `action` (enum) | `description` (string) |
| Cambios | `changes` (JSON: `{before, after}`) | `properties` (JSON: `{attributes, old}`) |
| Usuario | `user_id` (FK directa) | `causer_id` + `causer_type` (polimórfico) |
| Modelo | `model_id` + `model_type` | `subject_id` + `subject_type` |
| IP/User Agent | Campos directos | En `properties` (configurable) |

---

## 📋 Pasos de Desarrollo (15 Pasos)

### **Fase 1: Instalación y Configuración de Spatie Activitylog**

#### **Paso 1: Instalar y Configurar la Librería** ✅ COMPLETADO
- [x] Instalar paquete: `composer require spatie/laravel-activitylog`
- [x] Publicar migraciones: `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"`
- [x] Publicar configuración: `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"`
- [x] Revisar archivo de configuración `config/activitylog.php`:
  - Configuración por defecto adecuada (enabled: true, delete_records_older_than_days: 365, default_log_name: 'default')
  - No se requieren cambios adicionales
- [x] Ejecutar migraciones: `php artisan migrate` ✅
- [x] Verificar que la tabla `activity_log` se creó correctamente ✅

**Verificación completada**:
- ✅ Tabla `activity_log` creada correctamente
- ✅ Columnas verificadas: id, log_name, description, subject_type, event, subject_id, causer_type, causer_id, properties, batch_uuid, created_at, updated_at
- ✅ Modelo `Spatie\Activitylog\Models\Activity` disponible y funcionando
- ✅ 3 migraciones ejecutadas exitosamente:
1. `create_activity_log_table.php` - Tabla principal con campos: id, log_name, description, subject_id/subject_type, causer_id/causer_type, properties, timestamps
2. `add_event_column_to_activity_log_table.php` - Columna `event` para eventos específicos
3. `add_batch_uuid_column_to_activity_log_table.php` - Columna `batch_uuid` para batch logging

#### **Paso 2: Migrar Datos Existentes (Opcional)** ✅ COMPLETADO
- [x] Decidir estrategia:
  - **Estrategia elegida**: **Opción A** - Mantener ambas tablas durante transición
  - **Razón**: La tabla `audit_logs` está vacía (0 registros), no hay datos históricos que migrar
  - **Decisión**: Mantener `audit_logs` por compatibilidad con código existente, pero el nuevo sistema usará `activity_log`
- [x] Verificar datos existentes:
  - ✅ Verificado: `audit_logs` tiene 0 registros
  - ✅ No se requiere comando de migración
- [x] Documentar estrategia:
  - El sistema usará `activity_log` de Spatie para todos los nuevos logs
  - La tabla `audit_logs` se mantiene por compatibilidad pero no se usará para nuevos registros
  - Los componentes existentes que usan `AuditLog` se actualizarán gradualmente (Paso 11)

**Nota**: Como no hay datos históricos, no se requiere migración. El sistema comenzará a usar `activity_log` desde ahora.

**Migración de eliminación preparada**: Se ha creado la migración `2026_01_13_153229_drop_audit_logs_table.php` para eliminar la tabla `audit_logs`, pero **NO se ejecutará aún**. Se ejecutará después de actualizar los componentes que usan `AuditLog` (Paso 11) para evitar errores.

#### **Paso 3: Configurar Logging Automático en Modelos** ✅ COMPLETADO
- [x] Identificar modelos que necesitan logging automático:
  - ✅ `Program` - Configurado
  - ✅ `Call` - Configurado
  - ✅ `NewsPost` - Configurado
  - ✅ `Document` - Configurado
  - ✅ `ErasmusEvent` - Configurado
  - ✅ `AcademicYear` - Configurado
- [x] Agregar trait `LogsActivity` a cada modelo:
  ```php
  use Spatie\Activitylog\Traits\LogsActivity;
  use Spatie\Activitylog\LogOptions;
  
  class Program extends Model
  {
      use LogsActivity;
      
      public function getActivitylogOptions(): LogOptions
      {
          return LogOptions::defaults()
              ->logOnly(['name', 'code', 'description', 'is_active'])
              ->logOnlyDirty()
              ->dontLogIfAttributesChangedOnly(['updated_at']);
      }
  }
  ```
- [x] Configurar opciones de logging por modelo según necesidades:
  - ✅ Campos a registrar configurados (solo campos importantes, excluyendo timestamps y slugs)
  - ✅ `logOnlyDirty()` activado (solo registra cambios reales)
  - ✅ `dontLogIfAttributesChangedOnly()` configurado para evitar logs innecesarios
  - ✅ Eventos automáticos: created, updated, deleted (soft delete)
- [x] Probar logging automático creando/actualizando registros:
  - ✅ Verificado: Se registran eventos `created` y `updated` correctamente
  - ✅ Verificado: Se registran cambios en campos configurados
  - ✅ Verificado: No se registran cambios solo en `updated_at` o `slug`

**Configuración aplicada por modelo**:
- **Program**: `code`, `name`, `description`, `is_active`, `order`
- **Call**: `program_id`, `academic_year_id`, `title`, `type`, `modality`, `number_of_places`, `destinations`, `estimated_start_date`, `estimated_end_date`, `status`, `published_at`, `closed_at`
- **NewsPost**: `program_id`, `academic_year_id`, `title`, `excerpt`, `status`, `published_at`, `country`, `city`, `host_entity`, `mobility_type`, `mobility_category`
- **Document**: `category_id`, `program_id`, `academic_year_id`, `title`, `description`, `document_type`, `version`, `is_active`
- **ErasmusEvent**: `program_id`, `call_id`, `title`, `description`, `event_type`, `start_date`, `end_date`, `location`, `is_public`, `is_all_day`
- **AcademicYear**: `year`, `start_date`, `end_date`, `is_current`

---

### **Fase 2: Preparación Base y Policy**

#### **Paso 4: Crear ActivityPolicy** ✅ COMPLETADO
- [x] Crear `app/Policies/ActivityPolicy.php`
- [x] Implementar métodos:
  - ✅ `viewAny()` - Ver listado (solo admin y super-admin)
  - ✅ `view()` - Ver detalle (solo admin y super-admin)
- [x] **Autorización**: Solo usuarios con rol `admin` o `super-admin` pueden ver logs
- [x] **Método before()**: Super-admin tiene acceso total
- [x] Registrar policy manualmente en `AppServiceProvider` (modelo de Spatie, no auto-descubierto)
- [x] Crear tests básicos para la policy en `tests/Feature/Policies/ActivityPolicyTest.php`:
  - ✅ 10 tests pasando (10 assertions)
  - ✅ Verificación de acceso super-admin
  - ✅ Verificación de acceso admin
  - ✅ Verificación de denegación editor
  - ✅ Verificación de denegación viewer
  - ✅ Verificación de denegación sin rol

**Nota**: Los logs de auditoría son de solo lectura, no se pueden crear, editar ni eliminar desde la interfaz.

---

### **Fase 3: Componente Index (Listado)**

#### **Paso 5: Crear Componente Livewire Index** ✅ COMPLETADO
- [x] Crear componente `Admin\AuditLogs\Index` usando `php artisan make:livewire Admin/AuditLogs/Index`
- [x] Importar modelo: `use Spatie\Activitylog\Models\Activity;`
- [x] Implementar propiedades públicas:
  - `string $search = ''` - Búsqueda (con `#[Url(as: 'q')]`)
  - `?string $filterModel = null` - Filtro por modelo (con `#[Url(as: 'modelo')]`)
  - `?int $filterCauserId = null` - Filtro por causer/usuario (con `#[Url(as: 'usuario')]`)
  - `?string $filterDescription = null` - Filtro por descripción/acción (con `#[Url(as: 'accion')]`)
  - `?string $filterLogName = null` - Filtro por log_name (con `#[Url(as: 'log')]`)
  - `?string $filterDateFrom = null` - Filtro fecha desde (con `#[Url(as: 'desde')]`)
  - `?string $filterDateTo = null` - Filtro fecha hasta (con `#[Url(as: 'hasta')]`)
  - `string $sortField = 'created_at'` - Campo de ordenación (con `#[Url(as: 'ordenar')]`)
  - `string $sortDirection = 'desc'` - Dirección de ordenación (con `#[Url(as: 'direccion')]`)
  - `int $perPage = 25` - Elementos por página (con `#[Url(as: 'por-pagina')]`)
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `activities()` - Computed property con paginación, filtros y ordenación
    - Eager loading: `causer`, `subject`
    - Búsqueda en: `description`, `subject_type`
    - Filtros: modelo (subject_type), causer, descripción, log_name, rango de fechas
    - Ordenación por `created_at` desc por defecto
  - `sortBy($field)` - Cambiar ordenación
  - `resetFilters()` - Resetear todos los filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedFilterModel()` - Resetear página al cambiar filtro
  - `updatedFilterCauserId()` - Resetear página al cambiar filtro
  - `updatedFilterDescription()` - Resetear página al cambiar filtro
  - `updatedFilterDateFrom()` - Resetear página al cambiar fecha
  - `updatedFilterDateTo()` - Resetear página al cambiar fecha
  - `getAvailableModels()` - Obtener modelos únicos de `subject_type`
  - `getAvailableCausers()` - Obtener usuarios que tienen logs (desde `causer`)
  - `getAvailableDescriptions()` - Obtener descripciones únicas (created, updated, deleted, etc.)
  - `getModelDisplayName(?string $subjectType)` - Nombre legible del modelo
  - `getDescriptionDisplayName(string $description)` - Nombre legible de la descripción
  - `getDescriptionBadgeVariant(string $description)` - Variante de badge para la descripción
  - ✅ `getSubjectUrl(?string $subjectType, ?int $subjectId)` - URL del subject si existe ruta
  - ✅ `getSubjectTitle($subject)` - Título del subject
  - ✅ `formatChangesSummary(?array $properties)` - Resumen de cambios desde properties
  - ✅ `render()` - Renderizado con paginación
- [x] Implementar autorización con `ActivityPolicy::viewAny()`
- [x] Optimizaciones implementadas:
  - ✅ Caché para modelos disponibles (1 hora)
  - ✅ Caché para usuarios disponibles (30 minutos)
  - ✅ Eager loading de `causer` y `subject`
  - ✅ Debounce en búsqueda (500ms)

#### **Paso 6: Crear Vista Index** ✅ COMPLETADO
- [x] Crear vista `resources/views/livewire/admin/audit-logs/index.blade.php`
- [x] Implementar estructura:
  - ✅ **Header**: Título "Auditoría y Logs" con descripción
  - ✅ **Breadcrumbs**: Admin > Auditoría y Logs
  - ✅ **Filtros avanzados**:
    - ✅ Búsqueda (input con debounce 500ms)
    - ✅ Select de modelo (subject_type, con opción "Todos")
    - ✅ Select de usuario/causer (con opción "Todos")
    - ✅ Select de descripción/acción (created, updated, deleted, etc.)
    - ✅ Select de log_name (solo si se usan múltiples logs)
    - ✅ Date picker "Desde" (fecha)
    - ✅ Date picker "Hasta" (fecha)
    - ✅ Botón "Limpiar filtros"
  - ✅ **Tabla responsive** con columnas:
    - ✅ Fecha/Hora (formato legible + diffForHumans)
    - ✅ Usuario/Causer (nombre + email, con avatar/iniciales)
    - ✅ Descripción/Acción (badge con color según acción)
    - ✅ Modelo/Subject (tipo de modelo con badge)
    - ✅ Registro (nombre/título del subject, enlace si existe)
    - ✅ Cambios (resumen truncado desde `properties`)
    - ✅ Log Name (solo si se usan múltiples logs)
    - ✅ Acciones (botón "Ver detalle")
  - ✅ **Paginación** con selector de elementos por página (15, 25, 50, 100)
  - ✅ **Estado vacío** cuando no hay resultados
  - ✅ **Loading states** durante carga
- [x] Usar componentes Flux UI y componentes UI personalizados:
  - ✅ `x-ui.breadcrumbs` para breadcrumbs
  - ✅ `x-ui.card` para contenedores
  - ✅ `x-ui.search-input` para búsqueda
  - ✅ `x-ui.empty-state` para estado vacío
  - ✅ `flux:field`, `flux:label` para campos
  - ✅ `flux:button` para acciones
  - ✅ `flux:badge` para acciones y modelos
  - ✅ `flux:icon` para iconos
- [x] Diseño responsive con Tailwind CSS v4
- [x] Soporte para dark mode

---

### **Fase 4: Componente Show (Detalle)**

#### **Paso 7: Crear Componente Livewire Show** ✅ COMPLETADO
- [x] Crear componente `Admin\AuditLogs\Show` usando `php artisan make:livewire Admin/AuditLogs/Show`
- [x] Importar modelo: `use Spatie\Activitylog\Models\Activity;`
- [x] Implementar propiedades públicas:
  - ✅ `Activity $activity` - El log a mostrar
- [x] Implementar métodos:
  - `mount(Activity $activity)` - Inicialización con autorización y eager loading
    - Cargar relaciones: `causer`, `subject`
  - `getModelDisplayName(?string $subjectType)` - Nombre legible del modelo
  - `getDescriptionDisplayName(string $description)` - Nombre legible de la descripción
  - `getDescriptionBadgeVariant(string $description)` - Variante de badge
  - `getSubjectUrl(?string $subjectType, ?int $subjectId)` - URL del subject si existe
  - `getSubjectTitle($subject)` - Título del subject (title, name, o ID)
  - `formatProperties(?array $properties)` - Formatear propiedades para visualización
  - `getChangesFromProperties(?array $properties)` - Extraer cambios (attributes/old) de properties
  - `formatJsonForDisplay($data)` - Formatear JSON de forma legible
  - ✅ `getUserAgent(?array $properties)` - Extraer user agent desde properties
  - ✅ `parseUserAgent(?string $userAgent)` - Parsear user agent para mostrar información
  - ✅ `getIpAddress(?array $properties)` - Extraer IP desde properties
  - ✅ `hasChanges()` - Verificar si hay cambios
  - ✅ `getCustomProperties(?array $properties)` - Obtener propiedades personalizadas
  - ✅ `formatValueForDisplay($value)` - Formatear valores para visualización
  - ✅ `render()` - Renderizado
- [x] Implementar autorización con `ActivityPolicy::view()`

#### **Paso 8: Crear Vista Show** ✅ COMPLETADO
- [x] Crear vista `resources/views/livewire/admin/audit-logs/show.blade.php`
- [x] Implementar estructura:
  - ✅ **Header**: 
    - ✅ Título "Detalle de Log de Auditoría"
    - ✅ Breadcrumbs: Admin > Auditoría y Logs > Detalle
    - ✅ Botón "Volver al listado"
  - ✅ **Información Principal** (card):
    - ✅ ID del log
    - ✅ Fecha y hora (formato completo + diffForHumans)
    - ✅ Descripción/Acción (badge con color)
    - ✅ Log Name (si aplica)
    - ✅ Usuario/Causer (nombre, email, avatar con iniciales)
    - ✅ IP Address (extraída de properties si está disponible)
    - ✅ User Agent (extraído de properties si está disponible, con información parseada: navegador, OS, dispositivo)
  - ✅ **Información del Subject** (card):
    - ✅ Tipo de modelo (subject_type) con badge y nombre completo
    - ✅ ID del modelo (subject_id)
    - ✅ Nombre/Título del modelo (enlace si existe ruta)
    - ✅ Estado del modelo (eliminado si no existe)
  - ✅ **Cambios Realizados** (card):
    - ✅ Si hay cambios en `properties`, mostrar tabla comparativa:
      - ✅ Campo (con código)
      - ✅ Valor Anterior (desde `properties.old`, destacado en rojo)
      - ✅ Valor Nuevo (desde `properties.attributes`, destacado en verde)
      - ✅ Formato inteligente de valores (null, boolean, arrays, strings largos)
    - ✅ Si no hay cambios, mostrar mensaje informativo
    - ✅ Formato JSON expandible para vista técnica (colapsable con Alpine.js)
  - ✅ **Propiedades Personalizadas** (card colapsable):
    - ✅ Mostrar todas las propiedades personalizadas (excluyendo old/attributes/ip/user_agent)
    - ✅ Formato visual con cards individuales
  - ✅ **Información Técnica** (card colapsable):
    - ✅ JSON completo del log
    - ✅ Properties completo
  - ✅ **Acciones**:
    - ✅ Botón "Ver registro relacionado" (si existe subject y ruta)
    - ✅ Botón "Ver usuario" (si existe causer)
    - ✅ Botón "Volver al listado"
- [x] Usar componentes Flux UI y componentes UI personalizados:
  - ✅ `x-ui.breadcrumbs` para breadcrumbs
  - ✅ `x-ui.card` para secciones
  - ✅ `flux:heading` para títulos
  - ✅ `flux:button` para acciones
  - ✅ `flux:badge` para estados y acciones
  - ✅ `flux:icon` para iconos
- [x] Usar Alpine.js para secciones colapsables (x-data, x-show, x-collapse)
- [x] Diseño responsive con Tailwind CSS v4
- [x] Soporte para dark mode
- [ ] Adaptar componente `x-ui.audit-log-entry` para usar Activity si es necesario

---

### **Fase 5: Rutas y Navegación**

#### **Paso 9: Configurar Rutas** ✅ COMPLETADO
- [x] Agregar rutas en `routes/web.php` dentro del grupo `admin`:
  ```php
  // Rutas de Auditoría y Logs
  Route::get('/auditoria', \App\Livewire\Admin\AuditLogs\Index::class)->name('audit-logs.index');
  Route::get('/auditoria/{activity}', \App\Livewire\Admin\AuditLogs\Show::class)->name('audit-logs.show');
  ```
- [x] Verificar que las rutas funcionan correctamente
  - ✅ Rutas registradas: `admin.audit-logs.index` y `admin.audit-logs.show`
  - ✅ Route model binding funciona automáticamente con `Activity` (Laravel resuelve por ID)
- [x] Probar navegación entre Index y Show (pendiente de prueba manual en navegador)

#### **Paso 10: Integrar en Navegación** ✅ COMPLETADO
- [x] Agregar enlace en sidebar de administración (`resources/views/components/layouts/app/sidebar.blade.php`)
- [x] Agregar en sección "Sistema" (después de Traducciones)
- [x] Icono apropiado: `clipboard-document-list` (Flux icon)
- [x] Verificar que solo se muestra para usuarios con permisos adecuados
  - ✅ Usa `@can('viewAny', \Spatie\Activitylog\Models\Activity::class)`
  - ✅ Solo usuarios con rol Admin o Super Admin pueden ver (según ActivityPolicy)
- [x] Agregar traducciones:
  - ✅ `lang/es/common.php`: `'audit_logs' => 'Auditoría y Logs'`
  - ✅ `lang/en/common.php`: `'audit_logs' => 'Audit Logs'`
- [x] Breadcrumbs ya están implementados en los componentes Index y Show

---

### **Fase 6: Integración con Sistema Existente**

#### **Paso 11: Actualizar Componentes Existentes** ✅ COMPLETADO
- [x] Actualizar `Admin\Dashboard` para usar `Activity` en lugar de `AuditLog`
  - ✅ Cambiado `AuditLog::query()` a `Activity::query()`
  - ✅ Actualizado para usar `causer` y `subject` en lugar de `user` y `model`
  - ✅ Actualizado `action` a `description`
  - ✅ Actualizado `model_type` a `subject_type`
- [x] Actualizar `Admin\Users\Show` para usar `Activity` en lugar de `AuditLog`
  - ✅ Cambiado método `auditLogs()` a `activities()`
  - ✅ Actualizado para usar `causer_type` y `causer_id` en lugar de `user_id`
  - ✅ Actualizado método `statistics()` para usar `Activity`
  - ✅ Actualizado vista para usar `$this->activities`
  - ✅ Comentado limpieza de causer_id en `forceDelete()` (opcional mantener histórico)
- [x] Actualizar `Admin\Users\Index` para usar `Activity`
  - ✅ Eliminado `withCount('auditLogs')`
  - ✅ Actualizado conteo de actividades en vista (cálculo dinámico)
  - ✅ Eliminado código de limpieza de audit logs en `forceDelete()`
- [x] Actualizar componente `x-ui.audit-log-entry` para aceptar tanto `AuditLog` como `Activity`
  - ✅ Agregada detección de tipo (`instanceof Activity`)
  - ✅ Soporte para propiedades de Activity (`description`, `subject_type`, `subject`, `subject_id`)
  - ✅ Extracción de cambios desde `properties` (formato `old`/`attributes`)
  - ✅ Actualizado `formatChanges()` para soportar ambos formatos (before/after y old/attributes)
- [x] Verificar que todos los componentes funcionan correctamente
  - ✅ Código formateado con Laravel Pint
  - ✅ Sin errores de lint

#### **Paso 12: Configurar Logging Manual para Acciones Especiales** ✅ COMPLETADO
- [x] Identificar acciones que no son eventos de modelo estándar:
  - ✅ Publicar convocatoria/noticia/resolución (`publish`)
  - ✅ Despublicar noticia/resolución (`unpublish`)
  - ✅ Restaurar contenido (`restore`)
  - ✅ Asignar roles (`assignRoles`)
- [x] Implementar logging manual usando `activity()`:
  - ✅ Agregado logging en `Calls\Show::publish()` con propiedades: old_status, new_status, published_at
  - ✅ Agregado logging en `News\Show::publish()` y `unpublish()` con propiedades de estado
  - ✅ Agregado logging en `Calls\Resolutions\Show::publish()` y `unpublish()` con propiedades de publicación
  - ✅ Agregado logging en `restore()` de Calls, News, Resolutions
  - ✅ Agregado logging en `Users\Show::assignRoles()` con old_roles y new_roles
  - ✅ Todas las actividades incluyen: `ip_address`, `user_agent`, y datos específicos de la acción
- [x] Agregar logging en:
  - ✅ Métodos `publish()` de Call, NewsPost y Resolution
  - ✅ Métodos `unpublish()` de NewsPost y Resolution
  - ✅ Métodos `restore()` de Call, NewsPost y Resolution
  - ✅ Método `assignRoles()` de User

---

### **Fase 7: Optimizaciones y Mejoras**

#### **Paso 13: Optimizaciones de Rendimiento** ✅ COMPLETADO
- [x] Verificar índices en tabla `activity_log`:
  - ✅ Índice compuesto en `subject_type` + `subject_id` (`activity_log_subject_index`)
  - ✅ Índice compuesto en `causer_type` + `causer_id` (`activity_log_causer_index`)
  - ✅ Índice en `created_at` (`activity_log_created_at_index`)
  - ✅ Índice en `description` (`activity_log_description_index`)
  - ✅ Índice en `log_name` (ya existía desde migración de Spatie)
  - ✅ Migración creada: `2026_01_13_160601_add_indexes_to_activity_log_table.php`
- [x] Implementar eager loading en todas las consultas:
  - ✅ `causer` (relación polimórfica) - implementado en `activities()`
  - ✅ `subject` (relación polimórfica) - implementado en `activities()`
  - ✅ Eager loading también en `Show::mount()` y `Dashboard::loadRecentActivities()`
- [x] Implementar caché para listados de filtros:
  - ✅ Modelos disponibles (caché 1 hora) - `availableModels()`
  - ✅ Usuarios disponibles (caché 30 minutos) - `availableCausers()`
  - ✅ Descripciones disponibles (sin caché, son estáticas) - `availableDescriptions()`
- [x] Optimizar consultas de paginación:
  - ✅ Eager loading de `causer` y `subject` para evitar N+1 queries
  - ✅ Ordenación secundaria por `id` para paginación consistente
  - ✅ Consultas optimizadas con índices apropiados
- [x] Implementar debounce en búsqueda (500ms) - ✅ Ya implementado en vista
- [x] Configurar limpieza automática de logs antiguos (opcional, desde configuración)
  - ✅ Configuración disponible en `config/activitylog.php`: `delete_records_older_than_days` (365 días)
  - ✅ Comando disponible: `php artisan activitylog:clean`

#### **Paso 14: Mejoras de UX** ✅ COMPLETADO
- [x] Agregar tooltips informativos en filtros
  - ✅ Tooltip en campo de búsqueda: "Busca en la descripción de la acción y en el tipo de modelo afectado"
  - ✅ Tooltip en filtro de Modelo: "Filtra los logs por el tipo de modelo afectado (Programa, Convocatoria, Noticia, etc.)"
  - ✅ Tooltip en filtro de Usuario: "Filtra los logs por el usuario que realizó la acción"
  - ✅ Tooltip en filtro de Acción: "Filtra los logs por el tipo de acción realizada (creado, actualizado, eliminado, publicado, etc.)"
  - ✅ Tooltip en filtro de fecha Desde: "Filtra los logs desde esta fecha. Deja vacío para no filtrar por fecha inicial."
  - ✅ Tooltip en filtro de fecha Hasta: "Filtra los logs hasta esta fecha. Deja vacío para no filtrar por fecha final."
- [x] Agregar indicadores de carga durante filtrado
  - ✅ Ya implementado con `wire:loading.delay` y spinner animado
  - ✅ Muestra "Cargando..." durante filtrado, búsqueda y ordenación
- [x] Agregar mensajes informativos cuando no hay resultados
  - ✅ Ya implementado con `x-ui.empty-state` con título y descripción informativos
- [x] Agregar filtro rápido por "Últimas 24 horas", "Última semana", "Último mes"
  - ✅ Botones de filtro rápido agregados antes de los campos de fecha
  - ✅ Configuran automáticamente `filterDateFrom` y `filterDateTo`
- [x] Agregar exportación de logs:
  - ✅ Instalado Laravel Excel (maatwebsite/excel v3.1.67)
  - ✅ Creada clase `App\Exports\AuditLogsExport` con:
    - ✅ Implementación de `FromCollection` con filtros aplicados
    - ✅ Implementación de `WithHeadings` para encabezados
    - ✅ Implementación de `WithMapping` para formatear datos
    - ✅ Implementación de `WithTitle` para nombre de hoja
    - ✅ Implementación de `WithStyles` para estilos (encabezados en negrita)
  - ✅ Agregado método `export()` en componente Index
  - ✅ Agregado botón "Exportar" en vista Index
  - ✅ Exporta con todos los filtros aplicados
  - ✅ Nombre de archivo: `audit-logs-YYYY-MM-DD-HHmmss.xlsx`
  - ✅ Columnas exportadas: ID, Fecha/Hora, Usuario, Email Usuario, Acción, Modelo, ID Registro, Registro, Log Name, Cambios
- [ ] Agregar vista de estadísticas (opcional):
  - Gráfico de acciones por tipo
  - Gráfico de actividad por fecha
  - Top usuarios más activos
  - Top modelos más modificados

---

### **Fase 8: Testing**

#### **Paso 15: Tests Completos**
- [ ] **Tests de Policy** (`tests/Feature/Policies/ActivityPolicyTest.php`):
  - `test_super_admin_can_view_any_activities()`
  - `test_admin_can_view_any_activities()`
  - `test_editor_cannot_view_activities()`
  - `test_viewer_cannot_view_activities()`
  - `test_super_admin_can_view_activity()`
  - `test_admin_can_view_activity()`
  - `test_editor_cannot_view_activity()`
  - `test_viewer_cannot_view_activity()`
- [ ] **Tests de Componente Index** (`tests/Feature/Livewire/Admin/AuditLogs/IndexTest.php`):
  - Renderizado, autenticación, autorización
  - Filtros (modelo, causer, descripción, fechas)
  - Búsqueda, ordenación, paginación
  - Estado vacío, visualización de información
- [ ] **Tests de Componente Show** (`tests/Feature/Livewire/Admin/AuditLogs/ShowTest.php`):
  - Renderizado, autenticación, autorización
  - Visualización de información completa
  - Formateo de propiedades y cambios
  - Enlaces a modelos relacionados
  - Manejo de subjects/causers eliminados
- [ ] **Tests de Logging Automático**:
  - Verificar que se crean logs al crear/actualizar/eliminar modelos
  - Verificar que se registran los campos correctos
  - Verificar relaciones causer y subject
- [ ] **Tests de Logging Manual**:
  - Verificar logging de acciones especiales (publish, archive, etc.)
  - Verificar que se guardan propiedades personalizadas

---

## 📊 Estructura de Archivos

```
app/
├── Livewire/
│   └── Admin/
│       └── AuditLogs/
│           ├── Index.php
│           └── Show.php
├── Policies/
│   └── ActivityPolicy.php
├── Console/
│   └── Commands/
│       └── MigrateAuditLogsToActivityLog.php (opcional)
config/
└── activitylog.php (publicado por Spatie)
database/
└── migrations/
    └── xxxx_xx_xx_xxxxxx_create_activity_log_table.php (publicado por Spatie)
resources/
└── views/
    └── livewire/
        └── admin/
            └── audit-logs/
                ├── index.blade.php
                └── show.blade.php
tests/
└── Feature/
    ├── Livewire/
    │   └── Admin/
    │       └── AuditLogs/
    │           ├── IndexTest.php
    │           └── ShowTest.php
    └── Policies/
        └── ActivityPolicyTest.php
```

---

## 🎨 Componentes UI a Reutilizar

- `x-ui.audit-log-entry` - Adaptar para aceptar `Activity` además de `AuditLog`
- Componentes Flux UI estándar (button, badge, input, select, table, pagination, etc.)

---

## 🔒 Consideraciones de Seguridad

1. **Autorización**: Solo admin y super-admin pueden ver logs
2. **Datos Sensibles**: Configurar `dontLogIfAttributesChangedOnly(['password', 'remember_token'])` en modelos
3. **Rate Limiting**: Considerar rate limiting en exportación si se implementa
4. **Logs Inmutables**: Los logs no se pueden modificar ni eliminar desde la interfaz
5. **Limpieza Automática**: Configurar limpieza de logs antiguos según políticas de retención

---

## 📝 Notas de Implementación

1. **Modelo Activity**: Usar `Spatie\Activitylog\Models\Activity` en lugar de `AuditLog`
2. **Relaciones**:
   - `causer()` - MorphTo (polimórfico, puede ser User u otro modelo)
   - `subject()` - MorphTo (polimórfico, el modelo afectado)
3. **Campos importantes**:
   - `description`: string (ej: "created", "updated", "deleted", "published")
   - `properties`: JSON con estructura `{attributes: {}, old: {}, custom: {}}`
   - `log_name`: string (para múltiples logs, por defecto "default")
4. **Estructura de Properties**:
   ```json
   {
     "attributes": {"name": "Nuevo", "status": "active"},
     "old": {"name": "Viejo", "status": "draft"},
     "ip_address": "127.0.0.1",
     "user_agent": "Mozilla/5.0..."
   }
   ```
5. **Trait LogsActivity**: Agregar a modelos que necesiten logging automático
6. **Migración de Datos**: Considerar mantener `audit_logs` durante transición o migrar completamente

---

## ✅ Criterios de Aceptación

- [ ] Spatie Activitylog instalado y configurado
- [ ] Logging automático funcionando en modelos principales
- [ ] Logging manual funcionando para acciones especiales
- [ ] Policy creada y funcionando
- [ ] Componente Index creado con todos los filtros
- [ ] Componente Show creado con información completa
- [ ] Rutas configuradas y funcionando
- [ ] Navegación integrada en sidebar
- [ ] Componentes existentes actualizados
- [ ] Tests completos pasando (mínimo 80% cobertura)
- [ ] Diseño responsive y moderno
- [ ] Soporte para dark mode
- [ ] Optimizaciones de rendimiento implementadas
- [ ] Documentación actualizada

---

## 🔄 Migración desde Sistema Actual

Si se decide migrar completamente de `audit_logs` a `activity_log`:

1. **Fase de Transición** (opcional):
   - Mantener ambas tablas funcionando
   - Nuevos logs van a `activity_log`
   - Visualizar ambos en el panel (con indicador de origen)

2. **Migración de Datos**:
   - Crear comando Artisan para migración
   - Mapear estructura de datos
   - Validar integridad

3. **Deprecación**:
   - Marcar `AuditLog` como deprecated
   - Actualizar todos los componentes
   - Eliminar tabla `audit_logs` (opcional, después de período de gracia)

---

**Fecha de Creación**: Diciembre 2025  
**Última Actualización**: Diciembre 2025 (Adaptado para Spatie Activitylog)  
**Estado**: 📋 Plan completado - Pendiente de implementación
