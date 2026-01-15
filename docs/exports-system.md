# Sistema de Exportación de Datos

Documentación técnica completa del sistema de exportación de datos de la aplicación Erasmus+ Centro (Murcia), que permite exportar información a Excel con todos los filtros aplicados.

## Descripción General

El sistema de exportación permite a los administradores exportar datos de diferentes módulos a archivos Excel (XLSX) con todos los filtros aplicados en la interfaz. Utiliza Laravel Excel (maatwebsite/excel) para generar archivos formateados y estilizados.

## Características Principales

- ✅ **Exportación a Excel**: Archivos XLSX con formato y estilos
- ✅ **Filtros Aplicados**: Los mismos filtros que los componentes Index
- ✅ **Autorización**: Verificación de permisos antes de exportar
- ✅ **Formateo de Datos**: Fechas, traducciones, valores nulos formateados
- ✅ **Estilos**: Encabezados en negrita para mejor legibilidad
- ✅ **Nombres Dinámicos**: Archivos con timestamps y slugs
- ✅ **Traducciones**: Datos exportados en el idioma actual del usuario

---

## Clases de Exportación Disponibles

### 1. CallsExport

**Ubicación**: `app/Exports/CallsExport.php`

**Descripción**: Exporta convocatorias a Excel con todos los filtros aplicados.

**Columnas Exportadas**:
1. ID
2. Título
3. Programa
4. Año Académico
5. Tipo (Alumnado/Personal)
6. Modalidad (Corta/Larga)
7. Número de Plazas
8. Destinos (formateado como texto)
9. Fecha Inicio Estimada
10. Fecha Fin Estimada
11. Estado
12. Fecha Publicación
13. Fecha Cierre
14. Creador
15. Fecha Creación

**Filtros Aplicados**:
- `search` - Búsqueda por título/slug
- `filterProgram` - Filtro por programa
- `filterAcademicYear` - Filtro por año académico
- `filterType` - Filtro por tipo (alumnado/personal)
- `filterModality` - Filtro por modalidad (corta/larga)
- `filterStatus` - Filtro por estado
- `showDeleted` - Mostrar eliminados (soft deleted)
- `sortField` y `sortDirection` - Ordenación

**Uso**:
```php
use App\Exports\CallsExport;
use Maatwebsite\Excel\Facades\Excel;

$filters = [
    'search' => 'Erasmus',
    'filterProgram' => '1',
    'filterStatus' => 'abierta',
];

return Excel::download(new CallsExport($filters), 'convocatorias.xlsx');
```

**Nombre de Archivo**: `convocatorias-YYYY-MM-DD-HHMMSS.xlsx`

---

### 2. ResolutionsExport

**Ubicación**: `app/Exports/ResolutionsExport.php`

**Descripción**: Exporta resoluciones a Excel con todos los filtros aplicados. Las resoluciones están anidadas bajo convocatorias.

**Columnas Exportadas**:
1. ID
2. Título
3. Convocatoria
4. Fase
5. Tipo (Provisional/Definitivo/Alegaciones)
6. Descripción (truncada a 200 caracteres)
7. Procedimiento de Evaluación (truncado a 200 caracteres)
8. Fecha Oficial
9. Publicado (Sí/No)
10. Fecha Publicación
11. Creador
12. Fecha Creación

**Filtros Aplicados**:
- `call_id` - **Obligatorio**: Filtro por convocatoria
- `search` - Búsqueda por título/descripción
- `filterType` - Filtro por tipo (provisional/definitivo/alegaciones)
- `filterPublished` - Filtro por estado de publicación
- `filterPhase` - Filtro por fase
- `showDeleted` - Mostrar eliminados (soft deleted)
- `sortField` y `sortDirection` - Ordenación

**Uso**:
```php
use App\Exports\ResolutionsExport;
use Maatwebsite\Excel\Facades\Excel;

$filters = [
    'call_id' => $call->id, // Obligatorio
    'filterType' => 'provisional',
    'filterPublished' => '1',
];

$filename = 'resoluciones-'.Str::slug($call->title).'-'.now()->format('Y-m-d-His').'.xlsx';

return Excel::download(new ResolutionsExport($filters), $filename);
```

**Nombre de Archivo**: `resoluciones-{slug-convocatoria}-YYYY-MM-DD-HHMMSS.xlsx`

---

### 3. NewsletterSubscriptionsExport

**Ubicación**: `app/Exports/NewsletterSubscriptionsExport.php`

**Descripción**: Exporta suscriptores de newsletter a Excel (ya existente, mejorado para consistencia).

**Columnas Exportadas**:
1. Email
2. Nombre
3. Programas (formateado con nombres)
4. Estado (Activo/Inactivo)
5. Verificado (Sí/No)
6. Fecha Suscripción
7. Fecha Verificación
8. Fecha Baja

**Filtros Aplicados**:
- `filterProgram` - Filtro por programa
- `filterStatus` - Filtro por estado (activo/inactivo)
- `filterVerification` - Filtro por verificación (verificado/no-verificado)
- `search` - Búsqueda por email/nombre
- `sortField` y `sortDirection` - Ordenación

---

### 4. AuditLogsExport

**Ubicación**: `app/Exports/AuditLogsExport.php`

**Descripción**: Exporta logs de auditoría a Excel (ya existente, mejorado para consistencia).

---

## Cómo Usar las Exportaciones

### Desde Componentes Livewire

Las exportaciones se integran en los componentes Livewire mediante el método `export()`:

```php
use App\Exports\CallsExport;
use Maatwebsite\Excel\Facades\Excel;

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

### Desde Vistas Blade

Los botones de exportación se añaden en las vistas con estados de carga:

```blade
@if($this->canViewDeleted())
    <flux:button
        wire:click="export"
        variant="outline"
        icon="arrow-down-tray"
        wire:loading.attr="disabled"
        wire:target="export"
    >
        <span wire:loading.remove wire:target="export">
            {{ __('common.actions.export') }}
        </span>
        <span wire:loading wire:target="export">
            {{ __('common.actions.exporting') }}
        </span>
    </flux:button>
@endif
```

---

## Formato de Archivos Exportados

### Estructura

- **Formato**: XLSX (Excel 2007+)
- **Hoja**: Una hoja por exportación
- **Título de Hoja**: Nombre descriptivo (ej: "Convocatorias", "Resoluciones")
- **Encabezados**: Primera fila en negrita
- **Datos**: Formateados y traducidos

### Formateo de Datos

1. **Fechas**: Formato `d/m/Y H:i` (ej: "15/01/2024 10:30")
2. **Valores Nulos**: Mostrados como "-"
3. **Traducciones**: Tipos, modalidades, estados traducidos al idioma actual
4. **Arrays**: Convertidos a texto separado por comas (ej: destinos)
5. **Texto Largo**: Truncado cuando es necesario (ej: descripciones a 200 caracteres)

### Estilos Aplicados

- **Encabezados**: Negrita (`font: ['bold' => true]`)
- **Filas**: Sin estilos adicionales (formato estándar de Excel)

---

## Filtros Aplicados

### Consistencia con Componentes Index

Las exportaciones aplican **exactamente los mismos filtros** que los componentes Index para mantener consistencia. Esto significa que:

- Si un usuario filtra por programa en la interfaz, la exportación solo incluirá ese programa
- Si se busca por texto, la exportación solo incluirá resultados que coincidan
- Si se ordena por un campo, la exportación mantendrá ese orden

### Filtros Disponibles por Exportación

#### CallsExport
- ✅ Búsqueda (título/slug)
- ✅ Programa
- ✅ Año Académico
- ✅ Tipo (alumnado/personal)
- ✅ Modalidad (corta/larga)
- ✅ Estado
- ✅ Mostrar eliminados
- ✅ Ordenación

#### ResolutionsExport
- ✅ Convocatoria (obligatorio)
- ✅ Búsqueda (título/descripción)
- ✅ Tipo (provisional/definitivo/alegaciones)
- ✅ Estado de publicación
- ✅ Fase
- ✅ Mostrar eliminados
- ✅ Ordenación

---

## Autorización

Todas las exportaciones verifican permisos antes de ejecutarse:

```php
public function export()
{
    $this->authorize('viewAny', Call::class);
    // ... resto del código
}
```

**Permisos Requeridos**:
- **CallsExport**: `viewAny` en `Call` (permiso `CALLS_VIEW`)
- **ResolutionsExport**: `viewAny` en `Resolution` (permiso `CALLS_VIEW`)
- **NewsletterSubscriptionsExport**: `canExport()` en componente (permiso `NEWSLETTER_EXPORT`)

---

## Ejemplos de Uso

### Ejemplo 1: Exportar Todas las Convocatorias

```php
$export = new CallsExport([]);
$collection = $export->collection();
// Obtiene todas las convocatorias no eliminadas
```

### Ejemplo 2: Exportar Convocatorias Filtradas

```php
$filters = [
    'filterProgram' => '1',
    'filterStatus' => 'abierta',
    'search' => 'Erasmus',
];

$export = new CallsExport($filters);
$collection = $export->collection();
// Obtiene solo convocatorias que coincidan con los filtros
```

### Ejemplo 3: Exportar Resoluciones de una Convocatoria

```php
$filters = [
    'call_id' => $call->id, // Obligatorio
    'filterType' => 'provisional',
];

$export = new ResolutionsExport($filters);
$collection = $export->collection();
// Obtiene solo resoluciones provisionales de la convocatoria
```

---

## Testing

### Tests Implementados

- **CallsExportTest**: 22 tests (62 assertions)
  - Tests básicos (exportación, headings, title, mapping)
  - Tests de filtros (todos los filtros individuales y combinados)
  - Tests de formateo (fechas, traducciones, valores nulos)

- **ResolutionsExportTest**: 21 tests (55 assertions)
  - Tests básicos (exportación, headings, title, mapping)
  - Tests de filtros (todos los filtros individuales y combinados)
  - Tests de formateo (fechas, traducciones, truncado de texto)

- **Tests de Componentes Livewire**: 15 tests (15 assertions)
  - Tests de autorización
  - Tests de aplicación de filtros
  - Tests de generación de archivos

**Total**: 58 tests (132 assertions)

### Ejecutar Tests

```bash
# Todos los tests de exportación
php artisan test --filter="Export"

# Tests específicos
php artisan test tests/Feature/Exports/CallsExportTest.php
php artisan test tests/Feature/Exports/ResolutionsExportTest.php
```

---

## Traducciones

### Claves de Traducción Utilizadas

**Acciones**:
- `common.actions.export` - "Exportar"
- `common.actions.exporting` - "Exportando..."

**Mensajes**:
- `common.messages.yes` - "Sí" / "Yes"
- `common.messages.no` - "No" / "No"
- `common.messages.system` - "Sistema" / "System"

**Estados**:
- `common.status.active` - "Activo" / "Active"
- `common.status.inactive` - "Inactivo" / "Inactive"

**Tipos y Modalidades**:
- `common.call_types.*` - Tipos de convocatoria
- `common.call_modalities.*` - Modalidades
- `common.call_status.*` - Estados de convocatoria
- `common.resolutions.types.*` - Tipos de resolución

---

## Optimizaciones

### Eager Loading

Las exportaciones utilizan eager loading para evitar problemas N+1:

```php
$query->with(['program', 'academicYear', 'creator', 'updater']);
```

### Consultas Optimizadas

- Uso de índices de base de datos para filtros comunes
- Consultas eficientes con `when()` para filtros opcionales
- Ordenación aplicada en la consulta SQL

---

## Mejoras Futuras

1. **Exportación a PDF**: Añadir opción de exportar a PDF además de Excel
2. **Exportación Programada**: Permitir programar exportaciones automáticas
3. **Plantillas Personalizadas**: Permitir personalizar el formato de exportación
4. **Exportación Masiva**: Optimizar para grandes volúmenes de datos con chunking
5. **Múltiples Formatos**: Soporte para CSV, JSON, etc.

---

## Referencias

- [Laravel Excel Documentation](https://docs.laravel-excel.com/)
- [Documentación de Convocatorias](admin-calls-crud.md)
- [Documentación de Resoluciones](admin-resolutions-crud.md)
- [Documentación de Newsletter](admin-newsletter-subscriptions-crud.md)

---

**Última Actualización**: Enero 2026  
**Versión**: 1.0
