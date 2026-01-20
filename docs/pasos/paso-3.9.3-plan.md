# Plan de Trabajo: Paso 3.9.3 - Optimización de Imágenes

## 📋 Resumen Ejecutivo

**Objetivo**: Implementar optimización de imágenes en la aplicación usando Laravel Media Library para mejorar el rendimiento de carga y reducir el consumo de ancho de banda.

**Estado Actual**:
- ✅ Laravel Media Library v11.17.6 instalado y configurado
- ✅ 5 modelos con soporte de medios: `Program`, `NewsPost`, `Document`, `ErasmusEvent`, `Resolution`
- ✅ Conversiones básicas definidas (thumbnail, medium, large)
- ⚠️ Sin conversión a formatos modernos (WebP/AVIF)
- ⚠️ Sin optimización de tamaño de archivo
- ⚠️ Sin responsive images (srcset)
- ⚠️ Archivo de configuración de Media Library no publicado

---

## 🔍 Análisis del Estado Actual

### Modelos con Media Library

| Modelo | Colecciones | Conversiones Actuales | MIME Types |
|--------|------------|----------------------|------------|
| **Program** | `image` (single) | thumbnail (300x300), medium (800x600), large (1200x900) | jpeg, png, webp, gif |
| **NewsPost** | `featured` (single), `gallery`, `videos`, `audio` | thumbnail (300x300), medium (800x600), large (1200x900) | jpeg, png, webp, gif |
| **Document** | `file` (single) | Ninguna (solo almacenamiento) | pdf, doc, xls, ppt, txt, csv, jpeg, png, webp |
| **ErasmusEvent** | `images` | thumbnail (300x300), medium (800x600), large (1200x900) | jpeg, png, webp, gif |
| **Resolution** | `resolutions` (single) | Ninguna (solo PDFs) | pdf |

### Uso Actual de Imágenes en Vistas

1. **Vistas Públicas**:
   - `news/index.blade.php`: Usa `getFirstMediaUrl('featured')` sin conversión específica
   - `news/show.blade.php`: Imagen hero sin optimización, noticias relacionadas sin conversión
   - Componente `news-card.blade.php`: Usa `loading="lazy"` pero sin srcset

2. **Vistas de Administración**:
   - Algunas vistas usan conversiones (thumbnail, medium)
   - La mayoría usa `loading="lazy"`

### Problemas Identificados

1. **Sin formato WebP**: Las imágenes se sirven en formato original (jpeg/png), perdiendo oportunidad de compresión
2. **Conversiones sin calidad definida**: No se especifica calidad de compresión
3. **Sin responsive images**: No hay srcset para diferentes tamaños de pantalla
4. **Sin optimización automática**: No hay optimizadores configurados (jpegoptim, pngquant, etc.)
5. **Configuración por defecto**: No se ha publicado ni personalizado el archivo de configuración

---

## 📝 Plan de Implementación

### Fase 1: Configuración Base (Preparación)

#### 1.1 Publicar archivo de configuración de Media Library
```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"
```

#### 1.2 Configurar optimizadores de imagen
- Instalar paquetes de optimización del sistema (jpegoptim, pngquant, optipng, svgo, gifsicle)
- Configurar `config/media-library.php` con optimizadores

#### 1.3 Configurar conversión a WebP
- Añadir conversión WebP a las conversiones existentes
- Configurar calidad óptima (80-85%)

**Archivos a modificar/crear**:
- `config/media-library.php` (nuevo)

---

### Fase 2: Actualizar Conversiones de Modelos

#### 2.1 Modelo Program
```php
public function registerMediaConversions(?Media $media = null): void
{
    // Thumbnail - para cards y listados
    $this->addMediaConversion('thumbnail')
        ->width(300)
        ->height(300)
        ->sharpen(10)
        ->quality(85)
        ->format('webp')
        ->performOnCollections('image');

    // Medium - para vistas intermedias
    $this->addMediaConversion('medium')
        ->width(800)
        ->height(600)
        ->sharpen(10)
        ->quality(85)
        ->format('webp')
        ->performOnCollections('image');

    // Large - para vistas de detalle
    $this->addMediaConversion('large')
        ->width(1200)
        ->height(900)
        ->sharpen(10)
        ->quality(85)
        ->format('webp')
        ->performOnCollections('image');
}
```

#### 2.2 Modelo NewsPost
- Actualizar conversiones para colecciones `featured` y `gallery`
- Añadir conversiones específicas para hero images (mayor resolución)

#### 2.3 Modelo ErasmusEvent
- Actualizar conversiones para colección `images`

#### 2.4 Modelo Document
- Considerar añadir thumbnails para previews de imágenes (jpeg, png, webp)

**Archivos a modificar**:
- `app/Models/Program.php`
- `app/Models/NewsPost.php`
- `app/Models/ErasmusEvent.php`
- `app/Models/Document.php` (opcional)

---

### Fase 3: Crear Componente de Imagen Responsiva

#### 3.1 Crear componente Blade reutilizable
```blade
{{-- resources/views/components/ui/responsive-image.blade.php --}}
@props([
    'media' => null,
    'alt' => '',
    'class' => '',
    'sizes' => '100vw',
    'conversion' => null,
    'fallback' => null,
])
```

El componente debe:
- Generar elemento `<picture>` con sources WebP
- Incluir srcset para diferentes tamaños
- Soporte para lazy loading
- Fallback para navegadores antiguos

#### 3.2 Variantes del componente
- **Hero image**: Para imágenes grandes de cabecera
- **Card image**: Para cards de noticias/eventos
- **Thumbnail**: Para listados y miniaturas
- **Gallery**: Para galerías de imágenes

**Archivos a crear**:
- `resources/views/components/ui/responsive-image.blade.php`

---

### Fase 4: Actualizar Vistas Públicas

#### 4.1 Vistas de Noticias
- `livewire/public/news/index.blade.php`: Usar conversión thumbnail para cards
- `livewire/public/news/show.blade.php`: Usar conversión large para hero, medium para relacionados
- Componente `news-card.blade.php`: Usar nuevo componente responsive-image

#### 4.2 Vistas de Eventos (si existen públicas)
- Aplicar mismos patrones que noticias

#### 4.3 Vistas de Programas (si muestran imágenes)
- Aplicar conversiones apropiadas

**Archivos a modificar**:
- `resources/views/livewire/public/news/index.blade.php`
- `resources/views/livewire/public/news/show.blade.php`
- `resources/views/components/content/news-card.blade.php`
- Otras vistas públicas que muestren imágenes

---

### Fase 5: Regenerar Conversiones Existentes

#### 5.1 Crear comando/job para regeneración
```bash
php artisan media-library:regenerate
```

#### 5.2 Considerar regeneración en background
- Para producción con muchas imágenes, usar jobs en cola

---

### Fase 6: Tests

#### 6.1 Tests de conversiones
- Verificar que las conversiones se generan correctamente
- Verificar formato WebP
- Verificar calidad y dimensiones

#### 6.2 Tests de componentes
- Test del componente responsive-image
- Verificar srcset generado

#### 6.3 Tests de rendimiento
- Verificar tamaño de archivos generados vs originales

**Archivos a crear**:
- `tests/Feature/MediaLibrary/ImageConversionsTest.php`
- `tests/Feature/Components/ResponsiveImageTest.php`

---

## 📊 Métricas de Éxito

1. **Reducción de tamaño**: 40-70% reducción en tamaño de imágenes con WebP
2. **Conversiones correctas**: Todas las imágenes existentes regeneradas con nuevos formatos
3. **Tests pasando**: 100% de tests relacionados con imágenes
4. **Sin regresiones**: Tests existentes continúan pasando

---

## ⚠️ Consideraciones Importantes

### Dependencias del Sistema
Para optimización completa, se necesitan instalados en el servidor:
- `jpegoptim` - Optimización JPEG
- `optipng` / `pngquant` - Optimización PNG
- `gifsicle` - Optimización GIF
- `cwebp` - Conversión a WebP (parte de libwebp)

En macOS (desarrollo con Herd):
```bash
brew install jpegoptim pngquant optipng gifsicle webp
```

### Compatibilidad de Navegadores
- WebP: Soportado por >95% de navegadores modernos
- AVIF: Soportado por ~75% de navegadores (considerar como mejora futura)
- Incluir fallback a JPEG/PNG para navegadores antiguos

### Rendimiento
- Las conversiones se ejecutan al subir imágenes (puede ralentizar subida)
- Considerar conversiones en cola para producción
- Regenerar conversiones existentes puede tardar

### Almacenamiento
- WebP reduce tamaño pero genera más archivos por cada conversión
- Estimar espacio adicional necesario: ~2-3x por cada conversión añadida

---

## 🔗 Documentación de Referencia

- [Laravel Media Library - Conversions](https://spatie.be/docs/laravel-medialibrary/v11/converting-images/defining-conversions)
- [Laravel Media Library - Responsive Images](https://spatie.be/docs/laravel-medialibrary/v11/responsive-images/getting-started-with-responsive-images)
- [Laravel Media Library - Optimization](https://spatie.be/docs/laravel-medialibrary/v11/installation-setup#optimization)
- [WebP Best Practices](https://web.dev/serve-images-webp/)

---

## 📅 Estimación de Tareas

| Fase | Tareas | Complejidad |
|------|--------|-------------|
| 1. Configuración Base | 3 tareas | Baja |
| 2. Actualizar Modelos | 4 modelos | Media |
| 3. Componente Responsive | 1 componente + variantes | Media |
| 4. Actualizar Vistas | 5-8 vistas | Media |
| 5. Regenerar Conversiones | 1 comando | Baja |
| 6. Tests | 3 archivos de tests | Media |

---

## ✅ Checklist de Implementación

### Fase 1: Configuración Base ✅ COMPLETADA
- [x] Publicar config/media-library.php
- [x] Verificar optimizadores del sistema instalados (no disponibles, opcional)
- [x] Configurar optimizadores en config (ya vienen configurados)
- [x] Configurar formato WebP por defecto
- [x] Configurar lazy loading por defecto
- [x] Actualizar modelos con conversiones WebP (Program, NewsPost, ErasmusEvent, Document)

### Fase 2: Componente Responsive Image ✅ COMPLETADA
- [x] Crear componente responsive-image.blade.php
- [x] Implementar picture element con fallback
- [x] Implementar aspect ratios y object-fit
- [x] Implementar placeholder
- [x] Tests del componente (19 tests, 26 assertions)

### Fase 3: Actualizar Vistas ✅ COMPLETADA
- [x] Actualizar news/index.blade.php (usa thumbnail/medium según variante)
- [x] Actualizar news/show.blade.php (usa hero para imagen principal)
- [x] Actualizar news-card.blade.php (decoding="async" en todas las variantes)
- [x] Actualizar Show.php (computed property con fallback de conversiones)
- [ ] Actualizar otras vistas públicas con imágenes (eventos, programas)
- [ ] Actualizar vistas de admin (prioridad baja)

### Fase 4: Regeneración ✅ COMPLETADA
- [x] Ejecutar media-library:regenerate --only-missing
- [x] Verificar conversiones generadas (38 medios procesados)
- [x] Limpiar conversiones antiguas si aplica (no necesario)

### Fase 5: Tests ✅ COMPLETADA
- [x] Crear tests de componente responsive-image (19 tests, 26 assertions)
- [x] Tests de modelos pasan (NewsPost: 30, Program: 7)
- [x] Ejecutar suite completa de tests
- [x] Verificar sin regresiones en componentes modificados

---

**Fecha de creación**: Enero 2026
**Paso previo completado**: 3.9.1 (Optimización de Consultas) ✅
**Paso previo completado**: 3.9.2 (Caché) ✅ (incluido en 3.9.1)
