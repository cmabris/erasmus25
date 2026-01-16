# Plan de Trabajo: Paso 3.8.2 - Tests de Policies

## Objetivo
Alcanzar **100% de cobertura** en todos los Policies de la aplicación.

## Estado Actual de Cobertura

### Resumen General
- **Cobertura Total de Líneas**: 96.47% (164/170 líneas)
- **Cobertura de Funciones/Métodos**: 97.46% (115/118)
- **Cobertura de Clases**: 87.50% (14/16)
- **Total de Policies**: 16

### Policies con 100% de Cobertura ✅ (14 Policies)
1. **AcademicYearPolicy** - 100% (10/10 líneas, 8/8 métodos, 1/1 clase)
2. **ActivityPolicy** - 100% (5/5 líneas, 3/3 métodos, 1/1 clase)
3. **CallPhasePolicy** - 100% (10/10 líneas, 8/8 métodos, 1/1 clase)
4. **CallPolicy** - 100% (11/11 líneas, 9/9 métodos, 1/1 clase)
5. **DocumentCategoryPolicy** - 100% (10/10 líneas, 8/8 métodos, 1/1 clase)
6. **DocumentPolicy** - 100% (10/10 líneas, 8/8 métodos, 1/1 clase)
7. **ErasmusEventPolicy** - 100% (10/10 líneas, 8/8 métodos, 1/1 clase)
8. **NewsPostPolicy** - 100% (11/11 líneas, 9/9 métodos, 1/1 clase)
9. **NewsTagPolicy** - 100% (10/10 líneas, 8/8 métodos, 1/1 clase)
10. **NewsletterSubscriptionPolicy** - 100% (7/7 líneas, 5/5 métodos, 1/1 clase)
11. **ResolutionPolicy** - 100% (11/11 líneas, 9/9 métodos, 1/1 clase)
12. **RolePolicy** - 100% (12/12 líneas, 6/6 métodos, 1/1 clase)
13. **SettingPolicy** - 100% (8/8 líneas, 6/6 métodos, 1/1 clase)
14. **TranslationPolicy** - 100% (8/8 líneas, 6/6 métodos, 1/1 clase)

### Policies que Necesitan Trabajo 🔴 (2 Policies)

#### 1. **ProgramPolicy** - 87.50% (14/16 líneas, 7/8 métodos, 0/1 clase)
**Estado**: 🟠 Cobertura Media-Alta
**Líneas sin cubrir**: 2 líneas
**Métodos sin cubrir**: 1 método parcialmente

**Análisis del código**:
```php
public function forceDelete(User $user, Program $program): bool
{
    // Solo super-admin puede hacer forceDelete
    if (! $user->hasRole(Roles::SUPER_ADMIN)) {
        return false;  // ✅ CUBIERTO
    }

    // Verificar que no tenga relaciones antes de permitir forceDelete
    $hasRelations = $program->calls()->exists() || $program->newsPosts()->exists();  // ❌ NO CUBIERTO

    return ! $hasRelations;  // ❌ NO CUBIERTO (cuando hay relaciones)
}
```

**Líneas sin cubrir**:
- **Línea 103**: Verificación de relaciones (`$hasRelations = ...`)
- **Línea 105**: Retorno cuando hay relaciones (`return ! $hasRelations;` cuando `$hasRelations = true`)

**Tests faltantes**:
1. ✅ Super-admin puede hacer forceDelete cuando NO hay relaciones (ya existe)
2. ❌ **FALTA**: Super-admin NO puede hacer forceDelete cuando SÍ hay relaciones con `calls()`
3. ❌ **FALTA**: Super-admin NO puede hacer forceDelete cuando SÍ hay relaciones con `newsPosts()`
4. ❌ **FALTA**: Super-admin NO puede hacer forceDelete cuando SÍ hay relaciones con ambos

**Prioridad**: ALTA - Es un caso de seguridad importante (validación de relaciones antes de eliminación permanente)

---

#### 2. **UserPolicy** - 80.95% (17/21 líneas, 7/9 métodos, 0/1 clase)
**Estado**: 🟠 Cobertura Media
**Líneas sin cubrir**: 4 líneas
**Métodos sin cubrir**: 2 métodos completamente

**Análisis del código**:

**Método `restore()` - 0% de cobertura**:
```php
public function restore(User $user, User $model): bool
{
    return $user->can(Permissions::USERS_DELETE);  // ❌ NO CUBIERTO
}
```

**Método `forceDelete()` - 0% de cobertura**:
```php
public function forceDelete(User $user, User $model): bool
{
    // Un usuario no puede eliminarse a sí mismo
    if ($user->id === $model->id) {  // ❌ NO CUBIERTO
        return false;  // ❌ NO CUBIERTO
    }

    return $user->can(Permissions::USERS_DELETE);  // ❌ NO CUBIERTO
}
```

**Líneas sin cubrir**:
- **Línea 91-94**: Método `restore()` completo (0% de cobertura)
- **Línea 99-107**: Método `forceDelete()` completo (0% de cobertura)
  - Línea 102: Verificación de auto-eliminación
  - Línea 103: Retorno cuando es el mismo usuario
  - Línea 106: Retorno cuando tiene permisos

**Tests faltantes**:

**Para `restore()`**:
1. ❌ **FALTA**: Super-admin puede restaurar otros usuarios
2. ❌ **FALTA**: Usuario con permiso `USERS_DELETE` puede restaurar otros usuarios
3. ❌ **FALTA**: Usuario sin permiso `USERS_DELETE` NO puede restaurar
4. ❌ **FALTA**: Usuario sin roles NO puede restaurar

**Para `forceDelete()`**:
1. ❌ **FALTA**: Super-admin puede hacer forceDelete de otros usuarios
2. ❌ **FALTA**: Super-admin NO puede hacer forceDelete de sí mismo
3. ❌ **FALTA**: Usuario con permiso `USERS_DELETE` puede hacer forceDelete de otros usuarios
4. ❌ **FALTA**: Usuario con permiso `USERS_DELETE` NO puede hacer forceDelete de sí mismo
5. ❌ **FALTA**: Usuario sin permiso `USERS_DELETE` NO puede hacer forceDelete
6. ❌ **FALTA**: Usuario sin roles NO puede hacer forceDelete

**Prioridad**: ALTA - Métodos críticos de seguridad que no están siendo testeados

---

## Plan de Implementación

### Fase 1: ProgramPolicy - Completar cobertura de `forceDelete()` con relaciones

#### Objetivo
Añadir tests para cubrir el caso donde un programa tiene relaciones (calls o newsPosts) y no se puede hacer forceDelete.

#### Tests a implementar

**1. Test: Super-admin no puede hacer forceDelete cuando programa tiene calls**
```php
it('prevents super-admin from force deleting program with calls', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $program = Program::factory()->create();
    Call::factory()->create(['program_id' => $program->id]);

    expect($superAdmin->can('forceDelete', $program))->toBeFalse();
});
```

**2. Test: Super-admin no puede hacer forceDelete cuando programa tiene newsPosts**
```php
it('prevents super-admin from force deleting program with newsPosts', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $program = Program::factory()->create();
    NewsPost::factory()->create(['program_id' => $program->id]);

    expect($superAdmin->can('forceDelete', $program))->toBeFalse();
});
```

**3. Test: Super-admin no puede hacer forceDelete cuando programa tiene ambos tipos de relaciones**
```php
it('prevents super-admin from force deleting program with both calls and newsPosts', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $program = Program::factory()->create();
    Call::factory()->create(['program_id' => $program->id]);
    NewsPost::factory()->create(['program_id' => $program->id]);

    expect($superAdmin->can('forceDelete', $program))->toBeFalse();
});
```

**4. Test: Super-admin puede hacer forceDelete cuando programa NO tiene relaciones**
```php
it('allows super-admin to force delete program without relations', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $program = Program::factory()->create();
    // No crear relaciones

    expect($superAdmin->can('forceDelete', $program))->toBeTrue();
});
```

**Archivo**: `tests/Feature/Policies/ProgramPolicyTest.php`
**Ubicación**: Añadir al final del archivo, después de los tests existentes

---

### Fase 2: UserPolicy - Completar cobertura de `restore()`

#### Objetivo
Añadir tests para cubrir completamente el método `restore()`.

#### Tests a implementar

**1. Test: Super-admin puede restaurar otros usuarios**
```php
it('allows super-admin to restore other users', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $otherUser = User::factory()->create();
    $otherUser->delete(); // Soft delete

    expect($superAdmin->can('restore', $otherUser))->toBeTrue();
});
```

**2. Test: Usuario con permiso USERS_DELETE puede restaurar otros usuarios**
```php
it('allows user with USERS_DELETE permission to restore other users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permissions::USERS_DELETE);
    $otherUser = User::factory()->create();
    $otherUser->delete(); // Soft delete

    expect($user->can('restore', $otherUser))->toBeTrue();
});
```

**3. Test: Usuario sin permiso USERS_DELETE NO puede restaurar**
```php
it('prevents user without USERS_DELETE permission from restoring users', function () {
    $user = User::factory()->create();
    $user->assignRole(Roles::ADMIN); // Admin no tiene permisos de usuarios por defecto
    $otherUser = User::factory()->create();
    $otherUser->delete(); // Soft delete

    expect($user->can('restore', $otherUser))->toBeFalse();
});
```

**4. Test: Usuario sin roles NO puede restaurar**
```php
it('prevents user without roles from restoring users', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherUser->delete(); // Soft delete

    expect($user->can('restore', $otherUser))->toBeFalse();
});
```

**Archivo**: `tests/Feature/Policies/UserPolicyTest.php`
**Ubicación**: Añadir nueva sección `describe('UserPolicy restore access', function () { ... })`

---

### Fase 3: UserPolicy - Completar cobertura de `forceDelete()`

#### Objetivo
Añadir tests para cubrir completamente el método `forceDelete()`, incluyendo el caso de auto-eliminación.

#### Tests a implementar

**1. Test: Super-admin puede hacer forceDelete de otros usuarios**
```php
it('allows super-admin to force delete other users', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $otherUser = User::factory()->create();
    $otherUser->delete(); // Soft delete primero

    expect($superAdmin->can('forceDelete', $otherUser))->toBeTrue();
});
```

**2. Test: Super-admin NO puede hacer forceDelete de sí mismo**
```php
it('prevents super-admin from force deleting themselves', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $superAdmin->delete(); // Soft delete primero

    expect($superAdmin->can('forceDelete', $superAdmin))->toBeFalse();
});
```

**3. Test: Usuario con permiso USERS_DELETE puede hacer forceDelete de otros usuarios**
```php
it('allows user with USERS_DELETE permission to force delete other users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permissions::USERS_DELETE);
    $otherUser = User::factory()->create();
    $otherUser->delete(); // Soft delete primero

    expect($user->can('forceDelete', $otherUser))->toBeTrue();
});
```

**4. Test: Usuario con permiso USERS_DELETE NO puede hacer forceDelete de sí mismo**
```php
it('prevents user with USERS_DELETE permission from force deleting themselves', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permissions::USERS_DELETE);
    $user->delete(); // Soft delete primero

    expect($user->can('forceDelete', $user))->toBeFalse();
});
```

**5. Test: Usuario sin permiso USERS_DELETE NO puede hacer forceDelete**
```php
it('prevents user without USERS_DELETE permission from force deleting users', function () {
    $user = User::factory()->create();
    $user->assignRole(Roles::ADMIN); // Admin no tiene permisos de usuarios por defecto
    $otherUser = User::factory()->create();
    $otherUser->delete(); // Soft delete primero

    expect($user->can('forceDelete', $otherUser))->toBeFalse();
});
```

**6. Test: Usuario sin roles NO puede hacer forceDelete**
```php
it('prevents user without roles from force deleting users', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherUser->delete(); // Soft delete primero

    expect($user->can('forceDelete', $otherUser))->toBeFalse();
});
```

**Archivo**: `tests/Feature/Policies/UserPolicyTest.php`
**Ubicación**: Añadir nueva sección `describe('UserPolicy forceDelete access', function () { ... })`

---

## Resumen de Tests a Implementar

### ProgramPolicy
- ✅ 1 test existente (super-admin puede hacer forceDelete sin relaciones)
- ❌ 3 tests nuevos necesarios (con relaciones)

**Total**: 3 tests nuevos

### UserPolicy
- ❌ 4 tests nuevos para `restore()`
- ❌ 6 tests nuevos para `forceDelete()`

**Total**: 10 tests nuevos

### Total General
- **Tests nuevos**: 13 tests
- **Líneas a cubrir**: 6 líneas (2 en ProgramPolicy, 4 en UserPolicy)
- **Métodos a cubrir**: 2 métodos completos (restore y forceDelete en UserPolicy) + 1 método parcial (forceDelete en ProgramPolicy)

---

## Orden de Implementación Recomendado

1. **Fase 1**: ProgramPolicy - Tests de forceDelete con relaciones (3 tests)
   - Más simple, solo un método parcial
   - Casos de negocio claros

2. **Fase 2**: UserPolicy - Tests de restore() (4 tests)
   - Método simple, solo verifica permisos
   - No tiene lógica compleja

3. **Fase 3**: UserPolicy - Tests de forceDelete() (6 tests)
   - Método más complejo con lógica de auto-eliminación
   - Requiere más casos de prueba

---

## Verificación Final

Después de implementar todos los tests:

1. **Ejecutar tests**: `php artisan test --filter=Policy`
2. **Verificar cobertura**: `php artisan test --coverage --min=100`
3. **Revisar HTML**: Abrir `tests/coverage/Policies/index.html` y verificar:
   - ProgramPolicy: 100% (16/16 líneas, 8/8 métodos, 1/1 clase)
   - UserPolicy: 100% (21/21 líneas, 9/9 métodos, 1/1 clase)
   - Total Policies: 100% (170/170 líneas, 118/118 métodos, 16/16 clases)

---

## Notas Importantes

1. **Soft Deletes**: Los métodos `restore()` y `forceDelete()` requieren que el modelo esté previamente eliminado (soft delete). Asegurarse de llamar `$model->delete()` antes de probar estos métodos.

2. **Relaciones en ProgramPolicy**: Los tests de `forceDelete()` con relaciones deben crear efectivamente las relaciones (calls o newsPosts) antes de verificar que el forceDelete está bloqueado.

3. **Auto-eliminación en UserPolicy**: El método `forceDelete()` tiene lógica especial para prevenir que un usuario se elimine a sí mismo. Los tests deben verificar este comportamiento.

4. **Método `before()`**: Recordar que el método `before()` en las policies se ejecuta primero y puede devolver `true` para super-admin, pero los métodos específicos (`forceDelete`, `restore`) pueden tener lógica adicional que debe ser testeada.

5. **Cobertura de Clases**: La cobertura de clases está en 87.50% porque ProgramPolicy y UserPolicy no tienen cobertura completa. Una vez que se completen los tests, debería alcanzar 100%.

---

## Estimación de Tiempo

- **Fase 1 (ProgramPolicy)**: ~30 minutos
- **Fase 2 (UserPolicy restore)**: ~20 minutos
- **Fase 3 (UserPolicy forceDelete)**: ~40 minutos
- **Verificación y ajustes**: ~20 minutos

**Total estimado**: ~2 horas

---

**Fecha de creación**: Enero 2026  
**Estado**: 📋 Plan completado - Listo para implementación
