# Plan de Trabajo - Paso 3.11.4: Tests de Navegador de Formularios y Validación en Tiempo Real

## Objetivo

Implementar tests de navegador para los formularios públicos y la búsqueda en tiempo real de la aplicación. Estos tests validan el comportamiento completo desde la perspectiva del usuario: validación de formularios (errores mostrados tras submit o en tiempo real), envío exitoso, mensajes de confirmación, y búsqueda con actualización dinámica de resultados (wire:model.live, debounce). Se utilizan Pest v4 con Playwright.

---

## Estado Actual

### ✅ Ya Implementado

1. **Configuración de Browser Tests (Pasos 3.11.1, 3.11.2, 3.11.3)**:
   - Pest v4 con `pest-plugin-browser` y Playwright
   - Estructura `tests/Browser/` con `Public/`, `Auth/`, `Admin/`
   - Helpers: `createPublicTestData()`, `createAuthTestUser()`, `performLogin()`, `ensureRolesExist()`, `createHomeTestData()`, `createProgramsTestData()`, `createCallsTestData()`, `createNewsTestData()`, etc.
   - `RefreshDatabase` en tests de Browser

2. **Formulario de Suscripción a Newsletter**:
   - **Componente**: `App\Livewire\Public\Newsletter\Subscribe`
   - **Ruta**: `newsletter.subscribe` → `/newsletter/suscribir`
   - **Campos**: `email` (requerido), `name` (opcional), `selectedPrograms` (checkboxes, opcional), `acceptPrivacy` (checkbox, requerido)
   - **Validación**: `StoreNewsletterSubscriptionRequest` (email required, email, max:255, unique; name nullable, max:255; programs.* exists) + en `subscribe()`: `acceptPrivacy` accepted
   - **Flujo**: submit → crear `NewsletterSubscription` inactiva → enviar `NewsletterVerificationMail` → `$subscribed = true` → mensaje de éxito (session flash `newsletter-subscribed` y vista)
   - **Vista**: `resources/views/livewire/public/newsletter/subscribe.blade.php` — `wire:model` en email, name, acceptPrivacy; `wire:click="toggleProgram"` en programas; `wire:submit="subscribe"`
   - **Tests Feature**: `tests/Feature/Livewire/Public/Newsletter/SubscribeTest.php` (rendering, validation, success, Mail::fake)

3. **Búsqueda Global**:
   - **Componente**: `App\Livewire\Search\GlobalSearch`
   - **Ruta**: `search` → `/buscar`
   - **Comportamiento en tiempo real**: `wire:model.live.debounce.300ms="query"` en el input de búsqueda; `wire:model.live` en tipos (checkboxes) y en selects de programa y año académico
   - **Filtros**: tipos (programs, calls, news, documents), programa, año académico; panel "Filtros avanzados" con `toggleFilters`
   - **Resultados**: agrupados por tipo; enlaces con `wire:navigate` a rutas públicas o admin según contexto
   - **Tests Feature**: `tests/Feature/Search/GlobalSearchTest.php` (render, búsqueda por tipo, filtros, empty, etc.)

4. **Formularios de Administración en área pública**:
   - No aplica: los formularios de administración (CRUD) están en rutas `/admin/*` y requieren autenticación. No existe ningún formulario de administración expuesto en el área pública. El formulario de Newsletter es el único formulario público complejo con validación. Los de autenticación (login, registro, recuperación) se cubrieron en el paso 3.11.3.

### ⚠️ Pendiente de Implementar

1. ~~**Tests de navegador del formulario de Newsletter**~~ ✅ Completado (Fase 2).
2. ~~**Tests de navegador de la Búsqueda Global**~~ ✅ Completado (Fase 3).
3. ~~**Helpers específicos**~~ ✅ Completado (Fase 1).

---

## Dependencias y Premisas

- **Idioma en tests**: Por defecto puede usarse el locale de la app. Para aserciones con textos traducidos, usar `__('common.newsletter.subscribe')` o el texto esperado en el idioma configurado. Si hace falta, fijar `App::setLocale('es')` en `beforeEach` para los tests de Newsletter.
- **Mail en Newsletter**: En tests de suscripción exitosa usar `Mail::fake()` antes del flujo en el navegador; tras el submit, verificar con `Mail::assertSent(NewsletterVerificationMail::class)` (o equivalente). La suscripción se crea en BD; se puede comprobar con `assertDatabaseHas('newsletter_subscriptions', ['email' => '...'])`.
- **Debounce en Búsqueda Global**: El input usa `wire:model.live.debounce.300ms`. Tras escribir en el input, esperar al menos ~400–500 ms (en la práctica, 1 s es seguro) antes de comprobar resultados. Usar la API de espera de Pest Browser/Playwright (p. ej. `$page->wait(1)`, `sleep(1)`, o la que ofrezca el plugin) para evitar falsos negativos.
- **Selectores**: `fill('email', ...)` por name o label; `click('Suscribirse')` por texto del botón. Para checkboxes de programas, hacer `click` en el label o en el texto del programa. Para la búsqueda: `fill` en el input de búsqueda (revisar si usa `name`, `id` o `x-ui.search-input`); en `global-search.blade.php` el input viene de `<x-ui.search-input wire:model.live.debounce.300ms="query" ... />` — verificar en el componente el `name` o identificar por placeholder/label.
- **Programas activos**: El formulario Newsletter solo muestra programas con `is_active = true`. Los helpers o `beforeEach` deben crear `Program::factory()->create(['is_active' => true, 'code' => 'KA1', ...])` para poder seleccionar programas.

---

## Plan de Trabajo

### Fase 1: Helpers y Datos de Prueba para Newsletter y Búsqueda

**Objetivo**: Tener datos de prueba y, si hace falta, helpers reutilizables para los tests de Newsletter y GlobalSearch.

#### 1.1. Helper para datos de Newsletter (opcional)

**Archivo**: `tests/Browser/Helpers.php`

- [x] **Función `createNewsletterTestData(): array`** (opcional, si se quiere centralizar):
  - Crear 2–3 programas activos con `code` conocido (p. ej. `KA1`, `KA2`) para poder marcar checkboxes en los tests.
  - Devolver `['programs' => $programs]`.
  - Si los tests crean los programas en `beforeEach` directamente, este helper puede omitirse.

- [x] Decisión: Se añadió `createNewsletterTestData()` con 3 programas (KA1, KA2, KA3) y nombres/order para que los tests de Newsletter puedan usar `toggleProgram($program->code)` de forma fiable.

#### 1.2. Datos para Búsqueda Global

- [x] Se añadió **`createGlobalSearchTestData(): array`** en `tests/Browser/Helpers.php`. Crea program ("Programa de Movilidad", KA1), academicYear (2024-2025), call ("Convocatoria de Movilidad", abierta, published), news ("Noticia sobre Movilidad", publicado), document ("Documento de Movilidad", is_active). Todos con el término "Movilidad" para una búsqueda única. El `GlobalSearchTest` (browser) podrá usar este helper en `beforeEach` o invocarlo bajo demanda.

---

### Fase 2: Tests del Formulario de Suscripción a Newsletter ✅ COMPLETADA

**Objetivo**: Comprobar en el navegador el formulario, la validación, la selección de programas, el envío exitoso, la confirmación y el manejo de errores.

**Archivo**: `tests/Browser/Public/NewsletterSubscribeTest.php`

#### 2.1. Configuración y `beforeEach`

- [x] `RefreshDatabase` vía Pest `in('Browser')`.
- [x] `beforeEach`: `App::setLocale('es')` y `createNewsletterTestData()` (programas KA1, KA2, KA3).

#### 2.2. Tests a implementar

- [x] **Test: Verificar formulario de suscripción**
  - `visit(route('newsletter.subscribe'))`
  - `assertSee` textos clave: p. ej. `__('common.newsletter.stay_informed')` o equivalente, `__('common.newsletter.email')`, `__('common.newsletter.subscribe')`
  - `assertPresent` para input email (p. ej. `input[name="email"]` o por label).
  - Comprobar que se listan los programas activos (nombres o códigos).
  - Comprobar checkbox de privacidad y botón "Suscribirse" (o `__('common.newsletter.subscribe')`).
  - `assertNoJavascriptErrors()`.

- [x] **Test: Validación de email — campo vacío**
  - `visit(route('newsletter.subscribe'))`
  - Dejar email vacío, marcar `acceptPrivacy` (click en el checkbox o en su label).
  - `click(__('common.newsletter.subscribe'))` o el texto del botón.
  - `assertSee` mensaje de error relacionado con email requerido (texto según `lang` o validación de Laravel). Permanecer en la ruta de suscripción.

- [x] **Test: Validación de email — formato inválido**
  - `fill('email', 'invalid')`, `acceptPrivacy` marcado, submit.
  - `assertSee` error de formato de email.

- [x] **Test: Validación de email — duplicado**
  - `NewsletterSubscription::factory()->create(['email' => 'existente@example.com'])`
  - `fill('email', 'existente@example.com')`, `acceptPrivacy` marcado, submit.
  - `assertSee` mensaje de email ya registrado / unique.

- [x] **Test: Validación de aceptación de privacidad**
  - `fill('email', 'nuevo@example.com')`, **no** marcar `acceptPrivacy`, submit.
  - `assertSee` el mensaje personalizado `'Debe aceptar la política de privacidad para suscribirse.'` o la clave traducida equivalente.

- [x] **Test: Selección de programas de interés**
  - `visit(route('newsletter.subscribe'))`
  - `fill('email', 'test@example.com')`, marcar `acceptPrivacy`
  - Hacer click en el label/checkbox de al menos un programa (p. ej. el que tenga `code` 'KA1' o el primer programa listado).
  - Submit. En un test con `Mail::fake()`, comprobar que la suscripción se crea con `programs` conteniendo ese código (`assertDatabaseHas` o equivalente vía modelo). Si el test se centra solo en “selección visible”, comprobar que el flujo llega a éxito y que no hay error de validación en `selectedPrograms`/`programs.*`.

- [x] **Test: Envío exitoso y confirmación**
  - `Mail::fake()`
  - Crear programas activos en `beforeEach`.
  - `visit(route('newsletter.subscribe'))` → `fill('email', 'nuevo@example.com')` → marcar `acceptPrivacy` → `click(__('common.newsletter.subscribe'))`
  - `assertSee(__('common.newsletter.subscription_success'))` o equivalente.
  - `assertSee(__('common.newsletter.verification_email_sent'))` o equivalente.
  - `Mail::assertSent(NewsletterVerificationMail::class)`
  - `$this->assertDatabaseHas('newsletter_subscriptions', ['email' => 'nuevo@example.com'])`.

- [x] **Test: Manejo de errores — no se muestra éxito si hay error de validación**
  - Submit con email duplicado (o inválido) y `acceptPrivacy` marcado.
  - `assertDontSee(__('common.newsletter.subscription_success'))` (o que no aparezca el bloque de éxito). Opcional: comprobar que sigue visible el formulario (p. ej. el botón Suscribirse o el campo email).

- [x] **Test: Sin errores de JavaScript en la página de suscripción**
  - `visit(route('newsletter.subscribe'))` → `assertNoJavascriptErrors()`.

#### 2.3. Detalles de implementación (selectores y convenciones)

- **Vista**: Se añadió `name="email"` al `flux:input` de email y `name="acceptPrivacy"` al checkbox de privacidad.
- **Email**: `fill('email', '...')`. **AcceptPrivacy**: `check('acceptPrivacy')`. **Programas**: `click('Programa KA1')`. **Submit**: `click(__('common.newsletter.subscribe'))`.
- **script()**: No encadenar con `check`/`fill` (devuelve el resultado JS). Usar para quitar `required` o `type="email"` y forzar validación servidor. **wait(1)** tras submit antes de `assertSee` de errores.

---

### Fase 3: Tests de la Búsqueda Global ✅ COMPLETADA

**Objetivo**: Comprobar en el navegador la búsqueda en tiempo real, los resultados, los filtros avanzados y la navegación a los resultados.

**Archivo**: `tests/Browser/Public/GlobalSearchTest.php`

#### 3.1. Configuración y `beforeEach`

- [x] `RefreshDatabase` vía Pest `in('Browser')`.
- [x] `beforeEach`: `App::setLocale('es')` y `createGlobalSearchTestData()` (Program, AcademicYear, Call, NewsPost, Document con "Movilidad").

#### 3.2. Tests a implementar

- [x] **Test: Verificar página de búsqueda**
  - `visit(route('search'))`
  - `assertSee(__('common.search.global_title'))`, `assertSee(__('common.search.global_description'))`
  - `assertSee(__('common.search.start_search'))` o equivalente cuando `query` está vacío.
  - `assertNoJavascriptErrors()`.

- [x] **Test: Búsqueda en tiempo real — resultados de programas**
  - `visit(route('search'))`
  - `fill` en el input de búsqueda el término que coincida con un programa (p. ej. "Movilidad").
  - Esperar al debounce (≥ 400 ms, p. ej. 1 s) para que Livewire envíe la petición y se rendericen resultados.
  - `assertSee` el nombre del programa creado en `beforeEach`.
  - `assertSee(__('common.search.programs'))` en el encabezado de la sección.

- [x] **Test: Búsqueda en tiempo real — resultados de convocatorias, noticias, documentos**
  - Similar al anterior: término que coincida con Call, NewsPost, Document. Tras esperar debounce, `assertSee` títulos/contenido y los encabezados de sección (`common.search.calls`, `common.search.news`, `common.search.documents`).

- [x] **Test: Resultados vacíos**
  - `fill` con un término que no coincida con nada (p. ej. "XyZAbC123Nada").
  - Esperar debounce.
  - `assertSee(__('common.search.no_results'))` y `assertSee(__('common.search.no_results_message'))`.

- [x] **Test: Filtros avanzados — mostrar/ocultar panel**
  - `visit(route('search'))`
  - `click(__('common.search.advanced_filters'))` → `assertSee(__('common.search.content_types'))` (o texto del panel de filtros).
  - `click` de nuevo en "Filtros avanzados" (o el botón que hace `toggleFilters`) → comprobar que el panel se oculta (por ejemplo, que no se ve "content_types" o que el botón cambia de chevron). Ajustar según la vista (chevron-up/chevron-down).

- [x] **Test: Filtro por programa**
  - Crear 2 programas (uno con "Movilidad" en nombre, otro "Otro"). Crear Call/News solo para el primero.
  - `visit(route('search'))` → `fill('query', 'Movilidad')` → esperar debounce.
  - Abrir filtros avanzados → en el select de programa, elegir el programa que tiene "Movilidad" (por `fill` en el select o `select_option`). El componente usa `wire:model.live="program"`.
  - Esperar a que se actualicen los resultados. Comprobar que se muestran resultados del programa elegido. (Opcional: comprobar que al elegir el otro programa, los resultados de "Movilidad" en Call/News no aparecen si solo están asociados al primero.)

- [x] **Test: Botón “Limpiar búsqueda” (clear search)**
  - `visit(route('search'))` → `fill('query', 'algo')` → esperar debounce (para que exista el botón de limpiar).
  - `click(__('common.search.clear_search'))`. Comprobar que el input se limpia y que se muestra de nuevo el estado inicial (`common.search.start_search` o similar).

- [x] **Test: Navegación a un resultado**
  - `visit(route('search'))` → `fill('query', 'Movilidad')` → esperar debounce.
  - `assertSee` al menos un enlace (nombre de programa, convocatoria, etc.).
  - `click` en el enlace de uno de los resultados (p. ej. el programa). Comprobar que se navega a la ruta pública correcta (`programas.show` por slug, o `convocatorias.show`, etc.) y que la página de detalle muestra el contenido esperado (`assertSee` nombre/título del recurso). Se usa `wire:navigate`, por lo que la transición puede ser SPA; `assertPathIs` o `assertUrlContains` según la ruta.

- [x] **Test: Sin errores de JavaScript en la página de búsqueda**
  - `visit(route('search'))` → `assertNoJavascriptErrors()`.
  - Opcional: tras realizar una búsqueda y abrir filtros, `assertNoJavascriptErrors()` de nuevo.

#### 3.3. Detalles de implementación

- **Input de búsqueda**: se añadió `name="query"` al `x-ui.search-input` en `global-search.blade.php`. Uso: `fill('query', 'término')`.
- **Debounce**: tras `fill('query', ...)` se usa `wait(1)` antes de aserciones de resultados.
- **Select de programa**: `select('#program-filter', 'Programa de Movilidad')` por id y texto de la opción.

---

### Fase 4: Formularios de Administración en Área Pública ✅ COMPLETADA

**Objetivo**: Dejar documentada la decisión y, si en el futuro hubiera formularios de admin en área pública, un lugar donde añadir tests.

- [x] **Decisión**: No hay formularios de administración en el área pública. Los CRUD de admin están en `/admin/*` y requieren autenticación; los tests de acceso y formularios de admin se contemplan en pasos de tests de componentes de administración (p. ej. 3.8.4) o en un futuro paso de browser tests de admin.
- [x] En el plan se deja explícito que el ítem "Test de Formularios de Administración (si aplica en área pública)" **no aplica** en este momento. Si más adelante se expusiera algún formulario de admin en una ruta pública, se añadirían tests en un archivo `tests/Browser/Public/AdminFormXTest.php` o similar, siguiendo el mismo patrón: validación, mensajes de error, envío exitoso.

---

### Fase 5: Documentación y Verificación Final

#### 5.1. Documentación

- [x] Crear o actualizar una sección en `docs/browser-testing-public-pages.md` con:
  - Resumen de los archivos: `NewsletterSubscribeTest.php`, `GlobalSearchTest.php`.
  - Descripción de los escenarios: validación de newsletter, envío y confirmación, búsqueda en tiempo real, filtros, navegación a resultados.
  - Convenciones: uso de `fill`, `click`, espera de debounce, `Mail::fake()` en newsletter.
  - Comandos: `./vendor/bin/pest tests/Browser/Public/NewsletterSubscribeTest.php`, `./vendor/bin/pest tests/Browser/Public/GlobalSearchTest.php`, `--headed`, `--debug`.

#### 5.2. Actualizar `docs/planificacion_pasos.md`

- [x] En el paso 3.11.4, marcar como completados los ítems según el avance:
  - [x] Test de Formulario de Suscripción Newsletter
  - [x] Test de Formularios de Administración (si aplica en área pública) — N/A, anotado.
  - [x] Test de Búsqueda Global

#### 5.3. Verificación final

- [x] Ejecutar:
  - `./vendor/bin/pest tests/Browser/Public/NewsletterSubscribeTest.php`
  - `./vendor/bin/pest tests/Browser/Public/GlobalSearchTest.php`
  - Comprobar que todos pasan.
- [x] Revisar que no queden `skip()` o `todo()` sin justificar.
- [ ] Opcional: ejecutar `./vendor/bin/pest tests/Browser` y comprobar que la suite ampliada sigue pasando.

---

## Estructura de Archivos Final

```
tests/
├── Browser/
│   ├── Helpers.php                    # + createNewsletterTestData (opcional)
│   └── Public/
│       ├── NewsletterSubscribeTest.php   # NUEVO
│       ├── GlobalSearchTest.php          # NUEVO
│       ├── HomeTest.php
│       ├── ProgramsIndexTest.php
│       ├── ProgramsShowTest.php
│       ├── CallsIndexTest.php
│       ├── CallsShowTest.php
│       ├── NewsIndexTest.php
│       ├── NewsShowTest.php
│       ├── ...
│       └── ...
```

---

## Criterios de Éxito

1. **Newsletter**
   - Formulario: se ven email, nombre, programas, privacidad y botón Suscribirse.
   - Validación: email vacío, formato inválido, email duplicado y aceptación de privacidad devuelven mensajes de error visibles en la página.
   - Selección de programas: se pueden marcar y la suscripción exitosa puede incluir `programs` en BD (opcional en un test dedicado).
   - Envío exitoso: se muestra el mensaje de éxito, se envía `NewsletterVerificationMail` y se crea el registro en `newsletter_subscriptions`.
   - Sin errores de JavaScript en la página de suscripción.

2. **Búsqueda Global**
   - Página: se ven título, descripción y estado inicial ("Comienza tu búsqueda").
   - Búsqueda en tiempo real: al escribir un término, tras el debounce se muestran resultados agrupados por tipo (programas, convocatorias, noticias, documentos) cuando aplique.
   - Resultados vacíos: se muestra "No se encontraron resultados" (o equivalente).
   - Filtros: el panel de filtros avanzados se muestra/oculta; el filtro por programa (y opcionalmente año) restringe resultados.
   - Limpiar búsqueda: el botón limpia el término y vuelve al estado inicial.
   - Navegación: al hacer click en un resultado se llega a la página de detalle correcta (pública).
   - Sin errores de JavaScript.

3. **Formularios de administración en área pública**
   - N/A; documentado en el plan.

4. **Documentación**
   - `docs` actualizada y `planificacion_pasos.md` con el estado del paso 3.11.4.

---

## Notas Importantes

1. **Validación “en tiempo real”**: En el formulario Newsletter la validación se ejecuta en el servidor al hacer submit; los errores se muestran en la respuesta de Livewire sin recarga completa. Se considera “validación en tiempo real” en el sentido de que el usuario ve los errores de inmediato tras enviar. La Búsqueda Global sí usa `wire:model.live.debounce` y actualiza resultados sin submit.

2. **Componente `x-ui.search-input`**: Antes de implementar, revisar la vista/componente para saber el `name`/`id`/placeholder del input y si admite `fill` por label. Si es necesario, añadir `data-test="search-query"` o `name="query"` para estabilizar los tests.

3. **Espera al debounce**: La búsqueda usa 300 ms de debounce. En CI o con carga, puede hacer falta esperar más. Usar 1 s como valor seguro; si los tests son inestables, aumentar o usar `browser_wait_for` con reintentos.

4. **Navegación con `wire:navigate`**: Los enlaces de resultados usan `wire:navigate`. Pest/Playwright debe seguir la navegación SPA correctamente. Si se observan fallos, comprobar que la URL y el contenido de la página destino son los esperados; en algunos entornos puede ser necesario un `wait` corto tras el `click`.

5. **Mail y base de datos**: `Mail::fake()` y `assertDatabaseHas` se ejecutan en el mismo proceso PHP que el test, por lo que son válidos en browser tests. Asegurarse de que `Mail::fake()` se llame antes de `visit` (es decir, antes de cualquier petición que pueda disparar el envío).

6. **Idioma**: Si los textos de `assertSee` dependen del locale, fijar `App::setLocale` en `beforeEach` o aceptar que el locale por defecto de la app (o el de la petición) determine los textos. En la primera iteración puede usarse solo un idioma (p. ej. `es`) para simplificar.

---

## Próximos Pasos

Tras completar el paso 3.11.4:

- **Paso 3.11.5**: Tests de interacciones JavaScript y componentes dinámicos (Livewire, filtros, modales, tabs, paginación, etc.).
- **Paso 3.11.6**: Tests de rendimiento y optimización.

---

**Fecha de Creación**: Enero 2026  
**Estado**: ✅ Completado (Fases 1–5)
