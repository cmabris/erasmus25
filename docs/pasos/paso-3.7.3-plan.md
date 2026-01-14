# Plan Detallado: Paso 3.7.3 - Exportación de Datos

## Objetivo

Implementar un sistema completo de exportación de datos que permita:
- Exportar convocatorias a Excel con todos los filtros aplicados
- Exportar listados de resoluciones a Excel con todos los filtros aplicados
- Exportar suscriptores newsletter a CSV (ya implementado, se verifica y documenta)
- Usar Laravel Excel (maatwebsite/excel) que ya está instalado en la aplicación
- Aplicar los mismos filtros que los componentes Index
- Formatear datos de manera legible para Excel
- Incluir estilos en los archivos exportados

## Estado Actual

### ✅ Ya Implementado

1. **Laravel Excel**:
   - ✅ Paquete `maatwebsite/excel` v3.1 instalado
   - ✅ Configuración automática de Laravel Excel

2. **Exportaciones Existentes**:
   - ✅ `App\Exports\AuditLogsExport` - Exportación de logs de auditoría
   - ✅ `App\Exports\NewsletterSubscriptionsExport` - Exportación de suscriptores newsletter
   - ✅ Patrón establecido para exportaciones

3. **Componentes Index con Filtros**:
   - ✅ `App\Livewire\Admin\Calls\Index` - Listado de convocatorias con filtros
   - ✅ `App\Livewire\Admin\Calls\Resolutions\Index` - Listado de resoluciones con filtros
   - ✅ `App\Livewire\Admin\Newsletter\Index` - Listado de suscriptores (ya tiene exportación)

### ⚠️ Pendiente de Implementar

1. **Exportación de Convocatorias**:
   - ⚠️ Crear clase `App\Exports\CallsExport`
   - ⚠️ Implementar filtros del componente Index
   - ⚠️ Formatear datos para Excel
   - ⚠️ Añadir método `export()` en componente Index
   - ⚠️ Añadir botón de exportación en vista

2. **Exportación de Resoluciones**:
   - ⚠️ Crear clase `App\Exports\ResolutionsExport`
   - ⚠️ Implementar filtros del componente Index
   - ⚠️ Formatear datos para Excel
   - ⚠️ Añadir método `export()` en componente Index
   - ⚠️ Añadir botón de exportación en vista

3. **Verificación de Newsletter**:
   - ⚠️ Verificar que la exportación de newsletter funciona correctamente
   - ⚠️ Documentar si necesita mejoras

4. **Tests**:
   - ⚠️ Tests de exportación de convocatorias
   - ⚠️ Tests de exportación de resoluciones
   - ⚠️ Tests de aplicación de filtros

---

## Plan de Desarrollo

### **Fase 1: Exportación de Convocatorias**

#### Paso 1.1: Crear Clase CallsExport

**Objetivo**: Crear la clase de exportación para convocatorias.

**Archivo**: `app/Exports/CallsExport.php`

**Características**:
- Implementar `FromCollection` - Para obtener datos
- Implementar `WithHeadings` - Para encabezados
- Implementar `WithMapping` - Para formatear filas
- Implementar `WithTitle` - Para nombre de hoja
- Implementar `WithStyles` - Para estilos (headers en negrita)
- Aplicar los mismos filtros que el componente Index:
  - `search` - Búsqueda por título/slug
  - `filterProgram` - Filtro por programa
  - `filterAcademicYear` - Filtro por año académico
  - `filterType` - Filtro por tipo (alumnado/personal)
  - `filterModality` - Filtro por modalidad (corta/larga)
  - `filterStatus` - Filtro por estado
  - `showDeleted` - Mostrar eliminados
  - `sortField` y `sortDirection` - Ordenación

**Columnas a exportar**:
1. ID
2. Título
3. Programa
4. Año Académico
5. Tipo (Alumnado/Personal)
6. Modalidad (Corta/Larga)
7. Número de Plazas
8. Destinos (formateado)
9. Fecha Inicio Estimada
10. Fecha Fin Estimada
11. Estado
12. Fecha Publicación
13. Fecha Cierre
14. Creador
15. Fecha Creación
16. Fecha Actualización

**Formateo de datos**:
- Fechas en formato `d/m/Y` o `d/m/Y H:i`
- Tipos y modalidades traducidos
- Estados traducidos
- Destinos como lista separada por comas
- Nombres de programas y años académicos (no IDs)

**Archivos a crear**:
- `app/Exports/CallsExport.php`

**Resultado esperado**:
- Clase de exportación creada con todos los filtros
- Formateo correcto de datos
- Estilos aplicados a encabezados

---

#### Paso 1.2: Añadir Método export() en Componente Index

**Objetivo**: Añadir método de exportación en el componente de convocatorias.

**Archivo**: `app/Livewire/Admin/Calls/Index.php`

**Tareas**:
1. Importar `Maatwebsite\Excel\Facades\Excel`
2. Importar `App\Exports\CallsExport`
3. Crear método `export()`:
   ```php
   public function export()
   {
       $this->authorize('viewAny', Call::class);
       
       $filters = [
           'search' => $this->search,
           'filterProgram' => $this->filterProgram,
           'filterAcademicYear' => $this->filterAcademicYear,
           'filterType' => $this->filterType,
           'filterModality' => $this->filterModality,
           'filterStatus' => $this->filterStatus,
           'showDeleted' => $this->showDeleted,
           'sortField' => $this->sortField,
           'sortDirection' => $this->sortDirection,
       ];
       
       $filename = 'convocatorias-'.now()->format('Y-m-d-His').'.xlsx';
       
       return Excel::download(new CallsExport($filters), $filename);
   }
   ```

**Archivos a modificar**:
- `app/Livewire/Admin/Calls/Index.php`

**Resultado esperado**:
- Método `export()` añadido
- Autorización verificada
- Filtros aplicados correctamente
- Nombre de archivo con timestamp

---

#### Paso 1.3: Añadir Botón de Exportación en Vista

**Objetivo**: Añadir botón de exportación en la vista de listado.

**Archivo**: `resources/views/livewire/admin/calls/index.blade.php`

**Tareas**:
1. Añadir botón de exportación junto al botón "Crear Convocatoria"
2. Usar componente Flux UI `flux:button`
3. Icono: `arrow-down-tray` o `document-arrow-down`
4. Variante: `secondary` o `outline`
5. Acción: `wire:click="export"`
6. Mostrar solo si el usuario tiene permiso `viewAny` en Call

**Ubicación**: En el header, junto al botón "Crear Convocatoria"

**Código sugerido**:
```blade
<flux:button 
    wire:click="export"
    variant="secondary"
    icon="arrow-down-tray"
    wire:loading.attr="disabled"
    wire:target="export"
>
    <span wire:loading.remove wire:target="export">
        {{ __('Exportar') }}
    </span>
    <span wire:loading wire:target="export">
        {{ __('Exportando...') }}
    </span>
</flux:button>
```

**Archivos a modificar**:
- `resources/views/livewire/admin/calls/index.blade.php`

**Resultado esperado**:
- Botón de exportación visible
- Estado de carga durante exportación
- Deshabilitado durante exportación

---

### **Fase 2: Exportación de Resoluciones**

#### Paso 2.1: Crear Clase ResolutionsExport

**Objetivo**: Crear la clase de exportación para resoluciones.

**Archivo**: `app/Exports/ResolutionsExport.php`

**Características**:
- Implementar `FromCollection` - Para obtener datos
- Implementar `WithHeadings` - Para encabezados
- Implementar `WithMapping` - Para formatear filas
- Implementar `WithTitle` - Para nombre de hoja
- Implementar `WithStyles` - Para estilos (headers en negrita)
- Aplicar los mismos filtros que el componente Index:
  - `search` - Búsqueda por título/descripción
  - `filterType` - Filtro por tipo de resolución
  - `filterPublished` - Filtro por estado de publicación
  - `filterPhase` - Filtro por fase
  - `showDeleted` - Mostrar eliminados
  - `sortField` y `sortDirection` - Ordenación
- **Importante**: Las resoluciones están anidadas bajo una convocatoria, así que el filtro por `call_id` se aplica automáticamente

**Columnas a exportar**:
1. ID
2. Título
3. Convocatoria
4. Fase
5. Tipo (Provisional/Definitivo/Alegaciones)
6. Descripción
7. Procedimiento de Evaluación
8. Fecha Oficial
9. Publicada (Sí/No)
10. Fecha Publicación
11. Creador
12. Fecha Creación
13. Fecha Actualización

**Formateo de datos**:
- Fechas en formato `d/m/Y` o `d/m/Y H:i`
- Tipos traducidos
- Estado de publicación como "Sí"/"No"
- Nombres de convocatoria y fase (no IDs)
- Descripción truncada si es muy larga (opcional)

**Archivos a crear**:
- `app/Exports/ResolutionsExport.php`

**Resultado esperado**:
- Clase de exportación creada con todos los filtros
- Formateo correcto de datos
- Estilos aplicados a encabezados

---

#### Paso 2.2: Añadir Método export() en Componente Index

**Objetivo**: Añadir método de exportación en el componente de resoluciones.

**Archivo**: `app/Livewire/Admin/Calls/Resolutions/Index.php`

**Tareas**:
1. Importar `Maatwebsite\Excel\Facades\Excel`
2. Importar `App\Exports\ResolutionsExport`
3. Crear método `export()`:
   ```php
   public function export()
   {
       $this->authorize('viewAny', Resolution::class);
       
       $filters = [
           'call_id' => $this->call->id, // Importante: filtrar por convocatoria
           'search' => $this->search,
           'filterType' => $this->filterType,
           'filterPublished' => $this->filterPublished,
           'filterPhase' => $this->filterPhase,
           'showDeleted' => $this->showDeleted,
           'sortField' => $this->sortField,
           'sortDirection' => $this->sortDirection,
       ];
       
       $filename = 'resoluciones-'.Str::slug($this->call->title).'-'.now()->format('Y-m-d-His').'.xlsx';
       
       return Excel::download(new ResolutionsExport($filters), $filename);
   }
   ```

**Archivos a modificar**:
- `app/Livewire/Admin/Calls/Resolutions/Index.php`

**Resultado esperado**:
- Método `export()` añadido
- Autorización verificada
- Filtros aplicados correctamente (incluyendo call_id)
- Nombre de archivo con slug de convocatoria y timestamp

---

#### Paso 2.3: Añadir Botón de Exportación en Vista

**Objetivo**: Añadir botón de exportación en la vista de listado.

**Archivo**: `resources/views/livewire/admin/calls/resolutions/index.blade.php`

**Tareas**:
1. Añadir botón de exportación junto al botón "Crear Resolución"
2. Usar componente Flux UI `flux:button`
3. Icono: `arrow-down-tray` o `document-arrow-down`
4. Variante: `secondary` o `outline`
5. Acción: `wire:click="export"`
6. Mostrar solo si el usuario tiene permiso `viewAny` en Resolution

**Ubicación**: En el header, junto al botón "Crear Resolución"

**Código sugerido**:
```blade
<flux:button 
    wire:click="export"
    variant="secondary"
    icon="arrow-down-tray"
    wire:loading.attr="disabled"
    wire:target="export"
>
    <span wire:loading.remove wire:target="export">
        {{ __('Exportar') }}
    </span>
    <span wire:loading wire:target="export">
        {{ __('Exportando...') }}
    </span>
</flux:button>
```

**Archivos a modificar**:
- `resources/views/livewire/admin/calls/resolutions/index.blade.php`

**Resultado esperado**:
- Botón de exportación visible
- Estado de carga durante exportación
- Deshabilitado durante exportación

---

### **Fase 3: Verificación y Mejoras de Newsletter**

#### Paso 3.1: Verificar Exportación de Newsletter

**Objetivo**: Verificar que la exportación de newsletter funciona correctamente.

**Tareas**:
1. Revisar `App\Exports\NewsletterSubscriptionsExport`
2. Verificar que aplica todos los filtros del componente Index
3. Verificar que el formato CSV es correcto
4. Probar exportación manualmente
5. Verificar que el botón de exportación está visible y funciona

**Archivos a revisar**:
- `app/Exports/NewsletterSubscriptionsExport.php`
- `app/Livewire/Admin/Newsletter/Index.php`
- `resources/views/livewire/admin/newsletter/index.blade.php`

**Resultado esperado**:
- Exportación de newsletter verificada
- Documentación actualizada si es necesario

---

### **Fase 4: Traducciones**

#### Paso 4.1: Añadir Traducciones para Exportación

**Objetivo**: Añadir traducciones necesarias para exportación.

**Tareas**:
1. Revisar archivos de traducción:
   - `lang/es/common.php`
   - `lang/en/common.php`

2. Añadir traducciones para:
   - "Exportar" / "Export"
   - "Exportando..." / "Exporting..."
   - Tipos de convocatoria (Alumnado/Personal)
   - Modalidades (Corta/Larga)
   - Estados de convocatoria
   - Tipos de resolución (Provisional/Definitivo/Alegaciones)
   - "Publicada" / "Published"
   - "Sí" / "Yes"
   - "No" / "No"

3. Organizar en sección `exports` o añadir a secciones existentes:
   ```php
   'exports' => [
       'export' => 'Exportar',
       'exporting' => 'Exportando...',
       'convocatorias' => 'Convocatorias',
       'resoluciones' => 'Resoluciones',
   ],
   ```

**Archivos a modificar**:
- `lang/es/common.php`
- `lang/en/common.php`

**Resultado esperado**:
- Todas las traducciones añadidas
- Textos en español e inglés

---

### **Fase 5: Tests**

#### Paso 5.1: Crear Tests de Exportación de Convocatorias

**Objetivo**: Crear tests para CallsExport.

**Archivo**: `tests/Feature/Exports/CallsExportTest.php`

**Tests a implementar**:
- Test de exportación básica sin filtros
- Test de exportación con filtro por programa
- Test de exportación con filtro por año académico
- Test de exportación con filtro por tipo
- Test de exportación con filtro por modalidad
- Test de exportación con filtro por estado
- Test de exportación con búsqueda
- Test de exportación con ordenación
- Test de exportación incluyendo eliminados
- Test de formateo de datos (fechas, tipos, etc.)
- Test de autorización (solo usuarios con permiso pueden exportar)

**Archivos a crear**:
- `tests/Feature/Exports/CallsExportTest.php`

**Resultado esperado**:
- Tests de exportación creados y pasando

---

#### Paso 5.2: Crear Tests de Exportación de Resoluciones

**Objetivo**: Crear tests para ResolutionsExport.

**Archivo**: `tests/Feature/Exports/ResolutionsExportTest.php`

**Tests a implementar**:
- Test de exportación básica sin filtros (pero con call_id)
- Test de exportación con filtro por tipo
- Test de exportación con filtro por estado de publicación
- Test de exportación con filtro por fase
- Test de exportación con búsqueda
- Test de exportación con ordenación
- Test de exportación incluyendo eliminados
- Test de formateo de datos (fechas, tipos, etc.)
- Test de autorización (solo usuarios con permiso pueden exportar)
- Test de que solo exporta resoluciones de la convocatoria especificada

**Archivos a crear**:
- `tests/Feature/Exports/ResolutionsExportTest.php`

**Resultado esperado**:
- Tests de exportación creados y pasando

---

#### Paso 5.3: Crear Tests de Componentes Livewire

**Objetivo**: Crear tests para métodos export() en componentes.

**Archivos**:
- `tests/Feature/Livewire/Admin/Calls/IndexTest.php` - Añadir tests de exportación
- `tests/Feature/Livewire/Admin/Calls/Resolutions/IndexTest.php` - Añadir tests de exportación

**Tests a implementar**:
- Test de que el método export() requiere autorización
- Test de que el método export() aplica filtros correctamente
- Test de que el método export() genera nombre de archivo correcto
- Test de que usuarios sin permiso no pueden exportar

**Archivos a modificar**:
- `tests/Feature/Livewire/Admin/Calls/IndexTest.php`
- `tests/Feature/Livewire/Admin/Calls/Resolutions/IndexTest.php`

**Resultado esperado**:
- Tests de componentes creados y pasando

---

### **Fase 6: Documentación**

#### Paso 6.1: Crear Documentación Técnica

**Objetivo**: Documentar el sistema de exportación.

**Archivo**: `docs/exports-system.md`

**Contenido**:
- Descripción general del sistema de exportación
- Clases de exportación disponibles
- Cómo usar las exportaciones
- Filtros aplicados
- Formato de archivos exportados
- Ejemplos de uso

**Archivos a crear**:
- `docs/exports-system.md`

**Resultado esperado**:
- Documentación técnica completa

---

#### Paso 6.2: Actualizar Documentación de Componentes

**Objetivo**: Actualizar documentación de componentes con funcionalidad de exportación.

**Archivos a actualizar**:
- `docs/admin-calls-crud.md` - Añadir sección de exportación
- `docs/admin-resolutions-crud.md` - Añadir sección de exportación

**Contenido**:
- Descripción de funcionalidad de exportación
- Cómo usar el botón de exportación
- Filtros aplicados en exportación
- Formato de archivos

**Resultado esperado**:
- Documentación de componentes actualizada

---

## Resumen de Archivos

### Archivos a Crear

1. **Exportaciones**:
   - `app/Exports/CallsExport.php`
   - `app/Exports/ResolutionsExport.php`

2. **Tests**:
   - `tests/Feature/Exports/CallsExportTest.php`
   - `tests/Feature/Exports/ResolutionsExportTest.php`

3. **Documentación**:
   - `docs/exports-system.md`

### Archivos a Modificar

1. **Componentes Livewire**:
   - `app/Livewire/Admin/Calls/Index.php` - Añadir método `export()`
   - `app/Livewire/Admin/Calls/Resolutions/Index.php` - Añadir método `export()`

2. **Vistas**:
   - `resources/views/livewire/admin/calls/index.blade.php` - Añadir botón de exportación
   - `resources/views/livewire/admin/calls/resolutions/index.blade.php` - Añadir botón de exportación

3. **Traducciones**:
   - `lang/es/common.php` - Añadir traducciones
   - `lang/en/common.php` - Añadir traducciones

4. **Tests**:
   - `tests/Feature/Livewire/Admin/Calls/IndexTest.php` - Añadir tests de exportación
   - `tests/Feature/Livewire/Admin/Calls/Resolutions/IndexTest.php` - Añadir tests de exportación

5. **Documentación**:
   - `docs/admin-calls-crud.md` - Añadir sección de exportación
   - `docs/admin-resolutions-crud.md` - Añadir sección de exportación

---

## Notas Importantes

1. **Filtros**: Las exportaciones deben aplicar exactamente los mismos filtros que los componentes Index para mantener consistencia.

2. **Autorización**: Todas las exportaciones deben verificar permisos antes de ejecutarse.

3. **Formateo**: Los datos deben formatearse de manera legible para Excel (fechas, traducciones, etc.).

4. **Rendimiento**: Para grandes volúmenes de datos, considerar usar `WithChunkReading` o `WithBatchInserts` si es necesario.

5. **Nombres de Archivo**: Usar timestamps y slugs para evitar conflictos de nombres.

6. **Estilos**: Aplicar estilos básicos a encabezados (negrita) para mejorar legibilidad.

7. **Resoluciones**: Recordar que las resoluciones están anidadas bajo convocatorias, así que siempre filtrar por `call_id`.

---

## Orden de Implementación Recomendado

1. **Fase 1**: Exportación de Convocatorias (Pasos 1.1, 1.2, 1.3)
2. **Fase 2**: Exportación de Resoluciones (Pasos 2.1, 2.2, 2.3)
3. **Fase 3**: Verificación de Newsletter (Paso 3.1)
4. **Fase 4**: Traducciones (Paso 4.1)
5. **Fase 5**: Tests (Pasos 5.1, 5.2, 5.3)
6. **Fase 6**: Documentación (Pasos 6.1, 6.2)

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan completado - Listo para implementación
