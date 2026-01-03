# Plan de Desarrollo: Paso 3.5.7 - Gestión de Documentos en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Documentos en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Documentos en el panel de administración con:
- Listado moderno con tabla interactiva y filtros avanzados
- Formularios de creación y edición con subida de archivos
- Vista de detalle con información completa
- **SoftDeletes**: Los documentos nunca se eliminan permanentemente, solo se marcan como eliminados
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- Gestión de archivos mediante Laravel Media Library
- Gestión de consentimientos de medios (MediaConsent) asociados
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos de Desarrollo (18 Pasos)

### **Fase 1: Preparación Base**

#### **Paso 1: Implementar SoftDeletes en Document**
- [ ] Crear migración para añadir columna `deleted_at` a la tabla `documents`
- [ ] Ejecutar migración
- [ ] Actualizar modelo `Document` para usar el trait `SoftDeletes`
- [ ] Verificar que las relaciones funcionen correctamente con SoftDeletes
- [ ] Actualizar factory si es necesario

#### **Paso 2: Actualizar FormRequests con Autorización**
- [ ] Actualizar `StoreDocumentRequest`:
  - Añadir autorización con `DocumentPolicy::create()`
  - Añadir validación de archivo (opcional en creación, pero preparar para edición)
  - Añadir mensajes de error personalizados en español e inglés
  - Verificar validación de `title` y `slug` únicos
  - Añadir validación de `file` (opcional, pero preparar estructura)
- [ ] Actualizar `UpdateDocumentRequest`:
  - Añadir autorización con `DocumentPolicy::update()`
  - Añadir mensajes de error personalizados
  - Validación de `title` y `slug` únicos (ignorando el registro actual)
  - Añadir validación de `file` (opcional para reemplazar archivo existente)
- [ ] Verificar que `DocumentPolicy` tenga todos los métodos necesarios (ya existe)

---

### **Fase 2: Estructura Base y Listado** (MVP)

#### **Paso 3: Componente Index (Listado)**
- [ ] Crear componente Livewire `Admin\Documents\Index`
- [ ] Implementar propiedades públicas:
  - `Collection $documents` - Lista de documentos (computed)
  - `string $search = ''` - Búsqueda por título/descripción (con `#[Url]`)
  - `?int $categoryId = null` - Filtro por categoría (con `#[Url]`)
  - `?int $programId = null` - Filtro por programa (con `#[Url]`)
  - `?int $academicYearId = null` - Filtro por año académico (con `#[Url]`)
  - `?string $documentType = null` - Filtro por tipo de documento (con `#[Url]`)
  - `?bool $isActive = null` - Filtro por estado activo/inactivo (con `#[Url]`)
  - `string $showDeleted = '0'` - Filtro de eliminados (con `#[Url]`)
  - `string $sortField = 'created_at'` - Campo de ordenación (con `#[Url]`)
  - `string $sortDirection = 'desc'` - Dirección de ordenación (con `#[Url]`)
  - `int $perPage = 15` - Elementos por página (con `#[Url]`)
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `?int $documentToDelete = null` - ID de documento a eliminar
  - `bool $showRestoreModal = false` - Modal de confirmación de restauración
  - `?int $documentToRestore = null` - ID de documento a restaurar
  - `bool $showForceDeleteModal = false` - Modal de confirmación de eliminación permanente
  - `?int $documentToForceDelete = null` - ID de documento a eliminar permanentemente
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `documents()` - Computed property con paginación, filtros y ordenación
  - `sortBy($field)` - Ordenación
  - `confirmDelete($documentId)` - Confirmar eliminación
  - `delete()` - Eliminar con SoftDeletes (validar relaciones)
  - `confirmRestore($documentId)` - Confirmar restauración
  - `restore()` - Restaurar documento eliminado
  - `confirmForceDelete($documentId)` - Confirmar eliminación permanente
  - `forceDelete()` - Eliminar permanentemente (solo super-admin, validar relaciones)
  - `resetFilters()` - Resetear filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedShowDeleted()` - Resetear página al cambiar filtro
  - `canCreate()` - Verificar si puede crear
  - `canViewDeleted()` - Verificar si puede ver eliminados
  - `canDeleteDocument($document)` - Verificar si puede eliminar (sin relaciones)
  - `render()` - Renderizado con paginación y datos para filtros
- [ ] Implementar autorización con `DocumentPolicy`
- [ ] Crear vista `livewire/admin/documents/index.blade.php`:
  - Header con título y botón crear
  - Breadcrumbs
  - Filtros: búsqueda, categoría, programa, año académico, tipo, estado activo, eliminados, reset
  - Tabla responsive con columnas: título, categoría, tipo, programa, año académico, archivo (preview), estado, descargas, fecha creación, acciones
  - Modales de confirmación (eliminar, restaurar, force delete)
  - Paginación
  - Estado vacío
  - Loading states

---

### **Fase 3: Creación y Edición**

#### **Paso 4: Componente Create (Crear)**
- [ ] Crear componente Livewire `Admin\Documents\Create`
- [ ] Añadir traits necesarios:
  - `use Spatie\LivewireFilepond\WithFilePond;` - Para integración con FilePond
  - `use Livewire\WithFileUploads;` - Para manejo de archivos en Livewire
- [ ] Implementar propiedades públicas:
  - `?int $categoryId = null` - ID de categoría
  - `?int $programId = null` - ID de programa
  - `?int $academicYearId = null` - ID de año académico
  - `string $title = ''` - Título del documento
  - `string $slug = ''` - Slug del documento
  - `string $description = ''` - Descripción
  - `string $documentType = 'otro'` - Tipo de documento
  - `string $version = ''` - Versión
  - `bool $isActive = true` - Estado activo
  - `?UploadedFile $file = null` - Archivo a subir (usado con FilePond)
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `updatedTitle()` - Generar slug automáticamente desde título
  - `updatedSlug()` - Validar slug en tiempo real
  - `store()` - Guardar nuevo documento usando `StoreDocumentRequest`:
    - Validar datos con FormRequest
    - Crear documento
    - Si hay archivo, subirlo con `addMedia()` a la colección `file`
- [ ] Crear vista `livewire/admin/documents/create.blade.php`:
  - Header con título y breadcrumbs
  - Formulario con Flux UI:
    - Campo categoría (select, requerido)
    - Campo programa (select, opcional)
    - Campo año académico (select, opcional)
    - Campo título (requerido, validación en tiempo real)
    - Campo slug (opcional, se genera automáticamente, editable)
    - Campo descripción (textarea, opcional)
    - Campo tipo de documento (select, requerido)
    - Campo versión (opcional)
    - Campo estado activo (switch)
    - **Campo archivo con FilePond**:
      - Componente `<x-filepond::upload>` de Spatie
      - `wire:model="file"`
      - `accepted-file-types` con tipos MIME según modelo Document:
        - `application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/csv,image/jpeg,image/png,image/webp`
      - `max-file-size` configurado (ej: 20MB según necesidad)
      - Labels traducidos en español (seguir patrón de Resoluciones/Noticias)
      - Drag & drop mejorado
      - Preview automático para imágenes y PDFs
      - Indicador de progreso visual
      - Validación en cliente (tipo y tamaño)
    - Botones: guardar y cancelar
  - Validación visual en tiempo real
  - Mensajes de error

#### **Paso 5: Componente Edit (Editar)**
- [ ] Crear componente Livewire `Admin\Documents\Edit`
- [ ] Añadir traits necesarios:
  - `use Spatie\LivewireFilepond\WithFilePond;` - Para integración con FilePond
  - `use Livewire\WithFileUploads;` - Para manejo de archivos en Livewire
- [ ] Implementar propiedades públicas:
  - `Document $document` - Documento a editar
  - `?int $categoryId = null` - ID de categoría
  - `?int $programId = null` - ID de programa
  - `?int $academicYearId = null` - ID de año académico
  - `string $title = ''` - Título del documento
  - `string $slug = ''` - Slug del documento
  - `string $description = ''` - Descripción
  - `string $documentType = 'otro'` - Tipo de documento
  - `string $version = ''` - Versión
  - `bool $isActive = true` - Estado activo
  - `?UploadedFile $file = null` - Nuevo archivo a subir (reemplazar, usado con FilePond)
  - `bool $removeExistingFile = false` - Flag para eliminar archivo existente
- [ ] Implementar métodos:
  - `mount(Document $document)` - Cargar datos del documento con relaciones
  - `updatedTitle()` - Generar slug automáticamente desde título
  - `updatedSlug()` - Validar slug en tiempo real
  - `removeFile()` - Eliminar archivo actual (marcar flag `removeExistingFile = true`)
  - `update()` - Actualizar documento usando `UpdateDocumentRequest`:
    - Validar datos con FormRequest
    - Actualizar documento
    - Si `removeExistingFile` es true, eliminar archivo actual con `clearMediaCollection('file')`
    - Si hay nuevo archivo, eliminar el anterior y subir el nuevo con `addMedia()` a la colección `file`
- [ ] Crear computed property `existingFile()`:
  - Retornar `$this->document->getFirstMedia('file')` para obtener archivo actual
- [ ] Crear vista `livewire/admin/documents/edit.blade.php`:
  - Similar a Create pero con datos precargados
  - **Gestión de archivo actual**:
    - Si existe archivo: mostrar información (nombre, tamaño, tipo, fecha)
    - Botón para descargar archivo actual
    - Botón para eliminar archivo actual (sin subir uno nuevo)
    - Separador visual
  - **Subida de nuevo archivo**:
    - Si no hay archivo actual o se quiere reemplazar:
      - Componente `<x-filepond::upload>` igual que en Create
      - Mismos tipos MIME y configuración que Create
      - Al subir nuevo archivo, reemplaza automáticamente el anterior
  - Información adicional: fecha creación, fecha actualización, creador, actualizador, contador de descargas

---

### **Fase 4: Vista Detalle**

#### **Paso 6: Componente Show (Detalle)**
- [ ] Crear componente Livewire `Admin\Documents\Show`
- [ ] Implementar propiedades públicas:
  - `Document $document` - Documento a mostrar
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `bool $showForceDeleteModal = false` - Modal de confirmación de eliminación permanente
- [ ] Implementar métodos:
  - `mount(Document $document)` - Cargar documento con relaciones
  - `download()` - Descargar archivo del documento
  - `confirmDelete()` - Confirmar eliminación
  - `delete()` - Eliminar (redirigir a Index)
  - `confirmForceDelete()` - Confirmar eliminación permanente
  - `forceDelete()` - Eliminar permanentemente (solo super-admin)
  - `restore()` - Restaurar si está eliminado
- [ ] Crear vista `livewire/admin/documents/show.blade.php`:
  - Información completa del documento:
    - Título, slug, descripción
    - Categoría, programa, año académico
    - Tipo de documento, versión
    - Estado activo/inactivo
    - Archivo: preview/descarga/eliminar
    - Contador de descargas
    - Fechas: creación, actualización
    - Usuarios: creador, actualizador
  - Listado de consentimientos de medios asociados (si aplica)
  - Botones de acción: editar, eliminar, restaurar, volver
  - Breadcrumbs

---

### **Fase 5: Gestión de Archivos con Media Library**

#### **Paso 7: Verificar Configuración de Media Collections**
- [ ] Verificar que el modelo `Document` tenga configurada la colección `file` (ya existe en `registerMediaCollections()`)
- [ ] Verificar tipos MIME aceptados en el modelo (ya configurado):
  - PDF: `application/pdf`
  - Word: `application/msword`, `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
  - Excel: `application/vnd.ms-excel`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
  - PowerPoint: `application/vnd.ms-powerpoint`, `application/vnd.openxmlformats-officedocument.presentationml.presentation`
  - Texto: `text/plain`, `text/csv`
  - Imágenes: `image/jpeg`, `image/png`, `image/webp`
- [ ] Añadir conversiones si es necesario (thumbnails para imágenes, previews para PDFs) en `registerMediaConversions()`
- [ ] Configurar validación de tamaño máximo de archivo en FormRequests (ej: 20MB)

#### **Paso 8: Verificar Configuración de FilePond**
- [ ] Verificar que `spatie/livewire-filepond` esté instalado (ya está en uso en Resoluciones y Noticias)
- [ ] Verificar que FilePond esté configurado en `resources/js/app.js` (ya configurado)
- [ ] Usar componente `<x-filepond::upload>` en Create y Edit (como en Resoluciones y Noticias)
- [ ] Configurar `accepted-file-types` con los tipos MIME del modelo Document
- [ ] Configurar `max-file-size` según validación del FormRequest
- [ ] Añadir labels traducidos en español (seguir el patrón de Resoluciones/Noticias)
- [ ] El preview y validación en frontend ya están incluidos en el componente de Spatie

---

### **Fase 6: Rutas y Navegación**

#### **Paso 9: Configurar Rutas**
- [ ] Añadir rutas en `routes/web.php` dentro del grupo `admin`:
  - `GET /admin/documentos` → `Admin\Documents\Index` (nombre: `admin.documents.index`)
  - `GET /admin/documentos/crear` → `Admin\Documents\Create` (nombre: `admin.documents.create`)
  - `GET /admin/documentos/{document}` → `Admin\Documents\Show` (nombre: `admin.documents.show`)
  - `GET /admin/documentos/{document}/editar` → `Admin\Documents\Edit` (nombre: `admin.documents.edit`)
- [ ] Verificar que las rutas usen el middleware correcto (`auth`, `verified`)
- [ ] Verificar route model binding con `slug` si es necesario

#### **Paso 10: Actualizar Navegación**
- [ ] Añadir enlace en sidebar de administración
- [ ] Añadir traducciones necesarias en `lang/es/common.php` y `lang/en/common.php`:
  - `Documentos` / `Documents`
  - `Crear Documento` / `Create Document`
  - `Editar Documento` / `Edit Document`
  - `Ver Documento` / `View Document`
  - Mensajes de éxito/error relacionados
  - Tipos de documento traducidos

---

### **Fase 7: Validación de Relaciones y Optimizaciones**

#### **Paso 11: Validar Relaciones Antes de Eliminar**
- [ ] En método `delete()` del componente Index:
  - Verificar si el documento tiene consentimientos de medios asociados (`MediaConsent`)
  - Si tiene relaciones, mostrar error y no eliminar
  - Mensaje: "No se puede eliminar el documento porque tiene consentimientos de medios asociados"
- [ ] En método `forceDelete()`:
  - Verificar relaciones antes de eliminar permanentemente
  - Solo permitir si no hay relaciones
  - Mensaje de error si intenta eliminar con relaciones

#### **Paso 12: Optimizaciones**
- [ ] Añadir `withCount(['mediaConsents'])` en consulta de Index para evitar N+1
- [ ] Añadir eager loading de relaciones: `category`, `program`, `academicYear`, `creator`, `updater`
- [ ] Verificar índices en base de datos si es necesario
- [ ] Optimizar consultas de búsqueda y filtros

---

### **Fase 8: Gestión de Consentimientos de Medios (Opcional pero Recomendado)**

#### **Paso 13: Mostrar Consentimientos Asociados**
- [ ] En componente Show, añadir sección de consentimientos de medios
- [ ] Listar consentimientos que referencian este documento (`consent_document_id`)
- [ ] Mostrar información básica: tipo, persona, fecha, estado
- [ ] Enlaces a detalles de consentimientos si existe CRUD

---

### **Fase 9: Tests**

#### **Paso 14: Tests de Componentes Livewire**
- [ ] Crear test `tests/Feature/Livewire/Admin/Documents/IndexTest.php`:
  - Test de autorización (solo usuarios con permisos pueden ver)
  - Test de listado con datos
  - Test de búsqueda
  - Test de filtros (categoría, programa, año académico, tipo, estado)
  - Test de ordenación
  - Test de filtro de eliminados
  - Test de creación (redirección)
  - Test de eliminación (SoftDelete)
  - Test de restauración
  - Test de forceDelete (solo super-admin)
  - Test de validación de relaciones antes de eliminar
- [ ] Crear test `tests/Feature/Livewire/Admin/Documents/CreateTest.php`:
  - Test de autorización
  - Test de creación exitosa
  - Test de validación de campos
  - Test de generación automática de slug
  - Test de subida de archivo
  - Test de redirección después de crear
- [ ] Crear test `tests/Feature/Livewire/Admin/Documents/EditTest.php`:
  - Test de autorización
  - Test de carga de datos
  - Test de actualización exitosa
  - Test de validación de campos
  - Test de generación automática de slug
  - Test de reemplazo de archivo
  - Test de eliminación de archivo
  - Test de redirección después de actualizar
- [ ] Crear test `tests/Feature/Livewire/Admin/Documents/ShowTest.php`:
  - Test de autorización
  - Test de visualización de información
  - Test de descarga de archivo
  - Test de eliminación desde Show
- [ ] Ejecutar tests y verificar que pasen

#### **Paso 15: Tests de FormRequests**
- [ ] Actualizar tests existentes de `StoreDocumentRequest`:
  - Test de autorización
  - Test de validación de campos
  - Test de unicidad de `slug`
  - Test de validación de archivo (opcional)
- [ ] Actualizar tests para `UpdateDocumentRequest`:
  - Test de autorización
  - Test de validación de campos
  - Test de unicidad de `slug` (ignorando registro actual)
  - Test de validación de archivo (opcional)

---

### **Fase 10: Mejoras y Pulido**

#### **Paso 16: Mejoras de UX**
- [ ] Añadir indicadores de carga en todas las acciones
- [ ] Añadir mensajes de éxito/error con notificaciones
- [ ] Mejorar preview de archivos (PDFs, imágenes, documentos)
- [ ] Añadir tooltips informativos
- [ ] Mejorar estados vacíos con acciones sugeridas

#### **Paso 17: Validación y Formateo**
- [ ] Ejecutar `vendor/bin/pint --dirty` para formatear código
- [ ] Verificar que no haya errores de linter
- [ ] Verificar que todas las traducciones estén completas
- [ ] Verificar diseño responsive en diferentes dispositivos

#### **Paso 18: Documentación**
- [ ] Crear documentación técnica del CRUD de Documentos
- [ ] Documentar características principales
- [ ] Documentar uso de Media Library
- [ ] Documentar gestión de consentimientos

---

## 📝 Notas Importantes

### SoftDeletes
- Los documentos **nunca** se eliminan permanentemente por defecto
- Solo se marcan como eliminados (`deleted_at`)
- Solo super-admin puede realizar `forceDelete()`
- Antes de `forceDelete()`, validar que no existan relaciones con `MediaConsent`
- Filtrar registros eliminados por defecto en listados
- Opción de ver registros eliminados (solo para administradores)

### Validación de Relaciones
- Antes de eliminar (soft delete), verificar si tiene consentimientos de medios asociados
- Si tiene relaciones, mostrar error y no permitir eliminación
- Mensaje claro al usuario explicando por qué no se puede eliminar

### Gestión de Archivos
- Usar Laravel Media Library para almacenar archivos
- Colección: `file` (single file, ya configurada en modelo)
- Tipos MIME aceptados (según modelo Document):
  - PDF: `application/pdf`
  - Word: `application/msword`, `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
  - Excel: `application/vnd.ms-excel`, `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
  - PowerPoint: `application/vnd.ms-powerpoint`, `application/vnd.openxmlformats-officedocument.presentationml.presentation`
  - Texto: `text/plain`, `text/csv`
  - Imágenes: `image/jpeg`, `image/png`, `image/webp`
- Validar tamaño máximo (configurar en FormRequest, ej: 20MB)
- **Usar FilePond (Spatie Livewire-FilePond)**:
  - Trait `WithFilePond` en componentes Livewire
  - Componente `<x-filepond::upload>` en vistas
  - Preview automático para imágenes y PDFs
  - Validación en frontend (tipo y tamaño)
  - Drag & drop mejorado
  - Indicador de progreso visual
- Opción de reemplazar archivo en edición (eliminar anterior y subir nuevo)

### Generación de Slug
- El slug se genera automáticamente desde el título usando `Str::slug()`
- El usuario puede editar el slug manualmente si lo desea
- Validar que el slug sea único (ignorando el registro actual en edición)

### Tipos de Documento
- `convocatoria`: Documento de convocatoria
- `modelo`: Modelo o plantilla
- `seguro`: Documentación de seguros
- `consentimiento`: Consentimientos RGPD
- `guia`: Guías informativas
- `faq`: Preguntas frecuentes
- `otro`: Otro tipo de documento

### Diseño y UX
- Usar Flux UI components para mantener consistencia
- Diseño responsive (móvil, tablet, desktop)
- Loading states en todas las acciones
- Feedback visual en validaciones
- Modales de confirmación para acciones destructivas
- Mensajes de éxito/error claros
- Preview de archivos cuando sea posible

### Autorización
- Usar `DocumentPolicy` para todas las acciones
- Verificar permisos en cada método
- El rol `super-admin` tiene acceso total (definido en `before()` del Policy)

---

## 🎨 Componentes Reutilizables

Se pueden reutilizar los siguientes componentes existentes:
- `x-ui.card` - Tarjetas contenedoras
- `x-ui.breadcrumbs` - Breadcrumbs de navegación
- `x-ui.search-input` - Campo de búsqueda
- `x-ui.empty-state` - Estado vacío
- `flux:button` - Botones con variantes
- `flux:field` - Campos de formulario
- `flux:input` - Inputs
- `flux:label` - Etiquetas
- `flux:select` - Selects
- `flux:textarea` - Textareas
- `flux:switch` - Switches
- `flux:badge` - Badges para estados
- `flux:modal` - Modales de confirmación
- `x-filepond::upload` - Componente de subida de archivos (Spatie Livewire-FilePond, ya configurado)

---

## ✅ Checklist Final

Antes de considerar completado el paso 3.5.7, verificar:

- [ ] SoftDeletes implementado en modelo Document
- [ ] Migración ejecutada correctamente
- [ ] FormRequests actualizados con autorización y mensajes
- [ ] Componente Index funcionando con todos los filtros y acciones
- [ ] Componente Create funcionando con validación en tiempo real y subida de archivos
- [ ] Componente Edit funcionando con validación en tiempo real y gestión de archivos
- [ ] Componente Show funcionando con información completa
- [ ] Rutas configuradas correctamente
- [ ] Navegación actualizada
- [ ] Traducciones añadidas
- [ ] Validación de relaciones antes de eliminar
- [ ] Gestión de archivos con Media Library funcionando
- [ ] Preview de archivos implementado
- [ ] Tests completos y pasando
- [ ] Código formateado con Pint
- [ ] Sin errores de linter
- [ ] Diseño responsive verificado
- [ ] Autorización verificada en todas las acciones

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan detallado completado - Listo para implementación

