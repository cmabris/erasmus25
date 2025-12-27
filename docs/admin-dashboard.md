# Dashboard de Administración

Documentación técnica del Dashboard de Administración del panel de control (Back-office) de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El Dashboard de Administración es el componente principal del panel de administración que proporciona una visión general del estado de la aplicación, estadísticas clave en tiempo real, accesos rápidos a las secciones principales, actividad reciente del sistema y alertas importantes.

## Componente Livewire

### Ubicación

- **Clase**: `App\Livewire\Admin\Dashboard`
- **Vista**: `resources/views/livewire/admin/dashboard.blade.php`
- **Ruta**: `/admin` (nombre: `admin.dashboard`)

### Propiedades Públicas

```php
public int $activePrograms = 0;          // Total de programas activos
public int $openCalls = 0;               // Total de convocatorias abiertas
public int $closedCalls = 0;              // Total de convocatorias cerradas
public int $newsThisMonth = 0;           // Noticias publicadas este mes
public int $availableDocuments = 0;       // Total de documentos disponibles
public int $upcomingEvents = 0;          // Eventos próximos

public Collection $recentActivities;       // Actividad reciente del sistema
public Collection $alerts;                // Alertas que requieren atención
```

### Métodos Principales

#### `mount()`

Inicializa el componente y carga todas las estadísticas, actividad reciente y alertas.

```php
public function mount(): void
{
    $this->loadStatistics();
    $this->loadRecentActivities();
    $this->loadAlerts();
}
```

#### `loadStatistics()`

Carga todas las estadísticas principales con caché (TTL: 5 minutos).

**Estadísticas calculadas:**
- Programas activos (`is_active = true`)
- Convocatorias abiertas (`status = 'abierta'` y `published_at IS NOT NULL`)
- Convocatorias cerradas (`status = 'cerrada'`)
- Noticias del mes actual (`status = 'publicado'` y `published_at` en mes actual)
- Documentos disponibles (`is_active = true`)
- Eventos próximos (`start_date >= hoy` y `is_public = true`)

#### `loadRecentActivities()`

Carga las últimas actividades del sistema combinando:
- Registros de `AuditLog` (últimas 10 acciones)
- Convocatorias actualizadas recientemente
- Noticias publicadas recientemente
- Documentos creados recientemente

**Límite**: 10 actividades más recientes

#### `loadAlerts()`

Carga alertas que requieren atención:
- **Convocatorias próximas a cerrar**: Convocatorias abiertas que se cierran en menos de 7 días
- **Borradores sin publicar**: Convocatorias en estado borrador creadas hace más de 7 días
- **Eventos sin ubicación**: Eventos públicos próximos sin ubicación definida

#### Métodos de Datos para Gráficos

##### `getMonthlyActivityData()`

Retorna datos para el gráfico de actividad mensual (últimos 6 meses).

**Estructura de retorno:**
```php
[
    'labels' => ['Ene 2024', 'Feb 2024', ...],  // Meses traducidos
    'datasets' => [
        [
            'label' => 'Convocatorias',
            'data' => [5, 8, 3, ...],
            'backgroundColor' => 'rgba(0, 51, 153, 0.8)',
        ],
        [
            'label' => 'Noticias',
            'data' => [12, 15, 8, ...],
            'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
        ],
        [
            'label' => 'Documentos',
            'data' => [3, 5, 2, ...],
            'backgroundColor' => 'rgba(168, 85, 247, 0.8)',
        ],
    ],
]
```

**Caché**: 15 minutos

##### `getCallsByStatusData()`

Retorna datos para el gráfico de distribución de convocatorias por estado.

**Estructura de retorno:**
```php
[
    'labels' => ['Abiertas', 'Cerradas', 'Borrador'],
    'data' => [15, 42, 8],
    'colors' => [
        'rgba(34, 197, 94, 0.8)',   // Verde para abiertas
        'rgba(239, 68, 68, 0.8)',   // Rojo para cerradas
        'rgba(156, 163, 175, 0.8)', // Gris para borrador
    ],
]
```

**Caché**: 15 minutos

##### `getCallsByProgramData()`

Retorna datos para el gráfico de distribución de convocatorias por programa (top 5).

**Estructura de retorno:**
```php
[
    'labels' => ['KA121-VET', 'KA131-HED', ...],
    'data' => [25, 18, ...],
    'colors' => [
        'rgba(0, 51, 153, 0.8)',   // Erasmus blue
        'rgba(34, 197, 94, 0.8)',   // Green
        'rgba(168, 85, 247, 0.8)',  // Purple
        'rgba(251, 191, 36, 0.8)',  // Amber
        'rgba(239, 68, 68, 0.8)',   // Red
    ],
]
```

**Caché**: 15 minutos

#### Métodos de Permisos

El componente incluye métodos helper para verificar permisos del usuario actual:

```php
protected function canManageUsers(): bool
protected function canCreateCalls(): bool
protected function canCreateNews(): bool
protected function canCreateDocuments(): bool
protected function canCreateEvents(): bool
protected function canManagePrograms(): bool
```

#### Método Estático de Utilidad

##### `clearCache()`

Limpia todas las cachés relacionadas con el dashboard. Útil después de operaciones que modifican datos.

```php
public static function clearCache(): void
{
    Cache::forget('dashboard.statistics');
    Cache::forget('dashboard.charts.monthly_activity');
    Cache::forget('dashboard.charts.calls_by_status');
    Cache::forget('dashboard.charts.calls_by_program');
}
```

## Vista Blade

### Estructura de la Vista

La vista está organizada en las siguientes secciones:

1. **Encabezado**
   - Título del dashboard
   - Mensaje de bienvenida personalizado

2. **Estadísticas** (Grid responsive)
   - 6 tarjetas de estadísticas usando `x-ui.stat-card`
   - Layout: 1 columna móvil, 2 columnas tablet, 3 columnas desktop

3. **Accesos Rápidos** (Grid responsive)
   - Tarjetas clickeables con enlaces a secciones principales
   - Visibilidad condicional según permisos del usuario
   - Layout: 1 columna móvil, 2 columnas tablet, 3 columnas desktop

4. **Actividad Reciente**
   - Lista de últimas 10 actividades del sistema
   - Iconos y colores según tipo de actividad
   - Enlaces a elementos relacionados

5. **Alertas y Notificaciones**
   - Alertas de convocatorias próximas a cerrar
   - Alertas de borradores sin publicar
   - Alertas de eventos sin ubicación

6. **Gráficos de Actividad**
   - Gráfico de actividad mensual (Chart.js)
   - Gráfico de convocatorias por estado (Chart.js)
   - Gráfico de convocatorias por programa (Chart.js)

### Integración con Chart.js

El dashboard utiliza Chart.js para visualizar datos. La inicialización se realiza mediante JavaScript en un bloque `@script`:

```javascript
window.initDashboardCharts = function() {
    // Destruir gráficos existentes si existen
    // Inicializar nuevos gráficos con datos de Livewire
    // Manejar eventos de navegación de Livewire
}
```

**Características:**
- Destrucción automática de instancias previas al re-inicializar
- Manejo de eventos `livewire:init` y `livewire:navigated`
- Prevención de inicializaciones simultáneas

### Animaciones y Transiciones

El dashboard incluye animaciones CSS personalizadas:

- **`animate-fade-in`**: Aparición gradual de elementos
- **`animate-slide-up`**: Deslizamiento desde abajo

**Accesibilidad:**
- Respeta `prefers-reduced-motion` para usuarios con preferencias de movimiento reducido
- Animaciones con delays escalonados para efecto visual agradable

### Internacionalización

Todos los textos del dashboard están internacionalizados usando el sistema de traducciones de Laravel:

- Claves de traducción en `lang/es/common.php` y `lang/en/common.php`
- Formateo de fechas usando `format_date()` y `format_datetime()`
- Formateo de números usando `format_number()`
- Meses en gráficos traducidos usando `Carbon::translatedFormat()`

## Optimización y Caché

### Estrategia de Caché

El dashboard implementa caché en dos niveles:

1. **Estadísticas** (TTL: 5 minutos)
   - Clave: `dashboard.statistics`
   - Contiene todas las estadísticas principales

2. **Datos de Gráficos** (TTL: 15 minutos)
   - Claves:
     - `dashboard.charts.monthly_activity`
     - `dashboard.charts.calls_by_status`
     - `dashboard.charts.calls_by_program`

### Limpieza de Caché

La caché se puede limpiar manualmente usando:

```php
App\Livewire\Admin\Dashboard::clearCache();
```

**Recomendación**: Llamar este método después de operaciones que modifiquen datos relevantes (crear/editar/eliminar convocatorias, noticias, documentos, etc.).

## Autorización

### Middleware

La ruta del dashboard está protegida por:

- `auth`: Requiere autenticación
- `verified`: Requiere verificación de email

### Permisos Requeridos

El dashboard es accesible para usuarios con cualquiera de estos permisos:

- `Permissions::PROGRAMS_VIEW`
- `Permissions::USERS_VIEW`

Los accesos rápidos se muestran/ocultan según los permisos específicos del usuario.

## Accesos Rápidos

Los accesos rápidos disponibles son:

| Acceso | Ruta | Permiso Requerido |
|--------|------|-------------------|
| Crear Convocatoria | `admin.calls.create` | `Permissions::CALLS_CREATE` |
| Crear Noticia | `admin.news.create` | `Permissions::NEWS_CREATE` |
| Crear Documento | `admin.documents.create` | `Permissions::DOCUMENTS_CREATE` |
| Crear Evento | `admin.events.create` | `Permissions::EVENTS_CREATE` |
| Gestionar Programas | `admin.programs.index` | `Permissions::PROGRAMS_ALL` |
| Gestionar Usuarios | `admin.users.index` | `Permissions::USERS_VIEW` (solo super-admin) |

## Testing

### Tests Implementados

El dashboard cuenta con 29 tests completos que cubren:

1. **Control de Acceso** (3 tests)
   - Redirección de usuarios no autenticados
   - Acceso para usuarios con permisos de admin
   - Acceso para super-admin

2. **Estadísticas** (6 tests)
   - Conteo correcto de cada estadística

3. **Permisos** (3 tests)
   - Mostrar/ocultar acciones según permisos
   - Gestión de usuarios solo para super-admin

4. **Actividad Reciente** (2 tests)
   - Mostrar actividades recientes
   - Estado vacío cuando no hay actividades

5. **Alertas** (4 tests)
   - Alertas para convocatorias próximas a cerrar
   - Alertas para borradores sin publicar
   - Alertas para eventos sin ubicación
   - Sin alertas cuando no hay ninguna

6. **Datos de Gráficos** (3 tests)
   - Datos de actividad mensual
   - Datos de convocatorias por estado
   - Datos de convocatorias por programa

7. **Caché** (3 tests)
   - Caché de estadísticas funciona
   - Limpieza de caché funciona
   - Caché de datos de gráficos funciona

8. **Renderizado** (5 tests)
   - Renderizado de todas las secciones principales

**Ubicación**: `tests/Feature/Livewire/Admin/DashboardTest.php`

## Extensibilidad

El dashboard está diseñado para ser fácilmente extensible:

### Añadir Nueva Estadística

1. Añadir propiedad pública al componente:
```php
public int $nuevaEstadistica = 0;
```

2. Implementar método de cálculo:
```php
protected function getNuevaEstadisticaCount(): int
{
    return Cache::remember(
        'dashboard.statistics.nueva',
        self::CACHE_TTL_STATISTICS,
        fn() => Modelo::where(...)->count()
    );
}
```

3. Incluir en `loadStatistics()`:
```php
$this->nuevaEstadistica = $this->getNuevaEstadisticaCount();
```

4. Añadir tarjeta en la vista usando `x-ui.stat-card`

### Añadir Nuevo Gráfico

1. Implementar método de datos:
```php
public function getNuevoGraficoData(): array
{
    return Cache::remember(
        'dashboard.charts.nuevo_grafico',
        self::CACHE_TTL_CHARTS,
        fn() => [...]
    );
}
```

2. Añadir canvas en la vista:
```blade
<canvas id="nuevoGraficoChart" wire:ignore></canvas>
```

3. Inicializar gráfico en el script JavaScript

### Añadir Nueva Alerta

1. Añadir lógica en `loadAlerts()`:
```php
$nuevasAlertas = Modelo::where(...)->get();
foreach ($nuevasAlertas as $alerta) {
    $this->alerts->push([
        'type' => 'nueva_alerta',
        'title' => __('common.admin.dashboard.alerts.nueva_alerta'),
        'description' => $alerta->descripcion,
        'url' => route('admin.ruta', $alerta),
    ]);
}
```

2. Añadir traducciones en `lang/es/common.php` y `lang/en/common.php`

## Dependencias

- **Laravel Livewire 3**: Componente base
- **Flux UI v2**: Componentes de UI (cards, badges, buttons, etc.)
- **Chart.js**: Visualización de gráficos
- **Alpine.js**: Integración JavaScript (incluido con Livewire)
- **Tailwind CSS v4**: Estilos y animaciones

## Referencias

- [Plan de Desarrollo](pasos/paso-3.5.1-plan.md)
- [Resumen Ejecutivo](pasos/paso-3.5.1-resumen.md)
- [Sistema de Roles y Permisos](roles-and-permissions.md)
- [Sistema de Internacionalización](i18n-system.md)

