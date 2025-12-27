# Documentación Técnica: Componentes de Programas

Este documento describe la arquitectura y uso de los componentes creados para el listado y detalle de programas en la aplicación Erasmus+ Centro (Murcia).

---

## Índice

1. [Arquitectura General](#arquitectura-general)
2. [Componentes UI Nuevos](#componentes-ui-nuevos)
3. [Componentes Livewire](#componentes-livewire)
4. [Rutas](#rutas)
5. [Seeder de Programas](#seeder-de-programas)
6. [Guía de Uso](#guía-de-uso)

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                     Layout Público                               │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Public Nav                                ││
│  │        [Programas] activo cuando routeIs('programas.*')      ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │              Livewire Component                              ││
│  │                                                              ││
│  │  Programs\Index          Programs\Show                       ││
│  │  ┌──────────────┐        ┌──────────────┐                   ││
│  │  │ x-ui.search  │        │ x-ui.bread   │                   ││
│  │  │ x-ui.section │        │ x-ui.section │                   ││
│  │  │ program-card │        │ call-card    │                   ││
│  │  │ x-ui.empty   │        │ news-card    │                   ││
│  │  └──────────────┘        │ program-card │                   ││
│  │                          └──────────────┘                   ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                      Footer                                  ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

---

## Componentes UI Nuevos

### x-ui.breadcrumbs

Componente de navegación contextual (migas de pan).

**Ubicación:** `resources/views/components/ui/breadcrumbs.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `items` | array | `[]` | Array de items con `label`, `href`, `icon` |
| `separator` | string | `'chevron-right'` | Tipo de separador: `chevron-right`, `slash`, `arrow-right` |
| `homeIcon` | bool | `true` | Mostrar icono de inicio en lugar de texto |

**Estructura de items:**
```php
[
    ['label' => 'Programas', 'href' => route('programas.index')],
    ['label' => 'Movilidad FP', 'icon' => 'academic-cap'], // Último item sin href
]
```

**Ejemplo:**
```blade
<x-ui.breadcrumbs 
    :items="[
        ['label' => __('Programas'), 'href' => route('programas.index')],
        ['label' => $program->name],
    ]" 
    class="text-white/60 [&_a:hover]:text-white"
/>
```

**Características:**
- Enlace a inicio siempre presente
- Separadores configurables
- Soporte para iconos opcionales
- Último item como texto sin enlace (página actual)
- Accesibilidad completa (aria-label, aria-current)
- Soporte para dark mode
- Navegación SPA con `wire:navigate`

---

### x-ui.search-input

Input de búsqueda estilizado con integración Livewire.

**Ubicación:** `resources/views/components/ui/search-input.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `placeholder` | string | `'Buscar...'` | Texto placeholder |
| `size` | string | `'md'` | Tamaño: `sm`, `md`, `lg` |
| `icon` | string | `'magnifying-glass'` | Icono de búsqueda |
| `clearable` | bool | `true` | Mostrar botón de limpieza |
| `loading` | bool | `false` | Mostrar spinner de carga |

**Ejemplo:**
```blade
<x-ui.search-input 
    wire:model.live.debounce.300ms="search" 
    :placeholder="__('Buscar programa...')"
    size="md"
/>
```

**Características:**
- Integración nativa con Livewire
- Botón de limpieza con Alpine.js (aparece solo cuando hay texto)
- Estado de carga animado
- Estilos para focus y dark mode
- Input `type="search"` para mejor UX

---

## Componentes Livewire

### Programs\Index

Listado público de programas con filtros y búsqueda.

**Ubicación:** `app/Livewire/Public/Programs/Index.php`

**Propiedades públicas:**

| Propiedad | Tipo | URL Param | Descripción |
|-----------|------|-----------|-------------|
| `$search` | string | `q` | Término de búsqueda |
| `$type` | string | `tipo` | Filtro por tipo (KA1, KA2, JM, DISCOVER) |
| `$onlyActive` | bool | `activos` | Solo programas activos |

**Computed Properties:**

```php
#[Computed]
public function programTypes(): array
{
    return [
        '' => __('Todos los tipos'),
        'KA1' => __('KA1 - Movilidad'),
        'KA2' => __('KA2 - Cooperación'),
        'JM' => __('Jean Monnet'),
        'DISCOVER' => __('DiscoverEU'),
    ];
}

#[Computed]
public function stats(): array
{
    return [
        'total' => Program::count(),
        'active' => Program::where('is_active', true)->count(),
        'mobility' => Program::where('code', 'like', 'KA1%')->where('is_active', true)->count(),
        'cooperation' => Program::where('code', 'like', 'KA2%')->where('is_active', true)->count(),
    ];
}

#[Computed]
public function programs(): LengthAwarePaginator
{
    // Retorna programas filtrados y paginados (9 por página)
}
```

**Métodos públicos:**

| Método | Descripción |
|--------|-------------|
| `resetFilters()` | Limpia todos los filtros |
| `updatedSearch()` | Reset de paginación al cambiar búsqueda |
| `updatedType()` | Reset de paginación al cambiar tipo |
| `updatedOnlyActive()` | Reset de paginación al cambiar toggle |

**Vista:** `resources/views/livewire/public/programs/index.blade.php`

**Secciones:**
1. Hero section con estadísticas
2. Barra de filtros
3. Badges de filtros activos
4. Grid de programas (3 columnas en desktop)
5. Paginación
6. CTA final

---

### Programs\Show

Detalle público de un programa con contenido relacionado.

**Ubicación:** `app/Livewire/Public/Programs/Show.php`

**Propiedad pública:**

```php
public Program $program;
```

**Computed Properties:**

```php
#[Computed]
public function programConfig(): array
{
    // Retorna configuración visual según código del programa
    // icon, color, gradient, bgLight, textColor, badgeColor, type
}

#[Computed]
public function relatedCalls(): Collection
{
    // Convocatorias del programa (abiertas/cerradas, publicadas)
    // Ordenadas: abiertas primero, luego por fecha de publicación
    // Límite: 4
}

#[Computed]
public function relatedNews(): Collection
{
    // Noticias del programa (publicadas)
    // Ordenadas por fecha de publicación desc
    // Límite: 3
}

#[Computed]
public function otherPrograms(): Collection
{
    // Otros programas activos (excluyendo el actual)
    // Ordenados por order
    // Límite: 3
}
```

**Configuración por tipo de programa:**

| Código contiene | Color | Icono | Tipo mostrado |
|-----------------|-------|-------|---------------|
| VET | emerald | briefcase | Formación Profesional |
| HED | violet | building-library | Educación Superior |
| SCH | blue | academic-cap | Educación Escolar |
| ADU | teal | users | Educación de Adultos |
| KA1 | blue | academic-cap | Movilidad |
| KA2 | amber | users | Cooperación |
| JM | indigo | building-office-2 | Jean Monnet |
| DISCOVER | rose | map | DiscoverEU |

**Vista:** `resources/views/livewire/public/programs/show.blade.php`

**Secciones:**
1. Hero dinámico con color según tipo
2. Breadcrumbs
3. Badges de información
4. "Acerca de este programa"
5. Tarjetas de información (Ámbito, Destinatarios, Convocatorias)
6. Convocatorias relacionadas (si existen)
7. Noticias relacionadas (si existen)
8. Empty state (si no hay contenido)
9. Otros programas sugeridos
10. CTA final

---

## Rutas

**Archivo:** `routes/web.php`

```php
// Rutas públicas de programas
Route::get('/programas', Programs\Index::class)->name('programas.index');
Route::get('/programas/{program:slug}', Programs\Show::class)->name('programas.show');
```

**Ejemplos de URLs:**

| URL | Descripción |
|-----|-------------|
| `/programas` | Listado de todos los programas activos |
| `/programas?tipo=KA1` | Filtrado por tipo KA1 |
| `/programas?q=formacion` | Búsqueda por "formacion" |
| `/programas?tipo=KA2&activos=0` | KA2 incluyendo inactivos |
| `/programas/movilidad-formacion-profesional` | Detalle del programa |

---

## Seeder de Programas

**Archivo:** `database/seeders/ProgramsSeeder.php`

**Programas incluidos:**

| Código | Nombre | Tipo | Activo |
|--------|--------|------|--------|
| KA121-SCH | Movilidad Educación Escolar | KA1 | ✅ |
| KA121-VET | Movilidad Formación Profesional | KA1 | ✅ |
| KA131-HED | Movilidad Educación Superior | KA1 | ✅ |
| KA122-ADU | Movilidad Educación de Adultos | KA1 | ✅ |
| KA220-SCH | Asociaciones de Cooperación Escolar | KA2 | ✅ |
| KA220-VET | Asociaciones de Cooperación FP | KA2 | ✅ |
| KA210-SCH | Asociaciones a Pequeña Escala | KA2 | ✅ |
| JM-HEI | Jean Monnet - Educación Superior | JM | ✅ |
| DISCOVER-EU | DiscoverEU | DISCOVER | ❌ |
| KA1-2014 | Movilidad 2014-2020 (Histórico) | KA1 | ❌ |

**Ejecutar el seeder:**
```bash
php artisan db:seed --class=ProgramsSeeder
```

---

## Guía de Uso

### Añadir una nueva página de listado

```php
// 1. Crear el componente Livewire
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Computed]
    public function items(): LengthAwarePaginator
    {
        return Model::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->paginate(9);
    }

    public function render(): View
    {
        return view('livewire.public.items.index')
            ->layout('components.layouts.public', [
                'title' => __('Listado'),
            ]);
    }
}
```

### Añadir breadcrumbs a una página

```blade
<x-ui.breadcrumbs 
    :items="[
        ['label' => __('Sección'), 'href' => route('seccion.index')],
        ['label' => $item->name],
    ]" 
/>
```

### Usar el search-input con Livewire

```blade
<x-ui.search-input 
    wire:model.live.debounce.300ms="search" 
    :placeholder="__('Buscar...')"
/>
```

### Mostrar empty state con acción

```blade
@if($items->isEmpty())
    <x-ui.empty-state 
        :title="__('No hay resultados')"
        :description="__('Prueba con otros términos de búsqueda.')"
        icon="magnifying-glass"
    >
        <x-ui.button wire:click="resetFilters" variant="outline">
            {{ __('Limpiar filtros') }}
        </x-ui.button>
    </x-ui.empty-state>
@endif
```

---

## Tests

### Ejecutar tests de componentes de programas

```bash
php artisan test tests/Feature/Livewire/Public/Programs
```

### Ejecutar un test específico

```bash
php artisan test tests/Feature/Livewire/Public/Programs/IndexTest.php
php artisan test tests/Feature/Livewire/Public/Programs/ShowTest.php
```

### Tests incluidos

**IndexTest.php (15 tests):**
- Renderizado de página
- Visualización de programas activos
- Filtros por tipo
- Búsqueda por nombre/código
- Reset de filtros
- Empty state
- Paginación
- SEO y breadcrumbs

**ShowTest.php (19 tests):**
- Renderizado con programa válido
- 404 para programa inexistente
- Información del programa
- Badges de estado
- Convocatorias relacionadas
- Noticias relacionadas
- Otros programas sugeridos
- Configuración por tipo
- Empty state

---

**Fecha de Creación**: Diciembre 2025  
**Versión**: 1.0  
**Estado**: ✅ Documentación completa


