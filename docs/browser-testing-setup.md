# Configuración de Browser Testing

Esta guía describe cómo está configurado el entorno de browser testing en la aplicación usando Pest v4 y Playwright.

## Requisitos del Sistema

- **PHP 8.3+** (actualmente: PHP 8.3.30)
- **Node.js** (LTS recomendado, actualmente: v21.7.3)
- **npm** (actualmente: 10.5.0)
- **Composer**
- **Pest v4** (actualmente: v4.1.6)
- **Playwright** (se instala automáticamente, actualmente: v1.58.0)

## Instalación

### 1. Instalar Plugin de Browser Testing

```bash
composer require pestphp/pest-plugin-browser --dev
```

Esto instalará:
- `pestphp/pest-plugin-browser` (v4.1.1)
- Todas las dependencias necesarias (amphp, etc.)

### 2. Instalar Playwright

```bash
npm install playwright@latest --save-dev
npx playwright install --with-deps
```

Esto instalará:
- Playwright como dependencia npm
- Navegadores: Chrome, Firefox, WebKit
- Dependencias del sistema necesarias

**Nota**: La instalación de Playwright puede tardar varios minutos y requiere conexión a internet (~419MB de navegadores).

## Configuración Realizada

### Pest Configuration (`tests/Pest.php`)

```php
// Configuración para Browser Tests
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Browser');

// Configurar modo headed por defecto solo en desarrollo local (no en CI)
if (! env('CI')) {
    pest()->browser()->headed();
}
```

### Base de Datos de Testing

Los browser tests usan la misma configuración que los Feature tests:
- **Conexión**: SQLite en memoria (`:memory:`)
- **Trait**: `RefreshDatabase` para limpiar la BD entre tests
- **Configuración**: Definida en `phpunit.xml`

## Estructura de Directorios

```
tests/
├── Browser/
│   ├── Public/          # Tests de páginas públicas
│   │   ├── HomeTest.php
│   │   └── ...
│   ├── Auth/            # Tests de autenticación
│   │   └── ...
│   └── Admin/           # Tests de administración
│       └── ...
├── Feature/             # Tests funcionales existentes
└── Unit/                # Tests unitarios existentes
```

## Helpers Disponibles

### `createPublicTestData()`

Crea un conjunto completo de datos públicos para tests:

```php
use function Tests\Browser\Helpers\createPublicTestData;

$data = createPublicTestData();
// Retorna: ['program', 'academicYear', 'call', 'news']
```

### `createAuthenticatedUser()`

Crea un usuario autenticado para tests:

```php
use function Tests\Browser\Helpers\createAuthenticatedUser;

$user = createAuthenticatedUser(['email' => 'admin@example.com']);
```

## Comandos Útiles

### Ejecutar Todos los Browser Tests

```bash
./vendor/bin/pest tests/Browser
```

### Ejecutar Tests Específicos

```bash
./vendor/bin/pest tests/Browser/Public/HomeTest.php
```

### Ejecutar en Modo Headed (Ver Navegador)

```bash
./vendor/bin/pest tests/Browser --headed
```

Útil para debugging durante el desarrollo. El navegador se abrirá visiblemente.

### Ejecutar con Debug (Pausa en Errores)

```bash
./vendor/bin/pest tests/Browser --debug
```

Pausa la ejecución al final de un test fallido para inspeccionar el estado.

### Ejecutar en Paralelo

```bash
./vendor/bin/pest tests/Browser --parallel
```

Acelera la ejecución ejecutando tests en paralelo.

### Ejecutar con Navegador Específico

```bash
./vendor/bin/pest tests/Browser --browser firefox
./vendor/bin/pest tests/Browser --browser safari
```

## Ejemplos de Uso

### Test Básico

```php
<?php

use App\Models\Program;

it('can visit the home page', function () {
    Program::factory()->count(3)->create(['is_active' => true]);

    $page = visit('/');

    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});
```

### Test con Helper

```php
<?php

use function Tests\Browser\Helpers\createPublicTestData;

it('displays public content', function () {
    $data = createPublicTestData();

    $page = visit('/');

    $page->assertSee($data['program']->name)
        ->assertSee($data['call']->title)
        ->assertSee($data['news']->title)
        ->assertNoJavascriptErrors();
});
```

### Test con Interacciones

```php
it('can navigate to programs page', function () {
    $page = visit('/');

    $page->click('Programas')
         ->assertUrlIs('/programas')
         ->assertSee('Programas Erasmus+');
});
```

### Tomar Screenshot

```php
$page = visit('/');
$page->screenshot('home-page.png');
```

Los screenshots se guardan en `tests/Browser/Screenshots/` (añadir a `.gitignore`).

## Assertions Disponibles

Pest Browser proporciona muchas assertions útiles:

- `assertSee($text)` - Verifica que un texto aparece
- `assertDontSee($text)` - Verifica que un texto NO aparece
- `assertNoJavascriptErrors()` - Verifica que no hay errores JS
- `assertNoConsoleLogs()` - Verifica que no hay logs en consola
- `assertUrlIs($url)` - Verifica la URL actual
- `assertSeeLink($text)` - Verifica que existe un enlace
- `assertChecked($selector)` - Verifica que un checkbox está marcado
- `assertValue($selector, $value)` - Verifica el valor de un input
- Y muchas más...

Ver la [documentación oficial de Pest Browser](https://pestphp.com/docs/browser-testing) para la lista completa.

## Modo Headed vs Headless

- **Headed**: Abre el navegador visible (útil para debugging)
- **Headless**: Ejecuta sin interfaz (más rápido, ideal para CI)

Por defecto, en desarrollo local se ejecuta en modo headed (configurado en `tests/Pest.php`). En CI se ejecuta automáticamente en modo headless.

## Integración con CI/CD

Para ejecutar browser tests en CI, asegúrate de:

1. Instalar Node.js
2. Instalar dependencias npm: `npm ci`
3. Instalar Playwright browsers: `npx playwright install --with-deps`
4. Ejecutar tests: `./vendor/bin/pest tests/Browser`

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

## Notas Importantes

1. **Rendimiento**: Los browser tests son más lentos que los tests funcionales. Se recomienda ejecutarlos solo cuando sea necesario durante el desarrollo.

2. **Paralelización**: Pest v4 soporta ejecución en paralelo con `--parallel`. Útil para suites grandes de browser tests.

3. **Screenshots**: Útiles para debugging y documentación visual. Se guardan en `tests/Browser/screenshots/` por defecto.

4. **Lazy Loading Detection**: Los browser tests detectan automáticamente problemas de lazy loading porque renderizan completamente la vista, a diferencia de `Livewire::test()`.

5. **RefreshDatabase**: Los browser tests usan el mismo trait `RefreshDatabase` que los Feature tests, por lo que la base de datos se limpia antes de cada test.

## Próximos Pasos

Una vez completada esta configuración, el siguiente paso será:
- **Paso 3.11.2**: Implementar tests de páginas públicas críticas
- Crear tests para Home, Programas, Convocatorias, Noticias
- Enfocarse en detectar problemas de lazy loading
- Verificar renderizado completo y relaciones cargadas

---

**Fecha de Creación**: Enero 2026  
**Estado**: ✅ Configuración completada y funcionando
