# CRUD de Suscripciones Newsletter en Panel de Administración

Documentación técnica del sistema completo de gestión de suscripciones newsletter en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El sistema de gestión de Suscripciones Newsletter permite a los administradores visualizar, filtrar, exportar y eliminar suscripciones al newsletter del sistema. Proporciona una interfaz moderna y completa para gestionar todas las suscripciones de usuarios interesados en recibir información sobre programas Erasmus+.

## Características Principales

- ✅ **Listado Completo**: Tabla interactiva con todas las suscripciones
- ✅ **Filtros Avanzados**: Búsqueda, filtro por programa, estado (activo/inactivo) y verificación
- ✅ **Ordenación**: Por email, nombre, fecha de suscripción
- ✅ **Estadísticas Rápidas**: Total, activos y verificados
- ✅ **Exportación**: Exportación a Excel (XLSX) con filtros aplicados
- ✅ **Vista Detallada**: Información completa de cada suscripción
- ✅ **Eliminación**: Hard delete con confirmación (cumplimiento GDPR)
- ✅ **Autorización**: Control de acceso por roles y permisos
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 142 tests pasando (339 assertions)

---

## Estructura de Datos

### Modelo NewsletterSubscription

**Tabla**: `newsletter_subscriptions`

**Campos principales:**
- `id` - Identificador único
- `email` - Email del suscriptor (único)
- `name` - Nombre del suscriptor (opcional)
- `programs` - JSON array de códigos de programas de interés
- `is_active` - Estado activo/inactivo (boolean)
- `subscribed_at` - Fecha de suscripción
- `unsubscribed_at` - Fecha de baja (nullable)
- `verification_token` - Token de verificación (nullable)
- `verified_at` - Fecha de verificación (nullable)
- `created_at`, `updated_at` - Timestamps

**Índices:**
- `newsletter_subscriptions_verified_at_index` - En `verified_at`
- `newsletter_subscriptions_subscribed_at_index` - En `subscribed_at`
- `newsletter_subscriptions_status_verification_index` - Compuesto en `['is_active', 'verified_at']`

**Scopes:**
- `active()` - Suscripciones activas
- `verified()` - Suscripciones verificadas
- `unverified()` - Suscripciones no verificadas
- `forProgram(string $code)` - Suscripciones para un programa específico
- `verifiedForProgram(string $code)` - Suscripciones verificadas para un programa

**Métodos Helper:**
- `isVerified()` - Verifica si está verificada
- `isActive()` - Verifica si está activa
- `verify()` - Marca como verificada
- `unsubscribe()` - Marca como dada de baja
- `hasProgram(string $code)` - Verifica si tiene un programa específico
- `getProgramsModelsAttribute()` - Obtiene modelos Program asociados
- `getProgramsDisplayAttribute()` - Obtiene string de programas para mostrar
- `getProgramsCodesAttribute()` - Obtiene string de códigos de programas

**Relaciones:**
- No tiene relaciones directas con otros modelos
- Los programas se almacenan como códigos en JSON array

---

## Permisos y Autorización

### Permisos Definidos

**Ubicación**: `App\Support\Permissions`

```php
public const NEWSLETTER_VIEW = 'newsletter.view';
public const NEWSLETTER_DELETE = 'newsletter.delete';
public const NEWSLETTER_EXPORT = 'newsletter.export';
public const NEWSLETTER_ALL = 'newsletter.*';
```

### NewsletterSubscriptionPolicy

**Ubicación**: `app/Policies/NewsletterSubscriptionPolicy.php`

**Métodos:**
- `before(User $user, string $ability)` - Super-admin tiene acceso total
- `viewAny(User $user)` - Requiere `NEWSLETTER_VIEW`
- `view(User $user, NewsletterSubscription $newsletterSubscription)` - Requiere `NEWSLETTER_VIEW`
- `delete(User $user, NewsletterSubscription $newsletterSubscription)` - Requiere `NEWSLETTER_DELETE`
- `export(User $user)` - Requiere `NEWSLETTER_EXPORT`

**Matriz de Permisos por Rol:**

| Acción | Super-Admin | Admin | Editor | Viewer |
|--------|-------------|-------|--------|--------|
| Ver listado | ✅ | ✅ | ✅ | ✅ |
| Ver detalle | ✅ | ✅ | ✅ | ✅ |
| Eliminar | ✅ | ✅ | ❌ | ❌ |
| Exportar | ✅ | ✅ | ✅ | ❌ |

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Newsletter\Index`
- **Vista**: `resources/views/livewire/admin/newsletter/index.blade.php`
- **Ruta**: `/admin/newsletter` (nombre: `admin.newsletter.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'programa')]
public ?string $filterProgram = null;

#[Url(as: 'estado')]
public ?string $filterStatus = null; // 'activo' | 'inactivo'

#[Url(as: 'verificacion')]
public ?string $filterVerification = null; // 'verificado' | 'no-verificado'

#[Url(as: 'ordenar')]
public string $sortField = 'subscribed_at';

#[Url(as: 'direccion')]
public string $sortDirection = 'desc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public ?int $subscriptionToDelete = null;
```

**Métodos Principales:**

- `mount()` - Autoriza acceso con `viewAny`
- `subscriptions()` (computed) - Obtiene suscripciones paginadas con filtros aplicados
- `programs()` (computed) - Obtiene programas activos para filtro
- `statistics()` (computed) - Calcula estadísticas (total, activos, verificados)
- `sortBy(string $field)` - Cambia ordenación
- `resetFilters()` - Resetea todos los filtros
- `confirmDelete(int $id)` - Abre modal de confirmación
- `delete()` - Elimina suscripción (hard delete)
- `export()` - Exporta suscripciones a Excel
- `canDelete()` - Verifica permiso de eliminación
- `canExport()` - Verifica permiso de exportación
- `getStatusBadge(NewsletterSubscription $subscription)` - Retorna variant de badge para estado
- `getVerificationBadge(NewsletterSubscription $subscription)` - Retorna variant de badge para verificación

**Características:**
- Búsqueda en tiempo real por email o nombre
- Filtros por programa, estado y verificación
- Ordenación por email, nombre o fecha de suscripción
- Paginación configurable (15, 25, 50, 100)
- Estadísticas en tiempo real
- Exportación con filtros aplicados
- Eliminación con confirmación modal

### 2. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Newsletter\Show`
- **Vista**: `resources/views/livewire/admin/newsletter/show.blade.php`
- **Ruta**: `/admin/newsletter/{newsletter_subscription}` (nombre: `admin.newsletter.show`)

**Propiedades Públicas:**

```php
public NewsletterSubscription $subscription;
public bool $showDeleteModal = false;
```

**Métodos Principales:**

- `mount(NewsletterSubscription $newsletter_subscription)` - Autoriza acceso con `view`
- `programModels()` (computed) - Obtiene modelos Program asociados
- `delete()` - Elimina suscripción (hard delete)
- `canDelete()` - Verifica permiso de eliminación
- `getStatusBadge()` - Retorna variant de badge para estado
- `getVerificationBadge()` - Retorna variant de badge para verificación

**Características:**
- Vista detallada de suscripción individual
- Información completa: email, nombre, programas, fechas
- Visualización de programas como badges
- Acción de eliminación con confirmación

---

## Exportación de Datos

### NewsletterSubscriptionsExport

**Ubicación**: `app/Exports/NewsletterSubscriptionsExport.php`

**Interfaces implementadas:**
- `FromCollection` - Define la colección de datos
- `WithHeadings` - Define encabezados de columnas
- `WithMapping` - Formatea cada fila
- `WithStyles` - Aplica estilos al Excel
- `WithTitle` - Define título de la hoja

**Métodos:**

- `__construct(array $filters)` - Recibe filtros del componente Index
- `collection()` - Construye query con filtros aplicados
- `headings()` - Retorna encabezados de columnas
- `map(NewsletterSubscription $subscription)` - Formatea cada suscripción
- `title()` - Retorna título de la hoja
- `styles()` - Aplica estilos (primera fila en negrita)
- `formatPrograms(array $programs)` - Formatea programas para mostrar

**Columnas exportadas:**
1. Email
2. Nombre
3. Programas (nombres o códigos)
4. Estado (Activo/Inactivo)
5. Verificado (Sí/No)
6. Fecha Suscripción
7. Fecha Verificación
8. Fecha Baja

**Formato:**
- Excel (XLSX)
- Primera fila en negrita
- Fechas formateadas (d/m/Y H:i)
- Valores nulos mostrados como "-"

---

## Rutas

**Ubicación**: `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // ... otras rutas ...
    
    Route::get('/newsletter', \App\Livewire\Admin\Newsletter\Index::class)
        ->name('newsletter.index');
    
    Route::get('/newsletter/{newsletter_subscription}', \App\Livewire\Admin\Newsletter\Show::class)
        ->name('newsletter.show');
});
```

---

## Navegación

**Ubicación**: `resources/views/components/layouts/app/sidebar.blade.php`

```blade
@can('viewAny', \App\Models\NewsletterSubscription::class)
    <flux:navlist.item 
        icon="envelope" 
        :href="route('admin.newsletter.index')" 
        :current="request()->routeIs('admin.newsletter.*')" 
        wire:navigate
    >
        {{ __('common.nav.newsletter_subscriptions') }}
    </flux:navlist.item>
@endcan
```

**Traducciones:**
- Español: `'newsletter_subscriptions' => 'Suscripciones Newsletter'`
- Inglés: `'newsletter_subscriptions' => 'Newsletter Subscriptions'`

---

## Optimizaciones de Rendimiento

### Índices de Base de Datos

**Migración**: `2026_01_13_200128_add_indexes_to_newsletter_subscriptions_table.php`

```php
Schema::table('newsletter_subscriptions', function (Blueprint $table) {
    $table->index('verified_at', 'newsletter_subscriptions_verified_at_index');
    $table->index('subscribed_at', 'newsletter_subscriptions_subscribed_at_index');
    $table->index(['is_active', 'verified_at'], 'newsletter_subscriptions_status_verification_index');
});
```

**Beneficios:**
- Búsquedas rápidas por estado y verificación
- Ordenación eficiente por fecha de suscripción
- Filtros combinados optimizados

### Consultas Optimizadas

- Uso de `whereJsonContains()` para filtros de programas
- Eager loading cuando sea necesario
- Paginación eficiente con índices

---

## Testing

### Tests Implementados

**Total**: 142 tests pasando (339 assertions)

#### 1. NewsletterSubscriptionPolicyTest
**Ubicación**: `tests/Feature/Policies/NewsletterSubscriptionPolicyTest.php`

**Tests (5):**
- Super-admin tiene acceso total
- Admin tiene acceso total
- Editor puede ver y exportar, pero no eliminar
- Viewer solo puede ver
- Usuario sin rol no tiene acceso

#### 2. IndexTest
**Ubicación**: `tests/Feature/Livewire/Admin/Newsletter/IndexTest.php`

**Tests (32):**
- Autorización (4 tests)
- Visualización de listado (3 tests)
- Búsqueda (3 tests)
- Filtros (6 tests)
- Ordenación (3 tests)
- Paginación (2 tests)
- Eliminación (2 tests)
- Exportación (4 tests)
- Helpers (5 tests)

#### 3. ShowTest
**Ubicación**: `tests/Feature/Livewire/Admin/Newsletter/ShowTest.php`

**Tests (14):**
- Autorización (4 tests)
- Visualización de detalle (4 tests)
- Eliminación (2 tests)
- Helpers (4 tests)

#### 4. NewsletterSubscriptionsExportTest
**Ubicación**: `tests/Feature/Exports/NewsletterSubscriptionsExportTest.php`

**Tests (19):**
- Exportación básica (9 tests)
- Filtros (8 tests)
- Formato de datos (2 tests)

#### 5. NewsletterSubscriptionTest (Modelo)
**Ubicación**: `tests/Feature/Models/NewsletterSubscriptionTest.php`

**Tests existentes actualizados:**
- Helpers de programas (5 tests)

---

## Interfaz de Usuario

### Componentes Flux UI Utilizados

- `flux:button` - Botones de acción
- `flux:badge` - Badges para estados y verificación
- `flux:input` - Campo de búsqueda
- `flux:select` - Selectores de filtros
- `flux:modal` - Modal de confirmación de eliminación
- `flux:navlist.item` - Item de navegación
- `x-ui.stat-card` - Tarjetas de estadísticas
- `x-ui.empty-state` - Estado vacío
- `x-ui.badge` - Badges personalizados

### Estados Visuales

**Estado (is_active):**
- Activo: Badge verde (`success`)
- Inactivo: Badge rojo (`danger`)

**Verificación (verified_at):**
- Verificado: Badge verde (`success`)
- No verificado: Badge amarillo (`warning`)

### Diseño Responsive

- Tabla con scroll horizontal en móviles
- Filtros apilados verticalmente en pantallas pequeñas
- Estadísticas en grid adaptativo
- Modales centrados y responsive

---

## Flujo de Trabajo

### Visualizar Suscripciones

1. Usuario accede a `/admin/newsletter`
2. Sistema verifica permiso `NEWSLETTER_VIEW`
3. Se muestra listado con todas las suscripciones
4. Usuario puede filtrar, buscar y ordenar

### Exportar Suscripciones

1. Usuario aplica filtros deseados (opcional)
2. Usuario hace clic en "Exportar"
3. Sistema verifica permiso `NEWSLETTER_EXPORT`
4. Se genera archivo Excel con filtros aplicados
5. Se descarga automáticamente

### Eliminar Suscripción

1. Usuario hace clic en botón eliminar
2. Se abre modal de confirmación
3. Usuario confirma eliminación
4. Sistema verifica permiso `NEWSLETTER_DELETE`
5. Se realiza hard delete (eliminación permanente)
6. Se muestra notificación de éxito

---

## Notas Técnicas Importantes

### Hard Delete (Eliminación Permanente)

Las suscripciones se eliminan permanentemente (hard delete) para cumplir con GDPR. No se utiliza SoftDeletes.

### Programas como JSON

Los programas se almacenan como códigos en un array JSON. Para mostrar nombres:
1. Se obtienen los códigos del array
2. Se buscan los modelos Program correspondientes
3. Se muestran nombres si existen, códigos si no

### Exportación con Filtros

La exportación respeta todos los filtros activos en el componente Index:
- Búsqueda
- Filtro por programa
- Filtro por estado
- Filtro por verificación
- Ordenación

---

## Archivos Relacionados

### Modelos
- `app/Models/NewsletterSubscription.php`

### Policies
- `app/Policies/NewsletterSubscriptionPolicy.php`

### Componentes Livewire
- `app/Livewire/Admin/Newsletter/Index.php`
- `app/Livewire/Admin/Newsletter/Show.php`

### Vistas
- `resources/views/livewire/admin/newsletter/index.blade.php`
- `resources/views/livewire/admin/newsletter/show.blade.php`

### Exports
- `app/Exports/NewsletterSubscriptionsExport.php`

### Tests
- `tests/Feature/Policies/NewsletterSubscriptionPolicyTest.php`
- `tests/Feature/Livewire/Admin/Newsletter/IndexTest.php`
- `tests/Feature/Livewire/Admin/Newsletter/ShowTest.php`
- `tests/Feature/Exports/NewsletterSubscriptionsExportTest.php`
- `tests/Feature/Models/NewsletterSubscriptionTest.php`

### Migraciones
- `database/migrations/2026_01_13_200128_add_indexes_to_newsletter_subscriptions_table.php`

### Configuración
- `app/Support/Permissions.php` (permisos)
- `routes/web.php` (rutas)
- `resources/views/components/layouts/app/sidebar.blade.php` (navegación)
- `lang/es/common.php` y `lang/en/common.php` (traducciones)

---

## Estado Final

✅ **Completado** - Sistema completamente funcional y probado

- **Tests**: 142 tests pasando (339 assertions)
- **Cobertura**: Completa para todos los componentes
- **Documentación**: Completa y actualizada
- **Código**: Formateado y sin errores de linting

---

**Última actualización**: Enero 2026  
**Versión**: 1.0.0
