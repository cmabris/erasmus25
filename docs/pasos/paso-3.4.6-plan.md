# Plan de Desarrollo: Paso 3.4.6 - Calendario de Eventos

Este documento establece el plan detallado para desarrollar el **Paso 3.4.6: Calendario de Eventos** según la planificación general del proyecto.

---

## Objetivo

Desarrollar un sistema completo de calendario de eventos Erasmus+ con:
- Vista de calendario mensual interactiva
- Vista de listado con filtros
- Vista de detalle de evento
- Integración con eventos de convocatorias
- Diseño moderno y responsive siguiendo la línea de diseño existente

---

## Análisis del Estado Actual

### ✅ Componentes Existentes que Podemos Reutilizar

1. **Componente `event-card.blade.php`** - Ya existe con variantes:
   - `default` - Card estándar
   - `compact` - Card compacta
   - `timeline` - Vista de línea de tiempo
   - `calendar` - Vista de calendario (ya preparado)

2. **Componentes UI base**:
   - `x-ui.card` - Cards reutilizables
   - `x-ui.badge` - Badges con colores
   - `x-ui.button` - Botones con variantes
   - `x-ui.section` - Secciones de contenido
   - `x-ui.empty-state` - Estados vacíos
   - `x-ui.search-input` - Búsqueda
   - `x-ui.breadcrumbs` - Breadcrumbs

3. **Patrones de diseño**:
   - Hero section con gradiente Erasmus+
   - Filtros con selects y búsqueda
   - Grid responsive para listados
   - Paginación estándar

### 📋 Modelo de Datos

**ErasmusEvent** tiene:
- `title` - Título del evento
- `description` - Descripción (nullable)
- `event_type` - Enum: apertura, cierre, entrevista, publicacion_provisional, publicacion_definitivo, reunion_informativa, otro
- `start_date` - DateTime de inicio
- `end_date` - DateTime de fin (nullable)
- `location` - Ubicación (nullable)
- `is_public` - Boolean
- `program_id` - Relación con Program (nullable)
- `call_id` - Relación con Call (nullable)
- `created_by` - Usuario creador (nullable)

### 🎨 Tipos de Eventos y Configuración

Ya definidos en `event-card.blade.php`:
- `apertura` - Success (verde) - Icon: play-circle
- `cierre` - Danger (rojo) - Icon: stop-circle
- `entrevista` - Info (azul) - Icon: chat-bubble-left-right
- `publicacion_provisional` - Warning (amarillo) - Icon: document-text
- `publicacion_definitivo` - Success (verde) - Icon: document-check
- `reunion_informativa` - Primary (erasmus) - Icon: user-group
- `otro` - Neutral (gris) - Icon: calendar

---

## Plan de Desarrollo Paso a Paso

### **Fase 1: Seeder de Datos de Prueba**

**Objetivo**: Crear un seeder con eventos realistas para desarrollo y pruebas.

#### 1.1. Crear `ErasmusEventSeeder`
- [ ] Crear seeder con eventos variados:
  - Eventos de diferentes tipos
  - Eventos asociados a programas
  - Eventos asociados a convocatorias
  - Eventos independientes
  - Eventos pasados, presentes y futuros
  - Eventos con y sin ubicación
  - Eventos con y sin fecha de fin
- [ ] Distribuir eventos a lo largo del año académico actual
- [ ] Incluir eventos de diferentes programas (KA1xx, KA121-VET, KA131-HED)
- [ ] Asegurar que haya eventos para cada mes visible

**Archivos a crear:**
- `database/seeders/ErasmusEventSeeder.php`

---

### **Fase 2: Componente Livewire - Vista de Calendario (`Events\Calendar`)**

**Objetivo**: Crear una vista de calendario mensual interactiva y moderna.

#### 2.1. Crear componente Livewire `Events\Calendar`
- [ ] Crear `app/Livewire/Public/Events/Calendar.php`
- [ ] Propiedades:
  - `$currentDate` - Fecha actual del calendario (Carbon)
  - `$viewMode` - Modo de vista: 'month', 'week', 'day' (default: 'month')
  - `$selectedProgram` - Filtro por programa (URL)
  - `$selectedEventType` - Filtro por tipo de evento (URL)
- [ ] Métodos:
  - `mount()` - Inicializar con fecha actual
  - `previousMonth()` - Navegar al mes anterior
  - `nextMonth()` - Navegar al mes siguiente
  - `goToToday()` - Ir a la fecha actual
  - `goToDate($date)` - Ir a una fecha específica
  - `eventsForMonth()` - Obtener eventos del mes actual
  - `eventsForWeek()` - Obtener eventos de la semana actual
  - `eventsForDay()` - Obtener eventos del día actual
  - `eventsByDate()` - Agrupar eventos por fecha
  - `changeView($view)` - Cambiar modo de vista
- [ ] Filtros:
  - Por programa (select)
  - Por tipo de evento (select)
  - Reset de filtros
- [ ] Computed properties:
  - `availablePrograms()` - Programas activos
  - `eventTypes()` - Tipos de eventos disponibles
  - `calendarEvents()` - Eventos filtrados y agrupados

#### 2.2. Crear vista `livewire/public/events/calendar.blade.php`
- [ ] Hero section con gradiente Erasmus+
  - Título "Calendario de Eventos"
  - Descripción
  - Estadísticas (eventos este mes, próximos eventos)
- [ ] Barra de navegación del calendario:
  - Botones anterior/siguiente mes
  - Botón "Hoy"
  - Selector de fecha (date picker o input)
  - Selector de vista (mes/semana/día)
- [ ] Filtros:
  - Filtro por programa
  - Filtro por tipo de evento
  - Botón limpiar filtros
- [ ] Vista mensual:
  - Grid de calendario (7 columnas para días de la semana)
  - Días del mes con eventos
  - Indicadores visuales de eventos por día
  - Hover para mostrar preview de eventos
- [ ] Vista semanal:
  - Lista de días de la semana
  - Eventos agrupados por día
  - Timeline visual
- [ ] Vista diaria:
  - Lista de eventos del día
  - Timeline con horas
- [ ] Integración con `event-card` usando variante `calendar`
- [ ] Responsive:
  - Móvil: Vista de lista simplificada
  - Tablet: Vista semanal
  - Desktop: Vista mensual completa

**Archivos a crear:**
- `app/Livewire/Public/Events/Calendar.php`
- `resources/views/livewire/public/events/calendar.blade.php`

**Archivos a modificar:**
- `resources/views/components/content/event-card.blade.php` (si necesita ajustes)

---

### **Fase 3: Componente Livewire - Vista de Listado (`Events\Index`)**

**Objetivo**: Crear una vista de listado de eventos con filtros avanzados.

#### 3.1. Crear componente Livewire `Events\Index`
- [ ] Crear `app/Livewire/Public/Events/Index.php`
- [ ] Propiedades:
  - `$search` - Búsqueda por texto (URL)
  - `$program` - Filtro por programa (URL)
  - `$eventType` - Filtro por tipo de evento (URL)
  - `$dateFrom` - Filtro fecha desde (URL)
  - `$dateTo` - Filtro fecha hasta (URL)
  - `$showPast` - Mostrar eventos pasados (default: false)
- [ ] Métodos:
  - `resetFilters()` - Limpiar todos los filtros
  - `togglePastEvents()` - Alternar mostrar eventos pasados
- [ ] Computed properties:
  - `availablePrograms()` - Programas activos
  - `eventTypes()` - Tipos de eventos
  - `events()` - Eventos paginados y filtrados
  - `stats()` - Estadísticas (total, este mes, próximos)

#### 3.2. Crear vista `livewire/public/events/index.blade.php`
- [ ] Hero section similar a otras vistas públicas
  - Título "Eventos Erasmus+"
  - Descripción
  - Estadísticas (total eventos, este mes, próximos)
- [ ] Sección de filtros:
  - Búsqueda por texto
  - Filtro por programa (select)
  - Filtro por tipo de evento (select)
  - Filtro por rango de fechas (date inputs)
  - Toggle para mostrar eventos pasados
  - Botón limpiar filtros
  - Resumen de filtros activos
- [ ] Grid de eventos:
  - Usar `event-card` con variante `default` o `compact`
  - Mostrar eventos agrupados por mes
  - Indicadores de eventos próximos/hoy/pasados
- [ ] Paginación
- [ ] Estado vacío cuando no hay eventos
- [ ] Responsive design

**Archivos a crear:**
- `app/Livewire/Public/Events/Index.php`
- `resources/views/livewire/public/events/index.blade.php`

---

### **Fase 4: Componente Livewire - Vista de Detalle (`Events\Show`)**

**Objetivo**: Crear una vista de detalle completa de un evento.

#### 4.1. Crear componente Livewire `Events\Show`
- [ ] Crear `app/Livewire/Public/Events/Show.php`
- [ ] Propiedades:
  - `$event` - Modelo ErasmusEvent (route model binding)
- [ ] Métodos:
  - `mount(ErasmusEvent $event)` - Cargar evento
  - `relatedEvents()` - Eventos relacionados (mismo programa/convocatoria)
- [ ] Computed properties:
  - `isUpcoming()` - ¿Es un evento futuro?
  - `isToday()` - ¿Es hoy?
  - `isPast()` - ¿Es un evento pasado?
  - `relatedEvents()` - Eventos relacionados

#### 4.2. Crear vista `livewire/public/events/show.blade.php`
- [ ] Hero section con información del evento:
  - Badge de tipo de evento
  - Título del evento
  - Fecha y hora destacadas
  - Ubicación si existe
- [ ] Sección de contenido:
  - Descripción completa
  - Información del programa (si aplica)
  - Información de la convocatoria (si aplica)
  - Botón para ver convocatoria relacionada
- [ ] Sección de información adicional:
  - Fecha de inicio y fin
  - Ubicación con mapa (opcional)
  - Tipo de evento
  - Estado (próximo/hoy/pasado)
- [ ] Sección de eventos relacionados:
  - Eventos del mismo programa
  - Eventos de la misma convocatoria
  - Próximos eventos similares
- [ ] Breadcrumbs
- [ ] Botones de navegación:
  - Volver al calendario
  - Volver al listado
  - Ver convocatoria (si aplica)
- [ ] Responsive design

**Archivos a crear:**
- `app/Livewire/Public/Events/Show.php`
- `resources/views/livewire/public/events/show.blade.php`

---

### **Fase 5: Rutas y Navegación**

**Objetivo**: Configurar las rutas y actualizar la navegación.

#### 5.1. Agregar rutas en `routes/web.php`
- [ ] Ruta para calendario: `/calendario` → `Events\Calendar`
- [ ] Ruta para listado: `/eventos` → `Events\Index`
- [ ] Ruta para detalle: `/eventos/{event:slug}` → `Events\Show`
- [ ] Nota: Necesitaremos agregar `slug` al modelo ErasmusEvent o usar `id`

#### 5.2. Actualizar navegación pública
- [ ] Agregar enlace "Calendario" en `public-nav.blade.php`
- [ ] Agregar enlace "Eventos" en `public-nav.blade.php`
- [ ] Marcar como activo cuando `routeIs('eventos.*')` o `routeIs('calendario')`

#### 5.3. Actualizar enlaces en otros componentes
- [ ] Actualizar botón "Calendario" en `home.blade.php` para que apunte a `/calendario`
- [ ] Actualizar `event-card.blade.php` para usar rutas correctas
- [ ] Agregar enlaces en páginas relacionadas (convocatorias, programas)

**Archivos a modificar:**
- `routes/web.php`
- `resources/views/components/nav/public-nav.blade.php`
- `resources/views/livewire/public/home.blade.php`
- `resources/views/components/content/event-card.blade.php`

---

### **Fase 6: Mejoras del Modelo ErasmusEvent**

**Objetivo**: Agregar funcionalidades útiles al modelo.

#### 6.1. Agregar scope al modelo
- [ ] `scopePublic()` - Solo eventos públicos
- [ ] `scopeUpcoming()` - Solo eventos futuros
- [ ] `scopePast()` - Solo eventos pasados
- [ ] `scopeForDate($date)` - Eventos para una fecha específica
- [ ] `scopeForMonth($year, $month)` - Eventos de un mes
- [ ] `scopeForProgram($programId)` - Eventos de un programa
- [ ] `scopeForCall($callId)` - Eventos de una convocatoria
- [ ] `scopeByType($type)` - Eventos de un tipo específico

#### 6.2. Agregar métodos helper
- [ ] `isUpcoming()` - ¿Es futuro?
- [ ] `isToday()` - ¿Es hoy?
- [ ] `isPast()` - ¿Es pasado?
- [ ] `duration()` - Duración del evento (si tiene end_date)
- [ ] `isAllDay()` - ¿Es evento de todo el día? (sin hora específica)

#### 6.3. Considerar agregar slug (si se usa en rutas)
- [ ] Agregar campo `slug` a la migración (si no existe)
- [ ] Generar slug automáticamente desde `title`
- [ ] Usar `route model binding` con slug

**Archivos a modificar:**
- `app/Models/ErasmusEvent.php`
- `database/migrations/2025_12_12_193919_create_erasmus_events_table.php` (si agregamos slug)

---

### **Fase 7: Tests**

**Objetivo**: Crear tests completos para todos los componentes.

#### 7.1. Tests para `Events\Calendar`
- [ ] Test de renderizado inicial
- [ ] Test de navegación (mes anterior/siguiente)
- [ ] Test de filtros (programa, tipo)
- [ ] Test de cambio de vista (mes/semana/día)
- [ ] Test de eventos mostrados correctamente
- [ ] Test de eventos agrupados por fecha
- [ ] Test de responsive

#### 7.2. Tests para `Events\Index`
- [ ] Test de renderizado inicial
- [ ] Test de búsqueda
- [ ] Test de filtros (programa, tipo, fechas)
- [ ] Test de paginación
- [ ] Test de toggle eventos pasados
- [ ] Test de estadísticas
- [ ] Test de estado vacío
- [ ] Test de reset de filtros

#### 7.3. Tests para `Events\Show`
- [ ] Test de renderizado de evento existente
- [ ] Test de evento 404 (no existe)
- [ ] Test de evento privado (no público)
- [ ] Test de eventos relacionados
- [ ] Test de información mostrada correctamente
- [ ] Test de breadcrumbs

#### 7.4. Tests de integración
- [ ] Test de rutas
- [ ] Test de navegación
- [ ] Test de enlaces entre vistas

**Archivos a crear:**
- `tests/Feature/Livewire/Public/Events/CalendarTest.php`
- `tests/Feature/Livewire/Public/Events/IndexTest.php`
- `tests/Feature/Livewire/Public/Events/ShowTest.php`

---

### **Fase 8: Optimizaciones y Mejoras**

**Objetivo**: Optimizar rendimiento y mejorar UX.

#### 8.1. Optimizaciones de consultas
- [ ] Eager loading de relaciones (program, call, creator)
- [ ] Índices en base de datos (ya existen según migración)
- [ ] Caché de eventos frecuentes (opcional)

#### 8.2. Mejoras de UX
- [ ] Loading states durante filtros
- [ ] Transiciones suaves entre vistas
- [ ] Tooltips en calendario
- [ ] Modal para preview de eventos en calendario
- [ ] Exportar evento a calendario (iCal/Google Calendar)

#### 8.3. Accesibilidad
- [ ] Navegación por teclado en calendario
- [ ] ARIA labels apropiados
- [ ] Contraste de colores
- [ ] Screen reader friendly

---

## Estructura de Archivos Final

```
app/Livewire/Public/Events/
├── Calendar.php
├── Index.php
└── Show.php

resources/views/livewire/public/events/
├── calendar.blade.php
├── index.blade.php
└── show.blade.php

database/seeders/
└── ErasmusEventSeeder.php

tests/Feature/Livewire/Public/Events/
├── CalendarTest.php
├── IndexTest.php
└── ShowTest.php

routes/
└── web.php (modificar)

resources/views/components/
├── nav/public-nav.blade.php (modificar)
└── content/event-card.blade.php (modificar si necesario)

app/Models/
└── ErasmusEvent.php (modificar - agregar scopes y métodos)
```

---

## Consideraciones Técnicas

### Calendario Mensual
- Usar Carbon para manejo de fechas
- Generar grid de calendario dinámicamente
- Agrupar eventos por día
- Mostrar indicadores visuales de cantidad de eventos por día
- Permitir click en día para ver eventos

### Integración con Convocatorias
- Los eventos pueden estar asociados a convocatorias
- Mostrar información de la convocatoria en el evento
- Enlazar desde evento a convocatoria
- Mostrar eventos de convocatoria en la vista de detalle de convocatoria

### Responsive Design
- Móvil: Vista de lista simplificada
- Tablet: Vista semanal o calendario compacto
- Desktop: Vista mensual completa con todas las funcionalidades

### Internacionalización
- Todos los textos traducibles
- Formato de fechas según idioma
- Nombres de días y meses traducidos

---

## Criterios de Éxito

✅ **Funcionalidad Completa**
- Calendario mensual/semanal/diario funcional
- Listado con filtros avanzados
- Vista de detalle completa
- Integración con convocatorias

✅ **Diseño Moderno**
- Sigue la línea de diseño existente
- Responsive en todos los dispositivos
- Dark mode compatible
- Animaciones y transiciones suaves

✅ **Calidad de Código**
- Tests con alta cobertura (>90%)
- Código limpio y bien documentado
- Reutilización de componentes existentes
- Optimización de consultas

✅ **UX Excelente**
- Navegación intuitiva
- Filtros fáciles de usar
- Información clara y accesible
- Estados de carga y vacío apropiados

---

## Próximos Pasos Inmediatos

1. **Crear seeder** con datos de prueba realistas
2. **Desarrollar componente Calendar** con vista mensual básica
3. **Desarrollar componente Index** con filtros básicos
4. **Desarrollar componente Show** con información completa
5. **Agregar rutas y navegación**
6. **Mejorar modelo** con scopes y métodos helper
7. **Crear tests** para cada componente
8. **Optimizar y pulir** detalles finales

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan completado - Listo para implementación  
**Prioridad**: Alta (Paso 3.4.6 según planificación)

