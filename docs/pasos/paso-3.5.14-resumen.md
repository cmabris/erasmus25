# Resumen Ejecutivo: Paso 3.5.14 - Auditoría y Logs en Panel de Administración

## 🎯 Objetivo

Desarrollar un sistema completo de visualización de logs de auditoría en el panel de administración usando **Spatie Laravel Activitylog v4** con:
- Integración de librería profesional para logging automático
- Listado moderno con tabla interactiva y filtros avanzados
- Vista detallada de cada log con información completa
- Filtros por modelo, usuario, acción y fecha
- Visualización de cambios antes/después en formato legible
- Logging automático de eventos de modelos (created, updated, deleted)
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📚 Spatie Laravel Activitylog v4

### Ventajas de Usar la Librería

1. **Logging Automático**: Trait `LogsActivity` para eventos automáticos de modelos
2. **Logging Manual**: Función helper `activity()->log()` para acciones personalizadas
3. **Modelo Activity**: `Spatie\Activitylog\Models\Activity` con relaciones polimórficas
4. **Opciones Avanzadas**: Control granular de qué y cuándo registrar
5. **Múltiples Logs**: Soporte para diferentes logs por nombre
6. **Batch Logging**: Agrupar múltiples logs relacionados
7. **Mantenimiento**: Librería mantenida activamente por Spatie

### Estructura de Datos

- **Tabla**: `activity_log`
- **Relaciones**:
  - `causer` - Usuario/entidad que causó la actividad (polimórfico)
  - `subject` - Modelo sobre el que se realizó la actividad (polimórfico)
- **Campos principales**:
  - `description` - Descripción de la acción (ej: "created", "updated")
  - `properties` - JSON con cambios y datos adicionales
  - `log_name` - Nombre del log (para múltiples logs)

---

## 📋 Pasos Principales (15 Pasos)

### **Fase 1: Instalación y Configuración**

1. **Instalar y Configurar Spatie Activitylog** (Paso 1)
   - Instalar paquete via Composer
   - Publicar migraciones y configuración
   - Ejecutar migraciones
   - Revisar configuración

2. **Migrar Datos Existentes** (Paso 2, Opcional)
   - Decidir estrategia (mantener ambas o migrar)
   - Crear comando de migración si es necesario
   - Mapear estructura de datos

3. **Configurar Logging Automático** (Paso 3)
   - Agregar trait `LogsActivity` a modelos principales
   - Configurar opciones de logging por modelo
   - Probar logging automático

---

### **Fase 2: Preparación Base**

4. **Crear ActivityPolicy** (Paso 4)
   - Policy de solo lectura (viewAny, view)
   - Solo admin y super-admin pueden ver logs
   - Tests de autorización

---

### **Fase 3: Componente Index (Listado)**

5. **Componente Livewire Index** (Paso 5)
   - Propiedades con `#[Url]` para persistencia
   - Filtros: búsqueda, modelo, causer, descripción, log_name, fechas
   - Ordenación y paginación
   - Eager loading: `causer`, `subject`
   - Métodos helper para nombres legibles

6. **Vista Index** (Paso 6)
   - Header con título y descripción
   - Breadcrumbs
   - Panel de filtros avanzados
   - Tabla responsive con columnas:
     - Fecha/Hora
     - Usuario/Causer (con avatar)
     - Descripción/Acción (badge)
     - Modelo/Subject
     - Registro (enlace si existe)
     - Cambios (resumen)
     - Log Name
     - Acciones
   - Paginación
   - Estado vacío
   - Loading states

---

### **Fase 4: Componente Show (Detalle)**

7. **Componente Livewire Show** (Paso 7)
   - Propiedad `Activity $activity`
   - Eager loading de relaciones
   - Métodos helper para formateo:
     - `formatProperties()` - Formatear propiedades
     - `getChangesFromProperties()` - Extraer cambios
     - `getUserAgentInfo()` - Parsear user agent
     - `getIpAddress()` - Extraer IP
   - Generación de URLs a subjects relacionados

8. **Vista Show** (Paso 8)
   - Header con breadcrumbs y botón volver
   - **Card Información Principal**: ID, fecha, descripción, causer, IP, user agent
   - **Card Información del Subject**: Tipo, ID, nombre, estado
   - **Card Cambios Realizados**: Tabla comparativa (old vs attributes)
   - **Card Propiedades Personalizadas**: Propiedades adicionales
   - **Card Información Técnica**: JSON completo
   - **Acciones**: Enlaces a subject y causer, botón volver

---

### **Fase 5: Rutas y Navegación**

9. **Configurar Rutas** (Paso 9)
   - `/admin/auditoria` → Index
   - `/admin/auditoria/{activity}` → Show
   - Middleware de autenticación y verificación

10. **Integrar en Navegación** (Paso 10)
    - Añadir enlace en sidebar
    - Sección "Sistema" o "Configuración"
    - Icono apropiado
    - Verificación de permisos

---

### **Fase 6: Integración con Sistema Existente**

11. **Actualizar Componentes Existentes** (Paso 11)
    - Actualizar `Admin\Dashboard` para usar `Activity`
    - Actualizar `Admin\Users\Show` para usar `Activity`
    - Adaptar `x-ui.audit-log-entry` para aceptar `Activity`

12. **Configurar Logging Manual** (Paso 12)
    - Identificar acciones especiales (publish, archive, restore, etc.)
    - Implementar logging manual con `activity()->log()`
    - Agregar en métodos correspondientes

---

### **Fase 7: Optimizaciones y Mejoras**

13. **Optimizaciones de Rendimiento** (Paso 13)
    - Verificar índices en `activity_log`
    - Eager loading en todas las consultas
    - Caché para listados de filtros
    - Debounce en búsqueda
    - Configurar limpieza automática

14. **Mejoras de UX** (Paso 14)
    - Tooltips informativos
    - Indicadores de carga
    - Mensajes informativos
    - Exportación de logs (opcional)
    - Vista de estadísticas (opcional)
    - Filtros rápidos por período

---

### **Fase 8: Testing**

15. **Tests Completos** (Paso 15)
    - Tests de Policy
    - Tests de componentes Index y Show
    - Tests de logging automático
    - Tests de logging manual

---

## 🎨 Componentes UI a Reutilizar

- `x-ui.audit-log-entry` - Adaptar para aceptar `Activity` además de `AuditLog`
- Componentes Flux UI estándar:
  - `flux:heading` - Títulos
  - `flux:button` - Botones
  - `flux:input` - Inputs
  - `flux:select` - Selects
  - `flux:badge` - Badges
  - `flux:table` - Tablas
  - `flux:pagination` - Paginación
  - `flux:card` / `flux:callout` - Cards
  - `flux:field` - Campos estructurados

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
└── activitylog.php
database/
└── migrations/
    └── xxxx_xx_xx_xxxxxx_create_activity_log_table.php
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

## 🔒 Consideraciones de Seguridad

1. **Autorización**: Solo admin y super-admin pueden ver logs
2. **Datos Sensibles**: Configurar exclusión de campos sensibles (passwords, tokens)
3. **Logs Inmutables**: Los logs no se pueden modificar ni eliminar desde la interfaz
4. **Limpieza Automática**: Configurar retención de logs según políticas

---

## 📝 Características Técnicas

### Modelo Activity (Spatie)
- **Relaciones**:
  - `causer()` - MorphTo (polimórfico, puede ser User u otro modelo)
  - `subject()` - MorphTo (polimórfico, el modelo afectado)
- **Campos importantes**:
  - `description`: string (ej: "created", "updated", "deleted")
  - `properties`: JSON con `{attributes: {}, old: {}, custom: {}}`
  - `log_name`: string (para múltiples logs)
- **Índices**: Optimizados por Spatie para consultas frecuentes

### Filtros Disponibles
- **Búsqueda**: En `description` y `subject_type`
- **Modelo**: Filtro por `subject_type` (Program, Call, NewsPost, etc.)
- **Usuario/Causer**: Filtro por causer (usuario que realizó la acción)
- **Descripción**: Filtro por tipo de acción (created, updated, deleted, etc.)
- **Log Name**: Filtro por nombre de log (si se usan múltiples)
- **Rango de Fechas**: Desde/hasta para filtrar por período

### Visualización de Cambios
- Extraer de `properties.old` y `properties.attributes`
- Tabla comparativa mostrando:
  - Campo modificado
  - Valor anterior (desde `old`)
  - Valor nuevo (desde `attributes`)
  - Diferencia destacada
- JSON expandible para vista técnica
- Formateo legible de arrays y objetos JSON

### Logging Automático
```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Program extends Model
{
    use LogsActivity;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'description'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }
}
```

### Logging Manual
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

## 🚀 Orden de Implementación Recomendado

1. **Paso 1-3**: Instalar y configurar Spatie Activitylog, configurar logging automático
2. **Paso 4**: Crear Policy y tests básicos
3. **Paso 5-6**: Implementar Index (componente + vista)
4. **Paso 7-8**: Implementar Show (componente + vista)
5. **Paso 9-10**: Configurar rutas y navegación
6. **Paso 11-12**: Integrar con sistema existente, configurar logging manual
7. **Paso 13-14**: Optimizaciones y mejoras UX
8. **Paso 15**: Tests completos

---

## 🔄 Migración desde Sistema Actual

### Opciones de Migración

1. **Opción A: Mantener Ambas Tablas** (Recomendado para transición)
   - Mantener `audit_logs` para datos históricos
   - Nuevos logs van a `activity_log`
   - Visualizar ambos en el panel (con indicador de origen)

2. **Opción B: Migración Completa**
   - Crear comando Artisan para migración
   - Mapear estructura de datos
   - Deprecar `AuditLog` después de período de gracia

### Mapeo de Datos

| AuditLog | Activity |
|----------|----------|
| `action` | `description` |
| `changes.before` | `properties.old` |
| `changes.after` | `properties.attributes` |
| `user_id` | `causer_id` + `causer_type` |
| `model_id` + `model_type` | `subject_id` + `subject_type` |
| `ip_address` | `properties.ip_address` |
| `user_agent` | `properties.user_agent` |

---

## 📚 Referencias

- [Plan Detallado](paso-3.5.14-plan.md) - Plan completo paso a paso
- [Documentación Spatie Activitylog](https://spatie.be/docs/laravel-activitylog/v4/introduction)
- [Componente UI existente](../components/ui/audit-log-entry.blade.php)
- [Patrones de CRUD existentes](../admin-users-crud.md)

---

**Fecha de Creación**: Diciembre 2025  
**Última Actualización**: Diciembre 2025 (Adaptado para Spatie Activitylog)  
**Estado**: 📋 Plan completado - Pendiente de implementación
