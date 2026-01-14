# Rutas Públicas

Este documento describe todas las rutas públicas (front-office) de la aplicación Erasmus+ Centro (Murcia).

## Página Principal

### `GET /`
- **Nombre de ruta**: `home`
- **Componente**: `App\Livewire\Public\Home`
- **Descripción**: Página principal del sitio web que muestra programas activos, convocatorias abiertas, últimas noticias y próximos eventos.
- **Layout**: `components.layouts.public`
- **Parámetros**: Ninguno

**Ejemplo de uso**:
```php
route('home')
// o
url('/')
```

---

## Programas Erasmus+

### `GET /programas`
- **Nombre de ruta**: `programas.index`
- **Componente**: `App\Livewire\Public\Programs\Index`
- **Descripción**: Listado de todos los programas Erasmus+ disponibles con filtros y búsqueda.
- **Layout**: `components.layouts.public`
- **Parámetros de consulta**:
  - `q` (opcional): Búsqueda por nombre, descripción o código
  - `tipo` (opcional): Filtro por tipo de programa (KA1, KA2, JM, DISCOVER)
  - `activos` (opcional): Mostrar solo programas activos (default: true)

**Ejemplo de uso**:
```php
route('programas.index')
route('programas.index', ['tipo' => 'KA1', 'activos' => true])
```

### `GET /programas/{program:slug}`
- **Nombre de ruta**: `programas.show`
- **Componente**: `App\Livewire\Public\Programs\Show`
- **Descripción**: Detalle de un programa Erasmus+ específico.
- **Layout**: `components.layouts.public`
- **Parámetros**:
  - `program`: Modelo `Program` resuelto por slug (route model binding)

**Ejemplo de uso**:
```php
route('programas.show', $program)
route('programas.show', 'ka121-vet-movilidad-fp')
```

**Route Model Binding**: Usa `slug` para resolver el modelo `Program`.

---

## Convocatorias

### `GET /convocatorias`
- **Nombre de ruta**: `convocatorias.index`
- **Componente**: `App\Livewire\Public\Calls\Index`
- **Descripción**: Listado de convocatorias abiertas y cerradas (solo publicadas) con filtros avanzados.
- **Layout**: `components.layouts.public`
- **Parámetros de consulta**:
  - `q` (opcional): Búsqueda por título
  - `programa` (opcional): Filtro por programa
  - `anio` (opcional): Filtro por año académico
  - `tipo` (opcional): Filtro por tipo (alumnado, personal)
  - `modalidad` (opcional): Filtro por modalidad
  - `estado` (opcional): Filtro por estado (abierta, cerrada)

**Ejemplo de uso**:
```php
route('convocatorias.index')
route('convocatorias.index', ['estado' => 'abierta', 'programa' => 1])
```

### `GET /convocatorias/{call:slug}`
- **Nombre de ruta**: `convocatorias.show`
- **Componente**: `App\Livewire\Public\Calls\Show`
- **Descripción**: Detalle de una convocatoria específica. Solo muestra convocatorias con estado 'abierta' o 'cerrada' y que estén publicadas (`published_at` no nulo).
- **Layout**: `components.layouts.public`
- **Parámetros**:
  - `call`: Modelo `Call` resuelto por slug (route model binding)

**Ejemplo de uso**:
```php
route('convocatorias.show', $call)
route('convocatorias.show', 'convocatoria-movilidad-fp-2024-2025')
```

**Route Model Binding**: Usa `slug` para resolver el modelo `Call`.

**Validación**: El componente verifica que la convocatoria tenga estado 'abierta' o 'cerrada' y esté publicada. Si no cumple estos requisitos, retorna 404.

---

## Noticias

### `GET /noticias`
- **Nombre de ruta**: `noticias.index`
- **Componente**: `App\Livewire\Public\News\Index`
- **Descripción**: Listado de noticias publicadas con filtros y búsqueda.
- **Layout**: `components.layouts.public`
- **Parámetros de consulta**:
  - `q` (opcional): Búsqueda por título, extracto o contenido
  - `programa` (opcional): Filtro por programa
  - `anio` (opcional): Filtro por año académico
  - `etiquetas` (opcional): Filtro por etiquetas (IDs separados por comas)

**Ejemplo de uso**:
```php
route('noticias.index')
route('noticias.index', ['programa' => 1, 'etiquetas' => '1,2,3'])
```

### `GET /noticias/{newsPost:slug}`
- **Nombre de ruta**: `noticias.show`
- **Componente**: `App\Livewire\Public\News\Show`
- **Descripción**: Detalle de una noticia específica. Solo muestra noticias con estado 'publicado' y que tengan `published_at` no nulo.
- **Layout**: `components.layouts.public`
- **Parámetros**:
  - `newsPost`: Modelo `NewsPost` resuelto por slug (route model binding)

**Ejemplo de uso**:
```php
route('noticias.show', $newsPost)
route('noticias.show', 'mi-experiencia-erasmus-italia')
```

**Route Model Binding**: Usa `slug` para resolver el modelo `NewsPost`.

**Validación**: El componente verifica que la noticia esté publicada. Si no está publicada, retorna 404.

---

## Documentos

### `GET /documentos`
- **Nombre de ruta**: `documentos.index`
- **Componente**: `App\Livewire\Public\Documents\Index`
- **Descripción**: Listado de documentos activos disponibles para descarga con filtros y búsqueda.
- **Layout**: `components.layouts.public`
- **Parámetros de consulta**:
  - `q` (opcional): Búsqueda por título o descripción
  - `categoria` (opcional): Filtro por categoría
  - `programa` (opcional): Filtro por programa
  - `anio` (opcional): Filtro por año académico
  - `tipo` (opcional): Filtro por tipo de documento

**Ejemplo de uso**:
```php
route('documentos.index')
route('documentos.index', ['categoria' => 1, 'tipo' => 'convocatoria'])
```

### `GET /documentos/{document:slug}`
- **Nombre de ruta**: `documentos.show`
- **Componente**: `App\Livewire\Public\Documents\Show`
- **Descripción**: Detalle de un documento específico. Solo muestra documentos activos (`is_active = true`).
- **Layout**: `components.layouts.public`
- **Parámetros**:
  - `document`: Modelo `Document` resuelto por slug (route model binding)

**Ejemplo de uso**:
```php
route('documentos.show', $document)
route('documentos.show', 'formulario-solicitud-erasmus')
```

**Route Model Binding**: Usa `slug` para resolver el modelo `Document`.

**Validación**: El componente verifica que el documento esté activo. Si está inactivo, retorna 404.

---

## Eventos

### `GET /calendario`
- **Nombre de ruta**: `calendario`
- **Componente**: `App\Livewire\Public\Events\Calendar`
- **Descripción**: Vista de calendario interactiva de eventos públicos Erasmus+.
- **Layout**: `components.layouts.public`
- **Parámetros de consulta**:
  - `mes` (opcional): Mes a mostrar (formato: YYYY-MM)
  - `vista` (opcional): Tipo de vista (mes, semana, dia)
  - `programa` (opcional): Filtro por programa

**Ejemplo de uso**:
```php
route('calendario')
route('calendario', ['mes' => '2024-03', 'vista' => 'semana'])
```

### `GET /eventos`
- **Nombre de ruta**: `eventos.index`
- **Componente**: `App\Livewire\Public\Events\Index`
- **Descripción**: Listado de eventos públicos con filtros y búsqueda.
- **Layout**: `components.layouts.public`
- **Parámetros de consulta**:
  - `q` (opcional): Búsqueda por título
  - `programa` (opcional): Filtro por programa
  - `tipo` (opcional): Filtro por tipo de evento
  - `desde` (opcional): Filtro desde fecha (formato: YYYY-MM-DD)
  - `hasta` (opcional): Filtro hasta fecha (formato: YYYY-MM-DD)
  - `pasados` (opcional): Mostrar eventos pasados (default: false)

**Ejemplo de uso**:
```php
route('eventos.index')
route('eventos.index', ['programa' => 1, 'desde' => '2024-01-01'])
```

### `GET /eventos/{event}`
- **Nombre de ruta**: `eventos.show`
- **Componente**: `App\Livewire\Public\Events\Show`
- **Descripción**: Detalle de un evento específico. Solo muestra eventos públicos (`is_public = true`).
- **Layout**: `components.layouts.public`
- **Parámetros**:
  - `event`: Modelo `ErasmusEvent` resuelto por ID (route model binding)

**Ejemplo de uso**:
```php
route('eventos.show', $event)
route('eventos.show', 123)
```

**Route Model Binding**: Usa `id` para resolver el modelo `ErasmusEvent` (no tiene campo `slug`).

**Nota**: Los eventos usan ID en lugar de slug porque son entidades más temporales y no requieren URLs amigables para SEO.

**Validación**: El componente verifica que el evento sea público. Si es privado, retorna 404.

---

## Newsletter

### `GET /newsletter/suscribir`
- **Nombre de ruta**: `newsletter.subscribe`
- **Componente**: `App\Livewire\Public\Newsletter\Subscribe`
- **Descripción**: Formulario de suscripción al newsletter.
- **Layout**: `components.layouts.public`
- **Parámetros**: Ninguno

**Ejemplo de uso**:
```php
route('newsletter.subscribe')
```

### `GET /newsletter/verificar/{token}`
- **Nombre de ruta**: `newsletter.verify`
- **Componente**: `App\Livewire\Public\Newsletter\Verify`
- **Descripción**: Verificación de suscripción al newsletter mediante token.
- **Layout**: `components.layouts.public`
- **Parámetros**:
  - `token`: Token de verificación de suscripción

**Ejemplo de uso**:
```php
route('newsletter.verify', $token)
route('newsletter.verify', 'abc123def456')
```

### `GET /newsletter/baja`
- **Nombre de ruta**: `newsletter.unsubscribe`
- **Componente**: `App\Livewire\Public\Newsletter\Unsubscribe`
- **Descripción**: Formulario de baja del newsletter.
- **Layout**: `components.layouts.public`
- **Parámetros**: Ninguno

**Ejemplo de uso**:
```php
route('newsletter.unsubscribe')
```

### `GET /newsletter/baja/{token}`
- **Nombre de ruta**: `newsletter.unsubscribe.token`
- **Componente**: `App\Livewire\Public\Newsletter\Unsubscribe`
- **Descripción**: Baja del newsletter mediante token.
- **Layout**: `components.layouts.public`
- **Parámetros**:
  - `token`: Token de baja de suscripción

**Ejemplo de uso**:
```php
route('newsletter.unsubscribe.token', $token)
route('newsletter.unsubscribe.token', 'xyz789abc123')
```

---

## Route Model Binding

### Modelos con Slug

Los siguientes modelos usan `slug` para route model binding:

- `Program`: `{program:slug}`
- `Call`: `{call:slug}`
- `NewsPost`: `{newsPost:slug}`
- `Document`: `{document:slug}`

Todos estos modelos tienen:
- Campo `slug` en la tabla con restricción `unique()`
- Generación automática de slug si no se proporciona (en método `boot()`)
- Normalización usando `Str::slug()` de Laravel

### Modelos sin Slug

- `ErasmusEvent`: `{event}` (usa `id`)

Este modelo no tiene campo `slug` porque los eventos son entidades temporales que no requieren URLs amigables para SEO.

---

## Validaciones y Restricciones

### Convocatorias
- Solo se muestran convocatorias con estado 'abierta' o 'cerrada'
- Deben tener `published_at` no nulo
- Si no cumple estos requisitos, retorna 404

### Noticias
- Solo se muestran noticias con estado 'publicado'
- Deben tener `published_at` no nulo
- Si no cumple estos requisitos, retorna 404

### Documentos
- Solo se muestran documentos con `is_active = true`
- Si está inactivo, retorna 404

### Eventos
- Solo se muestran eventos con `is_public = true`
- Si es privado, retorna 404

---

## Layout Público

Todas las rutas públicas usan el layout `components.layouts.public` que incluye:

- Navegación pública (`x-nav.public-nav`)
- Contenido principal (`main-content`)
- Footer público (`x-footer`)
- Soporte para dark mode
- Meta tags SEO dinámicos

---

## Tests

Los tests de rutas públicas se encuentran en:
- `tests/Feature/Routes/PublicRoutesTest.php` - Tests generales de todas las rutas
- `tests/Feature/Routes/DocumentsRoutesTest.php` - Tests específicos de documentos

**Cobertura de tests**:
- 39 tests pasando (52 assertions)
- Cobertura de casos exitosos y de error (404)
- Tests de route model binding
- Tests de casos edge (slugs especiales, largos, etc.)

---

**Última actualización**: Diciembre 2025  
**Estado**: ✅ Completado - Paso 3.6.1
