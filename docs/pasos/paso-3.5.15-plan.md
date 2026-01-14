# Plan de Desarrollo: Paso 3.5.15 - Gestión de Suscripciones Newsletter en Panel de Administración

Este documento establece el plan detallado para desarrollar el sistema completo de gestión de Suscripciones Newsletter en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión de Suscripciones Newsletter en el panel de administración con:
- Listado moderno con tabla interactiva y filtros avanzados
- Vista detallada de suscriptor individual
- Filtros por programas de interés, estado (activo/inactivo), verificación
- Exportación de lista de emails a CSV/Excel
- Eliminación de suscripciones (con confirmación)
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4

---

## 📋 Análisis del Estado Actual

### ✅ Ya Implementado
- **Modelo**: `NewsletterSubscription` con campos:
  - `email` (único)
  - `name` (opcional)
  - `programs` (JSON array de códigos de programas)
  - `is_active` (boolean)
  - `subscribed_at` (timestamp)
  - `unsubscribed_at` (nullable timestamp)
  - `verification_token` (nullable string)
  - `verified_at` (nullable timestamp)
- **Scopes**: `active()`, `verified()`, `unverified()`, `forProgram()`, `verifiedForProgram()`
- **Métodos helper**: `isVerified()`, `isActive()`, `verify()`, `unsubscribe()`, `hasProgram()`
- **Factory**: `NewsletterSubscriptionFactory` con estados `unsubscribed()` y `unverified()`
- **Componente público**: `Public\Newsletter\Subscribe` para suscripciones públicas

### ⏳ Pendiente de Implementar
- Policy para autorización (`NewsletterSubscriptionPolicy`)
- Componente Livewire `Admin\Newsletter\Index` (listado)
- Componente Livewire `Admin\Newsletter\Show` (detalle)
- Form Request para validación de eliminación (opcional)
- Clase Export para exportación de emails (`NewsletterSubscriptionsExport`)
- Rutas de administración
- Tests completos (Feature tests)
- Integración en navegación de administración

---

## 📋 Pasos de Desarrollo (10 Pasos)

### **Fase 1: Preparación Base**

#### **Paso 1: Crear Policy de Autorización**
- [ ] Crear `NewsletterSubscriptionPolicy`:
  - `viewAny()` - Ver listado (admin, editor)
  - `view()` - Ver detalle (admin, editor)
  - `delete()` - Eliminar suscripción (admin)
  - `export()` - Exportar emails (admin, editor)
- [ ] Registrar policy en `AppServiceProvider` o `AuthServiceProvider`
- [ ] Crear tests básicos de la policy

#### **Paso 2: Verificar/Actualizar Modelo**
- [ ] Verificar que el modelo tenga todos los scopes necesarios (ya implementados)
- [ ] Añadir método `getProgramsDisplayAttribute()` para mostrar nombres de programas
- [ ] Añadir relación `programs()` si es necesario (acceso a modelos Program desde códigos)
- [ ] Verificar índices en base de datos para optimización

---

### **Fase 2: Componente Index (Listado)**

#### **Paso 3: Componente Index - Estructura Base**
- [ ] Crear componente Livewire `Admin\Newsletter\Index`
- [ ] Implementar propiedades públicas:
  - `string $search = ''` - Búsqueda por email/nombre (con `#[Url(as: 'q')]`)
  - `?string $filterProgram = null` - Filtro por programa (con `#[Url(as: 'programa')]`)
  - `?string $filterStatus = null` - Filtro por estado: 'activo', 'inactivo' (con `#[Url(as: 'estado')]`)
  - `?string $filterVerification = null` - Filtro por verificación: 'verificado', 'no-verificado' (con `#[Url(as: 'verificacion')]`)
  - `string $sortField = 'subscribed_at'` - Campo de ordenación (con `#[Url(as: 'ordenar')]`)
  - `string $sortDirection = 'desc'` - Dirección de ordenación (con `#[Url(as: 'direccion')]`)
  - `int $perPage = 15` - Elementos por página (con `#[Url(as: 'por-pagina')]`)
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
  - `?int $subscriptionToDelete = null` - ID de suscripción a eliminar
- [ ] Implementar métodos base:
  - `mount()` - Inicialización con autorización `viewAny`
  - `subscriptions()` - Computed property con paginación, filtros y ordenación
  - `programs()` - Computed property para dropdown de programas
  - `render()` - Renderizado con paginación

#### **Paso 4: Componente Index - Funcionalidades**
- [ ] Implementar métodos de filtrado:
  - `sortBy($field)` - Ordenación
  - `resetFilters()` - Resetear filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedFilterProgram()` - Resetear página al cambiar programa
  - `updatedFilterStatus()` - Resetear página al cambiar estado
  - `updatedFilterVerification()` - Resetear página al cambiar verificación
- [ ] Implementar métodos de eliminación:
  - `confirmDelete($subscriptionId)` - Confirmar eliminación
  - `delete()` - Eliminar suscripción (hard delete, sin SoftDeletes)
- [ ] Implementar método de exportación:
  - `export()` - Exportar lista de emails a CSV/Excel usando Laravel Excel
- [ ] Implementar métodos helper:
  - `canDelete()` - Verificar si puede eliminar
  - `canExport()` - Verificar si puede exportar
  - `getStatusBadge($subscription)` - Obtener variante de badge para estado
  - `getVerificationBadge($subscription)` - Obtener variante de badge para verificación

#### **Paso 5: Vista Index - UI**
- [ ] Crear vista `livewire/admin/newsletter/index.blade.php`:
  - Header con título, descripción y botón exportar
  - Breadcrumbs
  - Filtros: búsqueda, programa, estado, verificación, reset
  - Tabla responsive con columnas:
    - Email (con enlace a Show)
    - Nombre (opcional)
    - Programas (badges con códigos/nombres)
    - Estado (badge: activo/inactivo)
    - Verificación (badge: verificado/no verificado)
    - Fecha suscripción
    - Fecha verificación (si aplica)
    - Acciones (ver, eliminar)
  - Modales de confirmación (eliminar)
  - Paginación
  - Estado vacío
  - Loading states
  - Estadísticas rápidas (total, activos, verificados)

---

### **Fase 3: Componente Show (Detalle)**

#### **Paso 6: Componente Show - Estructura**
- [ ] Crear componente Livewire `Admin\Newsletter\Show`
- [ ] Implementar propiedades públicas:
  - `NewsletterSubscription $subscription` - Suscripción a mostrar
  - `bool $showDeleteModal = false` - Modal de confirmación de eliminación
- [ ] Implementar métodos:
  - `mount(NewsletterSubscription $subscription)` - Inicialización con autorización
  - `delete()` - Eliminar suscripción
  - `canDelete()` - Verificar si puede eliminar
  - `render()` - Renderizado

#### **Paso 7: Vista Show - UI**
- [ ] Crear vista `livewire/admin/newsletter/show.blade.php`:
  - Header con título (email), breadcrumbs y acciones (eliminar)
  - Sección de información básica:
    - Email (con badge de verificación)
    - Nombre (si existe)
    - Estado (badge: activo/inactivo)
    - Fecha de suscripción
    - Fecha de verificación (si aplica)
    - Fecha de baja (si aplica)
  - Sección de programas de interés:
    - Lista de programas con badges
    - Mostrar nombres completos de programas si es posible
  - Sección de acciones:
    - Botón eliminar (con confirmación)
  - Modal de confirmación de eliminación
  - Botón volver al listado

---

### **Fase 4: Exportación y Funcionalidades Adicionales**

#### **Paso 8: Clase Export para Emails**
- [ ] Crear clase `NewsletterSubscriptionsExport`:
  - Implementar `FromCollection` - Para obtener datos
  - Implementar `WithHeadings` - Para encabezados (Email, Nombre, Programas, Estado, Verificado, Fecha Suscripción)
  - Implementar `WithMapping` - Para formatear filas
  - Implementar `WithTitle` - Para nombre de hoja
  - Implementar `WithStyles` - Para estilos (headers en negrita)
  - Aplicar los mismos filtros que el componente Index
  - Formatear datos:
    - Programas como lista separada por comas
    - Estado como texto legible
    - Verificación como Sí/No
    - Fechas en formato legible
- [ ] Verificar que Laravel Excel esté instalado (ya está instalado según documentación)

#### **Paso 9: Rutas y Navegación**
- [ ] Añadir rutas en `routes/web.php`:
  ```php
  Route::get('/newsletter', \App\Livewire\Admin\Newsletter\Index::class)->name('newsletter.index');
  Route::get('/newsletter/{newsletter_subscription}', \App\Livewire\Admin\Newsletter\Show::class)->name('newsletter.show');
  ```
- [ ] Integrar en navegación de administración (sidebar):
  - Añadir entrada "Suscripciones Newsletter" con icono apropiado
  - Verificar permisos antes de mostrar enlace

---

### **Fase 5: Testing y Optimización**

#### **Paso 10: Tests Completos**
- [ ] Crear `tests/Feature/Livewire/Admin/Newsletter/IndexTest.php`:
  - Test de autorización (viewAny)
  - Test de visualización de listado
  - Test de búsqueda
  - Test de filtros (programa, estado, verificación)
  - Test de ordenación
  - Test de paginación
  - Test de eliminación
  - Test de exportación
  - Test de permisos
- [ ] Crear `tests/Feature/Livewire/Admin/Newsletter/ShowTest.php`:
  - Test de autorización (view)
  - Test de visualización de detalle
  - Test de eliminación desde detalle
  - Test de permisos
- [ ] Crear `tests/Feature/Policies/NewsletterSubscriptionPolicyTest.php`:
  - Test de viewAny
  - Test de view
  - Test de delete
  - Test de export
  - Test por roles
- [ ] Crear `tests/Feature/Exports/NewsletterSubscriptionsExportTest.php`:
  - Test de exportación básica
  - Test de exportación con filtros
  - Test de formato de datos
- [ ] Ejecutar todos los tests y verificar que pasen

---

## 🎨 Características de Diseño

### Componentes Flux UI a Utilizar
- `flux:button` - Botones de acción
- `flux:badge` - Badges para estados y verificación
- `flux:input` - Campo de búsqueda
- `flux:select` - Selectores de filtros
- `flux:modal` - Modales de confirmación
- `flux:table` - Tabla de datos (si está disponible)
- `flux:card` - Tarjetas de información
- `flux:heading` - Títulos y encabezados
- `flux:text` - Texto descriptivo

### Responsive Design
- Tabla responsive con scroll horizontal en móviles
- Filtros apilados verticalmente en móviles
- Modales adaptativos
- Botones con iconos en móviles

### Estados Visuales
- Badge verde para suscripciones activas y verificadas
- Badge rojo para suscripciones inactivas
- Badge amarillo para suscripciones no verificadas
- Iconos para acciones (ver, eliminar, exportar)

---

## 📊 Estructura de Datos

### Columnas de la Tabla Index
1. **Email** - Con enlace a Show
2. **Nombre** - Opcional, mostrar "-" si no existe
3. **Programas** - Badges con códigos de programas
4. **Estado** - Badge (activo/inactivo)
5. **Verificación** - Badge (verificado/no verificado)
6. **Fecha Suscripción** - Formato legible
7. **Acciones** - Botones ver y eliminar

### Información en Show
- **Información Básica**: Email, nombre, estado, fechas
- **Programas de Interés**: Lista de programas con badges
- **Acciones**: Eliminar suscripción

---

## 🔒 Seguridad y Autorización

### Permisos Requeridos
- `viewAny` - Ver listado (admin, editor)
- `view` - Ver detalle (admin, editor)
- `delete` - Eliminar suscripción (admin)
- `export` - Exportar emails (admin, editor)

### Validaciones
- Verificar autorización en cada método
- Validar que la suscripción existe antes de eliminar
- Confirmar eliminación mediante modal

---

## 📝 Notas Importantes

1. **Sin SoftDeletes**: Las suscripciones se eliminan permanentemente (hard delete) ya que no tienen relaciones críticas y es más limpio para GDPR.

2. **Exportación**: Usar Laravel Excel (Maatwebsite\Excel) que ya está instalado en el proyecto.

3. **Programas**: Los programas se almacenan como códigos en JSON. Mostrar nombres completos requiere consultar la tabla `programs` o usar un helper.

4. **Optimización**: 
   - Usar eager loading para programas si se muestran nombres
   - Índices en `email`, `is_active`, `verified_at`
   - Paginación para listados grandes

5. **GDPR**: Considerar añadir funcionalidad de exportación de datos del usuario (opcional, futuro).

---

## ✅ Criterios de Finalización

- [ ] Todos los componentes creados y funcionando
- [ ] Todas las rutas configuradas
- [ ] Navegación integrada
- [ ] Tests completos pasando
- [ ] Exportación funcionando correctamente
- [ ] Diseño responsive verificado
- [ ] Autorización verificada
- [ ] Documentación actualizada

---

**Fecha de Creación**: Enero 2025  
**Estado**: 📋 Planificación completada - Pendiente de implementación
