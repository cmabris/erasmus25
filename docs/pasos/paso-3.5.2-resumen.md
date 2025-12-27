# Resumen Ejecutivo: Paso 3.5.2 - CRUD de Programas en Panel de Administración

## 🎯 Objetivo

Desarrollar un sistema completo de gestión (CRUD) de Programas Erasmus+ en el panel de administración con:
- Listado moderno con tabla interactiva
- Formularios de creación y edición
- Vista de detalle
- Funcionalidades avanzadas: activar/desactivar, ordenar, subir imágenes, gestionar traducciones
- **SoftDeletes**: Los programas nunca se eliminan permanentemente, solo se marcan como eliminados
- **ForceDelete**: Solo super-admin puede eliminar permanentemente, y solo si no hay relaciones
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Pasos Principales (14 Pasos)

### ✅ **Fase 1: Estructura Base y Listado** (MVP)

1. **Componente Index (Listado)** (Paso 1)
   - Crear componente Livewire `Admin\Programs\Index`
   - Tabla responsive con búsqueda, filtros y ordenación
   - Paginación y acciones (ver, editar, eliminar, activar/desactivar)
   - Autorización con `ProgramPolicy`

2. **Rutas y Navegación** (Paso 2)
   - Configurar rutas `/admin/programas/*`
   - Actualizar sidebar y traducciones

---

### ✅ **Fase 2: Creación y Edición**

3. **Componente Create (Crear)** (Paso 3)
   - Formulario con Flux UI
   - Validación en tiempo real
   - Generación automática de slug
   - Subida de imagen con preview

4. **Componente Edit (Editar)** (Paso 4)
   - Similar a Create pero con datos precargados
   - Opción de eliminar imagen existente

5. **Adaptar FormRequests** (Paso 5)
   - Añadir validación de imagen
   - Mensajes de error personalizados
   - Verificar autorización con Policy

---

### ✅ **Fase 3: Vista Detalle y Funcionalidades Avanzadas**

6. **Componente Show (Detalle)** (Paso 6)
   - Información completa del programa
   - Estadísticas (convocatorias, noticias relacionadas)
   - Botones de acción

7. **Gestión de Imágenes** (Paso 7)
   - Integrar Laravel Media Library
   - Subir/eliminar imágenes
   - Preview de imágenes

8. **Gestión de Traducciones** (Paso 8)
   - Formulario para gestionar traducciones
   - Campos traducibles: `name`, `description`
   - Selector de idioma

9. **Ordenamiento de Programas** (Paso 9)
   - Botones arriba/abajo o drag & drop
   - Actualizar campo `order`

9.5. **Implementar SoftDeletes** (Paso 9.5)
   - Verificar trait SoftDeletes en modelo
   - Actualizar Policy con delete, restore, forceDelete
   - Filtrar eliminados por defecto
   - Opción de restaurar
   - Validar relaciones antes de forceDelete

---

### ✅ **Fase 4: UX y Optimización**

10. **Mejoras de UX** (Paso 10)
    - Confirmaciones para acciones destructivas
    - Notificaciones de éxito/error
    - Estados de carga
    - Búsqueda avanzada

11. **Optimización** (Paso 11)
    - Eager loading para relaciones
    - Caché para listados
    - Optimizar consultas

---

### ✅ **Fase 5: Calidad y Documentación**

12. **Tests** (Paso 12)
    - Tests para Index, Create, Edit, Show
    - Verificar autorización, validación, funcionalidades

13. **Documentación** (Paso 13)
    - Documentar componentes
    - Actualizar documentación general
    - Crear resumen del desarrollo

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

app/Http/Requests/
  ├── StoreProgramRequest.php       [MODIFICAR]
  └── UpdateProgramRequest.php      [MODIFICAR]

app/Models/
  └── Program.php                  [MODIFICAR - añadir HasMedia]

routes/web.php                     [MODIFICAR]

tests/Feature/Livewire/Admin/Programs/
  ├── IndexTest.php                [NUEVO]
  ├── CreateTest.php               [NUEVO]
  ├── EditTest.php                 [NUEVO]
  └── ShowTest.php                 [NUEVO]
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

## 🔧 Tecnologías y Componentes

- **Livewire 3**: Componentes reactivos
- **Flux UI v2**: Componentes UI base
- **Tailwind CSS v4**: Estilos y responsive
- **Laravel Media Library**: Gestión de imágenes
- **Laravel Permission**: Verificación de permisos

---

## 📝 Notas Importantes

1. **Reutilización**: Aprovechar componentes existentes (`x-ui.card`, `x-ui.search-input`, `x-ui.empty-state`, etc.)
2. **Consistencia**: Mantener estilo similar al Dashboard
3. **Performance**: Optimizar consultas, usar eager loading
4. **Seguridad**: Verificar permisos en cada acción
5. **Escalabilidad**: Diseñar para futuras expansiones

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

**📄 Documento Completo**: Ver [paso-3.5.2-plan.md](./paso-3.5.2-plan.md) para detalles técnicos completos.

**Fecha**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Listo para implementación

