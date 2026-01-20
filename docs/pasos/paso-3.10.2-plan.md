# Plan de Trabajo - Paso 3.10.2: Guía de Usuario

## Objetivo

Crear guías de usuario completas en formato Markdown para los diferentes perfiles de usuario de la aplicación Erasmus+ Centro (Murcia), incluyendo capturas de pantalla para facilitar la comprensión.

---

## Análisis del Alcance

### Público Objetivo

La guía debe cubrir **dos perfiles de usuario**:

| Perfil | Roles | Permisos | Necesidades |
|--------|-------|----------|-------------|
| **Administrador** | `super-admin`, `admin` | Acceso completo, publicación | Gestión integral, usuarios, configuración |
| **Editor** | `editor`, `viewer` | Creación/edición, solo lectura | Creación de contenido, consulta |

### Módulos a Documentar

Basándose en la documentación existente (`funcionalidades-modulos.md` y `flujos-trabajo.md`):

1. **Acceso al Sistema** (Login, 2FA, perfil)
2. **Dashboard** (estadísticas, alertas, gráficos)
3. **Programas** (CRUD, imágenes, traducciones)
4. **Convocatorias** (CRUD, fases, resoluciones, estados)
5. **Noticias** (CRUD, editor Tiptap, etiquetas, imágenes)
6. **Documentos** (CRUD, categorías, archivos)
7. **Eventos** (CRUD, calendario)
8. **Newsletter** (gestión de suscriptores)
9. **Usuarios y Roles** (solo admin)
10. **Configuración** (solo admin)
11. **Auditoría** (logs del sistema)
12. **Importación/Exportación**

---

## Formato Elegido

### Markdown

**Razones para elegir Markdown:**
- ✅ Compatible con conversión a PDF (Pandoc, Typora, etc.)
- ✅ Permite incrustar imágenes con rutas relativas
- ✅ Mantiene consistencia con la documentación existente
- ✅ Versionable con Git
- ✅ Editable fácilmente

### Capturas de Pantalla

- **Ubicación**: `docs/guia-usuario/images/`
- **Formato**: PNG (mejor compatibilidad con conversores PDF)
- **Captura**: Mediante herramienta MCP del navegador
- **Idioma**: Capturas en español (idioma principal)
- **Tamaño**: Ventana de navegador consistente (1280x800 aproximadamente)

---

## Estructura de Archivos

```
docs/guia-usuario/
├── README.md                    # Índice de la guía
├── guia-administrador.md        # Guía completa para administradores
├── guia-editor.md               # Guía resumida para editores
└── images/                      # Capturas de pantalla
    ├── acceso/
    │   ├── login.png
    │   ├── 2fa.png
    │   └── perfil.png
    ├── dashboard/
    │   ├── vista-general.png
    │   ├── estadisticas.png
    │   └── graficos.png
    ├── programas/
    │   ├── listado.png
    │   ├── crear.png
    │   ├── editar.png
    │   └── detalle.png
    ├── convocatorias/
    │   ├── listado.png
    │   ├── crear.png
    │   ├── editar.png
    │   ├── detalle.png
    │   ├── fases-listado.png
    │   ├── fases-crear.png
    │   ├── resoluciones-listado.png
    │   └── resoluciones-crear.png
    ├── noticias/
    │   ├── listado.png
    │   ├── crear.png
    │   ├── editor-tiptap.png
    │   └── etiquetas.png
    ├── documentos/
    │   ├── listado.png
    │   ├── crear.png
    │   └── categorias.png
    ├── eventos/
    │   ├── listado.png
    │   ├── calendario.png
    │   └── crear.png
    ├── newsletter/
    │   ├── listado.png
    │   └── detalle.png
    ├── usuarios/
    │   ├── listado.png
    │   ├── crear.png
    │   └── roles.png
    ├── configuracion/
    │   ├── listado.png
    │   └── editar.png
    └── auditoria/
        ├── listado.png
        └── detalle.png
```

---

## Plan de Trabajo por Fases

### Fase 1: Estructura Base y Acceso al Sistema

**Entregables:**
- `docs/guia-usuario/README.md` - Índice de la guía
- Inicio de `docs/guia-usuario/guia-administrador.md`

**Contenido de Fase 1:**

#### 1.1. Introducción al Sistema
- Descripción general de la aplicación
- Requisitos del sistema (navegador recomendado)
- URL de acceso

#### 1.2. Acceso y Autenticación
- Pantalla de inicio de sesión
- Recuperación de contraseña
- Autenticación de dos factores (2FA)

#### 1.3. Interfaz General
- Navegación principal
- Breadcrumbs (migas de pan)
- Búsqueda global
- Selector de idioma
- Menú de usuario

#### 1.4. Perfil de Usuario
- Editar datos personales
- Cambiar contraseña
- Configurar 2FA
- Preferencias de apariencia

**Capturas necesarias (~5):**
- login.png
- 2fa.png
- perfil.png
- navegacion.png
- busqueda-global.png

---

### Fase 2: Dashboard y Programas

**Contenido:**

#### 2.1. Dashboard de Administración
- Vista general
- Tarjetas de estadísticas
- Accesos rápidos
- Alertas y notificaciones
- Gráficos de actividad
- Actividad reciente

#### 2.2. Gestión de Programas
- Listado de programas
- Filtros y búsqueda
- Crear nuevo programa
- Editar programa existente
- Gestión de imágenes
- Ordenamiento de programas
- Eliminación y restauración
- Eliminación permanente (solo super-admin)

**Capturas necesarias (~8):**
- dashboard/vista-general.png
- dashboard/estadisticas.png
- dashboard/graficos.png
- programas/listado.png
- programas/crear.png
- programas/editar.png
- programas/detalle.png
- programas/imagen.png

---

### Fase 3: Gestión de Convocatorias (Módulo Central)

**Contenido:**

#### 3.1. Convocatorias - Conceptos
- Ciclo de vida de una convocatoria
- Estados: borrador → abierta → cerrada → archivada
- Tipos de convocatoria
- Modalidades

#### 3.2. Listado de Convocatorias
- Filtros avanzados (programa, año, tipo, modalidad, estado)
- Búsqueda
- Ordenamiento
- Acciones disponibles según estado

#### 3.3. Crear Convocatoria
- Campos obligatorios
- Campos opcionales
- Configuración de destinos (JSON)
- Configuración de baremo (JSON)

#### 3.4. Editar Convocatoria
- Modificación de datos
- Cambio de estado
- Publicación

#### 3.5. Gestión de Fases
- Tipos de fases disponibles
- Crear nueva fase
- Editar fase
- Marcar fase como actual
- Reordenar fases
- Eliminar fase

#### 3.6. Gestión de Resoluciones
- Tipos de resoluciones
- Crear resolución
- Subir PDF
- Publicar resolución
- Editar resolución
- Eliminar resolución

#### 3.7. Importación de Convocatorias
- Formato del archivo Excel/CSV
- Modo dry-run (validación)
- Importación real
- Manejo de errores

#### 3.8. Exportación de Convocatorias
- Filtros aplicados
- Formato de exportación

**Capturas necesarias (~12):**
- convocatorias/listado.png
- convocatorias/filtros.png
- convocatorias/crear.png
- convocatorias/editar.png
- convocatorias/detalle.png
- convocatorias/publicar.png
- convocatorias/fases-listado.png
- convocatorias/fases-crear.png
- convocatorias/fases-reordenar.png
- convocatorias/resoluciones-listado.png
- convocatorias/resoluciones-crear.png
- convocatorias/importar.png

---

### Fase 4: Gestión de Noticias

**Contenido:**

#### 4.1. Listado de Noticias
- Filtros (programa, año, etiqueta, estado)
- Búsqueda
- Estados: borrador, publicada

#### 4.2. Crear Noticia
- Campos básicos (título, extracto)
- Editor Tiptap (contenido enriquecido)
- Formateo de texto
- Insertar enlaces
- Listas y encabezados

#### 4.3. Gestión de Imágenes
- Subir imagen destacada
- Conversiones automáticas (WebP)
- Eliminar imagen
- Restaurar imagen eliminada

#### 4.4. Asignar Etiquetas
- Selección de etiquetas existentes
- Crear etiqueta desde el formulario (si disponible)

#### 4.5. Publicar Noticia
- Requisitos para publicar
- Efecto de la publicación (notificaciones)

#### 4.6. Gestión de Etiquetas
- Listado de etiquetas
- Crear etiqueta
- Editar etiqueta
- Ver noticias asociadas
- Eliminar etiqueta

**Capturas necesarias (~8):**
- noticias/listado.png
- noticias/crear.png
- noticias/editor-tiptap.png
- noticias/imagen.png
- noticias/etiquetas.png
- noticias/publicar.png
- etiquetas/listado.png
- etiquetas/crear.png

---

### Fase 5: Gestión de Documentos

**Contenido:**

#### 5.1. Listado de Documentos
- Filtros (categoría, programa, año)
- Búsqueda
- Vista de archivos

#### 5.2. Crear Documento
- Campos básicos
- Subir archivo (FilePond)
- Tipos de archivo permitidos
- Asignar categoría

#### 5.3. Editar Documento
- Modificar datos
- Reemplazar archivo
- Cambiar categoría

#### 5.4. Gestión de Categorías
- Listado de categorías
- Crear categoría
- Editar categoría
- Ordenamiento
- Eliminar categoría

#### 5.5. Consentimientos de Medios
- Qué son los consentimientos
- Ver consentimientos asociados
- Gestión de permisos de uso

**Capturas necesarias (~6):**
- documentos/listado.png
- documentos/crear.png
- documentos/subir-archivo.png
- documentos/detalle.png
- categorias/listado.png
- categorias/crear.png

---

### Fase 6: Gestión de Eventos

**Contenido:**

#### 6.1. Vista de Calendario
- Vista mensual
- Vista semanal
- Vista diaria
- Navegación entre fechas

#### 6.2. Listado de Eventos
- Filtros (programa, fecha)
- Búsqueda

#### 6.3. Crear Evento
- Campos básicos (título, fechas)
- Ubicación
- Descripción
- Asociar a programa
- Asociar a convocatoria
- Subir imagen

#### 6.4. Editar Evento
- Modificar datos
- Gestión de imagen

**Capturas necesarias (~5):**
- eventos/calendario-mes.png
- eventos/calendario-semana.png
- eventos/listado.png
- eventos/crear.png
- eventos/detalle.png

---

### Fase 7: Gestión de Newsletter

**Contenido:**

#### 7.1. Listado de Suscriptores
- Filtros (programa, verificación, estado)
- Búsqueda
- Estados de suscripción

#### 7.2. Detalle de Suscriptor
- Información del suscriptor
- Programas de interés
- Fechas relevantes

#### 7.3. Exportar Suscriptores
- Exportación a Excel
- Cumplimiento GDPR

#### 7.4. Eliminar Suscriptor
- Eliminación permanente (GDPR)
- Confirmación

**Capturas necesarias (~3):**
- newsletter/listado.png
- newsletter/detalle.png
- newsletter/exportar.png

---

### Fase 8: Administración del Sistema (Solo Admin)

**Contenido:**

#### 8.1. Gestión de Usuarios
- Listado de usuarios
- Filtros y búsqueda
- Crear usuario
- Editar usuario
- Asignar roles
- Ver actividad del usuario
- Eliminar usuario
- Importar usuarios

#### 8.2. Gestión de Roles
- Roles del sistema
- Crear nuevo rol
- Editar permisos de un rol
- Ver usuarios con el rol
- Protección de roles del sistema

#### 8.3. Configuración del Sistema
- Listado de configuraciones
- Editar configuración
- Tipos de datos (string, integer, boolean, json)
- Subir logo del centro

#### 8.4. Gestión de Traducciones
- Listado de traducciones
- Filtros (modelo, idioma, campo)
- Crear traducción
- Editar traducción

#### 8.5. Auditoría y Logs
- Listado de logs
- Filtros (modelo, usuario, acción, fecha)
- Ver detalle de un log
- Visualización de cambios (diff)
- Exportar logs

**Capturas necesarias (~12):**
- usuarios/listado.png
- usuarios/crear.png
- usuarios/editar.png
- usuarios/roles.png
- roles/listado.png
- roles/editar.png
- configuracion/listado.png
- configuracion/editar.png
- traducciones/listado.png
- traducciones/crear.png
- auditoria/listado.png
- auditoria/detalle.png

---

### Fase 9: Guía de Editores

**Archivo:** `docs/guia-usuario/guia-editor.md`

**Contenido:**
Guía resumida que cubre:

1. **Introducción**
   - Qué puede hacer un editor
   - Limitaciones del rol

2. **Acceso al Sistema**
   - Login y perfil (reutilizar de guía admin)

3. **Crear y Editar Contenido**
   - Convocatorias (sin publicar)
   - Noticias (sin publicar)
   - Documentos
   - Eventos

4. **Flujo de Trabajo**
   - Crear contenido
   - Solicitar publicación (al administrador)
   - Revisar correcciones

5. **Funcionalidades NO disponibles**
   - Publicar contenido
   - Eliminar contenido
   - Gestionar usuarios
   - Configuración del sistema

**Capturas:** Reutiliza las de la guía de administrador

---

### Fase 10: Revisión Final e Índice

**Tareas:**

1. **Crear índice general** (`docs/guia-usuario/README.md`)
   - Enlaces a todas las secciones
   - Descripción de cada guía

2. **Verificar capturas**
   - Todas las capturas existen
   - Rutas correctas en los documentos
   - Calidad adecuada

3. **Revisión de contenido**
   - Ortografía y gramática
   - Consistencia de terminología
   - Enlaces internos funcionan

4. **Actualizar documentación principal**
   - Añadir referencia en `docs/README.md`
   - Actualizar `planificacion_pasos.md`

---

## Resumen de Entregables

| Fase | Archivo | Capturas |
|------|---------|----------|
| 1 | README.md + inicio guia-administrador.md | ~5 |
| 2 | Dashboard y Programas | ~8 |
| 3 | Convocatorias completas | ~12 |
| 4 | Noticias y Etiquetas | ~8 |
| 5 | Documentos y Categorías | ~6 |
| 6 | Eventos | ~5 |
| 7 | Newsletter | ~3 |
| 8 | Administración del Sistema | ~12 |
| 9 | guia-editor.md | Reutiliza |
| 10 | Revisión final | - |
| **Total** | 3 archivos MD | **~59 capturas** |

---

## Notas Importantes

### Convenciones de Escritura

1. **Lenguaje**: Español, formal pero accesible
2. **Voz**: Segunda persona del singular (tú) o impersonal
3. **Verbos**: Imperativos para instrucciones ("Haz clic", "Selecciona")
4. **Nombres de botones/menús**: Entre comillas o en negrita
5. **Rutas de navegación**: Con flechas (Inicio → Convocatorias → Crear)

### Convenciones de Capturas

1. **Nombrado**: kebab-case (ejemplo: `crear-convocatoria.png`)
2. **Tamaño**: Consistente, preferiblemente 1280x800
3. **Contenido**: Datos de ejemplo realistas, sin información sensible
4. **Anotaciones**: Si son necesarias, añadir después con editor de imágenes

### Prioridad de Módulos

Si hay que priorizar, el orden de importancia es:

1. Convocatorias (módulo central)
2. Noticias
3. Documentos
4. Eventos
5. Newsletter
6. Usuarios/Roles
7. Configuración
8. Auditoría

---

## Progreso

### Estado Actual

| Fase | Estado | Fecha |
|------|--------|-------|
| 1 | ⏳ Pendiente | - |
| 2 | ⏳ Pendiente | - |
| 3 | ⏳ Pendiente | - |
| 4 | ⏳ Pendiente | - |
| 5 | ⏳ Pendiente | - |
| 6 | ⏳ Pendiente | - |
| 7 | ⏳ Pendiente | - |
| 8 | ⏳ Pendiente | - |
| 9 | ⏳ Pendiente | - |
| 10 | ⏳ Pendiente | - |

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan creado - Pendiente de ejecución
