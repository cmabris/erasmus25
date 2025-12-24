# Plan de Desarrollo - Paso 3.4.7: Suscripción a Newsletter

Este documento establece el plan detallado para desarrollar el paso 3.4.7 de la planificación: **Suscripción a Newsletter**.

## Objetivo

Implementar un sistema completo de suscripción a newsletter con:
- Formulario público de suscripción moderno y accesible
- Validación de email y verificación por correo electrónico
- Selección de programas de interés
- Confirmación de suscripción
- Gestión de suscripciones (verificación, baja)
- Seeder con datos de prueba realistas
- Tests completos

## Análisis del Estado Actual

### ✅ Ya Implementado
- **Modelo**: `NewsletterSubscription` con campos:
  - `email` (único)
  - `name` (opcional)
  - `programs` (JSON array)
  - `is_active` (boolean)
  - `subscribed_at` (timestamp)
  - `unsubscribed_at` (nullable timestamp)
  - `verification_token` (nullable string)
  - `verified_at` (nullable timestamp)
- **Factory**: `NewsletterSubscriptionFactory` con estados `unsubscribed()` y `unverified()`
- **Tests básicos**: Tests de relaciones y casts del modelo

### ⏳ Pendiente de Implementar
- Form Request para validación de suscripción
- Componente Livewire público `Newsletter\Subscribe`
- Componente Livewire para verificación de email `Newsletter\Verify`
- Componente Livewire para darse de baja `Newsletter\Unsubscribe`
- Vista Blade del formulario de suscripción
- Vista Blade de confirmación de suscripción
- Vista Blade de verificación de email
- Vista Blade de baja de suscripción
- Rutas públicas
- Seeder con datos de prueba
- Tests completos (Feature tests)
- Mejoras al modelo (scopes y métodos helper)

## Plan de Desarrollo por Fases

### **Fase 1: Form Request y Validación**

**Objetivo**: Crear la capa de validación para las suscripciones.

**Tareas**:
1. Crear `StoreNewsletterSubscriptionRequest`:
   - Validar `email`: requerido, email válido, único en tabla `newsletter_subscriptions`
   - Validar `name`: opcional, string, max 255
   - Validar `programs`: opcional, array, cada elemento debe existir en tabla `programs` (por código)
   - Mensajes de error personalizados en español e inglés

2. Actualizar archivos de traducción:
   - `lang/es/validation.php`
   - `lang/en/validation.php`
   - Agregar mensajes para campos de newsletter

**Archivos a crear**:
- `app/Http/Requests/StoreNewsletterSubscriptionRequest.php`

**Archivos a modificar**:
- `lang/es/validation.php`
- `lang/en/validation.php`

---

### **Fase 2: Mejoras al Modelo NewsletterSubscription**

**Objetivo**: Agregar scopes y métodos helper útiles al modelo.

**Tareas**:
1. Agregar scopes:
   - `scopeActive()` - Solo suscripciones activas
   - `scopeVerified()` - Solo suscripciones verificadas
   - `scopeUnverified()` - Solo suscripciones sin verificar
   - `scopeForProgram($programCode)` - Suscripciones para un programa específico
   - `scopeVerifiedForProgram($programCode)` - Suscripciones verificadas para un programa

2. Agregar métodos helper:
   - `isVerified()` - ¿Está verificada?
   - `isActive()` - ¿Está activa?
   - `verify()` - Marcar como verificada
   - `unsubscribe()` - Darse de baja
   - `generateVerificationToken()` - Generar token de verificación
   - `hasProgram($programCode)` - ¿Tiene suscrito un programa específico?

**Archivos a modificar**:
- `app/Models/NewsletterSubscription.php`

---

### **Fase 3: Componente Livewire de Suscripción**

**Objetivo**: Crear el componente principal de suscripción pública.

**Tareas**:
1. Crear `app/Livewire/Public/Newsletter/Subscribe.php`:
   - Propiedades: `email`, `name`, `selectedPrograms` (array)
   - Método `availablePrograms()` (computed) - Programas activos disponibles
   - Método `subscribe()` - Procesar suscripción:
     - Validar datos con Form Request
     - Crear suscripción con `is_active = false` inicialmente
     - Generar token de verificación
     - Enviar email de verificación (usar Mail facade o Notification)
     - Mostrar mensaje de éxito
   - Método `resetForm()` - Limpiar formulario después de suscripción exitosa
   - Manejo de errores y validación en tiempo real

2. Crear vista `resources/views/livewire/public/newsletter/subscribe.blade.php`:
   - Diseño moderno siguiendo el estilo de la aplicación
   - Formulario con campos:
     - Email (requerido)
     - Nombre (opcional)
     - Selección múltiple de programas (checkboxes o multi-select)
     - Checkbox de aceptación de términos/privacidad
   - Mensajes de éxito/error
   - Diseño responsive
   - Uso de componentes Flux UI existentes

**Archivos a crear**:
- `app/Livewire/Public/Newsletter/Subscribe.php`
- `resources/views/livewire/public/newsletter/subscribe.blade.php`

---

### **Fase 4: Componente Livewire de Verificación**

**Objetivo**: Permitir verificar el email mediante token.

**Tareas**:
1. Crear `app/Livewire/Public/Newsletter/Verify.php`:
   - Propiedad `token` (URL parameter)
   - Método `mount($token)` - Buscar suscripción por token
   - Método `verify()` - Verificar suscripción:
     - Buscar suscripción por token
     - Verificar que no esté ya verificada
     - Marcar como verificada (`verified_at`, `is_active = true`)
     - Mostrar mensaje de éxito
   - Manejo de errores (token inválido, ya verificado, etc.)

2. Crear vista `resources/views/livewire/public/newsletter/verify.blade.php`:
   - Página de confirmación de verificación
   - Mensaje de éxito o error
   - Botón para ir a la página principal

**Archivos a crear**:
- `app/Livewire/Public/Newsletter/Verify.php`
- `resources/views/livewire/public/newsletter/verify.blade.php`

---

### **Fase 5: Componente Livewire de Baja**

**Objetivo**: Permitir darse de baja de la newsletter.

**Tareas**:
1. Crear `app/Livewire/Public/Newsletter/Unsubscribe.php`:
   - Propiedad `token` (URL parameter) o `email` (formulario)
   - Método `mount($token = null)` - Si hay token, buscar suscripción
   - Método `unsubscribe()` - Procesar baja:
     - Buscar suscripción por email o token
     - Marcar como inactiva (`is_active = false`, `unsubscribed_at`)
     - Mostrar mensaje de confirmación
   - Manejo de errores (email no encontrado, ya dado de baja, etc.)

2. Crear vista `resources/views/livewire/public/newsletter/unsubscribe.blade.php`:
   - Formulario simple con email (si no hay token)
   - Confirmación de baja
   - Mensaje de éxito

**Archivos a crear**:
- `app/Livewire/Public/Newsletter/Unsubscribe.php`
- `resources/views/livewire/public/newsletter/unsubscribe.blade.php`

---

### **Fase 6: Email de Verificación**

**Objetivo**: Crear el email que se envía para verificar la suscripción.

**Tareas**:
1. Crear Mailable `app/Mail/NewsletterVerificationMail.php`:
   - Propiedades: `subscription`, `verificationUrl`
   - Método `build()` - Construir email:
     - Asunto: "Verifica tu suscripción a la newsletter Erasmus+"
     - Vista: `emails.newsletter.verification`
     - Incluir botón de verificación con URL

2. Crear vista de email `resources/views/emails/newsletter/verification.blade.php`:
   - Diseño moderno y responsive
   - Mensaje de bienvenida
   - Botón de verificación
   - Link alternativo si el botón no funciona
   - Información sobre cómo darse de baja

**Archivos a crear**:
- `app/Mail/NewsletterVerificationMail.php`
- `resources/views/emails/newsletter/verification.blade.php`

---

### **Fase 7: Rutas Públicas**

**Objetivo**: Definir las rutas públicas para newsletter.

**Tareas**:
1. Agregar rutas en `routes/web.php`:
   - `GET /newsletter/suscribir` → `Newsletter\Subscribe` (componente Livewire)
   - `GET /newsletter/verificar/{token}` → `Newsletter\Verify` (componente Livewire)
   - `GET /newsletter/baja` → `Newsletter\Unsubscribe` (componente Livewire)
   - `GET /newsletter/baja/{token}` → `Newsletter\Unsubscribe` (componente Livewire con token)

**Archivos a modificar**:
- `routes/web.php`

---

### **Fase 8: Seeder de Datos de Prueba**

**Objetivo**: Crear suscripciones de prueba para desarrollo.

**Tareas**:
1. Crear `database/seeders/NewsletterSubscriptionSeeder.php`:
   - Generar 50-100 suscripciones de prueba
   - Variedad de estados:
     - Suscripciones verificadas y activas (60%)
     - Suscripciones sin verificar (20%)
     - Suscripciones dadas de baja (20%)
   - Distribución de programas:
     - Algunas con todos los programas
     - Algunas con programas específicos
     - Algunas sin programas seleccionados
   - Fechas variadas (últimos 6 meses)
   - Emails realistas pero de prueba

2. Integrar en `DatabaseSeeder.php`:
   - Agregar llamada al seeder (solo en desarrollo)

**Archivos a crear**:
- `database/seeders/NewsletterSubscriptionSeeder.php`

**Archivos a modificar**:
- `database/seeders/DatabaseSeeder.php`

---

### **Fase 9: Tests Completos**

**Objetivo**: Asegurar cobertura completa de tests.

**Tareas**:
1. Crear `tests/Feature/Livewire/Public/Newsletter/SubscribeTest.php`:
   - Test: Suscripción exitosa con email válido
   - Test: Suscripción con programas seleccionados
   - Test: Validación de email duplicado
   - Test: Validación de email inválido
   - Test: Validación de programas inválidos
   - Test: Email de verificación enviado
   - Test: Reset del formulario después de suscripción

2. Crear `tests/Feature/Livewire/Public/Newsletter/VerifyTest.php`:
   - Test: Verificación exitosa con token válido
   - Test: Error con token inválido
   - Test: Error con token ya verificado
   - Test: Activación de suscripción al verificar

3. Crear `tests/Feature/Livewire/Public/Newsletter/UnsubscribeTest.php`:
   - Test: Baja exitosa por email
   - Test: Baja exitosa por token
   - Test: Error con email no encontrado
   - Test: Error con email ya dado de baja

4. Crear `tests/Feature/Models/NewsletterSubscriptionScopesTest.php`:
   - Tests para todos los scopes del modelo
   - Tests para métodos helper

**Archivos a crear**:
- `tests/Feature/Livewire/Public/Newsletter/SubscribeTest.php`
- `tests/Feature/Livewire/Public/Newsletter/VerifyTest.php`
- `tests/Feature/Livewire/Public/Newsletter/UnsubscribeTest.php`
- `tests/Feature/Models/NewsletterSubscriptionScopesTest.php`

---

### **Fase 10: Integración en Layout Público (Opcional)**

**Objetivo**: Agregar formulario de suscripción rápida en footer o sidebar.

**Tareas**:
1. Crear componente Blade `resources/views/components/newsletter/quick-subscribe.blade.php`:
   - Formulario compacto solo con email
   - Integración con componente Livewire Subscribe
   - Diseño minimalista para footer

2. Integrar en layout público:
   - Agregar en footer de `resources/views/components/layouts/public.blade.php`

**Archivos a crear**:
- `resources/views/components/newsletter/quick-subscribe.blade.php`

**Archivos a modificar**:
- `resources/views/components/layouts/public.blade.php`

---

## Componentes UI a Reutilizar

Basándonos en los componentes existentes, reutilizaremos:

- `x-ui.button` - Botones del formulario
- `x-ui.card` - Contenedor del formulario
- `x-ui.section` - Sección de la página
- `x-ui.breadcrumbs` - Navegación breadcrumb
- `x-ui.empty-state` - Estados vacíos si aplica

## Componentes Nuevos a Crear (si es necesario)

- `x-newsletter.subscribe-form` - Formulario de suscripción reutilizable
- `x-newsletter.program-checkbox` - Checkbox individual para programa

## Consideraciones de Diseño

1. **Estilo Visual**:
   - Seguir la paleta de colores Erasmus+ (azul #003399)
   - Diseño moderno y limpio
   - Responsive en todos los dispositivos
   - Accesible (WCAG 2.1)

2. **UX**:
   - Validación en tiempo real
   - Mensajes claros de éxito/error
   - Confirmación visual de acciones
   - Feedback inmediato

3. **Seguridad**:
   - Validación de email única
   - Tokens seguros para verificación
   - Protección contra spam (opcional: rate limiting)
   - Validación de programas existentes

## Orden de Implementación Recomendado

1. **Fase 1**: Form Request y Validación
2. **Fase 2**: Mejoras al Modelo
3. **Fase 3**: Componente de Suscripción
4. **Fase 4**: Componente de Verificación
5. **Fase 5**: Componente de Baja
6. **Fase 6**: Email de Verificación
7. **Fase 7**: Rutas Públicas
8. **Fase 8**: Seeder de Datos
9. **Fase 9**: Tests Completos
10. **Fase 10**: Integración en Layout (Opcional)

## Notas Importantes

1. **Email**: Para desarrollo, usar `MAIL_MAILER=log` para ver emails en logs
2. **Tokens**: Generar tokens seguros de 32 caracteres usando `Str::random(32)`
3. **Verificación**: Las suscripciones deben estar inactivas hasta verificación
4. **Programas**: Los programas se almacenan como códigos en JSON array (ej: `['KA1xx', 'KA121-VET']`)
5. **Baja**: No eliminar suscripciones, solo marcar como inactivas para mantener historial
6. **Tests**: Asegurar cobertura completa antes de considerar completado

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan completado - Listo para implementación

