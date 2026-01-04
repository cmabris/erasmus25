# Resumen Ejecutivo: Paso 3.5.9 - CRUD de Eventos en Panel de Administración

## 🎯 Objetivo

Desarrollar un sistema completo de gestión (CRUD) de Eventos Erasmus+ en el panel de administración con:
- Listado moderno con tabla interactiva y vista de calendario
- Formularios de creación y edición con gestión de fechas
- Vista de detalle con información completa
- Funcionalidades avanzadas: vista de calendario interactiva (mes/semana/día), asociación con programas y convocatorias, subida de imágenes
- **SoftDeletes**: Los eventos nunca se eliminan permanentemente, solo se marcan como eliminados
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos Principales (16 Pasos en 8 Fases)

### ✅ **Fase 1: Preparación Base** (3 pasos)
1. **Implementar SoftDeletes en ErasmusEvent** - Añadir SoftDeletes al modelo
2. **Implementar MediaLibrary en ErasmusEvent** - Añadir gestión de imágenes
3. **Actualizar FormRequests** - Actualizar validaciones con imágenes, autorización y validaciones de relaciones

### ✅ **Fase 2: Componente Index (Listado y Calendario)** (4 pasos)
4. **Componente Index - Estructura Base** - Crear componente con propiedades y métodos base
5. **Componente Index - Métodos de Acción** - Implementar eliminación, restauración y navegación de calendario
6. **Vista Index - Listado** - Tabla responsive con filtros avanzados
7. **Vista Index - Calendario** - Vista de calendario interactiva (mes/semana/día)

### ✅ **Fase 3: Componente Create (Crear)** (1 paso)
8. **Componente Create** - Formulario completo con todas las secciones (fechas, asociaciones, imagen)

### ✅ **Fase 4: Componente Edit (Editar)** (1 paso)
9. **Componente Edit** - Formulario de edición con gestión de imágenes existentes

### ✅ **Fase 5: Componente Show (Detalle)** (1 paso)
10. **Componente Show** - Vista de detalle con información completa y acciones

### ✅ **Fase 6: Rutas y Navegación** (2 pasos)
11. **Configurar Rutas** - Añadir rutas de administración para eventos
12. **Actualizar Navegación** - Añadir enlace en sidebar de administración

### ✅ **Fase 7: Optimizaciones y Mejoras** (2 pasos)
13. **Optimizaciones de Consultas** - Eager loading, índices de BD
14. **Mejoras de UX** - Validación en tiempo real, feedback visual, responsive

### ✅ **Fase 8: Testing** (2 pasos)
15. **Tests de Componentes** - Tests unitarios para cada componente
16. **Tests de Integración** - Tests de flujos completos

---

## 🎨 Características Principales

### Vista de Calendario Interactiva
- **Vista mensual**: Grid de calendario con eventos por día
- **Vista semanal**: Vista de semana con eventos detallados
- **Vista diaria**: Lista de eventos del día seleccionado
- **Navegación fluida**: Botones anterior/siguiente, botón "Hoy"
- **Filtros en calendario**: Por programa, tipo de evento, fecha

### Gestión de Fechas
- **Fechas de inicio y fin**: Con validación de que fin sea posterior a inicio
- **Eventos de todo el día**: Checkbox para eventos sin hora específica
- **Formato datetime-local**: Para selección de fecha y hora
- **Validación en tiempo real**: Feedback inmediato al usuario

### Asociaciones
- **Programa**: Asociación opcional con programa Erasmus+
- **Convocatoria**: Asociación opcional con convocatoria (dependiente de programa)
- **Validación de relaciones**: Si hay convocatoria, debe pertenecer al programa seleccionado

### Gestión de Imágenes
- **Subida de imágenes**: Múltiples imágenes por evento
- **Conversiones automáticas**: Thumbnail, medium, large
- **Gestión avanzada**: Soft delete, restauración, eliminación permanente
- **Preview en formularios**: Vista previa antes de guardar

### Filtros Avanzados
- **Búsqueda**: Por título y descripción
- **Filtro por programa**: Select con programas disponibles
- **Filtro por convocatoria**: Select dependiente de programa
- **Filtro por tipo**: Select con tipos de eventos
- **Filtro por fecha**: Date picker para filtrar por fecha específica
- **Filtro de eliminados**: Toggle para mostrar/ocultar eventos eliminados

---

## 🔧 Tecnologías y Patrones

### Laravel
- **SoftDeletes**: Para eliminación suave de eventos
- **MediaLibrary**: Para gestión de imágenes
- **FormRequests**: Para validación y autorización
- **Policies**: Para control de acceso
- **Eager Loading**: Para optimizar consultas

### Livewire
- **Computed Properties**: Para datos calculados (eventos, calendario)
- **URL Binding**: Para mantener estado en URL
- **WithPagination**: Para paginación
- **FilePond**: Para subida de archivos

### Flux UI
- **Componentes**: Button, Input, Select, Modal, Badge, etc.
- **Formularios**: Field, Textarea, Checkbox, Toggle
- **Tablas**: Para listado de eventos
- **Modales**: Para confirmaciones y acciones

### Tailwind CSS v4
- **Responsive**: Diseño adaptativo para móviles, tablets y desktop
- **Dark Mode**: Soporte para modo oscuro
- **Utilidades**: Para espaciado, colores, tipografía

---

## 📊 Estructura de Archivos

```
app/
├── Livewire/
│   └── Admin/
│       └── Events/
│           ├── Index.php          # Listado y calendario
│           ├── Create.php         # Crear evento
│           ├── Edit.php           # Editar evento
│           └── Show.php           # Ver detalle
├── Http/
│   └── Requests/
│       ├── StoreErasmusEventRequest.php    # Validación crear
│       └── UpdateErasmusEventRequest.php   # Validación editar
├── Models/
│   └── ErasmusEvent.php           # Modelo (con SoftDeletes y MediaLibrary)
└── Policies/
    └── ErasmusEventPolicy.php     # Autorización (ya existe)

resources/
└── views/
    └── livewire/
        └── admin/
            └── events/
                ├── index.blade.php    # Vista listado/calendario
                ├── create.blade.php   # Vista crear
                ├── edit.blade.php     # Vista editar
                └── show.blade.php     # Vista detalle

routes/
└── web.php                          # Rutas de administración

tests/
└── Feature/
    └── Admin/
        └── Events/
            ├── IndexTest.php
            ├── CreateTest.php
            ├── EditTest.php
            └── ShowTest.php
```

---

## 🚀 Estrategia de Desarrollo

### Enfoque Iterativo
1. **Primero**: Completar CRUD básico (Index, Create, Edit, Show) sin calendario
2. **Segundo**: Añadir vista de calendario al Index
3. **Tercero**: Añadir gestión de imágenes
4. **Cuarto**: Optimizaciones y mejoras de UX
5. **Quinto**: Tests completos

### Prioridades
1. **Alta**: CRUD básico funcional
2. **Alta**: SoftDeletes y autorización
3. **Media**: Vista de calendario
4. **Media**: Gestión de imágenes
5. **Baja**: Optimizaciones avanzadas

### Reutilización
- Reutilizar lógica del componente público `Events\Calendar` para la vista de calendario
- Seguir el mismo patrón de otros CRUDs (NewsTags, DocumentCategories)
- Reutilizar componentes Flux UI existentes
- Aprovechar scopes del modelo `ErasmusEvent` para filtros

---

## ✅ Criterios de Éxito

- [ ] Todos los componentes funcionan correctamente
- [ ] SoftDeletes implementado y probado
- [ ] MediaLibrary funcionando con conversiones
- [ ] Vista de calendario funcional (mes/semana/día)
- [ ] Filtros avanzados funcionando
- [ ] Validaciones completas
- [ ] Autorización por roles funcionando
- [ ] Tests con cobertura mínima del 80%
- [ ] Responsive en todos los dispositivos
- [ ] Sin errores de linter
- [ ] Código formateado con Pint

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Pendiente de implementación

