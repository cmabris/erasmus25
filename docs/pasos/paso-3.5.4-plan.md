# Plan de Desarrollo: Paso 3.5.4 - CRUD de Convocatorias en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Convocatorias (Calls) en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Convocatorias en el panel de administración con:
- Listado moderno con tabla interactiva y filtros avanzados
- Formularios de creación y edición completos
- Vista de detalle con gestión de fases y resoluciones
- Funcionalidades avanzadas: cambio de estado, publicación, gestión de fases y resoluciones
- **SoftDeletes**: Las convocatorias nunca se eliminan permanentemente, solo se marcan como eliminadas
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos de Desarrollo (14 Pasos)

### ✅ **Fase 1: Preparación Base**

#### **Paso 1: Implementar SoftDeletes en Call**
- [ ] Verificar que el modelo `Call` tenga el trait `SoftDeletes`
- [ ] Crear migración para añadir columna `deleted_at` a la tabla `calls`
- [ ] Ejecutar migración
- [ ] Actualizar modelo `Call` para usar `SoftDeletes`
- [ ] Verificar que las relaciones funcionen correctamente con SoftDeletes

#### **Paso 2: Actualizar FormRequests con Autorización**
- [ ] Actualizar `StoreCallRequest`:
  - Añadir autorización con `CallPolicy::create()`
  - Añadir mensajes de error personalizados en español e inglés
  - Validar que `estimated_end_date` sea posterior a `estimated_start_date`
  - Validar formato de `destinations` (array de strings)
  - Validar formato de `scoring_table` (array/JSON)
- [ ] Actualizar `UpdateCallRequest`:
  - Añadir autorización con `CallPolicy::update()`
  - Añadir mensajes de error personalizados
  - Validar que `estimated_end_date` sea posterior a `estimated_start_date`
  - Validar formato de `destinations` y `scoring_table`
- [ ] Actualizar `PublishCallRequest` (si existe):
  - Añadir autorización con `CallPolicy::publish()`
  - Validar que la convocatoria pueda ser publicada
- [ ] Verificar que `CallPolicy` tenga todos los métodos necesarios (ya existe)

---

### ✅ **Fase 2: Estructura Base y Listado** (MVP)

#### **Paso 3: Componente Index (Listado)**
- [ ] Crear componente Livewire `Admin\Calls\Index`
- [ ] Implementar propiedades públicas:
  - `Collection $calls` - Lista de convocatorias
  - `string $search = ''` - Búsqueda
  - `string $filterProgram = ''` - Filtro por programa
  - `string $filterAcademicYear = ''` - Filtro por año académico
  - `string $filterType = ''` - Filtro por tipo (alumnado/personal)
  - `string $filterModality = ''` - Filtro por modalidad (corta/larga)
  - `string $filterStatus = ''` - Filtro por estado
  - `string $sortField = 'created_at'` - Campo de ordenación
  - `string $sortDirection = 'desc'` - Dirección de ordenación
  - `string $showDeleted = '0'` - Mostrar eliminados
  - `int $perPage = 15` - Elementos por página
- [ ] Implementar métodos:
  - `mount()` - Inicialización y autorización
  - `updatedSearch()` - Búsqueda reactiva
  - `sortBy($field)` - Ordenación
  - `changeStatus($callId, $status)` - Cambiar estado
  - `publish($callId)` - Publicar convocatoria
  - `delete($callId)` - Eliminar con SoftDeletes (confirmación)
  - `restore($callId)` - Restaurar convocatoria eliminada
  - `forceDelete($callId)` - Eliminar permanentemente (solo super-admin, validar relaciones)
  - `render()` - Renderizado con paginación y eager loading
- [ ] Implementar autorización con `CallPolicy`
- [ ] Crear vista `livewire/admin/calls/index.blade.php`:
  - Header con título y botón crear
  - Breadcrumbs
  - Filtros avanzados (programa, año académico, tipo, modalidad, estado)
  - Búsqueda con debounce
  - Tabla responsive con columnas:
    - Título (con link a show)
    - Programa
    - Año Académico
    - Tipo
    - Modalidad
    - Estado (con badge de color)
    - Fecha publicación
    - Acciones (ver, editar, eliminar, cambiar estado, publicar)
  - Paginación
  - Modales de confirmación (eliminar, restaurar, forceDelete)
  - Estados de carga
  - Estado vacío

#### **Paso 4: Rutas y Navegación**
- [ ] Configurar rutas en `routes/web.php`:
  - `GET /admin/convocatorias` → `Admin\Calls\Index`
  - `GET /admin/convocatorias/crear` → `Admin\Calls\Create`
  - `GET /admin/convocatorias/{call}` → `Admin\Calls\Show`
  - `GET /admin/convocatorias/{call}/editar` → `Admin\Calls\Edit`
- [ ] Actualizar sidebar (`resources/views/components/layouts/app/sidebar.blade.php`)
- [ ] Añadir traducciones necesarias en `lang/es/common.php` y `lang/en/common.php`

---

### ✅ **Fase 3: Creación y Edición**

#### **Paso 5: Componente Create (Crear)**
- [ ] Crear componente Livewire `Admin\Calls\Create`
- [ ] Implementar propiedades públicas:
  - `int $program_id = 0` - Programa seleccionado
  - `int $academic_year_id = 0` - Año académico seleccionado
  - `string $title = ''` - Título
  - `string $slug = ''` - Slug (generado automáticamente)
  - `string $type = 'alumnado'` - Tipo (alumnado/personal)
  - `string $modality = 'corta'` - Modalidad (corta/larga)
  - `int $number_of_places = 1` - Número de plazas
  - `array $destinations = []` - Destinos (array dinámico)
  - `string $estimated_start_date = ''` - Fecha inicio estimada
  - `string $estimated_end_date = ''` - Fecha fin estimada
  - `string $requirements = ''` - Requisitos (textarea)
  - `string $documentation = ''` - Documentación (textarea)
  - `string $selection_criteria = ''` - Criterios de selección (textarea)
  - `array $scoring_table = []` - Baremo (array/JSON)
  - `string $status = 'borrador'` - Estado inicial
- [ ] Implementar métodos:
  - `mount()` - Inicialización y autorización
  - `updatedTitle()` - Generar slug automáticamente
  - `addDestination()` - Añadir destino al array
  - `removeDestination($index)` - Eliminar destino del array
  - `addScoringItem()` - Añadir item al baremo
  - `removeScoringItem($index)` - Eliminar item del baremo
  - `store()` - Guardar convocatoria usando `StoreCallRequest`
- [ ] Crear vista `livewire/admin/calls/create.blade.php`:
  - Header con título y botón volver
  - Breadcrumbs
  - Formulario con Flux UI:
    - Select de Programa (required)
    - Select de Año Académico (required)
    - Input de Título (required, genera slug automático)
    - Input de Slug (opcional, editable)
    - Select de Tipo (alumnado/personal)
    - Select de Modalidad (corta/larga)
    - Input numérico de Número de Plazas
    - Gestión dinámica de Destinos (añadir/eliminar)
    - Inputs de fechas (inicio y fin estimadas)
    - Textareas para Requisitos, Documentación, Criterios de Selección
    - Gestión dinámica de Baremo (tabla con campos: concepto, puntos máx, descripción)
    - Select de Estado inicial
  - Validación en tiempo real
  - Botones de acción (guardar, cancelar)

#### **Paso 6: Componente Edit (Editar)**
- [ ] Crear componente Livewire `Admin\Calls\Edit`
- [ ] Implementar propiedades similares a Create pero con datos precargados
- [ ] Implementar métodos:
  - `mount(Call $call)` - Cargar datos de la convocatoria
  - `update()` - Actualizar usando `UpdateCallRequest`
- [ ] Crear vista `livewire/admin/calls/edit.blade.php`:
  - Similar a Create pero con datos precargados
  - Mostrar información adicional (fecha creación, última actualización)
  - Botones de acción (actualizar, cancelar, eliminar)

---

### ✅ **Fase 4: Vista Detalle y Funcionalidades Avanzadas**

#### **Paso 7: Componente Show (Detalle)**
- [ ] Crear componente Livewire `Admin\Calls\Show`
- [ ] Implementar propiedades:
  - `Call $call` - Convocatoria a mostrar
  - Modales para eliminar, restaurar, forceDelete
- [ ] Implementar métodos:
  - `mount(Call $call)` - Cargar con relaciones (phases, resolutions, program, academicYear)
  - `changeStatus($status)` - Cambiar estado de la convocatoria
  - `publish()` - Publicar convocatoria (establecer `published_at`)
  - `delete()` - Eliminar (SoftDelete)
  - `restore()` - Restaurar
  - `forceDelete()` - Eliminar permanentemente
- [ ] Crear vista `livewire/admin/calls/show.blade.php`:
  - Header con título, estado (badge), y botones de acción
  - Breadcrumbs
  - Información principal:
    - Programa y Año Académico
    - Tipo y Modalidad
    - Número de plazas
    - Destinos
    - Fechas estimadas
    - Requisitos, Documentación, Criterios de Selección
    - Baremo (tabla)
    - Fechas de publicación y cierre
  - Sección de Fases:
    - Listado de fases con orden
    - Botón para crear nueva fase
    - Acciones por fase (editar, marcar como actual, eliminar)
  - Sección de Resoluciones:
    - Listado de resoluciones
    - Botón para crear nueva resolución
    - Acciones por resolución (editar, publicar, eliminar)
  - Estadísticas (número de aplicaciones, fases, resoluciones)
  - Botones de acción (editar, cambiar estado, publicar, eliminar)

#### **Paso 8: Gestión de Estados**
- [ ] Implementar método `changeStatus()` en componente Show
- [ ] Validar transiciones de estado:
  - `borrador` → `abierta` → `cerrada` → `archivada`
  - `borrador` → `en_baremacion` → `resuelta` → `archivada`
- [ ] Actualizar `published_at` cuando se publique
- [ ] Actualizar `closed_at` cuando se cierre
- [ ] Mostrar badges de color según estado

#### **Paso 9: Gestión de Fases (Integración)**
- [ ] En componente Show, añadir sección para gestionar fases
- [ ] Crear componentes modales o enlaces para:
  - Crear nueva fase (usar `StoreCallPhaseRequest`)
  - Editar fase existente (usar `UpdateCallPhaseRequest`)
  - Marcar fase como actual (solo una puede ser actual)
  - Eliminar fase
- [ ] Mostrar listado de fases ordenadas por `order`
- [ ] Mostrar fase actual destacada

#### **Paso 10: Gestión de Resoluciones (Integración)**
- [ ] En componente Show, añadir sección para gestionar resoluciones
- [ ] Crear componentes modales o enlaces para:
  - Crear nueva resolución (usar `StoreResolutionRequest`)
  - Editar resolución existente (usar `UpdateResolutionRequest`)
  - Publicar resolución (establecer `published_at`)
  - Subir PDF de resolución (Laravel Media Library)
  - Eliminar resolución
- [ ] Mostrar listado de resoluciones con información básica
- [ ] Mostrar enlace de descarga para PDFs

---

### ✅ **Fase 5: Optimizaciones y Mejoras**

#### **Paso 11: Optimización de Consultas**
- [ ] Implementar eager loading en Index:
  - `with(['program', 'academicYear', 'creator', 'updater'])`
  - `withCount(['phases', 'resolutions', 'applications'])`
- [ ] Implementar eager loading en Show:
  - Cargar todas las relaciones necesarias
- [ ] Usar índices de base de datos apropiados (ya existen)

#### **Paso 12: Validaciones y Mensajes**
- [ ] Añadir validaciones en tiempo real en formularios
- [ ] Añadir mensajes de éxito/error personalizados
- [ ] Validar relaciones antes de eliminar
- [ ] Mostrar mensajes informativos sobre estados y transiciones

---

### ✅ **Fase 6: Testing**

#### **Paso 13: Tests de Componentes Livewire**
- [ ] Crear `tests/Feature/Livewire/Admin/Calls/IndexTest.php`:
  - Test de autorización
  - Test de listado con filtros
  - Test de búsqueda
  - Test de ordenación
  - Test de cambio de estado
  - Test de publicación
  - Test de eliminación (SoftDelete)
  - Test de restauración
  - Test de forceDelete (solo super-admin)
- [ ] Crear `tests/Feature/Livewire/Admin/Calls/CreateTest.php`:
  - Test de autorización
  - Test de creación exitosa
  - Test de validación de campos requeridos
  - Test de generación automática de slug
  - Test de gestión de destinos
  - Test de gestión de baremo
- [ ] Crear `tests/Feature/Livewire/Admin/Calls/EditTest.php`:
  - Test de autorización
  - Test de edición exitosa
  - Test de validación
  - Test de actualización de relaciones
- [ ] Crear `tests/Feature/Livewire/Admin/Calls/ShowTest.php`:
  - Test de autorización
  - Test de visualización de información
  - Test de cambio de estado
  - Test de publicación
  - Test de eliminación y restauración

#### **Paso 14: Tests de FormRequests**
- [ ] Verificar que los FormRequests validen correctamente
- [ ] Verificar autorización en FormRequests
- [ ] Verificar mensajes de error personalizados

---

## 📝 Notas Técnicas

### Campos del Modelo Call
- `program_id` (required) - Relación con Program
- `academic_year_id` (required) - Relación con AcademicYear
- `title` (required) - Título de la convocatoria
- `slug` (unique, auto-generado) - Slug para URLs
- `type` (enum: alumnado, personal) - Tipo de convocatoria
- `modality` (enum: corta, larga) - Modalidad
- `number_of_places` (integer) - Número de plazas
- `destinations` (JSON) - Array de destinos
- `estimated_start_date` (date, nullable) - Fecha inicio estimada
- `estimated_end_date` (date, nullable) - Fecha fin estimada
- `requirements` (text, nullable) - Requisitos
- `documentation` (text, nullable) - Documentación requerida
- `selection_criteria` (text, nullable) - Criterios de selección
- `scoring_table` (JSON, nullable) - Baremo de evaluación
- `status` (enum: borrador, abierta, cerrada, en_baremacion, resuelta, archivada)
- `published_at` (datetime, nullable) - Fecha de publicación
- `closed_at` (datetime, nullable) - Fecha de cierre
- `created_by` - Usuario creador
- `updated_by` - Usuario que actualizó

### Estados de Convocatoria
- **borrador**: Convocatoria en preparación
- **abierta**: Convocatoria abierta para recibir solicitudes
- **cerrada**: Convocatoria cerrada, ya no acepta solicitudes
- **en_baremacion**: En proceso de baremación
- **resuelta**: Resolución publicada
- **archivada**: Convocatoria archivada

### Relaciones
- `Call` → `Program` (belongsTo)
- `Call` → `AcademicYear` (belongsTo)
- `Call` → `User` (created_by, updated_by)
- `Call` → `CallPhase[]` (hasMany)
- `Call` → `CallApplication[]` (hasMany)
- `Call` → `Resolution[]` (hasMany)

### Validaciones Importantes
- `estimated_end_date` debe ser posterior a `estimated_start_date`
- `destinations` debe ser un array con al menos un elemento
- `scoring_table` debe ser un array válido (opcional)
- `slug` debe ser único (excepto en edición)
- Solo una fase puede estar marcada como `is_current` por convocatoria

---

## 🎨 Componentes UI a Reutilizar

- `x-ui.card` - Tarjetas contenedoras
- `x-ui.breadcrumbs` - Navegación breadcrumb
- `x-ui.search-input` - Input de búsqueda
- `x-ui.empty-state` - Estado vacío
- `flux:button` - Botones con variantes
- `flux:field` - Campos de formulario
- `flux:input` - Inputs
- `flux:select` - Selects
- `flux:textarea` - Textareas
- `flux:badge` - Badges de estado
- `flux:modal` - Modales de confirmación
- `flux:tooltip` - Tooltips informativos

---

## ✅ Checklist Final

- [ ] SoftDeletes implementado en Call
- [ ] FormRequests actualizados con autorización
- [ ] Componente Index funcional con filtros avanzados
- [ ] Componente Create funcional con validación
- [ ] Componente Edit funcional
- [ ] Componente Show funcional con gestión de fases y resoluciones
- [ ] Rutas configuradas
- [ ] Navegación actualizada
- [ ] Traducciones añadidas
- [ ] Tests completos escritos y pasando
- [ ] Optimizaciones de consultas implementadas
- [ ] Código formateado con Pint

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan detallado - Listo para implementación

