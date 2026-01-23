# Paso 53: Configuración de Tests de Navegador (Paso 3.11.1 - Fase 1)

**Fecha**: 23 de Enero 2026  
**Objetivo**: Configurar el entorno de testing de navegador usando Pest v4 para permitir la ejecución de tests que simulan el comportamiento real del usuario en la aplicación.

---

## Resumen Ejecutivo

Se ha completado exitosamente la **Fase 1 del Paso 3.11.1: Configuración de Tests de Navegador**, que incluye 7 fases principales:

1. ✅ **Fase 1**: Verificación y Preparación del Entorno
2. ✅ **Fase 2**: Instalación del Plugin de Browser Testing
3. ✅ **Fase 3**: Configuración de Pest para Browser Tests
4. ✅ **Fase 4**: Crear Estructura de Directorios
5. ✅ **Fase 5**: Configurar Base de Datos de Testing
6. ✅ **Fase 6**: Crear Test de Ejemplo y Verificación
7. ✅ **Fase 7**: Documentación

**Resultado Final**: Entorno completamente configurado y funcionando. Tests de ejemplo pasando (3 tests, 8 assertions). Documentación completa creada.

---

## Prompts y Resultados

### Prompt 1: Inicio del Desarrollo de la Fase 1

**Prompt del Usuario**:
```
En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que actúa de índice y otro llamado @docs/planificacion_pasos.md que contiene las tareas a desarrollar en orden.
Estamos en situación de comenzar con el desarrollo del paso 3.11.1 del cual ya se ha hecho una planificación de las tareas que puedes encontrar en @docs/pasos/paso-3.11.1-plan.md 
Según ese plan, comenzamos con el desarrollo de la Fase 1
```

**Acciones Realizadas**:
1. Lectura del plan de trabajo (`paso-3.11.1-plan.md`)
2. Lectura de la planificación general (`planificacion_pasos.md`)
3. Creación de lista de tareas inicial
4. Inicio de la Fase 1: Verificación y Preparación del Entorno

**Resultados**:
- Plan de trabajo revisado y comprendido
- Tareas identificadas para la Fase 1
- Inicio del proceso de verificación

---

### Prompt 2: Verificación del Entorno (Fase 1)

**Acciones Realizadas**:
1. Verificación de versión de Pest v4: `./vendor/bin/pest --version`
2. Verificación de plugin Laravel: `composer show pestphp/pest-plugin-laravel`
3. Verificación de PHP: `php -v`
4. Verificación de Node.js y npm: `node -v && npm -v`
5. Verificación de espacio en disco
6. Revisión de `tests/Pest.php` y `tests/TestCase.php`

**Resultados**:
- ✅ Pest v4.1.6 instalado y funcionando
- ✅ `pestphp/pest-plugin-laravel` v4.0.0 instalado
- ✅ PHP 8.3.30 (cumple requisito 8.3+)
- ✅ Node.js v21.7.3 y npm 10.5.0 instalados
- ✅ 28Gi de espacio disponible (suficiente)
- ✅ `tests/Pest.php` configurado correctamente
- ✅ `tests/TestCase.php` verificado y correcto

**Documento Creado**: `docs/pasos/paso-3.11.1-fase1-verificacion.md`

---

### Prompt 3: Continuar con Fase 2

**Prompt del Usuario**:
```
Vayamos a la Fase 2
```

**Acciones Realizadas**:
1. Instalación de `pestphp/pest-plugin-browser`: `composer require pestphp/pest-plugin-browser --dev`
2. Verificación de instalación del plugin
3. Instalación de Playwright: `npx playwright install --with-deps`
4. Verificación de instalación de Playwright: `npx playwright --version`

**Resultados**:
- ✅ Plugin `pestphp/pest-plugin-browser` v4.1.1 instalado
- ✅ 21 paquetes dependientes instalados correctamente
- ✅ Playwright v1.58.0 instalado
- ✅ 4 navegadores instalados:
  - Chrome for Testing 145.0.7632.6 (162.3 MiB)
  - Chrome Headless Shell 145.0.7632.6 (91.1 MiB)
  - Firefox 146.0.1 (93.2 MiB)
  - WebKit 26.0 (72.6 MiB)
- ✅ Total descargado: ~419.2 MiB

**Documento Creado**: `docs/pasos/paso-3.11.1-fase2-instalacion.md`

---

### Prompt 4: Continuar con Fase 3

**Prompt del Usuario**:
```
Sí, pasemos a la Fase 3
```

**Acciones Realizadas**:
1. Actualización de `tests/Pest.php` con configuración para browser tests
2. Configuración de modo headed para desarrollo local
3. Verificación de `tests/TestCase.php`

**Resultados**:
- ✅ Configuración añadida en `tests/Pest.php`:
  ```php
  pest()->extend(Tests\TestCase::class)
      ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
      ->in('Browser');
  
  if (! env('CI')) {
      pest()->browser()->headed();
  }
  ```
- ✅ Modo headed configurado solo para desarrollo (no CI)
- ✅ `tests/TestCase.php` verificado y correcto

**Documento Creado**: `docs/pasos/paso-3.11.1-fase3-configuracion.md`

---

### Prompt 5: Continuar con Fase 4

**Prompt del Usuario**:
```
Sí, ahora la Fase 4
```

**Acciones Realizadas**:
1. Creación de directorio principal: `tests/Browser/`
2. Creación de subdirectorios:
   - `tests/Browser/Public/`
   - `tests/Browser/Auth/`
   - `tests/Browser/Admin/`
3. Creación de archivos `.gitkeep` en todos los directorios

**Resultados**:
- ✅ Estructura de directorios creada:
  ```
  tests/
  ├── Browser/
  │   ├── .gitkeep
  │   ├── Public/
  │   │   └── .gitkeep
  │   ├── Auth/
  │   │   └── .gitkeep
  │   └── Admin/
  │       └── .gitkeep
  ```
- ✅ Todos los directorios versionados en git

**Documento Creado**: `docs/pasos/paso-3.11.1-fase4-estructura.md`

---

### Prompt 6: Continuar con Fase 5

**Prompt del Usuario**:
```
Sigamos con la Fase 5
```

**Acciones Realizadas**:
1. Verificación de configuración de BD en `phpunit.xml`
2. Verificación de `RefreshDatabase` configurado
3. Verificación de todas las factories necesarias
4. Creación de helper `tests/Browser/Helpers.php` con:
   - `createPublicTestData()` - Crea datos públicos completos
   - `createAuthenticatedUser()` - Crea usuario autenticado

**Resultados**:
- ✅ SQLite en memoria configurado en `phpunit.xml`
- ✅ `RefreshDatabase` configurado para browser tests
- ✅ Todas las factories verificadas y disponibles:
  - UserFactory, ProgramFactory, CallFactory
  - NewsPostFactory, AcademicYearFactory
  - ErasmusEventFactory, DocumentFactory, DocumentCategoryFactory
- ✅ Helper `createPublicTestData()` creado y funcionando
- ✅ Helper `createAuthenticatedUser()` creado y funcionando
- ✅ Helper añadido al autoload de composer

**Documento Creado**: `docs/pasos/paso-3.11.1-fase5-bd-testing.md`

---

### Prompt 7: Continuar con Fase 6

**Prompt del Usuario**:
```
Sí, ahora la Fase 6
```

**Acciones Realizadas**:
1. Creación de test de ejemplo: `tests/Browser/Public/HomeTest.php`
2. Instalación de Playwright como dependencia npm: `npm install playwright@latest --save-dev`
3. Añadido helper al autoload de composer
4. Ejecución del test de ejemplo

**Resultados**:
- ✅ Test de ejemplo creado con 3 casos de prueba:
  - `it('can visit the home page')`
  - `it('displays active programs on home page')`
  - `it('displays public content using helper')`
- ✅ Playwright instalado como dependencia npm
- ✅ Helper añadido al autoload: `composer.json` actualizado
- ✅ **Tests ejecutados exitosamente**: 3 tests pasados (8 assertions)
- ✅ Duración: 2.05 segundos
- ✅ Sin errores de JavaScript

**Salida del Test**:
```
   PASS  Tests\Browser\Public\HomeTest
  ✓ it can visit the home page                                           0.77s  
  ✓ it displays active programs on home page                             0.45s  
  ✓ it displays public content using helper                              0.44s  

  Tests:    3 passed (8 assertions)
  Duration: 2.05s
```

**Documento Creado**: `docs/pasos/paso-3.11.1-fase6-test-ejemplo.md`

---

### Prompt 8: Continuar con Fase 7

**Prompt del Usuario**:
```
Vayamos a la Fase 7
```

**Acciones Realizadas**:
1. Creación de documentación de configuración: `docs/browser-testing-setup.md`
2. Actualización del README principal con sección de Browser Testing
3. Creación de guía de troubleshooting: `docs/browser-testing-troubleshooting.md`

**Resultados**:
- ✅ Documentación completa creada (`docs/browser-testing-setup.md`):
  - Requisitos del sistema
  - Instalación paso a paso
  - Configuración realizada
  - Comandos útiles
  - Ejemplos de uso
  - Assertions disponibles
  - Integración con CI/CD
- ✅ README actualizado:
  - Sección de Browser Testing añadida
  - Comandos básicos documentados
  - Enlace a documentación completa
  - Añadido a tecnologías de testing
- ✅ Guía de troubleshooting creada (`docs/browser-testing-troubleshooting.md`):
  - 10 problemas comunes con soluciones
  - 3 errores frecuentes
  - Recursos adicionales

**Documento Creado**: `docs/pasos/paso-3.11.1-fase7-documentacion.md`

---

## Archivos Creados/Modificados

### Archivos de Configuración

1. **`tests/Pest.php`** (modificado)
   - Añadida configuración para browser tests
   - Configurado modo headed para desarrollo

2. **`composer.json`** (modificado)
   - Añadido `pestphp/pest-plugin-browser` a dependencias dev
   - Añadido `tests/Browser/Helpers.php` al autoload-dev

3. **`package.json`** (modificado)
   - Añadido `playwright` como dependencia dev

### Archivos de Tests

4. **`tests/Browser/Helpers.php`** (nuevo)
   - Helper `createPublicTestData()`
   - Helper `createAuthenticatedUser()`

5. **`tests/Browser/Public/HomeTest.php`** (nuevo)
   - 3 tests de ejemplo funcionando

### Estructura de Directorios

6. **`tests/Browser/`** (nuevo)
   - Directorio principal para browser tests

7. **`tests/Browser/Public/`** (nuevo)
   - Tests de páginas públicas

8. **`tests/Browser/Auth/`** (nuevo)
   - Tests de autenticación

9. **`tests/Browser/Admin/`** (nuevo)
   - Tests de administración

### Documentación

10. **`docs/browser-testing-setup.md`** (nuevo)
    - Guía completa de configuración y uso

11. **`docs/browser-testing-troubleshooting.md`** (nuevo)
    - Guía de resolución de problemas

12. **`README.md`** (modificado)
    - Sección de Browser Testing añadida

13. **`docs/pasos/paso-3.11.1-fase1-verificacion.md`** (nuevo)
    - Resultados de la Fase 1

14. **`docs/pasos/paso-3.11.1-fase2-instalacion.md`** (nuevo)
    - Resultados de la Fase 2

15. **`docs/pasos/paso-3.11.1-fase3-configuracion.md`** (nuevo)
    - Resultados de la Fase 3

16. **`docs/pasos/paso-3.11.1-fase4-estructura.md`** (nuevo)
    - Resultados de la Fase 4

17. **`docs/pasos/paso-3.11.1-fase5-bd-testing.md`** (nuevo)
    - Resultados de la Fase 5

18. **`docs/pasos/paso-3.11.1-fase6-test-ejemplo.md`** (nuevo)
    - Resultados de la Fase 6

19. **`docs/pasos/paso-3.11.1-fase7-documentacion.md`** (nuevo)
    - Resultados de la Fase 7

---

## Estadísticas Finales

### Instalaciones Realizadas

- **Plugin Pest Browser**: v4.1.1
- **Playwright**: v1.58.0
- **Navegadores instalados**: 4 (Chrome, Chrome Headless, Firefox, WebKit)
- **Tamaño total**: ~419.2 MiB

### Tests Creados

- **Tests de ejemplo**: 3
- **Assertions**: 8
- **Estado**: ✅ Todos pasando
- **Duración**: 2.05 segundos

### Documentación Creada

- **Documentos principales**: 2
- **Documentos de fases**: 7
- **Total de líneas**: ~2,500+ líneas de documentación

### Estructura Creada

- **Directorios**: 4
- **Archivos de test**: 1
- **Helpers**: 2 funciones

---

## Comandos Ejecutados

### Verificación
```bash
./vendor/bin/pest --version
composer show pestphp/pest-plugin-laravel
php -v
node -v && npm -v
df -h .
```

### Instalación
```bash
composer require pestphp/pest-plugin-browser --dev
npx playwright install --with-deps
npm install playwright@latest --save-dev
npx playwright --version
```

### Configuración
```bash
mkdir -p tests/Browser/Public tests/Browser/Auth tests/Browser/Admin
touch tests/Browser/.gitkeep tests/Browser/Public/.gitkeep tests/Browser/Auth/.gitkeep tests/Browser/Admin/.gitkeep
composer dump-autoload
```

### Testing
```bash
./vendor/bin/pest tests/Browser/Public/HomeTest.php
```

---

## Configuraciones Realizadas

### 1. Pest Configuration (`tests/Pest.php`)

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

### 2. Composer Autoload (`composer.json`)

```json
"autoload-dev": {
    "psr-4": {
        "Tests\\": "tests/"
    },
    "files": [
        "tests/Browser/Helpers.php"
    ]
}
```

### 3. Base de Datos (`phpunit.xml`)

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## Helpers Creados

### `createPublicTestData()`

```php
function createPublicTestData(): array
{
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->published()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
    ]);
    $news = NewsPost::factory()->published()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    return [
        'program' => $program,
        'academicYear' => $academicYear,
        'call' => $call,
        'news' => $news,
    ];
}
```

### `createAuthenticatedUser()`

```php
function createAuthenticatedUser(array $attributes = []): User
{
    return User::factory()->create($attributes);
}
```

---

## Test de Ejemplo Creado

### `tests/Browser/Public/HomeTest.php`

```php
<?php

use App\Models\Program;

it('can visit the home page', function () {
    Program::factory()->count(3)->create(['is_active' => true]);

    $page = visit('/');

    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('displays active programs on home page', function () {
    $program = Program::factory()->create([
        'name' => 'Test Active Program',
        'is_active' => true,
    ]);

    $page = visit('/');

    $page->assertSee('Test Active Program')
        ->assertNoJavascriptErrors();
});

it('displays public content using helper', function () {
    $data = createPublicTestData();

    $page = visit('/');

    $page->assertSee($data['program']->name)
        ->assertSee($data['call']->title)
        ->assertSee($data['news']->title)
        ->assertNoJavascriptErrors();
});
```

**Resultado**: ✅ 3 tests pasados (8 assertions) en 2.05 segundos

---

## Próximos Pasos

Con la Fase 1 del Paso 3.11.1 completada, el siguiente paso según la planificación es:

- **Paso 3.11.2**: Implementar tests de páginas públicas críticas
  - Test de página Home (`/`)
  - Test de listado de Programas (`/programas`)
  - Test de detalle de Programa (`/programas/{slug}`)
  - Test de listado de Convocatorias (`/convocatorias`)
  - Test de detalle de Convocatoria (`/convocatorias/{slug}`)
  - Test de listado de Noticias (`/noticias`)
  - Test de detalle de Noticia (`/noticias/{slug}`)

---

## Lecciones Aprendidas

1. **Playwright requiere instalación como dependencia npm**: Aunque se puede instalar globalmente con `npx`, Pest necesita que esté en `package.json`.

2. **Helpers deben estar en autoload**: Las funciones helper necesitan estar en el autoload de composer para ser accesibles globalmente.

3. **Modo headed útil para debugging**: Configurar modo headed solo en desarrollo facilita el debugging sin afectar CI.

4. **RefreshDatabase funciona igual**: Los browser tests usan la misma configuración de BD que los Feature tests, lo cual simplifica la configuración.

5. **Tests más lentos pero más completos**: Los browser tests son más lentos que los funcionales, pero detectan problemas que solo aparecen en el renderizado completo.

---

## Estado Final

✅ **COMPLETADO**: Fase 1 del Paso 3.11.1 - Configuración de Tests de Navegador

- ✅ Entorno verificado y preparado
- ✅ Plugin y Playwright instalados
- ✅ Pest configurado para browser tests
- ✅ Estructura de directorios creada
- ✅ Base de datos configurada
- ✅ Test de ejemplo funcionando
- ✅ Documentación completa creada

**El entorno está completamente listo para comenzar a escribir más browser tests.**

---

**Fecha de Creación**: 23 de Enero 2026  
**Duración Total**: ~1 sesión de desarrollo  
**Estado**: ✅ Completado exitosamente
