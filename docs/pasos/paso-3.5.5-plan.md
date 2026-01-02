# Plan Detallado: Paso 3.5.5 - CRUD de Gestión de Noticias en Panel de Administración

## 🎯 Objetivo

Desarrollar un sistema completo de gestión (CRUD) de Noticias en el panel de administración con:
- Listado moderno con tabla interactiva y filtros avanzados
- Formularios de creación y edición con editor de contenido enriquecido
- Vista de detalle con información completa
- Funcionalidades avanzadas: publicar/despublicar, gestión de etiquetas (many-to-many), subir imágenes destacadas, gestión de traducciones
- **SoftDeletes**: Las noticias nunca se eliminan permanentemente, solo se marcan como eliminadas
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos Principales (18 Pasos)

**Nota sobre el orden de desarrollo:**
Este plan está organizado para completar primero el CRUD completo con textarea simple, y luego integrar Tiptap al final. Esto permite:
- Desarrollar y probar el CRUD completo más rápido
- No bloquearse si hay problemas con Tiptap
- Separar la lógica de negocio de la mejora del editor
- Integrar Tiptap como mejora final una vez que todo funciona

### ✅ **Fase 1: Preparación y Estructura Base**

#### Paso 1: Implementar SoftDeletes en el modelo NewsPost
**Objetivo**: Añadir SoftDeletes al modelo NewsPost para que las noticias no se eliminen permanentemente.

**Tareas**:
- [ ] Añadir `use SoftDeletes` al modelo `NewsPost`
- [ ] Añadir `deleted_at` al array `$fillable` si es necesario (no, es automático)
- [ ] Verificar que la migración ya tiene la columna `deleted_at` (si no, crear migración)
- [ ] Actualizar relaciones para incluir `withTrashed()` cuando sea necesario

**Archivos a modificar**:
- `app/Models/NewsPost.php`

**Verificación**:
- Verificar que el modelo puede hacer soft delete y restore
- Verificar que las relaciones funcionan correctamente con soft deletes

---

#### Paso 2: Adaptar FormRequests existentes
**Objetivo**: Actualizar los FormRequests para incluir validación de imágenes, etiquetas y autorización.

**Tareas**:
- [ ] Actualizar `StoreNewsPostRequest`:
  - [ ] Añadir validación de imagen destacada (`featured_image`)
  - [ ] Añadir validación de etiquetas (`tags` - array de IDs)
  - [ ] Añadir autorización usando `NewsPostPolicy`
  - [ ] Añadir mensajes de error personalizados
- [ ] Actualizar `UpdateNewsPostRequest`:
  - [ ] Añadir validación de imagen destacada (opcional en update)
  - [ ] Añadir validación de etiquetas (`tags` - array de IDs)
  - [ ] Añadir autorización usando `NewsPostPolicy`
  - [ ] Añadir mensajes de error personalizados

**Archivos a modificar**:
- `app/Http/Requests/StoreNewsPostRequest.php`
- `app/Http/Requests/UpdateNewsPostRequest.php`

**Verificación**:
- Verificar que las validaciones funcionan correctamente
- Verificar que la autorización se aplica correctamente

---

### ✅ **Fase 2: Componente Index (Listado)**

#### Paso 3: Crear componente Livewire Admin\News\Index
**Objetivo**: Crear el componente de listado con tabla interactiva, búsqueda, filtros y paginación.

**Tareas**:
- [ ] Crear clase `App\Livewire\Admin\News\Index`
- [ ] Implementar propiedades públicas:
  - [ ] `$search` (búsqueda por título, excerpt, contenido)
  - [ ] `$showDeleted` (filtrar eliminados: '0' o '1')
  - [ ] `$programFilter` (filtro por programa)
  - [ ] `$academicYearFilter` (filtro por año académico)
  - [ ] `$statusFilter` (filtro por estado: borrador, en_revision, publicado, archivado)
  - [ ] `$sortField` (campo de ordenación)
  - [ ] `$sortDirection` (dirección: asc/desc)
  - [ ] `$perPage` (elementos por página)
  - [ ] Modales de confirmación (delete, restore, forceDelete)
- [ ] Implementar métodos:
  - [ ] `mount()` - Autorización con `NewsPostPolicy::viewAny()`
  - [ ] `newsPosts()` (computed) - Query con filtros, búsqueda, ordenación y paginación
  - [ ] `sortBy()` - Cambiar ordenación
  - [ ] `confirmDelete()` - Confirmar eliminación (soft delete)
  - [ ] `delete()` - Eliminar noticia (soft delete con validación de relaciones)
  - [ ] `confirmRestore()` - Confirmar restauración
  - [ ] `restore()` - Restaurar noticia eliminada
  - [ ] `confirmForceDelete()` - Confirmar eliminación permanente
  - [ ] `forceDelete()` - Eliminar permanentemente (solo super-admin, validar relaciones)
  - [ ] `publish()` - Publicar noticia (cambiar estado y establecer `published_at`)
  - [ ] `unpublish()` - Despublicar noticia
  - [ ] `resetFilters()` - Resetear filtros
  - [ ] `updatedSearch()` - Resetear página al buscar
  - [ ] `canCreate()`, `canViewDeleted()`, `canDeleteNewsPost()` - Métodos de autorización
- [ ] Implementar eager loading para optimizar consultas:
  - [ ] `with(['program', 'academicYear', 'author', 'tags'])`
  - [ ] `withCount(['tags'])` para contar etiquetas

**Archivos a crear**:
- `app/Livewire/Admin/News/Index.php`

**Verificación**:
- Verificar que el listado muestra todas las noticias correctamente
- Verificar que los filtros funcionan
- Verificar que la búsqueda funciona
- Verificar que la paginación funciona

---

#### Paso 4: Crear vista del componente Index
**Objetivo**: Crear la vista Blade con tabla responsive, filtros y acciones.

**Tareas**:
- [ ] Crear `resources/views/livewire/admin/news/index.blade.php`
- [ ] Implementar header con título, descripción y botón "Crear Noticia"
- [ ] Implementar breadcrumbs
- [ ] Implementar sección de filtros:
  - [ ] Búsqueda (input con wire:model.live.debounce)
  - [ ] Filtro por programa (select)
  - [ ] Filtro por año académico (select)
  - [ ] Filtro por estado (select)
  - [ ] Filtro "Mostrar eliminados" (solo si tiene permisos)
  - [ ] Botón "Resetear filtros"
- [ ] Implementar tabla responsive:
  - [ ] Columnas: Imagen destacada (thumbnail), Título, Programa, Año Académico, Estado, Etiquetas, Autor, Fecha publicación, Acciones
  - [ ] Ordenación por columnas (click en header)
  - [ ] Badges para estados (con colores según estado)
  - [ ] Badges para etiquetas
  - [ ] Imagen destacada con thumbnail (si existe)
  - [ ] Botones de acción: Ver, Editar, Eliminar, Restaurar, Publicar/Despublicar
- [ ] Implementar modales de confirmación:
  - [ ] Modal de confirmación de eliminación
  - [ ] Modal de confirmación de restauración
  - [ ] Modal de confirmación de eliminación permanente
- [ ] Implementar estado vacío (cuando no hay noticias)
- [ ] Implementar loading states con `wire:loading`
- [ ] Implementar notificaciones con `wire:listen` para eventos de éxito/error
- [ ] Usar componentes Flux UI: `flux:button`, `flux:field`, `flux:badge`, `flux:modal`
- [ ] Usar componentes reutilizables: `x-ui.card`, `x-ui.search-input`, `x-ui.empty-state`

**Archivos a crear**:
- `resources/views/livewire/admin/news/index.blade.php`

**Verificación**:
- Verificar que la vista se renderiza correctamente
- Verificar que los filtros funcionan
- Verificar que las acciones funcionan
- Verificar que es responsive

---

#### Paso 5: Configurar rutas y navegación
**Objetivo**: Añadir rutas para el CRUD de noticias y actualizar la navegación del panel de administración.

**Tareas**:
- [ ] Añadir rutas en `routes/web.php`:
  - [ ] `GET /admin/noticias` → `Admin\News\Index` (nombre: `admin.news.index`)
  - [ ] `GET /admin/noticias/crear` → `Admin\News\Create` (nombre: `admin.news.create`)
  - [ ] `GET /admin/noticias/{news_post}` → `Admin\News\Show` (nombre: `admin.news.show`)
  - [ ] `GET /admin/noticias/{news_post}/editar` → `Admin\News\Edit` (nombre: `admin.news.edit`)
- [ ] Actualizar sidebar de administración para incluir enlace a "Noticias"
- [ ] Añadir traducciones necesarias en archivos de idioma

**Archivos a modificar**:
- `routes/web.php`
- `resources/views/components/layouts/admin-sidebar.blade.php` (o similar)
- `lang/es/common.php` y `lang/en/common.php` (si es necesario)

**Verificación**:
- Verificar que las rutas funcionan correctamente
- Verificar que la navegación muestra el enlace correctamente

---

### ✅ **Fase 3: Componente Create (Crear)**

#### Paso 6: Crear componente Livewire Admin\News\Create
**Objetivo**: Crear el componente para crear nuevas noticias con formulario completo.

**Tareas**:
- [ ] Crear clase `App\Livewire\Admin\News\Create`
- [ ] Implementar propiedades públicas:
  - [ ] Campos del formulario: `program_id`, `academic_year_id`, `title`, `slug`, `excerpt`, `content`, `country`, `city`, `host_entity`, `mobility_type`, `mobility_category`, `status`, `published_at`
  - [ ] `selectedTags` (array de IDs de etiquetas seleccionadas)
  - [ ] `availableTags` (computed - todas las etiquetas disponibles)
  - [ ] `featuredImage` (temporal para preview)
  - [ ] `featuredImageUrl` (URL temporal para preview)
- [ ] Implementar métodos:
  - [ ] `mount()` - Autorización con `NewsPostPolicy::create()`
  - [ ] `updatedTitle()` - Generar slug automáticamente cuando cambia el título
  - [ ] `updatedSlug()` - Validar slug en tiempo real
  - [ ] `updatedFeaturedImage()` - Manejar subida de imagen y preview
  - [ ] `removeFeaturedImage()` - Eliminar imagen temporal
  - [ ] `store()` - Validar y crear noticia:
    - [ ] Validar con `StoreNewsPostRequest`
    - [ ] Establecer `author_id` automáticamente al usuario actual
    - [ ] Crear noticia
    - [ ] Sincronizar etiquetas (`sync()`)
    - [ ] Subir imagen destacada si existe
    - [ ] Redirigir a `admin.news.show` con mensaje de éxito
- [ ] Implementar validación en tiempo real para campos clave

**Archivos a crear**:
- `app/Livewire/Admin/News/Create.php`

**Verificación**:
- Verificar que se puede crear una noticia correctamente
- Verificar que las etiquetas se asocian correctamente
- Verificar que la imagen se sube correctamente

---

#### Paso 7: Crear vista del componente Create
**Objetivo**: Crear el formulario de creación con todos los campos. **Nota**: Por ahora usaremos textarea simple para el contenido. Tiptap se integrará al final (Paso 18).

**Tareas**:
- [ ] Crear `resources/views/livewire/admin/news/create.blade.php`
- [ ] Implementar header con título y breadcrumbs
- [ ] Implementar formulario con secciones:
  - [ ] **Información básica**:
    - [ ] Programa (select, opcional)
    - [ ] Año académico (select, requerido)
    - [ ] Título (input, requerido)
    - [ ] Slug (input, generado automáticamente, editable)
    - [ ] Extracto (textarea)
    - [ ] **Contenido** (textarea simple por ahora, se reemplazará con Tiptap en Paso 18):
      - [ ] Textarea grande para contenido
      - [ ] Placeholder descriptivo
      - [ ] Validación visual
  - [ ] **Información de movilidad** (opcional):
    - [ ] País (input)
    - [ ] Ciudad (input)
    - [ ] Entidad de acogida (input)
    - [ ] Tipo de movilidad (select: alumnado/personal)
    - [ ] Categoría de movilidad (select: FCT, job_shadowing, intercambio, curso, otro)
  - [ ] **Estado y publicación**:
    - [ ] Estado (select: borrador, en_revision, publicado, archivado)
    - [ ] Fecha de publicación (date picker, opcional)
  - [ ] **Etiquetas**:
    - [ ] Select múltiple o checkboxes para seleccionar etiquetas existentes
    - [ ] Opción para crear nueva etiqueta (modal o inline) - se implementará en Paso 13
  - [ ] **Imagen destacada**:
    - [ ] Input file para subir imagen
    - [ ] Preview de imagen subida
    - [ ] Botón para eliminar imagen
- [ ] Implementar validación en tiempo real con feedback visual
- [ ] Implementar botones de acción: "Guardar", "Guardar y publicar", "Cancelar"
- [ ] Usar componentes Flux UI: `flux:field`, `flux:input`, `flux:textarea`, `flux:select`, `flux:button`

**Archivos a crear**:
- `resources/views/livewire/admin/news/create.blade.php`

**Verificación**:
- Verificar que el formulario se renderiza correctamente
- Verificar que la validación funciona
- Verificar que se puede crear una noticia con todos los campos
- Verificar que las etiquetas se asocian correctamente
- Verificar que la imagen se sube correctamente

**Nota**: El contenido se guarda como texto plano por ahora. En el Paso 18 se reemplazará el textarea con Tiptap para contenido enriquecido.

---

### ✅ **Fase 4: Componente Edit (Editar)**

#### Paso 9: Crear componente Livewire Admin\News\Edit
**Objetivo**: Crear el componente para editar noticias existentes.

**Tareas**:
- [ ] Crear clase `App\Livewire\Admin\News\Edit`
- [ ] Implementar propiedades públicas similares a Create:
  - [ ] `public NewsPost $newsPost` (modelo a editar)
  - [ ] Campos del formulario (precargados con datos del modelo)
  - [ ] `selectedTags` (precargado con etiquetas actuales)
  - [ ] `featuredImage` (nuevo archivo si se reemplaza)
  - [ ] `featuredImageUrl` (URL de imagen existente o nueva)
  - [ ] `removeFeaturedImage` (flag para eliminar imagen existente)
- [ ] Implementar métodos:
  - [ ] `mount(NewsPost $news_post)` - Autorización y precargar datos
  - [ ] `updatedTitle()` - Generar slug automáticamente
  - [ ] `updatedSlug()` - Validar slug en tiempo real
  - [ ] `updatedFeaturedImage()` - Manejar nueva imagen
  - [ ] `removeFeaturedImage()` - Marcar para eliminar imagen existente
  - [ ] `update()` - Validar y actualizar noticia:
    - [ ] Validar con `UpdateNewsPostRequest`
    - [ ] Actualizar noticia
    - [ ] Sincronizar etiquetas
    - [ ] Manejar imagen destacada (subir nueva o eliminar existente)
    - [ ] Redirigir a `admin.news.show` con mensaje de éxito

**Archivos a crear**:
- `app/Livewire/Admin/News/Edit.php`

**Verificación**:
- Verificar que se puede editar una noticia correctamente
- Verificar que las etiquetas se actualizan correctamente
- Verificar que la imagen se puede reemplazar o eliminar

---

#### Paso 10: Crear vista del componente Edit
**Objetivo**: Crear el formulario de edición similar al de creación pero con datos precargados. **Nota**: Por ahora usaremos textarea simple para el contenido. Tiptap se integrará al final (Paso 18).

**Tareas**:
- [ ] Crear `resources/views/livewire/admin/news/edit.blade.php`
- [ ] Reutilizar estructura similar a Create pero:
  - [ ] Mostrar imagen destacada existente si existe
  - [ ] Precargar todos los campos con datos del modelo
  - [ ] Precargar etiquetas seleccionadas
  - [ ] Mostrar información adicional: fecha de creación, última actualización, autor, revisor (si existe)
  - [ ] **Contenido** (textarea simple por ahora, se reemplazará con Tiptap en Paso 18):
    - [ ] Textarea grande con contenido precargado
    - [ ] Placeholder descriptivo
    - [ ] Validación visual
- [ ] Implementar opción para eliminar imagen existente
- [ ] Implementar botones de acción: "Guardar", "Guardar y publicar", "Cancelar", "Eliminar"

**Archivos a crear**:
- `resources/views/livewire/admin/news/edit.blade.php`

**Verificación**:
- Verificar que el formulario se renderiza con datos correctos
- Verificar que se puede editar una noticia
- Verificar que el contenido se carga y guarda correctamente

**Nota**: El contenido se edita como texto plano por ahora. En el Paso 18 se reemplazará el textarea con Tiptap para contenido enriquecido.

---

### ✅ **Fase 5: Componente Show (Detalle)**

#### Paso 11: Crear componente Livewire Admin\News\Show
**Objetivo**: Crear la vista de detalle de una noticia con información completa.

**Tareas**:
- [ ] Crear clase `App\Livewire\Admin\News\Show`
- [ ] Implementar propiedades públicas:
  - [ ] `public NewsPost $newsPost` (modelo a mostrar)
- [ ] Implementar métodos:
  - [ ] `mount(NewsPost $news_post)` - Autorización y cargar noticia con relaciones
  - [ ] `delete()` - Eliminar noticia (soft delete)
  - [ ] `restore()` - Restaurar noticia eliminada
  - [ ] `publish()` - Publicar noticia
  - [ ] `unpublish()` - Despublicar noticia
  - [ ] `forceDelete()` - Eliminar permanentemente (solo super-admin)
- [ ] Implementar eager loading: `with(['program', 'academicYear', 'author', 'reviewer', 'tags'])`

**Archivos a crear**:
- `app/Livewire/Admin/News/Show.php`

**Verificación**:
- Verificar que se muestra toda la información correctamente
- Verificar que las acciones funcionan

---

#### Paso 12: Crear vista del componente Show
**Objetivo**: Crear la vista de detalle con información completa y acciones.

**Tareas**:
- [ ] Crear `resources/views/livewire/admin/news/show.blade.php`
- [ ] Implementar header con título, breadcrumbs y botones de acción:
  - [ ] "Editar"
  - [ ] "Publicar/Despublicar" (según estado)
  - [ ] "Eliminar" (con modal de confirmación)
  - [ ] "Restaurar" (si está eliminada)
  - [ ] "Eliminar permanentemente" (solo super-admin, si está eliminada)
- [ ] Implementar secciones de información:
  - [ ] **Información básica**: Título, slug, extracto, contenido
  - [ ] **Imagen destacada**: Mostrar imagen con diferentes tamaños (thumbnail, medium, large)
  - [ ] **Metadatos**: Programa, año académico, estado, fecha de publicación
  - [ ] **Información de movilidad**: País, ciudad, entidad de acogida, tipo, categoría
  - [ ] **Etiquetas**: Lista de etiquetas con badges
  - [ ] **Autoría**: Autor, revisor (si existe), fechas de creación y actualización
  - [ ] **Estadísticas**: Número de etiquetas, fecha de publicación
- [ ] Implementar modales de confirmación para acciones destructivas
- [ ] Usar componentes Flux UI para diseño moderno

**Archivos a crear**:
- `resources/views/livewire/admin/news/show.blade.php`

**Verificación**:
- Verificar que se muestra toda la información correctamente
- Verificar que las acciones funcionan

---

### ✅ **Fase 6: Funcionalidades Avanzadas**

#### Paso 13: Implementar gestión de etiquetas en formularios
**Objetivo**: Permitir seleccionar etiquetas existentes y crear nuevas etiquetas desde el formulario.

**Tareas**:
- [ ] En componentes Create y Edit:
  - [ ] Implementar select múltiple o checkboxes para etiquetas existentes
  - [ ] Implementar funcionalidad para crear nueva etiqueta (modal o inline)
  - [ ] Usar `StoreNewsTagRequest` para validar nueva etiqueta
  - [ ] Actualizar lista de etiquetas disponibles después de crear nueva
- [ ] Implementar búsqueda/filtro de etiquetas en el select (opcional, con Alpine.js)

**Archivos a modificar**:
- `app/Livewire/Admin/News/Create.php`
- `app/Livewire/Admin/News/Edit.php`
- `resources/views/livewire/admin/news/create.blade.php`
- `resources/views/livewire/admin/news/edit.blade.php`

**Verificación**:
- Verificar que se pueden seleccionar etiquetas existentes
- Verificar que se puede crear una nueva etiqueta
- Verificar que las etiquetas se asocian correctamente

---

#### Paso 14: Implementar gestión de imágenes destacadas
**Objetivo**: Permitir subir, previsualizar y eliminar imágenes destacadas usando Laravel Media Library.

**Tareas**:
- [ ] En componentes Create y Edit:
  - [ ] Implementar input file para subir imagen
  - [ ] Implementar preview de imagen antes de guardar
  - [ ] Implementar opción para eliminar imagen existente (en Edit)
  - [ ] Validar tipo y tamaño de imagen
  - [ ] Subir imagen a colección 'featured' usando Media Library
  - [ ] Generar conversiones (thumbnail, medium, large)
- [ ] En componente Show:
  - [ ] Mostrar imagen destacada con diferentes tamaños
  - [ ] Mostrar thumbnail en listado (Index)

**Archivos a modificar**:
- `app/Livewire/Admin/News/Create.php`
- `app/Livewire/Admin/News/Edit.php`
- `resources/views/livewire/admin/news/create.blade.php`
- `resources/views/livewire/admin/news/edit.blade.php`
- `resources/views/livewire/admin/news/show.blade.php`
- `resources/views/livewire/admin/news/index.blade.php`

**Verificación**:
- Verificar que se puede subir una imagen
- Verificar que se muestra el preview
- Verificar que se puede eliminar una imagen
- Verificar que las conversiones se generan correctamente

---

#### Paso 15: Implementar publicación/despublicación
**Objetivo**: Permitir publicar y despublicar noticias cambiando el estado y estableciendo `published_at`.

**Tareas**:
- [ ] En componente Index:
  - [ ] Implementar botón "Publicar" para noticias no publicadas
  - [ ] Implementar botón "Despublicar" para noticias publicadas
  - [ ] Método `publish()`: cambiar estado a 'publicado' y establecer `published_at` a ahora
  - [ ] Método `unpublish()`: cambiar estado a 'borrador' y establecer `published_at` a null
- [ ] En componente Show:
  - [ ] Implementar botones de publicación/despublicación
- [ ] Verificar autorización con `NewsPostPolicy::publish()`

**Archivos a modificar**:
- `app/Livewire/Admin/News/Index.php`
- `app/Livewire/Admin/News/Show.php`
- `resources/views/livewire/admin/news/index.blade.php`
- `resources/views/livewire/admin/news/show.blade.php`

**Verificación**:
- Verificar que se puede publicar una noticia
- Verificar que se puede despublicar una noticia
- Verificar que `published_at` se establece correctamente

---

### ✅ **Fase 7: Testing**

#### Paso 16: Crear tests para los componentes
**Objetivo**: Crear tests completos para todos los componentes del CRUD.

**Tareas**:
- [ ] Crear `tests/Feature/Livewire/Admin/News/IndexTest.php`:
  - [ ] Test de autorización (solo usuarios con permisos pueden ver)
  - [ ] Test de listado de noticias
  - [ ] Test de búsqueda
  - [ ] Test de filtros (programa, año académico, estado, eliminados)
  - [ ] Test de ordenación
  - [ ] Test de paginación
  - [ ] Test de eliminación (soft delete)
  - [ ] Test de restauración
  - [ ] Test de eliminación permanente (solo super-admin)
  - [ ] Test de publicación/despublicación
- [ ] Crear `tests/Feature/Livewire/Admin/News/CreateTest.php`:
  - [ ] Test de autorización
  - [ ] Test de creación de noticia
  - [ ] Test de validación de campos requeridos
  - [ ] Test de generación automática de slug
  - [ ] Test de asociación de etiquetas
  - [ ] Test de subida de imagen destacada
  - [ ] Test de establecimiento automático de `author_id`
- [ ] Crear `tests/Feature/Livewire/Admin/News/EditTest.php`:
  - [ ] Test de autorización
  - [ ] Test de edición de noticia
  - [ ] Test de validación
  - [ ] Test de actualización de etiquetas
  - [ ] Test de reemplazo de imagen destacada
  - [ ] Test de eliminación de imagen destacada
- [ ] Crear `tests/Feature/Livewire/Admin/News/ShowTest.php`:
  - [ ] Test de autorización
  - [ ] Test de visualización de noticia
  - [ ] Test de acciones (eliminar, restaurar, publicar, etc.)

**Archivos a crear**:
- `tests/Feature/Livewire/Admin/News/IndexTest.php`
- `tests/Feature/Livewire/Admin/News/CreateTest.php`
- `tests/Feature/Livewire/Admin/News/EditTest.php`
- `tests/Feature/Livewire/Admin/News/ShowTest.php`

**Verificación**:
- Ejecutar todos los tests y verificar que pasan
- Verificar cobertura de código

---

### ✅ **Fase 8: Optimizaciones y Ajustes Finales**

#### Paso 17: Optimizaciones y ajustes finales
**Objetivo**: Optimizar consultas, añadir índices si es necesario, y realizar ajustes finales.

**Tareas**:
- [ ] Revisar y optimizar consultas (eager loading, índices)
- [ ] Verificar que todas las traducciones están presentes
- [ ] Verificar que el diseño es responsive
- [ ] Verificar accesibilidad (WCAG)
- [ ] Ejecutar Laravel Pint para formatear código
- [ ] Ejecutar todos los tests
- [ ] Revisar y actualizar documentación si es necesario

**Archivos a revisar**:
- Todos los archivos creados/modificados

**Verificación**:
- Verificar que todo funciona correctamente
- Verificar que el código está formateado correctamente
- Verificar que todos los tests pasan

---

### ✅ **Fase 9: Integración de Tiptap (Editor de Contenido Enriquecido)**

#### ✅ Paso 18: Instalar, configurar e integrar Tiptap (COMPLETADO)
**Objetivo**: Instalar Tiptap y reemplazar los textareas simples con el editor de contenido enriquecido en los formularios Create y Edit.

**Tareas**:
- [x] Instalar Tiptap y extensiones básicas:
  ```bash
  npm install @tiptap/core @tiptap/starter-kit @tiptap/pm
  ```
- [x] Instalar extensiones recomendadas para noticias:
  ```bash
  npm install @tiptap/extension-link @tiptap/extension-image @tiptap/extension-placeholder
  ```
- [x] Crear helper JavaScript para Tiptap en `resources/js/app.js`:
  - [x] Importar Editor y extensiones
  - [x] Crear función Alpine.js `tiptapEditor()` usando `Alpine.data()` (enfoque de Rick de Graaf)
  - [x] Configurar integración con `$wire.entangle()` de Livewire
  - [x] Configurar toolbar completo (negrita, cursiva, tachado, títulos H1-H3, listas, enlaces, undo/redo)
  - [x] Implementar métodos para todos los botones del toolbar
  - [x] Usar `updatedAt` para reactividad de Alpine
- [x] Importar estilos de Tiptap (usando Tailwind prose para estilos)
- [x] Crear componente Blade reutilizable `components/tiptap-editor.blade.php`
- [x] **Reemplazar textarea en Create**:
  - [x] Modificar `resources/views/livewire/admin/news/create.blade.php`
  - [x] Reemplazar textarea de contenido con componente Tiptap
  - [x] Configurar `wire:model="content"` para sincronización
  - [x] Añadir toolbar completo con todos los botones
- [x] **Reemplazar textarea en Edit**:
  - [x] Modificar `resources/views/livewire/admin/news/edit.blade.php`
  - [x] Reemplazar textarea de contenido con componente Tiptap
  - [x] Precargar contenido HTML existente en el editor
  - [x] Configurar `wire:model="content"` para sincronización
  - [x] Añadir toolbar completo con todos los botones
- [x] Verificar que el contenido HTML se guarda correctamente
- [x] Verificar que el contenido HTML se carga correctamente en edición

**Archivos a modificar**:
- `package.json` (se actualiza automáticamente con npm install)
- `resources/js/app.js`
- `resources/views/livewire/admin/news/create.blade.php`
- `resources/views/livewire/admin/news/edit.blade.php`

**Archivos a crear** (opcional):
- `resources/views/components/tiptap-editor.blade.php`

**Verificación**:
- Verificar que Tiptap se instala correctamente
- Verificar que el helper JavaScript funciona
- Verificar que se puede inicializar un editor básico
- Verificar que el contenido se sincroniza correctamente con Livewire
- ✅ Verificar que se puede crear una noticia con contenido enriquecido
- ✅ Verificar que se puede editar una noticia y el contenido HTML se carga correctamente
- ✅ Verificar que el contenido HTML se guarda y muestra correctamente

**Nota de implementación**: Se siguió el enfoque de Rick de Graaf (https://rickdegraaf.com/blog/mastering-tiptap-getting-started) usando `Alpine.data()` y `$wire.entangle()` para evitar problemas de sincronización con Livewire.

**Referencias**:
- [Documentación Tiptap](https://tiptap.dev/)
- [Guía de integración con PHP/Laravel](https://tiptap.dev/docs/editor/getting-started/install/php)
- [Comparación Trix vs Tiptap](paso-3.5.5-editor-comparison.md)

---

## 📝 Notas Importantes

### SoftDeletes
- Las noticias **nunca** se eliminan permanentemente por defecto
- Solo se marcan como eliminadas (`deleted_at`)
- Solo super-admin puede realizar `forceDelete()`
- Antes de `forceDelete()`, validar que no existan relaciones (aunque en este caso no hay relaciones que dependan de noticias)

### Gestión de Etiquetas
- Las etiquetas se gestionan mediante relación many-to-many
- Se pueden seleccionar etiquetas existentes o crear nuevas desde el formulario
- Usar `sync()` para actualizar etiquetas

### Imágenes Destacadas
- Usar Laravel Media Library con colección 'featured'
- Generar conversiones automáticamente (thumbnail, medium, large)
- Permitir preview antes de guardar
- Permitir eliminar imagen existente en edición

### Editor de Contenido (Tiptap)
- **Estrategia de desarrollo**: Primero completar el CRUD con textarea simple, luego integrar Tiptap
- **Fase inicial**: Los formularios Create y Edit usan textarea simple para el campo `content`
- **Fase final (Paso 18)**: Se reemplazará el textarea con Tiptap para contenido enriquecido
- **Tiptap**: Editor de contenido enriquecido basado en ProseMirror
- Integración con Livewire 3 usando Alpine.js y `@entangle()`
- Extensiones: StarterKit (básico), Link, Image, Placeholder
- Toolbar con botones: negrita, cursiva, enlaces, listas, etc.
- Guardar contenido como HTML en el campo `content` del modelo
- Ver [Comparación Trix vs Tiptap](paso-3.5.5-editor-comparison.md) para detalles

### Publicación
- Publicar una noticia implica cambiar estado a 'publicado' y establecer `published_at`
- Despublicar implica cambiar estado a 'borrador' y establecer `published_at` a null
- Verificar autorización con `NewsPostPolicy::publish()`

### Editor de Contenido
- **Estrategia**: Completar CRUD primero con textarea simple, luego integrar Tiptap (Paso 18)
- **Fase inicial**: Textarea simple para desarrollo rápido y pruebas
- **Fase final**: Tiptap se integrará en el Paso 18 como mejora del editor
- **Tiptap** será el editor de contenido enriquecido utilizado
- Integración con Livewire 3 usando Alpine.js y `@entangle()`
- Ver [Comparación Trix vs Tiptap](paso-3.5.5-editor-comparison.md) para más detalles
- Extensiones recomendadas: StarterKit, Link, Image, Placeholder

### Traducciones
- El modelo NewsPost tiene campos que pueden necesitar traducciones (title, excerpt, content)
- Por ahora no implementar gestión de traducciones (se hará en un paso posterior)
- Los campos se guardan en el idioma actual

---

## 🔧 **Mejoras en Gestión de Imágenes Destacadas** (5 Fases)

**Nota**: Estas fases son mejoras adicionales al CRUD básico y se desarrollan después de completar los pasos principales. Se documentan aquí para mantener el contexto completo del desarrollo.

**Objetivo**: Mejorar la gestión de imágenes destacadas incluyendo:
- Verificación y corrección de guardado de imágenes
- Generación automática de conversiones (thumbnail, medium, large)
- Visualización correcta en todas las vistas (Index, Show, Edit)
- Implementación de soft delete para imágenes (eliminar sin borrar archivo físico) usando `custom_properties`
- Opción de restaurar imágenes eliminadas

### 📄 Documentación Detallada
Ver [Plan Detallado de Mejoras de Imágenes](paso-3.5.5-imagenes-plan.md) para información completa.

### ✅ **Fase 1: Diagnóstico y Verificación** (COMPLETADA)

#### ✅ Fase 1.1: Verificar guardado de imágenes (COMPLETADA)
**Objetivo**: Confirmar que las imágenes se están guardando correctamente.

**Tareas**:
- [x] Verificar que `addMedia()` se está ejecutando correctamente en Create y Edit
- [x] Verificar que el archivo físico se guarda en `storage/app/public/media`
- [x] Verificar que el registro se crea en la tabla `media`
- [x] Verificar que la relación `collection_name = 'featured'` es correcta

**Archivos revisados**:
- `app/Livewire/Admin/News/Create.php` (método `store()`) - ✅ Correcto
- `app/Livewire/Admin/News/Edit.php` (método `update()`) - ✅ Correcto
- `storage/app/public/media/` (directorio de archivos) - ✅ Enlace simbólico creado
- Tabla `media` en base de datos - ✅ Estructura correcta

**Resultados**:
- ✅ El código de guardado es correcto y sigue el mismo patrón que otros CRUDs (Programs)
- ✅ Se usa `addMedia()->usingName()->usingFileName()->toMediaCollection('featured')`
- ✅ Se crearon tests en `CreateTest.php` que verifican:
  - Guardado correcto de imagen
  - Creación de registro en tabla `media`
  - Existencia del archivo físico
  - Configuración correcta de `collection_name = 'featured'`

---

#### ✅ Fase 1.2: Verificar generación de conversiones (COMPLETADA)
**Objetivo**: Confirmar que las conversiones (thumbnail, medium, large) se generan automáticamente.

**Tareas**:
- [x] Verificar que las conversiones se generan al guardar la imagen
- [x] Verificar que las conversiones existen físicamente en el disco
- [x] Verificar que `getFirstMediaUrl('featured', 'thumbnail')` retorna la URL correcta
- [x] Verificar configuración de conversiones en el modelo

**Archivos revisados**:
- `app/Models/NewsPost.php` (método `registerMediaConversions()`) - ✅ Correcto
- Conversiones configuradas: `thumbnail` (300x300), `medium` (800x600), `large` (1200x900)
- Aplicadas a colecciones: `featured` y `gallery`

**Resultados**:
- ✅ Las conversiones están correctamente configuradas en `registerMediaConversions()`
- ✅ Media Library genera las conversiones automáticamente de forma síncrona por defecto
- ✅ Se creó test que verifica que las URLs de conversiones están disponibles
- ✅ Las conversiones se generan cuando se añade una imagen a la colección `featured`

**Comandos de verificación**:
```bash
# Regenerar conversiones manualmente (si es necesario)
php artisan media-library:regenerate
```

---

### ✅ **Fase 2: Mejora de Visualización** (COMPLETADA)

#### ✅ Fase 2.1: Mejorar visualización en Index (COMPLETADA)
**Objetivo**: Agregar fallbacks y mejorar la presentación de imágenes en el listado.

**Tareas completadas**:
- [x] Verificar que `getFirstMediaUrl('featured', 'thumbnail')` funciona correctamente
- [x] Agregar fallback si la conversión no existe (usa imagen original si no hay thumbnail)
- [x] Agregar fallback si la imagen no carga (onerror muestra placeholder)
- [x] Mejorar presentación con bordes y lazy loading
- [x] Agregar placeholder visual cuando no hay imagen

**Mejoras implementadas**:
- Fallback en cascada: `thumbnail` → `original` → `placeholder`
- Manejo de errores con `onerror` para mostrar placeholder si la imagen falla
- Lazy loading para mejorar rendimiento
- Bordes y estilos mejorados para mejor presentación visual

---

#### ✅ Fase 2.2: Mejorar visualización en Show (COMPLETADA)
**Objetivo**: Mejorar la visualización de la imagen destacada en la vista de detalle.

**Tareas completadas**:
- [x] Verificar que `hasFeaturedImage()` retorna `true` cuando hay imagen
- [x] Verificar que `getFeaturedImageUrl('large')` retorna la URL correcta
- [x] Agregar fallback en cascada: `large` → `medium` → `original`
- [x] Mejorar presentación con bordes y lazy loading
- [x] Verificar que la imagen se muestra con el tamaño correcto

**Mejoras implementadas**:
- Fallback en cascada para conversiones: `large` → `medium` → `original`
- Lazy loading para mejorar rendimiento
- Bordes y estilos mejorados
- Información de tamaño de archivo mostrada correctamente

---

#### ✅ Fase 2.3: Mejorar visualización en Edit (COMPLETADA)
**Objetivo**: Mejorar la presentación de la imagen actual en el formulario de edición.

**Tareas completadas**:
- [x] Verificar que `hasExistingFeaturedImage()` funciona correctamente
- [x] Mejorar la presentación de la imagen actual con mejor diseño
- [x] Agregar información de tamaño de archivo
- [x] Mejorar botones de acción (Ver y Eliminar) con iconos
- [x] Usar conversión `medium` para preview si está disponible

**Mejoras implementadas**:
- Preview mejorado con fallback: `medium` → `original`
- Información de tamaño de archivo visible
- Botones con iconos para mejor UX
- Diseño mejorado con bordes y espaciado
- Lazy loading para mejor rendimiento

---

### ✅ **Fase 3: Implementar Soft Delete para Media** (COMPLETADA)

**Nota**: Se implementó usando la **Opción B** (más simple) - `custom_properties` para marcar como eliminado.

#### ✅ Fase 3.1: Implementar métodos de soft delete usando custom_properties (COMPLETADA)
**Objetivo**: Crear métodos en el modelo NewsPost para gestionar soft delete de imágenes usando `custom_properties`.

**Tareas completadas**:
- [x] Crear método `softDeleteFeaturedImage()` en modelo NewsPost
- [x] Crear método `restoreFeaturedImage()` en modelo NewsPost
- [x] Crear método `forceDeleteFeaturedImage()` para eliminación permanente
- [x] Crear método `isMediaSoftDeleted()` para verificar si una imagen está eliminada
- [x] Crear método `getSoftDeletedFeaturedImages()` para obtener imágenes eliminadas
- [x] Crear método `hasSoftDeletedFeaturedImages()` para verificar si hay imágenes eliminadas
- [x] Crear método `getMediaWithDeleted()` para obtener todas las imágenes incluyendo eliminadas

**Implementación**:
- Se usa `custom_properties['deleted_at']` para marcar imágenes como eliminadas
- El archivo físico no se elimina, solo se marca en la base de datos
- Los métodos sobrescriben `getFirstMedia()`, `hasMedia()` y `getMedia()` para excluir automáticamente imágenes eliminadas

---

#### ✅ Fase 3.2: Actualizar componente Edit para usar soft delete (COMPLETADA)
**Objetivo**: Modificar el componente Edit para usar soft delete en lugar de eliminación permanente.

**Tareas completadas**:
- [x] Modificar método `update()` para usar `softDeleteFeaturedImage()` en lugar de `clearMediaCollection()`
- [x] Agregar método `restoreFeaturedImage()` en componente Edit
- [x] Agregar método `hasSoftDeletedFeaturedImages()` en componente Edit
- [x] Actualizar vista para mostrar opción de restaurar si hay imagen eliminada

**Implementación**:
- Al eliminar una imagen, se marca como eliminada usando `softDeleteFeaturedImage()`
- Al subir una nueva imagen, la anterior se marca como eliminada (no se borra físicamente)
- Se muestra un callout con opción de restaurar si hay imágenes eliminadas disponibles

---

#### ✅ Fase 3.3: Actualizar consultas para excluir imágenes eliminadas (COMPLETADA)
**Objetivo**: Modificar las consultas para que automáticamente excluyan imágenes marcadas como eliminadas.

**Tareas completadas**:
- [x] Modificar `getFirstMedia()` para excluir imágenes eliminadas
- [x] Modificar `hasMedia()` para excluir imágenes eliminadas
- [x] Modificar `getMedia()` para excluir imágenes eliminadas
- [x] Los métodos en Show, Edit e Index funcionan automáticamente con las nuevas consultas

**Implementación**:
- Se sobrescribieron los métodos de Media Library en el modelo `NewsPost`
- Todos los métodos verifican `custom_properties['deleted_at']` antes de retornar resultados
- Las vistas (Index, Show, Edit) funcionan automáticamente sin cambios adicionales

---

### ✅ **Fase 4: Mejoras Adicionales** (COMPLETADA)

#### ✅ Fase 4.1: Verificar comando para regenerar conversiones (COMPLETADA)
**Objetivo**: Verificar que el comando de Media Library para regenerar conversiones funciona correctamente.

**Tareas completadas**:
- [x] Verificar que el comando `php artisan media-library:regenerate` existe y funciona
- [x] Documentar uso del comando

**Resultados**:
- ✅ El comando `php artisan media-library:regenerate` está disponible
- ✅ Opciones disponibles:
  - `--ids`: Regenerar conversiones para IDs específicos
  - `--only`: Regenerar conversiones específicas (thumbnail, medium, large)
  - `--only-missing`: Regenerar solo conversiones faltantes
  - `--with-responsive-images`: Regenerar imágenes responsivas
  - `--force`: Forzar ejecución en producción

**Uso del comando**:
```bash
# Regenerar todas las conversiones de todas las imágenes
php artisan media-library:regenerate

# Regenerar solo conversiones faltantes
php artisan media-library:regenerate --only-missing

# Regenerar conversiones específicas
php artisan media-library:regenerate --only=thumbnail --only=medium

# Regenerar para un modelo específico
php artisan media-library:regenerate "App\Models\NewsPost"
```

---

#### ✅ Fase 4.2: Optimizar carga de imágenes (COMPLETADA)
**Objetivo**: Verificar y optimizar la carga de imágenes en el Index.

**Tareas completadas**:
- [x] Verificar eager loading de media en consultas del Index
- [x] Verificar que lazy loading está implementado en el frontend

**Resultados**:
- ✅ El Index ya usa eager loading para relaciones: `with(['program', 'academicYear', 'author', 'tags'])`
- ✅ Las imágenes en Index, Show y Edit ya tienen `loading="lazy"` implementado
- ✅ No se necesita eager loading adicional para media ya que se obtiene bajo demanda con `getFirstMediaUrl()`

**Optimizaciones implementadas**:
- Lazy loading en todas las imágenes (`loading="lazy"`)
- Eager loading de relaciones principales
- Fallbacks para conversiones (thumbnail → original → placeholder)

---

#### ✅ Fase 4.3: Verificar validación de imágenes (COMPLETADA)
**Objetivo**: Verificar que las validaciones de imágenes funcionan correctamente.

**Tareas completadas**:
- [x] Verificar que la validación de tamaño funciona (5MB máximo)
- [x] Verificar que la validación de tipos MIME funciona

**Resultados**:
- ✅ Validación de tamaño: `max:5120` (5MB) en FormRequests
- ✅ Validación de tipos MIME: `mimes:jpeg,png,jpg,webp,gif` en FormRequests
- ✅ Validación en tiempo real en componentes Livewire
- ✅ Validación también en FilePond (frontend)

**Validaciones implementadas**:
- Tamaño máximo: 5MB (5120 KB)
- Tipos permitidos: JPEG, PNG, JPG, WebP, GIF
- Validación en backend (FormRequests)
- Validación en frontend (FilePond)
- Validación en tiempo real (Livewire `updatedFeaturedImage()`)

**Nota sobre dimensiones**: No se agregó validación de dimensiones (ancho/alto máximo) ya que las conversiones se generan automáticamente y las imágenes se redimensionan según sea necesario.

---

### ✅ **Fase 5: Testing y Verificación** (COMPLETADA)

#### ✅ Fase 5.1: Tests para guardado de imágenes (COMPLETADA)
**Objetivo**: Verificar que las imágenes se guardan correctamente al crear noticias.

**Tests implementados**:
- [x] `it('creates news post with featured image')` - Verifica que la imagen se guarda correctamente
- [x] `it('generates image conversions when creating news post with featured image')` - Verifica que las conversiones se generan
- [x] Tests en `CreateTest.php` verifican que la imagen se muestra correctamente

**Resultados**:
- ✅ Las imágenes se guardan correctamente en la colección 'featured'
- ✅ Las conversiones (thumbnail, medium, large) se generan automáticamente
- ✅ Los archivos físicos se almacenan correctamente en el disco configurado

---

#### ✅ Fase 5.2: Tests para edición de imágenes (COMPLETADA)
**Objetivo**: Verificar que las imágenes se pueden editar y reemplazar correctamente.

**Tests implementados**:
- [x] `it('can upload new featured image')` - Verifica subida de nueva imagen
- [x] `it('can replace existing image with new one')` - Verifica reemplazo de imagen
- [x] `it('can toggle remove existing image')` - Verifica toggle de eliminación
- [x] `it('sets removeFeaturedImage to false when uploading new image')` - Verifica lógica de estado

**Resultados**:
- ✅ Las imágenes se pueden subir y reemplazar correctamente
- ✅ La imagen anterior se mantiene (soft delete) cuando se reemplaza
- ✅ La nueva imagen se guarda correctamente

---

#### ✅ Fase 5.3: Tests para eliminación y restauración (COMPLETADA)
**Objetivo**: Verificar que el soft delete funciona correctamente y las imágenes se pueden restaurar.

**Tests implementados**:
- [x] `it('soft deletes existing image when removing it')` - Verifica soft delete
- [x] `it('can restore soft-deleted image')` - Verifica restauración
- [x] `it('can select image from modal and restore it')` - Verifica selección y restauración desde modal

**Resultados**:
- ✅ El archivo físico NO se elimina cuando se hace soft delete
- ✅ La imagen no se muestra en las vistas después del soft delete
- ✅ Las imágenes se pueden restaurar correctamente
- ✅ La imagen vuelve a mostrarse después de restaurar

---

#### ✅ Fase 5.4: Tests para eliminación permanente (COMPLETADA)
**Objetivo**: Verificar que la eliminación permanente funciona correctamente.

**Tests implementados**:
- [x] `it('can force delete soft-deleted image permanently')` - Verifica eliminación permanente

**Resultados**:
- ✅ El archivo físico se elimina del servidor cuando se hace force delete
- ✅ El registro se elimina de la base de datos
- ✅ La imagen no se puede restaurar después del force delete

---

#### ✅ Fase 5.5: Tests para selección de imágenes desde modal (COMPLETADA)
**Objetivo**: Verificar que el modal de selección de imágenes funciona correctamente.

**Tests implementados**:
- [x] `it('shows available images in selection modal')` - Verifica que el modal muestra todas las imágenes disponibles

**Resultados**:
- ✅ El modal muestra todas las imágenes (actuales y eliminadas)
- ✅ Las imágenes se marcan correctamente como "actual" o "eliminada"
- ✅ Se pueden seleccionar imágenes desde el modal para restaurarlas

---

## ✅ Checklist Final

Antes de considerar el paso 3.5.5 completado, verificar:

- [x] SoftDeletes implementado en NewsPost
- [x] FormRequests actualizados con validación completa
- [x] Componente Index creado y funcionando
- [x] Componente Create creado y funcionando
- [x] Componente Edit creado y funcionando
- [x] Componente Show creado y funcionando
- [x] Rutas configuradas correctamente
- [x] Navegación actualizada
- [x] Gestión de etiquetas funcionando
- [x] Gestión de imágenes destacadas funcionando
- [x] Publicación/despublicación funcionando
- [x] Tests completos y pasando (1231 tests ✅)
- [x] Código formateado con Pint
- [x] Diseño responsive
- [x] Accesibilidad verificada
- [x] **Tiptap integrado** (Paso 18 - ✅ COMPLETADO)
- [x] **Gestión avanzada de imágenes** (5 Fases - ✅ COMPLETADAS)
  - [x] Soft delete para imágenes
  - [x] Restauración de imágenes eliminadas
  - [x] Eliminación permanente de imágenes
  - [x] Selección de imágenes desde modal
  - [x] Tests completos para todas las funcionalidades

---

**Fecha de Creación**: Diciembre 2025  
**Fecha de Finalización**: Enero 2026  
**Estado**: ✅ **COMPLETADO** - Todos los tests pasando (1231 tests)

