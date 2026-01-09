# Plan de Desarrollo: Paso 3.5.13 - Gestión de Traducciones en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Traducciones en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Traducciones en el panel de administración con:
- Listado moderno con tabla interactiva
- Formularios de creación y edición para traducciones polimórficas
- Filtros por modelo traducible y idioma
- Búsqueda avanzada de traducciones
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4
- Integración con el sistema de traducciones polimórficas existente

---

## 📋 Pasos de Desarrollo (15 Pasos)

### **Fase 1: Preparación Base**

#### **Paso 1: Crear TranslationPolicy**
- [ ] Crear `app/Policies/TranslationPolicy.php`
- [ ] Implementar métodos:
  - `viewAny()` - Ver listado (solo admin)
  - `view()` - Ver detalle (solo admin)
  - `create()` - Crear traducción (solo admin)
  - `update()` - Actualizar traducción (solo admin)
  - `delete()` - Eliminar traducción (solo admin)
- [ ] Registrar policy en `app/Providers/AuthServiceProvider.php` o `bootstrap/providers.php`
- [ ] Crear tests básicos para la policy

#### **Paso 2: Crear FormRequests**
- [ ] Crear `app/Http/Requests/StoreTranslationRequest.php`:
  - Autorización con `TranslationPolicy::create()`
  - Validación de campos:
    - `translatable_type`: required, string, in:['App\Models\Program','App\Models\Setting']
    - `translatable_id`: required, integer, exists en tabla correspondiente
    - `language_id`: required, integer, exists:languages,id
    - `field`: required, string, max:255
    - `value`: required, string
  - Validación de unicidad: combinación única de (translatable_type, translatable_id, language_id, field)
  - Mensajes de error personalizados en español e inglés
- [ ] Crear `app/Http/Requests/UpdateTranslationRequest.php`:
  - Autorización con `TranslationPolicy::update()`
  - Mismas validaciones que Store, pero ignorando el registro actual en la unicidad
  - Mensajes de error personalizados

---

### **Fase 2: Estructura Base y Listado**

#### **Paso 3: Componente Index (Listado)**
- [ ] Crear componente Livewire `Admin\Translations\Index`
- [ ] Implementar propiedades públicas:
  - `string $search = ''` - Búsqueda (con `#[Url]`)
  - `?string $filterModel = null` - Filtro por modelo (con `#[Url]`)
  - `?int $filterLanguageId = null` - Filtro por idioma (con `#[Url]`)
  - `?int $filterTranslatableId = null` - Filtro por ID del modelo traducible (con `#[Url]`)
  - `string $sortField = 'created_at'` - Campo de ordenación (con `#[Url]`)
  - `string $sortDirection = 'desc'` - Dirección de ordenación (con `#[Url]`)
  - `int $perPage = 15` - Elementos por página (con `#[Url]`)
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `?int $translationToDelete = null` - ID de traducción a eliminar
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `translations()` - Computed property con paginación, filtros y ordenación
    - Eager loading: `language`, `translatable`
    - Búsqueda en: `field`, `value`
    - Filtros: modelo, idioma, translatable_id
  - `sortBy($field)` - Ordenación
  - `confirmDelete($translationId)` - Confirmar eliminación
  - `delete()` - Eliminar traducción (sin SoftDeletes, eliminación directa)
  - `resetFilters()` - Resetear filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedFilterModel()` - Resetear página al cambiar filtro
  - `updatedFilterLanguageId()` - Resetear página al cambiar filtro
  - `canCreate()` - Verificar si puede crear
  - `getAvailableModels()` - Obtener modelos traducibles disponibles (Program, Setting)
  - `getLanguages()` - Obtener idiomas activos
  - `getTranslatableDisplayName($translation)` - Obtener nombre para mostrar del modelo traducible
  - `render()` - Renderizado con paginación
- [ ] Implementar autorización con `TranslationPolicy`
- [ ] Crear vista `livewire/admin/translations/index.blade.php`:
  - Header con título y botón crear
  - Breadcrumbs
  - Filtros: búsqueda, modelo, idioma, reset
  - Tabla responsive con columnas:
    - Modelo traducible (tipo + nombre)
    - Campo (field)
    - Idioma
    - Valor (truncado si es largo)
    - Fecha creación
    - Acciones (ver, editar, eliminar)
  - Modal de confirmación de eliminación
  - Paginación
  - Estado vacío
  - Loading states

---

### **Fase 3: Creación y Edición**

#### **Paso 4: Componente Create (Crear)**
- [ ] Crear componente Livewire `Admin\Translations\Create`
- [ ] Implementar propiedades públicas:
  - `string $translatableType = ''` - Tipo de modelo traducible
  - `?int $translatableId = null` - ID del modelo traducible (opcional desde URL)
  - `?int $languageId = null` - ID del idioma (opcional desde URL)
  - `string $field = ''` - Campo a traducir
  - `string $value = ''` - Valor de la traducción
- [ ] Implementar métodos:
  - `mount(?string $model = null, ?int $id = null, ?int $language = null)` - Inicialización con autorización y parámetros opcionales
  - `updatedTranslatableType()` - Resetear translatableId cuando cambia el tipo
  - `updatedTranslatableId()` - Validar que el ID existe
  - `getAvailableModels()` - Obtener modelos traducibles
  - `getLanguages()` - Obtener idiomas activos
  - `getTranslatableOptions()` - Obtener opciones para select de modelos traducibles (según tipo)
  - `getAvailableFields()` - Obtener campos disponibles según el modelo seleccionado
  - `store()` - Guardar nueva traducción usando `StoreTranslationRequest`
- [ ] Crear vista `livewire/admin/translations/create.blade.php`:
  - Formulario con Flux UI:
    - Select de modelo traducible (Program, Setting)
    - Select de instancia del modelo (dinámico según modelo seleccionado)
    - Select de idioma
    - Select de campo (dinámico según modelo seleccionado)
    - Textarea para valor (con contador de caracteres)
  - Botones: Guardar, Cancelar
  - Validación en tiempo real
  - Mensajes de error

#### **Paso 5: Componente Edit (Editar)**
- [ ] Crear componente Livewire `Admin\Translations\Edit`
- [ ] Implementar propiedades públicas:
  - `Translation $translation` - Traducción a editar
  - `string $value = ''` - Valor de la traducción
- [ ] Implementar métodos:
  - `mount(Translation $translation)` - Inicialización con autorización y carga de datos
  - `update()` - Actualizar traducción usando `UpdateTranslationRequest`
- [ ] Crear vista `livewire/admin/translations/edit.blade.php`:
  - Formulario con Flux UI:
    - Información de solo lectura: modelo, instancia, campo, idioma
    - Textarea para valor (con contador de caracteres)
  - Botones: Guardar, Cancelar
  - Validación en tiempo real
  - Mensajes de error

---

### **Fase 4: Funcionalidades Avanzadas**

#### **Paso 6: Mejoras en Index - Información del Modelo Traducible**
- [ ] Mejorar método `getTranslatableDisplayName()`:
  - Para Program: mostrar código y nombre
  - Para Setting: mostrar key
  - Manejar casos donde el modelo fue eliminado (SoftDelete)
- [ ] Añadir columna "Modelo" en tabla con badge mostrando tipo
- [ ] Añadir tooltip o modal con información completa del modelo traducible

#### **Paso 7: Mejoras en Create - Selectores Dinámicos**
- [ ] Implementar select dinámico de instancias:
  - Cuando se selecciona Program: mostrar programas activos
  - Cuando se selecciona Setting: mostrar settings disponibles
- [ ] Implementar select dinámico de campos:
  - Para Program: ['name', 'description']
  - Para Setting: ['value'] (o según configuración)
- [ ] Añadir validación en tiempo real de unicidad
- [ ] Mostrar advertencia si ya existe traducción para esa combinación

#### **Paso 8: Vista de Detalle (Opcional pero Recomendado)**
- [ ] Crear componente Livewire `Admin\Translations\Show`
- [ ] Mostrar información completa:
  - Modelo traducible con enlace
  - Campo
  - Idioma
  - Valor completo
  - Fechas de creación y actualización
  - Botones: Editar, Eliminar, Volver
- [ ] Crear vista `livewire/admin/translations/show.blade.php`

---

### **Fase 5: Rutas y Navegación**

#### **Paso 9: Configurar Rutas**
- [ ] Añadir rutas en `routes/web.php` dentro del grupo `admin`:
  ```php
  Route::get('/traducciones', \App\Livewire\Admin\Translations\Index::class)->name('translations.index');
  Route::get('/traducciones/crear', \App\Livewire\Admin\Translations\Create::class)->name('translations.create');
  Route::get('/traducciones/{translation}', \App\Livewire\Admin\Translations\Show::class)->name('translations.show');
  Route::get('/traducciones/{translation}/editar', \App\Livewire\Admin\Translations\Edit::class)->name('translations.edit');
  ```
- [ ] Verificar que las rutas funcionen correctamente

#### **Paso 10: Integrar en Navegación**
- [ ] Añadir enlace en sidebar de administración (`resources/views/components/layouts/admin-sidebar.blade.php` o similar)
- [ ] Añadir en breadcrumbs cuando corresponda
- [ ] Verificar permisos en navegación

---

### **Fase 6: Tests**

#### **Paso 11: Tests de Policy**
- [ ] Crear `tests/Feature/Policies/TranslationPolicyTest.php`
- [ ] Tests para cada método:
  - `viewAny()` - Solo admin puede ver listado
  - `view()` - Solo admin puede ver detalle
  - `create()` - Solo admin puede crear
  - `update()` - Solo admin puede actualizar
  - `delete()` - Solo admin puede eliminar

#### **Paso 12: Tests de FormRequests**
- [ ] Crear `tests/Feature/Http/Requests/StoreTranslationRequestTest.php`
- [ ] Tests de validación:
  - Campos requeridos
  - Tipos de datos correctos
  - Unicidad de combinación (translatable_type, translatable_id, language_id, field)
  - Existencia de relaciones (language_id, translatable_id)
- [ ] Crear `tests/Feature/Http/Requests/UpdateTranslationRequestTest.php`
- [ ] Tests similares pero ignorando registro actual

#### **Paso 13: Tests de Componentes Livewire**
- [ ] Crear `tests/Feature/Livewire/Admin/Translations/IndexTest.php`:
  - Tests de autorización
  - Tests de listado con paginación
  - Tests de búsqueda
  - Tests de filtros (modelo, idioma, translatable_id)
  - Tests de ordenación
  - Tests de eliminación
  - Tests de estado vacío
- [ ] Crear `tests/Feature/Livewire/Admin/Translations/CreateTest.php`:
  - Tests de autorización
  - Tests de creación exitosa
  - Tests de validación
  - Tests de selectores dinámicos
  - Tests de unicidad
- [ ] Crear `tests/Feature/Livewire/Admin/Translations/EditTest.php`:
  - Tests de autorización
  - Tests de actualización exitosa
  - Tests de validación
- [ ] Crear `tests/Feature/Livewire/Admin/Translations/ShowTest.php` (si se implementa):
  - Tests de autorización
  - Tests de visualización

---

### **Fase 7: Optimizaciones y Mejoras**

#### **Paso 14: Optimizaciones**
- [ ] Añadir índices en consultas si es necesario
- [ ] Implementar caché para listado de modelos traducibles
- [ ] Optimizar eager loading en Index
- [ ] Añadir debounce en búsqueda
- [ ] Verificar rendimiento con muchos registros

#### **Paso 15: Documentación y Finalización**
- [ ] Crear documentación técnica en `docs/admin-translations-crud.md`
- [ ] Actualizar `docs/planificacion_pasos.md` marcando paso 3.5.13 como completado
- [ ] Ejecutar `vendor/bin/pint --dirty` para formatear código
- [ ] Ejecutar tests completos: `php artisan test --filter=Translation`
- [ ] Verificar que no haya errores de linting
- [ ] Revisar código para asegurar consistencia con otros CRUDs

---

## 🎨 Consideraciones de Diseño

### Componentes UI a Reutilizar
- `x-ui.card` - Para contenedores
- `x-ui.breadcrumbs` - Para navegación
- `x-ui.search-input` - Para búsqueda
- `x-ui.empty-state` - Para estado vacío
- `flux:button` - Para botones
- `flux:field` - Para campos de formulario
- `flux:select` - Para selects
- `flux:textarea` - Para textarea
- `flux:modal` - Para modales de confirmación

### Campos Traducibles por Modelo

#### Program
- `name` - Nombre del programa
- `description` - Descripción del programa

#### Setting
- `value` - Valor de la configuración (si es traducible)

### Validaciones Especiales
- La combinación (translatable_type, translatable_id, language_id, field) debe ser única
- El translatable_id debe existir en la tabla correspondiente
- El language_id debe corresponder a un idioma activo
- El campo debe ser válido para el modelo seleccionado

---

## 📝 Notas Importantes

1. **Sin SoftDeletes**: Las traducciones se eliminan directamente (no tienen SoftDeletes) ya que son datos derivados
2. **Relaciones Polimórficas**: Usar eager loading correctamente para evitar N+1
3. **Validación de Unicidad**: Implementar validación personalizada para la combinación única
4. **Selectores Dinámicos**: Los selects deben actualizarse dinámicamente según la selección anterior
5. **Integración con Sistema Existente**: Asegurar que las traducciones creadas/actualizadas funcionen con el sistema i18n existente

---

## ✅ Criterios de Aceptación

- [ ] Todos los componentes Livewire funcionan correctamente
- [ ] Los formularios validan correctamente
- [ ] Los filtros y búsqueda funcionan
- [ ] La autorización está implementada correctamente
- [ ] Los tests pasan (cobertura mínima 80%)
- [ ] El código sigue las convenciones del proyecto
- [ ] El diseño es responsive y moderno
- [ ] No hay errores de linting
- [ ] La documentación está completa

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan completado - Pendiente de implementación
