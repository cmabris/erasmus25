# Plan de Desarrollo: Paso 3.5.8 - Gestión de Categorías de Documentos en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Categorías de Documentos en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Categorías de Documentos en el panel de administración con:
- Listado moderno con tabla interactiva
- Formularios de creación y edición
- Vista de documentos asociados
- **SoftDeletes**: Las categorías nunca se eliminan permanentemente, solo se marcan como eliminadas
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones con documentos
- Gestión de orden (`order`) para controlar la visualización
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos de Desarrollo (12 Pasos)

### **Fase 1: Preparación Base**

#### **Paso 1: Implementar SoftDeletes en DocumentCategory**
- [ ] Crear migración para añadir columna `deleted_at` a la tabla `document_categories`
- [ ] Ejecutar migración
- [ ] Actualizar modelo `DocumentCategory` para usar el trait `SoftDeletes`
- [ ] Verificar que las relaciones funcionen correctamente con SoftDeletes
- [ ] Actualizar factory si es necesario

#### **Paso 2: Crear/Actualizar FormRequests**
- [ ] Actualizar `StoreDocumentCategoryRequest`:
  - Añadir autorización con `DocumentCategoryPolicy::create()`
  - Añadir mensajes de error personalizados en español e inglés
  - Verificar validación de `name` y `slug` únicos
  - Validar campo `order` (opcional, integer)
  - Validar campo `description` (opcional, string)
- [ ] Crear `UpdateDocumentCategoryRequest`:
  - Añadir autorización con `DocumentCategoryPolicy::update()`
  - Añadir mensajes de error personalizados
  - Validación de `name` y `slug` únicos (ignorando el registro actual)
  - Validar campo `order` (opcional, integer)
  - Validar campo `description` (opcional, string)
- [ ] Verificar que `DocumentCategoryPolicy` tenga todos los métodos necesarios (ya existe)

---

### **Fase 2: Estructura Base y Listado** (MVP)

#### **Paso 3: Componente Index (Listado)**
- [ ] Crear componente Livewire `Admin\DocumentCategories\Index`
- [ ] Implementar propiedades públicas:
  - `Collection $documentCategories` - Lista de categorías (computed)
  - `string $search = ''` - Búsqueda (con `#[Url]`)
  - `string $sortField = 'order'` - Campo de ordenación (con `#[Url]`, por defecto 'order')
  - `string $sortDirection = 'asc'` - Dirección de ordenación (con `#[Url]`)
  - `string $showDeleted = '0'` - Filtro de eliminados (con `#[Url]`)
  - `int $perPage = 15` - Elementos por página (con `#[Url]`)
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `?int $documentCategoryToDelete = null` - ID de categoría a eliminar
  - `bool $showRestoreModal = false` - Modal de confirmación de restauración
  - `?int $documentCategoryToRestore = null` - ID de categoría a restaurar
  - `bool $showForceDeleteModal = false` - Modal de confirmación de eliminación permanente
  - `?int $documentCategoryToForceDelete = null` - ID de categoría a eliminar permanentemente
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `documentCategories()` - Computed property con paginación, filtros y ordenación
  - `sortBy($field)` - Ordenación
  - `confirmDelete($documentCategoryId)` - Confirmar eliminación
  - `delete()` - Eliminar con SoftDeletes (validar relaciones)
  - `confirmRestore($documentCategoryId)` - Confirmar restauración
  - `restore()` - Restaurar categoría eliminada
  - `confirmForceDelete($documentCategoryId)` - Confirmar eliminación permanente
  - `forceDelete()` - Eliminar permanentemente (solo super-admin, validar relaciones)
  - `resetFilters()` - Resetear filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedShowDeleted()` - Resetear página al cambiar filtro
  - `canCreate()` - Verificar si puede crear
  - `canViewDeleted()` - Verificar si puede ver eliminados
  - `canDeleteDocumentCategory($documentCategory)` - Verificar si puede eliminar (sin relaciones)
  - `render()` - Renderizado con paginación
- [ ] Implementar autorización con `DocumentCategoryPolicy`
- [ ] Crear vista `livewire/admin/document-categories/index.blade.php`:
  - Header con título y botón crear
  - Breadcrumbs
  - Filtros: búsqueda, mostrar eliminados, reset
  - Tabla responsive con columnas: orden, nombre, slug, descripción, documentos asociados (count), fecha creación, acciones
  - Modales de confirmación (eliminar, restaurar, force delete)
  - Paginación
  - Estado vacío
  - Loading states
  - Botones para reordenar (mover arriba/abajo) - opcional pero recomendado

---

### **Fase 3: Creación y Edición**

#### **Paso 4: Componente Create (Crear)**
- [ ] Crear componente Livewire `Admin\DocumentCategories\Create`
- [ ] Implementar propiedades públicas:
  - `string $name = ''` - Nombre de la categoría
  - `string $slug = ''` - Slug de la categoría
  - `?string $description = null` - Descripción de la categoría
  - `?int $order = null` - Orden de visualización
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `updatedName()` - Generar slug automáticamente desde nombre
  - `updatedSlug()` - Validar slug en tiempo real
  - `store()` - Guardar nueva categoría usando `StoreDocumentCategoryRequest`
- [ ] Crear vista `livewire/admin/document-categories/create.blade.php`:
  - Header con título y breadcrumbs
  - Formulario con Flux UI:
    - Campo nombre (requerido, validación en tiempo real)
    - Campo slug (opcional, se genera automáticamente, editable)
    - Campo descripción (opcional, textarea)
    - Campo orden (opcional, integer)
    - Botones: guardar y cancelar
  - Validación visual en tiempo real
  - Mensajes de error

#### **Paso 5: Componente Edit (Editar)**
- [ ] Crear componente Livewire `Admin\DocumentCategories\Edit`
- [ ] Implementar propiedades públicas:
  - `DocumentCategory $documentCategory` - Categoría a editar
  - `string $name = ''` - Nombre de la categoría
  - `string $slug = ''` - Slug de la categoría
  - `?string $description = null` - Descripción de la categoría
  - `?int $order = null` - Orden de visualización
- [ ] Implementar métodos:
  - `mount(DocumentCategory $document_category)` - Cargar datos de la categoría
  - `updatedName()` - Generar slug automáticamente desde nombre
  - `updatedSlug()` - Validar slug en tiempo real
  - `update()` - Actualizar categoría usando `UpdateDocumentCategoryRequest`
- [ ] Crear vista `livewire/admin/document-categories/edit.blade.php`:
  - Similar a Create pero con datos precargados
  - Mostrar información adicional: fecha creación, fecha actualización, número de documentos asociados

---

### **Fase 4: Rutas y Navegación**

#### **Paso 6: Configurar Rutas**
- [ ] Añadir rutas en `routes/web.php` dentro del grupo `admin`:
  - `GET /admin/categorias` → `Admin\DocumentCategories\Index` (nombre: `admin.document-categories.index`)
  - `GET /admin/categorias/crear` → `Admin\DocumentCategories\Create` (nombre: `admin.document-categories.create`)
  - `GET /admin/categorias/{document_category}` → `Admin\DocumentCategories\Show` (nombre: `admin.document-categories.show`) - Opcional
  - `GET /admin/categorias/{document_category}/editar` → `Admin\DocumentCategories\Edit` (nombre: `admin.document-categories.edit`)
- [ ] Verificar que las rutas usen el middleware correcto (`auth`, `verified`)

#### **Paso 7: Actualizar Navegación**
- [ ] Añadir enlace en sidebar de administración (si existe)
- [ ] Añadir traducciones necesarias en `lang/es/common.php` y `lang/en/common.php`:
  - `Categorías de Documentos` / `Document Categories`
  - `Crear Categoría` / `Create Category`
  - `Editar Categoría` / `Edit Category`
  - Mensajes de éxito/error relacionados

---

### **Fase 5: Vista Detalle (Opcional pero Recomendado)**

#### **Paso 8: Componente Show (Detalle)**
- [ ] Crear componente Livewire `Admin\DocumentCategories\Show`
- [ ] Implementar propiedades públicas:
  - `DocumentCategory $documentCategory` - Categoría a mostrar
- [ ] Implementar métodos:
  - `mount(DocumentCategory $document_category)` - Cargar categoría con relaciones
  - `delete()` - Eliminar (redirigir a Index)
  - `restore()` - Restaurar (si está eliminada)
  - `forceDelete()` - Eliminar permanentemente
  - `render()` - Renderizado
- [ ] Crear vista `livewire/admin/document-categories/show.blade.php`:
  - Información completa de la categoría
  - Listado de documentos asociados (con enlaces)
  - Estadísticas: total de documentos, fecha creación, fecha actualización
  - Botones de acción: editar, eliminar, restaurar, volver

---

### **Fase 6: Validación de Relaciones y Optimizaciones**

#### **Paso 9: Validar Relaciones Antes de Eliminar**
- [ ] En método `delete()` del componente Index:
  - Verificar si la categoría tiene documentos asociados
  - Si tiene relaciones, mostrar error y no eliminar
  - Mensaje: "No se puede eliminar la categoría porque tiene documentos asociados"
- [ ] En método `forceDelete()`:
  - Verificar relaciones antes de eliminar permanentemente
  - Solo permitir si no hay relaciones
  - Mensaje de error si intenta eliminar con relaciones

#### **Paso 10: Optimizaciones**
- [ ] Añadir `withCount(['documents'])` en consulta de Index para evitar N+1
- [ ] Añadir índices en base de datos si es necesario (verificar índices para `name`, `slug`, `order`)
- [ ] Verificar eager loading en relaciones
- [ ] Considerar caché para categorías ordenadas si se usan frecuentemente

---

### **Fase 7: Tests**

#### **Paso 11: Tests de Componentes Livewire**
- [ ] Crear test `tests/Feature/Livewire/Admin/DocumentCategories/IndexTest.php`:
  - Test de autorización (solo usuarios con permisos pueden ver)
  - Test de listado con datos
  - Test de búsqueda
  - Test de ordenación
  - Test de filtro de eliminados
  - Test de creación (redirección)
  - Test de eliminación (SoftDelete)
  - Test de restauración
  - Test de forceDelete (solo super-admin)
  - Test de validación de relaciones antes de eliminar
- [ ] Crear test `tests/Feature/Livewire/Admin/DocumentCategories/CreateTest.php`:
  - Test de autorización
  - Test de creación exitosa
  - Test de validación de campos
  - Test de generación automática de slug
  - Test de redirección después de crear
- [ ] Crear test `tests/Feature/Livewire/Admin/DocumentCategories/EditTest.php`:
  - Test de autorización
  - Test de carga de datos
  - Test de actualización exitosa
  - Test de validación de campos
  - Test de generación automática de slug
  - Test de redirección después de actualizar
- [ ] Crear test `tests/Feature/Livewire/Admin/DocumentCategories/ShowTest.php`:
  - Test de autorización
  - Test de visualización de categoría
  - Test de visualización de documentos asociados
  - Test de eliminación desde Show
- [ ] Ejecutar tests y verificar que pasen

#### **Paso 12: Tests de FormRequests**
- [ ] Actualizar tests existentes de `StoreDocumentCategoryRequest`:
  - Test de autorización
  - Test de validación de campos
  - Test de unicidad de `name`
  - Test de unicidad de `slug`
- [ ] Crear tests para `UpdateDocumentCategoryRequest`:
  - Test de autorización
  - Test de validación de campos
  - Test de unicidad de `name` (ignorando registro actual)
  - Test de unicidad de `slug` (ignorando registro actual)

---

## 📝 Notas Importantes

### SoftDeletes
- Las categorías **nunca** se eliminan permanentemente por defecto
- Solo se marcan como eliminadas (`deleted_at`)
- Solo super-admin puede realizar `forceDelete()`
- Antes de `forceDelete()`, validar que no existan relaciones con documentos
- Filtrar registros eliminados por defecto en listados
- Opción de ver registros eliminados (solo para administradores)

### Validación de Relaciones
- Antes de eliminar (soft delete), verificar si tiene documentos asociados
- Si tiene relaciones, mostrar error y no permitir eliminación
- Mensaje claro al usuario explicando por qué no se puede eliminar

### Generación de Slug
- El slug se genera automáticamente desde el nombre usando `Str::slug()`
- El usuario puede editar el slug manualmente si lo desea
- Validar que el slug sea único (ignorando el registro actual en edición)

### Campo Order
- El campo `order` permite controlar el orden de visualización de las categorías
- Es opcional (puede ser null)
- Se puede usar para ordenar las categorías en listados y formularios
- Considerar añadir funcionalidad de reordenamiento (mover arriba/abajo) en el Index

### Diseño y UX
- Usar Flux UI components para mantener consistencia
- Diseño responsive (móvil, tablet, desktop)
- Loading states en todas las acciones
- Feedback visual en validaciones
- Modales de confirmación para acciones destructivas
- Mensajes de éxito/error claros

### Autorización
- Usar `DocumentCategoryPolicy` para todas las acciones
- Verificar permisos en cada método
- El rol `super-admin` tiene acceso total (definido en `before()` del Policy)
- Las categorías usan los permisos del módulo `documents.*`

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
- `flux:textarea` - Textarea para descripción
- `flux:label` - Etiquetas
- `flux:badge` - Badges para estados
- `flux:modal` - Modales de confirmación

---

## ✅ Checklist Final

Antes de considerar completado el paso 3.5.8, verificar:

- [ ] SoftDeletes implementado en modelo DocumentCategory
- [ ] Migración ejecutada correctamente
- [ ] FormRequests creados/actualizados con autorización y mensajes
- [ ] Componente Index funcionando con todos los filtros y acciones
- [ ] Componente Create funcionando con validación en tiempo real
- [ ] Componente Edit funcionando con validación en tiempo real
- [ ] Componente Show funcionando (opcional)
- [ ] Rutas configuradas correctamente
- [ ] Navegación actualizada
- [ ] Traducciones añadidas
- [ ] Validación de relaciones antes de eliminar
- [ ] Tests completos y pasando
- [ ] Código formateado con Pint
- [ ] Sin errores de linter
- [ ] Diseño responsive verificado
- [ ] Autorización verificada en todas las acciones

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan detallado completado - Listo para implementación

