# Plan de Trabajo - Paso 3.11.2: Tests de Navegador de Páginas Públicas Críticas

## Objetivo

Implementar tests de navegador completos para las páginas públicas críticas de la aplicación, validando el comportamiento completo desde la perspectiva del usuario final. Estos tests detectarán problemas que solo aparecen en el renderizado completo (lazy loading, JavaScript, CSS, interacciones) y que no son detectables en tests funcionales tradicionales.

---

## Estado Actual

### ✅ Ya Implementado

1. **Configuración de Browser Tests (Paso 3.11.1)**:
   - Pest v4 con `pest-plugin-browser` instalado
   - Playwright configurado
   - Estructura de directorios `tests/Browser/` creada
   - Helper `createPublicTestData()` disponible en `tests/Browser/Helpers.php`

2. **Tests Básicos Existentes**:
   - `tests/Browser/Public/HomeTest.php` con 3 tests básicos
   - Tests verifican renderizado básico y contenido

3. **Componentes Públicos Implementados**:
   - `App\Livewire\Public\Home` - Página principal
   - `App\Livewire\Public\Programs\Index` - Listado de programas
   - `App\Livewire\Public\Programs\Show` - Detalle de programa
   - `App\Livewire\Public\Calls\Index` - Listado de convocatorias
   - `App\Livewire\Public\Calls\Show` - Detalle de convocatoria
   - `App\Livewire\Public\News\Index` - Listado de noticias
   - `App\Livewire\Public\News\Show` - Detalle de noticia

4. **Rutas Públicas Configuradas**:
   - `/` - Home
   - `/programas` - Listado de programas
   - `/programas/{slug}` - Detalle de programa
   - `/convocatorias` - Listado de convocatorias
   - `/convocatorias/{slug}` - Detalle de convocatoria
   - `/noticias` - Listado de noticias
   - `/noticias/{slug}` - Detalle de noticia

### ⚠️ Pendiente de Implementar

1. **Tests Completos de Home**:
   - Verificación de todos los elementos (programas, convocatorias, noticias, eventos)
   - Detección de lazy loading
   - Verificación de enlaces y navegación

2. **Tests de Listado de Programas**:
   - Filtros (tipo, activos)
   - Búsqueda
   - Paginación
   - Enlaces a programas individuales

3. **Tests de Detalle de Programa**:
   - Renderizado completo con relaciones
   - Convocatorias relacionadas
   - Noticias relacionadas
   - Detección de lazy loading (program, academicYear)

4. **Tests de Listado de Convocatorias**:
   - Filtros (programa, año académico, tipo, modalidad, estado)
   - Búsqueda
   - Paginación
   - Enlaces a convocatorias individuales

5. **Tests de Detalle de Convocatoria**:
   - Renderizado completo con relaciones
   - Fases
   - Resoluciones publicadas
   - Noticias relacionadas
   - Detección de lazy loading (program, academicYear, phases, resolutions)

6. **Tests de Listado de Noticias**:
   - Filtros (programa, año académico, etiquetas)
   - Búsqueda
   - Paginación

7. **Tests de Detalle de Noticia**:
   - Renderizado completo
   - Noticias relacionadas
   - Convocatorias relacionadas
   - Detección de lazy loading (program, author, tags)

---

## Plan de Trabajo

### Fase 1: Mejora y Ampliación de Tests de Home

**Objetivo**: Completar los tests de la página principal con todas las verificaciones necesarias.

#### 1.1. Ampliar HomeTest.php

**Archivo**: `tests/Browser/Public/HomeTest.php`

- [ ] **Test: Verificar renderizado completo de Home**
  - Verificar que la página carga correctamente
  - Verificar que no hay errores de JavaScript
  - Verificar que no hay errores en consola
  - Verificar estructura HTML básica (header, main, footer)

- [ ] **Test: Verificar programas activos en Home**
  - Crear 6 programas activos
  - Verificar que se muestran en la sección correspondiente
  - Verificar que se muestran máximo 6 programas
  - Verificar que los programas inactivos no se muestran
  - Verificar enlaces a detalle de programas

- [ ] **Test: Verificar convocatorias abiertas en Home**
  - Crear convocatorias con estado 'abierta' y `published_at`
  - Verificar que se muestran en la sección correspondiente
  - Verificar que se muestran máximo 4 convocatorias
  - Verificar que las convocatorias no publicadas no se muestran
  - Verificar enlaces a detalle de convocatorias
  - Verificar eager loading (program, academicYear)

- [ ] **Test: Verificar noticias recientes en Home**
  - Crear noticias con estado 'publicado' y `published_at`
  - Verificar que se muestran en la sección correspondiente
  - Verificar que se muestran máximo 3 noticias
  - Verificar que las noticias no publicadas no se muestran
  - Verificar enlaces a detalle de noticias
  - Verificar eager loading (program, author)

- [ ] **Test: Verificar eventos próximos en Home**
  - Crear eventos con fechas futuras
  - Verificar que se muestran en la sección correspondiente
  - Verificar que se muestran máximo 5 eventos
  - Verificar que los eventos pasados no se muestran
  - Verificar enlaces a detalle de eventos

- [ ] **Test: Verificar navegación desde Home**
  - Verificar enlaces del menú de navegación
  - Verificar enlaces a programas desde cards
  - Verificar enlaces a convocatorias desde cards
  - Verificar enlaces a noticias desde cards
  - Verificar enlaces a eventos desde cards

- [ ] **Test: Detectar problemas de lazy loading en Home**
  - Verificar que todas las relaciones están eager loaded
  - Verificar que no hay consultas N+1
  - Usar `assertNoJavascriptErrors()` para detectar errores de acceso a relaciones no cargadas

- [ ] **Test: Verificar estado vacío en Home**
  - Verificar que cuando no hay datos, se muestran mensajes apropiados
  - Verificar que no hay errores cuando no hay contenido

#### 1.2. Crear Helper para Datos de Home

**Archivo**: `tests/Browser/Helpers.php` (ampliar)

- [ ] Añadir función `createHomeTestData()`:
  ```php
  function createHomeTestData(): array
  {
      // Crear programas activos
      $programs = Program::factory()->count(6)->create(['is_active' => true]);
      
      // Crear año académico
      $academicYear = AcademicYear::factory()->create();
      
      // Crear convocatorias abiertas
      $calls = Call::factory()->count(4)->create([
          'program_id' => $programs->first()->id,
          'academic_year_id' => $academicYear->id,
          'status' => 'abierta',
          'published_at' => now(),
      ]);
      
      // Crear noticias publicadas
      $news = NewsPost::factory()->count(3)->create([
          'program_id' => $programs->first()->id,
          'status' => 'publicado',
          'published_at' => now(),
      ]);
      
      // Crear eventos próximos
      $events = ErasmusEvent::factory()->count(5)->create([
          'start_date' => now()->addDays(7),
      ]);
      
      return [
          'programs' => $programs,
          'academicYear' => $academicYear,
          'calls' => $calls,
          'news' => $news,
          'events' => $events,
      ];
  }
  ```

---

### Fase 2: Tests de Listado de Programas

**Objetivo**: Implementar tests completos para el listado de programas con filtros, búsqueda y paginación.

#### 2.1. Crear ProgramsIndexTest.php

**Archivo**: `tests/Browser/Public/ProgramsIndexTest.php`

- [ ] **Test: Verificar renderizado de listado de programas**
  - Verificar que la página carga correctamente
  - Verificar que no hay errores de JavaScript
  - Verificar estructura HTML básica

- [ ] **Test: Verificar visualización de programas**
  - Crear múltiples programas (activos e inactivos)
  - Verificar que solo se muestran programas activos por defecto
  - Verificar que se muestran correctamente los datos (nombre, descripción, código)
  - Verificar enlaces a detalle de programas

- [ ] **Test: Verificar filtro por tipo de programa**
  - Crear programas de diferentes tipos (KA1, KA2, JM, DISCOVER)
  - Verificar que el filtro funciona correctamente
  - Verificar que al seleccionar un tipo, solo se muestran programas de ese tipo
  - Verificar que el filtro se refleja en la URL (`?tipo=KA1`)

- [ ] **Test: Verificar filtro de programas activos**
  - Crear programas activos e inactivos
  - Verificar que el toggle de "solo activos" funciona
  - Verificar que cuando está desactivado, se muestran todos los programas
  - Verificar que cuando está activado, solo se muestran activos

- [ ] **Test: Verificar búsqueda de programas**
  - Crear programas con nombres específicos
  - Verificar búsqueda por nombre
  - Verificar búsqueda por código
  - Verificar búsqueda por descripción
  - Verificar que la búsqueda se refleja en la URL (`?q=texto`)

- [ ] **Test: Verificar paginación**
  - Crear más de 9 programas (límite de paginación)
  - Verificar que se muestra paginación
  - Verificar navegación entre páginas
  - Verificar que los filtros se mantienen al cambiar de página

- [ ] **Test: Verificar estadísticas**
  - Verificar que se muestran estadísticas correctas (total, activos, movilidad, cooperación)
  - Verificar que las estadísticas se actualizan con los filtros

- [ ] **Test: Verificar reset de filtros**
  - Aplicar múltiples filtros
  - Verificar que el botón de reset funciona
  - Verificar que los filtros vuelven a valores por defecto

- [ ] **Test: Detectar problemas de lazy loading**
  - Verificar que no hay consultas N+1
  - Verificar que todas las relaciones necesarias están cargadas

#### 2.2. Crear Helper para Datos de Programas

- [ ] Añadir función `createProgramsTestData()` en `tests/Browser/Helpers.php`:
  ```php
  function createProgramsTestData(): array
  {
      $programs = collect();
      
      // Crear programas de diferentes tipos
      $programs->push(Program::factory()->create([
          'code' => 'KA121-VET',
          'name' => 'Programa KA1 VET',
          'is_active' => true,
      ]));
      
      $programs->push(Program::factory()->create([
          'code' => 'KA220-SCH',
          'name' => 'Programa KA2 Escolar',
          'is_active' => true,
      ]));
      
      $programs->push(Program::factory()->create([
          'code' => 'JM-001',
          'name' => 'Programa Jean Monnet',
          'is_active' => true,
      ]));
      
      // Crear programas inactivos
      $programs->push(Program::factory()->create([
          'code' => 'KA131-HED',
          'name' => 'Programa Inactivo',
          'is_active' => false,
      ]));
      
      return [
          'programs' => $programs,
      ];
  }
  ```

---

### Fase 3: Tests de Detalle de Programa

**Objetivo**: Implementar tests completos para el detalle de programa, verificando relaciones y detección de lazy loading.

#### 3.1. Crear ProgramsShowTest.php

**Archivo**: `tests/Browser/Public/ProgramsShowTest.php`

- [ ] **Test: Verificar renderizado de detalle de programa**
  - Crear programa con todos los datos
  - Verificar que la página carga correctamente
  - Verificar que no hay errores de JavaScript
  - Verificar que se muestra el nombre del programa
  - Verificar que se muestra la descripción
  - Verificar que se muestra el código

- [ ] **Test: Verificar configuración visual del programa**
  - Crear programas de diferentes tipos
  - Verificar que se muestran los colores correctos según el tipo
  - Verificar que se muestran los iconos correctos
  - Verificar que se muestran los badges correctos

- [ ] **Test: Verificar imagen del programa**
  - Crear programa con imagen (Media Library)
  - Verificar que se muestra la imagen
  - Verificar que se usa la conversión 'large' si está disponible
  - Verificar fallback a 'medium' y original

- [ ] **Test: Verificar convocatorias relacionadas**
  - Crear programa con múltiples convocatorias
  - Verificar que se muestran convocatorias relacionadas (máximo 4)
  - Verificar que solo se muestran convocatorias con estado 'abierta' o 'cerrada'
  - Verificar que solo se muestran convocatorias publicadas
  - Verificar que las convocatorias están ordenadas (abiertas primero)
  - Verificar enlaces a detalle de convocatorias
  - **CRÍTICO**: Verificar eager loading de `program` y `academicYear` en convocatorias

- [ ] **Test: Verificar noticias relacionadas**
  - Crear programa con múltiples noticias
  - Verificar que se muestran noticias relacionadas (máximo 3)
  - Verificar que solo se muestran noticias publicadas
  - Verificar que las noticias están ordenadas por fecha de publicación
  - Verificar enlaces a detalle de noticias
  - **CRÍTICO**: Verificar eager loading de `program` y `author` en noticias

- [ ] **Test: Verificar otros programas sugeridos**
  - Crear múltiples programas activos
  - Verificar que se muestran otros programas (máximo 3)
  - Verificar que no se muestra el programa actual
  - Verificar enlaces a otros programas

- [ ] **Test: Verificar navegación desde detalle de programa**
  - Verificar breadcrumbs
  - Verificar enlaces a convocatorias relacionadas
  - Verificar enlaces a noticias relacionadas
  - Verificar enlaces a otros programas

- [ ] **Test: Detectar problemas de lazy loading (CRÍTICO)**
  - Verificar que `program` está cargado (no lazy loading)
  - Verificar que `academicYear` está cargado en relaciones
  - Verificar que no hay consultas N+1 al acceder a relaciones
  - Usar `assertNoJavascriptErrors()` para detectar errores
  - Verificar que todas las relaciones necesarias están eager loaded

- [ ] **Test: Verificar estado vacío**
  - Crear programa sin convocatorias relacionadas
  - Verificar que se muestra mensaje apropiado
  - Crear programa sin noticias relacionadas
  - Verificar que se muestra mensaje apropiado

#### 3.2. Crear Helper para Datos de Detalle de Programa

- [ ] Añadir función `createProgramShowTestData()` en `tests/Browser/Helpers.php`:
  ```php
  function createProgramShowTestData(): array
  {
      $program = Program::factory()->create([
          'code' => 'KA121-VET',
          'name' => 'Programa de Prueba',
          'is_active' => true,
      ]);
      
      $academicYear = AcademicYear::factory()->create();
      
      // Crear convocatorias relacionadas
      $calls = Call::factory()->count(5)->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'status' => 'abierta',
          'published_at' => now(),
      ]);
      
      // Crear noticias relacionadas
      $news = NewsPost::factory()->count(4)->create([
          'program_id' => $program->id,
          'status' => 'publicado',
          'published_at' => now(),
      ]);
      
      // Crear otros programas
      $otherPrograms = Program::factory()->count(3)->create([
          'is_active' => true,
      ]);
      
      return [
          'program' => $program,
          'academicYear' => $academicYear,
          'calls' => $calls,
          'news' => $news,
          'otherPrograms' => $otherPrograms,
      ];
  }
  ```

---

### Fase 4: Tests de Listado de Convocatorias

**Objetivo**: Implementar tests completos para el listado de convocatorias con todos los filtros disponibles.

#### 4.1. Crear CallsIndexTest.php

**Archivo**: `tests/Browser/Public/CallsIndexTest.php`

- [ ] **Test: Verificar renderizado de listado de convocatorias**
  - Verificar que la página carga correctamente
  - Verificar que no hay errores de JavaScript
  - Verificar estructura HTML básica

- [ ] **Test: Verificar visualización de convocatorias**
  - Crear múltiples convocatorias (abiertas y cerradas)
  - Verificar que solo se muestran convocatorias publicadas
  - Verificar que se muestran correctamente los datos (título, programa, año académico)
  - Verificar enlaces a detalle de convocatorias
  - Verificar eager loading de `program` y `academicYear`

- [ ] **Test: Verificar filtro por programa**
  - Crear convocatorias de diferentes programas
  - Verificar que el filtro funciona correctamente
  - Verificar que al seleccionar un programa, solo se muestran convocatorias de ese programa
  - Verificar que el filtro se refleja en la URL (`?programa=1`)

- [ ] **Test: Verificar filtro por año académico**
  - Crear convocatorias de diferentes años académicos
  - Verificar que el filtro funciona correctamente
  - Verificar que al seleccionar un año, solo se muestran convocatorias de ese año
  - Verificar que el filtro se refleja en la URL (`?ano=1`)

- [ ] **Test: Verificar filtro por tipo (alumnado/personal)**
  - Crear convocatorias de diferentes tipos
  - Verificar que el filtro funciona correctamente
  - Verificar que al seleccionar un tipo, solo se muestran convocatorias de ese tipo
  - Verificar que el filtro se refleja en la URL (`?tipo=alumnado`)

- [ ] **Test: Verificar filtro por modalidad (corta/larga)**
  - Crear convocatorias de diferentes modalidades
  - Verificar que el filtro funciona correctamente
  - Verificar que al seleccionar una modalidad, solo se muestran convocatorias de esa modalidad
  - Verificar que el filtro se refleja en la URL (`?modalidad=corta`)

- [ ] **Test: Verificar filtro por estado (abierta/cerrada)**
  - Crear convocatorias con diferentes estados
  - Verificar que el filtro funciona correctamente
  - Verificar que al seleccionar un estado, solo se muestran convocatorias de ese estado
  - Verificar que el filtro se refleja en la URL (`?estado=abierta`)

- [ ] **Test: Verificar búsqueda de convocatorias**
  - Crear convocatorias con títulos específicos
  - Verificar búsqueda por título
  - Verificar búsqueda en requirements
  - Verificar búsqueda en documentation
  - Verificar que la búsqueda se refleja en la URL (`?q=texto`)

- [ ] **Test: Verificar combinación de filtros**
  - Aplicar múltiples filtros simultáneamente
  - Verificar que todos los filtros se aplican correctamente
  - Verificar que los resultados son correctos

- [ ] **Test: Verificar paginación**
  - Crear más de 12 convocatorias (límite de paginación)
  - Verificar que se muestra paginación
  - Verificar navegación entre páginas
  - Verificar que los filtros se mantienen al cambiar de página

- [ ] **Test: Verificar estadísticas**
  - Verificar que se muestran estadísticas correctas (total, abiertas, cerradas)
  - Verificar que las estadísticas se actualizan con los filtros

- [ ] **Test: Verificar reset de filtros**
  - Aplicar múltiples filtros
  - Verificar que el botón de reset funciona
  - Verificar que los filtros vuelven a valores por defecto

- [ ] **Test: Verificar ordenamiento**
  - Verificar que las convocatorias abiertas aparecen primero
  - Verificar que las convocatorias cerradas aparecen después
  - Verificar que dentro de cada grupo, se ordenan por fecha de publicación

- [ ] **Test: Detectar problemas de lazy loading**
  - Verificar que no hay consultas N+1
  - Verificar que `program` y `academicYear` están eager loaded

#### 4.2. Crear Helper para Datos de Convocatorias

- [ ] Añadir función `createCallsTestData()` en `tests/Browser/Helpers.php`:
  ```php
  function createCallsTestData(): array
  {
      $program = Program::factory()->create(['is_active' => true]);
      $academicYear = AcademicYear::factory()->create();
      
      // Crear convocatorias de diferentes tipos y estados
      $calls = collect();
      
      $calls->push(Call::factory()->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'type' => 'alumnado',
          'modality' => 'corta',
          'status' => 'abierta',
          'published_at' => now(),
      ]));
      
      $calls->push(Call::factory()->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'type' => 'personal',
          'modality' => 'larga',
          'status' => 'cerrada',
          'published_at' => now()->subDays(5),
      ]));
      
      return [
          'program' => $program,
          'academicYear' => $academicYear,
          'calls' => $calls,
      ];
  }
  ```

---

### Fase 5: Tests de Detalle de Convocatoria

**Objetivo**: Implementar tests completos para el detalle de convocatoria, verificando relaciones complejas y detección de lazy loading.

#### 5.1. Crear CallsShowTest.php

**Archivo**: `tests/Browser/Public/CallsShowTest.php`

- [ ] **Test: Verificar renderizado de detalle de convocatoria**
  - Crear convocatoria con todos los datos
  - Verificar que la página carga correctamente
  - Verificar que no hay errores de JavaScript
  - Verificar que se muestra el título
  - Verificar que se muestra el programa
  - Verificar que se muestra el año académico

- [ ] **Test: Verificar configuración visual de la convocatoria**
  - Crear convocatorias con diferentes estados (abierta, cerrada)
  - Verificar que se muestran los colores correctos según el estado
  - Verificar que se muestran los iconos correctos
  - Verificar que se muestran los badges correctos

- [ ] **Test: Verificar acceso a convocatorias no publicadas**
  - Crear convocatoria sin `published_at`
  - Verificar que devuelve 404
  - Crear convocatoria con estado 'borrador'
  - Verificar que devuelve 404

- [ ] **Test: Verificar fases de la convocatoria**
  - Crear convocatoria con múltiples fases
  - Verificar que se muestran todas las fases
  - Verificar que las fases están ordenadas por `order`
  - Verificar que se muestran los datos de cada fase (nombre, fechas)
  - **CRÍTICO**: Verificar eager loading de `phases` en mount

- [ ] **Test: Verificar resoluciones publicadas**
  - Crear convocatoria con múltiples resoluciones (publicadas y no publicadas)
  - Verificar que solo se muestran resoluciones publicadas
  - Verificar que las resoluciones están ordenadas por fecha oficial
  - Verificar que se muestran los datos de cada resolución
  - Verificar enlaces de descarga de PDFs (si aplica)
  - **CRÍTICO**: Verificar eager loading de `resolutions` y `callPhase` en mount

- [ ] **Test: Verificar noticias relacionadas**
  - Crear convocatoria con programa asociado
  - Crear noticias del mismo programa
  - Verificar que se muestran noticias relacionadas (máximo 3)
  - Verificar que solo se muestran noticias publicadas
  - Verificar enlaces a detalle de noticias
  - **CRÍTICO**: Verificar eager loading de `program` y `author` en noticias

- [ ] **Test: Verificar otras convocatorias del mismo programa**
  - Crear múltiples convocatorias del mismo programa
  - Verificar que se muestran otras convocatorias (máximo 3)
  - Verificar que no se muestra la convocatoria actual
  - Verificar que las abiertas aparecen primero
  - Verificar enlaces a otras convocatorias
  - **CRÍTICO**: Verificar eager loading de `program` y `academicYear` en otras convocatorias

- [ ] **Test: Verificar navegación desde detalle de convocatoria**
  - Verificar breadcrumbs
  - Verificar enlaces a noticias relacionadas
  - Verificar enlaces a otras convocatorias
  - Verificar enlaces a fases (si aplica)
  - Verificar enlaces a resoluciones (si aplica)

- [ ] **Test: Detectar problemas de lazy loading (CRÍTICO)**
  - Verificar que `program` está cargado (no lazy loading)
  - Verificar que `academicYear` está cargado (no lazy loading)
  - Verificar que `phases` está eager loaded en mount
  - Verificar que `resolutions` está eager loaded en mount
  - Verificar que `callPhase` está eager loaded en resoluciones
  - Verificar que no hay consultas N+1 al acceder a relaciones
  - Usar `assertNoJavascriptErrors()` para detectar errores

- [ ] **Test: Verificar estado vacío**
  - Crear convocatoria sin fases
  - Verificar que se muestra mensaje apropiado
  - Crear convocatoria sin resoluciones publicadas
  - Verificar que se muestra mensaje apropiado
  - Crear convocatoria sin noticias relacionadas
  - Verificar que se muestra mensaje apropiado

#### 5.2. Crear Helper para Datos de Detalle de Convocatoria

- [ ] Añadir función `createCallShowTestData()` en `tests/Browser/Helpers.php`:
  ```php
  function createCallShowTestData(): array
  {
      $program = Program::factory()->create(['is_active' => true]);
      $academicYear = AcademicYear::factory()->create();
      
      $call = Call::factory()->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'status' => 'abierta',
          'published_at' => now(),
      ]);
      
      // Crear fases
      $phases = CallPhase::factory()->count(3)->create([
          'call_id' => $call->id,
      ]);
      
      // Crear resoluciones (algunas publicadas, otras no)
      $resolutions = collect();
      $resolutions->push(Resolution::factory()->create([
          'call_id' => $call->id,
          'call_phase_id' => $phases->first()->id,
          'published_at' => now(),
      ]));
      $resolutions->push(Resolution::factory()->create([
          'call_id' => $call->id,
          'call_phase_id' => $phases->first()->id,
          'published_at' => null, // No publicada
      ]));
      
      // Crear noticias relacionadas
      $news = NewsPost::factory()->count(4)->create([
          'program_id' => $program->id,
          'status' => 'publicado',
          'published_at' => now(),
      ]);
      
      // Crear otras convocatorias
      $otherCalls = Call::factory()->count(3)->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'status' => 'abierta',
          'published_at' => now(),
      ]);
      
      return [
          'program' => $program,
          'academicYear' => $academicYear,
          'call' => $call,
          'phases' => $phases,
          'resolutions' => $resolutions,
          'news' => $news,
          'otherCalls' => $otherCalls,
      ];
  }
  ```

---

### Fase 6: Tests de Listado de Noticias

**Objetivo**: Implementar tests completos para el listado de noticias con filtros y búsqueda.

#### 6.1. Crear NewsIndexTest.php

**Archivo**: `tests/Browser/Public/NewsIndexTest.php`

- [ ] **Test: Verificar renderizado de listado de noticias**
  - Verificar que la página carga correctamente
  - Verificar que no hay errores de JavaScript
  - Verificar estructura HTML básica

- [ ] **Test: Verificar visualización de noticias**
  - Crear múltiples noticias publicadas
  - Verificar que solo se muestran noticias publicadas
  - Verificar que se muestran correctamente los datos (título, excerpt, autor, fecha)
  - Verificar enlaces a detalle de noticias
  - Verificar eager loading de `program`, `author`, `tags`

- [ ] **Test: Verificar filtro por programa**
  - Crear noticias de diferentes programas
  - Verificar que el filtro funciona correctamente
  - Verificar que al seleccionar un programa, solo se muestran noticias de ese programa
  - Verificar que el filtro se refleja en la URL (`?programa=1`)

- [ ] **Test: Verificar filtro por año académico**
  - Crear noticias de diferentes años académicos
  - Verificar que el filtro funciona correctamente
  - Verificar que al seleccionar un año, solo se muestran noticias de ese año
  - Verificar que el filtro se refleja en la URL (`?ano=1`)

- [ ] **Test: Verificar filtro por etiquetas**
  - Crear noticias con diferentes etiquetas
  - Verificar que el filtro funciona correctamente
  - Verificar que al seleccionar etiquetas, solo se muestran noticias con esas etiquetas
  - Verificar que se pueden seleccionar múltiples etiquetas
  - Verificar que el filtro se refleja en la URL (`?etiquetas=1,2`)

- [ ] **Test: Verificar búsqueda de noticias**
  - Crear noticias con títulos específicos
  - Verificar búsqueda por título
  - Verificar búsqueda en excerpt
  - Verificar búsqueda en content
  - Verificar que la búsqueda se refleja en la URL (`?q=texto`)

- [ ] **Test: Verificar combinación de filtros**
  - Aplicar múltiples filtros simultáneamente
  - Verificar que todos los filtros se aplican correctamente
  - Verificar que los resultados son correctos

- [ ] **Test: Verificar paginación**
  - Crear más de 12 noticias (límite de paginación)
  - Verificar que se muestra paginación
  - Verificar navegación entre páginas
  - Verificar que los filtros se mantienen al cambiar de página

- [ ] **Test: Verificar estadísticas**
  - Verificar que se muestran estadísticas correctas (total, este mes, este año)
  - Verificar que las estadísticas se actualizan con los filtros

- [ ] **Test: Verificar reset de filtros**
  - Aplicar múltiples filtros
  - Verificar que el botón de reset funciona
  - Verificar que los filtros vuelven a valores por defecto

- [ ] **Test: Verificar ordenamiento**
  - Verificar que las noticias están ordenadas por fecha de publicación (más recientes primero)

- [ ] **Test: Detectar problemas de lazy loading**
  - Verificar que no hay consultas N+1
  - Verificar que `program`, `author` y `tags` están eager loaded

#### 6.2. Crear Helper para Datos de Noticias

- [ ] Añadir función `createNewsTestData()` en `tests/Browser/Helpers.php`:
  ```php
  function createNewsTestData(): array
  {
      $program = Program::factory()->create(['is_active' => true]);
      $academicYear = AcademicYear::factory()->create();
      $author = User::factory()->create();
      
      // Crear etiquetas
      $tags = NewsTag::factory()->count(3)->create();
      
      // Crear noticias con diferentes configuraciones
      $news = collect();
      
      $news->push(NewsPost::factory()->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'author_id' => $author->id,
          'status' => 'publicado',
          'published_at' => now(),
      ])->tags()->attach($tags->first()));
      
      $news->push(NewsPost::factory()->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'author_id' => $author->id,
          'status' => 'publicado',
          'published_at' => now()->subDays(5),
      ])->tags()->attach($tags->take(2)));
      
      return [
          'program' => $program,
          'academicYear' => $academicYear,
          'author' => $author,
          'tags' => $tags,
          'news' => $news,
      ];
  }
  ```

---

### Fase 7: Tests de Detalle de Noticia

**Objetivo**: Implementar tests completos para el detalle de noticia, verificando relaciones y detección de lazy loading.

#### 7.1. Crear NewsShowTest.php

**Archivo**: `tests/Browser/Public/NewsShowTest.php`

- [ ] **Test: Verificar renderizado de detalle de noticia**
  - Crear noticia con todos los datos
  - Verificar que la página carga correctamente
  - Verificar que no hay errores de JavaScript
  - Verificar que se muestra el título
  - Verificar que se muestra el contenido
  - Verificar que se muestra el autor
  - Verificar que se muestra la fecha de publicación

- [ ] **Test: Verificar acceso a noticias no publicadas**
  - Crear noticia sin `published_at`
  - Verificar que devuelve 404
  - Crear noticia con estado 'borrador'
  - Verificar que devuelve 404

- [ ] **Test: Verificar imagen destacada**
  - Crear noticia con imagen destacada (Media Library)
  - Verificar que se muestra la imagen
  - Verificar que se usa la conversión 'hero' si está disponible
  - Verificar fallback a 'large' y original

- [ ] **Test: Verificar etiquetas de la noticia**
  - Crear noticia con múltiples etiquetas
  - Verificar que se muestran todas las etiquetas
  - Verificar enlaces a filtro por etiqueta (si aplica)

- [ ] **Test: Verificar noticias relacionadas**
  - Crear noticia con programa asociado
  - Crear otras noticias del mismo programa
  - Crear noticias con etiquetas comunes
  - Verificar que se muestran noticias relacionadas (máximo 3)
  - Verificar que no se muestra la noticia actual
  - Verificar priorización por programa y etiquetas
  - Verificar enlaces a noticias relacionadas
  - **CRÍTICO**: Verificar eager loading de `program`, `author`, `tags` en noticias relacionadas

- [ ] **Test: Verificar convocatorias relacionadas**
  - Crear noticia con programa asociado
  - Crear convocatorias del mismo programa
  - Verificar que se muestran convocatorias relacionadas (máximo 3)
  - Verificar que solo se muestran convocatorias publicadas
  - Verificar que las abiertas aparecen primero
  - Verificar enlaces a detalle de convocatorias
  - **CRÍTICO**: Verificar eager loading de `program` y `academicYear` en convocatorias

- [ ] **Test: Verificar navegación desde detalle de noticia**
  - Verificar breadcrumbs
  - Verificar enlaces a noticias relacionadas
  - Verificar enlaces a convocatorias relacionadas
  - Verificar enlaces a etiquetas

- [ ] **Test: Verificar metadatos SEO**
  - Verificar que se muestran metadatos Open Graph
  - Verificar que se muestran metadatos Twitter Cards
  - Verificar que se muestran datos estructurados (JSON-LD Article)

- [ ] **Test: Detectar problemas de lazy loading (CRÍTICO)**
  - Verificar que `program` está cargado (no lazy loading)
  - Verificar que `academicYear` está cargado (no lazy loading)
  - Verificar que `author` está cargado (no lazy loading)
  - Verificar que `tags` está eager loaded
  - Verificar que `media` está eager loaded
  - Verificar que no hay consultas N+1 al acceder a relaciones
  - Usar `assertNoJavascriptErrors()` para detectar errores

- [ ] **Test: Verificar estado vacío**
  - Crear noticia sin noticias relacionadas
  - Verificar que se muestra mensaje apropiado
  - Crear noticia sin convocatorias relacionadas
  - Verificar que se muestra mensaje apropiado

#### 7.2. Crear Helper para Datos de Detalle de Noticia

- [ ] Añadir función `createNewsShowTestData()` en `tests/Browser/Helpers.php`:
  ```php
  function createNewsShowTestData(): array
  {
      $program = Program::factory()->create(['is_active' => true]);
      $academicYear = AcademicYear::factory()->create();
      $author = User::factory()->create();
      
      // Crear etiquetas
      $tags = NewsTag::factory()->count(3)->create();
      
      // Crear noticia principal
      $newsPost = NewsPost::factory()->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'author_id' => $author->id,
          'status' => 'publicado',
          'published_at' => now(),
      ]);
      $newsPost->tags()->attach($tags->take(2));
      
      // Crear noticias relacionadas (mismo programa y etiquetas)
      $relatedNews = collect();
      $relatedNews->push(NewsPost::factory()->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'author_id' => $author->id,
          'status' => 'publicado',
          'published_at' => now()->subDays(2),
      ])->tags()->attach($tags->first()));
      
      // Crear convocatorias relacionadas
      $relatedCalls = Call::factory()->count(3)->create([
          'program_id' => $program->id,
          'academic_year_id' => $academicYear->id,
          'status' => 'abierta',
          'published_at' => now(),
      ]);
      
      return [
          'program' => $program,
          'academicYear' => $academicYear,
          'author' => $author,
          'tags' => $tags,
          'newsPost' => $newsPost,
          'relatedNews' => $relatedNews,
          'relatedCalls' => $relatedCalls,
      ];
  }
  ```

---

### Fase 8: Optimización y Mejoras

**Objetivo**: Optimizar los tests y añadir verificaciones adicionales.

#### 8.1. Optimizar Helpers

- [ ] Revisar y optimizar todas las funciones helper
- [ ] Asegurar que los helpers crean datos realistas
- [ ] Asegurar que los helpers son eficientes (no crean datos innecesarios)

#### 8.2. Añadir Tests de Rendimiento

- [ ] **Test: Verificar tiempos de carga**
  - Medir tiempo de carga de cada página
  - Verificar que los tiempos son aceptables (< 2 segundos)
  - Documentar tiempos de referencia

- [ ] **Test: Verificar número de consultas**
  - Contar número de consultas SQL por página
  - Verificar que no hay consultas innecesarias
  - Documentar número máximo de consultas esperadas

#### 8.3. Añadir Tests de Accesibilidad Básica

- [ ] **Test: Verificar estructura semántica**
  - Verificar que se usan elementos HTML semánticos
  - Verificar que hay headings jerárquicos (h1, h2, h3)
  - Verificar que hay landmarks (header, main, footer, nav)

- [ ] **Test: Verificar navegación por teclado**
  - Verificar que todos los enlaces son accesibles por teclado
  - Verificar que los formularios son navegables por teclado
  - Verificar que hay indicadores de foco visibles

#### 8.4. Añadir Tests de Responsive (Opcional)

- [ ] **Test: Verificar diseño responsive**
  - Verificar que las páginas se ven bien en móviles (375px)
  - Verificar que las páginas se ven bien en tablets (768px)
  - Verificar que las páginas se ven bien en desktop (1920px)
  - Usar `browser_resize()` para cambiar tamaño de ventana

---

### Fase 9: Documentación y Verificación Final

**Objetivo**: Documentar los tests y verificar que todo funciona correctamente.

#### 9.1. Documentar Tests

- [ ] Crear documentación en `docs/browser-testing-public-pages.md`:
  - Descripción de cada test
  - Cómo ejecutar los tests
  - Qué se verifica en cada test
  - Cómo interpretar los resultados

#### 9.2. Verificación Final

- [ ] **Ejecutar todos los tests**
  ```bash
  ./vendor/bin/pest tests/Browser/Public
  ```

- [ ] **Verificar que todos los tests pasan**
  - Sin errores
  - Sin warnings
  - Sin problemas de lazy loading detectados

- [ ] **Verificar cobertura**
  - Verificar que todas las páginas públicas críticas están testeadas
  - Verificar que todas las funcionalidades están testeadas
  - Verificar que todos los casos edge están cubiertos

#### 9.3. Checklist de Completitud

- [x] Tests de Home completos y pasando (34 tests, 102 assertions)
- [x] Tests de Listado de Programas completos y pasando (22 tests, 77 assertions)
- [x] Tests de Detalle de Programa completos y pasando (34 tests, 113 assertions)
- [x] Tests de Listado de Convocatorias completos y pasando (26 tests, 79 assertions)
- [x] Tests de Detalle de Convocatoria completos y pasando (32 tests, 100 assertions)
- [x] Tests de Listado de Noticias completos y pasando (23 tests, 84 assertions)
- [x] Tests de Detalle de Noticia completos y pasando (29 tests, 85 assertions)
- [x] Tests de Rendimiento completos y pasando (8 tests, 16 assertions)
- [x] Tests de Accesibilidad completos y pasando (8 tests, 16 assertions)
- [x] Helpers creados y funcionando (8 helpers)
- [x] Detección de lazy loading implementada
- [x] Documentación creada (`docs/browser-testing-public-pages.md`)
- [x] Todos los tests pasan sin errores (217 tests, 680 assertions)
- [x] Archivo paso54.md creado con documentación del proceso (Fase 10)

---

### Fase 10: Documentación del Proceso de Planificación

**Objetivo**: Documentar todo el proceso de planificación del paso 3.11.2, incluyendo todos los prompts utilizados y las respuestas obtenidas.

#### 10.1. Crear Archivo de Documentación del Chat

- [ ] Crear archivo `docs/pasos/paso54.md` con la siguiente estructura:

  ```markdown
  # Paso 54: Planificación de Tests de Navegador de Páginas Públicas Críticas
  
  Este documento contiene todos los prompts utilizados durante la planificación del paso 3.11.2 y un resumen de las respuestas obtenidas.
  
  ## Contexto
  
  [Descripción del contexto inicial y objetivos]
  
  ## Prompts y Respuestas
  
  ### Prompt 1: [Título del primer prompt]
  
  **Prompt:**
  ```
  [Contenido completo del prompt]
  ```
  
  **Resumen de la Respuesta:**
  [Resumen de lo que se hizo y los resultados obtenidos]
  
  ### Prompt 2: [Título del segundo prompt]
  
  **Prompt:**
  ```
  [Contenido completo del prompt]
  ```
  
  **Resumen de la Respuesta:**
  [Resumen de lo que se hizo y los resultados obtenidos]
  
  ## Archivos Creados/Modificados
  
  - `docs/pasos/paso-3.11.2-plan.md` - Plan detallado completo
  
  ## Resultado Final
  
  [Resumen del resultado final y estado del plan]
  ```

#### 10.2. Incluir Todos los Prompts del Chat

- [ ] Documentar el prompt inicial:
  - Prompt del usuario solicitando el desarrollo del plan del paso 3.11.2
  - Resumen de la respuesta: creación del plan detallado con 9 fases

- [ ] Documentar el prompt de ampliación:
  - Prompt del usuario solicitando añadir Fase 10
  - Resumen de la respuesta: adición de la fase de documentación

#### 10.3. Incluir Resumen de Respuestas

- [ ] Resumir las acciones realizadas:
  - Lectura de documentación existente
  - Análisis de componentes públicos
  - Análisis de tests existentes
  - Creación del plan detallado con 9 fases iniciales
  - Adición de la Fase 10 de documentación

- [ ] Resumir los archivos creados:
  - `docs/pasos/paso-3.11.2-plan.md` - Plan completo de 1077 líneas
  - Estructura de 9 fases iniciales + 1 fase de documentación

- [ ] Resumir el contenido del plan:
  - Tests para 7 páginas públicas críticas
  - Detección de lazy loading en relaciones
  - Helpers para datos de prueba
  - Documentación completa

#### 10.4. Incluir Metadatos del Chat

- [ ] Incluir información del chat:
  - Fecha de creación: Enero 2026
  - Número de prompts: 2
  - Archivos analizados durante el proceso
  - Componentes revisados

#### 10.5. Verificar Completitud

- [ ] Verificar que todos los prompts están documentados
- [ ] Verificar que todos los resúmenes están incluidos
- [ ] Verificar que la estructura del documento es clara
- [ ] Verificar que hay enlaces a archivos relacionados

---

## Estructura de Archivos Final

```
tests/
├── Browser/
│   ├── Helpers.php                    # Helpers para crear datos de prueba
│   └── Public/
│       ├── HomeTest.php               # Tests de página principal
│       ├── ProgramsIndexTest.php      # Tests de listado de programas
│       ├── ProgramsShowTest.php       # Tests de detalle de programa
│       ├── CallsIndexTest.php         # Tests de listado de convocatorias
│       ├── CallsShowTest.php          # Tests de detalle de convocatoria
│       ├── NewsIndexTest.php          # Tests de listado de noticias
│       └── NewsShowTest.php            # Tests de detalle de noticia
├── Feature/
└── Unit/
```

---

## Criterios de Éxito

1. **Cobertura Completa**: Todas las páginas públicas críticas tienen tests de navegador
2. **Detección de Lazy Loading**: Todos los tests verifican que no hay problemas de lazy loading
3. **Tests Pasando**: Todos los tests pasan sin errores
4. **Documentación**: Documentación completa de los tests y cómo ejecutarlos
5. **Rendimiento**: Los tests se ejecutan en tiempo razonable (< 5 minutos para toda la suite)

---

## Notas Importantes

1. **Lazy Loading Detection**: Los tests de navegador son críticos para detectar problemas de lazy loading porque renderizan completamente la vista, a diferencia de `Livewire::test()` que no renderiza HTML completo.

2. **Eager Loading**: Todos los componentes públicos deben usar eager loading para relaciones necesarias. Los tests verifican esto explícitamente.

3. **Datos de Prueba**: Usar factories para crear datos de prueba realistas. Los helpers facilitan la creación de datos complejos.

4. **Rendimiento**: Los browser tests son más lentos que los tests funcionales. Se recomienda ejecutarlos solo cuando sea necesario durante el desarrollo, y siempre antes de hacer commit.

5. **CI/CD**: Estos tests deben ejecutarse en CI/CD para asegurar que no se introducen regresiones.

---

## Próximos Pasos

Una vez completados estos tests (Fases 1-9) y la documentación del proceso (Fase 10), el siguiente paso será:

- **Paso 3.11.3**: Tests de Flujos de Autenticación y Autorización
- Implementar tests de login, registro, recuperación de contraseña
- Implementar tests de autorización en rutas públicas y de administración

---

**Fecha de Creación**: Enero 2026  
**Última Actualización**: Enero 2026 (Fase 10 añadida)  
**Estado**: 📋 Plan listo para implementación (10 fases completas)
