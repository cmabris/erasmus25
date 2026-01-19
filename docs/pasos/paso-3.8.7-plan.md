# Plan de Trabajo: Paso 3.8.7 - Tests de Integración

## Resumen de Cobertura Actual

**Estado de cobertura global de la aplicación (según tests/coverage2):**

| Área | Líneas | Funciones/Métodos | Clases/Traits |
|------|--------|-------------------|---------------|
| **TOTAL** | **96.72%** (10298/10647) | **89.73%** (1416/1578) | **66.09%** (115/174) |
| Actions | 100.00% (23/23) | 100.00% (3/3) | 100.00% (3/3) |
| Exports | 98.95% (473/478) | 88.37% (38/43) | 50.00% (3/6) |
| Http | 99.28% (1110/1118) | 96.00% (96/100) | 93.55% (29/31) |
| Imports | 91.51% (237/259) | 86.21% (25/29) | 0.00% (0/2) |
| Livewire | 96.14% (7219/7509) | 86.89% (921/1060) | 43.68% (38/87) |
| Mail | 100.00% (13/13) | 100.00% (5/5) | 100.00% (1/1) |
| Models | 100.00% (670/670) | 100.00% (161/161) | 100.00% (19/19) |
| Observers | 98.41% (62/63) | 95.83% (23/24) | 75.00% (3/4) |
| Policies | 100.00% (170/170) | 100.00% (118/118) | 100.00% (16/16) |
| Providers | 93.75% (30/32) | 71.43% (5/7) | 0.00% (0/2) |
| Services | 100.00% (82/82) | 100.00% (10/10) | 100.00% (1/1) |
| Support | 90.87% (209/230) | 61.11% (11/18) | 100.00% (2/2) |

---

## Áreas que Necesitan Mejorar (Ordenadas por Prioridad)

### 🔴 PRIORIDAD ALTA - Cobertura de Líneas < 95%

#### 1. Support/helpers.php (78.79% líneas, 41.67% métodos)
**Líneas faltantes:** 21/99
**Métodos faltantes:** 7/12

Funciones sin cobertura completa:
- `format_number()` - falta branch de NumberFormatter no disponible y fallback de excepción
- `format_date()` - falta cobertura de locales alternativos
- `format_datetime()` - falta cobertura de locales alternativos
- `setting()` - falta cobertura de ramas de center_logo (URL, storage path, public path)
- `setLanguage()` - falta cobertura de excepción

#### 2. Imports/UsersImport.php (88.79% líneas, 85.71% métodos)
**Líneas faltantes:** 12/107
**Métodos faltantes:** 2/14

#### 3. Imports/CallsImport.php (93.42% líneas, 86.67% métodos)
**Líneas faltantes:** 10/152
**Métodos faltantes:** 2/15

### 🟡 PRIORIDAD MEDIA - Cobertura de Métodos < 90%

#### 4. Livewire/Search/GlobalSearch.php (94.78% líneas, 77.27% métodos)
**Métodos faltantes:** 5/22

#### 5. Livewire/Language/Switcher.php (84.62% líneas, 80.00% métodos)
**Líneas faltantes:** 2/13
**Métodos faltantes:** 1/5

#### 6. Livewire/Notifications/Index.php (95.60% líneas, 77.78% métodos)
**Líneas faltantes:** 4/91
**Métodos faltantes:** 4/18

#### 7. Observers/NewsPostObserver.php (93.33% líneas, 83.33% métodos)
**Líneas faltantes:** 1/15
**Métodos faltantes:** 1/6 (método `deleted` no cubierto)

### 🟢 PRIORIDAD BAJA - Componentes Admin con Métodos < 90%

| Componente | Líneas | Métodos | Líneas Faltantes |
|------------|--------|---------|------------------|
| Admin/Calls | 94.69% | 80.41% | 85 |
| Admin/Translations | 93.55% | 72.34% | 28 |
| Admin/Roles | 93.79% | 76.60% | 21 |
| Admin/Programs | 93.91% | 81.48% | 19 |
| Admin/Settings | 92.68% | 79.41% | 21 |
| Admin/DocumentCategories | 96.50% | 79.41% | 7 |
| Admin/Events | 95.77% | 86.81% | 24 |
| Admin/Documents | 98.29% | 88.24% | 6 |
| Admin/News | 95.55% | 84.51% | 21 |

---

## Plan de Trabajo Detallado

### FASE 1: Tests de Integración para Support/helpers.php
**Objetivo:** Alcanzar 100% de cobertura

#### 1.1 Tests para `format_number()`
```php
// Test sin NumberFormatter disponible (mock)
// Test con excepción en NumberFormatter
// Test con locale 'es' (separador decimal coma)
// Test con locale 'en' (separador decimal punto)
// Test con diferentes cantidades de decimales
```

#### 1.2 Tests para `format_date()` y `format_datetime()`
```php
// Test con string de fecha
// Test con DateTime
// Test con Carbon
// Test con locale 'en' (formato m/d/Y)
// Test con formato personalizado
// Test con locale no definido (fallback Y-m-d)
```

#### 1.3 Tests para `setting()` - center_logo branches
```php
// Test con URL completa (filter_var VALIDATE_URL)
// Test con path de storage (logos/xxx.jpg)
// Test con path público (/path)
// Test con valor normal (return value directamente)
```

#### 1.4 Tests para `setLanguage()` - excepciones
```php
// Test con idioma inexistente
// Test con idioma inactivo
// Test con excepción en base de datos (mock)
```

---

### FASE 2: Tests de Integración para Imports
**Objetivo:** Alcanzar 95%+ de cobertura

#### 2.1 UsersImport
```php
// Test de importación con roles inexistentes
// Test de importación con validación fallida
// Test de métodos de interfaz no cubiertos
// Test de chunks grandes
```

#### 2.2 CallsImport
```php
// Test de importación con programa inexistente
// Test de importación con año académico inexistente
// Test de validación de fechas inválidas
// Test de métodos de error handling
```

---

### FASE 3: Tests de Integración para Observers
**Objetivo:** Alcanzar 100% de cobertura

#### 3.1 NewsPostObserver
```php
// Test de deleted() event
// Test de restored() event
// Test de forceDeleted() event
// Test de updated sin cambio en published_at
// Test de publicación con fecha futura (no debe notificar)
```

---

### FASE 4: Tests de Integración para Livewire Components
**Objetivo:** Mejorar cobertura de métodos al 90%+

#### 4.1 Search/GlobalSearch.php
```php
// Test de búsqueda vacía
// Test de búsqueda con resultados de cada tipo
// Test de navegación a resultados
// Test de métodos de filtrado no cubiertos
```

#### 4.2 Language/Switcher.php
```php
// Test de cambio de idioma con idioma inválido
// Test de renderizado con idioma no disponible
```

#### 4.3 Notifications/Index.php
```php
// Test de paginación
// Test de filtrado por tipo
// Test de marcar múltiples como leídas
// Test de eliminación de notificaciones
```

---

### FASE 5: Tests de Flujos End-to-End (Integración Completa)
**Objetivo:** Verificar flujos completos de la aplicación

#### 5.1 Flujo de Convocatoria Completa
```php
it('completes a full call lifecycle', function () {
    // 1. Admin crea convocatoria
    // 2. Admin añade fases
    // 3. Admin publica convocatoria
    // 4. Sistema notifica a usuarios
    // 5. Usuario público ve convocatoria
    // 6. Admin añade resolución
    // 7. Admin publica resolución
    // 8. Usuario público ve resolución
    // 9. Admin archiva convocatoria
});
```

#### 5.2 Flujo de Publicación de Noticias
```php
it('completes a full news publication flow', function () {
    // 1. Editor crea noticia como borrador
    // 2. Editor añade imágenes
    // 3. Editor añade etiquetas
    // 4. Editor publica noticia
    // 5. Sistema notifica a usuarios suscritos
    // 6. Usuario público ve noticia
    // 7. Usuario busca noticia (GlobalSearch)
    // 8. Editor edita noticia
    // 9. Editor despublica noticia
});
```

#### 5.3 Flujo de Suscripción a Newsletter
```php
it('completes a newsletter subscription flow', function () {
    // 1. Usuario rellena formulario de suscripción
    // 2. Sistema envía email de verificación
    // 3. Usuario verifica email
    // 4. Sistema confirma suscripción
    // 5. Admin ve suscriptor en panel
    // 6. Admin exporta lista
});
```

#### 5.4 Flujo de Gestión de Usuarios y Roles
```php
it('completes a user management flow', function () {
    // 1. Admin crea nuevo rol con permisos
    // 2. Admin crea nuevo usuario
    // 3. Admin asigna rol a usuario
    // 4. Usuario intenta acceder a área restringida
    // 5. Sistema verifica permisos
    // 6. Admin revoca permisos
    // 7. Sistema registra acción en audit log
});
```

#### 5.5 Flujo de Gestión de Documentos
```php
it('completes a document management flow', function () {
    // 1. Admin crea categoría de documentos
    // 2. Admin sube documento
    // 3. Admin asocia a programa
    // 4. Usuario público descarga documento
    // 5. Admin actualiza documento
    // 6. Admin soft-delete documento
    // 7. Admin restaura documento
});
```

#### 5.6 Flujo de Calendario de Eventos
```php
it('completes an event calendar flow', function () {
    // 1. Admin crea evento
    // 2. Admin asocia evento a convocatoria
    // 3. Usuario público ve calendario
    // 4. Usuario filtra por mes
    // 5. Usuario ve detalle de evento
});
```

---

### FASE 6: Tests de Internacionalización (i18n)
**Objetivo:** Verificar funcionamiento correcto del sistema i18n

```php
it('handles language switching correctly', function () {
    // 1. Verificar idioma por defecto
    // 2. Cambiar idioma
    // 3. Verificar persistencia en sesión
    // 4. Verificar persistencia en cookie
    // 5. Verificar traducciones dinámicas
    // 6. Verificar formato de fechas según locale
    // 7. Verificar formato de números según locale
});
```

---

### FASE 7: Tests de Exports
**Objetivo:** Alcanzar 100% de cobertura en clases

#### 7.1 AuditLogsExport
```php
// Test de exportación vacía
// Test de exportación con filtros
// Test de formateado de datos
```

#### 7.2 CallsExport
```php
// Test de columnas condicionales
// Test de formateo de fechas
// Test de campos JSON (destinos, baremo)
```

#### 7.3 ResolutionsExport
```php
// Test de exportación con/sin archivos
// Test de formateo de fechas de publicación
```

---

### FASE 8: Tests de Providers (Opcional)
**Objetivo:** Mejorar cobertura si es posible

#### 8.1 AppServiceProvider
```php
// Test de register() method
// Test de boot() method con Model::unguard en entorno de test
```

#### 8.2 FortifyServiceProvider
```php
// Test de configuración de vistas personalizadas
// Test de callbacks de autenticación
```

---

## Archivos de Test a Crear

| Archivo | Ubicación | Prioridad |
|---------|-----------|-----------|
| `HelpersIntegrationTest.php` | `tests/Feature/Support/` | Alta |
| `ImportsIntegrationTest.php` | `tests/Feature/Imports/` | Alta |
| `NewsPostObserverTest.php` | `tests/Feature/Observers/` | Media |
| `GlobalSearchIntegrationTest.php` | `tests/Feature/Livewire/Search/` | Media |
| `NotificationsIntegrationTest.php` | `tests/Feature/Livewire/Notifications/` | Media |
| `E2E/CallLifecycleTest.php` | `tests/Feature/E2E/` | Media |
| `E2E/NewsPublicationTest.php` | `tests/Feature/E2E/` | Media |
| `E2E/NewsletterFlowTest.php` | `tests/Feature/E2E/` | Media |
| `E2E/UserManagementTest.php` | `tests/Feature/E2E/` | Media |
| `E2E/DocumentFlowTest.php` | `tests/Feature/E2E/` | Baja |
| `E2E/EventCalendarTest.php` | `tests/Feature/E2E/` | Baja |
| `I18nIntegrationTest.php` | `tests/Feature/I18n/` | Media |
| `ExportsIntegrationTest.php` | `tests/Feature/Exports/` | Baja |

---

## Métricas Objetivo

| Métrica | Estado Actual | Objetivo |
|---------|---------------|----------|
| Cobertura de Líneas | 96.72% | 98%+ |
| Cobertura de Métodos | 89.73% | 95%+ |
| Cobertura de Clases | 66.09% | 80%+ |

---

## Estimación de Tests a Desarrollar

| Fase | Nº Tests Estimados | Prioridad |
|------|-------------------|-----------|
| Fase 1 (helpers) | ~15-20 | Alta |
| Fase 2 (imports) | ~10-15 | Alta |
| Fase 3 (observers) | ~5-8 | Media |
| Fase 4 (livewire) | ~15-20 | Media |
| Fase 5 (E2E) | ~6-10 | Media |
| Fase 6 (i18n) | ~8-12 | Media |
| Fase 7 (exports) | ~8-10 | Baja |
| Fase 8 (providers) | ~4-6 | Opcional |
| **TOTAL** | **~71-101** | - |

---

## Notas Importantes

1. **Orden de ejecución:** Las fases deben ejecutarse en orden de prioridad para maximizar el impacto en la cobertura.

2. **Tests incrementales:** Después de cada fase, generar cobertura parcial en `tests/coverage` para verificar progreso.

3. **Mocking:** Para algunos tests (como NumberFormatter, excepciones de BD), será necesario usar mocks.

4. **RefreshDatabase:** Los tests E2E deben usar el trait `RefreshDatabase` para asegurar un estado limpio.

5. **Factories:** Aprovechar las factories existentes para crear datos de prueba consistentes.

6. **Cobertura de clases:** El bajo porcentaje de cobertura de clases (66.09%) no es crítico - indica que no todas las clases tienen tests que ejecuten TODOS sus métodos. La cobertura de líneas y métodos es más relevante.

---

## Comando para Verificar Cobertura

```bash
# Ejecutar tests con cobertura parcial
php artisan test tests/Feature/Support/HelpersIntegrationTest.php --coverage-html tests/coverage

# Ejecutar todos los tests de una fase
php artisan test tests/Feature/E2E/ --coverage-html tests/coverage
```

---

**Fecha de Creación:** Enero 2026
**Estado:** 📋 Planificación completada - Listo para implementación
