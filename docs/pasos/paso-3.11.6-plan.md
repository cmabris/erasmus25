# Plan de Trabajo - Paso 3.11.6: Tests de Rendimiento y Optimización

## Objetivo

Implementar tests de navegador completos para validar el rendimiento y las optimizaciones de la aplicación desde la perspectiva del usuario final. Estos tests verifican tiempos de carga aceptables, detectan problemas de consultas N+1, validan el uso de eager loading, comprueban el uso de caché y aseguran que no hay consultas duplicadas. Se utilizan Pest v4 con Playwright y herramientas de análisis de queries de Laravel.

---

## Estado Actual

### ✅ Ya Implementado

1. **Configuración de Browser Tests (Pasos 3.11.1–3.11.5)**:
   - Pest v4 con `pest-plugin-browser` y Playwright
   - Estructura `tests/Browser/` con `Public/`, `Auth/`, `Admin/`
   - Helpers: `createPublicTestData()`, `createHomeTestData()`, `createProgramsTestData()`, `createCallsTestData()`, `createNewsTestData()`, etc.
   - `RefreshDatabase` en tests de Browser

2. **Tests básicos de rendimiento**:
   - **Archivo**: `tests/Browser/Public/PerformanceTest.php`
   - Tests de tiempos de carga para Home, Programs Index, Calls Index, News Index (< 2000ms)
   - Tests de número de consultas básicos para las mismas páginas (< 15-20 queries)
   - Uso básico de `DB::enableQueryLog()` y `DB::getQueryLog()`

3. **Trait para contar queries**:
   - **Archivo**: `tests/Concerns/CountsQueries.php`
   - Métodos: `startQueryLog()`, `stopQueryLog()`, `getQueryCount()`, `getQueries()`
   - Métodos de análisis: `getDuplicateQueries()`, `getSlowQueries()`, `normalizeQuery()`
   - Métodos de aserción: `assertQueryCountLessThan()`, `assertNoDuplicateQueries()`, `assertNoSlowQueries()`, `assertTotalQueryTimeLessThan()`
   - Método de debug: `outputQueryDetails()`

4. **Tests de optimización de queries (Feature tests)**:
   - **Archivo**: `tests/Feature/Performance/QueryOptimizationTest.php`
   - Tests para componentes Livewire públicos y de administración
   - Límites de queries definidos: Home < 20, Admin listados < 30, Dashboard < 40, Detail < 25, Global search < 40
   - Detección de N+1 con `assertNoDuplicateQueries()`
   - Uso del trait `CountsQueries`

5. **Optimizaciones implementadas**:
   - Eager loading en componentes principales (Program, Call, NewsPost, Document)
   - Caché para configuraciones del sistema (Program, AcademicYear, DocumentCategory)
   - Caché para año académico actual (24h TTL)
   - Conversiones de imágenes optimizadas (WebP)
   - Lazy loading de imágenes en vistas públicas

### ⚠️ Pendiente de Implementar

1. **Tests de carga de páginas completos**:
   - Tests de tiempos de carga para páginas de detalle (Program Show, Call Show, News Show, Document Show, Event Show)
   - Tests de tiempos de carga para páginas de administración (Dashboard, CRUD listados, CRUD detalles)
   - Tests de tiempos de carga con diferentes volúmenes de datos (10, 50, 100 registros)
   - Tests de tiempos de carga con relaciones complejas (fases, resoluciones, etiquetas, documentos)

2. **Tests de consultas a base de datos exhaustivos**:
   - Tests de número máximo de consultas para todas las páginas públicas y de administración
   - Tests de detección de consultas duplicadas (N+1) en browser tests
   - Tests de validación de eager loading en páginas de detalle
   - Tests de uso de caché (verificar que las consultas se reducen en segunda carga)

3. **Tests de detección de lazy loading**:
   - Tests para verificar que todas las relaciones necesarias están cargadas antes del renderizado
   - Tests para detectar intentos de lazy loading en componentes críticos
   - Tests para validar eager loading en páginas de detalle con relaciones múltiples

4. **Tests de rendimiento en administración**:
   - Tests de tiempos de carga para Dashboard de administración
   - Tests de consultas para CRUD de administración (Programs, Calls, News, Documents, Events, Users)
   - Tests de rendimiento con diferentes roles y permisos

5. **Integración del trait CountsQueries en browser tests**:
   - Adaptar el trait para funcionar en el contexto de browser tests
   - Crear helpers específicos para browser tests que usen el trait

---

## Dependencias y Premisas

- **Browser tests vs Feature tests**: Los browser tests ejecutan el flujo completo (HTTP request → Livewire → Blade → respuesta HTML), mientras que los feature tests ejecutan solo el componente Livewire. Los browser tests son más realistas pero más lentos. Para rendimiento, ambos son útiles: browser tests para tiempos de carga reales, feature tests para análisis detallado de queries.

- **Query logging en browser tests**: `DB::enableQueryLog()` funciona en browser tests, pero las queries se registran durante toda la petición HTTP. Para obtener solo las queries de la página visitada, habrá que habilitar el log antes de `visit()` y deshabilitarlo después.

- **Trait CountsQueries**: El trait está diseñado para feature tests con `$this->startQueryLog()` y `$this->stopQueryLog()`. En browser tests, necesitaremos adaptarlo o crear helpers que funcionen sin `$this`.

- **Tiempos de carga**: Los tiempos de carga en browser tests incluyen renderizado HTML, carga de assets (CSS/JS), ejecución de JavaScript y renderizado del navegador. Los tiempos pueden variar según el entorno (CI vs local). Se establecerán umbrales conservadores (p. ej. < 2000ms para páginas simples, < 3000ms para páginas complejas).

- **Límites de queries**: Los límites actuales son:
  - Páginas públicas simples: < 15 queries
  - Páginas públicas con relaciones: < 20 queries
  - Páginas de detalle públicas: < 25 queries
  - Listados de administración: < 30 queries
  - Dashboard de administración: < 40 queries
  - Búsqueda global: < 40 queries

  Estos límites pueden ajustarse según mediciones reales, pero deben ser estrictos para detectar regresiones.

- **Detección de N+1**: El método `normalizeQuery()` del trait `CountsQueries` normaliza queries para detectar patrones duplicados. En browser tests, puede haber queries legítimamente duplicadas (p. ej. permisos, configuraciones), por lo que se usarán `allowedPatterns` en `assertNoDuplicateQueries()`.

- **Caché**: Para verificar el uso de caché, se puede:
  1. Cargar la página dos veces y comparar el número de queries (segunda carga debería tener menos queries si hay caché)
  2. Verificar que las queries de configuración (`settings`, `languages`, `programs` activos) no se ejecutan en cada carga si están cacheadas
  3. Limpiar caché antes del test y verificar que la primera carga tiene más queries que la segunda

- **Eager loading**: Para validar eager loading, se puede:
  1. Verificar que no hay queries individuales para cada relación (p. ej. si hay 10 programas, no debería haber 10 queries `SELECT * FROM programs WHERE id = ?`)
  2. Verificar que hay queries con JOIN o WHERE IN para cargar relaciones en batch
  3. Usar `assertNoDuplicateQueries()` con patrones específicos de relaciones

---

## Plan de Trabajo

### Fase 1: Helpers y Adaptación del Trait CountsQueries para Browser Tests

**Objetivo**: Crear helpers específicos para browser tests que permitan contar queries, detectar N+1 y validar eager loading de forma similar al trait `CountsQueries` pero adaptado al contexto de browser tests.

**Archivo**: `tests/Browser/Helpers.php` (ampliar)

#### 1.1. Funciones helper para query logging

- [x] **Función `startBrowserQueryLog(): void`**
  - Habilitar `DB::enableQueryLog()` y limpiar log anterior
  - Similar a `startQueryLog()` del trait pero sin `$this`

- [x] **Función `stopBrowserQueryLog(): array`**
  - Obtener queries con `DB::getQueryLog()`
  - Deshabilitar `DB::disableQueryLog()`
  - Devolver array de queries

- [x] **Función `getBrowserQueryCount(array $queries): int`**
  - Obtener número de queries del log actual

- [x] **Función `getBrowserQueries(array $queries): array`**
  - Obtener todas las queries del log actual

- [x] **Función `normalizeBrowserQuery(string $query): string`**
  - Reutilizar lógica de `normalizeQuery()` del trait `CountsQueries`
  - Normalizar query para comparación (reemplazar valores específicos con placeholders)

- [x] **Función `getBrowserDuplicateQueries(array $queries): array`**
  - Detectar queries duplicadas usando `normalizeBrowserQuery()`
  - Devolver array con patrón => count

- [x] **Función `assertBrowserQueryCountLessThan(array $queries, int $maxQueries, ?string $message = null): void`**
  - Aserción para verificar que el número de queries es menor que el máximo
  - Si falla, mostrar detalles de queries (usar `outputBrowserQueryDetails()`)

- [x] **Función `assertBrowserNoDuplicateQueries(array $queries, array $allowedPatterns = [], ?string $message = null): void`**
  - Aserción para detectar N+1
  - Filtrar patrones permitidos (p. ej. `activity_log`, `permissions`, `settings`)
  - Si hay duplicados, mostrar detalles

- [x] **Función `outputBrowserQueryDetails(array $queries): void`**
  - Mostrar detalles de queries para debugging
  - Incluir número total, tiempo total, queries individuales, queries duplicadas

- [x] **Función `getBrowserTotalQueryTime(array $queries): float`**
  - Obtener tiempo total de queries en milisegundos

- [x] **Función `getBrowserSlowQueries(array $queries, float $threshold = 100.0): array`**
  - Obtener queries que exceden un umbral de tiempo

- [x] **Función `assertBrowserNoSlowQueries(array $queries, float $threshold = 100.0, ?string $message = null): void`**
  - Aserción para verificar que no hay queries lentas

- [x] **Función `assertBrowserTotalQueryTimeLessThan(array $queries, float $maxTime, ?string $message = null): void`**
  - Aserción para verificar que el tiempo total de queries es menor que el máximo

#### 1.2. Funciones helper para validación de eager loading

- [x] **Función `assertEagerLoaded(string $relation, array $queries, ?string $message = null): void`**
  - Verificar que una relación está eager loaded
  - Buscar queries con JOIN o WHERE IN que incluyan la relación
  - Si no se encuentra, buscar queries individuales que indiquen lazy loading

- [x] **Función `assertNoLazyLoading(string $model, string $relation, array $queries, ?string $message = null): void`**
  - Verificar que no hay queries individuales para cada instancia de un modelo cargando una relación
  - Ejemplo: si hay 10 programas, no debería haber 10 queries `SELECT * FROM programs WHERE id = ?` para cargar `academicYear`

#### 1.3. Funciones helper para validación de caché

- [x] **Función `assertCacheUsed(string $key, array $queries, array $queryPatterns = [], ?string $message = null): void`**
  - Verificar que una clave de caché se usa (no hay queries para obtener ese dato)
  - Requiere conocer qué queries corresponden a datos cacheados (patrones SQL)
  - Si no se proporcionan patrones, verifica que la clave de caché existe

- [x] **Función `compareQueryCountsWithCache(array $queriesWithoutCache, array $queriesWithCache, ?string $message = null): void`**
  - Comparar número de queries con y sin caché
  - Verificar que con caché hay menos queries (o igual)

---

### Fase 2: Tests de Carga de Páginas Completos

**Objetivo**: Ampliar los tests de tiempos de carga para cubrir todas las páginas públicas y de administración, con diferentes volúmenes de datos y relaciones complejas.

**Archivo**: `tests/Browser/Public/PerformanceTest.php` (ampliar) y `tests/Browser/Admin/PerformanceTest.php` (nuevo)

#### 2.1. Tests de tiempos de carga para páginas de detalle públicas

**Archivo**: `tests/Browser/Public/PerformanceTest.php`

- [x] **Test: Carga de detalle de Programa con relaciones**
  - Crear programa con convocatorias, noticias y documentos relacionados
  - Medir tiempo de carga de `/programas/{slug}`
  - Verificar < 2000ms

- [x] **Test: Carga de detalle de Convocatoria con fases y resoluciones**
  - Crear convocatoria con múltiples fases y resoluciones publicadas
  - Medir tiempo de carga de `/convocatorias/{slug}`
  - Verificar < 2500ms (más complejo por fases y resoluciones)

- [x] **Test: Carga de detalle de Noticia con etiquetas e imágenes**
  - Crear noticia con múltiples etiquetas y relaciones
  - Medir tiempo de carga de `/noticias/{slug}`
  - Verificar < 2000ms

- [x] **Test: Carga de detalle de Documento**
  - Crear documento con archivo (Media Library)
  - Medir tiempo de carga de `/documentos/{slug}`
  - Verificar < 2000ms

- [x] **Test: Carga de detalle de Evento**
  - Crear evento con imagen y convocatoria relacionada
  - Medir tiempo de carga de `/eventos/{id}`
  - Verificar < 2000ms

#### 2.2. Tests de tiempos de carga con diferentes volúmenes de datos

**Archivo**: `tests/Browser/Public/PerformanceTest.php`

- [x] **Test: Carga de listado de Programas con 10, 50, 100 programas**
  - Crear diferentes volúmenes de programas activos
  - Medir tiempos de carga para cada volumen
  - Verificar que los tiempos crecen de forma aceptable (no exponencial)
  - Límites: 10 programas < 1500ms, 50 programas < 2000ms, 100 programas < 3000ms

- [x] **Test: Carga de listado de Convocatorias con 10, 50, 100 convocatorias**
  - Similar al anterior pero con convocatorias
  - Límites similares

- [x] **Test: Carga de listado de Noticias con 10, 50, 100 noticias**
  - Similar al anterior pero con noticias
  - Límites similares

#### 2.3. Tests de tiempos de carga para páginas de administración

**Archivo**: `tests/Browser/Admin/PerformanceTest.php` (nuevo)

- [x] **Test: Carga de Dashboard de administración**
  - Crear datos variados (programas, convocatorias, noticias, documentos)
  - Autenticar usuario admin
  - Medir tiempo de carga de `/admin`
  - Verificar < 3000ms

- [x] **Test: Carga de listado de Programas (admin)**
  - Crear 20 programas
  - Autenticar usuario admin
  - Medir tiempo de carga de `/admin/programas`
  - Verificar < 2500ms

- [x] **Test: Carga de listado de Convocatorias (admin)**
  - Crear 20 convocatorias con relaciones
  - Autenticar usuario admin
  - Medir tiempo de carga de `/admin/convocatorias`
  - Verificar < 2500ms

- [x] **Test: Carga de detalle de Convocatoria (admin) con fases y resoluciones**
  - Crear convocatoria con múltiples fases y resoluciones
  - Autenticar usuario admin
  - Medir tiempo de carga de `/admin/convocatorias/{id}`
  - Verificar < 3000ms

- [x] **Test: Carga de listado de Noticias (admin)**
  - Crear 20 noticias con relaciones
  - Autenticar usuario admin
  - Medir tiempo de carga de `/admin/noticias`
  - Verificar < 2500ms

- [x] **Test: Carga de listado de Usuarios (admin)**
  - Crear 20 usuarios
  - Autenticar usuario super-admin
  - Medir tiempo de carga de `/admin/usuarios`
  - Verificar < 2500ms

---

### Fase 3: Tests de Consultas a Base de Datos Exhaustivos

**Objetivo**: Validar que todas las páginas ejecutan un número óptimo de consultas, detectar N+1 y verificar eager loading.

**Archivo**: `tests/Browser/Public/QueryPerformanceTest.php` (nuevo) y `tests/Browser/Admin/QueryPerformanceTest.php` (nuevo)

#### 3.1. Tests de número máximo de consultas para páginas públicas

**Archivo**: `tests/Browser/Public/QueryPerformanceTest.php`

- [x] **Test: Home ejecuta menos de 20 consultas**
  - Usar `startBrowserQueryLog()` antes de `visit('/')`
  - Usar `stopBrowserQueryLog()` después
  - `assertBrowserQueryCountLessThan(20)`

- [x] **Test: Listado de Programas ejecuta menos de 15 consultas**
  - Crear 10 programas activos
  - Medir queries en `/programas`
  - `assertBrowserQueryCountLessThan(15)`

- [x] **Test: Detalle de Programa ejecuta menos de 25 consultas**
  - Crear programa con relaciones (convocatorias, noticias, documentos)
  - Medir queries en `/programas/{slug}`
  - `assertBrowserQueryCountLessThan(25)`

- [x] **Test: Listado de Convocatorias ejecuta menos de 20 consultas**
  - Crear 15 convocatorias con relaciones
  - Medir queries en `/convocatorias`
  - `assertBrowserQueryCountLessThan(20)`

- [x] **Test: Detalle de Convocatoria ejecuta menos de 25 consultas**
  - Crear convocatoria con fases y resoluciones
  - Medir queries en `/convocatorias/{slug}`
  - `assertBrowserQueryCountLessThan(25)`

- [x] **Test: Listado de Noticias ejecuta menos de 20 consultas**
  - Crear 15 noticias con relaciones
  - Medir queries en `/noticias`
  - `assertBrowserQueryCountLessThan(20)`

- [x] **Test: Detalle de Noticia ejecuta menos de 25 consultas**
  - Crear noticia con etiquetas y relaciones
  - Medir queries en `/noticias/{slug}`
  - `assertBrowserQueryCountLessThan(25)`

- [x] **Test: Búsqueda Global ejecuta menos de 40 consultas**
  - Crear datos variados (programas, convocatorias, noticias, documentos)
  - Realizar búsqueda en `/buscar?q=Movilidad`
  - Medir queries
  - `assertBrowserQueryCountLessThan(40)`

#### 3.2. Tests de detección de N+1 en páginas públicas

**Archivo**: `tests/Browser/Public/QueryPerformanceTest.php`

- [x] **Test: No hay N+1 al cargar programas con academicYear**
  - Crear 10 programas con `academicYear` relacionado
  - Medir queries en `/programas`
  - `assertBrowserNoDuplicateQueries(['activity_log', 'permissions'])` (permitir duplicados legítimos)
  - Verificar que no hay 10 queries individuales para `academicYear`

- [x] **Test: No hay N+1 al cargar convocatorias con program y academicYear**
  - Crear 15 convocatorias con `program` y `academicYear` relacionados
  - Medir queries en `/convocatorias`
  - `assertBrowserNoDuplicateQueries(['activity_log', 'permissions'])`
  - Verificar eager loading de `program` y `academicYear`

- [x] **Test: No hay N+1 al cargar noticias con program, author y tags**
  - Crear 15 noticias con `program`, `author` y `tags` relacionados
  - Medir queries en `/noticias`
  - `assertBrowserNoDuplicateQueries(['activity_log', 'permissions'])`
  - Verificar eager loading de relaciones

- [x] **Test: No hay N+1 en detalle de Convocatoria con fases y resoluciones**
  - Crear convocatoria con fases y resoluciones
  - Medir queries en `/convocatorias/{slug}`
  - `assertBrowserNoDuplicateQueries(['activity_log', 'permissions'])`
  - Verificar que fases y resoluciones se cargan con eager loading

- [x] **Test: No hay N+1 en detalle de Noticia con etiquetas**
  - Crear noticia con etiquetas
  - Medir queries en `/noticias/{slug}`
  - `assertBrowserNoDuplicateQueries(['activity_log', 'permissions'])`
  - Verificar que las etiquetas se cargan con eager loading

#### 3.3. Tests de número máximo de consultas para páginas de administración

**Archivo**: `tests/Browser/Admin/QueryPerformanceTest.php`

- [x] **Test: Dashboard ejecuta menos de 40 consultas**
  - Crear datos variados
  - Autenticar usuario admin
  - Medir queries en `/admin`
  - `assertBrowserQueryCountLessThan(40)`

- [x] **Test: Listado de Programas (admin) ejecuta menos de 30 consultas**
  - Crear 20 programas
  - Autenticar usuario admin
  - Medir queries en `/admin/programas`
  - `assertBrowserQueryCountLessThan(30)`

- [x] **Test: Listado de Convocatorias (admin) ejecuta menos de 30 consultas**
  - Crear 20 convocatorias
  - Autenticar usuario admin
  - Medir queries en `/admin/convocatorias`
  - `assertBrowserQueryCountLessThan(30)`

- [x] **Test: Detalle de Convocatoria (admin) ejecuta menos de 35 consultas**
  - Crear convocatoria con fases y resoluciones
  - Autenticar usuario admin
  - Medir queries en `/admin/convocatorias/{id}`
  - `assertBrowserQueryCountLessThan(35)`

- [x] **Test: Listado de Noticias (admin) ejecuta menos de 30 consultas**
  - Crear 20 noticias
  - Autenticar usuario admin
  - Medir queries en `/admin/noticias`
  - `assertBrowserQueryCountLessThan(30)`

- [x] **Test: Listado de Usuarios (admin) ejecuta menos de 30 consultas**
  - Crear 20 usuarios
  - Autenticar usuario super-admin
  - Medir queries en `/admin/usuarios`
  - `assertBrowserQueryCountLessThan(30)`

#### 3.4. Tests de detección de N+1 en páginas de administración

**Archivo**: `tests/Browser/Admin/QueryPerformanceTest.php`

- [x] **Test: No hay N+1 en listado de Convocatorias (admin) con relaciones**
  - Crear 20 convocatorias con `program`, `academicYear`, `creator`, `updater`
  - Autenticar usuario admin
  - Medir queries en `/admin/convocatorias`
  - `assertBrowserNoDuplicateQueries(['activity_log', 'permissions'])`
  - Verificar eager loading de relaciones

- [x] **Test: No hay N+1 en listado de Noticias (admin) con relaciones**
  - Crear 20 noticias con `program`, `author`, `tags`
  - Autenticar usuario admin
  - Medir queries en `/admin/noticias`
  - `assertBrowserNoDuplicateQueries(['activity_log', 'permissions'])`
  - Verificar eager loading

- [x] **Test: No hay N+1 en listado de Usuarios (admin) con roles**
  - Crear 20 usuarios con roles asignados
  - Autenticar usuario super-admin
  - Medir queries en `/admin/usuarios`
  - `assertBrowserNoDuplicateQueries(['activity_log', 'permissions'])`
  - Verificar que los roles se cargan con eager loading

---

### Fase 4: Tests de Detección de Lazy Loading

**Objetivo**: Validar que todas las relaciones necesarias están cargadas antes del renderizado y detectar intentos de lazy loading en componentes críticos.

**Archivo**: `tests/Browser/Public/LazyLoadingTest.php` (nuevo) y `tests/Browser/Admin/LazyLoadingTest.php` (nuevo)

#### 4.1. Tests de validación de eager loading en páginas públicas

**Archivo**: `tests/Browser/Public/LazyLoadingTest.php`

- [x] **Test: Program está eager loaded en listado de Programas**
  - Crear 10 programas con `academicYear`
  - Medir queries en `/programas`
  - Usar `assertEagerLoaded('academicYear', $queries)` para verificar que no hay queries individuales para `academicYear`

- [x] **Test: Program y AcademicYear están eager loaded en listado de Convocatorias**
  - Crear 15 convocatorias con `program` y `academicYear`
  - Medir queries en `/convocatorias`
  - Verificar eager loading de ambas relaciones usando `assertNoLazyLoading()`

- [x] **Test: Program, Author y Tags están eager loaded en listado de Noticias**
  - Crear 15 noticias con `program`, `author` y `tags`
  - Medir queries en `/noticias`
  - Verificar eager loading de todas las relaciones usando `assertNoLazyLoading()`

- [x] **Test: Fases y Resoluciones están eager loaded en detalle de Convocatoria**
  - Crear convocatoria con fases y resoluciones
  - Medir queries en `/convocatorias/{slug}`
  - Verificar que `phases` y `resolutions` están eager loaded usando `assertNoLazyLoading()`

- [x] **Test: Etiquetas están eager loaded en detalle de Noticia**
  - Crear noticia con etiquetas
  - Medir queries en `/noticias/{slug}`
  - Verificar que `tags` está eager loaded usando `assertEagerLoaded()`

- [x] **Test: Relaciones están eager loaded en detalle de Programa**
  - Crear programa con convocatorias, noticias y documentos relacionados
  - Medir queries en `/programas/{slug}`
  - Verificar eager loading de `calls`, `newsPosts`, `documents` usando `assertNoLazyLoading()`

#### 4.2. Tests de validación de eager loading en páginas de administración

**Archivo**: `tests/Browser/Admin/LazyLoadingTest.php`

- [x] **Test: Relaciones están eager loaded en listado de Convocatorias (admin)**
  - Crear 20 convocatorias con `program`, `academicYear`, `creator`, `updater`
  - Autenticar usuario admin
  - Medir queries en `/admin/convocatorias`
  - Verificar eager loading de todas las relaciones usando `assertNoLazyLoading()`

- [x] **Test: Relaciones están eager loaded en listado de Noticias (admin)**
  - Crear 20 noticias con `program`, `author`, `tags`
  - Autenticar usuario admin
  - Medir queries en `/admin/noticias`
  - Verificar eager loading usando `assertNoLazyLoading()`

- [x] **Test: Roles están eager loaded en listado de Usuarios (admin)**
  - Crear 20 usuarios con roles asignados
  - Autenticar usuario super-admin
  - Medir queries en `/admin/usuarios`
  - Verificar que los roles se cargan con eager loading usando `assertNoLazyLoading()`

- [x] **Test: Fases y Resoluciones están eager loaded en detalle de Convocatoria (admin)**
  - Crear convocatoria con múltiples fases y resoluciones
  - Autenticar usuario admin
  - Medir queries en `/admin/convocatorias/{id}`
  - Verificar eager loading usando `assertNoLazyLoading()`

---

### Fase 5: Tests de Uso de Caché

**Objetivo**: Verificar que el sistema de caché funciona correctamente y reduce el número de consultas en cargas posteriores.

**Archivo**: `tests/Browser/Public/CachePerformanceTest.php` (nuevo) y `tests/Browser/Admin/CachePerformanceTest.php` (nuevo)

#### 5.1. Tests de caché en páginas públicas

**Archivo**: `tests/Browser/Public/CachePerformanceTest.php`

- [x] **Test: Caché reduce consultas en segunda carga de Home**
  - Limpiar caché antes del test
  - Cargar `/` primera vez y medir queries
  - Cargar `/` segunda vez y medir queries
  - Verificar que la segunda carga tiene menos queries (usar `compareQueryCountsWithCache()`)

- [x] **Test: Caché de año académico actual funciona**
  - Limpiar caché
  - Crear año académico con `is_current = true`
  - Cargar página que use año actual (p. ej. Home) dos veces
  - Verificar que la segunda carga tiene menos queries (caché funciona)

- [x] **Test: Caché de programas activos funciona**
  - Limpiar caché
  - Crear programas activos
  - Cargar página que use programas activos (p. ej. Home) dos veces
  - Verificar que la segunda carga tiene menos queries relacionadas con programas

- [x] **Test: Invalidación de caché al actualizar contenido**
  - Cargar Home y verificar uso de caché
  - Actualizar un programa (cambiar `is_active`)
  - Cargar Home de nuevo
  - Verificar que se ejecutan queries para obtener programas actualizados (caché invalidado)

#### 5.2. Tests de caché en páginas de administración

**Archivo**: `tests/Browser/Admin/CachePerformanceTest.php`

- [x] **Test: Caché reduce consultas en segunda carga de Dashboard**
  - Limpiar caché
  - Autenticar usuario admin
  - Cargar `/admin` primera vez y medir queries
  - Cargar `/admin` segunda vez y medir queries
  - Verificar que la segunda carga tiene menos queries usando `compareQueryCountsWithCache()`

- [x] **Test: Caché de configuraciones funciona en administración**
  - Limpiar caché
  - Autenticar usuario admin
  - Cargar página de administración que use configuraciones (p. ej. Dashboard) dos veces
  - Verificar que la segunda carga tiene menos queries (caché de configuraciones funciona)

---

### Fase 6: Documentación y Verificación Final

#### 6.1. Documentación

- [ ] Crear o actualizar `docs/browser-testing-performance.md` con:
  - Resumen de los archivos de tests: `PerformanceTest.php`, `QueryPerformanceTest.php`, `LazyLoadingTest.php`, `CachePerformanceTest.php`
  - Descripción de los helpers: `startBrowserQueryLog()`, `assertBrowserQueryCountLessThan()`, `assertBrowserNoDuplicateQueries()`, etc.
  - Límites de queries establecidos para cada tipo de página
  - Límites de tiempos de carga establecidos
  - Convenciones: cómo medir queries en browser tests, cómo detectar N+1, cómo validar caché
  - Comandos: `./vendor/bin/pest tests/Browser/Public/PerformanceTest.php`, `./vendor/bin/pest tests/Browser/Public/QueryPerformanceTest.php`, etc.
  - Troubleshooting: qué hacer si un test falla, cómo interpretar los resultados

#### 6.2. Actualizar `docs/planificacion_pasos.md`

- [ ] En el paso 3.11.6, marcar como completados los ítems:
  - [ ] Test de Carga de Páginas
  - [ ] Test de Consultas a Base de Datos
  - [ ] Test de Lazy Loading Detection

#### 6.3. Verificación final

- [ ] Ejecutar todos los tests de rendimiento:
  - `./vendor/bin/pest tests/Browser/Public/PerformanceTest.php`
  - `./vendor/bin/pest tests/Browser/Public/QueryPerformanceTest.php`
  - `./vendor/bin/pest tests/Browser/Public/LazyLoadingTest.php`
  - `./vendor/bin/pest tests/Browser/Public/CachePerformanceTest.php`
  - `./vendor/bin/pest tests/Browser/Admin/PerformanceTest.php`
  - `./vendor/bin/pest tests/Browser/Admin/QueryPerformanceTest.php`
  - `./vendor/bin/pest tests/Browser/Admin/LazyLoadingTest.php`
  - `./vendor/bin/pest tests/Browser/Admin/CachePerformanceTest.php`
- [ ] Comprobar que todos pasan
- [ ] Revisar que no queden `skip()` o `todo()` sin justificar
- [ ] Opcional: ejecutar `./vendor/bin/pest tests/Browser` y comprobar que la suite completa sigue pasando

---

## Estructura de Archivos Final

```
tests/
├── Browser/
│   ├── Helpers.php                          # + helpers para query logging y análisis
│   ├── Public/
│   │   ├── PerformanceTest.php             # AMPLIADO – tiempos de carga completos
│   │   ├── QueryPerformanceTest.php        # NUEVO – tests de consultas exhaustivos
│   │   ├── LazyLoadingTest.php             # NUEVO – tests de detección de lazy loading
│   │   ├── CachePerformanceTest.php        # NUEVO – tests de uso de caché
│   │   ├── HomeTest.php
│   │   ├── ProgramsIndexTest.php
│   │   ├── ProgramsShowTest.php
│   │   ├── CallsIndexTest.php
│   │   ├── CallsShowTest.php
│   │   ├── NewsIndexTest.php
│   │   ├── NewsShowTest.php
│   │   └── ...
│   └── Admin/
│       ├── PerformanceTest.php             # NUEVO – tiempos de carga admin
│       ├── QueryPerformanceTest.php        # NUEVO – tests de consultas admin
│       ├── LazyLoadingTest.php             # NUEVO – tests de lazy loading admin
│       ├── CachePerformanceTest.php        # NUEVO – tests de caché admin
│       └── ...
├── Concerns/
│   └── CountsQueries.php                   # Ya existe – trait para feature tests
└── Feature/
    └── Performance/
        └── QueryOptimizationTest.php        # Ya existe – tests de optimización
```

---

## Criterios de Éxito

1. **Tests de carga de páginas**
   - Todas las páginas públicas cargan en < 2000-3000ms según complejidad
   - Todas las páginas de administración cargan en < 2500-3000ms
   - Los tiempos crecen de forma aceptable con más datos (no exponencial)

2. **Tests de consultas a base de datos**
   - Todas las páginas ejecutan menos consultas que los límites establecidos
   - No se detectan problemas de N+1 en ninguna página
   - Las relaciones están eager loaded correctamente

3. **Tests de detección de lazy loading**
   - Todas las relaciones necesarias están cargadas antes del renderizado
   - No se detectan intentos de lazy loading en componentes críticos
   - El eager loading funciona correctamente en páginas de detalle con relaciones múltiples

4. **Tests de uso de caché**
   - El sistema de caché reduce el número de consultas en cargas posteriores
   - La invalidación de caché funciona correctamente al actualizar contenido
   - Las configuraciones y datos frecuentes están cacheados

5. **Helpers y documentación**
   - Helpers reutilizables para query logging y análisis en browser tests
   - Documentación completa de límites, convenciones y troubleshooting
   - `planificacion_pasos.md` actualizado con el estado del paso 3.11.6

---

## Notas Importantes

1. **Browser tests vs Feature tests**: Los browser tests son más lentos pero más realistas. Para análisis detallado de queries, los feature tests con el trait `CountsQueries` son más rápidos. Ambos son complementarios: browser tests para tiempos de carga reales, feature tests para análisis detallado.

2. **Límites de queries**: Los límites establecidos son conservadores y pueden ajustarse según mediciones reales. Si un test falla por estar cerca del límite pero no hay N+1, considerar aumentar el límite. Si hay N+1, corregir el código antes de ajustar el límite.

3. **Queries legítimamente duplicadas**: Algunas queries pueden ejecutarse múltiples veces de forma legítima (p. ej. permisos, configuraciones, activity_log). Usar `allowedPatterns` en `assertBrowserNoDuplicateQueries()` para permitirlas.

4. **Tiempos de carga variables**: Los tiempos de carga pueden variar según el entorno (CI vs local, carga del sistema, red). Establecer umbrales conservadores y considerar ejecutar tests múltiples veces para obtener promedios.

5. **Caché en tests**: Los tests de caché deben limpiar el caché antes de comenzar para tener un estado conocido. Usar `Cache::flush()` o `Cache::forget()` según corresponda.

6. **Eager loading**: Para validar eager loading, buscar queries con JOIN o WHERE IN que carguen relaciones en batch. Si hay queries individuales para cada instancia, es probable que haya lazy loading.

7. **Debugging**: Si un test falla, usar `outputBrowserQueryDetails()` para ver todas las queries ejecutadas. Esto ayuda a identificar qué queries están causando el problema.

---

## Próximos Pasos

Tras completar el paso 3.11.6:

- **Paso 3.11.7**: Tests de responsive y accesibilidad.
- **Paso 3.11.8**: Integración con CI/CD y documentación final.

---

**Fecha de Creación**: Enero 2026  
**Fecha de Finalización**: Enero 2026  
**Estado**: ✅ COMPLETADO

---

## Resumen de Implementación

### Tests Implementados

- **Total**: 66 tests (213 assertions)
- **Tests públicos**: 45 tests
- **Tests de administración**: 21 tests

### Archivos Creados/Modificados

1. **`tests/Browser/Helpers.php`** - Ampliado con helpers de rendimiento
2. **`tests/Browser/Public/PerformanceTest.php`** - Ampliado con tests de tiempos de carga
3. **`tests/Browser/Public/QueryPerformanceTest.php`** - Nuevo (13 tests)
4. **`tests/Browser/Public/LazyLoadingTest.php`** - Nuevo (6 tests)
5. **`tests/Browser/Public/CachePerformanceTest.php`** - Nuevo (4 tests)
6. **`tests/Browser/Admin/PerformanceTest.php`** - Nuevo (6 tests)
7. **`tests/Browser/Admin/QueryPerformanceTest.php`** - Nuevo (9 tests)
8. **`tests/Browser/Admin/LazyLoadingTest.php`** - Nuevo (4 tests)
9. **`tests/Browser/Admin/CachePerformanceTest.php`** - Nuevo (2 tests)
10. **`docs/browser-testing-performance.md`** - Nuevo (documentación completa)

### Fases Completadas

- ✅ **Fase 1**: Helpers de rendimiento (query logging, análisis, assertions)
- ✅ **Fase 2**: Tests de tiempos de carga (28 tests)
- ✅ **Fase 3**: Tests de consultas y N+1 (22 tests)
- ✅ **Fase 4**: Tests de eager loading (10 tests)
- ✅ **Fase 5**: Tests de caché (6 tests)
- ✅ **Fase 6**: Documentación y verificación final

### Correcciones Aplicadas

1. **Test de Program con academicYear**: Corregido porque `Program` no tiene relación directa con `AcademicYear`
2. **Permisos en tests de admin**: Cambiados a `SUPER_ADMIN` porque los wildcards están deshabilitados
3. **Roles en tests**: Añadido `ensureRolesExist()` antes de asignar roles

### Documentación

- ✅ Documentación completa creada en `docs/browser-testing-performance.md`
- ✅ `docs/planificacion_pasos.md` actualizado con estado completado
