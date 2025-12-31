# Plan de Trabajo: Mejoras en Gestión de Imágenes Destacadas

## Objetivo
Mejorar la gestión de imágenes destacadas en el CRUD de Noticias, incluyendo:
- Verificación y corrección de guardado de imágenes
- Generación automática de conversiones (thumbnail, medium, large)
- Visualización de imágenes en todas las vistas (Index, Show, Edit)
- Implementación de soft delete para imágenes (eliminar sin borrar archivo físico)
- Opción de restaurar imágenes eliminadas

---

## Fase 1: Diagnóstico y Verificación

### Paso 1.1: Verificar guardado de imágenes
**Objetivo**: Confirmar que las imágenes se están guardando correctamente.

**Tareas**:
- [ ] Verificar que `addMedia()` se está ejecutando correctamente en Create y Edit
- [ ] Verificar que el archivo físico se guarda en `storage/app/public/media`
- [ ] Verificar que el registro se crea en la tabla `media`
- [ ] Verificar que la relación `collection_name = 'featured'` es correcta

**Archivos a revisar**:
- `app/Livewire/Admin/News/Create.php` (método `store()`)
- `app/Livewire/Admin/News/Edit.php` (método `update()`)
- `storage/app/public/media/` (directorio de archivos)
- Tabla `media` en base de datos

**Comandos de verificación**:
```bash
# Verificar archivos guardados
ls -la storage/app/public/media/

# Verificar registros en base de datos
php artisan tinker
>>> \App\Models\NewsPost::find(22)->getFirstMedia('featured')
```

---

### Paso 1.2: Verificar generación de conversiones
**Objetivo**: Confirmar que las conversiones (thumbnail, medium, large) se generan automáticamente.

**Tareas**:
- [ ] Verificar que las conversiones se generan al guardar la imagen
- [ ] Verificar que las conversiones existen físicamente en el disco
- [ ] Verificar que `getFirstMediaUrl('featured', 'thumbnail')` retorna la URL correcta
- [ ] Si no se generan automáticamente, ejecutar comando para regenerar

**Archivos a revisar**:
- `app/Models/NewsPost.php` (método `registerMediaConversions()`)
- `storage/app/public/media/` (buscar carpetas de conversiones)

**Comandos de verificación**:
```bash
# Regenerar conversiones manualmente (si es necesario)
php artisan media-library:regenerate
```

**Nota**: Las conversiones se generan automáticamente al guardar, pero si las imágenes ya estaban guardadas antes de definir las conversiones, habrá que regenerarlas.

---

## Fase 2: Corrección de Visualización

### Paso 2.1: Corregir visualización en Index
**Objetivo**: Asegurar que las imágenes se muestran correctamente en el listado.

**Tareas**:
- [ ] Verificar que `getFirstMediaUrl('featured', 'thumbnail')` funciona correctamente
- [ ] Si no hay conversión thumbnail, usar la imagen original con tamaño reducido vía CSS
- [ ] Agregar fallback si la conversión no existe
- [ ] Verificar que las imágenes se cargan con eager loading si es necesario

**Archivos a modificar**:
- `resources/views/livewire/admin/news/index.blade.php` (línea ~209)

**Código sugerido**:
```php
@php
    $featuredImage = $newsPost->getFirstMediaUrl('featured', 'thumbnail') 
        ?: $newsPost->getFirstMediaUrl('featured');
@endphp
```

---

### Paso 2.2: Verificar visualización en Show
**Objetivo**: Confirmar que la imagen se muestra correctamente en la vista de detalle.

**Tareas**:
- [ ] Verificar que `hasFeaturedImage()` retorna `true` cuando hay imagen
- [ ] Verificar que `getFeaturedImageUrl('large')` retorna la URL correcta
- [ ] Verificar que la imagen se muestra con el tamaño correcto
- [ ] Verificar que se muestra información del archivo (nombre, tamaño)

**Archivos a revisar**:
- `app/Livewire/Admin/News/Show.php` (métodos `hasFeaturedImage()`, `getFeaturedImageUrl()`)
- `resources/views/livewire/admin/news/show.blade.php` (línea ~104)

**Nota**: La vista Show ya tiene código para mostrar la imagen, solo hay que verificar que funciona.

---

### Paso 2.3: Mejorar visualización en Edit
**Objetivo**: Mostrar la imagen actual en la vista de edición con opción de eliminarla.

**Tareas**:
- [ ] Verificar que `hasExistingFeaturedImage()` funciona correctamente
- [ ] Verificar que `featuredImageUrl` se carga correctamente en `mount()`
- [ ] Mejorar la presentación de la imagen actual (similar a Resolutions)
- [ ] Agregar botón para eliminar imagen actual
- [ ] Agregar confirmación antes de eliminar

**Archivos a modificar**:
- `resources/views/livewire/admin/news/edit.blade.php` (sección de imagen destacada, línea ~299)

**Nota**: Ya hay código para mostrar la imagen actual, pero puede necesitar mejoras visuales.

---

## Fase 3: Implementar Soft Delete para Media

### Paso 3.1: Agregar campo deleted_at a tabla media
**Objetivo**: Permitir soft deletes en la tabla `media` sin eliminar el archivo físico.

**Tareas**:
- [ ] Crear migración para agregar `deleted_at` a la tabla `media`
- [ ] Agregar índice para mejorar rendimiento de consultas
- [ ] Ejecutar migración

**Archivo a crear**:
- `database/migrations/YYYY_MM_DD_HHMMSS_add_soft_deletes_to_media_table.php`

**Código de migración**:
```php
Schema::table('media', function (Blueprint $table) {
    $table->softDeletes();
    $table->index('deleted_at', 'media_deleted_at_index');
});
```

---

### Paso 3.2: Crear modelo Media personalizado (opcional)
**Objetivo**: Extender el modelo Media de Spatie para usar SoftDeletes.

**Tareas**:
- [ ] Crear modelo `App\Models\Media` que extienda `Spatie\MediaLibrary\MediaCollections\Models\Media`
- [ ] Agregar trait `SoftDeletes`
- [ ] Configurar el modelo en `config/media-library.php` para usar nuestro modelo personalizado

**Archivo a crear**:
- `app/Models/Media.php`

**Alternativa más simple**: Usar `custom_properties` para marcar como eliminado sin modificar la tabla `media`.

---

### Paso 3.3: Implementar métodos de soft delete en NewsPost
**Objetivo**: Agregar métodos para eliminar/restaurar imágenes sin borrar el archivo físico.

**Tareas**:
- [ ] Crear método `softDeleteFeaturedImage()` en modelo NewsPost
- [ ] Crear método `restoreFeaturedImage()` en modelo NewsPost
- [ ] Crear método `forceDeleteFeaturedImage()` para eliminación permanente
- [ ] Modificar `clearMediaCollection()` para usar soft delete en lugar de eliminación física

**Archivos a modificar**:
- `app/Models/NewsPost.php`

**Métodos sugeridos**:
```php
public function softDeleteFeaturedImage(): void
{
    $media = $this->getFirstMedia('featured');
    if ($media) {
        $media->delete(); // Soft delete si el modelo Media tiene SoftDeletes
    }
}

public function restoreFeaturedImage(): void
{
    $media = $this->getMedia('featured', ['onlyTrashed' => true])->first();
    if ($media) {
        $media->restore();
    }
}
```

**Nota**: Si no usamos modelo Media personalizado, podemos usar `custom_properties` para marcar como eliminado.

---

### Paso 3.4: Actualizar componente Edit para usar soft delete
**Objetivo**: Modificar el componente Edit para usar soft delete en lugar de eliminación física.

**Tareas**:
- [ ] Modificar método `toggleRemoveFeaturedImage()` para usar soft delete
- [ ] Agregar método `restoreFeaturedImage()` en componente Edit
- [ ] Actualizar vista para mostrar opción de restaurar si hay imagen eliminada
- [ ] Agregar método `forceDeleteFeaturedImage()` para eliminación permanente

**Archivos a modificar**:
- `app/Livewire/Admin/News/Edit.php`
- `resources/views/livewire/admin/news/edit.blade.php`

---

### Paso 3.5: Actualizar consultas para excluir imágenes eliminadas
**Objetivo**: Asegurar que las imágenes eliminadas no se muestren en las vistas.

**Tareas**:
- [ ] Modificar `getFirstMedia()` para excluir imágenes eliminadas
- [ ] Modificar `hasMedia()` para excluir imágenes eliminadas
- [ ] Actualizar métodos en Show y Edit para considerar soft deletes

**Archivos a modificar**:
- `app/Livewire/Admin/News/Show.php`
- `app/Livewire/Admin/News/Edit.php`
- `app/Livewire/Admin/News/Index.php`

**Código sugerido**:
```php
// En lugar de:
$media = $newsPost->getFirstMedia('featured');

// Usar:
$media = $newsPost->getMedia('featured')->whereNull('deleted_at')->first();
```

---

## Fase 4: Mejoras Adicionales

### Paso 4.1: Agregar comando para regenerar conversiones
**Objetivo**: Permitir regenerar conversiones de imágenes existentes.

**Tareas**:
- [ ] Verificar que el comando `php artisan media-library:regenerate` funciona
- [ ] Documentar uso del comando
- [ ] Considerar crear comando personalizado si es necesario

---

### Paso 4.2: Optimizar carga de imágenes
**Objetivo**: Mejorar rendimiento al cargar listados con muchas imágenes.

**Tareas**:
- [ ] Verificar eager loading de media en consultas del Index
- [ ] Considerar usar lazy loading para imágenes en el frontend
- [ ] Verificar que las conversiones se generan de forma asíncrona si es posible

**Archivos a revisar**:
- `app/Livewire/Admin/News/Index.php` (método `newsPosts()`)

---

### Paso 4.3: Agregar validación de tamaño de imagen
**Objetivo**: Asegurar que las imágenes no sean demasiado grandes antes de subirlas.

**Tareas**:
- [ ] Verificar que la validación de tamaño funciona (5MB máximo)
- [ ] Considerar agregar validación de dimensiones (ancho/alto máximo)
- [ ] Agregar mensajes de error claros

**Archivos a revisar**:
- `app/Livewire/Admin/News/Create.php` (método `updatedFeaturedImage()`)
- `app/Livewire/Admin/News/Edit.php` (método `updatedFeaturedImage()`)

---

## Fase 5: Testing y Verificación

### Paso 5.1: Probar guardado de imágenes
**Tareas**:
- [ ] Crear nueva noticia con imagen
- [ ] Verificar que la imagen se guarda correctamente
- [ ] Verificar que las conversiones se generan
- [ ] Verificar que la imagen se muestra en Index, Show y Edit

---

### Paso 5.2: Probar edición de imágenes
**Tareas**:
- [ ] Editar noticia existente y cambiar imagen
- [ ] Verificar que la imagen anterior se mantiene (soft delete)
- [ ] Verificar que la nueva imagen se guarda correctamente
- [ ] Verificar que las conversiones se generan para la nueva imagen

---

### Paso 5.3: Probar eliminación y restauración
**Tareas**:
- [ ] Eliminar imagen desde Edit
- [ ] Verificar que el archivo físico no se elimina
- [ ] Verificar que la imagen no se muestra en las vistas
- [ ] Restaurar imagen eliminada
- [ ] Verificar que la imagen vuelve a mostrarse

---

### Paso 5.4: Probar eliminación permanente
**Tareas**:
- [ ] Eliminar imagen permanentemente
- [ ] Verificar que el archivo físico se elimina del servidor
- [ ] Verificar que el registro se elimina de la base de datos

---

## Notas Importantes

1. **Soft Deletes en Media Library**: Laravel Media Library no tiene soporte nativo para soft deletes. Tenemos dos opciones:
   - **Opción A**: Agregar `deleted_at` a la tabla `media` y crear modelo Media personalizado con SoftDeletes
   - **Opción B**: Usar `custom_properties` para marcar como eliminado (más simple, pero menos estándar)

2. **Generación de Conversiones**: Las conversiones se generan automáticamente al guardar, pero si las imágenes ya estaban guardadas antes de definir las conversiones, habrá que regenerarlas con `php artisan media-library:regenerate`.

3. **Rendimiento**: Si hay muchas imágenes, considerar usar queue jobs para generar conversiones de forma asíncrona.

4. **Limpieza**: Considerar crear un comando para limpiar imágenes eliminadas permanentemente después de X días.

---

## Orden de Implementación Recomendado

1. **Fase 1**: Diagnóstico (verificar qué está fallando)
2. **Fase 2**: Corrección de visualización (arreglar lo que no funciona)
3. **Fase 3**: Implementar soft delete (mejora solicitada)
4. **Fase 4**: Mejoras adicionales (optimizaciones)
5. **Fase 5**: Testing completo

---

## Referencias

- [Laravel Media Library Documentation](https://spatie.be/docs/laravel-medialibrary)
- [Media Library Conversions](https://spatie.be/docs/laravel-medialibrary/v11/converting-images/defining-conversions)
- [Media Library Regenerating Conversions](https://spatie.be/docs/laravel-medialibrary/v11/converting-images/regenerating-images)

