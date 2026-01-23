# Plan de Trabajo - Paso 3.11.1: Configuración de Tests de Navegador

## Objetivo

Configurar el entorno de testing de navegador usando Pest v4 para permitir la ejecución de tests que simulan el comportamiento real del usuario en la aplicación, detectando problemas que solo aparecen en el renderizado completo (lazy loading, JavaScript, CSS, interacciones).

---

## Estado Actual

### Análisis del Entorno

#### ✅ Ya Implementado

1. **Pest v4 Instalado**:
   - `pestphp/pest: ^4.1` en `composer.json`
   - `pestphp/pest-plugin-laravel: ^4.0` instalado
   - Configuración base en `tests/Pest.php`

2. **Configuración Base de Tests**:
   - `tests/Pest.php` configurado con `Tests\TestCase::class`
   - `RefreshDatabase` trait configurado para tests Feature
   - Helpers personalizados definidos

3. **Tests Funcionales Existentes**:
   - Tests de componentes Livewire
   - Tests de modelos
   - Tests de políticas y autorización
   - Tests de optimización de queries

#### ⚠️ Pendiente de Implementar

1. **Plugin de Browser Testing**:
   - `pest-plugin-browser` no está instalado
   - Configuración de browser tests no existe

2. **Estructura de Directorios**:
   - `tests/Browser/` no existe
   - No hay tests de navegador implementados

3. **Configuración de Browser Tests**:
   - No hay configuración específica para browser tests en `Pest.php`
   - No hay configuración de Playwright

4. **Documentación**:
   - No hay guía de configuración de browser tests
   - No hay documentación de requisitos del sistema

---

## Plan de Trabajo

### Fase 1: Verificación y Preparación del Entorno

**Objetivo**: Verificar que Pest v4 está correctamente instalado y preparar el entorno para browser testing.

#### 1.1. Verificar Instalación de Pest v4

- [ ] Verificar versión de Pest instalada:
  ```bash
  ./vendor/bin/pest --version
  ```
  Debe mostrar versión 4.x

- [ ] Verificar que `pestphp/pest-plugin-laravel` está instalado:
  ```bash
  composer show pestphp/pest-plugin-laravel
  ```

- [ ] Verificar configuración actual en `tests/Pest.php`:
  - Confirmar que extiende `Tests\TestCase::class`
  - Confirmar que usa `RefreshDatabase` para Feature tests
  - Verificar helpers personalizados existentes

#### 1.2. Verificar Requisitos del Sistema

- [ ] Verificar versión de PHP (requiere PHP 8.3+):
  ```bash
  php -v
  ```

- [ ] Verificar que Node.js está instalado (requerido para Playwright):
  ```bash
  node -v
  npm -v
  ```

- [ ] Verificar espacio en disco disponible (Playwright requiere ~500MB)

---

### Fase 2: Instalación del Plugin de Browser Testing

**Objetivo**: Instalar y configurar el plugin de browser testing de Pest.

#### 2.1. Instalar pest-plugin-browser

- [ ] Instalar el plugin de browser testing:
  ```bash
  composer require pestphp/pest-plugin-browser --dev
  ```

- [ ] Verificar instalación:
  ```bash
  composer show pestphp/pest-plugin-browser
  ```

#### 2.2. Instalar Playwright y Dependencias

- [ ] Instalar Playwright browsers:
  ```bash
  npx playwright install --with-deps
  ```

- [ ] Verificar instalación de Playwright:
  ```bash
  npx playwright --version
  ```

- [ ] (Opcional) Instalar solo Chrome si se prefiere:
  ```bash
  npx playwright install chromium
  ```

**Nota**: La instalación de Playwright puede tardar varios minutos y requiere conexión a internet.

---

### Fase 3: Configuración de Pest para Browser Tests

**Objetivo**: Configurar Pest para que reconozca y ejecute browser tests correctamente.

#### 3.1. Actualizar tests/Pest.php

- [ ] Añadir configuración para browser tests en `tests/Pest.php`:

  ```php
  // Configuración para Browser Tests
  pest()->extend(Tests\TestCase::class)
      ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
      ->in('Browser');
  ```

- [ ] (Opcional) Configurar modo headed por defecto para desarrollo:
  ```php
  // Solo en desarrollo local, no en CI
  if (! env('CI')) {
      pest()->browser()->headed();
  }
  ```

- [ ] Añadir helpers específicos para browser tests si es necesario

#### 3.2. Verificar TestCase.php

- [ ] Verificar que `tests/TestCase.php` extiende correctamente:
  ```php
  use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
  
  abstract class TestCase extends BaseTestCase
  {
      use CreatesApplication;
  }
  ```

- [ ] Asegurar que `CreatesApplication` trait está disponible

---

### Fase 4: Crear Estructura de Directorios

**Objetivo**: Crear la estructura de directorios para organizar los browser tests.

#### 4.1. Crear Directorio Principal

- [ ] Crear directorio `tests/Browser/`:
  ```bash
  mkdir -p tests/Browser
  ```

#### 4.2. Crear Subdirectorios Organizados

- [ ] Crear estructura de subdirectorios:
  ```bash
  mkdir -p tests/Browser/Public
  mkdir -p tests/Browser/Auth
  mkdir -p tests/Browser/Admin
  ```

**Estructura propuesta**:
```
tests/
├── Browser/
│   ├── Public/          # Tests de páginas públicas
│   │   ├── HomeTest.php
│   │   ├── ProgramsTest.php
│   │   ├── CallsTest.php
│   │   └── NewsTest.php
│   ├── Auth/            # Tests de autenticación
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── PasswordResetTest.php
│   └── Admin/           # Tests de administración (si aplica)
│       └── DashboardTest.php
├── Feature/             # Tests funcionales existentes
└── Unit/                # Tests unitarios existentes
```

#### 4.3. Crear Archivo .gitkeep (si es necesario)

- [ ] Asegurar que los directorios se versionan en git:
  ```bash
  touch tests/Browser/.gitkeep
  touch tests/Browser/Public/.gitkeep
  touch tests/Browser/Auth/.gitkeep
  ```

---

### Fase 5: Configurar Base de Datos de Testing

**Objetivo**: Asegurar que la base de datos de testing está correctamente configurada para browser tests.

#### 5.1. Verificar Configuración de Base de Datos

- [ ] Verificar `phpunit.xml` o `pest.xml` para configuración de BD:
  - Confirmar que usa SQLite en memoria o archivo de testing
  - Verificar que `DB_CONNECTION` está configurado correctamente

- [ ] Verificar que `RefreshDatabase` funciona correctamente:
  - Los browser tests deben usar la misma configuración que los Feature tests

#### 5.2. Configurar Factories para Browser Tests

- [ ] Verificar que todas las factories necesarias están disponibles:
  - `UserFactory`
  - `ProgramFactory`
  - `CallFactory`
  - `NewsPostFactory`
  - `AcademicYearFactory`
  - `ErasmusEventFactory`
  - `DocumentFactory`
  - `DocumentCategoryFactory`

- [ ] Verificar que las factories tienen estados apropiados:
  - Estados para datos públicos (published, active, etc.)
  - Estados para datos de administración

#### 5.3. Crear Helpers para Datos de Prueba

- [ ] Crear helper para datos de prueba comunes en `tests/Browser/Helpers.php`:

  ```php
  <?php

  namespace Tests\Browser\Helpers;

  use App\Models\Program;
  use App\Models\Call;
  use App\Models\NewsPost;
  use App\Models\AcademicYear;
  use App\Models\User;

  function createPublicTestData(): array
  {
      $program = Program::factory()->create(['is_active' => true]);
      $academicYear = AcademicYear::factory()->create();
      $call = Call::factory()->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'status' => 'abierta',
          'published_at' => now(),
      ]);
      $news = NewsPost::factory()->create([
          'program_id' => $program->id,
          'status' => 'publicado',
          'published_at' => now(),
      ]);

      return [
          'program' => $program,
          'academicYear' => $academicYear,
          'call' => $call,
          'news' => $news,
      ];
  }
  ```

- [ ] (Opcional) Crear seeders específicos para browser tests si es necesario

---

### Fase 6: Crear Test de Ejemplo y Verificación

**Objetivo**: Crear un test de ejemplo simple para verificar que todo funciona correctamente.

#### 6.1. Crear Test de Ejemplo

- [ ] Crear `tests/Browser/Public/HomeTest.php` como test de ejemplo:

  ```php
  <?php

  use App\Models\Program;

  uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

  it('can visit the home page', function () {
      Program::factory()->count(3)->create(['is_active' => true]);

      $page = visit('/');

      $page->assertOk()
          ->assertSee('Erasmus+')
          ->assertNoJavascriptErrors();
  });
  ```

#### 6.2. Ejecutar Test de Verificación

- [ ] Ejecutar el test de ejemplo:
  ```bash
  ./vendor/bin/pest tests/Browser/Public/HomeTest.php
  ```

- [ ] Verificar que el test pasa correctamente

- [ ] (Opcional) Ejecutar en modo headed para ver el navegador:
  ```bash
  ./vendor/bin/pest tests/Browser/Public/HomeTest.php --headed
  ```

#### 6.3. Verificar Detección de Lazy Loading

- [ ] Modificar el test para verificar que detecta lazy loading:
  ```php
  it('detects lazy loading violations', function () {
      $program = Program::factory()->create(['is_active' => true]);
      $call = Call::factory()->create([
          'program_id' => $program->id,
          'status' => 'abierta',
          'published_at' => now(),
      ]);

      // Este test debería fallar si hay lazy loading
      $page = visit(route('programas.show', $program->slug));

      $page->assertOk()
          ->assertSee($program->name)
          ->assertNoJavascriptErrors();
  });
  ```

---

### Fase 7: Documentación

**Objetivo**: Documentar la configuración y uso de browser tests.

#### 7.1. Crear Documentación de Configuración

- [ ] Crear `docs/browser-testing-setup.md` con:
  - Requisitos del sistema
  - Pasos de instalación
  - Configuración realizada
  - Comandos útiles

**Contenido sugerido**:

```markdown
# Configuración de Browser Testing

## Requisitos

- PHP 8.3+
- Node.js (LTS recomendado)
- Composer
- Pest v4
- Playwright (se instala automáticamente)

## Instalación

1. Instalar plugin de browser testing:
   ```bash
   composer require pestphp/pest-plugin-browser --dev
   ```

2. Instalar Playwright:
   ```bash
   npx playwright install --with-deps
   ```

## Estructura de Directorios

- `tests/Browser/Public/` - Tests de páginas públicas
- `tests/Browser/Auth/` - Tests de autenticación
- `tests/Browser/Admin/` - Tests de administración

## Comandos Útiles

- Ejecutar todos los browser tests:
  ```bash
  ./vendor/bin/pest tests/Browser
  ```

- Ejecutar en modo headed (ver navegador):
  ```bash
  ./vendor/bin/pest tests/Browser --headed
  ```

- Ejecutar con debug (pausa en errores):
  ```bash
  ./vendor/bin/pest tests/Browser --debug
  ```

- Tomar screenshot:
  ```php
  $page->screenshot();
  ```
```

#### 7.2. Actualizar README Principal

- [ ] Añadir sección de Browser Testing en `README.md`:
  - Referencia a la documentación
  - Comandos básicos
  - Enlace a la guía completa

#### 7.3. Crear Guía de Troubleshooting

- [ ] Crear sección de troubleshooting en la documentación:
  - Problemas comunes
  - Soluciones
  - Errores frecuentes

**Problemas comunes**:

1. **Playwright no encuentra el navegador**:
   - Solución: Ejecutar `npx playwright install --with-deps`

2. **Tests fallan en CI pero pasan localmente**:
   - Verificar que Playwright está instalado en CI
   - Verificar que se ejecuta `npx playwright install --with-deps` en CI

3. **Errores de permisos**:
   - Verificar permisos de ejecución
   - En Linux/Mac: `chmod +x vendor/bin/pest`

---

### Fase 8: Integración con CI/CD (Preparación)

**Objetivo**: Preparar la configuración para integración futura con CI/CD.

#### 8.1. Verificar Configuración de CI Existente

- [ ] Revisar configuración de CI/CD actual (GitHub Actions, GitLab CI, etc.)

- [ ] Identificar dónde añadir pasos para browser tests

#### 8.2. Documentar Requisitos de CI

- [ ] Documentar pasos necesarios en CI:
  - Instalación de Node.js
  - Instalación de dependencias npm
  - Instalación de Playwright browsers
  - Ejecución de browser tests

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

## Verificación Final

### Checklist de Completitud

- [ ] Pest v4 verificado y funcionando
- [ ] `pest-plugin-browser` instalado
- [ ] Playwright instalado y funcionando
- [ ] `tests/Pest.php` configurado para browser tests
- [ ] Estructura de directorios creada
- [ ] Test de ejemplo creado y pasando
- [ ] Base de datos configurada correctamente
- [ ] Factories verificadas
- [ ] Documentación creada
- [ ] README actualizado

### Pruebas de Verificación

- [ ] Ejecutar test de ejemplo sin errores
- [ ] Verificar que detecta lazy loading cuando existe
- [ ] Verificar que funciona en modo headed
- [ ] Verificar que funciona en modo headless (CI)
- [ ] Verificar que los screenshots funcionan
- [ ] Verificar que el debug funciona

---

## Notas Importantes

1. **Rendimiento**: Los browser tests son más lentos que los tests funcionales. Se recomienda ejecutarlos solo cuando sea necesario durante el desarrollo.

2. **Paralelización**: Pest v4 soporta ejecución en paralelo con `--parallel`. Útil para suites grandes de browser tests.

3. **Modo Headed vs Headless**:
   - **Headed**: Abre el navegador visible (útil para debugging)
   - **Headless**: Ejecuta sin interfaz (más rápido, ideal para CI)

4. **Screenshots**: Útiles para debugging y documentación visual. Se guardan en `tests/Browser/screenshots/` por defecto.

5. **Lazy Loading Detection**: Los browser tests detectan automáticamente problemas de lazy loading porque renderizan completamente la vista, a diferencia de `Livewire::test()`.

---

## Próximos Pasos

Una vez completada esta configuración, el siguiente paso será:

- **Paso 3.11.2**: Implementar tests de páginas públicas críticas
- Crear tests para Home, Programas, Convocatorias, Noticias
- Enfocarse en detectar problemas de lazy loading
- Verificar renderizado completo y relaciones cargadas

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan listo para implementación
