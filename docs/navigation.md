# Navegación Principal

Este documento describe la estructura y uso de la navegación principal de la aplicación Erasmus+ Centro (Murcia), tanto para el área pública como para el panel de administración.

## Índice

- [Navegación Pública](#navegación-pública)
- [Navegación de Administración](#navegación-de-administración)
- [Selector de Idioma](#selector-de-idioma)
- [Añadir Nuevos Elementos](#añadir-nuevos-elementos)
- [Permisos y Autorización](#permisos-y-autorización)

---

## Navegación Pública

### Componente

**Archivo**: `resources/views/components/nav/public-nav.blade.php`

**Uso**:
```blade
<x-nav.public-nav />
<x-nav.public-nav :transparent="true" />
```

### Características

- ✅ Menú responsive (desktop y móvil)
- ✅ Selector de idioma integrado
- ✅ Enlaces según autenticación y permisos
- ✅ Logo y nombre del centro configurable
- ✅ Soporte para modo transparente (hero sections)
- ✅ Posicionamiento sticky
- ✅ Accesibilidad (ARIA labels, navegación por teclado)

### Estructura

#### Elementos del Menú

Los elementos del menú se definen en el array `$navItems`:

```php
$navItems = [
    ['label' => __('common.nav.home'), 'route' => 'home', 'icon' => 'home'],
    ['label' => __('common.nav.programs'), 'route' => 'programas.index', 'icon' => 'academic-cap'],
    ['label' => __('common.nav.calls'), 'route' => 'convocatorias.index', 'icon' => 'document-text'],
    ['label' => __('common.nav.news'), 'route' => 'noticias.index', 'icon' => 'newspaper'],
    ['label' => __('common.nav.documents'), 'route' => 'documentos.index', 'icon' => 'folder-open'],
    ['label' => __('common.nav.calendar'), 'route' => 'calendario', 'icon' => 'calendar-days'],
];
```

#### Enlaces de Autenticación

**Para usuarios no autenticados**:
- Enlace "Iniciar sesión" (`route('login')`)
- Enlace "Registrarse" (`route('register')`) - si está habilitado

**Para usuarios autenticados sin permisos de admin**:
- Enlace "Panel" (`route('dashboard')`)

**Para usuarios autenticados con permisos de admin**:
- Enlace "Panel de Administración" (`route('admin.dashboard')`)
- Requiere permisos: `programs.view` o `users.view`

### Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `transparent` | `bool` | `false` | Si es `true`, el fondo es transparente (útil para hero sections) |

### Ejemplo de Uso

```blade
{{-- Navegación normal --}}
<x-nav.public-nav />

{{-- Navegación transparente para hero section --}}
<x-nav.public-nav :transparent="true" />
```

---

## Navegación de Administración

### Componente

**Archivo**: `resources/views/components/nav/admin-nav.blade.php`

**Uso**: Se incluye automáticamente en el sidebar de administración (`components/layouts/app/sidebar.blade.php`)

```blade
<x-nav.admin-nav />
```

### Características

- ✅ Sidebar con Flux UI
- ✅ Enlaces según permisos usando `@can`
- ✅ Selector de idioma integrado
- ✅ Grupos organizados lógicamente
- ✅ Menú responsive (stashable)
- ✅ Menú de usuario (desktop y móvil)

### Estructura de Grupos

La navegación de administración está organizada en grupos lógicos:

#### 1. Platform
- **Dashboard** (`admin.dashboard`)
  - Visible para: Todos los usuarios autenticados

#### 2. Contenido
Grupo visible si el usuario tiene acceso a al menos uno de:
- `programs.view`
- `calls.view`
- `news.view`
- `documents.view`
- `events.view`

**Elementos**:
- **Programas** (`admin.programs.index`) - Requiere `programs.view`
- **Convocatorias** (`admin.calls.index`) - Requiere `calls.view`
- **Noticias** (`admin.news.index`) - Requiere `news.view`
- **Etiquetas de Noticias** (`admin.news-tags.index`) - Requiere `news-tags.view`
- **Documentos** (`admin.documents.index`) - Requiere `documents.view`
- **Categorías de Documentos** (`admin.document-categories.index`) - Requiere `document-categories.view`
- **Eventos** (`admin.events.index`) - Requiere `events.view`

#### 3. Gestión
- **Años Académicos** (`admin.academic-years.index`)
  - Visible para: Todos los usuarios autenticados (según `AcademicYearPolicy`)

#### 4. Sistema
Grupo visible si el usuario tiene `users.view`

**Elementos**:
- **Usuarios** (`admin.users.index`) - Requiere `users.view`
- **Roles y Permisos** (`admin.roles.index`) - Requiere `roles.view`
- **Configuración** (`admin.settings.index`) - Requiere `settings.view`
- **Traducciones** (`admin.translations.index`) - Requiere `translations.view`
- **Auditoría y Logs** (`admin.audit-logs.index`) - Requiere `audit-logs.view`
- **Suscripciones Newsletter** (`admin.newsletter.index`) - Requiere `newsletter.view`

### Selector de Idioma

El selector de idioma está integrado en:
- **Desktop**: Parte inferior del sidebar, antes del enlace al centro
- **Móvil**: Header móvil, junto al toggle del sidebar

---

## Selector de Idioma

### Componente

**Componente Livewire**: `App\Livewire\Language\Switcher`

**Uso**:
```blade
<livewire:language.switcher variant="dropdown" size="md" />
<livewire:language.switcher variant="select" size="md" />
<livewire:language.switcher variant="buttons" size="sm" />
```

### Variantes

| Variante | Descripción | Uso Recomendado |
|----------|-------------|-----------------|
| `dropdown` | Menú desplegable | Desktop (navegación pública y admin) |
| `select` | Select HTML nativo | Móvil |
| `buttons` | Botones con códigos de idioma | Compacto |

### Tamaños

| Tamaño | Descripción |
|--------|-------------|
| `sm` | Pequeño (sidebar admin) |
| `md` | Mediano (navegación pública) |
| `lg` | Grande |

### Integración

**Navegación Pública**:
- Desktop: Variante `dropdown`, tamaño `md`
- Móvil: Variante `select`, tamaño `md`

**Navegación de Administración**:
- Desktop: Variante `dropdown`, tamaño `sm` (en sidebar)
- Móvil: Variante `dropdown`, tamaño `sm` (en header)

---

## Añadir Nuevos Elementos

### Añadir Enlace a Navegación Pública

1. Editar `resources/views/components/nav/public-nav.blade.php`
2. Añadir el elemento al array `$navItems`:

```php
$navItems = [
    // ... elementos existentes
    ['label' => __('common.nav.nuevo_elemento'), 'route' => 'nueva.ruta', 'icon' => 'icono'],
];
```

3. Añadir la traducción en `lang/*/common.php`:

```php
'nav' => [
    // ... traducciones existentes
    'nuevo_elemento' => 'Nuevo Elemento', // ES
    'nuevo_elemento' => 'New Element',     // EN
],
```

### Añadir Enlace a Navegación de Administración

1. Editar `resources/views/components/nav/admin-nav.blade.php`
2. Añadir el elemento al grupo apropiado:

```blade
{{-- Ejemplo: Añadir al grupo de Contenido --}}
@can('viewAny', \App\Models\NuevoModelo::class)
    <flux:navlist.item 
        icon="icono" 
        :href="route('admin.nuevo-modelo.index')" 
        :current="request()->routeIs('admin.nuevo-modelo.*')" 
        wire:navigate
    >
        {{ __('common.nav.nuevo_modelo') }}
    </flux:navlist.item>
@endcan
```

3. Asegurar que existe la Policy correspondiente
4. Añadir la traducción en `lang/*/common.php`

### Crear Nuevo Grupo

Si necesitas crear un nuevo grupo de navegación:

```blade
@can('viewAny', \App\Models\NuevoModelo::class)
    <flux:navlist.group :heading="__('common.admin.nav.nuevo_grupo')" class="grid">
        <flux:navlist.item 
            icon="icono" 
            :href="route('admin.nuevo-modelo.index')" 
            :current="request()->routeIs('admin.nuevo-modelo.*')" 
            wire:navigate
        >
            {{ __('common.nav.nuevo_modelo') }}
        </flux:navlist.item>
    </flux:navlist.group>
@endcan
```

Añadir la traducción del heading:

```php
'admin' => [
    'nav' => [
        // ... traducciones existentes
        'nuevo_grupo' => 'Nuevo Grupo', // ES
        'nuevo_grupo' => 'New Group',   // EN
    ],
],
```

---

## Permisos y Autorización

### Navegación Pública

La navegación pública verifica permisos para mostrar el enlace al panel de administración:

```blade
@if(auth()->user()?->can('viewAny', \App\Models\Program::class) || 
    auth()->user()?->can('viewAny', \App\Models\User::class))
    {{-- Mostrar enlace al panel de administración --}}
@else
    {{-- Mostrar enlace al dashboard normal --}}
@endif
```

**Permisos requeridos para panel de administración**:
- `programs.view` O
- `users.view`

### Navegación de Administración

La navegación de administración usa Policies de Laravel para verificar permisos:

```blade
@can('viewAny', \App\Models\Program::class)
    {{-- Mostrar enlace --}}
@endcan
```

**Verificación de grupos**:
- Los grupos se muestran solo si el usuario tiene acceso a al menos un elemento del grupo
- Cada elemento verifica su permiso individualmente

### Ejemplo: Verificar Permisos en PHP

```php
// Verificar si el usuario puede ver programas
if (auth()->user()?->can('viewAny', \App\Models\Program::class)) {
    // Usuario tiene permiso
}

// Verificar múltiples permisos (OR)
if (auth()->user()?->can('viewAny', \App\Models\Program::class) || 
    auth()->user()?->can('viewAny', \App\Models\User::class)) {
    // Usuario tiene al menos uno de los permisos
}
```

---

## Estilos y Personalización

### Navegación Pública

**Clases principales**:
- `sticky top-0 z-50` - Posicionamiento sticky
- `bg-white dark:bg-zinc-900` - Fondo (modo normal)
- `bg-transparent` - Fondo transparente (modo hero)

**Estados de enlaces**:
- **Activo**: `text-erasmus-700 bg-erasmus-50`
- **Normal**: `text-zinc-600 hover:text-zinc-900`

### Navegación de Administración

**Componente Flux UI**:
- Usa `flux:navlist` con variante `outline`
- Los grupos usan `flux:navlist.group`
- Los elementos usan `flux:navlist.item`

**Personalización**:
- Los estilos se heredan de Flux UI
- Se puede personalizar mediante clases CSS adicionales

---

## Testing

### Tests de Navegación Pública

**Archivo**: `tests/Feature/Components/PublicLayoutTest.php`

**Tests incluidos**:
- Renderizado del componente
- Logo y nombre del centro
- Elementos de navegación
- Enlaces según autenticación
- Enlaces según permisos
- Selector de idioma
- Menú móvil
- Variante transparente

### Tests de Navegación de Administración

**Archivo**: `tests/Feature/Components/AdminNavTest.php`

**Tests incluidos**:
- Renderizado del componente
- Visibilidad de enlaces según permisos
- Visibilidad de grupos según permisos
- Super-admin ve todos los enlaces
- Uso de `wire:navigate`
- Detección de ruta actual

### Ejecutar Tests

```bash
# Todos los tests de navegación
php artisan test --filter="AdminNavTest|PublicLayoutTest"

# Solo tests de navegación pública
php artisan test tests/Feature/Components/PublicLayoutTest.php

# Solo tests de navegación de administración
php artisan test tests/Feature/Components/AdminNavTest.php
```

---

## Referencias

- [Documentación de Rutas Públicas](./public-routes.md)
- [Documentación de Rutas de Administración](./admin-routes.md)
- [Sistema de i18n](./i18n-system.md)
- [Roles y Permisos](./roles-and-permissions.md)
- [Flux UI Documentation](https://flux.laravel.com/docs)

---

**Última actualización**: Diciembre 2025  
**Versión**: 1.0
