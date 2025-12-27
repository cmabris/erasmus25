# Diagramas de Base de Datos - Erasmus25

Este directorio contiene varios formatos del diagrama de base de datos de la aplicación Erasmus25.

## Archivos Disponibles

### 1. `database-diagram.md`
Diagrama completo en formato **Mermaid** que puede visualizarse en:
- GitHub (renderizado automático)
- Editores Markdown con soporte Mermaid (VS Code, Obsidian, etc.)
- [Mermaid Live Editor](https://mermaid.live/)

### 2. `database-diagram.png` y `database-diagram.svg`
**✅ Imágenes ya generadas** - Diagrama visual de la base de datos en formato PNG (265KB) y SVG (397KB).
- PNG: Imagen rasterizada de alta calidad (2400x1800px)
- SVG: Imagen vectorial escalable (mejor para impresión y zoom)

### 3. `database-diagram-simple.mmd`
Versión simplificada del diagrama Mermaid optimizada para generación de imágenes. Incluye solo los campos principales y relaciones esenciales.

### 4. `database-mysql-workbench.sql`
Script SQL completo para importar en **MySQL Workbench** y generar el diagrama EER visualmente.

**Cómo usar:**
1. Abre MySQL Workbench
2. Conecta a tu base de datos
3. File > Import > Run SQL Script
4. Selecciona `database-mysql-workbench.sql`
5. Ejecuta el script
6. Database > Reverse Engineer (Ctrl+R o Cmd+R)
7. Selecciona la base de datos y todas las tablas
8. MySQL Workbench generará automáticamente el diagrama EER
9. **Para guardar como .mwb:** File > Save Model (Ctrl+S o Cmd+S) y guarda como `database-model.mwb`

> **Nota:** El archivo `.mwb` es un formato binario propietario de MySQL Workbench que contiene el modelo completo con posicionamiento de tablas, colores, notas, etc. Solo puede generarse desde MySQL Workbench después de importar el SQL y crear el diagrama EER.

### 3. `generate-database-image.sh` y `generate-database-image.js`
Scripts para generar imágenes PNG/SVG desde el diagrama Mermaid.

**Requisitos:**
- Node.js instalado
- @mermaid-js/mermaid-cli instalado globalmente

**Instalación:**
```bash
npm install -g @mermaid-js/mermaid-cli
```

**Uso (Bash):**
```bash
chmod +x generate-database-image.sh
./generate-database-image.sh
```

**Uso (Node.js):**
```bash
node generate-database-image.js
```

**O usando npx directamente (recomendado):**
```bash
# Generar PNG
npx --yes @mermaid-js/mermaid-cli -i database-diagram-simple.mmd -o database-diagram.png -b transparent -w 2400 -H 1800

# Generar SVG
npx --yes @mermaid-js/mermaid-cli -i database-diagram-simple.mmd -o database-diagram.svg -b transparent
```

> **Nota:** Las imágenes ya están generadas en este repositorio. Solo necesitas ejecutar estos comandos si quieres regenerarlas.

## Generar Imágenes Manualmente

Si prefieres generar las imágenes manualmente:

### Opción 1: Mermaid Live Editor (Recomendado)
1. Ve a https://mermaid.live/
2. Copia el código Mermaid del archivo `database-diagram.md` (entre las etiquetas ```mermaid y ```)
3. Pega el código en el editor
4. Descarga como PNG o SVG

### Opción 2: Usando Mermaid CLI
```bash
# Instalar globalmente
npm install -g @mermaid-js/mermaid-cli

# Generar PNG
mmdc -i database-diagram.mmd -o database-diagram.png -b transparent -w 2400 -H 1800

# Generar SVG
mmdc -i database-diagram.mmd -o database-diagram.svg -b transparent
```

### Opción 3: Usando herramientas online
- [Mermaid Live Editor](https://mermaid.live/)
- [Kroki](https://kroki.io/)
- [Mermaid Chart](https://www.mermaidchart.com/)

## Estructura de la Base de Datos

La base de datos incluye:

### Tablas Principales
- **users**: Usuarios del sistema
- **programs**: Programas Erasmus+
- **academic_years**: Años académicos
- **calls**: Convocatorias
- **call_phases**: Fases de las convocatorias
- **call_applications**: Solicitudes a convocatorias
- **resolutions**: Resoluciones de convocatorias

### Contenido
- **news_posts**: Noticias y experiencias
- **news_tags**: Etiquetas para noticias
- **documents**: Documentos descargables
- **document_categories**: Categorías de documentos
- **erasmus_events**: Eventos del calendario

### Sistema
- **languages**: Idiomas disponibles
- **translations**: Traducciones polimórficas
- **settings**: Configuración del sistema
- **notifications**: Notificaciones de usuarios
- **audit_logs**: Registro de auditoría
- **newsletter_subscriptions**: Suscripciones al boletín

### Permisos (Laravel Permission)
- **roles**: Roles del sistema
- **permissions**: Permisos
- **model_has_roles**: Relación usuarios-roles
- **model_has_permissions**: Relación usuarios-permisos
- **role_has_permissions**: Relación roles-permisos

### Multimedia
- **media**: Archivos multimedia (Laravel Media Library)
- **media_consents**: Consentimientos de uso de multimedia

### Sistema Laravel
- **sessions**: Sesiones de usuario
- **password_reset_tokens**: Tokens de recuperación de contraseña
- **cache**: Caché del sistema
- **cache_locks**: Bloqueos de caché
- **jobs**: Trabajos en cola
- **job_batches**: Lotes de trabajos
- **failed_jobs**: Trabajos fallidos

## Relaciones Principales

- **Programs** → tiene múltiples **Calls**, **NewsPosts**, **Documents**, **ErasmusEvents**
- **AcademicYears** → tiene múltiples **Calls**, **NewsPosts**, **Documents**
- **Calls** → tiene múltiples **CallPhases**, **CallApplications**, **Resolutions**
- **Users** → crea/actualiza **Calls**, **Documents**, **Resolutions**, **ErasmusEvents**
- **NewsPosts** ↔ **NewsTags** (many-to-many)
- **Translations** → polimórfica (puede traducir cualquier modelo)

## Notas

- Las foreign keys a `users` utilizan `ON DELETE SET NULL` para mantener el historial
- Las foreign keys principales utilizan `ON DELETE CASCADE` para mantener la integridad
- Los campos JSON almacenan estructuras de datos complejas (`destinations`, `scoring_table`, `programs`, `changes`)



