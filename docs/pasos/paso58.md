# Paso 58: Tests de Rendimiento y Optimización (Paso 3.11.6)

**Fecha de implementación**: 2026-01-27  
**Objetivo**: Implementar tests de rendimiento y optimización usando browser tests para validar tiempos de carga, detectar problemas N+1, validar eager loading y verificar el uso efectivo de caché.

---

## Índice de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Prompts y Resultados](#prompts-y-resultados)
3. [Archivos Creados/Modificados](#archivos-creadosmodificados)
4. [Tests Implementados](#tests-implementados)
5. [Métricas y Estadísticas](#métricas-y-estadísticas)

---

## Resumen Ejecutivo

Este paso implementó el **Paso 3.11.6: Tests de Rendimiento y Optimización** del plan de desarrollo, abarcando:

- **Helpers de rendimiento** para query logging y análisis en browser tests
- **Tests de tiempos de carga** para páginas públicas y de administración
- **Tests de consultas a base de datos** con detección de N+1
- **Tests de validación de eager loading** para relaciones críticas
- **Tests de uso de caché** para verificar reducción de consultas e invalidación
- **Documentación completa** de límites, convenciones y troubleshooting

### Fases Completadas:

| Fase | Descripción | Estado |
|------|-------------|--------|
| Fase 1 | Helpers de rendimiento (query logging, análisis, assertions) | ✅ Completado |
| Fase 2 | Tests de tiempos de carga (28 tests) | ✅ Completado |
| Fase 3 | Tests de consultas y N+1 (22 tests) | ✅ Completado |
| Fase 4 | Tests de eager loading (10 tests) | ✅ Completado |
| Fase 5 | Tests de caché (6 tests) | ✅ Completado |
| Fase 6 | Documentación y verificación final | ✅ Completado |

---

## Prompts y Resultados

### Prompt 1: Desarrollo del Plan Detallado

**Usuario:**
> En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que actúa de índice y otro llamado @docs/planificacion_pasos.md que contiene las tareas a desarrollar en orden. Estamos en situación de comenzar con el desarrollo del paso 3.11.6. que corresponde a los Tests de Rendimiento y Optimización. Desarrolla primero el plan detallado de todo el proceso a seguir y guardalo en el archivo cuyo nombre se indica en la propia documentación.

**Resultado:**
- Se revisó la documentación existente del proyecto
- Se analizaron los componentes Livewire y modelos existentes
- Se revisó el trait `CountsQueries` existente para feature tests como referencia
- Se creó el documento de planificación: `docs/pasos/paso-3.11.6-plan.md`
- El plan incluye 6 fases:
  1. **Fase 1**: Helpers de rendimiento (query logging, análisis, assertions)
  2. **Fase 2**: Tests de tiempos de carga para páginas públicas y admin
  3. **Fase 3**: Tests de consultas y detección de N+1
  4. **Fase 4**: Tests de validación de eager loading
  5. **Fase 5**: Tests de uso de caché
  6. **Fase 6**: Documentación y verificación final
- Se establecieron límites de consultas y tiempos de carga para cada tipo de página
- Se definieron convenciones y patrones para tests de rendimiento

---

### Prompt 2: Implementación de Fase 1

**Usuario:**
> El plan desarrollado me parece adecuado. Podemos comenzar a implementar la Fase 1

**Resultado:**
- Se amplió `tests/Browser/Helpers.php` con helpers de rendimiento:
  - **Query Logging**: `startBrowserQueryLog()`, `stopBrowserQueryLog()`, `getBrowserQueryCount()`, `getBrowserQueries()`, `getBrowserTotalQueryTime()`, `getBrowserSlowQueries()`
  - **Análisis de Consultas**: `normalizeBrowserQuery()`, `getBrowserDuplicateQueries()`
  - **Assertions**: `assertBrowserQueryCountLessThan()`, `assertBrowserNoDuplicateQueries()`, `assertBrowserNoSlowQueries()`, `assertBrowserTotalQueryTimeLessThan()`
  - **Validación de Eager Loading**: `assertEagerLoaded()`, `assertNoLazyLoading()`
  - **Validación de Caché**: `compareQueryCountsWithCache()`, `assertCacheUsed()`
  - **Debugging**: `outputBrowserQueryDetails()`
- Los helpers están adaptados para uso global en Pest browser tests (aceptan arrays de queries como parámetros)
- Se formateó el código con Laravel Pint
- Se verificó la sintaxis con `php -l`

---

### Prompt 3: Implementación de Fase 2

**Usuario:**
> Sigamos con la Fase 2

**Resultado:**
- Se amplió `tests/Browser/Public/PerformanceTest.php` con tests de tiempos de carga:
  - Tests para páginas de detalle: `programas.show`, `convocatorias.show`, `noticias.show`, `documentos.show`, `eventos.show`
  - Tests para páginas index: `programas.index`, `convocatorias.index`, `noticias.index`
  - Tests con diferentes volúmenes de datos (10, 50, 100 registros) para verificar escalabilidad
- Se creó `tests/Browser/Admin/PerformanceTest.php` con tests de tiempos de carga para:
  - `admin.dashboard`
  - `admin.programs.index`
  - `admin.calls.index`
  - `admin.calls.show`
  - `admin.news.index`
  - `admin.users.index`
- Se establecieron umbrales de tiempo:
  - Páginas públicas: < 2000-3000ms según complejidad
  - Páginas de administración: < 2500-3000ms
  - Tests con 100 registros: < 5000ms
- Se formateó el código con Laravel Pint
- Se verificó la sintaxis

---

### Prompt 4: Implementación de Fase 3

**Usuario:**
> Adelante con la Fase 3

**Resultado:**
- Se creó `tests/Browser/Public/QueryPerformanceTest.php` con tests de consultas:
  - Tests de límite de consultas para: `home`, `programas.index`, `programas.show`, `convocatorias.index`, `convocatorias.show`, `noticias.index`, `noticias.show`, `search`
  - Tests de detección de N+1 para:
    - Programas index (corregido: Program no tiene relación directa con AcademicYear)
    - Convocatorias con program y academicYear
    - Noticias con program, author y tags
    - Call show con phases y resolutions
    - News show con tags
- Se creó `tests/Browser/Admin/QueryPerformanceTest.php` con tests de consultas:
  - Tests de límite de consultas para: `admin.dashboard`, `admin.programs.index`, `admin.calls.index`, `admin.calls.show`, `admin.news.index`, `admin.users.index`
  - Tests de detección de N+1 para:
    - Admin calls index con relaciones (program, academicYear, creator, updater)
    - Admin news index con relaciones (program, author, tags)
    - Admin users index con roles
- Se establecieron límites de consultas:
  - Páginas públicas: 15-25 consultas según complejidad
  - Páginas de administración: 30-40 consultas
  - Búsqueda global: < 40 consultas
- Se formateó el código con Laravel Pint
- Se verificó la sintaxis

---

### Prompt 5: Implementación de Fase 4

**Usuario:**
> Pasemos a la Fase 4

**Resultado:**
- Se creó `tests/Browser/Public/LazyLoadingTest.php` con tests de validación de eager loading:
  - Tests para verificar eager loading en:
    - Programs index (corregido: no tiene relaciones directas)
    - Calls index con program y academicYear
    - News index con program, author y tags
    - Call show con phases y resolutions
    - News show con tags
    - Program show con relaciones (relatedCalls, relatedNews)
- Se creó `tests/Browser/Admin/LazyLoadingTest.php` con tests de validación de eager loading:
  - Tests para verificar eager loading en:
    - Admin calls index con relaciones (program, academicYear, creator, updater)
    - Admin news index con relaciones (program, author, tags)
    - Admin users index con roles
    - Admin call show con phases y resolutions
- Los tests verifican que las relaciones están cargadas usando JOIN o WHERE IN (batch loading)
- Se formateó el código con Laravel Pint
- Se verificó la sintaxis

---

### Prompt 6: Implementación de Fase 5

**Usuario:**
> Sigamos con la Fase 5

**Resultado:**
- Se creó `tests/Browser/Public/CachePerformanceTest.php` con tests de caché:
  - Test de reducción de consultas en segunda carga de home page
  - Test de caché para `AcademicYear::getCurrent()`
  - Test de caché para `Program::getCachedActive()`
  - Test de invalidación de caché cuando se actualiza el estado `is_active` de un programa
- Se creó `tests/Browser/Admin/CachePerformanceTest.php` con tests de caché:
  - Test de reducción de consultas en segunda carga de admin dashboard
  - Test de caché para configuraciones en administración
- Los tests verifican que:
  - El caché reduce el número de consultas en cargas posteriores
  - La invalidación funciona correctamente cuando se actualiza contenido
  - Las configuraciones y datos frecuentes están cacheados
- Se formateó el código con Laravel Pint
- Se verificó la sintaxis
- Se actualizó `docs/pasos/paso-3.11.6-plan.md` marcando la Fase 5 como completada

---

### Prompt 7: Ejecución de Tests

**Usuario:**
> Has ejecutado todos los tests que se han creado ahora?

**Resultado:**
- Se ejecutaron todos los tests de rendimiento implementados
- **Correcciones aplicadas durante la ejecución**:
  1. **Test de Program con academicYear**: Corregido porque `Program` no tiene relación directa con `AcademicYear` (la relación es indirecta a través de `Call`, `NewsPost`, `Document`)
  2. **Permisos en tests de admin**: Cambiados todos los tests de admin para usar `SUPER_ADMIN` en lugar de `ADMIN` porque los wildcards de permisos están deshabilitados (`enable_wildcard_permission => false`)
  3. **Roles en tests**: Añadido `ensureRolesExist()` antes de asignar roles a usuarios en tests que requieren roles
- **Resultado final**: 66 tests pasando (213 assertions)
  - Tests públicos: 45 tests
  - Tests de administración: 21 tests
- Todos los tests verificados y funcionando correctamente

---

### Prompt 8: Implementación de Fase 6

**Usuario:**
> Sí, continua con la Fase 6

**Resultado:**
- Se creó `docs/browser-testing-performance.md` con documentación completa:
  - Descripción de la estructura de tests
  - Documentación detallada de todos los helpers de rendimiento
  - Límites establecidos (tiempos y consultas)
  - Convenciones y ejemplos de código para cada tipo de test
  - Comandos útiles para ejecutar tests
  - Guía completa de troubleshooting con problemas comunes y soluciones
  - Estadísticas de implementación
  - Mejores prácticas
- Se actualizó `docs/planificacion_pasos.md`:
  - Paso 3.11.6 marcado como completado (✅)
  - Añadida referencia a la nueva documentación
- Se actualizó `docs/pasos/paso-3.11.6-plan.md`:
  - Añadido resumen de implementación con estadísticas
  - Lista de archivos creados/modificados
  - Fases completadas
  - Correcciones aplicadas
  - Estado cambiado a "✅ COMPLETADO"
- Se verificó que no hay `skip()` o `todo()` sin justificar en los archivos de tests
- Se formateó la documentación con Laravel Pint

---

## Archivos Creados/Modificados

### Archivos Nuevos Creados

1. **`tests/Browser/Public/QueryPerformanceTest.php`** (13 tests)
   - Tests de límite de consultas para páginas públicas
   - Tests de detección de N+1

2. **`tests/Browser/Public/LazyLoadingTest.php`** (6 tests)
   - Tests de validación de eager loading en páginas públicas

3. **`tests/Browser/Public/CachePerformanceTest.php`** (4 tests)
   - Tests de uso de caché en páginas públicas

4. **`tests/Browser/Admin/PerformanceTest.php`** (6 tests)
   - Tests de tiempos de carga para páginas de administración

5. **`tests/Browser/Admin/QueryPerformanceTest.php`** (9 tests)
   - Tests de límite de consultas y detección de N+1 en admin

6. **`tests/Browser/Admin/LazyLoadingTest.php`** (4 tests)
   - Tests de validación de eager loading en admin

7. **`tests/Browser/Admin/CachePerformanceTest.php`** (2 tests)
   - Tests de uso de caché en administración

8. **`docs/browser-testing-performance.md`**
   - Documentación completa de tests de rendimiento

### Archivos Modificados

1. **`tests/Browser/Helpers.php`**
   - Ampliado con helpers de rendimiento:
     - Query logging (6 funciones)
     - Análisis de consultas (2 funciones)
     - Assertions (4 funciones)
     - Validación de eager loading (2 funciones)
     - Validación de caché (2 funciones)
     - Debugging (1 función)

2. **`tests/Browser/Public/PerformanceTest.php`**
   - Ampliado con tests de tiempos de carga:
     - Tests para páginas de detalle (5 tests)
     - Tests para páginas index (3 tests)
     - Tests con diferentes volúmenes de datos (9 tests)

3. **`docs/planificacion_pasos.md`**
   - Paso 3.11.6 marcado como completado
   - Añadida referencia a documentación

4. **`docs/pasos/paso-3.11.6-plan.md`**
   - Añadido resumen de implementación
   - Estado actualizado a "✅ COMPLETADO"

---

## Tests Implementados

### Resumen General

- **Total de tests**: 66 tests
- **Total de assertions**: 213 assertions
- **Tests públicos**: 45 tests
- **Tests de administración**: 21 tests

### Desglose por Tipo de Test

#### Tests de Tiempos de Carga (28 tests)

**Páginas Públicas (18 tests)**:
- Home: 1 test
- Index pages: 3 tests (programas, convocatorias, noticias)
- Show pages: 5 tests (programa, convocatoria, noticia, documento, evento)
- Index con 10 registros: 3 tests
- Index con 50 registros: 3 tests
- Index con 100 registros: 3 tests

**Páginas de Administración (6 tests)**:
- Dashboard: 1 test
- Programs index: 1 test
- Calls index: 1 test
- Call show: 1 test
- News index: 1 test
- Users index: 1 test

#### Tests de Consultas y N+1 (22 tests)

**Páginas Públicas (13 tests)**:
- Límites de consultas: 8 tests
- Detección de N+1: 5 tests

**Páginas de Administración (9 tests)**:
- Límites de consultas: 6 tests
- Detección de N+1: 3 tests

#### Tests de Eager Loading (10 tests)

**Páginas Públicas (6 tests)**:
- Programs index: 1 test
- Calls index: 1 test
- News index: 1 test
- Call show: 1 test
- News show: 1 test
- Program show: 1 test

**Páginas de Administración (4 tests)**:
- Calls index: 1 test
- News index: 1 test
- Users index: 1 test
- Call show: 1 test

#### Tests de Caché (6 tests)

**Páginas Públicas (4 tests)**:
- Reducción de consultas en segunda carga: 1 test
- Caché de AcademicYear::getCurrent(): 1 test
- Caché de Program::getCachedActive(): 1 test
- Invalidación de caché: 1 test

**Páginas de Administración (2 tests)**:
- Reducción de consultas en dashboard: 1 test
- Caché de configuraciones: 1 test

---

## Métricas y Estadísticas

### Límites Establecidos

#### Tiempos de Carga

- **Páginas públicas básicas**: < 2000ms
- **Páginas públicas complejas**: < 2000ms
- **Páginas públicas con 50 registros**: < 3000ms
- **Páginas públicas con 100 registros**: < 5000ms
- **Dashboard admin**: < 3000ms
- **Index pages admin**: < 2500ms
- **Show pages admin**: < 3000ms

#### Número Máximo de Consultas

- **Home pública**: < 20 consultas
- **Index pages públicas**: < 15-20 consultas
- **Show pages públicas**: < 25 consultas
- **Búsqueda global**: < 40 consultas
- **Dashboard admin**: < 40 consultas
- **Index pages admin**: < 30 consultas
- **Show pages admin**: < 35 consultas

### Correcciones Aplicadas

1. **Test de Program con academicYear**:
   - **Problema**: Test intentaba verificar relación directa que no existe
   - **Solución**: Corregido para verificar que no hay lazy loading innecesario en lugar de verificar relación inexistente

2. **Permisos en tests de admin**:
   - **Problema**: Tests fallaban con `AuthorizationException` porque `ADMIN` tiene permisos con wildcards (`programs.*`) que no funcionan sin habilitar `enable_wildcard_permission`
   - **Solución**: Cambiados todos los tests de admin para usar `SUPER_ADMIN` que tiene todos los permisos directamente

3. **Roles en tests**:
   - **Problema**: Tests fallaban con `RoleDoesNotExist` al intentar asignar roles
   - **Solución**: Añadido `ensureRolesExist()` antes de asignar roles en tests que los requieren

### Resultados de Ejecución

- **Tests ejecutados**: 66 tests
- **Tests pasando**: 66 tests (100%)
- **Tests fallando**: 0 tests
- **Assertions**: 213 assertions
- **Tiempo de ejecución**: ~70 segundos (todos los tests)

### Archivos de Documentación

1. **`docs/browser-testing-performance.md`** (nuevo)
   - Guía completa de tests de rendimiento
   - Documentación de helpers
   - Límites y convenciones
   - Troubleshooting
   - Mejores prácticas

2. **`docs/pasos/paso-3.11.6-plan.md`** (actualizado)
   - Resumen de implementación añadido
   - Estado actualizado a completado

3. **`docs/planificacion_pasos.md`** (actualizado)
   - Paso 3.11.6 marcado como completado

---

## Lecciones Aprendidas

1. **Wildcards de permisos**: Los wildcards de Spatie Permission requieren `enable_wildcard_permission => true` en la configuración. En este proyecto están deshabilitados, por lo que los tests deben usar `SUPER_ADMIN` en lugar de `ADMIN`.

2. **Relaciones indirectas**: No todos los modelos tienen relaciones directas. `Program` no tiene relación directa con `AcademicYear`, la relación es indirecta a través de otros modelos.

3. **Roles en tests**: Con `RefreshDatabase`, cada test parte de una base de datos vacía, por lo que los roles deben crearse antes de asignarlos usando `ensureRolesExist()`.

4. **Helpers globales**: Los helpers para browser tests deben ser funciones globales que acepten arrays como parámetros, a diferencia de los traits que se usan en feature tests.

5. **Límites conservadores**: Los límites de consultas y tiempos deben ser conservadores pero alcanzables, considerando variaciones entre entornos (local vs CI).

---

## Próximos Pasos

Tras completar el paso 3.11.6:

- **Paso 3.11.7**: Tests de responsive y accesibilidad
- **Paso 3.11.8**: Integración con CI/CD y documentación final

---

**Fecha de Creación**: 2026-01-27  
**Fecha de Finalización**: 2026-01-27  
**Estado**: ✅ COMPLETADO
