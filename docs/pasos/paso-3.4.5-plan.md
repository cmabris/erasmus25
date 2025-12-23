# Plan de Desarrollo: Paso 3.4.5 - Listado y Detalle de Documentos

Este documento describe el plan detallado para desarrollar el paso 3.4.5 del proyecto, que corresponde al **Listado y Detalle de Documentos** en el área pública.

---

## Objetivo

Implementar las vistas públicas de listado y detalle de documentos, siguiendo el mismo patrón y estilo visual de las vistas ya desarrolladas (Programas, Convocatorias, Noticias), con filtros avanzados, búsqueda, descarga de archivos mediante Laravel Media Library, y visualización de información de consentimiento cuando aplique.

---

## Requisitos del Paso 3.4.5

Según `planificacion_pasos.md`:

- [ ] Crear componente Livewire `Documents\Index` para listado público
- [ ] Crear componente Livewire `Documents\Show` para detalle público
- [ ] Filtros por categoría, programa, año académico
- [ ] Búsqueda de documentos
- [ ] Descarga de archivos (Laravel Media Library)
- [ ] Mostrar información de consentimiento si aplica

---

## Pasos Detallados de Desarrollo

### **Paso 1: Preparar el Modelo Document para Media Library**

**Objetivo**: Implementar Laravel Media Library en el modelo Document para gestionar archivos.

**Tareas**:
1. Agregar el trait `InteractsWithMedia` al modelo `Document`
2. Implementar la interfaz `HasMedia`
3. Registrar colección de medios `file` para documentos
4. Configurar conversiones si es necesario (thumbnails para PDFs)
5. Agregar métodos helper para obtener URLs de descarga

**Archivos a modificar**:
- `app/Models/Document.php`

**Resultado esperado**:
- El modelo Document puede almacenar y gestionar archivos mediante Media Library
- Métodos disponibles: `getFirstMediaUrl('file')`, `getFirstMediaPath('file')`, etc.

---

### **Paso 2: Crear Componente UI Document Card**

**Objetivo**: Crear un componente reutilizable para mostrar documentos en formato card, similar a `news-card` y `call-card`.

**Tareas**:
1. Crear componente `resources/views/components/content/document-card.blade.php`
2. Implementar variantes: `default`, `compact`, `featured`, `horizontal`
3. Mostrar información relevante:
   - Título del documento
   - Categoría (badge)
   - Tipo de documento (badge)
   - Programa (si aplica)
   - Año académico (si aplica)
   - Descripción (truncada)
   - Icono según tipo de archivo
   - Contador de descargas
   - Fecha de creación/actualización
4. Incluir enlace al detalle del documento
5. Soporte para dark mode
6. Diseño responsive

**Props del componente**:
```php
@props([
    'document' => null,
    'title' => null,
    'slug' => null,
    'description' => null,
    'category' => null,
    'program' => null,
    'academicYear' => null,
    'documentType' => null,
    'downloadCount' => 0,
    'createdAt' => null,
    'href' => null,
    'variant' => 'default', // default, compact, featured, horizontal
    'showCategory' => true,
    'showProgram' => true,
    'showDownloadCount' => true,
])
```

**Archivos a crear**:
- `resources/views/components/content/document-card.blade.php`

**Resultado esperado**:
- Componente reutilizable para mostrar documentos en diferentes contextos
- Consistente con el diseño de otros cards (news-card, call-card)

---

### **Paso 3: Crear Componente Livewire Documents\Index**

**Objetivo**: Implementar el listado público de documentos con filtros y búsqueda.

**Tareas**:
1. Crear `app/Livewire/Public/Documents/Index.php`
2. Implementar propiedades públicas con URL binding:
   - `search` (búsqueda)
   - `category` (filtro por categoría)
   - `program` (filtro por programa)
   - `academicYear` (filtro por año académico)
   - `documentType` (filtro por tipo de documento)
3. Implementar computed properties:
   - `availableCategories()` - Categorías disponibles para filtro
   - `availablePrograms()` - Programas activos para filtro
   - `availableAcademicYears()` - Años académicos para filtro
   - `availableDocumentTypes()` - Tipos de documento disponibles
   - `stats()` - Estadísticas (total, por categoría, descargas totales)
   - `documents()` - Documentos paginados y filtrados (12 por página)
4. Implementar métodos:
   - `resetFilters()` - Limpiar todos los filtros
   - `updatedSearch/Category/Program/AcademicYear/DocumentType()` - Reset de paginación
5. Filtros a implementar:
   - Búsqueda: título, descripción
   - Categoría: select con categorías disponibles
   - Programa: select con programas activos
   - Año académico: select con años disponibles
   - Tipo de documento: select con tipos disponibles
6. Solo mostrar documentos con `is_active = true`
7. Ordenar por fecha de creación descendente (más recientes primero)
8. Eager loading: `category`, `program`, `academicYear`, `creator`

**Vista `resources/views/livewire/public/documents/index.blade.php`**:
1. Hero section con gradiente Erasmus+ y estadísticas
2. Barra de filtros (búsqueda, categoría, programa, año, tipo)
3. Badges de filtros activos con opción de eliminar
4. Grid responsive de documentos (3 columnas en desktop, 2 en tablet, 1 en móvil)
5. Paginación automática (12 por página)
6. Empty state cuando no hay resultados
7. CTA final

**Archivos a crear**:
- `app/Livewire/Public/Documents/Index.php`
- `resources/views/livewire/public/documents/index.blade.php`

**Resultado esperado**:
- Listado funcional de documentos con filtros avanzados
- Búsqueda en tiempo real
- Diseño consistente con otras vistas públicas

---

### **Paso 4: Crear Componente Livewire Documents\Show**

**Objetivo**: Implementar la vista de detalle público de un documento.

**Tareas**:
1. Crear `app/Livewire/Public/Documents/Show.php`
2. Propiedad pública: `public Document $document`
3. Validación en `mount()`:
   - Solo mostrar documentos con `is_active = true`
   - Retornar 404 si no cumple condiciones
4. Implementar computed properties:
   - `fileUrl()` - URL del archivo para descarga (Media Library)
   - `fileSize()` - Tamaño del archivo formateado
   - `fileMimeType()` - Tipo MIME del archivo
   - `fileExtension()` - Extensión del archivo
   - `hasMediaConsent()` - Verificar si tiene consentimientos asociados
   - `mediaConsents()` - Consentimientos de medios asociados (si aplica)
   - `relatedDocuments()` - Documentos relacionados (misma categoría o programa, límite 3)
   - `relatedCalls()` - Convocatorias relacionadas (mismo programa, límite 3)
5. Método `download()`:
   - Incrementar contador de descargas
   - Retornar respuesta de descarga del archivo
   - Registrar en audit log (opcional)

**Vista `resources/views/livewire/public/documents/show.blade.php`**:
1. Hero section con gradiente Erasmus+ o icono según tipo de documento
2. Breadcrumbs
3. Badges (categoría, programa, año académico, tipo de documento)
4. Meta información (fecha creación, creador, contador de descargas)
5. Descripción completa
6. Información del archivo:
   - Nombre del archivo
   - Tamaño
   - Tipo MIME
   - Botón de descarga destacado
7. Información de consentimiento (si aplica):
   - Mostrar aviso si requiere consentimiento
   - Lista de consentimientos asociados (si hay)
8. Documentos relacionados (si existen)
9. Convocatorias relacionadas (si existen y el documento tiene programa)
10. CTA final

**Archivos a crear**:
- `app/Livewire/Public/Documents/Show.php`
- `resources/views/livewire/public/documents/show.blade.php`

**Resultado esperado**:
- Vista de detalle completa con información del documento
- Descarga funcional de archivos
- Visualización de consentimientos cuando aplique

---

### **Paso 5: Crear Rutas Públicas**

**Objetivo**: Definir las rutas públicas para documentos.

**Tareas**:
1. Agregar rutas en `routes/web.php`:
   ```php
   // Rutas públicas de documentos
   Route::get('/documentos', Documents\Index::class)->name('documentos.index');
   Route::get('/documentos/{document:slug}', Documents\Show::class)->name('documentos.show');
   ```
2. Verificar que las rutas funcionen correctamente
3. Actualizar navegación pública si es necesario

**Archivos a modificar**:
- `routes/web.php`

**Resultado esperado**:
- Rutas públicas funcionando
- URLs amigables con slugs

---

### **Paso 6: Crear Seeder de Documentos con Datos de Prueba**

**Objetivo**: Crear un seeder con documentos realistas para desarrollo y pruebas.

**Tareas**:
1. Crear `database/seeders/DocumentsSeeder.php`
2. Generar documentos variados:
   - Diferentes categorías (Convocatorias, Modelos, Seguros, Consentimientos, Guías, FAQ, Otros)
   - Diferentes programas (Educación Escolar, FP, Educación Superior)
   - Diferentes años académicos
   - Diferentes tipos de documento
   - Algunos con archivos asociados (usar Media Library)
   - Varios estados (activos/inactivos, pero solo activos se mostrarán)
   - Contadores de descargas variados
3. Crear archivos de prueba en `storage/app/public/documents/`:
   - PDFs de ejemplo
   - Documentos Word
   - Otros formatos comunes
4. Asociar archivos a documentos usando Media Library
5. Crear algunos documentos con consentimientos asociados (opcional)
6. Generar al menos 30-50 documentos para tener datos suficientes

**Estructura del seeder**:
```php
class DocumentsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener categorías, programas, años académicos existentes
        // Crear documentos variados
        // Asociar archivos mediante Media Library
        // Crear algunos con consentimientos
    }
}
```

**Archivos a crear**:
- `database/seeders/DocumentsSeeder.php`
- Archivos de prueba en `storage/app/public/documents/` (opcional, pueden generarse dinámicamente)

**Archivos a modificar**:
- `database/seeders/DatabaseSeeder.php` (agregar llamada al seeder)

**Resultado esperado**:
- Seeder con documentos realistas
- Archivos asociados mediante Media Library
- Datos suficientes para probar todas las funcionalidades

---

### **Paso 7: Actualizar Navegación Pública**

**Objetivo**: Agregar enlace a documentos en la navegación pública.

**Tareas**:
1. Revisar componente de navegación pública
2. Agregar enlace "Documentos" en el menú
3. Verificar que el enlace se active cuando estemos en rutas de documentos

**Archivos a modificar**:
- `resources/views/components/nav/public-nav.blade.php` (o donde esté la navegación)

**Resultado esperado**:
- Enlace visible en la navegación
- Estado activo cuando corresponde

---

### **Paso 8: Crear Tests**

**Objetivo**: Crear tests para verificar el funcionamiento de los componentes.

**Tareas**:
1. Crear `tests/Feature/Livewire/Public/Documents/IndexTest.php`:
   - Test de renderizado del componente
   - Test de filtros (categoría, programa, año, tipo)
   - Test de búsqueda
   - Test de paginación
   - Test de empty state
   - Test de estadísticas
2. Crear `tests/Feature/Livewire/Public/Documents/ShowTest.php`:
   - Test de renderizado del componente
   - Test de validación (solo documentos activos)
   - Test de 404 para documentos inactivos
   - Test de descarga de archivos
   - Test de incremento de contador de descargas
   - Test de documentos relacionados
   - Test de consentimientos (si aplica)
3. Crear `tests/Feature/Routes/DocumentsRoutesTest.php`:
   - Test de rutas públicas
   - Test de URLs con slugs

**Archivos a crear**:
- `tests/Feature/Livewire/Public/Documents/IndexTest.php`
- `tests/Feature/Livewire/Public/Documents/ShowTest.php`
- `tests/Feature/Routes/DocumentsRoutesTest.php`

**Resultado esperado**:
- Tests completos con buena cobertura
- Verificación de todas las funcionalidades

---

### **Paso 9: Documentación**

**Objetivo**: Documentar los componentes creados.

**Tareas**:
1. Crear `docs/documents-components.md`:
   - Arquitectura general
   - Modelo Document y Media Library
   - Componentes Livewire
   - Componente Document Card
   - Rutas
   - Seeders
   - Guía de uso
   - Tests
2. Actualizar `docs/README.md` con referencia al nuevo documento

**Archivos a crear**:
- `docs/documents-components.md`

**Archivos a modificar**:
- `docs/README.md`

**Resultado esperado**:
- Documentación completa y actualizada

---

## Orden de Ejecución Recomendado

1. **Paso 1**: Preparar el Modelo Document para Media Library
2. **Paso 2**: Crear Componente UI Document Card
3. **Paso 3**: Crear Componente Livewire Documents\Index
4. **Paso 4**: Crear Componente Livewire Documents\Show
5. **Paso 5**: Crear Rutas Públicas
6. **Paso 6**: Crear Seeder de Documentos
7. **Paso 7**: Actualizar Navegación Pública
8. **Paso 8**: Crear Tests
9. **Paso 9**: Documentación

---

## Consideraciones Importantes

### Media Library
- Los archivos se almacenarán en la colección `file`
- Usar `getFirstMediaUrl('file')` para obtener la URL de descarga
- Considerar seguridad: verificar permisos antes de permitir descarga

### Consentimientos
- La tabla `media_consents` está relacionada con documentos mediante `consent_document_id`
- Mostrar información de consentimiento solo si el documento tiene consentimientos asociados
- Considerar mostrar un aviso si el documento requiere consentimiento para su uso

### Performance
- Usar eager loading para evitar consultas N+1
- Considerar caché para estadísticas si es necesario
- Paginación adecuada (12 documentos por página)

### Diseño
- Mantener consistencia con otras vistas públicas
- Usar componentes UI reutilizables (cards, badges, buttons, etc.)
- Diseño responsive (móvil, tablet, desktop)
- Soporte para dark mode

### Accesibilidad
- Etiquetas semánticas correctas
- Textos alternativos para iconos
- Navegación por teclado
- Contraste adecuado

---

## Checklist Final

- [ ] Modelo Document con Media Library implementado
- [ ] Componente Document Card creado y funcionando
- [ ] Componente Livewire Documents\Index creado y funcionando
- [ ] Componente Livewire Documents\Show creado y funcionando
- [ ] Rutas públicas definidas y funcionando
- [ ] Seeder de documentos creado con datos realistas
- [ ] Navegación pública actualizada
- [ ] Tests creados y pasando
- [ ] Documentación actualizada
- [ ] Diseño responsive verificado
- [ ] Dark mode funcionando
- [ ] Descarga de archivos funcionando
- [ ] Filtros funcionando correctamente
- [ ] Búsqueda funcionando correctamente
- [ ] Paginación funcionando correctamente
- [ ] Consentimientos mostrándose cuando aplica

---

## Notas Adicionales

- Reutilizar componentes existentes siempre que sea posible
- Seguir el mismo patrón de código que en News y Calls
- Mantener consistencia visual con el resto de la aplicación
- Los seeders son solo para desarrollo, no se usarán en producción
- Considerar agregar más funcionalidades en el futuro (previsualización de PDFs, etc.)

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan completado - Listo para comenzar implementación

