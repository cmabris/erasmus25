# CRUD de Programas en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Programas Erasmus+ en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Programas permite a los administradores gestionar completamente los programas Erasmus+ desde el panel de administración. Incluye funcionalidades avanzadas como gestión de imágenes, traducciones, ordenamiento, SoftDeletes y validación de relaciones antes de eliminación permanente.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar programas
- ✅ **SoftDeletes**: Los programas nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- ✅ **Gestión de Imágenes**: Subida, preview y eliminación de imágenes usando Laravel Media Library
- ✅ **Traducciones**: Gestión de traducciones de `name` y `description` en múltiples idiomas
- ✅ **Ordenamiento**: Cambio de orden de visualización mediante botones arriba/abajo
- ✅ **Búsqueda y Filtros**: Búsqueda por código, nombre o descripción, filtros por estado activo/inactivo y eliminados
- ✅ **Autorización**: Control de acceso mediante `ProgramPolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Programs\Index`
- **Vista**: `resources/views/livewire/admin/programs/index.blade.php`
- **Ruta**: `/admin/programas` (nombre: `admin.programs.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'activos')]
public string $showActiveOnly = '';

#[Url(as: 'eliminados')]
public string $showDeleted = '0';

#[Url(as: 'ordenar')]
public string $sortField = 'order';

#[Url(as: 'direccion')]
public string $sortDirection = 'asc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public ?int $programToDelete = null;
public bool $showRestoreModal = false;
public ?int $programToRestore = null;
public bool $showForceDeleteModal = false;
public ?int $programToForceDelete = null;
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de acceso.

#### `programs()` (Computed)
Retorna la lista paginada de programas con filtros aplicados.

**Filtros aplicados:**
- Por estado eliminado (`showDeleted`: '0' = no eliminados, '1' = solo eliminados)
- Por estado activo (`showActiveOnly`: '' = todos, '1' = activos, '0' = inactivos)
- Por búsqueda (código, nombre o descripción)

**Ordenación:**
- Por campo configurable (`sortField`) y dirección (`sortDirection`)
- Orden secundario por nombre ascendente

**Eager Loading:**
- `withCount(['calls', 'newsPosts'])` para mostrar contadores de relaciones

#### `sortBy(string $field)`
Cambia el campo de ordenación. Si es el mismo campo, alterna la dirección.

#### `toggleActive(int $programId)`
Activa o desactiva un programa. Requiere permiso `PROGRAMS_EDIT`.

#### `confirmDelete(int $programId)`
Abre el modal de confirmación para eliminar un programa.

#### `delete()`
Elimina un programa usando SoftDeletes. Verifica que no tenga relaciones antes de eliminar.

#### `confirmRestore(int $programId)`
Abre el modal de confirmación para restaurar un programa eliminado.

#### `restore()`
Restaura un programa eliminado. Requiere permiso `PROGRAMS_DELETE`.

#### `confirmForceDelete(int $programId)`
Abre el modal de confirmación para eliminar permanentemente un programa.

#### `forceDelete()`
Elimina permanentemente un programa. Solo disponible para `SUPER_ADMIN` y solo si no tiene relaciones.

#### `moveUp(int $programId)` / `moveDown(int $programId)`
Cambia el orden de visualización del programa intercambiando valores del campo `order`.

#### `canMoveUp(int $programId)` / `canMoveDown(int $programId)` (Computed)
Verifica si el programa puede moverse arriba/abajo en la lista.

#### `canDeleteProgram(Program $program)`
Verifica si el programa puede ser eliminado (no tiene relaciones activas).

**Vista:**

La vista incluye:
- Header con título y botón de crear
- Breadcrumbs de navegación
- Búsqueda con componente `x-ui.search-input`
- Filtros (activos/inactivos, mostrar eliminados)
- Tabla responsive con columnas:
  - Orden (con botones arriba/abajo)
  - Código
  - Nombre
  - Estado (activo/inactivo)
  - Imagen (thumbnail si existe)
  - Relaciones (convocatorias, noticias)
  - Fechas (creación, actualización)
  - Acciones (ver, editar, activar/desactivar, eliminar/restaurar)
- Paginación
- Estado vacío con `x-ui.empty-state`
- Modales de confirmación (eliminar, restaurar, eliminar permanentemente)

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Programs\Create`
- **Vista**: `resources/views/livewire/admin/programs/create.blade.php`
- **Ruta**: `/admin/programas/crear` (nombre: `admin.programs.create`)

**Propiedades Públicas:**

```php
public string $code = '';
public string $name = '';
public string $slug = '';
public string $description = '';
public bool $is_active = true;
public int $order = 0;
public ?UploadedFile $image = null;
public ?string $imagePreview = null;
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de creación.

#### `updatedName()`
Genera automáticamente el slug cuando cambia el nombre (si el slug está vacío).

#### `updatedImage()`
Valida y muestra preview de la imagen seleccionada.

#### `removeImage()`
Elimina la imagen seleccionada y limpia el preview.

#### `store()`
Crea un nuevo programa usando `StoreProgramRequest` para validación.

**Validación:**
- `code`: requerido, único, máximo 255 caracteres
- `name`: requerido, máximo 255 caracteres
- `slug`: opcional, único, máximo 255 caracteres
- `description`: opcional
- `is_active`: booleano
- `order`: entero
- `image`: opcional, imagen, máximo 5MB, formatos: jpeg, png, webp, gif

**Proceso:**
1. Valida datos con `StoreProgramRequest`
2. Crea el programa
3. Si hay imagen, la sube usando Laravel Media Library
4. Redirige a la vista de detalle con notificación de éxito

**Vista:**

La vista incluye:
- Header con título y botones de navegación
- Breadcrumbs
- Formulario en 2 columnas:
  - **Columna principal:**
    - Campos: código, nombre, slug, descripción
    - Subida de imagen con preview
  - **Sidebar:**
    - Configuración (orden, activo/inactivo)
    - Acciones (guardar, cancelar)
- Validación en tiempo real con `wire:model.live`
- Estados de carga con `wire:loading`

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Programs\Edit`
- **Vista**: `resources/views/livewire/admin/programs/edit.blade.php`
- **Ruta**: `/admin/programas/{program}/editar` (nombre: `admin.programs.edit`)

**Propiedades Públicas:**

```php
public Program $program;
public string $code = '';
public string $name = '';
public string $slug = '';
public string $description = '';
public bool $is_active = true;
public int $order = 0;
public ?UploadedFile $image = null;
public ?string $imagePreview = null;
public bool $removeExistingImage = false;

// Traducciones
public array $translations = [];
public ?string $selectedLanguage = null;
```

**Métodos Principales:**

#### `mount(Program $program)`
Inicializa el componente, carga los datos del programa y verifica permisos.

#### `loadTranslations()`
Carga las traducciones existentes para todos los idiomas activos.

#### `updatedName()`
Genera automáticamente el slug cuando cambia el nombre (si el slug está vacío o coincide con el slug original).

#### `updatedImage()`
Valida y muestra preview de la nueva imagen seleccionada.

#### `removeImage()`
Elimina la nueva imagen seleccionada y limpia el preview.

#### `toggleRemoveExistingImage()`
Alterna la opción de eliminar la imagen existente.

#### `getCurrentImageUrl()`
Retorna la URL de la imagen actual del programa.

#### `hasExistingImage()`
Verifica si el programa tiene una imagen asociada.

#### `update()`
Actualiza el programa con validación directa (no usa FormRequest para mejor manejo de `Rule::unique()->ignore()`).

**Validación:**
- Similar a `store()` pero con `Rule::unique()->ignore($this->program->id)` para código y slug

**Proceso:**
1. Valida datos
2. Actualiza el programa
3. Si `removeExistingImage` está activo, elimina la imagen existente
4. Si hay nueva imagen, elimina la existente y sube la nueva
5. Guarda traducciones con `saveTranslations()`
6. Redirige a la vista de detalle con notificación de éxito

#### `saveTranslations()`
Guarda las traducciones para todos los idiomas. Si un campo está vacío, elimina la traducción.

#### `getAvailableLanguagesProperty()`
Retorna la lista de idiomas activos disponibles.

**Vista:**

La vista incluye:
- Similar a Create pero con:
  - Datos precargados en los campos
  - Imagen actual mostrada con opción de eliminar
  - Sección de traducciones con formularios para cada idioma activo
  - Campos traducibles: nombre y descripción

---

### 4. Show (Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Programs\Show`
- **Vista**: `resources/views/livewire/admin/programs/show.blade.php`
- **Ruta**: `/admin/programas/{program}` (nombre: `admin.programs.show`)

**Propiedades Públicas:**

```php
public Program $program;
public bool $showDeleteModal = false;
public bool $showRestoreModal = false;
public bool $showForceDeleteModal = false;
```

**Métodos Principales:**

#### `mount(Program $program)`
Inicializa el componente, carga relaciones y verifica permisos.

**Eager Loading:**
- `calls`: últimas 5 convocatorias relacionadas
- `newsPosts`: últimas 5 noticias relacionadas

#### `imageUrl()` (Computed)
Retorna la URL de la imagen del programa.

#### `getImageUrl(string $conversion = 'large')`
Retorna la URL de la imagen con conversión específica (thumbnail, medium, large).

#### `hasImage()` (Computed)
Verifica si el programa tiene imagen asociada.

#### `statistics()` (Computed)
Retorna estadísticas del programa:
- Total de convocatorias
- Convocatorias abiertas (`status = 'abierta'`)
- Total de noticias
- Noticias publicadas (`status = 'publicado'`)

#### `hasRelationships()` (Computed)
Verifica si el programa tiene relaciones activas (convocatorias o noticias).

#### `canDelete()` (Computed)
Verifica si el programa puede ser eliminado (permisos y sin relaciones).

#### `toggleActive()`
Activa o desactiva el programa.

#### `delete()`
Elimina el programa usando SoftDeletes. Verifica relaciones antes de eliminar.

#### `restore()`
Restaura un programa eliminado.

#### `forceDelete()`
Elimina permanentemente un programa. Solo para `SUPER_ADMIN` y sin relaciones.

#### `availableTranslations()` (Computed)
Retorna las traducciones disponibles del programa para todos los idiomas activos.

**Vista:**

La vista incluye:
- Header con nombre, código, slug y badge de estado
- Breadcrumbs
- Layout en 2 columnas:
  - **Columna principal:**
    - Imagen del programa (si existe)
    - Descripción
    - Estadísticas (convocatorias, noticias)
    - Traducciones disponibles (si existen)
  - **Sidebar:**
    - Información del programa (fechas, estado)
    - Acciones (editar, activar/desactivar, eliminar/restaurar)
- Modales de confirmación

---

## Modelo Program

### Modificaciones Realizadas

**Trait Translatable:**
```php
use App\Models\Concerns\Translatable;

class Program extends Model implements HasMedia
{
    use Translatable;
    // ...
}
```

**Trait SoftDeletes:**
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;
    // ...
}
```

**Interfaz HasMedia:**
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Program extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }
    
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(300)->height(300)->sharpen(10);
        
        $this->addMediaConversion('medium')
            ->width(800)->height(600)->sharpen(10);
        
        $this->addMediaConversion('large')
            ->width(1200)->height(900)->sharpen(10);
    }
}
```

**Campos Traducibles:**
- `name`: Nombre del programa
- `description`: Descripción del programa

---

## Política de Autorización (ProgramPolicy)

### Métodos Implementados

#### `before(User $user, string $ability)`
Concede acceso total a usuarios con rol `SUPER_ADMIN`.

#### `viewAny(User $user)`
Verifica permiso `PROGRAMS_VIEW`.

#### `view(User $user, Program $program)`
Verifica permiso `PROGRAMS_VIEW`.

#### `create(User $user)`
Verifica permiso `PROGRAMS_CREATE`.

#### `update(User $user, Program $program)`
Verifica permiso `PROGRAMS_EDIT`.

#### `delete(User $user, Program $program)`
Verifica permiso `PROGRAMS_DELETE` y que el programa no tenga relaciones activas.

#### `restore(User $user, Program $program)`
Verifica permiso `PROGRAMS_DELETE`.

#### `forceDelete(User $user, Program $program)`
Solo disponible para `SUPER_ADMIN` y solo si el programa no tiene relaciones activas.

---

## Form Requests

### StoreProgramRequest

**Ubicación:** `app/Http/Requests/StoreProgramRequest.php`

**Reglas de Validación:**
```php
'code' => ['required', 'string', 'max:255', 'unique:programs,code'],
'name' => ['required', 'string', 'max:255'],
'slug' => ['nullable', 'string', 'max:255', 'unique:programs,slug'],
'description' => ['nullable', 'string'],
'is_active' => ['nullable', 'boolean'],
'order' => ['nullable', 'integer'],
'image' => ['nullable', 'image', 'mimes:jpeg,png,webp,gif', 'max:5120'],
```

**Autorización:**
- Verifica permiso `PROGRAMS_CREATE` mediante `ProgramPolicy`.

### UpdateProgramRequest

**Ubicación:** `app/Http/Requests/UpdateProgramRequest.php`

**Reglas de Validación:**
Similar a `StoreProgramRequest` pero con `Rule::unique()->ignore($program->id)` para código y slug.

**Autorización:**
- Verifica permiso `PROGRAMS_EDIT` mediante `ProgramPolicy`.

---

## Rutas

**Ubicación:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/programas', Index::class)->name('programs.index');
    Route::get('/programas/crear', Create::class)->name('programs.create');
    Route::get('/programas/{program}', Show::class)->name('programs.show');
    Route::get('/programas/{program}/editar', Edit::class)->name('programs.edit');
});
```

**Middleware:**
- `auth`: Requiere autenticación
- `verified`: Requiere verificación de email

---

## Migraciones

### SoftDeletes

**Archivo:** `database/migrations/2025_12_27_185903_add_soft_deletes_to_programs_table.php`

```php
Schema::table('programs', function (Blueprint $table) {
    $table->softDeletes();
});
```

Añade la columna `deleted_at` para SoftDeletes.

---

## Traducciones

### Archivos Modificados

- `lang/es/common.php`
- `lang/en/common.php`

### Nuevas Claves Añadidas

```php
'actions' => [
    // ... existentes
    'move_up' => 'Mover arriba',
    'move_down' => 'Mover abajo',
    'view_program_details' => 'Ver detalles del programa',
    'edit_program' => 'Editar programa',
    'deactivate_program' => 'Desactivar programa',
    'activate_program' => 'Activar programa',
    'delete_program' => 'Eliminar programa',
    'restore_program' => 'Restaurar programa eliminado',
    'permanently_delete_program' => 'Eliminar permanentemente del sistema',
    'translations' => 'Traducciones',
    'translated_name' => 'Nombre traducido',
    'translated_description' => 'Descripción traducida',
    'available_translations' => 'Traducciones Disponibles',
    'current_language' => 'Idioma actual',
],

'messages' => [
    // ... existentes
    'confirm_delete_program' => '¿Estás seguro de que deseas eliminar este programa?',
    'confirm_restore_program' => '¿Estás seguro de que deseas restaurar este programa?',
    'confirm_force_delete_program' => '¿Estás seguro de que deseas eliminar permanentemente este programa?',
    'soft_delete_explanation' => 'Esta acción marcará el programa como eliminado, pero no se eliminará permanentemente. Podrás restaurarlo más tarde.',
    'order_updated_successfully' => 'Orden actualizado correctamente',
],

'errors' => [
    'cannot_delete_with_relations' => 'No se puede eliminar este programa porque tiene relaciones activas (convocatorias o noticias).',
],
```

---

## Tests

### Archivos de Test

- `tests/Feature/Livewire/Admin/Programs/IndexTest.php`
- `tests/Feature/Livewire/Admin/Programs/CreateTest.php`
- `tests/Feature/Livewire/Admin/Programs/EditTest.php`
- `tests/Feature/Livewire/Admin/Programs/ShowTest.php`
- `tests/Feature/Policies/ProgramPolicyTest.php` (actualizado)

### Cobertura de Tests

**IndexTest:**
- Autorización (acceso denegado, acceso permitido)
- Listado de programas
- Búsqueda por código, nombre, descripción
- Ordenación por diferentes campos
- Filtros (activos/inactivos, eliminados)
- Paginación
- Toggle activo/inactivo
- Eliminación con SoftDeletes
- Restauración
- Eliminación permanente (solo super-admin)
- Ordenamiento (mover arriba/abajo)

**CreateTest:**
- Autorización
- Creación exitosa
- Validación de campos requeridos
- Validación de unicidad
- Subida de imagen
- Generación automática de slug
- Valores por defecto

**EditTest:**
- Autorización
- Carga de datos existentes
- Actualización exitosa
- Validación (incluyendo unique con ignore)
- Gestión de imagen (subir, eliminar, reemplazar)
- Generación automática de slug
- Guardado de traducciones

**ShowTest:**
- Autorización
- Visualización de información
- Visualización de imagen
- Estadísticas
- Toggle activo/inactivo
- Eliminación con SoftDeletes
- Restauración
- Eliminación permanente
- Verificación de relaciones

**ProgramPolicyTest:**
- Verificación de permisos por rol
- Verificación de SoftDeletes
- Verificación de forceDelete (solo super-admin)
- Verificación de relaciones antes de eliminar

**Total:** 837 tests pasando (1955 assertions)

---

## Navegación

### Sidebar

**Ubicación:** `resources/views/components/layouts/app/sidebar.blade.php`

Añadido enlace a Programas en la sección "Contenido":

```blade
@can('viewAny', App\Models\Program::class)
    <x-ui.sidebar.item
        href="{{ route('admin.programs.index') }}"
        :active="request()->routeIs('admin.programs.*')"
        icon="academic-cap"
    >
        {{ __('common.nav.programs') }}
    </x-ui.sidebar.item>
@endcan
```

---

## Funcionalidades Avanzadas

### SoftDeletes

Los programas nunca se eliminan permanentemente por defecto. Al eliminar un programa:
1. Se marca como eliminado (`deleted_at` se establece)
2. No aparece en listados normales
3. Puede restaurarse desde la vista de eliminados
4. Solo `SUPER_ADMIN` puede hacer `forceDelete`
5. `forceDelete` solo está permitido si no hay relaciones activas

### Gestión de Imágenes

- **Subida:** Formatos JPEG, PNG, WebP, GIF, máximo 5MB
- **Conversiones:** Thumbnail (300x300), Medium (800x600), Large (1200x900)
- **Preview:** Vista previa antes de guardar
- **Eliminación:** Opción de eliminar imagen existente sin subir nueva
- **Reemplazo:** Al subir nueva imagen, se elimina automáticamente la anterior

### Traducciones

- **Campos traducibles:** `name`, `description`
- **Idiomas:** Todos los idiomas activos en el sistema
- **Fallback:** Si no hay traducción, se usa el valor por defecto
- **Gestión:** Formularios en la vista de edición para cada idioma
- **Visualización:** Sección en la vista de detalle mostrando traducciones disponibles

### Ordenamiento

- **Campo:** `order` (entero)
- **Método:** Botones arriba/abajo que intercambian valores de `order`
- **Validación:** Solo se puede mover si hay permisos de edición
- **Visualización:** Columna de orden en el listado con botones

---

## Optimizaciones

### Eager Loading

- `withCount(['calls', 'newsPosts'])` en Index para evitar N+1 queries
- Carga de relaciones limitadas en Show (últimas 5 convocatorias y noticias)

### Consultas Optimizadas

- Filtros aplicados a nivel de base de datos
- Ordenación con índices apropiados
- Paginación para listados grandes

---

## Guía de Uso

### Crear un Programa

1. Navegar a `/admin/programas`
2. Click en "Crear Programa"
3. Completar formulario:
   - Código (requerido, único)
   - Nombre (requerido, genera slug automáticamente)
   - Descripción (opcional)
   - Orden (opcional, para ordenamiento)
   - Estado activo (por defecto: activo)
   - Imagen (opcional)
4. Click en "Guardar"

### Editar un Programa

1. Navegar a `/admin/programas`
2. Click en el botón "Editar" del programa deseado
3. Modificar campos necesarios
4. Gestionar traducciones (opcional)
5. Gestionar imagen (subir nueva, eliminar existente)
6. Click en "Guardar"

### Eliminar un Programa

1. Navegar a `/admin/programas`
2. Click en el botón "Eliminar" del programa deseado
3. Confirmar en el modal
4. El programa se marca como eliminado (SoftDelete)

**Nota:** Si el programa tiene relaciones activas (convocatorias o noticias), no se puede eliminar.

### Restaurar un Programa Eliminado

1. Navegar a `/admin/programas`
2. Activar filtro "Mostrar eliminados"
3. Click en el botón "Restaurar" del programa deseado
4. Confirmar en el modal

### Eliminar Permanentemente un Programa

**Solo disponible para SUPER_ADMIN y solo si no tiene relaciones:**

1. Navegar a `/admin/programas`
2. Activar filtro "Mostrar eliminados"
3. Click en el botón "Eliminar permanentemente"
4. Confirmar en el modal

### Cambiar Orden de Programas

1. Navegar a `/admin/programas`
2. Usar botones de flecha arriba/abajo en la columna "Orden"
3. El orden se actualiza automáticamente

### Gestionar Traducciones

1. Editar un programa
2. Desplazarse a la sección "Traducciones"
3. Para cada idioma:
   - Introducir nombre traducido (opcional)
   - Introducir descripción traducida (opcional)
4. Si un campo está vacío, se usa el valor por defecto
5. Guardar cambios

---

## Notas Técnicas

### Trait Translatable

El modelo `Program` utiliza el trait `Translatable` que proporciona métodos para gestionar traducciones:

- `translate($field, $locale)`: Obtiene traducción de un campo
- `setTranslation($field, $locale, $value)`: Establece traducción
- `hasTranslation($field, $locale)`: Verifica si existe traducción
- `deleteTranslation($field, $locale)`: Elimina traducción específica

**Nota:** Se corrigió un conflicto de nombres en el trait renombrando el método `translations($locale)` a `getTranslationsForLocale($locale)`.

### Validación de Relaciones

Antes de permitir `delete` o `forceDelete`, se verifica que el programa no tenga:
- Convocatorias relacionadas (`calls()->exists()`)
- Noticias relacionadas (`newsPosts()->exists()`)

Si tiene relaciones, se muestra un mensaje de error y se deshabilita el botón de eliminar.

### URLs con Parámetros

Las propiedades públicas del componente Index utilizan el atributo `#[Url]` para mantener el estado en la URL:

- `search` → `?q=`
- `showActiveOnly` → `?activos=`
- `showDeleted` → `?eliminados=`
- `sortField` → `?ordenar=`
- `sortDirection` → `?direccion=`
- `perPage` → `?por-pagina=`

Esto permite compartir URLs con filtros aplicados y mejora la UX.

---

## Mejoras Futuras

Posibles mejoras para futuras versiones:

- [ ] Drag & drop para ordenamiento visual
- [ ] Búsqueda avanzada con múltiples criterios
- [ ] Exportación a Excel/CSV
- [ ] Historial de cambios (auditoría)
- [ ] Vista previa de cómo se verá en el área pública
- [ ] Duplicar programa existente
- [ ] Gestión masiva (activar/desactivar múltiples programas)

---

**Fecha de Documentación:** Diciembre 2025  
**Versión:** 1.0  
**Estado:** ✅ Completado y testeado

