# Documentación Técnica: Sistema de Suscripción a Newsletter

Este documento describe la arquitectura y uso del sistema completo de suscripción a newsletter implementado en la aplicación Erasmus+ Centro (Murcia).

---

## Índice

1. [Arquitectura General](#arquitectura-general)
2. [Modelo NewsletterSubscription](#modelo-newslettersubscription)
3. [Form Request y Validación](#form-request-y-validación)
4. [Componentes Livewire](#componentes-livewire)
5. [Email de Verificación](#email-de-verificación)
6. [Rutas](#rutas)
7. [Seeders](#seeders)
8. [Tests](#tests)
9. [Guía de Uso](#guía-de-uso)

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                     Layout Público                               │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Public Nav                                ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │              Livewire Components                            ││
│  │                                                              ││
│  │  Newsletter\Subscribe    Newsletter\Verify                  ││
│  │  Newsletter\Unsubscribe                                     ││
│  │  ┌──────────────┐        ┌──────────────┐                  ││
│  │  │ Formulario   │        │ Verificación │                  ││
│  │  │ - Email      │        │ por Token    │                  ││
│  │  │ - Nombre     │        └──────────────┘                  ││
│  │  │ - Programas  │        ┌──────────────┐                  ││
│  │  │ - Privacidad │        │ Baja         │                  ││
│  │  └──────────────┘        │ por Email/   │                  ││
│  │                           │ Token        │                  ││
│  │                           └──────────────┘                  ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Email Service                             ││
│  │              NewsletterVerificationMail                      ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

---

## Modelo NewsletterSubscription

### Estructura de la Tabla

**Ubicación:** `app/Models/NewsletterSubscription.php`

**Campos:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único |
| `email` | string(255) | Email único del suscriptor |
| `name` | string(255) nullable | Nombre opcional |
| `programs` | json nullable | Array de códigos de programas de interés |
| `is_active` | boolean | Estado activo/inactivo |
| `subscribed_at` | timestamp | Fecha de suscripción |
| `unsubscribed_at` | timestamp nullable | Fecha de baja |
| `verification_token` | string(32) nullable | Token para verificación |
| `verified_at` | timestamp nullable | Fecha de verificación |

### Scopes

**Ubicación:** `app/Models/NewsletterSubscription.php`

```php
// Suscripciones activas
NewsletterSubscription::active()->get();

// Suscripciones verificadas
NewsletterSubscription::verified()->get();

// Suscripciones sin verificar
NewsletterSubscription::unverified()->get();

// Suscripciones para un programa específico
NewsletterSubscription::forProgram('KA1xx')->get();

// Suscripciones verificadas para un programa específico
NewsletterSubscription::verifiedForProgram('KA1xx')->get();
```

### Métodos Helper

```php
// Verificar estado
$subscription->isVerified(); // bool
$subscription->isActive(); // bool
$subscription->hasProgram('KA1xx'); // bool

// Acciones
$subscription->verify(); // Marca como verificada y activa
$subscription->unsubscribe(); // Marca como inactiva y establece fecha de baja
$token = $subscription->generateVerificationToken(); // Genera token de 32 caracteres
```

### Casts

```php
protected function casts(): array
{
    return [
        'programs' => 'array',
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
}
```

---

## Form Request y Validación

### StoreNewsletterSubscriptionRequest

**Ubicación:** `app/Http/Requests/StoreNewsletterSubscriptionRequest.php`

**Reglas de validación:**

```php
'email' => [
    'required',
    'string',
    'email',
    'max:255',
    Rule::unique('newsletter_subscriptions', 'email'),
],
'name' => [
    'nullable',
    'string',
    'max:255',
],
'programs' => [
    'nullable',
    'array',
],
'programs.*' => [
    'string',
    Rule::exists('programs', 'code'),
],
```

**Mensajes personalizados:**

Los mensajes de validación están internacionalizados en:
- `lang/es/validation.php`
- `lang/en/validation.php`

**Atributos personalizados:**

```php
'newsletter_email' => 'correo electrónico',
'newsletter_name' => 'nombre',
'newsletter_programs' => 'programas de interés',
```

---

## Componentes Livewire

### Newsletter\Subscribe

**Ubicación:** `app/Livewire/Public/Newsletter/Subscribe.php`

**Vista:** `resources/views/livewire/public/newsletter/subscribe.blade.php`

**Propiedades públicas:**

```php
public string $email = '';
public string $name = '';
public array $selectedPrograms = [];
public bool $acceptPrivacy = false;
public bool $subscribed = false;
```

**Computed properties:**

```php
#[Computed]
public function availablePrograms(): Collection
{
    return Program::where('is_active', true)
        ->orderBy('order')
        ->orderBy('name')
        ->get();
}
```

**Métodos principales:**

- `subscribe()` - Procesa la suscripción, crea registro, genera token y envía email
- `resetForm()` - Limpia el formulario después de suscripción exitosa
- `toggleProgram(string $programCode)` - Alterna selección de programa
- `isProgramSelected(string $programCode)` - Verifica si un programa está seleccionado

**Flujo de suscripción:**

1. Usuario completa formulario (email requerido, nombre opcional, programas opcionales)
2. Usuario acepta política de privacidad
3. Se valida email único y programas válidos
4. Se crea suscripción con `is_active = false` y `verified_at = null`
5. Se genera token de verificación de 32 caracteres
6. Se envía email de verificación
7. Se muestra mensaje de éxito y se resetea formulario

**Características:**

- Validación en tiempo real con Livewire
- Selección múltiple de programas con checkboxes
- Mensajes de error personalizados
- Diseño responsive con Flux UI
- Integración con layout público

### Newsletter\Verify

**Ubicación:** `app/Livewire/Public/Newsletter/Verify.php`

**Vista:** `resources/views/livewire/public/newsletter/verify.blade.php`

**Propiedades públicas:**

```php
public ?string $token = null;
public ?NewsletterSubscription $subscription = null;
public string $status = 'pending'; // 'pending', 'success', 'already_verified', 'invalid', 'error'
public string $message = '';
```

**Flujo de verificación:**

1. Componente recibe token por URL (`/newsletter/verificar/{token}`)
2. Busca suscripción con token válido
3. Verifica estado:
   - Si ya está verificada → `status = 'already_verified'`
   - Si token inválido → `status = 'invalid'`
   - Si verificación exitosa → `status = 'success'` y activa suscripción

**Estados posibles:**

- `pending` - Verificación en proceso
- `success` - Verificación exitosa
- `already_verified` - Ya estaba verificada
- `invalid` - Token inválido o expirado
- `error` - Error al procesar verificación

### Newsletter\Unsubscribe

**Ubicación:** `app/Livewire/Public/Newsletter/Unsubscribe.php`

**Vista:** `resources/views/livewire/public/newsletter/unsubscribe.blade.php`

**Propiedades públicas:**

```php
public ?string $token = null;
public string $email = '';
public ?NewsletterSubscription $subscription = null;
public string $status = 'form'; // 'form', 'pending', 'success', 'already_unsubscribed', 'not_found', 'error'
public string $message = '';
```

**Dos métodos de baja:**

1. **Por token** (automático):
   - URL: `/newsletter/baja/{token}`
   - Busca suscripción por token
   - Si existe y está activa → da de baja automáticamente

2. **Por email** (manual):
   - Formulario con campo email
   - Usuario ingresa email
   - Busca suscripción por email
   - Si existe y está activa → da de baja

**Estados posibles:**

- `form` - Mostrando formulario de baja
- `pending` - Procesando baja
- `success` - Baja exitosa
- `already_unsubscribed` - Ya estaba dado de baja
- `not_found` - No se encontró suscripción
- `error` - Error al procesar baja

---

## Email de Verificación

### NewsletterVerificationMail

**Ubicación:** `app/Mail/NewsletterVerificationMail.php`

**Vista:** `resources/views/emails/newsletter/verification.blade.php`

**Características:**

- Diseño responsive compatible con clientes de email
- Estilo Erasmus+ (colores #003399)
- Mensaje personalizado con nombre (si está disponible)
- Botón de verificación destacado
- Enlace alternativo para accesibilidad
- Información sobre programas seleccionados
- Sección informativa sobre qué recibirán
- Enlace de cancelación de suscripción
- Compatibilidad con Outlook (comentarios condicionales)

**Estructura del email:**

1. Header con gradiente Erasmus+
2. Saludo personalizado
3. Mensaje de bienvenida
4. Botón de verificación
5. Enlace alternativo
6. Información de programas (si aplica)
7. Sección "¿Qué recibirás?"
8. Enlace de cancelación
9. Footer con información del centro

**URLs generadas:**

```php
// URL de verificación
$verificationUrl = route('newsletter.verify', ['token' => $token]);

// URL de cancelación
$unsubscribeUrl = route('newsletter.unsubscribe.token', ['token' => $token]);
```

---

## Rutas

**Ubicación:** `routes/web.php`

```php
// Suscripción
Route::get('/newsletter/suscribir', Newsletter\Subscribe::class)
    ->name('newsletter.subscribe');

// Verificación por token
Route::get('/newsletter/verificar/{token}', Newsletter\Verify::class)
    ->name('newsletter.verify');

// Baja (formulario)
Route::get('/newsletter/baja', Newsletter\Unsubscribe::class)
    ->name('newsletter.unsubscribe');

// Baja por token (automático)
Route::get('/newsletter/baja/{token}', Newsletter\Unsubscribe::class)
    ->name('newsletter.unsubscribe.token');
```

**Integración en Home:**

- Sección de newsletter en página principal (`resources/views/livewire/public/home.blade.php`)
- Enlace en footer (`resources/views/components/footer.blade.php`)

---

## Seeders

### NewsletterSubscriptionSeeder

**Ubicación:** `database/seeders/NewsletterSubscriptionSeeder.php`

**Distribución de datos:**

- **Total:** 80 suscripciones
- **Verificadas y activas:** 48 (60%)
- **Sin verificar:** 16 (20%)
- **Dadas de baja:** 16 (20%)

**Distribución de programas:**

- **30%** con todos los programas activos
- **50%** con algunos programas (1-3)
- **20%** sin programas seleccionados

**Fechas variadas:**

- Suscripciones verificadas: últimos 1-6 meses
- Sin verificar: últimos 1-30 días
- Dadas de baja: suscritas hace 2-6 meses, dadas de baja hace 7-90 días después de verificación

**Integración:**

```php
// database/seeders/DatabaseSeeder.php
NewsletterSubscriptionSeeder::class,
```

---

## Tests

### Cobertura de Tests

**Total:** 45+ tests cubriendo:

- Validación de formularios
- Flujos de suscripción/verificación/baja
- Envío de emails
- Estados de suscripción
- Scopes del modelo
- Métodos helper del modelo
- Manejo de errores
- Casos edge

### Archivos de Tests

1. **`tests/Feature/Livewire/Public/Newsletter/SubscribeTest.php`**
   - 22 tests (57 assertions)
   - Renderizado, validación, flujo de suscripción, selección de programas

2. **`tests/Feature/Livewire/Public/Newsletter/VerifyTest.php`**
   - 8+ tests
   - Verificación exitosa, ya verificada, token inválido

3. **`tests/Feature/Livewire/Public/Newsletter/UnsubscribeTest.php`**
   - 12+ tests
   - Baja por token, baja por email, estados de error

4. **`tests/Feature/Models/NewsletterSubscriptionScopesTest.php`**
   - 13 tests (35 assertions)
   - Scopes, métodos helper, estados

### Ejemplos de Tests

```php
// Test de suscripción exitosa
it('successfully subscribes with email only', function () {
    Mail::fake();
    
    Livewire::test(Subscribe::class)
        ->set('email', 'test@example.com')
        ->set('acceptPrivacy', true)
        ->call('subscribe')
        ->assertSet('subscribed', true);
    
    Mail::assertSent(NewsletterVerificationMail::class);
});

// Test de verificación
it('verifies subscription with valid token', function () {
    $subscription = NewsletterSubscription::factory()->unverified()->create([
        'verification_token' => $token = Str::random(32),
    ]);
    
    Livewire::test(Verify::class, ['token' => $token])
        ->assertSet('status', 'success');
    
    expect($subscription->fresh()->is_active)->toBeTrue();
});
```

---

## Guía de Uso

### Para Usuarios

#### Suscribirse

1. Acceder a `/newsletter/suscribir` o desde la sección de newsletter en la home
2. Completar email (requerido)
3. Opcionalmente agregar nombre
4. Opcionalmente seleccionar programas de interés
5. Aceptar política de privacidad
6. Enviar formulario
7. Recibir email de verificación
8. Hacer clic en el enlace de verificación

#### Verificar Suscripción

1. Recibir email de verificación
2. Hacer clic en el botón "Verificar suscripción"
3. Ser redirigido a página de confirmación
4. Suscripción activada automáticamente

#### Darse de Baja

**Opción 1: Por email del newsletter**
- Hacer clic en enlace de cancelación en cualquier email
- Ser redirigido a página de confirmación
- Baja automática

**Opción 2: Por formulario**
1. Acceder a `/newsletter/baja`
2. Ingresar email
3. Confirmar baja
4. Recibir confirmación

### Para Desarrolladores

#### Crear Nueva Suscripción Programáticamente

```php
use App\Models\NewsletterSubscription;

$subscription = NewsletterSubscription::create([
    'email' => 'user@example.com',
    'name' => 'John Doe',
    'programs' => ['KA1xx', 'KA121-VET'],
    'is_active' => false,
]);

$token = $subscription->generateVerificationToken();
Mail::to($subscription->email)->send(new NewsletterVerificationMail($subscription, $token));
```

#### Consultar Suscripciones

```php
// Suscripciones activas verificadas
$active = NewsletterSubscription::active()->verified()->get();

// Suscripciones para un programa específico
$forProgram = NewsletterSubscription::verifiedForProgram('KA1xx')->get();

// Verificar si una suscripción tiene un programa
if ($subscription->hasProgram('KA1xx')) {
    // Enviar email específico del programa
}
```

#### Enviar Newsletter a Suscriptores

```php
use App\Models\NewsletterSubscription;

// A todos los suscriptores activos verificados
$subscribers = NewsletterSubscription::active()->verified()->get();

foreach ($subscribers as $subscriber) {
    Mail::to($subscriber->email)->send(new NewsletterMail($content));
}

// Solo a suscriptores de un programa específico
$subscribers = NewsletterSubscription::verifiedForProgram('KA1xx')->get();
```

---

## Características Técnicas

### Seguridad

- Validación de email único
- Tokens de verificación de 32 caracteres aleatorios
- Verificación obligatoria antes de activar suscripción
- Baja fácil con un solo clic desde email

### Internacionalización

- Mensajes de validación en español e inglés
- Vistas traducidas
- Emails traducidos

### Accesibilidad

- Formularios accesibles con labels apropiados
- Mensajes de error claros
- Enlaces alternativos en emails
- Diseño responsive

### Performance

- Scopes optimizados para consultas eficientes
- Índices en campos clave (email, verification_token)
- Paginación cuando sea necesario

---

## Mejoras Futuras

- [ ] Panel de administración para gestionar suscripciones
- [ ] Estadísticas de suscripciones (crecimiento, programas más populares)
- [ ] Envío masivo de newsletters desde panel
- [ ] Plantillas de email personalizables
- [ ] Segmentación avanzada por programas
- [ ] Exportación de suscriptores a CSV
- [ ] Re-suscripción automática después de baja
- [ ] Confirmación de baja por email

---

## Referencias

- **Plan de Desarrollo:** `docs/pasos/paso-3.4.7-plan.md`
- **Historial de Desarrollo:** `docs/pasos/paso13.md`
- **Modelo:** `app/Models/NewsletterSubscription.php`
- **Componentes Livewire:** `app/Livewire/Public/Newsletter/`
- **Tests:** `tests/Feature/Livewire/Public/Newsletter/`

