# Plan de Trabajo - Paso 3.11.7: Tests de Responsive y Accesibilidad

## Objetivo

Implementar tests de navegador completos para validar el diseño responsive y la accesibilidad básica de la aplicación desde la perspectiva del usuario final. Estos tests verifican que las páginas se adaptan correctamente a diferentes tamaños de pantalla (móviles, tablets, desktop) y cumplen con estándares básicos de accesibilidad (navegación por teclado, contraste de colores, elementos accesibles, sin errores de JavaScript). Se utilizan Pest v4 con Playwright y sus capacidades de viewport y accesibilidad.

---

## Estado Actual

### ✅ Ya Implementado

1. **Configuración de Browser Tests (Pasos 3.11.1–3.11.6)**:
   - Pest v4 con `pest-plugin-browser` y Playwright
   - Estructura `tests/Browser/` con `Public/`, `Admin/`
   - Helpers: `createPublicTestData()`, `createHomeTestData()`, `createProgramsTestData()`, `createCallsTestData()`, `createNewsTestData()`, etc.
   - `RefreshDatabase` en tests de Browser

2. **Tests básicos de accesibilidad**:
   - **Archivo**: `tests/Browser/Public/AccessibilityTest.php`
   - Tests básicos de estructura semántica HTML (4 tests)
   - Tests básicos de navegación por teclado (4 tests)
   - Verificación de `assertNoJavascriptErrors()` en todas las páginas

3. **Capacidades de Pest v4**:
   - `on()->mobile()` - Viewport móvil (< 768px)
   - `on()->tablet()` - Viewport tablet (768px - 1024px)
   - `on()->desktop()` - Viewport desktop (> 1024px)
   - `inDarkMode()` / `inLightMode()` - Modo oscuro/claro
   - `assertNoJavascriptErrors()` - Verificar errores de JavaScript
   - `assertNoConsoleLogs()` - Verificar logs de consola

4. **Componentes públicos implementados**:
   - Home, Programs Index/Show, Calls Index/Show, News Index/Show, Documents Index/Show, Events Index/Show
   - Navegación pública con menú móvil responsive
   - Language Switcher responsive (dropdown desktop, select móvil)
   - Formularios con filtros dinámicos

5. **Componentes de administración implementados**:
   - Dashboard, CRUD de Programas, Años Académicos, Convocatorias, Noticias, Etiquetas, etc.
   - Navegación de administración responsive

### ⚠️ Pendiente de Implementar

1. **Tests de diseño responsive completos**:
   - Tests de viewport móvil para todas las páginas públicas críticas
   - Tests de viewport tablet para todas las páginas públicas críticas
   - Tests de viewport desktop para todas las páginas públicas críticas
   - Tests de responsive para páginas de administración críticas
   - Verificación de que los elementos se adaptan correctamente (menú móvil, filtros, cards, tablas)
   - Verificación de que no hay overflow horizontal
   - Verificación de que los textos son legibles en todos los tamaños

2. **Tests de accesibilidad básica completos**:
   - Tests de navegación por teclado (Tab, Enter, Escape, Arrow keys)
   - Tests de indicadores de foco visibles
   - Tests de estructura semántica HTML (headings jerárquicos, landmarks, ARIA labels)
   - Tests de contraste de colores (verificar que los textos tienen suficiente contraste)
   - Tests de elementos accesibles (enlaces, botones, formularios tienen labels/aria-labels)
   - Tests de errores de JavaScript en consola (ya parcialmente implementado)
   - Tests de accesibilidad en modo oscuro

3. **Tests de accesibilidad avanzada (opcional)**:
   - Tests de lectores de pantalla (verificar que los elementos tienen texto alternativo)
   - Tests de navegación por teclado en formularios complejos
   - Tests de modales accesibles (si hay en área pública)

---

## Dependencias y Premisas

- **Viewports estándar**: 
  - Móvil: 375px x 667px (iPhone SE) o 390px x 844px (iPhone 12/13)
  - Tablet: 768px x 1024px (iPad)
  - Desktop: 1920px x 1080px (Full HD) o 1280px x 720px (HD)
  - Pest v4 usa `on()->mobile()`, `on()->tablet()`, `on()->desktop()` que configuran viewports apropiados

- **Breakpoints de Tailwind CSS**: 
  - `sm`: 640px
  - `md`: 768px
  - `lg`: 1024px
  - `xl`: 1280px
  - `2xl`: 1536px
  - Los tests deben verificar que los componentes se adaptan en estos breakpoints

- **Accesibilidad básica (WCAG 2.1 Level AA)**:
  - Contraste mínimo: 4.5:1 para texto normal, 3:1 para texto grande (18pt+ o 14pt+ bold)
  - Navegación por teclado: todos los elementos interactivos deben ser accesibles con Tab
  - Indicadores de foco: deben ser visibles (outline, border, etc.)
  - Estructura semántica: usar elementos HTML semánticos (header, nav, main, footer, article, section)
  - ARIA labels: elementos sin texto visible deben tener `aria-label` o `aria-labelledby`

- **Errores de JavaScript**: 
  - `assertNoJavascriptErrors()` verifica que no hay errores en la consola
  - `assertNoConsoleLogs()` verifica que no hay logs (opcional, puede ser demasiado estricto)
  - Los tests deben ejecutarse después de todas las interacciones (navegación, filtros, etc.)

- **Navegación por teclado**: 
  - Tab: navegar entre elementos interactivos
  - Enter/Space: activar botones/enlaces
  - Escape: cerrar modales/dropdowns
  - Arrow keys: navegar en listas/selects
  - Los tests deben simular estas interacciones usando Playwright

- **Contraste de colores**: 
  - Playwright no tiene una API directa para verificar contraste, pero se puede:
    1. Verificar que los elementos tienen clases de Tailwind que garantizan contraste (p. ej. `text-gray-900` sobre `bg-white`)
    2. Usar screenshots y herramientas externas (opcional)
    3. Verificar que los elementos tienen estilos inline o clases que garantizan contraste suficiente
  - Para tests básicos, verificar que los textos tienen clases de color apropiadas

- **Ámbito**: 
  - Enfoque principal en páginas públicas críticas (Home, Programs, Calls, News, Documents, Events)
  - Tests básicos de responsive para páginas de administración críticas (Dashboard, listados principales)
  - Tests de accesibilidad principalmente en área pública (donde hay más usuarios)

---

## Plan de Trabajo

### Fase 1: Tests de Diseño Responsive - Páginas Públicas

**Objetivo**: Verificar que todas las páginas públicas críticas se adaptan correctamente a diferentes tamaños de pantalla.

**Archivo**: `tests/Browser/Public/ResponsiveTest.php` (nuevo)

#### 1.1. Tests de Home responsive

- [ ] **Test: Home se ve bien en móvil**
  - `createHomeTestData()`
  - `visit(route('home'))->on()->mobile()`
  - Verificar que el menú móvil está visible (botón hamburguesa)
  - Verificar que no hay overflow horizontal (`assertNoHorizontalScroll()` o verificar ancho)
  - Verificar que los programas se muestran en 1 columna
  - Verificar que las convocatorias se muestran en 1 columna
  - Verificar que las noticias se muestran en 1 columna
  - `assertNoJavascriptErrors()`

- [ ] **Test: Home se ve bien en tablet**
  - `visit(route('home'))->on()->tablet()`
  - Verificar que el menú móvil puede estar visible o no (según breakpoint)
  - Verificar que los programas se muestran en 2 columnas (o 1 según diseño)
  - Verificar que las convocatorias se muestran en 2 columnas
  - Verificar que las noticias se muestran en 2 columnas
  - `assertNoJavascriptErrors()`

- [ ] **Test: Home se ve bien en desktop**
  - `visit(route('home'))->on()->desktop()`
  - Verificar que el menú desktop está visible (no hamburguesa)
  - Verificar que los programas se muestran en 3+ columnas
  - Verificar que las convocatorias se muestran en 3+ columnas
  - Verificar que las noticias se muestran en 3+ columnas
  - `assertNoJavascriptErrors()`

#### 1.2. Tests de Programs Index responsive

- [ ] **Test: Programs Index se ve bien en móvil**
  - `createProgramsTestData()`
  - `visit(route('programas.index'))->on()->mobile()`
  - Verificar que los filtros se adaptan (pueden estar en acordeón o columna completa)
  - Verificar que los programas se muestran en 1 columna
  - Verificar que la paginación es accesible
  - `assertNoJavascriptErrors()`

- [ ] **Test: Programs Index se ve bien en tablet**
  - `visit(route('programas.index'))->on()->tablet()`
  - Verificar que los programas se muestran en 2 columnas
  - `assertNoJavascriptErrors()`

- [ ] **Test: Programs Index se ve bien en desktop**
  - `visit(route('programas.index'))->on()->desktop()`
  - Verificar que los programas se muestran en 3+ columnas
  - `assertNoJavascriptErrors()`

#### 1.3. Tests de Programs Show responsive

- [ ] **Test: Programs Show se ve bien en móvil**
  - `createProgramShowTestData()`
  - `visit(route('programas.show', $program))->on()->mobile()`
  - Verificar que el contenido principal es legible
  - Verificar que las convocatorias relacionadas se muestran en 1 columna
  - Verificar que las noticias relacionadas se muestran en 1 columna
  - `assertNoJavascriptErrors()`

- [ ] **Test: Programs Show se ve bien en tablet y desktop**
  - Similar al anterior pero con viewports tablet y desktop
  - Verificar que las columnas se adaptan (2 columnas tablet, 3+ desktop)

#### 1.4. Tests de Calls Index responsive

- [ ] **Test: Calls Index se ve bien en móvil, tablet y desktop**
  - `createCallsTestData()`
  - Similar estructura a Programs Index
  - Verificar filtros adaptativos
  - Verificar columnas de convocatorias (1 móvil, 2 tablet, 3+ desktop)

#### 1.5. Tests de Calls Show responsive

- [ ] **Test: Calls Show se ve bien en móvil, tablet y desktop**
  - `createCallShowTestData()`
  - Verificar que las fases se muestran correctamente
  - Verificar que las resoluciones se muestran correctamente
  - Verificar que las noticias relacionadas se adaptan

#### 1.6. Tests de News Index responsive

- [ ] **Test: News Index se ve bien en móvil, tablet y desktop**
  - `createNewsTestData()`
  - Similar estructura a Programs Index
  - Verificar filtros adaptativos
  - Verificar columnas de noticias

#### 1.7. Tests de News Show responsive

- [ ] **Test: News Show se ve bien en móvil, tablet y desktop**
  - `createNewsShowTestData()`
  - Verificar que el contenido de la noticia es legible
  - Verificar que las noticias relacionadas se adaptan

#### 1.8. Tests de Documents Index responsive (si existe)

- [ ] **Test: Documents Index se ve bien en móvil, tablet y desktop**
  - Similar estructura a otros índices

#### 1.9. Tests de Global Search responsive

- [ ] **Test: Global Search se ve bien en móvil**
  - `createGlobalSearchTestData()`
  - `visit(route('buscar.index'))->on()->mobile()`
  - Verificar que el input de búsqueda es accesible
  - Verificar que los filtros avanzados se adaptan
  - `assertNoJavascriptErrors()`

- [ ] **Test: Global Search se ve bien en tablet y desktop**
  - Similar al anterior pero con viewports tablet y desktop

#### 1.10. Helper para verificar overflow horizontal

- [ ] **Función `assertNoHorizontalScroll()` en `tests/Browser/Helpers.php`**
  - Verificar que el ancho del body no excede el viewport
  - Usar `$page->evaluate('document.body.scrollWidth <= window.innerWidth')`
  - O verificar que `overflow-x: hidden` está aplicado

---

### Fase 2: Tests de Diseño Responsive - Páginas de Administración

**Objetivo**: Verificar que las páginas de administración críticas se adaptan correctamente a diferentes tamaños de pantalla.

**Archivo**: `tests/Browser/Admin/ResponsiveTest.php` (nuevo)

#### 2.1. Tests de Dashboard responsive

- [ ] **Test: Dashboard se ve bien en móvil**
  - Crear datos variados
  - Autenticar usuario super-admin
  - `visit(route('admin.dashboard'))->on()->mobile()`
  - Verificar que las estadísticas se adaptan (pueden estar en columna única)
  - Verificar que las tablas son scrollables horizontalmente si es necesario
  - `assertNoJavascriptErrors()`

- [ ] **Test: Dashboard se ve bien en tablet y desktop**
  - Similar al anterior pero con viewports tablet y desktop
  - Verificar que las estadísticas se muestran en grid (2 columnas tablet, 3+ desktop)

#### 2.2. Tests de listados de administración responsive

- [ ] **Test: Programs Index (admin) se ve bien en móvil, tablet y desktop**
  - Crear programas
  - Autenticar usuario super-admin
  - `visit(route('admin.programas.index'))->on()->mobile()`
  - Verificar que la tabla es scrollable horizontalmente o se adapta a columnas apiladas
  - Verificar que los filtros se adaptan
  - `assertNoJavascriptErrors()`

- [ ] **Test: Calls Index (admin) se ve bien en móvil, tablet y desktop**
  - Similar estructura

- [ ] **Test: News Index (admin) se ve bien en móvil, tablet y desktop**
  - Similar estructura

---

### Fase 3: Tests de Accesibilidad Básica - Navegación por Teclado

**Objetivo**: Verificar que todos los elementos interactivos son accesibles mediante navegación por teclado.

**Archivo**: `tests/Browser/Public/AccessibilityTest.php` (ampliar)

#### 3.1. Tests de navegación por teclado en navegación principal

- [ ] **Test: Navegación por teclado en menú público (desktop)**
  - `createPublicTestData()`
  - `visit(route('home'))->on()->desktop()`
  - Simular Tab para navegar entre enlaces del menú
  - Verificar que cada enlace recibe foco (`:focus` o `document.activeElement`)
  - Verificar que hay indicador de foco visible (outline, border, etc.)
  - Presionar Enter en un enlace y verificar navegación
  - `assertNoJavascriptErrors()`

- [ ] **Test: Navegación por teclado en menú móvil**
  - `visit(route('home'))->on()->mobile()`
  - Abrir menú móvil con Tab + Enter en botón hamburguesa
  - Navegar por enlaces del menú con Tab
  - Presionar Enter para navegar
  - Verificar que el menú se cierra después de navegar
  - `assertNoJavascriptErrors()`

#### 3.2. Tests de navegación por teclado en formularios

- [ ] **Test: Navegación por teclado en formulario de búsqueda**
  - `visit(route('buscar.index'))`
  - Tab hasta el input de búsqueda
  - Verificar que el input recibe foco
  - Escribir texto y presionar Enter (o Tab hasta botón buscar)
  - Verificar que la búsqueda se ejecuta
  - `assertNoJavascriptErrors()`

- [ ] **Test: Navegación por teclado en filtros de Programs Index**
  - `createProgramsTestData()`
  - `visit(route('programas.index'))`
  - Tab hasta el select de tipo
  - Usar Arrow keys para navegar opciones
  - Presionar Enter para seleccionar
  - Verificar que los filtros se aplican
  - `assertNoJavascriptErrors()`

- [ ] **Test: Navegación por teclado en formulario de suscripción newsletter**
  - `createNewsletterTestData()`
  - `visit(route('newsletter.subscribe'))`
  - Tab hasta el input de email
  - Tab hasta los checkboxes de programas
  - Usar Space para marcar/desmarcar
  - Tab hasta el botón de enviar
  - Presionar Enter para enviar
  - `assertNoJavascriptErrors()`

#### 3.3. Tests de indicadores de foco visibles

- [ ] **Test: Indicadores de foco visibles en enlaces**
  - `visit(route('home'))`
  - Tab hasta un enlace
  - Verificar que el enlace tiene `:focus` y estilo visible (outline, border, etc.)
  - Usar `$page->evaluate('getComputedStyle(document.activeElement).outline')` o similar

- [ ] **Test: Indicadores de foco visibles en botones**
  - Similar al anterior pero con botones

- [ ] **Test: Indicadores de foco visibles en inputs**
  - Similar al anterior pero con inputs

#### 3.4. Helper para simular navegación por teclado

- [ ] **Función `pressKey(string $key)` en helpers o usar API de Playwright**
  - `$page->keyboard->press('Tab')`
  - `$page->keyboard->press('Enter')`
  - `$page->keyboard->press('Escape')`
  - `$page->keyboard->press('ArrowDown')`
  - Wrapper para facilitar uso en tests

---

### Fase 4: Tests de Accesibilidad Básica - Estructura Semántica

**Objetivo**: Verificar que las páginas usan elementos HTML semánticos correctamente.

**Archivo**: `tests/Browser/Public/AccessibilityTest.php` (ampliar)

#### 4.1. Tests de estructura semántica HTML

- [ ] **Test: Home tiene estructura semántica correcta**
  - `createHomeTestData()`
  - `visit(route('home'))`
  - Verificar que hay `<header>` (navegación)
  - Verificar que hay `<main>` (contenido principal)
  - Verificar que hay `<footer>` (si existe)
  - Verificar que hay `<nav>` para navegación principal
  - Verificar headings jerárquicos (`<h1>`, `<h2>`, `<h3>`)
  - `assertNoJavascriptErrors()`

- [ ] **Test: Programs Index tiene estructura semántica correcta**
  - `createProgramsTestData()`
  - `visit(route('programas.index'))`
  - Verificar `<main>` con contenido
  - Verificar `<h1>` con título de página
  - Verificar `<section>` o `<article>` para cada programa
  - Verificar `<nav>` para paginación (si aplica)
  - `assertNoJavascriptErrors()`

- [ ] **Test: Programs Show tiene estructura semántica correcta**
  - `createProgramShowTestData()`
  - `visit(route('programas.show', $program))`
  - Verificar `<article>` para el programa principal
  - Verificar `<section>` para secciones (convocatorias relacionadas, noticias relacionadas)
  - Verificar headings jerárquicos
  - `assertNoJavascriptErrors()`

- [ ] **Test: News Show tiene estructura semántica correcta**
  - `createNewsShowTestData()`
  - `visit(route('noticias.show', $newsPost))`
  - Verificar `<article>` para la noticia
  - Verificar `<time>` para fecha de publicación
  - Verificar `<address>` o similar para autor (si aplica)
  - `assertNoJavascriptErrors()`

#### 4.2. Tests de ARIA labels y roles

- [ ] **Test: Elementos interactivos tienen labels accesibles**
  - `visit(route('home'))`
  - Verificar que botones sin texto visible tienen `aria-label`
  - Verificar que iconos decorativos tienen `aria-hidden="true"` o `aria-label`
  - Verificar que enlaces tienen texto descriptivo o `aria-label`
  - `assertNoJavascriptErrors()`

- [ ] **Test: Formularios tienen labels asociados**
  - `visit(route('buscar.index'))`
  - Verificar que inputs tienen `<label>` asociado o `aria-label`
  - Verificar que selects tienen `<label>` asociado
  - Verificar que checkboxes tienen `<label>` asociado
  - `assertNoJavascriptErrors()`

- [ ] **Test: Menú móvil tiene roles ARIA correctos**
  - `visit(route('home'))->on()->mobile()`
  - Abrir menú móvil
  - Verificar que el menú tiene `role="menu"` o `role="navigation"`
  - Verificar que los enlaces tienen `role="menuitem"` (si aplica)
  - `assertNoJavascriptErrors()`

#### 4.3. Helper para verificar estructura semántica

- [ ] **Función `assertSemanticStructure(array $requiredElements)` en helpers**
  - Verificar que elementos HTML semánticos existen
  - `$page->querySelector('header')` o similar
  - Wrapper para facilitar uso en tests

---

### Fase 5: Tests de Accesibilidad Básica - Contraste de Colores

**Objetivo**: Verificar que los textos tienen suficiente contraste con el fondo.

**Archivo**: `tests/Browser/Public/AccessibilityTest.php` (ampliar)

#### 5.1. Tests de contraste básico

- [ ] **Test: Textos principales tienen contraste suficiente (modo claro)**
  - `visit(route('home'))->inLightMode()`
  - Verificar que los textos principales tienen clases de Tailwind que garantizan contraste
  - Ejemplo: `text-gray-900` sobre `bg-white` tiene contraste suficiente
  - Verificar que los enlaces tienen contraste suficiente
  - `assertNoJavascriptErrors()`

- [ ] **Test: Textos principales tienen contraste suficiente (modo oscuro)**
  - `visit(route('home'))->inDarkMode()`
  - Verificar que los textos principales tienen clases de Tailwind que garantizan contraste en modo oscuro
  - Ejemplo: `dark:text-gray-100` sobre `dark:bg-gray-900` tiene contraste suficiente
  - `assertNoJavascriptErrors()`

- [ ] **Test: Botones tienen contraste suficiente**
  - Verificar que los botones primarios tienen contraste suficiente con su fondo
  - Verificar que los botones secundarios tienen contraste suficiente
  - `assertNoJavascriptErrors()`

#### 5.2. Helper para verificar contraste (básico)

- [ ] **Función `assertSufficientContrast(string $selector)` en helpers (opcional)**
  - Obtener color de texto y fondo usando `getComputedStyle()`
  - Calcular ratio de contraste (WCAG)
  - Verificar que es >= 4.5:1 (texto normal) o >= 3:1 (texto grande)
  - Nota: Esto puede ser complejo, puede dejarse como verificación manual o usar herramientas externas

---

### Fase 6: Tests de Errores de JavaScript

**Objetivo**: Verificar que no hay errores de JavaScript en consola durante la navegación e interacciones.

**Archivo**: `tests/Browser/Public/AccessibilityTest.php` (ampliar) y tests existentes

#### 6.1. Tests de errores de JavaScript en navegación

- [ ] **Test: No hay errores de JavaScript al cargar Home**
  - `createHomeTestData()`
  - `visit(route('home'))`
  - `assertNoJavascriptErrors()`

- [ ] **Test: No hay errores de JavaScript al navegar entre páginas**
  - `visit(route('home'))`
  - `click(__('common.nav.programs'))->wait(1)`
  - `assertNoJavascriptErrors()`
  - `click(__('common.nav.calls'))->wait(1)`
  - `assertNoJavascriptErrors()`
  - Continuar con otras navegaciones

#### 6.2. Tests de errores de JavaScript en interacciones

- [ ] **Test: No hay errores de JavaScript al usar filtros**
  - `createProgramsTestData()`
  - `visit(route('programas.index'))`
  - `select('#type-filter', 'KA1')->wait(1)`
  - `assertNoJavascriptErrors()`
  - `fill('search', 'Movilidad')->wait(1)`
  - `assertNoJavascriptErrors()`

- [ ] **Test: No hay errores de JavaScript al usar paginación**
  - Crear suficientes programas para 2 páginas
  - `visit(route('programas.index'))`
  - Click en página 2
  - `assertNoJavascriptErrors()`

- [ ] **Test: No hay errores de JavaScript al abrir/cerrar menú móvil**
  - `visit(route('home'))->on()->mobile()`
  - `click(__('common.nav.open_menu'))`
  - `assertNoJavascriptErrors()`
  - `click(__('common.nav.close_menu'))` o click fuera
  - `assertNoJavascriptErrors()`

#### 6.3. Tests de errores de JavaScript en formularios

- [ ] **Test: No hay errores de JavaScript al enviar formulario de newsletter**
  - `createNewsletterTestData()`
  - `visit(route('newsletter.subscribe'))`
  - `fill('email', 'test@example.com')`
  - `click('Suscribirse')` o similar
  - `assertNoJavascriptErrors()`

---

### Fase 7: Tests de Accesibilidad en Modo Oscuro

**Objetivo**: Verificar que la accesibilidad se mantiene en modo oscuro.

**Archivo**: `tests/Browser/Public/AccessibilityTest.php` (ampliar)

#### 7.1. Tests de accesibilidad en modo oscuro

- [ ] **Test: Navegación por teclado funciona en modo oscuro**
  - `visit(route('home'))->inDarkMode()`
  - Tab hasta enlaces
  - Verificar que los indicadores de foco son visibles en modo oscuro
  - `assertNoJavascriptErrors()`

- [ ] **Test: Contraste es suficiente en modo oscuro**
  - `visit(route('home'))->inDarkMode()`
  - Verificar que los textos tienen contraste suficiente (ver Fase 5)
  - `assertNoJavascriptErrors()`

- [ ] **Test: Estructura semántica se mantiene en modo oscuro**
  - `visit(route('home'))->inDarkMode()`
  - Verificar estructura semántica (ver Fase 4)
  - `assertNoJavascriptErrors()`

---

### Fase 8: Documentación y Verificación Final

#### 8.1. Documentación

- [ ] Crear o actualizar `docs/browser-testing-accessibility.md` con:
  - Resumen de los archivos de tests: `ResponsiveTest.php` (Public y Admin), `AccessibilityTest.php` ampliado
  - Descripción de los helpers: `assertNoHorizontalScroll()`, helpers de navegación por teclado, etc.
  - Viewports estándar utilizados
  - Convenciones: cómo probar responsive, cómo probar accesibilidad
  - Comandos: `./vendor/bin/pest tests/Browser/Public/ResponsiveTest.php`, `./vendor/bin/pest tests/Browser/Public/AccessibilityTest.php`, etc.
  - Troubleshooting: qué hacer si un test falla, cómo interpretar los resultados

#### 8.2. Actualizar `docs/planificacion_pasos.md`

- [ ] En el paso 3.11.7, marcar como completados los ítems:
  - [ ] Test de Diseño Responsive
  - [ ] Test de Accesibilidad Básica

#### 8.3. Verificación final

- [ ] Ejecutar todos los tests de responsive y accesibilidad:
  - `./vendor/bin/pest tests/Browser/Public/ResponsiveTest.php`
  - `./vendor/bin/pest tests/Browser/Public/AccessibilityTest.php`
  - `./vendor/bin/pest tests/Browser/Admin/ResponsiveTest.php`
- [ ] Comprobar que todos pasan
- [ ] Revisar que no queden `skip()` o `todo()` sin justificar
- [ ] Opcional: ejecutar `./vendor/bin/pest tests/Browser` y comprobar que la suite completa sigue pasando

---

## Estructura de Archivos Final

```
tests/
├── Browser/
│   ├── Helpers.php                          # + helpers para responsive y accesibilidad
│   ├── Public/
│   │   ├── ResponsiveTest.php              # NUEVO – tests de responsive público
│   │   ├── AccessibilityTest.php           # AMPLIADO – tests de accesibilidad completos
│   │   ├── HomeTest.php
│   │   ├── ProgramsIndexTest.php
│   │   └── ...
│   └── Admin/
│       ├── ResponsiveTest.php              # NUEVO – tests de responsive admin
│       └── ...
```

---

## Criterios de Éxito

1. **Tests de diseño responsive**
   - Todas las páginas públicas críticas se adaptan correctamente a móvil, tablet y desktop
   - No hay overflow horizontal en ningún viewport
   - Los elementos se reorganizan correctamente (menú móvil, filtros, cards, tablas)
   - Los textos son legibles en todos los tamaños

2. **Tests de accesibilidad básica**
   - Todos los elementos interactivos son accesibles mediante navegación por teclado
   - Los indicadores de foco son visibles
   - Las páginas usan estructura semántica HTML correcta
   - Los elementos tienen labels/aria-labels apropiados
   - Los textos tienen contraste suficiente (verificación básica)
   - No hay errores de JavaScript en consola

3. **Tests de accesibilidad en modo oscuro**
   - La accesibilidad se mantiene en modo oscuro
   - Los indicadores de foco son visibles
   - Los textos tienen contraste suficiente

4. **Helpers y documentación**
   - Helpers reutilizables para responsive y accesibilidad
   - Documentación completa de viewports, convenciones y troubleshooting
   - `planificacion_pasos.md` actualizado con el estado del paso 3.11.7

---

## Notas Importantes

1. **Viewports en Pest v4**: `on()->mobile()`, `on()->tablet()`, `on()->desktop()` configuran viewports apropiados automáticamente. Si se necesita un viewport específico, usar `$page->setViewportSize(['width' => 375, 'height' => 667])`.

2. **Navegación por teclado**: Playwright permite simular teclas con `$page->keyboard->press('Tab')`. Los tests deben verificar que los elementos reciben foco y que hay indicadores visibles.

3. **Contraste de colores**: Verificar contraste automáticamente es complejo. Para tests básicos, verificar que los elementos tienen clases de Tailwind que garantizan contraste. Para verificación completa, usar herramientas externas o screenshots.

4. **Errores de JavaScript**: `assertNoJavascriptErrors()` verifica errores en consola. Ejecutar después de todas las interacciones (navegación, filtros, formularios).

5. **Overflow horizontal**: Verificar que `document.body.scrollWidth <= window.innerWidth` o que hay `overflow-x: hidden` aplicado.

6. **Estructura semántica**: Verificar que existen elementos HTML semánticos (`<header>`, `<nav>`, `<main>`, `<footer>`, `<article>`, `<section>`) usando `$page->querySelector()` o similar.

7. **ARIA labels**: Verificar que elementos sin texto visible tienen `aria-label` o `aria-labelledby` usando `$page->getAttribute()`.

8. **Modo oscuro**: `inDarkMode()` y `inLightMode()` permiten probar ambos modos. Verificar que la accesibilidad se mantiene en ambos.

---

## Próximos Pasos

Tras completar el paso 3.11.7:

- **Paso 3.11.8**: Integración con CI/CD y documentación final.

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan en desarrollo
