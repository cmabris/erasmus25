# Funcionalidades por Módulo

Este documento proporciona un índice consolidado de todas las funcionalidades de la aplicación Erasmus+ Centro (Murcia), organizadas por módulo funcional.

---

## Resumen de Módulos

| Módulo | Área Pública | Área Admin | Permisos | Componentes |
|--------|--------------|------------|----------|-------------|
| [Programas](#1-módulo-de-programas) | ✅ | ✅ | `programs.*` | 8 |
| [Convocatorias](#2-módulo-de-convocatorias) | ✅ | ✅ | `calls.*` | 16 |
| [Noticias](#3-módulo-de-noticias) | ✅ | ✅ | `news.*` | 12 |
| [Documentos](#4-módulo-de-documentos) | ✅ | ✅ | `documents.*` | 12 |
| [Eventos](#5-módulo-de-eventos) | ✅ | ✅ | `events.*` | 8 |
| [Newsletter](#6-módulo-de-newsletter) | ✅ | ✅ | - | 5 |
| [Usuarios](#7-módulo-de-usuarios) | ❌ | ✅ | `users.*` | 5 |
| [Roles](#8-módulo-de-roles-y-permisos) | ❌ | ✅ | Solo super-admin | 4 |
| [Configuración](#9-módulo-de-configuración) | ❌ | ✅ | Admin+ | 2 |
| [Traducciones](#10-módulo-de-traducciones) | ❌ | ✅ | Admin+ | 4 |
| [Auditoría](#11-módulo-de-auditoría) | ❌ | ✅ | Admin+ | 2 |
| [Notificaciones](#12-módulo-de-notificaciones) | ❌ | ✅ | Auth | 3 |

---

## 1. Módulo de Programas

Gestión de los programas Erasmus+ (Educación Escolar, Formación Profesional, Educación Superior).

### Área Pública

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de programas | `/programas` | `Public/Programs/Index` |
| Detalle de programa | `/programas/{slug}` | `Public/Programs/Show` |
| Filtro por tipo | `/programas?type=...` | - |
| Búsqueda | `/programas?search=...` | - |

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado (CRUD) | `/admin/programas` | `Admin/Programs/Index` |
| Crear programa | `/admin/programas/crear` | `Admin/Programs/Create` |
| Editar programa | `/admin/programas/{id}/editar` | `Admin/Programs/Edit` |
| Ver detalle | `/admin/programas/{id}` | `Admin/Programs/Show` |

### Funcionalidades Especiales

- **Imágenes**: Gestión mediante Media Library con conversiones WebP
- **Traducciones**: Soporte multiidioma (ES/EN)
- **Ordenamiento**: Campo `order` para ordenar en listados
- **SoftDeletes**: Eliminación lógica con posibilidad de restauración

### Permisos

| Permiso | Descripción |
|---------|-------------|
| `programs.view` | Ver listados y detalles |
| `programs.create` | Crear nuevos programas |
| `programs.edit` | Editar programas existentes |
| `programs.delete` | Eliminar programas |

### Documentación Relacionada

- [Componentes de Programas](programs-components.md)
- [CRUD de Programas](admin-programs-crud.md)

---

## 2. Módulo de Convocatorias

Sistema completo de gestión de convocatorias Erasmus+ con fases y resoluciones.

### Área Pública

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de convocatorias | `/convocatorias` | `Public/Calls/Index` |
| Detalle de convocatoria | `/convocatorias/{slug}` | `Public/Calls/Show` |
| Filtro por programa | `/convocatorias?program=...` | - |
| Filtro por año | `/convocatorias?year=...` | - |
| Filtro por tipo | `/convocatorias?type=...` | - |
| Filtro por modalidad | `/convocatorias?modality=...` | - |

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado (CRUD) | `/admin/convocatorias` | `Admin/Calls/Index` |
| Crear convocatoria | `/admin/convocatorias/crear` | `Admin/Calls/Create` |
| Editar convocatoria | `/admin/convocatorias/{id}/editar` | `Admin/Calls/Edit` |
| Ver detalle | `/admin/convocatorias/{id}` | `Admin/Calls/Show` |
| Importar | `/admin/convocatorias` (modal) | `Admin/Calls/Import` |

#### Gestión de Fases

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de fases | `/admin/convocatorias/{call}/fases` | `Admin/Calls/Phases/Index` |
| Crear fase | `/admin/convocatorias/{call}/fases/crear` | `Admin/Calls/Phases/Create` |
| Editar fase | `/admin/convocatorias/{call}/fases/{phase}/editar` | `Admin/Calls/Phases/Edit` |
| Ver fase | `/admin/convocatorias/{call}/fases/{phase}` | `Admin/Calls/Phases/Show` |

#### Gestión de Resoluciones

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de resoluciones | `/admin/convocatorias/{call}/resoluciones` | `Admin/Calls/Resolutions/Index` |
| Crear resolución | `/admin/convocatorias/{call}/resoluciones/crear` | `Admin/Calls/Resolutions/Create` |
| Editar resolución | `/admin/convocatorias/{call}/resoluciones/{resolution}/editar` | `Admin/Calls/Resolutions/Edit` |
| Ver resolución | `/admin/convocatorias/{call}/resoluciones/{resolution}` | `Admin/Calls/Resolutions/Show` |

### Funcionalidades Especiales

- **Estados**: Borrador, Abierta, Cerrada, Archivada
- **Publicación**: Requiere permiso `calls.publish`
- **Fases**: 7 tipos (publicacion, solicitudes, provisional, alegaciones, definitivo, renuncias, lista_espera)
- **Resoluciones**: PDFs mediante FilePond, publicación independiente
- **Destinos**: Configuración JSON de países/ciudades
- **Baremo**: Configuración JSON de criterios de evaluación
- **Importación**: Desde Excel/CSV con modo dry-run
- **Exportación**: A Excel con filtros aplicados

### Permisos

| Permiso | Descripción |
|---------|-------------|
| `calls.view` | Ver listados y detalles |
| `calls.create` | Crear convocatorias, fases, resoluciones |
| `calls.edit` | Editar convocatorias, fases, resoluciones |
| `calls.delete` | Eliminar convocatorias, fases, resoluciones |
| `calls.publish` | Publicar convocatorias y resoluciones |

### Documentación Relacionada

- [Componentes de Convocatorias](calls-components.md)
- [CRUD de Convocatorias](admin-calls-crud.md)
- [CRUD de Fases](admin-call-phases-crud.md)
- [CRUD de Resoluciones](admin-resolutions-crud.md)

---

## 3. Módulo de Noticias

Sistema de publicación de noticias con editor enriquecido y gestión de etiquetas.

### Área Pública

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de noticias | `/noticias` | `Public/News/Index` |
| Detalle de noticia | `/noticias/{slug}` | `Public/News/Show` |
| Filtro por programa | `/noticias?program=...` | - |
| Filtro por año | `/noticias?year=...` | - |
| Filtro por etiqueta | `/noticias?tag=...` | - |
| Búsqueda | `/noticias?search=...` | - |

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado (CRUD) | `/admin/noticias` | `Admin/News/Index` |
| Crear noticia | `/admin/noticias/crear` | `Admin/News/Create` |
| Editar noticia | `/admin/noticias/{id}/editar` | `Admin/News/Edit` |
| Ver detalle | `/admin/noticias/{id}` | `Admin/News/Show` |

#### Gestión de Etiquetas

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de etiquetas | `/admin/etiquetas` | `Admin/NewsTags/Index` |
| Crear etiqueta | `/admin/etiquetas/crear` | `Admin/NewsTags/Create` |
| Editar etiqueta | `/admin/etiquetas/{id}/editar` | `Admin/NewsTags/Edit` |
| Ver etiqueta | `/admin/etiquetas/{id}` | `Admin/NewsTags/Show` |

### Funcionalidades Especiales

- **Editor Tiptap**: Contenido enriquecido (negrita, cursiva, listas, enlaces, etc.)
- **Imágenes**: Gestión avanzada con soft delete, restauración, eliminación permanente
- **Conversiones**: thumbnail, medium, large, hero (1920x1080) en WebP
- **Etiquetas**: Relación many-to-many, generación automática de slugs
- **Publicación**: Requiere permiso `news.publish`
- **Notificaciones**: Automáticas al publicar

### Permisos

| Permiso | Descripción |
|---------|-------------|
| `news.view` | Ver listados y detalles |
| `news.create` | Crear noticias y etiquetas |
| `news.edit` | Editar noticias y etiquetas |
| `news.delete` | Eliminar noticias y etiquetas |
| `news.publish` | Publicar noticias |

### Documentación Relacionada

- [Componentes de Noticias](news-components.md)
- [CRUD de Noticias](admin-news-crud.md)
- [CRUD de Etiquetas](admin-news-tags-crud.md)

---

## 4. Módulo de Documentos

Sistema de gestión documental con categorización y descarga de archivos.

### Área Pública

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de documentos | `/documentos` | `Public/Documents/Index` |
| Detalle de documento | `/documentos/{slug}` | `Public/Documents/Show` |
| Filtro por categoría | `/documentos?category=...` | - |
| Filtro por programa | `/documentos?program=...` | - |
| Filtro por año | `/documentos?year=...` | - |
| Búsqueda | `/documentos?search=...` | - |
| Descarga de archivo | Botón en detalle | - |

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado (CRUD) | `/admin/documentos` | `Admin/Documents/Index` |
| Crear documento | `/admin/documentos/crear` | `Admin/Documents/Create` |
| Editar documento | `/admin/documentos/{id}/editar` | `Admin/Documents/Edit` |
| Ver detalle | `/admin/documentos/{id}` | `Admin/Documents/Show` |

#### Gestión de Categorías

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de categorías | `/admin/categorias` | `Admin/DocumentCategories/Index` |
| Crear categoría | `/admin/categorias/crear` | `Admin/DocumentCategories/Create` |
| Editar categoría | `/admin/categorias/{id}/editar` | `Admin/DocumentCategories/Edit` |
| Ver categoría | `/admin/categorias/{id}` | `Admin/DocumentCategories/Show` |

### Funcionalidades Especiales

- **Archivos**: Subida mediante FilePond (PDF, DOC, DOCX, XLS, XLSX, etc.)
- **Categorías**: Orden configurable, generación automática de slugs
- **Consentimientos**: Gestión de MediaConsent para permisos de uso
- **Descarga**: Mediante Media Library con tracking

### Permisos

| Permiso | Descripción |
|---------|-------------|
| `documents.view` | Ver listados y detalles |
| `documents.create` | Crear documentos y categorías |
| `documents.edit` | Editar documentos y categorías |
| `documents.delete` | Eliminar documentos y categorías |

### Documentación Relacionada

- [Componentes de Documentos](documents-components.md)
- [CRUD de Documentos](admin-documents-crud.md)
- [CRUD de Categorías](admin-document-categories-crud.md)

---

## 5. Módulo de Eventos

Sistema de calendario y gestión de eventos Erasmus+.

### Área Pública

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Calendario | `/calendario` | `Public/Events/Calendar` |
| Listado de eventos | `/eventos` | `Public/Events/Index` |
| Detalle de evento | `/eventos/{id}` | `Public/Events/Show` |
| Vista mensual | `/calendario?view=month` | - |
| Vista semanal | `/calendario?view=week` | - |
| Vista diaria | `/calendario?view=day` | - |
| Filtro por programa | `/calendario?program=...` | - |

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado (CRUD) | `/admin/eventos` | `Admin/Events/Index` |
| Crear evento | `/admin/eventos/crear` | `Admin/Events/Create` |
| Editar evento | `/admin/eventos/{id}/editar` | `Admin/Events/Edit` |
| Ver detalle | `/admin/eventos/{id}` | `Admin/Events/Show` |

### Funcionalidades Especiales

- **Calendario**: Vistas mensual, semanal y diaria interactivas
- **Asociaciones**: Vinculación con programas y convocatorias
- **Imágenes**: Gestión mediante FilePond y Media Library
- **Ubicación**: Campo de texto libre para localización

### Permisos

| Permiso | Descripción |
|---------|-------------|
| `events.view` | Ver calendario y detalles |
| `events.create` | Crear eventos |
| `events.edit` | Editar eventos |
| `events.delete` | Eliminar eventos |

### Documentación Relacionada

- [Componentes de Eventos](events-components.md)
- [CRUD de Eventos](admin-events-crud.md)

---

## 6. Módulo de Newsletter

Sistema de suscripción a boletín informativo con cumplimiento GDPR.

### Área Pública

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Formulario de suscripción | `/newsletter/suscribir` | `Public/Newsletter/Subscribe` |
| Verificación de email | `/newsletter/verificar/{token}` | `Public/Newsletter/Verify` |
| Baja de suscripción | `/newsletter/baja` | `Public/Newsletter/Unsubscribe` |

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de suscriptores | `/admin/newsletter` | `Admin/Newsletter/Index` |
| Ver suscriptor | `/admin/newsletter/{id}` | `Admin/Newsletter/Show` |
| Exportar a Excel | Botón en listado | - |
| Eliminar (GDPR) | Botón en detalle | - |

### Funcionalidades Especiales

- **Verificación**: Email con token único
- **Programas de interés**: Selección múltiple de programas
- **GDPR**: Eliminación permanente (hard delete), exportación de datos
- **Filtros**: Por programa, estado de verificación, estado de suscripción

### Permisos

- **Área pública**: Sin autenticación requerida
- **Área admin**: Requiere autenticación y rol admin+

### Documentación Relacionada

- [Sistema de Newsletter](newsletter-components.md)
- [CRUD de Newsletter](admin-newsletter-subscriptions-crud.md)

---

## 7. Módulo de Usuarios

Gestión de usuarios del sistema con asignación de roles.

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de usuarios | `/admin/usuarios` | `Admin/Users/Index` |
| Crear usuario | `/admin/usuarios/crear` | `Admin/Users/Create` |
| Editar usuario | `/admin/usuarios/{id}/editar` | `Admin/Users/Edit` |
| Ver detalle | `/admin/usuarios/{id}` | `Admin/Users/Show` |
| Importar usuarios | `/admin/usuarios` (modal) | `Admin/Users/Import` |

### Funcionalidades Especiales

- **Roles**: Asignación mediante Spatie Permission
- **Audit Logs**: Visualización de actividad del usuario
- **Seguridad**: Usuario no puede eliminarse a sí mismo ni modificar sus propios roles
- **Importación**: Desde Excel/CSV con validación de roles
- **SoftDeletes**: Con posibilidad de restauración

### Permisos

| Permiso | Descripción |
|---------|-------------|
| `users.view` | Ver listado y detalles de usuarios |
| `users.create` | Crear nuevos usuarios |
| `users.edit` | Editar usuarios y asignar roles |
| `users.delete` | Eliminar usuarios |

**Nota**: Solo `super-admin` tiene acceso a la gestión de usuarios.

### Documentación Relacionada

- [CRUD de Usuarios](admin-users-crud.md)

---

## 8. Módulo de Roles y Permisos

Gestión de roles del sistema y asignación de permisos.

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de roles | `/admin/roles` | `Admin/Roles/Index` |
| Crear rol | `/admin/roles/crear` | `Admin/Roles/Create` |
| Editar rol | `/admin/roles/{id}/editar` | `Admin/Roles/Edit` |
| Ver detalle | `/admin/roles/{id}` | `Admin/Roles/Show` |

### Funcionalidades Especiales

- **Permisos agrupados**: Visualización por módulo
- **Usuarios asignados**: Ver usuarios con cada rol
- **Protección**: Roles del sistema no pueden eliminarse
- **Validación**: Nombres según constantes del sistema

### Roles del Sistema

| Rol | Descripción |
|-----|-------------|
| `super-admin` | Acceso total al sistema |
| `admin` | Gestión completa de contenido |
| `editor` | Creación y edición de contenido |
| `viewer` | Solo lectura |

### Permisos

- Solo `super-admin` tiene acceso a la gestión de roles

### Documentación Relacionada

- [Sistema de Roles y Permisos](roles-and-permissions.md)
- [CRUD de Roles](admin-roles-crud.md)

---

## 9. Módulo de Configuración

Gestión de la configuración general del sistema.

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de configuraciones | `/admin/configuracion` | `Admin/Settings/Index` |
| Editar configuración | `/admin/configuracion/{id}/editar` | `Admin/Settings/Edit` |

### Funcionalidades Especiales

- **Tipos de datos**: string, integer, boolean, json
- **Validación automática**: Según tipo de dato
- **Logo del centro**: Subida mediante FilePond
- **Traducciones**: Valores traducibles
- **Auditoría**: Registro de cambios

### Configuraciones Disponibles

| Clave | Tipo | Descripción |
|-------|------|-------------|
| `site_name` | string | Nombre del sitio |
| `center_name` | string | Nombre del centro |
| `center_logo` | string | Ruta del logo |
| `contact_email` | string | Email de contacto |
| `items_per_page` | integer | Elementos por página |

### Permisos

- Requiere rol `admin` o `super-admin`

### Documentación Relacionada

- [CRUD de Configuración](admin-settings-crud.md)

---

## 10. Módulo de Traducciones

Gestión de traducciones dinámicas para contenido multiidioma.

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de traducciones | `/admin/traducciones` | `Admin/Translations/Index` |
| Crear traducción | `/admin/traducciones/crear` | `Admin/Translations/Create` |
| Editar traducción | `/admin/traducciones/{id}/editar` | `Admin/Translations/Edit` |
| Ver detalle | `/admin/traducciones/{id}` | `Admin/Translations/Show` |

### Funcionalidades Especiales

- **Polimórficas**: Traducciones para Program, Setting, etc.
- **Campos traducibles**: Múltiples campos por modelo
- **Filtros**: Por modelo, idioma, campo
- **Búsqueda**: En tiempo real
- **Caché**: Invalidación automática al actualizar

### Idiomas Soportados

| Código | Idioma |
|--------|--------|
| `es` | Español |
| `en` | Inglés |

### Permisos

- Requiere rol `admin` o `super-admin`

### Documentación Relacionada

- [Sistema de Internacionalización](i18n-system.md)
- [CRUD de Traducciones](admin-translations-crud.md)

---

## 11. Módulo de Auditoría

Sistema de registro y consulta de actividad del sistema.

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Listado de logs | `/admin/auditoria` | `Admin/AuditLogs/Index` |
| Ver detalle de log | `/admin/auditoria/{id}` | `Admin/AuditLogs/Show` |
| Exportar a Excel | Botón en listado | - |

### Funcionalidades Especiales

- **Logging automático**: En modelos configurados (Program, Call, NewsPost, Document, ErasmusEvent, User)
- **Logging manual**: Acciones especiales (publicación, cambio de estado, etc.)
- **Filtros**: Por modelo, usuario, acción, rango de fechas
- **Diff de cambios**: Visualización antes/después
- **Exportación**: A Excel con filtros aplicados

### Acciones Registradas

| Tipo | Acciones |
|------|----------|
| Automáticas | created, updated, deleted |
| Manuales | published, status_changed, role_assigned, etc. |

### Permisos

- Requiere rol `admin` o `super-admin`

### Documentación Relacionada

- [CRUD de Auditoría](admin-audit-logs.md)

---

## 12. Módulo de Notificaciones

Sistema de notificaciones internas para usuarios autenticados.

### Área de Administración

| Funcionalidad | Ruta | Componente |
|---------------|------|------------|
| Icono con contador | Navbar | `Notifications/Bell` |
| Dropdown de notificaciones | Navbar | `Notifications/Dropdown` |
| Listado completo | `/notificaciones` | `Notifications/Index` |

### Funcionalidades Especiales

- **Generación automática**: Mediante Observers al publicar contenido
- **Polling**: Actualización cada 30 segundos
- **Marcar como leída**: Individual o todas
- **Navegación**: Click lleva al contenido relacionado

### Tipos de Notificaciones

| Evento | Mensaje |
|--------|---------|
| Convocatoria publicada | "Nueva convocatoria: {título}" |
| Resolución publicada | "Nueva resolución en {convocatoria}" |
| Noticia publicada | "Nueva noticia: {título}" |

### Permisos

- Requiere autenticación (cualquier usuario logueado)

### Documentación Relacionada

- [Sistema de Notificaciones](notifications-system.md)

---

## Componentes Transversales

### Búsqueda Global

- **Componente**: `Search/GlobalSearch`
- **Ubicación**: Navbar (público y admin)
- **Entidades**: Programas, Convocatorias, Noticias, Documentos
- **Documentación**: [Búsqueda Global](global-search.md)

### Selector de Idioma

- **Componente**: `Language/Switcher`
- **Ubicación**: Navbar
- **Idiomas**: ES, EN
- **Documentación**: [Sistema i18n](i18n-system.md)

### Breadcrumbs

- **Componente**: `x-ui.breadcrumbs`
- **Ubicación**: Todas las páginas
- **Documentación**: [Breadcrumbs](breadcrumbs.md)

### SEO

- **Componentes**: `x-seo.meta`, `x-seo.json-ld`
- **Funcionalidades**: Open Graph, Twitter Cards, JSON-LD, Sitemap
- **Documentación**: [Paso 48](pasos/paso48.md)

---

## Estadísticas de Componentes

| Tipo | Cantidad |
|------|----------|
| Componentes Livewire Públicos | 15 |
| Componentes Livewire Admin | 48 |
| Componentes Blade UI | 20+ |
| Total aproximado | 80+ |

---

**Última actualización**: Enero 2026
