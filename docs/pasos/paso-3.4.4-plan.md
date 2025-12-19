# Plan de Desarrollo: Paso 3.4.4 - Listado y Detalle de Noticias

## Objetivo
Desarrollar las vistas públicas de listado y detalle de noticias siguiendo el estilo y estructura de las vistas de convocatorias ya implementadas.

## Análisis Previo

### Componentes Existentes a Reutilizar
- ✅ `x-content.news-card` - Componente de tarjeta de noticia (4 variantes: default, featured, horizontal, compact)
- ✅ `x-ui.card` - Componente base de tarjeta
- ✅ `x-ui.badge` - Componente de badge/etiqueta
- ✅ `x-ui.button` - Componente de botón
- ✅ `x-ui.section` - Contenedor de sección
- ✅ `x-ui.empty-state` - Estado vacío
- ✅ `x-ui.breadcrumbs` - Breadcrumbs
- ✅ `x-ui.search-input` - Input de búsqueda

### Modelo NewsPost
- **Campos principales**: title, slug, excerpt, content, country, city, host_entity
- **Relaciones**: program, academicYear, author, tags (many-to-many)
- **Estado**: status (borrador, publicado, archivado)
- **Publicación**: published_at (solo mostrar publicadas)
- **Multimedia**: Laravel Media Library (colección 'featured' para imagen destacada)

### Referencias
- Componente `Calls\Index` y `Calls\Show` como base de diseño
- Estructura de filtros y búsqueda similar a convocatorias

---

## Pasos de Desarrollo

### **Paso 1: Preparar el Modelo NewsPost para Media Library**
**Objetivo**: Asegurar que el modelo puede manejar imágenes destacadas.

**Tareas**:
1. Verificar si NewsPost tiene el trait `HasMedia` de Spatie Media Library
2. Si no lo tiene, añadirlo
3. Verificar configuración de colecciones de medios (featured, gallery)

**Archivos a modificar**:
- `app/Models/NewsPost.php`

**Criterios de éxito**:
- El modelo puede almacenar y recuperar imágenes destacadas
- Se puede acceder a la imagen destacada con `$newsPost->getFirstMediaUrl('featured')`

---

### **Paso 2: Crear Seeder de Noticias con Datos de Prueba**
**Objetivo**: Generar datos de prueba realistas para visualizar las vistas.

**Tareas**:
1. Crear `NewsTagSeeder` para generar etiquetas de ejemplo
2. Crear o actualizar `NewsPostSeeder` con noticias variadas:
   - Diferentes programas
   - Diferentes años académicos
   - Diferentes estados (solo publicadas para vista pública)
   - Con y sin imágenes destacadas
   - Con y sin etiquetas
   - Con diferentes ubicaciones (país, ciudad)
   - Con diferentes tipos y categorías de movilidad
3. Generar imágenes de prueba o usar placeholders
4. Asignar autores a las noticias

**Archivos a crear/modificar**:
- `database/seeders/NewsTagSeeder.php` (nuevo)
- `database/seeders/NewsPostSeeder.php` (crear o actualizar)
- `database/seeders/DatabaseSeeder.php` (añadir llamadas a seeders)

**Criterios de éxito**:
- Al ejecutar el seeder se crean al menos 20-30 noticias publicadas
- Las noticias tienen datos variados y realistas
- Algunas tienen imágenes destacadas
- Las noticias tienen etiquetas asignadas

---

### **Paso 3: Crear Componente Livewire News\Index**
**Objetivo**: Implementar el listado público de noticias con filtros y búsqueda.

**Tareas**:
1. Crear componente `app/Livewire/Public/News/Index.php`:
   - Propiedades para filtros (búsqueda, programa, año académico, etiquetas)
   - Propiedades con atributo `#[Url]` para mantener estado en URL
   - Computed properties para:
     - `news()` - Noticias paginadas y filtradas
     - `stats()` - Estadísticas (total, por programa, etc.)
     - `availablePrograms()` - Programas activos para filtro
     - `availableAcademicYears()` - Años académicos para filtro
     - `availableTags()` - Etiquetas disponibles para filtro
   - Métodos para resetear filtros
   - Métodos `updated*()` para resetear paginación al cambiar filtros
2. Implementar lógica de filtrado:
   - Solo mostrar noticias con `status = 'publicado'` y `published_at IS NOT NULL`
   - Búsqueda en título, excerpt, content
   - Filtros por programa, año académico, etiquetas
   - Ordenar por fecha de publicación (más recientes primero)
   - Paginación (12 por página)

**Archivos a crear**:
- `app/Livewire/Public/News/Index.php`

**Criterios de éxito**:
- El componente carga y muestra noticias publicadas
- Los filtros funcionan correctamente
- La búsqueda encuentra noticias por título y contenido
- La paginación funciona
- Los filtros se mantienen en la URL

---

### **Paso 4: Crear Vista Blade para News\Index**
**Objetivo**: Crear la interfaz visual del listado de noticias.

**Tareas**:
1. Crear `resources/views/livewire/public/news/index.blade.php`:
   - **Hero Section**: Similar a Calls\Index con gradiente Erasmus
     - Título y descripción
     - Estadísticas (total de noticias, por programa, etc.)
     - Breadcrumbs
   - **Filtros Section**: Barra de filtros
     - Input de búsqueda
     - Select de programa
     - Select de año académico
     - Select de etiquetas (múltiple o chips)
     - Botón para limpiar filtros
     - Badges de filtros activos con opción de eliminar
   - **News Grid Section**: Grid de noticias
     - Usar componente `x-content.news-card`
     - Grid responsive (1 columna móvil, 2 columnas tablet, 3 columnas desktop)
     - Primera noticia destacada (variante 'featured')
     - Resto con variante 'default'
     - Estado vacío cuando no hay resultados
   - **Pagination**: Paginación de Livewire
   - **CTA Section**: Sección de llamada a la acción

**Archivos a crear**:
- `resources/views/livewire/public/news/index.blade.php`

**Criterios de éxito**:
- La vista es responsive y se ve bien en todos los dispositivos
- Los filtros son intuitivos y fáciles de usar
- El diseño es consistente con Calls\Index
- Las noticias se muestran correctamente con imágenes
- El estado vacío se muestra cuando no hay resultados

---

### **Paso 5: Crear Componente Livewire News\Show**
**Objetivo**: Implementar la vista de detalle de una noticia.

**Tareas**:
1. Crear componente `app/Livewire/Public/News/Show.php`:
   - Propiedad pública `public NewsPost $newsPost`
   - Método `mount()` con validación:
     - Solo mostrar noticias con `status = 'publicado'` y `published_at IS NOT NULL`
     - Retornar 404 si no cumple condiciones
   - Computed properties para:
     - `featuredImage()` - Imagen destacada de Media Library
     - `relatedNews()` - Noticias relacionadas (mismo programa, diferentes tags, etc.)
     - `relatedCalls()` - Convocatorias relacionadas del mismo programa
   - Método `render()` con meta tags SEO

**Archivos a crear**:
- `app/Livewire/Public/News/Show.php`

**Criterios de éxito**:
- El componente carga la noticia correctamente
- Valida que la noticia esté publicada
- Retorna 404 para noticias no publicadas
- Las relaciones se cargan correctamente

---

### **Paso 6: Crear Vista Blade para News\Show**
**Objetivo**: Crear la interfaz visual del detalle de noticia.

**Tareas**:
1. Crear `resources/views/livewire/public/news/show.blade.php`:
   - **Hero Section**: 
     - Imagen destacada si existe (full width con overlay)
     - Título de la noticia
     - Badges: programa, año académico, etiquetas
     - Meta información: autor, fecha de publicación, ubicación
     - Breadcrumbs
   - **Content Section**: 
     - Contenido principal de la noticia (HTML)
     - Información adicional si existe:
       - Ubicación (país, ciudad)
       - Entidad de acogida
       - Tipo y categoría de movilidad
   - **Related Content Sections**:
     - Noticias relacionadas (grid de 3 columnas)
     - Convocatorias relacionadas (si aplica)
   - **CTA Section**: Llamada a la acción

**Archivos a crear**:
- `resources/views/livewire/public/news/show.blade.php`

**Criterios de éxito**:
- La vista muestra toda la información de la noticia
- La imagen destacada se muestra correctamente
- El contenido HTML se renderiza correctamente
- Las noticias relacionadas se muestran
- El diseño es responsive y moderno

---

### **Paso 7: Añadir Rutas**
**Objetivo**: Configurar las rutas públicas para noticias.

**Tareas**:
1. Añadir rutas en `routes/web.php`:
   - `GET /noticias` → `News\Index::class` (nombre: `noticias.index`)
   - `GET /noticias/{newsPost:slug}` → `News\Show::class` (nombre: `noticias.show`)

**Archivos a modificar**:
- `routes/web.php`

**Criterios de éxito**:
- Las rutas funcionan correctamente
- El binding por slug funciona
- Las URLs son amigables

---

### **Paso 8: Actualizar Componente news-card**
**Objetivo**: Asegurar que el componente news-card funciona correctamente con las rutas.

**Tareas**:
1. Actualizar `resources/views/components/content/news-card.blade.php`:
   - Cambiar el `href` para usar `route('noticias.show', $newsPost)` cuando esté disponible
   - Asegurar que se pasa la imagen destacada desde Media Library
   - Verificar que todos los datos se muestran correctamente

**Archivos a modificar**:
- `resources/views/components/content/news-card.blade.php`

**Criterios de éxito**:
- El componente genera enlaces correctos
- Las imágenes se muestran desde Media Library
- Todos los datos se renderizan correctamente

---

### **Paso 9: Crear Tests**
**Objetivo**: Asegurar que todo funciona correctamente con tests.

**Tareas**:
1. Crear `tests/Feature/Livewire/Public/News/IndexTest.php`:
   - Test de carga de página
   - Test de visualización de noticias publicadas
   - Test de no mostrar noticias no publicadas
   - Test de filtros (programa, año, etiquetas)
   - Test de búsqueda
   - Test de paginación
   - Test de estado vacío
2. Crear `tests/Feature/Livewire/Public/News/ShowTest.php`:
   - Test de carga de noticia publicada
   - Test de 404 para noticia no publicada
   - Test de visualización de contenido
   - Test de noticias relacionadas
   - Test de imagen destacada

**Archivos a crear**:
- `tests/Feature/Livewire/Public/News/IndexTest.php`
- `tests/Feature/Livewire/Public/News/ShowTest.php`

**Criterios de éxito**:
- Todos los tests pasan
- Cobertura de casos principales y edge cases

---

### **Paso 10: Verificación Final y Ajustes**
**Objetivo**: Verificar que todo funciona correctamente y hacer ajustes finales.

**Tareas**:
1. Ejecutar seeders y verificar visualmente:
   - El listado se ve bien con datos reales
   - El detalle muestra toda la información
   - Las imágenes se cargan correctamente
   - Los filtros funcionan
   - La búsqueda funciona
   - La paginación funciona
2. Verificar responsive design:
   - Móvil vertical
   - Móvil horizontal
   - Tablet
   - Desktop
3. Verificar dark mode
4. Ajustar estilos si es necesario
5. Ejecutar Pint para formatear código
6. Ejecutar tests finales

**Criterios de éxito**:
- Todo funciona correctamente
- El diseño es consistente con el resto de la aplicación
- No hay errores en consola
- El código está formateado correctamente

---

## Resumen de Archivos

### Archivos a Crear
1. `app/Livewire/Public/News/Index.php`
2. `app/Livewire/Public/News/Show.php`
3. `resources/views/livewire/public/news/index.blade.php`
4. `resources/views/livewire/public/news/show.blade.php`
5. `database/seeders/NewsTagSeeder.php`
6. `database/seeders/NewsPostSeeder.php` (o actualizar si existe)
7. `tests/Feature/Livewire/Public/News/IndexTest.php`
8. `tests/Feature/Livewire/Public/News/ShowTest.php`

### Archivos a Modificar
1. `app/Models/NewsPost.php` (añadir trait HasMedia si falta)
2. `routes/web.php` (añadir rutas)
3. `database/seeders/DatabaseSeeder.php` (añadir seeders)
4. `resources/views/components/content/news-card.blade.php` (actualizar rutas)

---

## Notas Importantes

1. **Solo mostrar noticias publicadas**: Las vistas públicas solo deben mostrar noticias con `status = 'publicado'` y `published_at IS NOT NULL`.

2. **Imágenes**: Usar Laravel Media Library para manejar imágenes destacadas. Si no hay imagen, mostrar un placeholder o icono.

3. **Consistencia**: Seguir el mismo patrón de diseño y estructura que `Calls\Index` y `Calls\Show`.

4. **Performance**: Usar eager loading para evitar N+1 queries (cargar program, academicYear, author, tags en las consultas).

5. **SEO**: Añadir meta tags apropiados en el método `render()` de los componentes Livewire.

6. **Accesibilidad**: Asegurar que todos los elementos sean accesibles (alt text en imágenes, labels en formularios, etc.).

---

## Orden de Ejecución Recomendado

1. Paso 1: Preparar modelo
2. Paso 2: Crear seeders (para tener datos de prueba)
3. Paso 3: Crear componente Index
4. Paso 4: Crear vista Index
5. Paso 5: Crear componente Show
6. Paso 6: Crear vista Show
7. Paso 7: Añadir rutas
8. Paso 8: Actualizar news-card
9. Paso 9: Crear tests
10. Paso 10: Verificación final

---

**Estado**: 📋 Plan creado - Listo para comenzar implementación
