# Plan de Desarrollo: Paso 3.5.14 - Auditoría y Logs en Panel de Administración

Este documento establece el plan detallado para desarrollar el sistema completo de Auditoría y Logs en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de visualización de logs de auditoría en el panel de administración con:
- Listado moderno con tabla interactiva y filtros avanzados
- Vista detallada de cada log con información completa
- Filtros por modelo, usuario, acción y fecha
- Visualización de cambios antes/después en formato legible
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4
- Integración con el sistema de auditoría existente (AuditLog model)

---

## 📋 Pasos de Desarrollo (12 Pasos)

### **Fase 1: Preparación Base**

#### **Paso 1: Crear AuditLogPolicy**
- [ ] Crear `app/Policies/AuditLogPolicy.php`
- [ ] Implementar métodos:
  - `viewAny()` - Ver listado (solo admin y super-admin)
  - `view()` - Ver detalle (solo admin y super-admin)
- [ ] **Autorización**: Solo usuarios con rol `admin` o `super-admin` pueden ver logs
- [ ] **Método before()**: Super-admin tiene acceso total
- [ ] Crear tests básicos para la policy en `tests/Feature/Policies/AuditLogPolicyTest.php`

**Nota**: Los logs de auditoría son de solo lectura, no se pueden crear, editar ni eliminar desde la interfaz.

---

### **Fase 2: Componente Index (Listado)**

#### **Paso 2: Crear Componente Livewire Index**
- [ ] Crear componente `Admin\AuditLogs\Index` usando `php artisan make:livewire Admin/AuditLogs/Index`
- [ ] Implementar propiedades públicas:
  - `string $search = ''` - Búsqueda (con `#[Url(as: 'q')]`)
  - `?string $filterModel = null` - Filtro por modelo (con `#[Url(as: 'modelo')]`)
  - `?int $filterUserId = null` - Filtro por usuario (con `#[Url(as: 'usuario')]`)
  - `?string $filterAction = null` - Filtro por acción (con `#[Url(as: 'accion')]`)
  - `?string $filterDateFrom = null` - Filtro fecha desde (con `#[Url(as: 'desde')]`)
  - `?string $filterDateTo = null` - Filtro fecha hasta (con `#[Url(as: 'hasta')]`)
  - `string $sortField = 'created_at'` - Campo de ordenación (con `#[Url(as: 'ordenar')]`)
  - `string $sortDirection = 'desc'` - Dirección de ordenación (con `#[Url(as: 'direccion')]`)
  - `int $perPage = 25` - Elementos por página (con `#[Url(as: 'por-pagina')]`)
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `auditLogs()` - Computed property con paginación, filtros y ordenación
    - Eager loading: `user`, `model`
    - Búsqueda en: `model_type`, `action` (si aplica)
    - Filtros: modelo, usuario, acción, rango de fechas
    - Ordenación por `created_at` desc por defecto
  - `sortBy($field)` - Cambiar ordenación
  - `resetFilters()` - Resetear todos los filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedFilterModel()` - Resetear página al cambiar filtro
  - `updatedFilterUserId()` - Resetear página al cambiar filtro
  - `updatedFilterAction()` - Resetear página al cambiar filtro
  - `updatedFilterDateFrom()` - Resetear página al cambiar fecha
  - `updatedFilterDateTo()` - Resetear página al cambiar fecha
  - `getAvailableModels()` - Obtener modelos únicos de audit_logs
  - `getAvailableUsers()` - Obtener usuarios que tienen logs
  - `getAvailableActions()` - Obtener acciones disponibles (create, update, delete, publish, archive, restore)
  - `getModelDisplayName(?string $modelType)` - Nombre legible del modelo
  - `getActionDisplayName(string $action)` - Nombre legible de la acción
  - `getActionBadgeVariant(string $action)` - Variante de badge para la acción
  - `getModelUrl(?string $modelType, ?int $modelId)` - URL del modelo si existe ruta
  - `render()` - Renderizado con paginación
- [ ] Implementar autorización con `AuditLogPolicy::viewAny()`

#### **Paso 3: Crear Vista Index**
- [ ] Crear vista `resources/views/livewire/admin/audit-logs/index.blade.php`
- [ ] Implementar estructura:
  - **Header**: Título "Auditoría y Logs" con descripción
  - **Breadcrumbs**: Admin > Auditoría y Logs
  - **Filtros avanzados**:
    - Búsqueda (input con debounce)
    - Select de modelo (con opción "Todos")
    - Select de usuario (con opción "Todos")
    - Select de acción (create, update, delete, publish, archive, restore)
    - Date picker "Desde" (fecha)
    - Date picker "Hasta" (fecha)
    - Botón "Limpiar filtros"
  - **Tabla responsive** con columnas:
    - Fecha/Hora (formato legible + diffForHumans)
    - Usuario (nombre + email, con avatar si disponible)
    - Acción (badge con color según acción)
    - Modelo (tipo de modelo)
    - Registro (nombre/título del modelo, enlace si existe)
    - Cambios (resumen truncado, enlace a detalle)
    - IP (si está disponible)
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

### **Fase 3: Componente Show (Detalle)**

#### **Paso 4: Crear Componente Livewire Show**
- [ ] Crear componente `Admin\AuditLogs\Show` usando `php artisan make:livewire Admin/AuditLogs/Show`
- [ ] Implementar propiedades públicas:
  - `AuditLog $auditLog` - El log a mostrar
- [ ] Implementar métodos:
  - `mount(AuditLog $auditLog)` - Inicialización con autorización y eager loading
    - Cargar relaciones: `user`, `model`
  - `getModelDisplayName(?string $modelType)` - Nombre legible del modelo
  - `getActionDisplayName(string $action)` - Nombre legible de la acción
  - `getActionBadgeVariant(string $action)` - Variante de badge
  - `getModelUrl(?string $modelType, ?int $modelId)` - URL del modelo si existe
  - `getModelTitle($model)` - Título del modelo (title, name, o ID)
  - `formatChanges(?array $changes)` - Formatear cambios para visualización
  - `formatJsonForDisplay($data)` - Formatear JSON de forma legible
  - `getUserAgentInfo(?string $userAgent)` - Extraer información del user agent
  - `render()` - Renderizado
- [ ] Implementar autorización con `AuditLogPolicy::view()`

#### **Paso 5: Crear Vista Show**
- [ ] Crear vista `resources/views/livewire/admin/audit-logs/show.blade.php`
- [ ] Implementar estructura:
  - **Header**: 
    - Título "Detalle de Log de Auditoría"
    - Breadcrumbs: Admin > Auditoría y Logs > Detalle
    - Botón "Volver al listado"
  - **Información Principal** (card):
    - ID del log
    - Fecha y hora (formato completo + diffForHumans)
    - Acción (badge con color)
    - Usuario (nombre, email, avatar si disponible)
    - IP Address (si disponible)
    - User Agent (si disponible, con información parseada)
  - **Información del Modelo** (card):
    - Tipo de modelo
    - ID del modelo
    - Nombre/Título del modelo (enlace si existe ruta)
    - Estado actual del modelo (si está disponible)
  - **Cambios Realizados** (card expandible):
    - Si hay cambios, mostrar tabla comparativa:
      - Campo
      - Valor Anterior
      - Valor Nuevo
      - Diferencia destacada
    - Si no hay cambios, mostrar mensaje
    - Formato JSON expandible para vista técnica
  - **Información Técnica** (card colapsable):
    - JSON completo del log
    - User Agent completo
    - Información de la sesión (si disponible)
  - **Acciones**:
    - Botón "Ver registro relacionado" (si existe modelo y ruta)
    - Botón "Ver usuario" (si existe usuario)
    - Botón "Volver al listado"
- [ ] Usar componentes Flux UI:
  - `flux:heading` para títulos
  - `flux:button` para acciones
  - `flux:badge` para estados
  - `flux:card` o `flux:callout` para secciones
  - `flux:field` para información estructurada
- [ ] Diseño responsive con Tailwind CSS v4
- [ ] Soporte para dark mode
- [ ] Usar el componente `x-ui.audit-log-entry` existente si es apropiado

---

### **Fase 4: Rutas y Navegación**

#### **Paso 6: Configurar Rutas**
- [ ] Agregar rutas en `routes/web.php` dentro del grupo `admin`:
  ```php
  // Rutas de Auditoría y Logs
  Route::get('/auditoria', \App\Livewire\Admin\AuditLogs\Index::class)->name('audit-logs.index');
  Route::get('/auditoria/{audit_log}', \App\Livewire\Admin\AuditLogs\Show::class)->name('audit-logs.show');
  ```
- [ ] Verificar que las rutas funcionan correctamente
- [ ] Probar navegación entre Index y Show

#### **Paso 7: Integrar en Navegación**
- [ ] Agregar enlace en sidebar de administración (`resources/views/components/layouts/admin-sidebar.blade.php` o similar)
- [ ] Agregar en sección "Sistema" o "Configuración"
- [ ] Icono apropiado (ej: `heroicon-o-clipboard-document-list` o `heroicon-o-shield-check`)
- [ ] Verificar que solo se muestra para usuarios con permisos adecuados
- [ ] Agregar en breadcrumbs si es necesario

---

### **Fase 5: Optimizaciones y Mejoras**

#### **Paso 8: Optimizaciones de Rendimiento**
- [ ] Implementar índices en consultas frecuentes:
  - Ya existen índices en `audit_logs` para `user_id + created_at` y `model_type + model_id`
  - Verificar que se usan correctamente
- [ ] Implementar eager loading en todas las consultas:
  - `user` (relación BelongsTo)
  - `model` (relación MorphTo)
- [ ] Implementar caché para listados de filtros:
  - Modelos disponibles (caché 1 hora)
  - Usuarios disponibles (caché 30 minutos)
  - Acciones disponibles (sin caché, son estáticas)
- [ ] Optimizar consultas de paginación:
  - Usar `select()` específico si no se necesitan todos los campos
  - Evitar N+1 queries
- [ ] Implementar debounce en búsqueda (500ms)

#### **Paso 9: Mejoras de UX**
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

---

### **Fase 6: Testing**

#### **Paso 10: Tests de Policy**
- [ ] Crear `tests/Feature/Policies/AuditLogPolicyTest.php`
- [ ] Tests a implementar:
  - `test_super_admin_can_view_any_audit_logs()` - Super-admin puede ver todos
  - `test_admin_can_view_any_audit_logs()` - Admin puede ver todos
  - `test_editor_cannot_view_audit_logs()` - Editor no puede ver
  - `test_viewer_cannot_view_audit_logs()` - Viewer no puede ver
  - `test_super_admin_can_view_audit_log()` - Super-admin puede ver detalle
  - `test_admin_can_view_audit_log()` - Admin puede ver detalle
  - `test_editor_cannot_view_audit_log()` - Editor no puede ver detalle
  - `test_viewer_cannot_view_audit_log()` - Viewer no puede ver detalle

#### **Paso 11: Tests de Componente Index**
- [ ] Crear `tests/Feature/Livewire/Admin/AuditLogs/IndexTest.php`
- [ ] Tests a implementar:
  - `test_can_render_index_page()` - Renderiza correctamente
  - `test_requires_authentication()` - Requiere autenticación
  - `test_requires_authorization()` - Requiere autorización
  - `test_can_filter_by_model()` - Filtro por modelo funciona
  - `test_can_filter_by_user()` - Filtro por usuario funciona
  - `test_can_filter_by_action()` - Filtro por acción funciona
  - `test_can_filter_by_date_range()` - Filtro por rango de fechas funciona
  - `test_can_search_logs()` - Búsqueda funciona
  - `test_can_sort_logs()` - Ordenación funciona
  - `test_can_change_per_page()` - Cambio de elementos por página funciona
  - `test_shows_empty_state()` - Muestra estado vacío cuando no hay logs
  - `test_pagination_works()` - Paginación funciona
  - `test_reset_filters_works()` - Resetear filtros funciona
  - `test_shows_user_information()` - Muestra información de usuario
  - `test_shows_model_information()` - Muestra información de modelo
  - `test_shows_action_badges()` - Muestra badges de acción correctamente

#### **Paso 12: Tests de Componente Show**
- [ ] Crear `tests/Feature/Livewire/Admin/AuditLogs/ShowTest.php`
- [ ] Tests a implementar:
  - `test_can_render_show_page()` - Renderiza correctamente
  - `test_requires_authentication()` - Requiere autenticación
  - `test_requires_authorization()` - Requiere autorización
  - `test_shows_log_information()` - Muestra información del log
  - `test_shows_user_information()` - Muestra información del usuario
  - `test_shows_model_information()` - Muestra información del modelo
  - `test_shows_changes_when_available()` - Muestra cambios cuando existen
  - `test_shows_no_changes_message()` - Muestra mensaje cuando no hay cambios
  - `test_formats_changes_correctly()` - Formatea cambios correctamente
  - `test_shows_json_data()` - Muestra datos JSON formateados
  - `test_shows_user_agent_info()` - Muestra información de user agent
  - `test_shows_ip_address()` - Muestra dirección IP
  - `test_links_to_related_model()` - Enlaces a modelo relacionado funcionan
  - `test_links_to_user()` - Enlaces a usuario funcionan
  - `test_handles_missing_model()` - Maneja modelo eliminado correctamente
  - `test_handles_missing_user()` - Maneja usuario eliminado correctamente

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
│   └── AuditLogPolicy.php
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
        └── AuditLogPolicyTest.php
```

---

## 🎨 Componentes UI a Reutilizar

- `x-ui.audit-log-entry` - Componente existente para mostrar entrada de log
- Componentes Flux UI estándar (button, badge, input, select, table, pagination, etc.)

---

## 🔒 Consideraciones de Seguridad

1. **Autorización**: Solo admin y super-admin pueden ver logs
2. **Datos Sensibles**: Considerar ocultar información sensible en cambios (passwords, tokens, etc.)
3. **Rate Limiting**: Considerar rate limiting en exportación si se implementa
4. **Logs Inmutables**: Los logs no se pueden modificar ni eliminar desde la interfaz

---

## 📝 Notas de Implementación

1. **Modelo AuditLog**: Ya existe y está configurado correctamente
2. **Relaciones**: 
   - `user()` - BelongsTo User (nullable)
   - `model()` - MorphTo (polimórfico)
3. **Campos importantes**:
   - `action`: enum (create, update, delete, publish, archive, restore)
   - `changes`: JSON con estructura `{before: {}, after: {}}`
   - `ip_address`: string nullable
   - `user_agent`: text nullable
4. **Índices**: Ya existen índices optimizados
5. **Componente UI existente**: `x-ui.audit-log-entry` puede reutilizarse en Show

---

## ✅ Criterios de Aceptación

- [ ] Policy creada y funcionando
- [ ] Componente Index creado con todos los filtros
- [ ] Componente Show creado con información completa
- [ ] Rutas configuradas y funcionando
- [ ] Navegación integrada en sidebar
- [ ] Tests completos pasando (mínimo 80% cobertura)
- [ ] Diseño responsive y moderno
- [ ] Soporte para dark mode
- [ ] Optimizaciones de rendimiento implementadas
- [ ] Documentación actualizada

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan completado - Pendiente de implementación
