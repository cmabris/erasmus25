# Paso 47: Implementación del Paso 3.9.3 - Optimización de Imágenes

**Fecha**: Enero 2026  
**Objetivo**: Implementar optimización de imágenes usando Laravel Media Library con conversiones WebP

---

## Resumen de Resultados

### Logros Principales
- ✅ Configuración de Media Library con WebP por defecto
- ✅ 4 modelos actualizados con conversiones optimizadas
- ✅ Componente `responsive-image.blade.php` creado
- ✅ Vistas públicas optimizadas
- ✅ 38 medios regenerados
- ✅ Corrección de tests preexistentes fallando
- ✅ **Tests finales: 3,830 pasando (8,673 assertions)**

### Archivos Creados
- `config/media-library.php`
- `resources/views/components/ui/responsive-image.blade.php`
- `tests/Feature/Components/ResponsiveImageTest.php`

### Archivos Modificados
- `app/Models/Program.php`
- `app/Models/NewsPost.php`
- `app/Models/ErasmusEvent.php`
- `app/Models/Document.php`
- `app/Livewire/Public/News/Show.php`
- `app/Livewire/Admin/NewsTags/Show.php`
- `app/Livewire/Admin/AcademicYears/Show.php`
- `resources/views/livewire/public/news/index.blade.php`
- `resources/views/livewire/public/news/show.blade.php`
- `resources/views/components/content/news-card.blade.php`
- `tests/Feature/Exports/CallsExportTest.php`
- `tests/Feature/Exports/ResolutionsExportTest.php`
- `tests/Feature/Exports/AuditLogsExportTest.php`

---

## Prompts y Resultados

### Prompt 1: Solicitud de Plan de Trabajo

**Usuario:**
> En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que hace de índice y otro llamado @planificacion_pasos.md que contiene las tareas a desarrollar en orden. Estamos en situación de continuar con el desarrollo del Paso 3.9. el cual se compone de varios pasos parciales. Comenzamos con el desarrollo del paso 3.9.3. que corresponde a la optimización de imágenes. Antes de comenzar el paso 3.9.3. quiero que revises los archivos necesarios para la tarea y cuya documentación está en la carpeta docs y me generes un plan de trabajo.

**Resultado:**
- Revisión completa del estado actual de Media Library
- Identificación de 5 modelos con soporte de medios
- Análisis de conversiones existentes y problemas
- Creación del plan de trabajo en `docs/pasos/paso-3.9.3-plan.md` con:
  - 6 fases de implementación
  - Checklist detallado
  - Métricas de éxito
  - Consideraciones de dependencias

---

### Prompt 2: Inicio de Fase 1

**Usuario:**
> Empecemos con la Fase 1

**Resultado:**
- Verificación de optimizadores del sistema (no instalados, opcional)
- Publicación de `config/media-library.php`
- Configuración personalizada:
  - `default_quality` => 85
  - `default_format` => 'webp'
  - `default_loading_attribute_value` => 'lazy'
- Actualización de 4 modelos con conversiones WebP:
  - **Program**: thumbnail, medium, large (WebP, 85%)
  - **NewsPost**: thumbnail, medium, large, hero (1920x1080) (WebP, 85%)
  - **ErasmusEvent**: thumbnail, medium, large (WebP, 85%)
  - **Document**: preview (WebP, 85%)
- Tests: 103 pasando (206 assertions)

---

### Prompt 3: Consulta sobre Optimizadores

**Usuario:**
> Si no instalo los optimizadores en local, cuando pruebe la aplicación en el navegador me dará error o simplemente no se realizarán las conversiones de imágenes?

**Resultado:**
- Explicación de que las conversiones WebP funcionan sin optimizadores externos
- GD/Imagick de PHP manejan las conversiones
- Los optimizadores son opcionales y añaden ~20% compresión adicional
- Sin errores en producción si faltan optimizadores

---

### Prompt 4: Inicio de Fase 2

**Usuario:**
> Sí, pasemos a la Fase 2

**Resultado:**
- Creación del componente `responsive-image.blade.php` con:
  - Soporte para objeto Media o URL directa
  - Elemento `<picture>` con fallback
  - Aspect ratios configurables
  - Object-fit configurable
  - Placeholder con icono personalizable
  - `loading="lazy"` y `decoding="async"`
- Creación de tests: 19 tests (26 assertions)
- Actualización de vistas públicas:
  - `news/index.blade.php`: Usa conversiones thumbnail/medium
  - `news/show.blade.php`: Usa conversión hero con eager loading
  - `news-card.blade.php`: Añadido `decoding="async"`
- Regeneración de conversiones: 38 medios procesados

---

### Prompt 5: Corrección de Tests Fallando

**Usuario:**
> Antes de dar por terminado todo, corrijamos los tests que están fallando, aunque el error sea previo a este desarrollo.

**Resultado:**
- Identificación de 41 tests fallando en exports
- Problema: Tests usaban `$export->collection()` pero las clases implementan `FromQuery`
- Corrección en 3 archivos de tests:
  - `CallsExportTest.php`: Cambiado a `$export->query()->get()`
  - `ResolutionsExportTest.php`: Cambiado a `$export->query()->get()`
  - `AuditLogsExportTest.php`: Cambiado a `$export->query()->get()`
- Identificación de 5 tests adicionales con errores de lazy loading N+1
- Corrección de eager loading en:
  - `NewsTags/Show.php`: Añadido `with(['author', 'program'])` a newsPosts
  - `AcademicYears/Show.php`: Añadido eager loading de relaciones anidadas
- **Tests finales: 3,830 pasando (8,673 assertions)**

---

## Configuraciones Implementadas

### config/media-library.php

```php
// Configuración personalizada añadida
'default_quality' => 85,
'default_format' => 'webp',
'default_loading_attribute_value' => 'lazy',
```

### Conversiones por Modelo

| Modelo | Conversión | Dimensiones | Formato | Calidad | Cola |
|--------|------------|-------------|---------|---------|------|
| Program | thumbnail | 300x300 | WebP | 85% | No |
| Program | medium | 800x600 | WebP | 85% | Sí |
| Program | large | 1200x900 | WebP | 85% | Sí |
| NewsPost | thumbnail | 300x300 | WebP | 85% | No |
| NewsPost | medium | 800x600 | WebP | 85% | Sí |
| NewsPost | large | 1200x900 | WebP | 85% | Sí |
| NewsPost | hero | 1920x1080 | WebP | 85% | Sí |
| ErasmusEvent | thumbnail | 300x300 | WebP | 85% | No |
| ErasmusEvent | medium | 800x600 | WebP | 85% | Sí |
| ErasmusEvent | large | 1200x900 | WebP | 85% | Sí |
| Document | preview | 200x200 | WebP | 85% | No |

---

## Componente responsive-image.blade.php

### Props Disponibles

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| media | Media\|null | null | Objeto Media de Spatie |
| src | string\|null | null | URL directa de imagen |
| alt | string | '' | Texto alternativo |
| conversion | string\|null | null | Nombre de conversión a usar |
| fallbackConversion | string\|null | null | Conversión de fallback |
| class | string | '' | Clases CSS del contenedor |
| imgClass | string | '' | Clases CSS de la imagen |
| aspectRatio | string\|null | null | Ratio de aspecto (16/9, 4/3, 1/1) |
| loading | string | 'lazy' | Comportamiento de carga |
| placeholder | bool | true | Mostrar placeholder si no hay imagen |
| placeholderIcon | string | 'photo' | Icono del placeholder |
| objectFit | string | 'cover' | Ajuste de imagen |

### Ejemplo de Uso

```blade
<x-ui.responsive-image 
    :media="$newsPost->getFirstMedia('featured')"
    conversion="medium"
    fallback-conversion="thumbnail"
    aspect-ratio="16/9"
    class="rounded-lg shadow"
/>
```

---

## Optimizaciones en Vistas

### news/index.blade.php

```php
// Antes
$featuredImage = $newsPost->getFirstMediaUrl('featured');

// Después
$isFeatured = $loop->first && $loop->iteration === 1 && $this->news->currentPage() === 1;
$featuredImage = $newsPost->getFirstMediaUrl('featured', $isFeatured ? 'medium' : 'thumbnail') 
    ?: $newsPost->getFirstMediaUrl('featured');
```

### news/show.blade.php (Hero Image)

```blade
<img 
    src="{{ $this->featuredImage }}" 
    alt="{{ $newsPost->title }}"
    class="h-full w-full object-cover"
    loading="eager"
    decoding="async"
    fetchpriority="high"
/>
```

### Show.php Computed Property

```php
#[Computed]
public function featuredImage(): ?string
{
    return $this->newsPost->getFirstMediaUrl('featured', 'hero')
        ?: $this->newsPost->getFirstMediaUrl('featured', 'large')
        ?: $this->newsPost->getFirstMediaUrl('featured');
}
```

---

## Correcciones de Bugs Preexistentes

### Export Tests (FromQuery vs FromCollection)

Los tests de exports usaban `$export->collection()` pero las clases implementan `FromQuery`, no `FromCollection`.

```php
// Antes (incorrecto)
$collection = $export->collection();

// Después (correcto)
$collection = $export->query()->get();
```

Archivos corregidos:
- `tests/Feature/Exports/CallsExportTest.php` (12 ocurrencias)
- `tests/Feature/Exports/ResolutionsExportTest.php` (12 ocurrencias)
- `tests/Feature/Exports/AuditLogsExportTest.php` (11 ocurrencias)

### Lazy Loading N+1 en Show Components

```php
// NewsTags/Show.php - Antes
$this->newsTag = $news_tag->load([
    'newsPosts' => fn ($query) => $query->latest()->limit(10),
])->loadCount(['newsPosts']);

// Después
$this->newsTag = $news_tag->load([
    'newsPosts' => fn ($query) => $query->with(['author', 'program'])->latest()->limit(10),
])->loadCount(['newsPosts']);
```

```php
// AcademicYears/Show.php - Antes
$this->academicYear = $academic_year->load([
    'calls' => fn ($query) => $query->latest()->limit(5),
    'newsPosts' => fn ($query) => $query->latest()->limit(5),
    'documents' => fn ($query) => $query->latest()->limit(5),
])->loadCount(['calls', 'newsPosts', 'documents']);

// Después
$this->academicYear = $academic_year->load([
    'calls' => fn ($query) => $query->with('program')->latest()->limit(5),
    'newsPosts' => fn ($query) => $query->with(['author', 'program'])->latest()->limit(5),
    'documents' => fn ($query) => $query->with(['category', 'program'])->latest()->limit(5),
])->loadCount(['calls', 'newsPosts', 'documents']);
```

---

## Comandos Ejecutados

```bash
# Publicar configuración de Media Library
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"

# Regenerar conversiones existentes
php artisan media-library:regenerate --only-missing

# Formatear código
vendor/bin/pint --dirty

# Ejecutar tests
php artisan test --parallel
```

---

## Métricas Finales

| Métrica | Valor |
|---------|-------|
| Tests totales | 3,830 |
| Assertions | 8,673 |
| Archivos creados | 3 |
| Archivos modificados | 13 |
| Modelos actualizados | 4 |
| Medios regenerados | 38 |
| Conversiones nuevas | 11 |
| Bugs corregidos | 46 |

---

## Beneficios Esperados

1. **Reducción de tamaño**: 40-70% menos peso en imágenes con WebP
2. **Mejor rendimiento**: Lazy loading y conversiones apropiadas por contexto
3. **Mejor UX**: Thumbnails generados inmediatamente (nonQueued)
4. **Compatibilidad**: Fallback a formatos originales si WebP no soportado
5. **Mantenibilidad**: Componente reutilizable para todas las imágenes

---

**Próximo paso sugerido**: Paso 3.9.4 (Paginación y Lazy Loading) o Paso 3.9.5 (SEO)
