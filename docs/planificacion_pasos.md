# Planificación de Pasos de Desarrollo

Este documento establece la planificación de los siguientes pasos en el desarrollo de la aplicación "Erasmus+ Centro (Murcia)" después de completar las migraciones, modelos, factories y relaciones.

## Estado Actual del Proyecto

### ✅ Completado

- **Base de Datos**: Migraciones creadas y ejecutadas correctamente
- **Modelos**: 19 modelos Eloquent con relaciones bien definidas
- **Factories**: Factories para todos los modelos con estados apropiados
- **Tests**: 100% de cobertura en modelos y componentes Livewire básicos
- **Autenticación**: Laravel Fortify configurado (login, registro, recuperación, 2FA)
- **Configuración de Usuario**: Componentes Livewire para perfil, contraseña, apariencia y 2FA
- **Layouts Base**: Estructura de layouts con Flux UI configurada
- **Permisos**: Laravel Permission instalado y configurado
- **Multimedia**: Laravel Media Library instalado y configurado

### ⏳ Pendiente

- Controladores y lógica de negocio
- Form Requests para validación
- Policies para autorización
- Seeders para datos iniciales
- Rutas públicas y de administración
- Componentes Livewire para CRUD de entidades principales
- Vistas públicas y de administración

---

## Planificación de Pasos

### **Paso 3.1: Configuración Base y Datos Iniciales**

**Objetivo**: Establecer la base para el desarrollo de funcionalidades.

#### 3.1.1. Seeders y Datos Iniciales
- [ ] Crear `DatabaseSeeder` principal
- [ ] Crear seeder para `Programs` (programas Erasmus+ básicos)
- [ ] Crear seeder para `AcademicYears` (años académicos recientes)
- [ ] Crear seeder para `Languages` (ES, EN como mínimo)
- [ ] Crear seeder para `DocumentCategories` (categorías básicas)
- [ ] Crear seeder para `Settings` (configuración inicial del sistema)
- [ ] Crear seeder para roles y permisos básicos (admin, editor, viewer)
- [ ] Crear seeder para usuario administrador inicial

#### 3.1.2. Configuración de Roles y Permisos
- [ ] Definir estructura de roles:
  - `super-admin`: Acceso total al sistema
  - `admin`: Gestión completa de contenido y convocatorias
  - `editor`: Creación y edición de contenido
  - `viewer`: Solo lectura
- [ ] Definir permisos específicos por módulo:
  - `programs.*`, `programs.view`, `programs.create`, `programs.edit`, `programs.delete`
  - `calls.*`, `calls.view`, `calls.create`, `calls.edit`, `calls.delete`, `calls.publish`
  - `news.*`, `news.view`, `news.create`, `news.edit`, `news.delete`, `news.publish`
  - `documents.*`, `documents.view`, `documents.create`, `documents.edit`, `documents.delete`
  - `events.*`, `events.view`, `events.create`, `events.edit`, `events.delete`
  - `users.*`, `users.view`, `users.create`, `users.edit`, `users.delete`
- [ ] Crear seeder para asignar permisos a roles

#### 3.1.3. Middleware Personalizado
- [ ] Crear middleware para verificar permisos específicos
- [ ] Registrar middleware en `bootstrap/app.php`
- [ ] Crear tests para middleware de permisos

---

### **Paso 3.2: Form Requests y Validación**

**Objetivo**: Establecer la capa de validación para todas las entidades.

#### 3.2.1. Form Requests para Programas
- [ ] `StoreProgramRequest` - Validación para crear programas
- [ ] `UpdateProgramRequest` - Validación para actualizar programas

#### 3.2.2. Form Requests para Años Académicos
- [ ] `StoreAcademicYearRequest` - Validación para crear años académicos
- [ ] `UpdateAcademicYearRequest` - Validación para actualizar años académicos

#### 3.2.3. Form Requests para Convocatorias
- [ ] `StoreCallRequest` - Validación para crear convocatorias
- [ ] `UpdateCallRequest` - Validación para actualizar convocatorias
- [ ] `PublishCallRequest` - Validación para publicar convocatorias
- [ ] `StoreCallPhaseRequest` - Validación para crear fases
- [ ] `UpdateCallPhaseRequest` - Validación para actualizar fases
- [ ] `StoreResolutionRequest` - Validación para crear resoluciones
- [ ] `UpdateResolutionRequest` - Validación para actualizar resoluciones

#### 3.2.4. Form Requests para Noticias
- [ ] `StoreNewsPostRequest` - Validación para crear noticias
- [ ] `UpdateNewsPostRequest` - Validación para actualizar noticias
- [ ] `StoreNewsTagRequest` - Validación para crear etiquetas

#### 3.2.5. Form Requests para Documentos
- [ ] `StoreDocumentRequest` - Validación para crear documentos
- [ ] `UpdateDocumentRequest` - Validación para actualizar documentos
- [ ] `StoreDocumentCategoryRequest` - Validación para crear categorías

#### 3.2.6. Form Requests para Eventos
- [ ] `StoreErasmusEventRequest` - Validación para crear eventos
- [ ] `UpdateErasmusEventRequest` - Validación para actualizar eventos

#### 3.2.7. Form Requests para Usuarios
- [ ] `StoreUserRequest` - Validación para crear usuarios
- [ ] `UpdateUserRequest` - Validación para actualizar usuarios
- [ ] `AssignRoleRequest` - Validación para asignar roles

---

### **Paso 3.3: Policies y Autorización**

**Objetivo**: Implementar la lógica de autorización para todas las entidades.

#### 3.3.1. Policies Principales
- [ ] `ProgramPolicy` - Autorización para programas
- [ ] `AcademicYearPolicy` - Autorización para años académicos
- [ ] `CallPolicy` - Autorización para convocatorias
- [ ] `CallPhasePolicy` - Autorización para fases
- [ ] `ResolutionPolicy` - Autorización para resoluciones
- [ ] `NewsPostPolicy` - Autorización para noticias
- [ ] `NewsTagPolicy` - Autorización para etiquetas
- [ ] `DocumentPolicy` - Autorización para documentos
- [ ] `DocumentCategoryPolicy` - Autorización para categorías
- [ ] `ErasmusEventPolicy` - Autorización para eventos
- [ ] `UserPolicy` - Autorización para usuarios

#### 3.3.2. Métodos de Policy a Implementar
Para cada policy:
- [ ] `viewAny()` - Ver listado
- [ ] `view()` - Ver detalle
- [ ] `create()` - Crear nuevo
- [ ] `update()` - Actualizar existente
- [ ] `delete()` - Eliminar
- [ ] `restore()` - Restaurar (si aplica)
- [ ] `forceDelete()` - Eliminación permanente (si aplica)
- [ ] Métodos específicos según entidad (ej: `publish()` para convocatorias y noticias)

#### 3.3.3. Tests de Policies
- [ ] Crear tests para cada policy
- [ ] Verificar permisos por rol
- [ ] Verificar restricciones de acceso

---

### **Paso 3.4: Área Pública (Front-office)**

**Objetivo**: Implementar las vistas y funcionalidades públicas de la aplicación.

#### 3.4.1. Página Principal (Home)
- [ ] Crear componente Livewire `Home` o vista estática
- [ ] Mostrar programas activos destacados
- [ ] Mostrar convocatorias abiertas recientes
- [ ] Mostrar últimas noticias
- [ ] Mostrar próximos eventos del calendario
- [ ] Diseño responsive con Flux UI:
  - [ ] Móviles en vertical (< 640px)
  - [ ] Móviles en horizontal (640px - 768px)
  - [ ] Tabletas (768px - 1024px)
  - [ ] Portátiles (1024px - 1280px)
  - [ ] Pantallas grandes (> 1280px)
- [ ] Crear componentes UI reutilizables:
  - [ ] Card configurable (variantes, elevación, bordes)
  - [ ] Badge/etiqueta con colores y tamaños
  - [ ] Botón avanzado con variantes, tamaños e iconos
  - [ ] Contenedor de sección con título y descripción
  - [ ] Card de estadística para métricas
  - [ ] Estado vacío para cuando no hay datos
- [ ] Crear cards especializadas de contenido:
  - [ ] Program Card para programas Erasmus+
  - [ ] Call Card para convocatorias
  - [ ] News Card para noticias
  - [ ] Event Card para eventos
- [ ] Crear layout público con navegación y footer

#### 3.4.2. Listado y Detalle de Programas
- [ ] Crear componente Livewire `Programs\Index` para listado público
- [ ] Crear componente Livewire `Programs\Show` para detalle público
- [ ] Filtros por tipo de programa
- [ ] Búsqueda de programas
- [ ] Mostrar convocatorias relacionadas
- [ ] Mostrar documentos relacionados
- [ ] Mostrar noticias relacionadas

#### 3.4.3. Listado y Detalle de Convocatorias
- [ ] Crear componente Livewire `Calls\Index` para listado público
- [ ] Crear componente Livewire `Calls\Show` para detalle público
- [ ] Filtros por programa, año académico, tipo, modalidad
- [ ] Mostrar solo convocatorias con estado `abierta` o `cerrada`
- [ ] Mostrar fases actuales
- [ ] Mostrar resoluciones publicadas
- [ ] Descarga de documentos asociados (PDFs de resoluciones)

#### 3.4.4. Listado y Detalle de Noticias
- [ ] Crear componente Livewire `News\Index` para listado público
- [ ] Crear componente Livewire `News\Show` para detalle público
- [ ] Filtros por programa, año académico, etiquetas
- [ ] Búsqueda de noticias
- [ ] Paginación
- [ ] Mostrar imágenes asociadas (Laravel Media Library)
- [ ] Mostrar autor y fecha de publicación

#### 3.4.5. Listado y Detalle de Documentos
- [ ] Crear componente Livewire `Documents\Index` para listado público
- [ ] Crear componente Livewire `Documents\Show` para detalle público
- [ ] Filtros por categoría, programa, año académico
- [ ] Búsqueda de documentos
- [ ] Descarga de archivos (Laravel Media Library)
- [ ] Mostrar información de consentimiento si aplica

#### 3.4.6. Calendario de Eventos
- [ ] Crear componente Livewire `Events\Calendar` para vista de calendario
- [ ] Crear componente Livewire `Events\Index` para listado
- [ ] Crear componente Livewire `Events\Show` para detalle
- [ ] Vista mensual/semanal/diaria
- [ ] Filtros por programa
- [ ] Integración con eventos de convocatorias

#### 3.4.7. Suscripción a Newsletter
- [ ] Crear componente Livewire `Newsletter\Subscribe`
- [ ] Formulario de suscripción público
- [ ] Validación de email
- [ ] Selección de programas de interés
- [ ] Confirmación de suscripción

#### 3.4.8. Internacionalización (i18n) ✅ COMPLETADO
- [x] Implementar cambio de idioma en frontend
- [x] Crear componente Livewire `Language\Switcher`
- [x] Traducir textos estáticos
- [x] Implementar traducciones dinámicas desde tabla `translations`

**Documentación:**
- [Plan detallado](pasos/paso-3.4.8-plan.md)
- [Resumen completo del desarrollo](pasos/paso14.md)
- [Documentación técnica del sistema i18n](../i18n-system.md)

---

### **Paso 3.5: Panel de Administración (Back-office)**

**Objetivo**: Implementar el panel de administración para gestión de contenido.

#### 3.5.1. Dashboard de Administración
- [ ] Crear componente Livewire `Admin\Dashboard`
- [ ] Estadísticas generales:
  - Total de programas activos
  - Convocatorias abiertas/cerradas
  - Noticias publicadas este mes
  - Documentos disponibles
  - Eventos próximos
- [ ] Gráficos de actividad (opcional)
- [ ] Accesos rápidos a secciones principales

#### 3.5.2. Gestión de Programas (CRUD)
- [ ] Crear componente Livewire `Admin\Programs\Index` (listado con tabla)
- [ ] Crear componente Livewire `Admin\Programs\Create` (formulario creación)
- [ ] Crear componente Livewire `Admin\Programs\Edit` (formulario edición)
- [ ] Crear componente Livewire `Admin\Programs\Show` (vista detalle)
- [ ] Funcionalidades:
  - Crear, editar, eliminar programas (SoftDeletes)
  - Restaurar programas eliminados
  - ForceDelete solo para super-admin (validar relaciones)
  - Activar/desactivar programas
  - Ordenar programas
  - Subir imágenes (Laravel Media Library)
  - Gestión de traducciones
- [ ] **SoftDeletes**: Implementar SoftDeletes en modelo Program
  - Los programas nunca se eliminan permanentemente por defecto
  - Solo super-admin puede hacer forceDelete
  - Validar que no haya relaciones antes de forceDelete

#### 3.5.3. Gestión de Años Académicos (CRUD) ✅ COMPLETADO
- [x] Crear componente Livewire `Admin\AcademicYears\Index`
- [x] Crear componente Livewire `Admin\AcademicYears\Create`
- [x] Crear componente Livewire `Admin\AcademicYears\Edit`
- [x] Crear componente Livewire `Admin\AcademicYears\Show`
- [x] Funcionalidades:
  - Crear, editar, eliminar años académicos (SoftDeletes)
  - Restaurar años académicos eliminados
  - ForceDelete solo para administradores (validar relaciones)
  - Marcar año actual (solo uno puede ser actual)
  - Validar unicidad de años
- [x] **SoftDeletes**: Implementar SoftDeletes en modelo AcademicYear
- [x] **Optimizaciones**: Caché del año actual (24h TTL), índices de BD, búsqueda optimizada
- [x] **Tests**: 61 tests completos (149 assertions)

#### 3.5.4. Gestión de Convocatorias (CRUD Completo) ✅ COMPLETADO
- [x] Crear componente Livewire `Admin\Calls\Index` (listado con filtros avanzados)
- [x] Crear componente Livewire `Admin\Calls\Create` (formulario creación completo)
- [x] Crear componente Livewire `Admin\Calls\Edit` (formulario edición)
- [x] Crear componente Livewire `Admin\Calls\Show` (vista detalle con fases y resoluciones)
- [x] Funcionalidades básicas:
  - Crear, editar, eliminar convocatorias (SoftDeletes)
  - Restaurar convocatorias eliminadas
  - ForceDelete solo para super-admin (validar relaciones)
  - Cambiar estado (borrador → abierta → cerrada → archivada)
  - Publicar convocatorias (establecer `published_at`)
  - Visualización de fases y resoluciones
  - Marcar fase como actual
  - Publicar resoluciones
  - Gestión de destinos (JSON)
  - Configuración de baremo (JSON)
- [x] **SoftDeletes**: Implementar SoftDeletes en modelo Call
- [x] **FormRequests**: Actualizados con autorización completa
- [x] **Vistas**: Componentes completos con Flux UI
- [x] **Rutas**: Configuradas y funcionando
- [x] **Navegación**: Integrada en sidebar de administración

#### 3.5.4.1. Gestión Completa de Fases de Convocatorias (CRUD) ✅ COMPLETADO
- [x] Crear componente Livewire `Admin\Calls\Phases\Index` (listado de fases de una convocatoria)
- [x] Crear componente Livewire `Admin\Calls\Phases\Create` (formulario creación de fase)
- [x] Crear componente Livewire `Admin\Calls\Phases\Edit` (formulario edición de fase)
- [x] Crear componente Livewire `Admin\Calls\Phases\Show` (vista detalle de fase)
- [x] Funcionalidades básicas:
  - Crear, editar, eliminar fases (SoftDeletes)
  - Restaurar fases eliminadas
  - ForceDelete solo para super-admin (validar relaciones)
  - Reordenar fases (mover arriba/abajo)
  - Marcar fase como actual (solo una por convocatoria)
  - Validación de solapamiento de fechas
- [x] **SoftDeletes**: Implementar SoftDeletes en modelo CallPhase con cascade delete manual
- [x] **FormRequests**: Actualizados con autorización completa y validación de fase actual
- [x] **Rutas**: Configuradas como rutas anidadas bajo `/admin/convocatorias/{call}/fases`
- [x] **Optimizaciones**: Índices de BD, eager loading, withCount
- [x] **Tests**: 76 tests completos (203 assertions)
- [ ] Funcionalidades:
  - Crear nuevas fases para una convocatoria
  - Editar fases existentes
  - Eliminar fases (SoftDeletes si aplica)
  - Reordenar fases (campo `order`)
  - Marcar/desmarcar fase como actual (solo una por convocatoria)
  - Validar fechas de inicio/fin entre fases
  - Gestión de tipos de fase (publicacion, solicitudes, provisional, alegaciones, definitivo, renuncias, lista_espera)
  - Integración con componente Show de convocatoria (modales o navegación)
- [ ] **Rutas**: Rutas anidadas bajo `/admin/convocatorias/{call}/fases`
- [ ] **Autorización**: Usar `CallPhasePolicy` existente
- [ ] **Validación**: Usar `StoreCallPhaseRequest` y `UpdateCallPhaseRequest` existentes

#### 3.5.4.2. Gestión Completa de Resoluciones (CRUD)
- [ ] Crear componente Livewire `Admin\Calls\Resolutions\Index` (listado de resoluciones de una convocatoria)
- [ ] Crear componente Livewire `Admin\Calls\Resolutions\Create` (formulario creación de resolución)
- [ ] Crear componente Livewire `Admin\Calls\Resolutions\Edit` (formulario edición de resolución)
- [ ] Crear componente Livewire `Admin\Calls\Resolutions\Show` (vista detalle de resolución)
- [ ] Funcionalidades:
  - Crear nuevas resoluciones para una convocatoria/fase
  - Editar resoluciones existentes
  - Eliminar resoluciones (SoftDeletes si aplica)
  - Publicar/despublicar resoluciones (establecer `published_at`)
  - Subir PDFs de resoluciones (Laravel Media Library)
  - Gestión de tipos de resolución (provisional, definitivo, alegaciones)
  - Asociar resolución a fase específica
  - Validar fecha oficial vs fecha de publicación
  - Integración con componente Show de convocatoria (modales o navegación)
- [ ] **Rutas**: Rutas anidadas bajo `/admin/convocatorias/{call}/resoluciones`
- [ ] **Autorización**: Usar `ResolutionPolicy` existente
- [ ] **Validación**: Usar `StoreResolutionRequest` y `UpdateResolutionRequest` existentes
- [ ] **Media Library**: Configurar colección 'resolutions' para PDFs

#### 3.5.5. Gestión de Noticias (CRUD) ✅ **COMPLETADO**
- [x] Crear componente Livewire `Admin\News\Index` (listado con filtros)
- [x] Crear componente Livewire `Admin\News\Create` (editor de contenido)
- [x] Crear componente Livewire `Admin\News\Edit` (editor de contenido)
- [x] Crear componente Livewire `Admin\News\Show` (vista previa)
- [x] Funcionalidades:
  - [x] Crear, editar, eliminar noticias (SoftDeletes)
  - [x] Restaurar noticias eliminadas
  - [x] ForceDelete solo para super-admin (validar relaciones)
  - [x] Publicar/despublicar noticias
  - [x] Gestión de etiquetas (many-to-many)
  - [x] Subir imágenes destacadas (Laravel Media Library)
  - [x] Editor de contenido enriquecido (Tiptap)
  - [x] Gestión avanzada de imágenes (soft delete, restauración, eliminación permanente)
  - [x] Selección de imágenes desde modal
- [x] **SoftDeletes**: Implementar SoftDeletes en modelo NewsPost
- [x] **Tests**: 1231 tests pasando ✅

**Documentación:**
- [Plan detallado](pasos/paso-3.5.5-plan.md) - Plan paso a paso completo (18 pasos + 5 fases de imágenes) ✅
- [Resumen ejecutivo](pasos/paso-3.5.5-resumen.md) - Resumen de objetivos y estructura ✅

#### 3.5.6. Gestión de Etiquetas de Noticias
- [x] Crear componente Livewire `Admin\NewsTags\Index`
- [x] Crear componente Livewire `Admin\NewsTags\Create`
- [x] Crear componente Livewire `Admin\NewsTags\Edit`
- [x] Crear componente Livewire `Admin\NewsTags\Show`
- [x] Funcionalidades:
  - Crear, editar, eliminar etiquetas (SoftDeletes)
  - Restaurar etiquetas eliminadas
  - ForceDelete solo para super-admin (validar relaciones)
  - Ver noticias asociadas
- [x] **SoftDeletes**: Implementar SoftDeletes en modelo NewsTag
- [x] **Tests**: 59 tests pasando (129 assertions) ✅

**Documentación:**
- [Plan detallado](pasos/paso-3.5.6-plan.md) - Plan paso a paso completo (12 pasos, 7 fases) ✅
- [Resumen ejecutivo](pasos/paso-3.5.6-resumen.md) - Resumen de objetivos y estructura ✅
- [ ] **SoftDeletes**: Implementar SoftDeletes en modelo NewsTag

#### 3.5.7. Gestión de Documentos (CRUD)
- [ ] Crear componente Livewire `Admin\Documents\Index` (listado con filtros)
- [ ] Crear componente Livewire `Admin\Documents\Create` (formulario con upload)
- [ ] Crear componente Livewire `Admin\Documents\Edit` (formulario edición)
- [ ] Crear componente Livewire `Admin\Documents\Show` (vista detalle)
- [ ] Funcionalidades:
  - Crear, editar, eliminar documentos (SoftDeletes)
  - Restaurar documentos eliminados
  - ForceDelete solo para super-admin (validar relaciones)
  - Subir archivos (Laravel Media Library)
  - Asignar categorías
  - Gestión de consentimientos de medios
  - Gestión de traducciones
- [ ] **SoftDeletes**: Implementar SoftDeletes en modelo Document

#### 3.5.8. Gestión de Categorías de Documentos
- [ ] Crear componente Livewire `Admin\DocumentCategories\Index`
- [ ] Crear componente Livewire `Admin\DocumentCategories\Create`
- [ ] Crear componente Livewire `Admin\DocumentCategories\Edit`
- [ ] Funcionalidades:
  - Crear, editar, eliminar categorías (SoftDeletes)
  - Restaurar categorías eliminadas
  - ForceDelete solo para super-admin (validar relaciones)
  - Ver documentos asociados
- [ ] **SoftDeletes**: Implementar SoftDeletes en modelo DocumentCategory

#### 3.5.9. Gestión de Eventos (CRUD)
- [ ] Crear componente Livewire `Admin\Events\Index` (vista calendario y listado)
- [ ] Crear componente Livewire `Admin\Events\Create` (formulario creación)
- [ ] Crear componente Livewire `Admin\Events\Edit` (formulario edición)
- [ ] Crear componente Livewire `Admin\Events\Show` (vista detalle)
- [ ] Funcionalidades:
  - Crear, editar, eliminar eventos (SoftDeletes)
  - Restaurar eventos eliminados
  - ForceDelete solo para super-admin (validar relaciones)
  - Vista de calendario interactiva
  - Asociar eventos a convocatorias
  - Subir imágenes (Laravel Media Library)
- [ ] **SoftDeletes**: Implementar SoftDeletes en modelo ErasmusEvent

#### 3.5.10. Gestión de Usuarios y Roles
- [ ] Crear componente Livewire `Admin\Users\Index` (listado con tabla)
- [ ] Crear componente Livewire `Admin\Users\Create` (formulario creación)
- [ ] Crear componente Livewire `Admin\Users\Edit` (formulario edición con roles)
- [ ] Crear componente Livewire `Admin\Users\Show` (vista detalle)
- [ ] Funcionalidades:
  - Crear, editar, eliminar usuarios (SoftDeletes)
  - Restaurar usuarios eliminados
  - ForceDelete solo para super-admin (validar relaciones)
  - Asignar/revocar roles
  - Asignar permisos directos
  - Ver actividad del usuario (audit logs)
- [ ] **SoftDeletes**: Implementar SoftDeletes en modelo User (si aplica)

#### 3.5.11. Gestión de Roles y Permisos
- [ ] Crear componente Livewire `Admin\Roles\Index` (listado de roles)
- [ ] Crear componente Livewire `Admin\Roles\Create` (crear rol con permisos)
- [ ] Crear componente Livewire `Admin\Roles\Edit` (editar rol y permisos)
- [ ] Funcionalidades:
  - Crear, editar, eliminar roles
  - Asignar permisos a roles
  - Ver usuarios con cada rol

#### 3.5.12. Configuración del Sistema
- [ ] Crear componente Livewire `Admin\Settings\Index` (listado de configuraciones)
- [ ] Crear componente Livewire `Admin\Settings\Edit` (editar configuración)
- [ ] Funcionalidades:
  - Editar configuraciones del sistema
  - Validar tipos de datos (integer, boolean, json, string)
  - Gestión de traducciones de configuraciones

#### 3.5.13. Gestión de Traducciones
- [ ] Crear componente Livewire `Admin\Translations\Index` (listado de traducciones)
- [ ] Crear componente Livewire `Admin\Translations\Create` (crear traducción)
- [ ] Crear componente Livewire `Admin\Translations\Edit` (editar traducción)
- [ ] Funcionalidades:
  - Traducir contenido de modelos polimórficos
  - Filtrar por modelo y idioma
  - Búsqueda de traducciones

#### 3.5.14. Auditoría y Logs
- [ ] Instalar y configurar **Spatie Laravel Activitylog v4**
- [ ] Configurar logging automático en modelos principales
- [ ] Crear componente Livewire `Admin\AuditLogs\Index` (listado de logs)
- [ ] Crear componente Livewire `Admin\AuditLogs\Show` (detalle de log)
- [ ] Funcionalidades:
  - Ver historial de cambios
  - Filtrar por modelo, usuario, acción, fecha
  - Ver cambios antes/después (JSON)
  - Logging automático de eventos de modelos
  - Logging manual para acciones especiales

**Documentación:**
- [Plan detallado](pasos/paso-3.5.14-plan.md) - Plan paso a paso completo (15 pasos, 8 fases) ✅ Adaptado para Spatie Activitylog
- [Resumen ejecutivo](pasos/paso-3.5.14-resumen.md) - Resumen de objetivos y estructura ✅ Adaptado para Spatie Activitylog

#### 3.5.15. Gestión de Suscripciones Newsletter
- [ ] Crear componente Livewire `Admin\Newsletter\Index` (listado de suscriptores)
- [ ] Crear componente Livewire `Admin\Newsletter\Show` (detalle de suscriptor)
- [ ] Funcionalidades:
  - Ver listado de suscriptores
  - Filtrar por programas de interés
  - Exportar lista de emails
  - Eliminar suscripciones

**Documentación:**
- [Plan detallado](pasos/paso-3.5.15-plan.md) - Plan paso a paso completo (10 pasos, 5 fases) ✅
- [Resumen ejecutivo](pasos/paso-3.5.15-resumen.md) - Resumen de objetivos y estructura ✅

---

### **Paso 3.6: Rutas y Navegación**

**Objetivo**: Establecer la estructura de rutas y navegación de la aplicación.

#### 3.6.1. Rutas Públicas ✅ COMPLETADO
- [x] Definir rutas públicas en `routes/web.php`:
  - `/` - Página principal
  - `/programas` - Listado de programas
  - `/programas/{program}` - Detalle de programa
  - `/convocatorias` - Listado de convocatorias
  - `/convocatorias/{call}` - Detalle de convocatoria
  - `/noticias` - Listado de noticias
  - `/noticias/{newsPost}` - Detalle de noticia
  - `/documentos` - Listado de documentos
  - `/documentos/{document}` - Detalle de documento
  - `/calendario` - Calendario de eventos
  - `/eventos/{event}` - Detalle de evento
  - `/newsletter/suscribir` - Suscripción a newsletter
- [x] **Organización**: Rutas agrupadas y bien comentadas
- [x] **Route Model Binding**: Verificado y documentado (slug para Program, Call, NewsPost, Document; ID para ErasmusEvent)
- [x] **Tests**: 39 tests pasando (52 assertions) - `tests/Feature/Routes/PublicRoutesTest.php`
- [x] **Documentación**: `docs/public-routes.md` creada con documentación completa
- [x] **Casos Edge**: Tests para slugs especiales, largos, con números, etc.

**Documentación:**
- [Plan detallado](pasos/paso-3.6.1-plan.md) - Plan paso a paso completo ✅
- [Documentación de rutas públicas](../public-routes.md) - Documentación completa de todas las rutas ✅

#### 3.6.2. Rutas de Administración ✅ COMPLETADO
- [x] Crear archivo `routes/admin.php` (opcional) o agrupar en `web.php` - Agrupadas en `web.php`
- [x] Definir prefijo `/admin` para todas las rutas de administración
- [x] Middleware `auth` y verificación de permisos - Implementado en componentes Livewire
- [x] Rutas de administración:
  - `/admin` - Dashboard ✅
  - `/admin/programas` - CRUD programas ✅
  - `/admin/anios-academicos` - CRUD años académicos ✅
  - `/admin/convocatorias` - CRUD convocatorias ✅
  - `/admin/convocatorias/{call}/fases` - CRUD fases (anidadas) ✅
  - `/admin/convocatorias/{call}/resoluciones` - CRUD resoluciones (anidadas) ✅
  - `/admin/noticias` - CRUD noticias ✅
  - `/admin/etiquetas` - CRUD etiquetas ✅
  - `/admin/documentos` - CRUD documentos ✅
  - `/admin/categorias` - CRUD categorías ✅
  - `/admin/eventos` - CRUD eventos ✅
  - `/admin/usuarios` - CRUD usuarios ✅
  - `/admin/roles` - CRUD roles ✅
  - `/admin/configuracion` - Configuración del sistema ✅
  - `/admin/traducciones` - Gestión de traducciones ✅
  - `/admin/auditoria` - Logs de auditoría ✅
  - `/admin/newsletter` - Suscripciones newsletter ✅
- [x] **Organización**: Rutas agrupadas y bien comentadas
- [x] **Route Model Binding**: Verificado y documentado (ID para todas las rutas de administración)
- [x] **Tests**: 90 tests pasando (107 assertions) - `tests/Feature/Routes/AdminRoutesTest.php`
- [x] **Documentación**: `docs/admin-routes.md` y `docs/admin-routes-authorization.md` creadas

**Documentación:**
- [Plan detallado](pasos/paso-3.6.2-plan.md) - Plan paso a paso completo ✅
- [Documentación de rutas](../admin-routes.md) - Documentación completa de todas las rutas ✅
- [Documentación de autorización](../admin-routes-authorization.md) - Decisión de diseño y patrones ✅

#### 3.6.3. Navegación Principal ✅ COMPLETADO
- [x] Crear componente de navegación pública (`components/nav/public-nav.blade.php`)
- [x] Crear componente de navegación de administración (`components/nav/admin-nav.blade.php`)
- [x] Menú responsive con Flux UI
- [x] Indicador de idioma actual
- [x] Enlaces según permisos del usuario
- [x] **Mejoras**: Enlace al panel de administración según permisos en navegación pública
- [x] **Organización**: Navegación de administración extraída a componente separado
- [x] **Optimización**: Grupos de navegación reorganizados sin duplicación
- [x] **Tests**: 41 tests pasando (105 assertions) - `tests/Feature/Components/PublicLayoutTest.php` y `AdminNavTest.php`
- [x] **Documentación**: `docs/navigation.md` creada con documentación completa

**Documentación:**
- [Plan detallado](pasos/paso-3.6.3-plan.md) - Plan paso a paso completo ✅
- [Documentación de navegación](../navigation.md) - Documentación completa de navegación ✅

#### 3.6.4. Breadcrumbs ✅ COMPLETADO
- [x] Implementar breadcrumbs en vistas públicas
- [x] Implementar breadcrumbs en panel de administración
- [x] Usar componente Flux UI si está disponible
- [x] **Breadcrumb añadido a newsletter/subscribe**
- [x] **Traducciones añadidas**: `common.nav.phases` y `common.nav.resolutions`
- [x] **Iconos corregidos**: Convocatorias usa `megaphone`, Fases usa `calendar`
- [x] **Traducciones actualizadas**: Todas las vistas usan `common.nav.*`
- [x] **Tests**: 27 tests pasando (48 assertions) - `tests/Feature/Components/BreadcrumbsTest.php`

**Documentación:**
- [Plan detallado](pasos/paso-3.6.4-plan.md) - Plan paso a paso completo ✅
- [Auditoría completa](pasos/paso-3.6.4-auditoria.md) - Estado de breadcrumbs en todas las vistas ✅
- [Verificación y correcciones](pasos/paso-3.6.4-verificacion.md) - Correcciones aplicadas ✅
- [Documentación de breadcrumbs](../breadcrumbs.md) - Guía completa de uso ✅

---

### **Paso 3.7: Funcionalidades Avanzadas**

**Objetivo**: Implementar funcionalidades adicionales para mejorar la experiencia de usuario.

#### 3.7.1. Búsqueda Global
- [ ] Crear componente Livewire `Search\GlobalSearch`
- [ ] Búsqueda en programas, convocatorias, noticias, documentos
- [ ] Resultados agrupados por tipo
- [ ] Filtros avanzados
- [ ] Historial de búsquedas (opcional)

#### 3.7.2. Notificaciones del Sistema
- [ ] Implementar notificaciones en tiempo real (opcional con Laravel Echo)
- [ ] Notificaciones para:
  - Nueva convocatoria publicada
  - Nueva resolución publicada
  - Nueva noticia publicada
  - Nuevo documento disponible
- [ ] Componente Livewire para mostrar notificaciones
- [ ] Marcar como leídas

#### 3.7.3. Exportación de Datos
- [ ] Exportar convocatorias a PDF/Excel
- [ ] Exportar listados de resoluciones
- [ ] Exportar suscriptores newsletter a CSV
- [ ] Usar Laravel Excel o similar

#### 3.7.4. Importación de Datos
- [ ] Importar convocatorias desde Excel/CSV
- [ ] Importar usuarios desde Excel/CSV
- [ ] Validación de datos importados

#### 3.7.5. API REST (Opcional)
- [ ] Crear API para consulta pública de datos
- [ ] Implementar autenticación con Sanctum
- [ ] Crear API Resources para serialización
- [ ] Documentación con Laravel API Documentation

---

### **Paso 3.8: Testing y Cobertura**

**Objetivo**: Asegurar cobertura completa de tests para todas las funcionalidades.

#### 3.8.1. Tests de Form Requests
- [ ] Tests para cada Form Request
- [ ] Validar reglas de validación
- [ ] Validar mensajes de error personalizados

#### 3.8.2. Tests de Policies
- [ ] Tests para cada Policy
- [ ] Verificar autorización por rol
- [ ] Verificar restricciones de acceso

#### 3.8.3. Tests de Componentes Livewire Públicos
- [ ] Tests para componentes de área pública
- [ ] Verificar visualización correcta
- [ ] Verificar filtros y búsquedas
- [ ] Verificar paginación

#### 3.8.4. Tests de Componentes Livewire de Administración
- [ ] Tests para componentes CRUD
- [ ] Verificar creación, edición, eliminación
- [ ] Verificar autorización
- [ ] Verificar validación
- [ ] Verificar subida de archivos

#### 3.8.5. Tests de Rutas
- [ ] Tests para rutas públicas
- [ ] Tests para rutas de administración
- [ ] Verificar middleware y permisos

#### 3.8.6. Tests de Seeders
- [ ] Tests para verificar datos iniciales
- [ ] Verificar integridad de relaciones

#### 3.8.7. Tests de Integración
- [ ] Tests end-to-end de flujos completos
- [ ] Tests de flujo de convocatoria completa
- [ ] Tests de publicación de contenido

---

### **Paso 3.9: Optimización y Mejoras**

**Objetivo**: Optimizar rendimiento y mejorar la experiencia de usuario.

#### 3.9.1. Optimización de Consultas
- [ ] Implementar eager loading donde sea necesario
- [ ] Revisar y optimizar consultas N+1
- [ ] Implementar caché para consultas frecuentes
- [ ] Usar índices de base de datos apropiados

#### 3.9.2. Caché
- [ ] Implementar caché para listados públicos
- [ ] Caché para configuraciones del sistema
- [ ] Invalidación de caché al actualizar contenido

#### 3.9.3. Optimización de Imágenes
- [ ] Implementar conversión de imágenes (Laravel Media Library)
- [ ] Generar thumbnails automáticamente
- [ ] Optimizar tamaño de archivos

#### 3.9.4. Paginación y Lazy Loading
- [ ] Implementar paginación en todos los listados
- [ ] Lazy loading para imágenes
- [ ] Infinite scroll donde sea apropiado

#### 3.9.5. SEO
- [ ] Meta tags dinámicos
- [ ] Sitemap.xml
- [ ] Robots.txt
- [ ] URLs amigables (ya implementado con slugs)

---

### **Paso 3.10: Documentación Final**

**Objetivo**: Completar la documentación del proyecto.

#### 3.10.1. Documentación de Funcionalidades
- [ ] Documentar cada módulo implementado
- [ ] Documentar flujos de trabajo principales
- [ ] Documentar políticas de autorización

#### 3.10.2. Guía de Usuario
- [ ] Crear guía para administradores
- [ ] Crear guía para editores
- [ ] Capturas de pantalla de funcionalidades principales

#### 3.10.3. Documentación Técnica
- [ ] Actualizar README principal
- [ ] Documentar arquitectura de la aplicación
- [ ] Documentar decisiones técnicas importantes

---

## Priorización Recomendada

### Fase 1: Fundamentos (Pasos 3.1 - 3.3)
**Duración estimada**: 1-2 semanas
- Seeders y datos iniciales
- Form Requests
- Policies

### Fase 2: Área Pública (Paso 3.4)
**Duración estimada**: 2-3 semanas
- Implementar todas las vistas públicas
- Componentes Livewire públicos

### Fase 3: Panel de Administración Básico (Paso 3.5 - Secciones principales)
**Duración estimada**: 3-4 semanas
- Dashboard
- CRUD de Programas
- CRUD de Convocatorias (3.5.4) ✅
- CRUD de Fases de Convocatorias (3.5.4.1) - Pendiente
- CRUD de Resoluciones (3.5.4.2) - Pendiente
- CRUD de Noticias
- CRUD de Documentos

### Fase 4: Panel de Administración Completo (Paso 3.5 - Resto)
**Duración estimada**: 2-3 semanas
- Gestión de usuarios y roles
- Configuración
- Traducciones
- Auditoría

### Fase 5: Rutas y Navegación (Paso 3.6)
**Duración estimada**: 1 semana
- Estructura de rutas
- Navegación

### Fase 6: Funcionalidades Avanzadas (Paso 3.7)
**Duración estimada**: 2-3 semanas
- Búsqueda global
- Notificaciones
- Exportación/Importación

### Fase 7: Testing y Optimización (Pasos 3.8 - 3.9)
**Duración estimada**: 2-3 semanas
- Tests completos
- Optimización de rendimiento

### Fase 8: Documentación (Paso 3.10)
**Duración estimada**: 1 semana
- Documentación final

---

## Notas Importantes

1. **Enfoque Iterativo**: Se recomienda desarrollar de forma iterativa, completando cada módulo antes de pasar al siguiente.

2. **Tests Continuos**: Escribir tests mientras se desarrolla, no al final.

3. **Reutilización de Componentes**: Crear componentes Flux UI reutilizables cuando sea posible.

4. **Consistencia**: Mantener consistencia en el diseño y estructura de código en todos los módulos.

5. **Seguridad**: Verificar siempre autorización y validación en cada endpoint y componente.

6. **Performance**: Considerar rendimiento desde el inicio, especialmente en listados con muchos registros.

7. **Accesibilidad**: Asegurar que todos los componentes sean accesibles (WCAG).

8. **Responsive**: Todos los componentes deben ser responsive y funcionar en móviles.

9. **SoftDeletes**: **IMPORTANTE** - Todos los modelos con CRUD deben implementar SoftDeletes:
   - Los registros nunca se eliminan permanentemente por defecto
   - Solo se marcan como eliminados (`deleted_at`)
   - Solo super-admin puede realizar `forceDelete()`
   - Antes de `forceDelete()`, validar que no existan relaciones con otros modelos
   - Implementar funcionalidad de restauración en todos los CRUDs
   - Filtrar registros eliminados por defecto en listados
   - Opción de ver registros eliminados (solo para administradores)

10. **Gestión de Fases y Resoluciones (3.5.4.1 y 3.5.4.2)**: **RECOMENDACIÓN DE IMPLEMENTACIÓN**
    
    **Opción Recomendada: Rutas Anidadas con Componentes Separados**
    
    Laravel y Livewire manejan mejor las relaciones padre-hijo cuando se implementan como rutas anidadas con componentes separados. Esta aproximación ofrece:
    
    - **Separación de responsabilidades**: Cada componente tiene su propia lógica y vista
    - **Mejor rendimiento**: Solo se carga el componente necesario, no toda la página padre
    - **Navegación clara**: URLs semánticas (`/admin/convocatorias/{call}/fases/{phase}/editar`)
    - **Reutilización**: Los componentes pueden usarse desde diferentes contextos
    - **Testing más fácil**: Cada componente se testea independientemente
    - **Mantenibilidad**: Código más organizado y fácil de mantener
    
    **Estructura recomendada**:
    ```
    routes/web.php:
    Route::prefix('admin/convocatorias/{call}')->group(function () {
        Route::get('/fases', ...)->name('admin.calls.phases.index');
        Route::get('/fases/crear', ...)->name('admin.calls.phases.create');
        Route::get('/fases/{phase}', ...)->name('admin.calls.phases.show');
        Route::get('/fases/{phase}/editar', ...)->name('admin.calls.phases.edit');
        
        Route::get('/resoluciones', ...)->name('admin.calls.resolutions.index');
        Route::get('/resoluciones/crear', ...)->name('admin.calls.resolutions.create');
        Route::get('/resoluciones/{resolution}', ...)->name('admin.calls.resolutions.show');
        Route::get('/resoluciones/{resolution}/editar', ...)->name('admin.calls.resolutions.edit');
    });
    ```
    
    **Alternativa: Modales (Solo para acciones rápidas)**
    
    Los modales son útiles para acciones rápidas (marcar como actual, publicar), pero para CRUD completo se recomienda rutas separadas porque:
    - Los formularios complejos son difíciles de manejar en modales
    - La validación y manejo de errores es más compleja
    - No hay historial de navegación (botón atrás)
    - Difícil de testear completamente
    
    **Integración con Show de Convocatoria**:
    - En `Admin\Calls\Show`, añadir botones que naveguen a las rutas anidadas
    - Usar `wire:navigate` para transiciones suaves
    - Mantener breadcrumbs que muestren la jerarquía (Convocatorias > {Call} > Fases > {Phase})

---

## Próximos Pasos Inmediatos

Una vez que se apruebe esta planificación, el siguiente paso sería comenzar con el **Paso 3.1: Configuración Base y Datos Iniciales**, empezando por los seeders para tener datos de prueba disponibles durante el desarrollo.

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Pendiente de aprobación para comenzar implementación
