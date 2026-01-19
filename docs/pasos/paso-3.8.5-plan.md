# Plan de Trabajo - Paso 3.8.5: Tests de Rutas

**Fecha:** 19 de enero de 2026  
**Estado:** 📋 Planificación completada  
**Objetivo:** Aumentar la cobertura de tests para acercarnos al 100%

---

## Resumen de Cobertura Actual (de tests/coverage2)

| Área | Líneas | % Cobertura | Estado |
|------|--------|-------------|--------|
| **Total Global** | 10,096 / 10,647 | **94.82%** | ✅ Bueno |
| Livewire/Public | 854 / 854 | **100%** | ✅ Completo |
| Livewire/Admin | 5,982 / 6,290 | **95.10%** | ✅ Bueno |
| Policies | 170 / 170 | **100%** | ✅ Completo |
| Http | 1,109 / 1,118 | **99.19%** | ✅ Muy bueno |
| Models | 592 / 670 | **88.36%** | ⚠️ Mejorable |
| Exports | 436 / 478 | **91.21%** | ✅ Bueno |
| Imports | 223 / 259 | **86.10%** | ⚠️ Mejorable |
| Mail | 4 / 13 | **30.77%** | ❌ Crítico |
| Support/helpers.php | 46 / 99 | **46.46%** | ❌ Crítico |
| Observers | 61 / 63 | **96.83%** | ✅ Muy bueno |

---

## Tests de Rutas Existentes

Ya existen 3 archivos de tests de rutas:

| Archivo | Tests | Assertions |
|---------|-------|------------|
| `PublicRoutesTest.php` | 39 | 52 |
| `AdminRoutesTest.php` | 90 | 107 |
| `DocumentsRoutesTest.php` | 5 | - |

---

## Plan de Trabajo Detallado

El paso 3.8.5 indica "Tests de Rutas" con el objetivo de verificar middleware y permisos. Dado que ya tenemos tests robustos de rutas, el plan se enfocará en **completar la cobertura faltante** para acercarnos al 100%.

---

## Fase 1: Análisis de Áreas Críticas (Prioridad Alta)

### 1.1 Mail - NewsletterVerificationMail (30.77% → 100%)

**Archivo:** `app/Mail/NewsletterVerificationMail.php`  
**Líneas faltantes:** 9 de 13

**Tareas:**
- [ ] Crear test `tests/Feature/Mail/NewsletterVerificationMailTest.php`
- [ ] Test de construcción del mailable
- [ ] Test del método `envelope()` (subject, from)
- [ ] Test del método `content()` (view correcta)
- [ ] Test del método `attachments()` (array vacío)
- [ ] Test de renderizado del contenido

### 1.2 Support/helpers.php (46.46% → 90%+)

**Archivo:** `app/Support/helpers.php`  
**Líneas faltantes:** 53 de 99

**Tareas:**
- [ ] Crear test `tests/Feature/Support/HelpersTest.php`
- [ ] Identificar funciones helper no cubiertas
- [ ] Tests para cada función helper global

---

## Fase 2: Modelos con Cobertura Incompleta (Prioridad Media-Alta)

### 2.1 Model: ErasmusEvent (81.54% → 100%)

**Líneas faltantes:** 24 de 130

**Tareas:**
- [ ] Revisar cobertura detallada en `tests/coverage2/Models/ErasmusEvent.php.html`
- [ ] Crear tests para métodos no cubiertos
- [ ] Tests de scopes no utilizados
- [ ] Tests de accessors/mutators

### 2.2 Model: NewsPost (83.64% → 100%)

**Líneas faltantes:** 18 de 110

**Tareas:**
- [ ] Tests para métodos de Media Library no cubiertos
- [ ] Tests de scopes adicionales

### 2.3 Model: Notification (84.62% → 100%)

**Líneas faltantes:** 6 de 39

**Tareas:**
- [ ] Tests para métodos no cubiertos

### 2.4 Model: Setting (79.49% → 100%)

**Líneas faltantes:** 8 de 39

**Tareas:**
- [ ] Tests para métodos no cubiertos

### 2.5 Model: AcademicYear (91.67% → 100%)

**Líneas faltantes:** 4 de 48

**Tareas:**
- [ ] Tests para 2 métodos no cubiertos

### 2.6 Models/Concerns (60.87% → 100%)

**Líneas faltantes:** 18 de 46

**Tareas:**
- [ ] Tests para traits de modelos

---

## Fase 3: Imports y Exports (Prioridad Media)

### 3.1 AuditLogsExport (64.81% → 100%)

**Líneas faltantes:** 38 de 108

**Tareas:**
- [ ] Tests de exportación con filtros
- [ ] Tests de formateo de columnas
- [ ] Tests de estilos

### 3.2 CallsImport (86.18% → 100%)

**Líneas faltantes:** 21 de 152

**Tareas:**
- [ ] Tests de validación de importación
- [ ] Tests de errores de importación

### 3.3 UsersImport (85.98% → 100%)

**Líneas faltantes:** 15 de 107

**Tareas:**
- [ ] Tests de validación de importación
- [ ] Tests de errores de importación

---

## Fase 4: Componentes Livewire Admin (Prioridad Media)

Aunque la cobertura general es 95.10%, hay componentes específicos por mejorar:

### 4.1 Admin/Calls (93.63%)

**Líneas faltantes:** 102 de 1,602

**Tareas:**
- [ ] Revisar componentes Phases y Resolutions
- [ ] Tests de acciones específicas no cubiertas

### 4.2 Admin/Translations (91.47%)

**Líneas faltantes:** 37 de 434

**Tareas:**
- [ ] Tests de filtros y búsqueda adicionales

### 4.3 Admin/Programs (92.63%)

**Líneas faltantes:** 23 de 312

### 4.4 Admin/Settings (92.68%)

**Líneas faltantes:** 21 de 287

---

## Fase 5: Observers (Prioridad Baja)

### 5.1 CallObserver y ResolutionObserver (94.12% cada uno)

**Líneas faltantes:** 1 línea cada uno

**Tareas:**
- [ ] Tests para métodos no cubiertos (probablemente `restored()`)

---

## Fase 6: Tests de Rutas Adicionales

### 6.1 Tests de Rutas de API/Middleware

- [ ] Tests de middleware de localización (`SetLocale`)
- [ ] Tests de redirecciones
- [ ] Tests de rutas con parámetros opcionales

### 6.2 Tests de Rutas de Autenticación (Fortify)

- [ ] Verificar cobertura de rutas de login
- [ ] Verificar cobertura de rutas de registro
- [ ] Verificar cobertura de rutas de 2FA

---

## Orden de Ejecución Recomendado

| Orden | Fase | Descripción | Impacto |
|-------|------|-------------|---------|
| 1 | Fase 1 | Áreas críticas (Mail y helpers) | Alto en % global |
| 2 | Fase 2 | Modelos incompletos | Mejora robustez |
| 3 | Fase 3 | Imports/Exports | Funcionalidades importantes |
| 4 | Fase 5 | Observers | Fácil de completar |
| 5 | Fase 4 | Livewire Admin | Ya tiene buena cobertura |
| 6 | Fase 6 | Tests adicionales de rutas | Completitud |

---

## Meta de Cobertura

| Métrica | Actual | Objetivo |
|---------|--------|----------|
| Líneas | 94.82% | **98%+** |
| Métodos | 86.69% | **95%+** |
| Clases | 61.49% | **85%+** |

---

## Archivos de Test a Crear

| # | Archivo | Propósito |
|---|---------|-----------|
| 1 | `tests/Feature/Mail/NewsletterVerificationMailTest.php` | Tests del mailable |
| 2 | `tests/Feature/Support/HelpersTest.php` | Tests de funciones helper |
| 3 | `tests/Feature/Models/ErasmusEventMethodsTest.php` | Métodos no cubiertos |
| 4 | `tests/Feature/Models/NewsPostMethodsTest.php` | Métodos no cubiertos |
| 5 | `tests/Feature/Exports/AuditLogsExportTest.php` | Exportación completa |
| 6 | `tests/Feature/Imports/CallsImportValidationTest.php` | Validación import |
| 7 | `tests/Feature/Imports/UsersImportValidationTest.php` | Validación import |

---

## Notas Técnicas

### Generación de Cobertura

Para verificar la cobertura durante el desarrollo:

```bash
# Cobertura parcial (se guarda en tests/coverage)
php artisan test --coverage-html=tests/coverage

# Cobertura de un archivo específico
php artisan test tests/Feature/Mail/NewsletterVerificationMailTest.php --coverage-html=tests/coverage
```

### Cobertura de Referencia

La cobertura completa de referencia está en `tests/coverage2` (solo lectura).
No se debe sobrescribir para mantener el baseline.

---

## Verificación de Completitud

Al finalizar cada fase, verificar:

- [ ] Tests pasan sin errores
- [ ] Cobertura de líneas aumentó según lo esperado
- [ ] No hay regresiones en otros tests
- [ ] Código sigue las convenciones del proyecto

---

## Referencias

- [Planificación general](../planificacion_pasos.md)
- [Cobertura actual](../../tests/coverage2/index.html)
- [Tests de rutas públicas](../../tests/Feature/Routes/PublicRoutesTest.php)
- [Tests de rutas admin](../../tests/Feature/Routes/AdminRoutesTest.php)
