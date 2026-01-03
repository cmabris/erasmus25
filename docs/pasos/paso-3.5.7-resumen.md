# Resumen Ejecutivo: Paso 3.5.7 - Gestión de Documentos en Panel de Administración

## 🎯 Objetivo

Desarrollar un sistema completo de gestión (CRUD) de Documentos en el panel de administración con:
- Listado moderno con tabla interactiva y filtros avanzados
- Formularios de creación y edición con subida de archivos
- Vista de detalle con información completa
- **SoftDeletes**: Los documentos nunca se eliminan permanentemente, solo se marcan como eliminados
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones con consentimientos de medios
- Gestión de archivos mediante Laravel Media Library
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos Principales (18 Pasos)

### ✅ **Fase 1: Preparación Base**

1. **Implementar SoftDeletes en Document** (Paso 1)
   - Crear migración para añadir `deleted_at`
   - Actualizar modelo con trait `SoftDeletes`
   - Verificar relaciones

2. **Actualizar FormRequests con Autorización** (Paso 2)
   - Actualizar `StoreDocumentRequest` con autorización y validación de archivo
   - Actualizar `UpdateDocumentRequest` con autorización y validación de archivo
   - Añadir mensajes de error personalizados

---

### ✅ **Fase 2: Estructura Base y Listado** (MVP)

3. **Componente Index (Listado)** (Paso 3)
   - Tabla responsive con búsqueda, filtros avanzados y ordenación
   - Filtros: categoría, programa, año académico, tipo, estado activo, eliminados
   - Paginación y acciones (ver, editar, eliminar, restaurar)
   - Modales de confirmación
   - Autorización con `DocumentPolicy`

---

### ✅ **Fase 3: Creación y Edición**

4. **Componente Create (Crear)** (Paso 4)
   - Formulario con Flux UI
   - Validación en tiempo real
   - Generación automática de slug
   - **Subida de archivos con FilePond (Spatie Livewire-FilePond)**:
     - Trait `WithFilePond` en componente
     - Componente `<x-filepond::upload>` en vista
     - Drag & drop mejorado
     - Preview automático para imágenes y PDFs
     - Validación en frontend (tipo y tamaño)
     - Indicador de progreso visual

5. **Componente Edit (Editar)** (Paso 5)
   - Similar a Create pero con datos precargados
   - **Gestión de archivo actual**:
     - Mostrar información del archivo existente
     - Opción de descargar archivo actual
     - Opción de eliminar archivo actual (sin subir uno nuevo)
   - **Subida de nuevo archivo**:
     - Componente FilePond para reemplazar archivo
     - Al subir nuevo archivo, reemplaza automáticamente el anterior
   - Validación en tiempo real

---

### ✅ **Fase 4: Vista Detalle**

6. **Componente Show (Detalle)** (Paso 6)
   - Información completa del documento
   - Preview/descarga de archivo
   - Listado de consentimientos de medios asociados
   - Estadísticas: contador de descargas, fechas, usuarios

---

### ✅ **Fase 5: Gestión de Archivos**

7. **Verificar Configuración de Media Collections** (Paso 7)
   - Verificar colección `file` en modelo Document (ya configurada)
   - Verificar tipos MIME aceptados (ya configurados)
   - Añadir conversiones si es necesario (thumbnails para imágenes, previews para PDFs)

8. **Verificar Configuración de FilePond** (Paso 8)
   - Verificar que `spatie/livewire-filepond` esté instalado (ya está en uso)
   - Verificar configuración en `resources/js/app.js` (ya configurado)
   - Usar componente `<x-filepond::upload>` siguiendo el patrón de Resoluciones/Noticias
   - Configurar tipos MIME y tamaño máximo según modelo Document

---

### ✅ **Fase 6: Rutas y Navegación**

9. **Configurar Rutas** (Paso 9)
   - Rutas en `/admin/documentos/*`
   - Middleware de autenticación

10. **Actualizar Navegación** (Paso 10)
    - Añadir enlace en sidebar
    - Añadir traducciones

---

### ✅ **Fase 7: Validación y Optimizaciones**

11. **Validar Relaciones Antes de Eliminar** (Paso 11)
    - Verificar consentimientos de medios asociados
    - Mensajes de error claros

12. **Optimizaciones** (Paso 12)
    - Añadir `withCount` para evitar N+1
    - Eager loading de relaciones
    - Verificar índices de BD

---

### ✅ **Fase 8: Gestión de Consentimientos (Opcional)**

13. **Mostrar Consentimientos Asociados** (Paso 13)
    - Listar consentimientos que referencian el documento
    - Información básica de cada consentimiento

---

### ✅ **Fase 9: Tests**

14. **Tests de Componentes Livewire** (Paso 14)
    - Tests de Index, Create, Edit y Show
    - Tests de autorización, validación y acciones
    - Tests de gestión de archivos

15. **Tests de FormRequests** (Paso 15)
    - Tests de `StoreDocumentRequest` y `UpdateDocumentRequest`

---

### ✅ **Fase 10: Mejoras y Pulido**

16. **Mejoras de UX** (Paso 16)
    - Indicadores de carga
    - Mensajes de éxito/error
    - Preview mejorado de archivos

17. **Validación y Formateo** (Paso 17)
    - Ejecutar Pint
    - Verificar linter
    - Verificar responsive

18. **Documentación** (Paso 18)
    - Documentación técnica del CRUD

---

## 🔑 Características Principales

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar documentos
- ✅ **SoftDeletes**: Los documentos nunca se eliminan permanentemente por defecto
- ✅ **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- ✅ **Gestión de Archivos**: Subida y gestión mediante Laravel Media Library y **FilePond (Spatie Livewire-FilePond)**
- ✅ **Filtros Avanzados**: Categoría, programa, año académico, tipo, estado activo, eliminados
- ✅ **Búsqueda**: Búsqueda por título y descripción
- ✅ **Preview de Archivos**: Preview para imágenes y PDFs
- ✅ **Validación de Relaciones**: No se puede eliminar si tiene consentimientos asociados
- ✅ **Generación Automática de Slug**: Desde el título del documento
- ✅ **Autorización**: Control de acceso mediante `DocumentPolicy`
- ✅ **Responsive**: Diseño adaptativo usando Flux UI y Tailwind CSS v4
- ✅ **Tests Completos**: Cobertura completa de funcionalidades

---

## 📁 Estructura de Archivos

```
app/
├── Livewire/
│   └── Admin/
│       └── Documents/
│           ├── Index.php
│           ├── Create.php
│           ├── Edit.php
│           └── Show.php
├── Http/
│   └── Requests/
│       ├── StoreDocumentRequest.php (actualizar)
│       └── UpdateDocumentRequest.php (actualizar)
└── Models/
    └── Document.php (actualizar con SoftDeletes)

database/
└── migrations/
    └── YYYY_MM_DD_HHMMSS_add_deleted_at_to_documents_table.php (crear)

resources/
└── views/
    └── livewire/
        └── admin/
            └── documents/
                ├── index.blade.php
                ├── create.blade.php
                ├── edit.blade.php
                └── show.blade.php

routes/
└── web.php (actualizar)

tests/
└── Feature/
    └── Livewire/
        └── Admin/
            └── Documents/
                ├── IndexTest.php
                ├── CreateTest.php
                ├── EditTest.php
                └── ShowTest.php
```

---

## 🎨 Componentes Reutilizables

- `x-ui.card` - Tarjetas contenedoras
- `x-ui.breadcrumbs` - Breadcrumbs de navegación
- `x-ui.search-input` - Campo de búsqueda
- `x-ui.empty-state` - Estado vacío
- `flux:button` - Botones con variantes
- `flux:field` - Campos de formulario
- `flux:input` - Inputs
- `flux:select` - Selects
- `flux:textarea` - Textareas
- `flux:switch` - Switches
- `flux:badge` - Badges para estados
- `flux:modal` - Modales de confirmación
- `x-filepond::upload` - Componente de subida de archivos (Spatie Livewire-FilePond, ya configurado)

---

## 📝 Notas Importantes

### SoftDeletes
- Los documentos **nunca** se eliminan permanentemente por defecto
- Solo se marcan como eliminados (`deleted_at`)
- Solo super-admin puede realizar `forceDelete()`
- Antes de `forceDelete()`, validar que no existan relaciones con `MediaConsent`

### Validación de Relaciones
- Antes de eliminar, verificar si tiene consentimientos de medios asociados
- Si tiene relaciones, mostrar error y no permitir eliminación

### Gestión de Archivos
- Usar Laravel Media Library (colección `file`, ya configurada)
- Tipos MIME aceptados (según modelo Document):
  - PDF, Word, Excel, PowerPoint, texto, CSV, imágenes
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
- Validar que el slug sea único

### Tipos de Documento
- `convocatoria`, `modelo`, `seguro`, `consentimiento`, `guia`, `faq`, `otro`

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Resumen ejecutivo completado

