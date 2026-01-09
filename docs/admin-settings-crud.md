# CRUD de Configuración del Sistema en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Configuraciones del Sistema en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Configuración del Sistema permite a los administradores gestionar todas las configuraciones del sistema desde el panel de administración. Incluye funcionalidades avanzadas como validación automática según tipo de dato (string, integer, boolean, json), gestión de traducciones, subida de imágenes para el logo del centro, formateo inteligente de valores, y registro de auditoría de cambios.

## Características Principales

- ✅ **CRUD Completo**: Visualizar y editar configuraciones del sistema
- ✅ **Validación por Tipo**: Validación automática según tipo de dato (string, integer, boolean, json)
- ✅ **Gestión de Traducciones**: Traducción de descripciones y valores (para strings)
- ✅ **Subida de Imágenes**: Gestión del logo del centro mediante FilePond
- ✅ **Formateo Inteligente**: Visualización formateada de valores según tipo
- ✅ **Agrupación por Grupos**: Configuraciones organizadas por categorías (general, email, rgpd, media, seo)
- ✅ **Búsqueda y Filtros**: Búsqueda por clave, valor o descripción; filtro por grupo
- ✅ **Registro de Auditoría**: Registro automático de usuario y fecha de actualización
- ✅ **Autorización**: Control de acceso mediante `SettingPolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 82 tests pasando (176 assertions)

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Settings\Index`
- **Vista**: `resources/views/livewire/admin/settings/index.blade.php`
- **Ruta**: `/admin/configuracion` (nombre: `admin.settings.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'grupo')]
public string $filterGroup = '';

#[Url(as: 'ordenar')]
public string $sortField = 'group';

#[Url(as: 'direccion')]
public string $sortDirection = 'asc';
```

**Métodos Principales:**

- `settings()` - Computed property con agrupación por grupos
  - Eager loading: `with(['updater:id,name,email'])`, `withCount('translations')`
  - Filtro por búsqueda (key, value, description)
  - Filtro por grupo
  - Ordenación por campo y dirección
  - Agrupación por `group`
- `availableGroups()` - Computed property para obtener grupos disponibles
- `sortBy($field)` - Ordenación con toggle de dirección
- `resetFilters()` - Resetear filtros
- `formatValue($setting)` - Formatear valor según tipo para visualización
- `isValueTruncated($setting)` - Verificar si el valor está truncado
- `getFullValue($setting)` - Obtener valor completo para tooltip
- `getGroupLabel($group)` - Obtener etiqueta traducida del grupo
- `getTypeLabel($type)` - Obtener etiqueta traducida del tipo
- `getTypeBadgeVariant($type)` - Obtener variante de badge para el tipo
- `getGroupBadgeVariant($group)` - Obtener variante de badge para el grupo
- `canEdit()` - Verificar si el usuario puede editar configuraciones
- `hasTranslations($setting)` - Verificar si la configuración tiene traducciones

**Características:**

- Tabla responsive agrupada por grupos (general, email, rgpd, media, seo)
- Columnas: Clave, Valor, Tipo, Descripción, Última Actualización, Acciones
- Búsqueda en tiempo real con debounce (key, value, description)
- Filtro por grupo con dropdown
- Ordenación por campo (group, key) con indicadores visuales
- Formateo inteligente de valores:
  - **Boolean**: "Sí" / "No"
  - **Integer**: Formato con separador de miles (1.234)
  - **JSON**: "JSON Object (N elementos)"
  - **String**: Truncado a 100 caracteres con tooltip
- Tooltips para valores truncados o JSON completos
- Badges para tipos y grupos con colores diferenciados
- Indicador de traducciones disponibles (icono de idioma)
- Información de última actualización con usuario
- Estados de carga y vacío

---

### 2. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Settings\Edit`
- **Vista**: `resources/views/livewire/admin/settings/edit.blade.php`
- **Ruta**: `/admin/configuracion/{setting}/editar` (nombre: `admin.settings.edit`)

**Propiedades Públicas:**

```php
public Setting $setting;
public mixed $value = null;
public string $description = '';
public array $translations = [];
public ?string $jsonPreview = null;
public ?UploadedFile $logoFile = null;
public bool $removeExistingLogo = false;
```

**Métodos Principales:**

- `mount(Setting $setting)` - Inicialización con autorización y carga de datos
  - Cargar valor según tipo (especial manejo para JSON)
  - Cargar descripción
  - Cargar traducciones existentes
  - Generar preview de JSON si aplica
- `loadTranslations()` - Cargar traducciones existentes para todos los idiomas activos
- `availableLanguages` - Computed property para obtener idiomas activos
- `updatedValue($value)` - Validación en tiempo real cuando cambia el valor
- `validateValue($value)` - Validar valor según tipo y añadir errores si es necesario
- `isCenterLogo()` - Verificar si es la configuración `center_logo`
- `getCurrentLogoUrl()` - Obtener URL pública del logo actual
- `removeLogo()` - Marcar logo para eliminación
- `validateUploadedFile($filename)` - Validar archivo subido (FilePond)
- `getValidationRules()` - Obtener reglas de validación según tipo
- `getValidationMessages()` - Obtener mensajes de error personalizados
- `confirmUpdate()` - Validar y mostrar modal de confirmación
- `update()` - Actualizar configuración
  - Validar datos
  - Manejar subida de logo (si aplica)
  - Actualizar valor y descripción
  - Guardar traducciones
  - Registrar usuario que actualiza
  - Invalidar caché
  - Redirigir a index
- `saveTranslations()` - Guardar traducciones de description y value (solo strings)

**Características:**

- Formulario dinámico según tipo de dato:
  - **String**: Textarea (o FilePond para `center_logo`)
  - **Integer**: Input number
  - **Boolean**: Switch
  - **JSON**: Textarea con preview formateado
- Validación en tiempo real con feedback inmediato
- Gestión especial para `center_logo`:
  - Subida de imagen mediante FilePond
  - Preview de imagen actual
  - Opción para eliminar logo existente
  - Fallback a input de URL manual
  - Validación de tipo de archivo (JPG, PNG, SVG, WebP)
  - Tamaño máximo: 5MB
- Gestión de traducciones:
  - Tabs por idioma activo
  - Traducción de `description` (siempre disponible)
  - Traducción de `value` (solo para tipo string)
  - Eliminación automática de traducciones vacías
- Preview de JSON formateado con sintaxis destacada
- Validación de JSON en tiempo real
- Campos inmutables (key y type) mostrados como solo lectura
- Modal de confirmación antes de actualizar
- Mensajes de éxito/error con notificaciones
- Breadcrumbs de navegación

---

## Form Request

### UpdateSettingRequest

**Ubicación:**
- **Clase**: `App\Http\Requests\UpdateSettingRequest`
- **Ruta**: Usado internamente por componente Livewire Edit

**Reglas de Validación:**

```php
// Según tipo de configuración:
'string' => ['required', 'string']
'integer' => ['required', 'integer']
'boolean' => ['required', 'boolean']
'json' => ['required', 'json']
'description' => ['nullable', 'string']
```

**Características:**

- Autorización mediante `SettingPolicy::update()`
- Validación dinámica según tipo de configuración
- Conversión automática de valores boolean (string a boolean)
- Conversión automática de arrays a JSON
- Validación de sintaxis JSON
- Mensajes de error personalizados en español e inglés
- Protección de campos inmutables (key, type)

---

## Policy

### SettingPolicy

**Ubicación:**
- **Clase**: `App\Policies\SettingPolicy`

**Métodos:**

- `before(User $user, string $ability)` - Super-admin tiene acceso total
- `viewAny(User $user)` - Requiere permiso `SETTINGS_VIEW`
- `view(User $user, Setting $setting)` - Requiere permiso `SETTINGS_VIEW`
- `create(User $user)` - Solo super-admin
- `update(User $user, Setting $setting)` - Requiere permiso `SETTINGS_EDIT`
- `delete(User $user, Setting $setting)` - Solo super-admin

**Permisos Requeridos:**

- `settings.view` - Ver configuraciones
- `settings.edit` - Editar configuraciones
- `settings.*` - Todos los permisos de configuración

**Roles con Acceso:**

- **Super Admin**: Acceso total (método `before()`)
- **Admin**: Puede ver y editar configuraciones
- **Editor**: Puede ver y editar configuraciones
- **Viewer**: Solo puede ver configuraciones

---

## Modelo Setting

### Características Especiales

**Trait Translatable:**
- El modelo utiliza el trait `Translatable` para gestionar traducciones
- Campos traducibles: `description` (siempre), `value` (solo para tipo string)

**Accessors y Mutators:**
- `getValueAttribute()` - Convierte valor según tipo:
  - `integer`: Convierte a int
  - `boolean`: Convierte a boolean
  - `json`: Decodifica JSON a array
  - `string`: Retorna como string
- `setValueAttribute()` - Convierte valor al formato de almacenamiento:
  - `integer`: Convierte a string
  - `boolean`: Convierte a '1' o '0'
  - `json`: Codifica a JSON string
  - `string`: Retorna como string

**Cache Invalidation:**
- El modelo invalida automáticamente la caché cuando se actualiza
- Cache key: `setting.{key}`
- TTL: 24 horas

**Método Estático `get()`:**
- `Setting::get(string $key, $default = null)` - Obtener configuración con caché
- Manejo especial para `center_logo`: Convierte rutas de almacenamiento a URLs públicas

**Relaciones:**
- `updater()` - BelongsTo User (usuario que actualizó)
- `translations()` - MorphMany Translation (traducciones)

---

## Rutas

**Definidas en `routes/web.php`:**

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // ...
    Route::get('/configuracion', \App\Livewire\Admin\Settings\Index::class)
        ->name('settings.index');
    Route::get('/configuracion/{setting}/editar', \App\Livewire\Admin\Settings\Edit::class)
        ->name('settings.edit');
});
```

---

## Navegación

**Sidebar de Administración:**

El enlace a Configuración del Sistema se añadió en el grupo "Sistema" del sidebar:

```blade
@can('viewAny', \App\Models\Setting::class)
    <flux:navlist.item 
        icon="cog-6-tooth" 
        :href="route('admin.settings.index')" 
        :current="request()->routeIs('admin.settings.*')" 
        wire:navigate
    >
        {{ __('common.nav.settings') }}
    </flux:navlist.item>
@endcan
```

---

## Configuraciones Especiales

### center_logo

Configuración especial que permite subir una imagen del logo del centro mediante FilePond.

**Características:**
- Subida mediante componente FilePond
- Tipos permitidos: JPG, PNG, SVG, WebP
- Tamaño máximo: 5MB
- Almacenamiento en `storage/app/public/logos/`
- Conversión automática de ruta a URL pública
- Opción para eliminar logo existente
- Fallback a input de URL manual

**Uso en la Aplicación:**
- Se muestra en el sidebar del dashboard (componente `app-logo`)
- Se muestra en la navegación pública (componente `public-nav`)
- Reemplaza el logo por defecto "Laravel starter kit"

### center_name

Configuración que almacena el nombre del centro.

**Uso en la Aplicación:**
- Se muestra junto al logo en el sidebar del dashboard
- Se muestra junto al logo en la navegación pública
- Reemplaza el texto "Laravel starter kit"
- Permite texto de dos líneas (usando `line-clamp-2`)

---

## Traducciones

**Archivos de Traducción:**

- `lang/es/common.php` - Traducciones en español
- `lang/en/common.php` - Traducciones en inglés

**Claves Añadidas:**

```php
// Navegación
'common.nav.settings' => 'Configuración del Sistema'

// Títulos
'common.settings.title' => 'Configuración del Sistema'
'common.settings.edit' => 'Editar Configuración'

// Grupos
'common.settings.groups.general' => 'General'
'common.settings.groups.email' => 'Email'
'common.settings.groups.rgpd' => 'RGPD'
'common.settings.groups.media' => 'Media'
'common.settings.groups.seo' => 'SEO'

// Tipos
'common.settings.types.string' => 'Texto'
'common.settings.types.integer' => 'Número'
'common.settings.types.boolean' => 'Booleano'
'common.settings.types.json' => 'JSON'

// Mensajes
'common.settings.messages.updated' => 'Configuración actualizada correctamente'
'common.settings.messages.view_json_complete' => 'Ver JSON completo'
'common.settings.messages.view_full_value' => 'Ver valor completo'
```

---

## Tests

### Tests de Componentes Livewire

**IndexTest** (`tests/Feature/Livewire/Admin/Settings/IndexTest.php`):
- 24 tests, 52 assertions
- Autorización (3 tests)
- Listado y agrupación (2 tests)
- Búsqueda (3 tests)
- Filtros por grupo (2 tests)
- Ordenación (4 tests)
- Formateo de valores (4 tests)
- Reset de filtros (1 test)
- Grupos disponibles (1 test)
- Métodos helper (4 tests)

**EditTest** (`tests/Feature/Livewire/Admin/Settings/EditTest.php`):
- 29 tests, 77 assertions
- Autorización (3 tests)
- Carga de datos (4 tests)
- Actualización por tipo (4 tests para string, 2 para integer, 2 para boolean, 3 para JSON)
- Validación (3 tests)
- Traducciones (5 tests)
- Subida de logo (3 tests)
- Campos inmutables (2 tests)
- Validación en tiempo real (3 tests)

### Tests de Form Request

**UpdateSettingRequestTest** (`tests/Feature/Http/Requests/UpdateSettingRequestTest.php`):
- 14 tests
- Validación por tipo (string, integer, boolean, json)
- Mensajes personalizados

### Tests de Policy

**SettingPolicyTest** (`tests/Feature/Policies/SettingPolicyTest.php`):
- 15 tests, 19 assertions
- Acceso de super-admin (5 tests)
- ViewAny/View (4 tests)
- Create (1 test)
- Update (4 tests)
- Delete (1 test)

**Total: 82 tests, 176 assertions**

---

## Archivos Creados/Modificados

### Nuevos Archivos

**Componentes Livewire:**
- `app/Livewire/Admin/Settings/Index.php`
- `app/Livewire/Admin/Settings/Edit.php`
- `resources/views/livewire/admin/settings/index.blade.php`
- `resources/views/livewire/admin/settings/edit.blade.php`

**Form Request:**
- `app/Http/Requests/UpdateSettingRequest.php`

**Policy:**
- `app/Policies/SettingPolicy.php`

**Tests:**
- `tests/Feature/Livewire/Admin/Settings/IndexTest.php`
- `tests/Feature/Livewire/Admin/Settings/EditTest.php`
- `tests/Feature/Http/Requests/UpdateSettingRequestTest.php`
- `tests/Feature/Policies/SettingPolicyTest.php`

### Archivos Modificados

**Modelo:**
- `app/Models/Setting.php` - Añadido trait Translatable, método `get()`, invalidación de caché, manejo especial de `center_logo`

**Permisos:**
- `app/Support/Permissions.php` - Añadidos permisos `SETTINGS_VIEW`, `SETTINGS_EDIT`, `SETTINGS_ALL`

**Rutas:**
- `routes/web.php` - Añadidas rutas para Index y Edit

**Navegación:**
- `resources/views/components/layouts/app/sidebar.blade.php` - Añadido enlace a Configuración del Sistema
- `resources/views/components/layouts/app/header.blade.php` - Actualizado para usar `center_name` dinámico

**Componentes:**
- `resources/views/components/app-logo.blade.php` - Actualizado para usar `center_logo` y `center_name` dinámicos
- `resources/views/components/nav/public-nav.blade.php` - Actualizado para usar `center_logo` y `center_name` dinámicos

**Traducciones:**
- `lang/es/common.php` - Añadidas traducciones para Settings
- `lang/en/common.php` - Añadidas traducciones para Settings

**Seeder:**
- `database/seeders/SettingsSeeder.php` - Añadidos `center_name` y `center_logo`

**Helper:**
- `app/Support/helpers.php` - Añadida función `setting()` (opcional, se usa `Setting::get()` directamente)

**Autoload:**
- `composer.json` - Añadido `app/Support/helpers.php` a `autoload.files` (si se usa el helper)

---

## Uso del Sistema

### Obtener una Configuración

```php
// Usando el método estático (recomendado)
$siteName = \App\Models\Setting::get('site_name', 'Valor por defecto');

// Usando el helper (si está disponible)
$siteName = setting('site_name', 'Valor por defecto');
```

### Obtener Logo del Centro

```php
$logoUrl = \App\Models\Setting::get('center_logo');
// Retorna URL pública si es una ruta de almacenamiento
// Retorna URL completa si es una URL externa
// Retorna null si no está configurado
```

### Obtener Nombre del Centro

```php
$centerName = \App\Models\Setting::get('center_name', 'Erasmus+ Centro (Murcia)');
```

### En Vistas Blade

```blade
@php
    $centerLogo = \App\Models\Setting::get('center_logo');
    $centerName = \App\Models\Setting::get('center_name', 'Erasmus+ Centro (Murcia)');
@endphp

@if($centerLogo)
    <img src="{{ $centerLogo }}" alt="{{ $centerName }}" />
@endif
```

---

## Optimizaciones

### Caché

- Las configuraciones se cachean automáticamente con TTL de 24 horas
- La caché se invalida automáticamente cuando se actualiza una configuración
- Cache key: `setting.{key}`

### Consultas

- Eager loading de relaciones (`updater`, `translations_count`)
- Agrupación eficiente en memoria (no en base de datos)
- Consultas optimizadas con índices en `key` y `group`

---

## Mejoras Futuras

### Posibles Extensiones

1. **Historial de Cambios**: Registrar historial completo de cambios de configuraciones
2. **Validación Avanzada**: Validación personalizada por configuración (rangos, formatos específicos)
3. **Importar/Exportar**: Exportar e importar configuraciones en formato JSON
4. **Configuraciones por Entorno**: Diferentes valores según entorno (dev, staging, production)
5. **Configuraciones Sensibles**: Encriptar configuraciones sensibles (API keys, passwords)
6. **Validación de Dependencias**: Validar dependencias entre configuraciones
7. **Preview en Tiempo Real**: Preview de cambios antes de guardar (especialmente para JSON)

---

## Notas Técnicas

### Manejo de JSON

- Los valores JSON se almacenan como strings en la base de datos
- El accessor convierte automáticamente a array/objeto al acceder
- El mutator convierte automáticamente a JSON string al guardar
- La validación verifica sintaxis JSON antes de guardar

### Manejo de Boolean

- Los valores boolean se almacenan como '1' o '0' en la base de datos
- El accessor convierte automáticamente a boolean al acceder
- El mutator convierte automáticamente a '1'/'0' al guardar
- La validación acepta true/false, 1/0, '1'/'0', 'true'/'false'

### Manejo de center_logo

- Si el valor es una ruta de almacenamiento (`logos/xxx.jpg`), se convierte a URL pública
- Si el valor es una URL completa, se retorna tal cual
- Si el valor es null, se retorna null
- El método `Setting::get()` maneja esta conversión automáticamente

### Traducciones

- Solo las configuraciones de tipo `string` pueden tener traducciones del valor
- La descripción siempre es traducible
- Las traducciones se eliminan automáticamente si el valor está vacío
- Se utiliza el trait `Translatable` del modelo

---

**Fecha de Creación**: Diciembre 2025  
**Última Actualización**: Diciembre 2025  
**Estado**: ✅ Completado - 82 tests pasando (176 assertions)
