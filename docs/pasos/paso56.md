# Paso 56: Paso 3.11.4 — Tests de Formularios y Validación en Tiempo Real (Registro de Prompts)

**Fecha**: Enero 2026  
**Paso de planificación**: 3.11.4 — Tests de Formularios y Validación en Tiempo Real

---

## Índice

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Prompts y Respuestas](#prompts-y-respuestas)
3. [Archivos Creados o Modificados](#archivos-creados-o-modificados)

---

## Resumen Ejecutivo

Este documento recoge todos los **prompts** utilizados en el chat para desarrollar el **Paso 3.11.4** y un **resumen de las respuestas** obtenidas. El paso implementa tests de navegador (Pest v4 + Playwright) para:

- **Formulario de suscripción a Newsletter**: validación (email vacío, inválido, duplicado, privacidad), selección de programas, envío exitoso, manejo de errores.
- **Búsqueda global**: búsqueda en tiempo real (`wire:model.live.debounce.300ms`), resultados por tipo, filtros avanzados, limpiar búsqueda, navegación a resultados.
- **Formularios de admin en área pública**: no aplica; se documenta la decisión.

**Fases completadas**: 1 (Helpers), 2 (Newsletter), 3 (GlobalSearch), 4 (N/A admin), 5 (Documentación y verificación).

**Tests**: 19 (9 Newsletter + 10 GlobalSearch).

---

## Prompts y Respuestas

### Prompt 1: Plan detallado del paso 3.11.4

**Usuario:**
> En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que actúa de índice y otro llamado @docs/planificacion_pasos.md que contiene las tareas a desarrollar en orden. Estamos en situación de comenzar con el desarrollo del paso 3.11.4 que corresponde a los Tests de Formularios y Validación en Tiempo Real. Desarrolla primero el plan detallado de todo el proceso a seguir y guardalo en el archivo cuyo nombre se indica en la propia documentación.

**Resumen de la respuesta:**
- Se redactó el plan en `docs/pasos/paso-3.11.4-plan.md` con: objetivo, estado actual, dependencias, 5 fases.
- **Fase 1**: Helpers `createNewsletterTestData()` y `createGlobalSearchTestData()` en `tests/Browser/Helpers.php`.
- **Fase 2**: Tests del formulario Newsletter (formulario, validaciones, programas, éxito, errores, JS) en `tests/Browser/Public/NewsletterSubscribeTest.php`.
- **Fase 3**: Tests de Búsqueda Global en `tests/Browser/Public/GlobalSearchTest.php` (página, búsqueda en tiempo real, resultados vacíos, filtros, limpiar, navegación, JS).
- **Fase 4**: Formularios de admin en área pública → N/A; decisión documentada en el plan.
- **Fase 5**: Documentación en `docs/browser-testing-public-pages.md` y verificación final.

---

### Prompt 2: Implementación de la Fase 1

**Usuario:**
> Me parece adecuado. Comencemos con la implementación de la Fase 1.

**Resumen de la respuesta:**
- Se añadió **`createNewsletterTestData()`** en `tests/Browser/Helpers.php`: crea 3 programas activos con códigos `KA1`, `KA2`, `KA3` y nombres/`order` para que los tests de Newsletter puedan usar `toggleProgram($program->code)` de forma fiable.
- Se añadió **`createGlobalSearchTestData()`** en `tests/Browser/Helpers.php`: crea program ("Programa de Movilidad", KA1), academicYear (2024-2025), call ("Convocatoria de Movilidad", abierta, published), news ("Noticia sobre Movilidad", publicado), document ("Documento de Movilidad", is_active). Todos con el término "Movilidad" para búsquedas únicas.
- Se creó **`tests/Feature/BrowserHelpersTest.php`** con tests que comprueban que ambos helpers generan los datos esperados en BD (programas, call, news, document).
- En el plan se marcaron 1.1 y 1.2 como realizados.

---

### Prompt 3: Fase 2 — Tests del formulario Newsletter

**Usuario:**
> Sigamos con la Fase 2.

**Resumen de la respuesta:**
- Se creó **`tests/Browser/Public/NewsletterSubscribeTest.php`** con 9 tests:
  - **beforeEach**: `App::setLocale('es')`, `createNewsletterTestData()`.
  - Formulario: `assertSee` (stay_informed, email, subscribe, Programas KA1/KA2, accept_privacy), `assertPresent('input[name="email"]')`, `assertNoJavascriptErrors`.
  - Email vacío: `script()` para quitar `required`, `check('acceptPrivacy')`, `click` Suscribirse, `wait(1)`, `assertSee('obligatorio')`.
  - Email inválido: `script()` para poner `type="text"`, `fill('email','invalid')`, `check`, `click`, `wait(1)`, `assertSee('válida')`.
  - Email duplicado: `NewsletterSubscription::factory()->create`, `fill`, `check`, `click`, `wait(1)`, `assertDontSee(subscription_success)`, `assertSee(common.newsletter.email)`, `assertDatabaseCount('newsletter_subscriptions', 1)`.
  - Privacidad: `fill('email','nuevo@example.com')`, sin `check`, `click`, `assertSee('Debe aceptar la política de privacidad')`.
  - Programas: `fill`, `check`, `click('Programa KA1')`, `click` Suscribirse; `Mail::fake()`; `assertSee` éxito; comprobar `programs` con 'KA1' en la suscripción.
  - Éxito: `Mail::fake()`, `fill`, `check`, `click`, `assertSee` subscription_success y verification_email_sent, `Mail::assertSent(NewsletterVerificationMail::class)`, `assertDatabaseHas`.
  - Errores: email duplicado, `assertDontSee` éxito, `assertSee` label email.
  - JS: `assertNoJavascriptErrors`.
- En **`resources/views/livewire/public/newsletter/subscribe.blade.php`** se añadieron `name="email"` al `flux:input` de email y `name="acceptPrivacy"` al checkbox de privacidad para que `fill` y `check` funcionen.
- Se ejecutaron los tests (9 pasan) y se marcó la Fase 2 como completada en el plan y en `docs/planificacion_pasos.md`.

---

### Prompt 4: Fase 3 — Tests de Búsqueda Global

**Usuario:**
> Vayamos a la fase 3.

**Resumen de la respuesta:**
- Se añadió **`name="query"`** al `x-ui.search-input` en `resources/views/livewire/search/global-search.blade.php` para poder usar `fill('query', 'término')` en los tests.
- Se creó **`tests/Browser/Public/GlobalSearchTest.php`** con 10 tests:
  - **beforeEach**: `App::setLocale('es')`, `createGlobalSearchTestData()`.
  - Página: `assertSee` global_title, global_description, start_search, `assertNoJavascriptErrors`.
  - Programas: `fill('query','Movilidad')`, `wait(1)`, `assertSee` 'Programa de Movilidad', `assertSee` programs.
  - Calls, news, documents: mismo patrón; `assertSee` títulos y secciones (calls, news, documents).
  - Resultados vacíos: `fill('query','XyZAbC123Nada')`, `wait(1)`, `assertSee` no_results y no_results_message.
  - Filtros: `click` advanced_filters → `assertSee` content_types; otro `click` → `assertDontSee` content_types.
  - Filtro por programa: crear programa "Otro", `fill`, `wait`, `click` advanced_filters, `select('#program-filter','Programa de Movilidad')`, `wait`, `assertSee` Programa, Convocatoria, Noticia.
  - Limpiar búsqueda: `fill('query','algo')`, `wait`, `click` clear_search, `assertSee` start_search.
  - Navegación: `fill`, `wait`, `click` 'Programa de Movilidad', `wait`, `assertPathBeginsWith('/programas/')`, `assertSee` 'Programa de Movilidad'.
  - JS: `assertNoJavascriptErrors` en página inicial y tras búsqueda + filtros.
- Se ejecutaron los tests (10 pasan) y se marcó la Fase 3 como completada en el plan y en `docs/planificacion_pasos.md`.

---

### Prompt 5: Fases 4 y 5 — Adelante

**Usuario:**
> Adelante.

**Resumen de la respuesta:**
- **Fase 4**: Se marcó como completada la decisión de que no hay formularios de admin en área pública; los CRUD están en `/admin/*` con autenticación. Se dejó indicado que, si en el futuro se expusiera alguno en ruta pública, los tests irían en `tests/Browser/Public/AdminFormXTest.php` siguiendo el mismo patrón.
- **Fase 5.1 — Documentación**: Se actualizó **`docs/browser-testing-public-pages.md`**:
  - Estructura de tests: se añadieron `NewsletterSubscribeTest.php` y `GlobalSearchTest.php`.
  - Helpers: `createNewsletterTestData()` y `createGlobalSearchTestData()`.
  - Nuevas secciones: *Formulario de Suscripción Newsletter* y *Búsqueda Global* con objetivo, tests, convenciones (`fill`, `check`, `script()`, `Mail::fake()`, `wait(1)`, `fill('query',...)`, `select('#program-filter',...)`) y comandos. Referencia al plan 3.11.4.
- **Fase 5.2**: En `docs/planificacion_pasos.md` los ítems del paso 3.11.4 ya estaban marcados (Newsletter, Formularios Admin N/A, Búsqueda Global). Se marcaron las tareas 5.2 en el plan.
- **Fase 5.3**: Se ejecutaron `NewsletterSubscribeTest` y `GlobalSearchTest` (19 tests pasan). No hay `skip()` ni `todo()` sin justificar en esos archivos. Opcional: ejecutar `./vendor/bin/pest tests/Browser` (parcialmente verificado; suite ampliada).
- Se actualizó el **estado** del plan 3.11.4 a: *Completado (Fases 1–5)*.

---

### Prompt 6: Generar archivo paso56 con prompts y resúmenes

**Usuario:**
> Para terminar, genera un archivo llamado paso56 en docs/pasos con todos los prompts de este chat y un resumen de las respuestas obtenidas.

**Resumen de la respuesta:**
- Se creó **`docs/pasos/paso56.md`** con: resumen ejecutivo del paso 3.11.4, todos los prompts anteriores (1–5) y sus resúmenes de respuesta, y la lista de archivos creados o modificados. El propio contenido de este archivo es la respuesta a este prompt.

---

## Archivos Creados o Modificados

| Archivo | Acción |
|---------|--------|
| `docs/pasos/paso-3.11.4-plan.md` | Creado/actualizado — Plan detallado del paso 3.11.4 |
| `tests/Browser/Helpers.php` | Modificado — `createNewsletterTestData()`, `createGlobalSearchTestData()` |
| `tests/Feature/BrowserHelpersTest.php` | Creado — Tests de los helpers de Browser |
| `tests/Browser/Public/NewsletterSubscribeTest.php` | Creado — 9 tests del formulario Newsletter |
| `resources/views/livewire/public/newsletter/subscribe.blade.php` | Modificado — `name="email"`, `name="acceptPrivacy"` |
| `resources/views/livewire/search/global-search.blade.php` | Modificado — `name="query"` en `x-ui.search-input` |
| `tests/Browser/Public/GlobalSearchTest.php` | Creado — 10 tests de Búsqueda Global |
| `docs/browser-testing-public-pages.md` | Modificado — Secciones Newsletter, GlobalSearch, helpers, referencias |
| `docs/planificacion_pasos.md` | Modificado — Paso 3.11.4 marcado como completado |
| `docs/pasos/paso56.md` | Creado — Este archivo (prompts y resúmenes) |

---

**Última actualización**: Enero 2026
