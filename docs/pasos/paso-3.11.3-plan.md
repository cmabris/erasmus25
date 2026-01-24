# Plan de Trabajo - Paso 3.11.3: Tests de Navegador de Flujos de Autenticación y Autorización

## Objetivo

Implementar tests de navegador completos para los flujos de autenticación (login, registro, recuperación de contraseña) y de autorización (acceso a rutas públicas y de administración según el estado de autenticación y los permisos del usuario). Estos tests validan el comportamiento completo desde la perspectiva del usuario final usando Pest v4 con Playwright.

---

## Estado Actual

### ✅ Ya Implementado

1. **Configuración de Browser Tests (Pasos 3.11.1 y 3.11.2)**:
   - Pest v4 con `pest-plugin-browser` y Playwright
   - Estructura `tests/Browser/` con subdirectorios `Public/`, `Auth/`, `Admin/`
   - `tests/Browser/Auth/` existe vacío (solo `.gitkeep`)
   - Helper `createAuthenticatedUser()` en `tests/Browser/Helpers.php` (crea usuario sin autenticar en sesión)
   - `RefreshDatabase` en tests de Browser

2. **Autenticación (Laravel Fortify)**:
   - **Rutas**: `login`, `login.store`, `register`, `register.store`, `logout`, `forgot-password`, `password.request`, `password.email`, `password.update`, `reset-password/{token}`, `email/verify`, `email/verification-notification`, `two-factor-challenge`, etc.
   - **Vistas Livewire**: `livewire/auth/login`, `register`, `forgot-password`, `reset-password`, `verify-email`, `confirm-password`, `two-factor-challenge`
   - **Features**: `registration`, `resetPasswords`, `emailVerification`, `twoFactorAuthentication` (confirm + confirmPassword)
   - **Redirección post-login**: `home` → `/dashboard` (`config/fortify.php`)
   - **Formularios**:
     - Login: `email`, `password`, `remember`, enlace "Forgot your password?", `data-test="login-button"`
     - Register: `name`, `email`, `password`, `password_confirmation`
     - Forgot: `email`, `data-test="email-password-reset-link-button"`
     - Reset: `token` (hidden), `email`, `password`, `password_confirmation`, `data-test="reset-password-button"`
   - **Login Links (solo @env('local'))**: super-admin@, admin@, editor@, viewer@ (Spatie Login Link)

3. **Autorización**:
   - Rutas admin con `middleware(['auth', 'verified'])`
   - Autorización en componentes Livewire vía Policies y `AuthorizesRequests`
   - Roles: `super-admin`, `admin`, `editor`, `viewer` (`App\Support\Roles`)
   - Permisos por módulo (programs, calls, news, documents, events, users, etc.)
   - Dashboard: acceso a cualquier autenticado; contenido según permisos

4. **User Factory**:
   - Contraseña por defecto: `password` (Hash::make('password'))
   - `email_verified_at => now()` por defecto
   - Estados: `unverified()`, `withoutTwoFactor()`
   - `AdminUserSeeder`: super-admin@, admin@, editor@, viewer@ (contraseña `password` en desarrollo)

5. **Rutas relevantes**:
   - Públicas: `/`, `/programas`, `/convocatorias`, `/noticias`, etc.
   - Auth: `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`, `/dashboard`
   - Admin: `/admin`, `/admin/programas`, `/admin/noticias`, `/admin/usuarios`, `/admin/roles`, etc.

### ⚠️ Pendiente de Implementar

1. **Tests de Login** (formulario, validación, redirección, errores)
2. **Tests de Registro** (formulario, validación, creación, verificación de email si aplica)
3. **Tests de Recuperación de contraseña** (solicitud, enlace reset, cambio de contraseña)
4. **Tests de Autorización en rutas públicas** (guest y autenticado)
5. **Tests de Autorización en rutas de administración** (redirect guest, 403 sin permisos, acceso con permisos)

---

## Dependencias y Premisas

- **Email verification**: `User` no implementa `MustVerifyEmail`; `email_verified_at` se usa con middleware `verified`. En tests, crear usuarios con `email_verified_at => now()` para acceder a `/admin`. Si tras registro se exige verificación, cubrir en tests; si no, omitir o marcar como opcional.
- **2FA**: Para flujos de login estándar en tests, usar `User::factory()->withoutTwoFactor()->create([...])` para evitar `two-factor-challenge`.
- **Login Links (Spatie)**: Solo en `@env('local')`; en tests (`APP_ENV=testing`) no se muestran. No basar tests en ellos.
- **Sesión en browser tests**: No hay `actingAs()` en browser. La autenticación debe hacerse con **flujo real**: `visit('/login')` → `fill()` → `click('Log in')` → luego `visit('/admin/...')` en el mismo `$page` para reutilizar cookies de sesión.
- **Navegación**: Pest Browser encadena `visit()` → `click()` → `fill()` → `submit()`; la sesión se mantiene en el mismo “browser”/página. Para tests que requieran estar logueado, hacer login al inicio del test y después `navigate()` o `visit()` a la ruta a probar.

---

## Plan de Trabajo

### Fase 1: Helpers y Datos de Prueba para Autenticación

**Objetivo**: Centralizar la creación de usuarios y la ejecución del flujo de login en helpers reutilizables para todos los tests de Auth y Admin.

#### 1.1. Ampliar `tests/Browser/Helpers.php`

**Archivo**: `tests/Browser/Helpers.php`

- [ ] **Función `createAuthTestUser(array $overrides = [], ?string $role = null): User`**
  - Crear usuario con `User::factory()->withoutTwoFactor()->create(array_merge(['password' => 'password'], $overrides))`. La contraseña `password` (plain) se hashea por el cast del modelo.
  - Si `$role` no es null, llamar a `ensureRolesExist()` (o `RolesAndPermissionsSeeder`) y `$user->assignRole($role)` (p. ej. `Roles::VIEWER`, `Roles::ADMIN` de `App\Support\Roles`).
  - Por defecto `email_verified_at => now()` (el factory ya lo incluye).
  - Devolver el `User` con contraseña conocida `password`.

  ```php
  function createAuthTestUser(array $overrides = [], ?string $role = null): User
  {
      $user = User::factory()->withoutTwoFactor()->create(array_merge(
          ['password' => 'password'],
          $overrides
      ));
      if ($role !== null) {
          ensureRolesExist();
          $user->assignRole($role);
      }
      return $user;
  }
  ```

- [ ] **Función `loginInBrowser($page, User $user): void` o equivalente que devuelva la página tras login**
  - Dado un `$page` (objeto devuelto por `visit('/login')` o `visit('/')` que luego navegue a `/login`), rellenar `email` y `password` con `$user->email` y `'password'`, hacer click en el botón de login (por texto `Log in` o `data-test="login-button"`).
  - Opción: **`performLogin(User $user)`** que hace `visit(route('login'))->fill('email', $user->email)->fill('password', 'password')->click('Log in')` y devuelve la página para encadenar más pasos. Como `visit()` inicia el flujo, la firma más útil es una función que devuelve la página tras login:

  ```php
  function performLogin(\App\Models\User $user)
  {
      $page = visit(route('login'));
      $page->fill('email', $user->email)
           ->fill('password', 'password')
           ->click('Log in'); // o ->click('@login-button') si existe data-test
      return $page;
  }
  ```

  Nota: En Pest Browser, `fill('email', ...)` asume un label "Email" o name "email". Ajustar si las vistas usan `name="email"` (normalmente `fill` se mapea por label o name). Revisar la doc de Pest: `fill('email', 'x')` suele buscar por label o selector.

- [ ] **Documentar** en comentarios que la contraseña de prueba es `'password'` y que los usuarios deben crearse con `withoutTwoFactor()` para evitar 2FA en estos tests.

#### 1.2. Seeder de roles y helper `ensureRolesExist`

- [ ] Crear helper `ensureRolesExist(): void` que ejecute `app(RolesAndPermissionsSeeder::class)->run()` (o `$this->seed(RolesAndPermissionsSeeder::class)` si se llama desde un test con `$this`). Así se crean roles, permisos y su asignación; las Policies que usan `$user->can(Permission::X)` funcionarán correctamente.
- [ ] Opción alternativa: en `beforeEach` de los archivos de test en `tests/Browser/Auth` y `tests/Browser/Admin` que usen roles, ejecutar `RolesAndPermissionsSeeder`. En ese caso `createAuthTestUser` con `$role` puede asumir que los roles ya existen y solo hacer `$user->assignRole($role)`.
- [ ] Decisión recomendada: que `createAuthTestUser(..., $role)` llame a `ensureRolesExist()` la primera vez que se necesita un rol (o en cada llamada si es idempotente). Para evitar ejecutar el seeder en cada test, se puede implementar `ensureRolesExist()` con un `static $run = false` que ejecute el seeder una sola vez por ejecución.

---

### Fase 2: Tests de Login

**Objetivo**: Comprobar formulario, validación, login correcto, redirección y manejo de errores.

**Archivo**: `tests/Browser/Auth/LoginTest.php`

#### 2.1. Estructura y configuración

- [ ] Usar `uses(\Illuminate\Foundation\Testing\RefreshDatabase::class)` (o que Pest ya lo aplique vía `in('Browser')` con `RefreshDatabase`).
- [ ] Importar `User`, `route`, helpers de `Tests\Browser\Helpers` si se extraen a ese namespace.

#### 2.2. Tests a implementar

- [ ] **Test: Verificar formulario de login**
  - `visit(route('login'))`
  - `assertSee('Log in to your account')` o equivalente del `auth-header`
  - `assertSee('Email address')`, `assertSee('Password')`
  - Comprobar que existen inputs `email` y `password` (p. ej. `assertPresent('input[name="email"]')` o equivalente Pest)
  - Comprobar enlace "Forgot your password?" con `assertSeeLink` o `assertSee` + enlace a `route('password.request')`
  - Comprobar enlace "Sign up" a `route('register')` si está presente
  - `assertNoJavascriptErrors()`

- [ ] **Test: Login con credenciales válidas**
  - `createAuthTestUser(['email' => 'test@example.com'])`
  - `performLogin($user)` (o `visit(route('login'))->fill(...)->click('Log in')`)
  - `assertUrlIs(route('dashboard'))` o `assertPathIs('/dashboard')` (según `fortify.home`)
  - `assertSee` algo característico del dashboard (p. ej. "Dashboard" o texto de la vista)
  - `$this->assertAuthenticated()` (Pest Laravel)

- [ ] **Test: Login con credenciales inválidas (email incorrecto)**
  - `User::factory()->create(['email' => 'good@example.com'])` (no usar este en el formulario)
  - `visit(route('login'))->fill('email', 'wrong@example.com')->fill('password', 'password')->click('Log in')`
  - Permanecer en `route('login')` o que se muestre mensaje de error (Fortify suele devolver 422 o redirigir con `errors`)
  - `assertSee` mensaje de error típico (p. ej. "These credentials do not match our records" o la clave de traducción)
  - `$this->assertGuest()`

- [ ] **Test: Login con contraseña incorrecta**
  - `createAuthTestUser(['email' => 'u@ex.com'])`
  - `visit(route('login'))->fill('email', 'u@ex.com')->fill('password', 'wrong')->click('Log in')`
  - Comprobar que sigue en login y se muestra error; `assertGuest()`

- [ ] **Test: Validación – email vacío o formato inválido**
  - `visit(route('login'))->fill('password', 'password')->click('Log in')` (sin email, o con `fill('email','invalid')`)
  - Comprobar que no se produce login y que aparece mensaje de validación (o que se queda en la misma página con errores)

- [ ] **Test: Validación – contraseña vacía**
  - `visit(route('login'))->fill('email', 'a@b.com')->click('Log in')` (sin password)
  - Comprobar que no hay login y hay feedback de validación si el frontend o Fortify lo muestran

- [ ] **Test: Redirección tras login a la URL intentada**
  - Si la app redirige a `login` con `?redirect=...` o similar cuando se accede a una ruta protegida, visitar `/admin` sin estar logueado, comprobar redirección a login, luego hacer login y comprobar que se redirige a `/admin`. Si la app no guarda `intended`, este test puede omitirse o documentarse como "si se implementa redirección intended".

- [ ] **Test: Opción "Remember me"**
  - Crear usuario, login marcando "Remember me" (p. ej. `check('Remember me')` antes de `click('Log in')` si el nombre/selector está disponible), cerrar sesión o simular expiración de sesión; en un test más simple, al menos comprobar que el checkbox existe y que el login con "Remember me" lleva al dashboard. Si es complejo simular la persistencia de la cookie, se puede dejar en "checkbox presente y login exitoso con él marcado".

- [ ] **Test: Navegación desde login a registro y a olvidé contraseña**
  - Desde `visit(route('login'))`, `click('Forgot your password?')` → `assertUrlIs(route('password.request'))`
  - Volver a login, `click('Sign up')` → `assertUrlIs(route('register'))`

- [ ] **Test: Sin errores de JavaScript en la página de login**
  - `visit(route('login'))->assertNoJavascriptErrors()`

---

### Fase 3: Tests de Registro

**Objetivo**: Comprobar formulario, validación, creación de usuario y, si aplica, flujo de verificación de email.

**Archivo**: `tests/Browser/Auth/RegisterTest.php`

#### 3.1. Tests a implementar

- [ ] **Test: Verificar formulario de registro**
  - `visit(route('register'))`
  - `assertSee('Create an account')` o equivalente
  - Comprobar campos: `name`, `email`, `password`, `password_confirmation`
  - Comprobar enlace a `route('login')`
  - `assertNoJavascriptErrors()`

- [ ] **Test: Registro con datos válidos**
  - `visit(route('register'))->fill('name', 'Foo Bar')->fill('email', 'new@example.com')->fill('password', 'SecurePass123!')->fill('password_confirmation', 'SecurePass123!')->click('Create account')` (o el texto exacto del botón)
  - Comprobar que se crea el usuario: `$this->assertDatabaseHas('users', ['email' => 'new@example.com'])`
  - Si tras registro se redirige a dashboard y no se exige email verification: `assertPathIs('/dashboard')` y `assertAuthenticated()`
  - Si se redirige a `email/verify`: `assertUrlIs(route('verification.notice'))` (o path equivalente) y que el usuario existe pero no tiene `email_verified_at` (o el mensaje de verificación). Ajustar según implementación real.

- [ ] **Test: Validación – email duplicado**
  - `User::factory()->create(['email' => 'exists@example.com'])`
  - Intentar registro con `email` `exists@example.com` y datos válidos en el resto
  - Comprobar que no se crea otro usuario y se muestra error de validación

- [ ] **Test: Validación – contraseña y confirmación no coinciden**
  - Registro con `password` != `password_confirmation`
  - Comprobar que se muestra error y no se crea usuario (o que Fortify/Request devuelve error)

- [ ] **Test: Validación – campos requeridos (nombre, email, password)**
  - Enviar formulario vacío o con valores inválidos (email mal formado, password corto si hay regla `min`) y comprobar mensajes de validación

- [ ] **Test: Verificación de email (si aplica)**
  - Si la app envía email de verificación y hay ruta `verification.verify` con hash: en tests se puede usar `Notification::fake()`, registrar usuario, y simular la visita al enlace de verificación construido manualmente con el hash correcto. Comprobar que `email_verified_at` se rellena y que luego puede acceder a rutas con `verified`. Si el flujo es complejo, documentar y dejar un test básico (p. ej. que tras registro se muestra la vista `verification.notice`).

- [ ] **Test: Navegación desde registro a login**
  - `visit(route('register'))->click('Log in')` (o el texto del enlace) → `assertUrlIs(route('login'))`

---

### Fase 4: Tests de Recuperación de Contraseña

**Objetivo**: Comprobar solicitud de enlace, pantalla de reset y cambio de contraseña.

**Archivo**: `tests/Browser/Auth/PasswordResetTest.php`

#### 4.1. Tests a implementar

- [ ] **Test: Verificar formulario "Forgot password"**
  - `visit(route('password.request'))`
  - `assertSee('Forgot password')` o similar
  - Comprobar campo `email` y botón "Email password reset link" (o `data-test="email-password-reset-link-button"`)
  - Enlace de vuelta a login
  - `assertNoJavascriptErrors()`

- [ ] **Test: Solicitud de enlace con email existente**
  - `createAuthTestUser(['email' => 'u@example.com'])`
  - `Notification::fake()`
  - `visit(route('password.request'))->fill('email', 'u@example.com')->click('Email password reset link')` (o selector del botón)
  - Comprobar mensaje de éxito (p. ej. "We have emailed your password reset link" o la clave que use la app)
  - `Notification::assertSentTo($user, ResetPassword::class)` (o la notificación que use Laravel)

- [ ] **Test: Solicitud con email inexistente**
  - `visit(route('password.request'))->fill('email', 'nonexistent@example.com')->click('Email password reset link')`
  - Por seguridad, Laravel suele mostrar el mismo mensaje de éxito (para no revelar si el email existe). Comprobar ese mensaje; no debe enviarse notificación si no hay usuario.

- [ ] **Test: Formulario de reset con token válido**
  - Crear usuario y generar token con `Password::createToken($user)` (o `Password::broker()->createToken($user)`). Construir URL `route('password.reset', ['token' => $token, 'email' => $user->email])`.
  - `visit($url)`
  - Comprobar que se muestran `email` (pre-rellenado), `password`, `password_confirmation` y botón "Reset password" (`data-test="reset-password-button"` si existe)

- [ ] **Test: Cambio de contraseña exitoso**
  - Usuario y token como arriba. `visit($resetUrl)->fill('password', 'NewSecure123!')->fill('password_confirmation', 'NewSecure123!')->click('Reset password')`
  - Redirección a login o a dashboard según Fortify. Comprobar que el usuario puede hacer login con `NewSecure123!` (opcional: hacer `performLogin` con la nueva contraseña y `assertAuthenticated`).

- [ ] **Test: Token inválido o expirado**
  - `visit(route('password.reset', ['token' => 'invalid', 'email' => 'u@example.com']))` y enviar formulario con contraseña nueva. Comprobar mensaje de error (p. ej. "This password reset token is invalid") y que la contraseña del usuario no cambia.

- [ ] **Test: Validación en reset – contraseña y confirmación no coinciden**
  - En la pantalla de reset, `password` != `password_confirmation`. Comprobar error de validación.

- [ ] **Test: Navegación desde "Forgot" a login**
  - `visit(route('password.request'))->click('log in')` (o el texto del enlace) → `assertUrlIs(route('login'))`

---

### Fase 5: Tests de Autorización en Rutas Públicas

**Objetivo**: Comprobar que invitados y autenticados pueden acceder a las páginas públicas sin restricción por auth.

**Archivo**: `tests/Browser/Auth/PublicAuthorizationTest.php` (o integrar en `tests/Browser/Public/` si se prefiere; el plan de 3.11.2 ya cubre contenido público; aquí se enfoca en auth). Se recomienda `tests/Browser/Auth/PublicAuthorizationTest.php` para mantener Auth junto a autorización.

#### 5.1. Tests a implementar

- [ ] **Test: Usuario no autenticado puede acceder a Home**
  - `visit('/')` → `assertOk()` o `assertSee` contenido típico (p. ej. "Erasmus+"), `assertNoJavascriptErrors()`

- [ ] **Test: Usuario no autenticado puede acceder a listados públicos**
  - `visit(route('programas.index'))`, `visit(route('convocatorias.index'))`, `visit(route('noticias.index'))` (o las rutas nombradas que use la app). Para cada una: `assertOk()`, `assertNoJavascriptErrors()`.

- [ ] **Test: Usuario autenticado puede acceder a las mismas páginas públicas**
  - `createAuthTestUser` + `performLogin($user)` (o `loginInBrowser`), luego `visit('/')`, `visit(route('programas.index'))`, etc. Comprobar que se muestran correctamente (sin redirección a login). Opcional: comprobar que en el layout se ve indicador de usuario logueado o enlace a dashboard/admin si aplica.

- [ ] **Test: Usuario autenticado puede acceder a detalle de recurso público**
  - Crear programa/convocatoria/noticia publicada con factories, `performLogin($user)`, `visit(route('programas.show', $program))` (o equivalente). `assertOk()`, `assertSee` algo del recurso.

---

### Fase 6: Tests de Autorización en Rutas de Administración

**Objetivo**: Comprobar redirección de invitados, 403 para usuarios sin permisos y acceso correcto para usuarios con permisos. La autorización se aplica en componentes Livewire vía Policies; el middleware `auth` y `verified` se aseguran antes.

**Archivo**: `tests/Browser/Admin/AdminAuthorizationTest.php`

#### 6.1. Rutas a cubrir (selección representativa)

- `/admin` (dashboard)
- `/admin/programas` (ProgramPolicy: viewAny)
- `/admin/noticias` (NewsPostPolicy: viewAny)
- `/admin/usuarios` (UserPolicy: viewAny; típicamente solo admin/super-admin)
- `/admin/roles` (RolePolicy: viewAny; típicamente solo admin/super-admin)

Se pueden añadir más rutas (convocatorias, documentos, etc.) en la misma estructura.

#### 6.2. Tests a implementar

- [ ] **Test: Usuario no autenticado es redirigido a login**
  - Para cada ruta de la selección: `visit(route('admin.dashboard'))` (y equivalentes). Comprobar `assertRedirect` a `route('login')` o que la URL final es `login` (en browser a veces se sigue la redirección; en ese caso `assertUrlIs(route('login'))` tras `visit`). Aplicar a: `admin.dashboard`, `admin.programs.index`, `admin.news.index`, `admin.users.index`, `admin.roles.index`.

- [ ] **Test: Usuario autenticado sin permisos recibe 403 en rutas que requieren permiso**
  - Crear usuario **sin roles** (o con rol `viewer` para rutas que requieren más que view, según Policies):
    - Para `admin.users.index` y `admin.roles.index`: típicamente solo admin/super-admin. Usuario `viewer` o sin rol: `performLogin($user)` luego `visit(route('admin.users.index'))` → `assertSee` texto de 403 o `assertStatus` 403. (En Pest Browser puede que la respuesta sea 200 con cuerpo "403" o una vista de error; en ese caso `assertSee('Forbidden')` o similar.)
  - Repetir para `admin.roles.index` con usuario sin permiso de roles.

- [ ] **Test: Usuario con rol viewer puede acceder al dashboard**
  - `createAuthTestUser(..., Roles::VIEWER)` (asegurando que el rol existe y tiene permisos de solo lectura). `performLogin($user)` → `visit(route('admin.dashboard'))` → `assertOk()` y `assertSee` algo del dashboard.

- [ ] **Test: Usuario con rol viewer puede acceder a listados de solo lectura**
  - Módulos donde `viewer` tiene `view`/`viewAny`: p. ej. `admin.programs.index`, `admin.news.index`. `performLogin($user)` con `viewer` → `visit(route('admin.programs.index'))` → `assertOk()`, `assertSee` texto del listado. Idem para noticias si aplica.

- [ ] **Test: Usuario con rol viewer recibe 403 en módulos que no tienen permiso**
  - Según `Permissions::viewOnly()` y las Policies, `viewer` puede no tener acceso a `admin.users.index` ni `admin.roles.index`. `performLogin($viewer)` → `visit(route('admin.users.index'))` → 403 o mensaje de denegación.

- [ ] **Test: Usuario admin puede acceder a programas, noticias y usuarios**
  - `createAuthTestUser(..., Roles::ADMIN)` + `performLogin` → `visit(route('admin.programs.index'))`, `admin.news.index`, `admin.users.index`. `assertOk()` y contenido esperado.

- [ ] **Test: Usuario super-admin puede acceder a todas las rutas de la selección**
  - `createAuthTestUser(..., Roles::SUPER_ADMIN)` + `performLogin` → para cada ruta: `visit(...)` → `assertOk()`.

- [ ] **Test: Usuario sin email verificado es redirigido (middleware `verified`)**
  - `User::factory()->unverified()->withoutTwoFactor()->create([...])` y asignar un rol con acceso. `performLogin($user)` (el login puede funcionar) y luego `visit(route('admin.dashboard'))`. Si el middleware `verified` redirige a `verification.notice`, `assertUrlIs(route('verification.notice'))` o equivalente. Si la app no usa `verified` en admin, omitir.

- [ ] **Test: Logout y acceso de nuevo a admin**
  - Login → `visit(route('admin.dashboard'))` → `assertOk()`. Luego hacer logout: si existe un botón/enlace "Log out" o "Cerrar sesión" en el layout (p. ej. menú de usuario), `click` en él (suele enviar POST a `logout`); si la app usa solo una ruta GET, `visit(route('logout'))`. Tras cerrar sesión, `visit(route('admin.dashboard'))` → redirección a login.

---

### Fase 7: Helpers para Pest Browser (fill, click, selectores)

**Objetivo**: Ajustar los selectores y la API de Pest a las vistas reales.

- [ ] Revisar en vistas de auth los `name`, `id`, `data-test` y etiquetas de los campos. Ajustar en los tests:
  - `fill('email', ...)`: Pest suele aceptar label "Email address" o `name="email"`.
  - `fill('password', ...)`: análogo.
  - `click('Log in')`: si el botón tiene `data-test="login-button"`, se puede usar `click('@login-button')` si Pest lo soporta, o `click('Log in')` por texto.
- [ ] Si `fill` no encuentra el campo, usar `fill('input[name="email"]', ...)` o el selector que admita Pest. Documentar en el plan la convención adoptada.
- [ ] Para "Remember me": `check('Remember me')` si existe; si el checkbox tiene `name="remember"`, podría ser `check('remember')` según la API.

---

### Fase 8: Documentación y Verificación Final

#### 8.1. Documentación

- [ ] Crear o actualizar `docs/browser-testing-auth.md` (o sección en `docs/browser-testing-setup.md`) con:
  - Resumen de los archivos de tests: `LoginTest`, `RegisterTest`, `PasswordResetTest`, `PublicAuthorizationTest`, `AdminAuthorizationTest`.
  - Descripción de helpers: `createAuthTestUser`, `performLogin`, `ensureRolesExist` (si se crea).
  - Convenciones: contraseña `password`, uso de `withoutTwoFactor`, uso de `unverified` cuando se testea `verified`.
  - Comandos: `./vendor/bin/pest tests/Browser/Auth`, `./vendor/bin/pest tests/Browser/Admin/AdminAuthorizationTest.php`, `--headed`, `--debug`.

#### 8.2. Actualizar `docs/planificacion_pasos.md`

- [ ] En el paso 3.11.3, marcar como completados los ítems correspondientes según el avance.

#### 8.3. Verificación final

- [ ] Ejecutar `./vendor/bin/pest tests/Browser/Auth` y `./vendor/bin/pest tests/Browser/Admin/AdminAuthorizationTest.php` y comprobar que todos pasan.
- [ ] Revisar que no queden `skip()` o `todo()` sin justificar.
- [ ] Comprobar que `RolesAndPermissionsSeeder` o `ensureRolesExist` no dejan la BD en estado que rompa otros tests (por ejemplo, si se ejecuta en `beforeEach` solo para Auth/Admin, el resto de tests no debería depender de esos roles salvo que ya se haga en el proyecto).

---

## Estructura de Archivos Final

```
tests/
├── Browser/
│   ├── Helpers.php                          # + createAuthTestUser, performLogin, ensureRolesExist (si aplica)
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   ├── PasswordResetTest.php
│   │   └── PublicAuthorizationTest.php
│   └── Admin/
│       └── AdminAuthorizationTest.php
```

---

## Criterios de Éxito

1. **Login**: formulario, login válido, inválido, validaciones, redirección y "Remember me" cubiertos.
2. **Registro**: formulario, registro válido, validaciones (duplicado, contraseña/confirmación, requeridos) y verificación de email si aplica.
3. **Recuperación de contraseña**: formulario forgot, envío de enlace, formulario reset, cambio exitoso, token inválido y validaciones.
4. **Autorización pública**: guest y autenticado acceden a Home y listados/detalles públicos.
5. **Autorización admin**: guest → login; sin permisos → 403; viewer/admin/super-admin acceden según Policies; usuario no verificado → `verification.notice` si aplica; logout y nuevo acceso a admin redirige a login.
6. **Helpers** reutilizables y documentados.
7. **Documentación** en `docs` y `planificacion_pasos.md` actualizada.

---

## Notas Importantes

1. **Pest Browser y sesión**: La autenticación en browser tests se hace con flujo real (formulario de login). No hay `actingAs` en el contexto del navegador; la sesión se mantiene por cookies en el mismo flujo de `visit`/`navigate`/`click`.

2. **2FA**: Los usuarios de tests deben usar `withoutTwoFactor()` para no caer en `two-factor-challenge` en los flujos estándar. Tests específicos de 2FA (pantalla de desafío, códigos de recuperación) pueden planificarse en un paso posterior.

3. **Email verification**: Si `User` implementa `MustVerifyEmail` y Fortify está configurado para exigir verificación, los tests de registro y de admin deben contemplar `verification.notice` y la ruta `verification.verify`. Si no, se simplifican.

4. **Roles y permisos**: La existencia de roles (y permisos) debe garantizarse en los tests que usan `createAuthTestUser(..., $role)`. `RolesAndPermissionsSeeder` o un helper `ensureRolesExist` que haga `firstOrCreate` evita fallos por tablas vacías.

5. **Nombres de rutas**: Verificar en `php artisan route:list` los nombres exactos: `login`, `register`, `password.request`, `password.update`, `password.reset`, `verification.notice`, `dashboard`, `admin.dashboard`, `admin.programs.index`, etc., y usarlos en los tests.

6. **Logout**: Fortify suele registrar `POST /logout`. Si en la UI el cierre de sesión es un botón que hace POST, en Pest se puede usar `click` en ese botón desde una página donde exista (p. ej. dashboard o layout). Si no hay tal botón en las vistas de auth, se puede simular `post(route('logout'))` en un test Laravel estándar; para browser, hacer click en "Log out" donde esté (p. ej. menú de usuario en el layout). Ajustar según la implementación.

---

## Próximos Pasos

Tras completar el paso 3.11.3:

- **Paso 3.11.4**: Tests de formularios y validación en tiempo real (newsletter, búsqueda, etc.).
- **Paso 3.11.5**: Tests de interacciones JavaScript y componentes dinámicos (Livewire, filtros, modales, etc.).

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan listo para implementación
