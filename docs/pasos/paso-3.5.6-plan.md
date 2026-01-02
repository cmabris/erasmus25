# Plan de Desarrollo: Paso 3.5.6 - Gestión de Etiquetas de Noticias en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Etiquetas de Noticias en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Etiquetas de Noticias en el panel de administración con:
- Listado moderno con tabla interactiva
- Formularios de creación y edición
- Vista de noticias asociadas
- **SoftDeletes**: Las etiquetas nunca se eliminan permanentemente, solo se marcan como eliminadas
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones con noticias
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos de Desarrollo (12 Pasos)

### **Fase 1: Preparación Base**

#### **Paso 1: Implementar SoftDeletes en NewsTag**
- [ ] Crear migración para añadir columna `deleted_at` a la tabla `news_tags`
- [ ] Ejecutar migración
- [ ] Actualizar modelo `NewsTag` para usar el trait `SoftDeletes`
- [ ] Verificar que las relaciones funcionen correctamente con SoftDeletes
- [ ] Actualizar factory si es necesario

#### **Paso 2: Crear/Actualizar FormRequests**
- [ ] Verificar `StoreNewsTagRequest`:
  - Ya tiene autorización con `NewsTagPolicy::create()`
  - Añadir mensajes de error personalizados en español e inglés
  - Verificar validación de `name` y `slug` únicos
- [ ] Crear `UpdateNewsTagRequest`:
  - Añadir autorización con `NewsTagPolicy::update()`
  - Añadir mensajes de error personalizados
  - Validación de `name` y `slug` únicos (ignorando el registro actual)
- [ ] Verificar que `NewsTagPolicy` tenga todos los métodos necesarios (ya existe)

---

### **Fase 2: Estructura Base y Listado** (MVP)

#### **Paso 3: Componente Index (Listado)**
- [ ] Crear componente Livewire `Admin\NewsTags\Index`
- [ ] Implementar propiedades públicas:
  - `Collection $newsTags` - Lista de etiquetas (computed)
  - `string $search = ''` - Búsqueda (con `#[Url]`)
  - `string $sortField = 'name'` - Campo de ordenación (con `#[Url]`)
  - `string $sortDirection = 'asc'` - Dirección de ordenación (con `#[Url]`)
  - `string $showDeleted = '0'` - Filtro de eliminados (con `#[Url]`)
  - `int $perPage = 15` - Elementos por página (con `#[Url]`)
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `?int $newsTagToDelete = null` - ID de etiqueta a eliminar
  - `bool $showRestoreModal = false` - Modal de confirmación de restauración
  - `?int $newsTagToRestore = null` - ID de etiqueta a restaurar
  - `bool $showForceDeleteModal = false` - Modal de confirmación de eliminación permanente
  - `?int $newsTagToForceDelete = null` - ID de etiqueta a eliminar permanentemente
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `newsTags()` - Computed property con paginación, filtros y ordenación
  - `sortBy($field)` - Ordenación
  - `confirmDelete($newsTagId)` - Confirmar eliminación
  - `delete()` - Eliminar con SoftDeletes (validar relaciones)
  - `confirmRestore($newsTagId)` - Confirmar restauración
  - `restore()` - Restaurar etiqueta eliminada
  - `confirmForceDelete($newsTagId)` - Confirmar eliminación permanente
  - `forceDelete()` - Eliminar permanentemente (solo super-admin, validar relaciones)
  - `resetFilters()` - Resetear filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedShowDeleted()` - Resetear página al cambiar filtro
  - `canCreate()` - Verificar si puede crear
  - `canViewDeleted()` - Verificar si puede ver eliminados
  - `canDeleteNewsTag($newsTag)` - Verificar si puede eliminar (sin relaciones)
  - `render()` - Renderizado con paginación
- [ ] Implementar autorización con `NewsTagPolicy`
- [ ] Crear vista `livewire/admin/news-tags/index.blade.php`:
  - Header con título y botón crear
  - Breadcrumbs
  - Filtros: búsqueda, mostrar eliminados, reset
  - Tabla responsive con columnas: nombre, slug, noticias asociadas (count), fecha creación, acciones
  - Modales de confirmación (eliminar, restaurar, force delete)
  - Paginación
  - Estado vacío
  - Loading states

---

### **Fase 3: Creación y Edición**

#### **Paso 4: Componente Create (Crear)**
- [ ] Crear componente Livewire `Admin\NewsTags\Create`
- [ ] Implementar propiedades públicas:
  - `string $name = ''` - Nombre de la etiqueta
  - `string $slug = ''` - Slug de la etiqueta
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `updatedName()` - Generar slug automáticamente desde nombre
  - `updatedSlug()` - Validar slug en tiempo real
  - `store()` - Guardar nueva etiqueta usando `StoreNewsTagRequest`
- [ ] Crear vista `livewire/admin/news-tags/create.blade.php`:
  - Header con título y breadcrumbs
  - Formulario con Flux UI:
    - Campo nombre (requerido, validación en tiempo real)
    - Campo slug (opcional, se genera automáticamente, editable)
    - Botones: guardar y cancelar
  - Validación visual en tiempo real
  - Mensajes de error

#### **Paso 5: Componente Edit (Editar)**
- [ ] Crear componente Livewire `Admin\NewsTags\Edit`
- [ ] Implementar propiedades públicas:
  - `NewsTag $newsTag` - Etiqueta a editar
  - `string $name = ''` - Nombre de la etiqueta
  - `string $slug = ''` - Slug de la etiqueta
- [ ] Implementar métodos:
  - `mount(NewsTag $news_tag)` - Cargar datos de la etiqueta
  - `updatedName()` - Generar slug automáticamente desde nombre
  - `updatedSlug()` - Validar slug en tiempo real
  - `update()` - Actualizar etiqueta usando `UpdateNewsTagRequest`
- [ ] Crear vista `livewire/admin/news-tags/edit.blade.php`:
  - Similar a Create pero con datos precargados
  - Mostrar información adicional: fecha creación, fecha actualización, número de noticias asociadas

---

### **Fase 4: Rutas y Navegación**

#### **Paso 6: Configurar Rutas**
- [ ] Añadir rutas en `routes/web.php` dentro del grupo `admin`:
  - `GET /admin/etiquetas` → `Admin\NewsTags\Index` (nombre: `admin.news-tags.index`)
  - `GET /admin/etiquetas/crear` → `Admin\NewsTags\Create` (nombre: `admin.news-tags.create`)
  - `GET /admin/etiquetas/{news_tag}` → `Admin\NewsTags\Show` (nombre: `admin.news-tags.show`) - Opcional
  - `GET /admin/etiquetas/{news_tag}/editar` → `Admin\NewsTags\Edit` (nombre: `admin.news-tags.edit`)
- [ ] Verificar que las rutas usen el middleware correcto (`auth`, `verified`)

#### **Paso 7: Actualizar Navegación**
- [ ] Añadir enlace en sidebar de administración (si existe)
- [ ] Añadir traducciones necesarias en `lang/es/common.php` y `lang/en/common.php`:
  - `Etiquetas de Noticias` / `News Tags`
  - `Crear Etiqueta` / `Create Tag`
  - `Editar Etiqueta` / `Edit Tag`
  - Mensajes de éxito/error relacionados

---

### **Fase 5: Vista Detalle (Opcional pero Recomendado)**

#### **Paso 8: Componente Show (Detalle)**
- [ ] Crear componente Livewire `Admin\NewsTags\Show`
- [ ] Implementar propiedades públicas:
  - `NewsTag $newsTag` - Etiqueta a mostrar
- [ ] Implementar métodos:
  - `mount(NewsTag $news_tag)` - Cargar etiqueta con relaciones
  - `delete()` - Eliminar (redirigir a Index)
  - `render()` - Renderizado
- [ ] Crear vista `livewire/admin/news-tags/show.blade.php`:
  - Información completa de la etiqueta
  - Listado de noticias asociadas (con enlaces)
  - Estadísticas: total de noticias, fecha creación, fecha actualización
  - Botones de acción: editar, eliminar, volver

---

### **Fase 6: Validación de Relaciones y Optimizaciones**

#### **Paso 9: Validar Relaciones Antes de Eliminar**
- [ ] En método `delete()` del componente Index:
  - Verificar si la etiqueta tiene noticias asociadas
  - Si tiene relaciones, mostrar error y no eliminar
  - Mensaje: "No se puede eliminar la etiqueta porque tiene noticias asociadas"
- [ ] En método `forceDelete()`:
  - Verificar relaciones antes de eliminar permanentemente
  - Solo permitir si no hay relaciones
  - Mensaje de error si intenta eliminar con relaciones

#### **Paso 10: Optimizaciones**
- [ ] Añadir `withCount(['newsPosts'])` en consulta de Index para evitar N+1
- [ ] Añadir índices en base de datos si es necesario (ya existen para `name` y `slug`)
- [ ] Verificar eager loading en relaciones

---

### **Fase 7: Tests**

#### **Paso 11: Tests de Componentes Livewire**
- [ ] Crear test `tests/Feature/Livewire/Admin/NewsTags/IndexTest.php`:
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
- [ ] Crear test `tests/Feature/Livewire/Admin/NewsTags/CreateTest.php`:
  - Test de autorización
  - Test de creación exitosa
  - Test de validación de campos
  - Test de generación automática de slug
  - Test de redirección después de crear
- [ ] Crear test `tests/Feature/Livewire/Admin/NewsTags/EditTest.php`:
  - Test de autorización
  - Test de carga de datos
  - Test de actualización exitosa
  - Test de validación de campos
  - Test de generación automática de slug
  - Test de redirección después de actualizar
- [ ] Ejecutar tests y verificar que pasen

#### **Paso 12: Tests de FormRequests**
- [ ] Verificar tests existentes de `StoreNewsTagRequest`
- [ ] Crear tests para `UpdateNewsTagRequest`:
  - Test de autorización
  - Test de validación de campos
  - Test de unicidad de `name` (ignorando registro actual)
  - Test de unicidad de `slug` (ignorando registro actual)

---

## 📝 Notas Importantes

### SoftDeletes
- Las etiquetas **nunca** se eliminan permanentemente por defecto
- Solo se marcan como eliminadas (`deleted_at`)
- Solo super-admin puede realizar `forceDelete()`
- Antes de `forceDelete()`, validar que no existan relaciones con noticias
- Filtrar registros eliminados por defecto en listados
- Opción de ver registros eliminados (solo para administradores)

### Validación de Relaciones
- Antes de eliminar (soft delete), verificar si tiene noticias asociadas
- Si tiene relaciones, mostrar error y no permitir eliminación
- Mensaje claro al usuario explicando por qué no se puede eliminar

### Generación de Slug
- El slug se genera automáticamente desde el nombre usando `Str::slug()`
- El usuario puede editar el slug manualmente si lo desea
- Validar que el slug sea único (ignorando el registro actual en edición)

### Diseño y UX
- Usar Flux UI components para mantener consistencia
- Diseño responsive (móvil, tablet, desktop)
- Loading states en todas las acciones
- Feedback visual en validaciones
- Modales de confirmación para acciones destructivas
- Mensajes de éxito/error claros

### Autorización
- Usar `NewsTagPolicy` para todas las acciones
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
- `flux:badge` - Badges para estados
- `flux:modal` - Modales de confirmación

---

## ✅ Checklist Final

Antes de considerar completado el paso 3.5.6, verificar:

- [ ] SoftDeletes implementado en modelo NewsTag
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

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan detallado completado - Listo para implementación


