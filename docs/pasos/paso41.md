# Paso 41: Completar Cobertura de Tests para Policies (Paso 3.8.2)

**Fecha**: Enero 2026  
**Paso**: 3.8.2 - Tests de Policies  
**Estado**: ✅ COMPLETADO

---

## Resumen Ejecutivo

Este documento contiene todos los prompts del usuario y las respuestas del asistente durante el desarrollo completo del paso 3.8.2 (Tests de Policies). El trabajo se realizó en tres fases: análisis de cobertura existente, creación del plan de trabajo, e implementación de tests para alcanzar 100% de cobertura.

**Resultado Final:**
- ✅ **100% de cobertura** en todas las Policies (170/170 líneas, 118/118 métodos, 16/16 clases)
- ✅ **13 tests nuevos** implementados
- ✅ **140 tests totales** pasando (569 assertions)
- ✅ Sin errores de linting
- ✅ Documentación técnica actualizada

---

## Prompt 1: Solicitud de Análisis y Plan de Trabajo

### Prompt del Usuario:
```
En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que hace de índice y otro llamado @planificacion_pasos.md que contiene las tareas a desarrollar en orden.
Estamos en situación de seguir con el desarrollo del Paso 3.8. el cual se compone de varios pasos parciales. Con respecto a todo el paso 3.8. debes saber que acabo de generar la cobertura de la aplicación con Pest usando el flag --coverage-html para generarlo en formato HTML y está almacenado en tests/coverage2 siendo el archivo index el que nos presenta los resultados globales de cobertura de toda la aplicación.
Ahora vamos a continuar con el desarrollo del paso 3.8.2. que corresponde a los tests de Policies. Podemos encontrar un resumen de todos estos tests en tests/coverage2/Policies/index.html que está en formato tabla. La primera columna es el nombre del form request y es un enlace que lleva a otro archivo con los detalles del mismo. Hay otra columna con el porcentaje de cobertura y otra más que contiene la cantidad de líneas cubiertas del total existente. Si accedemos a la vista de un form request nos encontramos que hay lineas en color de fondo rojo claro que indica que son líneas no cubiertas por ningún test y otras con color verde claro que indica que sí están cubiertas.
Si necesitas verificar la cobertura existente, lo puedes hacer en tests/coverage2, pero para generar nueva cobertura de lo que desarrollamos, en caso de necesitarlo, lo generaremos en tests/coverage. El motivo es que cada vez que generamos con Pest una cobertura parcial, se pierde la anterior de toda la aplicación. Por tanto usaremos tests/coverage2 solo en modo lectura.
Antes de comenzar el paso 3.8.2 quiero que revises los archivos de cobertura que te he indicado y me generes un plan de trabajo para tratar de acercarnos lo más posible a una Cobertura del 100%.
```

### Respuesta del Asistente:

**Acciones realizadas:**
1. Revisión de `docs/planificacion_pasos.md` para entender el contexto del paso 3.8.2
2. Análisis del archivo `tests/coverage2/Policies/index.html` para identificar cobertura actual
3. Identificación de 2 Policies que necesitaban trabajo:
   - **ProgramPolicy**: 87.50% (14/16 líneas, 7/8 métodos)
   - **UserPolicy**: 80.95% (17/21 líneas, 7/9 métodos)
4. Análisis detallado de las líneas sin cubrir en ambas Policies
5. Revisión de los tests existentes para entender la estructura
6. Creación de plan detallado estructurado en 3 fases

**Resultado:**
- ✅ Archivo creado: `docs/pasos/paso-3.8.2-plan.md` (402 líneas)
- ✅ Plan estructurado en 3 fases:
  1. **Fase 1**: ProgramPolicy - Completar cobertura de `forceDelete()` con relaciones (3 tests)
  2. **Fase 2**: UserPolicy - Completar cobertura de `restore()` (4 tests)
  3. **Fase 3**: UserPolicy - Completar cobertura de `forceDelete()` (6 tests)

**Análisis de Cobertura Identificado:**

**ProgramPolicy (87.50% → 100%):**
- Faltaban 2 líneas en el método `forceDelete()`
- No se testaba cuando el programa tiene relaciones (calls o newsPosts)
- Líneas sin cubrir: 103 (verificación de relaciones) y 105 (retorno cuando hay relaciones)

**UserPolicy (80.95% → 100%):**
- Método `restore()`: 0% de cobertura (4 líneas sin cubrir)
- Método `forceDelete()`: 0% de cobertura (7 líneas sin cubrir)
- Total: 4 líneas en restore() + 3 líneas en forceDelete() = 7 líneas

**Total de tests necesarios**: 13 tests nuevos

---

## Prompt 2: Inicio de Fase 1 - ProgramPolicy

### Prompt del Usuario:
```
Podemos comenzar con la Fase 1
```

### Respuesta del Asistente:

**Acciones realizadas:**
1. Lectura del archivo de test existente `tests/Feature/Policies/ProgramPolicyTest.php`
2. Identificación de imports necesarios (`Call` y `NewsPost`)
3. Añadidos imports necesarios al archivo de test
4. Implementación de 4 tests nuevos para `forceDelete()` con relaciones:
   - Test: Super-admin puede hacer forceDelete sin relaciones (verificación)
   - Test: Super-admin NO puede hacer forceDelete con calls
   - Test: Super-admin NO puede hacer forceDelete con newsPosts
   - Test: Super-admin NO puede hacer forceDelete con ambos tipos de relaciones

**Resultado:**
- ✅ 4 tests nuevos añadidos en nueva sección `describe('ProgramPolicy forceDelete with relations')`
- ✅ Tests implementados llamando directamente al método de la policy (porque `before()` devuelve `true` para super-admin)
- ✅ Todos los tests pasando (13 tests totales, 50 assertions)

**Nota técnica importante:**
Como el método `before()` devuelve `true` para super-admin, `$user->can()` no ejecuta el método `forceDelete()`. Por tanto, los tests llaman directamente al método de la policy (`$policy->forceDelete()`) para testear la lógica de validación de relaciones.

**Código implementado:**
```php
describe('ProgramPolicy forceDelete with relations', function () {
    it('allows super-admin to force delete program without relations', function () {
        // ... verificación con can() y directamente con policy
    });

    it('prevents super-admin from force deleting program with calls', function () {
        $policy = new \App\Policies\ProgramPolicy;
        expect($policy->forceDelete($superAdmin, $program))->toBeFalse();
    });
    // ... más tests
});
```

---

## Prompt 3: Continuación con Fase 2 - UserPolicy restore()

### Prompt del Usuario:
```
Sigamos con la Fase 2
```

### Respuesta del Asistente:

**Acciones realizadas:**
1. Lectura del archivo de test existente `tests/Feature/Policies/UserPolicyTest.php`
2. Implementación de 4 tests nuevos para el método `restore()`:
   - Test: Super-admin puede restaurar otros usuarios
   - Test: Usuario con permiso USERS_DELETE puede restaurar otros usuarios
   - Test: Usuario sin permiso USERS_DELETE NO puede restaurar
   - Test: Usuario sin roles NO puede restaurar

**Resultado:**
- ✅ 4 tests nuevos añadidos en nueva sección `describe('UserPolicy restore access')`
- ✅ Todos los tests pasando (21 tests totales, 51 assertions)
- ✅ Método `restore()` ahora tiene cobertura completa (de 0% a 100%)

**Características destacadas:**
- Los tests verifican que el usuario esté previamente eliminado (soft delete) antes de probar restore
- Se testean todos los casos: super-admin, permisos directos, sin permisos, sin roles

**Código implementado:**
```php
describe('UserPolicy restore access', function () {
    it('allows super-admin to restore other users', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Roles::SUPER_ADMIN);
        $otherUser = User::factory()->create();
        $otherUser->delete(); // Soft delete

        expect($superAdmin->can('restore', $otherUser))->toBeTrue();
    });
    // ... más tests
});
```

---

## Prompt 4: Continuación con Fase 3 - UserPolicy forceDelete()

### Prompt del Usuario:
```
Sigamos con la Fase 3
```

### Respuesta del Asistente:

**Acciones realizadas:**
1. Lectura del método `forceDelete()` en `app/Policies/UserPolicy.php` para entender la lógica
2. Implementación de 6 tests nuevos para el método `forceDelete()`:
   - Test: Super-admin puede hacer forceDelete de otros usuarios
   - Test: Super-admin NO puede hacer forceDelete de sí mismo
   - Test: Usuario con permiso USERS_DELETE puede hacer forceDelete de otros usuarios
   - Test: Usuario con permiso USERS_DELETE NO puede hacer forceDelete de sí mismo
   - Test: Usuario sin permiso USERS_DELETE NO puede hacer forceDelete
   - Test: Usuario sin roles NO puede hacer forceDelete

**Resultado:**
- ✅ 6 tests nuevos añadidos en nueva sección `describe('UserPolicy forceDelete access')`
- ✅ Todos los tests pasando (27 tests totales, 57 assertions)
- ✅ Método `forceDelete()` ahora tiene cobertura completa (de 0% a 100%)

**Características destacadas:**
- Los tests verifican la lógica de auto-eliminación (un usuario no puede eliminarse a sí mismo)
- Para testear la lógica de auto-eliminación en super-admin, se llama directamente al método de la policy
- Se testean todos los casos: super-admin, permisos directos, sin permisos, sin roles

**Código implementado:**
```php
describe('UserPolicy forceDelete access', function () {
    it('prevents super-admin from force deleting themselves', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Roles::SUPER_ADMIN);
        $superAdmin->delete(); // Soft delete primero

        // Llamar directamente al método de la policy para testear la lógica
        $policy = new \App\Policies\UserPolicy;
        expect($policy->forceDelete($superAdmin, $superAdmin))->toBeFalse();
    });
    // ... más tests
});
```

---

## Prompt 5: Verificación de Cobertura

### Prompt del Usuario:
```
Ya lo he hecho yo y alcanza el 100% de cobertura
```

### Respuesta del Asistente:

**Acciones realizadas:**
1. Actualización de `docs/pasos/paso-3.8.2-plan.md` para marcar como completado
2. Actualización de `docs/planificacion_pasos.md` para marcar el paso 3.8.2 como completado
3. Verificación de que todos los tests pasan correctamente

**Resultado:**
- ✅ Documentación actualizada con estado de completado
- ✅ Confirmación de 100% de cobertura alcanzado
- ✅ Resumen final del trabajo realizado

---

## Resumen Final de Implementación

### Tests Implementados por Fase

#### Fase 1: ProgramPolicy - forceDelete() con relaciones
- ✅ 3 tests nuevos (más 1 test de verificación)
- ✅ Cobertura: 87.50% → 100% (16/16 líneas)
- ✅ Archivo: `tests/Feature/Policies/ProgramPolicyTest.php`

**Tests añadidos:**
1. `it('allows super-admin to force delete program without relations')` - Verificación
2. `it('prevents super-admin from force deleting program with calls')` - Nuevo
3. `it('prevents super-admin from force deleting program with newsPosts')` - Nuevo
4. `it('prevents super-admin from force deleting program with both calls and newsPosts')` - Nuevo

#### Fase 2: UserPolicy - restore()
- ✅ 4 tests nuevos
- ✅ Cobertura: 0% → 100% del método restore()
- ✅ Archivo: `tests/Feature/Policies/UserPolicyTest.php`

**Tests añadidos:**
1. `it('allows super-admin to restore other users')` - Nuevo
2. `it('allows user with USERS_DELETE permission to restore other users')` - Nuevo
3. `it('prevents user without USERS_DELETE permission from restoring users')` - Nuevo
4. `it('prevents user without roles from restoring users')` - Nuevo

#### Fase 3: UserPolicy - forceDelete()
- ✅ 6 tests nuevos
- ✅ Cobertura: 0% → 100% del método forceDelete()
- ✅ Archivo: `tests/Feature/Policies/UserPolicyTest.php`

**Tests añadidos:**
1. `it('allows super-admin to force delete other users')` - Nuevo
2. `it('prevents super-admin from force deleting themselves')` - Nuevo
3. `it('allows user with USERS_DELETE permission to force delete other users')` - Nuevo
4. `it('prevents user with USERS_DELETE permission from force deleting themselves')` - Nuevo
5. `it('prevents user without USERS_DELETE permission from force deleting users')` - Nuevo
6. `it('prevents user without roles from force deleting users')` - Nuevo

### Resultados Finales

**Cobertura:**
- ✅ **100% de líneas** (170/170)
- ✅ **100% de métodos** (118/118)
- ✅ **100% de clases** (16/16)

**Tests:**
- ✅ **13 tests nuevos** implementados
- ✅ **140 tests totales** pasando (569 assertions)
- ✅ **0 errores** de linting

**Policies completadas:**
- ✅ **ProgramPolicy**: 100% (era 87.50%)
- ✅ **UserPolicy**: 100% (era 80.95%)
- ✅ **Todas las demás**: Ya tenían 100%

### Notas Técnicas Importantes

1. **Método `before()` y testing directo:**
   - Cuando `before()` devuelve `true` para super-admin, `$user->can()` no ejecuta el método específico
   - Para testear la lógica interna, se llama directamente al método de la policy: `$policy->forceDelete($user, $model)`
   - Esto permite testear validaciones importantes como relaciones o auto-eliminación

2. **Soft Deletes en tests:**
   - Los métodos `restore()` y `forceDelete()` requieren que el modelo esté previamente eliminado
   - Se debe llamar a `$model->delete()` antes de probar estos métodos

3. **Validación de relaciones:**
   - `ProgramPolicy::forceDelete()` verifica que no haya relaciones antes de permitir eliminación permanente
   - Se testean casos con `calls()`, `newsPosts()`, y ambos tipos de relaciones

4. **Auto-eliminación:**
   - `UserPolicy::forceDelete()` previene que un usuario se elimine a sí mismo
   - Se testea tanto para super-admin como para usuarios con permisos directos

### Archivos Modificados

1. **Tests:**
   - `tests/Feature/Policies/ProgramPolicyTest.php` - Añadidos 4 tests nuevos
   - `tests/Feature/Policies/UserPolicyTest.php` - Añadidos 10 tests nuevos

2. **Documentación:**
   - `docs/pasos/paso-3.8.2-plan.md` - Plan de trabajo creado y actualizado como completado
   - `docs/planificacion_pasos.md` - Paso 3.8.2 marcado como completado
   - `docs/policies.md` - Actualizado con información sobre tests y cobertura

### Comandos Útiles

```bash
# Ejecutar todos los tests de policies
php artisan test tests/Feature/Policies/

# Ejecutar tests específicos
php artisan test --filter=ProgramPolicy
php artisan test --filter=UserPolicy

# Ejecutar con cobertura
php artisan test tests/Feature/Policies/ --coverage

# Verificar linting
vendor/bin/pint tests/Feature/Policies/
```

---

## Conclusiones

El paso 3.8.2 se completó exitosamente, alcanzando **100% de cobertura** en todas las Policies de la aplicación. Se implementaron **13 tests nuevos** que cubren casos críticos de seguridad:

- Validación de relaciones antes de eliminación permanente (ProgramPolicy)
- Restauración de usuarios eliminados (UserPolicy::restore())
- Eliminación permanente con prevención de auto-eliminación (UserPolicy::forceDelete())

Todos los tests pasan correctamente y la documentación técnica ha sido actualizada para reflejar el estado actual de cobertura y las mejores prácticas de testing de policies.

**Fecha de Finalización**: Enero 2026  
**Estado**: ✅ COMPLETADO
