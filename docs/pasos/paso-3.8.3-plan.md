# Plan de Trabajo - Paso 3.8.3: Tests de Componentes Livewire Públicos

## Objetivo
Aumentar la cobertura de tests de los componentes Livewire públicos del 94.73% actual al 100% (o lo más cercano posible).

## Estado Actual de Cobertura

### Resumen General
- **Líneas**: 94.73% (809/854) - Faltan 45 líneas
- **Métodos**: 92.59% (125/135) - Faltan 10 métodos
- **Clases**: 60.00% (9/15) - Faltan 6 clases

### Componentes con 100% de Cobertura ✅
- `Calls/Index.php` - 100%
- `Calls/Show.php` - 100%
- `News/Index.php` - 100%
- `News/Show.php` - 100%
- `Home.php` - 100%
- `Documents/Index.php` - 100%
- `Events/Show.php` - 100%
- `Newsletter/Subscribe.php` - 100%
- `Programs/Index.php` - 100%

### Componentes que Necesitan Trabajo

#### 1. Documents/Show.php
- **Líneas**: 85.37% (70/82) - Faltan 12 líneas
- **Métodos**: 71.43% (10/14) - Faltan 4 métodos
- **Clases**: 0% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas relacionadas con `hasMediaConsent()` cuando no hay consentimientos
- Líneas relacionadas con `mediaConsents()` cuando no hay consentimientos
- Líneas relacionadas con `relatedDocuments()` cuando no hay categoría ni programa
- Líneas relacionadas con `relatedCalls()` cuando no hay programa
- Líneas relacionadas con `documentTypeConfig()` para tipos no cubiertos (seguro, consentimiento, faq, otro)
- Líneas relacionadas con `download()` cuando hay error
- Líneas relacionadas con `formatBytes()` para diferentes tamaños (MB, GB, TB)

**Métodos sin cubrir:**
- `hasMediaConsent()` - Casos sin consentimientos
- `mediaConsents()` - Casos sin consentimientos
- `relatedDocuments()` - Casos sin categoría ni programa
- `documentTypeConfig()` - Tipos no cubiertos

#### 2. Events/Calendar.php
- **Líneas**: 98.02% (99/101) - Faltan 2 líneas
- **Métodos**: 93.75% (15/16) - Falta 1 método
- **Clases**: 0% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas relacionadas con manejo de errores o casos edge en `eventsByDate()`
- Líneas relacionadas con `weekDays()` cuando hay conversión de tipos

**Métodos sin cubrir:**
- `eventsByDate()` - Casos edge con agrupación

#### 3. Events/Index.php
- **Líneas**: 96.43% (54/56) - Faltan 2 líneas
- **Métodos**: 91.67% (11/12) - Falta 1 método
- **Clases**: 0% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas relacionadas con `updatedDateFrom()` o `updatedDateTo()` cuando hay valores inválidos
- Líneas relacionadas con manejo de errores en filtros

**Métodos sin cubrir:**
- `updatedDateFrom()` o `updatedDateTo()` - Casos edge

#### 4. Newsletter/Unsubscribe.php
- **Líneas**: 86.96% (40/46) - Faltan 6 líneas
- **Métodos**: 50% (2/4) - Faltan 2 métodos
- **Clases**: 0% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas 79-82: Bloque `catch` en `unsubscribeByToken()` - Manejo de excepciones
- Líneas 123-126: Bloque `catch` en `unsubscribeByEmail()` - Manejo de excepciones

**Métodos sin cubrir:**
- `unsubscribeByToken()` - Bloque catch de excepciones
- `unsubscribeByEmail()` - Bloque catch de excepciones

#### 5. Newsletter/Verify.php
- **Líneas**: 86.96% (20/23) - Faltan 3 líneas
- **Métodos**: 66.67% (2/3) - Falta 1 método
- **Clases**: 0% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas 70-72: Bloque `catch` en `verifySubscription()` - Manejo de excepciones

**Métodos sin cubrir:**
- `verifySubscription()` - Bloque catch de excepciones

#### 6. Programs/Show.php
- **Líneas**: 83.33% (100/120) - Faltan 20 líneas
- **Métodos**: 83.33% (5/6) - Falta 1 método
- **Clases**: 0% (0/1) - Falta 1 clase

**Líneas sin cubrir identificadas:**
- Líneas relacionadas con `programConfig()` para códigos no cubiertos:
  - `ADU` (Educación de Adultos)
  - `KA1` (Movilidad) - cuando no es VET/HED/SCH/ADU
  - `JM` (Jean Monnet)
  - `DISCOVER` (DiscoverEU)
  - `default` (caso por defecto)
- Líneas relacionadas con `relatedCalls()` cuando no hay convocatorias
- Líneas relacionadas con `relatedNews()` cuando no hay noticias
- Líneas relacionadas con `otherPrograms()` cuando no hay otros programas

**Métodos sin cubrir:**
- `programConfig()` - Casos de códigos no cubiertos

## Plan de Implementación

### Fase 1: Documents/Show.php (Prioridad Alta) ✅ COMPLETADO AL 100%
**Objetivo**: Aumentar de 85.37% a 100%

**Estado**: ✅ **COMPLETADO AL 100%** - 47 tests pasando (1 skipped debido a restricción de enum)
**Cobertura Final**: 
- ✅ Líneas: **100.00%** (82/82)
- ✅ Métodos: **100.00%** (14/14)
- ✅ Clases: **100.00%** (1/1)

#### Tareas:
1. **Test para `hasMediaConsent()` sin consentimientos**
   - Crear test que verifique que retorna `false` cuando no hay consentimientos
   - Verificar que retorna `false` cuando hay consentimientos pero están revocados

2. **Test para `mediaConsents()` sin consentimientos**
   - Crear test que verifique que retorna colección vacía cuando no hay consentimientos
   - Verificar que retorna solo consentimientos activos (no revocados)

3. **Test para `relatedDocuments()` sin categoría ni programa**
   - Crear test con documento sin categoría ni programa
   - Verificar que retorna colección vacía o documentos activos sin filtro específico

4. **Test para `relatedCalls()` sin programa**
   - Crear test con documento sin programa
   - Verificar que retorna colección vacía

5. **Test para `documentTypeConfig()` - Tipos no cubiertos**
   - Crear tests para tipos: `seguro`, `consentimiento`, `faq`, `otro`
   - Verificar que retorna configuración correcta para cada tipo

6. **Test para `download()` con error**
   - Crear test que simule error al descargar (archivo no encontrado)
   - Verificar que retorna 404

7. **Test para `formatBytes()` - Diferentes tamaños**
   - Crear tests para MB, GB, TB
   - Verificar formato correcto para cada unidad

**Archivo**: `tests/Feature/Livewire/Public/Documents/ShowTest.php`
**Tests implementados**: 15 tests nuevos
- ✅ Test para `hasMediaConsent()` sin consentimientos
- ✅ Test para `hasMediaConsent()` con consentimientos revocados
- ✅ Test para `hasMediaConsent()` con `consent_given = false`
- ✅ Test para `hasMediaConsent()` con consentimiento activo
- ✅ Test para `mediaConsents()` sin consentimientos
- ✅ Test para `mediaConsents()` con todos revocados
- ✅ Test para `mediaConsents()` filtrando solo activos
- ✅ Test para `relatedDocuments()` sin documentos relacionados
- ✅ Test para `relatedCalls()` sin programa (ya existía, verificado)
- ✅ Test para `documentTypeConfig()` - tipo `seguro`
- ✅ Test para `documentTypeConfig()` - tipo `consentimiento`
- ✅ Test para `documentTypeConfig()` - tipo `faq`
- ✅ Test para `documentTypeConfig()` - tipo `otro`
- ⏭️ Test para `documentTypeConfig()` - caso default (skipped por restricción enum)
- ✅ Test para `documentTypeConfig()` - tipo `convocatoria`
- ✅ Test para `documentTypeConfig()` - tipo `modelo`
- ✅ Test para `formatBytes()` - Bytes (< 1KB)
- ✅ Test para `formatBytes()` - MB
- ✅ Test para `formatBytes()` - GB
- ✅ Test para `formatBytes()` - TB
- ✅ Test para `fileSize()` - retorna null cuando no hay archivo
- ✅ Test para `fileExtension()` - retorna null cuando no hay archivo
- ✅ Test para `fileExtension()` - retorna extensión correcta cuando hay archivo
- ✅ Test para `relatedDocuments()` - caso elseif (sin categoría pero con programa)

---

### Fase 2: Programs/Show.php (Prioridad Alta) ✅ COMPLETADO AL 100%
**Objetivo**: Aumentar de 83.33% a 100%

**Estado**: ✅ **COMPLETADO AL 100%** - 28 tests pasando
**Cobertura Final**: 
- ✅ Líneas: **100.00%** (120/120)
- ✅ Métodos: **100.00%** (6/6)
- ✅ Clases: **100.00%** (1/1)

#### Tareas:
1. **Test para `programConfig()` - Código ADU**
   - Crear programa con código que contenga `ADU`
   - Verificar que retorna configuración de "Educación de Adultos"

2. **Test para `programConfig()` - Código KA1 puro**
   - Crear programa con código `KA1xx` que no contenga VET/HED/SCH/ADU
   - Verificar que retorna configuración de "Movilidad"

3. **Test para `programConfig()` - Código JM**
   - Crear programa con código que contenga `JM`
   - Verificar que retorna configuración de "Jean Monnet"

4. **Test para `programConfig()` - Código DISCOVER**
   - Crear programa con código que contenga `DISCOVER`
   - Verificar que retorna configuración de "DiscoverEU"

5. **Test para `programConfig()` - Caso default**
   - Crear programa con código que no coincida con ningún patrón
   - Verificar que retorna configuración por defecto "Erasmus+"

6. **Test para `relatedCalls()` sin convocatorias**
   - Crear programa sin convocatorias relacionadas
   - Verificar que retorna colección vacía

7. **Test para `relatedNews()` sin noticias**
   - Crear programa sin noticias relacionadas
   - Verificar que retorna colección vacía

8. **Test para `otherPrograms()` sin otros programas**
   - Crear programa único en la base de datos
   - Verificar que retorna colección vacía

**Archivo**: `tests/Feature/Livewire/Public/Programs/ShowTest.php`
**Tests implementados**: 10 tests nuevos
- ✅ Test para `programConfig()` - Código ADU
- ✅ Test para `programConfig()` - Código KA1 puro (sin VET/HED/SCH/ADU)
- ✅ Test para `programConfig()` - Código JM
- ✅ Test para `programConfig()` - Código DISCOVER
- ✅ Test para `programConfig()` - Caso default (código desconocido)
- ✅ Test para `programConfig()` - Caso default (código null)
- ✅ Test para `relatedCalls()` sin convocatorias
- ✅ Test para `relatedNews()` sin noticias
- ✅ Test para `otherPrograms()` sin otros programas

---

### Fase 3: Newsletter/Verify.php (Prioridad Media) ✅ COMPLETADO AL 100%
**Objetivo**: Aumentar de 86.96% a 100%

**Estado**: ✅ **COMPLETADO AL 100%** - 12 tests pasando
**Cobertura Final**: 
- ✅ Líneas: **100.00%** (23/23)
- ✅ Métodos: **100.00%** (3/3)
- ✅ Clases: **100.00%** (1/1)

#### Tareas:
1. **Test para manejo de excepciones en `verifySubscription()`**
   - Usar Event Listener (`updating`) para simular error en `update()`
   - Verificar que se establece `status = 'error'`
   - Verificar que se establece mensaje de error apropiado

**Archivo**: `tests/Feature/Livewire/Public/Newsletter/VerifyTest.php`
**Tests implementados**: 1 test nuevo
- ✅ Test para manejo de excepciones cuando `verify()` falla (usando Event Listener)

---

### Fase 4: Newsletter/Unsubscribe.php (Prioridad Media)
**Objetivo**: Aumentar de 86.96% a 100%

#### Tareas:
1. **Test para manejo de excepciones en `unsubscribeByToken()`**
   - Mockear el método `unsubscribe()` del modelo para que lance excepción
   - Verificar que se establece `status = 'error'`
   - Verificar que se establece mensaje de error apropiado

2. **Test para manejo de excepciones en `unsubscribeByEmail()`**
   - Mockear el método `unsubscribe()` del modelo para que lance excepción
   - Verificar que se establece `status = 'error'`
   - Verificar que se establece mensaje de error apropiado

**Archivo**: `tests/Feature/Livewire/Public/Newsletter/UnsubscribeTest.php`
**Tests estimados**: 2 tests nuevos

---

### Fase 5: Events/Calendar.php (Prioridad Baja) ✅ COMPLETADO AL 100%
**Objetivo**: Aumentar de 98.02% a 100%

**Estado**: ✅ **COMPLETADO AL 100%** - 22 tests pasando
**Cobertura Final**: 
- ✅ Líneas: **100.00%** (101/101)
- ✅ Métodos: **100.00%** (16/16)
- ✅ Clases: **100.00%** (1/1)

#### Tareas:
1. **Test para `previous()` - Caso 'week'**
   - Crear test que verifique navegación a semana anterior en vista semanal
   - Verificar que retorna fecha de la semana anterior

2. **Test para `previous()` - Caso 'day'**
   - Crear test que verifique navegación a día anterior en vista diaria
   - Verificar que retorna fecha del día anterior

**Archivo**: `tests/Feature/Livewire/Public/Events/CalendarTest.php`
**Tests implementados**: 2 tests nuevos
- ✅ Test para `previous()` - navegación a semana anterior en vista 'week'
- ✅ Test para `previous()` - navegación a día anterior en vista 'day'

---

### Fase 6: Events/Index.php (Prioridad Baja) ✅ COMPLETADO AL 100%
**Objetivo**: Aumentar de 96.43% a 100%

**Estado**: ✅ **COMPLETADO AL 100%** - 16 tests pasando
**Cobertura Final**: 
- ✅ Líneas: **100.00%** (56/56)
- ✅ Métodos: **100.00%** (12/12)
- ✅ Clases: **100.00%** (1/1)

#### Tareas:
1. **Test para `togglePastEvents()`**
   - Crear test que verifique el toggle de eventos pasados
   - Verificar que cambia el estado y resetea la página

2. **Mejorar tests existentes para acceder a propiedades computed**
   - Asegurar que los tests accedan explícitamente a `events` para cubrir todas las líneas
   - Agregar test para búsqueda en descripción

**Archivo**: `tests/Feature/Livewire/Public/Events/IndexTest.php`
**Tests implementados**: 1 test nuevo + mejoras a tests existentes
- ✅ Test para `togglePastEvents()` - toggle correcto y reset de página
- ✅ Mejora en test de búsqueda para cubrir búsqueda en descripción
- ✅ Mejora en test de rango de fechas para acceder explícitamente a `events`

---

## Estrategia de Testing

### Para Tests de Excepciones
- Usar `Mockery` o `Pest\Laravel\mock()` para mockear métodos que lanzan excepciones
- Verificar que los bloques `catch` se ejecutan correctamente
- Verificar que los mensajes de error son apropiados

### Para Tests de Casos Edge
- Crear datos de prueba que cubran todos los casos posibles
- Verificar que los métodos retornan valores esperados (colecciones vacías, arrays vacíos, etc.)
- Verificar que no se lanzan excepciones inesperadas

### Para Tests de Configuración
- Crear programas/documentos con diferentes configuraciones
- Verificar que se retorna la configuración correcta para cada caso
- Verificar que los valores por defecto se aplican correctamente

## Criterios de Éxito

### Cobertura Objetivo
- **Líneas**: ≥ 99% (idealmente 100%)
- **Métodos**: ≥ 99% (idealmente 100%)
- **Clases**: 100% (todas las clases deben estar cubiertas)

### Validación
1. Ejecutar `php artisan test --coverage-html=tests/coverage` después de cada fase
2. Verificar que la cobertura aumenta según lo esperado
3. Asegurar que todos los tests pasan
4. Verificar que no se rompen tests existentes

## Orden de Ejecución Recomendado

1. **Fase 1**: Documents/Show.php (mayor impacto, más líneas faltantes)
2. **Fase 2**: Programs/Show.php (segundo mayor impacto)
3. **Fase 3**: Newsletter/Verify.php (rápido, solo excepciones)
4. **Fase 4**: Newsletter/Unsubscribe.php (rápido, solo excepciones)
5. **Fase 5**: Events/Calendar.php (casos edge menores)
6. **Fase 6**: Events/Index.php (casos edge menores)

## Notas Importantes

1. **Cobertura de Clases**: El 0% de cobertura de clases en algunos componentes puede ser un falso positivo del reporte. Verificar que los tests realmente cubren la clase completa.

2. **Tests de Excepciones**: Para los tests de excepciones en Newsletter, necesitaremos mockear los métodos del modelo `NewsletterSubscription` para simular errores.

3. **Tests de Configuración**: Para `programConfig()`, asegurarse de probar todos los casos del `match` statement, incluyendo el caso `default`.

4. **Mantenimiento**: Después de completar cada fase, ejecutar todos los tests para asegurar que no se rompe nada.

5. **Documentación**: Actualizar este plan con el progreso real después de cada fase.

## Estimación de Tiempo

- **Fase 1**: 2-3 horas
- **Fase 2**: 1-2 horas
- **Fase 3**: 30 minutos
- **Fase 4**: 30 minutos
- **Fase 5**: 30 minutos
- **Fase 6**: 30 minutos

**Total estimado**: 5-7 horas

---

**Fecha de creación**: 2026-01-17
**Estado**: Pendiente de implementación
