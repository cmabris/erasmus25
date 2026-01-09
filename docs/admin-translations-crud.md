# CRUD de Traducciones en Panel de Administración

Documentación técnica del sistema completo de gestión (CRUD) de Traducciones en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## Descripción General

El CRUD de Traducciones permite a los administradores gestionar todas las traducciones dinámicas del sistema desde el panel de administración. Incluye funcionalidades avanzadas como gestión de traducciones polimórficas (Program, Setting), filtros avanzados, búsqueda en tiempo real, validación de unicidad, y optimizaciones de rendimiento con caché e índices de base de datos.

## Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar traducciones
- ✅ **Traducciones Polimórficas**: Soporte para múltiples modelos traducibles (Program, Setting)
- ✅ **Filtros Avanzados**: Por modelo traducible, idioma, registro específico
- ✅ **Búsqueda en Tiempo Real**: Búsqueda por campo o valor con debounce
- ✅ **Validación de Unicidad**: Prevención de duplicados en tiempo real
- ✅ **Gestión de Soft Deletes**: Visualización de registros eliminados asociados
- ✅ **Optimizaciones**: Caché de listados, índices de BD, eager loading
- ✅ **Autorización**: Control de acceso mediante `TranslationPolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: 66 tests pasando (152 assertions)

---

## Componentes Livewire

### 1. Index (Listado)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Translations\Index`
- **Vista**: `resources/views/livewire/admin/translations/index.blade.php`
- **Ruta**: `/admin/traducciones` (nombre: `admin.translations.index`)

**Propiedades Públicas:**

```php
#[Url(as: 'q')]
public string $search = '';

#[Url(as: 'modelo')]
public ?string $filterModel = null;

#[Url(as: 'idioma')]
public ?int $filterLanguageId = null;

#[Url(as: 'registro')]
public ?int $filterTranslatableId = null;

#[Url(as: 'ordenar')]
public string $sortField = 'created_at';

#[Url(as: 'direccion')]
public string $sortDirection = 'desc';

#[Url(as: 'por-pagina')]
public int $perPage = 15;

public bool $showDeleteModal = false;
public ?int $translationToDelete = null;
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de acceso mediante `TranslationPolicy::viewAny()`.

#### `translations()` (Computed)
Retorna la lista paginada de traducciones con filtros aplicados.

**Filtros aplicados:**
- Por búsqueda (`search`): campo o valor (LIKE)
- Por modelo traducible (`filterModel`): Program o Setting
- Por idioma (`filterLanguageId`): ID de idioma
- Por registro específico (`filterTranslatableId`): ID del registro traducible

**Ordenación:**
- Por campo configurable (`sortField`) y dirección (`sortDirection`)
- Orden secundario por `created_at` descendente

**Eager Loading:**
- `with(['language'])` para evitar N+1 queries

#### `sortBy(string $field)`
Cambia el campo de ordenación. Si es el mismo campo, alterna la dirección.

#### `confirmDelete(int $translationId)`
Abre el modal de confirmación para eliminar una traducción.

#### `delete()`
Elimina una traducción. Requiere permiso `TRANSLATIONS_DELETE`.

#### `getAvailableModels()`
Retorna array de modelos traducibles disponibles con sus etiquetas.

#### `getLanguages()` (Computed)
Retorna idiomas activos con caché de 1 hora.

#### `getTranslatableOptions()`
Retorna opciones de modelos traducibles según filtro seleccionado, con caché de 30 minutos.

#### `getTranslatableDisplayName(Translation $translation)`
Obtiene el nombre para mostrar del modelo traducible asociado.

#### `getTranslatableModel(Translation $translation)`
Obtiene el modelo traducible asociado, manejando SoftDeletes si aplica. Usa caché interno para evitar N+1 queries.

#### `isTranslatableDeleted(Translation $translation)`
Verifica si el modelo traducible asociado está eliminado (soft delete).

#### `getTranslatableTooltip(Translation $translation)`
Genera texto de tooltip con información detallada del modelo traducible.

#### `getModelTypeDisplayName(string $type)`
Obtiene el nombre para mostrar del tipo de modelo.

**Características:**

- Tabla responsive con columnas: Tipo de Modelo, Registro, Campo, Idioma, Valor, Fecha de Creación, Acciones
- Búsqueda en tiempo real con debounce (300ms) por campo o valor
- Filtros con dropdowns dinámicos:
  - Modelo traducible (Program, Setting)
  - Idioma (solo activos)
  - Registro específico (según modelo seleccionado)
- Ordenación por campo con indicadores visuales
- Visualización de registros eliminados:
  - Badge "Eliminado" en rojo
  - Texto en cursiva y gris
  - Tooltip con información detallada
- Tooltips informativos con detalles del modelo traducible
- Estados de carga y vacío
- Paginación configurable (15 por defecto)
- Botones de acción: Ver, Editar, Eliminar

---

### 2. Create (Crear)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Translations\Create`
- **Vista**: `resources/views/livewire/admin/translations/create.blade.php`
- **Ruta**: `/admin/traducciones/crear` (nombre: `admin.translations.create`)

**Propiedades Públicas:**

```php
public ?string $translatableType = null;
public ?int $translatableId = null;
public ?int $languageId = null;
public string $field = '';
public string $value = '';
```

**Métodos Principales:**

#### `mount()`
Inicializa el componente y verifica permisos de acceso mediante `TranslationPolicy::create()`.

#### `getAvailableModels()`
Retorna array de modelos traducibles disponibles.

#### `getLanguages()` (Computed)
Retorna idiomas activos con caché de 1 hora.

#### `getTranslatableOptions()` (Computed)
Retorna opciones de modelos traducibles según tipo seleccionado, con caché de 30 minutos.

#### `getAvailableFields()`
Retorna campos disponibles según tipo de modelo seleccionado.

#### `updatedTranslatableType()`
Resetea `translatableId` y `field` cuando cambia el tipo de modelo.

#### `updatedTranslatableId()`
Resetea validación de `value` cuando cambia el registro traducible.

#### `updatedLanguageId()`
Resetea validación de `value` cuando cambia el idioma.

#### `updatedField()`
Resetea validación de `value` cuando cambia el campo.

#### `translationExists(): bool`
Verifica si ya existe una traducción con la combinación actual (translatable_type, translatable_id, language_id, field).

#### `getExistingTranslation(): ?Translation`
Obtiene la traducción existente si `translationExists()` es true.

#### `store()`
Crea una nueva traducción:
- Valida datos mediante `StoreTranslationRequest`
- Verifica unicidad antes de guardar
- Muestra error si ya existe
- Guarda la traducción
- Redirige a index con mensaje de éxito

**Características:**

- Formulario con selectores dinámicos:
  - Tipo de modelo (Program, Setting)
  - Registro traducible (según tipo seleccionado)
  - Idioma (solo activos)
  - Campo (según tipo de modelo)
  - Valor (textarea)
- Validación en tiempo real:
  - Selectores con `wire:model.live.debounce.300ms`
  - Verificación de unicidad antes de guardar
  - Feedback visual con callout de advertencia
- Prevención de duplicados:
  - Callout de advertencia si ya existe traducción
  - Enlace para editar traducción existente
  - Deshabilitación de formulario si hay duplicado
- Breadcrumbs de navegación
- Botón de cancelar que redirige a index

---

### 3. Edit (Editar)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Translations\Edit`
- **Vista**: `resources/views/livewire/admin/translations/edit.blade.php`
- **Ruta**: `/admin/traducciones/{translation}/editar` (nombre: `admin.translations.edit`)

**Propiedades Públicas:**

```php
public Translation $translation;
public string $value = '';
```

**Métodos Principales:**

#### `mount(Translation $translation)`
Inicializa el componente con la traducción a editar y verifica permisos mediante `TranslationPolicy::update()`.

#### `update()`
Actualiza la traducción:
- Valida datos mediante `UpdateTranslationRequest`
- Verifica unicidad (excluyendo el registro actual)
- Actualiza el valor
- Redirige a show con mensaje de éxito

**Características:**

- Formulario simple con solo campo `value` (textarea)
- Campos de solo lectura: Tipo de Modelo, Registro, Campo, Idioma
- Validación de unicidad (excluyendo el registro actual)
- Breadcrumbs de navegación
- Botones: Guardar, Ver, Cancelar

---

### 4. Show (Ver Detalle)

**Ubicación:**
- **Clase**: `App\Livewire\Admin\Translations\Show`
- **Vista**: `resources/views/livewire/admin/translations/show.blade.php`
- **Ruta**: `/admin/traducciones/{translation}` (nombre: `admin.translations.show`)

**Propiedades Públicas:**

```php
public Translation $translation;
public bool $showDeleteModal = false;
```

**Métodos Principales:**

#### `mount(Translation $translation)`
Inicializa el componente y carga la relación `language`.

#### `getModelTypeDisplayName(string $type)`
Obtiene el nombre para mostrar del tipo de modelo.

#### `getTranslatableModel(Translation $translation)`
Obtiene el modelo traducible asociado, manejando SoftDeletes si aplica.

#### `getTranslatableDisplayName(Translation $translation)`
Obtiene el nombre para mostrar del modelo traducible.

#### `isTranslatableDeleted(Translation $translation)`
Verifica si el modelo traducible está eliminado.

#### `getTranslatableRoute()`
Obtiene la ruta para ver el detalle del modelo traducible asociado.

#### `confirmDelete()`
Abre el modal de confirmación para eliminar.

#### `delete()`
Elimina la traducción. Requiere permiso `TRANSLATIONS_DELETE`.

**Características:**

- Vista de detalle completa con:
  - Valor completo (pre-formateado)
  - Tipo de modelo con badge
  - Registro traducible con enlace (si no está eliminado)
  - Badge "Eliminado" si el registro está soft-deleted
  - Campo traducido
  - Idioma (nombre y código)
  - Fechas de creación y actualización
- Breadcrumbs de navegación
- Botones de acción: Editar, Eliminar, Volver
- Modal de confirmación para eliminación

---

## Form Requests

### StoreTranslationRequest

**Ubicación:**
- **Clase**: `App\Http\Requests\StoreTranslationRequest`
- **Ruta**: Usado internamente por componente Livewire Create

**Reglas de Validación:**

```php
'translatable_type' => ['required', 'string', Rule::in([Program::class, Setting::class])]
'translatable_id' => ['required', 'integer', function ($attribute, $value, $fail) {
    // Validación dinámica según translatable_type
}]
'language_id' => ['required', 'integer', 'exists:languages,id']
'field' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
    // Validación dinámica según translatable_type
}]
'value' => ['required', 'string']
// Unicidad: combinación única de (translatable_type, translatable_id, language_id, field)
```

**Características:**

- Autorización mediante `TranslationPolicy::create()`
- Validación dinámica de `translatable_id` según tipo de modelo
- Validación dinámica de `field` según tipo de modelo
- Validación de unicidad de la combinación completa
- Mensajes de error personalizados en español e inglés

---

### UpdateTranslationRequest

**Ubicación:**
- **Clase**: `App\Http\Requests\UpdateTranslationRequest`
- **Ruta**: Usado internamente por componente Livewire Edit

**Reglas de Validación:**

```php
'value' => ['required', 'string']
// Unicidad: combinación única excluyendo el registro actual
```

**Características:**

- Autorización mediante `TranslationPolicy::update()`
- Validación de unicidad excluyendo el registro actual
- Mensajes de error personalizados en español e inglés

---

## Policy

### TranslationPolicy

**Ubicación:**
- **Clase**: `app/Policies/TranslationPolicy.php`
- **Registro**: Automático mediante convención de nombres

**Métodos de Autorización:**

| Método | Permiso Requerido | Descripción |
|--------|-------------------|-------------|
| `before()` | - | Super-admin tiene acceso total |
| `viewAny()` | `TRANSLATIONS_VIEW` | Ver listado de traducciones |
| `view()` | `TRANSLATIONS_VIEW` | Ver detalle de traducción |
| `create()` | `TRANSLATIONS_CREATE` | Crear traducción |
| `update()` | `TRANSLATIONS_EDIT` | Actualizar traducción |
| `delete()` | `TRANSLATIONS_DELETE` | Eliminar traducción |

**Permisos del Módulo:**
- `translations.view` - Ver traducciones
- `translations.create` - Crear traducciones
- `translations.edit` - Editar traducciones
- `translations.delete` - Eliminar traducciones
- `translations.all` - Todos los permisos de traducciones

**Lógica de Autorización:**
- Super-admin: Acceso total mediante `before()`
- Admin: Acceso completo a todas las operaciones
- Editor: Solo lectura (view, viewAny)
- Viewer: Solo lectura (view, viewAny)
- Sin rol: Sin acceso

---

## Modelo

### Translation

**Ubicación:**
- **Clase**: `app/Models/Translation.php`

**Relaciones:**

| Relación | Tipo | Modelo Relacionado |
|----------|------|-------------------|
| `language()` | BelongsTo | `Language` |
| `translatable()` | MorphTo | `Program`, `Setting` (polimórfica) |

**Atributos Fillable:**

```php
protected $fillable = [
    'translatable_type',
    'translatable_id',
    'language_id',
    'field',
    'value',
];
```

**Eventos del Modelo:**

El modelo incluye eventos en `booted()` para limpiar caché automáticamente:
- `saved`: Limpia cachés de traducciones
- `deleted`: Limpia cachés de traducciones

**Cachés Limpiados:**
- `translations.active_languages`
- `translations.active_programs`
- `translations.all_settings`

---

## Base de Datos

### Tabla `translations`

**Estructura:**

```sql
CREATE TABLE translations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    translatable_type VARCHAR(255) NOT NULL,
    translatable_id BIGINT UNSIGNED NOT NULL,
    language_id BIGINT UNSIGNED NOT NULL,
    field VARCHAR(255) NOT NULL,
    value TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY translation_unique (
        translatable_type, 
        translatable_id, 
        language_id, 
        field
    ),
    INDEX translations_translatable_type_translatable_id_index (
        translatable_type, 
        translatable_id
    ),
    INDEX translations_language_id_index (language_id),
    INDEX translations_field_index (field),
    INDEX translations_created_at_index (created_at),
    INDEX translations_type_language_index (
        translatable_type, 
        language_id
    ),
    INDEX translations_created_type_index (
        created_at, 
        translatable_type
    ),
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE
);
```

**Índices Optimizados:**

1. **Índice único**: `translation_unique` - Previene duplicados
2. **Índice compuesto**: `translations_translatable_type_translatable_id_index` - Optimiza consultas por modelo traducible
3. **Índice simple**: `translations_language_id_index` - Optimiza filtros por idioma
4. **Índice simple**: `translations_field_index` - Optimiza filtros y ordenación por campo
5. **Índice simple**: `translations_created_at_index` - Optimiza ordenación por fecha
6. **Índice compuesto**: `translations_type_language_index` - Optimiza consultas combinadas
7. **Índice compuesto**: `translations_created_type_index` - Optimiza ordenación con filtro de tipo

---

## Optimizaciones de Rendimiento

### Caché

**Caché de Idiomas Activos:**
- **Clave**: `translations.active_languages`
- **TTL**: 1 hora
- **Ubicación**: `Index::getLanguages()`, `Create::getLanguages()`
- **Limpieza**: Automática cuando se crean/actualizan/eliminan traducciones

**Caché de Modelos Traducibles:**
- **Clave**: `translations.active_programs` (para Program)
- **Clave**: `translations.all_settings` (para Setting)
- **TTL**: 30 minutos
- **Ubicación**: `Index::getTranslatableOptions()`, `Create::getTranslatableOptions()`
- **Limpieza**: Automática cuando se crean/actualizan/eliminan traducciones

### Eager Loading

- `with(['language'])` en consulta principal de Index
- Caché interno de modelos traducibles para evitar N+1 queries

### Búsqueda con Debounce

- `wire:model.live.debounce.300ms` en campo de búsqueda
- Reduce consultas durante la escritura

---

## Rutas

**Ubicación**: `routes/web.php`

```php
// Rutas de Traducciones
Route::get('/traducciones', \App\Livewire\Admin\Translations\Index::class)
    ->name('translations.index');
Route::get('/traducciones/crear', \App\Livewire\Admin\Translations\Create::class)
    ->name('translations.create');
Route::get('/traducciones/{translation}', \App\Livewire\Admin\Translations\Show::class)
    ->name('translations.show');
Route::get('/traducciones/{translation}/editar', \App\Livewire\Admin\Translations\Edit::class)
    ->name('translations.edit');
```

**Middleware:**
- `web`
- `auth`
- `verified`

**Autorización:**
- Todas las rutas verifican permisos mediante `TranslationPolicy`

---

## Navegación

### Sidebar de Administración

**Ubicación**: `resources/views/components/layouts/app/sidebar.blade.php`

```blade
@can('viewAny', \App\Models\Translation::class)
    <flux:navlist.item 
        icon="language" 
        :href="route('admin.translations.index')" 
        :current="request()->routeIs('admin.translations.*')" 
        wire:navigate
    >
        {{ __('common.nav.translations') }}
    </flux:navlist.item>
@endcan
```

**Ubicación en Menú:**
- Grupo "System"
- Requiere permiso `TRANSLATIONS_VIEW`

---

## Internacionalización

### Archivos de Traducción

**Español**: `lang/es/common.php`
**Inglés**: `lang/en/common.php`

**Claves Añadidas:**

```php
'nav' => [
    'translations' => 'Traducciones', // 'Translations'
],
```

---

## Tests

### Cobertura de Tests

**Total: 66 tests pasando (152 assertions)**

#### Form Requests (15 tests)
- `tests/Feature/Http/Requests/StoreTranslationRequestTest.php` - 9 tests
- `tests/Feature/Http/Requests/UpdateTranslationRequestTest.php` - 6 tests

#### Policies (11 tests)
- `tests/Feature/Policies/TranslationPolicyTest.php` - 11 tests

#### Componentes Livewire (40 tests)
- `tests/Feature/Livewire/Admin/Translations/IndexTest.php` - 16 tests (35 assertions)
- `tests/Feature/Livewire/Admin/Translations/CreateTest.php` - 9 tests
- `tests/Feature/Livewire/Admin/Translations/EditTest.php` - 7 tests
- `tests/Feature/Livewire/Admin/Translations/ShowTest.php` - 8 tests

### Casos de Prueba Cubiertos

**Autorización:**
- Acceso denegado para usuarios no autenticados
- Acceso permitido según roles (super-admin, admin, editor, viewer)
- Verificación de permisos específicos por operación

**Form Requests:**
- Validación de campos requeridos
- Validación de tipos de datos
- Validación de existencia de relaciones
- Validación de unicidad
- Mensajes de error personalizados

**Componentes Livewire:**
- Listado con filtros y búsqueda
- Creación con validación en tiempo real
- Edición con validación de unicidad
- Visualización de detalle
- Eliminación con confirmación
- Paginación
- Ordenación
- Manejo de registros eliminados (soft deletes)

---

## Mejoras de UX

### Feedback Visual

- **Callouts de advertencia**: Cuando se detecta duplicado en Create
- **Tooltips informativos**: Información detallada de modelos traducibles
- **Badges de estado**: Indicadores visuales para registros eliminados
- **Estados de carga**: Indicadores durante operaciones asíncronas
- **Notificaciones**: Mensajes de éxito/error con toasts

### Validación en Tiempo Real

- Verificación de unicidad antes de guardar
- Feedback inmediato en formularios
- Deshabilitación de campos cuando hay errores

### Navegación

- Breadcrumbs en todas las vistas
- Enlaces contextuales a modelos traducibles
- Botones de acción claramente identificados

---

## Consideraciones Técnicas

### Manejo de Soft Deletes

El sistema detecta y muestra correctamente cuando un modelo traducible asociado ha sido eliminado (soft delete):
- Badge "Eliminado" en rojo
- Texto en cursiva y gris
- Tooltip con información detallada
- Uso de `withTrashed()` para recuperar modelos eliminados

### Validación de Unicidad

La validación de unicidad se realiza en múltiples niveles:
1. **Base de datos**: Constraint único en la tabla
2. **Form Request**: Validación en reglas
3. **Livewire**: Verificación en tiempo real antes de guardar

### Optimización de Consultas

- Eager loading de relaciones
- Caché de listados estáticos
- Índices optimizados en base de datos
- Caché interno para evitar N+1 queries en modelos polimórficos

---

## Extensibilidad

### Añadir Nuevos Modelos Traducibles

Para añadir un nuevo modelo traducible:

1. **Añadir al modelo**:
   ```php
   use App\Models\Concerns\Translatable;
   
   class NewModel extends Model
   {
       use Translatable;
       
       // Definir campos traducibles
       protected $translatableFields = ['name', 'description'];
   }
   ```

2. **Actualizar `Index::getAvailableModels()` y `Create::getAvailableModels()`**:
   ```php
   return [
       Program::class => __('Programa'),
       Setting::class => __('Configuración'),
       NewModel::class => __('Nuevo Modelo'), // Añadir aquí
   ];
   ```

3. **Actualizar `StoreTranslationRequest`**:
   - Añadir `NewModel::class` a la validación de `translatable_type`
   - Añadir validación de `translatable_id` para el nuevo modelo
   - Añadir campos traducibles en validación de `field`

4. **Actualizar caché**:
   - Añadir clave de caché para el nuevo modelo en `getTranslatableOptions()`

---

## Notas Importantes

- Las traducciones se eliminan en cascada cuando se elimina un idioma (`cascadeOnDelete`)
- El sistema soporta múltiples idiomas activos simultáneamente
- Los modelos traducibles pueden usar SoftDeletes sin afectar las traducciones
- El caché se limpia automáticamente cuando se modifican traducciones
- Los tests limpian el caché antes de cada ejecución para evitar interferencias

---

## Referencias

- [Sistema de Internacionalización (i18n)](i18n-system.md)
- [Trait Translatable](../app/Models/Concerns/Translatable.php)
- [Modelo Translation](../app/Models/Translation.php)
- [Plan de Desarrollo](pasos/paso-3.5.13-plan.md)
