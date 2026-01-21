# Arquitectura de la Aplicación - Erasmus+ Centro (Murcia)

Este documento describe la arquitectura técnica de la aplicación Erasmus+ Centro, incluyendo su estructura, capas, componentes principales y patrones de diseño utilizados.

---

## 1. Visión General

### 1.1. Descripción

Erasmus+ Centro es una aplicación web monolítica construida con Laravel 12 y Livewire 3. Sigue una arquitectura MVC extendida con componentes reactivos del lado del servidor.

### 1.2. Diagrama de Arquitectura

```
┌─────────────────────────────────────────────────────────────────────────┐
│                              CLIENTE                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                   │
│  │   Browser    │  │   Alpine.js  │  │   Chart.js   │                   │
│  │   (HTML/CSS) │  │ (Interacción)│  │  (Gráficos)  │                   │
│  └──────────────┘  └──────────────┘  └──────────────┘                   │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ HTTP/WebSocket
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           CAPA DE PRESENTACIÓN                           │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                         Livewire 3                                │   │
│  │  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐   │   │
│  │  │ Componentes     │  │ Componentes     │  │ Componentes     │   │   │
│  │  │ Públicos (15)   │  │ Admin (50+)     │  │ Settings (6)    │   │   │
│  │  └─────────────────┘  └─────────────────┘  └─────────────────┘   │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                    Blade + Flux UI v2                             │   │
│  │  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐   │   │
│  │  │ Layouts (2)     │  │ Componentes UI  │  │ Componentes     │   │   │
│  │  │ (public, admin) │  │ (x-ui.*)        │  │ Contenido       │   │   │
│  │  └─────────────────┘  └─────────────────┘  └─────────────────┘   │   │
│  └──────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                        CAPA DE LÓGICA DE NEGOCIO                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │
│  │  Services   │  │  Observers  │  │   Policies  │  │Form Requests│    │
│  │  (1)        │  │  (4)        │  │   (16)      │  │   (30)      │    │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘    │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │
│  │   Exports   │  │   Imports   │  │    Mail     │  │  Constants  │    │
│  │   (4)       │  │   (2)       │  │    (2)      │  │    (2)      │    │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘    │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           CAPA DE DATOS                                  │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                       Eloquent ORM                                │   │
│  │  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐   │   │
│  │  │ Modelos (18)    │  │ Relaciones      │  │ Scopes          │   │   │
│  │  │ + Traits        │  │ (HasMany, etc.) │  │ (published, etc)│   │   │
│  │  └─────────────────┘  └─────────────────┘  └─────────────────┘   │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                          MySQL 8.0+                               │   │
│  │  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐   │   │
│  │  │ 25+ Tablas      │  │ Índices         │  │ Foreign Keys    │   │   │
│  │  │ principales     │  │ optimizados     │  │ con SoftDeletes │   │   │
│  │  └─────────────────┘  └─────────────────┘  └─────────────────┘   │   │
│  └──────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                      CAPA DE INFRAESTRUCTURA                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │
│  │Media Library│  │ Permission  │  │ Activitylog │  │Laravel Excel│    │
│  │(Multimedia) │  │(Roles/Perm) │  │ (Auditoría) │  │(Import/Exp) │    │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘    │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │
│  │  FilePond   │  │   Fortify   │  │    Cache    │  │   Storage   │    │
│  │  (Uploads)  │  │   (Auth)    │  │  (Redis/DB) │  │   (Files)   │    │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘    │
└─────────────────────────────────────────────────────────────────────────┘
```

### 1.3. Stack Tecnológico

| Capa | Tecnología | Versión |
|------|------------|---------|
| **Framework** | Laravel | 12.x |
| **Componentes UI** | Livewire | 3.x |
| **Estilos** | Tailwind CSS | 4.x |
| **Componentes** | Flux UI | 2.x |
| **Base de Datos** | MySQL | 8.0+ |
| **Autenticación** | Laravel Fortify | 1.x |
| **Autorización** | Spatie Permission | 6.x |
| **Multimedia** | Spatie Media Library | 11.x |
| **Auditoría** | Spatie Activitylog | 4.x |
| **Excel** | Laravel Excel | 3.x |
| **Testing** | Pest PHP | 4.x |

---

## 2. Estructura de Directorios

```
erasmus25/
├── app/
│   ├── Constants/              # Constantes del sistema
│   │   ├── Permissions.php     # Definición de permisos
│   │   └── Roles.php           # Definición de roles
│   │
│   ├── Exports/                # Exportaciones a Excel
│   │   ├── CallsExport.php
│   │   ├── NewsletterSubscriptionsExport.php
│   │   ├── ResolutionsExport.php
│   │   └── AuditLogsExport.php
│   │
│   ├── Http/
│   │   ├── Middleware/         # Middleware personalizado
│   │   │   └── SetLocale.php   # Gestión de idioma
│   │   └── Requests/           # Form Requests (30 archivos)
│   │       ├── Program/
│   │       ├── Call/
│   │       ├── News/
│   │       └── ...
│   │
│   ├── Imports/                # Importaciones desde Excel
│   │   ├── CallsImport.php
│   │   └── UsersImport.php
│   │
│   ├── Livewire/               # Componentes Livewire
│   │   ├── Admin/              # Panel de administración (50+)
│   │   │   ├── Dashboard.php
│   │   │   ├── Programs/       # Index, Create, Edit, Show
│   │   │   ├── Calls/          # + Phases/, Resolutions/
│   │   │   ├── News/
│   │   │   ├── Documents/
│   │   │   ├── Events/
│   │   │   ├── Users/
│   │   │   ├── Roles/
│   │   │   ├── AuditLogs/
│   │   │   ├── Newsletter/
│   │   │   ├── Settings/
│   │   │   └── Translations/
│   │   │
│   │   ├── Auth/               # Autenticación (Login, Register, etc.)
│   │   ├── Language/           # Selector de idioma
│   │   ├── Notifications/      # Sistema de notificaciones
│   │   ├── Search/             # Búsqueda global
│   │   ├── Settings/           # Configuración de usuario
│   │   │
│   │   ├── Home.php            # Página de inicio
│   │   ├── Programs/           # Área pública de programas
│   │   ├── Calls/              # Área pública de convocatorias
│   │   ├── News/               # Área pública de noticias
│   │   ├── Documents/          # Área pública de documentos
│   │   ├── Events/             # Área pública de eventos
│   │   └── Newsletter/         # Suscripción a newsletter
│   │
│   ├── Mail/                   # Emails
│   │   ├── NewsletterVerification.php
│   │   └── NewsletterUnsubscribe.php
│   │
│   ├── Models/                 # Modelos Eloquent (18)
│   │   ├── Concerns/           # Traits de modelos
│   │   │   └── Translatable.php
│   │   ├── User.php
│   │   ├── Program.php
│   │   ├── Call.php
│   │   ├── CallPhase.php
│   │   ├── Resolution.php
│   │   ├── NewsPost.php
│   │   ├── NewsTag.php
│   │   ├── Document.php
│   │   ├── DocumentCategory.php
│   │   ├── ErasmusEvent.php
│   │   ├── AcademicYear.php
│   │   ├── NewsletterSubscription.php
│   │   ├── Notification.php
│   │   ├── Setting.php
│   │   ├── Translation.php
│   │   ├── Language.php
│   │   ├── MediaConsent.php
│   │   └── CallApplication.php
│   │
│   ├── Observers/              # Observers de modelos
│   │   ├── CallObserver.php
│   │   ├── ResolutionObserver.php
│   │   ├── NewsPostObserver.php
│   │   └── DocumentObserver.php
│   │
│   ├── Policies/               # Policies de autorización (16)
│   │   ├── ProgramPolicy.php
│   │   ├── CallPolicy.php
│   │   ├── NewsPostPolicy.php
│   │   └── ...
│   │
│   ├── Providers/              # Service Providers
│   │   └── AppServiceProvider.php
│   │
│   ├── Services/               # Servicios de negocio
│   │   └── NotificationService.php
│   │
│   └── Support/                # Helpers y utilidades
│       └── helpers.php         # Funciones globales
│
├── config/                     # Configuración
│   ├── app.php
│   ├── database.php
│   ├── media-library.php       # Config de Media Library
│   ├── permission.php          # Config de Spatie Permission
│   └── activitylog.php         # Config de Activitylog
│
├── database/
│   ├── factories/              # Factories para testing (18)
│   ├── migrations/             # Migraciones (30+)
│   └── seeders/                # Seeders de datos
│       ├── DatabaseSeeder.php
│       ├── RolesAndPermissionsSeeder.php
│       ├── ProgramSeeder.php
│       └── ...
│
├── resources/
│   ├── css/
│   │   └── app.css             # Estilos Tailwind
│   │
│   ├── js/
│   │   ├── app.js              # JavaScript principal
│   │   └── tiptap-editor.js    # Editor de texto enriquecido
│   │
│   └── views/
│       ├── components/         # Componentes Blade
│       │   ├── layouts/        # Layouts (app, public, admin)
│       │   ├── ui/             # Componentes UI reutilizables
│       │   └── content/        # Componentes de contenido
│       │
│       └── livewire/           # Vistas de componentes Livewire
│           ├── admin/
│           └── ...
│
├── routes/
│   ├── web.php                 # Rutas web (públicas + admin)
│   └── console.php             # Comandos Artisan
│
├── storage/
│   └── app/public/             # Archivos subidos
│
├── tests/
│   ├── Feature/                # Tests de integración
│   └── Unit/                   # Tests unitarios
│
└── docs/                       # Documentación técnica
```

---

## 3. Capas de la Aplicación

### 3.1. Capa de Presentación

#### Livewire 3

Los componentes Livewire son el núcleo de la interfaz. Cada componente encapsula:
- **Estado**: Propiedades públicas reactivas
- **Acciones**: Métodos que responden a eventos del usuario
- **Vista**: Template Blade asociado

```php
// Ejemplo: app/Livewire/Admin/Programs/Index.php
class Index extends Component
{
    public string $search = '';           // Estado reactivo
    public string $status = 'all';        // Filtro
    
    public function delete(Program $program)  // Acción
    {
        $this->authorize('delete', $program);
        $program->delete();
    }
    
    public function render(): View         // Vista
    {
        return view('livewire.admin.programs.index', [
            'programs' => $this->getPrograms(),
        ]);
    }
}
```

#### Componentes Blade

Se organizan en tres categorías:

| Tipo | Prefijo | Uso |
|------|---------|-----|
| **Layout** | `x-layouts.*` | Estructuras de página |
| **UI** | `x-ui.*` | Elementos reutilizables (botones, cards, badges) |
| **Content** | `x-content.*` | Contenido específico (call-phase-timeline, etc.) |

#### Flux UI v2

Biblioteca de componentes predefinidos de Livewire:

```blade
<flux:button variant="primary">Guardar</flux:button>
<flux:input wire:model="name" label="Nombre" />
<flux:modal name="confirm-delete">...</flux:modal>
```

### 3.2. Capa de Lógica de Negocio

#### Form Requests (Validación)

30 Form Requests organizados por entidad:

```php
// app/Http/Requests/Program/StoreProgramRequest.php
class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Program::class);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:programs'],
            'name' => ['required', 'string', 'max:255'],
            // ...
        ];
    }
}
```

#### Policies (Autorización)

16 Policies que definen permisos granulares:

```php
// app/Policies/ProgramPolicy.php
class ProgramPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            return true;  // Super admin puede todo
        }
        return null;
    }

    public function view(User $user, Program $program): bool
    {
        return $user->can(Permissions::PROGRAMS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::PROGRAMS_CREATE);
    }
}
```

#### Observers

4 Observers para lógica de eventos de modelos:

| Observer | Modelo | Funcionalidad |
|----------|--------|---------------|
| `CallObserver` | Call | Notifica al publicar convocatoria |
| `ResolutionObserver` | Resolution | Notifica al publicar resolución |
| `NewsPostObserver` | NewsPost | Notifica al publicar noticia |
| `DocumentObserver` | Document | Notifica al activar documento |

#### Services

```php
// app/Services/NotificationService.php
class NotificationService
{
    public function notifyContentPublished(
        string $type, 
        Model $content, 
        string $title
    ): void {
        // Crea notificaciones para usuarios admin
    }
}
```

### 3.3. Capa de Datos

#### Modelos Eloquent

18 modelos con relaciones bien definidas:

```php
// app/Models/Call.php
class Call extends Model
{
    use HasFactory, SoftDeletes;
    use InteractsWithMedia;        // Spatie Media Library
    use LogsActivity;              // Spatie Activitylog

    protected $casts = [
        'destinations' => 'array',
        'scoring_table' => 'array',
        'published_at' => 'datetime',
    ];

    // Relaciones
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(CallPhase::class)->orderBy('order');
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }
}
```

#### Diagrama de Relaciones (Simplificado)

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   Program   │──────<│    Call     │──────<│  CallPhase  │
└─────────────┘  1:N  └─────────────┘  1:N  └─────────────┘
      │                     │                     │
      │1:N                  │1:N                  │1:N
      ▼                     ▼                     ▼
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│  NewsPost   │       │ErasmusEvent │       │ Resolution  │
└─────────────┘       └─────────────┘       └─────────────┘
      │                     
      │N:M                  
      ▼                     
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   NewsTag   │       │  Document   │──────>│DocCategory  │
└─────────────┘       └─────────────┘  N:1  └─────────────┘

┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    User     │──────<│Notification │       │AcademicYear │
└─────────────┘  1:N  └─────────────┘       └─────────────┘
      │
      │N:M (Spatie)
      ▼
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    Role     │──────<│ Permission  │       │  Setting    │
└─────────────┘  N:M  └─────────────┘       └─────────────┘
```

### 3.4. Capa de Infraestructura

#### Spatie Media Library

Gestión de archivos multimedia:

```php
// En el modelo
class NewsPost extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(400)->height(300)
            ->format('webp');
    }
}
```

#### Spatie Permission

Sistema de roles y permisos:

```php
// Verificar permiso
if ($user->can('programs.create')) { ... }

// En Policy
return $user->can(Permissions::PROGRAMS_CREATE);

// En Blade
@can('create', App\Models\Program::class)
    <button>Crear Programa</button>
@endcan
```

#### Spatie Activitylog

Auditoría automática:

```php
// En el modelo
class Program extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'is_active'])
            ->logOnlyDirty();
    }
}

// Logging manual
activity()
    ->performedOn($program)
    ->causedBy(auth()->user())
    ->withProperties(['ip' => request()->ip()])
    ->log('published');
```

#### Laravel Excel

Importación y exportación:

```php
// Exportar
return Excel::download(
    new CallsExport($filters), 
    'convocatorias.xlsx'
);

// Importar
Excel::import(new UsersImport, $file);
```

---

## 4. Flujo de Datos

### 4.1. Ciclo de Vida de una Petición

```
1. Request HTTP
       │
       ▼
2. Middleware (SetLocale, Auth, etc.)
       │
       ▼
3. Router → Componente Livewire
       │
       ▼
4. Componente:
   a) Autorización (Policy)
   b) Validación (Form Request / inline)
   c) Lógica de negocio
   d) Acceso a datos (Eloquent)
       │
       ▼
5. Render vista Blade
       │
       ▼
6. Response HTML (con estado Livewire)
```

### 4.2. Flujo de Autenticación

```
1. Usuario accede a /login
       │
       ▼
2. Livewire\Auth\Login muestra formulario
       │
       ▼
3. Usuario envía credenciales
       │
       ▼
4. Laravel Fortify valida y autentica
       │
       ├─── Si tiene 2FA → TwoFactorChallenge
       │
       ▼
5. Redirect a /admin (dashboard)
       │
       ▼
6. Middleware 'auth' protege rutas admin
```

### 4.3. Flujo de Autorización

```
1. Usuario intenta acción (ej: editar programa)
       │
       ▼
2. Componente Livewire llama: $this->authorize('update', $program)
       │
       ▼
3. ProgramPolicy::update() se ejecuta:
   a) before() → ¿Es super-admin? → Permitir
   b) update() → ¿Tiene permiso 'programs.edit'?
       │
       ├─── Autorizado → Continúa acción
       │
       └─── Denegado → 403 Forbidden
```

---

## 5. Patrones de Diseño

### 5.1. Service Pattern

Encapsula lógica de negocio compleja:

```php
// app/Services/NotificationService.php
class NotificationService
{
    public function notifyContentPublished(...): void
    {
        $admins = User::role([Roles::SUPER_ADMIN, Roles::ADMIN])->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                // ...
            ]);
        }
    }
}
```

### 5.2. Observer Pattern

Reacciona a eventos del ciclo de vida de modelos:

```php
// app/Observers/CallObserver.php
class CallObserver
{
    public function updated(Call $call): void
    {
        // Si se acaba de publicar
        if ($call->wasChanged('published_at') && $call->published_at) {
            app(NotificationService::class)->notifyContentPublished(
                'call_published',
                $call,
                "Nueva convocatoria: {$call->title}"
            );
        }
    }
}
```

### 5.3. Trait Pattern

Comportamiento reutilizable en modelos:

```php
// app/Models/Concerns/Translatable.php
trait Translatable
{
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function translate(string $field, string $locale): ?string
    {
        return $this->translations
            ->where('locale', $locale)
            ->where('field', $field)
            ->first()?->value;
    }
}

// Uso en modelo
class Program extends Model
{
    use Translatable;
}
```

### 5.4. Policy Pattern

Autorización declarativa:

```php
// Definición en Policy
public function publish(User $user, NewsPost $newsPost): bool
{
    return $user->can(Permissions::NEWS_PUBLISH);
}

// Uso en componente Livewire
public function publish(NewsPost $newsPost): void
{
    $this->authorize('publish', $newsPost);
    $newsPost->update(['published_at' => now()]);
}
```

### 5.5. Scope Pattern

Consultas reutilizables en modelos:

```php
// En el modelo
public function scopePublished(Builder $query): Builder
{
    return $query->whereNotNull('published_at');
}

public function scopeForProgram(Builder $query, int $programId): Builder
{
    return $query->where('program_id', $programId);
}

// Uso
$calls = Call::published()->forProgram($id)->get();
```

---

## 6. Seguridad

### 6.1. Autenticación

- **Laravel Fortify** para login, registro, reset de contraseña
- **2FA opcional** con códigos de recuperación
- **Sesiones** con timeout configurable
- **CSRF** en todos los formularios

### 6.2. Autorización

- **4 roles**: super-admin, admin, editor, viewer
- **Permisos granulares** por módulo (view, create, edit, delete, publish)
- **Policies** en todos los modelos
- **Middleware** de autenticación en rutas admin

### 6.3. Validación

- **Form Requests** para toda entrada de datos
- **Sanitización** automática de HTML (editor Tiptap)
- **Validación de archivos** (tipo, tamaño)

### 6.4. Protección de Datos

- **SoftDeletes** para recuperación de datos
- **Auditoría** de todas las acciones
- **GDPR**: Hard delete en newsletter con confirmación

---

## 7. Rendimiento

### 7.1. Optimizaciones Implementadas

| Área | Técnica |
|------|---------|
| **Consultas** | Eager loading, índices de BD, eliminación de N+1 |
| **Caché** | Datos de referencia (programas, años académicos) |
| **Imágenes** | Conversión a WebP, thumbnails, lazy loading |
| **Exports** | Chunking para grandes volúmenes |
| **Frontend** | Debounce en búsquedas, polling optimizado |

### 7.2. Índices de Base de Datos

```sql
-- Ejemplos de índices creados
CREATE INDEX calls_program_id_index ON calls(program_id);
CREATE INDEX calls_published_at_index ON calls(published_at);
CREATE INDEX news_posts_published_at_index ON news_posts(published_at);
CREATE INDEX activity_log_subject_type_subject_id_index ON activity_log(subject_type, subject_id);
```

---

## 8. Testing

### 8.1. Estructura de Tests

```
tests/
├── Feature/
│   ├── Livewire/
│   │   ├── Admin/          # Tests de componentes admin
│   │   └── Public/         # Tests de componentes públicos
│   ├── Models/             # Tests de modelos y relaciones
│   ├── Policies/           # Tests de autorización
│   └── ...
└── Unit/
    ├── FormRequests/       # Tests de validación
    └── ...
```

### 8.2. Cobertura

- **3,867+ tests** con **8,793+ assertions**
- **100%** cobertura en modelos, policies, form requests
- **100%** cobertura en componentes Livewire

### 8.3. Ejecutar Tests

```bash
# Todos los tests
php artisan test

# En paralelo (más rápido)
php artisan test --parallel

# Con filtro
php artisan test --filter="ProgramTest"
```

---

## 9. Referencias

- [Documentación de Laravel](https://laravel.com/docs)
- [Documentación de Livewire](https://livewire.laravel.com/docs)
- [Documentación de Flux UI](https://fluxui.dev/docs)
- [Documentación de Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Documentación de Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)
- [Documentación de Spatie Activitylog](https://spatie.be/docs/laravel-activitylog)

---

**Última actualización**: Enero 2026
