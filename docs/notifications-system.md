# Sistema de Notificaciones

Documentación técnica del sistema completo de notificaciones internas de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El sistema de notificaciones permite a los usuarios recibir alertas automáticas cuando se publican nuevos contenidos (convocatorias, resoluciones, noticias, documentos). Utiliza **polling** (actualización periódica cada 30 segundos) como método principal de actualización, con estructura preparada para migrar a tiempo real (Laravel Echo) en el futuro si es necesario.

## Características Principales

- ✅ **Notificaciones Automáticas**: Se crean automáticamente al publicar contenido
- ✅ **Polling**: Actualización automática cada 30 segundos usando `wire:poll`
- ✅ **Componentes Livewire**: Bell, Dropdown e Index para visualización
- ✅ **Gestión Completa**: Marcar como leída, marcar todas, eliminar
- ✅ **Filtros Avanzados**: Por estado (leída/no leída) y tipo de notificación
- ✅ **Selección Múltiple**: Acciones en lote (marcar como leída, eliminar)
- ✅ **Integración Completa**: Observers para creación automática
- ✅ **Tests Completos**: 111 tests pasando (236 assertions)
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4

---

## Arquitectura

### Modelo de Datos

**Modelo:** `App\Models\Notification`

```php
- user_id (FK a users)
- type (enum: convocatoria, resolucion, noticia, revision, sistema)
- title (string)
- message (text)
- link (nullable string)
- is_read (boolean, default: false)
- read_at (nullable timestamp)
- timestamps (created_at, updated_at)
```

**Relaciones:**
- `belongsTo(User::class)`

**Scopes:**
- `unread()` - Notificaciones no leídas
- `read()` - Notificaciones leídas
- `byType(string $type)` - Filtrar por tipo
- `recent(int $days = 7)` - Notificaciones recientes (últimos N días)

**Métodos Helper:**
- `markAsRead()` - Marcar como leída
- `getTypeLabel()` - Etiqueta del tipo
- `getTypeIcon()` - Icono del tipo
- `getTypeColor()` - Color del tipo

---

## Servicio de Notificaciones

**Clase:** `App\Services\NotificationService`

### Métodos Principales

#### Creación de Notificaciones

```php
// Crear notificación básica
public function create(array $data): Notification

// Crear y preparar para broadcasting (futuro)
public function createAndBroadcast(array $data): Notification
```

#### Notificaciones por Tipo de Contenido

```php
// Notificar publicación de convocatoria
public function notifyConvocatoriaPublished(Call $call, User|Collection $users): void

// Notificar publicación de resolución
public function notifyResolucionPublished(Resolution $resolution, User|Collection $users): void

// Notificar publicación de noticia
public function notifyNoticiaPublished(NewsPost $newsPost, User|Collection $users): void

// Notificar publicación de documento
public function notifyDocumentoPublished(Document $document, User|Collection $users): void
```

#### Gestión de Estado

```php
// Marcar una notificación como leída
public function markAsRead(Notification $notification): void

// Marcar todas las notificaciones de un usuario como leídas
public function markAllAsRead(User $user): void

// Obtener contador de no leídas
public function getUnreadCount(User $user): int
```

---

## Componentes Livewire

### 1. Bell (Icono con Contador)

**Ubicación:**
- **Clase**: `App\Livewire\Notifications\Bell`
- **Vista**: `resources/views/livewire/notifications/bell.blade.php`
- **Uso**: Integrado en header y sidebar

**Propiedades:**
```php
public int $unreadCount = 0;
```

**Métodos:**
```php
public function mount(): void
public function loadUnreadCount(): void
```

**Características:**
- Muestra contador de notificaciones no leídas
- Actualización automática cada 30 segundos (`wire:poll.30s`)
- Enlace a página de notificaciones
- Badge con número (máximo 99+)

**Integración:**
- Header de administración
- Sidebar de administración (desktop y mobile)

---

### 2. Dropdown (Lista Desplegable)

**Ubicación:**
- **Clase**: `App\Livewire\Notifications\Dropdown`
- **Vista**: `resources/views/livewire/notifications/dropdown.blade.php`
- **Uso**: Integrado en header y sidebar (opcional)

**Propiedades:**
```php
public Collection $notifications;
public int $unreadCount = 0;
public bool $isOpen = false;
```

**Métodos:**
```php
public function mount(): void
public function loadNotifications(): void
public function markAsRead(int $notificationId): void
public function markAllAsRead(): void
public function toggle(): void
```

**Características:**
- Muestra últimas 10 notificaciones no leídas (últimos 7 días)
- Actualización automática cada 30 segundos cuando está abierto
- Acciones rápidas: marcar como leída, marcar todas
- Dispara eventos para actualizar otros componentes

---

### 3. Index (Página Completa)

**Ubicación:**
- **Clase**: `App\Livewire\Notifications\Index`
- **Vista**: `resources/views/livewire/notifications/index.blade.php`
- **Ruta**: `/notificaciones` (nombre: `notifications.index`)

**Propiedades:**
```php
#[Url(as: 'filtro')]
public string $filter = 'all'; // 'all', 'unread', 'read'

#[Url(as: 'tipo')]
public ?string $filterType = null;

public bool $showDeleteModal = false;
public ?int $notificationToDelete = null;
public array $selectedNotifications = [];
public bool $selectAll = false;
```

**Métodos Computados:**
```php
#[Computed]
public function notifications(): LengthAwarePaginator

#[Computed]
public function availableTypes(): array

#[Computed]
public function unreadCount(): int

#[Computed]
public function selectedCount(): int
```

**Métodos Principales:**
```php
public function markAsRead(int $notificationId): void
public function markAllAsRead(): void
public function delete(): void
public function confirmDelete(int $notificationId): void
public function toggleSelectAll(): void
public function markSelectedAsRead(): void
public function deleteSelected(): void
public function clearSelection(): void
public function resetFilters(): void
public function updatedFilter(): void
public function updatedFilterType(): void
```

**Características:**
- Paginación (20 por página)
- Filtros por estado y tipo
- Selección múltiple con acciones en lote
- Confirmación de eliminación
- Estado vacío cuando no hay notificaciones
- Diseño responsive con Flux UI

---

## Observers (Integración Automática)

### CallObserver

**Ubicación:** `App\Observers\CallObserver`

**Eventos:**
- `created()` - Crea notificaciones si se crea como publicada
- `updated()` - Crea notificaciones cuando se publica por primera vez

**Lógica:**
- Solo notifica si `published_at` cambia de `null` a una fecha (pasada o hoy)
- Carga relación `program` antes de crear notificaciones
- Notifica a todos los usuarios activos

---

### ResolutionObserver

**Ubicación:** `App\Observers\ResolutionObserver`

**Eventos:**
- `created()` - Crea notificaciones si se crea como publicada
- `updated()` - Crea notificaciones cuando se publica por primera vez

**Lógica:**
- Solo notifica si `published_at` cambia de `null` a una fecha (pasada o hoy)
- Carga relación `call` antes de crear notificaciones
- Notifica a todos los usuarios activos

---

### NewsPostObserver

**Ubicación:** `App\Observers\NewsPostObserver`

**Eventos:**
- `created()` - Crea notificaciones si se crea como publicada
- `updated()` - Crea notificaciones cuando se publica por primera vez

**Lógica:**
- Solo notifica si `published_at` cambia de `null` a una fecha (pasada o hoy)
- Notifica a todos los usuarios activos

---

### DocumentObserver

**Ubicación:** `App\Observers\DocumentObserver`

**Eventos:**
- `created()` - Crea notificaciones si se crea como activo
- `updated()` - Crea notificaciones cuando se activa por primera vez

**Lógica:**
- Solo notifica si `is_active` cambia de `false` a `true`
- Notifica a todos los usuarios activos

---

## Rutas

### Página de Notificaciones

```php
Route::get('/notificaciones', NotificationsIndex::class)
    ->middleware(['auth'])
    ->name('notifications.index');
```

**Acceso:**
- Requiere autenticación
- Disponible para todos los usuarios autenticados

---

## Traducciones

**Archivos:**
- `lang/es/notifications.php`
- `lang/en/notifications.php`

**Estructura:**
```php
[
    'title' => 'Notificaciones',
    'bell' => [...],
    'dropdown' => [...],
    'filters' => [...],
    'types' => [
        'convocatoria' => [...],
        'resolucion' => [...],
        'noticia' => [...],
        'revision' => [...],
        'sistema' => [...],
    ],
    'actions' => [...],
    'empty' => [...],
    'batch' => [...],
    'delete' => [...],
    'messages' => [...],
]
```

---

## Polling (Actualización Automática)

### Implementación

El sistema utiliza `wire:poll` de Livewire para actualizar notificaciones automáticamente:

```blade
<div wire:poll.30s="loadUnreadCount">
    <!-- Contenido del componente -->
</div>
```

**Características:**
- Actualización cada 30 segundos
- Solo cuando el componente está en el DOM
- Eficiente: Livewire solo actualiza si hay cambios
- No requiere configuración adicional

### Preparación para Tiempo Real

El código está estructurado para facilitar migración futura a Laravel Echo:

1. **Método `createAndBroadcast()`** en `NotificationService`
   - Preparado para disparar eventos cuando se implemente

2. **Eventos Livewire**
   - `notification-read` - Cuando se marca una como leída
   - `notifications-read` - Cuando se marcan todas
   - `notification-deleted` - Cuando se elimina una
   - `notifications-deleted` - Cuando se eliminan varias

3. **Documentación**
   - `docs/notificaciones-tiempo-real.md` - Guía completa para migración

---

## Tests

### Estructura de Tests

#### 1. Tests del Servicio
**Archivo:** `tests/Feature/Services/NotificationServiceTest.php`
- 25 tests, 63 assertions
- Cobertura completa de todos los métodos del servicio

#### 2. Tests de Componentes Livewire
**Archivos:**
- `tests/Feature/Livewire/Notifications/BellTest.php` (7 tests)
- `tests/Feature/Livewire/Notifications/DropdownTest.php` (20 tests)
- `tests/Feature/Livewire/Notifications/IndexTest.php` (38 tests)

**Total:** 65 tests, 115 assertions

#### 3. Tests de Integración
**Archivo:** `tests/Feature/Notifications/IntegrationTest.php`
- 21 tests, 58 assertions
- Verifica integración con Observers
- Valida creación automática de notificaciones

### Resumen de Tests

- **Total de tests:** 111
- **Total de assertions:** 236
- **Estado:** ✅ Todos pasando
- **Cobertura:** Funcionalidad completa

---

## Uso

### Para Usuarios

1. **Ver Notificaciones:**
   - Click en icono de campana en header/sidebar
   - Ver contador de no leídas
   - Acceder a página completa: `/notificaciones`

2. **Gestionar Notificaciones:**
   - Marcar como leída (individual o todas)
   - Eliminar notificaciones
   - Filtrar por estado y tipo
   - Selección múltiple para acciones en lote

### Para Desarrolladores

#### Crear Notificación Manualmente

```php
use App\Services\NotificationService;

$service = app(NotificationService::class);

$service->create([
    'user_id' => $user->id,
    'type' => 'sistema',
    'title' => 'Título de la notificación',
    'message' => 'Mensaje de la notificación',
    'link' => route('some.route'),
]);
```

#### Notificar Publicación de Contenido

```php
// Las notificaciones se crean automáticamente mediante Observers
// No es necesario llamar manualmente al servicio
```

---

## Mejoras Futuras

### Tiempo Real (Opcional)

- Migración a Laravel Echo + WebSockets
- Actualización instantánea sin polling
- Ver `docs/notificaciones-tiempo-real.md` para detalles

### Otras Mejoras

- Notificaciones por preferencias de usuario
- Notificaciones por email (opcional)
- Notificaciones push (PWA)
- Agrupación de notificaciones similares
- Historial de notificaciones eliminadas

---

## Archivos Relacionados

### Modelos
- `app/Models/Notification.php`
- `app/Models/User.php` (relación `hasMany`)

### Servicios
- `app/Services/NotificationService.php`

### Observers
- `app/Observers/CallObserver.php`
- `app/Observers/ResolutionObserver.php`
- `app/Observers/NewsPostObserver.php`
- `app/Observers/DocumentObserver.php`

### Componentes Livewire
- `app/Livewire/Notifications/Bell.php`
- `app/Livewire/Notifications/Dropdown.php`
- `app/Livewire/Notifications/Index.php`

### Vistas
- `resources/views/livewire/notifications/bell.blade.php`
- `resources/views/livewire/notifications/dropdown.blade.php`
- `resources/views/livewire/notifications/index.blade.php`

### Tests
- `tests/Feature/Services/NotificationServiceTest.php`
- `tests/Feature/Livewire/Notifications/BellTest.php`
- `tests/Feature/Livewire/Notifications/DropdownTest.php`
- `tests/Feature/Livewire/Notifications/IndexTest.php`
- `tests/Feature/Notifications/IntegrationTest.php`

### Traducciones
- `lang/es/notifications.php`
- `lang/en/notifications.php`

### Documentación
- `docs/notificaciones-tiempo-real.md`
- `docs/pasos/paso-3.7.2-plan.md`

---

**Última actualización:** Enero 2026  
**Versión:** 1.0  
**Estado:** ✅ Completado y en producción
