# Plan Detallado: Paso 3.6.4 - Breadcrumbs

## Objetivo

Completar la implementación de breadcrumbs (migas de pan) en todas las vistas públicas y de administración de la aplicación, asegurando una navegación contextual consistente y mejorando la experiencia de usuario.

## Estado Actual

### ✅ Ya Implementado

1. **Componente de Breadcrumbs** (`resources/views/components/ui/breadcrumbs.blade.php`):
   - ✅ Componente reutilizable creado
   - ✅ Soporta items con `label`, `href` e `icon` opcional
   - ✅ Separadores configurables (chevron-right, slash, arrow-right)
   - ✅ Icono de inicio configurable
   - ✅ Accesibilidad (ARIA labels)
   - ✅ Soporte para dark mode
   - ✅ Integración con `wire:navigate` para navegación SPA

2. **Vistas Públicas con Breadcrumbs**:
   - ✅ `public/documents/index.blade.php` - Listado de documentos
   - ✅ `public/documents/show.blade.php` - Detalle de documento
   - ✅ `public/events/index.blade.php` - Listado de eventos
   - ✅ `public/events/show.blade.php` - Detalle de evento
   - ✅ `public/news/index.blade.php` - Listado de noticias
   - ✅ `public/news/show.blade.php` - Detalle de noticia
   - ✅ `public/calls/index.blade.php` - Listado de convocatorias
   - ✅ `public/calls/show.blade.php` - Detalle de convocatoria
   - ✅ `public/programs/index.blade.php` - Listado de programas (probablemente)
   - ✅ `public/programs/show.blade.php` - Detalle de programa (probablemente)

3. **Vistas de Administración con Breadcrumbs**:
   - ✅ `admin/settings/index.blade.php` - Configuración del sistema
   - ✅ `admin/audit-logs/index.blade.php` - Auditoría y logs
   - ✅ `admin/documents/index.blade.php` - Listado de documentos
   - ✅ `admin/documents/edit.blade.php` - Editar documento
   - ✅ `admin/news/index.blade.php` - Listado de noticias
   - ✅ `admin/calls/phases/index.blade.php` - Listado de fases
   - ✅ `admin/document-categories/index.blade.php` - Listado de categorías

### ⚠️ Pendiente

1. **Vistas Públicas sin Breadcrumbs**:
   - ⚠️ `public/home.blade.php` - Página principal (probablemente no necesita)
   - ⚠️ `public/events/calendar.blade.php` - Calendario de eventos
   - ⚠️ `public/newsletter/subscribe.blade.php` - Suscripción newsletter
   - ⚠️ `public/newsletter/verify.blade.php` - Verificación newsletter
   - ⚠️ `public/newsletter/unsubscribe.blade.php` - Baja newsletter

2. **Vistas de Administración sin Breadcrumbs** (muchas):
   - ⚠️ Dashboard (`admin/dashboard.blade.php`) - Probablemente no necesita
   - ⚠️ Programas: `index`, `create`, `show`, `edit`
   - ⚠️ Años Académicos: `index`, `create`, `show`, `edit`
   - ⚠️ Convocatorias: `index`, `create`, `show`, `edit`
   - ⚠️ Fases de Convocatorias: `create`, `show`, `edit`
   - ⚠️ Resoluciones: `index`, `create`, `show`, `edit`
   - ⚠️ Noticias: `create`, `show`, `edit`
   - ⚠️ Etiquetas: `index`, `create`, `show`, `edit`
   - ⚠️ Documentos: `create`, `show`
   - ⚠️ Categorías de Documentos: `create`, `show`, `edit`
   - ⚠️ Eventos: `index`, `create`, `show`, `edit`
   - ⚠️ Usuarios: `index`, `create`, `show`, `edit`
   - ⚠️ Roles: `index`, `create`, `show`, `edit`
   - ⚠️ Configuración: `edit`
   - ⚠️ Traducciones: `index`, `create`, `show`, `edit`
   - ⚠️ Auditoría: `show`
   - ⚠️ Newsletter: `index`, `show`

3. **Consistencia**:
   - ⚠️ Verificar que todos los breadcrumbs sigan el mismo patrón
   - ⚠️ Asegurar que las traducciones estén disponibles
   - ⚠️ Verificar iconos apropiados para cada sección

4. **Tests**:
   - ⚠️ Crear tests para verificar que los breadcrumbs se muestran correctamente
   - ⚠️ Verificar que los enlaces funcionan correctamente

5. **Documentación**:
   - ⚠️ Documentar el uso del componente breadcrumbs
   - ⚠️ Documentar patrones de breadcrumbs por tipo de vista

---

## Plan de Implementación

### **Fase 1: Revisión y Auditoría Completa**

#### Paso 1.1: Auditar todas las vistas públicas

**Objetivo**: Identificar exactamente qué vistas públicas tienen breadcrumbs y cuáles faltan.

**Tareas**:
1. Revisar todas las vistas públicas:
   - `public/home.blade.php`
   - `public/programs/index.blade.php`
   - `public/programs/show.blade.php`
   - `public/calls/index.blade.php`
   - `public/calls/show.blade.php`
   - `public/news/index.blade.php`
   - `public/news/show.blade.php`
   - `public/documents/index.blade.php`
   - `public/documents/show.blade.php`
   - `public/events/index.blade.php`
   - `public/events/show.blade.php`
   - `public/events/calendar.blade.php`
   - `public/newsletter/subscribe.blade.php`
   - `public/newsletter/verify.blade.php`
   - `public/newsletter/unsubscribe.blade.php`

2. Crear lista de vistas que necesitan breadcrumbs:
   - Marcar las que ya tienen
   - Marcar las que necesitan añadirse
   - Decidir si algunas no necesitan breadcrumbs (ej: home, newsletter)

**Archivos a revisar**:
- Todas las vistas en `resources/views/livewire/public/`

**Resultado esperado**:
- Lista completa de estado de breadcrumbs en vistas públicas
- Decisión sobre qué vistas necesitan breadcrumbs

---

#### Paso 1.2: Auditar todas las vistas de administración

**Objetivo**: Identificar exactamente qué vistas de administración tienen breadcrumbs y cuáles faltan.

**Tareas**:
1. Revisar todas las vistas de administración (59 archivos)
2. Crear lista organizada por módulo:
   - Dashboard
   - Programas (index, create, show, edit)
   - Años Académicos (index, create, show, edit)
   - Convocatorias (index, create, show, edit)
   - Fases (index, create, show, edit)
   - Resoluciones (index, create, show, edit)
   - Noticias (index, create, show, edit)
   - Etiquetas (index, create, show, edit)
   - Documentos (index, create, show, edit)
   - Categorías (index, create, show, edit)
   - Eventos (index, create, show, edit)
   - Usuarios (index, create, show, edit)
   - Roles (index, create, show, edit)
   - Configuración (index, edit)
   - Traducciones (index, create, show, edit)
   - Auditoría (index, show)
   - Newsletter (index, show)

3. Marcar estado de cada vista:
   - ✅ Tiene breadcrumbs
   - ⚠️ Necesita breadcrumbs
   - ❌ No necesita breadcrumbs (ej: dashboard)

**Archivos a revisar**:
- Todas las vistas en `resources/views/livewire/admin/`

**Resultado esperado**:
- Lista completa de estado de breadcrumbs en vistas de administración
- Decisión sobre qué vistas necesitan breadcrumbs

---

### **Fase 2: Definir Patrones de Breadcrumbs**

#### Paso 2.1: Definir patrón para vistas públicas

**Objetivo**: Establecer un patrón consistente para breadcrumbs en vistas públicas.

**Patrón propuesto**:
```php
// Para páginas index (listado)
[
    ['label' => __('common.nav.{section}'), 'href' => route('{section}.index')],
]

// Para páginas show (detalle)
[
    ['label' => __('common.nav.{section}'), 'href' => route('{section}.index')],
    ['label' => $model->title], // o $model->name según el modelo
]

// Para páginas especiales (calendar, newsletter)
[
    ['label' => __('common.nav.{section}'), 'href' => route('{section}.index')],
    ['label' => __('{section}.calendar')], // o título específico
]
```

**Ejemplos**:
- **Programas Index**: `[['label' => __('common.nav.programs'), 'href' => route('programas.index')]]`
- **Programas Show**: `[['label' => __('common.nav.programs'), 'href' => route('programas.index')], ['label' => $program->name]]`
- **Calendario**: `[['label' => __('common.nav.events'), 'href' => route('eventos.index')], ['label' => __('common.events.calendar')]]`

**Archivos a crear/modificar**:
- Documentar patrón en plan

**Resultado esperado**:
- Patrón claro y consistente definido
- Ejemplos documentados

---

#### Paso 2.2: Definir patrón para vistas de administración

**Objetivo**: Establecer un patrón consistente para breadcrumbs en vistas de administración.

**Patrón propuesto**:
```php
// Para páginas index (listado)
[
    ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
    ['label' => __('{Module}'), 'icon' => '{icon}'],
]

// Para páginas create
[
    ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
    ['label' => __('{Module}'), 'href' => route('admin.{module}.index'), 'icon' => '{icon}'],
    ['label' => __('common.actions.create'), 'icon' => 'plus'],
]

// Para páginas show (detalle)
[
    ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
    ['label' => __('{Module}'), 'href' => route('admin.{module}.index'), 'icon' => '{icon}'],
    ['label' => $model->title, 'href' => route('admin.{module}.show', $model), 'icon' => '{icon}'],
]

// Para páginas edit
[
    ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
    ['label' => __('{Module}'), 'href' => route('admin.{module}.index'), 'icon' => '{icon}'],
    ['label' => $model->title, 'href' => route('admin.{module}.show', $model), 'icon' => '{icon}'],
    ['label' => __('common.actions.edit'), 'icon' => 'pencil'],
]

// Para rutas anidadas (fases, resoluciones)
[
    ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
    ['label' => __('Convocatorias'), 'href' => route('admin.calls.index'), 'icon' => 'megaphone'],
    ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'megaphone'],
    ['label' => __('Fases'), 'href' => route('admin.calls.phases.index', $call), 'icon' => 'calendar'],
    ['label' => $phase->name, 'icon' => 'calendar'], // para show
]
```

**Iconos sugeridos por módulo**:
- Dashboard: `squares-2x2`
- Programas: `academic-cap`
- Años Académicos: `calendar-days`
- Convocatorias: `megaphone`
- Fases: `calendar`
- Resoluciones: `document-check`
- Noticias: `newspaper`
- Etiquetas: `tag`
- Documentos: `document`
- Categorías: `folder`
- Eventos: `calendar`
- Usuarios: `user-group`
- Roles: `shield-check`
- Configuración: `cog-6-tooth`
- Traducciones: `language`
- Auditoría: `clipboard-document-list`
- Newsletter: `envelope`

**Archivos a crear/modificar**:
- Documentar patrón en plan

**Resultado esperado**:
- Patrón claro y consistente definido
- Iconos definidos para cada módulo
- Ejemplos documentados

---

### **Fase 3: Implementar Breadcrumbs en Vistas Públicas**

#### Paso 3.1: Añadir breadcrumbs a vistas públicas que faltan

**Objetivo**: Implementar breadcrumbs en todas las vistas públicas que los necesiten.

**Tareas**:
1. Revisar cada vista pública identificada como pendiente
2. Añadir breadcrumbs siguiendo el patrón definido
3. Verificar que las traducciones estén disponibles
4. Asegurar que los estilos sean consistentes (usar clases de texto blanco para hero sections)

**Vistas a modificar**:
- `public/events/calendar.blade.php` (si necesita)
- `public/newsletter/subscribe.blade.php` (evaluar si necesita)
- `public/newsletter/verify.blade.php` (evaluar si necesita)
- `public/newsletter/unsubscribe.blade.php` (evaluar si necesita)
- Verificar `public/programs/index.blade.php` y `show.blade.php` (si no tienen)

**Archivos a modificar**:
- Vistas identificadas en Fase 1

**Resultado esperado**:
- Todas las vistas públicas que necesitan breadcrumbs los tienen
- Breadcrumbs consistentes y funcionales

---

### **Fase 4: Implementar Breadcrumbs en Vistas de Administración**

#### Paso 4.1: Añadir breadcrumbs a módulos principales

**Objetivo**: Implementar breadcrumbs en los módulos principales de administración.

**Tareas**:
1. **Programas**:
   - `admin/programs/index.blade.php`
   - `admin/programs/create.blade.php`
   - `admin/programs/show.blade.php`
   - `admin/programs/edit.blade.php`

2. **Años Académicos**:
   - `admin/academic-years/index.blade.php`
   - `admin/academic-years/create.blade.php`
   - `admin/academic-years/show.blade.php`
   - `admin/academic-years/edit.blade.php`

3. **Convocatorias**:
   - `admin/calls/index.blade.php`
   - `admin/calls/create.blade.php`
   - `admin/calls/show.blade.php`
   - `admin/calls/edit.blade.php`

**Archivos a modificar**:
- Vistas identificadas en Fase 1

**Resultado esperado**:
- Breadcrumbs implementados en módulos principales
- Patrón consistente aplicado

---

#### Paso 4.2: Añadir breadcrumbs a módulos secundarios

**Objetivo**: Implementar breadcrumbs en los módulos secundarios de administración.

**Tareas**:
1. **Noticias**:
   - `admin/news/create.blade.php`
   - `admin/news/show.blade.php`
   - `admin/news/edit.blade.php`

2. **Etiquetas**:
   - `admin/news-tags/index.blade.php`
   - `admin/news-tags/create.blade.php`
   - `admin/news-tags/show.blade.php`
   - `admin/news-tags/edit.blade.php`

3. **Documentos**:
   - `admin/documents/create.blade.php`
   - `admin/documents/show.blade.php`

4. **Categorías de Documentos**:
   - `admin/document-categories/create.blade.php`
   - `admin/document-categories/show.blade.php`
   - `admin/document-categories/edit.blade.php`

**Archivos a modificar**:
- Vistas identificadas en Fase 1

**Resultado esperado**:
- Breadcrumbs implementados en módulos secundarios
- Patrón consistente aplicado

---

#### Paso 4.3: Añadir breadcrumbs a rutas anidadas

**Objetivo**: Implementar breadcrumbs en rutas anidadas (fases y resoluciones).

**Tareas**:
1. **Fases de Convocatorias**:
   - `admin/calls/phases/create.blade.php`
   - `admin/calls/phases/show.blade.php`
   - `admin/calls/phases/edit.blade.php`

2. **Resoluciones**:
   - `admin/calls/resolutions/index.blade.php`
   - `admin/calls/resolutions/create.blade.php`
   - `admin/calls/resolutions/show.blade.php`
   - `admin/calls/resolutions/edit.blade.php`

**Patrón especial para rutas anidadas**:
```php
// Fases - Create
[
    ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
    ['label' => __('Convocatorias'), 'href' => route('admin.calls.index'), 'icon' => 'megaphone'],
    ['label' => $call->title, 'href' => route('admin.calls.show', $call), 'icon' => 'megaphone'],
    ['label' => __('Fases'), 'href' => route('admin.calls.phases.index', $call), 'icon' => 'calendar'],
    ['label' => __('common.actions.create'), 'icon' => 'plus'],
]
```

**Archivos a modificar**:
- Vistas de fases y resoluciones

**Resultado esperado**:
- Breadcrumbs implementados en rutas anidadas
- Jerarquía clara mostrada (Convocatorias > {Call} > Fases > {Phase})

---

#### Paso 4.4: Añadir breadcrumbs a módulos de sistema

**Objetivo**: Implementar breadcrumbs en módulos de sistema (usuarios, roles, configuración, etc.).

**Tareas**:
1. **Eventos**:
   - `admin/events/index.blade.php`
   - `admin/events/create.blade.php`
   - `admin/events/show.blade.php`
   - `admin/events/edit.blade.php`

2. **Usuarios**:
   - `admin/users/index.blade.php`
   - `admin/users/create.blade.php`
   - `admin/users/show.blade.php`
   - `admin/users/edit.blade.php`

3. **Roles**:
   - `admin/roles/index.blade.php`
   - `admin/roles/create.blade.php`
   - `admin/roles/show.blade.php`
   - `admin/roles/edit.blade.php`

4. **Configuración**:
   - `admin/settings/edit.blade.php`

5. **Traducciones**:
   - `admin/translations/index.blade.php`
   - `admin/translations/create.blade.php`
   - `admin/translations/show.blade.php`
   - `admin/translations/edit.blade.php`

6. **Auditoría**:
   - `admin/audit-logs/show.blade.php`

7. **Newsletter**:
   - `admin/newsletter/index.blade.php`
   - `admin/newsletter/show.blade.php`

**Archivos a modificar**:
- Vistas identificadas en Fase 1

**Resultado esperado**:
- Breadcrumbs implementados en todos los módulos de sistema
- Patrón consistente aplicado

---

### **Fase 5: Verificación y Consistencia**

#### Paso 5.1: Verificar consistencia de breadcrumbs

**Objetivo**: Asegurar que todos los breadcrumbs siguen el mismo patrón y estilo.

**Tareas**:
1. Revisar todas las vistas modificadas
2. Verificar que:
   - Todos usan el mismo componente `x-ui.breadcrumbs`
   - Los iconos son consistentes
   - Las traducciones están disponibles
   - Los estilos son consistentes
   - Los enlaces funcionan correctamente
3. Corregir cualquier inconsistencia encontrada

**Archivos a revisar**:
- Todas las vistas modificadas

**Resultado esperado**:
- Breadcrumbs consistentes en toda la aplicación
- Estilos uniformes

---

#### Paso 5.2: Verificar traducciones

**Objetivo**: Asegurar que todas las traducciones necesarias están disponibles.

**Tareas**:
1. Revisar archivos de traducción:
   - `lang/es/common.php`
   - `lang/en/common.php`
2. Verificar que existen todas las traducciones usadas en breadcrumbs:
   - `common.nav.dashboard`
   - `common.nav.programs`
   - `common.nav.calls`
   - `common.nav.news`
   - `common.nav.documents`
   - `common.nav.events`
   - `common.actions.create`
   - `common.actions.edit`
   - Etc.
3. Añadir traducciones faltantes si es necesario

**Archivos a revisar/modificar**:
- `lang/es/common.php`
- `lang/en/common.php`

**Resultado esperado**:
- Todas las traducciones disponibles
- Breadcrumbs traducidos correctamente

---

### **Fase 6: Tests**

#### Paso 6.1: Crear tests para breadcrumbs en vistas públicas

**Objetivo**: Verificar que los breadcrumbs se muestran correctamente en vistas públicas.

**Tareas**:
1. Crear o actualizar tests existentes para verificar breadcrumbs
2. Tests a implementar:
   - Verificar que breadcrumbs se muestran en páginas index
   - Verificar que breadcrumbs se muestran en páginas show
   - Verificar que los enlaces funcionan correctamente
   - Verificar que las traducciones se muestran correctamente

**Archivos a crear/modificar**:
- `tests/Feature/Components/BreadcrumbsTest.php` (nuevo)
- O actualizar tests existentes de componentes públicos

**Tests sugeridos**:
```php
describe('Public Breadcrumbs', function () {
    it('shows breadcrumbs on programs index page', ...);
    it('shows breadcrumbs on program show page', ...);
    it('breadcrumb links work correctly', ...);
    // ... más tests
});
```

**Resultado esperado**:
- Tests completos para breadcrumbs en vistas públicas
- Todos los tests pasan

---

#### Paso 6.2: Crear tests para breadcrumbs en vistas de administración

**Objetivo**: Verificar que los breadcrumbs se muestran correctamente en vistas de administración.

**Tareas**:
1. Crear tests para verificar breadcrumbs en vistas de administración
2. Tests a implementar:
   - Verificar que breadcrumbs se muestran en páginas index
   - Verificar que breadcrumbs se muestran en páginas create
   - Verificar que breadcrumbs se muestran en páginas show
   - Verificar que breadcrumbs se muestran en páginas edit
   - Verificar que breadcrumbs anidados funcionan correctamente (fases, resoluciones)
   - Verificar que los enlaces funcionan correctamente

**Archivos a crear/modificar**:
- `tests/Feature/Components/AdminBreadcrumbsTest.php` (nuevo)
- O actualizar tests existentes de componentes de administración

**Tests sugeridos**:
```php
describe('Admin Breadcrumbs', function () {
    it('shows breadcrumbs on programs index page', ...);
    it('shows breadcrumbs on program create page', ...);
    it('shows breadcrumbs on program show page', ...);
    it('shows breadcrumbs on program edit page', ...);
    it('shows nested breadcrumbs for call phases', ...);
    // ... más tests
});
```

**Resultado esperado**:
- Tests completos para breadcrumbs en vistas de administración
- Todos los tests pasan

---

### **Fase 7: Documentación**

#### Paso 7.1: Documentar uso del componente breadcrumbs

**Objetivo**: Crear documentación completa sobre cómo usar el componente breadcrumbs.

**Tareas**:
1. Crear o actualizar `docs/breadcrumbs.md`
2. Documentar:
   - Cómo usar el componente `x-ui.breadcrumbs`
   - Props disponibles
   - Patrones para vistas públicas
   - Patrones para vistas de administración
   - Patrones para rutas anidadas
   - Iconos disponibles por módulo
   - Ejemplos de uso
   - Mejores prácticas

**Archivos a crear/modificar**:
- `docs/breadcrumbs.md`

**Estructura sugerida**:
```markdown
# Breadcrumbs (Migas de Pan)

## Componente

`x-ui.breadcrumbs` - Componente reutilizable para navegación contextual

## Props

- `items`: Array de items con `label`, `href`, `icon`
- `separator`: Tipo de separador
- `homeIcon`: Mostrar icono de inicio

## Patrones

### Vistas Públicas
...

### Vistas de Administración
...

### Rutas Anidadas
...

## Iconos por Módulo
...
```

**Resultado esperado**:
- Documentación completa y actualizada
- Ejemplos de uso incluidos
- Guía clara para desarrolladores

---

#### Paso 7.2: Actualizar planificación principal

**Objetivo**: Marcar el paso 3.6.4 como completado en la planificación.

**Tareas**:
1. Actualizar `docs/planificacion_pasos.md`
2. Marcar el paso 3.6.4 como completado `[x]`
3. Añadir referencia a la documentación creada

**Archivos a modificar**:
- `docs/planificacion_pasos.md`

**Resultado esperado**:
- Planificación actualizada
- Paso marcado como completado

---

## Resumen de Archivos

### Archivos a Modificar
- **Vistas Públicas** (según auditoría):
  - `resources/views/livewire/public/events/calendar.blade.php` (si necesita)
  - `resources/views/livewire/public/newsletter/*.blade.php` (evaluar)
  - Verificar `resources/views/livewire/public/programs/*.blade.php`

- **Vistas de Administración** (muchas, según auditoría):
  - `resources/views/livewire/admin/programs/*.blade.php`
  - `resources/views/livewire/admin/academic-years/*.blade.php`
  - `resources/views/livewire/admin/calls/*.blade.php`
  - `resources/views/livewire/admin/calls/phases/*.blade.php`
  - `resources/views/livewire/admin/calls/resolutions/*.blade.php`
  - `resources/views/livewire/admin/news/*.blade.php`
  - `resources/views/livewire/admin/news-tags/*.blade.php`
  - `resources/views/livewire/admin/documents/*.blade.php`
  - `resources/views/livewire/admin/document-categories/*.blade.php`
  - `resources/views/livewire/admin/events/*.blade.php`
  - `resources/views/livewire/admin/users/*.blade.php`
  - `resources/views/livewire/admin/roles/*.blade.php`
  - `resources/views/livewire/admin/settings/edit.blade.php`
  - `resources/views/livewire/admin/translations/*.blade.php`
  - `resources/views/livewire/admin/audit-logs/show.blade.php`
  - `resources/views/livewire/admin/newsletter/*.blade.php`

- **Traducciones**:
  - `lang/es/common.php` (verificar)
  - `lang/en/common.php` (verificar)

- **Planificación**:
  - `docs/planificacion_pasos.md`

### Archivos a Crear
- `tests/Feature/Components/BreadcrumbsTest.php` - Tests de breadcrumbs públicos
- `tests/Feature/Components/AdminBreadcrumbsTest.php` - Tests de breadcrumbs de administración
- `docs/breadcrumbs.md` - Documentación de breadcrumbs

### Archivos a Revisar
- `resources/views/components/ui/breadcrumbs.blade.php` - Verificar que funciona correctamente
- Todas las vistas públicas y de administración - Auditoría completa

---

## Criterios de Éxito

1. ✅ Todas las vistas públicas que necesitan breadcrumbs los tienen
2. ✅ Todas las vistas de administración que necesitan breadcrumbs los tienen
3. ✅ Breadcrumbs consistentes en toda la aplicación
4. ✅ Patrones claros y documentados
5. ✅ Iconos apropiados para cada módulo
6. ✅ Traducciones disponibles
7. ✅ Tests completos que verifican breadcrumbs
8. ✅ Documentación completa y actualizada
9. ✅ Todos los tests pasan
10. ✅ Planificación actualizada

---

## Orden de Ejecución Recomendado

1. **Fase 1**: Revisión y auditoría completa (Pasos 1.1 y 1.2)
2. **Fase 2**: Definir patrones (Pasos 2.1 y 2.2)
3. **Fase 3**: Implementar en vistas públicas (Paso 3.1)
4. **Fase 4**: Implementar en vistas de administración (Pasos 4.1, 4.2, 4.3, 4.4)
5. **Fase 5**: Verificación y consistencia (Pasos 5.1 y 5.2)
6. **Fase 6**: Tests (Pasos 6.1 y 6.2)
7. **Fase 7**: Documentación (Pasos 7.1 y 7.2)

---

## Notas Importantes

1. **Componente Existente**: El componente `x-ui.breadcrumbs` ya está implementado y funcional. Solo necesitamos añadirlo a las vistas que faltan.

2. **Patrones Consistentes**: Es importante seguir los patrones definidos para mantener consistencia en toda la aplicación.

3. **Iconos**: Usar iconos apropiados y consistentes para cada módulo. Los iconos deben ser de Heroicons (compatibles con Flux UI).

4. **Traducciones**: Asegurar que todas las traducciones estén disponibles en español e inglés.

5. **Rutas Anidadas**: Las rutas anidadas (fases, resoluciones) deben mostrar la jerarquía completa: Dashboard > Convocatorias > {Call} > Fases > {Phase}.

6. **Estilos**: En vistas públicas con hero sections, usar clases de texto blanco para breadcrumbs. En vistas de administración, usar estilos por defecto.

7. **Tests**: Los tests deben verificar que los breadcrumbs se muestran correctamente y que los enlaces funcionan.

8. **Dashboard**: El dashboard probablemente no necesita breadcrumbs ya que es la página principal.

9. **Home Público**: La página principal pública probablemente no necesita breadcrumbs.

10. **Newsletter**: Las páginas de newsletter pueden no necesitar breadcrumbs, evaluar caso por caso.

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan listo para implementación