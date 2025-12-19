# Documentación Técnica: Componentes de Convocatorias

Este documento describe la arquitectura y uso de los componentes creados para el listado y detalle de convocatorias en la aplicación Erasmus+ Centro (Murcia).

---

## Índice

1. [Arquitectura General](#arquitectura-general)
2. [Componentes UI Nuevos](#componentes-ui-nuevos)
3. [Componentes Livewire](#componentes-livewire)
4. [Rutas](#rutas)
5. [Seeders](#seeders)
6. [Guía de Uso](#guía-de-uso)
7. [Tests](#tests)

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                     Layout Público                               │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Public Nav                                ││
│  │        [Convocatorias] activo cuando routeIs('convocatorias.*')│
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │              Livewire Component                              ││
│  │                                                              ││
│  │  Calls\Index          Calls\Show                             ││
│  │  ┌──────────────┐        ┌──────────────┐                   ││
│  │  │ x-ui.search  │        │ x-ui.bread   │                   ││
│  │  │ x-ui.section │        │ x-ui.section │                   ││
│  │  │ call-card    │        │ call-phase-  │                   ││
│  │  │ x-ui.empty   │        │   timeline   │                   ││
│  │  └──────────────┘        │ resolution-  │                   ││
│  │                          │   card       │                   ││
│  │                          │ news-card    │                   ││
│  │                          └──────────────┘                   ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                      Footer                                  ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

---

## Componentes UI Nuevos

### x-content.call-phase-timeline

Componente para mostrar las fases de una convocatoria en formato timeline.

**Ubicación:** `resources/views/components/content/call-phase-timeline.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `phases` | Collection/array | `[]` | Colección de fases de la convocatoria |
| `variant` | string | `'default'` | Variante: `default`, `compact` |

**Estados de fase:**

| Estado | Color | Icono | Descripción |
|--------|-------|-------|-------------|
| `current` | emerald | check-circle | Fase actual activa |
| `past` | zinc | check | Fase completada |
| `upcoming` | amber | clock | Fase próxima |

**Ejemplo:**
```blade
<x-content.call-phase-timeline :phases="$call->phases" />
```

**Características:**
- Detección automática del estado de cada fase (actual, pasada, próxima)
- Indicador visual con colores según estado
- Badge "Fase actual" para la fase activa
- Fechas de inicio y fin formateadas
- Soporte para dark mode
- Empty state cuando no hay fases

---

### x-content.resolution-card

Componente para mostrar resoluciones publicadas con información y descarga.

**Ubicación:** `resources/views/components/content/resolution-card.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `resolution` | Resolution | `null` | Modelo de resolución |
| `variant` | string | `'default'` | Variante: `default`, `compact` |

**Tipos de resolución:**

| Tipo | Etiqueta |
|------|----------|
| `provisional` | Provisional |
| `definitiva` | Definitiva |
| `rectificativa` | Rectificativa |
| `alegaciones` | Alegaciones |

**Ejemplo:**
```blade
<x-content.resolution-card :resolution="$resolution" />
```

**Características:**
- Muestra tipo de resolución con badge
- Fecha oficial y fecha de publicación
- Descripción y procedimiento de evaluación
- Asociación con fase de convocatoria
- Preparado para descarga de PDFs (Media Library)
- Variante compacta para listados
- Soporte para dark mode

**Nota:** La descarga de PDFs requiere implementar Laravel Media Library en el modelo `Resolution`.

---

## Componentes Livewire

### Calls\Index

Listado público de convocatorias con filtros avanzados y búsqueda.

**Ubicación:** `app/Livewire/Public/Calls/Index.php`

**Propiedades públicas:**

| Propiedad | Tipo | URL Param | Descripción |
|-----------|------|-----------|-------------|
| `$search` | string | `q` | Término de búsqueda |
| `$program` | string | `programa` | ID del programa |
| `$academicYear` | string | `ano` | ID del año académico |
| `$type` | string | `tipo` | Tipo: `alumnado`, `personal` |
| `$modality` | string | `modalidad` | Modalidad: `corta`, `larga` |
| `$status` | string | `estado` | Estado: `abierta`, `cerrada` |

**Computed Properties:**

```php
#[Computed]
public function programTypes(): array
{
    return [
        '' => __('Todos los tipos'),
        'alumnado' => __('Alumnado'),
        'personal' => __('Personal'),
    ];
}

#[Computed]
public function modalities(): array
{
    return [
        '' => __('Todas las modalidades'),
        'corta' => __('Corta duración'),
        'larga' => __('Larga duración'),
    ];
}

#[Computed]
public function statuses(): array
{
    return [
        '' => __('Todos los estados'),
        'abierta' => __('Abierta'),
        'cerrada' => __('Cerrada'),
    ];
}

#[Computed]
public function availablePrograms(): Collection
{
    // Programas activos ordenados
}

#[Computed]
public function availableAcademicYears(): Collection
{
    // Años académicos ordenados por año desc
}

#[Computed]
public function stats(): array
{
    return [
        'total' => Call::whereIn('status', ['abierta', 'cerrada'])
            ->whereNotNull('published_at')->count(),
        'abierta' => Call::where('status', 'abierta')
            ->whereNotNull('published_at')->count(),
        'cerrada' => Call::where('status', 'cerrada')
            ->whereNotNull('published_at')->count(),
    ];
}

#[Computed]
public function calls(): LengthAwarePaginator
{
    // Retorna convocatorias filtradas y paginadas (12 por página)
    // Solo muestra: status IN ('abierta', 'cerrada') AND published_at IS NOT NULL
    // Orden: abiertas primero, luego por published_at desc
}
```

**Métodos públicos:**

| Método | Descripción |
|--------|-------------|
| `resetFilters()` | Limpia todos los filtros |
| `updatedSearch()` | Reset de paginación al cambiar búsqueda |
| `updatedProgram()` | Reset de paginación al cambiar programa |
| `updatedAcademicYear()` | Reset de paginación al cambiar año |
| `updatedType()` | Reset de paginación al cambiar tipo |
| `updatedModality()` | Reset de paginación al cambiar modalidad |
| `updatedStatus()` | Reset de paginación al cambiar estado |

**Vista:** `resources/views/livewire/public/calls/index.blade.php`

**Secciones:**
1. Hero section con estadísticas (total, abiertas, cerradas)
2. Barra de filtros (búsqueda, programa, año, tipo, modalidad, estado)
3. Badges de filtros activos con opción de eliminar
4. Grid de convocatorias (2 columnas en desktop)
5. Paginación
6. CTA final

**Filtros disponibles:**
- **Búsqueda:** Título, requisitos, documentación
- **Programa:** Select con programas activos
- **Año académico:** Select con años disponibles
- **Tipo:** Alumnado / Personal
- **Modalidad:** Corta / Larga duración
- **Estado:** Abierta / Cerrada

---

### Calls\Show

Detalle público de una convocatoria con información completa.

**Ubicación:** `app/Livewire/Public/Calls/Show.php`

**Propiedad pública:**

```php
public Call $call;
```

**Validación en mount:**
- Solo muestra convocatorias con `status IN ('abierta', 'cerrada')`
- Requiere `published_at IS NOT NULL`
- Retorna 404 si no cumple condiciones

**Computed Properties:**

```php
#[Computed]
public function callConfig(): array
{
    // Retorna configuración visual según estado
    // Para 'abierta': color emerald, icon check-circle
    // Para 'cerrada': color red, icon x-circle
    // icon, color, gradient, gradientDark, bgLight, textColor, badgeColor, statusLabel
}

#[Computed]
public function currentPhases(): Collection
{
    // Fases de la convocatoria ordenadas por order
}

#[Computed]
public function publishedResolutions(): Collection
{
    // Resoluciones publicadas (published_at IS NOT NULL)
    // Ordenadas por official_date desc, published_at desc
}

#[Computed]
public function relatedNews(): Collection
{
    // Noticias del programa (publicadas)
    // Ordenadas por published_at desc
    // Límite: 3
}

#[Computed]
public function otherCalls(): Collection
{
    // Otras convocatorias del mismo programa
    // Solo abiertas/cerradas y publicadas
    // Orden: abiertas primero, luego por published_at desc
    // Límite: 3
}
```

**Configuración por estado:**

| Estado | Color | Icono | Gradient |
|--------|-------|-------|----------|
| `abierta` | emerald | check-circle | from-emerald-500 to-emerald-600 |
| `cerrada` | red | x-circle | from-red-500 to-red-600 |

**Vista:** `resources/views/livewire/public/calls/show.blade.php`

**Secciones:**
1. Hero dinámico con color según estado
2. Breadcrumbs
3. Badges (programa, año académico, estado)
4. Tarjetas de información (Tipo, Modalidad, Plazas, Destinos)
5. Lista de países de destino
6. Fechas estimadas (si disponibles)
7. Requisitos
8. Documentación necesaria
9. Criterios de selección
10. Fases de la convocatoria (si existen)
11. Resoluciones publicadas (si existen)
12. Noticias relacionadas (si existen)
13. Otras convocatorias del programa (si existen)
14. CTA final

---

## Rutas

**Archivo:** `routes/web.php`

```php
// Rutas públicas de convocatorias
Route::get('/convocatorias', Calls\Index::class)->name('convocatorias.index');
Route::get('/convocatorias/{call:slug}', Calls\Show::class)->name('convocatorias.show');
```

**Ejemplos de URLs:**

| URL | Descripción |
|-----|-------------|
| `/convocatorias` | Listado de todas las convocatorias publicadas |
| `/convocatorias?programa=1` | Filtrado por programa |
| `/convocatorias?tipo=alumnado` | Filtrado por tipo alumnado |
| `/convocatorias?estado=abierta` | Solo convocatorias abiertas |
| `/convocatorias?q=movilidad` | Búsqueda por "movilidad" |
| `/convocatorias/convocatoria-movilidad-fp-2024-2025` | Detalle de convocatoria |

**Actualizaciones en otros componentes:**
- `call-card.blade.php`: Usa `route('convocatorias.show', $slug)`
- `home.blade.php`: Enlaces actualizados a `route('convocatorias.index')`

---

## Seeders

### CallSeeder

**Archivo:** `database/seeders/CallSeeder.php`

Crea convocatorias realistas con diferentes estados y características.

**Características:**
- 11 convocatorias creadas
- 8 convocatorias abiertas (con `published_at`)
- 3 convocatorias cerradas (con `published_at` y `closed_at`)
- Variaciones: alumnado/personal, corta/larga duración
- Destinos europeos variados (3-8 países por convocatoria)
- Datos completos: requisitos, documentación, criterios, baremo
- Fechas estimadas coherentes

**Ejecutar:**
```bash
php artisan db:seed --class=CallSeeder
```

---

### CallPhaseSeeder

**Archivo:** `database/seeders/CallPhaseSeeder.php`

Crea fases para todas las convocatorias publicadas.

**Fases creadas:**
1. **Publicación** - Fase inicial de publicación
2. **Periodo de solicitudes** - Marcada como actual si la convocatoria está abierta
3. **Listado provisional** - Solo para convocatorias cerradas
4. **Periodo de alegaciones** - Solo para convocatorias cerradas
5. **Listado definitivo** - Solo para convocatorias cerradas
6. **Renuncias y lista de espera** - Solo para convocatorias cerradas

**Características:**
- 34 fases creadas para 11 convocatorias
- Fechas calculadas dinámicamente según el estado de la convocatoria
- Fase actual marcada automáticamente para convocatorias abiertas

**Ejecutar:**
```bash
php artisan db:seed --class=CallPhaseSeeder
```

---

### ResolutionSeeder

**Archivo:** `database/seeders/ResolutionSeeder.php`

Crea resoluciones publicadas para convocatorias cerradas.

**Características:**
- 6 resoluciones creadas para 3 convocatorias cerradas
- Tipos: provisional, definitiva, alegaciones
- Asociadas a fases correspondientes
- Información de procedimiento de evaluación
- Preparado para Media Library (comentado en código)

**Ejecutar:**
```bash
php artisan db:seed --class=ResolutionSeeder
```

**Nota:** Para añadir PDFs a las resoluciones, descomentar y adaptar el código al final del seeder.

---

## Guía de Uso

### Añadir una nueva fase a una convocatoria

```php
CallPhase::create([
    'call_id' => $call->id,
    'phase_type' => 'publicacion',
    'name' => 'Publicación de la convocatoria',
    'description' => 'Descripción de la fase',
    'start_date' => now(),
    'end_date' => now()->addDays(7),
    'is_current' => true,
    'order' => 1,
]);
```

### Mostrar fases en una vista

```blade
@if($call->phases->isNotEmpty())
    <x-content.call-phase-timeline :phases="$call->phases" />
@endif
```

### Añadir una resolución

```php
Resolution::create([
    'call_id' => $call->id,
    'call_phase_id' => $phase->id,
    'type' => 'provisional',
    'title' => 'Resolución provisional',
    'description' => 'Descripción de la resolución',
    'evaluation_procedure' => 'Procedimiento de evaluación',
    'official_date' => now(),
    'published_at' => now(),
]);
```

### Mostrar resoluciones en una vista

```blade
@foreach($resolutions as $resolution)
    <x-content.resolution-card :resolution="$resolution" />
@endforeach
```

### Filtrar convocatorias en una consulta

```php
$calls = Call::query()
    ->whereIn('status', ['abierta', 'cerrada'])
    ->whereNotNull('published_at')
    ->when($programId, fn($q) => $q->where('program_id', $programId))
    ->when($type, fn($q) => $q->where('type', $type))
    ->orderByRaw("CASE WHEN status = 'abierta' THEN 0 ELSE 1 END")
    ->orderBy('published_at', 'desc')
    ->get();
```

---

## Tests

### Ejecutar tests de componentes de convocatorias

```bash
php artisan test tests/Feature/Livewire/Public/Calls
```

### Ejecutar un test específico

```bash
php artisan test tests/Feature/Livewire/Public/Calls/IndexTest.php
php artisan test tests/Feature/Livewire/Public/Calls/ShowTest.php
```

### Tests incluidos

**IndexTest.php (17 tests, 35 assertions):**
- Renderizado de página
- Solo muestra convocatorias publicadas con estado válido
- Búsqueda por título
- Filtros: programa, año académico, tipo, modalidad, estado
- Reset de filtros
- Empty state
- Estadísticas correctas
- Paginación
- SEO y breadcrumbs
- Enlaces a detalle
- Ordenamiento (abiertas primero)

**ShowTest.php (25 tests, 43 assertions):**
- Renderizado con convocatoria válida
- 404 para convocatorias no válidas (borrador, sin published_at, estado inválido)
- Información de la convocatoria
- Badges de estado (abierta/cerrada)
- Fases cuando están disponibles
- Resoluciones publicadas (solo publicadas)
- Noticias relacionadas
- Otras convocatorias del programa
- Breadcrumbs y SEO
- Destinos, fechas, tipo, modalidad, plazas
- Configuración de colores según estado

**Cobertura total:** 42 tests pasando, 78 assertions exitosas

---

## Consideraciones Importantes

### Seguridad y Visibilidad

1. **Solo convocatorias públicas:**
   - Estado debe ser `'abierta'` o `'cerrada'`
   - `published_at` debe ser no nulo
   - Cualquier otra combinación retorna 404

2. **Resoluciones:**
   - Solo se muestran resoluciones con `published_at IS NOT NULL`
   - Las resoluciones sin publicar no aparecen en la vista pública

3. **Fases:**
   - Todas las fases se muestran si existen
   - La fase actual se detecta automáticamente según fechas

### Performance

- Uso de `with()` para eager loading de relaciones
- Paginación en listados (12 por página)
- Límites en relaciones (3-4 items máximo)
- Índices en base de datos para búsquedas rápidas

### Extensibilidad

- Preparado para Media Library en resoluciones
- Componentes reutilizables (`call-phase-timeline`, `resolution-card`)
- Fácil añadir nuevos filtros o campos
- Estructura escalable para futuras funcionalidades

---

**Fecha de Creación**: Diciembre 2025  
**Versión**: 1.0  
**Estado**: ✅ Documentación completa
