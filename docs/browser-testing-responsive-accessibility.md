# Browser Testing: Responsive y Accesibilidad

Esta guía describe los tests de diseño responsive y accesibilidad implementados en la aplicación usando Pest v4 con browser testing.

## Resumen

Se han implementado **69 tests** que verifican:

- **Diseño Responsive**: 36 tests verificando que las páginas se adaptan correctamente a diferentes tamaños de pantalla
- **Accesibilidad Básica**: 33 tests verificando navegación por teclado, estructura semántica, contraste de colores y ausencia de errores JavaScript

## Tests de Diseño Responsive

### Páginas Públicas (`tests/Browser/Public/ResponsiveTest.php`)

**24 tests** verificando que las siguientes páginas se ven correctamente en móvil, tablet y desktop:

- Home (`/`)
- Programs Index (`/programas`)
- Programs Show (`/programas/{slug}`)
- Calls Index (`/convocatorias`)
- Calls Show (`/convocatorias/{slug}`)
- News Index (`/noticias`)
- News Show (`/noticias/{slug}`)
- Global Search (`/buscar`)

**Viewports utilizados:**
- **Móvil**: `on()->mobile()` (375x667px)
- **Tablet**: `resize(768, 1024)`
- **Desktop**: `on()->desktop()` (1920x1080px)

**Verificaciones realizadas:**
- No hay scroll horizontal (`assertNoHorizontalScroll()`)
- El contenido se adapta correctamente al viewport
- Los elementos interactivos son accesibles en todos los tamaños

### Páginas de Administración (`tests/Browser/Admin/ResponsiveTest.php`)

**12 tests** verificando que las siguientes páginas administrativas se ven correctamente:

- Dashboard (`/admin`)
- Programs Index (`/admin/programs`)
- Calls Index (`/admin/calls`)
- News Index (`/admin/news`)

**Viewports utilizados:**
- Mismo conjunto que las páginas públicas

**Autenticación:**
- Los tests utilizan `createAuthTestUser()` y `performLogin()` para autenticarse como `SUPER_ADMIN`

## Tests de Accesibilidad (`tests/Browser/Public/AccessibilityTest.php`)

### 1. Navegación por Teclado (9 tests)

Verifica que todos los elementos interactivos son accesibles mediante teclado:

- **Enlaces accesibles**: Verifica que los enlaces pueden ser enfocados y activados con teclado
- **Formularios accesibles**: Verifica que los inputs, selects y botones son accesibles por teclado
- **Navegación en menú**: Verifica navegación por teclado en menú público (desktop y móvil)
- **Navegación en formularios**: Verifica navegación en formularios de búsqueda, filtros y newsletter
- **Indicadores de foco**: Verifica que los elementos enfocados muestran indicadores visuales claros

**Helpers utilizados:**
- `focusElement($page, $selector)`: Enfoca un elemento específico
- `assertElementHasFocus($page, $selector)`: Verifica que un elemento tiene foco
- `assertFocusIndicatorVisible($page)`: Verifica que hay un indicador de foco visible

### 2. Estructura Semántica (9 tests)

Verifica que las páginas utilizan HTML semántico correcto:

- **Elementos semánticos**: Verifica presencia de `<header>`, `<main>`, `<nav>`, `<article>`, `<section>`
- **Jerarquía de encabezados**: Verifica que existe al menos un `<h1>` en cada página
- **Estructura de páginas**: Verifica estructura semántica en Home, Programs, Calls y News

**Helpers utilizados:**
- `assertSemanticElementExists($page, $tagName)`: Verifica que existe un elemento semántico
- `assertSemanticStructure($page, $tagNames)`: Verifica estructura completa de elementos semánticos
- `assertHeadingExists($page, $level)`: Verifica existencia de encabezados

### 3. ARIA Labels y Roles (3 tests)

Verifica que los elementos interactivos tienen etiquetas accesibles:

- **Labels en formularios**: Verifica que los inputs tienen labels asociados (directos, `for`, `aria-label`, o estructura Flux UI)
- **Roles ARIA**: Verifica roles correctos en menú móvil (`role="menu"`, `role="menuitem"`)
- **Elementos interactivos**: Verifica que botones sin texto visible tienen `aria-label`

**Helpers utilizados:**
- `assertInputHasLabel($page, $selector)`: Verifica que un input tiene label asociado
- `assertHasAriaAttribute($page, $selector, $attribute)`: Verifica atributos ARIA

### 4. Contraste de Colores (3 tests)

Verifica que el contraste de colores cumple con WCAG AA:

- **Texto principal**: Verifica contraste suficiente en modo claro y oscuro
- **Botones**: Verifica contraste suficiente en botones
- **Clases Tailwind**: Verifica que se utilizan clases con contraste adecuado (`text-gray-900`, `dark:text-white`, `bg-erasmus-600`)

**Helpers utilizados:**
- `assertHasContrastClasses($page, $selector, $isLargeText)`: Verifica clases de contraste

**Nota**: Los tests verifican la presencia de clases Tailwind conocidas por tener buen contraste, no calculan el contraste real (esto requeriría herramientas más avanzadas).

### 5. Errores de JavaScript (6 tests)

Verifica que no hay errores de JavaScript en consola:

- **Carga inicial**: Verifica ausencia de errores al cargar páginas
- **Navegación**: Verifica ausencia de errores al navegar entre páginas
- **Filtros**: Verifica ausencia de errores al usar filtros
- **Paginación**: Verifica ausencia de errores al usar paginación
- **Menú móvil**: Verifica ausencia de errores al abrir/cerrar menú móvil
- **Formularios**: Verifica ausencia de errores al enviar formularios

**Método utilizado:**
- `assertNoJavascriptErrors()`: Verifica que no hay errores en la consola del navegador

### 6. Modo Oscuro (3 tests)

Verifica que la accesibilidad se mantiene en modo oscuro:

- **Navegación por teclado**: Verifica que la navegación por teclado funciona en modo oscuro
- **Contraste**: Verifica que el contraste es suficiente en modo oscuro
- **Estructura semántica**: Verifica que la estructura semántica se mantiene en modo oscuro

**Método utilizado:**
- `inDarkMode()`: Activa el modo oscuro antes de las verificaciones

## Helpers Personalizados

Todos los helpers están en `tests/Browser/Helpers.php`:

### Helpers de Responsive

```php
assertNoHorizontalScroll($page, $message = null): void
```
Verifica que no hay scroll horizontal usando JavaScript (`document.body.scrollWidth <= window.innerWidth`).

### Helpers de Accesibilidad

```php
focusElement($page, $selector): void
```
Enfoca un elemento específico usando JavaScript.

```php
assertElementHasFocus($page, $selector, $message = null): void
```
Verifica que un elemento tiene foco.

```php
assertFocusIndicatorVisible($page, $message = null): void
```
Verifica que hay un indicador de foco visible (outline, ring, etc.).

```php
assertSemanticElementExists($page, $tagName, $message = null): void
```
Verifica que existe un elemento semántico específico.

```php
assertSemanticStructure($page, $tagNames): void
```
Verifica que existe una estructura completa de elementos semánticos.

```php
assertHeadingExists($page, $level, $message = null): void
```
Verifica que existe un encabezado de nivel específico.

```php
assertHasAriaAttribute($page, $selector, $ariaAttribute, $expectedValue = null): void
```
Verifica que un elemento tiene un atributo ARIA específico.

```php
assertInputHasLabel($page, $inputSelector): void
```
Verifica que un input tiene un label asociado (soporta múltiples métodos: `for`, `aria-label`, `aria-labelledby`, estructura Flux UI).

```php
assertHasContrastClasses($page, $selector, $isLargeText = false): void
```
Verifica que un elemento tiene clases Tailwind con contraste adecuado.

## Ejecución de Tests

### Ejecutar todos los tests de responsive y accesibilidad

```bash
php artisan test tests/Browser/Public/ResponsiveTest.php tests/Browser/Admin/ResponsiveTest.php tests/Browser/Public/AccessibilityTest.php
```

### Ejecutar tests específicos

```bash
# Solo tests de responsive público
php artisan test tests/Browser/Public/ResponsiveTest.php

# Solo tests de responsive admin
php artisan test tests/Browser/Admin/ResponsiveTest.php

# Solo tests de accesibilidad
php artisan test tests/Browser/Public/AccessibilityTest.php

# Filtrar por nombre de test
php artisan test --filter="keyboard navigation"
```

### Ejecutar tests en modo headed (ver navegador)

Los tests se ejecutan en modo headless por defecto. Para ver el navegador durante la ejecución, edita `tests/Pest.php` y cambia:

```php
->headed() // En lugar de ->headless()
```

## Mejores Prácticas

### 1. Viewports Consistentes

Siempre usa los métodos estándar para viewports:
- `on()->mobile()` para móvil
- `resize(768, 1024)` para tablet
- `on()->desktop()` para desktop

### 2. Esperar a que los Elementos Estén Listos

Usa `->wait()` cuando sea necesario para esperar a que los elementos sean interactivos:

```php
$page->click('button')->wait(1)->assertSee('Content');
```

### 3. Verificar Errores JavaScript

Siempre incluye `assertNoJavascriptErrors()` al final de los tests para asegurar que no hay errores en consola.

### 4. Tests de Accesibilidad Incrementales

Los tests de accesibilidad están organizados por categorías. Al agregar nuevos tests, sigue la misma estructura:
- Navegación por teclado
- Estructura semántica
- ARIA labels y roles
- Contraste de colores
- Errores JavaScript
- Modo oscuro

### 5. Helpers Reutilizables

Usa los helpers existentes en `tests/Browser/Helpers.php` en lugar de duplicar código. Si necesitas un nuevo helper, agrégalo allí.

## Troubleshooting

### Tests fallan por timing

Si los tests fallan porque los elementos no están listos:
- Agrega `->wait(1)` después de acciones que cambian el DOM
- Usa `assertPresent()` antes de interactuar con elementos dinámicos

### Tests fallan por selectores

Si los selectores no encuentran elementos:
- Verifica que el elemento existe en el DOM usando `assertPresent()`
- Usa selectores más específicos (IDs, clases, atributos)
- Para elementos con atributos especiales (como `wire:click`), escapa correctamente: `button[wire\\\\:click*=\\\\\"resetFilters\\\\\"]`

### Tests de contraste fallan

Los tests de contraste verifican clases Tailwind conocidas, no calculan el contraste real. Si un test falla:
- Verifica que el elemento tiene clases con buen contraste
- Asegúrate de que las clases de modo oscuro están presentes (`dark:text-white`, etc.)

### Errores de datos de prueba

Si ves errores de `UniqueConstraintViolationException`:
- Los helpers de datos de prueba usan `firstOrCreate()` para evitar conflictos
- Si persisten, verifica que `RefreshDatabase` está activo en los tests

## Referencias

- [Pest Browser Testing Documentation](https://pestphp.com/docs/plugins/browser)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Flux UI Documentation](https://flux.laravel.com/docs)
