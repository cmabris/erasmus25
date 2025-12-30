# Plan de Desarrollo: CRUD de Resoluciones (Paso 3.5.4.2)

Este documento establece el plan detallado para desarrollar el CRUD completo de Resoluciones en el panel de administración, siguiendo el patrón establecido en el CRUD de Fases de Convocatorias.

## Objetivo

Implementar un CRUD completo y moderno para la gestión de Resoluciones asociadas a Convocatorias y Fases, con las siguientes características:

- ✅ Rutas anidadas bajo `/admin/convocatorias/{call}/resoluciones`
- ✅ SoftDeletes para eliminación suave
- ✅ Publicación de resoluciones (campo `published_at`)
- ✅ Subida de PDFs mediante Laravel Media Library
- ✅ Gestión de tipos de resolución (provisional, definitivo, alegaciones)
- ✅ Asociación a convocatoria y fase específica
- ✅ Validación de fecha oficial vs fecha de publicación
- ✅ Integración con componente Show de Convocatoria
- ✅ Autorización mediante `ResolutionPolicy`
- ✅ Validación mediante `StoreResolutionRequest` y `UpdateResolutionRequest`

---

## Fase 1: Preparación del Modelo

### Paso 1.1: Agregar SoftDeletes al Modelo Resolution

**Archivo:** `app/Models/Resolution.php`

**Tareas:**
- [ ] Agregar `use Illuminate\Database\Eloquent\SoftDeletes;`
- [ ] Agregar trait `use SoftDeletes;`
- [ ] Verificar que el modelo tenga todas las relaciones necesarias (`call`, `callPhase`, `creator`)

### Paso 1.2: Crear Migración para Agregar SoftDeletes

**Archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_add_soft_deletes_to_resolutions_table.php`

**Tareas:**
- [ ] Crear migración para agregar columna `deleted_at` a tabla `resolutions`
- [ ] Ejecutar migración

### Paso 1.3: Agregar Media Library al Modelo Resolution

**Archivo:** `app/Models/Resolution.php`

**Tareas:**
- [ ] Agregar `use Spatie\MediaLibrary\HasMedia;`
- [ ] Agregar `use Spatie\MediaLibrary\InteractsWithMedia;`
- [ ] Implementar interface `HasMedia`
- [ ] Agregar trait `InteractsWithMedia`
- [ ] Configurar colección de medios 'resolutions' para PDFs en método `registerMediaCollections()`

### Paso 1.4: Instalar y Configurar Spatie Livewire-FilePond

**Archivos:**
- `composer.json` - Agregar dependencia PHP
- `package.json` - Agregar dependencias NPM
- Componentes Livewire - Usar trait `WithFilePond`

**Tareas:**
- [ ] Instalar paquete PHP: `composer require spatie/livewire-filepond`
- [ ] Instalar dependencias NPM: `npm install filepond filepond-plugin-file-validate-type filepond-plugin-file-validate-size`
- [ ] Publicar assets (opcional): `php artisan vendor:publish --tag="livewire-filepond-assets"`
- [ ] Publicar vistas (opcional): `php artisan vendor:publish --tag="livewire-filepond-views"`
- [ ] Configurar FilePond en componentes Livewire usando:
  - Trait `Spatie\LivewireFilepond\WithFilePond`
  - Componente `<x-filepond::upload>` en vistas
- [ ] Configurar validaciones para:
  - Solo aceptar PDFs (`application/pdf`)
  - Tamaño máximo: 10MB (configurable)
  - Validación en tiempo real

---

## Fase 2: Actualización de Form Requests

### Paso 2.1: Revisar y Actualizar StoreResolutionRequest

**Archivo:** `app/Http/Requests/StoreResolutionRequest.php`

**Tareas:**
- [ ] Verificar que valida correctamente `call_id` y `call_phase_id`
- [ ] Verificar que valida que `call_phase_id` pertenezca a `call_id`
- [ ] Agregar validación de archivo PDF (opcional pero recomendado)
- [ ] Verificar mensajes de validación personalizados
- [ ] Asegurar que `created_by` se establece automáticamente

### Paso 2.2: Revisar y Actualizar UpdateResolutionRequest

**Archivo:** `app/Http/Requests/UpdateResolutionRequest.php`

**Tareas:**
- [ ] Verificar que valida correctamente `call_id` y `call_phase_id`
- [ ] Verificar que valida que `call_phase_id` pertenezca a `call_id`
- [ ] Agregar validación de archivo PDF (opcional)
- [ ] Verificar mensajes de validación personalizados
- [ ] Asegurar que excluye la resolución actual en validaciones de unicidad si aplica

---

## Fase 3: Componente Index (Listado)

### Paso 3.1: Crear Componente Livewire Index

**Archivo:** `app/Livewire/Admin/Calls/Resolutions/Index.php`

**Propiedades públicas necesarias:**
- `public Call $call;` - La convocatoria padre
- `#[Url(as: 'q')] public string $search = '';` - Búsqueda por título
- `#[Url(as: 'tipo')] public string $filterType = '';` - Filtro por tipo
- `#[Url(as: 'publicada')] public string $filterPublished = '';` - Filtro por estado de publicación
- `#[Url(as: 'fase')] public string $filterPhase = '';` - Filtro por fase
- `#[Url(as: 'eliminados')] public string $showDeleted = '0';` - Mostrar eliminados
- `#[Url(as: 'ordenar')] public string $sortField = 'official_date';` - Campo de ordenación
- `#[Url(as: 'direccion')] public string $sortDirection = 'desc';` - Dirección de ordenación
- `#[Url(as: 'por-pagina')] public int $perPage = 15;` - Items por página
- Modales de confirmación (delete, restore, forceDelete)

**Métodos principales:**
- `mount(Call $call)` - Inicializar componente y autorizar
- `resolutions()` (Computed) - Listado paginado con filtros
- `sortBy(string $field)` - Cambiar ordenación
- `publish(int $resolutionId)` - Publicar resolución
- `unpublish(int $resolutionId)` - Despublicar resolución
- `confirmDelete(int $resolutionId)` - Abrir modal de eliminación
- `delete()` - Eliminar resolución (soft delete)
- `confirmRestore(int $resolutionId)` - Abrir modal de restauración
- `restore()` - Restaurar resolución eliminada
- `confirmForceDelete(int $resolutionId)` - Abrir modal de eliminación permanente
- `forceDelete()` - Eliminar permanentemente
- `resetFilters()` - Resetear filtros
- `getTypeColor(string $type)` - Color del badge por tipo
- `getTypeLabel(string $type)` - Etiqueta del tipo

**Características:**
- Búsqueda por título con debounce
- Filtros por tipo, estado de publicación, fase
- Ordenación por campo configurable
- Paginación configurable
- Eager loading de relaciones (`call`, `callPhase`, `creator`)
- Conteo de medios asociados
- Modales de confirmación para acciones destructivas

### Paso 3.2: Crear Vista Index

**Archivo:** `resources/views/livewire/admin/calls/resolutions/index.blade.php`

**Estructura:**
- Header con título, breadcrumbs y botón crear
- Información de la convocatoria padre (título, programa, año académico)
- Filtros (tipo, publicada, fase)
- Búsqueda con debounce
- Tabla responsive con columnas:
  - Tipo (badge con color)
  - Título
  - Fase asociada
  - Fecha oficial
  - Fecha de publicación (badge si está publicada)
  - PDF (icono si tiene archivo)
  - Acciones (ver, editar, eliminar, publicar/despublicar)
- Paginación
- Modales de confirmación (eliminar, restaurar, forceDelete)
- Estados de carga
- Estado vacío con componente Flux UI

---

## Fase 4: Componente Create (Crear)

### Paso 4.1: Crear Componente Livewire Create

**Archivo:** `app/Livewire/Admin/Calls/Resolutions/Create.php`

**Propiedades públicas:**
- `public Call $call;` - Convocatoria padre
- `public ?int $call_id = null;` - ID de convocatoria (pre-llenado)
- `public ?int $call_phase_id = null;` - ID de fase (pre-llenado)
- `public string $type = 'provisional';` - Tipo de resolución
- `public string $title = '';` - Título
- `public ?string $description = null;` - Descripción
- `public ?string $evaluation_procedure = null;` - Procedimiento de evaluación
- `public ?string $official_date = null;` - Fecha oficial
- `public ?string $published_at = null;` - Fecha de publicación (opcional)
- `public $pdfFile = null;` - Archivo PDF (temporal)

**Métodos principales:**
- `mount(Call $call, ?int $call_phase_id = null)` - Inicializar con convocatoria y opcionalmente fase
- `save()` - Guardar nueva resolución
- `updatedCallPhaseId()` - Validar que la fase pertenezca a la convocatoria
- `getCallPhases()` (Computed) - Obtener fases de la convocatoria
- `getTypeOptions()` - Opciones de tipos de resolución

**Traits:**
- `use Spatie\LivewireFilepond\WithFilePond;` - Para integración con FilePond

**Características:**
- Formulario completo con Flux UI
- Select de fase con opciones filtradas por convocatoria
- Select de tipo de resolución
- Campo de fecha oficial (requerido)
- Campo de fecha de publicación (opcional)
- Upload de archivo PDF con **Spatie Livewire-FilePond**:
  - Componente `<x-filepond::upload>` de Spatie
  - Drag & drop mejorado
  - Preview del PDF seleccionado
  - Indicador de progreso visual
  - Validación en cliente (tipo y tamaño)
  - Integración nativa con Livewire mediante trait `WithFilePond`
- Validación en tiempo real
- Mensajes de éxito/error con notificaciones toast
- Redirección al listado o al detalle después de crear

### Paso 4.2: Crear Vista Create

**Archivo:** `resources/views/livewire/admin/calls/resolutions/create.blade.php`

**Estructura:**
- Header con título y breadcrumbs
- Información de la convocatoria padre
- Formulario con campos:
  - Select de fase (requerido, filtrado por convocatoria)
  - Select de tipo (requerido)
  - Input de título (requerido)
  - Textarea de descripción (opcional)
  - Textarea de procedimiento de evaluación (opcional)
  - Input de fecha oficial (requerido)
  - Input de fecha de publicación (opcional)
  - **Componente `<x-filepond::upload>` de Spatie**:
    - Drag & drop zone mejorado
    - Preview del PDF seleccionado
    - Indicador de progreso durante upload
    - Botón para eliminar archivo seleccionado
    - Validación visual de tipo y tamaño
    - Configuración mediante props del componente
- Botones: Guardar y Cancelar
- Validación en tiempo real
- Mensajes de error debajo de cada campo

---

## Fase 5: Componente Edit (Editar)

### Paso 5.1: Crear Componente Livewire Edit

**Archivo:** `app/Livewire/Admin/Calls/Resolutions/Edit.php`

**Propiedades públicas:**
- `public Resolution $resolution;` - Resolución a editar
- Mismas propiedades que Create pero con valores pre-cargados

**Métodos principales:**
- `mount(Resolution $resolution)` - Cargar resolución con relaciones
- `update()` - Actualizar resolución existente
- `removePdf()` - Eliminar PDF existente
- Mismos métodos auxiliares que Create

**Traits:**
- `use Spatie\LivewireFilepond\WithFilePond;` - Para integración con FilePond

**Características:**
- Formulario completo con datos pre-cargados
- Mismas validaciones que Create
- Mostrar PDF actual si existe con:
  - Preview del PDF actual
  - Botón para descargar
  - Opción de eliminarlo
  - Opción de reemplazarlo con FilePond
- Validación de que la fase pertenezca a la convocatoria
- Mensajes de éxito/error con notificaciones toast
- Redirección al detalle después de actualizar

### Paso 5.2: Crear Vista Edit

**Archivo:** `resources/views/livewire/admin/calls/resolutions/edit.blade.php`

**Estructura:**
- Similar a Create pero con datos pre-cargados
- Mostrar PDF actual si existe:
  - Preview del PDF con información (nombre, tamaño, fecha)
  - Botón para descargar
  - Botón para eliminar
  - Opción de reemplazar con componente `<x-filepond::upload>` (drag & drop o selección)
- Botones: Actualizar y Cancelar

---

## Fase 6: Componente Show (Detalle)

### Paso 6.1: Crear Componente Livewire Show

**Archivo:** `app/Livewire/Admin/Calls/Resolutions/Show.php`

**Propiedades públicas:**
- `public Resolution $resolution;` - Resolución a mostrar
- Modales de confirmación (delete, restore, forceDelete)

**Métodos principales:**
- `mount(Resolution $resolution)` - Cargar resolución con relaciones
- `publish()` - Publicar resolución
- `unpublish()` - Despublicar resolución
- `delete()` - Eliminar resolución (soft delete)
- `restore()` - Restaurar resolución eliminada
- `forceDelete()` - Eliminar permanentemente
- `getTypeColor(string $type)` - Color del badge por tipo
- `getTypeLabel(string $type)` - Etiqueta del tipo
- `downloadPdf()` - Descargar PDF

**Características:**
- Vista completa de detalles de la resolución
- Información de la convocatoria y fase padre con eager loading
- Badge de tipo de resolución con color
- Badge de estado "Publicada" si aplica
- Mostrar PDF con botón de descarga
- Botones de acción: editar, eliminar, restaurar, publicar/despublicar
- Breadcrumbs correctamente configurados

### Paso 6.2: Crear Vista Show

**Archivo:** `resources/views/livewire/admin/calls/resolutions/show.blade.php`

**Estructura:**
- Header con título y breadcrumbs
- Información de la convocatoria y fase padre
- Detalles de la resolución:
  - Tipo (badge)
  - Título
  - Descripción
  - Procedimiento de evaluación
  - Fecha oficial
  - Fecha de publicación (si está publicada)
  - PDF (si existe, con botón de descarga)
  - Creado por y fecha de creación
- Botones de acción: editar, eliminar, restaurar, publicar/despublicar
- Modales de confirmación

---

## Fase 7: Rutas y Navegación

### Paso 7.1: Configurar Rutas Anidadas

**Archivo:** `routes/web.php`

**Tareas:**
- [ ] Agregar rutas anidadas bajo `/admin/convocatorias/{call}/resoluciones`:
  ```php
  Route::prefix('convocatorias/{call}')->group(function () {
      // ... rutas de fases existentes ...
      
      Route::get('/resoluciones', \App\Livewire\Admin\Calls\Resolutions\Index::class)
          ->name('calls.resolutions.index');
      Route::get('/resoluciones/crear', \App\Livewire\Admin\Calls\Resolutions\Create::class)
          ->name('calls.resolutions.create');
      Route::get('/resoluciones/{resolution}', \App\Livewire\Admin\Calls\Resolutions\Show::class)
          ->name('calls.resolutions.show');
      Route::get('/resoluciones/{resolution}/editar', \App\Livewire\Admin\Calls\Resolutions\Edit::class)
          ->name('calls.resolutions.edit');
  });
  ```

### Paso 7.2: Integrar en Vista Show de Convocatorias

**Archivo:** `resources/views/livewire/admin/calls/show.blade.php`

**Tareas:**
- [ ] Agregar sección de Resoluciones en la vista Show de Convocatorias
- [ ] Agregar botón "Gestionar Resoluciones" que navega al listado
- [ ] Agregar botón "Añadir Resolución" que navega al formulario de creación
- [ ] Mostrar listado de resoluciones con información básica
- [ ] Agregar acciones rápidas (publicar/despublicar desde el listado)

---

## Fase 8: Traducciones

### Paso 8.1: Agregar Traducciones en Español

**Archivo:** `lang/es/common.php` o `lang/es/resolutions.php`

**Tareas:**
- [ ] Agregar traducciones para:
  - Títulos de páginas
  - Etiquetas de campos
  - Mensajes de éxito/error
  - Botones de acción
  - Tipos de resolución
  - Estados de publicación

### Paso 8.2: Agregar Traducciones en Inglés

**Archivo:** `lang/en/common.php` o `lang/en/resolutions.php`

**Tareas:**
- [ ] Agregar traducciones equivalentes en inglés

---

## Fase 9: Tests

### Paso 9.1: Tests del Componente Index

**Archivo:** `tests/Feature/Livewire/Admin/Calls/Resolutions/IndexTest.php`

**Tareas:**
- [ ] Test de autorización (verificar permisos)
- [ ] Test de listado con filtros
- [ ] Test de búsqueda
- [ ] Test de ordenación
- [ ] Test de paginación
- [ ] Test de publicación/despublicación
- [ ] Test de eliminación (soft delete)
- [ ] Test de restauración
- [ ] Test de eliminación permanente

### Paso 9.2: Tests del Componente Create

**Archivo:** `tests/Feature/Livewire/Admin/Calls/Resolutions/CreateTest.php`

**Tareas:**
- [ ] Test de autorización
- [ ] Test de creación exitosa
- [ ] Test de validación de campos requeridos
- [ ] Test de validación de fase pertenece a convocatoria
- [ ] Test de subida de PDF
- [ ] Test de establecimiento de `created_by`

### Paso 9.3: Tests del Componente Edit

**Archivo:** `tests/Feature/Livewire/Admin/Calls/Resolutions/EditTest.php`

**Tareas:**
- [ ] Test de autorización
- [ ] Test de actualización exitosa
- [ ] Test de validación de campos
- [ ] Test de actualización de PDF
- [ ] Test de eliminación de PDF existente

### Paso 9.4: Tests del Componente Show

**Archivo:** `tests/Feature/Livewire/Admin/Calls/Resolutions/ShowTest.php`

**Tareas:**
- [ ] Test de autorización
- [ ] Test de visualización de detalles
- [ ] Test de publicación/despublicación
- [ ] Test de eliminación
- [ ] Test de restauración
- [ ] Test de eliminación permanente
- [ ] Test de descarga de PDF

### Paso 9.5: Tests de Form Requests

**Archivos:**
- `tests/Feature/Http/Requests/StoreResolutionRequestTest.php`
- `tests/Feature/Http/Requests/UpdateResolutionRequestTest.php`

**Tareas:**
- [ ] Test de reglas de validación
- [ ] Test de mensajes personalizados
- [ ] Test de autorización
- [ ] Test de validación de fase pertenece a convocatoria

---

## Fase 10: Optimizaciones y Mejoras

### Paso 10.1: Optimización de Consultas

**Tareas:**
- [ ] Implementar eager loading en todos los componentes
- [ ] Usar `withCount()` para conteos
- [ ] Revisar y optimizar consultas N+1

### Paso 10.2: Validación de Relaciones

**Tareas:**
- [ ] Validar que no se pueda eliminar resolución si tiene relaciones importantes
- [ ] Implementar validación antes de forceDelete

### Paso 10.3: Mejoras de UX

**Tareas:**
- [ ] Agregar estados de carga en acciones asíncronas
- [ ] Mejorar mensajes de error
- [ ] Agregar confirmaciones para acciones destructivas
- [ ] Implementar notificaciones toast consistentes

---

## Resumen de Archivos a Crear/Modificar

### Archivos Nuevos a Crear:
1. `app/Livewire/Admin/Calls/Resolutions/Index.php`
2. `app/Livewire/Admin/Calls/Resolutions/Create.php`
3. `app/Livewire/Admin/Calls/Resolutions/Edit.php`
4. `app/Livewire/Admin/Calls/Resolutions/Show.php`
5. `resources/views/livewire/admin/calls/resolutions/index.blade.php`
6. `resources/views/livewire/admin/calls/resolutions/create.blade.php`
7. `resources/views/livewire/admin/calls/resolutions/edit.blade.php`
8. `resources/views/livewire/admin/calls/resolutions/show.blade.php`
9. `database/migrations/YYYY_MM_DD_HHMMSS_add_soft_deletes_to_resolutions_table.php`
11. `tests/Feature/Livewire/Admin/Calls/Resolutions/IndexTest.php`
12. `tests/Feature/Livewire/Admin/Calls/Resolutions/CreateTest.php`
13. `tests/Feature/Livewire/Admin/Calls/Resolutions/EditTest.php`
14. `tests/Feature/Livewire/Admin/Calls/Resolutions/ShowTest.php`

### Archivos a Modificar:
1. `app/Models/Resolution.php` - Agregar SoftDeletes y Media Library
2. `app/Http/Requests/StoreResolutionRequest.php` - Revisar y ajustar validaciones
3. `app/Http/Requests/UpdateResolutionRequest.php` - Revisar y ajustar validaciones
4. `routes/web.php` - Agregar rutas anidadas
5. `resources/views/livewire/admin/calls/show.blade.php` - Integrar gestión de resoluciones
6. `composer.json` - Agregar dependencia `spatie/livewire-filepond`
7. `package.json` - Agregar dependencias NPM de FilePond
8. `lang/es/common.php` o `lang/es/resolutions.php` - Agregar traducciones
9. `lang/en/common.php` o `lang/en/resolutions.php` - Agregar traducciones

---

## Orden de Implementación Recomendado

1. **Fase 1**: Preparación del Modelo (SoftDeletes y Media Library)
2. **Fase 2**: Actualización de Form Requests
3. **Fase 3**: Componente Index (listado)
4. **Fase 4**: Componente Create (crear)
5. **Fase 5**: Componente Edit (editar)
6. **Fase 6**: Componente Show (detalle)
7. **Fase 7**: Rutas y Navegación
8. **Fase 8**: Traducciones
9. **Fase 9**: Tests
10. **Fase 10**: Optimizaciones y Mejoras

---

## Notas Técnicas

### SoftDeletes
- Las resoluciones nunca se eliminan permanentemente por defecto
- Solo super-admin puede realizar `forceDelete()`
- Antes de `forceDelete()`, validar que no existan relaciones importantes

### Media Library
- Configurar colección 'resolutions' para PDFs
- Validar tipo de archivo (solo PDF)
- Validar tamaño máximo de archivo (10MB recomendado)
- Generar nombres de archivo únicos

### Spatie Livewire-FilePond Integration
- **Instalación PHP**: `composer require spatie/livewire-filepond`
- **Instalación NPM**: `npm install filepond filepond-plugin-file-validate-type filepond-plugin-file-validate-size`
- **Configuración**:
  - Publicar assets (opcional): `php artisan vendor:publish --tag="livewire-filepond-assets"`
  - Publicar vistas (opcional): `php artisan vendor:publish --tag="livewire-filepond-views"`
  - Usar trait `Spatie\LivewireFilepond\WithFilePond` en componentes Livewire
  - Usar componente `<x-filepond::upload wire:model="pdfFile" />` en vistas
- **Configuración del componente**:
  - Solo aceptar PDFs: `accepted-file-types="application/pdf"`
  - Tamaño máximo: `max-file-size="10MB"`
  - Validación en tiempo real integrada
  - Preview del archivo seleccionado
  - Indicador de progreso visual
  - Drag & drop mejorado
- **Ventajas sobre implementación manual**:
  - Integración nativa con Livewire
  - Menos código personalizado
  - Mejor mantenimiento (paquete oficial de Spatie)
  - Documentación completa y soporte activo

### Rutas Anidadas
- Las rutas de resoluciones están anidadas bajo `/admin/convocatorias/{call}/resoluciones`
- Requiere pasar ambos parámetros (`call` y `resolution`) a las funciones `route()` en las vistas
- Usar route model binding cuando sea posible

### Validación de Relaciones
- Validar que `call_phase_id` pertenezca a `call_id` en FormRequests
- Validar que la fase existe y pertenece a la convocatoria antes de crear/editar

### Publicación
- El campo `published_at` se establece manualmente o mediante acción de publicación
- Las resoluciones publicadas son visibles públicamente
- Las resoluciones no publicadas solo son visibles para administradores

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Listo para implementación

