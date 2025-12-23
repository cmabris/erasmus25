# Documentación Técnica: Componentes de la Página Home

Este documento describe la arquitectura y uso de los componentes creados para la página principal (Home) de la aplicación Erasmus+ Centro (Murcia).

---

## Índice

1. [Arquitectura General](#arquitectura-general)
2. [Componentes UI Base](#componentes-ui-base)
3. [Componentes de Contenido](#componentes-de-contenido)
4. [Layout Público](#layout-público)
5. [Componente Livewire Home](#componente-livewire-home)
6. [Paleta de Colores Erasmus+](#paleta-de-colores-erasmus)
7. [Guía de Uso](#guía-de-uso)

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                     Layout Público                               │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Public Nav                                ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │              Livewire Component (Home)                       ││
│  │  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐        ││
│  │  │  x-ui.card   │ │ x-ui.button  │ │  x-ui.badge  │  ...   ││
│  │  └──────────────┘ └──────────────┘ └──────────────┘        ││
│  │  ┌──────────────────────────────────────────────────┐      ││
│  │  │         x-content.program-card                    │      ││
│  │  │         x-content.call-card                       │      ││
│  │  │         x-content.news-card                       │      ││
│  │  │         x-content.event-card                      │      ││
│  │  └──────────────────────────────────────────────────┘      ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                      Footer                                  ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

---

## Componentes UI Base

### x-ui.card

Componente de tarjeta versátil para contenido estructurado.

**Ubicación:** `resources/views/components/ui/card.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `variant` | string | `'default'` | Estilo visual: `default`, `elevated`, `bordered`, `flat`, `gradient` |
| `padding` | string | `'md'` | Espaciado interno: `none`, `sm`, `md`, `lg` |
| `hover` | bool | `false` | Efecto hover al pasar el mouse |
| `href` | string | `null` | URL para hacer la tarjeta clickeable |
| `rounded` | string | `'lg'` | Radio de bordes: `none`, `sm`, `md`, `lg`, `xl`, `full` |

**Slots:**
- Default: Contenido principal
- `header`: Cabecera de la tarjeta
- `footer`: Pie de la tarjeta
- `media`: Área multimedia (imagen, video)

**Ejemplo:**
```blade
<x-ui.card variant="elevated" hover>
    <x-slot:header>
        <h3>Título de la tarjeta</h3>
    </x-slot:header>
    
    <p>Contenido de la tarjeta</p>
    
    <x-slot:footer>
        <x-ui.button size="sm">Acción</x-ui.button>
    </x-slot:footer>
</x-ui.card>
```

---

### x-ui.badge

Etiqueta/badge para estados, categorías y metadatos.

**Ubicación:** `resources/views/components/ui/badge.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `variant` | string | `'primary'` | Color: `primary`, `secondary`, `success`, `warning`, `danger`, `info`, `erasmus`, `gold` |
| `size` | string | `'md'` | Tamaño: `xs`, `sm`, `md`, `lg` |
| `rounded` | string | `'full'` | Radio: `sm`, `md`, `lg`, `full` |
| `icon` | string | `null` | Nombre del icono Heroicons |

**Ejemplo:**
```blade
<x-ui.badge variant="success" icon="check-circle">
    Activo
</x-ui.badge>

<x-ui.badge variant="erasmus" size="lg">
    KA1
</x-ui.badge>
```

---

### x-ui.button

Botón avanzado con múltiples variantes y soporte para iconos.

**Ubicación:** `resources/views/components/ui/button.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `variant` | string | `'primary'` | Estilo: `primary`, `secondary`, `outline`, `ghost`, `danger`, `erasmus`, `gold` |
| `size` | string | `'md'` | Tamaño: `xs`, `sm`, `md`, `lg`, `xl` |
| `href` | string | `null` | URL para convertir en enlace |
| `disabled` | bool | `false` | Estado deshabilitado |
| `loading` | bool | `false` | Muestra spinner de carga |
| `iconLeft` | string | `null` | Icono a la izquierda |
| `iconRight` | string | `null` | Icono a la derecha |
| `fullWidth` | bool | `false` | Ancho completo |
| `navigate` | bool | `false` | Usar `wire:navigate` para SPA |

**Ejemplo:**
```blade
<x-ui.button variant="erasmus" iconRight="arrow-right" href="/programas" navigate>
    Ver programas
</x-ui.button>

<x-ui.button variant="outline" size="sm" loading>
    Cargando...
</x-ui.button>
```

---

### x-ui.section

Contenedor de sección con título y descripción.

**Ubicación:** `resources/views/components/ui/section.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `title` | string | `null` | Título de la sección |
| `subtitle` | string | `null` | Subtítulo/descripción |
| `background` | string | `'default'` | Fondo: `default`, `muted`, `erasmus`, `gradient` |
| `padding` | string | `'lg'` | Espaciado: `none`, `sm`, `md`, `lg`, `xl` |
| `centered` | bool | `false` | Centrar título y subtítulo |

**Slots:**
- Default: Contenido de la sección
- `actions`: Acciones en el header (botones, enlaces)

**Ejemplo:**
```blade
<x-ui.section title="Programas Destacados" subtitle="Descubre las oportunidades disponibles">
    <x-slot:actions>
        <x-ui.button variant="outline" href="/programas">Ver todos</x-ui.button>
    </x-slot:actions>
    
    <div class="grid grid-cols-3 gap-6">
        <!-- Contenido -->
    </div>
</x-ui.section>
```

---

### x-ui.stat-card

Tarjeta para mostrar estadísticas y métricas.

**Ubicación:** `resources/views/components/ui/stat-card.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `value` | string/int | requerido | Valor numérico o texto |
| `label` | string | requerido | Etiqueta descriptiva |
| `icon` | string | `null` | Nombre del icono |
| `trend` | string | `null` | Tendencia: `up`, `down`, `neutral` |
| `variant` | string | `'default'` | Estilo: `default`, `erasmus`, `gold` |

**Ejemplo:**
```blade
<x-ui.stat-card 
    value="150" 
    label="Plazas disponibles" 
    icon="users"
    trend="up"
    variant="erasmus"
/>
```

---

### x-ui.empty-state

Componente para mostrar cuando no hay datos disponibles.

**Ubicación:** `resources/views/components/ui/empty-state.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `title` | string | `'No hay datos'` | Título del mensaje |
| `description` | string | `null` | Descripción adicional |
| `icon` | string | `'inbox'` | Icono a mostrar |
| `actionLabel` | string | `null` | Texto del botón de acción |
| `actionHref` | string | `null` | URL del botón de acción |

**Ejemplo:**
```blade
<x-ui.empty-state
    title="No hay convocatorias abiertas"
    description="Vuelve a consultar próximamente para nuevas oportunidades."
    icon="document-text"
    actionLabel="Ver todas las convocatorias"
    actionHref="/convocatorias"
/>
```

---

## Componentes de Contenido

### x-content.program-card

Tarjeta especializada para mostrar programas Erasmus+.

**Ubicación:** `resources/views/components/content/program-card.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `program` | Program | `null` | Modelo Program de Eloquent |
| `variant` | string | `'default'` | Variante: `default`, `featured`, `compact` |
| `showBadge` | bool | `true` | Mostrar badge de código |
| `name` | string | `null` | Override del nombre |
| `code` | string | `null` | Override del código |
| `description` | string | `null` | Override de descripción |
| `slug` | string | `null` | Override del slug |
| `isActive` | bool | `true` | Estado activo |
| `href` | string | `null` | Override de URL |

**Configuración de colores por tipo:**
- **KA1**: Azul Erasmus (movilidad de personas)
- **KA2**: Esmeralda (cooperación)
- **KA3**: Amber (reformas políticas)
- **Default**: Zinc (otros programas)

**Ejemplo:**
```blade
{{-- Usando modelo --}}
<x-content.program-card :program="$program" variant="featured" />

{{-- Usando props individuales --}}
<x-content.program-card
    name="Erasmus+ KA1"
    code="KA1"
    description="Movilidad de estudiantes y personal"
    slug="ka1"
    variant="compact"
/>
```

---

### x-content.call-card

Tarjeta para mostrar convocatorias.

**Ubicación:** `resources/views/components/content/call-card.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `call` | Call | `null` | Modelo Call de Eloquent |
| `variant` | string | `'default'` | Variante: `default`, `featured`, `compact` |
| `showProgram` | bool | `true` | Mostrar badge del programa |

**Características:**
- Muestra programa y año académico
- Badge de estado (abierta, cerrada, etc.)
- Información de tipo, modalidad y plazas
- Fechas de inicio/fin formateadas
- Badge de urgencia si la fecha límite está próxima

**Ejemplo:**
```blade
<x-content.call-card :call="$call" variant="featured" />
```

---

### x-content.news-card

Tarjeta para mostrar noticias.

**Ubicación:** `resources/views/components/content/news-card.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `news` | NewsPost | `null` | Modelo NewsPost de Eloquent |
| `variant` | string | `'default'` | Variante: `default`, `featured`, `horizontal`, `minimal` |
| `showImage` | bool | `true` | Mostrar imagen destacada |

**Características:**
- Imagen destacada con placeholder si no existe
- Badge de programa relacionado
- Autor y fecha de publicación
- Extracto de contenido truncado
- Ubicación si está definida

**Ejemplo:**
```blade
<x-content.news-card :news="$newsPost" variant="horizontal" />
```

---

### x-content.event-card

Tarjeta para mostrar eventos del calendario.

**Ubicación:** `resources/views/components/content/event-card.blade.php`

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `event` | ErasmusEvent | `null` | Modelo ErasmusEvent de Eloquent |
| `variant` | string | `'default'` | Variante: `default`, `featured`, `compact`, `minimal` |
| `showDate` | bool | `true` | Mostrar bloque de fecha visual |

**Características:**
- Bloque visual de fecha (día/mes)
- Badge "Hoy" si el evento es hoy
- Hora de inicio/fin
- Ubicación
- Tipo de evento y programa relacionado

**Ejemplo:**
```blade
<x-content.event-card :event="$event" variant="compact" />
```

---

## Layout Público

### components/layouts/public.blade.php

Layout base para todas las páginas públicas.

**Props:**

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `title` | string | config('app.name') | Título de la página para SEO |
| `description` | string | `null` | Meta description |

**Estructura:**
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <!-- Meta tags, title, Vite assets -->
</head>
<body class="min-h-screen bg-white dark:bg-zinc-900">
    <!-- Skip link para accesibilidad -->
    <a href="#main-content" class="sr-only focus:not-sr-only ...">
        Saltar al contenido principal
    </a>
    
    <x-nav.public-nav />
    
    <main id="main-content">
        {{ $slot }}
    </main>
    
    <x-footer />
</body>
</html>
```

---

### components/nav/public-nav.blade.php

Barra de navegación responsive.

**Características:**
- Logo con enlace a home
- Menú de navegación principal (Programas, Convocatorias, Noticias, Documentos, Eventos)
- Selector de tema claro/oscuro
- Menú de usuario (login/dashboard según autenticación)
- Menú hamburguesa para móviles con Alpine.js
- Sticky en scroll

---

### components/footer.blade.php

Pie de página completo.

**Secciones:**
1. **Acerca de**: Descripción de la aplicación y logotipos institucionales
2. **Programas**: Enlaces a tipos de programas
3. **Enlaces rápidos**: Navegación secundaria
4. **Contacto**: Información de contacto

**Características:**
- Diseño responsive de 4 columnas
- Logotipos de Erasmus+ y Unión Europea
- Copyright dinámico con año actual
- Política de privacidad y términos

---

## Componente Livewire Home

### App\Livewire\Public\Home

**Ubicación:** `app/Livewire/Public/Home.php`

**Propiedades públicas:**

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| `$programs` | Collection | Programas activos (límite: 6) |
| `$calls` | Collection | Convocatorias abiertas (límite: 4) |
| `$news` | Collection | Noticias publicadas (límite: 3) |
| `$events` | Collection | Eventos próximos (límite: 5) |

**Métodos:**

```php
public function mount(): void
{
    $this->loadPrograms();
    $this->loadCalls();
    $this->loadNews();
    $this->loadEvents();
}

protected function loadPrograms(): void
{
    $this->programs = Program::query()
        ->where('is_active', true)
        ->orderBy('order')
        ->limit(6)
        ->get();
}

protected function loadCalls(): void
{
    $this->calls = Call::query()
        ->with(['program', 'academicYear'])
        ->where('status', 'abierta')
        ->whereNotNull('published_at')
        ->orderBy('published_at', 'desc')
        ->limit(4)
        ->get();
}

protected function loadNews(): void
{
    $this->news = NewsPost::query()
        ->with(['program', 'author'])
        ->where('status', 'publicado')
        ->whereNotNull('published_at')
        ->orderBy('published_at', 'desc')
        ->limit(3)
        ->get();
}

protected function loadEvents(): void
{
    $this->events = ErasmusEvent::query()
        ->with(['program', 'call'])
        ->where('is_public', true)
        ->where('start_date', '>=', now()->startOfDay())
        ->orderBy('start_date')
        ->limit(5)
        ->get();
}
```

**Vista:** `resources/views/livewire/public/home.blade.php`

**Secciones de la vista:**
1. **Hero Section**: Gradiente Erasmus+, título, descripción, CTAs, estadísticas
2. **Programas**: Grid de tarjetas de programas con variante featured para el primero
3. **Convocatorias**: Grid de tarjetas de convocatorias
4. **Noticias**: Layout magazine (1 featured + 2 secundarias)
5. **Eventos**: Timeline vertical de próximos eventos
6. **CTA Final**: Llamada a acción para suscripción al newsletter

---

## Paleta de Colores Erasmus+

**Ubicación:** `resources/css/app.css`

### Azul Erasmus (Primary)

```css
--color-erasmus-50: oklch(0.97 0.01 240);
--color-erasmus-100: oklch(0.93 0.02 240);
--color-erasmus-200: oklch(0.86 0.04 240);
--color-erasmus-300: oklch(0.76 0.08 240);
--color-erasmus-400: oklch(0.64 0.14 240);
--color-erasmus-500: oklch(0.53 0.19 240);   /* Color principal */
--color-erasmus-600: oklch(0.45 0.20 240);
--color-erasmus-700: oklch(0.39 0.18 240);
--color-erasmus-800: oklch(0.33 0.15 240);
--color-erasmus-900: oklch(0.28 0.12 240);
--color-erasmus-950: oklch(0.20 0.08 240);
```

### Dorado Erasmus (Accent)

```css
--color-gold-50: oklch(0.98 0.02 85);
--color-gold-100: oklch(0.95 0.05 85);
--color-gold-200: oklch(0.90 0.10 85);
--color-gold-300: oklch(0.84 0.14 85);
--color-gold-400: oklch(0.78 0.16 85);
--color-gold-500: oklch(0.72 0.17 85);       /* Color principal */
--color-gold-600: oklch(0.65 0.16 85);
--color-gold-700: oklch(0.55 0.14 85);
--color-gold-800: oklch(0.45 0.12 85);
--color-gold-900: oklch(0.38 0.10 85);
--color-gold-950: oklch(0.25 0.06 85);
```

### Uso en Tailwind

```blade
{{-- Backgrounds --}}
<div class="bg-erasmus-500 dark:bg-erasmus-600">...</div>
<div class="bg-gold-500 dark:bg-gold-600">...</div>

{{-- Text --}}
<p class="text-erasmus-700 dark:text-erasmus-300">...</p>

{{-- Borders --}}
<div class="border-erasmus-200 dark:border-erasmus-700">...</div>

{{-- Gradients --}}
<div class="bg-gradient-to-r from-erasmus-600 to-erasmus-800">...</div>
```

---

## Guía de Uso

### Crear una nueva página pública

```blade
{{-- resources/views/livewire/public/mi-pagina.blade.php --}}
<div>
    <x-ui.section title="Mi Sección" background="muted">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($items as $item)
                <x-ui.card hover>
                    <h3>{{ $item->title }}</h3>
                    <p>{{ $item->description }}</p>
                </x-ui.card>
            @endforeach
        </div>
    </x-ui.section>
</div>
```

```php
// app/Livewire/Public/MiPagina.php
class MiPagina extends Component
{
    public function render(): View
    {
        return view('livewire.public.mi-pagina')
            ->layout('components.layouts.public', [
                'title' => 'Mi Página',
                'description' => 'Descripción para SEO',
            ]);
    }
}
```

### Mostrar estados vacíos

```blade
@if($items->isEmpty())
    <x-ui.empty-state
        title="No hay elementos"
        description="Vuelve a consultar más tarde."
        icon="inbox"
    />
@else
    {{-- Contenido --}}
@endif
```

### Responsive Design

Usa los breakpoints de Tailwind para adaptar el layout:

```blade
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 lg:grid-cols-3 xl:grid-cols-4">
    @foreach($items as $item)
        <x-ui.card>...</x-ui.card>
    @endforeach
</div>
```

### Dark Mode

Todos los componentes soportan dark mode automáticamente. Usa las clases `dark:` para personalizar:

```blade
<div class="bg-white text-zinc-900 dark:bg-zinc-800 dark:text-white">
    Contenido con soporte dark mode
</div>
```

---

## Tests

### Ejecutar tests de componentes UI

```bash
php artisan test tests/Feature/Components/UiComponentsTest.php
```

### Ejecutar tests de componentes de contenido

```bash
php artisan test tests/Feature/Components/ContentCardsTest.php
```

### Ejecutar tests del layout público

```bash
php artisan test tests/Feature/Components/PublicLayoutTest.php
```

### Ejecutar tests del componente Home

```bash
php artisan test tests/Feature/Livewire/Public/HomeTest.php
```

### Ejecutar todos los tests relacionados

```bash
php artisan test tests/Feature/Components tests/Feature/Livewire/Public
```

---

**Fecha de Creación**: Diciembre 2025  
**Versión**: 1.0  
**Estado**: ✅ Documentación completa

