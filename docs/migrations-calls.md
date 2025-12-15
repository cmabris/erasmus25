# Migraciones - Sistema de Convocatorias

Este documento describe las migraciones relacionadas con el sistema de convocatorias Erasmus+, incluyendo convocatorias, fases, solicitudes y resoluciones.

## Tabla: `calls`

**Archivo**: `2025_12_12_193712_create_calls_table.php`

### Descripción

Almacena las convocatorias Erasmus+ del centro. Cada convocatoria pertenece a un programa y un año académico específico.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `program_id` | foreignId | Programa al que pertenece (FK → programs) |
| `academic_year_id` | foreignId | Año académico (FK → academic_years) |
| `title` | string | Título de la convocatoria |
| `slug` | string (unique) | Slug para URLs amigables |
| `type` | enum | Tipo: 'alumnado' o 'personal' |
| `modality` | enum | Modalidad: 'corta' o 'larga' |
| `number_of_places` | integer | Número de plazas disponibles |
| `destinations` | json | Array de países/ciudades/entidades de acogida |
| `estimated_start_date` | date (nullable) | Fecha estimada de inicio |
| `estimated_end_date` | date (nullable) | Fecha estimada de fin |
| `requirements` | text (nullable) | Requisitos de la convocatoria |
| `documentation` | text (nullable) | Documentación necesaria |
| `selection_criteria` | text (nullable) | Criterios de selección |
| `scoring_table` | json (nullable) | Estructura del baremo de evaluación |
| `status` | enum | Estado: 'borrador', 'abierta', 'cerrada', 'en_baremacion', 'resuelta', 'archivada' |
| `published_at` | timestamp (nullable) | Fecha de publicación |
| `closed_at` | timestamp (nullable) | Fecha de cierre |
| `created_by` | foreignId (nullable) | Usuario creador (FK → users) |
| `updated_by` | foreignId (nullable) | Usuario que actualizó (FK → users) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

### Estados de la Convocatoria

- **borrador**: Convocatoria en preparación, no visible públicamente
- **abierta**: Convocatoria abierta para recibir solicitudes
- **cerrada**: Convocatoria cerrada, no acepta más solicitudes
- **en_baremacion**: En proceso de evaluación y baremación
- **resuelta**: Convocatoria resuelta con listados publicados
- **archivada**: Convocatoria archivada para consulta histórica

### Campos JSON

#### `destinations`

```json
[
    "España",
    "Francia",
    "Alemania"
]
```

#### `scoring_table`

```json
{
    "expediente_academico": 40,
    "idioma": 30,
    "entrevista": 20,
    "otros_meritos": 10
}
```

### Relaciones

- **BelongsTo**: `program` - Programa al que pertenece
- **BelongsTo**: `academicYear` - Año académico
- **BelongsTo**: `creator` - Usuario creador
- **BelongsTo**: `updater` - Usuario que actualizó
- **HasMany**: `phases` - Fases de la convocatoria
- **HasMany**: `applications` - Solicitudes recibidas
- **HasMany**: `resolutions` - Resoluciones publicadas

### Índices

- `['program_id', 'academic_year_id', 'status']` - Búsqueda por programa, año y estado
- `['status', 'published_at']` - Listado de convocatorias activas ordenadas por fecha

---

## Tabla: `call_phases`

**Archivo**: `2025_12_12_193714_create_call_phases_table.php`

### Descripción

Gestiona las diferentes fases del proceso de una convocatoria: publicación, solicitudes, listados provisionales, alegaciones, definitivos, etc.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `call_id` | foreignId | Convocatoria (FK → calls) |
| `phase_type` | enum | Tipo de fase |
| `name` | string | Nombre de la fase |
| `description` | text (nullable) | Descripción de la fase |
| `start_date` | date (nullable) | Fecha de inicio |
| `end_date` | date (nullable) | Fecha de fin |
| `is_current` | boolean | Indica si es la fase actual (default: false) |
| `order` | integer | Orden de la fase (default: 0) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

### Tipos de Fase

- **publicacion**: Fase de publicación de la convocatoria
- **solicitudes**: Periodo de recepción de solicitudes
- **provisional**: Publicación del listado provisional
- **alegaciones**: Periodo de alegaciones
- **definitivo**: Publicación del listado definitivo
- **renuncias**: Gestión de renuncias
- **lista_espera**: Gestión de lista de espera

### Relaciones

- **BelongsTo**: `call` - Convocatoria a la que pertenece
- **HasMany**: `resolutions` - Resoluciones de esta fase

### Índices

- `['call_id', 'is_current']` - Búsqueda de la fase actual de una convocatoria

### Consideraciones

- Solo debería haber una fase con `is_current = true` por convocatoria
- El campo `order` permite ordenar las fases cronológicamente
- Las fechas pueden ser null si la fase aún no ha comenzado o terminado

---

## Tabla: `call_applications`

**Archivo**: `2025_12_12_193717_create_call_applications_table.php`

### Descripción

Almacena las solicitudes recibidas para una convocatoria. Esta tabla es opcional y puede utilizarse para futuras funcionalidades de gestión de solicitudes.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `call_id` | foreignId | Convocatoria (FK → calls) |
| `applicant_name` | string | Nombre del solicitante |
| `applicant_email` | string | Email del solicitante |
| `applicant_phone` | string (nullable) | Teléfono del solicitante |
| `status` | enum | Estado: 'pendiente', 'admitida', 'rechazada', 'renunciada' |
| `score` | decimal(5,2) (nullable) | Puntuación obtenida |
| `position` | integer (nullable) | Posición en el listado |
| `notes` | text (nullable) | Notas adicionales |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

### Estados de la Solicitud

- **pendiente**: Solicitud recibida, pendiente de evaluación
- **admitida**: Solicitud admitida en el listado
- **rechazada**: Solicitud rechazada
- **renunciada**: El solicitante ha renunciado a la plaza

### Relaciones

- **BelongsTo**: `call` - Convocatoria a la que pertenece

### Índices

- `['call_id', 'status']` - Búsqueda de solicitudes por convocatoria y estado
- `['call_id', 'position']` - Ordenación por posición en el listado

---

## Tabla: `resolutions`

**Archivo**: `2025_12_12_193747_create_resolutions_table.php`

### Descripción

Gestiona las resoluciones publicadas para cada convocatoria y fase. Incluye listados provisionales, definitivos y resoluciones sobre alegaciones.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `call_id` | foreignId | Convocatoria (FK → calls) |
| `call_phase_id` | foreignId | Fase de la convocatoria (FK → call_phases) |
| `type` | enum | Tipo: 'provisional', 'definitivo', 'alegaciones' |
| `title` | string | Título de la resolución |
| `description` | text (nullable) | Descripción de la resolución |
| `evaluation_procedure` | text (nullable) | Explicación del procedimiento de evaluación |
| `official_date` | date | Fecha oficial de la resolución |
| `published_at` | timestamp (nullable) | Fecha de publicación |
| `created_by` | foreignId (nullable) | Usuario creador (FK → users) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

### Tipos de Resolución

- **provisional**: Listado provisional de admitidos
- **definitivo**: Listado definitivo de admitidos
- **alegaciones**: Resolución sobre alegaciones presentadas

### Relaciones

- **BelongsTo**: `call` - Convocatoria a la que pertenece
- **BelongsTo**: `callPhase` - Fase de la convocatoria
- **BelongsTo**: `creator` - Usuario creador

### Índices

- `['call_id', 'type']` - Búsqueda de resoluciones por convocatoria y tipo

### Nota sobre Documentos

Los PDFs de resoluciones y listados se gestionan mediante Laravel Media Library, asociados al modelo `Resolution` mediante colecciones como 'resoluciones', 'anexos', 'listados'.

---

## Flujo de Trabajo de una Convocatoria

```
1. Crear Convocatoria (status: 'borrador')
   ↓
2. Crear Fase 'publicacion' (is_current: true)
   ↓
3. Publicar Convocatoria (status: 'abierta', published_at: now())
   ↓
4. Crear Fase 'solicitudes' (is_current: true)
   ↓
5. Cerrar Convocatoria (status: 'cerrada', closed_at: now())
   ↓
6. Crear Fase 'provisional' (is_current: true)
   ↓
7. Crear Resolución tipo 'provisional'
   ↓
8. Crear Fase 'alegaciones' (is_current: true)
   ↓
9. Crear Resolución tipo 'alegaciones' (si aplica)
   ↓
10. Crear Fase 'definitivo' (is_current: true)
    ↓
11. Crear Resolución tipo 'definitivo'
    ↓
12. Archivar Convocatoria (status: 'archivada')
```

---

## Ejemplos de Uso

### Crear una Convocatoria

```php
$call = Call::create([
    'program_id' => $program->id,
    'academic_year_id' => $academicYear->id,
    'title' => 'Convocatoria FCT 2024-2025',
    'slug' => 'convocatoria-fct-2024-2025',
    'type' => 'alumnado',
    'modality' => 'larga',
    'number_of_places' => 20,
    'destinations' => ['Francia', 'Alemania', 'Italia'],
    'estimated_start_date' => '2025-03-01',
    'estimated_end_date' => '2025-08-31',
    'requirements' => 'Estar matriculado en 2º curso...',
    'status' => 'borrador',
    'created_by' => auth()->id()
]);
```

### Crear una Fase

```php
CallPhase::create([
    'call_id' => $call->id,
    'phase_type' => 'publicacion',
    'name' => 'Publicación de la convocatoria',
    'start_date' => '2024-10-01',
    'end_date' => '2024-10-15',
    'is_current' => true,
    'order' => 1
]);
```

### Crear una Resolución

```php
Resolution::create([
    'call_id' => $call->id,
    'call_phase_id' => $phase->id,
    'type' => 'provisional',
    'title' => 'Resolución provisional de la convocatoria',
    'evaluation_procedure' => 'Se ha evaluado según el baremo...',
    'official_date' => '2024-11-15',
    'published_at' => now(),
    'created_by' => auth()->id()
]);
```

---

## Notas de Implementación

- Los slugs se generan automáticamente desde el título si no se proporcionan
- El campo `scoring_table` permite almacenar estructuras de baremo flexibles
- Las foreign keys a `users` utilizan `nullOnDelete()` para mantener el historial
- Los documentos asociados (PDFs) se gestionan mediante Laravel Media Library
- Solo una fase debería tener `is_current = true` por convocatoria a la vez

