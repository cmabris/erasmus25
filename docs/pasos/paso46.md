# Paso 46: Optimización de Consultas (Paso 3.9.1)

**Fecha de implementación**: 2026-01-20  
**Objetivo**: Optimización de consultas de base de datos para mejorar el rendimiento de la aplicación

---

## Índice de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Prompts y Resultados](#prompts-y-resultados)
3. [Archivos Creados/Modificados](#archivos-creadosmodificados)
4. [Tests Implementados](#tests-implementados)
5. [Métricas de Mejora](#métricas-de-mejora)

---

## Resumen Ejecutivo

Este paso implementó la **Fase 3.9.1: Optimización de Consultas** del plan de desarrollo, abarcando:

- **Detección y eliminación de N+1 queries** en todos los componentes Livewire
- **Sistema de caché** para datos de referencia y estadísticas
- **Índices de base de datos** optimizados
- **Exports con chunking** para eficiencia de memoria
- **Suite de tests de rendimiento** con 29 tests y 83 assertions

### Fases Completadas:

| Fase | Descripción | Estado |
|------|-------------|--------|
| Fase 1 | Auditoría y Detección de N+1 | ✅ Completado |
| Fase 2 | Optimización de Eager Loading | ✅ Completado |
| Fase 3 | Implementación de Caché | ✅ Completado |
| Fase 4 | Índices de Base de Datos | ✅ Completado |
| Fase 5 | Optimizaciones Específicas | ✅ Completado |
| Fase 6 | Testing | ✅ Completado |

---

## Prompts y Resultados

### Prompt 1: Inicio del Paso 3.9.1

**Usuario:**
> En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que hace de índice y otro llamado @planificacion_pasos.md que contiene las tareas a desarrollar en orden. Estamos en situación de comenzar con el desarrollo del Paso 3.9. el cual se compone de varios pasos parciales. Comenzamos con el desarrollo del paso 3.9.1. que corresponde a la optimización de Consultas. Antes de comenzar el paso 3.9.1. quiero que revises los archivos necesarios para la tarea y cuya documentación está en la carpeta docs y me generes un plan de trabajo.

**Resultado:**
- Se revisó la documentación existente del proyecto
- Se analizaron los componentes Livewire y modelos existentes
- Se creó el documento de planificación: `docs/pasos/paso-3.9.1-plan.md`
- El plan incluye 6 fases: Auditoría, Eager Loading, Caché, Índices, Optimizaciones Específicas y Testing

---

### Prompt 2: Inicio de Fase 1

**Usuario:**
> Ya podemos comenzar con la Fase 1

**Resultado:**
- Se configuró Laravel Debugbar para detección de N+1 queries
- Se habilitó la detección automática de N+1 en `config/debugbar.php`
- Se activó `Model::shouldBeStrict()` en `AppServiceProvider.php` (ajustado posteriormente para control granular)

---

### Prompt 3: Automatización de Auditoría

**Usuario:**
> ¿Existe alguna forma de automatizar el proceso de auditoría para la detección de las consultas lentas?

**Resultado:**
- Se explicaron las opciones disponibles:
  1. Tests automatizados con conteo de queries
  2. Middleware de desarrollo para alertas
  3. Custom assertions en PHPUnit/Pest

---

### Prompt 4: Implementación de Detección Automatizada

**Usuario:**
> Sí, por favor

**Resultado:**
- Se creó el trait `Tests\Concerns\CountsQueries` con métodos:
  - `startQueryLog()` / `stopQueryLog()`: Control del log de queries
  - `assertQueryCountLessThan()`: Verificar límite de queries
  - `assertNoDuplicateQueries()`: Detectar N+1 patterns
  - `assertNoSlowQueries()`: Detectar queries lentas
- Se creó `tests/Feature/Performance/QueryOptimizationTest.php` con tests iniciales
- Se documentó en `docs/debugbar-n1-detection.md`

---

### Prompt 5: Confirmación de Fase 2 (implícito)

**Usuario:**
> Sí, pasemos a la Fase 2

**Resultado:**
- Se implementó eager loading en componentes Admin Index:
  - `Admin\News\Index.php`: Añadido `'media'` a `->with()`
  - `Admin\Documents\Index.php`: Añadido `'media'` a `->with()`
  - `Admin\Events\Index.php`: Consolidado `->with()` con `'media'`
  - `Admin\Users\Index.php`: Añadido `withCount(['activities'])`
- Se creó la relación `activities()` en `User.php` model
- Se actualizó la vista Blade para usar `activities_count`

---

### Prompt 6: Verificación de Fase 2.3

**Usuario:**
> Antes de pasar a la Fase 3, ¿es conveniente hacer el 2.3?

**Resultado:**
- Se confirmó que la Fase 2.3 (Optimizar componentes Show públicos) no estaba completa
- Se procedió a implementarla

---

### Prompt 7: Implementación de Fase 2.3

**Usuario:**
> Sí, por favor

**Resultado:**
- Se optimizó `Public\Calls\Show.php`:
  - Eager loading de `program`, `academicYear`, `phases`, `resolutions`
  - Computed properties ahora usan datos ya cargados
- Se optimizó `Public\News\Show.php`:
  - Eager loading de `program`, `academicYear`, `author`, `tags`, `media`
- Se optimizó `Public\Events\Show.php`:
  - Añadido `'media'` al eager loading existente

---

### Prompt 8: Inicio de Fase 3

**Usuario:**
> Pasemos a la Fase 3

**Resultado:**
- Se implementó sistema de caché en modelos:
  - `Program.php`: `getCachedActive()`, `clearCache()`, auto-invalidación
  - `AcademicYear.php`: `getCachedAll()`, `clearCache()` ampliado
  - `DocumentCategory.php`: `getCachedAll()`, `clearCache()`
- Se implementó caché en `Public\Home.php`:
  - Constantes `CACHE_KEY_CALLS`, `CACHE_KEY_NEWS`, `CACHE_KEY_EVENTS`
  - Método `clearCache()` estático
- Se configuró invalidación automática en modelos `Call`, `NewsPost`, `ErasmusEvent`
- Se actualizaron componentes para usar métodos de caché

---

### Prompt 9: Verificación de Fase 2.4 pendiente

**Usuario:**
> Antes de pasar a la Fase 4 he visto que nos hemos dejado sin hacer la 2.4. ¿Puedes verificarlo?

**Resultado:**
- Se confirmó que la Fase 2.4 (Optimizar Computed Properties con Consultas) estaba pendiente

---

### Prompt 10: Implementación de Fase 2.4

**Usuario:**
> Sí, gracias

**Resultado:**
- Se optimizó `Admin\Calls\Show.php`:
  - `canDelete()` y `hasRelationships()` ahora usan `*_count` pre-cargados
  - Evita queries adicionales `->exists()` en cada renderizado

---

### Prompt 11: Inicio de Fase 4

**Usuario:**
> Continuemos con la Fase 4

**Resultado:**
- Se creó migración `2026_01_20_160821_add_performance_indexes_phase_4.php`
- Índices añadidos:
  - `calls`: `deleted_at`, `type`, `modality`
  - `resolutions`: `published_at`, `(call_id, published_at)`
  - `programs`: `is_active`, `(is_active, order)`
- Se ejecutó la migración exitosamente

---

### Prompt 12: Inicio de Fase 5

**Usuario:**
> Sigamos con la Fase 5

**Resultado:**
- Se optimizó `Admin\Dashboard.php`:
  - `loadAlerts()` ahora cacheado (5 minutos)
  - `loadRecentActivities()` ahora cacheado (2 minutos)
  - `clearCache()` actualizado para incluir nuevas claves
- Se optimizaron exports para usar chunking:
  - `CallsExport.php`: Convertido a `FromQuery` + `WithChunkReading`
  - `AuditLogsExport.php`: Convertido a `FromQuery` + `WithChunkReading`
  - `ResolutionsExport.php`: Convertido a `FromQuery` + `WithChunkReading`
- Se optimizó `GlobalSearch.php`:
  - `availablePrograms()` usa `Program::getCachedActive()`
  - `availableAcademicYears()` usa `AcademicYear::getCachedAll()`

---

### Prompt 13: Inicio de Fase 6

**Usuario:**
> Ya he hecho el commit. Podemos pasar a la Fase 6

**Resultado:**
- Se creó `tests/Feature/Performance/CacheInvalidationTest.php` con 16 tests:
  - Program Cache Invalidation (3 tests)
  - AcademicYear Cache Invalidation (4 tests)
  - DocumentCategory Cache Invalidation (3 tests)
  - Home Page Cache Invalidation (4 tests)
  - Dashboard Cache Invalidation (1 test)
  - Home Component Cache (1 test)
- Se corrigió `ErasmusEvent.php` para invalidar caché en creación de eventos públicos
- Suite completa: 29 tests, 83 assertions

---

### Prompt 14: Documentación Final

**Usuario:**
> Para terminar, ahora necesito que complementes la documentación técnica existente con lo que hemos hecho en este chat. A continuación, en la carpeta docs/pasos genera un archivo llamado paso46 que contenga todos los prompts de este chat y un resumen de lo conseguido en cada uno de ellos.

**Resultado:**
- Se creó este documento (`docs/pasos/paso46.md`)
- Se actualizó la documentación técnica

---

## Archivos Creados/Modificados

### Archivos Creados

| Archivo | Descripción |
|---------|-------------|
| `docs/pasos/paso-3.9.1-plan.md` | Plan de trabajo detallado |
| `docs/debugbar-n1-detection.md` | Documentación de detección N+1 |
| `tests/Concerns/CountsQueries.php` | Trait para conteo de queries |
| `tests/Feature/Performance/QueryOptimizationTest.php` | Tests de optimización de queries |
| `tests/Feature/Performance/CacheInvalidationTest.php` | Tests de invalidación de caché |
| `database/migrations/2026_01_20_160821_add_performance_indexes_phase_4.php` | Migración de índices |

### Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `app/Providers/AppServiceProvider.php` | Control granular de Model strictness |
| `app/Models/Program.php` | `getCachedActive()`, `clearCache()`, hooks |
| `app/Models/AcademicYear.php` | `getCachedAll()`, `clearCache()` ampliado |
| `app/Models/DocumentCategory.php` | `getCachedAll()`, `clearCache()`, hooks |
| `app/Models/Call.php` | Hook para invalidar caché de Home |
| `app/Models/NewsPost.php` | Hook para invalidar caché de Home |
| `app/Models/ErasmusEvent.php` | Hook para invalidar caché de Home |
| `app/Models/User.php` | Relación `activities()` para withCount |
| `app/Livewire/Admin/Dashboard.php` | Caché en alerts y activities |
| `app/Livewire/Admin/News/Index.php` | Eager loading de media, uso de caché |
| `app/Livewire/Admin/Documents/Index.php` | Eager loading de media, uso de caché |
| `app/Livewire/Admin/Events/Index.php` | Eager loading consolidado |
| `app/Livewire/Admin/Users/Index.php` | withCount para activities |
| `app/Livewire/Admin/Calls/Show.php` | Optimización de computed properties |
| `app/Livewire/Public/Home.php` | Sistema de caché completo |
| `app/Livewire/Public/Calls/Show.php` | Eager loading optimizado |
| `app/Livewire/Public/News/Show.php` | Eager loading optimizado |
| `app/Livewire/Public/Events/Show.php` | Eager loading de media |
| `app/Livewire/Search/GlobalSearch.php` | Uso de métodos de caché |
| `app/Exports/CallsExport.php` | FromQuery + WithChunkReading |
| `app/Exports/AuditLogsExport.php` | FromQuery + WithChunkReading |
| `app/Exports/ResolutionsExport.php` | FromQuery + WithChunkReading |
| `resources/views/livewire/admin/users/index.blade.php` | Uso de activities_count |

---

## Tests Implementados

### QueryOptimizationTest.php (13 tests, 35 assertions)

```
Public Pages Query Optimization:
├── it loads home page with optimal queries
├── it loads public calls index with optimal queries
└── it loads public call show with optimal queries

Admin Pages Query Optimization:
├── it loads admin dashboard with optimal queries
├── it loads admin calls index with optimal queries
├── it loads admin call show with optimal queries
├── it loads admin news index with optimal queries
├── it loads admin documents index with optimal queries
├── it loads admin users index with optimal queries
├── it loads admin events index with optimal queries
└── it loads admin audit logs index with optimal queries

Search Query Optimization:
└── it loads global search with optimal queries when searching

Query Performance Metrics:
└── it has no slow queries on main pages
```

### CacheInvalidationTest.php (16 tests, 48 assertions)

```
Program Cache Invalidation:
├── it caches active programs
├── it invalidates cache when program is saved
└── it invalidates cache when program is deleted

AcademicYear Cache Invalidation:
├── it caches all academic years
├── it invalidates cache when academic year is saved
├── it invalidates cache when academic year is deleted
└── it clears current academic year cache on update

DocumentCategory Cache Invalidation:
├── it caches all document categories
├── it invalidates cache when category is saved
└── it invalidates cache when category is deleted

Home Page Cache Invalidation:
├── it invalidates home cache when call is published
├── it invalidates home cache when news is published
├── it invalidates home cache when public event is created
└── it does not invalidate home cache when draft call is updated

Dashboard Cache Invalidation:
└── it clears all dashboard caches via clearCache method

Home Component Cache:
└── it clears all home caches via clearCache method
```

---

## Métricas de Mejora

### Reducción de Queries

| Componente | Antes | Después | Mejora |
|------------|-------|---------|--------|
| Home Page | ~20+ | <15 | -25% |
| Admin Calls Index | ~40+ | <30 | -25% |
| Admin News Index | ~60+ | <50 | -17% |
| Admin Documents Index | ~40+ | <30 | -25% |
| Admin Users Index | ~30+ | <20 | -33% |

### Sistema de Caché

| Caché | TTL | Invalidación |
|-------|-----|--------------|
| `programs.active` | 1 hora | Al guardar/eliminar programa |
| `academic_years.all` | 1 hora | Al guardar/eliminar año |
| `document_categories.all` | 1 hora | Al guardar/eliminar categoría |
| `home.open_calls` | 15 min | Al publicar/eliminar convocatoria |
| `home.recent_news` | 15 min | Al publicar/eliminar noticia |
| `home.upcoming_events` | 15 min | Al crear/eliminar evento público |
| `dashboard.statistics` | 5 min | Via `Dashboard::clearCache()` |
| `dashboard.alerts` | 5 min | Via `Dashboard::clearCache()` |
| `dashboard.recent_activities` | 2 min | Via `Dashboard::clearCache()` |

### Índices de Base de Datos Añadidos

```sql
-- calls
CREATE INDEX calls_deleted_at_index ON calls(deleted_at);
CREATE INDEX calls_type_index ON calls(type);
CREATE INDEX calls_modality_index ON calls(modality);

-- resolutions
CREATE INDEX resolutions_published_at_index ON resolutions(published_at);
CREATE INDEX resolutions_call_published_index ON resolutions(call_id, published_at);

-- programs
CREATE INDEX programs_is_active_index ON programs(is_active);
CREATE INDEX programs_active_order_index ON programs(is_active, order);
```

---

## Notas Técnicas

### Spatie Media Library

Se detectó que `getFirstMediaUrl()` con conversiones específicas puede generar queries adicionales que no se benefician del eager loading. Esto se documentó como comportamiento esperado en los tests.

### Model::shouldBeStrict()

Se ajustó para usar control granular:
- `Model::preventLazyLoading()` ✅ Activado
- `Model::preventSilentlyDiscardingAttributes()` ✅ Activado
- `Model::preventAccessingMissingAttributes()` ❌ No activado (conflicto con SoftDeletes en tests)

---

*Documento generado: 2026-01-20*
*Paso 3.9.1 - Optimización de Consultas*
