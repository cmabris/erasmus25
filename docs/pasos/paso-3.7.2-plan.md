# Plan Detallado: Paso 3.7.2 - Notificaciones del Sistema

## ⚙️ Método de Actualización: Polling

**Este plan implementa notificaciones usando polling (actualización periódica) como método principal.**

- ✅ **Actualización automática cada 30 segundos** usando `wire:poll` de Livewire
- ✅ **No requiere configuración adicional** (servidores WebSocket, etc.)
- ✅ **Funciona inmediatamente** sin dependencias externas
- ✅ **Suficiente para este caso de uso** (retraso de 30s es aceptable para notificaciones de contenido)

**Tiempo real (Laravel Echo) está preparado como opción futura** pero no se implementa ahora. Ver:
- `docs/notificaciones-tiempo-real.md` - Explicación detallada
- Fase 8 de este plan - Preparación para migración futura

---

## Objetivo

Implementar un sistema completo de notificaciones internas del sistema que permita:
- Crear notificaciones automáticas cuando se publican nuevos contenidos (convocatorias, resoluciones, noticias, documentos)
- Mostrar notificaciones usando **polling** (actualización periódica cada 30 segundos) - método principal
- Preparar la estructura para migrar a tiempo real con Laravel Echo en el futuro (opcional)
- Componente Livewire para visualizar y gestionar notificaciones
- Marcar notificaciones como leídas
- Contador de notificaciones no leídas
- Integración en navegación de administración y área pública (si aplica)

## Estado Actual

### ✅ Ya Implementado

1. **Modelo Notification**:
   - ✅ Modelo `App\Models\Notification` con campos:
     - `user_id` (FK a users)
     - `type` (enum: convocatoria, resolucion, noticia, revision, sistema)
     - `title` (string)
     - `message` (text)
     - `link` (nullable string)
     - `is_read` (boolean, default: false)
     - `read_at` (nullable timestamp)
   - ✅ Relación `belongsTo(User::class)`
   - ✅ Casts: `is_read` → boolean, `read_at` → datetime

2. **Factory y Tests**:
   - ✅ `NotificationFactory` con estado `read()`
   - ✅ Tests básicos del modelo en `tests/Feature/Models/NotificationTest.php`

3. **Migración**:
   - ✅ Tabla `notifications` creada con índices apropiados
   - ✅ Índices: `['user_id', 'is_read']`, `['type', 'created_at']`

### ⚠️ Pendiente de Implementar

1. **Servicio de Notificaciones**:
   - ⚠️ Crear servicio `NotificationService` para crear notificaciones
   - ⚠️ Métodos helper para cada tipo de notificación
   - ⚠️ Integración con eventos de publicación
   - ⚠️ Estructura preparada para tiempo real (opcional, futuro)

2. **Componente Livewire de Notificaciones**:
   - ⚠️ Crear `App\Livewire\Notifications\Bell` (icono con contador + polling)
   - ⚠️ Crear `App\Livewire\Notifications\Dropdown` (dropdown con lista + polling)
   - ⚠️ Crear `App\Livewire\Notifications\Index` (página completa de notificaciones)
   - ⚠️ Funcionalidad de marcar como leída
   - ⚠️ Funcionalidad de marcar todas como leídas
   - ⚠️ Funcionalidad de eliminar notificaciones
   - ⚠️ Implementar `wire:poll.30s` para actualización automática

3. **Integración en Eventos**:
   - ⚠️ Crear notificaciones cuando se publica una convocatoria
   - ⚠️ Crear notificaciones cuando se publica una resolución
   - ⚠️ Crear notificaciones cuando se publica una noticia
   - ⚠️ Crear notificaciones cuando se publica un documento

4. **Vistas y UI**:
   - ⚠️ Componente de icono de campana con contador
   - ⚠️ Dropdown con lista de notificaciones recientes
   - ⚠️ Página completa de notificaciones con filtros
   - ⚠️ Diseño responsive con Flux UI

5. **Rutas**:
   - ⚠️ Ruta para página de notificaciones
   - ⚠️ Rutas para acciones (marcar como leída, eliminar)

6. **Traducciones**:
   - ⚠️ Añadir traducciones para notificaciones
   - ⚠️ Mensajes de notificaciones por tipo

7. **Tests**:
   - ⚠️ Tests del servicio de notificaciones
   - ⚠️ Tests de componentes Livewire
   - ⚠️ Tests de integración con eventos

8. **Preparación para Tiempo Real (Opcional - Futuro)**:
   - ⚠️ Estructurar código para facilitar migración futura
   - ⚠️ Documentar cómo migrar a Laravel Echo si es necesario
   - ⚠️ No se implementa ahora, solo se prepara la estructura

---

## Plan de Implementación

### **Fase 1: Servicio de Notificaciones**

#### Paso 1.1: Crear NotificationService

**Objetivo**: Crear un servicio centralizado para gestionar notificaciones.

**Tareas**:
1. Crear `app/Services/NotificationService.php`:
   ```php
   class NotificationService
   {
       public function create(array $data): Notification
       public function notifyConvocatoriaPublished(Call $call, User|Collection $users): void
       public function notifyResolucionPublished(Resolution $resolution, User|Collection $users): void
       public function notifyNoticiaPublished(NewsPost $newsPost, User|Collection $users): void
       public function notifyDocumentoPublished(Document $document, User|Collection $users): void
       public function markAsRead(Notification $notification): void
       public function markAllAsRead(User $user): void
       public function getUnreadCount(User $user): int
   }
   ```

2. Implementar método `create()`:
   - Validar datos requeridos
   - Crear notificación en BD
   - Retornar instancia de Notification

3. Implementar métodos específicos por tipo:
   - `notifyConvocatoriaPublished()`: Crear notificación tipo 'convocatoria'
   - `notifyResolucionPublished()`: Crear notificación tipo 'resolucion'
   - `notifyNoticiaPublished()`: Crear notificación tipo 'noticia'
   - `notifyDocumentoPublished()`: Crear notificación tipo 'sistema' (o nuevo tipo 'documento')

4. Implementar métodos de gestión:
   - `markAsRead()`: Marcar notificación como leída
   - `markAllAsRead()`: Marcar todas las notificaciones de un usuario como leídas
   - `getUnreadCount()`: Obtener contador de no leídas

**Archivos a crear**:
- `app/Services/NotificationService.php`

**Resultado esperado**:
- Servicio completo para gestionar notificaciones
- Métodos helper para cada tipo de notificación

---

#### Paso 1.2: Mejorar Modelo Notification

**Objetivo**: Añadir métodos helper y scopes al modelo.

**Tareas**:
1. Añadir scopes al modelo:
   ```php
   public function scopeUnread($query)
   public function scopeRead($query)
   public function scopeByType($query, string $type)
   public function scopeRecent($query, int $days = 7)
   ```

2. Añadir métodos helper:
   ```php
   public function markAsRead(): void
   public function getTypeLabel(): string
   public function getTypeIcon(): string
   public function getTypeColor(): string
   ```

3. Añadir relación con User (ya existe, verificar)

**Archivos a modificar**:
- `app/Models/Notification.php`

**Resultado esperado**:
- Modelo con scopes y métodos helper útiles
- Código más limpio y reutilizable

---

### **Fase 2: Componente Livewire de Campana (Bell)**

#### Paso 2.1: Crear Componente Bell

**Objetivo**: Crear componente Livewire para mostrar icono de campana con contador usando polling.

**Tareas**:
1. Crear `app/Livewire/Notifications/Bell.php`:
   ```php
   class Bell extends Component
   {
       public int $unreadCount = 0;
       
       public function mount(): void
       {
           $this->loadUnreadCount();
       }
       
       public function loadUnreadCount(): void
       {
           $this->unreadCount = NotificationService::getUnreadCount(auth()->user());
       }
       
       public function render(): View
       {
           return view('livewire.notifications.bell');
       }
   }
   ```

2. Implementar polling con `wire:poll`:
   - Usar `wire:poll.30s="loadUnreadCount"` en la vista para actualizar cada 30 segundos
   - El polling se ejecuta automáticamente mientras el componente está visible
   - Considerar usar `wire:poll.keep-alive` para mantener la conexión activa
   - Opcional: Usar polling más frecuente cuando la página está activa (ej: 15s) y menos cuando está en segundo plano (ej: 60s)

3. Crear vista `resources/views/livewire/notifications/bell.blade.php`:
   - Icono de campana (Flux UI)
   - Badge con contador de no leídas
   - Enlace a dropdown o página de notificaciones
   - Implementar `wire:poll.30s="loadUnreadCount"` en el contenedor principal

**Archivos a crear**:
- `app/Livewire/Notifications/Bell.php`
- `resources/views/livewire/notifications/bell.blade.php`

**Resultado esperado**:
- Componente de campana funcional con contador
- Actualización automática del contador cada 30 segundos mediante polling
- Contador se actualiza sin necesidad de recargar la página

---

#### Paso 2.2: Crear Componente Dropdown

**Objetivo**: Crear dropdown con lista de notificaciones recientes.

**Tareas**:
1. Crear `app/Livewire/Notifications/Dropdown.php`:
   ```php
   class Dropdown extends Component
   {
       public Collection $notifications;
       public int $unreadCount = 0;
       
       public function mount(): void
       {
           $this->loadNotifications();
       }
       
       public function loadNotifications(): void
       {
           $this->notifications = auth()->user()
               ->notifications()
               ->unread()
               ->recent(7)
               ->latest()
               ->limit(10)
               ->get();
               
           $this->unreadCount = $this->notifications->count();
       }
       
       public function markAsRead(int $notificationId): void
       {
           $notification = Notification::findOrFail($notificationId);
           NotificationService::markAsRead($notification);
           $this->loadNotifications();
       }
       
       public function markAllAsRead(): void
       {
           NotificationService::markAllAsRead(auth()->user());
           $this->loadNotifications();
       }
       
       public function render(): View
       {
           return view('livewire.notifications.dropdown');
       }
   }
   ```

2. Crear vista `resources/views/livewire/notifications/dropdown.blade.php`:
   - Lista de notificaciones recientes (máx 10)
   - Cada notificación con:
     - Icono según tipo
     - Título y mensaje
     - Fecha relativa
     - Botón para marcar como leída
   - Botón "Ver todas" que lleva a página completa
   - Botón "Marcar todas como leídas"
   - Implementar `wire:poll.30s="loadNotifications"` para actualizar la lista periódicamente
   - El polling solo debe ejecutarse cuando el dropdown está abierto (usar `wire:poll.keep-alive` condicionalmente)

3. Integrar con componente Bell:
   - El componente Bell puede abrir el dropdown
   - O ambos pueden ser independientes
   - Compartir el mismo intervalo de polling para sincronización

**Archivos a crear**:
- `app/Livewire/Notifications/Dropdown.php`
- `resources/views/livewire/notifications/dropdown.blade.php`

**Resultado esperado**:
- Dropdown funcional con lista de notificaciones
- Acciones de marcar como leída funcionando
- Lista se actualiza automáticamente cada 30 segundos mediante polling

---

### **Fase 3: Página Completa de Notificaciones**

#### Paso 3.1: Crear Componente Index

**Objetivo**: Crear página completa para gestionar todas las notificaciones.

**Tareas**:
1. Crear `app/Livewire/Notifications/Index.php`:
   ```php
   class Index extends Component
   {
       use WithPagination;
       
       public string $filter = 'all'; // all, unread, read
       public ?string $filterType = null;
       
       public function mount(): void
       {
           // Autorización si es necesario
       }
       
       public function notifications()
       {
           $query = auth()->user()->notifications()->latest();
           
           if ($this->filter === 'unread') {
               $query->unread();
           } elseif ($this->filter === 'read') {
               $query->read();
           }
           
           if ($this->filterType) {
               $query->byType($this->filterType);
           }
           
           return $query->paginate(20);
       }
       
       public function markAsRead(int $notificationId): void
       {
           $notification = Notification::findOrFail($notificationId);
           NotificationService::markAsRead($notification);
           $this->dispatch('notification-read');
       }
       
       public function markAllAsRead(): void
       {
           NotificationService::markAllAsRead(auth()->user());
           $this->dispatch('notifications-read');
       }
       
       public function delete(int $notificationId): void
       {
           Notification::findOrFail($notificationId)->delete();
           $this->dispatch('notification-deleted');
       }
       
       public function render(): View
       {
           return view('livewire.notifications.index')
               ->layout('components.layouts.app');
       }
   }
   ```

2. Crear vista `resources/views/livewire/notifications/index.blade.php`:
   - Header con título y acciones
   - Filtros (todas, no leídas, leídas, por tipo)
   - Lista paginada de notificaciones
   - Cada notificación con:
     - Icono y tipo
     - Título y mensaje
     - Fecha
     - Enlace si tiene `link`
     - Acciones (marcar como leída, eliminar)
   - Estado vacío cuando no hay notificaciones
   - Breadcrumbs

**Archivos a crear**:
- `app/Livewire/Notifications/Index.php`
- `resources/views/livewire/notifications/index.blade.php`

**Resultado esperado**:
- Página completa de notificaciones funcional
- Filtros y paginación funcionando

---

#### Paso 3.2: Mejorar UX de la Página

**Objetivo**: Mejorar la experiencia de usuario en la página de notificaciones.

**Tareas**:
1. Añadir estados de carga:
   - Spinner mientras se cargan notificaciones
   - Usar `wire:loading` de Livewire

2. Añadir confirmación para eliminar:
   - Modal de confirmación antes de eliminar
   - O usar `wire:confirm` de Livewire 3

3. Mejorar diseño visual:
   - Cards para cada notificación
   - Colores según tipo
   - Iconos apropiados
   - Fechas relativas (hace 2 horas, ayer, etc.)

4. Añadir acciones en lote:
   - Checkbox para seleccionar múltiples
   - Botón "Marcar seleccionadas como leídas"
   - Botón "Eliminar seleccionadas"

**Archivos a modificar**:
- `resources/views/livewire/notifications/index.blade.php`
- `app/Livewire/Notifications/Index.php`

**Resultado esperado**:
- Interfaz pulida y responsive
- Mejor experiencia de usuario

---

### **Fase 4: Integración con Eventos de Publicación**

#### Paso 4.1: Integrar con Publicación de Convocatorias

**Objetivo**: Crear notificaciones cuando se publica una convocatoria.

**Tareas**:
1. Revisar componente `Admin\Calls\Edit` o `Admin\Calls\Create`:
   - Identificar dónde se publica una convocatoria (establecer `published_at`)

2. Añadir lógica de notificación:
   ```php
   // Cuando se publica una convocatoria
   if ($call->published_at && !$call->wasChanged('published_at')) {
       // Obtener usuarios a notificar (todos los usuarios activos, o según suscripciones)
       $users = User::where('is_active', true)->get();
       
       NotificationService::notifyConvocatoriaPublished($call, $users);
   }
   ```

3. Considerar suscripciones:
   - Si hay sistema de suscripciones, notificar solo a usuarios suscritos al programa
   - O notificar a todos los usuarios activos

**Archivos a modificar**:
- `app/Livewire/Admin/Calls/Edit.php` (o donde se publique)
- O crear Observer para el modelo Call

**Resultado esperado**:
- Notificaciones creadas automáticamente al publicar convocatorias

---

#### Paso 4.2: Integrar con Publicación de Resoluciones

**Objetivo**: Crear notificaciones cuando se publica una resolución.

**Tareas**:
1. Revisar componente de resoluciones:
   - Identificar dónde se publica una resolución

2. Añadir lógica de notificación:
   ```php
   // Cuando se publica una resolución
   if ($resolution->published_at && !$resolution->wasChanged('published_at')) {
       $users = User::where('is_active', true)->get();
       NotificationService::notifyResolucionPublished($resolution, $users);
   }
   ```

**Archivos a modificar**:
- Componente de resoluciones o Observer

**Resultado esperado**:
- Notificaciones creadas automáticamente al publicar resoluciones

---

#### Paso 4.3: Integrar con Publicación de Noticias

**Objetivo**: Crear notificaciones cuando se publica una noticia.

**Tareas**:
1. Revisar componente `Admin\News\Edit`:
   - Identificar dónde se publica una noticia

2. Añadir lógica de notificación:
   ```php
   // Cuando se publica una noticia
   if ($newsPost->published_at && !$newsPost->wasChanged('published_at')) {
       $users = User::where('is_active', true)->get();
       NotificationService::notifyNoticiaPublished($newsPost, $users);
   }
   ```

**Archivos a modificar**:
- `app/Livewire/Admin/News/Edit.php` (o Observer)

**Resultado esperado**:
- Notificaciones creadas automáticamente al publicar noticias

---

#### Paso 4.4: Integrar con Publicación de Documentos

**Objetivo**: Crear notificaciones cuando se publica un documento.

**Tareas**:
1. Revisar componente de documentos:
   - Identificar dónde se activa/publica un documento

2. Añadir lógica de notificación:
   ```php
   // Cuando se activa un documento
   if ($document->is_active && $document->wasChanged('is_active')) {
       $users = User::where('is_active', true)->get();
       NotificationService::notifyDocumentoPublished($document, $users);
   }
   ```

**Archivos a modificar**:
- Componente de documentos o Observer

**Resultado esperado**:
- Notificaciones creadas automáticamente al publicar documentos

---

#### Paso 4.5: Usar Observers (Alternativa Recomendada)

**Objetivo**: Usar Observers de Laravel para automatizar notificaciones.

**Tareas**:
1. Crear Observer para cada modelo:
   - `app/Observers/CallObserver.php`
   - `app/Observers/ResolutionObserver.php`
   - `app/Observers/NewsPostObserver.php`
   - `app/Observers/DocumentObserver.php`

2. Implementar método `updated()` en cada Observer:
   ```php
   public function updated(Call $call): void
   {
       if ($call->isDirty('published_at') && $call->published_at) {
           $users = User::where('is_active', true)->get();
           NotificationService::notifyConvocatoriaPublished($call, $users);
       }
   }
   ```

3. Registrar Observers en `AppServiceProvider`:
   ```php
   public function boot(): void
   {
       Call::observe(CallObserver::class);
       Resolution::observe(ResolutionObserver::class);
       NewsPost::observe(NewsPostObserver::class);
       Document::observe(DocumentObserver::class);
   }
   ```

**Archivos a crear**:
- `app/Observers/CallObserver.php`
- `app/Observers/ResolutionObserver.php`
- `app/Observers/NewsPostObserver.php`
- `app/Observers/DocumentObserver.php`

**Archivos a modificar**:
- `app/Providers/AppServiceProvider.php`

**Resultado esperado**:
- Notificaciones automáticas usando Observers
- Código más limpio y separado

---

### **Fase 5: Integración en Navegación**

#### Paso 5.1: Integrar en Header de Administración

**Objetivo**: Añadir componente de notificaciones en el header de administración.

**Tareas**:
1. Revisar `resources/views/components/layouts/app/header.blade.php`:
   - Identificar dónde añadir el componente de notificaciones

2. Añadir componente Bell antes del menú de usuario:
   ```blade
   <livewire:notifications.bell />
   ```

3. Integrar dropdown si es necesario:
   - O usar el componente Bell que abre el dropdown
   - O usar un componente combinado

**Archivos a modificar**:
- `resources/views/components/layouts/app/header.blade.php`

**Resultado esperado**:
- Icono de notificaciones visible en header de administración
- Contador de no leídas actualizado

---

#### Paso 5.2: Añadir Ruta de Notificaciones

**Objetivo**: Crear ruta para la página de notificaciones.

**Tareas**:
1. Añadir ruta en `routes/web.php`:
   ```php
   Route::middleware(['auth'])->group(function () {
       Route::get('/notificaciones', Notifications\Index::class)
           ->name('notifications.index');
   });
   ```

2. Verificar que la ruta funcione correctamente
3. Añadir comentarios descriptivos

**Archivos a modificar**:
- `routes/web.php`

**Resultado esperado**:
- Ruta creada y funcionando
- Accesible en `/notificaciones`

---

### **Fase 6: Traducciones**

#### Paso 6.1: Añadir Traducciones

**Objetivo**: Añadir todas las traducciones necesarias.

**Tareas**:
1. Revisar archivos de traducción:
   - `lang/es/common.php`
   - `lang/en/common.php`

2. Añadir traducciones para:
   - Título de página: "Notificaciones"
   - Labels de filtros (todas, no leídas, leídas)
   - Tipos de notificación (convocatoria, resolucion, noticia, sistema)
   - Mensajes de acciones (marcar como leída, eliminar)
   - Mensajes de estado vacío
   - Títulos y mensajes de notificaciones por tipo

3. Organizar en sección `notifications`:
   ```php
   'notifications' => [
       'title' => 'Notificaciones',
       'unread' => 'No leídas',
       'read' => 'Leídas',
       'all' => 'Todas',
       'mark_as_read' => 'Marcar como leída',
       'mark_all_as_read' => 'Marcar todas como leídas',
       'delete' => 'Eliminar',
       'empty' => 'No hay notificaciones',
       'types' => [
           'convocatoria' => 'Convocatoria',
           'resolucion' => 'Resolución',
           'noticia' => 'Noticia',
           'sistema' => 'Sistema',
       ],
   ]
   ```

**Archivos a modificar**:
- `lang/es/common.php`
- `lang/en/common.php`

**Resultado esperado**:
- Todas las traducciones añadidas
- Textos en español e inglés

---

### **Fase 7: Tests**

#### Paso 7.1: Crear Tests del Servicio

**Objetivo**: Crear tests para NotificationService.

**Tareas**:
1. Crear archivo de test:
   - `tests/Feature/Services/NotificationServiceTest.php`

2. Implementar tests:
   - Test de creación de notificación
   - Test de notificar convocatoria publicada
   - Test de notificar resolución publicada
   - Test de notificar noticia publicada
   - Test de notificar documento publicado
   - Test de marcar como leída
   - Test de marcar todas como leídas
   - Test de contador de no leídas

**Archivos a crear**:
- `tests/Feature/Services/NotificationServiceTest.php`

**Resultado esperado**:
- Tests del servicio creados y pasando

---

#### Paso 7.2: Crear Tests de Componentes Livewire

**Objetivo**: Crear tests para componentes Livewire.

**Tareas**:
1. Crear archivos de test:
   - `tests/Feature/Livewire/Notifications/BellTest.php`
   - `tests/Feature/Livewire/Notifications/DropdownTest.php`
   - `tests/Feature/Livewire/Notifications/IndexTest.php`

2. Implementar tests para cada componente:
   - Test de renderizado
   - Test de carga de notificaciones
   - Test de marcar como leída
   - Test de marcar todas como leídas
   - Test de eliminar notificación
   - Test de filtros
   - Test de paginación

**Archivos a crear**:
- `tests/Feature/Livewire/Notifications/BellTest.php`
- `tests/Feature/Livewire/Notifications/DropdownTest.php`
- `tests/Feature/Livewire/Notifications/IndexTest.php`

**Resultado esperado**:
- Tests de componentes creados y pasando

---

#### Paso 7.3: Crear Tests de Integración

**Objetivo**: Crear tests de integración con eventos de publicación.

**Tareas**:
1. Crear tests de integración:
   - Test de notificación al publicar convocatoria
   - Test de notificación al publicar resolución
   - Test de notificación al publicar noticia
   - Test de notificación al publicar documento

2. Verificar que:
   - Se crean notificaciones correctamente
   - Se notifica a los usuarios correctos
   - Los datos de la notificación son correctos

**Archivos a crear/modificar**:
- `tests/Feature/Notifications/IntegrationTest.php`

**Resultado esperado**:
- Tests de integración creados y pasando

---

### **Fase 8: Preparación para Tiempo Real (Opcional - Futuro)**

#### Paso 8.1: Preparar Estructura para Laravel Echo (Opcional)

**Objetivo**: Preparar la estructura del código para facilitar la migración a tiempo real en el futuro, sin implementarlo ahora.

**Tareas**:
1. **Estructurar NotificationService para facilitar migración**:
   - Crear método `createAndBroadcast()` que por ahora solo crea la notificación
   - Documentar dónde se añadiría el broadcasting cuando se implemente
   - Usar el método `create()` normal por ahora

2. **Documentar estructura futura**:
   - Crear comentarios en el código indicando dónde se añadiría Laravel Echo
   - Documentar en código los pasos necesarios para migrar a tiempo real
   - Crear archivo `docs/notificaciones-migracion-tiempo-real.md` con guía de migración

3. **Preparar eventos (sin implementar)**:
   - Crear clase `NotificationCreated` como evento normal (sin `ShouldBroadcast` por ahora)
   - Documentar que cuando se quiera tiempo real, solo hay que implementar `ShouldBroadcast`
   - El evento ya se puede usar para otros propósitos (logs, etc.)

4. **Estructura de código preparada**:
   ```php
   // En NotificationService
   public function createAndBroadcast(array $data): Notification
   {
       $notification = $this->create($data);
       
       // TODO: Cuando se implemente tiempo real, descomentar:
       // event(new NotificationCreated($notification));
       
       return $notification;
   }
   ```

**Consideraciones**:
- Esta fase es completamente opcional
- No requiere configuración adicional ahora
- Facilita la migración futura si se necesita tiempo real
- El sistema funciona perfectamente con polling sin esta fase

**Resultado esperado**:
- Código estructurado para facilitar migración futura
- Documentación de cómo migrar a tiempo real cuando sea necesario
- Sistema funciona completamente con polling sin necesidad de esta fase

---

## Consideraciones Técnicas

### Rendimiento

1. **Límite de Notificaciones**:
   - Limitar notificaciones mostradas en dropdown (ej: 10)
   - Paginación en página completa (20 por página)

2. **Optimización de Consultas**:
   - Usar eager loading para relaciones
   - Índices de BD ya configurados
   - Caché del contador de no leídas (opcional)

3. **Polling (Método Principal)**:
   - Usar `wire:poll.30s` para actualizar contador cada 30 segundos
   - Considerar polling más frecuente cuando la página está activa (15s) y menos cuando está en segundo plano (60s)
   - Usar `wire:poll.keep-alive` para mantener la conexión activa
   - El polling se detiene automáticamente cuando el componente no está visible
   - **Ventaja**: No requiere configuración adicional, funciona inmediatamente
   - **Desventaja**: Hay un retraso de hasta 30 segundos (aceptable para este caso de uso)

4. **Tiempo Real (Preparado para Futuro)**:
   - Estructura preparada para migrar a Laravel Echo si es necesario
   - Ver documentación en `docs/notificaciones-tiempo-real.md` para detalles
   - No se implementa ahora, pero el código está preparado

### Seguridad

1. **Autorización**:
   - Usuarios solo pueden ver sus propias notificaciones
   - Verificar `user_id` en todas las acciones

2. **Validación**:
   - Validar datos al crear notificaciones
   - Sanitizar mensajes y títulos

### Accesibilidad

1. **ARIA Labels**:
   - Añadir labels apropiados
   - Indicar estado de notificaciones

2. **Navegación por Teclado**:
   - Asegurar que todos los elementos sean accesibles
   - Orden lógico de tabulación

### Responsive

1. **Móviles**:
   - Dropdown optimizado para pantallas pequeñas
   - Página completa responsive

2. **Tabletas y Desktop**:
   - Layout optimizado para diferentes tamaños

---

## Estructura de Archivos

```
app/
  Livewire/
    Notifications/
      Bell.php                    # Componente de campana
      Dropdown.php                # Componente de dropdown
      Index.php                   # Página completa
  Models/
    Notification.php             # Modelo (mejorado)
  Observers/
    CallObserver.php              # Observer para Call
    ResolutionObserver.php       # Observer para Resolution
    NewsPostObserver.php          # Observer para NewsPost
    DocumentObserver.php          # Observer para Document
  Services/
    NotificationService.php      # Servicio de notificaciones

resources/
  views/
    livewire/
      notifications/
        bell.blade.php           # Vista de campana
        dropdown.blade.php       # Vista de dropdown
        index.blade.php          # Vista de página completa

routes/
  web.php                         # Ruta /notificaciones

lang/
  es/
    common.php                    # Traducciones ES
  en/
    common.php                    # Traducciones EN

tests/
  Feature/
    Services/
      NotificationServiceTest.php  # Tests del servicio
    Livewire/
      Notifications/
        BellTest.php             # Tests de Bell
        DropdownTest.php         # Tests de Dropdown
        IndexTest.php            # Tests de Index
    Notifications/
      IntegrationTest.php        # Tests de integración
```

---

## Checklist de Implementación

### Fase 1: Servicio de Notificaciones
- [ ] Paso 1.1: Crear NotificationService
- [ ] Paso 1.2: Mejorar Modelo Notification

### Fase 2: Componente Livewire de Campana
- [ ] Paso 2.1: Crear Componente Bell
- [ ] Paso 2.2: Crear Componente Dropdown

### Fase 3: Página Completa de Notificaciones
- [ ] Paso 3.1: Crear Componente Index
- [ ] Paso 3.2: Mejorar UX de la Página

### Fase 4: Integración con Eventos de Publicación
- [ ] Paso 4.1: Integrar con Publicación de Convocatorias
- [ ] Paso 4.2: Integrar con Publicación de Resoluciones
- [ ] Paso 4.3: Integrar con Publicación de Noticias
- [ ] Paso 4.4: Integrar con Publicación de Documentos
- [ ] Paso 4.5: Usar Observers (Alternativa Recomendada)

### Fase 5: Integración en Navegación
- [ ] Paso 5.1: Integrar en Header de Administración
- [ ] Paso 5.2: Añadir Ruta de Notificaciones

### Fase 6: Traducciones
- [ ] Paso 6.1: Añadir Traducciones

### Fase 7: Tests
- [ ] Paso 7.1: Crear Tests del Servicio
- [ ] Paso 7.2: Crear Tests de Componentes Livewire
- [ ] Paso 7.3: Crear Tests de Integración

### Fase 8: Preparación para Tiempo Real (Opcional - Futuro)
- [ ] Paso 8.1: Preparar Estructura para Laravel Echo (Opcional)

---

## Próximos Pasos

Una vez completado este plan, el siguiente paso sería:

1. **Revisar y aprobar el plan** antes de comenzar la implementación
2. **Comenzar con Fase 1** - Servicio de Notificaciones
3. **Implementar iterativamente** - Completar cada fase antes de pasar a la siguiente
4. **Testing continuo** - Ejecutar tests después de cada fase
5. **Revisión final** - Verificar que todo funciona correctamente antes de marcar como completado

---

---

## Nota sobre Polling vs Tiempo Real

Este plan implementa **polling como método principal** para actualizar notificaciones. Esto significa:

- ✅ **Actualización automática cada 30 segundos** usando `wire:poll` de Livewire
- ✅ **No requiere configuración adicional** (servidores WebSocket, etc.)
- ✅ **Funciona inmediatamente** sin dependencias externas
- ✅ **Suficiente para la mayoría de casos de uso** (retraso de 30s es aceptable)

Si en el futuro necesitas notificaciones en tiempo real (< 1 segundo de latencia), la estructura está preparada para migrar a Laravel Echo. Ver:
- `docs/notificaciones-tiempo-real.md` - Explicación detallada de tiempo real
- Fase 8 de este plan - Preparación para migración futura

---

**Fecha de Creación**: Enero 2026  
**Última Actualización**: Enero 2026 (Actualizado para usar polling como método principal)  
**Estado**: 📋 Plan detallado completado - Pendiente de aprobación para comenzar implementación
