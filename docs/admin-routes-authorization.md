# Autorización en Rutas de Administración

## Decisión de Diseño

**Decisión**: Mantener la autorización únicamente en los componentes Livewire mediante Policies, sin añadir middleware de permisos adicionales en las rutas.

**Fecha**: Diciembre 2025  
**Estado**: ✅ Implementado y documentado

---

## Análisis de Opciones

### Opción A: Autorización solo en Componentes Livewire (✅ Implementada)

**Implementación actual**:
- Middleware básico en rutas: `auth` y `verified`
- Autorización verificada en componentes mediante `AuthorizesRequests` trait
- Uso de Policies para lógica de autorización compleja
- Verificación en `mount()` y en acciones específicas

**Ventajas**:
1. **Flexibilidad**: Permite lógica compleja de autorización
   - Verificar propiedad del recurso (ej: solo el autor puede editar su noticia)
   - Condiciones contextuales (ej: solo se puede editar si está en borrador)
   - Lógica específica por acción (create, update, delete, publish)

2. **Mantenibilidad**: 
   - Lógica centralizada en Policies
   - Fácil de testear (tests de componentes ya verifican autorización)
   - Código más limpio y organizado

3. **Mensajes de error personalizados**:
   - Las Policies pueden retornar mensajes específicos
   - Mejor experiencia de usuario

4. **Ya implementado y funcionando**:
   - Todos los componentes verifican autorización correctamente
   - Tests completos verifican el comportamiento
   - No requiere cambios adicionales

**Desventajas**:
1. **Requiere disciplina**: Si un componente no verifica autorización, la ruta es accesible
   - **Mitigación**: Tests exhaustivos verifican que todos los componentes autorizan correctamente

2. **No falla rápido**: La autorización se verifica después de cargar el componente
   - **Mitigación**: El overhead es mínimo y la flexibilidad compensa

---

### Opción B: Middleware de Permisos en Rutas

**Implementación propuesta**:
- Añadir middleware `permission:module.action` en cada ruta
- Mantener autorización en componentes como segunda capa

**Ventajas**:
1. **Doble capa de seguridad**: Middleware + componentes
2. **Falla rápido**: Si no hay permisos, se rechaza antes de cargar el componente
3. **Más explícito**: Los permisos requeridos están visibles en la definición de rutas

**Desventajas**:
1. **Duplicación de lógica**: 
   - Permisos en rutas + autorización en componentes
   - Más difícil de mantener
   - Cambios requieren actualizar dos lugares

2. **Menos flexible**:
   - No permite verificar propiedad del recurso (ej: solo el autor puede editar)
   - No permite condiciones contextuales complejas
   - Requiere lógica adicional en componentes de todas formas

3. **Complejidad innecesaria**:
   - Los componentes ya verifican autorización correctamente
   - Los tests ya verifican el comportamiento
   - Añadir middleware sería redundante

4. **Mantenimiento**:
   - Cada cambio de permisos requiere actualizar rutas
   - Más propenso a errores
   - Más difícil de testear completamente

---

## Implementación Actual

### Middleware en Rutas

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Todas las rutas de administración
});
```

**Middleware aplicado**:
- `auth`: Requiere usuario autenticado
- `verified`: Requiere email verificado (si está habilitado)

### Autorización en Componentes

Todos los componentes Livewire de administración siguen este patrón:

```php
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Index extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('viewAny', Model::class);
    }
}
```

**Ejemplos de autorización**:

1. **Listado (Index)**:
   ```php
   $this->authorize('viewAny', Program::class);
   ```

2. **Detalle (Show)**:
   ```php
   public function mount(Program $program): void
   {
       $this->authorize('view', $program);
   }
   ```

3. **Crear (Create)**:
   ```php
   public function mount(): void
   {
       $this->authorize('create', Program::class);
   }
   ```

4. **Editar (Edit)**:
   ```php
   public function mount(Program $program): void
   {
       $this->authorize('update', $program);
   }
   ```

5. **Acciones específicas**:
   ```php
   public function delete(Program $program): void
   {
       $this->authorize('delete', $program);
       // ... lógica de eliminación
   }
   ```

### Policies

Cada modelo tiene su Policy correspondiente que implementa la lógica de autorización:

- `ProgramPolicy`
- `CallPolicy`
- `NewsPostPolicy`
- `DocumentPolicy`
- `UserPolicy`
- `RolePolicy`
- `SettingPolicy`
- `TranslationPolicy`
- `ActivityPolicy` (para AuditLogs)
- etc.

**Ejemplo de Policy**:

```php
class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::PROGRAMS_VIEW);
    }

    public function view(User $user, Program $program): bool
    {
        return $user->can(Permissions::PROGRAMS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::PROGRAMS_CREATE);
    }

    public function update(User $user, Program $program): bool
    {
        return $user->can(Permissions::PROGRAMS_EDIT);
    }

    public function delete(User $user, Program $program): bool
    {
        return $user->can(Permissions::PROGRAMS_DELETE);
    }
}
```

---

## Verificación y Tests

### Tests de Autorización

Todos los componentes tienen tests que verifican:
- Redirección de usuarios no autenticados
- Acceso con permisos correctos
- Denegación sin permisos (403)
- Comportamiento específico por rol

**Ejemplo de test**:

```php
it('denies access to users without programs permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.programs.index'))
        ->assertForbidden();
});
```

### Tests de Rutas

El archivo `tests/Feature/Routes/AdminRoutesTest.php` verifica:
- Redirección de usuarios no autenticados
- Acceso con permisos correctos
- Denegación sin permisos
- Route model binding
- 404 para recursos no existentes

**Cobertura**: 83 tests pasando (100 assertions)

---

## Checklist de Verificación para Nuevos Componentes

Al crear un nuevo componente Livewire de administración, asegúrate de:

- [ ] Usar el trait `AuthorizesRequests`
- [ ] Verificar autorización en `mount()` usando `$this->authorize()`
- [ ] Verificar autorización en acciones específicas (delete, update, etc.)
- [ ] Crear tests que verifiquen autorización
- [ ] Usar la Policy correspondiente del modelo
- [ ] Documentar permisos requeridos en comentarios

**Ejemplo de plantilla**:

```php
<?php

namespace App\Livewire\Admin\Module;

use App\Models\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;

    /**
     * Mount the component.
     * 
     * Permisos requeridos: module.view
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Model::class);
    }

    // ... resto del componente
}
```

---

## Casos Especiales

### Dashboard

El Dashboard es un caso especial:
- Permite acceso a cualquier usuario autenticado
- Los elementos se muestran/ocultan según permisos específicos
- No bloquea el acceso, solo controla la visualización

**Razón**: El dashboard es una página de entrada que muestra información según los permisos del usuario, no requiere permisos específicos para acceder.

### Rutas Anidadas

Las rutas anidadas (fases y resoluciones de convocatorias) también verifican autorización:
- Verifican permisos sobre el recurso padre (convocatoria)
- Verifican permisos sobre el recurso hijo (fase/resolución)
- La Policy puede verificar relaciones entre recursos

**Ejemplo**:

```php
public function mount(Call $call, CallPhase $callPhase): void
{
    // Verificar permisos sobre la convocatoria
    $this->authorize('view', $call);
    
    // Verificar permisos sobre la fase
    $this->authorize('view', $callPhase);
    
    // Verificar que la fase pertenece a la convocatoria
    if ($callPhase->call_id !== $call->id) {
        abort(404);
    }
}
```

---

## Conclusión

La decisión de mantener la autorización únicamente en componentes Livewire es la más adecuada porque:

1. ✅ **Ya está implementado y funcionando correctamente**
2. ✅ **Permite lógica compleja de autorización**
3. ✅ **Más fácil de mantener y testear**
4. ✅ **No requiere cambios adicionales**
5. ✅ **Tests exhaustivos verifican el comportamiento**

**Recomendación**: Mantener esta implementación y asegurar que todos los nuevos componentes sigan el mismo patrón.

---

## Referencias

- [Laravel Authorization Documentation](https://laravel.com/docs/authorization)
- [Livewire Authorization](https://livewire.laravel.com/docs/authorization)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- Tests: `tests/Feature/Routes/AdminRoutesTest.php`
- Policies: `app/Policies/`
