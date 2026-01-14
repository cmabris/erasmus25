# Búsqueda Global

Este documento describe la funcionalidad de búsqueda global implementada en la aplicación Erasmus+ Centro (Murcia), que permite buscar contenido en múltiples entidades desde una única interfaz con detección automática de contexto (público vs administración).

## Índice

- [Descripción General](#descripción-general)
- [Características Principales](#características-principales)
- [Detección de Contexto](#detección-de-contexto)
- [Componente Livewire](#componente-livewire)
- [Rutas](#rutas)
- [Búsqueda por Entidades](#búsqueda-por-entidades)
- [Filtros Avanzados](#filtros-avanzados)
- [Vista y Diseño](#vista-y-diseño)
- [Traducciones](#traducciones)
- [Tests](#tests)
- [Uso y Ejemplos](#uso-y-ejemplos)

---

## Descripción General

La búsqueda global es una funcionalidad unificada que permite a los usuarios buscar contenido en cuatro tipos de entidades principales:

- **Programas** (`Program`)
- **Convocatorias** (`Call`)
- **Noticias** (`NewsPost`)
- **Documentos** (`Document`)

La característica principal es la **detección automática de contexto**, que adapta los enlaces de resultados según desde dónde se accede:

- **Desde área pública**: Los resultados enlazan a las vistas públicas (`programas.show`, `convocatorias.show`, etc.)
- **Desde área admin**: Los resultados enlazan a las vistas de administración (`admin.programs.show`, `admin.calls.show`, etc.)

---

## Características Principales

- ✅ **Búsqueda Unificada**: Busca en 4 tipos de contenido simultáneamente
- ✅ **Resultados Agrupados**: Resultados organizados por tipo con contadores
- ✅ **Filtros Avanzados**: Filtros por tipo de contenido, programa y año académico
- ✅ **Detección de Contexto**: Enlaces adaptativos según área (público/admin)
- ✅ **Layout Adaptativo**: Usa layout público o admin según contexto
- ✅ **Optimización**: Eager loading, límites de resultados, debounce
- ✅ **Responsive**: Diseño adaptativo para móviles, tablets y desktop
- ✅ **Tests Completos**: 24 tests pasando (50 assertions)

---

## Detección de Contexto

El componente detecta automáticamente el contexto desde el que se accede y adapta su comportamiento:

### Métodos de Detección

1. **Parámetro URL**: `?admin=true` cuando se accede desde el menú de administración
2. **Ruta Actual**: Detecta si la ruta actual es `admin.*`
3. **Referer**: Analiza el header `referer` para detectar navegación desde admin

### Implementación

```php
#[Computed]
public function isAdminContext(): bool
{
    // Check if admin flag is set
    if ($this->admin) {
        return true;
    }

    // Check if current route is admin
    if (request()->routeIs('admin.*')) {
        return true;
    }

    // Check referer if available
    $referer = request()->header('referer');
    if ($referer && str_contains($referer, '/admin')) {
        return true;
    }

    return false;
}
```

### Enlaces Dinámicos

El componente proporciona métodos helper para generar las rutas correctas:

```php
// Programas
$this->getProgramRoute($program)  // admin.programs.show o programas.show

// Convocatorias
$this->getCallRoute($call)        // admin.calls.show o convocatorias.show

// Noticias
$this->getNewsRoute($news)        // admin.news.show o noticias.show

// Documentos
$this->getDocumentRoute($document) // admin.documents.show o documentos.show
```

### Layout Adaptativo

El layout se adapta automáticamente:

```php
$layout = $this->isAdminContext()
    ? 'components.layouts.app'      // Layout con sidebar de admin
    : 'components.layouts.public';  // Layout público
```

---

## Componente Livewire

### Ubicación

- **Clase**: `App\Livewire\Search\GlobalSearch`
- **Vista**: `resources/views/livewire/search/global-search.blade.php`
- **Ruta**: `/buscar` (nombre: `search`)

### Propiedades Públicas

```php
#[Url(as: 'q')]
public string $query = '';              // Término de búsqueda

#[Url(as: 'tipos')]
public array $types = [                 // Tipos de contenido a buscar
    'programs', 'calls', 'news', 'documents'
];

#[Url(as: 'programa')]
public ?int $program = null;            // Filtro por programa

#[Url(as: 'ano')]
public ?int $academicYear = null;       // Filtro por año académico

public bool $showFilters = false;       // Mostrar/ocultar filtros

public int $limitPerType = 10;          // Límite de resultados por tipo

#[Url(as: 'admin')]
public bool $admin = false;             // Contexto admin (parámetro URL)
```

### Métodos Computados

```php
#[Computed]
public function availablePrograms(): Collection
// Lista de programas activos para filtro

#[Computed]
public function availableAcademicYears(): Collection
// Lista de años académicos para filtro

#[Computed]
public function results(): array
// Resultados agrupados por tipo: ['programs' => [...], 'calls' => [...], ...]

#[Computed]
public function totalResults(): int
// Total de resultados encontrados

#[Computed]
public function hasResults(): bool
// Verifica si hay resultados

#[Computed]
public function isAdminContext(): bool
// Detecta si estamos en contexto admin
```

### Métodos de Búsqueda

Cada tipo de entidad tiene su método de búsqueda privado:

```php
protected function searchPrograms(string $query): Collection
protected function searchCalls(string $query): Collection
protected function searchNews(string $query): Collection
protected function searchDocuments(string $query): Collection
```

### Métodos Helper de Rutas

```php
public function getProgramRoute(Program $program): string
public function getCallRoute(Call $call): string
public function getNewsRoute(NewsPost $news): string
public function getDocumentRoute(Document $document): string
```

### Métodos de Utilidad

```php
public function resetFilters(): void
// Resetea todos los filtros a valores por defecto

public function toggleType(string $type): void
// Activa/desactiva un tipo de contenido en la búsqueda

public function toggleFilters(): void
// Muestra/oculta el panel de filtros avanzados
```

---

## Rutas

### Ruta Pública

```php
Route::get('/buscar', GlobalSearch::class)->name('search');
```

**URLs de acceso:**
- Público: `/buscar`
- Admin: `/buscar?admin=true`

### Integración en Navegación

#### Navegación Pública

**Archivo**: `resources/views/components/nav/public-nav.blade.php`

```php
$navItems = [
    // ...
    ['label' => __('common.search.global_title'), 'route' => 'search', 'icon' => 'magnifying-glass'],
];
```

#### Navegación de Administración

**Archivo**: `resources/views/components/nav/admin-nav.blade.php`

```blade
<flux:navlist.item 
    icon="magnifying-glass" 
    :href="route('search', ['admin' => true])" 
    :current="request()->routeIs('search')" 
    wire:navigate
>
    {{ __('common.search.global_title') }}
</flux:navlist.item>
```

---

## Búsqueda por Entidades

### Programas

**Campos buscados:**
- `name` (nombre)
- `description` (descripción)
- `code` (código)

**Filtros aplicados:**
- Solo programas activos (`is_active = true`)
- Filtro opcional por `program_id`
- Ordenado por `order` y `name`
- Límite: 10 resultados

**Ejemplo de consulta:**
```php
Program::query()
    ->where('is_active', true)
    ->when($this->program, fn ($q) => $q->where('id', $this->program))
    ->where(function ($q) use ($query) {
        $q->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%");
    })
    ->orderBy('order')
    ->orderBy('name')
    ->limit($this->limitPerType)
    ->get();
```

### Convocatorias

**Campos buscados:**
- `title` (título)
- `requirements` (requisitos)
- `documentation` (documentación)

**Filtros aplicados:**
- Solo convocatorias con estado `abierta` o `cerrada`
- Solo convocatorias publicadas (`published_at IS NOT NULL`)
- Filtro opcional por `program_id` y `academic_year_id`
- Eager loading: `program`, `academicYear`
- Ordenado: primero abiertas, luego por fecha de publicación
- Límite: 10 resultados

### Noticias

**Campos buscados:**
- `title` (título)
- `excerpt` (resumen)
- `content` (contenido)

**Filtros aplicados:**
- Solo noticias publicadas (`status = 'publicado'`)
- Solo con `published_at IS NOT NULL`
- Filtro opcional por `program_id` y `academic_year_id`
- Eager loading: `program`, `academicYear`, `author`, `tags`
- Ordenado por fecha de publicación descendente
- Límite: 10 resultados

### Documentos

**Campos buscados:**
- `title` (título)
- `description` (descripción)

**Filtros aplicados:**
- Solo documentos activos (`is_active = true`)
- Filtro opcional por `program_id` y `academic_year_id`
- Eager loading: `category`, `program`, `academicYear`, `creator`
- Ordenado por fecha de creación descendente
- Límite: 10 resultados

---

## Filtros Avanzados

### Panel de Filtros

El panel de filtros avanzados es colapsable y se muestra/oculta con el botón "Filtros avanzados".

### Tipos de Filtros

1. **Tipos de Contenido** (Checkboxes múltiples):
   - Programas
   - Convocatorias
   - Noticias
   - Documentos

2. **Programa** (Select):
   - Lista de programas activos
   - Opción "Todos los programas"

3. **Año Académico** (Select):
   - Lista de años académicos
   - Opción "Todos los años"

### Implementación

```blade
@if($showFilters)
    <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4">
        {{-- Tipos de contenido --}}
        <div class="flex flex-wrap gap-3">
            @foreach(['programs', 'calls', 'news', 'documents'] as $type)
                <label class="inline-flex items-center gap-2">
                    <input
                        type="checkbox"
                        wire:model.live="types"
                        value="{{ $type }}"
                    />
                    <span>{{ __('common.search.' . $type) }}</span>
                </label>
            @endforeach
        </div>
        
        {{-- Filtros de programa y año académico --}}
        <!-- ... -->
    </div>
@endif
```

---

## Vista y Diseño

### Estructura de la Vista

1. **Header Section**:
   - Título y descripción
   - Campo de búsqueda principal
   - Botón de filtros avanzados
   - Botón de limpiar búsqueda

2. **Filtros Avanzados** (colapsable):
   - Checkboxes de tipos de contenido
   - Select de programa
   - Select de año académico

3. **Results Section**:
   - Resumen de resultados (total encontrado)
   - Resultados agrupados por tipo
   - Cards de resultados con información relevante
   - Estado vacío cuando no hay resultados

4. **Initial State**:
   - Mensaje de bienvenida
   - Instrucciones de uso

### Cards de Resultados

Cada tipo de entidad tiene su propio diseño de card:

#### Programas
- Título (enlace)
- Código del programa
- Descripción truncada (100 caracteres)

#### Convocatorias
- Título (enlace)
- Nombre del programa
- Badge de estado (Abierta/Cerrada)

#### Noticias
- Título (enlace)
- Excerpt (resumen)
- Fecha de publicación

#### Documentos
- Título (enlace)
- Nombre de categoría
- Descripción truncada (100 caracteres)

### Responsive Design

- **Móviles**: Filtros colapsables, cards en una columna
- **Tabletas**: Cards en 2 columnas
- **Desktop**: Cards en 3 columnas, filtros siempre visibles

---

## Traducciones

### Archivos de Traducción

- `lang/es/common.php` - Sección `search`
- `lang/en/common.php` - Sección `search`

### Claves de Traducción

```php
'search' => [
    'global_title' => 'Búsqueda Global',
    'global_description' => 'Busca en programas, convocatorias, noticias y documentos',
    'global_placeholder' => 'Buscar en programas, convocatorias, noticias...',
    'advanced_filters' => 'Filtros avanzados',
    'clear_search' => 'Limpiar búsqueda',
    'content_types' => 'Tipos de contenido',
    'programs' => 'Programas',
    'calls' => 'Convocatorias',
    'news' => 'Noticias',
    'documents' => 'Documentos',
    'all_programs' => 'Todos los programas',
    'all_years' => 'Todos los años',
    'results_found' => 'Se encontraron :total resultados',
    'no_results' => 'No se encontraron resultados',
    'no_results_message' => 'Intenta con otros términos de búsqueda o ajusta los filtros',
    'start_search' => 'Comienza tu búsqueda',
    'start_search_message' => 'Escribe en el campo de búsqueda para encontrar programas, convocatorias, noticias y documentos',
],
```

---

## Tests

### Ubicación

**Archivo**: `tests/Feature/Search/GlobalSearchTest.php`

### Cobertura

- ✅ **24 tests pasando** (50 assertions)

### Tests Implementados

#### Tests Básicos
- Renderizado del componente
- Estado inicial sin query
- Búsqueda en cada tipo de entidad
- Búsqueda en todos los tipos por defecto
- Mensaje cuando no hay resultados

#### Tests de Filtros
- Filtro por programa
- Filtro por año académico
- Toggle de tipos de contenido
- Toggle de panel de filtros
- Reset de filtros

#### Tests de Validación
- Solo muestra programas activos
- Solo muestra convocatorias publicadas
- Solo muestra noticias publicadas
- Solo muestra documentos activos
- Límite de resultados por tipo

#### Tests de Contexto
- Usa rutas públicas desde área pública
- Usa rutas admin desde área admin
- Detecta contexto admin desde parámetro
- Detecta contexto público por defecto

#### Tests de Ruta
- Acceso a ruta de búsqueda

---

## Uso y Ejemplos

### Acceso desde Área Pública

1. **Desde navegación pública**:
   - Clic en "Búsqueda Global" en el menú principal
   - Navega a `/buscar`
   - Los resultados enlazan a vistas públicas

2. **URL directa**:
   ```
   http://erasmus25.test/buscar
   ```

### Acceso desde Área Admin

1. **Desde navegación de administración**:
   - Clic en "Búsqueda Global" en el sidebar
   - Navega a `/buscar?admin=true`
   - Los resultados enlazan a vistas de administración
   - Usa layout de administración con sidebar

2. **URL directa**:
   ```
   http://erasmus25.test/buscar?admin=true
   ```

### Ejemplo de Búsqueda

1. Usuario escribe "Movilidad" en el campo de búsqueda
2. El componente busca automáticamente (debounce 300ms)
3. Muestra resultados agrupados:
   - **Programas** (2): Programa de Movilidad Estudiantil, ...
   - **Convocatorias** (3): Convocatoria de Movilidad KA1, ...
   - **Noticias** (1): Nueva oportunidad de movilidad, ...
   - **Documentos** (1): Guía de movilidad, ...

4. Usuario hace clic en un resultado
5. Navega a la vista correspondiente según contexto:
   - Si viene de público → `/programas/programa-de-movilidad`
   - Si viene de admin → `/admin/programas/1`

### Filtros Avanzados

1. Usuario hace clic en "Filtros avanzados"
2. Se muestra panel con:
   - Checkboxes para seleccionar tipos de contenido
   - Select para filtrar por programa
   - Select para filtrar por año académico
3. Usuario selecciona solo "Convocatorias" y programa "KA1"
4. Los resultados se filtran automáticamente
5. Solo muestra convocatorias del programa KA1

---

## Optimizaciones Implementadas

### Rendimiento

1. **Eager Loading**:
   - Todas las consultas cargan relaciones necesarias
   - Evita N+1 queries

2. **Límites de Resultados**:
   - Máximo 10 resultados por tipo inicialmente
   - Reduce carga de datos

3. **Debounce**:
   - Búsqueda con debounce de 300ms
   - Evita búsquedas excesivas mientras el usuario escribe

4. **Consultas Optimizadas**:
   - Uso de índices de base de datos
   - Consultas eficientes con `where` y `when`

### Seguridad

1. **Validación**:
   - Solo muestra contenido público/activo según entidad
   - Respeto de filtros de publicación

2. **Autorización**:
   - No requiere permisos especiales (búsqueda pública)
   - Los enlaces de admin requieren permisos correspondientes

---

## Estructura de Archivos

```
app/
  Livewire/
    Search/
      GlobalSearch.php              # Componente principal

resources/
  views/
    livewire/
      search/
        global-search.blade.php     # Vista del componente

routes/
  web.php                           # Ruta /buscar

lang/
  es/
    common.php                      # Traducciones ES (sección search)
  en/
    common.php                      # Traducciones EN (sección search)

tests/
  Feature/
    Search/
      GlobalSearchTest.php          # Tests del componente
```

---

## Mejoras Futuras (Opcional)

### Historial de Búsquedas

- Guardar búsquedas recientes en sesión o base de datos
- Mostrar dropdown con historial
- Permitir seleccionar búsqueda anterior

### Highlight de Términos

- Resaltar términos buscados en resultados
- Usar `<mark>` tag con estilos apropiados

### Paginación por Tipo

- Añadir "Ver más" para cada tipo de resultado
- Paginación independiente por tipo

### Búsqueda Full-Text

- Considerar full-text search para grandes volúmenes
- Mejorar relevancia de resultados

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: ✅ COMPLETADO  
**Tests**: 24 tests pasando (50 assertions)  
**Cobertura**: Funcionalidad completa implementada y probada
