# Tests de Rendimiento y Optimización - Browser Testing

## Descripción General

Este documento describe los tests de rendimiento implementados para validar el rendimiento de la aplicación desde la perspectiva del usuario final. Estos tests utilizan Pest v4 con el plugin de browser testing para medir tiempos de carga reales, detectar problemas de N+1, validar eager loading y verificar el uso efectivo de caché.

## Estructura de Tests

Los tests de rendimiento están organizados en el directorio `tests/Browser/` con la siguiente estructura:

```
tests/Browser/
├── Helpers.php                          # Helpers para query logging y análisis
├── Public/
│   ├── PerformanceTest.php             # Tests de tiempos de carga (páginas públicas)
│   ├── QueryPerformanceTest.php        # Tests de consultas y detección N+1
│   ├── LazyLoadingTest.php             # Tests de validación de eager loading
│   └── CachePerformanceTest.php       # Tests de uso de caché
└── Admin/
    ├── PerformanceTest.php             # Tests de tiempos de carga (admin)
    ├── QueryPerformanceTest.php        # Tests de consultas y detección N+1
    ├── LazyLoadingTest.php             # Tests de validación de eager loading
    └── CachePerformanceTest.php       # Tests de uso de caché
```

## Helpers de Rendimiento

Los helpers están ubicados en `tests/Browser/Helpers.php` y proporcionan funciones especializadas para análisis de rendimiento:

### Query Logging

- **`startBrowserQueryLog()`**: Inicia el registro de consultas a la base de datos
- **`stopBrowserQueryLog()`**: Detiene el registro y devuelve el array de consultas ejecutadas
- **`getBrowserQueryCount(array $queries)`**: Cuenta el número total de consultas
- **`getBrowserQueries(array $queries)`**: Devuelve todas las consultas normalizadas
- **`getBrowserTotalQueryTime(array $queries)`**: Calcula el tiempo total de ejecución de consultas
- **`getBrowserSlowQueries(array $queries, float $threshold = 100.0)`**: Identifica consultas lentas

### Análisis de Consultas

- **`normalizeBrowserQuery(string $query)`**: Normaliza una consulta SQL para comparación
- **`getBrowserDuplicateQueries(array $queries)`**: Detecta consultas duplicadas (posible N+1)

### Assertions de Rendimiento

- **`assertBrowserQueryCountLessThan(array $queries, int $maxQueries, ?string $message = null)`**: Verifica que el número de consultas no exceda un límite
- **`assertBrowserNoDuplicateQueries(array $queries, array $allowedPatterns = [], ?string $message = null)`**: Verifica que no haya consultas duplicadas (N+1)
- **`assertBrowserNoSlowQueries(array $queries, float $threshold = 100.0, ?string $message = null)`**: Verifica que no haya consultas lentas
- **`assertBrowserTotalQueryTimeLessThan(array $queries, float $maxTime, ?string $message = null)`**: Verifica que el tiempo total de consultas no exceda un límite

### Validación de Eager Loading

- **`assertEagerLoaded(string $relation, array $queries, ?string $message = null)`**: Verifica que una relación está eager loaded (busca JOIN o WHERE IN)
- **`assertNoLazyLoading(string $model, string $relation, array $queries, ?string $message = null)`**: Verifica que no hay lazy loading para una relación específica

### Validación de Caché

- **`compareQueryCountsWithCache(array $queriesWithoutCache, array $queriesWithCache, ?string $message = null)`**: Compara el número de consultas con y sin caché
- **`assertCacheUsed(string $key, array $queries, array $queryPatterns = [], ?string $message = null)`**: Verifica que el caché está siendo utilizado

### Debugging

- **`outputBrowserQueryDetails(array $queries)`**: Imprime detalles de todas las consultas para debugging

## Límites Establecidos

### Tiempos de Carga

#### Páginas Públicas
- **Home**: < 2000ms
- **Index pages** (programas, convocatorias, noticias): < 2000ms
- **Show pages** (detalle): < 2000ms
- **Index con 10 registros**: < 2000ms
- **Index con 50 registros**: < 3000ms
- **Index con 100 registros**: < 5000ms

#### Páginas de Administración
- **Dashboard**: < 3000ms
- **Index pages**: < 2500ms
- **Show pages**: < 3000ms

### Número Máximo de Consultas

#### Páginas Públicas
- **Home**: < 20 consultas
- **Index pages**: < 15-20 consultas
- **Show pages**: < 25 consultas
- **Búsqueda global**: < 40 consultas

#### Páginas de Administración
- **Dashboard**: < 40 consultas
- **Index pages**: < 30 consultas
- **Show pages**: < 35 consultas

### Consultas Lentas

- **Umbral por defecto**: 100ms
- Todas las consultas deben ejecutarse en menos de 100ms (ajustable según necesidades)

## Convenciones

### Estructura de Tests

1. **Tests de Tiempos de Carga** (`PerformanceTest.php`):
   ```php
   it('loads page within acceptable time', function () {
       // Preparar datos
       $data = createTestData();
       
       // Medir tiempo
       $startTime = microtime(true);
       $page = visit(route('page'));
       $endTime = microtime(true);
       
       $loadTime = ($endTime - $startTime) * 1000; // ms
       expect($loadTime)->toBeLessThan(2000);
       
       $page->assertSee('Expected Content')
           ->assertNoJavascriptErrors();
   });
   ```

2. **Tests de Consultas** (`QueryPerformanceTest.php`):
   ```php
   it('executes less than N queries on page', function () {
       // Preparar datos
       createTestData();
       
       // Registrar consultas
       startBrowserQueryLog();
       $page = visit(route('page'));
       $queries = stopBrowserQueryLog();
       
       // Verificar límite
       assertBrowserQueryCountLessThan($queries, 20);
       
       $page->assertSee('Expected Content')
           ->assertNoJavascriptErrors();
   });
   ```

3. **Tests de N+1** (`QueryPerformanceTest.php`):
   ```php
   it('does not have N+1 when loading relations', function () {
       // Crear datos con relaciones
       $items = createItemsWithRelations(20);
       
       startBrowserQueryLog();
       $page = visit(route('index'));
       $queries = stopBrowserQueryLog();
       
       // Verificar no hay duplicados (permitir legítimos)
       assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions']);
       
       $page->assertSee('Expected Content')
           ->assertNoJavascriptErrors();
   });
   ```

4. **Tests de Eager Loading** (`LazyLoadingTest.php`):
   ```php
   it('has relations eager loaded', function () {
       createTestData();
       
       startBrowserQueryLog();
       $page = visit(route('page'));
       $queries = stopBrowserQueryLog();
       
       // Verificar eager loading
       assertEagerLoaded('relation', $queries);
       
       $page->assertSee('Expected Content')
           ->assertNoJavascriptErrors();
   });
   ```

5. **Tests de Caché** (`CachePerformanceTest.php`):
   ```php
   it('cache reduces queries on second load', function () {
       createTestData();
       
       // Primera carga (sin caché)
       Cache::flush();
       startBrowserQueryLog();
       visit(route('page'));
       $queriesWithoutCache = stopBrowserQueryLog();
       
       // Segunda carga (con caché)
       startBrowserQueryLog();
       visit(route('page'));
       $queriesWithCache = stopBrowserQueryLog();
       
       // Verificar reducción
       compareQueryCountsWithCache($queriesWithoutCache, $queriesWithCache);
   });
   ```

### Patrones de Consultas Legítimamente Duplicadas

Algunas consultas pueden ejecutarse múltiples veces de forma legítima y deben ser excluidas de la detección de N+1:

- **`activity_log`**: Registros de actividad (pueden ejecutarse por cada acción)
- **`permissions`**: Verificación de permisos (puede ejecutarse múltiples veces)
- **`count`**: Consultas de conteo en stats (pueden ser múltiples)

Ejemplo:
```php
assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions', 'count']);
```

### Autenticación en Tests de Admin

**Importante**: Los tests de administración deben usar `SUPER_ADMIN` en lugar de `ADMIN` porque los wildcards de permisos están deshabilitados (`enable_wildcard_permission => false`). El rol `ADMIN` tiene permisos con wildcards (`programs.*`, `calls.*`, etc.) que no funcionan sin habilitar los wildcards.

```php
// ✅ Correcto
$user = createAuthTestUser([], Roles::SUPER_ADMIN);
performLogin($user);

// ❌ Incorrecto (fallará por permisos)
$user = createAuthTestUser([], Roles::ADMIN);
performLogin($user);
```

### Creación de Roles en Tests

Antes de asignar roles a usuarios en tests, asegurar que los roles existen:

```php
it('test with roles', function () {
    // Asegurar que los roles existen
    ensureRolesExist();
    
    $users = User::factory()->count(20)->create();
    foreach ($users as $user) {
        $user->assignRole(Roles::VIEWER);
    }
    
    // ... resto del test
});
```

## Comandos

### Ejecutar Todos los Tests de Rendimiento

```bash
php artisan test tests/Browser/Public/PerformanceTest.php \
    tests/Browser/Public/QueryPerformanceTest.php \
    tests/Browser/Public/LazyLoadingTest.php \
    tests/Browser/Public/CachePerformanceTest.php \
    tests/Browser/Admin/PerformanceTest.php \
    tests/Browser/Admin/QueryPerformanceTest.php \
    tests/Browser/Admin/LazyLoadingTest.php \
    tests/Browser/Admin/CachePerformanceTest.php
```

### Ejecutar Tests Específicos

```bash
# Solo tests de tiempos de carga
php artisan test tests/Browser/Public/PerformanceTest.php

# Solo tests de consultas
php artisan test tests/Browser/Public/QueryPerformanceTest.php

# Solo tests de lazy loading
php artisan test tests/Browser/Public/LazyLoadingTest.php

# Solo tests de caché
php artisan test tests/Browser/Public/CachePerformanceTest.php
```

### Ejecutar con Filtro

```bash
# Ejecutar solo tests que contengan "loads" en el nombre
php artisan test tests/Browser/Public/PerformanceTest.php --filter="loads"

# Ejecutar solo tests de N+1
php artisan test tests/Browser/Public/QueryPerformanceTest.php --filter="N+1"
```

### Modo Debug

```bash
# Ver el navegador durante la ejecución
php artisan test tests/Browser/Public/PerformanceTest.php --headed

# Pausar en errores para debugging
php artisan test tests/Browser/Public/PerformanceTest.php --debug
```

## Troubleshooting

### Problema: Test falla por exceder límite de consultas

**Síntomas**: El test falla con `assertBrowserQueryCountLessThan()` pero no hay N+1 evidente.

**Soluciones**:
1. Usar `outputBrowserQueryDetails($queries)` para ver todas las consultas ejecutadas
2. Verificar si hay consultas legítimas que se ejecutan múltiples veces (usar `allowedPatterns`)
3. Si el límite es demasiado estricto y no hay problemas de rendimiento, considerar aumentar el límite
4. Si hay N+1, corregir el código antes de ajustar el límite

### Problema: Test detecta N+1 pero las consultas son legítimas

**Síntomas**: `assertBrowserNoDuplicateQueries()` falla pero las consultas duplicadas son esperadas.

**Solución**: Usar `allowedPatterns` para excluir consultas legítimamente duplicadas:

```php
assertBrowserNoDuplicateQueries($queries, [
    'activity_log',
    'permissions',
    'count',
    // ... otros patrones
]);
```

### Problema: Test de caché no detecta reducción de consultas

**Síntomas**: `compareQueryCountsWithCache()` falla porque no hay diferencia significativa.

**Soluciones**:
1. Verificar que el caché está habilitado en el entorno de testing
2. Asegurar que `Cache::flush()` se ejecuta antes de la primera carga
3. Verificar que la funcionalidad realmente usa caché (revisar código)
4. Usar `outputBrowserQueryDetails()` para comparar las consultas manualmente

### Problema: Test de eager loading falla

**Síntomas**: `assertEagerLoaded()` no encuentra la relación esperada.

**Soluciones**:
1. Verificar que la relación está siendo cargada en el componente (revisar `with()` o `load()`)
2. Usar `outputBrowserQueryDetails($queries)` para ver qué consultas se ejecutan
3. Verificar que el patrón de búsqueda es correcto (JOIN o WHERE IN)
4. Si la relación se carga de forma diferente, ajustar la función de validación

### Problema: Test falla por permisos en admin

**Síntomas**: `AuthorizationException: This action is unauthorized` en tests de admin.

**Solución**: Usar `SUPER_ADMIN` en lugar de `ADMIN`:

```php
// ✅ Correcto
$user = createAuthTestUser([], Roles::SUPER_ADMIN);

// ❌ Incorrecto
$user = createAuthTestUser([], Roles::ADMIN);
```

### Problema: Test falla por rol no existe

**Síntomas**: `RoleDoesNotExist: There is no role named 'viewer'`.

**Solución**: Llamar `ensureRolesExist()` antes de asignar roles:

```php
it('test with roles', function () {
    ensureRolesExist(); // ← Añadir esto
    
    $users = User::factory()->count(20)->create();
    foreach ($users as $user) {
        $user->assignRole(Roles::VIEWER);
    }
});
```

### Problema: Tiempos de carga variables

**Síntomas**: Los tests de tiempo de carga fallan intermitentemente.

**Soluciones**:
1. Los tiempos pueden variar según el entorno (CI vs local, carga del sistema)
2. Establecer umbrales conservadores (más altos que el promedio esperado)
3. Considerar ejecutar tests múltiples veces para obtener promedios
4. En CI, considerar aumentar los umbrales si el entorno es más lento

## Estadísticas de Tests

### Resumen de Implementación

- **Total de tests**: 66 tests
- **Total de assertions**: 213 assertions
- **Tests públicos**: 45 tests
- **Tests de administración**: 21 tests

### Desglose por Tipo

#### Tests de Tiempos de Carga (28 tests)
- Páginas públicas: 18 tests
- Páginas de administración: 6 tests
- Tests con diferentes volúmenes de datos: 4 tests

#### Tests de Consultas (22 tests)
- Límites de consultas: 13 tests
- Detección de N+1: 9 tests

#### Tests de Eager Loading (10 tests)
- Validación de relaciones: 10 tests

#### Tests de Caché (6 tests)
- Reducción de consultas: 2 tests
- Validación de caché: 2 tests
- Invalidación de caché: 2 tests

## Mejores Prácticas

1. **Siempre limpiar caché antes de tests de caché**: Usar `Cache::flush()` o `Cache::forget()` para tener un estado conocido.

2. **Usar `allowedPatterns` para consultas legítimamente duplicadas**: No todas las consultas duplicadas son N+1.

3. **Verificar eager loading en relaciones críticas**: Especialmente en listados y páginas de detalle con múltiples relaciones.

4. **Establecer límites conservadores**: Los límites deben ser alcanzables pero no demasiado permisivos.

5. **Usar `outputBrowserQueryDetails()` para debugging**: Cuando un test falla, esta función ayuda a identificar el problema.

6. **Ejecutar tests en diferentes entornos**: Los tiempos pueden variar entre local y CI.

7. **Mantener tests actualizados**: Si se añaden nuevas relaciones o funcionalidades, actualizar los tests correspondientes.

## Referencias

- [Plan detallado de implementación](pasos/paso-3.11.6-plan.md)
- [Documentación de browser testing setup](browser-testing-setup.md)
- [Documentación de páginas públicas](browser-testing-public-pages.md)
- [Trait CountsQueries para feature tests](../tests/Concerns/CountsQueries.php)

---

**Fecha de Creación**: Enero 2026  
**Última Actualización**: Enero 2026  
**Estado**: ✅ Completado
