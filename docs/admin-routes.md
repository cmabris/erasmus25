# Rutas de Administración

Este documento describe todas las rutas de administración (back-office) de la aplicación Erasmus+ Centro (Murcia).

## Middleware y Seguridad

Todas las rutas de administración están protegidas por:
- `auth`: Requiere autenticación
- `verified`: Requiere verificación de email (si está habilitada)
- Prefijo: `/admin`
- Nombre de ruta: `admin.*`

## Autorización

La autorización se verifica en los componentes Livewire mediante `authorize()` en `mount()`.
Cada componente usa su Policy correspondiente para verificar permisos.

**Documentación detallada**: Ver [admin-routes-authorization.md](./admin-routes-authorization.md)

## Route Model Binding

**Decisión de diseño**: Las rutas de administración usan **ID** (no slug) para route model binding porque:
- No requieren URLs amigables para SEO
- Más simple de implementar (no requiere slugs únicos)
- Más rápido (búsqueda por ID es más eficiente)
- Los usuarios de administración no necesitan URLs amigables

**Nota**: Las rutas públicas usan slug para SEO, pero las rutas de administración usan ID.

**Comportamiento con SoftDeletes**: Los modelos soft-deleted retornan 404 en route model binding (comportamiento estándar de Laravel). Los componentes pueden acceder a modelos eliminados usando `withTrashed()` o `onlyTrashed()` cuando es necesario (restaurar, force delete).

---

## Dashboard

### `GET /admin`
- **Nombre de ruta**: `admin.dashboard`
- **Componente**: `App\Livewire\Admin\Dashboard`
- **Descripción**: Panel principal de administración con estadísticas y accesos rápidos.
- **Layout**: `components.layouts.app` (layout de administración)
- **Permisos**: Requiere al menos `programs.view` o `users.view` (mínimo)
- **Parámetros**: Ninguno

**Ejemplo de uso**:
```php
route('admin.dashboard')
```

**Nota**: El dashboard permite acceso a cualquier usuario autenticado. Los elementos se muestran/ocultan según permisos específicos.

---

## Programas

### `GET /admin/programas`
- **Nombre de ruta**: `admin.programs.index`
- **Componente**: `App\Livewire\Admin\Programs\Index`
- **Descripción**: Listado de programas con filtros, búsqueda y gestión de soft-deleted.
- **Permisos**: `programs.view`
- **Route Model Binding**: No aplica

**Ejemplo de uso**:
```php
route('admin.programs.index')
```

### `GET /admin/programas/crear`
- **Nombre de ruta**: `admin.programs.create`
- **Componente**: `App\Livewire\Admin\Programs\Create`
- **Descripción**: Formulario para crear un nuevo programa.
- **Permisos**: `programs.create`
- **Route Model Binding**: No aplica

**Ejemplo de uso**:
```php
route('admin.programs.create')
```

### `GET /admin/programas/{program}`
- **Nombre de ruta**: `admin.programs.show`
- **Componente**: `App\Livewire\Admin\Programs\Show`
- **Descripción**: Vista detalle de un programa con relaciones y acciones.
- **Permisos**: `programs.view`
- **Route Model Binding**: `{program}` usa ID

**Ejemplo de uso**:
```php
route('admin.programs.show', $program)
route('admin.programs.show', 1)
```

### `GET /admin/programas/{program}/editar`
- **Nombre de ruta**: `admin.programs.edit`
- **Componente**: `App\Livewire\Admin\Programs\Edit`
- **Descripción**: Formulario para editar un programa existente.
- **Permisos**: `programs.edit`
- **Route Model Binding**: `{program}` usa ID

**Ejemplo de uso**:
```php
route('admin.programs.edit', $program)
route('admin.programs.edit', 1)
```

---

## Años Académicos

### `GET /admin/anios-academicos`
- **Nombre de ruta**: `admin.academic-years.index`
- **Componente**: `App\Livewire\Admin\AcademicYears\Index`
- **Descripción**: Listado de años académicos con gestión completa.
- **Permisos**: `academic-years.view`
- **Route Model Binding**: No aplica

### `GET /admin/anios-academicos/crear`
- **Nombre de ruta**: `admin.academic-years.create`
- **Componente**: `App\Livewire\Admin\AcademicYears\Create`
- **Descripción**: Formulario para crear un nuevo año académico.
- **Permisos**: `academic-years.create`

### `GET /admin/anios-academicos/{academic_year}`
- **Nombre de ruta**: `admin.academic-years.show`
- **Componente**: `App\Livewire\Admin\AcademicYears\Show`
- **Descripción**: Vista detalle de un año académico.
- **Permisos**: `academic-years.view`
- **Route Model Binding**: `{academic_year}` usa ID

### `GET /admin/anios-academicos/{academic_year}/editar`
- **Nombre de ruta**: `admin.academic-years.edit`
- **Componente**: `App\Livewire\Admin\AcademicYears\Edit`
- **Descripción**: Formulario para editar un año académico.
- **Permisos**: `academic-years.edit`
- **Route Model Binding**: `{academic_year}` usa ID

---

## Convocatorias

### `GET /admin/convocatorias`
- **Nombre de ruta**: `admin.calls.index`
- **Componente**: `App\Livewire\Admin\Calls\Index`
- **Descripción**: Listado de convocatorias con filtros avanzados.
- **Permisos**: `calls.view`
- **Route Model Binding**: No aplica

### `GET /admin/convocatorias/crear`
- **Nombre de ruta**: `admin.calls.create`
- **Componente**: `App\Livewire\Admin\Calls\Create`
- **Descripción**: Formulario para crear una nueva convocatoria.
- **Permisos**: `calls.create`

### `GET /admin/convocatorias/{call}`
- **Nombre de ruta**: `admin.calls.show`
- **Componente**: `App\Livewire\Admin\Calls\Show`
- **Descripción**: Vista detalle de una convocatoria con fases y resoluciones.
- **Permisos**: `calls.view`
- **Route Model Binding**: `{call}` usa ID

### `GET /admin/convocatorias/{call}/editar`
- **Nombre de ruta**: `admin.calls.edit`
- **Componente**: `App\Livewire\Admin\Calls\Edit`
- **Descripción**: Formulario para editar una convocatoria.
- **Permisos**: `calls.edit`
- **Route Model Binding**: `{call}` usa ID

---

## Fases de Convocatorias (Rutas Anidadas)

### `GET /admin/convocatorias/{call}/fases`
- **Nombre de ruta**: `admin.calls.phases.index`
- **Componente**: `App\Livewire\Admin\Calls\Phases\Index`
- **Descripción**: Listado de fases de una convocatoria específica.
- **Permisos**: `calls.phases.view`
- **Route Model Binding**: `{call}` usa ID

**Ejemplo de uso**:
```php
route('admin.calls.phases.index', $call)
route('admin.calls.phases.index', 1)
```

### `GET /admin/convocatorias/{call}/fases/crear`
- **Nombre de ruta**: `admin.calls.phases.create`
- **Componente**: `App\Livewire\Admin\Calls\Phases\Create`
- **Descripción**: Formulario para crear una nueva fase.
- **Permisos**: `calls.phases.create`
- **Route Model Binding**: `{call}` usa ID

### `GET /admin/convocatorias/{call}/fases/{call_phase}`
- **Nombre de ruta**: `admin.calls.phases.show`
- **Componente**: `App\Livewire\Admin\Calls\Phases\Show`
- **Descripción**: Vista detalle de una fase.
- **Permisos**: `calls.phases.view`
- **Route Model Binding**: `{call}` y `{call_phase}` usan ID

**Ejemplo de uso**:
```php
route('admin.calls.phases.show', [$call, $callPhase])
route('admin.calls.phases.show', [1, 5])
```

### `GET /admin/convocatorias/{call}/fases/{call_phase}/editar`
- **Nombre de ruta**: `admin.calls.phases.edit`
- **Componente**: `App\Livewire\Admin\Calls\Phases\Edit`
- **Descripción**: Formulario para editar una fase.
- **Permisos**: `calls.phases.edit`
- **Route Model Binding**: `{call}` y `{call_phase}` usan ID

---

## Resoluciones de Convocatorias (Rutas Anidadas)

### `GET /admin/convocatorias/{call}/resoluciones`
- **Nombre de ruta**: `admin.calls.resolutions.index`
- **Componente**: `App\Livewire\Admin\Calls\Resolutions\Index`
- **Descripción**: Listado de resoluciones de una convocatoria específica.
- **Permisos**: `resolutions.view`
- **Route Model Binding**: `{call}` usa ID

### `GET /admin/convocatorias/{call}/resoluciones/crear`
- **Nombre de ruta**: `admin.calls.resolutions.create`
- **Componente**: `App\Livewire\Admin\Calls\Resolutions\Create`
- **Descripción**: Formulario para crear una nueva resolución.
- **Permisos**: `resolutions.create`
- **Route Model Binding**: `{call}` usa ID

### `GET /admin/convocatorias/{call}/resoluciones/{resolution}`
- **Nombre de ruta**: `admin.calls.resolutions.show`
- **Componente**: `App\Livewire\Admin\Calls\Resolutions\Show`
- **Descripción**: Vista detalle de una resolución.
- **Permisos**: `resolutions.view`
- **Route Model Binding**: `{call}` y `{resolution}` usan ID

### `GET /admin/convocatorias/{call}/resoluciones/{resolution}/editar`
- **Nombre de ruta**: `admin.calls.resolutions.edit`
- **Componente**: `App\Livewire\Admin\Calls\Resolutions\Edit`
- **Descripción**: Formulario para editar una resolución.
- **Permisos**: `resolutions.edit`
- **Route Model Binding**: `{call}` y `{resolution}` usan ID

---

## Noticias

### `GET /admin/noticias`
- **Nombre de ruta**: `admin.news.index`
- **Componente**: `App\Livewire\Admin\News\Index`
- **Descripción**: Listado de noticias con filtros y gestión de imágenes.
- **Permisos**: `news.view`
- **Route Model Binding**: No aplica

### `GET /admin/noticias/crear`
- **Nombre de ruta**: `admin.news.create`
- **Componente**: `App\Livewire\Admin\News\Create`
- **Descripción**: Formulario para crear una nueva noticia con editor Tiptap.
- **Permisos**: `news.create`

### `GET /admin/noticias/{news_post}`
- **Nombre de ruta**: `admin.news.show`
- **Componente**: `App\Livewire\Admin\News\Show`
- **Descripción**: Vista detalle de una noticia.
- **Permisos**: `news.view`
- **Route Model Binding**: `{news_post}` usa ID

### `GET /admin/noticias/{news_post}/editar`
- **Nombre de ruta**: `admin.news.edit`
- **Componente**: `App\Livewire\Admin\News\Edit`
- **Descripción**: Formulario para editar una noticia.
- **Permisos**: `news.edit`
- **Route Model Binding**: `{news_post}` usa ID

---

## Etiquetas de Noticias

### `GET /admin/etiquetas`
- **Nombre de ruta**: `admin.news-tags.index`
- **Componente**: `App\Livewire\Admin\NewsTags\Index`
- **Descripción**: Listado de etiquetas de noticias.
- **Permisos**: `news-tags.view`
- **Route Model Binding**: No aplica

### `GET /admin/etiquetas/crear`
- **Nombre de ruta**: `admin.news-tags.create`
- **Componente**: `App\Livewire\Admin\NewsTags\Create`
- **Descripción**: Formulario para crear una nueva etiqueta.
- **Permisos**: `news-tags.create`

### `GET /admin/etiquetas/{news_tag}`
- **Nombre de ruta**: `admin.news-tags.show`
- **Componente**: `App\Livewire\Admin\NewsTags\Show`
- **Descripción**: Vista detalle de una etiqueta.
- **Permisos**: `news-tags.view`
- **Route Model Binding**: `{news_tag}` usa ID

### `GET /admin/etiquetas/{news_tag}/editar`
- **Nombre de ruta**: `admin.news-tags.edit`
- **Componente**: `App\Livewire\Admin\NewsTags\Edit`
- **Descripción**: Formulario para editar una etiqueta.
- **Permisos**: `news-tags.edit`
- **Route Model Binding**: `{news_tag}` usa ID

---

## Documentos

### `GET /admin/documentos`
- **Nombre de ruta**: `admin.documents.index`
- **Componente**: `App\Livewire\Admin\Documents\Index`
- **Descripción**: Listado de documentos con filtros y gestión de archivos.
- **Permisos**: `documents.view`
- **Route Model Binding**: No aplica

### `GET /admin/documentos/crear`
- **Nombre de ruta**: `admin.documents.create`
- **Componente**: `App\Livewire\Admin\Documents\Create`
- **Descripción**: Formulario para crear un nuevo documento con upload de archivos.
- **Permisos**: `documents.create`

### `GET /admin/documentos/{document}`
- **Nombre de ruta**: `admin.documents.show`
- **Componente**: `App\Livewire\Admin\Documents\Show`
- **Descripción**: Vista detalle de un documento.
- **Permisos**: `documents.view`
- **Route Model Binding**: `{document}` usa ID

### `GET /admin/documentos/{document}/editar`
- **Nombre de ruta**: `admin.documents.edit`
- **Componente**: `App\Livewire\Admin\Documents\Edit`
- **Descripción**: Formulario para editar un documento.
- **Permisos**: `documents.edit`
- **Route Model Binding**: `{document}` usa ID

---

## Categorías de Documentos

### `GET /admin/categorias`
- **Nombre de ruta**: `admin.document-categories.index`
- **Componente**: `App\Livewire\Admin\DocumentCategories\Index`
- **Descripción**: Listado de categorías de documentos.
- **Permisos**: `document-categories.view`
- **Route Model Binding**: No aplica

### `GET /admin/categorias/crear`
- **Nombre de ruta**: `admin.document-categories.create`
- **Componente**: `App\Livewire\Admin\DocumentCategories\Create`
- **Descripción**: Formulario para crear una nueva categoría.
- **Permisos**: `document-categories.create`

### `GET /admin/categorias/{document_category}`
- **Nombre de ruta**: `admin.document-categories.show`
- **Componente**: `App\Livewire\Admin\DocumentCategories\Show`
- **Descripción**: Vista detalle de una categoría.
- **Permisos**: `document-categories.view`
- **Route Model Binding**: `{document_category}` usa ID

### `GET /admin/categorias/{document_category}/editar`
- **Nombre de ruta**: `admin.document-categories.edit`
- **Componente**: `App\Livewire\Admin\DocumentCategories\Edit`
- **Descripción**: Formulario para editar una categoría.
- **Permisos**: `document-categories.edit`
- **Route Model Binding**: `{document_category}` usa ID

---

## Eventos

### `GET /admin/eventos`
- **Nombre de ruta**: `admin.events.index`
- **Componente**: `App\Livewire\Admin\Events\Index`
- **Descripción**: Listado de eventos con vista de calendario y gestión de imágenes.
- **Permisos**: `events.view`
- **Route Model Binding**: No aplica

**Nota**: El modelo `ErasmusEvent` no tiene campo `slug`, por lo que siempre usa ID.

### `GET /admin/eventos/crear`
- **Nombre de ruta**: `admin.events.create`
- **Componente**: `App\Livewire\Admin\Events\Create`
- **Descripción**: Formulario para crear un nuevo evento.
- **Permisos**: `events.create`

### `GET /admin/eventos/{event}`
- **Nombre de ruta**: `admin.events.show`
- **Componente**: `App\Livewire\Admin\Events\Show`
- **Descripción**: Vista detalle de un evento.
- **Permisos**: `events.view`
- **Route Model Binding**: `{event}` usa ID

### `GET /admin/eventos/{event}/editar`
- **Nombre de ruta**: `admin.events.edit`
- **Componente**: `App\Livewire\Admin\Events\Edit`
- **Descripción**: Formulario para editar un evento.
- **Permisos**: `events.edit`
- **Route Model Binding**: `{event}` usa ID

---

## Usuarios

### `GET /admin/usuarios`
- **Nombre de ruta**: `admin.users.index`
- **Componente**: `App\Livewire\Admin\Users\Index`
- **Descripción**: Listado de usuarios con gestión de roles y permisos.
- **Permisos**: `users.view`
- **Route Model Binding**: No aplica

**Nota**: Los usuarios no tienen slug, siempre usan ID.

### `GET /admin/usuarios/crear`
- **Nombre de ruta**: `admin.users.create`
- **Componente**: `App\Livewire\Admin\Users\Create`
- **Descripción**: Formulario para crear un nuevo usuario.
- **Permisos**: `users.create`

### `GET /admin/usuarios/{user}`
- **Nombre de ruta**: `admin.users.show`
- **Componente**: `App\Livewire\Admin\Users\Show`
- **Descripción**: Vista detalle de un usuario con actividad y roles.
- **Permisos**: `users.view`
- **Route Model Binding**: `{user}` usa ID

### `GET /admin/usuarios/{user}/editar`
- **Nombre de ruta**: `admin.users.edit`
- **Componente**: `App\Livewire\Admin\Users\Edit`
- **Descripción**: Formulario para editar un usuario y asignar roles.
- **Permisos**: `users.edit`
- **Route Model Binding**: `{user}` usa ID

---

## Roles y Permisos

### `GET /admin/roles`
- **Nombre de ruta**: `admin.roles.index`
- **Componente**: `App\Livewire\Admin\Roles\Index`
- **Descripción**: Listado de roles con gestión de permisos.
- **Permisos**: `roles.view` (solo super-admin)
- **Route Model Binding**: No aplica

**Nota**: Solo super-admin puede acceder a la gestión de roles.

### `GET /admin/roles/crear`
- **Nombre de ruta**: `admin.roles.create`
- **Componente**: `App\Livewire\Admin\Roles\Create`
- **Descripción**: Formulario para crear un nuevo rol con permisos.
- **Permisos**: `roles.create` (solo super-admin)

### `GET /admin/roles/{role}`
- **Nombre de ruta**: `admin.roles.show`
- **Componente**: `App\Livewire\Admin\Roles\Show`
- **Descripción**: Vista detalle de un rol con usuarios asignados.
- **Permisos**: `roles.view` (solo super-admin)
- **Route Model Binding**: `{role}` usa ID

### `GET /admin/roles/{role}/editar`
- **Nombre de ruta**: `admin.roles.edit`
- **Componente**: `App\Livewire\Admin\Roles\Edit`
- **Descripción**: Formulario para editar un rol y sus permisos.
- **Permisos**: `roles.edit` (solo super-admin)
- **Route Model Binding**: `{role}` usa ID

---

## Configuración del Sistema

### `GET /admin/configuracion`
- **Nombre de ruta**: `admin.settings.index`
- **Componente**: `App\Livewire\Admin\Settings\Index`
- **Descripción**: Listado de configuraciones del sistema agrupadas por categoría.
- **Permisos**: `settings.view` (solo admin y super-admin)
- **Route Model Binding**: No aplica

### `GET /admin/configuracion/{setting}/editar`
- **Nombre de ruta**: `admin.settings.edit`
- **Componente**: `App\Livewire\Admin\Settings\Edit`
- **Descripción**: Formulario para editar una configuración del sistema.
- **Permisos**: `settings.edit` (solo admin y super-admin)
- **Route Model Binding**: `{setting}` usa ID

---

## Traducciones

### `GET /admin/traducciones`
- **Nombre de ruta**: `admin.translations.index`
- **Componente**: `App\Livewire\Admin\Translations\Index`
- **Descripción**: Listado de traducciones con filtros por modelo e idioma.
- **Permisos**: `translations.view`
- **Route Model Binding**: No aplica

### `GET /admin/traducciones/crear`
- **Nombre de ruta**: `admin.translations.create`
- **Componente**: `App\Livewire\Admin\Translations\Create`
- **Descripción**: Formulario para crear una nueva traducción.
- **Permisos**: `translations.create`

### `GET /admin/traducciones/{translation}`
- **Nombre de ruta**: `admin.translations.show`
- **Componente**: `App\Livewire\Admin\Translations\Show`
- **Descripción**: Vista detalle de una traducción.
- **Permisos**: `translations.view`
- **Route Model Binding**: `{translation}` usa ID

### `GET /admin/traducciones/{translation}/editar`
- **Nombre de ruta**: `admin.translations.edit`
- **Componente**: `App\Livewire\Admin\Translations\Edit`
- **Descripción**: Formulario para editar una traducción.
- **Permisos**: `translations.edit`
- **Route Model Binding**: `{translation}` usa ID

---

## Auditoría y Logs

### `GET /admin/auditoria`
- **Nombre de ruta**: `admin.audit-logs.index`
- **Componente**: `App\Livewire\Admin\AuditLogs\Index`
- **Descripción**: Listado de logs de auditoría con filtros avanzados y exportación.
- **Permisos**: `audit-logs.view` (solo admin y super-admin)
- **Route Model Binding**: No aplica

**Nota**: Solo admin y super-admin pueden acceder a los logs de auditoría.

### `GET /admin/auditoria/{activity}`
- **Nombre de ruta**: `admin.audit-logs.show`
- **Componente**: `App\Livewire\Admin\AuditLogs\Show`
- **Descripción**: Vista detalle de un log de auditoría con cambios antes/después.
- **Permisos**: `audit-logs.view` (solo admin y super-admin)
- **Route Model Binding**: `{activity}` usa ID

---

## Suscripciones Newsletter

### `GET /admin/newsletter`
- **Nombre de ruta**: `admin.newsletter.index`
- **Componente**: `App\Livewire\Admin\Newsletter\Index`
- **Descripción**: Listado de suscriptores al newsletter con filtros y exportación.
- **Permisos**: `newsletter.view` (solo admin y super-admin)
- **Route Model Binding**: No aplica

### `GET /admin/newsletter/{newsletter_subscription}`
- **Nombre de ruta**: `admin.newsletter.show`
- **Componente**: `App\Livewire\Admin\Newsletter\Show`
- **Descripción**: Vista detalle de una suscripción al newsletter.
- **Permisos**: `newsletter.view` (solo admin y super-admin)
- **Route Model Binding**: `{newsletter_subscription}` usa ID

---

## Resumen de Rutas por Módulo

| Módulo | Rutas | Permisos Base |
|--------|-------|---------------|
| Dashboard | 1 | `programs.view` o `users.view` |
| Programas | 4 | `programs.*` |
| Años Académicos | 4 | `academic-years.*` |
| Convocatorias | 4 | `calls.*` |
| Fases | 4 (anidadas) | `calls.phases.*` |
| Resoluciones | 4 (anidadas) | `resolutions.*` |
| Noticias | 4 | `news.*` |
| Etiquetas | 4 | `news-tags.*` |
| Documentos | 4 | `documents.*` |
| Categorías | 4 | `document-categories.*` |
| Eventos | 4 | `events.*` |
| Usuarios | 4 | `users.*` |
| Roles | 4 | `roles.*` (solo super-admin) |
| Configuración | 2 | `settings.*` (solo admin/super-admin) |
| Traducciones | 4 | `translations.*` |
| Auditoría | 2 | `audit-logs.view` (solo admin/super-admin) |
| Newsletter | 2 | `newsletter.view` (solo admin/super-admin) |

**Total**: 59 rutas de administración

---

## Testing

Todas las rutas están cubiertas por tests en `tests/Feature/Routes/AdminRoutesTest.php`:
- ✅ 90 tests pasando (107 assertions)
- ✅ Verificación de redirección de usuarios no autenticados
- ✅ Verificación de acceso con permisos correctos
- ✅ Verificación de denegación sin permisos (403)
- ✅ Verificación de route model binding
- ✅ Verificación de 404 para recursos no existentes
- ✅ Verificación de casos edge (SoftDeletes, IDs inválidos)

---

## Referencias

- [Documentación de Autorización](./admin-routes-authorization.md)
- [Documentación de Rutas Públicas](./public-routes.md)
- [Laravel Routing Documentation](https://laravel.com/docs/routing)
- [Laravel Authorization Documentation](https://laravel.com/docs/authorization)
- [Livewire Authorization](https://livewire.laravel.com/docs/authorization)
