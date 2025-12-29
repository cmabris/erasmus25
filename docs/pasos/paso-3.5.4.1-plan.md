# Plan de Desarrollo: Paso 3.5.4.1 - CRUD de Fases de Convocatorias en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Fases de Convocatorias (CallPhases) en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Fases de Convocatorias en el panel de administración con:
- Listado de fases de una convocatoria con tabla interactiva
- Formularios de creación y edición completos
- Vista de detalle de fase
- Funcionalidades avanzadas: reordenar fases, marcar como actual, validación de fechas
- **SoftDeletes**: Las fases nunca se eliminan permanentemente por defecto
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4
- Rutas anidadas bajo `/admin/convocatorias/{call}/fases`

---

## 📋 Pasos de Desarrollo (12 Pasos)

### **Fase 1: Preparación Base**

#### **Paso 1: Implementar SoftDeletes en CallPhase**
- [ ] Verificar que el modelo `CallPhase` tenga el trait `SoftDeletes`
- [ ] Crear migración para añadir columna `deleted_at` a la tabla `call_phases`
- [ ] Ejecutar migración
- [ ] Actualizar modelo `CallPhase` para usar `SoftDeletes`
- [ ] Verificar que las relaciones funcionen correctamente con SoftDeletes

#### **Paso 2: Actualizar FormRequests con Autorización y Validaciones**
- [ ] Actualizar `StoreCallPhaseRequest`:
  - Verificar autorización con `CallPhasePolicy::create()`
  - Añadir mensajes de error personalizados en español e inglés
  - Validar que `end_date` sea posterior a `start_date` si ambos están presentes
  - Validar que solo una fase pueda ser `is_current` por convocatoria
  - Validar que `order` sea único por convocatoria (opcional, puede auto-generarse)
  - Validar que `call_id` existe y pertenece a una convocatoria válida
- [ ] Actualizar `UpdateCallPhaseRequest`:
  - Verificar autorización con `CallPhasePolicy::update()`
  - Añadir mensajes de error personalizados
  - Validar que `end_date` sea posterior a `start_date`
  - Validar que solo una fase pueda ser `is_current` por convocatoria
  - Validar que `order` sea único por convocatoria (excepto la fase actual)
- [ ] Verificar que `CallPhasePolicy` tenga todos los métodos necesarios (ya existe)

---

### **Fase 2: Estructura Base y Listado**

#### **Paso 3: Componente Index (Listado de Fases)**
- [ ] Crear componente Livewire `Admin\Calls\Phases\Index`
- [ ] Implementar propiedades públicas:
  - `Call $call` - Convocatoria padre (route model binding)
  - `string $search = ''` - Búsqueda por nombre
  - `string $filterPhaseType = ''` - Filtro por tipo de fase
  - `string $filterIsCurrent = ''` - Filtro por fase actual
  - `string $sortField = 'order'` - Campo de ordenación
  - `string $sortDirection = 'asc'` - Dirección de ordenación
  - `string $showDeleted = '0'` - Mostrar eliminados
  - `int $perPage = 15` - Elementos por página
- [ ] Implementar métodos:
  - `mount(Call $call)` - Inicialización y autorización
  - `updatedSearch()` - Búsqueda reactiva
  - `sortBy($field)` - Ordenación
  - `markAsCurrent($phaseId)` - Marcar fase como actual (solo una por convocatoria)
  - `unmarkAsCurrent($phaseId)` - Desmarcar fase como actual
  - `delete($phaseId)` - Eliminar con SoftDeletes (confirmación)
  - `restore($phaseId)` - Restaurar fase eliminada
  - `forceDelete($phaseId)` - Eliminar permanentemente (solo super-admin, validar relaciones)
  - `reorder($phaseId, $direction)` - Reordenar fases (arriba/abajo)
  - `render()` - Renderizado con paginación y eager loading
- [ ] Implementar autorización con `CallPhasePolicy`
- [ ] Crear vista `livewire/admin/calls/phases/index.blade.php`:
  - Header con título, breadcrumbs y botón crear
  - Información de la convocatoria padre (título, programa, año académico)
  - Filtros (tipo de fase, fase actual)
  - Búsqueda con debounce
  - Tabla responsive con columnas:
    - Orden (con botones para reordenar)
    - Tipo de fase (con badge)
    - Nombre
    - Fechas (inicio/fin)
    - Estado actual (badge si es actual)
    - Acciones (ver, editar, eliminar, marcar como actual)
  - Paginación
  - Modales de confirmación (eliminar, restaurar, forceDelete)
  - Estados de carga
  - Estado vacío

#### **Paso 4: Rutas Anidadas y Navegación**
- [ ] Configurar rutas anidadas en `routes/web.php`:
  ```php
  Route::prefix('admin/convocatorias/{call}')->group(function () {
      Route::get('/fases', \App\Livewire\Admin\Calls\Phases\Index::class)
          ->name('calls.phases.index');
      Route::get('/fases/crear', \App\Livewire\Admin\Calls\Phases\Create::class)
          ->name('calls.phases.create');
      Route::get('/fases/{call_phase}', \App\Livewire\Admin\Calls\Phases\Show::class)
          ->name('calls.phases.show');
      Route::get('/fases/{call_phase}/editar', \App\Livewire\Admin\Calls\Phases\Edit::class)
          ->name('calls.phases.edit');
  });
  ```
- [ ] Actualizar componente `Admin\Calls\Show` para añadir enlaces a gestión de fases
- [ ] Añadir traducciones necesarias en `lang/es/common.php` y `lang/en/common.php`

---

### **Fase 3: Creación y Edición**

#### **Paso 5: Componente Create (Crear Fase)**
- [ ] Crear componente Livewire `Admin\Calls\Phases\Create`
- [ ] Implementar propiedades públicas:
  - `Call $call` - Convocatoria padre (route model binding)
  - `int $call_id` - ID de convocatoria (prellenado)
  - `string $phase_type = 'publicacion'` - Tipo de fase
  - `string $name = ''` - Nombre de la fase
  - `?string $description = null` - Descripción
  - `?string $start_date = null` - Fecha inicio
  - `?string $end_date = null` - Fecha fin
  - `bool $is_current = false` - Es fase actual
  - `int $order = 0` - Orden (auto-generado si no se especifica)
- [ ] Implementar métodos:
  - `mount(Call $call)` - Cargar convocatoria y autorizar
  - `updatedIsCurrent()` - Si se marca como actual, desmarcar otras fases
  - `store()` - Guardar fase usando `StoreCallPhaseRequest`
  - `getNextOrder()` - Obtener siguiente orden disponible
- [ ] Crear vista `livewire/admin/calls/phases/create.blade.php`:
  - Header con título, breadcrumbs y botón volver
  - Información de la convocatoria padre
  - Formulario con Flux UI:
    - Select de Tipo de Fase (required, con opciones del enum)
    - Input de Nombre (required)
    - Textarea de Descripción (opcional)
    - Inputs de fechas (inicio y fin, opcionales)
    - Switch de "Es fase actual" (con advertencia si ya hay una fase actual)
    - Input numérico de Orden (opcional, auto-generado)
  - Validación en tiempo real
  - Botones de acción (guardar, cancelar)

#### **Paso 6: Componente Edit (Editar Fase)**
- [ ] Crear componente Livewire `Admin\Calls\Phases\Edit`
- [ ] Implementar propiedades similares a Create pero con datos precargados
- [ ] Implementar métodos:
  - `mount(Call $call, CallPhase $callPhase)` - Cargar datos de la fase
  - `updatedIsCurrent()` - Si se marca como actual, desmarcar otras fases
  - `update()` - Actualizar usando `UpdateCallPhaseRequest`
- [ ] Crear vista `livewire/admin/calls/phases/edit.blade.php`:
  - Similar a Create pero con datos precargados
  - Mostrar información adicional (fecha creación, última actualización)
  - Botones de acción (actualizar, cancelar, eliminar)

---

### **Fase 4: Vista Detalle y Funcionalidades Avanzadas**

#### **Paso 7: Componente Show (Detalle de Fase)**
- [ ] Crear componente Livewire `Admin\Calls\Phases\Show`
- [ ] Implementar propiedades:
  - `Call $call` - Convocatoria padre
  - `CallPhase $callPhase` - Fase a mostrar
  - Modales para eliminar, restaurar, forceDelete
- [ ] Implementar métodos:
  - `mount(Call $call, CallPhase $callPhase)` - Cargar con relaciones (call, resolutions)
  - `markAsCurrent()` - Marcar fase como actual
  - `unmarkAsCurrent()` - Desmarcar fase como actual
  - `delete()` - Eliminar (SoftDelete)
  - `restore()` - Restaurar
  - `forceDelete()` - Eliminar permanentemente
- [ ] Crear vista `livewire/admin/calls/phases/show.blade.php`:
  - Header con título, estado actual (badge), y botones de acción
  - Breadcrumbs (Convocatorias > {Call} > Fases > {Phase})
  - Información principal:
    - Convocatoria padre (con enlace)
    - Tipo de fase (con badge)
    - Nombre y descripción
    - Fechas (inicio y fin)
    - Estado actual (badge)
    - Orden
    - Fechas de creación y actualización
  - Sección de Resoluciones:
    - Listado de resoluciones asociadas a esta fase
    - Botón para crear nueva resolución (enlace a CRUD de resoluciones)
  - Estadísticas (número de resoluciones)
  - Botones de acción (editar, marcar como actual, eliminar)

#### **Paso 8: Funcionalidades Avanzadas**
- [ ] Implementar reordenamiento de fases:
  - Método `moveUp($phaseId)` - Mover fase hacia arriba
  - Método `moveDown($phaseId)` - Mover fase hacia abajo
  - Validar que no se pueda mover fuera de los límites
  - Actualizar campo `order` de todas las fases afectadas
- [ ] Implementar validación de fechas entre fases:
  - Validar que las fechas de una fase no se solapen con otras fases
  - Mostrar advertencias si hay solapamientos
- [ ] Implementar auto-generación de orden:
  - Si no se especifica orden, asignar el siguiente disponible
  - Al crear nueva fase, asignar orden = max(order) + 1

---

### **Fase 5: Optimizaciones y Mejoras**

#### **Paso 9: Optimización de Consultas**
- [ ] Implementar eager loading en Index:
  - `with(['call', 'resolutions'])`
  - `withCount(['resolutions'])`
- [ ] Implementar eager loading en Show:
  - Cargar todas las relaciones necesarias
- [ ] Usar índices de base de datos apropiados (ya existen)

#### **Paso 10: Validaciones y Mensajes**
- [ ] Añadir validaciones en tiempo real en formularios
- [ ] Añadir mensajes de éxito/error personalizados
- [ ] Validar relaciones antes de eliminar
- [ ] Mostrar mensajes informativos sobre estados y transiciones
- [ ] Validar que no se pueda eliminar una fase si tiene resoluciones asociadas

---

### **Fase 6: Testing**

#### **Paso 11: Tests de Componentes Livewire**
- [ ] Crear `tests/Feature/Livewire/Admin/Calls/Phases/IndexTest.php`:
  - Test de autorización
  - Test de listado con filtros
  - Test de búsqueda
  - Test de ordenación
  - Test de marcar como actual (solo una por convocatoria)
  - Test de reordenamiento
  - Test de eliminación (SoftDelete)
  - Test de restauración
  - Test de forceDelete (solo super-admin)
- [ ] Crear `tests/Feature/Livewire/Admin/Calls/Phases/CreateTest.php`:
  - Test de autorización
  - Test de creación exitosa
  - Test de validación de campos requeridos
  - Test de auto-generación de orden
  - Test de marcar como actual (desmarca otras)
  - Test de validación de fechas
- [ ] Crear `tests/Feature/Livewire/Admin/Calls/Phases/EditTest.php`:
  - Test de autorización
  - Test de edición exitosa
  - Test de validación
  - Test de actualización de relaciones
- [ ] Crear `tests/Feature/Livewire/Admin/Calls/Phases/ShowTest.php`:
  - Test de autorización
  - Test de visualización de información
  - Test de marcar como actual
  - Test de eliminación y restauración

#### **Paso 12: Tests de FormRequests**
- [ ] Verificar que los FormRequests validen correctamente
- [ ] Verificar autorización en FormRequests
- [ ] Verificar mensajes de error personalizados
- [ ] Verificar validación de unicidad de fase actual

---

## 📝 Notas Técnicas

### Campos del Modelo CallPhase
- `call_id` (required) - Relación con Call
- `phase_type` (required, enum) - Tipo de fase: publicacion, solicitudes, provisional, alegaciones, definitivo, renuncias, lista_espera
- `name` (required) - Nombre de la fase
- `description` (nullable) - Descripción de la fase
- `start_date` (nullable, date) - Fecha inicio
- `end_date` (nullable, date) - Fecha fin (debe ser posterior a start_date)
- `is_current` (boolean, default: false) - Es fase actual (solo una por convocatoria)
- `order` (integer, default: 0) - Orden de la fase

### Tipos de Fase (phase_type)
- **publicacion**: Fase de publicación de la convocatoria
- **solicitudes**: Fase de recepción de solicitudes
- **provisional**: Listado provisional
- **alegaciones**: Periodo de alegaciones
- **definitivo**: Listado definitivo
- **renuncias**: Gestión de renuncias
- **lista_espera**: Lista de espera

### Relaciones
- `CallPhase` → `Call` (belongsTo)
- `CallPhase` → `Resolution[]` (hasMany)

### Validaciones Importantes
- `end_date` debe ser posterior a `start_date` si ambos están presentes
- Solo una fase puede estar marcada como `is_current` por convocatoria
- `order` debe ser único por convocatoria (opcional, puede auto-generarse)
- No se puede eliminar una fase si tiene resoluciones asociadas

### Estructura de Rutas Anidadas
```
/admin/convocatorias/{call}/fases                    → Index
/admin/convocatorias/{call}/fases/crear              → Create
/admin/convocatorias/{call}/fases/{call_phase}       → Show
/admin/convocatorias/{call}/fases/{call_phase}/editar → Edit
```

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
- `flux:switch` - Switch para is_current
- `flux:tooltip` - Tooltips informativos

---

## ✅ Checklist Final

- [ ] SoftDeletes implementado en CallPhase
- [ ] FormRequests actualizados con autorización y validaciones
- [ ] Componente Index funcional con filtros avanzados
- [ ] Componente Create funcional con validación
- [ ] Componente Edit funcional
- [ ] Componente Show funcional
- [ ] Rutas anidadas configuradas
- [ ] Navegación actualizada (enlaces desde Show de Call)
- [ ] Traducciones añadidas
- [ ] Funcionalidad de reordenamiento implementada
- [ ] Validación de fase actual (solo una por convocatoria)
- [ ] Tests completos escritos y pasando
- [ ] Optimizaciones de consultas implementadas
- [ ] Código formateado con Pint

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan detallado - Listo para implementación

