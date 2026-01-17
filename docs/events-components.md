# Documentación Técnica: Componentes de Eventos

Este documento describe la arquitectura y uso de los componentes creados para el calendario y gestión de eventos en la aplicación Erasmus+ Centro (Murcia).

---

## Índice

1. [Arquitectura General](#arquitectura-general)
2. [Modelo ErasmusEvent](#modelo-erasmusevent)
3. [Componentes Livewire](#componentes-livewire)
4. [Componente Event Card](#componente-event-card)
5. [Rutas](#rutas)
6. [Seeders](#seeders)
7. [Guía de Uso](#guía-de-uso)
8. [Tests](#tests)

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                     Layout Público                               │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Public Nav                                ││
│  │        [Calendario] activo cuando routeIs('calendario')     ││
│  │        [Eventos] activo cuando routeIs('eventos.*')         ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │              Livewire Component                              ││
│  │                                                              ││
│  │  Events\Calendar    Events\Index      Events\Show           ││
│  │  ┌──────────────┐   ┌──────────────┐  ┌──────────────┐     ││
│  │  │ Vista Mes/  │   │ x-ui.search  │  │ x-ui.bread   │     ││
│  │  │ Semana/Día  │   │ x-ui.section │  │ x-ui.section │     ││
│  │  │ event-card  │   │ event-card   │  │ event-card   │     ││
│  │  │ (calendar)  │   │ x-ui.empty   │  │ related      │     ││
│  │  └──────────────┘   └──────────────┘  │   events     │     ││
│  │                                       └──────────────┘     ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                      Footer                                  ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

---

## Modelo ErasmusEvent

### Estructura del Modelo

**Ubicación:** `app/Models/ErasmusEvent.php`

**Campos principales:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único del evento |
| `program_id` | bigint (nullable) | ID del programa asociado |
| `call_id` | bigint (nullable) | ID de la convocatoria asociada |
| `title` | string | Título del evento |
| `description` | text (nullable) | Descripción del evento |
| `event_type` | enum | Tipo de evento (ver tipos abajo) |
| `start_date` | datetime | Fecha y hora de inicio |
| `end_date` | datetime (nullable) | Fecha y hora de fin |
| `location` | string (nullable) | Ubicación del evento |
| `is_public` | boolean | Si el evento es público |
| `created_by` | bigint (nullable) | ID del usuario creador |

**Tipos de eventos:**

| Tipo | Etiqueta | Color | Icono |
|------|----------|-------|-------|
| `apertura` | Apertura | success (verde) | play-circle |
| `cierre` | Cierre | danger (rojo) | stop-circle |
| `entrevista` | Entrevistas | info (azul) | chat-bubble-left-right |
| `publicacion_provisional` | Listado provisional | warning (amarillo) | document-text |
| `publicacion_definitivo` | Listado definitivo | success (verde) | document-check |
| `reunion_informativa` | Reunión informativa | primary (erasmus) | user-group |
| `otro` | Otro | neutral (gris) | calendar |

### Relaciones

```php
public function program(): BelongsTo
public function call(): BelongsTo
public function creator(): BelongsTo
```

### Scopes Disponibles

| Scope | Descripción |
|-------|-------------|
| `public()` | Solo eventos públicos |
| `upcoming()` | Solo eventos futuros |
| `past()` | Solo eventos pasados |
| `forDate($date)` | Eventos para una fecha específica |
| `forMonth($year, $month)` | Eventos de un mes |
| `forProgram($programId)` | Eventos de un programa |
| `forCall($callId)` | Eventos de una convocatoria |
| `byType($type)` | Eventos de un tipo específico |
| `inDateRange($from, $to)` | Eventos en rango de fechas |

### Métodos Helper

| Método | Tipo de Retorno | Descripción |
|--------|-----------------|-------------|
| `isUpcoming()` | bool | ¿Es un evento futuro? |
| `isToday()` | bool | ¿Es hoy? |
| `isPast()` | bool | ¿Es un evento pasado? |
| `duration()` | float\|null | Duración en horas (si tiene end_date) |
| `isAllDay()` | bool | ¿Es evento de todo el día? |
| `formatted_date_range` | string | Atributo computado: rango de fechas formateado |

**Ejemplo de uso de scopes:**
```php
// Eventos públicos del próximo mes
$events = ErasmusEvent::public()
    ->forMonth(2025, 1)
    ->upcoming()
    ->get();

// Eventos de una convocatoria específica
$callEvents = ErasmusEvent::public()
    ->forCall($callId)
    ->orderBy('start_date')
    ->get();
```

---

## Componentes Livewire

### Events\Calendar

Vista de calendario con tres modos: mensual, semanal y diaria.

**Ubicación:** `app/Livewire/Public/Events/Calendar.php`

**Propiedades públicas con URL binding:**

| Propiedad | URL Alias | Tipo | Default | Descripción |
|-----------|-----------|------|---------|-------------|
| `$currentDate` | `fecha` | string | `now()->format('Y-m-d')` | Fecha actual del calendario |
| `$viewMode` | `vista` | string | `'month'` | Modo de vista: `month`, `week`, `day` |
| `$selectedProgram` | `programa` | string | `''` | Filtro por programa |
| `$selectedEventType` | `tipo` | string | `''` | Filtro por tipo de evento |

**Computed Properties:**

| Propiedad | Tipo de Retorno | Descripción |
|-----------|-----------------|-------------|
| `currentDateCarbon` | Carbon | Fecha actual como instancia Carbon |
| `availablePrograms` | Collection | Programas activos para filtros |
| `eventTypes` | array | Tipos de eventos con etiquetas traducidas |
| `calendarEvents` | Collection | Eventos filtrados según vista actual |
| `eventsByDate` | array | Eventos agrupados por fecha (clave: Y-m-d) |
| `calendarDays` | array | Días del calendario mensual con eventos |
| `weekDays` | array | Días de la semana con eventos |
| `stats` | array | Estadísticas (this_month, upcoming) |

**Métodos públicos:**

| Método | Parámetros | Descripción |
|--------|------------|-------------|
| `previous()` | - | Navegar al período anterior |
| `next()` | - | Navegar al período siguiente |
| `goToToday()` | - | Ir a la fecha actual |
| `goToDate($date)` | string | Ir a una fecha específica |
| `changeView($view)` | string | Cambiar modo de vista |
| `resetFilters()` | - | Limpiar filtros |

**Vista mensual:**
- Grid de calendario (7 columnas × semanas)
- Indicadores de eventos por día
- Eventos mostrados como enlaces truncados (máx. 3 por día)
- Enlace "+X más" si hay más eventos

**Vista semanal:**
- Lista de 7 días con eventos completos
- Cards de eventos usando variante `compact`
- Indicador "Hoy" para el día actual

**Vista diaria:**
- Lista completa de eventos del día
- Cards de eventos usando variante `default`
- Empty state si no hay eventos

**Ejemplo de uso:**
```blade
<livewire:public.events.calendar />
```

---

### Events\Index

Listado público de eventos con filtros avanzados.

**Ubicación:** `app/Livewire/Public/Events/Index.php`

**Propiedades públicas con URL binding:**

| Propiedad | URL Alias | Tipo | Default | Descripción |
|-----------|-----------|------|---------|-------------|
| `$search` | `q` | string | `''` | Búsqueda por texto |
| `$program` | `programa` | string | `''` | Filtro por programa |
| `$eventType` | `tipo` | string | `''` | Filtro por tipo de evento |
| `$dateFrom` | `desde` | string | `''` | Filtro fecha desde |
| `$dateTo` | `hasta` | string | `''` | Filtro fecha hasta |
| `$showPast` | `pasados` | bool | `false` | Mostrar eventos pasados |

**Computed Properties:**

| Propiedad | Tipo de Retorno | Descripción |
|-----------|-----------------|-------------|
| `availablePrograms` | Collection | Programas activos para filtros |
| `eventTypes` | array | Tipos de eventos con etiquetas |
| `stats` | array | Estadísticas (total, this_month, upcoming) |
| `events` | LengthAwarePaginator | Eventos filtrados y paginados (12 por página) |

**Métodos públicos:**

| Método | Descripción |
|--------|-------------|
| `resetFilters()` | Limpiar todos los filtros |
| `togglePastEvents()` | Alternar mostrar eventos pasados |

**Características:**
- Búsqueda en tiempo real (título y descripción)
- Filtros múltiples combinables
- Paginación (12 eventos por página)
- Por defecto solo muestra eventos futuros
- Toggle para incluir eventos pasados
- Resumen de filtros activos
- Estadísticas en hero section

**Ejemplo de uso:**
```blade
<livewire:public.events.index />
```

---

### Events\Show

Vista de detalle completa de un evento.

**Ubicación:** `app/Livewire/Public/Events/Show.php`

**Propiedades públicas:**

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| `$event` | ErasmusEvent | Modelo del evento (route model binding) |

**Computed Properties:**

| Propiedad | Tipo de Retorno | Descripción |
|-----------|-----------------|-------------|
| `isUpcoming` | bool | ¿Es un evento futuro? |
| `isToday` | bool | ¿Es hoy? |
| `isPast` | bool | ¿Es un evento pasado? |
| `relatedEvents` | Collection | Eventos relacionados (mismo programa/convocatoria) |

**Características:**
- Información completa del evento
- Badge de tipo de evento con color e icono
- Badge de estado (Hoy/Próximo/Pasado)
- Fecha y hora formateadas
- Ubicación si existe
- Descripción completa
- Información de programa asociado
- Enlace a convocatoria relacionada si aplica
- Eventos relacionados (mismo programa/convocatoria)
- Breadcrumbs
- Botones de navegación

**Lógica de eventos relacionados:**
1. Si el evento tiene `call_id`: muestra eventos de la misma convocatoria
2. Si no tiene `call_id` pero tiene `program_id`: muestra eventos del mismo programa
3. Solo muestra eventos futuros (`upcoming()`)
4. Excluye el evento actual
5. Máximo 4 eventos relacionados

**Ejemplo de uso:**
```blade
<livewire:public.events.show :event="$event" />
```

---

## Componente Event Card

Componente reutilizable para mostrar eventos en diferentes contextos.

**Ubicación:** `resources/views/components/content/event-card.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `event` | ErasmusEvent\|null | `null` | Modelo del evento |
| `title` | string\|null | `null` | Título (si no se proporciona event) |
| `description` | string\|null | `null` | Descripción |
| `eventType` | string\|null | `null` | Tipo de evento |
| `startDate` | Carbon\|string\|null | `null` | Fecha de inicio |
| `endDate` | Carbon\|string\|null | `null` | Fecha de fin |
| `location` | string\|null | `null` | Ubicación |
| `isPublic` | bool | `true` | Si es público |
| `program` | Program\|null | `null` | Programa asociado |
| `call` | Call\|null | `null` | Convocatoria asociada |
| `href` | string\|null | `null` | URL de enlace |
| `variant` | string | `'default'` | Variante: `default`, `compact`, `timeline`, `calendar` |
| `showCall` | bool | `true` | Mostrar información de convocatoria |

**Variantes:**

### Variante `default`
- Card estándar con fecha destacada
- Badge de tipo de evento
- Descripción (truncada a 2 líneas)
- Hora y ubicación
- Badge "Hoy" si aplica

### Variante `compact`
- Card compacta horizontal
- Fecha pequeña a la izquierda
- Título y detalles en una línea
- Badge de tipo a la derecha

### Variante `timeline`
- Diseño de línea de tiempo vertical
- Burbuja de fecha con icono de tipo
- Línea conectora entre eventos
- Badge "Hoy" si aplica
- Información completa expandida

### Variante `calendar`
- Diseño optimizado para calendario
- Header con fecha destacada
- Contenido compacto
- Hora si aplica
- Ideal para grid de calendario

**Ejemplo de uso:**
```blade
{{-- Con modelo --}}
<x-content.event-card :event="$event" variant="default" />

{{-- Con props individuales --}}
<x-content.event-card 
    title="Reunión Informativa"
    eventType="reunion_informativa"
    :startDate="now()->addDays(5)"
    location="Aula Magna"
    variant="compact"
/>
```

---

## Rutas

**Ubicación:** `routes/web.php`

| Ruta | Método | Componente | Nombre |
|------|--------|------------|--------|
| `/calendario` | GET | `Events\Calendar` | `calendario` |
| `/eventos` | GET | `Events\Index` | `eventos.index` |
| `/eventos/{event}` | GET | `Events\Show` | `eventos.show` |

**Route Model Binding:**
- `{event}` usa el ID del evento (no slug, ya que el modelo no tiene slug)

**Ejemplo de uso:**
```php
Route::get('/calendario', Events\Calendar::class)->name('calendario');
Route::get('/eventos', Events\Index::class)->name('eventos.index');
Route::get('/eventos/{event}', Events\Show::class)->name('eventos.show');
```

---

## Seeders

### ErasmusEventSeeder

Seeder con eventos realistas para desarrollo y pruebas.

**Ubicación:** `database/seeders/ErasmusEventSeeder.php`

**Características:**
- 66 eventos creados por defecto
- Eventos asociados a convocatorias (36 eventos)
- Eventos independientes (30 eventos)
- Eventos pasados, presentes y futuros
- Distribución a lo largo del año académico
- Diferentes tipos de eventos
- Ubicaciones variadas
- Eventos con y sin fecha de fin

**Tipos de eventos generados:**
- Aperturas de convocatorias
- Cierres de convocatorias
- Reuniones informativas
- Entrevistas de selección
- Publicaciones de listados (provisional y definitivo)
- Eventos especiales (talleres, charlas, jornadas)

**Distribución temporal:**
- Eventos pasados: últimos 3-8 meses
- Eventos futuros: próximos 6 meses
- Eventos de hoy y próximos días
- Eventos distribuidos por meses

**Ejemplo de uso:**
```bash
php artisan db:seed --class=ErasmusEventSeeder
```

**Incluido en DatabaseSeeder:**
```php
$this->call([
    // ... otros seeders
    ErasmusEventSeeder::class,
]);
```

---

## Guía de Uso

### Vista de Calendario

**Navegación:**
- Botones anterior/siguiente para cambiar mes/semana/día
- Botón "Hoy" para volver a la fecha actual
- Selector de vista (Mes/Semana/Día)

**Filtros:**
- Por programa: dropdown con programas activos
- Por tipo: dropdown con tipos de eventos
- Botón "Limpiar" para resetear filtros

**Vista mensual:**
- Click en un día muestra eventos de ese día
- Enlace "+X más" lleva al listado filtrado por fecha

**Vista semanal:**
- Muestra eventos de la semana actual
- Cards completas de eventos

**Vista diaria:**
- Muestra todos los eventos del día seleccionado
- Empty state si no hay eventos

### Vista de Listado

**Búsqueda:**
- Búsqueda en tiempo real por título y descripción
- Debounce de 300ms

**Filtros:**
- Programa: dropdown
- Tipo de evento: dropdown
- Rango de fechas: inputs de fecha (desde/hasta)
- Toggle "Incluir pasados": checkbox

**Paginación:**
- 12 eventos por página
- Links de paginación estándar de Livewire

**Estadísticas:**
- Total de eventos
- Eventos este mes
- Próximos eventos

### Vista de Detalle

**Información mostrada:**
- Título y tipo de evento
- Fecha y hora (formateadas)
- Ubicación si existe
- Descripción completa
- Programa asociado (con enlace)
- Convocatoria relacionada (con enlace si aplica)

**Eventos relacionados:**
- Mismo programa o convocatoria
- Solo eventos futuros
- Máximo 4 eventos

**Navegación:**
- Breadcrumbs
- Botón "Volver al listado"
- Botón "Ver calendario"

---

## Tests

### CalendarTest

**Ubicación:** `tests/Feature/Livewire/Public/Events/CalendarTest.php`

**Tests implementados (22 tests):**
- ✅ Renderizado de página
- ✅ Visualización de mes actual
- ✅ Navegación (mes anterior/siguiente)
- ✅ Botón "Hoy"
- ✅ Cambio de vista (mes/semana/día)
- ✅ Filtros (programa, tipo)
- ✅ Reset de filtros
- ✅ Visualización de eventos en cada vista
- ✅ Estadísticas
- ✅ Solo eventos públicos
- ✅ Empty state
- ✅ Navegación en vistas semanal y diaria
- ✅ Ir a fecha específica

**Cobertura:** ✅ **100%** (101/101 líneas, 16/16 métodos, 1/1 clase)

**Tests adicionales para cobertura completa:**
- ✅ `previous()` - navegación a semana anterior en vista 'week'
- ✅ `previous()` - navegación a día anterior en vista 'day'

### IndexTest

**Ubicación:** `tests/Feature/Livewire/Public/Events/IndexTest.php`

**Tests implementados (16 tests):**
- ✅ Renderizado de página
- ✅ Visualización de eventos públicos
- ✅ Ocultación de eventos privados
- ✅ Búsqueda por texto
- ✅ Filtros (programa, tipo, fechas)
- ✅ Toggle eventos pasados
- ✅ Reset de filtros
- ✅ Estadísticas
- ✅ Paginación
- ✅ Empty state
- ✅ Actualización de página al cambiar filtros

**Cobertura:** ✅ **100%** (56/56 líneas, 12/12 métodos, 1/1 clase)

**Tests adicionales para cobertura completa:**
- ✅ `togglePastEvents()` - toggle correcto y reset de página
- ✅ Mejora en test de búsqueda para cubrir búsqueda en descripción
- ✅ Mejora en test de rango de fechas para acceder explícitamente a `events`

**Técnicas utilizadas:**
- Acceso explícito a propiedades `#[Computed]` usando `$component->get('events')`
- Esto asegura que el método `events()` se ejecute y cubra todas las líneas, incluyendo closures `when()`

### ShowTest

**Ubicación:** `tests/Feature/Livewire/Public/Events/ShowTest.php`

**Tests implementados (14 tests):**
- ✅ Renderizado de página
- ✅ Visualización de información del evento
- ✅ 404 para eventos privados
- ✅ Eventos relacionados (misma convocatoria)
- ✅ Eventos relacionados (mismo programa)
- ✅ No mostrar eventos pasados en relacionados
- ✅ Información de convocatoria asociada
- ✅ Badge de tipo de evento
- ✅ Visualización de fecha y hora
- ✅ Badges de estado (Hoy/Próximo/Pasado)
- ✅ Duración del evento
- ✅ Botones de navegación

**Total:** 60 tests pasando (95 assertions)

---

## Mejoras Pendientes

### Vista Mensual del Calendario

**Estado:** Pendiente de mejora

**Problema actual:** La vista mensual utiliza un grid HTML simple que muestra los eventos en cada día del mes.

**Mejora propuesta:** Reemplazar por un componente JavaScript moderno que ofrezca:
- Mejor visualización de eventos múltiples en un día
- Interacciones más fluidas (drag & drop, hover effects)
- Mejor responsive en móviles
- Integración con librerías como FullCalendar, Calendar.js o Vanilla Calendar

**Documentación:** Ver `docs/pasos/paso-3.4.6-mejoras-pendientes.md`

---

## Notas Técnicas Importantes

### Serialización de Livewire

Los eventos se mantienen como objetos Eloquent en las Collections para preservar las propiedades del modelo. Cuando se agrupan por fecha, se mantienen como Collections en lugar de convertirlos a arrays.

### Filtros y Búsqueda

- Los filtros se sincronizan con la URL usando `#[Url]` attributes
- La búsqueda tiene debounce de 300ms para evitar consultas excesivas
- Los filtros se pueden combinar para búsquedas complejas

### Performance

- Eager loading de relaciones: `program`, `call`, `creator`
- Índices en base de datos: `['program_id', 'start_date']`, `['call_id', 'start_date']`, `['is_public', 'start_date']`
- Paginación de 12 eventos por página en listado
- Límite de 4 eventos relacionados en vista de detalle

### Seguridad

- Solo se muestran eventos públicos (`is_public = true`)
- Los eventos privados retornan 404 en vista de detalle
- Filtros validados en el componente Livewire

### Correcciones Realizadas

- Corregido manejo de Collections vs Arrays en vista de calendario
- Corregido método `relatedEvents()` para usar `if/elseif` en lugar de `when()` complejo
- Corregido `CallFactory`, `NewsPostFactory` y `DocumentFactory` para usar `firstOrCreate` y evitar colisiones de años académicos en tests paralelos

---

## Estadísticas Finales

- **Componentes Livewire:** 3 (Calendar, Index, Show)
- **Componentes UI:** 1 (Event Card - ya existía, mejorado)
- **Rutas públicas:** 3
- **Seeders:** 1 (66 eventos de prueba)
- **Tests:** 60 tests (95 assertions) - 100% pasando
- **Scopes en modelo:** 9 scopes útiles
- **Métodos helper:** 5 métodos helper

---

**Fecha de Creación:** Diciembre 2025  
**Última Actualización:** Diciembre 2025  
**Estado:** ✅ Completado y documentado

