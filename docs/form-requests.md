# Form Requests - Documentación Técnica

Este documento describe todos los Form Requests implementados en la aplicación "Erasmus+ Centro (Murcia)" para la validación de datos de entrada.

## Resumen

La aplicación cuenta con **22 Form Requests** organizados por entidad, siguiendo el patrón Store/Update de Laravel. Todos los mensajes de error están internacionalizados en español e inglés.

## Form Requests por Entidad

### Programas (Programs)

| Form Request | Descripción |
|-------------|-------------|
| `StoreProgramRequest` | Validación para crear programas |
| `UpdateProgramRequest` | Validación para actualizar programas |

**Campos validados:**
- `code`: requerido, string, max 255, único
- `name`: requerido, string, max 255
- `slug`: opcional, string, max 255, único
- `description`: opcional, string
- `is_active`: opcional, boolean
- `order`: opcional, integer

---

### Años Académicos (AcademicYears)

| Form Request | Descripción |
|-------------|-------------|
| `StoreAcademicYearRequest` | Validación para crear años académicos |
| `UpdateAcademicYearRequest` | Validación para actualizar años académicos |

**Campos validados:**
- `year`: requerido, string, formato YYYY-YYYY (regex), único
- `start_date`: requerido, fecha
- `end_date`: requerido, fecha, posterior a start_date
- `is_current`: opcional, boolean

---

### Convocatorias (Calls)

| Form Request | Descripción |
|-------------|-------------|
| `StoreCallRequest` | Validación para crear convocatorias |
| `UpdateCallRequest` | Validación para actualizar convocatorias |
| `PublishCallRequest` | Validación para publicar convocatorias |

**Campos validados:**
- `program_id`: requerido, exists en programs
- `academic_year_id`: requerido, exists en academic_years
- `title`: requerido, string, max 255
- `slug`: opcional, string, max 255, único
- `type`: requerido, enum ['alumnado', 'personal']
- `modality`: requerido, enum ['corta', 'larga']
- `number_of_places`: requerido, integer, min 1
- `destinations`: requerido, array de strings
- `estimated_start_date`: opcional, fecha
- `estimated_end_date`: opcional, fecha, posterior a estimated_start_date
- `requirements`: opcional, string
- `documentation`: opcional, string
- `selection_criteria`: opcional, string
- `scoring_table`: opcional, array
- `status`: opcional, enum ['borrador', 'abierta', 'cerrada', 'en_baremacion', 'resuelta', 'archivada']
- `published_at`: opcional, fecha
- `closed_at`: opcional, fecha

---

### Fases de Convocatoria (CallPhases)

| Form Request | Descripción |
|-------------|-------------|
| `StoreCallPhaseRequest` | Validación para crear fases |
| `UpdateCallPhaseRequest` | Validación para actualizar fases |

**Campos validados:**
- `call_id`: requerido, exists en calls
- `phase_type`: requerido, enum ['solicitud', 'documentacion', 'baremacion', 'entrevista', 'resolucion_provisional', 'alegaciones', 'resolucion_definitiva']
- `title`: requerido, string, max 255
- `description`: opcional, string
- `start_date`: requerido, fecha
- `end_date`: requerido, fecha, posterior a start_date
- `order`: opcional, integer
- `is_active`: opcional, boolean

---

### Resoluciones (Resolutions)

| Form Request | Descripción |
|-------------|-------------|
| `StoreResolutionRequest` | Validación para crear resoluciones |
| `UpdateResolutionRequest` | Validación para actualizar resoluciones |

**Campos validados:**
- `call_id`: requerido, exists en calls
- `call_phase_id`: opcional, exists en call_phases
- `title`: requerido, string, max 255
- `slug`: opcional, string, max 255, único
- `type`: requerido, enum ['provisional', 'definitiva', 'alegaciones', 'renuncia', 'adjudicacion']
- `content`: opcional, string
- `evaluation_procedure`: opcional, string
- `official_date`: opcional, fecha
- `published_at`: opcional, fecha
- `status`: opcional, enum ['borrador', 'publicado', 'archivado']

---

### Noticias (NewsPosts)

| Form Request | Descripción |
|-------------|-------------|
| `StoreNewsPostRequest` | Validación para crear noticias |
| `UpdateNewsPostRequest` | Validación para actualizar noticias |

**Campos validados:**
- `program_id`: opcional, exists en programs
- `academic_year_id`: requerido, exists en academic_years
- `title`: requerido, string, max 255
- `slug`: opcional, string, max 255, único
- `excerpt`: opcional, string
- `content`: requerido, string
- `country`: opcional, string, max 255
- `city`: opcional, string, max 255
- `host_entity`: opcional, string, max 255
- `mobility_type`: opcional, enum ['alumnado', 'personal']
- `mobility_category`: opcional, enum ['FCT', 'job_shadowing', 'intercambio', 'curso', 'otro']
- `status`: opcional, enum ['borrador', 'en_revision', 'publicado', 'archivado']
- `published_at`: opcional, fecha
- `author_id`: opcional, exists en users
- `reviewed_by`: opcional, exists en users
- `reviewed_at`: opcional, fecha

---

### Etiquetas de Noticias (NewsTags)

| Form Request | Descripción |
|-------------|-------------|
| `StoreNewsTagRequest` | Validación para crear etiquetas |

**Campos validados:**
- `name`: requerido, string, max 255, único
- `slug`: opcional, string, max 255, único

---

### Documentos (Documents)

| Form Request | Descripción |
|-------------|-------------|
| `StoreDocumentRequest` | Validación para crear documentos |
| `UpdateDocumentRequest` | Validación para actualizar documentos |

**Campos validados:**
- `category_id`: requerido, exists en document_categories
- `program_id`: opcional, exists en programs
- `academic_year_id`: opcional, exists en academic_years
- `title`: requerido, string, max 255
- `slug`: opcional, string, max 255, único
- `description`: opcional, string
- `document_type`: requerido, enum ['convocatoria', 'modelo', 'seguro', 'consentimiento', 'guia', 'faq', 'otro']
- `version`: opcional, string, max 255
- `is_active`: opcional, boolean
- `created_by`: opcional, exists en users
- `updated_by`: opcional, exists en users

---

### Categorías de Documentos (DocumentCategories)

| Form Request | Descripción |
|-------------|-------------|
| `StoreDocumentCategoryRequest` | Validación para crear categorías |

**Campos validados:**
- `name`: requerido, string, max 255
- `slug`: opcional, string, max 255, único
- `description`: opcional, string
- `order`: opcional, integer

---

### Eventos (ErasmusEvents)

| Form Request | Descripción |
|-------------|-------------|
| `StoreErasmusEventRequest` | Validación para crear eventos |
| `UpdateErasmusEventRequest` | Validación para actualizar eventos |

**Campos validados:**
- `program_id`: opcional, exists en programs
- `call_id`: opcional, exists en calls
- `title`: requerido, string, max 255
- `description`: opcional, string
- `event_type`: requerido, enum ['apertura', 'cierre', 'entrevista', 'publicacion_provisional', 'publicacion_definitivo', 'reunion_informativa', 'otro']
- `start_date`: requerido, fecha
- `end_date`: opcional, fecha, posterior a start_date
- `location`: opcional, string, max 255
- `is_public`: opcional, boolean
- `created_by`: opcional, exists en users

---

### Usuarios (Users)

| Form Request | Descripción |
|-------------|-------------|
| `StoreUserRequest` | Validación para crear usuarios |
| `UpdateUserRequest` | Validación para actualizar usuarios |
| `AssignRoleRequest` | Validación para asignar roles |

**Campos validados en Store/Update:**
- `name`: requerido, string, max 255
- `email`: requerido, string, email válido, max 255, único
- `password`: requerido (store) / opcional (update), Password::defaults(), confirmed

**Campos validados en AssignRole:**
- `roles`: requerido, array
- `roles.*`: string, debe ser uno de los roles válidos del sistema (usa `App\Support\Roles::all()`)

---

## Internacionalización

Todos los mensajes de error están traducidos en dos idiomas:

- **Español**: `lang/es/validation.php`
- **Inglés**: `lang/en/validation.php`

### Estructura de traducciones

Los mensajes personalizados se encuentran en el array `custom` de cada archivo de validación:

```php
'custom' => [
    'code' => [
        'required' => 'El código del programa es obligatorio.',
        'unique' => 'Ya existe un programa con este código.',
        // ...
    ],
    // ...
],
```

Los nombres de atributos personalizados están en el array `attributes`:

```php
'attributes' => [
    'code' => 'código del programa',
    'name' => 'nombre del programa',
    // ...
],
```

---

## Características Comunes

### Autorización

Todos los Form Requests tienen `authorize()` retornando `true`. La autorización real se manejará a través de Policies (Paso 3.3 de la planificación).

### Validación Unique con Ignore

Los Form Requests de tipo Update implementan la validación `unique` ignorando el registro actual mediante route model binding:

```php
public function rules(): array
{
    $programId = $this->route('program');
    if ($programId instanceof Program) {
        $programId = $programId->id;
    }

    return [
        'code' => ['required', 'string', 'max:255', Rule::unique('programs', 'code')->ignore($programId)],
        // ...
    ];
}
```

### Validación de Contraseñas

Los Form Requests de usuarios utilizan `Password::defaults()` para aplicar las reglas de contraseña configuradas en Laravel, junto con la validación `confirmed` para requerir confirmación.

---

## Testing

### Cobertura de Tests

Todos los Form Requests tienen **100% de cobertura** mediante tests dedicados ubicados en `tests/Feature/Http/Requests/`.

**Estadísticas de cobertura:**
- **Total de Form Requests**: 30
- **Cobertura total**: 100% (1072/1072 líneas)
- **Tests creados/mejorados**: 538 tests
- **Total de assertions**: 1,391

### Estructura de Tests

Cada Form Request tiene un archivo de test dedicado que sigue el siguiente patrón:

```php
<?php

use App\Http\Requests\{FormRequest};
use App\Models\{Model};
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup de permisos y roles
});

describe('{FormRequest} - Authorization', function () {
    // Tests de autorización
});

describe('{FormRequest} - Validation Rules', function () {
    // Tests de reglas de validación
});

describe('{FormRequest} - Custom Messages', function () {
    // Tests de mensajes personalizados
});
```

### Categorías de Tests

#### 1. Tests de Autorización

Cubren el método `authorize()` verificando:
- Usuarios con permisos específicos pueden realizar la acción
- Usuarios con rol SUPER_ADMIN pueden realizar la acción
- Usuarios sin permisos no pueden realizar la acción
- Usuarios no autenticados no pueden realizar la acción
- Casos edge: parámetros de ruta que no son instancias del modelo esperado o son null

#### 2. Tests de Validación

Cubren el método `rules()` verificando:
- Campos requeridos
- Tipos de datos (string, integer, boolean, array, date, file)
- Longitudes máximas/mínimas
- Validaciones `unique` (con y sin `ignore`)
- Validaciones `exists` en tablas relacionadas
- Validaciones `enum` con `Rule::in()`
- Validaciones personalizadas (closures)
- Validaciones de archivos (mimes, tamaño máximo)
- Validaciones de arrays y elementos anidados
- Validaciones de fechas (`after`, `before`)
- Validaciones de contraseñas (`Password::defaults()`, `confirmed`)

#### 3. Tests de Mensajes Personalizados

Cubren el método `messages()` verificando:
- Que todos los mensajes personalizados estén definidos
- Que las claves de mensajes coincidan con las reglas de validación

### Casos Especiales Testeados

#### Route Model Binding

Los Form Requests de tipo Update que usan route model binding tienen tests específicos para:
- Cuando el parámetro de ruta es una instancia del modelo
- Cuando el parámetro de ruta es un ID (integer)
- Cuando el parámetro de ruta no es del tipo esperado
- Cuando el parámetro de ruta es null

#### Validaciones Personalizadas

Los Form Requests con validaciones personalizadas (closures) tienen tests para:
- Todas las ramas de lógica condicional
- Casos `default` en expresiones `match`
- Validaciones que dependen de otros campos del request

#### Métodos Especiales

- **`prepareForValidation()`**: Tests específicos para la manipulación de datos antes de la validación
- **`withValidator()`**: Tests específicos para validaciones adicionales después de las reglas básicas
- **`attributes()`**: Tests específicos para nombres de atributos personalizados

### Ejecución de Tests

Para ejecutar todos los tests de Form Requests:

```bash
php artisan test --filter=RequestTest
```

Para ejecutar tests de un Form Request específico:

```bash
php artisan test --filter=StoreProgramRequestTest
```

### Generación de Cobertura

Para generar el reporte de cobertura en formato HTML:

```bash
php artisan test --coverage-html=tests/coverage
```

El reporte se genera en `tests/coverage/Http/Requests/index.html` y muestra:
- Cobertura por Form Request
- Líneas cubiertas vs no cubiertas
- Métodos cubiertos vs no cubiertos
- Clases cubiertas vs no cubiertas
