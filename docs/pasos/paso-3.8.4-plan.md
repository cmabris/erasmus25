# Plan de Trabajo - Paso 3.8.4: Tests de Componentes Livewire de Administración

## Objetivo
Aumentar la cobertura de tests de los componentes Livewire de administración del 87.30% actual (líneas) al 100% (o lo más cercano posible).

## Estado Actual de Cobertura

### Resumen General
- **Líneas**: 87.30% (5497/6297) - Faltan 800 líneas
- **Métodos**: 75.15% (641/853) - Faltan 212 métodos
- **Clases**: 9.84% (6/61) - Faltan 55 clases

### Componentes con 100% de Cobertura ✅
- `DocumentCategories` - 96.50% (193/200) - Muy cerca
- `Documents` - 98.29% (345/351) - Muy cerca
- `News` - 95.55% (451/472) - Muy cerca
- `Newsletter` - 97.89% (93/95) - Muy cerca
- `Programs` - 92.63% (289/312) - Muy cerca
- `Roles` - 93.79% (317/338) - Muy cerca
- `Settings` - 92.68% (266/287) - Muy cerca
- `Translations` - 91.47% (397/434) - Muy cerca
- `Dashboard` - 93.55% (319/341) - Muy cerca

### Componentes que Necesitan Trabajo Prioritario

#### 1. NewsTags/Show.php (Prioridad CRÍTICA) 🔴
- **Líneas**: 0.00% (0/46) - **TODAS las líneas sin cubrir**
- **Métodos**: 0.00% (0/8) - **TODOS los métodos sin cubrir**
- **Clases**: 0.00% (0/1) - **Clase sin cubrir**
- **Estado**: No existe test para este componente

**Líneas sin cubrir:**
- Todo el componente (46 líneas)
- Métodos: `mount()`, `statistics()`, `delete()`, `restore()`, `forceDelete()`, `canDelete()`, `hasRelationships()`, `render()`

**Tests necesarios:**
- Test de autorización (mount)
- Test de visualización (render)
- Test de estadísticas (statistics)
- Test de eliminación (delete) - con y sin relaciones
- Test de restauración (restore)
- Test de eliminación permanente (forceDelete) - con y sin relaciones
- Test de verificación de relaciones (hasRelationships, canDelete)

#### 2. Users/Import.php (Prioridad ALTA) 🔴
- **Líneas**: 20.35% (23/113) - Faltan 90 líneas
- **Métodos**: 66.67% (4/6) - Faltan 2 métodos
- **Clases**: 0.00% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas 69-112: Método `validateUploadedFile()` completo (44 líneas)
- Líneas 138-214: Bloque try-catch en `import()` - especialmente el bloque catch (líneas 193-211)
- Líneas relacionadas con manejo de errores y validación de archivos

**Métodos sin cubrir:**
- `validateUploadedFile()` - Validación de archivos con Filepond
- Bloque catch en `import()` - Manejo de excepciones durante importación

**Tests necesarios:**
- Test para `validateUploadedFile()` - archivo válido
- Test para `validateUploadedFile()` - archivo inválido (mime type incorrecto)
- Test para `validateUploadedFile()` - archivo demasiado grande
- Test para `validateUploadedFile()` - archivo no es UploadedFile
- Test para `import()` - bloque catch cuando Excel::import() lanza excepción
- Test para `import()` - validación de errores en el archivo
- Test para `import()` - dry run mode completo
- Test para `import()` - modo normal con envío de emails
- Test para `import()` - resultados con usuarios y contraseñas generadas

#### 3. Calls/Import.php (Prioridad ALTA) 🔴
- **Líneas**: 22.55% (23/102) - Faltan 79 líneas
- **Métodos**: 66.67% (4/6) - Faltan 2 métodos
- **Clases**: 0.00% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas 64-107: Método `validateUploadedFile()` completo (44 líneas)
- Líneas 133-195: Bloque try-catch en `import()` - especialmente el bloque catch (líneas 175-192)
- Líneas relacionadas con manejo de errores y validación de archivos

**Métodos sin cubrir:**
- `validateUploadedFile()` - Validación de archivos con Filepond
- Bloque catch en `import()` - Manejo de excepciones durante importación

**Tests necesarios:**
- Test para `validateUploadedFile()` - archivo válido
- Test para `validateUploadedFile()` - archivo inválido (mime type incorrecto)
- Test para `validateUploadedFile()` - archivo demasiado grande
- Test para `validateUploadedFile()` - archivo no es UploadedFile
- Test para `import()` - bloque catch cuando Excel::import() lanza excepción
- Test para `import()` - validación de errores en el archivo
- Test para `import()` - dry run mode completo
- Test para `import()` - modo normal con resultados

#### 4. Calls/Edit.php (Prioridad ALTA) 🟠
- **Líneas**: 64.97% (102/157) - Faltan 55 líneas
- **Métodos**: 33.33% (6/18) - Faltan 12 métodos
- **Clases**: 0.00% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas relacionadas con actualización de campos específicos
- Líneas relacionadas con validación de relaciones
- Líneas relacionadas con manejo de errores
- Líneas relacionadas con actualización de estados

**Tests necesarios:**
- Test para actualización de campos específicos (baremo, destinos, etc.)
- Test para validación de relaciones antes de actualizar
- Test para manejo de errores en actualización
- Test para actualización de estados (borrador, abierta, cerrada, archivada)
- Test para publicación de convocatoria
- Test para actualización de fechas y validaciones

#### 5. Calls/Show.php (Prioridad ALTA) 🟠
- **Líneas**: 66.15% (127/192) - Faltan 65 líneas
- **Métodos**: 58.82% (10/17) - Faltan 7 métodos
- **Clases**: 0.00% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas relacionadas con visualización de fases
- Líneas relacionadas con visualización de resoluciones
- Líneas relacionadas con acciones específicas (publicar, cambiar estado)
- Líneas relacionadas con computed properties

**Tests necesarios:**
- Test para visualización de fases
- Test para visualización de resoluciones
- Test para acciones de publicación
- Test para cambio de estado
- Test para computed properties (estadísticas, relaciones)

#### 6. AuditLogs/Show.php (Prioridad MEDIA) 🟡
- **Líneas**: 77.04% (104/135) - Faltan 31 líneas
- **Métodos**: 40.00% (6/15) - Faltan 9 métodos
- **Clases**: 0.00% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas relacionadas con visualización de cambios (before/after)
- Líneas relacionadas con formateo de datos JSON
- Líneas relacionadas con computed properties

**Tests necesarios:**
- Test para visualización de cambios (before/after)
- Test para formateo de datos JSON
- Test para computed properties
- Test para diferentes tipos de actividades

#### 7. Users/Show.php (Prioridad MEDIA) 🟡
- **Líneas**: 89.11% (180/202) - Faltan 22 líneas
- **Métodos**: 63.64% (14/22) - Faltan 8 métodos
- **Clases**: 0.00% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas relacionadas con acciones específicas (restore, forceDelete)
- Líneas relacionadas con computed properties
- Líneas relacionadas con validación de relaciones

**Tests necesarios:**
- Test para restore de usuario
- Test para forceDelete de usuario
- Test para computed properties
- Test para validación de relaciones antes de eliminar

#### 8. AuditLogs/Index.php (Prioridad MEDIA) 🟡
- **Líneas**: 76.40% (136/178) - Faltan 42 líneas
- **Métodos**: 65.22% (15/23) - Faltan 8 métodos
- **Clases**: 0.00% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas relacionadas con filtros avanzados
- Líneas relacionadas con búsqueda
- Líneas relacionadas con paginación
- Líneas relacionadas con computed properties

**Tests necesarios:**
- Test para filtros avanzados (modelo, usuario, acción, fecha)
- Test para búsqueda
- Test para paginación
- Test para computed properties

#### 9. NewsTags (General) (Prioridad MEDIA) 🟡
- **Líneas**: 69.54% (121/174) - Faltan 53 líneas
- **Métodos**: 58.82% (20/34) - Faltan 14 métodos
- **Clases**: 0.00% (0/4) - Faltan 4 clases

**Componentes:**
- `Show.php` - 0% (ya cubierto arriba)
- `Index.php` - 95.35% (82/86) - Faltan 4 líneas
- `Create.php` - 94.44% (17/18) - Falta 1 línea
- `Edit.php` - 91.67% (22/24) - Faltan 2 líneas

**Tests necesarios:**
- Completar tests para `Show.php` (prioridad crítica)
- Test para casos edge en `Index.php`
- Test para casos edge en `Create.php`
- Test para casos edge en `Edit.php`

#### 10. Events (General) (Prioridad BAJA) 🟢
- **Líneas**: 87.30% (495/567) - Faltan 72 líneas
- **Métodos**: 75.82% (69/91) - Faltan 22 métodos
- **Clases**: 0.00% (0/4) - Faltan 4 clases

**Componentes:**
- Todos los componentes tienen buena cobertura pero faltan casos edge

**Tests necesarios:**
- Test para casos edge en todos los componentes
- Test para validaciones específicas
- Test para relaciones complejas

#### 11. AcademicYears (Prioridad BAJA) 🟢
- **Líneas**: 93.57% (233/249) - Faltan 16 líneas
- **Métodos**: 78.57% (33/42) - Faltan 9 métodos
- **Clases**: 25.00% (1/4) - Faltan 3 clases

**Tests necesarios:**
- Test para casos edge
- Test para validaciones específicas

## Plan de Implementación

### Fase 1: NewsTags/Show.php (Prioridad CRÍTICA) 🔴
**Objetivo**: Aumentar de 0% a 100%
**Estimación**: 2-3 horas

#### Tareas:
1. **Crear archivo de test** `tests/Feature/Livewire/Admin/NewsTags/ShowTest.php`
2. **Test de autorización**
   - Test que requiere autenticación
   - Test que requiere permiso `view`
   - Test que permite acceso con permiso correcto
3. **Test de mount()**
   - Test que carga relaciones correctamente
   - Test que carga count de noticias
4. **Test de statistics()**
   - Test que retorna estadísticas correctas
   - Test con etiqueta sin noticias
   - Test con etiqueta con noticias
5. **Test de delete()**
   - Test que elimina etiqueta sin relaciones
   - Test que no elimina etiqueta con relaciones
   - Test que requiere permiso `delete`
   - Test que dispara evento correcto
6. **Test de restore()**
   - Test que restaura etiqueta eliminada
   - Test que requiere permiso `restore`
   - Test que dispara evento correcto
7. **Test de forceDelete()**
   - Test que elimina permanentemente sin relaciones
   - Test que no elimina permanentemente con relaciones
   - Test que requiere permiso `forceDelete`
   - Test que dispara evento correcto
8. **Test de canDelete()**
   - Test que retorna true sin relaciones
   - Test que retorna false con relaciones
   - Test que retorna false sin permiso
9. **Test de hasRelationships()**
   - Test que retorna true con relaciones
   - Test que retorna false sin relaciones
10. **Test de render()**
    - Test que renderiza vista correcta
    - Test que pasa datos correctos a la vista

**Archivo**: `tests/Feature/Livewire/Admin/NewsTags/ShowTest.php`
**Tests estimados**: 20-25 tests

---

### Fase 2: Users/Import.php (Prioridad ALTA) 🔴
**Objetivo**: Aumentar de 20.35% a 100%
**Estimación**: 3-4 horas

#### Tareas:
1. **Test para `validateUploadedFile()`**
   - Test con archivo válido (Excel)
   - Test con archivo válido (CSV)
   - Test con archivo inválido (mime type incorrecto)
   - Test con archivo demasiado grande (>10MB)
   - Test cuando `$this->file` no es UploadedFile
   - Test que resetea `results` cuando archivo válido
   - Test que retorna false cuando validación falla
   - Test que retorna true cuando validación pasa
2. **Test para `import()` - bloque catch**
   - Mockear `Excel::import()` para que lance excepción
   - Verificar que se establece `results` con error
   - Verificar que se dispara evento `import-error`
   - Verificar que `isProcessing` se establece en false
3. **Test para `import()` - dry run mode**
   - Test con archivo válido en modo dry run
   - Verificar que no se crean usuarios
   - Verificar que se retornan resultados de validación
4. **Test para `import()` - modo normal**
   - Test con archivo válido en modo normal
   - Verificar que se crean usuarios
   - Verificar que se retornan resultados de importación
5. **Test para `import()` - con envío de emails**
   - Test con `sendEmails = true`
   - Verificar que se generan contraseñas
   - Verificar que se retornan usuarios con contraseñas
6. **Test para `import()` - con errores en archivo**
   - Test con archivo que tiene filas con errores
   - Verificar que se retornan errores correctamente
   - Verificar que se cuenta correctamente `failed`

**Archivo**: `tests/Feature/Livewire/Admin/Users/ImportTest.php` (actualizar existente)
**Tests estimados**: 15-20 tests nuevos

---

### Fase 3: Calls/Import.php (Prioridad ALTA) 🔴
**Objetivo**: Aumentar de 22.55% a 100%
**Estimación**: 3-4 horas

#### Tareas:
1. **Test para `validateUploadedFile()`**
   - Test con archivo válido (Excel)
   - Test con archivo válido (CSV)
   - Test con archivo inválido (mime type incorrecto)
   - Test con archivo demasiado grande (>10MB)
   - Test cuando `$this->file` no es UploadedFile
   - Test que resetea `results` cuando archivo válido
   - Test que retorna false cuando validación falla
   - Test que retorna true cuando validación pasa
2. **Test para `import()` - bloque catch**
   - Mockear `Excel::import()` para que lance excepción
   - Verificar que se establece `results` con error
   - Verificar que se dispara evento `import-error`
   - Verificar que `isProcessing` se establece en false
3. **Test para `import()` - dry run mode**
   - Test con archivo válido en modo dry run
   - Verificar que no se crean convocatorias
   - Verificar que se retornan resultados de validación
4. **Test para `import()` - modo normal**
   - Test con archivo válido en modo normal
   - Verificar que se crean convocatorias
   - Verificar que se retornan resultados de importación
5. **Test para `import()` - con errores en archivo**
   - Test con archivo que tiene filas con errores
   - Verificar que se retornan errores correctamente
   - Verificar que se cuenta correctamente `failed`

**Archivo**: `tests/Feature/Livewire/Admin/Calls/ImportTest.php` (actualizar existente)
**Tests estimados**: 15-20 tests nuevos

---

### Fase 4: Calls/Edit.php (Prioridad ALTA) 🟠
**Objetivo**: Aumentar de 64.97% a 100%
**Estimación**: 4-5 horas

#### Tareas:
1. **Test para actualización de campos específicos**
   - Test para actualizar `baremo` (JSON)
   - Test para actualizar `destinos` (JSON)
   - Test para actualizar campos de fecha
   - Test para actualizar campos de texto
2. **Test para validación de relaciones**
   - Test que valida programa existe
   - Test que valida año académico existe
   - Test que valida relaciones antes de actualizar
3. **Test para manejo de errores**
   - Test que maneja errores de validación
   - Test que maneja errores de base de datos
4. **Test para actualización de estados**
   - Test para cambiar a `borrador`
   - Test para cambiar a `abierta`
   - Test para cambiar a `cerrada`
   - Test para cambiar a `archivada`
5. **Test para publicación**
   - Test que publica convocatoria (establece `published_at`)
   - Test que despublica convocatoria
6. **Test para actualización de fechas**
   - Test que valida fechas de inicio/fin
   - Test que valida fechas de publicación

**Archivo**: `tests/Feature/Livewire/Admin/Calls/EditTest.php` (actualizar existente)
**Tests estimados**: 20-25 tests nuevos

---

### Fase 5: Calls/Show.php (Prioridad ALTA) 🟠
**Objetivo**: Aumentar de 66.15% a 100%
**Estimación**: 3-4 horas

#### Tareas:
1. **Test para visualización de fases**
   - Test que muestra fases correctamente
   - Test que muestra fase actual
   - Test que ordena fases correctamente
2. **Test para visualización de resoluciones**
   - Test que muestra resoluciones correctamente
   - Test que filtra resoluciones por fase
   - Test que muestra solo resoluciones publicadas
3. **Test para acciones de publicación**
   - Test que publica convocatoria
   - Test que despublica convocatoria
4. **Test para cambio de estado**
   - Test que cambia estado correctamente
   - Test que valida transiciones de estado
5. **Test para computed properties**
   - Test para estadísticas
   - Test para relaciones cargadas

**Archivo**: `tests/Feature/Livewire/Admin/Calls/ShowTest.php` (actualizar existente)
**Tests estimados**: 15-20 tests nuevos

---

### Fase 6: AuditLogs/Show.php (Prioridad MEDIA) 🟡
**Objetivo**: Aumentar de 77.04% a 100%
**Estimación**: 2-3 horas

#### Tareas:
1. **Test para visualización de cambios (before/after)**
   - Test que muestra cambios correctamente
   - Test que formatea JSON correctamente
   - Test que maneja cambios complejos
2. **Test para formateo de datos JSON**
   - Test que formatea arrays
   - Test que formatea objetos
   - Test que maneja valores null
3. **Test para computed properties**
   - Test para propiedades calculadas
   - Test para relaciones cargadas
4. **Test para diferentes tipos de actividades**
   - Test para creación
   - Test para actualización
   - Test para eliminación
   - Test para restauración

**Archivo**: `tests/Feature/Livewire/Admin/AuditLogs/ShowTest.php` (actualizar existente)
**Tests estimados**: 10-15 tests nuevos

---

### Fase 7: Users/Show.php (Prioridad MEDIA) 🟡
**Objetivo**: Aumentar de 89.11% a 100%
**Estimación**: 2-3 horas

#### Tareas:
1. **Test para restore de usuario**
   - Test que restaura usuario eliminado
   - Test que requiere permiso `restore`
   - Test que dispara evento correcto
2. **Test para forceDelete de usuario**
   - Test que elimina permanentemente sin relaciones
   - Test que no elimina permanentemente con relaciones
   - Test que requiere permiso `forceDelete`
   - Test que dispara evento correcto
3. **Test para computed properties**
   - Test para estadísticas
   - Test para relaciones cargadas
4. **Test para validación de relaciones**
   - Test que valida relaciones antes de eliminar
   - Test que muestra mensaje cuando hay relaciones

**Archivo**: `tests/Feature/Livewire/Admin/Users/ShowTest.php` (actualizar existente)
**Tests estimados**: 10-15 tests nuevos

---

### Fase 8: AuditLogs/Index.php (Prioridad MEDIA) 🟡
**Objetivo**: Aumentar de 76.40% a 100%
**Estimación**: 2-3 horas

#### Tareas:
1. **Test para filtros avanzados**
   - Test para filtrar por modelo
   - Test para filtrar por usuario
   - Test para filtrar por acción
   - Test para filtrar por fecha
   - Test para combinación de filtros
2. **Test para búsqueda**
   - Test que busca en descripción
   - Test que busca en propiedades
3. **Test para paginación**
   - Test que pagina correctamente
   - Test que resetea página al cambiar filtros
4. **Test para computed properties**
   - Test para propiedades calculadas
   - Test para relaciones cargadas

**Archivo**: `tests/Feature/Livewire/Admin/AuditLogs/IndexTest.php` (actualizar existente)
**Tests estimados**: 10-15 tests nuevos

---

### Fase 9: NewsTags (Casos Edge) (Prioridad MEDIA) 🟡
**Objetivo**: Aumentar de 69.54% a 100%
**Estimación**: 1-2 horas

#### Tareas:
1. **Test para Index.php - casos edge**
   - Test para búsqueda sin resultados
   - Test para filtros combinados
2. **Test para Create.php - casos edge**
   - Test para validación de nombre único
3. **Test para Edit.php - casos edge**
   - Test para validación de nombre único (excluyendo actual)

**Archivo**: Tests existentes (actualizar)
**Tests estimados**: 5-8 tests nuevos

---

### Fase 10: Events (Casos Edge) (Prioridad BAJA) 🟢
**Objetivo**: Aumentar de 87.30% a 100%
**Estimación**: 2-3 horas

#### Tareas:
1. **Test para casos edge en todos los componentes**
   - Test para validaciones específicas
   - Test para relaciones complejas
   - Test para casos límite

**Archivo**: Tests existentes (actualizar)
**Tests estimados**: 10-15 tests nuevos

---

### Fase 11: AcademicYears (Casos Edge) (Prioridad BAJA) 🟢
**Objetivo**: Aumentar de 93.57% a 100%
**Estimación**: 1-2 horas

#### Tareas:
1. **Test para casos edge**
   - Test para validaciones específicas
   - Test para casos límite

**Archivo**: Tests existentes (actualizar)
**Tests estimados**: 5-8 tests nuevos

---

## Estrategia de Testing

### Para Tests de Importación
- Usar `Storage::fake()` para simular archivos
- Crear archivos Excel/CSV de prueba usando `Maatwebsite\Excel`
- Mockear `Excel::import()` para simular errores
- Verificar que los bloques `catch` se ejecutan correctamente

### Para Tests de Validación de Archivos
- Crear `UploadedFile` con diferentes tipos MIME
- Crear archivos de diferentes tamaños
- Verificar que la validación funciona correctamente
- Verificar que se resetean resultados cuando archivo válido

### Para Tests de Excepciones
- Usar `Mockery` o `Pest\Laravel\mock()` para mockear métodos que lanzan excepciones
- Verificar que los bloques `catch` se ejecutan correctamente
- Verificar que los mensajes de error son apropiados

### Para Tests de Casos Edge
- Crear datos de prueba que cubran todos los casos posibles
- Verificar que los métodos retornan valores esperados
- Verificar que no se lanzan excepciones inesperadas

### Para Tests de Computed Properties
- Acceder explícitamente a las propiedades computed en los tests
- Verificar que se calculan correctamente
- Verificar que se cachean correctamente

## Criterios de Éxito

### Cobertura Objetivo
- **Líneas**: ≥ 95% (idealmente 100%)
- **Métodos**: ≥ 90% (idealmente 100%)
- **Clases**: ≥ 80% (idealmente 100%)

### Validación
1. Ejecutar `php artisan test --coverage-html=tests/coverage` después de cada fase
2. Verificar que la cobertura aumenta según lo esperado
3. Asegurar que todos los tests pasan
4. Verificar que no se rompen tests existentes

## Orden de Ejecución Recomendado

1. **Fase 1**: NewsTags/Show.php (crítico, 0% cobertura)
2. **Fase 2**: Users/Import.php (alta prioridad, 20% cobertura)
3. **Fase 3**: Calls/Import.php (alta prioridad, 22% cobertura)
4. **Fase 4**: Calls/Edit.php (alta prioridad, 65% cobertura)
5. **Fase 5**: Calls/Show.php (alta prioridad, 66% cobertura)
6. **Fase 6**: AuditLogs/Show.php (media prioridad, 77% cobertura)
7. **Fase 7**: Users/Show.php (media prioridad, 89% cobertura)
8. **Fase 8**: AuditLogs/Index.php (media prioridad, 76% cobertura)
9. **Fase 9**: NewsTags (casos edge) (media prioridad, 70% cobertura)
10. **Fase 10**: Events (casos edge) (baja prioridad, 87% cobertura)
11. **Fase 11**: AcademicYears (casos edge) (baja prioridad, 94% cobertura)

## Notas Importantes

1. **Cobertura de Clases**: El bajo porcentaje de cobertura de clases puede ser un falso positivo del reporte. Verificar que los tests realmente cubren la clase completa.

2. **Tests de Importación**: Para los tests de importación, necesitaremos crear archivos Excel/CSV de prueba y mockear `Excel::import()` para simular errores.

3. **Tests de Validación de Archivos**: Para `validateUploadedFile()`, necesitaremos crear diferentes tipos de archivos y verificar que la validación funciona correctamente.

4. **Mantenimiento**: Después de completar cada fase, ejecutar todos los tests para asegurar que no se rompe nada.

5. **Documentación**: Actualizar este plan con el progreso real después de cada fase.

## Estimación de Tiempo Total

- **Fase 1**: 2-3 horas
- **Fase 2**: 3-4 horas
- **Fase 3**: 3-4 horas
- **Fase 4**: 4-5 horas
- **Fase 5**: 3-4 horas
- **Fase 6**: 2-3 horas
- **Fase 7**: 2-3 horas
- **Fase 8**: 2-3 horas
- **Fase 9**: 1-2 horas
- **Fase 10**: 2-3 horas
- **Fase 11**: 1-2 horas

**Total estimado**: 25-35 horas

---

**Fecha de creación**: 2026-01-17
**Estado**: Pendiente de implementación
