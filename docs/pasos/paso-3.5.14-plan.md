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

#### **Paso 1: Instalar y Configurar la Librería**
- [ ] Instalar paquete: `composer require spatie/laravel-activitylog`
- [ ] Publicar migraciones: `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"`
- [ ] Publicar configuración: `php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"`
- [ ] Revisar archivo de configuración `config/activitylog.php`:
  - Configurar `default_log_name` si es necesario
  - Revisar opciones de limpieza automática
- [ ] Ejecutar migraciones: `php artisan migrate`
- [ ] Verificar que la tabla `activity_log` se creó correctamente

#### **Paso 2: Migrar Datos Existentes (Opcional)**
- [ ] Decidir estrategia:
  - **Opción A**: Mantener ambas tablas (`audit_logs` y `activity_log`) durante transición
  - **Opción B**: Migrar datos de `audit_logs` a `activity_log` y deprecar `audit_logs`
- [ ] Si se elige migración, crear comando Artisan `MigrateAuditLogsToActivityLog`:
  - Mapear campos: `action` → `description`, `changes` → `properties`, etc.
  - Convertir estructura `{before, after}` a `{attributes, old}`
  - Mapear `user_id` a `causer_id` + `causer_type`
  - Mapear `model_id/model_type` a `subject_id/subject_type`
  - Guardar IP y User Agent en `properties`
- [ ] Ejecutar migración de datos
- [ ] Verificar integridad de datos migrados

#### **Paso 3: Configurar Logging Automático en Modelos**
- [ ] Identificar modelos que necesitan logging automático:
  - `Program`, `Call`, `NewsPost`, `Document`, `ErasmusEvent`, `AcademicYear`, etc.
- [ ] Agregar trait `LogsActivity` a cada modelo:
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
- [ ] Configurar opciones de logging por modelo según necesidades:
  - Campos a registrar
  - Eventos a registrar (created, updated, deleted)
  - Descripciones personalizadas
- [ ] Probar logging automático creando/actualizando registros

---

### **Fase 2: Preparación Base y Policy**

#### **Paso 4: Crear ActivityPolicy**
- [ ] Crear `app/Policies/ActivityPolicy.php`
- [ ] Implementar métodos:
  - `viewAny()` - Ver listado (solo admin y super-admin)
  - `view()` - Ver detalle (solo admin y super-admin)
- [ ] **Autorización**: Solo usuarios con rol `admin` o `super-admin` pueden ver logs
- [ ] **Método before()**: Super-admin tiene acceso total
- [ ] Crear tests básicos para la policy en `tests/Feature/Policies/ActivityPolicyTest.php`

**Nota**: Los logs de auditoría son de solo lectura, no se pueden crear, editar ni eliminar desde la interfaz.

---

### **Fase 3: Componente Index (Listado)**

#### **Paso 5: Crear Componente Livewire Index**
- [ ] Crear componente `Admin\AuditLogs\Index` usando `php artisan make:livewire Admin/AuditLogs/Index`
- [ ] Importar modelo: `use Spatie\Activitylog\Models\Activity;`
- [ ] Implementar propiedades públicas:
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
  - `getSubjectUrl(?string $subjectType, ?int $subjectId)` - URL del subject si existe ruta
  - `render()` - Renderizado con paginación
- [ ] Implementar autorización con `ActivityPolicy::viewAny()`

#### **Paso 6: Crear Vista Index**
- [ ] Crear vista `resources/views/livewire/admin/audit-logs/index.blade.php`
- [ ] Implementar estructura:
  - **Header**: Título "Auditoría y Logs" con descripción
  - **Breadcrumbs**: Admin > Auditoría y Logs
  - **Filtros avanzados**:
    - Búsqueda (input con debounce)
    - Select de modelo (subject_type, con opción "Todos")
    - Select de usuario/causer (con opción "Todos")
    - Select de descripción/acción (created, updated, deleted, etc.)
    - Select de log_name (si se usan múltiples logs)
    - Date picker "Desde" (fecha)
    - Date picker "Hasta" (fecha)
    - Botón "Limpiar filtros"
  - **Tabla responsive** con columnas:
    - Fecha/Hora (formato legible + diffForHumans)
    - Usuario/Causer (nombre + email, con avatar si disponible)
    - Descripción/Acción (badge con color según acción)
    - Modelo/Subject (tipo de modelo)
    - Registro (nombre/título del subject, enlace si existe)
    - Cambios (resumen truncado desde `properties`, enlace a detalle)
    - Log Name (si se usan múltiples logs)
    - Acciones (botón "Ver detalle")
  - **Paginación** con selector de elementos por página
  - **Estado vacío** cuando no hay resultados
  - **Loading states** durante carga
- [ ] Usar componentes Flux UI:
  - `flux:heading` para títulos
  - `flux:button` para acciones
  - `flux:input` para búsqueda
  - `flux:select` para filtros
  - `flux:badge` para acciones
  - `flux:table` para tabla
  - `flux:pagination` para paginación
- [ ] Diseño responsive con Tailwind CSS v4
- [ ] Soporte para dark mode

---

### **Fase 4: Componente Show (Detalle)**

#### **Paso 7: Crear Componente Livewire Show**
- [ ] Crear componente `Admin\AuditLogs\Show` usando `php artisan make:livewire Admin/AuditLogs/Show`
- [ ] Importar modelo: `use Spatie\Activitylog\Models\Activity;`
- [ ] Implementar propiedades públicas:
  - `Activity $activity` - El log a mostrar
- [ ] Implementar métodos:
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
  - `getUserAgentInfo(?array $properties)` - Extraer información del user agent desde properties
  - `getIpAddress(?array $properties)` - Extraer IP desde properties
  - `render()` - Renderizado
- [ ] Implementar autorización con `ActivityPolicy::view()`

#### **Paso 8: Crear Vista Show**
- [ ] Crear vista `resources/views/livewire/admin/audit-logs/show.blade.php`
- [ ] Implementar estructura:
  - **Header**: 
    - Título "Detalle de Log de Auditoría"
    - Breadcrumbs: Admin > Auditoría y Logs > Detalle
    - Botón "Volver al listado"
  - **Información Principal** (card):
    - ID del log
    - Fecha y hora (formato completo + diffForHumans)
    - Descripción/Acción (badge con color)
    - Log Name (si aplica)
    - Usuario/Causer (nombre, email, avatar si disponible)
    - IP Address (extraída de properties si está disponible)
    - User Agent (extraído de properties si está disponible, con información parseada)
  - **Información del Subject** (card):
    - Tipo de modelo (subject_type)
    - ID del modelo (subject_id)
    - Nombre/Título del modelo (enlace si existe ruta)
    - Estado actual del modelo (si está disponible)
  - **Cambios Realizados** (card expandible):
    - Si hay cambios en `properties`, mostrar tabla comparativa:
      - Campo
      - Valor Anterior (desde `properties.old`)
      - Valor Nuevo (desde `properties.attributes`)
      - Diferencia destacada
    - Si no hay cambios, mostrar mensaje
    - Formato JSON expandible para vista técnica
  - **Propiedades Personalizadas** (card colapsable):
    - Mostrar todas las propiedades personalizadas
    - Formato JSON expandible
  - **Información Técnica** (card colapsable):
    - JSON completo del log
    - Properties completo
    - Información de la sesión (si disponible)
  - **Acciones**:
    - Botón "Ver registro relacionado" (si existe subject y ruta)
    - Botón "Ver usuario" (si existe causer)
    - Botón "Volver al listado"
- [ ] Usar componentes Flux UI:
  - `flux:heading` para títulos
  - `flux:button` para acciones
  - `flux:badge` para estados
  - `flux:card` o `flux:callout` para secciones
  - `flux:field` para información estructurada
- [ ] Diseño responsive con Tailwind CSS v4
- [ ] Soporte para dark mode
- [ ] Adaptar componente `x-ui.audit-log-entry` para usar Activity si es necesario

---

### **Fase 5: Rutas y Navegación**

#### **Paso 9: Configurar Rutas**
- [ ] Agregar rutas en `routes/web.php` dentro del grupo `admin`:
  ```php
  // Rutas de Auditoría y Logs
  Route::get('/auditoria', \App\Livewire\Admin\AuditLogs\Index::class)->name('audit-logs.index');
  Route::get('/auditoria/{activity}', \App\Livewire\Admin\AuditLogs\Show::class)->name('audit-logs.show');
  ```
- [ ] Verificar que las rutas funcionan correctamente
- [ ] Probar navegación entre Index y Show

#### **Paso 10: Integrar en Navegación**
- [ ] Agregar enlace en sidebar de administración (`resources/views/components/layouts/admin-sidebar.blade.php` o similar)
- [ ] Agregar en sección "Sistema" o "Configuración"
- [ ] Icono apropiado (ej: `heroicon-o-clipboard-document-list` o `heroicon-o-shield-check`)
- [ ] Verificar que solo se muestra para usuarios con permisos adecuados
- [ ] Agregar en breadcrumbs si es necesario

---

### **Fase 6: Integración con Sistema Existente**

#### **Paso 11: Actualizar Componentes Existentes**
- [ ] Actualizar `Admin\Dashboard` para usar `Activity` en lugar de `AuditLog`
- [ ] Actualizar `Admin\Users\Show` para usar `Activity` en lugar de `AuditLog`
- [ ] Actualizar componente `x-ui.audit-log-entry` para aceptar tanto `AuditLog` como `Activity`
- [ ] Crear helper o método para convertir entre formatos si es necesario
- [ ] Verificar que todos los componentes funcionan correctamente

#### **Paso 12: Configurar Logging Manual para Acciones Especiales**
- [ ] Identificar acciones que no son eventos de modelo estándar:
  - Publicar convocatoria/noticia (`publish`)
  - Archivar contenido (`archive`)
  - Restaurar contenido (`restore`)
  - Asignar roles (`assignRoles`)
- [ ] Implementar logging manual usando `activity()`:
  ```php
  activity()
      ->performedOn($call)
      ->causedBy(auth()->user())
      ->withProperties([
          'ip_address' => request()->ip(),
          'user_agent' => request()->userAgent(),
          'old_status' => $call->getOriginal('status'),
          'new_status' => 'published',
      ])
      ->log('published');
  ```
- [ ] Agregar logging en:
  - Métodos `publish()` de Call y NewsPost
  - Métodos `archive()` y `restore()` donde existan
  - Métodos de asignación de roles

---

### **Fase 7: Optimizaciones y Mejoras**

#### **Paso 13: Optimizaciones de Rendimiento**
- [ ] Verificar índices en tabla `activity_log`:
  - Índice en `subject_type` + `subject_id`
  - Índice en `causer_type` + `causer_id`
  - Índice en `created_at`
  - Índice en `log_name` (si se usa)
- [ ] Implementar eager loading en todas las consultas:
  - `causer` (relación polimórfica)
  - `subject` (relación polimórfica)
- [ ] Implementar caché para listados de filtros:
  - Modelos disponibles (caché 1 hora)
  - Usuarios disponibles (caché 30 minutos)
  - Descripciones disponibles (sin caché, son estáticas)
- [ ] Optimizar consultas de paginación:
  - Usar `select()` específico si no se necesitan todos los campos
  - Evitar N+1 queries
- [ ] Implementar debounce en búsqueda (500ms)
- [ ] Configurar limpieza automática de logs antiguos (opcional, desde configuración)

#### **Paso 14: Mejoras de UX**
- [ ] Agregar tooltips informativos en filtros
- [ ] Agregar indicadores de carga durante filtrado
- [ ] Agregar mensajes informativos cuando no hay resultados
- [ ] Agregar exportación de logs (opcional, para futura implementación):
  - Botón "Exportar" en Index
  - Exportar a CSV/Excel con filtros aplicados
- [ ] Agregar vista de estadísticas (opcional):
  - Gráfico de acciones por tipo
  - Gráfico de actividad por fecha
  - Top usuarios más activos
  - Top modelos más modificados
- [ ] Agregar filtro rápido por "Últimas 24 horas", "Última semana", "Último mes"

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
