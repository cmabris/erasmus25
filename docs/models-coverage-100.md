# Cobertura 100% - Modelos Eloquent

Este documento detalla el proceso y los resultados de alcanzar el **100% de cobertura de código** en todos los modelos Eloquent de la aplicación.

## Objetivo Alcanzado

✅ **100% de cobertura de código en todos los modelos**

- **Líneas**: 165/165 (100%)
- **Métodos**: 66/66 (100%)
- **Clases**: 19/19 (100%)

## Proceso de Mejora

### Fase 1: Tests de Relaciones (Completado Anteriormente)
Se implementaron tests para todas las relaciones Eloquent definidas en los modelos, alcanzando una cobertura inicial del **96.36%**.

### Fase 2: Identificación de Código No Cubierto

Se identificaron las siguientes áreas sin cobertura:

1. **Eventos de Modelo (`boot()` y `creating`)**
   - Generación automática de slugs en: Call, Program, NewsTag, DocumentCategory, Document, NewsPost

2. **Accessors y Mutators**
   - Modelo `Setting`: `getValueAttribute()` y `setValueAttribute()` con diferentes tipos (integer, boolean, json, string)

3. **Métodos Personalizados**
   - Modelo `User`: método `initials()` para generar iniciales desde el nombre

### Fase 3: Implementación de Tests Adicionales

#### Tests de Generación Automática de Slugs

Se añadieron 6 tests nuevos para verificar que los eventos `creating` generan slugs automáticamente cuando están vacíos:

**CallTest**
```php
it('generates slug automatically when slug is empty', function () {
    $call = Call::create([
        'title' => 'Test Call Title',
        'slug' => '', // Empty slug
        // ... otros campos requeridos
    ]);

    expect($call->slug)->toBe('test-call-title');
});
```

**ProgramTest**
```php
it('generates slug automatically when slug is empty', function () {
    $program = Program::create([
        'name' => 'Test Program Name',
        'slug' => '', // Empty slug
        // ... otros campos requeridos
    ]);

    expect($program->slug)->toBe('test-program-name');
});
```

**NewsTagTest, DocumentCategoryTest, DocumentTest, NewsPostTest**
- Tests similares para cada modelo que genera slugs automáticamente

#### Tests de Accessors y Mutators (Setting)

Se añadieron 10 tests nuevos para el modelo `Setting`:

**Accessors (`getValueAttribute`)**
- Conversión de valores integer
- Conversión de valores boolean (true/false)
- Conversión de valores JSON
- Valores string sin modificar
- Manejo del tipo por defecto

**Mutators (`setValueAttribute`)**
- Conversión de integer al guardar
- Conversión de boolean al guardar
- Conversión de JSON al guardar
- Valores string sin modificar
- Manejo del tipo por defecto

#### Tests de Métodos Personalizados (User)

Se añadieron 5 tests nuevos para el método `initials()`:

```php
it('generates initials from user name', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    expect($user->initials())->toBe('JD');
});

it('generates initials from single name', function () {
    $user = User::factory()->create(['name' => 'John']);
    expect($user->initials())->toBe('J');
});

it('generates initials from name with multiple words', function () {
    $user = User::factory()->create(['name' => 'John Michael Smith']);
    expect($user->initials())->toBe('JM');
});

it('generates initials from name with only first two words', function () {
    $user = User::factory()->create(['name' => 'María José García López']);
    expect($user->initials())->toBe('MJ');
});

it('handles empty name gracefully', function () {
    $user = User::factory()->create(['name' => '']);
    expect($user->initials())->toBe('');
});
```

## Estadísticas Finales

### Antes de la Mejora
- **Cobertura total**: 96.36% (159/165 líneas)
- **Métodos**: 90.91% (60/66 métodos)
- **Clases**: 68.42% (13/19 clases)
- **Tests**: 113 tests, 209 assertions

### Después de la Mejora
- **Cobertura total**: 100% (165/165 líneas) ✅
- **Métodos**: 100% (66/66 métodos) ✅
- **Clases**: 100% (19/19 clases) ✅
- **Tests**: 134 tests, 245 assertions

### Tests Añadidos

- **6 tests** para generación automática de slugs
- **10 tests** para accessors y mutators de Setting
- **5 tests** para método `initials()` de User
- **Total**: 21 nuevos tests, 36 nuevas assertions

## Modelos con 100% de Cobertura

1. ✅ AcademicYear (8 líneas, 4 métodos)
2. ✅ AuditLog (6 líneas, 3 métodos)
3. ✅ Call (21 líneas, 9 métodos)
4. ✅ CallApplication (5 líneas, 2 métodos)
5. ✅ CallPhase (8 líneas, 3 métodos)
6. ✅ Document (14 líneas, 7 métodos)
7. ✅ DocumentCategory (9 líneas, 3 métodos)
8. ✅ ErasmusEvent (8 líneas, 4 métodos)
9. ✅ Language (5 líneas, 2 métodos)
10. ✅ MediaConsent (7 líneas, 2 métodos)
11. ✅ NewsPost (14 líneas, 7 métodos)
12. ✅ NewsTag (6 líneas, 2 métodos)
13. ✅ NewsletterSubscription (7 líneas, 1 método)
14. ✅ Notification (5 líneas, 2 métodos)
15. ✅ Program (11 líneas, 4 métodos)
16. ✅ Resolution (7 líneas, 4 métodos)
17. ✅ Setting (13 líneas, 3 métodos)
18. ✅ Translation (2 líneas, 2 métodos)
19. ✅ User (9 líneas, 2 métodos)

## Lecciones Aprendidas

1. **Eventos de Modelo Requieren Tests Específicos**: Los eventos `creating` no se ejecutan automáticamente cuando se crean modelos con valores ya definidos. Es necesario crear modelos sin esos valores para ejercitar el código del evento.

2. **Accessors y Mutators Necesitan Ejercicio Funcional**: No basta con crear modelos mediante factories; hay que acceder y modificar los atributos para ejercitar los accessors y mutators.

3. **Métodos Personalizados Requieren Cobertura Explícita**: Los métodos personalizados como `initials()` necesitan tests específicos que cubran todos los casos posibles (nombres simples, múltiples palabras, casos límite).

4. **Cobertura de Código vs Tests de Relaciones**: Los tests de relaciones verifican la estructura, pero la cobertura completa requiere tests funcionales que ejerciten toda la lógica del modelo.

## Ejecución de Tests

```bash
# Ejecutar todos los tests de modelos
php artisan test tests/Feature/Models/

# Verificar cobertura
php artisan test --coverage-html=tests/coverage tests/Feature/Models/

# Ejecutar un test específico
php artisan test tests/Feature/Models/UserTest.php
```

## Referencias

- [Tests de Relaciones de Modelos](models-tests.md)
- [Resumen de Testing](testing-summary.md)
- [Mejora de Cobertura - Setting](setting-coverage-improvement.md)

