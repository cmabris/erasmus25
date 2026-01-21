# Guía del Editor - Erasmus+ Centro (Murcia)

**Portal de Gestión de Programas Erasmus+**

---

## Índice

1. [Introducción](#1-introducción)
2. [Acceso al Sistema](#2-acceso-al-sistema)
3. [Tu Perfil](#3-tu-perfil)
4. [Interfaz General](#4-interfaz-general)
5. [Gestión de Programas](#5-gestión-de-programas)
6. [Gestión de Convocatorias](#6-gestión-de-convocatorias)
7. [Gestión de Noticias](#7-gestión-de-noticias)
8. [Gestión de Documentos](#8-gestión-de-documentos)
9. [Gestión de Eventos](#9-gestión-de-eventos)
10. [Preguntas Frecuentes](#10-preguntas-frecuentes)

---

## 1. Introducción

### 1.1. ¿Qué es esta Guía?

Esta guía está diseñada específicamente para usuarios con rol de **Editor** en el sistema Erasmus+ Centro. Como editor, puedes crear y modificar contenido, pero algunas acciones requieren aprobación de un administrador.

### 1.2. Tu Rol como Editor

Como editor, puedes:

| Puedes hacer ✅ | No puedes hacer ❌ |
|-----------------|-------------------|
| Ver todo el contenido | Eliminar registros |
| Crear nuevos registros | Publicar contenido |
| Editar registros existentes | Gestionar usuarios |
| Subir imágenes y archivos | Ver auditoría del sistema |
| Gestionar etiquetas de noticias | Importar/Exportar datos |

### 1.3. Flujo de Trabajo Típico

```
1. Crear contenido (borrador)
        ↓
2. Editar y perfeccionar
        ↓
3. Notificar al administrador
        ↓
4. Administrador revisa y publica
```

---

## 2. Acceso al Sistema

### 2.1. Iniciar Sesión

1. Accede a la URL del sistema
2. Introduce tu **email** y **contraseña**
3. Opcionalmente marca **"Recordarme"**
4. Haz clic en **"Iniciar sesión"**

### 2.2. Si Olvidaste tu Contraseña

1. Haz clic en **"¿Olvidaste tu contraseña?"**
2. Introduce tu email
3. Revisa tu bandeja de entrada
4. Sigue el enlace del email
5. Establece una nueva contraseña

### 2.3. Cerrar Sesión

1. Haz clic en tu **nombre** (esquina superior derecha)
2. Selecciona **"Cerrar sesión"**

---

## 3. Tu Perfil

### 3.1. Acceder a Configuración

1. Haz clic en tu **nombre** (esquina superior derecha)
2. Selecciona **"Configuración"**

### 3.2. Opciones Disponibles

| Sección | Qué puedes hacer |
|---------|------------------|
| **Perfil** | Cambiar nombre y email |
| **Contraseña** | Cambiar tu contraseña |
| **Apariencia** | Elegir tema (claro/oscuro/sistema) |
| **Seguridad** | Configurar autenticación en dos pasos (2FA) |

### 3.3. Configurar 2FA (Recomendado)

1. Ve a **Configuración → Seguridad**
2. Haz clic en **"Habilitar 2FA"**
3. Escanea el código QR con tu app de autenticación
4. Introduce el código de verificación
5. **Guarda los códigos de recuperación** en lugar seguro

---

## 4. Interfaz General

### 4.1. Estructura de la Pantalla

```
┌─────────────────────────────────────────────────┐
│  Logo    [Búsqueda]           🔔  ES/EN  👤    │
├──────────┬──────────────────────────────────────┤
│          │                                      │
│ SIDEBAR  │         CONTENIDO PRINCIPAL          │
│          │                                      │
│ Dashboard│  ┌─────────────────────────────────┐ │
│ Programas│  │  Breadcrumbs                    │ │
│ Convoc.  │  │  Título de la página            │ │
│ Noticias │  │  Contenido...                   │ │
│ Docs     │  └─────────────────────────────────┘ │
│ Eventos  │                                      │
│          │                                      │
└──────────┴──────────────────────────────────────┘
```

### 4.2. Navegación Principal

| Menú | Descripción |
|------|-------------|
| **Dashboard** | Panel principal con resumen |
| **Programas** | Gestión de programas Erasmus+ |
| **Convocatorias** | Gestión de convocatorias |
| **Noticias** | Gestión de noticias |
| **Documentos** | Gestión de documentos |
| **Eventos** | Gestión de eventos |

### 4.3. Búsqueda Global

1. Usa el campo de búsqueda en la parte superior
2. O pulsa `Ctrl/Cmd + K`
3. Escribe lo que buscas
4. Selecciona un resultado para ir directamente

### 4.4. Cambiar Idioma

1. Haz clic en **ES** o **EN** en la barra superior
2. El idioma cambia inmediatamente

### 4.5. Notificaciones

1. Haz clic en el icono de **campana** 🔔
2. Verás las notificaciones recientes
3. Haz clic en una para ir al contenido relacionado

---

## 5. Gestión de Programas

**Ruta:** `/admin/programas`

### 5.1. Ver Programas

- Accede desde el menú lateral → **Programas**
- Verás la lista de todos los programas
- Usa la búsqueda para encontrar uno específico

### 5.2. Crear un Programa

1. Haz clic en **"Crear Programa"**
2. Completa los campos:
   - **Código** (obligatorio): Identificador único
   - **Nombre** (obligatorio): Nombre del programa
   - **Descripción**: Descripción detallada
   - **Imagen**: Imagen representativa (opcional)
   - **Orden**: Posición en listados
   - **Activo**: Si está visible públicamente
3. Haz clic en **"Guardar"**

### 5.3. Editar un Programa

1. En la lista, haz clic en **"Editar"** (icono de lápiz)
2. Modifica los campos necesarios
3. Haz clic en **"Guardar"**

### 5.4. Gestionar Traducciones

1. Edita el programa
2. Desplázate a la sección **"Traducciones"**
3. Completa el nombre y descripción en otros idiomas
4. Guarda los cambios

> **Nota:** Como editor, puedes crear y editar programas, pero no puedes eliminarlos ni cambiar su estado activo/inactivo. Contacta a un administrador si necesitas estas acciones.

---

## 6. Gestión de Convocatorias

**Ruta:** `/admin/convocatorias`

### 6.1. Ver Convocatorias

- Accede desde el menú lateral → **Convocatorias**
- Usa los filtros para encontrar convocatorias específicas:
  - Por programa
  - Por año académico
  - Por tipo (alumnado/personal)
  - Por estado

### 6.2. Crear una Convocatoria

1. Haz clic en **"Crear Convocatoria"**
2. Completa la información básica:
   - **Programa** (obligatorio)
   - **Año Académico** (obligatorio)
   - **Título** (obligatorio)
   - **Tipo**: Alumnado o Personal
   - **Modalidad**: Corta o Larga duración
   - **Número de plazas**
3. Añade destinos (al menos uno)
4. Completa requisitos, documentación y criterios
5. Configura la tabla de baremo
6. Haz clic en **"Guardar"**

> **Importante:** La convocatoria se crea en estado **Borrador**. Un administrador deberá publicarla.

### 6.3. Editar una Convocatoria

1. Haz clic en **"Editar"** (icono de lápiz)
2. Modifica los campos necesarios
3. Haz clic en **"Guardar"**

### 6.4. Estados de la Convocatoria

| Estado | Significado | ¿Puedes modificar? |
|--------|-------------|:------------------:|
| **Borrador** | En preparación | ✅ Sí |
| **Abierta** | Publicada | ⚠️ Con cuidado |
| **Cerrada** | Plazo finalizado | ⚠️ Solo correcciones |
| **Otros** | En proceso | ⚠️ Solo correcciones |

### 6.5. Gestionar Fases

Las fases definen el cronograma de la convocatoria:

1. Ve al detalle de la convocatoria
2. Busca la sección **"Fases"**
3. Haz clic en **"Gestionar Fases"**
4. Puedes crear, editar y reordenar fases
5. Marca la **fase actual** para que se muestre destacada

### 6.6. Gestionar Resoluciones

Las resoluciones son los documentos oficiales de cada fase:

1. Ve al detalle de la convocatoria
2. Busca la sección **"Resoluciones"**
3. Haz clic en **"Gestionar Resoluciones"**
4. Puedes crear y editar resoluciones
5. Sube el PDF de la resolución

> **Nota:** La publicación de resoluciones requiere un administrador.

---

## 7. Gestión de Noticias

**Ruta:** `/admin/noticias`

### 7.1. Ver Noticias

- Accede desde el menú lateral → **Noticias**
- Filtra por programa, año académico o estado

### 7.2. Crear una Noticia

1. Haz clic en **"Crear Noticia"**
2. Completa:
   - **Año Académico** (obligatorio)
   - **Título** (obligatorio)
   - **Contenido** (obligatorio) - Usa el editor de texto
   - **Programa**: Si está relacionada con un programa
   - **Extracto**: Resumen para listados
   - **Imagen destacada**: Imagen principal
   - **Etiquetas**: Para categorizar
3. Haz clic en **"Guardar"**

### 7.3. Usar el Editor de Texto

El editor incluye herramientas para:

| Función | Cómo usar |
|---------|-----------|
| **Negrita** | Selecciona texto → clic en **B** |
| **Cursiva** | Selecciona texto → clic en *I* |
| **Encabezados** | Selecciona → elige H1, H2 o H3 |
| **Listas** | Clic en icono de lista |
| **Enlaces** | Selecciona texto → clic en 🔗 → introduce URL |
| **Imágenes** | Clic en 🖼️ → introduce URL de imagen |
| **Videos** | Clic en ▶️ → pega URL de YouTube |
| **Tablas** | Clic en menú de tablas |

### 7.4. Subir Imagen Destacada

1. Arrastra la imagen al área de subida, o
2. Haz clic para seleccionar del ordenador
3. Formatos: JPEG, PNG, WebP, GIF
4. Tamaño máximo: 5 MB

### 7.5. Gestionar Etiquetas

**Seleccionar etiquetas existentes:**
- Marca las etiquetas en la lista

**Crear nueva etiqueta:**
1. Haz clic en **"Crear etiqueta"**
2. Introduce el nombre
3. Haz clic en **"Guardar"**

> **Nota:** Las noticias se crean en estado **Borrador**. Un administrador deberá publicarlas.

---

## 8. Gestión de Documentos

**Ruta:** `/admin/documentos`

### 8.1. Ver Documentos

- Accede desde el menú lateral → **Documentos**
- Filtra por categoría, programa, tipo o estado

### 8.2. Crear un Documento

1. Haz clic en **"Crear Documento"**
2. Completa:
   - **Categoría** (obligatorio)
   - **Título** (obligatorio)
   - **Tipo de documento** (obligatorio)
   - **Descripción**: Descripción del contenido
   - **Programa/Año académico**: Si aplica
   - **Versión**: Número de versión
   - **Archivo**: El documento a subir
3. Haz clic en **"Guardar"**

### 8.3. Tipos de Documento

| Tipo | Uso |
|------|-----|
| **Convocatoria** | Documentos oficiales de convocatorias |
| **Modelo** | Plantillas y formularios |
| **Seguro** | Documentación de seguros |
| **Consentimiento** | Formularios de consentimiento |
| **Guía** | Guías informativas |
| **FAQ** | Preguntas frecuentes |
| **Otro** | Otros documentos |

### 8.4. Formatos Soportados

| Tipo | Extensiones |
|------|-------------|
| PDF | .pdf |
| Word | .doc, .docx |
| Excel | .xls, .xlsx |
| PowerPoint | .ppt, .pptx |
| Texto | .txt, .csv |
| Imágenes | .jpeg, .jpg, .png, .webp |

**Tamaño máximo:** 20 MB

### 8.5. Editar un Documento

1. Haz clic en **"Editar"** (icono de lápiz)
2. Modifica los campos
3. Para cambiar el archivo:
   - Sube uno nuevo (reemplaza el anterior), o
   - Marca "Eliminar archivo" si no quieres archivo
4. Haz clic en **"Guardar"**

---

## 9. Gestión de Eventos

**Ruta:** `/admin/eventos`

### 9.1. Ver Eventos

- Accede desde el menú lateral → **Eventos**
- Puedes ver en **lista** o **calendario**
- Filtra por programa, convocatoria, tipo o fecha

### 9.2. Vista de Calendario

1. Haz clic en **"Calendario"** para cambiar la vista
2. Navega con los botones **← Anterior** / **Siguiente →**
3. Haz clic en **"Hoy"** para ir a la fecha actual
4. Cambia entre vista de **Mes**, **Semana** o **Día**

### 9.3. Crear un Evento

1. Haz clic en **"Crear Evento"**
2. Completa:
   - **Título** (obligatorio)
   - **Tipo de evento** (obligatorio)
   - **Fecha de inicio** (obligatorio)
   - **Fecha de fin**: Si el evento dura varios días
   - **Todo el día**: Marca si es evento completo
   - **Ubicación**: Lugar del evento
   - **Descripción**: Detalles del evento
   - **Programa/Convocatoria**: Si está relacionado
   - **Público**: Si es visible en el área pública
   - **Imágenes**: Fotos del evento
3. Haz clic en **"Guardar"**

### 9.4. Tipos de Evento

| Tipo | Uso |
|------|-----|
| **Apertura** | Inicio de convocatoria |
| **Cierre** | Fin de plazo |
| **Entrevista** | Entrevistas de selección |
| **Publicación Provisional** | Listado provisional |
| **Publicación Definitivo** | Listado definitivo |
| **Reunión Informativa** | Sesiones informativas |
| **Otro** | Otros eventos |

### 9.5. Subir Imágenes del Evento

1. Arrastra las imágenes al área de subida
2. Puedes subir varias imágenes
3. Formatos: JPEG, PNG, WebP, GIF
4. Tamaño máximo: 5 MB por imagen

---

## 10. Preguntas Frecuentes

### ¿Por qué no puedo publicar contenido?

Como editor, puedes crear y editar contenido, pero la publicación requiere permisos de administrador. Esto permite un flujo de revisión antes de hacer público el contenido.

**Qué hacer:**
1. Crea o edita el contenido
2. Asegúrate de que está completo y correcto
3. Notifica a un administrador para que lo revise y publique

---

### ¿Por qué no puedo eliminar registros?

La eliminación de contenido requiere permisos de administrador para evitar borrados accidentales.

**Qué hacer:**
- Contacta a un administrador si necesitas eliminar algo

---

### ¿Cómo sé qué contenido está pendiente de publicar?

1. Ve al listado correspondiente (noticias, convocatorias, etc.)
2. Filtra por estado **"Borrador"** o **"En revisión"**
3. Verás todo el contenido pendiente

---

### ¿Puedo ver quién publicó un contenido?

Sí, en la vista de detalle de cualquier registro puedes ver:
- Quién lo creó
- Quién lo modificó por última vez
- Fechas de creación y modificación

---

### ¿Qué hago si cometo un error?

- **Si aún no se ha publicado:** Simplemente edita y corrige
- **Si ya está publicado:** Contacta a un administrador para despublicar, corregir y volver a publicar

---

### ¿Cómo subo una imagen correctamente?

1. **Formato recomendado:** JPEG o PNG
2. **Tamaño:** Máximo 5 MB
3. **Dimensiones ideales:** 
   - Imagen destacada: 1200x800 píxeles
   - Imágenes de eventos: 1200x900 píxeles
4. **Consejo:** Comprime las imágenes antes de subir para mejor rendimiento

---

### ¿Puedo programar publicaciones?

No directamente. Puedes crear el contenido y notificar a un administrador para que lo publique en una fecha específica.

---

### ¿Cómo contacto a un administrador?

- Usa el sistema de comunicación interno de tu organización
- El administrador verá las notificaciones de contenido nuevo en su panel

---

## Resumen de Capacidades

| Módulo | Ver | Crear | Editar | Eliminar | Publicar |
|--------|:---:|:-----:|:------:|:--------:|:--------:|
| Programas | ✅ | ✅ | ✅ | ❌ | ❌ |
| Convocatorias | ✅ | ✅ | ✅ | ❌ | ❌ |
| Fases | ✅ | ✅ | ✅ | ❌ | - |
| Resoluciones | ✅ | ✅ | ✅ | ❌ | ❌ |
| Noticias | ✅ | ✅ | ✅ | ❌ | ❌ |
| Documentos | ✅ | ✅ | ✅ | ❌ | - |
| Eventos | ✅ | ✅ | ✅ | ❌ | - |
| Usuarios | ❌ | ❌ | ❌ | ❌ | - |
| Auditoría | ❌ | - | - | - | - |

---

**Fin de la Guía del Editor**

---

*Documento generado: Enero 2026*  
*Versión: 1.0*
