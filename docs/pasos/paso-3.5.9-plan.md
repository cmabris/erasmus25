# Plan de Desarrollo: Paso 3.5.9 - Gestión de Eventos en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Eventos Erasmus+ en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Eventos Erasmus+ en el panel de administración con:
- Listado moderno con tabla interactiva y vista de calendario
- Formularios de creación y edición con gestión de fechas
- Vista de detalle con información completa
- **SoftDeletes**: Los eventos nunca se eliminan permanentemente, solo se marcan como eliminados
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- Vista de calendario interactiva (mes/semana/día)
- Asociación con programas y convocatorias
- Subida de imágenes (Laravel Media Library)
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos de Desarrollo (16 Pasos en 8 Fases)

### **Fase 1: Preparación Base**

#### **Paso 1: Implementar SoftDeletes en ErasmusEvent**
- [ ] Crear migración para añadir columna `deleted_at` a la tabla `erasmus_events`
- [ ] Ejecutar migración
- [ ] Actualizar modelo `ErasmusEvent` para usar el trait `SoftDeletes`
- [ ] Verificar que las relaciones funcionen correctamente con SoftDeletes
- [ ] Actualizar factory si es necesario
- [ ] Actualizar scopes para excluir eliminados por defecto

#### **Paso 2: Implementar MediaLibrary en ErasmusEvent**
- [ ] Actualizar modelo `ErasmusEvent` para implementar `HasMedia` interface
- [ ] Añadir trait `InteractsWithMedia` al modelo
- [ ] Crear método `registerMediaCollections()`:
  - Colección `'images'` para imágenes del evento
- [ ] Crear método `registerMediaConversions()`:
  - Conversión `'thumbnail'` (150x150)
  - Conversión `'medium'` (500x500)
  - Conversión `'large'` (1200x1200)
- [ ] Verificar que las relaciones funcionen correctamente

#### **Paso 3: Actualizar FormRequests**
- [ ] Actualizar `StoreErasmusEventRequest`:
  - Añadir autorización con `ErasmusEventPolicy::create()`
  - Añadir validación de imagen (`image`, `mimes:jpeg,png,jpg,webp,gif`, `max:5120`)
  - Añadir mensajes de error personalizados en español e inglés
  - Validar que `call_id` pertenezca al `program_id` si ambos están presentes
  - Validar que `end_date` sea posterior a `start_date`
- [ ] Actualizar `UpdateErasmusEventRequest`:
  - Añadir autorización con `ErasmusEventPolicy::update()`
  - Añadir validación de imagen (opcional)
  - Añadir mensajes de error personalizados
  - Mismas validaciones de relaciones que Store
- [ ] Verificar que `ErasmusEventPolicy` tenga todos los métodos necesarios (ya existe)

---

### **Fase 2: Componente Index (Listado y Calendario)**

#### **Paso 4: Componente Index - Estructura Base**
- [ ] Crear componente Livewire `Admin\Events\Index`
- [ ] Implementar propiedades públicas:
  - `string $viewMode = 'list'` - Modo de vista: 'list' o 'calendar' (con `#[Url]`)
  - `string $search = ''` - Búsqueda (con `#[Url]`)
  - `string $sortField = 'start_date'` - Campo de ordenación (con `#[Url]`)
  - `string $sortDirection = 'desc'` - Dirección de ordenación (con `#[Url]`)
  - `string $showDeleted = '0'` - Filtro de eliminados (con `#[Url]`)
  - `?int $programFilter = null` - Filtro por programa (con `#[Url]`)
  - `?int $callFilter = null` - Filtro por convocatoria (con `#[Url]`)
  - `string $eventTypeFilter = ''` - Filtro por tipo de evento (con `#[Url]`)
  - `string $dateFilter = ''` - Filtro por fecha (con `#[Url]`)
  - `int $perPage = 15` - Elementos por página (con `#[Url]`)
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `?int $eventToDelete = null` - ID de evento a eliminar
  - `bool $showRestoreModal = false` - Modal de confirmación de restauración
  - `?int $eventToRestore = null` - ID de evento a restaurar
  - `bool $showForceDeleteModal = false` - Modal de confirmación de eliminación permanente
  - `?int $eventToForceDelete = null` - ID de evento a eliminar permanentemente
  - **Para vista calendario:**
    - `string $currentDate = ''` - Fecha actual del calendario (con `#[Url]`)
    - `string $calendarView = 'month'` - Vista del calendario: 'month', 'week', 'day' (con `#[Url]`)
- [ ] Implementar métodos base:
  - `mount()` - Inicialización con autorización
  - `events()` - Computed property con paginación, filtros y ordenación (para vista lista)
  - `calendarEvents()` - Computed property para eventos del calendario
  - `calendarDays()` - Computed property para días del mes (vista calendario)
  - `weekDays()` - Computed property para días de la semana (vista calendario)
  - `dayEvents()` - Computed property para eventos del día (vista calendario)
  - `availablePrograms()` - Computed property para programas disponibles
  - `availableCalls()` - Computed property para convocatorias disponibles (filtradas por programa)
  - `eventTypes()` - Array de tipos de eventos disponibles
  - `sortBy($field)` - Ordenación
  - `resetFilters()` - Resetear filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedShowDeleted()` - Resetear página al cambiar filtro
  - `canCreate()` - Verificar si puede crear
  - `canViewDeleted()` - Verificar si puede ver eliminados
  - `render()` - Renderizado con paginación

#### **Paso 5: Componente Index - Métodos de Acción**
- [ ] Implementar métodos de eliminación:
  - `confirmDelete($eventId)` - Confirmar eliminación
  - `delete()` - Eliminar con SoftDeletes
  - `confirmRestore($eventId)` - Confirmar restauración
  - `restore()` - Restaurar evento eliminado
  - `confirmForceDelete($eventId)` - Confirmar eliminación permanente
  - `forceDelete()` - Eliminar permanentemente (solo super-admin)
- [ ] Implementar métodos de calendario:
  - `goToToday()` - Ir a la fecha actual
  - `goToDate($date)` - Ir a una fecha específica
  - `previousMonth()` - Mes anterior
  - `nextMonth()` - Mes siguiente
  - `previousWeek()` - Semana anterior
  - `nextWeek()` - Semana siguiente
  - `previousDay()` - Día anterior
  - `nextDay()` - Día siguiente
  - `changeCalendarView($view)` - Cambiar vista del calendario
  - `changeViewMode($mode)` - Cambiar entre lista y calendario

#### **Paso 6: Vista Index - Listado**
- [ ] Crear vista `livewire/admin/events/index.blade.php`
- [ ] Implementar header con:
  - Título "Eventos Erasmus+"
  - Botón crear evento
  - Selector de modo de vista (Lista / Calendario)
- [ ] Implementar breadcrumbs
- [ ] Implementar sección de filtros:
  - Búsqueda por título/descripción
  - Filtro por programa (select)
  - Filtro por convocatoria (select, dependiente de programa)
  - Filtro por tipo de evento (select)
  - Filtro por fecha (date picker)
  - Filtro mostrar eliminados (toggle)
  - Botón resetear filtros
- [ ] Implementar tabla responsive con columnas:
  - Imagen (thumbnail)
  - Título
  - Tipo de evento (badge)
  - Programa
  - Convocatoria (si aplica)
  - Fecha inicio
  - Fecha fin (si aplica)
  - Ubicación
  - Público (badge sí/no)
  - Estado (próximo/hoy/pasado)
  - Fecha creación
  - Acciones (ver, editar, eliminar)
- [ ] Implementar modales de confirmación:
  - Modal eliminar
  - Modal restaurar
  - Modal force delete
- [ ] Implementar paginación
- [ ] Implementar estado vacío
- [ ] Implementar loading states

#### **Paso 7: Vista Index - Calendario**
- [ ] Implementar vista de calendario en el mismo componente
- [ ] Crear sección de controles de calendario:
  - Botones anterior/siguiente (mes/semana/día)
  - Botón "Hoy"
  - Selector de vista (mes/semana/día)
  - Filtros (programa, tipo, fecha)
- [ ] Implementar vista mensual:
  - Grid de 7 columnas (días de la semana)
  - Días del mes con eventos
  - Indicador de eventos por día
  - Click en día para ver eventos
- [ ] Implementar vista semanal:
  - Grid de 7 columnas (días de la semana)
  - Eventos por día con horarios
- [ ] Implementar vista diaria:
  - Lista de eventos del día
  - Horarios detallados
- [ ] Implementar modales para ver/editar eventos desde calendario
- [ ] Implementar navegación fluida entre vistas

---

### **Fase 3: Componente Create (Crear)**

#### **Paso 8: Componente Create**
- [ ] Crear componente Livewire `Admin\Events\Create`
- [ ] Implementar propiedades públicas:
  - `?int $program_id = null` - ID del programa
  - `?int $call_id = null` - ID de la convocatoria
  - `string $title = ''` - Título del evento
  - `string $description = ''` - Descripción
  - `string $event_type = ''` - Tipo de evento
  - `string $start_date = ''` - Fecha de inicio (datetime-local)
  - `string $end_date = ''` - Fecha de fin (datetime-local, opcional)
  - `string $location = ''` - Ubicación
  - `bool $is_public = true` - Evento público
  - `?UploadedFile $image = null` - Imagen del evento
  - `bool $is_all_day = false` - Evento de todo el día
- [ ] Implementar métodos:
  - `mount(?int $program_id = null, ?int $call_id = null)` - Inicialización con autorización y parámetros opcionales
  - `availablePrograms()` - Computed property para programas disponibles
  - `availableCalls()` - Computed property para convocatorias (filtradas por programa)
  - `eventTypes()` - Array de tipos de eventos
  - `updatedProgramId()` - Actualizar convocatorias disponibles cuando cambia el programa
  - `updatedStartDate()` - Validar fecha de inicio
  - `updatedEndDate()` - Validar que fecha fin sea posterior a inicio
  - `updatedImage()` - Validar imagen en tiempo real
  - `store()` - Guardar nuevo evento usando `StoreErasmusEventRequest`
- [ ] Crear vista `livewire/admin/events/create.blade.php`:
  - Formulario con Flux UI
  - Sección de información básica:
    - Título (requerido)
    - Descripción (textarea)
    - Tipo de evento (select)
  - Sección de fechas:
    - Fecha inicio (datetime-local)
    - Fecha fin (datetime-local, opcional)
    - Checkbox "Todo el día"
  - Sección de asociaciones:
    - Select programa (opcional)
    - Select convocatoria (opcional, dependiente de programa)
  - Sección de ubicación:
    - Campo ubicación
  - Sección de visibilidad:
    - Toggle público/privado
  - Sección de imagen:
    - Upload de imagen con preview
    - Validación de tamaño y formato
  - Botones: Guardar, Cancelar
  - Breadcrumbs

---

### **Fase 4: Componente Edit (Editar)**

#### **Paso 9: Componente Edit**
- [ ] Crear componente Livewire `Admin\Events\Edit`
- [ ] Implementar propiedades públicas (similares a Create):
  - `ErasmusEvent $event` - Evento a editar
  - Propiedades del formulario (igual que Create)
  - `?int $imageToDelete = null` - ID de imagen a eliminar
  - `bool $showDeleteImageModal = false` - Modal de confirmación de eliminación de imagen
- [ ] Implementar métodos:
  - `mount(ErasmusEvent $event)` - Cargar datos del evento
  - Métodos similares a Create
  - `deleteImage()` - Eliminar imagen (soft delete)
  - `restoreImage()` - Restaurar imagen eliminada
  - `forceDeleteImage()` - Eliminar imagen permanentemente
  - `update()` - Actualizar evento usando `UpdateErasmusEventRequest`
- [ ] Crear vista `livewire/admin/events/edit.blade.php`:
  - Similar a Create pero con datos precargados
  - Mostrar imagen actual si existe
  - Opción de eliminar/restaurar imagen existente
  - Botones: Actualizar, Cancelar

---

### **Fase 5: Componente Show (Detalle)**

#### **Paso 10: Componente Show**
- [ ] Crear componente Livewire `Admin\Events\Show`
- [ ] Implementar propiedades públicas:
  - `ErasmusEvent $event` - Evento a mostrar
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `bool $showRestoreModal = false` - Modal de confirmación de restauración
  - `bool $showForceDeleteModal = false` - Modal de confirmación de eliminación permanente
- [ ] Implementar métodos:
  - `mount(ErasmusEvent $event)` - Cargar evento con relaciones
  - `togglePublic()` - Cambiar visibilidad pública/privada
  - `confirmDelete()` - Confirmar eliminación
  - `delete()` - Eliminar con SoftDeletes
  - `confirmRestore()` - Confirmar restauración
  - `restore()` - Restaurar evento
  - `confirmForceDelete()` - Confirmar eliminación permanente
  - `forceDelete()` - Eliminar permanentemente
- [ ] Crear vista `livewire/admin/events/show.blade.php`:
  - Header con título, estado y botones de acción
  - Breadcrumbs
  - Información principal:
    - Imagen destacada (si existe)
    - Título y descripción
    - Tipo de evento (badge)
    - Fechas (formato legible)
    - Ubicación
    - Visibilidad (público/privado)
  - Sección de asociaciones:
    - Programa (con enlace)
    - Convocatoria (con enlace, si aplica)
  - Sección de imágenes:
    - Galería de imágenes (si hay más de una)
    - Acciones por imagen (eliminar, restaurar)
  - Sección de metadatos:
    - Creado por
    - Fecha de creación
    - Fecha de actualización
  - Botones de acción:
    - Editar
    - Cambiar visibilidad
    - Eliminar/Restaurar/Force Delete (según estado)
  - Estadísticas (opcional):
    - Duración del evento
    - Estado (próximo/hoy/pasado)

---

### **Fase 6: Rutas y Navegación**

#### **Paso 11: Configurar Rutas**
- [ ] Añadir rutas en `routes/web.php` dentro del grupo `admin`:
  ```php
  // Rutas de Eventos
  Route::get('/eventos', \App\Livewire\Admin\Events\Index::class)->name('events.index');
  Route::get('/eventos/crear', \App\Livewire\Admin\Events\Create::class)->name('events.create');
  Route::get('/eventos/{event}', \App\Livewire\Admin\Events\Show::class)->name('events.show');
  Route::get('/eventos/{event}/editar', \App\Livewire\Admin\Events\Edit::class)->name('events.edit');
  ```
- [ ] Verificar que las rutas funcionen correctamente
- [ ] Probar navegación entre rutas

#### **Paso 12: Actualizar Navegación**
- [ ] Buscar componente de sidebar/navegación de administración
- [ ] Añadir enlace a "Eventos" en el menú de administración
- [ ] Añadir traducciones necesarias para el menú
- [ ] Verificar que el enlace aparezca según permisos del usuario
- [ ] Añadir icono apropiado (calendario)

---

### **Fase 7: Optimizaciones y Mejoras**

#### **Paso 13: Optimizaciones de Consultas**
- [ ] Implementar eager loading en Index:
  - Cargar relaciones: `program`, `call`, `creator`
  - Cargar imágenes: `media`
- [ ] Implementar eager loading en Show:
  - Cargar todas las relaciones necesarias
- [ ] Añadir índices de base de datos si es necesario:
  - `start_date`, `end_date` para búsquedas por fecha
  - `program_id`, `call_id` para filtros
  - `event_type` para filtros por tipo
  - `is_public` para filtros de visibilidad
- [ ] Optimizar consultas del calendario:
  - Usar scopes del modelo para filtrar por fecha
  - Cargar solo eventos necesarios para la vista actual

#### **Paso 14: Mejoras de UX**
- [x] Añadir validación en tiempo real en formularios
- [x] Añadir feedback visual al guardar/actualizar
- [x] Implementar confirmaciones antes de acciones destructivas
- [x] Añadir tooltips informativos
- [x] Mejorar responsive design en móviles
- [x] Implementar Filepond para subida de imágenes con drag & drop

---

### **Fase 8: Testing**

#### **Paso 15: Tests de Componentes**
- [x] Crear test `Admin\Events\IndexTest`:
  - Test de autorización
  - Test de listado con filtros
  - Test de ordenación
  - Test de paginación
  - Test de eliminación/restauración/force delete
  - Test de vista calendario
  - Test de navegación de calendario
- [x] Crear test `Admin\Events\CreateTest`:
  - Test de autorización
  - Test de creación exitosa
  - Test de validación de campos
  - Test de subida de imagen
  - Test de asociación con programa/convocatoria
- [x] Crear test `Admin\Events\EditTest`:
  - Test de autorización
  - Test de actualización exitosa
  - Test de validación
  - Test de gestión de imágenes
- [x] Crear test `Admin\Events\ShowTest`:
  - Test de autorización
  - Test de visualización
  - Test de acciones (eliminar, restaurar, etc.)
  - Test de cambio de visibilidad

#### **Paso 16: Tests de Integración**
- [x] Test de flujo completo: crear → editar → eliminar → restaurar
- [x] Test de asociación con convocatorias
- [x] Test de filtros combinados
- [x] Test de calendario con múltiples eventos
- [x] Test de permisos por rol

---

## 📝 Notas Técnicas

### SoftDeletes
- Los eventos nunca se eliminan permanentemente por defecto
- Solo super-admin puede hacer `forceDelete()`
- Validar que no haya relaciones antes de `forceDelete()` (aunque los eventos no tienen relaciones dependientes, es buena práctica)
- Filtrar eventos eliminados por defecto en listados
- Opción de ver eventos eliminados (solo para administradores)

### MediaLibrary
- Colección `'images'` para imágenes del evento
- Conversiones: thumbnail, medium, large
- Gestión de soft delete de imágenes (similar a NewsPost)
- Permitir múltiples imágenes por evento

### Validaciones Especiales
- `end_date` debe ser posterior a `start_date`
- Si se selecciona `call_id`, debe pertenecer al `program_id` seleccionado
- Validar que las fechas no sean en el pasado (opcional, según requisitos)
- Validar formato de fechas y horas

### Calendario
- Reutilizar lógica del componente público `Events\Calendar`
- Adaptar para mostrar todos los eventos (no solo públicos) en admin
- Permitir crear eventos directamente desde el calendario (click en día)
- Mostrar eventos eliminados en color diferente (opcional)

### Asociaciones
- Un evento puede estar asociado a un programa (opcional)
- Un evento puede estar asociado a una convocatoria (opcional)
- Si hay convocatoria, debe pertenecer al programa seleccionado
- Mostrar eventos relacionados en Show de programa/convocatoria (futuro)

---

## ✅ Checklist Final

Antes de considerar completado el paso 3.5.9, verificar:

- [ ] SoftDeletes implementado y funcionando
- [ ] MediaLibrary implementado y funcionando
- [ ] FormRequests actualizados con autorización y validación de imágenes
- [ ] Componente Index funcionando (lista y calendario)
- [ ] Componente Create funcionando
- [ ] Componente Edit funcionando
- [ ] Componente Show funcionando
- [ ] Rutas configuradas y funcionando
- [ ] Navegación actualizada
- [ ] Tests pasando (mínimo 80% cobertura)
- [ ] Código formateado con Pint
- [ ] Sin errores de linter
- [ ] Responsive en móviles
- [ ] Documentación actualizada

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Pendiente de implementación

