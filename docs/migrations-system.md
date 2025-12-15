# Migraciones - Sistema

Este documento describe las migraciones relacionadas con las funcionalidades del sistema: auditoría, notificaciones, calendario, internacionalización y configuración.

## Sistema de Auditoría

### Tabla: `audit_logs`

**Archivo**: `2025_12_12_193919_create_audit_logs_table.php`

#### Descripción

Registro completo de todas las acciones realizadas en el sistema para mantener trazabilidad y cumplimiento. Almacena quién hizo qué, cuándo y qué cambios se realizaron.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `user_id` | foreignId (nullable) | Usuario que realizó la acción (FK → users) |
| `action` | enum | Acción realizada |
| `model_type` | string | Tipo del modelo afectado (polimórfico) |
| `model_id` | unsignedBigInteger | ID del modelo afectado (polimórfico) |
| `changes` | json (nullable) | Cambios realizados (antes/después) |
| `ip_address` | string (nullable) | Dirección IP desde la que se realizó la acción |
| `user_agent` | text (nullable) | User agent del navegador |
| `created_at` | timestamp | Fecha y hora de la acción |

#### Acciones Registradas

- **create**: Creación de un nuevo registro
- **update**: Actualización de un registro existente
- **delete**: Eliminación de un registro
- **publish**: Publicación de contenido
- **archive**: Archivado de contenido
- **restore**: Restauración de contenido archivado

#### Estructura del Campo `changes`

```json
{
    "before": {
        "status": "borrador",
        "title": "Título anterior"
    },
    "after": {
        "status": "publicado",
        "title": "Título nuevo"
    }
}
```

#### Relaciones

- **BelongsTo**: `user` - Usuario que realizó la acción
- **MorphTo**: `model` - Modelo afectado (polimórfico)

#### Índices

- `['user_id', 'created_at']` - Búsqueda de acciones por usuario y fecha
- `['model_type', 'model_id']` - Búsqueda de acciones sobre un modelo específico

#### Notas

- Esta tabla no tiene `updated_at` ya que los logs de auditoría son inmutables
- El campo `user_id` puede ser null para acciones del sistema
- El campo `changes` puede ser null para acciones que no modifican datos

---

## Sistema de Notificaciones

### Tabla: `notifications`

**Archivo**: `2025_12_12_193919_create_notifications_table.php`

#### Descripción

Sistema de notificaciones internas del sistema para usuarios. Diferente de la tabla `notifications` de Laravel, esta es una tabla personalizada para notificaciones específicas de la aplicación.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `user_id` | foreignId | Usuario destinatario (FK → users) |
| `type` | enum | Tipo de notificación |
| `title` | string | Título de la notificación |
| `message` | text | Mensaje de la notificación |
| `link` | string (nullable) | Enlace relacionado |
| `is_read` | boolean | Indica si la notificación ha sido leída (default: false) |
| `read_at` | timestamp (nullable) | Fecha de lectura |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Tipos de Notificación

- **convocatoria**: Notificación sobre convocatorias
- **resolucion**: Notificación sobre resoluciones publicadas
- **noticia**: Notificación sobre noticias publicadas
- **revision**: Notificación sobre contenido pendiente de revisión
- **sistema**: Notificación del sistema

#### Relaciones

- **BelongsTo**: `user` - Usuario destinatario

#### Índices

- `['user_id', 'is_read']` - Búsqueda de notificaciones no leídas por usuario
- `['type', 'created_at']` - Búsqueda por tipo y fecha

---

## Sistema de Suscripciones

### Tabla: `newsletter_subscriptions`

**Archivo**: `2025_12_12_193919_create_newsletter_subscriptions_table.php`

#### Descripción

Gestiona las suscripciones al newsletter/avisos por email. Permite a los usuarios suscribirse para recibir notificaciones sobre programas específicos.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `email` | string (unique) | Email del suscriptor |
| `name` | string (nullable) | Nombre del suscriptor |
| `programs` | json (nullable) | Array de programas de interés |
| `is_active` | boolean | Indica si la suscripción está activa (default: true) |
| `subscribed_at` | timestamp | Fecha de suscripción |
| `unsubscribed_at` | timestamp (nullable) | Fecha de cancelación |
| `verification_token` | string (nullable) | Token de verificación |
| `verified_at` | timestamp (nullable) | Fecha de verificación |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Estructura del Campo `programs`

```json
["KA1xx", "KA121-VET", "KA131-HED"]
```

#### Índices

- `['email', 'is_active']` - Búsqueda de suscripciones activas por email

---

## Sistema de Calendario

### Tabla: `erasmus_events`

**Archivo**: `2025_12_12_193919_create_erasmus_events_table.php`

#### Descripción

Calendario público de eventos Erasmus+: fechas clave de convocatorias, entrevistas, publicaciones, reuniones informativas, etc.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `program_id` | foreignId (nullable) | Programa asociado (FK → programs) |
| `call_id` | foreignId (nullable) | Convocatoria asociada (FK → calls) |
| `title` | string | Título del evento |
| `description` | text (nullable) | Descripción del evento |
| `event_type` | enum | Tipo de evento |
| `start_date` | dateTime | Fecha y hora de inicio |
| `end_date` | dateTime (nullable) | Fecha y hora de fin |
| `location` | string (nullable) | Ubicación del evento |
| `is_public` | boolean | Indica si el evento es público (default: true) |
| `created_by` | foreignId (nullable) | Usuario creador (FK → users) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Tipos de Evento

- **apertura**: Apertura de convocatoria
- **cierre**: Cierre de convocatoria
- **entrevista**: Entrevistas de selección
- **publicacion_provisional**: Publicación del listado provisional
- **publicacion_definitivo**: Publicación del listado definitivo
- **reunion_informativa**: Reunión informativa
- **otro**: Otro tipo de evento

#### Relaciones

- **BelongsTo**: `program` - Programa asociado (opcional)
- **BelongsTo**: `call` - Convocatoria asociada (opcional)
- **BelongsTo**: `creator` - Usuario creador

#### Índices

- `['program_id', 'start_date']` - Eventos por programa ordenados por fecha
- `['call_id', 'start_date']` - Eventos por convocatoria ordenados por fecha
- `['is_public', 'start_date']` - Eventos públicos ordenados por fecha

---

## Sistema de Internacionalización

### Tabla: `languages`

**Archivo**: `2025_12_12_193919_create_languages_table.php`

#### Descripción

Gestiona los idiomas disponibles en la aplicación. Soporta multilingüe con ES/EN mínimo para difusión internacional.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `code` | string(2) (unique) | Código ISO del idioma (ej: 'es', 'en') |
| `name` | string | Nombre del idioma (ej: 'Español', 'English') |
| `is_default` | boolean | Indica si es el idioma por defecto (default: false) |
| `is_active` | boolean | Indica si el idioma está activo (default: true) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Relaciones

- **HasMany**: `translations` - Traducciones en este idioma

#### Índices

- `['is_default', 'is_active']` - Búsqueda del idioma por defecto activo

#### Consideraciones

- Solo debería haber un idioma con `is_default = true` a la vez
- Los códigos deben seguir el estándar ISO 639-1 (2 letras)

---

### Tabla: `translations`

**Archivo**: `2025_12_12_193919_create_translations_table.php`

#### Descripción

Sistema de traducciones polimórfico que permite traducir campos específicos de cualquier modelo a diferentes idiomas.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `translatable_type` | string | Tipo del modelo (polimórfico) |
| `translatable_id` | unsignedBigInteger | ID del modelo (polimórfico) |
| `language_id` | foreignId | Idioma de la traducción (FK → languages) |
| `field` | string | Nombre del campo traducido |
| `value` | text | Valor traducido |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Relaciones

- **BelongsTo**: `language` - Idioma de la traducción
- **MorphTo**: `translatable` - Modelo traducido (polimórfico)

#### Índices y Constraints

- **Unique**: `['translatable_type', 'translatable_id', 'language_id', 'field']` - Evita duplicados
- `['translatable_type', 'translatable_id']` - Búsqueda de traducciones de un modelo

#### Ejemplo de Uso

```php
// Traducir el título de una convocatoria
Translation::create([
    'translatable_type' => 'App\Models\Call',
    'translatable_id' => $call->id,
    'language_id' => $english->id,
    'field' => 'title',
    'value' => 'Erasmus+ Mobility Call'
]);
```

---

## Sistema de Configuración

### Tabla: `settings`

**Archivo**: `2025_12_12_193919_create_settings_table.php`

#### Descripción

Almacena la configuración de la aplicación de forma flexible. Permite diferentes tipos de valores (string, integer, boolean, json) organizados por grupos.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `key` | string (unique) | Clave de la configuración |
| `value` | text (nullable) | Valor de la configuración |
| `type` | enum | Tipo del valor: 'string', 'integer', 'boolean', 'json' |
| `group` | enum | Grupo: 'general', 'email', 'rgpd', 'media', 'seo' |
| `description` | text (nullable) | Descripción de la configuración |
| `updated_by` | foreignId (nullable) | Usuario que actualizó (FK → users) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Tipos de Valor

- **string**: Valor de texto
- **integer**: Valor numérico entero
- **boolean**: Valor booleano (true/false)
- **json**: Valor JSON estructurado

#### Grupos de Configuración

- **general**: Configuración general de la aplicación
- **email**: Configuración de correo electrónico
- **rgpd**: Configuración relacionada con RGPD
- **media**: Configuración de multimedia
- **seo**: Configuración SEO

#### Relaciones

- **BelongsTo**: `updater` - Usuario que actualizó la configuración

#### Índices

- `['group', 'key']` - Búsqueda por grupo y clave

#### Nota sobre Casting

El modelo `Setting` implementa accessors y mutators personalizados para convertir automáticamente los valores según su tipo:

- `integer`: Convierte a entero
- `boolean`: Convierte a booleano
- `json`: Decodifica/encodifica JSON
- `string`: Valor de texto sin conversión

---

## Ejemplos de Uso

### Registrar una Acción en Auditoría

```php
AuditLog::create([
    'user_id' => auth()->id(),
    'action' => 'publish',
    'model_type' => 'App\Models\Call',
    'model_id' => $call->id,
    'changes' => [
        'before' => ['status' => 'borrador'],
        'after' => ['status' => 'publicado']
    ],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'created_at' => now()
]);
```

### Crear una Notificación

```php
Notification::create([
    'user_id' => $user->id,
    'type' => 'resolucion',
    'title' => 'Nueva resolución publicada',
    'message' => 'Se ha publicado la resolución definitiva de la convocatoria FCT 2024',
    'link' => route('resolutions.show', $resolution),
    'is_read' => false
]);
```

### Suscribirse al Newsletter

```php
NewsletterSubscription::create([
    'email' => 'usuario@example.com',
    'name' => 'Juan Pérez',
    'programs' => ['KA1xx', 'KA121-VET'],
    'is_active' => true,
    'subscribed_at' => now(),
    'verification_token' => Str::random(32)
]);
```

### Crear un Evento en el Calendario

```php
ErasmusEvent::create([
    'program_id' => $program->id,
    'call_id' => $call->id,
    'title' => 'Apertura de convocatoria FCT',
    'event_type' => 'apertura',
    'start_date' => '2024-10-01 09:00:00',
    'end_date' => '2024-10-01 18:00:00',
    'location' => 'Salón de actos',
    'is_public' => true,
    'created_by' => auth()->id()
]);
```

### Configurar un Setting

```php
Setting::create([
    'key' => 'site_name',
    'value' => 'Erasmus+ Centro Murcia',
    'type' => 'string',
    'group' => 'general',
    'description' => 'Nombre del sitio web'
]);

Setting::create([
    'key' => 'max_upload_size',
    'value' => '10485760', // 10MB en bytes
    'type' => 'integer',
    'group' => 'media',
    'description' => 'Tamaño máximo de archivo en bytes'
]);
```

---

## Notas de Implementación

- Los logs de auditoría son inmutables (no tienen `updated_at`)
- Las notificaciones pueden marcarse como leídas actualizando `is_read` y `read_at`
- Las suscripciones al newsletter requieren verificación mediante token
- Los eventos del calendario pueden ser públicos o privados
- El sistema de traducciones es polimórfico y flexible
- Los settings se convierten automáticamente según su tipo
- Todas las foreign keys a `users` utilizan `nullOnDelete()` para mantener el historial

