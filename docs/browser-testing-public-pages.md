# Tests de Navegador - Páginas Públicas

## Descripción General

Este documento describe los tests de navegador implementados para las páginas públicas críticas de la aplicación Erasmus+. Estos tests utilizan Pest v4 con el plugin de browser testing para simular interacciones reales del usuario y detectar problemas que solo aparecen en el renderizado completo (lazy loading, errores de JavaScript, problemas de CSS, etc.).

## Estructura de Tests

Los tests están organizados en el directorio `tests/Browser/Public/` con la siguiente estructura:

```
tests/Browser/Public/
├── HomeTest.php                # Tests de página de inicio
├── ProgramsIndexTest.php        # Tests de listado de programas
├── ProgramsShowTest.php        # Tests de detalle de programa
├── CallsIndexTest.php          # Tests de listado de convocatorias
├── CallsShowTest.php          # Tests de detalle de convocatoria
├── NewsIndexTest.php          # Tests de listado de noticias
├── NewsShowTest.php           # Tests de detalle de noticia
├── NewsletterSubscribeTest.php # Tests de formulario de suscripción newsletter
├── GlobalSearchTest.php       # Tests de búsqueda global en tiempo real
├── PerformanceTest.php        # Tests de rendimiento
└── AccessibilityTest.php      # Tests de accesibilidad básica
```

## Helpers de Datos de Prueba

Los helpers están ubicados en `tests/Browser/Helpers.php` y proporcionan funciones para crear datos de prueba realistas:

- `createPublicTestData()` - Datos básicos para tests generales
- `createHomeTestData()` - Datos completos para página de inicio
- `createProgramsTestData()` - Datos para listado de programas
- `createProgramShowTestData()` - Datos para detalle de programa
- `createCallsTestData()` - Datos para listado de convocatorias
- `createCallShowTestData()` - Datos para detalle de convocatoria
- `createNewsTestData()` - Datos para listado de noticias
- `createNewsShowTestData()` - Datos para detalle de noticia
- `createNewsletterTestData()` - Datos para formulario newsletter (programas KA1, KA2, KA3)
- `createGlobalSearchTestData()` - Datos para búsqueda global (programa, call, news, document con «Movilidad»)

## Cómo Ejecutar los Tests

### Ejecutar todos los tests de páginas públicas

```bash
./vendor/bin/pest tests/Browser/Public
```

### Ejecutar un archivo específico

```bash
./vendor/bin/pest tests/Browser/Public/HomeTest.php
```

### Ejecutar un test específico

```bash
./vendor/bin/pest tests/Browser/Public/HomeTest.php --filter="can visit home page"
```

### Ejecutar tests con cobertura

```bash
./vendor/bin/pest tests/Browser/Public --coverage
```

## Descripción de Tests por Página

### Home (HomeTest.php)

**Objetivo**: Verificar que la página de inicio carga correctamente y muestra todos los elementos esperados.

**Tests principales**:
- Renderizado básico de la página
- Visualización de programas activos (máximo 6)
- Visualización de convocatorias abiertas (máximo 4)
- Visualización de noticias publicadas (máximo 3)
- Visualización de eventos próximos (máximo 5)
- Verificación de eager loading
- Estados vacíos cuando no hay datos

**Qué se verifica**:
- La página carga sin errores de JavaScript
- Los elementos se muestran correctamente
- No hay problemas de lazy loading
- Los límites de elementos se respetan

### Listado de Programas (ProgramsIndexTest.php)

**Objetivo**: Verificar el funcionamiento del listado de programas con filtros y búsqueda.

**Tests principales**:
- Renderizado del listado
- Visualización de programas activos e inactivos
- Filtros por tipo de programa
- Búsqueda por nombre, código y descripción
- Paginación
- Estadísticas
- Reset de filtros
- Verificación de eager loading

**Qué se verifica**:
- Los filtros funcionan correctamente
- La búsqueda encuentra resultados relevantes
- La paginación mantiene los filtros
- No hay consultas N+1

### Detalle de Programa (ProgramsShowTest.php)

**Objetivo**: Verificar que el detalle de programa muestra toda la información correctamente.

**Tests principales**:
- Renderizado del detalle
- Configuración visual según tipo de programa
- Visualización de imagen del programa
- Convocatorias relacionadas (máximo 4)
- Noticias relacionadas (máximo 3)
- Otros programas sugeridos (máximo 3)
- Navegación (breadcrumbs, enlaces)
- Verificación de eager loading
- Estados vacíos

**Qué se verifica**:
- Las relaciones están eager loaded
- Los límites de elementos relacionados se respetan
- La navegación funciona correctamente

### Listado de Convocatorias (CallsIndexTest.php)

**Objetivo**: Verificar el funcionamiento del listado de convocatorias con múltiples filtros.

**Tests principales**:
- Renderizado del listado
- Visualización de convocatorias
- Filtros por programa, año académico, tipo, modalidad y estado
- Búsqueda por título, requisitos y documentación
- Combinación de filtros
- Paginación
- Estadísticas
- Ordenamiento
- Verificación de eager loading

**Qué se verifica**:
- Todos los filtros funcionan correctamente
- La búsqueda funciona en múltiples campos
- La paginación mantiene los filtros
- No hay consultas N+1

### Detalle de Convocatoria (CallsShowTest.php)

**Objetivo**: Verificar que el detalle de convocatoria muestra toda la información y relaciones.

**Tests principales**:
- Renderizado del detalle
- Configuración visual según estado
- Acceso a convocatorias no publicadas (404)
- Visualización de fases
- Visualización de resoluciones publicadas
- Noticias relacionadas (máximo 3)
- Otras convocatorias del mismo programa (máximo 3)
- Navegación
- Verificación de eager loading
- Estados vacíos

**Qué se verifica**:
- Las fases y resoluciones están eager loaded
- Solo se muestran resoluciones publicadas
- Los límites de elementos relacionados se respetan
- La seguridad funciona (404 para no publicadas)

### Listado de Noticias (NewsIndexTest.php)

**Objetivo**: Verificar el funcionamiento del listado de noticias con filtros y búsqueda.

**Tests principales**:
- Renderizado del listado
- Visualización de noticias publicadas
- Filtros por programa, año académico y etiquetas
- Búsqueda por título, excerpt y contenido
- Combinación de filtros
- Paginación
- Estadísticas
- Ordenamiento
- Verificación de eager loading

**Qué se verifica**:
- Los filtros funcionan correctamente, incluyendo múltiples etiquetas
- La búsqueda funciona en múltiples campos
- La paginación mantiene los filtros
- No hay consultas N+1

### Detalle de Noticia (NewsShowTest.php)

**Objetivo**: Verificar que el detalle de noticia muestra toda la información y relaciones.

**Tests principales**:
- Renderizado del detalle
- Acceso a noticias no publicadas (404)
- Visualización de imagen destacada
- Visualización de etiquetas
- Noticias relacionadas (máximo 3, priorizadas por programa y etiquetas)
- Convocatorias relacionadas (máximo 3)
- Navegación
- Metadatos SEO
- Verificación de eager loading
- Estados vacíos

**Qué se verifica**:
- Las relaciones están eager loaded
- La priorización de noticias relacionadas funciona
- Los límites de elementos relacionados se respetan
- La seguridad funciona (404 para no publicadas)

### Formulario de Suscripción Newsletter (NewsletterSubscribeTest.php)

**Objetivo**: Comprobar en el navegador el formulario de suscripción, la validación (email vacío, inválido, duplicado, privacidad), la selección de programas, el envío exitoso con confirmación y el manejo de errores.

**Tests principales**:
- Formulario con email, programas activos (KA1, KA2, KA3), checkbox de privacidad y botón Suscribirse
- Validación: email vacío, formato inválido, email duplicado, aceptación de privacidad
- Selección de programas y comprobación de que la suscripción incluye `programs` en BD
- Envío exitoso: mensaje de éxito, envío de `NewsletterVerificationMail`, registro en `newsletter_subscriptions`
- Sin éxito cuando hay error de validación
- Sin errores de JavaScript

**Convenciones**:
- `fill('email', ...)`, `check('acceptPrivacy')`, `click(__('common.newsletter.subscribe'))`
- Para forzar validación servidor (email vacío/inválido): `script()` para quitar `required` o `type="email"`; no encadenar `script()` con `check`/`fill`
- `Mail::fake()` antes de `visit` en tests de envío exitoso; `wait(1)` tras submit antes de `assertSee` de errores

**Comando**:
```bash
./vendor/bin/pest tests/Browser/Public/NewsletterSubscribeTest.php
```

### Búsqueda Global (GlobalSearchTest.php)

**Objetivo**: Comprobar la búsqueda en tiempo real (`wire:model.live.debounce.300ms`), los resultados agrupados por tipo, los filtros avanzados (mostrar/ocultar, filtro por programa), el botón «Limpiar búsqueda» y la navegación a resultados.

**Tests principales**:
- Página: título, descripción, estado inicial «Comienza tu búsqueda»
- Búsqueda en tiempo real: programas, convocatorias, noticias, documentos (término «Movilidad»)
- Resultados vacíos
- Filtros avanzados: mostrar/ocultar panel, filtro por programa (`select('#program-filter', '...')`)
- Limpiar búsqueda: vuelta al estado inicial
- Navegación a detalle (p. ej. programa) con `assertPathBeginsWith('/programas/')`
- Sin errores de JavaScript (página inicial y tras búsqueda + filtros)

**Convenciones**:
- `fill('query', 'término')` — el input tiene `name="query"` en `global-search.blade.php`
- `wait(1)` tras `fill('query', ...)` para el debounce (300 ms) y la respuesta Livewire
- `select('#program-filter', 'Programa de Movilidad')` para el select de programa

**Comandos**:
```bash
./vendor/bin/pest tests/Browser/Public/GlobalSearchTest.php
# Depuración: --headed (ver navegador), --debug (pausar al fallar)
```

### Rendimiento (PerformanceTest.php)

**Objetivo**: Verificar que las páginas cargan en tiempos aceptables y con un número razonable de consultas.

**Tests principales**:
- Tiempos de carga (< 2 segundos)
- Número de consultas SQL (< 15-20 consultas según página)

**Qué se verifica**:
- Las páginas cargan rápidamente
- No hay consultas innecesarias
- El eager loading está funcionando correctamente

### Accesibilidad (AccessibilityTest.php)

**Objetivo**: Verificar aspectos básicos de accesibilidad.

**Tests principales**:
- Estructura semántica HTML
- Navegación por teclado

**Qué se verifica**:
- Los elementos HTML son semánticos
- Los enlaces y formularios son accesibles por teclado

## Cómo Interpretar los Resultados

### Tests que pasan

Si un test pasa, significa que:
- La funcionalidad está trabajando correctamente
- No hay errores de JavaScript
- No hay problemas de lazy loading detectados
- Los datos se muestran correctamente

### Tests que fallan

Si un test falla, puede indicar:

1. **Error de JavaScript**: La página tiene errores de JavaScript que impiden su funcionamiento
2. **Problema de lazy loading**: Hay consultas N+1 que causan problemas de rendimiento
3. **Datos incorrectos**: Los datos no se están mostrando como se espera
4. **Problema de filtros**: Los filtros no están funcionando correctamente
5. **Problema de paginación**: La paginación no mantiene los filtros o no funciona

### Screenshots

Cuando un test falla, Pest Browser guarda automáticamente un screenshot en `tests/Browser/Screenshots/` con el nombre del test. Esto ayuda a diagnosticar problemas visuales.

## Detección de Lazy Loading

Los tests incluyen verificaciones críticas para detectar problemas de lazy loading:

1. **Verificación de eager loading**: Se verifica que las relaciones están cargadas usando `with()` o `load()`
2. **Verificación de errores de JavaScript**: Si hay lazy loading, puede causar errores de JavaScript
3. **Conteo de consultas**: Los tests de rendimiento verifican que no hay demasiadas consultas

## Mejores Prácticas

1. **Usar helpers**: Siempre usar los helpers de datos de prueba para crear datos realistas
2. **Limpiar caché**: Limpiar la caché cuando sea necesario para evitar datos obsoletos
3. **Verificar eager loading**: Siempre verificar que las relaciones están eager loaded
4. **Estados vacíos**: Probar también casos donde no hay datos
5. **Límites**: Verificar que los límites de elementos (máximo N) se respetan

## Troubleshooting

### Tests que fallan intermitentemente

- Verificar que se está limpiando la caché correctamente
- Verificar que los datos de prueba son únicos (evitar conflictos de claves únicas)
- Verificar que no hay condiciones de carrera

### Tests que son lentos

- Verificar que el eager loading está funcionando
- Verificar que no hay consultas innecesarias
- Considerar optimizar los datos de prueba

### Problemas con caracteres especiales

- Usar texto ASCII simple en búsquedas y datos de prueba
- Evitar caracteres especiales en URLs

## Referencias

- [Documentación de Pest Browser](https://pestphp.com/docs/browser-testing)
- [Documentación de Playwright](https://playwright.dev)
- [Plan de Tests - Páginas públicas](docs/pasos/paso-3.11.2-plan.md)
- [Plan 3.11.4 - Formularios y validación en tiempo real](docs/pasos/paso-3.11.4-plan.md)

---

**Última actualización**: Enero 2026
