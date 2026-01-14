# Breadcrumbs (Migas de Pan)

## Componente

El componente `x-ui.breadcrumbs` es un componente reutilizable para navegación contextual que muestra la jerarquía de navegación de la aplicación.

**Ubicación**: `resources/views/components/ui/breadcrumbs.blade.php`

---

## Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `items` | array | `[]` | Array de items con `label`, `href` (opcional), `icon` (opcional) |
| `separator` | string | `'chevron-right'` | Tipo de separador: `chevron-right`, `slash`, `arrow-right` |
| `homeIcon` | bool | `true` | Mostrar icono de inicio en lugar de texto |

### Estructura de Items

Cada item del array debe tener la siguiente estructura:

```php
[
    'label' => 'Texto del breadcrumb',  // Requerido
    'href' => route('ruta.nombre'),      // Opcional (si no se proporciona, será el último item)
    'icon' => 'icon-name',               // Opcional (nombre del icono de Heroicons)
]
```

---

## Patrones de Uso

### Vistas Públicas

#### Páginas Index (Listado)

Para páginas de listado, el breadcrumb muestra solo el nombre de la sección:

```php
<x-ui.breadcrumbs 
    :items="[
        ['label' => __('common.nav.programs'), 'href' => route('programas.index')],
    ]" 
    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
/>
```

**Ejemplos**:
- `public/programs/index.blade.php` - `[Programas]`
- `public/calls/index.blade.php` - `[Convocatorias]`
- `public/news/index.blade.php` - `[Noticias]`
- `public/documents/index.blade.php` - `[Documentos]`
- `public/events/index.blade.php` - `[Eventos]`
- `public/events/calendar.blade.php` - `[Calendario]`
- `public/newsletter/subscribe.blade.php` - `[Newsletter]`

#### Páginas Show (Detalle)

Para páginas de detalle, el breadcrumb muestra la sección y el nombre del recurso:

```php
<x-ui.breadcrumbs 
    :items="[
        ['label' => __('common.nav.programs'), 'href' => route('programas.index')],
        ['label' => $program->name],
    ]" 
    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
/>
```

**Ejemplos**:
- `public/programs/show.blade.php` - `[Programas] > {Program Name}`
- `public/calls/show.blade.php` - `[Convocatorias] > {Call Title}`
- `public/news/show.blade.php` - `[Noticias] > {News Title}`
- `public/documents/show.blade.php` - `[Documentos] > {Document Title}`
- `public/events/show.blade.php` - `[Eventos] > {Event Title}`

#### Estilos para Vistas Públicas

Las vistas públicas con hero sections usan clases especiales para breadcrumbs blancos:

```php
class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
```

---

### Vistas de Administración

#### Páginas Index (Listado)

Para páginas de listado en administración, el breadcrumb muestra Dashboard y el módulo:

```php
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Programas'), 'icon' => 'academic-cap'],
    ]"
/>
```

**Patrón**:
```
[Dashboard] > {Module}
```

#### Páginas Create

Para páginas de creación, el breadcrumb muestra Dashboard, módulo y acción:

```php
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Programas'), 'href' => route('admin.programs.index'), 'icon' => 'academic-cap'],
        ['label' => __('common.actions.create'), 'icon' => 'plus'],
    ]"
/>
```

**Patrón**:
```
[Dashboard] > {Module} > Crear
```

#### Páginas Show (Detalle)

Para páginas de detalle, el breadcrumb muestra Dashboard, módulo y el nombre del recurso:

```php
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Programas'), 'href' => route('admin.programs.index'), 'icon' => 'academic-cap'],
        ['label' => $program->name, 'href' => route('admin.programs.show', $program), 'icon' => 'academic-cap'],
    ]"
/>
```

**Patrón**:
```
[Dashboard] > {Module} > {Resource Name}
```

#### Páginas Edit

Para páginas de edición, el breadcrumb muestra Dashboard, módulo, recurso y acción:

```php
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Programas'), 'href' => route('admin.programs.index'), 'icon' => 'academic-cap'],
        ['label' => $program->name, 'href' => route('admin.programs.show', $program), 'icon' => 'academic-cap'],
        ['label' => __('common.actions.edit'), 'icon' => 'pencil'],
    ]"
/>
```

**Patrón**:
```
[Dashboard] > {Module} > {Resource Name} > Editar
```

#### Rutas Anidadas (Fases y Resoluciones)

Para rutas anidadas, el breadcrumb muestra la jerarquía completa:

**Fases de Convocatorias - Create**:
```php
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Convocatorias'), 'href' => route('admin.calls.index'), 'icon' => 'megaphone'],
        ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'megaphone'],
        ['label' => __('Fases'), 'href' => route('admin.calls.phases.index', $call), 'icon' => 'calendar'],
        ['label' => __('common.actions.create'), 'icon' => 'plus'],
    ]"
/>
```

**Patrón**:
```
[Dashboard] > Convocatorias > {Call} > Fases > Crear
```

**Resoluciones - Show**:
```php
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Convocatorias'), 'href' => route('admin.calls.index'), 'icon' => 'megaphone'],
        ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'megaphone'],
        ['label' => __('Resoluciones'), 'href' => route('admin.calls.resolutions.index', $call), 'icon' => 'document-check'],
        ['label' => $resolution->title, 'icon' => 'document-check'],
    ]"
/>
```

**Patrón**:
```
[Dashboard] > Convocatorias > {Call} > Resoluciones > {Resolution}
```

---

## Iconos por Módulo

Para mantener consistencia, se recomienda usar los siguientes iconos por módulo:

| Módulo | Icono | Heroicon Name |
|--------|-------|---------------|
| Dashboard | `squares-2x2` | `squares-2x2` |
| Programas | `academic-cap` | `academic-cap` |
| Años Académicos | `calendar-days` | `calendar-days` |
| Convocatorias | `megaphone` | `megaphone` |
| Fases | `calendar` | `calendar` |
| Resoluciones | `document-check` | `document-check` |
| Noticias | `newspaper` | `newspaper` |
| Etiquetas | `tag` | `tag` |
| Documentos | `document` | `document` |
| Categorías | `folder` | `folder` |
| Eventos | `calendar` | `calendar` |
| Usuarios | `user-group` | `user-group` |
| Roles | `shield-check` | `shield-check` |
| Configuración | `cog-6-tooth` | `cog-6-tooth` |
| Traducciones | `language` | `language` |
| Auditoría | `clipboard-document-list` | `clipboard-document-list` |
| Newsletter | `envelope` | `envelope` |

### Iconos de Acciones

| Acción | Icono | Heroicon Name |
|--------|-------|---------------|
| Crear | `plus` | `plus` |
| Editar | `pencil` | `pencil` |
| Ver | `eye` | `eye` |
| Eliminar | `trash` | `trash` |

---

## Ejemplos Completos

### Ejemplo 1: Vista Pública - Listado de Programas

```blade
<div class="mb-8">
    <x-ui.breadcrumbs 
        :items="[
            ['label' => __('common.nav.programs'), 'href' => route('programas.index')],
        ]" 
        class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
    />
</div>
```

### Ejemplo 2: Vista Pública - Detalle de Programa

```blade
<div class="mb-8">
    <x-ui.breadcrumbs 
        :items="[
            ['label' => __('common.nav.programs'), 'href' => route('programas.index')],
            ['label' => $program->name],
        ]" 
        class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
    />
</div>
```

### Ejemplo 3: Vista Admin - Listado de Programas

```blade
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Programas'), 'icon' => 'academic-cap'],
    ]"
/>
```

### Ejemplo 4: Vista Admin - Crear Programa

```blade
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Programas'), 'href' => route('admin.programs.index'), 'icon' => 'academic-cap'],
        ['label' => __('common.actions.create'), 'icon' => 'plus'],
    ]"
/>
```

### Ejemplo 5: Vista Admin - Editar Programa

```blade
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Programas'), 'href' => route('admin.programs.index'), 'icon' => 'academic-cap'],
        ['label' => $program->name, 'href' => route('admin.programs.show', $program), 'icon' => 'academic-cap'],
        ['label' => __('common.actions.edit'), 'icon' => 'pencil'],
    ]"
/>
```

### Ejemplo 6: Vista Admin - Ruta Anidada (Fase de Convocatoria)

```blade
<x-ui.breadcrumbs 
    class="mt-4"
    :items="[
        ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
        ['label' => __('Convocatorias'), 'href' => route('admin.calls.index'), 'icon' => 'megaphone'],
        ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'megaphone'],
        ['label' => __('Fases'), 'href' => route('admin.calls.phases.index', $call), 'icon' => 'calendar'],
        ['label' => $phase->name, 'icon' => 'calendar'],
    ]"
/>
```

---

## Mejores Prácticas

### 1. Consistencia

- **Siempre** incluir Dashboard como primer item en vistas de administración
- **Siempre** usar iconos apropiados para cada módulo
- **Siempre** usar traducciones (`__()`) para los labels

### 2. Navegación

- El último item del breadcrumb **nunca** debe tener `href` (es la página actual)
- Todos los items anteriores deben tener `href` para permitir navegación
- Usar `wire:navigate` en el componente para navegación SPA

### 3. Estilos

- **Vistas públicas**: Usar clases de texto blanco para hero sections
- **Vistas de administración**: Usar estilos por defecto (no especificar clases especiales)
- Añadir `class="mt-4"` en vistas de administración para espaciado consistente

### 4. Traducciones

- Usar `__('common.nav.{section}')` para secciones de navegación
- Usar `__('common.actions.{action}')` para acciones (create, edit, etc.)
- Usar nombres de modelos directamente para recursos específicos (ej: `$program->name`)

### 5. Iconos

- Usar iconos de Heroicons (compatibles con Flux UI)
- Mantener consistencia de iconos por módulo
- No usar iconos en vistas públicas (solo en administración)

---

## Vistas que NO necesitan Breadcrumbs

Las siguientes vistas **no deben** tener breadcrumbs:

1. **`public/home.blade.php`** - Página principal pública
2. **`admin/dashboard.blade.php`** - Dashboard principal de administración
3. **`public/newsletter/verify.blade.php`** - Página transaccional con token
4. **`public/newsletter/unsubscribe.blade.php`** - Página transaccional con token

---

## Verificación

Para verificar que los breadcrumbs están correctamente implementados:

1. ✅ Todos los items tienen `label`
2. ✅ Todos los items excepto el último tienen `href`
3. ✅ Los iconos son consistentes por módulo
4. ✅ Las traducciones están disponibles
5. ✅ Los estilos son apropiados (blanco para públicas, default para admin)
6. ✅ La jerarquía es correcta (especialmente en rutas anidadas)

---

## Referencias

- **Componente**: `resources/views/components/ui/breadcrumbs.blade.php`
- **Traducciones**: `lang/es/common.php` y `lang/en/common.php`
- **Iconos**: [Heroicons](https://heroicons.com/) (compatibles con Flux UI)
- **Auditoría**: `docs/pasos/paso-3.6.4-auditoria.md`

---

## Tests

Los breadcrumbs están completamente testeados en `tests/Feature/Components/BreadcrumbsTest.php`:

- ✅ 27 tests pasando (48 assertions)
- ✅ Tests del componente breadcrumbs
- ✅ Tests de breadcrumbs en vistas públicas
- ✅ Tests de breadcrumbs en vistas de administración
- ✅ Tests de enlaces y navegación
- ✅ Tests de accesibilidad

Para ejecutar los tests:
```bash
php artisan test tests/Feature/Components/BreadcrumbsTest.php
```

---

## Estado del Proyecto

**Paso 3.6.4 - Breadcrumbs**: ✅ COMPLETADO

- ✅ Breadcrumbs implementados en todas las vistas necesarias
- ✅ Consistencia verificada y corregida
- ✅ Tests completos y pasando
- ✅ Documentación completa y actualizada

**Cobertura**: 95% (70/74 vistas que necesitan breadcrumbs los tienen)

---

**Última actualización**: Diciembre 2025