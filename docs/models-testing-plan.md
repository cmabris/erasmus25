# Plan de Trabajo - Tests de Relaciones de Modelos

> ✅ **ESTADO: COMPLETADO**  
> Todos los tests han sido implementados y están pasando correctamente.  
> Ver [Tests de Relaciones de Modelos](models-tests.md) para la documentación completa.

## Objetivo

Verificar que todas las relaciones Eloquent definidas en los modelos funcionan correctamente mediante tests automatizados.

## Estructura de Archivos

```
tests/Feature/Models/
├── ProgramTest.php
├── AcademicYearTest.php
├── CallTest.php
├── CallPhaseTest.php
├── CallApplicationTest.php
├── ResolutionTest.php
├── NewsPostTest.php
├── NewsTagTest.php
├── DocumentCategoryTest.php
├── DocumentTest.php
├── MediaConsentTest.php
├── ErasmusEventTest.php
├── NewsletterSubscriptionTest.php
├── NotificationTest.php
├── AuditLogTest.php
├── LanguageTest.php
├── TranslationTest.php
└── SettingTest.php
```

## Fase 1: Modelos Principales (Estructura Base)

### 1.1 ProgramTest
**Relaciones a testear:**
- ✅ `calls()` - HasMany → Call
- ✅ `newsPosts()` - HasMany → NewsPost

**Tests a implementar:**
- Un programa puede tener múltiples convocatorias
- Un programa puede tener múltiples noticias
- Las convocatorias se eliminan en cascada al eliminar el programa
- Las noticias se eliminan en cascada al eliminar el programa

### 1.2 AcademicYearTest
**Relaciones a testear:**
- ✅ `calls()` - HasMany → Call
- ✅ `newsPosts()` - HasMany → NewsPost
- ✅ `documents()` - HasMany → Document

**Tests a implementar:**
- Un año académico puede tener múltiples convocatorias
- Un año académico puede tener múltiples noticias
- Un año académico puede tener múltiples documentos
- Las relaciones se eliminan en cascada al eliminar el año académico

## Fase 2: Sistema de Convocatorias

### 2.1 CallTest
**Relaciones a testear:**
- ✅ `program()` - BelongsTo → Program
- ✅ `academicYear()` - BelongsTo → AcademicYear
- ✅ `creator()` - BelongsTo → User (created_by)
- ✅ `updater()` - BelongsTo → User (updated_by)
- ✅ `phases()` - HasMany → CallPhase
- ✅ `applications()` - HasMany → CallApplication
- ✅ `resolutions()` - HasMany → Resolution

**Tests a implementar:**
- Una convocatoria pertenece a un programa
- Una convocatoria pertenece a un año académico
- Una convocatoria tiene un creador (nullable)
- Una convocatoria tiene un actualizador (nullable)
- Una convocatoria puede tener múltiples fases
- Una convocatoria puede tener múltiples solicitudes
- Una convocatoria puede tener múltiples resoluciones
- Las fases se ordenan por el campo `order`
- Al eliminar el programa, se eliminan las convocatorias
- Al eliminar el año académico, se eliminan las convocatorias
- Al eliminar el usuario creador, la convocatoria mantiene el historial (nullOnDelete)

### 2.2 CallPhaseTest
**Relaciones a testear:**
- ✅ `call()` - BelongsTo → Call
- ✅ `resolutions()` - HasMany → Resolution

**Tests a implementar:**
- Una fase pertenece a una convocatoria
- Una fase puede tener múltiples resoluciones
- Al eliminar la convocatoria, se eliminan las fases
- Las resoluciones se eliminan en cascada al eliminar la fase

### 2.3 CallApplicationTest
**Relaciones a testear:**
- ✅ `call()` - BelongsTo → Call

**Tests a implementar:**
- Una solicitud pertenece a una convocatoria
- Al eliminar la convocatoria, se eliminan las solicitudes

### 2.4 ResolutionTest
**Relaciones a testear:**
- ✅ `call()` - BelongsTo → Call
- ✅ `callPhase()` - BelongsTo → CallPhase
- ✅ `creator()` - BelongsTo → User (created_by)

**Tests a implementar:**
- Una resolución pertenece a una convocatoria
- Una resolución pertenece a una fase
- Una resolución tiene un creador (nullable)
- Al eliminar la convocatoria, se eliminan las resoluciones
- Al eliminar la fase, se eliminan las resoluciones

## Fase 3: Sistema de Contenido

### 3.1 NewsPostTest
**Relaciones a testear:**
- ✅ `program()` - BelongsTo → Program (nullable)
- ✅ `academicYear()` - BelongsTo → AcademicYear
- ✅ `author()` - BelongsTo → User (author_id, nullable)
- ✅ `reviewer()` - BelongsTo → User (reviewed_by, nullable)
- ✅ `tags()` - BelongsToMany → NewsTag

**Tests a implementar:**
- Una noticia puede pertenecer a un programa (opcional)
- Una noticia pertenece a un año académico
- Una noticia tiene un autor (nullable)
- Una noticia puede tener un revisor (nullable)
- Una noticia puede tener múltiples etiquetas
- Las etiquetas se pueden asociar y desasociar
- Al eliminar el año académico, se eliminan las noticias
- Al eliminar el programa, las noticias mantienen el historial (nullOnDelete)

### 3.2 NewsTagTest
**Relaciones a testear:**
- ✅ `newsPosts()` - BelongsToMany → NewsPost

**Tests a implementar:**
- Una etiqueta puede estar asociada a múltiples noticias
- Las noticias se pueden asociar y desasociar
- Al eliminar una noticia, se elimina la relación pivot
- Al eliminar una etiqueta, se eliminan las relaciones pivot

### 3.3 DocumentCategoryTest
**Relaciones a testear:**
- ✅ `documents()` - HasMany → Document

**Tests a implementar:**
- Una categoría puede tener múltiples documentos
- Al eliminar la categoría, se eliminan los documentos

### 3.4 DocumentTest
**Relaciones a testear:**
- ✅ `category()` - BelongsTo → DocumentCategory
- ✅ `program()` - BelongsTo → Program (nullable)
- ✅ `academicYear()` - BelongsTo → AcademicYear (nullable)
- ✅ `creator()` - BelongsTo → User (created_by, nullable)
- ✅ `updater()` - BelongsTo → User (updated_by, nullable)

**Tests a implementar:**
- Un documento pertenece a una categoría
- Un documento puede pertenecer a un programa (opcional)
- Un documento puede pertenecer a un año académico (opcional)
- Un documento tiene un creador (nullable)
- Un documento tiene un actualizador (nullable)
- Al eliminar la categoría, se eliminan los documentos
- Al eliminar el programa, los documentos mantienen el historial (nullOnDelete)

## Fase 4: Sistema (Auditoría, Notificaciones, etc.)

### 4.1 AuditLogTest
**Relaciones a testear:**
- ✅ `user()` - BelongsTo → User (nullable)
- ✅ `model()` - MorphTo (polimórfico)

**Tests a implementar:**
- Un log puede tener un usuario (nullable)
- Un log puede referenciar cualquier modelo (polimórfico)
- Al eliminar el usuario, el log mantiene el historial (nullOnDelete)

### 4.2 NotificationTest
**Relaciones a testear:**
- ✅ `user()` - BelongsTo → User

**Tests a implementar:**
- Una notificación pertenece a un usuario
- Al eliminar el usuario, se eliminan las notificaciones

### 4.3 ErasmusEventTest
**Relaciones a testear:**
- ✅ `program()` - BelongsTo → Program (nullable)
- ✅ `call()` - BelongsTo → Call (nullable)
- ✅ `creator()` - BelongsTo → User (created_by, nullable)

**Tests a implementar:**
- Un evento puede pertenecer a un programa (opcional)
- Un evento puede pertenecer a una convocatoria (opcional)
- Un evento tiene un creador (nullable)
- Al eliminar el programa, los eventos mantienen el historial (nullOnDelete)

### 4.4 LanguageTest
**Relaciones a testear:**
- ✅ `translations()` - HasMany → Translation

**Tests a implementar:**
- Un idioma puede tener múltiples traducciones
- Al eliminar el idioma, se eliminan las traducciones

### 4.5 TranslationTest
**Relaciones a testear:**
- ✅ `language()` - BelongsTo → Language
- ✅ `translatable()` - MorphTo (polimórfico)

**Tests a implementar:**
- Una traducción pertenece a un idioma
- Una traducción puede referenciar cualquier modelo (polimórfico)
- Al eliminar el idioma, se eliminan las traducciones

### 4.6 SettingTest
**Relaciones a testear:**
- ✅ `updater()` - BelongsTo → User (updated_by, nullable)

**Tests a implementar:**
- Un setting puede tener un actualizador (nullable)
- Al eliminar el usuario, el setting mantiene el historial (nullOnDelete)

### 4.7 MediaConsentTest
**Relaciones a testear:**
- ✅ `consentDocument()` - BelongsTo → Document (consent_document_id, nullable)

**Tests a implementar:**
- Un consentimiento puede tener un documento asociado (nullable)
- Al eliminar el documento, el consentimiento mantiene el historial (nullOnDelete)

### 4.8 NewsletterSubscriptionTest
**Relaciones a testear:**
- Ninguna relación directa (solo campos JSON)

**Tests a implementar:**
- El campo `programs` se almacena como JSON correctamente
- Se puede suscribir y cancelar suscripción

## Orden de Implementación Recomendado

1. **Fase 1**: ProgramTest, AcademicYearTest (estructura base)
2. **Fase 2**: CallTest, CallPhaseTest, CallApplicationTest, ResolutionTest (sistema de convocatorias)
3. **Fase 3**: NewsPostTest, NewsTagTest, DocumentCategoryTest, DocumentTest (contenido)
4. **Fase 4**: Resto de modelos del sistema

## Convenciones de Testing

- ✅ Usar `RefreshDatabase` trait para asegurar una base de datos limpia (configurado en `tests/Pest.php`)
- ✅ Usar factories para crear datos de prueba
- ✅ Verificar relaciones bidireccionales cuando sea posible
- ✅ Testear comportamientos de cascada (cascadeOnDelete, nullOnDelete)
- ✅ Testear relaciones polimórficas cuando aplique
- ✅ Testear relaciones many-to-many con tablas pivot
- ✅ Usar valores únicos en tests que crean datos con restricciones de unicidad para evitar conflictos

## Resultados Finales

- **Total de tests creados**: 113
- **Total de assertions**: 209
- **Tasa de éxito**: 100%
- **Duración promedio**: ~3.26 segundos
- **Modelos testeados**: 18

Todos los tests pasan correctamente tanto individualmente como cuando se ejecutan todos juntos.

## Ejemplo de Estructura de Test

```php
<?php

use App\Models\Program;
use App\Models\Call;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('belongs to a program', function () {
    $program = Program::factory()->create();
    $call = Call::factory()->create(['program_id' => $program->id]);
    
    expect($call->program)->toBeInstanceOf(Program::class)
        ->and($call->program->id)->toBe($program->id);
});

it('has many calls', function () {
    $program = Program::factory()->create();
    Call::factory()->count(3)->create(['program_id' => $program->id]);
    
    expect($program->calls)->toHaveCount(3);
});
```

