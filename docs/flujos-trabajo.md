# Flujos de Trabajo Principales

Este documento describe los flujos de trabajo principales de la aplicación Erasmus+ Centro (Murcia), detallando el ciclo de vida completo de cada proceso, los roles involucrados y las acciones disponibles.

---

## 1. Flujo de Gestión de Convocatorias

El flujo de convocatorias es el proceso central de la aplicación, gestionando todo el ciclo desde la creación hasta el archivo.

### 1.1. Ciclo de Vida de una Convocatoria

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  BORRADOR   │───▶│   ABIERTA   │───▶│   CERRADA   │───▶│  ARCHIVADA  │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
     │                   │                   │
     │              Publicación         Fin plazo
     │              (published_at)
     ▼
  Editable
```

### 1.2. Estados y Transiciones

| Estado | Descripción | Acciones Permitidas |
|--------|-------------|---------------------|
| **Borrador** | Convocatoria en preparación | Editar, Eliminar, Publicar |
| **Abierta** | Convocatoria publicada y activa | Editar, Cerrar, Añadir Fases/Resoluciones |
| **Cerrada** | Plazo finalizado, en evaluación | Editar, Archivar, Añadir Resoluciones |
| **Archivada** | Convocatoria histórica | Solo lectura |

### 1.3. Proceso Completo

#### Paso 1: Creación de la Convocatoria
- **Quién**: Admin, Editor
- **Ruta**: `/admin/convocatorias/crear`
- **Campos requeridos**: Título, Programa, Año Académico, Tipo, Modalidad
- **Campos opcionales**: Descripción, Destinos (JSON), Baremo (JSON), Fechas

#### Paso 2: Definición de Fases
- **Quién**: Admin, Editor
- **Ruta**: `/admin/convocatorias/{call}/fases/crear`
- **Tipos de fase disponibles**:
  - `publicacion` - Publicación de la convocatoria
  - `solicitudes` - Período de solicitudes
  - `provisional` - Lista provisional
  - `alegaciones` - Período de alegaciones
  - `definitivo` - Lista definitiva
  - `renuncias` - Período de renuncias
  - `lista_espera` - Lista de espera
- **Nota**: Solo una fase puede estar marcada como "actual" por convocatoria

#### Paso 3: Publicación
- **Quién**: Admin (requiere permiso `calls.publish`)
- **Acción**: Cambiar estado a "Abierta" y establecer `published_at`
- **Efecto**: La convocatoria aparece en el área pública
- **Notificación**: Se genera notificación automática para usuarios

#### Paso 4: Gestión de Resoluciones
- **Quién**: Admin, Editor
- **Ruta**: `/admin/convocatorias/{call}/resoluciones/crear`
- **Tipos de resolución**:
  - `provisional` - Resolución provisional
  - `definitivo` - Resolución definitiva
  - `alegaciones` - Resolución de alegaciones
- **Archivos**: Subida de PDFs mediante FilePond
- **Publicación**: Las resoluciones pueden publicarse independientemente

#### Paso 5: Cierre y Archivo
- **Cierre**: Cuando finaliza el plazo de solicitudes
- **Archivo**: Una vez completado todo el proceso

### 1.4. Roles y Permisos

| Rol | Ver | Crear | Editar | Eliminar | Publicar |
|-----|-----|-------|--------|----------|----------|
| Super Admin | ✅ | ✅ | ✅ | ✅ | ✅ |
| Admin | ✅ | ✅ | ✅ | ✅ | ✅ |
| Editor | ✅ | ✅ | ✅ | ❌ | ❌ |
| Viewer | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 2. Flujo de Publicación de Noticias

El flujo de noticias permite la creación, edición y publicación de contenido informativo.

### 2.1. Ciclo de Vida de una Noticia

```
┌─────────────┐    ┌─────────────┐
│  BORRADOR   │───▶│  PUBLICADA  │
└─────────────┘    └─────────────┘
     │                   │
     │              Publicación
     │              (published_at)
     ▼
  Editable
```

### 2.2. Proceso Completo

#### Paso 1: Creación de la Noticia
- **Quién**: Admin, Editor
- **Ruta**: `/admin/noticias/crear`
- **Campos requeridos**: Título, Contenido
- **Campos opcionales**: Programa, Año Académico, Extracto, Imagen destacada
- **Editor**: Tiptap para contenido enriquecido (negrita, cursiva, listas, enlaces, etc.)

#### Paso 2: Gestión de Imágenes
- **Imagen destacada**: Subida mediante FilePond
- **Conversiones automáticas**: thumbnail, medium, large, hero (WebP)
- **Gestión avanzada**: Soft delete, restauración, eliminación permanente

#### Paso 3: Asignación de Etiquetas
- **Relación**: Many-to-many con NewsTag
- **Gestión**: Selección múltiple de etiquetas existentes
- **Creación**: Las etiquetas se crean desde `/admin/etiquetas`

#### Paso 4: Publicación
- **Quién**: Admin (requiere permiso `news.publish`)
- **Acción**: Establecer `published_at`
- **Efecto**: La noticia aparece en el área pública
- **Notificación**: Se genera notificación automática para usuarios

### 2.3. Roles y Permisos

| Rol | Ver | Crear | Editar | Eliminar | Publicar |
|-----|-----|-------|--------|----------|----------|
| Super Admin | ✅ | ✅ | ✅ | ✅ | ✅ |
| Admin | ✅ | ✅ | ✅ | ✅ | ✅ |
| Editor | ✅ | ✅ | ✅ | ❌ | ❌ |
| Viewer | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 3. Flujo de Gestión de Documentos

El flujo de documentos permite la organización y publicación de archivos descargables.

### 3.1. Proceso Completo

#### Paso 1: Creación del Documento
- **Quién**: Admin, Editor
- **Ruta**: `/admin/documentos/crear`
- **Campos requeridos**: Título, Archivo
- **Campos opcionales**: Descripción, Categoría, Programa, Año Académico

#### Paso 2: Subida de Archivo
- **Método**: FilePond con validación de tipos
- **Tipos permitidos**: PDF, DOC, DOCX, XLS, XLSX, etc.
- **Almacenamiento**: Laravel Media Library

#### Paso 3: Categorización
- **Categorías**: Asignación a DocumentCategory existente
- **Jerarquía**: Las categorías tienen orden configurable
- **Gestión**: Las categorías se crean desde `/admin/categorias`

#### Paso 4: Consentimientos de Medios (Opcional)
- **Modelo**: MediaConsent
- **Propósito**: Gestionar permisos de uso de imágenes/medios
- **Campos**: Tipo de consentimiento, persona, fecha, documento asociado

### 3.2. Roles y Permisos

| Rol | Ver | Crear | Editar | Eliminar |
|-----|-----|-------|--------|----------|
| Super Admin | ✅ | ✅ | ✅ | ✅ |
| Admin | ✅ | ✅ | ✅ | ✅ |
| Editor | ✅ | ✅ | ✅ | ❌ |
| Viewer | ✅ | ❌ | ❌ | ❌ |

---

## 4. Flujo de Newsletter

El flujo de newsletter gestiona las suscripciones de usuarios externos para recibir información.

### 4.1. Ciclo de Vida de una Suscripción

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  PENDIENTE  │───▶│  VERIFICADA │───▶│    BAJA     │
└─────────────┘    └─────────────┘    └─────────────┘
     │                   │                   │
     │              Verificación         Unsuscribe
     │              (verified_at)        (unsubscribed_at)
     ▼
  Email enviado
```

### 4.2. Proceso de Suscripción (Usuario Público)

#### Paso 1: Solicitud de Suscripción
- **Ruta**: `/newsletter/suscribir`
- **Campos**: Email, Nombre (opcional), Programas de interés
- **Validación**: Email único, formato válido

#### Paso 2: Verificación por Email
- **Email enviado**: Contiene enlace con token único
- **Ruta de verificación**: `/newsletter/verificar/{token}`
- **Resultado**: Se establece `verified_at`

#### Paso 3: Gestión de Suscripción
- **Baja voluntaria**: `/newsletter/baja?email={email}&token={token}`
- **Resultado**: Se establece `unsubscribed_at`

### 4.3. Gestión Administrativa

- **Ruta**: `/admin/newsletter`
- **Funcionalidades**:
  - Ver listado de suscriptores
  - Filtrar por programa, estado, verificación
  - Exportar a Excel (cumplimiento GDPR)
  - Eliminar suscripciones (hard delete para GDPR)

### 4.4. Cumplimiento GDPR

- **Consentimiento explícito**: Verificación por email
- **Derecho al olvido**: Eliminación permanente disponible
- **Portabilidad**: Exportación a Excel
- **Transparencia**: Información clara sobre el uso de datos

---

## 5. Flujo de Eventos

El flujo de eventos permite la gestión del calendario de actividades Erasmus+.

### 5.1. Proceso Completo

#### Paso 1: Creación del Evento
- **Quién**: Admin, Editor
- **Ruta**: `/admin/eventos/crear`
- **Campos requeridos**: Título, Fecha inicio, Fecha fin
- **Campos opcionales**: Descripción, Ubicación, Programa, Convocatoria, Imagen

#### Paso 2: Asociaciones
- **Programa**: Vincular a un programa Erasmus+
- **Convocatoria**: Vincular a una convocatoria específica
- **Nota**: Los eventos de convocatorias aparecen en el calendario

#### Paso 3: Visualización Pública
- **Calendario**: Vista mensual/semanal/diaria
- **Listado**: Vista de lista con filtros
- **Detalle**: Información completa del evento

### 5.2. Vistas de Calendario

| Vista | Descripción |
|-------|-------------|
| **Mensual** | Cuadrícula con días del mes |
| **Semanal** | 7 días con horarios |
| **Diaria** | Agenda del día seleccionado |

---

## 6. Flujo de Auditoría

El flujo de auditoría registra automáticamente todas las acciones importantes del sistema.

### 6.1. Logging Automático

Se registran automáticamente las siguientes acciones en los modelos configurados:

| Modelo | Acciones Registradas |
|--------|---------------------|
| Program | created, updated, deleted |
| Call | created, updated, deleted |
| NewsPost | created, updated, deleted |
| Document | created, updated, deleted |
| ErasmusEvent | created, updated, deleted |
| User | created, updated, deleted |

### 6.2. Logging Manual

Acciones especiales registradas manualmente:

| Acción | Descripción |
|--------|-------------|
| `call.published` | Publicación de convocatoria |
| `call.status_changed` | Cambio de estado de convocatoria |
| `resolution.published` | Publicación de resolución |
| `news.published` | Publicación de noticia |
| `user.role_assigned` | Asignación de rol a usuario |
| `user.role_removed` | Eliminación de rol de usuario |
| `setting.updated` | Actualización de configuración |
| `import.completed` | Importación completada |
| `export.completed` | Exportación completada |

### 6.3. Consulta de Logs

- **Ruta**: `/admin/auditoria`
- **Filtros**: Por modelo, usuario, acción, rango de fechas
- **Detalle**: Ver cambios antes/después (JSON diff)
- **Exportación**: Excel con todos los registros filtrados

---

## 7. Flujo de Importación/Exportación

### 7.1. Importación de Datos

#### Convocatorias
- **Ruta**: `/admin/convocatorias` (botón Importar)
- **Formato**: Excel/CSV
- **Modo dry-run**: Validar sin guardar
- **Plantilla**: Descargable con ejemplos

#### Usuarios
- **Ruta**: `/admin/usuarios` (botón Importar)
- **Formato**: Excel/CSV
- **Campos**: Nombre, Email, Rol
- **Validación**: Email único, rol válido

### 7.2. Exportación de Datos

| Entidad | Formato | Campos Incluidos |
|---------|---------|------------------|
| Convocatorias | Excel | Título, Programa, Estado, Fechas, etc. |
| Resoluciones | Excel | Título, Tipo, Fecha, Estado publicación |
| Newsletter | Excel | Email, Nombre, Programa, Verificación, Fechas |
| Audit Logs | Excel | Fecha, Usuario, Acción, Modelo, Cambios |

---

## 8. Flujo de Notificaciones

### 8.1. Generación Automática

Las notificaciones se generan automáticamente mediante Observers cuando:

| Evento | Notificación |
|--------|--------------|
| Convocatoria publicada | "Nueva convocatoria: {título}" |
| Resolución publicada | "Nueva resolución en {convocatoria}" |
| Noticia publicada | "Nueva noticia: {título}" |

### 8.2. Visualización

- **Componente Bell**: Icono con contador de no leídas
- **Dropdown**: Lista de notificaciones recientes
- **Página completa**: `/notificaciones` con todas las notificaciones
- **Polling**: Actualización automática cada 30 segundos

### 8.3. Acciones

- **Marcar como leída**: Individual o todas
- **Navegar**: Click lleva al contenido relacionado

---

## Diagrama General de Flujos

```
                                    ┌─────────────────┐
                                    │   ÁREA PÚBLICA  │
                                    └────────┬────────┘
                                             │
        ┌────────────────────────────────────┼────────────────────────────────────┐
        │                                    │                                    │
        ▼                                    ▼                                    ▼
┌───────────────┐                    ┌───────────────┐                    ┌───────────────┐
│   Programas   │                    │ Convocatorias │                    │   Noticias    │
│   (listado)   │                    │   (listado)   │                    │   (listado)   │
└───────────────┘                    └───────────────┘                    └───────────────┘
        │                                    │                                    │
        ▼                                    ▼                                    ▼
┌───────────────┐                    ┌───────────────┐                    ┌───────────────┐
│   Documentos  │                    │    Eventos    │                    │  Newsletter   │
│   (listado)   │                    │  (calendario) │                    │ (suscripción) │
└───────────────┘                    └───────────────┘                    └───────────────┘


                                    ┌─────────────────┐
                                    │ ADMINISTRACIÓN  │
                                    └────────┬────────┘
                                             │
    ┌────────────────┬───────────────┬───────┴───────┬───────────────┬────────────────┐
    │                │               │               │               │                │
    ▼                ▼               ▼               ▼               ▼                ▼
┌────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│Dashboard│    │ Programas│    │Convocat. │    │ Noticias │    │Documentos│    │ Eventos  │
└────────┘    └──────────┘    └──────────┘    └──────────┘    └──────────┘    └──────────┘
                                   │
                    ┌──────────────┼──────────────┐
                    │              │              │
                    ▼              ▼              ▼
              ┌──────────┐  ┌──────────┐  ┌──────────┐
              │  Fases   │  │Resoluc.  │  │ Estados  │
              └──────────┘  └──────────┘  └──────────┘
```

---

## Buenas Prácticas

### Para Administradores

1. **Convocatorias**: Definir todas las fases antes de publicar
2. **Noticias**: Usar imágenes optimizadas (se convierten a WebP automáticamente)
3. **Documentos**: Categorizar correctamente para facilitar la búsqueda
4. **Auditoría**: Revisar periódicamente los logs de actividad

### Para Editores

1. **Contenido**: Revisar antes de solicitar publicación
2. **Imágenes**: Usar formatos estándar (JPG, PNG)
3. **Etiquetas**: Reutilizar etiquetas existentes cuando sea posible

### Para el Sistema

1. **Caché**: Se invalida automáticamente al actualizar contenido
2. **Notificaciones**: Se generan automáticamente al publicar
3. **SEO**: Meta tags se generan dinámicamente

---

**Última actualización**: Enero 2026
