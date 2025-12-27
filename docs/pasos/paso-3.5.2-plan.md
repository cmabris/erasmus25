# Plan de Desarrollo: Paso 3.5.2 - CRUD de Programas en Panel de Administración

Este documento establece el plan detallado para desarrollar el CRUD completo de Programas en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión (CRUD) de Programas Erasmus+ en el panel de administración con:
- Listado moderno con tabla interactiva
- Formularios de creación y edición
- Vista de detalle
- Funcionalidades avanzadas: activar/desactivar, ordenar, subir imágenes, gestionar traducciones
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos de Desarrollo (12 Pasos)

### ✅ **Fase 1: Estructura Base y Listado** (MVP)

#### **Paso 1: Componente Index (Listado)**
- [ ] Crear componente Livewire `Admin\Programs\Index`
- [ ] Implementar propiedades públicas:
  - `Collection $programs` - Lista de programas
  - `string $search = ''` - Búsqueda
  - `string $sortField = 'order'` - Campo de ordenación
  - `string $sortDirection = 'asc'` - Dirección de ordenación
  - `bool $showActiveOnly = false` - Filtro de activos
  - `int $perPage = 15` - Elementos por página
- [ ] Implementar métodos:
  - `mount()` - Inicialización
  - `updatedSearch()` - Búsqueda reactiva
  - `sortBy($field)` - Ordenación
  - `toggleActive($programId)` - Activar/desactivar
  - `delete($programId)` - Eliminar con confirmación
  - `render()` - Renderizado con paginación
- [ ] Implementar autorización con `ProgramPolicy`
- [ ] Crear vista `livewire/admin/programs/index.blade.php`:
  - Tabla responsive con Flux UI
  - Búsqueda con componente `x-ui.search-input`
  - Filtros (activos/inactivos)
  - Botones de acción (ver, editar, eliminar, activar/desactivar)
  - Paginación
  - Estado vacío con `x-ui.empty-state`
  - Breadcrumbs con `x-ui.breadcrumbs`

#### **Paso 2: Rutas y Navegación**
- [ ] Añadir rutas en `routes/web.php`:
  - `GET /admin/programas` → `Admin\Programs\Index`
  - `GET /admin/programas/crear` → `Admin\Programs\Create`
  - `GET /admin/programas/{program}` → `Admin\Programs\Show`
  - `GET /admin/programas/{program}/editar` → `Admin\Programs\Edit`
- [ ] Actualizar sidebar para incluir enlace a programas
- [ ] Añadir traducciones necesarias en `lang/{es,en}/common.php`

---

### ✅ **Fase 2: Creación y Edición**

#### **Paso 3: Componente Create (Crear)**
- [ ] Crear componente Livewire `Admin\Programs\Create`
- [ ] Implementar propiedades públicas:
  - `string $code = ''`
  - `string $name = ''`
  - `string $slug = ''`
  - `string $description = ''`
  - `bool $is_active = true`
  - `int $order = 0`
  - `?UploadedFile $image = null` - Para imagen (Laravel Media Library)
- [ ] Implementar métodos:
  - `mount()` - Inicialización
  - `updatedName()` - Generar slug automáticamente
  - `store()` - Guardar usando `StoreProgramRequest`
  - `render()` - Renderizado
- [ ] Implementar autorización con `ProgramPolicy::create()`
- [ ] Crear vista `livewire/admin/programs/create.blade.php`:
  - Formulario con Flux UI (`flux:field`, `flux:input`, `flux:textarea`, `flux:checkbox`)
  - Validación en tiempo real con `wire:model.live`
  - Subida de imagen con preview
  - Botones de acción (guardar, cancelar)
  - Breadcrumbs

#### **Paso 4: Componente Edit (Editar)**
- [ ] Crear componente Livewire `Admin\Programs\Edit`
- [ ] Implementar propiedades públicas (igual que Create)
- [ ] Implementar métodos:
  - `mount(Program $program)` - Cargar datos del programa
  - `updatedName()` - Generar slug automáticamente
  - `update()` - Actualizar usando `UpdateProgramRequest`
  - `removeImage()` - Eliminar imagen
  - `render()` - Renderizado
- [ ] Implementar autorización con `ProgramPolicy::update()`
- [ ] Crear vista `livewire/admin/programs/edit.blade.php`:
  - Similar a Create pero con datos precargados
  - Mostrar imagen actual si existe
  - Opción de eliminar imagen

#### **Paso 5: Adaptar FormRequests**
- [ ] Revisar `StoreProgramRequest`:
  - Añadir validación para imagen (opcional, max 5MB, tipos: jpg, png, webp)
  - Añadir mensajes de error personalizados
  - Verificar autorización con Policy
- [ ] Revisar `UpdateProgramRequest`:
  - Añadir validación para imagen (opcional)
  - Añadir mensajes de error personalizados
  - Verificar autorización con Policy

---

### ✅ **Fase 3: Vista Detalle y Funcionalidades Avanzadas**

#### **Paso 6: Componente Show (Detalle)**
- [ ] Crear componente Livewire `Admin\Programs\Show`
- [ ] Implementar propiedades públicas:
  - `Program $program` - Programa a mostrar
  - `Collection $calls` - Convocatorias relacionadas
  - `Collection $newsPosts` - Noticias relacionadas
- [ ] Implementar métodos:
  - `mount(Program $program)` - Cargar programa y relaciones
  - `delete()` - Eliminar con confirmación
  - `toggleActive()` - Activar/desactivar
  - `render()` - Renderizado
- [ ] Implementar autorización con `ProgramPolicy::view()`
- [ ] Crear vista `livewire/admin/programs/show.blade.php`:
  - Card principal con información del programa
  - Imagen destacada si existe
  - Sección de estadísticas (convocatorias, noticias)
  - Listado de convocatorias relacionadas
  - Listado de noticias relacionadas
  - Botones de acción (editar, eliminar, activar/desactivar)
  - Breadcrumbs

#### **Paso 7: Gestión de Imágenes (Laravel Media Library)**
- [ ] Verificar configuración de Media Library en modelo `Program`
- [ ] Añadir trait `HasMedia` al modelo si no existe
- [ ] Implementar registro de colección de medios:
  - `'image'` - Imagen destacada del programa
- [ ] Actualizar métodos `store()` y `update()` para guardar imagen
- [ ] Crear componente Blade opcional para preview de imagen
- [ ] Añadir validación de tipos y tamaños de imagen

#### **Paso 8: Gestión de Traducciones**
- [ ] Verificar modelo `Translation` y su relación polimórfica
- [ ] Crear componente Livewire opcional `Admin\Programs\Translations` o integrar en Edit
- [ ] Implementar formulario para gestionar traducciones:
  - Campos traducibles: `name`, `description`
  - Selector de idioma
  - Guardar/actualizar traducciones
- [ ] Mostrar traducciones disponibles en vista Show

#### **Paso 9: Ordenamiento de Programas**
- [ ] Añadir funcionalidad de drag & drop para ordenar (opcional con Alpine.js)
- [ ] O implementar botones arriba/abajo para cambiar orden
- [ ] Actualizar método `updateOrder()` en componente Index
- [ ] Validar que el orden sea único o permitir duplicados

---

### ✅ **Fase 4: UX y Optimización**

#### **Paso 10: Mejoras de UX**
- [ ] Añadir confirmaciones para acciones destructivas (eliminar)
- [ ] Implementar notificaciones de éxito/error con Flux UI
- [ ] Añadir estados de carga (`wire:loading`)
- [ ] Mejorar responsive design para móviles
- [ ] Añadir tooltips informativos
- [ ] Implementar búsqueda avanzada (por código, nombre, descripción)

#### **Paso 11: Optimización**
- [ ] Implementar eager loading para relaciones (calls, newsPosts)
- [ ] Añadir índices de base de datos si es necesario
- [ ] Implementar caché para listados si hay muchos programas
- [ ] Optimizar consultas de búsqueda

---

### ✅ **Fase 5: Calidad y Documentación**

#### **Paso 12: Tests**
- [ ] Crear test `Admin\Programs\IndexTest`:
  - Verificar autorización
  - Verificar listado de programas
  - Verificar búsqueda
  - Verificar ordenación
  - Verificar filtros
  - Verificar paginación
- [ ] Crear test `Admin\Programs\CreateTest`:
  - Verificar autorización
  - Verificar creación exitosa
  - Verificar validación
  - Verificar subida de imagen
- [ ] Crear test `Admin\Programs\EditTest`:
  - Verificar autorización
  - Verificar edición exitosa
  - Verificar validación
  - Verificar eliminación de imagen
- [ ] Crear test `Admin\Programs\ShowTest`:
  - Verificar autorización
  - Verificar visualización
  - Verificar eliminación
  - Verificar activar/desactivar

#### **Paso 13: Documentación**
- [ ] Documentar componentes creados
- [ ] Actualizar documentación general
- [ ] Crear resumen del desarrollo
- [ ] Documentar funcionalidades avanzadas (imágenes, traducciones)

---

## 🏗️ Estructura de Archivos

```
app/Livewire/Admin/Programs/
  ├── Index.php                    [NUEVO]
  ├── Create.php                   [NUEVO]
  ├── Edit.php                     [NUEVO]
  └── Show.php                     [NUEVO]

resources/views/livewire/admin/programs/
  ├── index.blade.php              [NUEVO]
  ├── create.blade.php             [NUEVO]
  ├── edit.blade.php               [NUEVO]
  └── show.blade.php               [NUEVO]

resources/views/components/admin/programs/
  └── image-preview.blade.php      [NUEVO - opcional]

app/Http/Requests/
  ├── StoreProgramRequest.php       [MODIFICAR]
  └── UpdateProgramRequest.php      [MODIFICAR]

app/Models/
  └── Program.php                  [MODIFICAR - añadir HasMedia]

routes/web.php                     [MODIFICAR]

lang/{es,en}/common.php            [MODIFICAR]

tests/Feature/Livewire/Admin/Programs/
  ├── IndexTest.php                [NUEVO]
  ├── CreateTest.php               [NUEVO]
  ├── EditTest.php                 [NUEVO]
  └── ShowTest.php                 [NUEVO]
```

---

## 🎨 Diseño Visual

### Vista Index (Listado)
```
┌─────────────────────────────────────────────────────────────┐
│  Programas Erasmus+                    [+ Crear Programa]   │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  [🔍 Buscar...]  [Filtro: Todos/Activos]  [Ordenar] │ │
│  └───────────────────────────────────────────────────────┘ │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  Tabla:                                               │ │
│  │  Orden │ Código │ Nombre │ Estado │ Acciones         │ │
│  │  ──────┼────────┼────────┼────────┼───────────────── │ │
│  │   1    │ ERASM+ │ ...    │ ✅     │ [👁️] [✏️] [🗑️] │ │
│  └───────────────────────────────────────────────────────┘ │
│  [Paginación]                                               │
└─────────────────────────────────────────────────────────────┘
```

### Vista Create/Edit (Formulario)
```
┌─────────────────────────────────────────────────────────────┐
│  Crear/Editar Programa                                      │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  Código:        [___________]  *                        │ │
│  │  Nombre:        [___________]  *                        │ │
│  │  Slug:          [___________]  (auto-generado)          │ │
│  │  Descripción:   [___________]                           │ │
│  │                 [___________]                           │ │
│  │  Orden:         [___]                                   │ │
│  │  Activo:        [✓]                                     │ │
│  │  Imagen:        [Subir archivo] [Preview]               │ │
│  └───────────────────────────────────────────────────────┘ │
│  [Cancelar]  [Guardar]                                      │
└─────────────────────────────────────────────────────────────┘
```

### Vista Show (Detalle)
```
┌─────────────────────────────────────────────────────────────┐
│  Programa: Erasmus+                                         │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  [Imagen]        Código: ERASM+                        │ │
│  │                  Nombre: Erasmus+                       │ │
│  │                  Estado: ✅ Activo                      │ │
│  │                  Orden: 1                               │ │
│  │                  Descripción: ...                       │ │
│  └───────────────────────────────────────────────────────┘ │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  Estadísticas:                                         │ │
│  │  • Convocatorias: 5                                    │ │
│  │  • Noticias: 12                                        │ │
│  └───────────────────────────────────────────────────────┘ │
│  [Editar]  [Eliminar]  [Activar/Desactivar]               │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚦 Priorización Recomendada

### **Sprint 1** (MVP - 2-3 días)
- ✅ Pasos 1, 2, 3, 4, 5
- CRUD básico funcional sin imágenes ni traducciones

### **Sprint 2** (Funcionalidades Avanzadas - 1-2 días)
- ✅ Pasos 6, 7, 8, 9
- Vista detalle, imágenes, traducciones, ordenamiento

### **Sprint 3** (Pulido - 1 día)
- ✅ Pasos 10, 11, 12, 13
- Optimización, tests y documentación

**Total estimado: 4-6 días de desarrollo**

---

## 🔧 Tecnologías y Componentes a Usar

- **Livewire 3**: Componentes reactivos
- **Flux UI v2**: Componentes UI base (`flux:field`, `flux:input`, `flux:textarea`, `flux:button`, `flux:checkbox`, `flux:callout`)
- **Tailwind CSS v4**: Estilos y responsive
- **Heroicons**: Iconos
- **Laravel Media Library**: Gestión de imágenes
- **Laravel Permission**: Verificación de permisos
- **Alpine.js**: Interactividad (drag & drop opcional)

---

## 📝 Notas Importantes

1. **Reutilización**: Aprovechar componentes existentes (`x-ui.card`, `x-ui.stat-card`, `x-ui.search-input`, `x-ui.empty-state`, `x-ui.breadcrumbs`)
2. **Consistencia**: Mantener estilo similar al Dashboard y área pública
3. **Performance**: Optimizar consultas desde el inicio, usar eager loading
4. **Seguridad**: Verificar permisos en cada acción, validar datos
5. **Escalabilidad**: Diseñar para futuras expansiones (más campos, más relaciones)

---

## 🎯 Resultado Esperado

Un CRUD completo y moderno de Programas que:
- ✅ Permite gestionar programas de forma intuitiva
- ✅ Incluye funcionalidades avanzadas (imágenes, traducciones, ordenamiento)
- ✅ Es responsive y accesible
- ✅ Sigue las mejores prácticas de UX/UI
- ✅ Está completamente testeado
- ✅ Está documentado

---

**📄 Documento Completo**: Este plan detallado para el desarrollo del paso 3.5.2

**Fecha**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Listo para implementación

