# Migraciones - Estructura Base

Este documento describe las migraciones relacionadas con la estructura base de la aplicación: programas Erasmus+ y años académicos.

## Tabla: `programs`

**Archivo**: `2025_12_12_193645_create_programs_table.php`

### Descripción

Almacena los diferentes programas Erasmus+ disponibles en el centro: Educación Escolar, Formación Profesional y Educación Superior.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `code` | string (unique) | Código del programa (ej: 'KA1xx', 'KA121-VET', 'KA131-HED') |
| `name` | string | Nombre del programa (ej: 'Educación Escolar') |
| `slug` | string (unique) | Slug para URLs amigables |
| `description` | text (nullable) | Descripción del programa |
| `is_active` | boolean | Indica si el programa está activo (default: true) |
| `order` | integer | Orden de visualización (default: 0) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

### Relaciones

- **HasMany**: `calls` - Convocatorias del programa
- **HasMany**: `newsPosts` - Noticias del programa
- **HasMany**: `documents` - Documentos del programa

### Índices

No hay índices adicionales más allá de las claves primarias y únicas.

### Ejemplo de Datos

```php
[
    'code' => 'KA1xx',
    'name' => 'Educación Escolar',
    'slug' => 'educacion-escolar',
    'description' => 'Programa de movilidades escolares y de personal',
    'is_active' => true,
    'order' => 1
]
```

---

## Tabla: `academic_years`

**Archivo**: `2025_12_12_193647_create_academic_years_table.php`

### Descripción

Gestiona los años académicos del centro. Permite organizar convocatorias, noticias y documentos por curso académico.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `year` | string (unique) | Año académico en formato 'YYYY-YYYY' (ej: '2024-2025') |
| `start_date` | date | Fecha de inicio del año académico |
| `end_date` | date | Fecha de fin del año académico |
| `is_current` | boolean | Indica si es el año académico actual (default: false) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

### Relaciones

- **HasMany**: `calls` - Convocatorias del año académico
- **HasMany**: `newsPosts` - Noticias del año académico
- **HasMany**: `documents` - Documentos del año académico

### Índices

No hay índices adicionales más allá de las claves primarias y únicas.

### Consideraciones

- Solo debería haber un año académico con `is_current = true` a la vez
- El formato del año debe seguir el patrón 'YYYY-YYYY'
- Las fechas deben ser consistentes (start_date < end_date)

### Ejemplo de Datos

```php
[
    'year' => '2024-2025',
    'start_date' => '2024-09-01',
    'end_date' => '2025-06-30',
    'is_current' => true
]
```

---

## Relaciones entre Tablas Base

### Diagrama de Relaciones

```
programs (1) ──< (N) calls
programs (1) ──< (N) news_posts
programs (1) ──< (N) documents

academic_years (1) ──< (N) calls
academic_years (1) ──< (N) news_posts
academic_years (1) ──< (N) documents
```

### Foreign Keys

- `calls.program_id` → `programs.id` (cascade on delete)
- `calls.academic_year_id` → `academic_years.id` (cascade on delete)
- `news_posts.program_id` → `programs.id` (null on delete, nullable)
- `news_posts.academic_year_id` → `academic_years.id` (cascade on delete)
- `documents.program_id` → `programs.id` (null on delete, nullable)
- `documents.academic_year_id` → `academic_years.id` (null on delete, nullable)

---

## Casos de Uso

### Crear un Nuevo Programa

```php
Program::create([
    'code' => 'KA131-HED',
    'name' => 'Educación Superior',
    'slug' => 'educacion-superior',
    'description' => 'Programa de movilidad de estudios y prácticas',
    'is_active' => true,
    'order' => 3
]);
```

### Crear un Nuevo Año Académico

```php
AcademicYear::create([
    'year' => '2025-2026',
    'start_date' => '2025-09-01',
    'end_date' => '2026-06-30',
    'is_current' => false
]);
```

### Marcar un Año Académico como Actual

```php
// Desmarcar el año actual anterior
AcademicYear::where('is_current', true)->update(['is_current' => false]);

// Marcar el nuevo año como actual
$academicYear->update(['is_current' => true]);
```

---

## Notas de Implementación

- Los slugs se generan automáticamente desde el nombre si no se proporcionan
- El campo `order` permite controlar el orden de visualización en listados
- El campo `is_active` permite desactivar programas sin eliminarlos
- Solo un año académico debería tener `is_current = true` a la vez

