# Paso 51: Plan de Comandos de Setup (Developer y Production)

Este documento registra los prompts utilizados y los resultados obtenidos durante la sesión de planificación de los comandos de setup para desarrollo y producción.

---

## Resumen Ejecutivo

**Fecha**: Enero 2026  
**Duración**: 1 sesión  
**Objetivo**: Crear un plan detallado para desarrollar dos comandos Artisan que preparen la aplicación para desarrollo y producción

### Entregables Generados

| Archivo | Líneas | Descripción |
|---------|--------|-------------|
| `docs/pasos/paso-comandos-plan.md` | ~600 | Plan de trabajo completo para comandos de setup |
| `docs/pasos/paso51.md` | ~300 | Este archivo con prompts y resúmenes |

**Total**: ~900 líneas de documentación

---

## Prompt 1: Solicitud Inicial

**Usuario**:
> En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que hace de índice y otro llamado @planificacion_pasos.md que contiene las tareas a desarrollar en orden. Ya hemos terminado de desarrollar todos los puntos que aparecen en el documento, pero me gustaría añadir algo más. Durante el desarrollo de la aplicación se han generado seeders para la intriducción de datos de prueba. Muchos de ellos son simplemente para ver cómo la aplicación funciona, por tanto solo sirven para el desarrollo. Pero puede que haya otros datos que sí deban estar en la aplicación desde el inicio cuando se ponga en producción. Quiero que desarrolles dos comandos: uno será setup:developer y debe de preparar la aplicación para desarrollo (lanzar migraciones, seeders completos, podría limpiar cachés, y aquello que creas necesario). Luego generaremos otro comando que será setup:production que hará lo mismo, preparar la aplicación para producción (y lanzará solo los seeders estrictamente necesarios). Documenta el proceso a seguir en un archivo llamado paso-comandos-plan en la carpeta docs/pasos para luego desarrollarlo. Si hay preguntas o dudas a resolver previamente, estoy aquí para contestarlas.

**Resumen de la Respuesta**:
- Revisión de la documentación existente y estructura de seeders
- Identificación de 17 seeders existentes
- Clasificación de seeders en esenciales para producción vs. solo desarrollo
- Creación de un plan detallado con 6 fases iniciales:
  1. Análisis y Preparación
  2. Comando `setup:developer`
  3. Comando `setup:production`
  4. Seeders de Producción
  5. Tests
  6. Documentación
- Identificación de 5 preguntas pendientes que requieren decisión del usuario

**Decisiones Identificadas**:
1. NewsTagSeeder: ¿Crear etiquetas básicas o dejarlo vacío?
2. AdminUserSeeder: ¿Modificar existente o crear nuevo?
3. Migraciones en Producción: ¿Permitir `migrate:fresh` o solo `migrate`?
4. Contraseña Super-Admin: ¿Generar aleatoria o requerir en comando?
5. Validaciones de Entorno: ¿Qué nivel de validación?

---

## Prompt 2: Respuestas a Preguntas Pendientes

**Usuario**:
> Con respecto a las preguntas:
> 1. Podemos crear varias etiquetas por defecto que sean comunes en este tipo de web que estamos desarrollando. Así no empezamos de cero.
> 2. Considero que es mejor crear un ProductionAdminUserSeeder que solo contenga un usuario de rol super-administrador, el cual será encargado de crear el resto de usuarios o dotar del rol adecuado a los usuarios que se vayan registrando. De este usuario solo pediremos por terminal su email con el que se registre.
> 3. Permitiremos migrate:fresh, para que sea más sencillo, pero si estamos en producción solicitaremos confirmación antes de ejecutar el comando.
> 4. Generaremos una contraseña aleatoria y en el primer acceso el usuario indicará que la ha olvidado y podrá generar él mismo una nueva.
> 5. Esta pregunta no la entiendo. ¿A qué te refieres con nivel de validación de entorno?

**Resumen de la Respuesta**:
- Actualización del plan con las decisiones del usuario
- Aclaración de la pregunta 5 sobre validaciones de entorno
- Cambios realizados:
  - NewsTagSeeder marcado como ESENCIAL con etiquetas básicas
  - ProductionAdminUserSeeder separado, solo solicita email
  - `migrate:fresh` permitido con doble confirmación en producción
  - Contraseña aleatoria generada automáticamente
  - Validaciones de entorno explicadas (APP_ENV, APP_DEBUG, conexión BD, permisos)

---

## Prompt 3: Aclaración de Validaciones

**Usuario**:
> Con respecto al punto 5 no saltes las validaciones, bloquea en caso de error crítico y advierte en otro caso.

**Resumen de la Respuesta**:
- Actualización del plan con comportamiento específico de validaciones
- **Errores Críticos (BLOQUEAN)**:
  - Conexión a base de datos → Bloquea si falla
  - Archivo `.env` → Bloquea si no existe
  - Permisos de escritura → Bloquea si no hay permisos
- **Advertencias (NO BLOQUEAN)**:
  - `APP_ENV` no en production → Advierte pero permite continuar
  - `APP_DEBUG` en true → Advierte pero permite continuar
- Opción `--force` solo salta advertencias, no errores críticos
- Actualización de tests para reflejar el nuevo comportamiento

---

## Prompt 4: Actualización de Fase 6 y Nueva Fase 7

**Usuario**:
> En la Fase 6 me gustaría que tuvieras en cuenta también el añadir lo necesario a la guía de administrador que se generó en un paso anterior y que está almacenada en docs/guia-usuario. Y para cerrar el chat, añade una Fase 7 que sea generar un archivo llamado paso51 en docs/pasos que contenga todos los prompts de este chat y un resumen de cada una de sus respuestas.

**Resumen de la Respuesta**:
- Actualización de la Fase 6 para incluir actualización de la guía de administrador
- Añadida nueva sección 6.3: Actualizar Guía de Administrador
- Creación de la Fase 7: Generar Archivo de Documentación del Chat
- Actualización de cronograma y entregables
- Actualización de la tabla de progreso

---

## Decisiones Finales Tomadas

### 1. NewsTagSeeder
✅ **Decisión**: Crear etiquetas básicas comunes para el sitio web
- Etiquetas como: "Noticias", "Eventos", "Convocatorias", "Erasmus+", "Movilidad", "Formación", etc.
- Disponibles tanto en desarrollo como en producción

### 2. ProductionAdminUserSeeder
✅ **Decisión**: Crear seeder separado `ProductionAdminUserSeeder`
- Solo crea un usuario super-admin
- Solicita email por terminal (interactivo)
- El super-admin será responsable de crear el resto de usuarios

### 3. Migraciones en Producción
✅ **Decisión**: Permitir `migrate:fresh` con confirmación
- Se permite `migrate:fresh` pero con doble confirmación obligatoria
- Advertir claramente que se eliminarán todos los datos

### 4. Contraseña Super-Admin
✅ **Decisión**: Generar aleatoria automáticamente
- Contraseña aleatoria segura (mínimo 16 caracteres)
- Se muestra solo una vez al finalizar el setup
- El usuario usará "olvidé mi contraseña" en el primer acceso para establecer una nueva

### 5. Validaciones de Entorno
✅ **Decisión**: Validaciones con bloqueo en errores críticos
- **Errores Críticos (BLOQUEAN)**:
  - Conexión a base de datos → Bloquea si falla
  - Archivo `.env` → Bloquea si no existe
  - Permisos de escritura → Bloquea si no hay permisos
- **Advertencias (NO BLOQUEAN)**:
  - `APP_ENV` no en production → Advierte pero permite continuar
  - `APP_DEBUG` en true → Advierte pero permite continuar
- Opción `--force` solo salta advertencias, no errores críticos

---

## Estructura del Plan Final

El plan `paso-comandos-plan.md` incluye:

### Fases de Desarrollo

1. **Fase 1**: Análisis y Preparación
   - Revisión de seeders
   - Modificaciones necesarias

2. **Fase 2**: Comando `setup:developer`
   - Migraciones fresh
   - Todos los seeders
   - Limpieza de cachés
   - Credenciales de prueba

3. **Fase 3**: Comando `setup:production`
   - Validaciones de entorno
   - Solo seeders esenciales
   - Optimización de cachés
   - Creación de super-admin

4. **Fase 4**: Seeders de Producción
   - ProductionAdminUserSeeder
   - Modificaciones a NewsTagSeeder

5. **Fase 5**: Tests
   - Tests del comando developer
   - Tests del comando production
   - Tests del ProductionAdminUserSeeder

6. **Fase 6**: Documentación
   - Actualizar README.md
   - Crear documentación de comandos
   - Actualizar guía de administrador

7. **Fase 7**: Generar archivo paso51.md
   - Documentar todos los prompts
   - Resumir respuestas
   - Documentar decisiones

### Seeders Clasificados

**Esenciales para Producción (8)**:
1. LanguagesSeeder
2. ProgramsSeeder
3. AcademicYearsSeeder
4. DocumentCategoriesSeeder
5. SettingsSeeder
6. RolesAndPermissionsSeeder
7. NewsTagSeeder (con etiquetas básicas)
8. ProductionAdminUserSeeder

**Solo para Desarrollo (9)**:
1. AdminUserSeeder
2. CallSeeder
3. CallPhaseSeeder
4. ResolutionSeeder
5. NewsPostSeeder
6. DocumentsSeeder
7. ErasmusEventSeeder
8. NewsletterSubscriptionSeeder
9. DashboardDataSeeder

---

## Referencias

- **Plan Detallado**: [paso-comandos-plan.md](./paso-comandos-plan.md)
- **Planificación General**: [planificacion_pasos.md](../planificacion_pasos.md)
- **Guía de Administrador**: [guia-administrador.md](../guia-usuario/guia-administrador.md)

---

## Estado del Proyecto

**Estado**: 📋 Plan completado - Pendiente de aprobación para comenzar implementación

**Próximos Pasos**:
1. Revisar y aprobar el plan `paso-comandos-plan.md`
2. Comenzar implementación siguiendo las fases del plan
3. Ejecutar tests después de cada fase
4. Actualizar documentación según se complete cada fase

---

**Fecha de Creación**: Enero 2026  
**Última Actualización**: Enero 2026
