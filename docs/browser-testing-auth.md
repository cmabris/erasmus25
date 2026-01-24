# Tests de Navegador: Autenticación y Autorización

Documentación de los tests de browser para flujos de autenticación (Fortify) y autorización (roles/permisos) del paso 3.11.3.

## Resumen de Tests

| Archivo | Contenido |
|---------|-----------|
| `tests/Browser/Auth/LoginTest.php` | Formulario de login, credenciales correctas/incorrectas, validación, redirección a URL intentada, "Remember me", enlaces forgot/register |
| `tests/Browser/Auth/RegisterTest.php` | Formulario de registro, registro exitoso, email duplicado, password ≠ confirmación, validaciones (email, password corta), enlace a login |
| `tests/Browser/Auth/PasswordResetTest.php` | Forgot: formulario, envío de enlace (Notification::fake), email inexistente. Reset: formulario con token, reset exitoso, token inválido, password ≠ confirmación, enlace a login |
| `tests/Browser/Auth/PublicAuthorizationTest.php` | Invitado y autenticado en Home, programas, convocatorias, noticias (índice y detalle) |
| `tests/Browser/Admin/AdminAuthorizationTest.php` | Invitado → login; Viewer → 403 en users/roles, acceso a dashboard/programas/noticias; Admin y Super-admin en todas; logout |
| `tests/Browser/Auth/AuthHelpersTest.php` | Comprueba `createAuthTestUser` + `performLogin` → `/dashboard` |

## Helpers de Autenticación

Definidos en `tests/Browser/Helpers.php`:

### `ensureRolesExist()`

Ejecuta `RolesAndPermissionsSeeder` (roles: super-admin, admin, editor, viewer). Idempotente. Se invoca automáticamente al usar `createAuthTestUser` con `$role`.

### `createAuthTestUser($overrides = [], $role = null)`

- Usuario con `User::factory()->withoutTwoFactor()->create()` y `password => 'password'`.
- `$overrides`: atributos que sobrescriben (ej. `['name' => 'Logout Test']`).
- `$role`: si se pasa, llama a `ensureRolesExist()` y `assignRole($role)` (p. ej. `App\Support\Roles::VIEWER`).
- Uso: `$user = createAuthTestUser([], Roles::ADMIN);`

### `performLogin(User $user)`

- `visit(route('login'))` → `fill('email', $user->email)` → `fill('password', 'password')` → `click('Log in')`.
- Tras login, Fortify redirige a `/dashboard`. Devuelve la página para encadenar `navigate()` o `visit()` (p. ej. `/admin/users`).

**Importar:**

```php
use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\performLogin;
use function Tests\Browser\Helpers\ensureRolesExist;
```

## Convenciones

- **Contraseña de prueba**: `'password'` (hash por el modelo).
- **2FA**: `withoutTwoFactor()` en usuarios de auth; no se testea flujo 2FA en estos tests.
- **Usuario sin verificar**: `User` no implementa `MustVerifyEmail`; el test de redirección por `verified` está omitido y documentado en el plan.
- **Fill**: por `name` del input: `fill('email', ...)`, `fill('password', ...)`.
- **Click**: por texto del botón/enlace: `click('Log in')`, `click('Create account')`, `click('Log Out')`.
- **Reset password**: en el formulario de reset se usa **`submit()`** para asegurar el POST; `click('Reset password')` puede no enviar bien el formulario.
- **`data-test`** en vistas de auth: `login-button`, `email-password-reset-link-button`, `reset-password-button` (para posibles mejoras de selectores).

## Datos de prueba

- Para rutas públicas con contenido: `createPublicTestData()` (programa, convocatoria, noticia).
- Para admin con roles: `createAuthTestUser($overrides, $role)` + `performLogin($user)`.
- En reset: el token se obtiene del flujo real (solicitar enlace → `Notification::fake` → `ResetPassword::class`); o `Password::broker(...)->createToken($user)` para el formulario de reset.

## Comandos

```bash
# Todos los tests de autenticación
./vendor/bin/pest tests/Browser/Auth

# Solo tests de autorización en admin
./vendor/bin/pest tests/Browser/Admin/AdminAuthorizationTest.php

# Con navegador visible y pausa en fallos
./vendor/bin/pest tests/Browser/Auth --headed --debug
```

## Notas

- `ensureRolesExist` / `RolesAndPermissionsSeeder` solo se ejecutan cuando `createAuthTestUser` recibe `$role`; el seeder es idempotente.
- Los tests usan `RefreshDatabase`; la BD se limpia entre tests.
- `assertNoJavascriptErrors()` se usa en los flujos principales.

---

**Plan detallado**: [pasos/paso-3.11.3-plan.md](pasos/paso-3.11.3-plan.md)  
**Configuración general**: [browser-testing-setup.md](browser-testing-setup.md)
