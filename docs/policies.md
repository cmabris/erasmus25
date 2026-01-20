# Sistema de Policies y Autorización

Este documento describe las Policies implementadas para autorización en la aplicación Erasmus+ Centro (Murcia).

## Visión General

Las Policies de Laravel proporcionan una forma centralizada de gestionar la autorización de acciones sobre modelos. Cada Policy define qué usuarios pueden realizar qué acciones sobre un modelo específico.

## Estructura de Policies

Todas las policies se encuentran en `app/Policies/` y siguen una estructura consistente:

```php
namespace App\Policies;

use App\Models\{Model};
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;

class {Model}Policy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::MODULE_VIEW);
    }

    // ... más métodos
}
```

## Policies Implementadas

### 1. ProgramPolicy

**Modelo:** `App\Models\Program`  
**Permisos base:** `programs.*`

| Método | Permiso Requerido |
|--------|------------------|
| viewAny | programs.view |
| view | programs.view |
| create | programs.create |
| update | programs.edit |
| delete | programs.delete |
| restore | programs.delete |
| forceDelete | programs.delete |

---

### 2. AcademicYearPolicy

**Modelo:** `App\Models\AcademicYear`  
**Autorización:** Basada en roles (sin permisos específicos)

Los años académicos son datos de configuración del sistema. La autorización se basa directamente en roles:

| Método | Autorización |
|--------|-------------|
| viewAny | Todos los usuarios autenticados |
| view | Todos los usuarios autenticados |
| create | Rol admin o super-admin |
| update | Rol admin o super-admin |
| delete | Rol admin o super-admin |
| restore | Rol admin o super-admin |
| forceDelete | Rol admin o super-admin |

---

### 3. CallPolicy

**Modelo:** `App\Models\Call`  
**Permisos base:** `calls.*`

| Método | Permiso Requerido |
|--------|------------------|
| viewAny | calls.view |
| view | calls.view |
| create | calls.create |
| update | calls.edit |
| delete | calls.delete |
| **publish** | calls.publish |
| restore | calls.delete |
| forceDelete | calls.delete |

**Método especial `publish()`:** Permite cambiar el estado de una convocatoria a publicado y establecer `published_at`.

---

### 4. CallPhasePolicy

**Modelo:** `App\Models\CallPhase`  
**Permisos base:** `calls.*` (sub-entidad de Call)

| Método | Permiso Requerido |
|--------|------------------|
| viewAny | calls.view |
| view | calls.view |
| create | calls.create |
| update | calls.edit |
| delete | calls.delete |
| restore | calls.delete |
| forceDelete | calls.delete |

---

### 5. ResolutionPolicy

**Modelo:** `App\Models\Resolution`  
**Permisos base:** `calls.*` (sub-entidad de Call)

| Método | Permiso Requerido |
|--------|------------------|
| viewAny | calls.view |
| view | calls.view |
| create | calls.create |
| update | calls.edit |
| delete | calls.delete |
| **publish** | calls.publish |
| restore | calls.delete |
| forceDelete | calls.delete |

**Método especial `publish()`:** Permite publicar una resolución.

---

### 6. NewsPostPolicy

**Modelo:** `App\Models\NewsPost`  
**Permisos base:** `news.*`

| Método | Permiso Requerido |
|--------|------------------|
| viewAny | news.view |
| view | news.view |
| create | news.create |
| update | news.edit |
| delete | news.delete |
| **publish** | news.publish |
| restore | news.delete |
| forceDelete | news.delete |

**Método especial `publish()`:** Permite publicar una noticia.

---

### 7. NewsTagPolicy

**Modelo:** `App\Models\NewsTag`  
**Permisos base:** `news.*` (sub-entidad de NewsPost)

| Método | Permiso Requerido |
|--------|------------------|
| viewAny | news.view |
| view | news.view |
| create | news.create |
| update | news.edit |
| delete | news.delete |
| restore | news.delete |
| forceDelete | news.delete |

---

### 8. DocumentPolicy

**Modelo:** `App\Models\Document`  
**Permisos base:** `documents.*`

| Método | Permiso Requerido |
|--------|------------------|
| viewAny | documents.view |
| view | documents.view |
| create | documents.create |
| update | documents.edit |
| delete | documents.delete |
| restore | documents.delete |
| forceDelete | documents.delete |

---

### 9. DocumentCategoryPolicy

**Modelo:** `App\Models\DocumentCategory`  
**Permisos base:** `documents.*` (sub-entidad de Document)

| Método | Permiso Requerido |
|--------|------------------|
| viewAny | documents.view |
| view | documents.view |
| create | documents.create |
| update | documents.edit |
| delete | documents.delete |
| restore | documents.delete |
| forceDelete | documents.delete |

---

### 10. ErasmusEventPolicy

**Modelo:** `App\Models\ErasmusEvent`  
**Permisos base:** `events.*`

| Método | Permiso Requerido |
|--------|------------------|
| viewAny | events.view |
| view | events.view |
| create | events.create |
| update | events.edit |
| delete | events.delete |
| restore | events.delete |
| forceDelete | events.delete |

---

### 11. UserPolicy

**Modelo:** `App\Models\User`  
**Permisos base:** `users.*`

| Método | Permiso Requerido | Notas |
|--------|------------------|-------|
| viewAny | users.view | - |
| view | users.view | Usuario puede ver su propio perfil |
| create | users.create | - |
| update | users.edit | Usuario puede editar su propio perfil |
| delete | users.delete | Usuario NO puede eliminarse a sí mismo |
| restore | users.delete | - |
| forceDelete | users.delete | Usuario NO puede eliminarse a sí mismo |
| **assignRoles** | users.edit | Usuario NO puede modificar sus propios roles |

**Lógica especial:**
- Un usuario siempre puede ver y actualizar su propio perfil
- Un usuario nunca puede eliminarse a sí mismo
- Un usuario nunca puede modificar sus propios roles

---

## Uso de Policies

### En Controladores

```php
public function update(UpdateProgramRequest $request, Program $program)
{
    $this->authorize('update', $program);
    // ...
}
```

### En Componentes Livewire

#### Autorización en Acciones

```php
public function publish(): void
{
    $this->authorize('publish', $this->call);
    
    $this->call->update([
        'status' => 'abierta',
        'published_at' => now(),
    ]);
    
    $this->dispatch('notify', message: __('admin.calls.published'));
}
```

#### Autorización en mount()

```php
public function mount(Call $call): void
{
    $this->authorize('view', $call);
    $this->call = $call;
}
```

#### Autorización Condicional para Botones

```php
public function render(): View
{
    return view('livewire.admin.calls.show', [
        'canEdit' => auth()->user()->can('update', $this->call),
        'canDelete' => auth()->user()->can('delete', $this->call),
        'canPublish' => auth()->user()->can('publish', $this->call),
    ]);
}
```

#### Uso en la Vista del Componente

```blade
{{-- En la vista Blade del componente Livewire --}}
@if($canPublish && !$call->published_at)
    <flux:button wire:click="publish" variant="primary">
        {{ __('admin.calls.actions.publish') }}
    </flux:button>
@endif

@if($canDelete)
    <flux:button wire:click="delete" variant="danger">
        {{ __('common.actions.delete') }}
    </flux:button>
@endif
```

### En Vistas Blade

#### Directivas Básicas

```blade
@can('update', $program)
    <button>Editar Programa</button>
@endcan

@can('publish', $call)
    <button>Publicar Convocatoria</button>
@endcan
```

#### Con Flux UI - Botones de Acción

```blade
{{-- Grupo de acciones con autorización --}}
<div class="flex gap-2">
    @can('update', $program)
        <flux:button 
            href="{{ route('admin.programs.edit', $program) }}" 
            variant="primary"
            icon="pencil"
        >
            {{ __('common.actions.edit') }}
        </flux:button>
    @endcan

    @can('delete', $program)
        <flux:button 
            wire:click="confirmDelete({{ $program->id }})" 
            variant="danger"
            icon="trash"
        >
            {{ __('common.actions.delete') }}
        </flux:button>
    @endcan
</div>
```

#### Con Flux UI - Dropdown de Acciones

```blade
<flux:dropdown>
    <flux:button variant="ghost" icon="ellipsis-vertical" />
    
    <flux:menu>
        @can('view', $call)
            <flux:menu.item href="{{ route('admin.calls.show', $call) }}" icon="eye">
                {{ __('common.actions.view') }}
            </flux:menu.item>
        @endcan
        
        @can('update', $call)
            <flux:menu.item href="{{ route('admin.calls.edit', $call) }}" icon="pencil">
                {{ __('common.actions.edit') }}
            </flux:menu.item>
        @endcan
        
        @can('publish', $call)
            @unless($call->published_at)
                <flux:menu.item wire:click="publish({{ $call->id }})" icon="megaphone">
                    {{ __('admin.calls.actions.publish') }}
                </flux:menu.item>
            @endunless
        @endcan
        
        @can('delete', $call)
            <flux:menu.separator />
            <flux:menu.item wire:click="confirmDelete({{ $call->id }})" icon="trash" variant="danger">
                {{ __('common.actions.delete') }}
            </flux:menu.item>
        @endcan
    </flux:menu>
</flux:dropdown>
```

#### Múltiples Permisos con @canany

```blade
@canany(['update', 'delete'], $document)
    <div class="bg-yellow-50 p-4 rounded">
        <p class="text-sm text-yellow-700">
            {{ __('admin.documents.has_actions') }}
        </p>
    </div>
@endcanany
```

#### Verificación Negativa con @cannot

```blade
@cannot('publish', $newsPost)
    <flux:callout variant="warning" icon="exclamation-triangle">
        {{ __('admin.news.cannot_publish') }}
    </flux:callout>
@endcannot
```

#### En Tablas de Listado

```blade
<flux:table>
    <flux:columns>
        <flux:column>{{ __('common.fields.title') }}</flux:column>
        <flux:column>{{ __('common.fields.status') }}</flux:column>
        @canany(['update', 'delete'], App\Models\Call::class)
            <flux:column>{{ __('common.fields.actions') }}</flux:column>
        @endcanany
    </flux:columns>

    <flux:rows>
        @foreach($calls as $call)
            <flux:row>
                <flux:cell>{{ $call->title }}</flux:cell>
                <flux:cell>
                    <flux:badge :variant="$call->status_variant">
                        {{ $call->status_label }}
                    </flux:badge>
                </flux:cell>
                @canany(['update', 'delete'], $call)
                    <flux:cell>
                        {{-- Acciones aquí --}}
                    </flux:cell>
                @endcanany
            </flux:row>
        @endforeach
    </flux:rows>
</flux:table>
```

### Verificación Programática

```php
if ($user->can('delete', $document)) {
    // Usuario puede eliminar el documento
}

// O usando Gate
if (Gate::allows('publish', $newsPost)) {
    // Usuario puede publicar la noticia
}
```

---

## Método `before()` - Pre-autorización

Todas las policies implementan el método `before()` que se ejecuta antes de cualquier otra verificación:

```php
public function before(User $user, string $ability): ?bool
{
    if ($user->hasRole(Roles::SUPER_ADMIN)) {
        return true;  // Acceso total para super-admin
    }
    return null;  // Continuar con la autorización normal
}
```

**Importante:** Devolver `null` (no `false`) permite que la autorización continúe con los métodos específicos.

---

## Matriz de Permisos por Rol

### Matriz General

| Rol | programs | calls | news | documents | events | users |
|-----|----------|-------|------|-----------|--------|-------|
| super-admin | ✅ Todo | ✅ Todo | ✅ Todo | ✅ Todo | ✅ Todo | ✅ Todo |
| admin | ✅ Todo | ✅ Todo | ✅ Todo | ✅ Todo | ✅ Todo | ❌ |
| editor | Ver, Crear, Editar | Ver, Crear, Editar | Ver, Crear, Editar | Ver, Crear, Editar | Ver, Crear, Editar | ❌ |
| viewer | Solo Ver | Solo Ver | Solo Ver | Solo Ver | Solo Ver | ❌ |

**Nota:** Solo el rol `super-admin` tiene acceso a la gestión de usuarios (`users.*`).

### Matriz Detallada por Acción

```
                    ┌─────────────────────────────────────────────────────────────┐
                    │                    PERMISOS POR ROL                         │
                    ├─────────────┬─────────┬─────────┬─────────┬─────────┬───────┤
                    │ super-admin │  admin  │ editor  │ viewer  │ sin rol │       │
┌───────────────────┼─────────────┼─────────┼─────────┼─────────┼─────────┤       │
│ viewAny           │     ✅      │    ✅    │    ✅    │    ✅    │    ❌    │       │
│ view              │     ✅      │    ✅    │    ✅    │    ✅    │    ❌    │       │
│ create            │     ✅      │    ✅    │    ✅    │    ❌    │    ❌    │       │
│ update            │     ✅      │    ✅    │    ✅    │    ❌    │    ❌    │       │
│ delete            │     ✅      │    ✅    │    ❌    │    ❌    │    ❌    │       │
│ publish           │     ✅      │    ✅    │    ❌    │    ❌    │    ❌    │ *     │
│ restore           │     ✅      │    ✅    │    ❌    │    ❌    │    ❌    │       │
│ forceDelete       │     ✅      │    ✅    │    ❌    │    ❌    │    ❌    │ **    │
└───────────────────┴─────────────┴─────────┴─────────┴─────────┴─────────┴───────┘

* publish: Solo disponible en Call, Resolution, NewsPost
** forceDelete: Validación adicional de relaciones en algunos modelos
```

### Acceso a Módulos de Sistema

| Módulo | super-admin | admin | editor | viewer |
|--------|-------------|-------|--------|--------|
| Configuración | ✅ | ✅ | ❌ | ❌ |
| Traducciones | ✅ | ✅ | ❌ | ❌ |
| Auditoría | ✅ | ✅ | ❌ | ❌ |
| Roles | ✅ | ❌ | ❌ | ❌ |
| Usuarios | ✅ | ❌ | ❌ | ❌ |

---

## Tests de Policies

Los tests de policies se encuentran en `tests/Feature/Policies/` y verifican:

1. **Acceso de super-admin**: Tiene acceso total a todas las acciones
2. **Acceso de admin**: Tiene todos los permisos del módulo
3. **Acceso de editor**: Puede ver, crear y editar (sin delete ni publish)
4. **Acceso de viewer**: Solo puede ver
5. **Sin rol**: Acceso denegado
6. **Permisos directos**: Verificación de permisos asignados directamente
7. **Casos especiales**: Validación de relaciones, auto-eliminación, etc.

### Cobertura de Tests

**Estado**: ✅ **100% de cobertura alcanzado** (Enero 2026)

- **Cobertura Total de Líneas**: 100% (170/170 líneas)
- **Cobertura de Funciones/Métodos**: 100% (118/118)
- **Cobertura de Clases**: 100% (16/16)
- **Total de Tests**: 140 tests pasando (569 assertions)

### Tests Especiales Implementados

#### ProgramPolicy - Validación de Relaciones en forceDelete()

Los tests verifican que un programa no puede ser eliminado permanentemente si tiene relaciones con otros modelos:

```php
// Test: Super-admin no puede hacer forceDelete con calls
it('prevents super-admin from force deleting program with calls', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $program = Program::factory()->create();
    Call::factory()->create(['program_id' => $program->id]);

    $policy = new \App\Policies\ProgramPolicy;
    expect($policy->forceDelete($superAdmin, $program))->toBeFalse();
});
```

**Nota técnica**: Como el método `before()` devuelve `true` para super-admin, se llama directamente al método de la policy para testear la lógica de validación de relaciones.

#### UserPolicy - Métodos restore() y forceDelete()

Los tests cubren todos los casos de restauración y eliminación permanente:

- **restore()**: Verifica permisos `USERS_DELETE` para restaurar usuarios eliminados
- **forceDelete()**: Verifica permisos y previene auto-eliminación

```php
// Test: Usuario no puede eliminarse a sí mismo
it('prevents user with USERS_DELETE permission from force deleting themselves', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permissions::USERS_DELETE);
    $user->delete(); // Soft delete primero

    $policy = new \App\Policies\UserPolicy;
    expect($policy->forceDelete($user, $user))->toBeFalse();
});
```

### Ejecutar Tests

```bash
# Todos los tests de policies
php artisan test tests/Feature/Policies/

# Test específico
php artisan test --filter=ProgramPolicy
php artisan test --filter=UserPolicy

# Con cobertura
php artisan test tests/Feature/Policies/ --coverage
```

### Archivos de Test

Cada policy tiene su archivo de test correspondiente:

- `tests/Feature/Policies/ProgramPolicyTest.php` - 13 tests
- `tests/Feature/Policies/UserPolicyTest.php` - 27 tests
- `tests/Feature/Policies/CallPolicyTest.php` - 7 tests
- `tests/Feature/Policies/NewsPostPolicyTest.php` - 7 tests
- ... (y así para todas las 16 policies)

---

## Auto-Discovery

Laravel detecta automáticamente las policies gracias a la convención de nombres:
- `App\Models\Program` → `App\Policies\ProgramPolicy`
- `App\Models\User` → `App\Policies\UserPolicy`

No es necesario registrar manualmente las policies en `AuthServiceProvider`.

**Excepción**: `RolePolicy` requiere registro manual porque el modelo `Role` pertenece al paquete Spatie Permission:

```php
// En AuthServiceProvider o AppServiceProvider
Gate::policy(\Spatie\Permission\Models\Role::class, \App\Policies\RolePolicy::class);
```

---

## Casos Especiales y Patrones Avanzados

### 1. Validación de Relaciones en forceDelete()

Algunos modelos no pueden eliminarse permanentemente si tienen relaciones activas:

```php
// ProgramPolicy
public function forceDelete(User $user, Program $program): bool
{
    // Verificar que no tiene relaciones
    if ($program->calls()->exists()) {
        return false;
    }
    if ($program->newsPosts()->exists()) {
        return false;
    }
    if ($program->documents()->exists()) {
        return false;
    }
    if ($program->events()->exists()) {
        return false;
    }
    
    return $user->can(Permissions::PROGRAMS_DELETE);
}
```

**Modelos afectados**:
- `Program`: No eliminar si tiene calls, newsPosts, documents, events
- `AcademicYear`: No eliminar si tiene calls
- `DocumentCategory`: No eliminar si tiene documents
- `NewsTag`: No eliminar si tiene newsPosts asociados

### 2. Auto-protección de Usuario

Un usuario nunca puede realizar ciertas acciones sobre sí mismo:

```php
// UserPolicy
public function delete(User $user, User $model): bool
{
    // Prevenir auto-eliminación
    if ($user->id === $model->id) {
        return false;
    }
    
    return $user->can(Permissions::USERS_DELETE);
}

public function assignRoles(User $user, User $model): bool
{
    // Prevenir modificación de propios roles
    if ($user->id === $model->id) {
        return false;
    }
    
    return $user->can(Permissions::USERS_EDIT);
}
```

### 3. Protección de Roles del Sistema

Los roles del sistema no pueden ser eliminados ni modificados en ciertos aspectos:

```php
// RolePolicy
public function delete(User $user, Role $role): bool
{
    // Roles del sistema protegidos
    $protectedRoles = [
        Roles::SUPER_ADMIN,
        Roles::ADMIN,
        Roles::EDITOR,
        Roles::VIEWER,
    ];
    
    if (in_array($role->name, $protectedRoles)) {
        return false;
    }
    
    return $user->hasRole(Roles::SUPER_ADMIN);
}
```

### 4. Autorización Basada en Estado

Algunas acciones dependen del estado del modelo:

```php
// En el componente Livewire
public function publish(): void
{
    $this->authorize('publish', $this->call);
    
    // Verificación adicional de estado
    if ($this->call->status !== 'borrador') {
        $this->dispatch('notify', 
            message: __('admin.calls.already_published'),
            type: 'error'
        );
        return;
    }
    
    // Proceder con publicación...
}
```

### 5. Permisos en Entidades Anidadas

Las sub-entidades heredan permisos de la entidad padre:

```php
// CallPhasePolicy - hereda permisos de calls
public function create(User $user): bool
{
    return $user->can(Permissions::CALLS_CREATE);
}

// ResolutionPolicy - hereda permisos de calls
public function publish(User $user, Resolution $resolution): bool
{
    return $user->can(Permissions::CALLS_PUBLISH);
}
```

**Jerarquía de permisos**:
```
calls.*
├── CallPhase (usa calls.*)
└── Resolution (usa calls.*)

news.*
└── NewsTag (usa news.*)

documents.*
└── DocumentCategory (usa documents.*)
```

### 6. Acceso a Propio Perfil

Un usuario siempre puede ver y editar su propio perfil, independientemente de permisos:

```php
// UserPolicy
public function view(User $user, User $model): bool
{
    // Siempre puede ver su propio perfil
    if ($user->id === $model->id) {
        return true;
    }
    
    return $user->can(Permissions::USERS_VIEW);
}

public function update(User $user, User $model): bool
{
    // Siempre puede editar su propio perfil
    if ($user->id === $model->id) {
        return true;
    }
    
    return $user->can(Permissions::USERS_EDIT);
}
```

---

## Buenas Prácticas

### 1. Usar Constantes de Permisos

```php
// ✅ Correcto
$user->can(Permissions::CALLS_PUBLISH);

// ❌ Evitar strings hardcodeados
$user->can('calls.publish');
```

### 2. Autorizar Antes de Ejecutar

```php
// ✅ Correcto - autorizar primero
public function delete(int $id): void
{
    $call = Call::findOrFail($id);
    $this->authorize('delete', $call);
    $call->delete();
}

// ❌ Evitar - ejecutar sin autorizar
public function delete(int $id): void
{
    Call::findOrFail($id)->delete();
}
```

### 3. Usar Policies en Lugar de Verificaciones Manuales

```php
// ✅ Correcto - usar policy
@can('update', $program)
    <button>Editar</button>
@endcan

// ❌ Evitar - verificación manual
@if(auth()->user()->hasRole('admin') || auth()->user()->hasPermissionTo('programs.edit'))
    <button>Editar</button>
@endif
```

### 4. Documentar Casos Especiales

Cuando una policy tiene lógica especial, documentarla en el código:

```php
/**
 * Determina si el usuario puede eliminar permanentemente el programa.
 * 
 * IMPORTANTE: La eliminación permanente solo es posible si el programa
 * no tiene relaciones con otros modelos (calls, news, documents, events).
 */
public function forceDelete(User $user, Program $program): bool
{
    // ... implementación
}
```

---

**Fecha de Creación**: Diciembre 2025  
**Última Actualización**: Enero 2026  
**Cobertura de Tests**: 100% (Enero 2026)
