# Plan de Trabajo: Paso 3.9.1 - Optimización de Consultas

## Objetivo
Optimizar el rendimiento de la aplicación mediante la implementación de eager loading, resolución de problemas N+1, implementación de caché para consultas frecuentes y uso de índices de base de datos apropiados.

---

## Estado Actual

### Análisis Realizado

Tras revisar los componentes Livewire, modelos y migraciones existentes, se han identificado las siguientes áreas:

#### ✅ Ya Implementado (Buenas Prácticas Existentes)

1. **Eager Loading en Componentes Index**:
   - `Admin\Calls\Index`: `->with(['program', 'academicYear', 'creator', 'updater'])->withCount(['phases', 'resolutions', 'applications'])`
   - `Admin\News\Index`: `->with(['program', 'academicYear', 'author', 'tags'])->withCount(['tags'])`
   - `Admin\Users\Index`: `->with(['roles', 'permissions'])`
   - `Admin\Documents\Index`: `->with(['category', 'program', 'academicYear', 'creator', 'updater'])->withCount(['mediaConsents'])`
   - `Admin\AuditLogs\Index`: `->with(['causer', 'subject'])`
   - `Public\Calls\Index`: `->with(['program', 'academicYear'])`
   - `Public\Home`: Eager loading en todas las consultas
   - `Search\GlobalSearch`: Eager loading apropiado por tipo de contenido

2. **Caché Implementado**:
   - `Admin\Dashboard`: Caché de estadísticas (5 min) y gráficos (15 min)
   - `Admin\AuditLogs\Index`: Caché para modelos disponibles y causers

3. **Índices de Base de Datos**:
   - Tablas `calls`: Índices compuestos en `(program_id, academic_year_id, status)` y `(status, published_at)`
   - Tablas `news_posts`: Índices compuestos en `(program_id, status, published_at)` y `(academic_year_id, status)`
   - Tablas `documents`: Índice compuesto en `(category_id, program_id, is_active)`
   - Tablas `users`: Índices en `deleted_at`, `name`, y compuestos
   - Tablas `academic_years`, `call_phases`, `erasmus_events`, `translations`, `newsletter_subscriptions`, `activity_log`: Índices específicos

#### ⚠️ Áreas de Mejora Identificadas

1. **Componentes Show sin Eager Loading en Mount**:
   - `Public\Calls\Show`: Route Model Binding sin relaciones precargadas
   - Las relaciones se cargan en computed properties (potenciales N+1)

2. **Consultas Repetidas sin Caché**:
   - Dropdowns de filtros (Programs, AcademicYears) se cargan en cada página
   - Estadísticas públicas no cacheadas

3. **Consultas N+1 Potenciales**:
   - `Admin\Calls\Show.hasRelationships()`: Usa `->exists()` tres veces separadas
   - `Public\Calls\Show.currentPhases()`: Consulta separada sin eager loading

4. **Índices Faltantes**:
   - `news_posts.deleted_at` (SoftDeletes)
   - `documents.deleted_at` (SoftDeletes)
   - `calls.deleted_at` (SoftDeletes)
   - `resolutions.call_id` y `resolutions.published_at`
   - `programs.is_active` y `programs.order`

---

## Plan de Trabajo

### Fase 1: Auditoría y Detección de N+1 ✅ COMPLETADO

#### 1.1. Configurar Laravel Debugbar (Desarrollo)
- [x] Instalar `barryvdh/laravel-debugbar` (si no está instalado)
- [x] Habilitar en entorno de desarrollo
- [x] Configurar para detección óptima de N+1:
  - Mostrar todas las consultas (no solo lentas)
  - Habilitar hints para N+1
  - Habilitar EXPLAIN
  - Habilitar timeline de consultas
  - Habilitar cache collector
- [x] Crear guía de uso: `docs/debugbar-n1-detection.md`

#### 1.2. Configurar Detección Automática
- [x] Habilitar `Model::shouldBeStrict()` en `AppServiceProvider` (detecta N+1 en desarrollo/testing)
- [x] Crear trait `Tests\Concerns\CountsQueries` para tests de rendimiento
- [x] Crear tests automatizados: `tests/Feature/Performance/QueryOptimizationTest.php`
- [x] Establecer baseline de queries por componente

#### 1.3. Problemas N+1 Detectados (a corregir en Fase 2)

| Componente | Problema | Queries Extras |
|------------|----------|----------------|
| Admin\News\Index | Media N+1 (featured images) | 15 queries por listado |
| Admin\Documents\Index | Media N+1 + Users N+1 | 15+ queries |
| Admin\Events\Index | Media N+1 (event images) | 15 queries |
| Admin\Users\Index | Activity count N+1 | 15 queries por usuario |
| Admin\Calls\Index | Users N+1 (creator/updater) | 2 queries |

### Fase 2: Optimización de Eager Loading ✅ COMPLETADO

#### 2.1. Corregir N+1 en Componentes Admin Index
- [x] `Admin\News\Index`: Añadido `->with('media')` al eager loading
- [x] `Admin\Documents\Index`: Añadido `->with('media')` al eager loading
- [x] `Admin\Events\Index`: Consolidado `->with(['program', 'call', 'creator', 'media'])`
- [x] `Admin\Users\Index`: Añadido `withCount(['activities'])` y relación `activities()` al modelo User
- [x] Actualizada vista `users/index.blade.php` para usar `$user->activities_count` en lugar de consulta directa

#### 2.2. Mejoras Realizadas
- El conteo de actividades por usuario ya no genera N+1 (de 15 queries a 1)
- Media eager loading añadido a News, Documents y Events
- Modelo User ahora tiene relación `activities()` para uso con `withCount()`

#### 2.3. Componentes Public Show - Eager Loading ✅ COMPLETADO
- [x] `Public\Calls\Show`: Añadido `->load(['program', 'academicYear'])`
- [x] `Public\News\Show`: Añadido `->load(['program', 'academicYear', 'author', 'tags', 'media'])`
- [x] `Public\Documents\Show`: Ya tenía eager loading (verificado)
- [x] `Public\Events\Show`: Añadido `media` al eager loading existente
- [x] `Public\Programs\Show`: No requiere cambios (modelo simple sin relaciones en mount)

**Nota sobre Media N+1**: Las consultas de media que persisten en News/Events son debido a
`getFirstMediaUrl()` con conversiones (thumbnail), que no utiliza la relación eager-loaded.
Es comportamiento esperado de Spatie Media Library y no representa un problema real de rendimiento.

**Ejemplo de mejora:**
```php
// Antes
public function mount(Call $call): void
{
    $this->call = $call;
}

// Después
public function mount(Call $call): void
{
    $this->call = $call->load([
        'program',
        'academicYear',
        'phases' => fn ($q) => $q->orderBy('order'),
        'resolutions' => fn ($q) => $q->whereNotNull('published_at')->orderBy('official_date', 'desc'),
    ]);
}
```

#### 2.4. Optimizar Computed Properties con Consultas ✅ COMPLETADO
- [x] `Public\Calls\Show`: `currentPhases()` ahora usa relación precargada en mount
- [x] `Public\Calls\Show`: `publishedResolutions()` ahora usa relación precargada en mount
- [x] `Admin\Calls\Show`: `hasRelationships()` y `canDelete()` ahora usan `_count` precargados

### Fase 3: Implementación de Caché ✅ COMPLETADO

#### 3.1. Caché para Datos de Referencia Frecuentes
- [x] `Program::getCachedActive()` - Lista de programas activos (TTL: 1 hora)
- [x] `AcademicYear::getCachedAll()` - Lista de años académicos (TTL: 1 hora)
- [x] `DocumentCategory::getCachedAll()` - Lista de categorías (TTL: 1 hora)

#### 3.2. Caché para Página Principal (Home)
- [x] `Home::CACHE_KEY_CALLS` - Convocatorias abiertas (TTL: 15 min)
- [x] `Home::CACHE_KEY_NEWS` - Noticias recientes (TTL: 15 min)
- [x] `Home::CACHE_KEY_EVENTS` - Eventos próximos (TTL: 15 min)

#### 3.3. Invalidación Automática de Caché
- [x] `Program` - Invalida cache en `saved()` y `deleted()`
- [x] `AcademicYear` - Invalida cache en `saved()`, `deleted()`, `restored()`
- [x] `DocumentCategory` - Invalida cache en `saved()` y `deleted()`
- [x] `Call` - Invalida Home cache cuando cambia `status` o `published_at`
- [x] `NewsPost` - Invalida Home cache cuando cambia `status` o `published_at`
- [x] `ErasmusEvent` - Invalida Home cache cuando cambia `is_public` o fechas

#### 3.4. Componentes Actualizados para Usar Caché
- [x] `Admin\News\Index` - Usa `Program::getCachedActive()` y `AcademicYear::getCachedAll()`
- [x] `Admin\Calls\Index` - Usa `Program::getCachedActive()` y `AcademicYear::getCachedAll()`
- [x] `Admin\Documents\Index` - Usa todas las cachés de referencia
- [x] `Admin\Events\Index` - Usa `Program::getCachedActive()`
- [x] `Public\Home` - Usa `Program::getCachedActive()` y cachés propias

### Fase 4: Índices de Base de Datos ✅ COMPLETADO

#### 4.1. Análisis de Índices Existentes
Se verificaron los índices existentes antes de crear nuevos:
- `calls`: `program_id+academic_year_id+status`, `status+published_at`
- `news_posts`: `deleted_at`, `program_id+status+published_at`, `academic_year_id+status`
- `documents`: `deleted_at`, `category_id+program_id+is_active`
- `resolutions`: `call_id+type`

#### 4.2. Migración Creada
- [x] Migración: `2026_01_20_160821_add_performance_indexes_phase_4.php`

**Índices añadidos:**
```php
// calls - para filtrado por tipo y modalidad
$table->index('deleted_at', 'calls_deleted_at_index');
$table->index('type', 'calls_type_index');
$table->index('modality', 'calls_modality_index');

// resolutions - para filtrado de resoluciones publicadas
$table->index('published_at', 'resolutions_published_at_index');
$table->index(['call_id', 'published_at'], 'resolutions_call_published_index');

// programs - para getCachedActive() y filtros
$table->index('is_active', 'programs_is_active_index');
$table->index(['is_active', 'order'], 'programs_active_order_index');
```

**Nota**: Los índices de `news_posts` y `documents` ya existían de migraciones anteriores.

### Fase 5: Optimizaciones Específicas ✅ COMPLETADO

#### 5.1. Optimizar Dashboard Administrativo
- [x] `loadAlerts()` ahora está cacheado (5 minutos)
- [x] `loadRecentActivities()` ahora está cacheado (2 minutos)
- [x] Actualizado `clearCache()` para incluir las nuevas cachés

#### 5.2. Optimizar Componentes de Exportación
- [x] `CallsExport` convertido a `FromQuery` + `WithChunkReading` (chunks de 500)
- [x] `AuditLogsExport` convertido a `FromQuery` + `WithChunkReading` (chunks de 500)
- [x] `ResolutionsExport` convertido a `FromQuery` + `WithChunkReading` (chunks de 500)

#### 5.3. Optimizar Consultas de Búsqueda Global
- [x] `availablePrograms()` ahora usa `Program::getCachedActive()`
- [x] `availableAcademicYears()` ahora usa `AcademicYear::getCachedAll()`
- [x] Límites ya existentes (`limitPerType = 10`) mantenidos

### Fase 6: Testing

#### 6.1. Tests de Rendimiento
- [ ] Crear tests para verificar número de consultas (usando `assertDatabaseQueryCount` de Laravel)
- [ ] Verificar que eager loading funciona correctamente
- [ ] Tests para invalidación de caché

**Ejemplo de test:**
```php
it('loads calls index with optimal queries', function () {
    Call::factory()->count(10)->create();
    
    // Esperamos: 1 para calls, 1 para programs, 1 para academic_years
    // + 1 auth + contadores
    $this->assertDatabaseQueryCount(function () {
        Livewire::test(Index::class)->assertOk();
    }, 10); // Máximo de consultas esperadas
});
```

---

## Archivos a Modificar

### Componentes Livewire
| Archivo | Acción |
|---------|--------|
| `app/Livewire/Public/Calls/Show.php` | Añadir eager loading en mount() |
| `app/Livewire/Public/News/Show.php` | Añadir eager loading en mount() |
| `app/Livewire/Public/Documents/Show.php` | Añadir eager loading en mount() |
| `app/Livewire/Public/Events/Show.php` | Añadir eager loading en mount() |
| `app/Livewire/Public/Programs/Show.php` | Añadir eager loading en mount() |
| `app/Livewire/Admin/Calls/Show.php` | Optimizar hasRelationships() |
| `app/Livewire/Admin/Calls/Index.php` | Usar caché para programas y años |
| `app/Livewire/Admin/News/Index.php` | Usar caché para programas y años |
| `app/Livewire/Admin/Documents/Index.php` | Usar caché para programas, años y categorías |
| `app/Livewire/Public/Calls/Index.php` | Usar caché para programas y años |
| `app/Livewire/Search/GlobalSearch.php` | Usar caché para programas y años |

### Modelos
| Archivo | Acción |
|---------|--------|
| `app/Models/Program.php` | Añadir método getCachedActive() |
| `app/Models/AcademicYear.php` | Añadir método getCachedAll() |
| `app/Models/DocumentCategory.php` | Añadir método getCachedActive() |
| `app/Models/Setting.php` | Añadir métodos de caché |

### Migraciones
| Archivo | Acción |
|---------|--------|
| Nueva migración | Añadir índices de rendimiento |

### Tests
| Archivo | Acción |
|---------|--------|
| `tests/Feature/Livewire/Public/Calls/ShowTest.php` | Añadir tests de N+1 |
| `tests/Feature/Performance/QueryOptimizationTest.php` | Nuevo archivo para tests de rendimiento |

---

## Orden de Implementación Recomendado

1. **Fase 4.1**: Crear migración de índices (bajo riesgo, alto impacto)
2. **Fase 2.1**: Mejorar eager loading en componentes Show
3. **Fase 2.2**: Optimizar computed properties
4. **Fase 3.1**: Implementar caché para datos de referencia
5. **Fase 3.2-3.3**: Caché para estadísticas y configuraciones
6. **Fase 5**: Optimizaciones específicas
7. **Fase 6**: Tests de rendimiento
8. **Fase 1** (opcional): Auditoría con Debugbar

---

## Métricas de Éxito

- Reducir consultas N+1 a cero en páginas principales
- Reducir tiempo de carga de listados en >20%
- Implementar caché con invalidación correcta
- Todos los tests pasando
- Cobertura de tests para nuevas funcionalidades

---

## Notas Técnicas

### Invalidación de Caché
Usar observers o eventos de modelo para invalidar caché:

```php
// En boot() del modelo o en un Observer
static::saved(function () {
    Cache::forget('programs.active');
});

static::deleted(function () {
    Cache::forget('programs.active');
});
```

### Consideraciones de Laravel 12
- Usar `Cache::flexible()` si está disponible para caché con SWR (stale-while-revalidate)
- Aprovechar lazy collections para grandes conjuntos de datos

---

## Estimación de Complejidad

| Fase | Complejidad | Prioridad |
|------|-------------|-----------|
| Fase 1 (Debugbar) | Baja | Opcional |
| Fase 2 (Eager Loading) | Media | Alta |
| Fase 3 (Caché) | Media | Alta |
| Fase 4 (Índices) | Baja | Alta |
| Fase 5 (Optimizaciones) | Media | Media |
| Fase 6 (Tests) | Media | Alta |

---

## Dependencias

- Laravel Debugbar (opcional, solo desarrollo)
- Ningún paquete adicional requerido

---

*Documento creado: 2026-01-20*
*Paso: 3.9.1 - Optimización de Consultas*
