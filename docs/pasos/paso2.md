# Paso 2: Generación de Diagramas de Base de Datos

Este documento contiene todos los prompts utilizados para generar los diagramas de base de datos de la aplicación "Erasmus+ Centro (Murcia)" en diferentes formatos (Mermaid, imágenes PNG/SVG, y SQL para MySQL Workbench).

## Prompt 1: Solicitud de Diagrama Gráfico de Base de Datos

**Prompt:**
> "Recuerda que la aplicación está documentada en la carpeta docs, para que busques allí lo necesario. ¿Puedes generar una imagen o archivo en el formato adecuado que contenga el diseño de la base de datos en modo gráfico? En caso de que lo puedas generar, guárdalo en la carpeta docs."

**Resultado:**
- Se exploró la estructura completa de la base de datos leyendo todas las migraciones y modelos
- Se identificaron todas las tablas y relaciones del sistema:
  - Tablas principales: `users`, `programs`, `academic_years`, `calls`, `call_phases`, `call_applications`, `resolutions`
  - Tablas de contenido: `news_posts`, `news_tags`, `documents`, `document_categories`, `erasmus_events`
  - Tablas del sistema: `languages`, `translations`, `settings`, `notifications`, `audit_logs`, `newsletter_subscriptions`
  - Tablas de permisos: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`
  - Tablas multimedia: `media`, `media_consents`
  - Tablas Laravel: `sessions`, `password_reset_tokens`, `cache`, `jobs`, etc.
- Se creó el archivo `docs/database-diagram.md` con un diagrama completo en formato Mermaid
- El diagrama incluye todas las relaciones entre tablas (uno-a-muchos, muchos-a-muchos)
- Se documentaron todas las relaciones principales y convenciones de claves foráneas

**Archivos generados:**
- `docs/database-diagram.md` - Diagrama Mermaid completo con descripción de relaciones

---

## Prompt 2: Solicitud de Formatos Adicionales (Imagen y MySQL Workbench)

**Prompt:**
> "Si, por favor, genéralo también como imagen o como archivo .mwb de Mysql Workbench."

**Resultado:**
- Se crearon múltiples formatos del diagrama para diferentes usos:

### Archivos Generados:

1. **`docs/database-diagram.md`**
   - Diagrama completo en formato Mermaid
   - Visualizable en GitHub, VS Code, Obsidian, y herramientas online
   - Incluye descripción detallada de todas las relaciones

2. **`docs/database-diagram.png`** (265KB)
   - Imagen PNG generada automáticamente (2400x1800px)
   - Formato rasterizado de alta calidad
   - Lista para usar en documentación, presentaciones, etc.

3. **`docs/database-diagram.svg`** (397KB)
   - Imagen SVG vectorial escalable
   - Mejor calidad para impresión y zoom
   - Formato vectorial sin pérdida de calidad

4. **`docs/database-diagram-simple.mmd`**
   - Versión simplificada del diagrama Mermaid
   - Optimizada para generación de imágenes
   - Incluye solo campos principales y relaciones esenciales
   - Resuelve problemas de sintaxis con el CLI de Mermaid

5. **`docs/database-mysql-workbench.sql`**
   - Script SQL completo con todas las tablas y relaciones
   - Incluye todas las foreign keys y constraints
   - Listo para importar en MySQL Workbench
   - Instrucciones incluidas para generar el diagrama EER y guardar como .mwb

6. **`docs/generate-database-image.sh`**
   - Script Bash para regenerar las imágenes
   - Verifica e instala automáticamente @mermaid-js/mermaid-cli si es necesario
   - Genera tanto PNG como SVG

7. **`docs/generate-database-image.js`**
   - Script Node.js alternativo para generar imágenes
   - Usa npx si mmdc no está instalado globalmente
   - Manejo de errores mejorado

8. **`docs/README-database-diagrams.md`**
   - Documentación completa sobre todos los archivos
   - Instrucciones de uso para cada formato
   - Guía para generar el archivo .mwb desde MySQL Workbench

### Proceso de Generación:

1. **Extracción del código Mermaid:**
   - Se extrajo el código Mermaid del archivo markdown
   - Se creó una versión simplificada para compatibilidad con el CLI

2. **Generación de imágenes:**
   - Se utilizó `npx @mermaid-js/mermaid-cli` para generar las imágenes
   - PNG generado con dimensiones 2400x1800px y fondo transparente
   - SVG generado con fondo transparente

3. **Script SQL para MySQL Workbench:**
   - Se creó un script SQL completo basado en todas las migraciones
   - Incluye todas las tablas con sus campos, tipos, índices y foreign keys
   - Mantiene las convenciones de `ON DELETE CASCADE` y `ON DELETE SET NULL`
   - Incluye comentarios explicativos

### Instrucciones para MySQL Workbench (.mwb):

Para generar el archivo `.mwb` desde MySQL Workbench:
1. Abrir MySQL Workbench
2. Importar el script SQL: File > Import > Run SQL Script → seleccionar `database-mysql-workbench.sql`
3. Generar diagrama EER: Database > Reverse Engineer (Ctrl+R o Cmd+R)
4. Seleccionar la base de datos y todas las tablas
5. Guardar el modelo: File > Save Model → guardar como `database-model.mwb`

**Nota:** El archivo `.mwb` es un formato binario propietario de MySQL Workbench que contiene el modelo completo con posicionamiento de tablas, colores, notas, etc. Solo puede generarse desde MySQL Workbench después de importar el SQL y crear el diagrama EER.

---

## Resumen de Archivos Creados

### Diagramas y Documentación:
- ✅ `docs/database-diagram.md` - Diagrama Mermaid completo
- ✅ `docs/database-diagram.png` - Imagen PNG (265KB, 2400x1800px)
- ✅ `docs/database-diagram.svg` - Imagen SVG (397KB, vectorial)
- ✅ `docs/database-diagram-simple.mmd` - Versión simplificada para CLI
- ✅ `docs/database-mysql-workbench.sql` - Script SQL para MySQL Workbench
- ✅ `docs/README-database-diagrams.md` - Documentación completa

### Scripts de Utilidad:
- ✅ `docs/generate-database-image.sh` - Script Bash para regenerar imágenes
- ✅ `docs/generate-database-image.js` - Script Node.js para regenerar imágenes

### Estructura del Diagrama:

El diagrama incluye:

**Tablas Principales:**
- `users` - Usuarios del sistema
- `programs` - Programas Erasmus+
- `academic_years` - Años académicos
- `calls` - Convocatorias
- `call_phases` - Fases de convocatorias
- `call_applications` - Solicitudes
- `resolutions` - Resoluciones

**Contenido:**
- `news_posts` - Noticias y experiencias
- `news_tags` - Etiquetas
- `documents` - Documentos descargables
- `document_categories` - Categorías
- `erasmus_events` - Eventos del calendario

**Sistema:**
- `languages` - Idiomas
- `translations` - Traducciones polimórficas
- `settings` - Configuración
- `notifications` - Notificaciones
- `audit_logs` - Auditoría
- `newsletter_subscriptions` - Suscripciones

**Permisos (Laravel Permission):**
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

**Multimedia:**
- `media` - Archivos (Laravel Media Library)
- `media_consents` - Consentimientos

**Laravel:**
- `sessions`, `password_reset_tokens`, `cache`, `jobs`, `job_batches`, `failed_jobs`

### Relaciones Documentadas:

- **Programs** → tiene múltiples Calls, NewsPosts, Documents, ErasmusEvents
- **AcademicYears** → tiene múltiples Calls, NewsPosts, Documents
- **Calls** → tiene múltiples CallPhases, CallApplications, Resolutions
- **Users** → crea/actualiza Calls, Documents, Resolutions, ErasmusEvents
- **NewsPosts** ↔ **NewsTags** (many-to-many)
- **Translations** → polimórfica (puede traducir cualquier modelo)

---

## Notas Técnicas

### Convenciones de Claves Foráneas:
- **Cascade Delete**: Se eliminan en cascada cuando se elimina el padre (ej: `call_phases` cuando se elimina `call`)
- **Null On Delete**: Se establecen en NULL cuando se elimina el padre (ej: `created_by` cuando se elimina `user`)
- Las foreign keys a `users` utilizan `ON DELETE SET NULL` para mantener el historial
- Las foreign keys principales utilizan `ON DELETE CASCADE` para mantener la integridad

### Campos JSON:
- `destinations` en `calls`: Array de países/ciudades/entidades
- `scoring_table` en `calls`: Estructura del baremo de evaluación
- `programs` en `newsletter_subscriptions`: Array de programas de interés
- `changes` en `audit_logs`: Objeto con cambios antes/después

### Herramientas Utilizadas:
- **Mermaid**: Para diagramas en formato texto
- **@mermaid-js/mermaid-cli**: Para generar imágenes desde Mermaid
- **MySQL Workbench**: Para diagramas EER y archivos .mwb
- **npx**: Para ejecutar herramientas sin instalación global

---

## Visualización de los Diagramas

Los diagramas pueden visualizarse en:

1. **GitHub**: El archivo `.md` se renderiza automáticamente
2. **VS Code**: Con extensiones de Mermaid
3. **Mermaid Live Editor**: https://mermaid.live/
4. **MySQL Workbench**: Importando el SQL y generando el EER
5. **Cualquier visor de imágenes**: Para los archivos PNG/SVG generados

---

## Regeneración de Imágenes

Si necesitas regenerar las imágenes en el futuro:

```bash
# Opción 1: Usando el script Bash
cd docs
./generate-database-image.sh

# Opción 2: Usando el script Node.js
cd docs
node generate-database-image.js

# Opción 3: Usando npx directamente
cd docs
npx --yes @mermaid-js/mermaid-cli -i database-diagram-simple.mmd -o database-diagram.png -b transparent -w 2400 -H 1800
npx --yes @mermaid-js/mermaid-cli -i database-diagram-simple.mmd -o database-diagram.svg -b transparent
```

---

## Conclusión

Se generaron exitosamente múltiples formatos del diagrama de base de datos:
- ✅ Formato Mermaid para documentación y visualización en línea
- ✅ Imágenes PNG y SVG para uso en presentaciones y documentación impresa
- ✅ Script SQL para MySQL Workbench con instrucciones para generar el archivo .mwb
- ✅ Scripts de utilidad para regenerar las imágenes cuando sea necesario
- ✅ Documentación completa sobre todos los formatos disponibles

Todos los archivos están guardados en la carpeta `docs/` y listos para usar.
