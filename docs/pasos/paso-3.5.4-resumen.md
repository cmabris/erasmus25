# Resumen Ejecutivo: Paso 3.5.4 - CRUD de Convocatorias en Panel de Administración

## 🎯 Objetivo

Desarrollar un sistema completo de gestión (CRUD) de Convocatorias en el panel de administración con:
- Listado moderno con tabla interactiva y filtros avanzados
- Formularios de creación y edición completos
- Vista de detalle con gestión de fases y resoluciones
- Funcionalidades avanzadas: cambio de estado, publicación, gestión de fases y resoluciones
- **SoftDeletes**: Las convocatorias nunca se eliminan permanentemente, solo se marcan como eliminadas
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos Principales (14 Pasos)

### ✅ **Fase 1: Preparación Base** (2 Pasos)

1. **Implementar SoftDeletes en Call** (Paso 1)
   - Añadir trait `SoftDeletes` al modelo `Call`
   - Crear migración para columna `deleted_at`
   - Ejecutar migración

2. **Actualizar FormRequests con Autorización** (Paso 2)
   - Añadir autorización con `CallPolicy` en `StoreCallRequest` y `UpdateCallRequest`
   - Añadir mensajes de error personalizados
   - Validar relaciones y formatos (destinations, scoring_table)

---

### ✅ **Fase 2: Estructura Base y Listado** (2 Pasos)

3. **Componente Index (Listado)** (Paso 3)
   - Crear componente Livewire `Admin\Calls\Index`
   - Tabla responsive con búsqueda, filtros avanzados (programa, año académico, tipo, modalidad, estado) y ordenación
   - Paginación y acciones (ver, editar, eliminar, cambiar estado, publicar)
   - Modales de confirmación (eliminar, restaurar, forceDelete)
   - Autorización con `CallPolicy`

4. **Rutas y Navegación** (Paso 4)
   - Configurar rutas `/admin/convocatorias/*`
   - Actualizar sidebar y traducciones

---

### ✅ **Fase 3: Creación y Edición** (2 Pasos)

5. **Componente Create (Crear)** (Paso 5)
   - Formulario completo con Flux UI
   - Gestión dinámica de destinos (añadir/eliminar)
   - Gestión dinámica de baremo (tabla con campos)
   - Validación en tiempo real
   - Generación automática de slug

6. **Componente Edit (Editar)** (Paso 6)
   - Similar a Create pero con datos precargados
   - Mostrar información adicional (fecha creación, última actualización)

---

### ✅ **Fase 4: Vista Detalle y Funcionalidades Avanzadas** (4 Pasos)

7. **Componente Show (Detalle)** (Paso 7)
   - Información completa de la convocatoria
   - Estadísticas (aplicaciones, fases, resoluciones)
   - Botones de acción (editar, cambiar estado, publicar, eliminar)

8. **Gestión de Estados** (Paso 8)
   - Implementar cambio de estado con validación de transiciones
   - Actualizar `published_at` y `closed_at` según corresponda
   - Mostrar badges de color según estado

9. **Gestión de Fases** (Paso 9)
   - Sección en Show para gestionar fases
   - Crear, editar, marcar como actual, eliminar fases
   - Mostrar listado ordenado con fase actual destacada

10. **Gestión de Resoluciones** (Paso 10)
    - Sección en Show para gestionar resoluciones
    - Crear, editar, publicar, eliminar resoluciones
    - Subir PDFs de resoluciones (Laravel Media Library)
    - Mostrar enlaces de descarga

---

### ✅ **Fase 5: Optimizaciones y Mejoras** (2 Pasos)

11. **Optimización de Consultas** (Paso 11)
    - Implementar eager loading en Index y Show
    - Usar `withCount()` para estadísticas

12. **Validaciones y Mensajes** (Paso 12)
    - Añadir validaciones en tiempo real
    - Mensajes de éxito/error personalizados
    - Validar relaciones antes de eliminar

---

### ✅ **Fase 6: Testing** (2 Pasos)

13. **Tests de Componentes Livewire** (Paso 13)
    - Tests para Index, Create, Edit, Show
    - Tests de autorización, validación, eliminación, restauración

14. **Tests de FormRequests** (Paso 14)
    - Verificar validación y autorización en FormRequests

---

## 🔑 Características Clave

### Campos Principales
- **Información básica**: Programa, Año Académico, Título, Tipo, Modalidad
- **Plazas y destinos**: Número de plazas, Array de destinos (JSON)
- **Fechas**: Fechas estimadas de inicio y fin
- **Contenido**: Requisitos, Documentación, Criterios de selección
- **Baremo**: Tabla de evaluación (JSON)
- **Estado**: borrador → abierta → cerrada → archivada (o en_baremacion → resuelta)

### Estados de Convocatoria
- **borrador**: En preparación
- **abierta**: Abierta para solicitudes
- **cerrada**: Cerrada, no acepta solicitudes
- **en_baremacion**: En proceso de baremación
- **resuelta**: Resolución publicada
- **archivada**: Archivada

### Funcionalidades Especiales
- **Gestión dinámica de destinos**: Añadir/eliminar destinos en tiempo real
- **Gestión dinámica de baremo**: Tabla editable con conceptos y puntos
- **Cambio de estado**: Validación de transiciones de estado
- **Publicación**: Establecer `published_at` al publicar
- **Gestión de fases**: Crear, editar, marcar como actual
- **Gestión de resoluciones**: Crear, editar, publicar, subir PDFs

---

## 📊 Estructura de Archivos

```
app/Livewire/Admin/Calls/
├── Index.php          # Listado con filtros
├── Create.php         # Crear convocatoria
├── Edit.php           # Editar convocatoria
└── Show.php           # Vista detalle con fases y resoluciones

resources/views/livewire/admin/calls/
├── index.blade.php    # Vista del listado
├── create.blade.php   # Formulario de creación
├── edit.blade.php     # Formulario de edición
└── show.blade.php     # Vista de detalle

tests/Feature/Livewire/Admin/Calls/
├── IndexTest.php      # Tests del listado
├── CreateTest.php     # Tests de creación
├── EditTest.php       # Tests de edición
└── ShowTest.php       # Tests de detalle
```

---

## 🎨 Componentes UI a Reutilizar

- `x-ui.card` - Tarjetas contenedoras
- `x-ui.breadcrumbs` - Navegación breadcrumb
- `x-ui.search-input` - Input de búsqueda
- `x-ui.empty-state` - Estado vacío
- `flux:button` - Botones con variantes
- `flux:field` - Campos de formulario
- `flux:badge` - Badges de estado
- `flux:modal` - Modales de confirmación

---

## ✅ Checklist de Finalización

- [ ] SoftDeletes implementado
- [ ] FormRequests actualizados
- [ ] Componente Index funcional
- [ ] Componente Create funcional
- [ ] Componente Edit funcional
- [ ] Componente Show funcional
- [ ] Gestión de estados implementada
- [ ] Gestión de fases integrada
- [ ] Gestión de resoluciones integrada
- [ ] Rutas configuradas
- [ ] Navegación actualizada
- [ ] Traducciones añadidas
- [ ] Tests completos
- [ ] Optimizaciones implementadas
- [ ] Código formateado con Pint

---

**Ver plan detallado**: [paso-3.5.4-plan.md](./paso-3.5.4-plan.md)

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan completado - Listo para implementación

