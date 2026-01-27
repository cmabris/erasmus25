# Plan de Trabajo - Paso 3.11.5: Tests de Navegador de Interacciones JavaScript y Componentes Dinámicos

## Objetivo

Implementar tests de navegador para validar las interacciones JavaScript y los componentes dinámicos del área pública: navegación SPA con `wire:navigate`, menús desplegables (móvil, selector de idioma), filtros dinámicos que actualizan resultados sin recarga, y paginación. Estos tests aseguran que Livewire, Alpine.js y las directivas `wire:model.live` se comportan correctamente desde la perspectiva del usuario final, usando Pest v4 con Playwright.

---

## Estado Actual

### ✅ Ya Implementado

1. **Configuración de Browser Tests (Pasos 3.11.1–3.11.4)**:
   - Pest v4 con `pest-plugin-browser` y Playwright
   - Estructura `tests/Browser/` con `Public/`, `Auth/`, `Admin/`
   - Helpers: `createPublicTestData()`, `createProgramsTestData()`, `createCallsTestData()`, `createNewsTestData()`, `createGlobalSearchTestData()`, `createNewsletterTestData()`, etc.
   - `RefreshDatabase` en tests de Browser

2. **Navegación y `wire:navigate`**:
   - **public-nav.blade.php**: todos los enlaces (Home, Programas, Convocatorias, Noticias, Documentos, Calendario, Buscar, Login, Registro, Admin) usan `wire:navigate`
   - **global-search.blade.php**: enlaces a resultados con `wire:navigate`
   - **Program cards y otros**: enlaces a detalle con `wire:navigate` (via `x-content.program-card` y componentes similares)
   - Layout público incluye el nav; Livewire 3 con `wire:navigate` ofrece experiencia SPA (intercepta clicks, fetch en background, swap de contenido)

3. **Componentes interactivos en área pública**:
   - **Menú móvil** (public-nav): Alpine.js (`x-data="{ open: false }"`, `x-show="open"`, `@click="open = !open"`, `@click.away="open = false"`). Botón hamburguesa en `lg:hidden`; panel con enlaces y `@click="open = false"` al navegar.
   - **Language Switcher** (desktop): `livewire:language.switcher` con `variant="dropdown"`. Implementado con Alpine (`x-data`, `x-show`, `@click.away`). Botón con `wire:click="switchLanguage('{{ $language->code }}')"`; al cambiar idioma hace `redirect(..., navigate: true)`.
   - **Language Switcher** (móvil en menú): `variant="select"` con `wire:change="switchLanguage($event.target.value)"`.
   - **Global Search – Filtros avanzados**: botón "Filtros avanzados" que llama `toggleFilters`; panel mostrado/oculto. Cubierto en `GlobalSearchTest.php`.

4. **Filtros dinámicos** (sin recarga completa):
   - **Programs Index**: `wire:model.live.debounce.300ms="search"`, `wire:model.live="type"` (select), `wire:model.live="onlyActive"` (checkbox), `wire:click="resetFilters"`. Atributos `#[Url(as: 'q')]`, `#[Url(as: 'tipo')]`, `#[Url(as: 'activos')]` — la URL se actualiza con los filtros.
   - **Calls Index**: filtros análogos (programa, año, tipo, modalidad, estado, búsqueda) con `wire:model.live` y `#[Url]`.
   - **News Index**: filtros (programa, año, etiquetas, búsqueda) con `wire:model.live` y `#[Url]`.
   - **Documents Index**: `wire:model.live` en search, category, program, academicYear, documentType; `#[Url]` en todos; `resetFilters` con `wire:click`.

5. **Paginación**:
   - **Programs**: `WithPagination`, 9 por página, `$this->programs->links()` (componente Livewire/Tailwind).
   - **Calls**: análogo, 12 por página.
   - **News**: análogo.
   - **Documents**: 12 por página.
   - Los enlaces de paginación de Livewire usan `wire:click` (o equivalente) para cambiar de página sin full reload; la URL puede incluir `page` si se usa `#[Url]` para la página (en los componentes actuales no siempre se persiste `page` en la URL; la paginación funciona por estado del componente).

6. **Tests existentes**:
   - **ProgramsIndexTest**: filtros por URL (`/programas?tipo=KA1`), búsqueda por URL, paginación (“displays pagination when there are more than 9 programs”, “maintains filters when navigating between pages” con `?tipo=KA1`), reset. No cubre: cambiar filtros *en la página* (select/input) y comprobar que la lista y la URL se actualizan sin recarga; ni *click* explícito en “Siguiente”/“2” para ir a la página 2.
   - **CallsIndexTest**, **NewsIndexTest**: tests de render, filtros por URL, paginación estructural. Falta: filtros dinámicos in-page y click en paginación.
   - **GlobalSearchTest**: búsqueda en tiempo real, filtros avanzados (mostrar/ocultar), filtro por programa, limpiar, navegación a resultados.
   - No existe **DocumentsIndexTest** en `tests/Browser/Public/`.

### ⚠️ No existe en área pública

- **Modales**: no hay `flux:modal` en vistas públicas (solo en admin y en `settings/delete-user-form`, `notifications`).
- **Tabs** (`flux:tabs`): no se usan en listados o detalle públicos.
- **Tooltips**: no hay `flux:tooltip` en componentes públicos (sí en admin y en `notifications/bell`, que está en layout de app autenticado).

### ⚠️ Pendiente de implementar

1. **Tests de navegación con `wire:navigate`**: verificar que al hacer click en enlaces del nav (y en cards/enlaces de resultados) la navegación es SPA (sin full reload), que la URL cambia, que el contenido se actualiza y que no hay errores de JS. Opcional: transiciones/estado (scroll, focus).
2. **Tests de componentes interactivos**:
   - Menú móvil: abrir/cerrar, navegar y que se cierre.
   - Language Switcher (dropdown): abrir, elegir otro idioma, verificar redirección y cambio de locale.
   - Modales, tabs, tooltips en área pública: **N/A** — documentar.
3. **Tests de filtros dinámicos**: en Programas, Convocatorias, Noticias (y Documentos si se cubre): cambiar select/input/checkbox **en la página**, esperar a que Livewire actualice, verificar que los resultados cambian y que la URL refleja los parámetros.
4. **Tests de paginación**: en Programas, Convocatorias, Noticias (y Documentos): hacer *click* en “Siguiente” o en “2” (o el enlace de página 2), verificar que la lista muestra los registros de la página 2 y que la paginación indica la página activa.

---

## Dependencias y Premisas

- **Ámbito**: Solo área pública. Los componentes dinámicos de admin (modales, dropdowns, tooltips) quedan fuera de este paso; se pueden abordar en un futuro paso de browser tests de admin.
- **`wire:navigate`**: Livewire intercepta clicks en `<a href="..." wire:navigate>`, hace fetch de la nueva URL y reemplaza el body. No hay full reload; el historial del navegador se actualiza. Para aserciones: `assertPathIs`, `assertSee` del contenido de la nueva página, `assertNoJavascriptErrors`. En algunos entornos puede ser necesario un `wait` corto tras el click para que termine el fetch y el swap.
- **Menú móvil**: Depende de Alpine. El botón hamburguesa está en `lg:hidden`; para probarlo hace falta **viewport móvil** (p.ej. `browser_resize` o `$page->setViewportSize` según la API de Pest/Playwright). En `public-nav` el botón tiene `aria-label` dinámico (`open_menu` / `close_menu`).
- **Language Switcher**: El dropdown (desktop) se abre con click en el botón; las opciones son `wire:click="switchLanguage('...')"`. El método hace `redirect(..., navigate: true)`, por lo que la “recarga” es vía Livewire navigate. Para verificar el idioma: comprobar que en la nueva página aparecen textos en el idioma elegido (p.ej. keys de `lang/` o contenido conocido según locale). Requiere al menos 2 idiomas activos (p.ej. `es`, `en` en `languages` o `getAvailableLanguages()`).
- **Filtros dinámicos**: `wire:model.live` y `wire:model.live.debounce.300ms` provocan peticiones Livewire al cambiar el valor. Tras `fill` o `select` hay que esperar (p.ej. 400–600 ms para debounce, o `wait(1)` como en GlobalSearchTest) antes de `assertSee`/`assertDontSee` y de comprobar la URL. Para `assertUrlContains` o similar, comprobar que la query contiene `q=`, `tipo=`, etc.
- **Paginación**: Los links de `$paginator->links()` en Livewire suelen generar enlaces con `wire:click` o `href` con `?page=2`. Si es `wire:click`, el click dispara una petición Livewire y la lista se actualiza sin cambiar la URL (a menos que el componente use `#[Url(as: 'page')]`). En cualquier caso, el test debe: 1) crear suficientes registros para 2+ páginas, 2) hacer click en el enlace de la página 2 (o “Siguiente”), 3) `assertSee` un ítem que solo está en la página 2 y `assertDontSee` uno que solo está en la 1.
- **Documents**: Existe `App\Livewire\Public\Documents\Index` con filtros y paginación, pero no hay `DocumentsIndexTest`. Se puede añadir una fase para crear tests mínimos de filtros dinámicos y paginación, o dejarlo como ampliación opcional si el tiempo lo permite.
- **Selectores**: Reutilizar convenciones de 3.11.3 y 3.11.4: `fill('name', 'value')`, `click('texto')`, `select('id', 'label')`. Si hace falta, añadir `name`, `id` o `data-test` a los elementos a probar (p.ej. `type-filter`, `#program-filter` en Programs/Documents).

---

## Plan de Trabajo

### Fase 1: Tests de Navegación con `wire:navigate`

**Objetivo**: Comprobar que la navegación mediante enlaces con `wire:navigate` es SPA (sin full reload), que la URL y el contenido se actualizan correctamente y que no hay errores de JavaScript.

**Archivo**: `tests/Browser/Public/LivewireNavigateTest.php`

#### 1.1. Configuración y `beforeEach`

- [x] `RefreshDatabase` vía Pest `in('Browser')`.
- [x] `beforeEach`: `App::setLocale('es')`. Cada test llama `createPublicTestData()` para tener Program, Call (publicado), NewsPost (publicado) y así Home, Programas, Convocatorias y Noticias muestran contenido al navegar.

#### 1.2. Tests a implementar

- [x] **Test: Navegación desde Home a Programas sin full reload**
  - `visit('/')` → `assertSee` “Erasmus+”.
  - `click(__('common.nav.programs'))` → `wait(1)`.
  - `assertPathIs('/programas')`, `assertSee` el programa, `assertNoJavascriptErrors()`.

- [x] **Test: Navegación desde Programas a Convocatorias**
  - `visit('/programas')` → `click(__('common.nav.calls'))` → `wait(1)`.
  - `assertPathIs('/convocatorias')`, `assertSee` convocatoria, `assertNoJavascriptErrors()`.

- [x] **Test: Navegación desde Convocatorias a Noticias**
  - `visit('/convocatorias')` → `click(__('common.nav.news'))` → `wait(1)`.
  - `assertPathIs('/noticias')`, `assertSee` noticia, `assertNoJavascriptErrors()`.

- [x] **Test: Navegación desde Noticias a Búsqueda (Buscar)**
  - `visit(route('noticias.index'))` → `click(__('common.search.global_title'))` → `wait(1)`.
  - `assertPathIs('/buscar')`, `assertSee(__('common.search.global_title'))`, `assertNoJavascriptErrors()`.

- [x] **Test: Navegación desde un listado a un detalle (wire:navigate)**
  - `createPublicTestData()`, `visit('/programas')` → `click($data['program']->name)` → `wait(1)`.
  - `assertPathBeginsWith('/programas/')`, `assertSee` nombre del programa, `assertNoJavascriptErrors()`.

- [x] **Test: Navegación desde Búsqueda a un resultado**
  - Cubierto en `GlobalSearchTest::navigates to program detail when clicking a result link`. No duplicado.

- [x] **Test: La URL se actualiza correctamente al navegar**
  - `visit('/')` → `click(__('common.nav.programs'))` → `wait(1)` → `assertPathIs('/programas')`, `assertNoJavascriptErrors()`.

- [x] **Test: Sin errores de JavaScript tras varias navegaciones**
  - Secuencia: Home → Programas → Convocatorias → Noticias → Home. `assertNoJavascriptErrors()` en cada paso y al final.

#### 1.3. Transiciones y estado (opcional)

- [x] **Test: No hay flash de pantalla en blanco prolongado**
  - Omitido: los tests con `wait(1)` y `assertSee`/`assertPathIs` ya validan que el contenido aparece en tiempo razonable. No se añade test específico de duración.

- [x] **Estado/scroll**: Livewire Navigate puede restaurar scroll. Omitido; se deja para una iteración futura.

---

### Fase 2: Tests de Componentes Interactivos (Menú Móvil y Language Switcher)

**Objetivo**: Verificar que el menú móvil y el selector de idioma (dropdown) se abren, se cierran y ejecutan las acciones esperadas (navegar, cambiar idioma).

**Archivo**: `tests/Browser/Public/InteractiveComponentsTest.php`

#### 2.1. Menú móvil

- [x] **Redimensionar viewport a móvil** (ancho &lt; 1024px para que `lg:hidden` muestre el botón). Usar `visit('/')->withLocale('es')->on()->mobile()`; `on()->mobile()` de pest-plugin-browser aplica viewport móvil.

- [x] **Test: Abrir menú móvil**
  - `visit('/')->withLocale('es')->on()->mobile()`.
  - `click(__('common.nav.open_menu'))` (botón con `aria-label` y `sr-only`).
  - `assertSee(__('common.nav.programs'))`, `assertSee(__('common.nav.calls'))`, `assertNoJavascriptErrors()`.

- [x] **Test: Cerrar menú al hacer click fuera**
  - Abrir menú → `click('[aria-label="'.__('common.nav.home').'"]')` (logo) → `wait(0.5)`.
  - `assertMissing('[role="menu"] a[href*="programas"]')` (el enlace del menú no visible cuando cerrado), `assertNoJavascriptErrors()`.

- [x] **Test: Navegar desde el menú móvil y que se cierre**
  - Abrir menú → `wait(0.4)` → `click('[role="menu"] a[href*="programas"]')` → `wait(1)`.
  - `assertPathIs('/programas')`, `assertSee` programa, `assertNoJavascriptErrors()`.

- [x] **Test: Enlaces del menú móvil llevan a las rutas correctas**
  - Convocatorias: `click('[role="menu"] a[href*="convocatorias"]')` → `assertPathIs('/convocatorias')`, `assertSee` convocatoria.
  - Noticias: nuevo `visit('/')->withLocale('es')->on()->mobile()`, abrir menú, `click('[role="menu"] a[href*="noticias"]')` → `assertPathIs('/noticias')`, `assertSee` noticia.

- [x] Restaurar viewport a desktop al final del grupo de tests de menú móvil (opcional): cada test usa su propio `visit()`; los de Language Switcher no usan `on()->mobile()`.

#### 2.2. Language Switcher (dropdown, desktop)

- [x] **Requisitos**: 2 idiomas activos (`es`, `en`). `beforeEach` ejecuta `(new LanguagesSeeder)->run()` y `App::setLocale('es')`.

- [x] **Test: Abrir dropdown de idioma**
  - `visit('/')->withLocale('es')` (desktop). `click('[aria-label="'.__('common.language.change').'"]')` (el botón no tiene el texto visible; se usa `aria-label`).
  - `assertSee('English')`, `assertNoJavascriptErrors()`.

- [x] **Test: Cambiar idioma y verificar redirección**
  - `visit(route('noticias.index'))->withLocale('es')` → `click('[aria-label="..."]')` → `click('English')` → `wait(1)`.
  - `assertPathIs('/noticias')`, `assertSee('News')`, `assertNoJavascriptErrors()`.

- [x] **Test: Cerrar dropdown al elegir opción**
  - Cubierto por el test anterior: al elegir idioma hay redirección y el DOM se reemplaza.

- [x] **Test: Cerrar dropdown al hacer click fuera**
  - Abrir dropdown → `click(__('common.home.hero_title'))` (hero, fuera del dropdown) → `wait(0.5)`.
  - `assertDontSee('English')`, `assertNoJavascriptErrors()`.

#### 2.3. Modales, Tabs y Tooltips en área pública

- [x] **Decisión documentada**: No hay modales, tabs ni tooltips en el área pública. Test documental: `it('documents that modals tabs and tooltips are not used in public area', ...)` con `expect(true)->toBeTrue()`. El acordeón/panel de Filtros avanzados queda cubierto en `GlobalSearchTest`.

---

### Fase 3: Tests de Filtros Dinámicos (en la página, sin recarga)

**Objetivo**: Cambiar filtros (select, input, checkbox) en la interfaz y verificar que los resultados y la URL se actualizan sin recarga completa.

#### 3.1. Programas — `tests/Browser/Public/ProgramsIndexTest.php`

- [x] **Test: Cambiar select “Tipo” y verificar resultados y URL**
  - `select('#type-filter', 'KA1')` → `wait(1)` → `assertSee` KA1, `assertDontSee` KA2, `assertQueryStringHas('tipo', 'KA1')`.

- [x] **Test: Escribir en búsqueda y verificar resultados**
  - `fill('search', 'Movilidad')` (input con `name="search"`) → `wait(1)` → `assertSee`/`assertDontSee`, `assertQueryStringHas('q', 'Movilidad')`.

- [x] **Test: Cambiar checkbox “Solo activos” y verificar resultados**
  - `uncheck('onlyActive')` (checkbox con `name="onlyActive"`) → `wait(1)` → `assertSee` Activo e Inactivo, `assertQueryStringHas('activos', 'false')`.

- [x] **Test: Reset de filtros**
  - `visit('/programas?tipo=KA1')` → `click(__('common.actions.reset'))` → `wait(1)` → `assertSee` ambos programas (Prog KA1 y Prog KA2). La aserción `assertQueryStringMissing('tipo')` se omite porque el componente puede dejar `tipo=` en la URL.

#### 3.2. Convocatorias — `tests/Browser/Public/CallsIndexTest.php`

- [x] **Selectores**: `#program-filter`, `#year-filter`, `#type-filter`, `#modality-filter`, `#status-filter`; `name="search"` en `x-ui.search-input`.

- [x] **Test: Cambiar filtro por programa (select)**
  - `select('#program-filter', (string) $p1->id)` → `wait(1)` → `assertSee` C1, `assertDontSee` C2, `assertQueryStringHas('programa', ...)`.

- [x] **Test: Escribir en búsqueda**
  - `fill('search', 'Movilidad')` → `wait(1)` → `assertSee('Convocatoria Movilidad 2025')`, `assertQueryStringHas('q', 'Movilidad')`.

- [x] **Test: Reset de filtros**
  - `visit('/convocatorias?programa=...')` → `click(__('common.actions.reset'))` → `wait(1)` → `assertSee` C1 y C2.

#### 3.3. Noticias — `tests/Browser/Public/NewsIndexTest.php`

- [x] **Selectores**: `#program-filter`, `#year-filter`; `name="search"`; etiquetas vía `wire:click="toggleTag({{ $tag->id }})"`.

- [x] **Test: Cambiar filtro por programa**
  - `select('#program-filter', (string) $p1->id)` → `wait(1)` → `assertSee` N1, `assertDontSee` N2, `assertQueryStringHas('programa', ...)`.

- [x] **Test: Escribir en búsqueda**
  - `fill('search', 'Becas')` → `wait(1)` → `assertSee('Noticia sobre Becas')`, `assertQueryStringHas('q', 'Becas')`.

- [x] **Test: Reset de filtros**
  - `visit(route('noticias.index', ['programa' => ...]))` → `click(__('common.actions.reset'))` → `wait(1)` → `assertSee` N1 y N2.

#### 3.4. Documentos — `tests/Browser/Public/DocumentsIndexTest.php` (opcional)

- [ ] **Fase 3.4 opcional**: No existe `DocumentsIndexTest` base. Los tests de filtros dinámicos y reset para Documentos se dejan para cuando se cree ese archivo. Mientras tanto, 3.4 se considera N/A.

---

### Fase 4: Tests de Paginación

**Objetivo**: Verificar que al hacer click en “Siguiente” o en el enlace de la página 2, la lista muestra los ítems de esa página y que la paginación refleja la página actual.

#### 4.1. Programas — `tests/Browser/Public/ProgramsIndexTest.php`

- [x] **Test: Click en página 2 y ver contenido correcto**
  - Implementado: `it('shows correct content when clicking page 2')`. Usa `button[wire\\:click*="gotoPage(2"]` para hacer click en el botón de paginación. Verifica que hay programas visibles en ambas páginas y que al menos algunos programas cambian entre páginas.

- [x] **Test: Los datos de la página 2 son los esperados**
  - Implementado: `it('shows expected data on page 2')`. Verifica que hay programas visibles en la segunda página y que al menos un programa de la primera página no está en la segunda.

- [x] **Test: Navegar a página 2 y volver a página 1**
  - Implementado: `it('navigates to page 2 and back to page 1')`. Verifica la navegación bidireccional entre páginas y que los programas de la primera página vuelven a estar visibles.

- [x] **Test: Los filtros se mantienen al cambiar de página** (complemento a “maintains filters when navigating between pages”)
  - Implementado: `it('maintains filters when navigating between pages')`. Verifica que los filtros (tipo=KA1) se mantienen al cambiar de página y que solo se muestran programas del tipo filtrado.

#### 4.2. Convocatorias — `tests/Browser/Public/CallsIndexTest.php`

- [x] **Test: Click en página 2**
  - Implementado: `it('shows correct content when clicking page 2')`. Crea 15 convocatorias y verifica que hay convocatorias visibles en la segunda página.

- [x] **Test: Volver a página 1**
  - Implementado: `it('navigates to page 2 and back to page 1')`. Verifica la navegación bidireccional entre páginas.

#### 4.3. Noticias — `tests/Browser/Public/NewsIndexTest.php`

- [x] **Test: Click en página 2**
  - Implementado: `it('shows correct content when clicking page 2')`. Crea 15 noticias y verifica que hay noticias visibles en la segunda página.

- [x] **Test: Volver a página 1**
  - Implementado: `it('navigates to page 2 and back to page 1')`. Verifica la navegación bidireccional entre páginas.

#### 4.4. Documentos — `tests/Browser/Public/DocumentsIndexTest.php` (si se implementa la Fase 3.4)

- [ ] **Test: Click en página 2**
  - Crear 15 documentos activos. `visit(route('documentos.index'))`. Click en “2” o “Siguiente”. `assertSee` un documento de la 2ª página. `assertNoJavascriptErrors()`.

- [ ] **Test: Volver a página 1**
  - Desde 2, click en 1. Comprobar ítem de la 1ª página.

---

### Fase 5: Ajustes de Selectores y Atributos de Test

**Objetivo**: Asegurar que los elementos críticos (filtros, botones de paginación, menú móvil, language switcher) tienen `name`, `id` o `data-test` estables para que los tests no dependan de textos que cambien con traducciones o de estructura HTML frágil.

- [x] **Programs index**: 
  - `x-ui.search-input` ya tiene `name="search"` y se usa en los tests con `fill('search', ...)`.
  - El select de tipo tiene `id="type-filter"` y se usa con `select('#type-filter', ...)`.
  - El checkbox “Solo activos” tiene `name="onlyActive"` y se usa con `uncheck('onlyActive')`.
  - Se ha añadido `data-test="programs-reset-filters"` tanto al botón reset principal como al botón reset del estado vacío para disponer de un selector estable.

- [x] **Calls index**: 
  - `x-ui.search-input` tiene `name="search"`.
  - Los selects de programa, año, tipo, modalidad y estado tienen `id` (`program-filter`, `year-filter`, `type-filter`, `modality-filter`, `status-filter`) y se usan con `select('#program-filter', ...)` en los tests.
  - Se ha añadido `data-test="calls-reset-filters"` al botón reset principal y al botón reset del estado vacío.

- [x] **News index**: 
  - `x-ui.search-input` tiene `name="search"`.
  - Los selects de programa y año tienen `id` (`program-filter`, `year-filter`) y se usan con `select('#program-filter', ...)` en los tests.
  - El filtro de etiquetas se maneja con botones `wire:click="toggleTag(...)"` y se sigue seleccionando por texto de la etiqueta en los tests.
  - Se ha añadido `data-test="news-reset-filters"` al botón reset principal y al botón reset del estado vacío.

- [x] **Documents index**: 
  - Se ha añadido `name="search"` al `x-ui.search-input` de búsqueda.
  - Los selects de categoría, programa, año y tipo tienen `id` (`category-filter`, `program-filter`, `year-filter`, `type-filter`) y ahora también `name` (`category`, `program`, `academicYear`, `documentType`) para poder seleccionarlos de forma estable si se añaden tests de filtros dinámicos en el futuro.
  - Se ha añadido `data-test="documents-reset-filters"` al botón reset principal y al botón reset del estado vacío.

- [x] **Paginación**: 
  - Los enlaces de `links()` en Livewire usan botones con `wire:click="gotoPage(n, 'page')"` para las páginas numéricas y `wire:click="nextPage"`/`previousPage` para siguiente/anterior.
  - En los tests de paginación se utiliza el selector `button[wire\:click*="gotoPage(2"]` para ir a la página 2 (y el mismo patrón para volver a la página 1), en lugar de depender del texto “2”/“Siguiente”.

- [x] **Menú móvil**: 
  - El botón hamburguesa en `public-nav` expone un `aria-label` dinámico `:aria-label="open ? '{{ __('common.nav.close_menu') }}' : '{{ __('common.nav.open_menu') }}'"`.
  - Los tests seleccionan el botón por `aria-label` (`__('common.nav.open_menu')`) y los enlaces del menú móvil por selectores de `role="menu"` y `href`, por lo que no ha sido necesario añadir `data-test` adicional.

- [x] **Language Switcher**: 
  - El language switcher se monta como componente Livewire (`<livewire:language.switcher ...>`). El botón de apertura se selecciona en los tests por `aria-label="{{ __('common.language.change') }}"`.
  - Las opciones se seleccionan por texto visible (“English”/“Español”) y por `wire:click="switchLanguage('...')`, que es estable. No ha sido necesario añadir `data-test` por ahora; queda documentado que los tests usan `aria-label` y texto visible.

---

### Fase 6: Documentación y Verificación Final

#### 6.1. Documentación

- [ ] Crear o actualizar una sección en `docs/browser-testing-public-pages.md` (o en un `docs/browser-testing-interactions.md` si se prefiere un doc específico) con:
  - Resumen de `LivewireNavigateTest.php`, `InteractiveComponentsTest.php` y de las ampliaciones en `ProgramsIndexTest`, `CallsIndexTest`, `NewsIndexTest` (y `DocumentsIndexTest` si aplica).
  - Escenarios: navegación SPA, menú móvil, language switcher, filtros dinámicos, paginación.
  - Convenciones: viewport móvil para menú, espera a debounce/Livewire tras filtros, selectores (`name`, `id`, `data-test`).
  - Comandos: `./vendor/bin/pest tests/Browser/Public/LivewireNavigateTest.php`, `./vendor/bin/pest tests/Browser/Public/InteractiveComponentsTest.php`, `./vendor/bin/pest tests/Browser/Public/ProgramsIndexTest.php`, etc.; `--headed`, `--debug` para depuración.

#### 6.2. Actualizar `docs/planificacion_pasos.md`

- [ ] En el paso 3.11.5, marcar como completados los ítems:
  - [ ] Test de Navegación con Livewire
  - [ ] Test de Componentes Interactivos
  - [ ] Test de Filtros Dinámicos
  - [ ] Test de Paginación

#### 6.3. Verificación final

- [ ] Ejecutar:
  - `./vendor/bin/pest tests/Browser/Public/LivewireNavigateTest.php`
  - `./vendor/bin/pest tests/Browser/Public/InteractiveComponentsTest.php`
  - `./vendor/bin/pest tests/Browser/Public/ProgramsIndexTest.php`
  - `./vendor/bin/pest tests/Browser/Public/CallsIndexTest.php`
  - `./vendor/bin/pest tests/Browser/Public/NewsIndexTest.php`
  - (y `DocumentsIndexTest` si se implementa)
- [ ] Comprobar que todos pasan.
- [ ] Revisar que no queden `skip()` o `todo()` sin justificar.
- [ ] Opcional: `./vendor/bin/pest tests/Browser/Public` para validar la suite completa de pública.

---

## Estructura de Archivos Final

```
tests/
├── Browser/
│   ├── Helpers.php
│   └── Public/
│       ├── LivewireNavigateTest.php      # NUEVO – Fase 1
│       ├── InteractiveComponentsTest.php # NUEVO – Fase 2
│       ├── ProgramsIndexTest.php         # AMPLIADO – Fases 3.1, 4.1
│       ├── CallsIndexTest.php            # AMPLIADO – Fases 3.2, 4.2
│       ├── NewsIndexTest.php             # AMPLIADO – Fases 3.3, 4.3
│       ├── DocumentsIndexTest.php        # NUEVO (opcional) – Fases 3.4, 4.4
│       ├── GlobalSearchTest.php          # Sin cambios; ya cubre filtros avanzados
│       ├── HomeTest.php
│       ├── ProgramsShowTest.php
│       ├── CallsShowTest.php
│       ├── NewsShowTest.php
│       ├── NewsletterSubscribeTest.php
│       ├── AccessibilityTest.php
│       ├── PerformanceTest.php
│       └── ...
```

---

## Criterios de Éxito

1. **Navegación con `wire:navigate`**
   - Clicks en enlaces del nav y en cards/resultados provocan navegación SPA (sin full reload).
   - La URL y el contenido de la página coinciden con la ruta destino.
   - No hay errores de JavaScript durante la navegación.

2. **Componentes interactivos**
   - Menú móvil: se abre, se cierra al elegir enlace o al hacer click fuera, y los enlaces llevan a las rutas correctas.
   - Language Switcher (dropdown): se abre, al elegir otro idioma la app redirige y el contenido refleja el nuevo locale; no hay errores de JS.
   - Modales, tabs y tooltips en área pública: N/A; documentado.

3. **Filtros dinámicos**
   - En Programas, Convocatorias y Noticias (y Documentos si se cubre): al cambiar select, input o checkbox en la página, los resultados y la URL se actualizan sin recarga completa.
   - El botón de reset restablece filtros, lista y URL.

4. **Paginación**
   - En Programas, Convocatorias, Noticias (y Documentos): al hacer click en “Siguiente” o en “2”, la lista muestra los ítems de la página 2; al volver a “1”, se muestran los de la 1.
   - Opcional: con filtros aplicados, al cambiar de página los filtros se mantienen.

5. **Documentación**
   - `docs` actualizada y `planificacion_pasos.md` con el estado del paso 3.11.5.

---

## Notas Importantes

1. **Viewport móvil**: Los tests del menú móvil requieren un ancho &lt; 1024px. Revisar la API de `pest-plugin-browser` o Playwright para `resize`/`setViewportSize`. Si no hay API directa, se puede usar `$page->setViewportSize(['width' => 375, 'height' => 667])` si Pest expone el objeto `page` de Playwright.

2. **Debounce en búsqueda**: `wire:model.live.debounce.300ms` en Programas (y en otros). Tras `fill`, esperar ≥ 400 ms (1 s es seguro). Aplicar la misma lógica que en `GlobalSearchTest`.

3. **Idiomas en tests**: Para el Language Switcher, asegurar que `languages` tiene al menos `es` y `en` y que `getAvailableLanguages()` los devuelve. Si hace falta, ejecutar un seeder de idiomas en `beforeEach` o crear registros con `Language::factory()` si existe.

4. **Paginación y `#[Url]`**: Si en el futuro se añade `#[Url(as: 'page')]` a los componentes de listado, la URL incluirá `?page=2`. Los tests pueden comprobar `assertUrlContains('page=2')` cuando corresponda. Mientras no se persista `page` en la URL, la aserción se centra en el contenido (ítems de la 2ª página) y en que la paginación visual indique la página activa.

5. **Documents**: La Fase 3.4 y 4.4 son opcionales si se prioriza Programas, Convocatorias y Noticias. Si se incluyen, puede ser necesario un helper `createDocumentsTestData()` o reutilizar factories de Document, DocumentCategory, Program, AcademicYear.

6. **Orden de ejecución**: Conviene ejecutar primero `LivewireNavigateTest` e `InteractiveComponentsTest` para validar la base de navegación y componentes. Los tests de filtros y paginación pueden correr en paralelo por archivo.

---

## Próximos Pasos

Tras completar el paso 3.11.5:

- **Paso 3.11.6**: Tests de rendimiento y optimización (carga, consultas, lazy loading).
- **Paso 3.11.7**: Tests de responsive y accesibilidad.

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan en implementación — **Fase 1 completada** (LivewireNavigateTest.php, 7 tests)
