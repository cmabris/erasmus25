# Guía de Troubleshooting - Browser Testing

Esta guía ayuda a resolver problemas comunes al trabajar con browser tests en Pest v4.

## Problemas Comunes

### 1. Playwright no encuentra el navegador

**Error**:
```
Pest\Browser\Exceptions\PlaywrightNotInstalledException
Playwright is not installed. Please run [npm install playwright && npx playwright install] in the root directory of your project.
```

**Solución**:

1. Verificar que Playwright está instalado como dependencia npm:
   ```bash
   npm install playwright@latest --save-dev
   ```

2. Instalar los navegadores de Playwright:
   ```bash
   npx playwright install --with-deps
   ```

3. Verificar la instalación:
   ```bash
   npx playwright --version
   ```

**Nota**: Los navegadores se instalan en `~/Library/Caches/ms-playwright/` (macOS) o `~/.cache/ms-playwright/` (Linux).

---

### 2. Tests fallan en CI pero pasan localmente

**Causas comunes**:
- Playwright no está instalado en CI
- Los navegadores de Playwright no están instalados
- Variables de entorno diferentes
- Timeouts muy cortos

**Solución**:

1. Asegurar que Node.js está instalado en CI
2. Instalar dependencias npm:
   ```bash
   npm ci
   ```
3. Instalar navegadores de Playwright:
   ```bash
   npx playwright install --with-deps
   ```
4. Ejecutar tests con flag `--ci`:
   ```bash
   ./vendor/bin/pest tests/Browser --ci
   ```

**Ejemplo para GitHub Actions**:

```yaml
- uses: actions/setup-node@v4
  with:
    node-version: lts/*

- name: Install dependencies
  run: npm ci

- name: Install Playwright Browsers
  run: npx playwright install --with-deps

- name: Run Browser Tests
  run: ./vendor/bin/pest tests/Browser --ci
```

---

### 3. Errores de permisos

**Error**:
```
Permission denied: ./vendor/bin/pest
```

**Solución**:

En Linux/Mac:
```bash
chmod +x vendor/bin/pest
```

En Windows, asegúrate de que el archivo no esté bloqueado.

---

### 4. Tests son muy lentos

**Causas**:
- Ejecución secuencial en lugar de paralela
- Timeouts muy largos
- Muchos tests ejecutándose

**Soluciones**:

1. Ejecutar en paralelo:
   ```bash
   ./vendor/bin/pest tests/Browser --parallel
   ```

2. Ajustar timeout en `tests/Pest.php`:
   ```php
   pest()->browser()->timeout(5000); // 5 segundos en lugar de 10
   ```

3. Ejecutar solo tests específicos durante desarrollo:
   ```bash
   ./vendor/bin/pest tests/Browser/Public/HomeTest.php
   ```

---

### 5. Helper functions no encontradas

**Error**:
```
Call to undefined function Tests\Browser\Helpers\createPublicTestData()
```

**Solución**:

1. Verificar que el archivo `tests/Browser/Helpers.php` existe
2. Verificar que está en el autoload de composer:
   ```json
   "autoload-dev": {
       "files": [
           "tests/Browser/Helpers.php"
       ]
   }
   ```
3. Regenerar autoload:
   ```bash
   composer dump-autoload
   ```

---

### 6. Errores de JavaScript en tests

**Error**:
```
Failed asserting that no JavaScript errors occurred.
```

**Solución**:

1. Ejecutar en modo headed para ver el error:
   ```bash
   ./vendor/bin/pest tests/Browser --headed
   ```

2. Revisar la consola del navegador para ver el error específico

3. Verificar que todos los assets están compilados:
   ```bash
   npm run build
   ```

4. Verificar que no hay errores en el código JavaScript de la aplicación

---

### 7. Tests fallan por lazy loading

**Síntoma**: Tests fallan con errores relacionados con relaciones no cargadas (ej: `Trying to get property 'name' of null`)

**Solución**:

Los browser tests detectan automáticamente problemas de lazy loading porque renderizan completamente la vista. Si un test falla por lazy loading:

1. Verificar que el componente usa `with()` o `load()` para cargar relaciones:
   ```php
   $programs = Program::with('academicYear')->get();
   ```

2. Verificar que las relaciones están definidas correctamente en el modelo

3. Usar eager loading en lugar de lazy loading:
   ```php
   // ❌ Lazy loading (malo)
   $call->program->name;
   
   // ✅ Eager loading (bueno)
   Call::with('program')->get();
   ```

---

### 8. Screenshots no se guardan

**Problema**: `$page->screenshot()` no guarda el archivo

**Solución**:

1. Verificar que el directorio existe:
   ```bash
   mkdir -p tests/Browser/Screenshots
   ```

2. Verificar permisos de escritura:
   ```bash
   chmod -R 755 tests/Browser/Screenshots
   ```

3. Añadir a `.gitignore`:
   ```
   tests/Browser/Screenshots/
   ```

---

### 9. Tests fallan intermitentemente

**Causas comunes**:
- Race conditions
- Timeouts muy cortos
- Caché no limpiado
- Datos compartidos entre tests

**Soluciones**:

1. Aumentar timeout:
   ```php
   pest()->browser()->timeout(10000); // 10 segundos
   ```

2. Limpiar caché antes de cada test:
   ```php
   beforeEach(function () {
       Cache::flush();
   });
   ```

3. Usar `RefreshDatabase` (ya configurado):
   ```php
   pest()->extend(Tests\TestCase::class)
       ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
       ->in('Browser');
   ```

4. Asegurar que cada test es independiente (no comparte estado)

---

### 10. Modo headed no funciona

**Problema**: El navegador no se abre aunque se use `--headed`

**Solución**:

1. Verificar que no estás en CI (el modo headed está deshabilitado en CI):
   ```php
   if (! env('CI')) {
       pest()->browser()->headed();
   }
   ```

2. Ejecutar explícitamente con `--headed`:
   ```bash
   ./vendor/bin/pest tests/Browser --headed
   ```

3. Verificar que tienes permisos para abrir ventanas (especialmente en servidores remotos)

---

## Errores Frecuentes

### Error: "Call to undefined method assertOk()"

**Causa**: `assertOk()` no existe en Pest Browser.

**Solución**: Usar `assertUrlIs()` o simplemente omitir la verificación de status code (Pest Browser verifica automáticamente que la página carga correctamente).

```php
// ❌ Incorrecto
$page->assertOk();

// ✅ Correcto
$page->assertSee('Erasmus+');
// O
$page->assertUrlIs('/');
```

---

### Error: "Element not found"

**Causa**: El elemento no existe o no es visible cuando se intenta interactuar.

**Solución**:

1. Esperar a que el elemento aparezca:
   ```php
   $page->wait('selector')->click('selector');
   ```

2. Verificar que el elemento es visible:
   ```php
   $page->assertVisible('selector');
   ```

3. Usar selector más específico:
   ```php
   // ❌ Puede ser ambiguo
   $page->click('Button');
   
   // ✅ Más específico
   $page->click('.btn-primary');
   $page->click('#submit-button');
   $page->click('@data-test-id');
   ```

---

### Error: "Timeout waiting for element"

**Causa**: El elemento tarda más en aparecer que el timeout configurado.

**Solución**:

1. Aumentar timeout global:
   ```php
   pest()->browser()->timeout(10000); // 10 segundos
   ```

2. Esperar explícitamente:
   ```php
   $page->wait('selector', 10000)->click('selector');
   ```

3. Verificar que el elemento realmente aparece (puede ser un problema de la aplicación)

---

## Recursos Adicionales

- [Documentación oficial de Pest Browser](https://pestphp.com/docs/browser-testing)
- [Documentación de Playwright](https://playwright.dev)
- [Documentación de configuración](docs/browser-testing-setup.md)

---

**Última actualización**: Enero 2026
