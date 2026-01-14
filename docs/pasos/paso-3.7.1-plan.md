# Plan Detallado: Paso 3.7.1 - Búsqueda Global

## Objetivo

Implementar una funcionalidad de búsqueda global que permita a los usuarios buscar contenido en múltiples entidades (programas, convocatorias, noticias, documentos) desde una única interfaz, con resultados agrupados por tipo y filtros avanzados.

## Estado Actual

### ✅ Ya Implementado

1. **Componentes de Búsqueda Individuales**:
   - ✅ `Public\Programs\Index` - Búsqueda en programas
   - ✅ `Public\Calls\Index` - Búsqueda en convocatorias
   - ✅ `Public\News\Index` - Búsqueda en noticias
   - ✅ `Public\Documents\Index` - Búsqueda en documentos
   - Todos usan el componente `x-ui.search-input` reutilizable
   - Todos implementan filtros específicos por entidad

2. **Componente UI de Búsqueda**:
   - ✅ `resources/views/components/ui/search-input.blade.php` - Componente reutilizable
   - ✅ Soporta debounce, loading states, clear button
   - ✅ Integrado con Alpine.js para interactividad

3. **Modelos con Búsqueda**:
   - ✅ `Program` - Campos: name, description, code
   - ✅ `Call` - Campos: title, requirements, documentation
   - ✅ `NewsPost` - Campos: title, excerpt, content
   - ✅ `Document` - Campos: title, description

### ⚠️ Pendiente

1. **Componente de Búsqueda Global**:
   - ⚠️ Crear `App\Livewire\Search\GlobalSearch`
   - ⚠️ Búsqueda unificada en múltiples entidades
   - ⚠️ Resultados agrupados por tipo
   - ⚠️ Filtros avanzados (tipo de contenido, programa, año académico)
   - ⚠️ Paginación por grupo de resultados
   - ⚠️ Historial de búsquedas (opcional)

2. **Ruta Pública**:
   - ⚠️ Crear ruta `/buscar` o `/search`
   - ⚠️ Integrar en navegación pública

3. **Vista de Resultados**:
   - ⚠️ Diseño responsive con Flux UI
   - ⚠️ Cards de resultados por tipo
   - ⚠️ Indicadores de cantidad de resultados
   - ⚠️ Enlaces a detalles de cada resultado

4. **Optimizaciones**:
   - ⚠️ Búsqueda eficiente con índices de BD
   - ⚠️ Debounce en búsqueda en tiempo real
   - ⚠️ Límite de resultados por tipo

5. **Tests**:
   - ⚠️ Tests de búsqueda por tipo de contenido
   - ⚠️ Tests de filtros
   - ⚠️ Tests de paginación
   - ⚠️ Tests de resultados vacíos

6. **Traducciones**:
   - ⚠️ Añadir traducciones para búsqueda global
   - ⚠️ Mensajes de resultados vacíos
   - ⚠️ Labels de filtros

---

## Plan de Implementación

### **Fase 1: Análisis y Diseño**

#### Paso 1.1: Definir Estructura del Componente

**Objetivo**: Establecer la estructura base del componente Livewire y definir los campos de búsqueda.

**Tareas**:
1. Crear estructura de directorio:
   - `app/Livewire/Search/GlobalSearch.php`
   - `resources/views/livewire/search/global-search.blade.php`

2. Definir propiedades del componente:
   - `$query` - Término de búsqueda (con `#[Url]`)
   - `$types` - Tipos de contenido a buscar (array: programs, calls, news, documents)
   - `$program` - Filtro por programa (opcional)
   - `$academicYear` - Filtro por año académico (opcional)
   - `$showFilters` - Mostrar/ocultar filtros avanzados

3. Definir métodos computados:
   - `results()` - Resultados agrupados por tipo
   - `totalResults()` - Total de resultados encontrados
   - `hasResults()` - Verificar si hay resultados

**Archivos a crear**:
- `app/Livewire/Search/GlobalSearch.php`

**Resultado esperado**:
- Estructura base del componente definida
- Propiedades y métodos principales identificados

---

#### Paso 1.2: Diseñar Lógica de Búsqueda

**Objetivo**: Definir cómo se realizará la búsqueda en cada tipo de entidad.

**Tareas**:
1. Analizar campos de búsqueda por entidad:
   - **Programs**: `name`, `description`, `code`
   - **Calls**: `title`, `requirements`, `documentation` (solo publicadas)
   - **News**: `title`, `excerpt`, `content` (solo publicadas)
   - **Documents**: `title`, `description` (solo activos)

2. Definir criterios de búsqueda:
   - Búsqueda con `LIKE` en múltiples campos
   - Considerar búsqueda case-insensitive
   - Límite de resultados por tipo (ej: 10 por tipo inicialmente)

3. Definir filtros disponibles:
   - Tipo de contenido (checkbox múltiple)
   - Programa (select)
   - Año académico (select)

**Consideraciones técnicas**:
- Usar `whereIn` para múltiples tipos
- Aplicar filtros de publicación/activo según entidad
- Optimizar consultas con eager loading

**Resultado esperado**:
- Lógica de búsqueda definida para cada entidad
- Criterios de filtrado establecidos

---

### **Fase 2: Implementación del Componente Livewire**

#### Paso 2.1: Crear Componente Base

**Objetivo**: Crear el componente Livewire con estructura básica.

**Tareas**:
1. Crear `app/Livewire/Search/GlobalSearch.php`:
   ```php
   - Usar `WithPagination` trait
   - Definir propiedades públicas con `#[Url]` donde corresponda
   - Implementar método `mount()`
   - Implementar método `render()`
   ```

2. Implementar propiedades:
   - `public string $query = ''` con `#[Url(as: 'q')]`
   - `public array $types = ['programs', 'calls', 'news', 'documents']`
   - `public ?int $program = null` con `#[Url]`
   - `public ?int $academicYear = null` con `#[Url]`
   - `public bool $showFilters = false`

3. Implementar métodos básicos:
   - `resetFilters()` - Resetear todos los filtros
   - `toggleType(string $type)` - Activar/desactivar tipo de búsqueda
   - `updatedQuery()` - Resetear página al cambiar búsqueda

**Archivos a crear**:
- `app/Livewire/Search/GlobalSearch.php`

**Resultado esperado**:
- Componente base creado con estructura correcta
- Propiedades y métodos básicos implementados

---

#### Paso 2.2: Implementar Búsqueda por Entidades

**Objetivo**: Implementar la lógica de búsqueda para cada tipo de entidad.

**Tareas**:
1. Crear métodos privados para búsqueda por tipo:
   - `searchPrograms(string $query): Collection`
   - `searchCalls(string $query): Collection`
   - `searchNews(string $query): Collection`
   - `searchDocuments(string $query): Collection`

2. Implementar cada método:
   - **Programs**: Buscar en `name`, `description`, `code` donde `is_active = true`
   - **Calls**: Buscar en `title`, `requirements`, `documentation` donde `status IN ('abierta', 'cerrada')` y `published_at IS NOT NULL`
   - **News**: Buscar en `title`, `excerpt`, `content` donde `status = 'publicado'` y `published_at IS NOT NULL`
   - **Documents**: Buscar en `title`, `description` donde `is_active = true`

3. Aplicar filtros comunes:
   - Filtrar por `program_id` si está seleccionado
   - Filtrar por `academic_year_id` si está seleccionado
   - Limitar resultados (ej: 10 por tipo inicialmente)

4. Crear método `results()` computado:
   - Agrupar resultados por tipo
   - Retornar estructura: `['programs' => [...], 'calls' => [...], ...]`

**Archivos a modificar**:
- `app/Livewire/Search/GlobalSearch.php`

**Resultado esperado**:
- Búsqueda funcional para cada tipo de entidad
- Resultados agrupados correctamente

---

#### Paso 2.3: Implementar Filtros Avanzados

**Objetivo**: Añadir funcionalidad de filtros avanzados.

**Tareas**:
1. Crear propiedades computadas para opciones de filtros:
   - `availablePrograms()` - Lista de programas para filtro
   - `availableAcademicYears()` - Lista de años académicos para filtro

2. Implementar métodos de filtrado:
   - Aplicar filtros en cada método de búsqueda
   - Validar que los filtros se apliquen correctamente

3. Implementar toggle de filtros:
   - Método `toggleFilters()` para mostrar/ocultar
   - Estado persistente (opcional con `#[Url]`)

**Archivos a modificar**:
- `app/Livewire/Search/GlobalSearch.php`

**Resultado esperado**:
- Filtros avanzados funcionales
- Opciones de filtros cargadas correctamente

---

#### Paso 2.4: Optimizar Consultas y Rendimiento

**Objetivo**: Optimizar las consultas de búsqueda para mejor rendimiento.

**Tareas**:
1. Añadir eager loading:
   - Cargar relaciones necesarias (program, academicYear, author, etc.)
   - Evitar N+1 queries

2. Implementar límites:
   - Limitar resultados por tipo (ej: 10 iniciales)
   - Añadir "Ver más" para cada tipo

3. Optimizar búsqueda:
   - Usar índices de BD donde sea posible
   - Considerar full-text search si hay muchos registros

4. Implementar debounce:
   - Usar `wire:model.live.debounce.300ms` en vista
   - Evitar búsquedas excesivas mientras el usuario escribe

**Archivos a modificar**:
- `app/Livewire/Search/GlobalSearch.php`

**Resultado esperado**:
- Consultas optimizadas
- Búsqueda rápida y eficiente

---

### **Fase 3: Implementación de la Vista**

#### Paso 3.1: Crear Vista Base

**Objetivo**: Crear la vista Blade con estructura básica.

**Tareas**:
1. Crear `resources/views/livewire/search/global-search.blade.php`

2. Implementar estructura base:
   - Header con título y descripción
   - Campo de búsqueda principal
   - Botón para mostrar/ocultar filtros
   - Sección de resultados

3. Usar layout público:
   - `components.layouts.public`
   - Título y meta description apropiados

**Archivos a crear**:
- `resources/views/livewire/search/global-search.blade.php`

**Resultado esperado**:
- Vista base creada con estructura correcta
- Layout aplicado correctamente

---

#### Paso 3.2: Implementar Campo de Búsqueda y Filtros

**Objetivo**: Añadir el campo de búsqueda y filtros avanzados.

**Tareas**:
1. Implementar campo de búsqueda:
   - Usar componente `x-ui.search-input`
   - Configurar `wire:model.live.debounce.300ms="query"`
   - Placeholder apropiado

2. Implementar filtros avanzados:
   - Checkboxes para tipos de contenido
   - Select para programa
   - Select para año académico
   - Botón "Limpiar filtros"

3. Diseño responsive:
   - Filtros colapsables en móvil
   - Layout flexible con Flux UI

**Archivos a modificar**:
- `resources/views/livewire/search/global-search.blade.php`

**Resultado esperado**:
- Campo de búsqueda funcional
- Filtros avanzados implementados y responsive

---

#### Paso 3.3: Implementar Visualización de Resultados

**Objetivo**: Mostrar resultados agrupados por tipo.

**Tareas**:
1. Crear secciones por tipo de resultado:
   - Sección "Programas" (`@if(isset($results['programs']))`)
   - Sección "Convocatorias" (`@if(isset($results['calls']))`)
   - Sección "Noticias" (`@if(isset($results['news']))`)
   - Sección "Documentos" (`@if(isset($results['documents']))`)

2. Para cada sección:
   - Título con contador de resultados
   - Lista de resultados usando cards reutilizables
   - Enlace "Ver más" si hay más resultados

3. Reutilizar componentes existentes:
   - Usar cards de `Public\Programs\Index` si es posible
   - O crear cards simplificadas para resultados de búsqueda

4. Implementar estado vacío:
   - Mensaje cuando no hay resultados
   - Sugerencias de búsqueda

**Archivos a modificar**:
- `resources/views/livewire/search/global-search.blade.php`

**Archivos a revisar**:
- `resources/views/livewire/public/programs/index.blade.php` (para reutilizar cards)
- `resources/views/livewire/public/calls/index.blade.php`
- `resources/views/livewire/public/news/index.blade.php`
- `resources/views/livewire/public/documents/index.blade.php`

**Resultado esperado**:
- Resultados mostrados correctamente agrupados
- Cards de resultados consistentes con el resto de la aplicación

---

#### Paso 3.4: Mejorar UX y Diseño

**Objetivo**: Mejorar la experiencia de usuario y el diseño visual.

**Tareas**:
1. Añadir estados de carga:
   - Spinner mientras se busca
   - Usar `wire:loading` de Livewire

2. Añadir indicadores visuales:
   - Badge con total de resultados
   - Iconos por tipo de contenido
   - Highlight del término buscado (opcional)

3. Mejorar responsive:
   - Ajustar layout para móviles
   - Optimizar cards para pantallas pequeñas

4. Añadir breadcrumbs:
   - Breadcrumb: Inicio > Búsqueda

**Archivos a modificar**:
- `resources/views/livewire/search/global-search.blade.php`

**Resultado esperado**:
- Interfaz pulida y responsive
- Mejor experiencia de usuario

---

### **Fase 4: Integración y Rutas**

#### Paso 4.1: Crear Ruta Pública

**Objetivo**: Añadir la ruta para la búsqueda global.

**Tareas**:
1. Añadir ruta en `routes/web.php`:
   ```php
   Route::get('/buscar', Search\GlobalSearch::class)->name('search');
   ```

2. Verificar que la ruta funcione correctamente
3. Añadir comentarios descriptivos

**Archivos a modificar**:
- `routes/web.php`

**Resultado esperado**:
- Ruta creada y funcionando
- Accesible en `/buscar`

---

#### Paso 4.2: Integrar en Navegación Pública

**Objetivo**: Añadir enlace a búsqueda global en la navegación.

**Tareas**:
1. Revisar componente de navegación pública:
   - `resources/views/components/nav/public-nav.blade.php`

2. Añadir enlace a búsqueda:
   - Icono de búsqueda
   - Texto "Buscar"
   - Enlace a `route('search')`

3. Considerar posición:
   - En el menú principal
   - O como botón destacado

**Archivos a modificar**:
- `resources/views/components/nav/public-nav.blade.php`

**Resultado esperado**:
- Enlace a búsqueda visible en navegación
- Acceso fácil desde cualquier página pública

---

### **Fase 5: Traducciones**

#### Paso 5.1: Añadir Traducciones

**Objetivo**: Añadir todas las traducciones necesarias.

**Tareas**:
1. Revisar archivos de traducción:
   - `lang/es/common.php`
   - `lang/en/common.php`

2. Añadir traducciones para:
   - Título de página: "Búsqueda Global"
   - Placeholder de búsqueda
   - Labels de filtros
   - Títulos de secciones de resultados
   - Mensajes de estado vacío
   - Botones (Limpiar, Ver más, etc.)

3. Organizar en sección `search`:
   ```php
   'search' => [
       'title' => 'Búsqueda Global',
       'placeholder' => 'Buscar en programas, convocatorias, noticias...',
       'filters' => [...],
       'results' => [...],
       'empty' => [...],
   ]
   ```

**Archivos a modificar**:
- `lang/es/common.php`
- `lang/en/common.php`

**Resultado esperado**:
- Todas las traducciones añadidas
- Textos en español e inglés

---

### **Fase 6: Tests**

#### Paso 6.1: Crear Tests Básicos

**Objetivo**: Crear tests para funcionalidad básica.

**Tareas**:
1. Crear archivo de test:
   - `tests/Feature/Search/GlobalSearchTest.php`

2. Implementar tests básicos:
   - Test de renderizado del componente
   - Test de búsqueda básica
   - Test de resultados por tipo
   - Test de filtros

**Archivos a crear**:
- `tests/Feature/Search/GlobalSearchTest.php`

**Resultado esperado**:
- Tests básicos creados y pasando

---

#### Paso 6.2: Crear Tests Avanzados

**Objetivo**: Añadir tests para casos edge y funcionalidades avanzadas.

**Tareas**:
1. Tests adicionales:
   - Test de búsqueda vacía
   - Test de búsqueda sin resultados
   - Test de filtros combinados
   - Test de límite de resultados
   - Test de paginación (si se implementa)
   - Test de reset de filtros

2. Tests de integración:
   - Test de ruta
   - Test de navegación

**Archivos a modificar**:
- `tests/Feature/Search/GlobalSearchTest.php`

**Resultado esperado**:
- Cobertura completa de tests
- Todos los tests pasando

---

### **Fase 7: Optimizaciones y Mejoras (Opcional)**

#### Paso 7.1: Historial de Búsquedas (Opcional)

**Objetivo**: Implementar historial de búsquedas recientes.

**Tareas**:
1. Decidir almacenamiento:
   - Session (solo para usuario actual)
   - Base de datos (para usuarios autenticados)
   - LocalStorage (frontend)

2. Implementar funcionalidad:
   - Guardar búsquedas recientes
   - Mostrar historial en dropdown
   - Permitir seleccionar búsqueda anterior

3. Considerar privacidad:
   - No guardar búsquedas sensibles
   - Permitir limpiar historial

**Archivos a crear/modificar**:
- `app/Livewire/Search/GlobalSearch.php` (añadir métodos)
- `resources/views/livewire/search/global-search.blade.php` (añadir UI)

**Resultado esperado**:
- Historial de búsquedas funcional (si se implementa)

---

#### Paso 7.2: Búsqueda con Highlight

**Objetivo**: Resaltar términos buscados en resultados.

**Tareas**:
1. Implementar highlight:
   - Función helper para resaltar texto
   - Aplicar en títulos y descripciones
   - Usar `<mark>` tag con estilos

2. Considerar seguridad:
   - Escapar HTML correctamente
   - Prevenir XSS

**Archivos a crear/modificar**:
- Helper function o método en componente
- `resources/views/livewire/search/global-search.blade.php`

**Resultado esperado**:
- Términos buscados resaltados en resultados

---

## Consideraciones Técnicas

### Rendimiento

1. **Límite de Resultados**:
   - Limitar resultados por tipo (ej: 10 iniciales)
   - Implementar "Ver más" para cada tipo
   - Considerar paginación si hay muchos resultados

2. **Optimización de Consultas**:
   - Usar índices de BD en campos de búsqueda
   - Eager loading para relaciones
   - Evitar N+1 queries

3. **Debounce**:
   - Usar `wire:model.live.debounce.300ms` para evitar búsquedas excesivas
   - Ajustar tiempo según necesidad

### Seguridad

1. **Validación**:
   - Validar parámetros de búsqueda
   - Sanitizar input del usuario
   - Prevenir SQL injection (usar Eloquent, no raw queries)

2. **Autorización**:
   - Solo mostrar contenido público
   - Respetar filtros de publicación/activo

### Accesibilidad

1. **ARIA Labels**:
   - Añadir labels apropiados
   - Indicar estado de búsqueda

2. **Navegación por Teclado**:
   - Asegurar que todos los elementos sean accesibles
   - Orden lógico de tabulación

### Responsive

1. **Móviles**:
   - Filtros colapsables
   - Cards optimizadas para pantallas pequeñas
   - Búsqueda fácil de usar en touch

2. **Tabletas y Desktop**:
   - Layout de dos columnas si es apropiado
   - Filtros siempre visibles en desktop

---

## Estructura de Archivos

```
app/
  Livewire/
    Search/
      GlobalSearch.php          # Componente principal

resources/
  views/
    livewire/
      search/
        global-search.blade.php  # Vista del componente

routes/
  web.php                        # Ruta /buscar

lang/
  es/
    common.php                   # Traducciones ES
  en/
    common.php                   # Traducciones EN

tests/
  Feature/
    Search/
      GlobalSearchTest.php       # Tests del componente
```

---

## Checklist de Implementación

### Fase 1: Análisis y Diseño
- [ ] Paso 1.1: Definir estructura del componente
- [ ] Paso 1.2: Diseñar lógica de búsqueda

### Fase 2: Implementación del Componente
- [ ] Paso 2.1: Crear componente base
- [ ] Paso 2.2: Implementar búsqueda por entidades
- [ ] Paso 2.3: Implementar filtros avanzados
- [ ] Paso 2.4: Optimizar consultas y rendimiento

### Fase 3: Implementación de la Vista
- [ ] Paso 3.1: Crear vista base
- [ ] Paso 3.2: Implementar campo de búsqueda y filtros
- [ ] Paso 3.3: Implementar visualización de resultados
- [ ] Paso 3.4: Mejorar UX y diseño

### Fase 4: Integración y Rutas
- [ ] Paso 4.1: Crear ruta pública
- [ ] Paso 4.2: Integrar en navegación pública

### Fase 5: Traducciones
- [ ] Paso 5.1: Añadir traducciones

### Fase 6: Tests
- [ ] Paso 6.1: Crear tests básicos
- [ ] Paso 6.2: Crear tests avanzados

### Fase 7: Optimizaciones (Opcional)
- [ ] Paso 7.1: Historial de búsquedas (opcional)
- [ ] Paso 7.2: Búsqueda con highlight (opcional)

---

## Próximos Pasos

Una vez completado este plan, el siguiente paso sería:

1. **Revisar y aprobar el plan** antes de comenzar la implementación
2. **Comenzar con Fase 1** - Análisis y Diseño
3. **Implementar iterativamente** - Completar cada fase antes de pasar a la siguiente
4. **Testing continuo** - Ejecutar tests después de cada fase
5. **Revisión final** - Verificar que todo funciona correctamente antes de marcar como completado

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan detallado completado - Pendiente de aprobación para comenzar implementación
