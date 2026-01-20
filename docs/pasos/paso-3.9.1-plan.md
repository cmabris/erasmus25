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

### Fase 1: Auditoría y Detección de N+1 (Opcional con Laravel Telescope/Debugbar)

#### 1.1. Configurar Laravel Debugbar (Desarrollo)
- [ ] Instalar `barryvdh/laravel-debugbar` (si no está instalado)
- [ ] Habilitar en entorno de desarrollo
- [ ] Identificar consultas lentas/repetidas

### Fase 2: Optimización de Eager Loading

#### 2.1. Mejorar Route Model Binding con Eager Loading
- [ ] `Public\Calls\Show`: Precargar relaciones en `mount()`
- [ ] `Public\News\Show`: Precargar relaciones en `mount()`
- [ ] `Public\Documents\Show`: Precargar relaciones en `mount()`
- [ ] `Public\Events\Show`: Precargar relaciones en `mount()`
- [ ] `Public\Programs\Show`: Precargar relaciones en `mount()`

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

#### 2.2. Optimizar Computed Properties con Consultas
- [ ] Convertir `currentPhases()` en `Public\Calls\Show` a usar relación precargada
- [ ] Convertir `publishedResolutions()` en `Public\Calls\Show` a usar relación precargada
- [ ] Optimizar `hasRelationships()` en `Admin\Calls\Show` usando counts precargados

### Fase 3: Implementación de Caché

#### 3.1. Caché para Datos de Referencia Frecuentes
- [ ] Cachear lista de programas activos (usado en filtros en múltiples componentes)
- [ ] Cachear lista de años académicos (usado en filtros)
- [ ] Cachear categorías de documentos activas

**Archivos a crear:**
- `app/Services/CacheService.php` o usar métodos estáticos en modelos

**Ejemplo de implementación:**
```php
// app/Models/Program.php
public static function getCachedActive(): Collection
{
    return Cache::remember('programs.active', 3600, function () {
        return static::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    });
}
```

#### 3.2. Caché para Estadísticas Públicas
- [ ] Cachear estadísticas de la página principal
- [ ] Cachear contadores de convocatorias abiertas/cerradas
- [ ] Implementar invalidación de caché al actualizar contenido

#### 3.3. Cachear Configuraciones del Sistema
- [ ] Cachear settings del sistema
- [ ] Implementar invalidación al editar configuraciones

### Fase 4: Índices de Base de Datos

#### 4.1. Crear Migración para Índices Faltantes
- [ ] Crear migración: `php artisan make:migration add_performance_indexes`

**Índices a añadir:**
```php
// calls
$table->index('deleted_at', 'calls_deleted_at_index');
$table->index(['deleted_at', 'status'], 'calls_deleted_at_status_index');
$table->index('type', 'calls_type_index');
$table->index('modality', 'calls_modality_index');

// news_posts
$table->index('deleted_at', 'news_posts_deleted_at_index');
$table->index(['deleted_at', 'status', 'published_at'], 'news_posts_deleted_status_published_index');

// documents
$table->index('deleted_at', 'documents_deleted_at_index');
$table->index('document_type', 'documents_type_index');

// resolutions
$table->index('published_at', 'resolutions_published_at_index');
$table->index(['call_id', 'published_at'], 'resolutions_call_published_index');

// programs
$table->index('is_active', 'programs_is_active_index');
$table->index(['is_active', 'order'], 'programs_active_order_index');
```

### Fase 5: Optimizaciones Específicas

#### 5.1. Optimizar Dashboard Administrativo
- [ ] Revisar y mejorar consultas de `loadAlerts()`
- [ ] Usar una sola consulta para múltiples contadores cuando sea posible
- [ ] Cachear actividades recientes por usuario

#### 5.2. Optimizar Componentes de Exportación
- [ ] Verificar que los exports usen `chunk()` o `cursor()` para grandes volúmenes
- [ ] Implementar lazy loading en exportaciones

#### 5.3. Optimizar Consultas de Búsqueda Global
- [ ] Implementar full-text search si es necesario (MySQL FULLTEXT)
- [ ] Considerar límites más estrictos en búsquedas

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
