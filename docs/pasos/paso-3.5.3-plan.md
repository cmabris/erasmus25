# Plan de Desarrollo: Paso 3.5.3 - CRUD de Años Académicos en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Años Académicos en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Años Académicos en el panel de administración con:
- Listado moderno con tabla interactiva
- Formularios de creación y edición
- Funcionalidades avanzadas: marcar año actual (solo uno puede ser actual)
- **SoftDeletes**: Los años académicos nunca se eliminan permanentemente, solo se marcan como eliminados
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos de Desarrollo (10 Pasos)

### ✅ **Fase 1: Preparación Base**

#### **Paso 1: Implementar SoftDeletes en AcademicYear**
- [ ] Verificar que el modelo `AcademicYear` tenga el trait `SoftDeletes`
- [ ] Crear migración para añadir columna `deleted_at` a la tabla `academic_years`
- [ ] Ejecutar migración
- [ ] Actualizar modelo `AcademicYear` para usar `SoftDeletes`
- [ ] Verificar que las relaciones funcionen correctamente con SoftDeletes

#### **Paso 2: Actualizar FormRequests con Autorización**
- [ ] Actualizar `StoreAcademicYearRequest`:
  - Añadir autorización con `AcademicYearPolicy::create()`
  - Añadir mensajes de error personalizados en español e inglés
  - Verificar validación de formato de año (`YYYY-YYYY`)
  - Validar que `end_date` sea posterior a `start_date`
- [ ] Actualizar `UpdateAcademicYearRequest`:
  - Añadir autorización con `AcademicYearPolicy::update()`
  - Añadir mensajes de error personalizados
  - Verificar validación de formato de año único (ignorando el registro actual)
  - Validar que `end_date` sea posterior a `start_date`
- [ ] Verificar que `AcademicYearPolicy` tenga todos los métodos necesarios (ya existe)

---

### ✅ **Fase 2: Estructura Base y Listado** (MVP)

#### **Paso 3: Componente Index (Listado)**
- [ ] Crear componente Livewire `Admin\AcademicYears\Index`
- [ ] Implementar propiedades públicas:
  - `string $search = ''` - Búsqueda por año
  - `string $sortField = 'year'` - Campo de ordenación (year, start_date, end_date)
  - `string $sortDirection = 'desc'` - Dirección de ordenación (desc por defecto para ver más recientes primero)
  - `string $showDeleted = '0'` - Filtro para mostrar eliminados ('0' = no, '1' = sí)
  - `int $perPage = 15` - Elementos por página
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `?int $academicYearToDelete = null` - ID del año a eliminar
  - `bool $showRestoreModal = false` - Modal de confirmación de restauración
  - `?int $academicYearToRestore = null` - ID del año a restaurar
  - `bool $showForceDeleteModal = false` - Modal de confirmación de eliminación permanente
  - `?int $academicYearToForceDelete = null` - ID del año a eliminar permanentemente
- [ ] Implementar métodos:
  - `mount()` - Inicialización y verificación de permisos
  - `academicYears()` (Computed) - Listado paginado con filtros
  - `updatedSearch()` - Resetear página al buscar
  - `sortBy($field)` - Ordenación por campo
  - `updatedShowDeleted()` - Resetear página al cambiar filtro
  - `confirmDelete($academicYearId)` - Mostrar modal de confirmación
  - `delete()` - Eliminar con SoftDeletes (validar relaciones)
  - `confirmRestore($academicYearId)` - Mostrar modal de restauración
  - `restore()` - Restaurar año académico eliminado
  - `confirmForceDelete($academicYearId)` - Mostrar modal de eliminación permanente
  - `forceDelete()` - Eliminar permanentemente (solo super-admin, validar relaciones)
  - `resetFilters()` - Resetear filtros a valores por defecto
  - `canDeleteAcademicYear(AcademicYear $academicYear)` - Verificar si puede eliminarse
  - `render()` - Renderizado con paginación
- [ ] Implementar autorización con `AcademicYearPolicy`
- [ ] Crear vista `livewire/admin/academic-years/index.blade.php`:
  - Header con título y botón de crear
  - Breadcrumbs con `x-ui.breadcrumbs`
  - Búsqueda con componente `x-ui.search-input`
  - Filtro para mostrar eliminados (solo admin)
  - Tabla responsive con Flux UI:
    - Columna: Año académico (formato YYYY-YYYY)
    - Columna: Fecha inicio
    - Columna: Fecha fin
    - Columna: Año actual (badge indicador)
    - Columna: Relaciones (convocatorias, noticias, documentos) con contadores
    - Columna: Fechas (creación, actualización)
    - Columna: Acciones (ver, editar, marcar como actual, eliminar/restaurar)
  - Indicador visual de años académicos eliminados
  - Paginación
  - Estado vacío con `x-ui.empty-state`
  - Modales de confirmación (eliminar, restaurar, eliminar permanentemente)

#### **Paso 4: Rutas y Navegación**
- [ ] Añadir rutas en `routes/web.php`:
  - `GET /admin/anios-academicos` → `Admin\AcademicYears\Index`
  - `GET /admin/anios-academicos/crear` → `Admin\AcademicYears\Create`
  - `GET /admin/anios-academicos/{academic_year}` → `Admin\AcademicYears\Show`
  - `GET /admin/anios-academicos/{academic_year}/editar` → `Admin\AcademicYears\Edit`
- [ ] Actualizar sidebar de administración para incluir enlace a años académicos
- [ ] Añadir traducciones necesarias en `lang/{es,en}/common.php`:
  - Títulos de páginas
  - Mensajes de éxito/error
  - Etiquetas de formularios
  - Botones de acción

---

### ✅ **Fase 3: Creación y Edición**

#### **Paso 5: Componente Create (Crear)**
- [ ] Crear componente Livewire `Admin\AcademicYears\Create`
- [ ] Implementar propiedades públicas:
  - `string $year = ''` - Año académico (formato YYYY-YYYY)
  - `string $start_date = ''` - Fecha de inicio
  - `string $end_date = ''` - Fecha de fin
  - `bool $is_current = false` - Marcar como año actual
- [ ] Implementar métodos:
  - `mount()` - Inicialización y verificación de permisos
  - `updatedYear()` - Validar formato de año automáticamente
  - `updatedStartDate()` - Validar que end_date sea posterior
  - `updatedEndDate()` - Validar que sea posterior a start_date
  - `updatedIsCurrent()` - Si se marca como actual, desmarcar otros años actuales
  - `store()` - Guardar usando `StoreAcademicYearRequest`
  - `render()` - Renderizado
- [ ] Implementar autorización con `AcademicYearPolicy::create()`
- [ ] Crear vista `livewire/admin/academic-years/create.blade.php`:
  - Formulario con Flux UI (`flux:field`, `flux:input`, `flux:checkbox`)
  - Validación en tiempo real con `wire:model.live`
  - Campo año con formato YYYY-YYYY y ayuda visual
  - Campos de fecha con date picker
  - Checkbox para marcar como año actual con advertencia si ya existe uno
  - Botones de acción (guardar, cancelar)
  - Breadcrumbs
  - Mensajes de validación claros

#### **Paso 6: Componente Edit (Editar)**
- [ ] Crear componente Livewire `Admin\AcademicYears\Edit`
- [ ] Implementar propiedades públicas (igual que Create):
  - `AcademicYear $academicYear` - Año académico a editar
  - `string $year = ''`
  - `string $start_date = ''`
  - `string $end_date = ''`
  - `bool $is_current = false`
- [ ] Implementar métodos:
  - `mount(AcademicYear $academicYear)` - Cargar datos del año académico
  - `updatedYear()` - Validar formato de año
  - `updatedStartDate()` - Validar fechas
  - `updatedEndDate()` - Validar fechas
  - `updatedIsCurrent()` - Si se marca como actual, desmarcar otros años actuales
  - `update()` - Actualizar usando `UpdateAcademicYearRequest`
  - `render()` - Renderizado
- [ ] Implementar autorización con `AcademicYearPolicy::update()`
- [ ] Crear vista `livewire/admin/academic-years/edit.blade.php`:
  - Similar a Create pero con datos precargados
  - Mostrar información de relaciones si existen (solo lectura)
  - Advertencia si se intenta cambiar el año actual y hay relaciones

---

### ✅ **Fase 4: Vista Detalle y Funcionalidades Avanzadas**

#### **Paso 7: Componente Show (Detalle)**
- [ ] Crear componente Livewire `Admin\AcademicYears\Show`
- [ ] Implementar propiedades públicas:
  - `AcademicYear $academicYear` - Año académico a mostrar
  - `bool $showDeleteModal = false` - Modal de eliminación
  - `bool $showRestoreModal = false` - Modal de restauración
  - `bool $showForceDeleteModal = false` - Modal de eliminación permanente
- [ ] Implementar métodos:
  - `mount(AcademicYear $academicYear)` - Cargar año académico y relaciones
  - `toggleCurrent()` - Marcar/desmarcar como año actual
  - `confirmDelete()` - Mostrar modal de eliminación
  - `delete()` - Eliminar con SoftDeletes (validar relaciones)
  - `confirmRestore()` - Mostrar modal de restauración
  - `restore()` - Restaurar año académico eliminado
  - `confirmForceDelete()` - Mostrar modal de eliminación permanente
  - `forceDelete()` - Eliminar permanentemente (solo super-admin, validar relaciones)
  - `render()` - Renderizado
- [ ] Implementar autorización con `AcademicYearPolicy::view()`
- [ ] Crear vista `livewire/admin/academic-years/show.blade.php`:
  - Card principal con información del año académico
  - Badge indicando si es el año actual
  - Sección de estadísticas (convocatorias, noticias, documentos) con contadores
  - Listado de convocatorias relacionadas (últimas 5)
  - Listado de noticias relacionadas (últimas 5)
  - Listado de documentos relacionados (últimos 5)
  - Botones de acción (editar, marcar como actual, eliminar con SoftDeletes, restaurar)
  - Mostrar estado de eliminación si está eliminado
  - Validar relaciones antes de permitir forceDelete
  - Breadcrumbs

#### **Paso 8: Funcionalidad "Marcar como Año Actual"**
- [ ] Implementar lógica en modelo `AcademicYear`:
  - Método `markAsCurrent()` - Marca este año como actual y desmarca otros
  - Scope `current()` - Obtener el año actual
  - Validación en observer o mutator para asegurar que solo uno sea actual
- [ ] Actualizar componentes Livewire:
  - En `Create`: Si se marca `is_current = true`, desmarcar otros automáticamente
  - En `Edit`: Igual que Create
  - En `Show`: Botón para marcar/desmarcar como actual
  - En `Index`: Botón rápido para marcar como actual desde el listado
- [ ] Añadir confirmación cuando se cambia el año actual y hay relaciones existentes

---

### ✅ **Fase 5: UX y Optimización**

#### **Paso 9: Mejoras de UX**
- [ ] Añadir confirmaciones para acciones destructivas (eliminar, forceDelete)
- [ ] Mensajes claros sobre SoftDelete vs ForceDelete
- [ ] Advertencias cuando se intenta forceDelete con relaciones existentes
- [ ] Implementar notificaciones de éxito/error con Flux UI
- [ ] Añadir estados de carga (`wire:loading`)
- [ ] Mejorar responsive design para móviles
- [ ] Añadir tooltips informativos:
  - Formato de año académico (YYYY-YYYY)
  - Qué significa "año actual"
  - Relaciones con otras entidades
- [ ] Implementar búsqueda por año (formato YYYY-YYYY)
- [ ] Validación visual de fechas (mostrar error si end_date < start_date)

#### **Paso 10: Optimización**
- [ ] Implementar eager loading para relaciones (calls, newsPosts, documents)
- [ ] Añadir índices de base de datos si es necesario (ya existe índice único en `year`)
- [ ] Optimizar consultas de búsqueda
- [ ] Implementar caché para el año actual si se consulta frecuentemente

---

### ✅ **Fase 6: Calidad y Documentación**

#### **Paso 11: Tests**
- [ ] Crear test `Admin\AcademicYears\IndexTest`:
  - Verificar autorización
  - Verificar listado de años académicos
  - Verificar búsqueda
  - Verificar ordenación
  - Verificar filtros (eliminados)
  - Verificar paginación
  - Verificar eliminación con SoftDeletes
  - Verificar restauración
  - Verificar forceDelete (solo super-admin, validar relaciones)
- [ ] Crear test `Admin\AcademicYears\CreateTest`:
  - Verificar autorización
  - Verificar creación exitosa
  - Verificar validación (formato de año, fechas)
  - Verificar que al marcar como actual se desmarcan otros
- [ ] Crear test `Admin\AcademicYears\EditTest`:
  - Verificar autorización
  - Verificar edición exitosa
  - Verificar validación
  - Verificar cambio de año actual
- [ ] Crear test `Admin\AcademicYears\ShowTest`:
  - Verificar autorización
  - Verificar visualización
  - Verificar marcar como actual
  - Verificar eliminación con SoftDeletes
  - Verificar restauración
  - Verificar forceDelete (solo super-admin, validar relaciones)

#### **Paso 12: Documentación**
- [ ] Documentar componentes creados
- [ ] Actualizar documentación general
- [ ] Crear resumen del desarrollo
- [ ] Documentar funcionalidad especial de "año actual"
- [ ] Actualizar `planificacion_pasos.md` marcando el paso 3.5.3 como completado

---

## 🏗️ Estructura de Archivos

```
app/Livewire/Admin/AcademicYears/
  ├── Index.php                    [NUEVO]
  ├── Create.php                   [NUEVO]
  ├── Edit.php                     [NUEVO]
  └── Show.php                     [NUEVO]

resources/views/livewire/admin/academic-years/
  ├── index.blade.php              [NUEVO]
  ├── create.blade.php             [NUEVO]
  ├── edit.blade.php               [NUEVO]
  └── show.blade.php               [NUEVO]

app/Http/Requests/
  ├── StoreAcademicYearRequest.php  [MODIFICAR - añadir autorización]
  └── UpdateAcademicYearRequest.php [MODIFICAR - añadir autorización]

app/Models/
  └── AcademicYear.php             [MODIFICAR - añadir SoftDeletes]

database/migrations/
  └── YYYY_MM_DD_HHMMSS_add_soft_deletes_to_academic_years_table.php [NUEVO]

routes/web.php                     [MODIFICAR]

lang/{es,en}/common.php            [MODIFICAR]

tests/Feature/Livewire/Admin/AcademicYears/
  ├── IndexTest.php                [NUEVO]
  ├── CreateTest.php               [NUEVO]
  ├── EditTest.php                 [NUEVO]
  └── ShowTest.php                 [NUEVO]
```

---

## 🎨 Diseño Visual

### Vista Index (Listado)
```
┌─────────────────────────────────────────────────────────────┐
│  Años Académicos                    [+ Crear Año Académico] │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  [🔍 Buscar...]  [Mostrar eliminados: ☐]  [Ordenar] │ │
│  └───────────────────────────────────────────────────────┘ │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  Tabla:                                               │ │
│  │  Año      │ Inicio   │ Fin      │ Actual │ Acciones │ │
│  │  ─────────┼──────────┼──────────┼────────┼───────── │ │
│  │  2024-2025│ 01/09/24 │ 31/08/25 │ ✅     │ [👁️][✏️]│ │
│  │  2023-2024│ 01/09/23 │ 31/08/24 │        │ [👁️][✏️]│ │
│  └───────────────────────────────────────────────────────┘ │
│  [Paginación]                                               │
└─────────────────────────────────────────────────────────────┘
```

### Vista Create/Edit (Formulario)
```
┌─────────────────────────────────────────────────────────────┐
│  Crear/Editar Año Académico                                  │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  Año académico:  [2024-2025]  *                        │ │
│  │                 (Formato: YYYY-YYYY)                    │ │
│  │  Fecha inicio:   [📅 01/09/2024]  *                     │ │
│  │  Fecha fin:      [📅 31/08/2025]  *                     │ │
│  │  Marcar como año actual: [✓]                            │ │
│  │  ⚠️ Si marca este año como actual, se desmarcará el    │ │
│  │     año actual anterior.                                 │ │
│  └───────────────────────────────────────────────────────┘ │
│  [Cancelar]  [Guardar]                                      │
└─────────────────────────────────────────────────────────────┘
```

### Vista Show (Detalle)
```
┌─────────────────────────────────────────────────────────────┐
│  Año Académico: 2024-2025                    [✅ Año Actual]│
│  ┌───────────────────────────────────────────────────────┐ │
│  │  Fecha inicio: 01/09/2024                               │ │
│  │  Fecha fin:    31/08/2025                               │ │
│  │  Creado:       15/01/2024                               │ │
│  │  Actualizado:  20/01/2024                               │ │
│  └───────────────────────────────────────────────────────┘ │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  Estadísticas:                                         │ │
│  │  • Convocatorias: 8                                    │ │
│  │  • Noticias: 15                                       │ │
│  │  • Documentos: 12                                     │ │
│  └───────────────────────────────────────────────────────┘ │
│  [Editar]  [Marcar como Actual]  [Eliminar]                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚦 Priorización Recomendada

### **Sprint 1** (MVP - 2-3 días)
- ✅ Pasos 1, 2, 3, 4, 5, 6
- CRUD básico funcional con SoftDeletes

### **Sprint 2** (Funcionalidades Avanzadas - 1 día)
- ✅ Pasos 7, 8
- Vista detalle y funcionalidad de año actual

### **Sprint 3** (Pulido - 1 día)
- ✅ Pasos 9, 10, 11, 12
- Optimización, tests y documentación

**Total estimado: 4-5 días de desarrollo**

---

## 🔧 Tecnologías y Componentes a Usar

- **Livewire 3**: Componentes reactivos
- **Flux UI v2**: Componentes UI base (`flux:field`, `flux:input`, `flux:button`, `flux:checkbox`, `flux:callout`, `flux:badge`)
- **Tailwind CSS v4**: Estilos y responsive
- **Heroicons**: Iconos
- **Laravel Permission**: Verificación de permisos
- **Alpine.js**: Interactividad (modales, confirmaciones)

---

## 📝 Notas Importantes

1. **Reutilización**: Aprovechar componentes existentes (`x-ui.card`, `x-ui.stat-card`, `x-ui.search-input`, `x-ui.empty-state`, `x-ui.breadcrumbs`)
2. **Consistencia**: Mantener estilo similar al CRUD de Programas
3. **Performance**: Optimizar consultas desde el inicio, usar eager loading
4. **Seguridad**: Verificar permisos en cada acción, validar datos
5. **Año Actual**: Solo un año puede ser actual a la vez. Al marcar uno como actual, automáticamente se desmarca el anterior.
6. **Validación de Relaciones**: Antes de eliminar permanentemente, verificar que no haya convocatorias, noticias o documentos asociados.
7. **Formato de Año**: El formato debe ser YYYY-YYYY (ej: 2024-2025). Validar con regex.

---

## 🎯 Resultado Esperado

Un CRUD completo y moderno de Años Académicos que:
- ✅ Permite gestionar años académicos de forma intuitiva
- ✅ Incluye funcionalidad especial de "año actual" (solo uno activo)
- ✅ Es responsive y accesible
- ✅ Sigue las mejores prácticas de UX/UI
- ✅ Está completamente testeado
- ✅ Está documentado
- ✅ Implementa SoftDeletes correctamente
- ✅ Valida relaciones antes de eliminación permanente

---

## 🔄 Diferencias con el CRUD de Programas

1. **No tiene imágenes**: Los años académicos no tienen imágenes asociadas
2. **No tiene traducciones**: Los años académicos no necesitan traducciones (el formato YYYY-YYYY es universal)
3. **No tiene ordenamiento manual**: Los años académicos se ordenan por año (desc por defecto)
4. **Funcionalidad especial**: Marcar como "año actual" (solo uno puede ser actual)
5. **Campos diferentes**: `year`, `start_date`, `end_date`, `is_current` (no tiene `code`, `slug`, `description`, `order`, `is_active`)
6. **Validación de formato**: El campo `year` debe seguir el formato YYYY-YYYY

---

**📄 Documento Completo**: Este plan detallado para el desarrollo del paso 3.5.3

**Fecha**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Listo para implementación

