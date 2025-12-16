# Tests de Relaciones de Modelos

Este documento describe los tests automatizados que verifican las relaciones Eloquent definidas en los modelos de la aplicación.

## Estado del Proyecto

✅ **COMPLETADO** - Todos los tests de relaciones de modelos han sido implementados y están pasando correctamente.

## Resumen Ejecutivo

Se han creado **113 tests** con **209 assertions** que verifican todas las relaciones Eloquent definidas en los 18 modelos principales de la aplicación. Todos los tests pasan correctamente tanto individualmente como cuando se ejecutan todos juntos.

### Estadísticas

- **Total de tests**: 113
- **Total de assertions**: 209
- **Tasa de éxito**: 100%
- **Duración promedio**: ~3.26 segundos
- **Modelos testeados**: 18

## Estructura de Tests

Los tests están organizados en la carpeta `tests/Feature/Models/` siguiendo la convención de nombrar cada archivo con el nombre del modelo seguido de `Test.php`.

```
tests/Feature/Models/
├── AcademicYearTest.php          ✅ 8 tests, 18 assertions
├── AuditLogTest.php              ✅ 6 tests, 12 assertions
├── CallApplicationTest.php       ✅ 2 tests, 3 assertions
├── CallPhaseTest.php             ✅ 4 tests, 6 assertions
├── CallTest.php                  ✅ 15 tests, 29 assertions
├── DocumentCategoryTest.php      ✅ 4 tests, 8 assertions
├── DocumentTest.php              ✅ 14 tests, 28 assertions
├── ErasmusEventTest.php          ✅ 9 tests, 15 assertions
├── LanguageTest.php              ✅ 2 tests, 4 assertions
├── MediaConsentTest.php          ✅ 3 tests, 5 assertions
├── NewsPostTest.php              ✅ 14 tests, 28 assertions
├── NewsTagTest.php               ✅ 4 tests, 8 assertions
├── NewsletterSubscriptionTest.php ✅ 4 tests, 8 assertions
├── NotificationTest.php          ✅ 3 tests, 5 assertions
├── ProgramTest.php               ✅ 6 tests, 14 assertions
├── ResolutionTest.php            ✅ 7 tests, 11 assertions
├── SettingTest.php               ✅ 3 tests, 5 assertions
└── TranslationTest.php           ✅ 5 tests, 9 assertions
```

## Fases de Implementación

### Fase 1: Modelos Principales (Estructura Base) ✅

#### ProgramTest
**Relaciones verificadas:**
- `calls()` - HasMany → Call
- `newsPosts()` - HasMany → NewsPost

**Tests implementados:**
- ✅ Un programa puede tener múltiples convocatorias
- ✅ Un programa puede tener múltiples noticias
- ✅ Las convocatorias se eliminan en cascada al eliminar el programa
- ✅ Las noticias mantienen el historial cuando se elimina el programa (nullOnDelete)
- ✅ Un programa puede tener convocatorias de diferentes años académicos
- ✅ Un programa puede tener noticias de diferentes años académicos

#### AcademicYearTest
**Relaciones verificadas:**
- `calls()` - HasMany → Call
- `newsPosts()` - HasMany → NewsPost
- `documents()` - HasMany → Document

**Tests implementados:**
- ✅ Un año académico puede tener múltiples convocatorias
- ✅ Un año académico puede tener múltiples noticias
- ✅ Un año académico puede tener múltiples documentos
- ✅ Las convocatorias se eliminan en cascada al eliminar el año académico
- ✅ Las noticias se eliminan en cascada al eliminar el año académico
- ✅ Los documentos mantienen el historial cuando se elimina el año académico (nullOnDelete)
- ✅ Un año académico puede tener convocatorias de diferentes programas
- ✅ Un año académico puede tener noticias de diferentes programas

### Fase 2: Sistema de Convocatorias ✅

#### CallTest
**Relaciones verificadas:**
- `program()` - BelongsTo → Program
- `academicYear()` - BelongsTo → AcademicYear
- `creator()` - BelongsTo → User (created_by, nullable)
- `updater()` - BelongsTo → User (updated_by, nullable)
- `phases()` - HasMany → CallPhase (ordenado por `order`)
- `applications()` - HasMany → CallApplication
- `resolutions()` - HasMany → Resolution

**Tests implementados:**
- ✅ Una convocatoria pertenece a un programa
- ✅ Una convocatoria pertenece a un año académico
- ✅ Una convocatoria tiene un creador (nullable)
- ✅ Una convocatoria tiene un actualizador (nullable)
- ✅ Una convocatoria puede tener múltiples fases
- ✅ Las fases se ordenan correctamente por el campo `order`
- ✅ Una convocatoria puede tener múltiples solicitudes
- ✅ Una convocatoria puede tener múltiples resoluciones
- ✅ Las fases se eliminan en cascada al eliminar la convocatoria
- ✅ Las solicitudes se eliminan en cascada al eliminar la convocatoria
- ✅ Las resoluciones se eliminan en cascada al eliminar la convocatoria
- ✅ Al eliminar el usuario creador, la convocatoria mantiene el historial (nullOnDelete)
- ✅ Al eliminar el usuario actualizador, la convocatoria mantiene el historial (nullOnDelete)

#### CallPhaseTest
**Relaciones verificadas:**
- `call()` - BelongsTo → Call
- `resolutions()` - HasMany → Resolution

**Tests implementados:**
- ✅ Una fase pertenece a una convocatoria
- ✅ Una fase puede tener múltiples resoluciones
- ✅ Las resoluciones se eliminan en cascada al eliminar la fase
- ✅ La fase se elimina en cascada al eliminar la convocatoria

#### CallApplicationTest
**Relaciones verificadas:**
- `call()` - BelongsTo → Call

**Tests implementados:**
- ✅ Una solicitud pertenece a una convocatoria
- ✅ La solicitud se elimina en cascada al eliminar la convocatoria

#### ResolutionTest
**Relaciones verificadas:**
- `call()` - BelongsTo → Call
- `callPhase()` - BelongsTo → CallPhase
- `creator()` - BelongsTo → User (created_by, nullable)

**Tests implementados:**
- ✅ Una resolución pertenece a una convocatoria
- ✅ Una resolución pertenece a una fase
- ✅ Una resolución tiene un creador (nullable)
- ✅ La resolución se elimina en cascada al eliminar la convocatoria
- ✅ La resolución se elimina en cascada al eliminar la fase
- ✅ Al eliminar el usuario creador, la resolución mantiene el historial (nullOnDelete)

### Fase 3: Sistema de Contenido ✅

#### NewsPostTest
**Relaciones verificadas:**
- `program()` - BelongsTo → Program (nullable)
- `academicYear()` - BelongsTo → AcademicYear
- `author()` - BelongsTo → User (author_id, nullable)
- `reviewer()` - BelongsTo → User (reviewed_by, nullable)
- `tags()` - BelongsToMany → NewsTag (tabla pivot: `news_post_tag`)

**Tests implementados:**
- ✅ Una noticia puede pertenecer a un programa (opcional)
- ✅ Una noticia pertenece a un año académico
- ✅ Una noticia tiene un autor (nullable)
- ✅ Una noticia puede tener un revisor (nullable)
- ✅ Una noticia puede tener múltiples etiquetas
- ✅ Las etiquetas se pueden asociar y desasociar correctamente
- ✅ Al eliminar el programa, las noticias mantienen el historial (nullOnDelete)
- ✅ Al eliminar el año académico, se eliminan las noticias en cascada
- ✅ Al eliminar el usuario autor, la noticia mantiene el historial (nullOnDelete)
- ✅ Al eliminar el usuario revisor, la noticia mantiene el historial (nullOnDelete)
- ✅ Al eliminar una noticia, se eliminan las relaciones pivot con las etiquetas

#### NewsTagTest
**Relaciones verificadas:**
- `newsPosts()` - BelongsToMany → NewsPost (tabla pivot: `news_post_tag`)

**Tests implementados:**
- ✅ Una etiqueta puede estar asociada a múltiples noticias
- ✅ Las noticias se pueden asociar y desasociar correctamente
- ✅ Al eliminar una etiqueta, se eliminan las relaciones pivot
- ✅ Al eliminar una noticia, se eliminan las relaciones pivot

#### DocumentCategoryTest
**Relaciones verificadas:**
- `documents()` - HasMany → Document (clave foránea: `category_id`)

**Tests implementados:**
- ✅ Una categoría puede tener múltiples documentos
- ✅ Al eliminar la categoría, se eliminan los documentos en cascada
- ✅ Una categoría puede tener documentos de diferentes programas
- ✅ Una categoría puede tener documentos sin programa asociado

#### DocumentTest
**Relaciones verificadas:**
- `category()` - BelongsTo → DocumentCategory
- `program()` - BelongsTo → Program (nullable)
- `academicYear()` - BelongsTo → AcademicYear (nullable)
- `creator()` - BelongsTo → User (created_by, nullable)
- `updater()` - BelongsTo → User (updated_by, nullable)

**Tests implementados:**
- ✅ Un documento pertenece a una categoría
- ✅ Un documento puede pertenecer a un programa (opcional)
- ✅ Un documento puede pertenecer a un año académico (opcional)
- ✅ Un documento tiene un creador (nullable)
- ✅ Un documento tiene un actualizador (nullable)
- ✅ Al eliminar la categoría, se eliminan los documentos en cascada
- ✅ Al eliminar el programa, los documentos mantienen el historial (nullOnDelete)
- ✅ Al eliminar el año académico, los documentos mantienen el historial (nullOnDelete)
- ✅ Al eliminar el usuario creador, el documento mantiene el historial (nullOnDelete)
- ✅ Al eliminar el usuario actualizador, el documento mantiene el historial (nullOnDelete)

### Fase 4: Sistema (Auditoría, Notificaciones, etc.) ✅

#### AuditLogTest
**Relaciones verificadas:**
- `user()` - BelongsTo → User (nullable)
- `model()` - MorphTo (polimórfico)

**Tests implementados:**
- ✅ Un log puede tener un usuario (nullable)
- ✅ Un log puede referenciar cualquier modelo (polimórfico)
- ✅ Un log puede referenciar diferentes tipos de modelos
- ✅ Al eliminar el usuario, el log mantiene el historial (nullOnDelete)
- ✅ El log mantiene la referencia al modelo aunque este haya sido eliminado

#### NotificationTest
**Relaciones verificadas:**
- `user()` - BelongsTo → User

**Tests implementados:**
- ✅ Una notificación pertenece a un usuario
- ✅ Al eliminar el usuario, se eliminan las notificaciones en cascada
- ✅ Un usuario puede tener múltiples notificaciones

#### ErasmusEventTest
**Relaciones verificadas:**
- `program()` - BelongsTo → Program (nullable)
- `call()` - BelongsTo → Call (nullable)
- `creator()` - BelongsTo → User (created_by, nullable)

**Tests implementados:**
- ✅ Un evento puede pertenecer a un programa (opcional)
- ✅ Un evento puede pertenecer a una convocatoria (opcional)
- ✅ Un evento tiene un creador (nullable)
- ✅ Al eliminar el programa, los eventos mantienen el historial (nullOnDelete)
- ✅ Al eliminar la convocatoria, los eventos mantienen el historial (nullOnDelete)
- ✅ Al eliminar el usuario creador, el evento mantiene el historial (nullOnDelete)

#### LanguageTest
**Relaciones verificadas:**
- `translations()` - HasMany → Translation

**Tests implementados:**
- ✅ Un idioma puede tener múltiples traducciones
- ✅ Al eliminar el idioma, se eliminan las traducciones en cascada

#### TranslationTest
**Relaciones verificadas:**
- `language()` - BelongsTo → Language
- `translatable()` - MorphTo (polimórfico)

**Tests implementados:**
- ✅ Una traducción pertenece a un idioma
- ✅ Una traducción puede referenciar cualquier modelo (polimórfico)
- ✅ Una traducción puede referenciar diferentes tipos de modelos
- ✅ Al eliminar el idioma, se eliminan las traducciones en cascada
- ✅ La traducción mantiene la referencia al modelo aunque este haya sido eliminado

#### SettingTest
**Relaciones verificadas:**
- `updater()` - BelongsTo → User (updated_by, nullable)

**Tests implementados:**
- ✅ Un setting puede tener un actualizador (nullable)
- ✅ Al eliminar el usuario actualizador, el setting mantiene el historial (nullOnDelete)

#### MediaConsentTest
**Relaciones verificadas:**
- `consentDocument()` - BelongsTo → Document (consent_document_id, nullable)

**Tests implementados:**
- ✅ Un consentimiento puede tener un documento asociado (nullable)
- ✅ Al eliminar el documento, el consentimiento mantiene el historial (nullOnDelete)

#### NewsletterSubscriptionTest
**Relaciones verificadas:**
- Ninguna relación directa (solo campos JSON)

**Tests implementados:**
- ✅ El campo `programs` se almacena como JSON correctamente
- ✅ El campo `programs` puede ser null
- ✅ El campo `programs` puede ser un array vacío
- ✅ Se puede suscribir y cancelar suscripción correctamente

## Correcciones Realizadas en Modelos

Durante la implementación de los tests, se identificaron y corrigieron algunos problemas en las relaciones de los modelos:

### NewsPost
**Problema**: La relación `tags()` no especificaba el nombre de la tabla pivot.
**Solución**: Se especificó explícitamente la tabla pivot `news_post_tag`:
```php
public function tags(): BelongsToMany
{
    return $this->belongsToMany(NewsTag::class, 'news_post_tag');
}
```

### NewsTag
**Problema**: La relación `newsPosts()` no especificaba el nombre de la tabla pivot.
**Solución**: Se especificó explícitamente la tabla pivot `news_post_tag`:
```php
public function newsPosts(): BelongsToMany
{
    return $this->belongsToMany(NewsPost::class, 'news_post_tag');
}
```

### DocumentCategory
**Problema**: La relación `documents()` no especificaba la clave foránea.
**Solución**: Se especificó explícitamente la clave foránea `category_id`:
```php
public function documents(): HasMany
{
    return $this->hasMany(Document::class, 'category_id');
}
```

## Estrategias de Aislamiento de Tests

Para garantizar que todos los tests puedan ejecutarse juntos sin conflictos, se implementaron las siguientes estrategias:

### 1. Uso de RefreshDatabase
Todos los tests de Feature utilizan automáticamente `RefreshDatabase` (configurado en `tests/Pest.php`), lo que garantiza que cada test tenga una base de datos limpia.

### 2. Valores Únicos en Tests
Los tests que crean datos con restricciones de unicidad (como `programs.slug` o `academic_years.year`) utilizan valores únicos específicos para evitar conflictos:

```php
// Ejemplo de uso de valores únicos
$program = Program::factory()->create([
    'code' => 'KA999',
    'name' => 'Programa Test',
    'slug' => 'programa-test'
]);
```

### 3. Factories con Datos Únicos
Los factories utilizan `fake()->unique()` cuando es necesario para generar valores únicos automáticamente.

## Convenciones Seguidas

- ✅ Todos los tests utilizan Pest PHP v4
- ✅ Se utilizan factories para crear datos de prueba
- ✅ Se verifican relaciones bidireccionales cuando es posible
- ✅ Se testean comportamientos de cascada (cascadeOnDelete, nullOnDelete)
- ✅ Se testean relaciones polimórficas cuando aplican
- ✅ Se testean relaciones many-to-many con tablas pivot
- ✅ Los nombres de los tests son descriptivos y siguen el patrón: "it [descripción del comportamiento]"

## Ejecución de Tests

### Ejecutar todos los tests de modelos
```bash
php artisan test tests/Feature/Models/
```

### Ejecutar un test específico
```bash
php artisan test tests/Feature/Models/ProgramTest.php
```

### Ejecutar con filtro
```bash
php artisan test --filter="belongs to a program"
```

### Ejecutar con parada en el primer fallo
```bash
php artisan test tests/Feature/Models/ --stop-on-failure
```

## Resultados

Todos los tests pasan correctamente tanto individualmente como cuando se ejecutan todos juntos:

```
Tests:    113 passed (209 assertions)
Duration: 3.26s
```

✅ **Verificado**: Todos los tests pasan correctamente al ejecutarlos todos juntos sin conflictos.

## Próximos Pasos

- [ ] Implementar tests de funcionalidad de modelos (métodos personalizados, scopes, etc.)
- [ ] Implementar tests de validación de modelos
- [ ] Implementar tests de eventos de modelos (observers, events)
- [ ] Implementar tests de acceso a atributos (accessors, mutators)

## Referencias

- [Plan de Trabajo Original](models-testing-plan.md)
- [Documentación de Migraciones](migrations-overview.md)
- [Pest PHP Documentation](https://pestphp.com/docs)

