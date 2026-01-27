# Plan de Trabajo - Paso 3.11.8: Integración con CI/CD y Documentación

## Objetivo

Configurar la integración continua (CI/CD) para ejecutar automáticamente todos los tests (unitarios, funcionales y de navegador) en cada push y pull request, asegurando que el código cumple con los estándares de calidad antes de ser fusionado. Además, crear documentación completa sobre cómo ejecutar los tests, troubleshooting común y mejores prácticas para mantener la suite de tests.

---

## Estado Actual

### ✅ Ya Implementado

1. **Suite de Tests Completa**:
   - Tests unitarios en `tests/Unit/`
   - Tests funcionales en `tests/Feature/`
   - Tests de navegador en `tests/Browser/` (Public/, Admin/, Auth/)
   - Pest v4 configurado correctamente
   - `pest-plugin-browser` instalado y configurado
   - Playwright instalado y funcionando

2. **Configuración de Tests**:
   - `tests/Pest.php` configurado con soporte para Browser tests
   - `phpunit.xml` configurado con SQLite en memoria
   - `RefreshDatabase` trait configurado para todos los tests
   - Helpers personalizados en `tests/Browser/Helpers.php`

3. **Tests Implementados**:
   - Tests de páginas públicas críticas (Home, Programs, Calls, News, Documents, Events)
   - Tests de autenticación y autorización
   - Tests de formularios y validación
   - Tests de interacciones JavaScript y Livewire
   - Tests de rendimiento y optimización
   - Tests de responsive y accesibilidad

4. **Comandos de Testing**:
   - `php artisan test` - Ejecuta todos los tests
   - `php artisan test --filter=testName` - Ejecuta tests filtrados
   - `./vendor/bin/pest` - Ejecuta Pest directamente

### ⚠️ Pendiente de Implementar

1. **Configuración de CI/CD**:
   - No hay workflows de GitHub Actions configurados
   - No hay configuración para ejecutar browser tests en CI
   - No hay reportes automáticos de resultados de tests
   - No hay badges de estado de tests

2. **Documentación de Testing**:
   - No hay guía completa de ejecución de tests
   - No hay documentación de troubleshooting común
   - No hay guía de mejores prácticas
   - No hay documentación de estrategia de testing completa

3. **Integración con Suite de Tests**:
   - No hay configuración para ejecutar tests en paralelo en CI
   - No hay configuración de cobertura de código en CI
   - No hay notificaciones de fallos de tests

---

## Dependencias y Premisas

- **GitHub Actions**: Se utilizará GitHub Actions como plataforma de CI/CD (estándar para proyectos Laravel en GitHub)
- **Playwright en CI**: Los browser tests requieren Playwright instalado en el entorno de CI
- **Base de datos en CI**: SQLite en memoria es suficiente para la mayoría de tests, pero algunos pueden requerir MySQL
- **Tiempo de ejecución**: Los browser tests son más lentos que los tests funcionales, por lo que se ejecutarán en paralelo cuando sea posible
- **Cobertura de código**: Se utilizará Pest/PHPUnit para generar reportes de cobertura
- **Entorno de CI**: Ubuntu latest (recomendado por GitHub Actions y Playwright)

---

## Plan de Trabajo

### Fase 1: Configuración de GitHub Actions para Tests Básicos

**Objetivo**: Configurar un workflow básico de GitHub Actions que ejecute los tests unitarios y funcionales en cada push y pull request.

**Archivo**: `.github/workflows/tests.yml` (nuevo)

#### 1.1. Crear estructura de directorios

- [ ] Crear directorio `.github/workflows/` si no existe
- [ ] Verificar que el directorio está en el repositorio (no en .gitignore)

#### 1.2. Crear workflow básico de tests

- [ ] Crear archivo `.github/workflows/tests.yml` con:
  - Trigger en `push` y `pull_request` a ramas principales
  - Matriz de versiones de PHP (8.3)
  - Matriz de versiones de Node.js (20.x, 22.x)
  - Instalación de dependencias (Composer y npm)
  - Configuración de base de datos SQLite
  - Ejecución de tests unitarios y funcionales
  - Reporte de resultados

#### 1.3. Configurar entorno de testing

- [ ] Configurar variables de entorno necesarias:
  - `APP_ENV=testing`
  - `APP_KEY` (generar con `php artisan key:generate`)
  - `DB_CONNECTION=sqlite`
  - `DB_DATABASE=:memory:`
  - `CACHE_STORE=array`
  - `SESSION_DRIVER=array`
  - `MAIL_MAILER=array`
  - `QUEUE_CONNECTION=sync`

#### 1.4. Configurar instalación de dependencias

- [ ] Instalar dependencias PHP con Composer:
  ```yaml
  - name: Install PHP dependencies
    run: composer install --prefer-dist --no-progress --no-interaction
  ```

- [ ] Instalar dependencias JavaScript con npm:
  ```yaml
  - name: Install Node dependencies
    run: npm ci
  ```

- [ ] Compilar assets para tests (si es necesario):
  ```yaml
  - name: Build assets
    run: npm run build
  ```

#### 1.5. Configurar ejecución de tests básicos

- [ ] Ejecutar tests unitarios y funcionales:
  ```yaml
  - name: Run tests
    run: php artisan test
  ```

- [ ] Configurar timeouts apropiados (los tests pueden tardar varios minutos)

#### 1.6. Verificar workflow básico

- [ ] Hacer push del workflow a una rama de prueba
- [ ] Verificar que GitHub Actions ejecuta el workflow
- [ ] Verificar que los tests se ejecutan correctamente
- [ ] Verificar que el workflow falla si hay tests que fallan

---

### Fase 2: Configuración de Browser Tests en CI

**Objetivo**: Configurar el workflow para ejecutar browser tests en el entorno de CI, instalando Playwright y configurando el entorno adecuado.

**Archivo**: `.github/workflows/tests.yml` (actualizar)

#### 2.1. Instalar Playwright en CI

- [ ] Añadir paso para instalar Playwright browsers:
  ```yaml
  - name: Install Playwright Browsers
    run: npx playwright install --with-deps chromium
  ```

- [ ] Configurar variables de entorno para Playwright:
  ```yaml
  env:
    PLAYWRIGHT_BROWSERS_PATH: 0
  ```

#### 2.2. Configurar ejecución de browser tests

- [ ] Ejecutar browser tests en un paso separado:
  ```yaml
  - name: Run browser tests
    run: php artisan test --testsuite=Browser
  ```

- [ ] O ejecutar todos los tests juntos (incluyendo browser tests):
  ```yaml
  - name: Run all tests
    run: php artisan test
  ```

#### 2.3. Configurar modo headless para CI

- [ ] Verificar que `tests/Pest.php` tiene configuración para modo headless en CI:
  ```php
  // Ya implementado: if (! env('CI')) { pest()->browser()->headed(); }
  ```

- [ ] Asegurar que la variable `CI` está configurada en GitHub Actions:
  ```yaml
  env:
    CI: true
  ```

#### 2.4. Configurar timeouts para browser tests

- [ ] Aumentar timeout para browser tests (son más lentos):
  ```yaml
  - name: Run browser tests
    run: php artisan test --testsuite=Browser
    timeout-minutes: 30
  ```

#### 2.5. Configurar capturas de pantalla en caso de fallo

- [ ] Configurar Playwright para guardar capturas de pantalla en fallos:
  ```yaml
  - name: Upload screenshots on failure
    if: failure()
    uses: actions/upload-artifact@v4
    with:
      name: browser-screenshots
      path: tests/Browser/screenshots/
  ```

#### 2.6. Verificar browser tests en CI

- [ ] Hacer push del workflow actualizado
- [ ] Verificar que Playwright se instala correctamente
- [ ] Verificar que los browser tests se ejecutan en modo headless
- [ ] Verificar que los tests pasan correctamente
- [ ] Verificar que las capturas de pantalla se guardan en caso de fallo

---

### Fase 3: Optimización y Paralelización de Tests

**Objetivo**: Optimizar la ejecución de tests en CI ejecutándolos en paralelo cuando sea posible y cacheando dependencias.

**Archivo**: `.github/workflows/tests.yml` (actualizar)

#### 3.1. Configurar caché de dependencias

- [ ] Configurar caché de Composer:
  ```yaml
  - name: Cache Composer dependencies
    uses: actions/cache@v4
    with:
      path: ~/.composer/cache
      key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
      restore-keys: |
        ${{ runner.os }}-composer-
  ```

- [ ] Configurar caché de npm:
  ```yaml
  - name: Cache npm dependencies
    uses: actions/cache@v4
    with:
      path: ~/.npm
      key: ${{ runner.os }}-npm-${{ hashFiles('**/package-lock.json') }}
      restore-keys: |
        ${{ runner.os }}-npm-
  ```

- [ ] Configurar caché de Playwright browsers:
  ```yaml
  - name: Cache Playwright browsers
    uses: actions/cache@v4
    with:
      path: ~/.cache/ms-playwright
      key: ${{ runner.os }}-playwright-${{ hashFiles('**/package-lock.json') }}
      restore-keys: |
        ${{ runner.os }}-playwright-
  ```

#### 3.2. Configurar ejecución paralela de tests

- [ ] Ejecutar tests unitarios y funcionales en paralelo con browser tests:
  ```yaml
  - name: Run unit and feature tests
    run: php artisan test --exclude-group=browser
    continue-on-error: false

  - name: Run browser tests
    run: php artisan test --group=browser
    continue-on-error: false
  ```

- [ ] O usar jobs separados para mejor paralelización:
  ```yaml
  jobs:
    unit-feature-tests:
      # ... configuración ...
      - name: Run unit and feature tests
        run: php artisan test --exclude-group=browser

    browser-tests:
      # ... configuración ...
      - name: Run browser tests
        run: php artisan test --group=browser
  ```

#### 3.3. Configurar grupos de tests en Pest

- [ ] Añadir grupos a tests de navegador si no existen:
  ```php
  // En tests/Browser/*.php
  uses()->group('browser');
  ```

- [ ] Verificar que los tests funcionales no tienen el grupo `browser`

#### 3.4. Optimizar tiempo de ejecución

- [ ] Revisar tests lentos y optimizarlos si es posible
- [ ] Considerar ejecutar solo tests afectados por cambios (opcional, con herramientas como `phpunit-filter`)
- [ ] Configurar timeouts apropiados para evitar timeouts prematuros

#### 3.5. Verificar optimizaciones

- [ ] Verificar que el caché funciona correctamente
- [ ] Verificar que los tests se ejecutan en paralelo
- [ ] Verificar que el tiempo total de ejecución se reduce

---

### Fase 4: Reportes y Cobertura de Código

**Objetivo**: Configurar reportes de cobertura de código y publicar resultados de tests.

**Archivo**: `.github/workflows/tests.yml` (actualizar)

#### 4.1. Configurar generación de reportes de cobertura

- [ ] Instalar dependencias necesarias para cobertura:
  ```yaml
  - name: Install coverage dependencies
    run: |
      composer require --dev phpunit/php-code-coverage
  ```

- [ ] Configurar PHPUnit para generar reportes de cobertura:
  ```yaml
  - name: Run tests with coverage
    run: php artisan test --coverage --min=80
  ```

- [ ] O usar Pest directamente:
  ```yaml
  - name: Run tests with coverage
    run: ./vendor/bin/pest --coverage --min=80
  ```

#### 4.2. Publicar reportes de cobertura

- [ ] Publicar reportes de cobertura como artifact:
  ```yaml
  - name: Upload coverage reports
    uses: actions/upload-artifact@v4
    with:
      name: coverage-report
      path: coverage/
  ```

- [ ] Integrar con servicios de cobertura (opcional):
  - Codecov
  - Coveralls
  - GitHub Code Scanning

#### 4.3. Configurar umbral mínimo de cobertura

- [ ] Definir umbral mínimo de cobertura (ej: 80%)
- [ ] Configurar el workflow para fallar si la cobertura es menor al umbral
- [ ] Documentar el umbral en la documentación

#### 4.4. Configurar reportes de resultados de tests

- [ ] Publicar resultados de tests como artifact:
  ```yaml
  - name: Upload test results
    if: always()
    uses: actions/upload-artifact@v4
    with:
      name: test-results
      path: tests/results/
  ```

#### 4.5. Configurar badges de estado

- [ ] Crear badge de estado de tests en README:
  ```markdown
  ![Tests](https://github.com/usuario/erasmus25/workflows/Tests/badge.svg)
  ```

- [ ] Verificar que el badge muestra el estado correcto

---

### Fase 5: Documentación de Ejecución de Tests

**Objetivo**: Crear documentación completa sobre cómo ejecutar los tests localmente y en CI.

**Archivo**: `docs/testing-guide.md` (nuevo)

#### 5.1. Crear estructura de documentación

- [ ] Crear archivo `docs/testing-guide.md`
- [ ] Estructurar con secciones:
  - Introducción
  - Requisitos del sistema
  - Ejecución local de tests
  - Ejecución de browser tests
  - Configuración de CI/CD
  - Troubleshooting
  - Mejores prácticas

#### 5.2. Documentar requisitos del sistema

- [ ] Documentar versiones requeridas:
  - PHP 8.3+
  - Composer
  - Node.js 20.x o 22.x
  - npm
  - Playwright (se instala automáticamente)

#### 5.3. Documentar ejecución local de tests

- [ ] Documentar comandos básicos:
  ```bash
  # Ejecutar todos los tests
  php artisan test

  # Ejecutar tests específicos
  php artisan test --filter=testName

  # Ejecutar tests de un archivo
  php artisan test tests/Feature/ExampleTest.php

  # Ejecutar solo tests unitarios
  php artisan test --testsuite=Unit

  # Ejecutar solo tests funcionales
  php artisan test --testsuite=Feature

  # Ejecutar solo browser tests
  php artisan test --testsuite=Browser
  ```

- [ ] Documentar ejecución con Pest directamente:
  ```bash
  ./vendor/bin/pest
  ./vendor/bin/pest --filter=testName
  ```

#### 5.4. Documentar ejecución de browser tests

- [ ] Documentar instalación de Playwright:
  ```bash
  npx playwright install --with-deps
  ```

- [ ] Documentar ejecución de browser tests:
  ```bash
  php artisan test --testsuite=Browser
  ```

- [ ] Documentar modo headed vs headless:
  - Modo headed: `pest()->browser()->headed()` (solo desarrollo local)
  - Modo headless: por defecto en CI

- [ ] Documentar ejecución de tests específicos de browser:
  ```bash
  php artisan test tests/Browser/Public/HomeTest.php
  ```

#### 5.5. Documentar configuración de CI/CD

- [ ] Documentar el workflow de GitHub Actions
- [ ] Explicar cómo funciona el CI/CD
- [ ] Documentar cómo ver los resultados de tests en GitHub
- [ ] Documentar cómo descargar artifacts (capturas de pantalla, reportes)

#### 5.6. Documentar variables de entorno para testing

- [ ] Documentar variables de entorno necesarias:
  ```env
  APP_ENV=testing
  APP_KEY=base64:...
  DB_CONNECTION=sqlite
  DB_DATABASE=:memory:
  CACHE_STORE=array
  SESSION_DRIVER=array
  MAIL_MAILER=array
  QUEUE_CONNECTION=sync
  CI=true  # Para CI/CD
  ```

---

### Fase 6: Documentación de Troubleshooting

**Objetivo**: Documentar problemas comunes y sus soluciones.

**Archivo**: `docs/testing-guide.md` (ampliar) y `docs/browser-testing-troubleshooting.md` (actualizar)

#### 6.1. Documentar problemas comunes de tests funcionales

- [ ] Problema: Tests fallan por base de datos
  - Solución: Verificar que `RefreshDatabase` está configurado
  - Solución: Verificar que SQLite está configurado correctamente

- [ ] Problema: Tests fallan por caché
  - Solución: Limpiar caché antes de ejecutar tests
  - Solución: Verificar que `CACHE_STORE=array` en entorno de testing

- [ ] Problema: Tests fallan por permisos
  - Solución: Verificar que los seeders de roles y permisos se ejecutan
  - Solución: Verificar que el usuario de test tiene los permisos correctos

#### 6.2. Documentar problemas comunes de browser tests

- [ ] Problema: Playwright no se instala
  - Solución: Ejecutar `npx playwright install --with-deps`
  - Solución: Verificar que Node.js está instalado

- [ ] Problema: Browser tests fallan por timeouts
  - Solución: Aumentar timeout en `tests/Pest.php`
  - Solución: Verificar que la aplicación está respondiendo correctamente

- [ ] Problema: Browser tests fallan por elementos no encontrados
  - Solución: Verificar que los datos de test se crean correctamente
  - Solución: Añadir `waitFor()` antes de interactuar con elementos

- [ ] Problema: Browser tests fallan en CI pero pasan localmente
  - Solución: Verificar que el modo headless está configurado
  - Solución: Verificar que las variables de entorno son correctas
  - Solución: Revisar capturas de pantalla en artifacts

#### 6.3. Documentar problemas comunes de CI/CD

- [ ] Problema: Workflow falla por dependencias
  - Solución: Verificar que `composer.lock` y `package-lock.json` están actualizados
  - Solución: Verificar que el caché de dependencias funciona

- [ ] Problema: Browser tests fallan en CI
  - Solución: Verificar que Playwright se instala correctamente
  - Solución: Verificar que el modo headless está configurado
  - Solución: Revisar logs de GitHub Actions

- [ ] Problema: Tests son demasiado lentos en CI
  - Solución: Ejecutar tests en paralelo
  - Solución: Usar caché de dependencias
  - Solución: Optimizar tests lentos

#### 6.4. Documentar comandos útiles de debugging

- [ ] Comandos para limpiar entorno:
  ```bash
  php artisan cache:clear
  php artisan config:clear
  php artisan view:clear
  ```

- [ ] Comandos para ejecutar tests con más información:
  ```bash
  php artisan test --verbose
  ./vendor/bin/pest --verbose
  ```

- [ ] Comandos para ejecutar un test específico:
  ```bash
  php artisan test --filter=testName
  ./vendor/bin/pest --filter=testName
  ```

---

### Fase 7: Documentación de Mejores Prácticas

**Objetivo**: Documentar mejores prácticas para escribir y mantener tests.

**Archivo**: `docs/testing-guide.md` (ampliar)

#### 7.1. Documentar mejores prácticas para tests funcionales

- [ ] Usar `RefreshDatabase` para asegurar estado limpio
- [ ] Usar factories en lugar de crear modelos manualmente
- [ ] Usar nombres descriptivos para tests
- [ ] Agrupar tests relacionados en archivos
- [ ] Usar datasets cuando hay datos repetidos
- [ ] Verificar tanto casos exitosos como casos de error

#### 7.2. Documentar mejores prácticas para browser tests

- [ ] Usar helpers para crear datos de test
- [ ] Usar `waitFor()` antes de interactuar con elementos
- [ ] Verificar que no hay errores de JavaScript (`assertNoJavascriptErrors()`)
- [ ] Usar viewports apropiados (`on()->mobile()`, `on()->tablet()`, `on()->desktop()`)
- [ ] Limpiar datos de test después de cada test
- [ ] Usar `wire:navigate` para probar navegación de Livewire

#### 7.3. Documentar mejores prácticas para CI/CD

- [ ] Ejecutar tests en cada push y pull request
- [ ] Usar caché de dependencias para acelerar ejecución
- [ ] Ejecutar tests en paralelo cuando sea posible
- [ ] Configurar umbral mínimo de cobertura
- [ ] Publicar reportes de cobertura y resultados

#### 7.4. Documentar estrategia de testing completa

- [ ] Explicar la pirámide de testing:
  - Tests unitarios (base): muchos, rápidos, aislados
  - Tests funcionales (medio): menos, más lentos, integración
  - Browser tests (cima): pocos, lentos, end-to-end

- [ ] Explicar cuándo usar cada tipo de test:
  - Tests unitarios: lógica de negocio, modelos, helpers
  - Tests funcionales: componentes Livewire, políticas, form requests
  - Browser tests: flujos completos, interacciones JavaScript, responsive

- [ ] Explicar la cobertura objetivo:
  - Objetivo: 80%+ de cobertura de código
  - Priorizar cobertura de código crítico
  - No obsesionarse con 100% de cobertura

---

### Fase 8: Integración con Suite de Tests Existente

**Objetivo**: Asegurar que los browser tests se integran correctamente con la suite de tests existente.

**Archivo**: `phpunit.xml` y `tests/Pest.php` (verificar)

#### 8.1. Verificar configuración de PHPUnit

- [ ] Verificar que `phpunit.xml` incluye todos los testsuites:
  ```xml
  <testsuite name="Unit">
    <directory>tests/Unit</directory>
  </testsuite>
  <testsuite name="Feature">
    <directory>tests/Feature</directory>
  </testsuite>
  <testsuite name="Browser">
    <directory>tests/Browser</directory>
  </testsuite>
  ```

- [ ] Verificar que las variables de entorno están configuradas correctamente

#### 8.2. Verificar configuración de Pest

- [ ] Verificar que `tests/Pest.php` tiene configuración para Browser tests
- [ ] Verificar que `RefreshDatabase` está configurado para Browser tests
- [ ] Verificar que el modo headed/headless está configurado correctamente

#### 8.3. Verificar ejecución de todos los tests

- [ ] Ejecutar todos los tests juntos:
  ```bash
  php artisan test
  ```

- [ ] Verificar que todos los tests pasan:
  - Tests unitarios
  - Tests funcionales
  - Browser tests

- [ ] Verificar tiempos de ejecución:
  - Tests unitarios: < 1 minuto
  - Tests funcionales: < 5 minutos
  - Browser tests: < 15 minutos
  - Total: < 20 minutos

#### 8.4. Verificar cobertura combinada

- [ ] Ejecutar tests con cobertura:
  ```bash
  php artisan test --coverage
  ```

- [ ] Verificar que la cobertura incluye todos los tipos de tests
- [ ] Verificar que el umbral mínimo se cumple

---

### Fase 9: Configuración de Notificaciones y Badges

**Objetivo**: Configurar notificaciones de fallos de tests y badges de estado.

**Archivo**: `.github/workflows/tests.yml` (actualizar) y `README.md` (actualizar)

#### 9.1. Configurar notificaciones de fallos

- [ ] Configurar notificaciones por email (opcional):
  ```yaml
  - name: Notify on failure
    if: failure()
    uses: actions/github-script@v7
    with:
      script: |
        // Enviar notificación
  ```

- [ ] O usar GitHub's built-in notifications (por defecto)

#### 9.2. Configurar badges de estado

- [ ] Añadir badge de estado de tests en `README.md`:
  ```markdown
  ![Tests](https://github.com/usuario/erasmus25/workflows/Tests/badge.svg)
  ```

- [ ] Añadir badge de cobertura (si se usa Codecov o similar):
  ```markdown
  ![Coverage](https://codecov.io/gh/usuario/erasmus25/branch/main/graph/badge.svg)
  ```

#### 9.3. Configurar protección de ramas (opcional)

- [ ] Configurar protección de rama `main` para requerir que los tests pasen antes de merge
- [ ] Configurar protección para requerir revisión de código (opcional)

---

### Fase 10: Documentación Final y Verificación

**Objetivo**: Verificar que todo funciona correctamente y crear documentación final.

#### 10.1. Verificar configuración completa

- [ ] Ejecutar workflow de CI localmente (usando `act` o similar) o hacer push de prueba
- [ ] Verificar que todos los tests se ejecutan correctamente
- [ ] Verificar que los reportes se generan correctamente
- [ ] Verificar que las notificaciones funcionan

#### 10.2. Crear documentación de resumen

- [ ] Crear resumen ejecutivo en `docs/testing-guide.md`:
  - Resumen de la estrategia de testing
  - Comandos principales
  - Enlaces a documentación detallada

#### 10.3. Actualizar README principal

- [ ] Añadir sección de testing en `README.md`:
  ```markdown
  ## Testing

  Ejecutar todos los tests:
  ```bash
  php artisan test
  ```

  Ver [Guía de Testing](docs/testing-guide.md) para más información.
  ```

- [ ] Añadir badges de estado y cobertura

#### 10.4. Verificar documentación completa

- [ ] Verificar que todos los archivos de documentación están creados:
  - `docs/testing-guide.md` - Guía completa de testing
  - `docs/browser-testing-troubleshooting.md` - Troubleshooting (actualizar si existe)
  - `.github/workflows/tests.yml` - Workflow de CI/CD

- [ ] Verificar que la documentación es clara y completa
- [ ] Verificar que los ejemplos de código funcionan

---

## Estructura de Archivos a Crear/Modificar

### Archivos Nuevos

1. **`.github/workflows/tests.yml`**
   - Workflow completo de GitHub Actions para CI/CD
   - Configuración de tests unitarios, funcionales y browser tests
   - Configuración de caché y paralelización
   - Configuración de reportes y cobertura

2. **`docs/testing-guide.md`**
   - Guía completa de ejecución de tests
   - Requisitos del sistema
   - Comandos principales
   - Troubleshooting común
   - Mejores prácticas
   - Estrategia de testing

### Archivos a Modificar

1. **`phpunit.xml`**
   - Añadir testsuite para Browser tests (si no existe)

2. **`README.md`**
   - Añadir sección de testing
   - Añadir badges de estado y cobertura

3. **`docs/browser-testing-troubleshooting.md`** (si existe)
   - Actualizar con problemas comunes de CI/CD

---

## Criterios de Éxito

### ✅ Configuración de CI/CD

- [ ] Workflow de GitHub Actions configurado y funcionando
- [ ] Tests se ejecutan automáticamente en cada push y pull request
- [ ] Browser tests se ejecutan correctamente en CI
- [ ] Caché de dependencias funciona correctamente
- [ ] Tests se ejecutan en paralelo cuando es posible

### ✅ Reportes y Cobertura

- [ ] Reportes de cobertura se generan correctamente
- [ ] Umbral mínimo de cobertura configurado (80%+)
- [ ] Artifacts (capturas de pantalla, reportes) se publican correctamente
- [ ] Badges de estado funcionan correctamente

### ✅ Documentación

- [ ] Guía completa de testing creada
- [ ] Troubleshooting común documentado
- [ ] Mejores prácticas documentadas
- [ ] Estrategia de testing documentada
- [ ] README actualizado con información de testing

### ✅ Integración

- [ ] Todos los tests (unitarios, funcionales, browser) se ejecutan juntos correctamente
- [ ] Cobertura combinada funciona correctamente
- [ ] Tiempos de ejecución son aceptables (< 20 minutos total)

---

## Notas Importantes

1. **Tiempo de Ejecución**: Los browser tests son significativamente más lentos que los tests funcionales. Se recomienda ejecutarlos en paralelo cuando sea posible.

2. **Caché**: El uso de caché de dependencias puede reducir significativamente el tiempo de ejecución en CI. Se recomienda configurar caché para Composer, npm y Playwright.

3. **Modo Headless**: Los browser tests deben ejecutarse en modo headless en CI para mejor rendimiento. El modo headed solo debe usarse en desarrollo local.

4. **Cobertura**: El objetivo de cobertura es 80%+, pero no se debe obsesionar con 100%. Es más importante tener tests de calidad que alta cobertura.

5. **Mantenimiento**: La suite de tests debe mantenerse actualizada. Cuando se añaden nuevas funcionalidades, se deben añadir tests correspondientes.

6. **Documentación**: La documentación debe mantenerse actualizada. Cuando se cambia la configuración de tests o CI/CD, se debe actualizar la documentación.

---

## Referencias

- [Pest Documentation](https://pestphp.com/docs)
- [Pest Browser Testing](https://pestphp.com/docs/plugins/browser)
- [Playwright Documentation](https://playwright.dev/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan detallado completado - Pendiente de implementación
