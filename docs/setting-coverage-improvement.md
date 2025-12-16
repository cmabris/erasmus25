# Mejora de Cobertura - Modelo Setting

## Resumen

Se ha mejorado la cobertura de código del modelo `Setting` del **30.77%** al **100%**, añadiendo tests específicos para los accessors y mutators del atributo `value`.

## Problema Identificado

El reporte de cobertura inicial mostraba:
- **Cobertura total**: 30.77% (4/13 líneas)
- **getValueAttribute**: 0% cubierto (0/6 líneas)
- **setValueAttribute**: 50% cubierto (3/6 líneas)
- **updater**: 100% cubierto (1/1 línea)

Los tests existentes solo verificaban las relaciones, pero no probaban los accessors y mutators que convierten los valores según el tipo (`integer`, `boolean`, `json`, `string`).

## Solución Implementada

Se añadieron **10 nuevos tests** que cubren todos los casos de los accessors y mutators:

### Tests de Accessor (`getValueAttribute`)

1. **`it('casts integer value correctly when getting')`**
   - Verifica que valores almacenados como string se convierten a integer al acceder
   - Ejemplo: `'42'` → `42`

2. **`it('casts boolean value correctly when getting')`**
   - Verifica que valores almacenados como string se convierten a boolean
   - Casos: `'1'` → `true`, `'0'` → `false`

3. **`it('casts json value correctly when getting')`**
   - Verifica que valores JSON almacenados como string se decodifican a array
   - Ejemplo: `'{"key":"value"}'` → `['key' => 'value']`

4. **`it('returns string value as is when getting')`**
   - Verifica que valores string se devuelven sin modificar

5. **`it('handles default type when getting value')`**
   - Verifica el comportamiento por defecto cuando el tipo es 'string'

### Tests de Mutator (`setValueAttribute`)

6. **`it('casts integer value correctly when setting')`**
   - Verifica que valores integer se convierten a string al guardar
   - Ejemplo: `42` → `'42'`

7. **`it('casts boolean value correctly when setting')`**
   - Verifica que valores boolean se convierten a string
   - Casos: `true` → `'1'`, `false` → `'0'`

8. **`it('casts json value correctly when setting')`**
   - Verifica que arrays se codifican a JSON al guardar
   - Ejemplo: `['key' => 'value']` → `'{"key":"value"}'`

9. **`it('sets string value as is when setting')`**
   - Verifica que valores string se guardan sin modificar

10. **`it('handles default type when setting value')`**
    - Verifica el comportamiento por defecto cuando el tipo es 'string'

## Resultados

### Antes
```
Cobertura: 30.77% (4/13 líneas)
Métodos: 33.33% (1/3 métodos)
```

### Después
```
Cobertura: 100% (13/13 líneas) ✅
Métodos: 100% (3/3 métodos) ✅
```

### Estadísticas de Tests

- **Tests totales**: 13 (3 de relaciones + 10 de accessors/mutators)
- **Assertions totales**: 30
- **Todos los tests pasando**: ✅

## Código Cubierto

### Accessor `getValueAttribute`
```php
public function getValueAttribute($value): mixed
{
    return match ($this->type) {
        'integer' => (int) $value,                    // ✅ Cubierto
        'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN), // ✅ Cubierto
        'json' => json_decode($value, true),          // ✅ Cubierto
        default => $value,                            // ✅ Cubierto
    };
}
```

### Mutator `setValueAttribute`
```php
public function setValueAttribute($value)
{
    $this->attributes['value'] = match ($this->type) {
        'integer' => (string) $value,                 // ✅ Cubierto
        'boolean' => $value ? '1' : '0',             // ✅ Cubierto
        'json' => json_encode($value),                // ✅ Cubierto
        default => $value,                            // ✅ Cubierto
    };
}
```

## Ejecución de Tests

```bash
# Ejecutar solo los tests de Setting
php artisan test tests/Feature/Models/SettingTest.php

# Verificar cobertura
php artisan test --coverage-html=tests/coverage tests/Feature/Models/SettingTest.php
```

## Lecciones Aprendidas

1. **Accessors y Mutators necesitan tests específicos**: No basta con crear modelos mediante factories; hay que acceder y modificar los atributos para ejercitar los accessors y mutators.

2. **Cobertura de código vs Tests de relaciones**: Los tests de relaciones verifican la estructura, pero los accessors/mutators requieren tests funcionales que ejerciten la lógica de conversión.

3. **Importancia de probar todos los casos del match**: Cada rama del `match` statement debe ser probada individualmente para alcanzar 100% de cobertura.

## Referencias

- [Tests de Relaciones de Modelos](models-tests.md)
- [Resumen de Testing](testing-summary.md)

