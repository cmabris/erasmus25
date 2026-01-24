# Paso 55: Tests de Navegador de Autenticación y Autorización (Paso 3.11.3)

Este documento contiene todos los prompts utilizados durante la planificación e implementación del paso 3.11.3 y un resumen de las respuestas obtenidas.

**Fecha de creación**: Enero 2026  
**Estado**: ✅ Completado (8 fases)

---

## Índice

1. [Contexto](#contexto)
2. [Prompts y Respuestas](#prompts-y-respuestas)
3. [Archivos Creados/Modificados](#archivos-creadosmodificados)
4. [Resultado Final](#resultado-final)

---

## Contexto

El paso 3.11.3 corresponde a los **Tests de Navegador de flujos de autenticación y autorización** dentro del plan de desarrollo. El objetivo es implementar tests con Pest v4 y Playwright que validen:

- **Autenticación (Fortify)**: login, registro, recuperación de contraseña (forgot + reset).
- **Autorización**: acceso como invitado y autenticado a rutas públicas (Home, programas, convocatorias, noticias); acceso a rutas de administración según roles (invitado → login; viewer → 403 en users/roles; admin y super-admin con acceso completo; logout).

**Documentación de referencia:**
- `docs/planificacion_pasos.md` — sección 3.11.3
- `docs/pasos/paso-3.11.3-plan.md` — plan detallado (8 fases)
- `docs/browser-testing-auth.md` — guía de tests de auth (creada en Fase 8)

---

## Prompts y Respuestas

### Prompt 1: Plan detallado del paso 3.11.3

**Prompt:**
```
Crear el plan detallado del paso 3.11.3 (Tests de navegador de autenticación y autorización) y guardarlo en el archivo indicado en la documentación.
```

**Resumen de la respuesta:**
- Se revisó la documentación en `docs/planificacion_pasos.md` y la estructura de Fortify, rutas, roles y helpers existentes.
- Se creó el plan detallado en `docs/pasos/paso-3.11.3-plan.md` con **8 fases**:
  1. Helpers y datos de prueba para autenticación (`createAuthTestUser`, `performLogin`, `ensureRolesExist`)
  2. Tests de Login (formulario, credenciales, validación, redirección, remember me, enlaces)
  3. Tests de Registro (formulario, registro exitoso, validaciones, enlace a login)
  4. Tests de Recuperación de contraseña (forgot, reset, token inválido, validaciones)
  5. Tests de autorización en rutas públicas (invitado y autenticado en Home, programas, convocatorias, noticias)
  6. Tests de autorización en rutas de administración (invitado, viewer, admin, super-admin, logout)
  7. Revisión de selectores y convenciones
  8. Documentación y verificación final

---

### Prompt 2: Fase 1 — Helpers y datos de prueba

**Prompt:**
```
Implementar la Fase 1 (Helpers y datos de prueba para auth).
```

**Resumen de la respuesta:**
- Se amplió `tests/Browser/Helpers.php`:
  - **`ensureRolesExist()`**: ejecuta `RolesAndPermissionsSeeder` (idempotente).
  - **`createAuthTestUser($overrides = [], $role = null)`**: crea usuario con `User::factory()->withoutTwoFactor()->create(array_merge(['password' => 'password'], $overrides))`; si `$role` se pasa, llama a `ensureRolesExist()` y `assignRole($role)`.
  - **`performLogin(User $user)`**: `visit(route('login'))` → `fill('email', $user->email)` → `fill('password', 'password')` → `click('Log in')`; devuelve la página para encadenar `navigate()` o `visit()`.
- Se creó `tests/Browser/Auth/AuthHelpersTest.php` que verifica `createAuthTestUser` + `performLogin` → redirección a `/dashboard`.

---

### Prompt 3: Fase 2 — Tests de Login

**Prompt:**
```
Implementar la Fase 2 (Tests de Login).
```

**Resumen de la respuesta:**
- Se creó `tests/Browser/Auth/LoginTest.php` con tests para:
  - Formulario con elementos requeridos (email, password, remember, enlaces forgot/register).
  - Login correcto con credenciales válidas → `/dashboard`.
  - Errores con email o contraseña incorrectos.
  - Validación: email inválido, contraseña vacía.
  - Redirección a URL intentada tras login en ruta protegida.
  - «Remember me»: presencia del checkbox y login exitoso (el `flux:checkbox` no permitía `check()` directo en el test).
  - Navegación a forgot-password y register.
  - `assertNoJavascriptErrors` en la página de login.

---

### Prompt 4: Fase 3 — Tests de Registro

**Prompt:**
```
Implementar la Fase 3 (Tests de Registro).
```

**Resumen de la respuesta:**
- Se creó `tests/Browser/Auth/RegisterTest.php` con tests para:
  - Formulario con name, email, password, password_confirmation.
  - Registro exitoso con datos válidos → `/dashboard` (User no implementa `MustVerifyEmail`, no hay redirección a `/email/verify`).
  - Email duplicado, password ≠ confirmación, email inválido, password demasiado corta.
  - Enlace a login y `assertNoJavascriptErrors`.

---

### Prompt 5: Fase 4 — Tests de Recuperación de contraseña

**Prompt:**
```
Implementar la Fase 4 (Tests de Recuperación de contraseña).
```

**Resumen de la respuesta:**
- Se creó `tests/Browser/Auth/PasswordResetTest.php`:
  - **Forgot**: formulario con email; envío con `Notification::fake` y `assertSentTo(ResetPassword::class)`; email inexistente → se permanece en `/forgot-password`.
  - **Reset**: formulario con token (obtenido vía `Password::broker()->createToken($user)` o del flujo real de solicitud); reset exitoso usando **`submit()`** en el formulario (no `click('Reset password')` para asegurar el POST); token inválido → se mantiene en el formulario y la contraseña no cambia; password ≠ confirmación; enlace a login; `assertNoJavascriptErrors`.

---

### Prompt 6: Fase 5 — Tests de autorización en rutas públicas

**Prompt:**
```
Implementar la Fase 5 (Tests de autorización en rutas públicas).
```

**Resumen de la respuesta:**
- Se creó `tests/Browser/Auth/PublicAuthorizationTest.php`:
  - Invitado: Home, programas, convocatorias, noticias (índice).
  - Autenticado: mismas rutas y detalle (programas.show, convocatorias.show, noticias.show) usando `createPublicTestData()` y **`navigate()`** para conservar la sesión tras el login.

---

### Prompt 7: Fase 6 — Tests de autorización en rutas de administración

**Prompt:**
```
Implementar la Fase 6 (Tests de autorización en rutas de administración).
```

**Resumen de la respuesta:**
- Se creó `tests/Browser/Admin/AdminAuthorizationTest.php`:
  - Invitado en admin.dashboard, programs, news, users, roles → `assertPathIs('/login')`.
  - Viewer: `assertSee('403')` en users y roles; acceso a dashboard, programas y noticias.
  - Admin: acceso a programas, noticias, usuarios.
  - Super-admin: acceso a las mismas rutas.
  - Test de usuario sin email verificado **omitido**: `User` no implementa `MustVerifyEmail`; el middleware `verified` no redirige en esta app.
  - Logout: `createAuthTestUser(['name' => 'Logout Test'], Roles::VIEWER)` → login → `/admin` → `click('Logout Test')` para abrir menú → `click('Log Out')` → visit admin → `assertPathIs('/login')`.

---

### Prompt 8: Sigamos (Fase 7)

**Prompt:**
```
Sigamos
```

**Resumen de la respuesta:**
- Se completó la **Fase 7** (revisión de selectores y convenciones):
  - En `docs/pasos/paso-3.11.3-plan.md` se marcaron los ítems de la Fase 7 como hechos y se añadió la sección **«Convenciones adoptadas»**:
    - **fill**: por `name` del input (`fill('email', ...)`, `fill('password', ...)`).
    - **click**: por texto del botón/enlace (`click('Log in')`, `click('Create account')`, `click('Log Out')`).
    - En reset-password se usa **`submit()`** para asegurar el envío del POST.
    - `data-test` en auth: `login-button`, `email-password-reset-link-button`, `reset-password-button`.
- Quedó pendiente el cierre de la **Fase 8** (documentación, planificación, verificación final).

---

### Prompt 9: Sigamos (Fase 8)

**Prompt:**
```
Sigamos
```

**Resumen de la respuesta:**
- Se completó la **Fase 8** (documentación y verificación):
  - **Documentación**: se creó `docs/browser-testing-auth.md` con resumen de LoginTest, RegisterTest, PasswordResetTest, PublicAuthorizationTest, AdminAuthorizationTest; helpers `createAuthTestUser`, `performLogin`, `ensureRolesExist`; convenciones (contraseña `password`, `withoutTwoFactor`, `unverified` cuando se testee `verified`); comandos `./vendor/bin/pest tests/Browser/Auth`, `./vendor/bin/pest tests/Browser/Admin/AdminAuthorizationTest.php`, `--headed`, `--debug`. En `docs/browser-testing-setup.md` se añadió un apartado de helpers de autenticación con enlace a `browser-testing-auth.md`.
  - **Planificación**: en `docs/planificacion_pasos.md`, sección 3.11.3, se marcaron como hechos: Test de Login, Registro, Recuperación de Contraseña, Autorización en Rutas Públicas, Autorización en Rutas de Administración.
  - **Verificación**: se ejecutaron `./vendor/bin/pest tests/Browser/Auth` y `./vendor/bin/pest tests/Browser/Admin/AdminAuthorizationTest.php` — **43 tests pasando** (~26 s).

---

### Prompt 10: Generar paso55

**Prompt:**
```
Para terminar este chat, genera un archivo llamado paso55 en la carpeta docs/pasos con todos los prompts de este chat y un resumen de las respuestas de cada uno de ellos.
```

**Resumen de la respuesta:**
- Se creó el archivo `docs/pasos/paso55.md` con: contexto del paso 3.11.3, los 10 prompts anteriores (con el texto o descripción de cada uno) y un resumen de la respuesta/acción realizada para cada prompt; además de las secciones «Archivos creados/modificados» y «Resultado final».

---

## Archivos Creados/Modificados

| Archivo | Acción |
|---------|--------|
| `docs/pasos/paso-3.11.3-plan.md` | Creado (Prompt 1); modificado en Fases 7 y 8 |
| `tests/Browser/Helpers.php` | Modificado: `ensureRolesExist`, `createAuthTestUser`, `performLogin` |
| `tests/Browser/Auth/AuthHelpersTest.php` | Creado |
| `tests/Browser/Auth/LoginTest.php` | Creado |
| `tests/Browser/Auth/RegisterTest.php` | Creado |
| `tests/Browser/Auth/PasswordResetTest.php` | Creado |
| `tests/Browser/Auth/PublicAuthorizationTest.php` | Creado |
| `tests/Browser/Admin/AdminAuthorizationTest.php` | Creado |
| `docs/browser-testing-auth.md` | Creado |
| `docs/browser-testing-setup.md` | Modificado: apartado de helpers de auth |
| `docs/planificacion_pasos.md` | Modificado: ítems 3.11.3 marcados como [x] |
| `docs/pasos/paso55.md` | Creado (este archivo) |

---

## Resultado Final

- **43 tests** de navegador de autenticación y autorización pasando:
  - Auth: AuthHelpersTest, LoginTest, RegisterTest, PasswordResetTest, PublicAuthorizationTest.
  - Admin: AdminAuthorizationTest.
- **Helpers**: `createAuthTestUser`, `performLogin`, `ensureRolesExist` documentados en `docs/browser-testing-auth.md`.
- **Convenciones** (fill por `name`, click por texto, `submit()` en reset, `data-test`) documentadas en el plan y en la guía de auth.
- **Decisiones** reflejadas en el plan: User sin `MustVerifyEmail` (test unverified omitido); reset exitoso con `submit()`; logout en admin vía menú (`click` en nombre de usuario y luego «Log Out»).

**Comandos de verificación:**
```bash
./vendor/bin/pest tests/Browser/Auth tests/Browser/Admin/AdminAuthorizationTest.php
```
