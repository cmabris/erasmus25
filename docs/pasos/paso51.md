# Paso 51: Comandos de Setup (Developer y Production)

Este documento registra todos los prompts utilizados y los resultados obtenidos durante la sesión de desarrollo de los comandos `setup:developer` y `setup:production` para la aplicación Erasmus+ Centro (Murcia).

---

## Resumen Ejecutivo

**Fecha**: Enero 2026  
**Duración**: 1 sesión completa  
**Objetivo**: Desarrollar dos comandos Artisan que preparen la aplicación para desarrollo y producción, incluyendo migraciones, seeders, optimización de cachés y creación de usuarios iniciales.

### Entregables Generados

| Archivo | Líneas | Descripción |
|---------|--------|-------------|
| `docs/pasos/paso-comandos-plan.md` | ~900 | Plan de trabajo completo |
| `database/seeders/NewsTagSeeder.php` | ~50 | Actualizado con etiquetas básicas |
| `database/seeders/ProductionAdminUserSeeder.php` | ~150 | Nuevo seeder para producción |
| `app/Console/Commands/SetupDeveloper.php` | ~200 | Comando para desarrollo |
| `app/Console/Commands/SetupProduction.php` | ~350 | Comando para producción |
| `tests/Feature/Commands/SetupDeveloperTest.php` | ~120 | Tests del comando developer |
| `tests/Feature/Commands/SetupProductionTest.php` | ~250 | Tests del comando production |
| `tests/Feature/Seeders/ProductionAdminUserSeederTest.php` | ~200 | Tests del seeder |
| `tests/Pest.php` | +50 | Helpers para configuración de BD en tests |
| `README.md` | +50 | Actualizado con documentación de comandos |
| `docs/guia-usuario/guia-administrador.md` | +200 | Sección de configuración inicial |

**Total**: ~2,500 líneas de código y documentación

---

## Prompt 1: Solicitud Inicial

**Usuario**:
> En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que hace de índice y otro llamado @planificacion_pasos.md que contiene las tareas a desarrollar en orden. Ya hemos terminado de desarrollar todos los puntos que aparecen en el documento, pero me gustaría añadir algo más. Durante el desarrollo de la aplicación se han generado seeders para la intriducción de datos de prueba. Muchos de ellos son simplemente para ver cómo la aplicación funciona, por tanto solo sirven para el desarrollo. Pero puede que haya otros datos que sí deban estar en la aplicación desde el inicio cuando se ponga en producción. Quiero que desarrolles dos comandos: uno será setup:developer y debe de preparar la aplicación para desarrollo (lanzar migraciones, seeders completos, podría limpiar cachés, y aquello que creas necesario). Luego generaremos otro comando que será setup:production que hará lo mismo, preparar la aplicación para producción (y lanzará solo los seeders estrictamente necesarios). Documenta el proceso a seguir en un archivo llamado paso-comandos-plan en la carpeta docs/pasos para luego desarrollarlo. Si hay preguntas o dudas a resolver previamente, estoy aquí para contestarlas.

**Resumen de la Respuesta**:
- Revisión completa de la documentación existente y estructura de seeders
- Identificación de 17 seeders existentes en la aplicación
- Clasificación de seeders en dos categorías:
  - **Esenciales para producción** (8 seeders): Idiomas, Programas, Años Académicos, Categorías de Documentos, Configuración, Roles y Permisos, Etiquetas de Noticias, Usuario Admin
  - **Solo para desarrollo** (9 seeders): Usuarios de prueba, Convocatorias, Noticias, Documentos, Eventos, Newsletter, Dashboard
- Creación de un plan detallado con 6 fases iniciales:
  1. Análisis y Preparación (modificaciones a seeders)
  2. Comando `setup:developer` (migraciones, todos los seeders, cachés, credenciales)
  3. Comando `setup:production` (validaciones, solo seeders esenciales, optimización)
  4. Seeders de Producción (ProductionAdminUserSeeder, NewsTagSeeder actualizado)
  5. Tests (comandos y seeders)
  6. Documentación (README, guía de administrador)
- Identificación de 5 preguntas pendientes que requieren decisión del usuario:
  1. NewsTagSeeder: ¿Crear etiquetas básicas o dejarlo vacío?
  2. AdminUserSeeder: ¿Modificar existente o crear nuevo?
  3. Migraciones en Producción: ¿Permitir `migrate:fresh` o solo `migrate`?
  4. Contraseña Super-Admin: ¿Generar aleatoria o requerir en comando?
  5. Validaciones de Entorno: ¿Qué nivel de validación?

**Archivos creados**:
- `docs/pasos/paso-comandos-plan.md` - Plan completo con todas las fases y detalles técnicos

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
- Actualización del plan con todas las decisiones del usuario
- Aclaración de la pregunta 5 sobre validaciones de entorno (APP_ENV, APP_DEBUG, conexión BD, permisos de archivos)
- Cambios realizados en el plan:
  - NewsTagSeeder marcado como ESENCIAL con etiquetas básicas comunes
  - ProductionAdminUserSeeder separado, solo solicita email interactivamente
  - `migrate:fresh` permitido con doble confirmación en producción
  - Contraseña aleatoria generada automáticamente (mínimo 16 caracteres, con mayúsculas, minúsculas, números y símbolos)
  - Validaciones de entorno explicadas en detalle (verificación de APP_ENV, APP_DEBUG, conexión a BD, permisos de escritura)

**Archivos modificados**:
- `docs/pasos/paso-comandos-plan.md` - Actualizado con decisiones y aclaraciones

---

## Prompt 3: Aclaración de Validaciones

**Usuario**:
> Con respecto al punto 5 no saltes las validaciones, bloquea en caso de error crítico y advierte en otro caso.

**Resumen de la Respuesta**:
- Actualización del plan con comportamiento específico de validaciones
- **Errores Críticos (BLOQUEAN la ejecución)**:
  - Conexión a base de datos → Si falla, BLOQUEAR y terminar
  - Archivo `.env` → Si no existe, BLOQUEAR y terminar
  - Permisos de escritura en `storage/` y `bootstrap/cache/` → Si no hay permisos, BLOQUEAR y terminar
- **Advertencias (NO BLOQUEAN, solo advierten)**:
  - `APP_ENV` no en 'production' → Advierte pero permite continuar
  - `APP_DEBUG` en 'true' → Advierte pero permite continuar
- Opción `--force` solo salta advertencias, no errores críticos
- Actualización de la sección de tests para reflejar el nuevo comportamiento

**Archivos modificados**:
- `docs/pasos/paso-comandos-plan.md` - Actualizado con comportamiento detallado de validaciones

---

## Prompt 4: Actualización de Fase 6 y Nueva Fase 7

**Usuario**:
> En la Fase 6 me gustaría que tuvieras en cuenta también el añadir lo necesario a la guía de administrador que se generó en un paso anterior y que está almacenada en docs/guia-usuario. Y para cerrar el chat, añade una Fase 7 que sea generar un archivo llamado paso51 en docs/pasos que contenga todos los prompts de este chat y un resumen de cada una de sus respuestas.

**Resumen de la Respuesta**:
- Actualización de la Fase 6 para incluir actualización de la guía de administrador
- Añadida nueva sección 6.3: Actualizar Guía de Administrador con:
  - Sección "Configuración Inicial del Sistema"
  - Documentación del comando `setup:production` para instalación inicial
  - Explicación de qué seeders se ejecutan en producción
  - Documentación de creación del usuario super-admin inicial
  - Instrucciones para cambiar contraseña del super-admin
- Creación de la Fase 7: Generar Archivo de Documentación del Chat
  - Archivo `docs/pasos/paso51.md`
  - Contener todos los prompts del chat
  - Resumir cada una de las respuestas
- Actualización de cronograma y entregables
- Actualización de la tabla de progreso

**Archivos modificados**:
- `docs/pasos/paso-comandos-plan.md` - Añadida Fase 7 y actualizada Fase 6

---

## Prompt 5: Inicio de Fase 1

**Usuario**:
> Ya podemos comenzar con la Fase 1

**Resumen de la Respuesta**:
- Implementación completa de la Fase 1: Análisis y Preparación
- **NewsTagSeeder actualizado**:
  - Añadidas 6 etiquetas básicas comunes: "Noticias", "Eventos", "Convocatorias", "Erasmus+", "Movilidad", "Formación"
  - Disponibles tanto en desarrollo como en producción
- **ProductionAdminUserSeeder creado**:
  - Solicitud interactiva de email del super-administrador
  - Generación automática de contraseña aleatoria segura (mínimo 16 caracteres)
  - Validación de email y verificación de duplicados
  - Asignación automática del rol `super-admin`
  - Propiedades públicas `$email` y `$password` para acceso desde comandos
  - Mensajes informativos y advertencias de seguridad
  - Instrucciones para usar "olvidé mi contraseña" en el primer acceso

**Archivos creados/modificados**:
- `database/seeders/NewsTagSeeder.php` - Actualizado con etiquetas básicas
- `database/seeders/ProductionAdminUserSeeder.php` - Creado nuevo seeder

---

## Prompt 6: Inicio de Fase 2

**Usuario**:
> Sigamos con la Fase 2

**Resumen de la Respuesta**:
- Implementación completa de la Fase 2: Comando `setup:developer`
- **Funcionalidades implementadas**:
  - Ejecución de `migrate:fresh` (elimina y recrea todas las tablas)
  - Ejecución de todos los seeders (incluyendo datos de prueba completos)
  - Limpieza de cachés (config, route, view, application, permissions, events)
  - Creación del enlace de almacenamiento (`storage:link`)
  - Muestra credenciales de prueba para todos los roles
  - Opción `--force` para ejecutar sin confirmación
  - Opción `--no-cache` para omitir la limpieza de cachés
- **Salida del comando**:
  - Mensajes informativos en cada paso
  - Tabla con credenciales de prueba al finalizar
  - Formato claro y legible

**Archivos creados**:
- `app/Console/Commands/SetupDeveloper.php` - Comando completo para desarrollo

---

## Prompt 7: Inicio de Fase 3

**Usuario**:
> Sí, sigamos con la Fase 3

**Resumen de la Respuesta**:
- Implementación completa de la Fase 3: Comando `setup:production`
- **Validaciones de entorno implementadas**:
  - **Errores críticos (bloquean)**:
    - Verificación de conexión a base de datos
    - Verificación de existencia del archivo `.env`
    - Verificación de permisos de escritura en `storage/` y `bootstrap/cache/`
  - **Advertencias (no bloquean)**:
    - Verificación de `APP_ENV` (advierte si no es 'production')
    - Verificación de `APP_DEBUG` (advierte si es 'true')
  - Opción `--force` para saltar solo advertencias
- **Funcionalidades implementadas**:
  - Doble confirmación para `migrate:fresh` (advertencia inicial + confirmación específica)
  - Ejecución de solo seeders esenciales (8 seeders)
  - Optimización de cachés (limpieza + regeneración optimizada)
  - Creación del enlace de almacenamiento
  - Ejecución de `ProductionAdminUserSeeder` con solicitud interactiva de email
  - Opción `--admin-email` para especificar email directamente
  - Verificaciones post-setup (usuario super-admin, roles, idiomas)
  - Muestra credenciales del super-administrador al finalizar

**Archivos creados**:
- `app/Console/Commands/SetupProduction.php` - Comando completo para producción

---

## Prompt 8: Inicio de Fase 4

**Usuario**:
> Adelante con la Fase 4

**Resumen de la Respuesta**:
- La Fase 4 (Seeders de Producción) ya estaba completada en la Fase 1
- Verificación de que `NewsTagSeeder` y `ProductionAdminUserSeeder` están correctamente implementados
- Confirmación de que ambos seeders cumplen con los requisitos establecidos

**Estado**: Fase 4 ya completada en Fase 1

---

## Prompt 9: Ejecutar Tests

**Usuario**:
> Ejecuta todos los tests que acabas de crear por si hay que solucionar errores

**Resumen de la Respuesta**:
- Creación de tests completos para todos los componentes
- **Tests creados**:
  - `tests/Feature/Commands/SetupDeveloperTest.php` - 10 tests
  - `tests/Feature/Commands/SetupProductionTest.php` - 16 tests
  - `tests/Feature/Seeders/ProductionAdminUserSeederTest.php` - 8 tests
- **Problemas identificados y corregidos**:
  1. **Error**: `BadMethodCallException: Method Illuminate\Filesystem\Filesystem::isLink does not exist`
     - **Causa**: Uso incorrecto de `File::isLink()`
     - **Solución**: Reemplazado por función nativa `is_link()`
  2. **Error**: `SQLSTATE[42000]: Syntax error or access violation: 1170 BLOB/TEXT column 'description' used in key specification without a key length`
     - **Causa**: Índice en columna TEXT de MySQL sin especificar longitud
     - **Solución**: Modificada migración para usar `DB::statement()` con longitud especificada para MySQL
  3. **Error**: `Attempted to lazy load [program] on model [App\Models\Call]`
     - **Causa**: `ErasmusEventSeeder` accediendo a relación sin eager loading
     - **Solución**: Añadido `Call::with('program')->get()` en el seeder
  4. **Error**: `MassAssignmentException: Add fillable property [email_verified_at]`
     - **Causa**: `ProductionAdminUserSeeder` intentando asignar `email_verified_at` sin estar en `$fillable`
     - **Solución**: Añadido `'email_verified_at'` al array `$fillable` en `app/Models/User.php`
  5. **Error**: Tests fallando con código de salida 1
     - **Causa**: SQLite `:memory:` desaparece cuando `Artisan::call()` abre nuevas conexiones
     - **Solución inicial**: Configuración de SQLite persistente en `tests/TestCase.php` (luego revertida)

**Archivos creados/modificados**:
- `tests/Feature/Commands/SetupDeveloperTest.php` - Tests completos
- `tests/Feature/Commands/SetupProductionTest.php` - Tests completos
- `tests/Feature/Seeders/ProductionAdminUserSeederTest.php` - Tests completos
- `database/migrations/2026_01_13_160601_add_indexes_to_activity_log_table.php` - Corregida para MySQL
- `database/seeders/ErasmusEventSeeder.php` - Añadido eager loading
- `app/Models/User.php` - Añadido `email_verified_at` a `$fillable`
- `app/Console/Commands/SetupDeveloper.php` - Corregido uso de `is_link()`
- `app/Console/Commands/SetupProduction.php` - Corregido uso de `is_link()`

---

## Prompt 10: Investigación de Problemas de Tests

**Usuario**:
> Vamos a investigar más a fondo el problema de los tests. He investigado el problema por mi cuenta y he visto esto: Tu comando falla dentro de migrate:fresh, justo después de "Dropping all tables … FAIL". Eso significa que Artisan::call('migrate:fresh') está devolviendo 1 porque el comando interno está fallando. La causa más común en tests, especialmente en tu caso, es esta: ✅ Estás usando SQLite :memory: y al llamar subcomandos con Artisan::call se pierde la conexión/BD Con :memory: la base de datos existe solo mientras dura esa conexión. migrate:fresh hace varias cosas (drop, migrate) y en el proceso Laravel puede abrir otra conexión → "desaparece" la BD → el drop/migrate falla. Solución: en tests usa SQLite en archivo (persistente), no :memory:. He obtenido pero no he implementado todavía esta posible solución: 1) Arreglo rápido y estable: SQLite en archivo para testing A) Crea database/testing.sqlite touch database/testing.sqlite B) En .env.testing (o phpunit.xml) pon: DB_CONNECTION=sqlite DB_DATABASE=database/testing.sqlite Si lo pones en .env.testing, asegúrate de que realmente se carga en tu suite. 2) (Recomendado) Asegúralo desde PEST para que no dependa del entorno En tests/Pest.php: use Illuminate\Support\Facades\File; beforeEach(function () { $dbPath = database_path('testing.sqlite'); if (! File::exists($dbPath)) { File::put($dbPath, ''); } config()->set('database.default', 'sqlite'); config()->set('database.connections.sqlite.database', $dbPath); }); Con esto te garantizas que el comando siempre usa la BD persistente en tests. Pero me surgía la duda de si al implementar esta solución podría haber problemas con la ejecución de los tests con el flag --parallel y cuando lo he preguntado he obtenido esto: 1) Arreglo rápido y estable: SQLite en archivo para testing A) Crea database/testing.sqlite touch database/testing.sqlite B) En .env.testing (o phpunit.xml) pon: DB_CONNECTION=sqlite DB_DATABASE=database/testing.sqlite Si lo pones en .env.testing, asegúrate de que realmente se carga en tu suite. 2) (Recomendado) Asegúralo desde PEST para que no dependa del entorno En tests/Pest.php: use Illuminate\Support\Facades\File; beforeEach(function () { $dbPath = database_path('testing.sqlite'); if (! File::exists($dbPath)) { File::put($dbPath, ''); } config()->set('database.default', 'sqlite'); config()->set('database.connections.sqlite.database', $dbPath); }); Con esto te garantizas que el comando siempre usa la BD persistente en tests. Investiga todo esto a ver si es la solución correcta a nuestro problema.

**Resumen de la Respuesta**:
- Investigación del problema de SQLite `:memory:` en tests
- **Problema identificado**: SQLite `:memory:` desaparece cuando `Artisan::call()` abre nuevas conexiones en subcomandos (como `migrate:fresh`)
- **Solución implementada**:
  - Configuración de SQLite persistente en `tests/TestCase.php::setUp()`
  - Uso de archivo `testing-{TEST_TOKEN}.sqlite` para soportar ejecución paralela
  - Configuración antes de `parent::setUp()` para que se aplique correctamente
- **Archivos modificados**:
  - `tests/TestCase.php` - Añadida configuración de SQLite persistente
  - `phpunit.xml` - Actualizado para usar archivo SQLite en lugar de `:memory:`

**Resultado**: Tests de comandos ahora pasan correctamente

---

## Prompt 11: Problemas con Tests en Paralelo

**Usuario**:
> Acabo de ejecutar todos los tests de la aplicación en paralelo y están fallando prácticamente todos. Algo en la configuración que hemos tocado no está bien hecho. Puedes revisarlo?

**Resumen de la Respuesta**:
- Identificación del problema: La configuración de SQLite persistente en `TestCase.php` afecta a TODOS los tests, no solo a los de comandos
- **Problema**: Los tests existentes (que funcionaban con `:memory:`) ahora fallan porque esperan una base de datos limpia en cada test
- **Solución implementada**:
  - Revertida la configuración global en `TestCase.php`
  - Restaurado `phpunit.xml` a usar `:memory:`
  - Configuración específica solo para tests de comandos usando `beforeEach()` en cada archivo de test
  - Uso de archivos SQLite específicos por suite de tests

**Archivos modificados**:
- `tests/TestCase.php` - Revertido a configuración original
- `phpunit.xml` - Revertido a `:memory:`
- `tests/Feature/Commands/SetupDeveloperTest.php` - Configuración específica en `beforeEach()`
- `tests/Feature/Commands/SetupProductionTest.php` - Configuración específica en `beforeEach()`
- `tests/Feature/Seeders/ProductionAdminUserSeederTest.php` - Configuración específica en `beforeEach()`

**Resultado**: Tests existentes vuelven a funcionar, tests de comandos usan SQLite persistente solo cuando es necesario

---

## Prompt 12: Propuesta de Solución Mejorada

**Usuario**:
> He visto a lo largo de los dos últimos prompts que se ha conseguido que los tests de comandos pasen, pero eso ha roto a los demás. Te voy a plantear un escenario y mira a ver si es posible o incluso mejorarlo: Dejamos todos los tests que ya teníamos antes (excluimos los tests de comandos desarrollados en este chat) con su configuración original y base de datos de SQLite en memoria, que sabíamos que eso funcionaba perfectamente, tanto en ejecución en paralelo como no. Para los tests que hemos desarrollado en este chat y que requieren una configuración especial vamos a tratar de crearla. Por ejemplo podemos añadirles el siguiente código para que no se ejecuten en paralelo: if (ParallelTesting::running()) { $this->markTestSkipped('No se ejecuta en modo paralelo'); } o este otro: if (ParallelTesting::token()) { $this->markTestSkipped('Test no compatible con ejecución paralela'); } Así conseguimos excluirlos del resto de tests de la suite. Pero si los queremos ejecutar todos (sin el flag --parallel) tambien tenemos que indicarles que ellos deben usar una base de datos SQLite en fichero, a diferencia del resto. Y para eso podemos definir dos helpers en tests/Pest.php de esta forma: function useSqliteInMemory(): void { config()->set('database.default', 'sqlite'); config()->set('database.connections.sqlite.database', ':memory:'); } function useSqliteFile(string $filename = 'testing_command.sqlite'): void { $dbPath = database_path($filename); if (! File::exists($dbPath)) { File::put($dbPath, ''); } config()->set('database.default', 'sqlite'); config()->set('database.connections.sqlite.database', $dbPath); } Y utilizarlos según corresponda en los métodos beforeEach() de cada fichero de tests. Antes de comenzar a implementar esta idea o algo similar quiero que me respondas primero si es factible o no y en caso afirmativo empieza por modificar la Fase 5 de tests del archivo @docs/pasos/paso-comandos-plan.md añadiendo lo que vamos a hacer. Una vez planificado, lo reviso.

**Resumen de la Respuesta**:
- Análisis de la propuesta del usuario
- **Evaluación**: La solución propuesta es factible y mejor que la implementación anterior
- **Ventajas**:
  - No afecta a los tests existentes (siguen usando `:memory:`)
  - Tests de comandos usan SQLite en archivo solo cuando es necesario
  - Tests de comandos se excluyen de ejecución paralela
  - Helpers reutilizables para futuros tests similares
- **Plan actualizado en Fase 5**:
  - Revertir cambios en `tests/TestCase.php` (restaurar configuración original)
  - Crear helpers `useSqliteInMemory()` y `useSqliteFile()` en `tests/Pest.php`
  - Modificar tests de comandos para usar `useSqliteFile()` y skip en paralelo
  - Documentar la estrategia y el patrón a seguir

**Archivos modificados**:
- `docs/pasos/paso-comandos-plan.md` - Actualizada Fase 5 con nueva estrategia

---

## Prompt 13: Implementación de la Solución Mejorada

**Usuario**:
> Me parece adecuado el plan desarrollado. Empecemos con la implementación.

**Resumen de la Respuesta**:
- Implementación completa de la solución mejorada
- **Cambios realizados**:
  1. **Revertido `tests/TestCase.php`**: Restaurado a configuración original (sin SQLite persistente)
  2. **Revertido `phpunit.xml`**: Restaurado a usar `:memory:` por defecto
  3. **Creados helpers en `tests/Pest.php`**:
     - `useSqliteInMemory()`: Restaura configuración por defecto
     - `useSqliteFile(string $filename)`: Configura SQLite en archivo persistente con `DB::purge()` y `DB::reconnect()`
  4. **Modificados tests de comandos**:
     - `SetupDeveloperTest.php`: Usa `useSqliteFile('testing_setup_developer.sqlite')` y skip en paralelo
     - `SetupProductionTest.php`: Usa `useSqliteFile('testing_setup_production.sqlite')` y skip en paralelo
     - `ProductionAdminUserSeederTest.php`: Usa `useSqliteFile('testing_production_admin_user.sqlite')` y skip en paralelo
  5. **Lógica de skip en paralelo**: Usa `getenv('TEST_TOKEN') !== false || isset($_SERVER['TEST_TOKEN'])` para detectar modo paralelo
  6. **Limpieza de archivos SQLite**: Añadida en `beforeEach()` y `afterEach()` para evitar conflictos

**Archivos modificados**:
- `tests/TestCase.php` - Revertido a configuración original
- `phpunit.xml` - Revertido a `:memory:`
- `tests/Pest.php` - Añadidos helpers `useSqliteInMemory()` y `useSqliteFile()`
- `tests/Feature/Commands/SetupDeveloperTest.php` - Configuración específica con skip en paralelo
- `tests/Feature/Commands/SetupProductionTest.php` - Configuración específica con skip en paralelo
- `tests/Feature/Seeders/ProductionAdminUserSeederTest.php` - Configuración específica con skip en paralelo

**Resultado**: 
- ✅ Tests existentes funcionan correctamente (usan `:memory:`)
- ✅ Tests de comandos pasan individualmente (usan SQLite en archivo)
- ✅ Tests de comandos se saltan en modo paralelo (31 skipped)
- ⚠️ Algunos tests de comandos fallan cuando se ejecutan juntos en suite completa (problema conocido con `RefreshDatabase` y VACUUM en SQLite)

---

## Prompt 14: Aceptar Estado Actual

**Usuario**:
> Vale, vamos a dejarlo aquí de momento. Aceptemos el estado actual y continuemos.

**Resumen de la Respuesta**:
- Aceptación del estado actual de los tests
- Documentación del problema conocido en `paso-comandos-plan.md`
- Recomendación: Ejecutar tests de comandos individualmente o en grupos pequeños
- Continuación con Fase 6 (Documentación)

**Archivos modificados**:
- `docs/pasos/paso-comandos-plan.md` - Documentado estado actual y limitación conocida

---

## Prompt 15: Inicio de Fase 6

**Usuario**:
> Pasemos a la Fase 6

**Resumen de la Respuesta**:
- Implementación completa de la Fase 6: Documentación
- **README.md actualizado**:
  - Sección "Ejecutar Migraciones y Seeders" actualizada con comandos `setup:developer` y `setup:production`
  - Sección "Credenciales de Prueba" actualizada con nota sobre desarrollo vs producción
  - Sección "Comandos Útiles" actualizada con subsección "Setup Inicial"
  - Sección "Crear Usuario Administrador" actualizada con referencia a `setup:production`
- **Guía de Administrador actualizada**:
  - Nueva Sección 15: "Configuración Inicial del Sistema"
  - Documentación completa del comando `setup:production` con todas sus opciones
  - Documentación del comando `setup:developer` (solo para desarrollo)
  - Instrucciones para cambiar contraseña del super-admin
  - Lista de seeders ejecutados en producción
  - Próximos pasos después del setup
  - Secciones renumeradas correctamente (15, 16, 17)

**Archivos modificados**:
- `README.md` - Actualizado con documentación de comandos
- `docs/guia-usuario/guia-administrador.md` - Añadida sección de configuración inicial

---

## Prompt 16: Inicio de Fase 7

**Usuario**:
> Adelante con la Fase 7

**Resumen de la Respuesta**:
- Generación del archivo `paso51.md` con todos los prompts y resúmenes del chat
- Documentación completa de todo el proceso de desarrollo
- Inclusión de todos los prompts del usuario y resúmenes de las respuestas
- Documentación de decisiones tomadas y problemas encontrados

**Archivo creado**:
- `docs/pasos/paso51.md` - Este archivo

---

## Decisiones Finales Tomadas

### 1. NewsTagSeeder
✅ **Decisión**: Crear etiquetas básicas comunes para el sitio web
- Etiquetas implementadas: "Noticias", "Eventos", "Convocatorias", "Erasmus+", "Movilidad", "Formación"
- Disponibles tanto en desarrollo como en producción

### 2. ProductionAdminUserSeeder
✅ **Decisión**: Crear seeder separado `ProductionAdminUserSeeder`
- Solo crea un usuario super-admin
- Solicita email por terminal (interactivo) o mediante opción `--admin-email`
- Genera contraseña aleatoria segura automáticamente
- El super-admin será responsable de crear el resto de usuarios

### 3. Migraciones en Producción
✅ **Decisión**: Permitir `migrate:fresh` con doble confirmación
- Se permite `migrate:fresh` pero con doble confirmación obligatoria
- Advertencia inicial sobre eliminación de datos
- Confirmación específica antes de ejecutar `migrate:fresh`

### 4. Contraseña Super-Admin
✅ **Decisión**: Generar aleatoria automáticamente
- Contraseña aleatoria segura (mínimo 16 caracteres)
- Incluye mayúsculas, minúsculas, números y símbolos
- Se muestra solo una vez al finalizar el setup
- El usuario usará "olvidé mi contraseña" en el primer acceso para establecer una nueva

### 5. Validaciones de Entorno
✅ **Decisión**: Validaciones con bloqueo en errores críticos
- **Errores Críticos (BLOQUEAN)**:
  - Conexión a base de datos → Bloquea si falla
  - Archivo `.env` → Bloquea si no existe
  - Permisos de escritura → Bloquea si no hay permisos
- **Advertencias (NO BLOQUEAN)**:
  - `APP_ENV` no en 'production' → Advierte pero permite continuar
  - `APP_DEBUG` en 'true' → Advierte pero permite continuar
- Opción `--force` solo salta advertencias, no errores críticos

---

## Problemas Encontrados y Solucionados

### 1. SQLite :memory: en Tests
**Problema**: SQLite `:memory:` desaparece cuando `Artisan::call()` abre nuevas conexiones en subcomandos.

**Solución**: 
- Helpers `useSqliteInMemory()` y `useSqliteFile()` en `tests/Pest.php`
- Tests de comandos usan SQLite en archivo persistente
- Tests de comandos se saltan en modo paralelo
- Limpieza de archivos SQLite en `beforeEach()` y `afterEach()`

### 2. Migración de Índices en MySQL
**Problema**: MySQL requiere especificar longitud de clave para índices en columnas TEXT.

**Solución**: Uso de `DB::statement()` con longitud especificada para MySQL, con lógica condicional para SQLite.

### 3. Lazy Loading en Seeders
**Problema**: `ErasmusEventSeeder` accediendo a relación sin eager loading.

**Solución**: Añadido `Call::with('program')->get()` para cargar relación antes de acceder.

### 4. Mass Assignment en User
**Problema**: `email_verified_at` no estaba en `$fillable` del modelo User.

**Solución**: Añadido `'email_verified_at'` al array `$fillable`.

### 5. Método isLink() No Existente
**Problema**: `File::isLink()` no existe en Laravel.

**Solución**: Reemplazado por función nativa `is_link()`.

---

## Estado Final del Proyecto

### Comandos Implementados

✅ **`setup:developer`**:
- Ejecuta migraciones fresh
- Ejecuta todos los seeders (datos de prueba completos)
- Limpia cachés
- Crea storage link
- Muestra credenciales de prueba
- Opciones: `--force`, `--no-cache`

✅ **`setup:production`**:
- Validaciones de entorno (errores críticos y advertencias)
- Doble confirmación para `migrate:fresh`
- Ejecuta solo seeders esenciales (8 seeders)
- Optimiza cachés
- Crea storage link
- Crea usuario super-administrador
- Verificaciones post-setup
- Opciones: `--force`, `--admin-email`

### Seeders Modificados/Creados

✅ **NewsTagSeeder**: Actualizado con 6 etiquetas básicas comunes

✅ **ProductionAdminUserSeeder**: Creado nuevo seeder para producción

### Tests Implementados

✅ **SetupDeveloperTest**: 10 tests completos

✅ **SetupProductionTest**: 16 tests completos

✅ **ProductionAdminUserSeederTest**: 8 tests completos

⚠️ **Limitación conocida**: Algunos tests de comandos fallan cuando se ejecutan juntos en suite completa debido a conflictos con `RefreshDatabase` y VACUUM en SQLite. Se recomienda ejecutarlos individualmente.

### Documentación Actualizada

✅ **README.md**: Actualizado con sección de Setup Inicial

✅ **Guía de Administrador**: Añadida sección "Configuración Inicial del Sistema"

---

## Referencias

- **Plan Detallado**: [paso-comandos-plan.md](./paso-comandos-plan.md)
- **Planificación General**: [planificacion_pasos.md](../planificacion_pasos.md)
- **Guía de Administrador**: [guia-administrador.md](../guia-usuario/guia-administrador.md)

---

**Fecha de Creación**: Enero 2026  
**Última Actualización**: Enero 2026  
**Estado**: ✅ Completado
