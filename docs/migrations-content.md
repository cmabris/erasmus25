# Migraciones - Sistema de Contenido

Este documento describe las migraciones relacionadas con el contenido de la aplicación: noticias, documentos y gestión de multimedia.

## Sistema de Noticias

### Tabla: `news_posts`

**Archivo**: `2025_12_12_193821_create_news_posts_table.php`

#### Descripción

Almacena las noticias y crónicas de movilidades Erasmus+. Cada noticia puede estar asociada a un programa y año académico, y puede incluir información sobre el destino y tipo de movilidad.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `program_id` | foreignId (nullable) | Programa asociado (FK → programs) |
| `academic_year_id` | foreignId | Año académico (FK → academic_years) |
| `title` | string | Título de la noticia |
| `slug` | string (unique) | Slug para URLs amigables |
| `excerpt` | text (nullable) | Extracto/resumen de la noticia |
| `content` | longText | Contenido completo de la noticia |
| `country` | string (nullable) | País de destino |
| `city` | string (nullable) | Ciudad de destino |
| `host_entity` | string (nullable) | Entidad de acogida |
| `mobility_type` | enum (nullable) | Tipo: 'alumnado' o 'personal' |
| `mobility_category` | enum (nullable) | Categoría: 'FCT', 'job_shadowing', 'intercambio', 'curso', 'otro' |
| `status` | enum | Estado: 'borrador', 'en_revision', 'publicado', 'archivado' |
| `published_at` | timestamp (nullable) | Fecha de publicación |
| `author_id` | foreignId (nullable) | Autor de la noticia (FK → users) |
| `reviewed_by` | foreignId (nullable) | Revisor de la noticia (FK → users) |
| `reviewed_at` | timestamp (nullable) | Fecha de revisión |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Estados de la Noticia

- **borrador**: Noticia en preparación, no visible públicamente
- **en_revision**: Noticia pendiente de revisión
- **publicado**: Noticia publicada y visible
- **archivado**: Noticia archivada para consulta histórica

#### Categorías de Movilidad

- **FCT**: Formación en Centros de Trabajo
- **job_shadowing**: Observación de prácticas profesionales
- **intercambio**: Intercambio estudiantil
- **curso**: Curso de formación
- **otro**: Otra categoría

#### Relaciones

- **BelongsTo**: `program` - Programa asociado (opcional)
- **BelongsTo**: `academicYear` - Año académico
- **BelongsTo**: `author` - Usuario autor
- **BelongsTo**: `reviewer` - Usuario revisor
- **BelongsToMany**: `tags` - Etiquetas asociadas (tabla pivot: news_post_tag)

#### Índices

- `['program_id', 'status', 'published_at']` - Búsqueda de noticias por programa, estado y fecha
- `['academic_year_id', 'status']` - Búsqueda por año académico y estado

#### Nota sobre Multimedia

Las imágenes destacadas, galerías de fotos, videos y audios se gestionan mediante Laravel Media Library, asociados al modelo `NewsPost` mediante colecciones:
- `featured`: Imagen destacada
- `gallery`: Galería de imágenes
- `videos`: Videos asociados
- `audio`: Archivos de audio/podcasts

---

### Tabla: `news_tags`

**Archivo**: `2025_12_12_193821_create_news_tags_table.php`

#### Descripción

Almacena las etiquetas utilizadas para categorizar y organizar las noticias.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `name` | string (unique) | Nombre de la etiqueta |
| `slug` | string (unique) | Slug para URLs amigables |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Relaciones

- **BelongsToMany**: `newsPosts` - Noticias asociadas (tabla pivot: news_post_tag)

---

### Tabla: `news_post_tag`

**Archivo**: `2025_12_12_193822_create_news_post_tag_table.php`

#### Descripción

Tabla pivot que relaciona noticias con etiquetas (relación many-to-many).

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `news_post_id` | foreignId | Noticia (FK → news_posts) |
| `news_tag_id` | foreignId | Etiqueta (FK → news_tags) |

#### Clave Primaria

- Clave primaria compuesta: `['news_post_id', 'news_tag_id']`

#### Relaciones

- **BelongsTo**: `newsPost` - Noticia
- **BelongsTo**: `newsTag` - Etiqueta

---

## Sistema de Documentos

### Tabla: `document_categories`

**Archivo**: `2025_12_12_193902_create_document_categories_table.php`

#### Descripción

Categorías para organizar los documentos del repositorio: convocatorias, modelos, seguros, consentimientos, guías, FAQ, etc.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `name` | string | Nombre de la categoría |
| `slug` | string (unique) | Slug para URLs amigables |
| `description` | text (nullable) | Descripción de la categoría |
| `order` | integer | Orden de visualización (default: 0) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Relaciones

- **HasMany**: `documents` - Documentos de la categoría

---

### Tabla: `documents`

**Archivo**: `2025_12_12_193902_create_documents_table.php`

#### Descripción

Repositorio de documentos descargables: PDFs de convocatorias, modelos, guías, consentimientos, etc.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `category_id` | foreignId | Categoría del documento (FK → document_categories) |
| `program_id` | foreignId (nullable) | Programa asociado (FK → programs) |
| `academic_year_id` | foreignId (nullable) | Año académico (FK → academic_years) |
| `title` | string | Título del documento |
| `slug` | string (unique) | Slug para URLs amigables |
| `description` | text (nullable) | Descripción del documento |
| `document_type` | enum | Tipo: 'convocatoria', 'modelo', 'seguro', 'consentimiento', 'guia', 'faq', 'otro' |
| `version` | string (nullable) | Versión del documento |
| `is_active` | boolean | Indica si el documento está activo (default: true) |
| `download_count` | integer | Contador de descargas (default: 0) |
| `created_by` | foreignId (nullable) | Usuario creador (FK → users) |
| `updated_by` | foreignId (nullable) | Usuario que actualizó (FK → users) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Tipos de Documento

- **convocatoria**: Documento de convocatoria
- **modelo**: Modelo o plantilla
- **seguro**: Documentación de seguros
- **consentimiento**: Consentimientos RGPD
- **guia**: Guías informativas
- **faq**: Preguntas frecuentes
- **otro**: Otro tipo de documento

#### Relaciones

- **BelongsTo**: `category` - Categoría del documento
- **BelongsTo**: `program` - Programa asociado (opcional)
- **BelongsTo**: `academicYear` - Año académico (opcional)
- **BelongsTo**: `creator` - Usuario creador
- **BelongsTo**: `updater` - Usuario que actualizó

#### Índices

- `['category_id', 'program_id', 'is_active']` - Búsqueda por categoría, programa y estado

#### Nota sobre Archivos

El archivo físico del documento se gestiona mediante Laravel Media Library, asociado al modelo `Document` mediante la colección `file`. Los metadatos adicionales pueden almacenarse en custom properties si es necesario.

---

## Sistema de Multimedia y Consentimientos RGPD

### Tabla: `media`

**Archivo**: `2025_12_12_200313_create_media_table.php` (Laravel Media Library)

#### Descripción

Tabla gestionada por Laravel Media Library que almacena todos los archivos multimedia (imágenes, videos, audios, documentos) asociados a modelos Eloquent mediante relaciones polimórficas.

#### Campos Principales

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `model_type` | string | Tipo del modelo (polimórfico) |
| `model_id` | unsignedBigInteger | ID del modelo (polimórfico) |
| `uuid` | uuid (nullable, unique) | UUID único del archivo |
| `collection_name` | string | Nombre de la colección |
| `name` | string | Nombre del archivo |
| `file_name` | string | Nombre completo del archivo |
| `mime_type` | string (nullable) | Tipo MIME |
| `disk` | string | Disco de almacenamiento |
| `size` | unsignedBigInteger | Tamaño del archivo en bytes |
| `manipulations` | json | Manipulaciones aplicadas |
| `custom_properties` | json | Propiedades personalizadas |
| `generated_conversions` | json | Conversiones generadas |
| `responsive_images` | json | Imágenes responsivas |
| `order_column` | unsignedInteger (nullable) | Orden de visualización |

#### Uso en la Aplicación

Los modelos que utilizan Media Library:
- `Call`: Colecciones 'documents', 'bases', 'anexos'
- `Resolution`: Colecciones 'resoluciones', 'anexos', 'listados'
- `NewsPost`: Colecciones 'featured', 'gallery', 'videos', 'audio'
- `Document`: Colección 'file'

---

### Tabla: `media_consents`

**Archivo**: `2025_12_12_193918_create_media_consents_table.php`

#### Descripción

Gestiona los consentimientos RGPD para el uso de imágenes, videos y audios de personas. Permite mantener un registro completo de los consentimientos dados y su estado.

#### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigIncrements | Identificador único |
| `media_id` | unsignedBigInteger | Archivo multimedia (FK → media) |
| `consent_type` | enum | Tipo: 'imagen', 'video', 'audio' |
| `person_name` | string (nullable) | Nombre de la persona |
| `person_email` | string (nullable) | Email de la persona |
| `consent_given` | boolean | Indica si se ha dado el consentimiento |
| `consent_date` | date | Fecha del consentimiento |
| `consent_document_id` | foreignId (nullable) | PDF del consentimiento firmado (FK → documents) |
| `expires_at` | date (nullable) | Fecha de expiración del consentimiento |
| `revoked_at` | timestamp (nullable) | Fecha de revocación del consentimiento |
| `notes` | text (nullable) | Notas adicionales |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Tipos de Consentimiento

- **imagen**: Consentimiento para uso de imágenes
- **video**: Consentimiento para uso de videos
- **audio**: Consentimiento para uso de audios

#### Relaciones

- **BelongsTo**: `consentDocument` - Documento PDF del consentimiento firmado

#### Índices

- `['media_id']` - Búsqueda por archivo multimedia
- `['consent_type', 'consent_given']` - Búsqueda por tipo y estado

#### Nota Importante

La foreign key a la tabla `media` se agrega en una migración separada (`2025_12_12_200314_add_foreign_key_to_media_consents_table.php`) que se ejecuta después de instalar Laravel Media Library.

---

### Migración: `add_foreign_key_to_media_consents_table`

**Archivo**: `2025_12_12_200314_add_foreign_key_to_media_consents_table.php`

#### Descripción

Agrega la foreign key constraint entre `media_consents` y `media` después de que Laravel Media Library haya sido instalado y su migración ejecutada.

#### Funcionamiento

La migración verifica si la tabla `media` existe antes de agregar la foreign key, permitiendo que las migraciones se ejecuten en cualquier orden.

---

## Ejemplos de Uso

### Crear una Noticia

```php
$newsPost = NewsPost::create([
    'program_id' => $program->id,
    'academic_year_id' => $academicYear->id,
    'title' => 'Movilidad en París - FCT 2024',
    'slug' => 'movilidad-paris-fct-2024',
    'excerpt' => 'Estudiantes realizan prácticas en empresas parisinas...',
    'content' => 'Contenido completo de la noticia...',
    'country' => 'Francia',
    'city' => 'París',
    'mobility_type' => 'alumnado',
    'mobility_category' => 'FCT',
    'status' => 'publicado',
    'published_at' => now(),
    'author_id' => auth()->id()
]);

// Agregar imagen destacada mediante Media Library
$newsPost->addMedia($pathToImage)->toMediaCollection('featured');

// Agregar etiquetas
$newsPost->tags()->attach([$tag1->id, $tag2->id]);
```

### Crear un Documento

```php
$document = Document::create([
    'category_id' => $category->id,
    'program_id' => $program->id,
    'academic_year_id' => $academicYear->id,
    'title' => 'Guía de Movilidad Erasmus+',
    'slug' => 'guia-movilidad-erasmus',
    'description' => 'Guía completa para estudiantes...',
    'document_type' => 'guia',
    'version' => '2.0',
    'is_active' => true,
    'created_by' => auth()->id()
]);

// Agregar archivo PDF mediante Media Library
$document->addMedia($pathToPdf)->toMediaCollection('file');
```

### Registrar un Consentimiento RGPD

```php
MediaConsent::create([
    'media_id' => $media->id,
    'consent_type' => 'imagen',
    'person_name' => 'Juan Pérez',
    'person_email' => 'juan@example.com',
    'consent_given' => true,
    'consent_date' => now(),
    'consent_document_id' => $consentPdf->id,
    'expires_at' => now()->addYears(2)
]);
```

---

## Notas de Implementación

- Los slugs se generan automáticamente desde el título/nombre si no se proporcionan
- El flujo editorial de noticias permite revisión antes de publicar
- Los documentos pueden versionarse mediante el campo `version`
- El contador de descargas se incrementa automáticamente al descargar
- Los consentimientos RGPD pueden tener fecha de expiración y pueden ser revocados
- La gestión de multimedia se realiza completamente mediante Laravel Media Library

