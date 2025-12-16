# Paso 1: Diseño de Base de Datos y Cobertura Completa de Tests

Este documento contiene todos los prompts utilizados en el desarrollo inicial de la aplicación "Erasmus+ Centro (Murcia)" y los resultados obtenidos en cada paso. Este guión puede servir como referencia para el desarrollo de futuras aplicaciones web con Laravel.

## Prompt 1: Diseño Inicial de Base de Datos

**Prompt:**
> "Quiero que me propongas, sin generar código todavía, un diseño de base de datos para la aplicación web que te propongo a continuación. Si el texto fuera demasiado largo para que puedas procesarlo, avísame y te adjunto un PDF."

**Resultado:**
- Se propuso un diseño completo de base de datos en 3NF para la aplicación Erasmus+ Centro (Murcia)
- Incluía tablas para programas, años académicos, convocatorias, noticias, documentos, multimedia, auditoría, notificaciones, configuración e internacionalización
- El diseño contemplaba relaciones 1:N y N:M apropiadas

---

## Prompt 2: Revisión del Diseño con Librerías Externas

**Prompt:**
> "Para el manejo de Roles y permisos voy a usar la librería de Laravel-Permission cuya documentación puedes encontrar aquí: https://spatie.be/docs/laravel-permission/v6/introduction Por tanto no necesito tablas para los roles y los permisos pues serán incluidas con la librería. También, para el manejo de los archivos multimedia, usaré la librería de Laravel media library cuya documentación puedes encontrar aquí: https://spatie.be/docs/laravel-medialibrary/v11/introduction y también incorpora las tablas necesarias. Revisa la base de datos propuesta para eliminar las tablas que serán gestionadas a través de ambas librerías, y vuelve a hacerme la propuesta."

**Resultado:**
- Se eliminaron las tablas `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `model_has_permissions` (gestionadas por Laravel Permission)
- Se eliminó la tabla `media` (gestionada por Laravel Media Library)
- Se mantuvo `media_consents` con referencia a `media_id` para consentimientos de uso de multimedia
- El diseño quedó optimizado para trabajar con las librerías Spatie

---

## Prompt 3: Generación de Migraciones, Modelos y Factories

**Prompt:**
> "No es necesario que profundices. Me parece un desarrollo correcto. Así que empieza generando las migraciones para las tablas propuestas. También vamos a generar los modelos asociados a las tablas y los factories. Si es mucho de una sola vez, plantea un plan de varios pasos para ir poco a poco. Más adelante instalaremos las librerías indicadas, pero ahora todavía no lo haremos."

**Resultado:**
- Se generaron todas las migraciones para las tablas propuestas
- Se crearon todos los modelos Eloquent con relaciones bien definidas
- Se generaron factories para todos los modelos con estados y relaciones apropiadas
- Se utilizó `php artisan make:` para crear los archivos siguiendo las convenciones de Laravel
- Se implementaron relaciones `belongsTo`, `hasMany`, `belongsToMany` y `morphTo` según correspondía

---

## Prompt 4: Instalación y Verificación de Laravel Permission

**Prompt:**
> "Acabo de instalar la librería de Laravel-Permission siguiendo las instrucciones de su web https://spatie.be/docs/laravel-permission/v6/installation-laravel pero me he quedado en el paso 6. Me puedes verificar ese paso por si tengo que modificar algo."

**Resultado:**
- Se verificó el paso 6 de la instalación de Laravel Permission
- Se añadió el trait `HasRoles` al modelo `User`
- Se configuró correctamente el archivo `config/permission.php`
- Se preparó la migración para ejecutarse después de la instalación

---

## Prompt 5: Ajuste para MySQL 8+

**Prompt:**
> "Si consultas el archivo .env, estoy usando MySQL donde la base de datos se llama igual que el proyecto"

**Resultado:**
- Se identificó el uso de MySQL 8.0.33
- Se ajustó la migración de Laravel Permission para evitar errores de "Specified key was too long"
- Se estableció explícitamente la longitud de columnas `name` y `guard_name` a 191 caracteres en las tablas `permissions` y `roles`

---

## Prompt 6: Corrección de Foreign Keys Nullable

**Prompt:**
> "Al lanzar las migraciones obtengo error en la tabla calls: SQLSTATE[HY000]: General error: 1830 Column 'created_by' cannot be NOT NULL: needed in a foreign key constraint 'calls_created_by_foreign' SET NULL"

**Resultado:**
- Se identificó que las columnas con `nullOnDelete()` deben ser explícitamente `nullable()`
- Se corrigieron todas las migraciones que tenían `foreignId()->constrained()->nullOnDelete()` sin `nullable()`
- Se aplicó la corrección a las tablas: `calls`, `documents`, `news_posts`, `resolutions`, `erasmus_events`
- Todas las foreign keys a `users` ahora usan `nullOnDelete()` para mantener el historial

---

## Prompt 7: Corrección de Orden de Migraciones

**Prompt:**
> "Y ahora el error es: 2025_12_12_193821_create_news_post_tag_table ...................................................... 3.86ms FAIL Illuminate\Database\QueryException SQLSTATE[HY000]: General error: 1824 Failed to open the referenced table 'news_posts'"

**Resultado:**
- Se identificó que el orden alfabético de migraciones con timestamps idénticos causaba dependencias incorrectas
- Se renombró la migración `news_post_tag` para que ejecute después de `news_posts`
- Se ajustó el timestamp de `193821` a `193822` para garantizar el orden correcto

---

## Prompt 8: Instalación de Laravel Media Library

**Prompt:**
> "Acabo de instalar la librería de Laravel Media Library. Revisa si hay que hacer cambios y lanza las migraciones con migrate:fresh. Verifica si hay errores y corrígelos"

**Resultado:**
- Se ejecutó `migrate:fresh` para aplicar todas las migraciones desde cero
- Se identificó que `media_consents` intentaba crear una foreign key a `media` antes de que existiera
- Se modificó la migración de `media_consents` para crear `media_id` sin foreign key inicialmente
- Se creó una nueva migración que añade la foreign key condicionalmente solo si la tabla `media` existe
- Se ajustó el timestamp de la nueva migración para ejecutarse después de la migración de Media Library

---

## Prompt 9: Generación de Documentación de Migraciones

**Prompt:**
> "Vamos a generar la documentación de la aplicación para que esté disponible para la IA en futuros chats. Crea en la raiz del proyecto una carpeta llamada docs que contendrá archivos en formato markdown. Habrá uno que actuará de índice con respecto a los demás. Empieza generando la estructura y la documentación sobre las migraciones. Si el archivo de documentación va a superar las 2000 líneas, genéralo en varios archivos"

**Resultado:**
- Se creó la carpeta `docs/` con estructura organizada
- Se generó `docs/README.md` como índice principal
- Se crearon múltiples archivos de documentación de migraciones:
  - `docs/migrations-overview.md` - Resumen general
  - `docs/migrations-structure.md` - Estructura base (programas y años académicos)
  - `docs/migrations-calls.md` - Sistema de convocatorias
  - `docs/migrations-content.md` - Contenido (noticias, documentos, multimedia)
  - `docs/migrations-system.md` - Sistema (auditoría, notificaciones, configuración, i18n)
- Cada archivo contiene descripciones detalladas de tablas, columnas, relaciones e índices

---

## Prompt 10: Desarrollo de Tests de Relaciones de Modelos

**Prompt:**
> "Ahora, en la carpeta 'docs' actualiza la documentación existente ampliando archivos o añadiendo nuevos y deja por escrito lo que acabamos de hacer."

**Resultado:**
- Se creó un plan de trabajo en `docs/models-testing-plan.md`
- Se implementaron tests de relaciones para todos los modelos en `tests/Feature/Models/`
- Se crearon 113 tests con 209 assertions cubriendo:
  - Relaciones `belongsTo`, `hasMany`, `belongsToMany`, `morphTo`
  - Comportamientos `cascadeOnDelete` y `nullOnDelete`
  - Polimorfismo en `AuditLog` y `Translation`
- Se documentó todo en `docs/models-tests.md` y `docs/testing-summary.md`

---

## Prompt 11: Corrección de Tests para Ejecución Conjunta

**Prompt:**
> "No puede ocurrir que al lanzar todos los tests haya algunos que fallen. Utiliza DatabaseTransaction para aislar un test del resto aunque se lancen todos juntos. Si persisten los errores, corrígelos."

**Resultado:**
- Se identificaron errores de `UniqueConstraintViolationException` por datos duplicados
- Se corrigieron factories para generar valores únicos en cada test (slugs, años académicos)
- Se añadió `->fresh()` después de operaciones `attach()` en relaciones `belongsToMany`
- Se corrigieron definiciones explícitas de relaciones con tablas pivot y foreign keys
- Se ajustó el manejo de `media_id` en `MediaConsentTest` para evitar fallos de FK
- Todos los tests pasan correctamente al ejecutarse juntos

---

## Prompt 12: Mejora de Cobertura del Modelo Setting

**Prompt:**
> "En la carpeta 'tests/coverage' existe un archivo @index.html que contiene la cobertura de código generada por la librería Pest. He detectado que en el modelo Setting solo están cubiertas el 30% de las líneas de código. ¿Puedes revisar la cobertura de dicho modelo y añadir los tests necesarios para llegar al 100%?"

**Resultado:**
- Se identificó que faltaban tests para los accessors y mutators del modelo `Setting`
- Se añadieron 10 nuevos tests para `getValueAttribute()` y `setValueAttribute()`
- Se cubrieron todos los tipos: `integer`, `boolean`, `json`, `string` y el caso `default`
- Se corrigió el uso de tipos válidos del `enum` en la base de datos
- Se alcanzó 100% de cobertura (13/13 líneas)
- Se documentó en `docs/setting-coverage-improvement.md`

---

## Prompt 13: Mejora de Cobertura de Todos los Modelos

**Prompt:**
> "Perfecto, ya tenemos cobertura del 100% en ese modelo. ¿Me puedes revisar la cobertura del resto de modelos y añadir los tests necesarios para llegar al 100% en cada uno de ellos?"

**Resultado:**
- Se identificaron modelos con menos del 100%: Call (95%), Document (93%), DocumentCategory (89%), NewsPost (93%), NewsTag (83%), Program (91%), User (44%)
- Se añadieron tests para generación automática de slugs en eventos `creating`:
  - Call, Program, NewsTag, DocumentCategory, Document, NewsPost
- Se añadieron 5 tests para el método `initials()` del modelo `User`
- Se alcanzó 100% de cobertura en todos los modelos:
  - **Líneas**: 165/165 (100%)
  - **Métodos**: 66/66 (100%)
  - **Clases**: 19/19 (100%)
- Total: 134 tests, 245 assertions

---

## Prompt 14: Mejora de Cobertura de Livewire

**Prompt:**
> "Para terminar con la cobertura, en tests/coverage/@index.html puedes ver que la sección de Livewire solo está cubierta al 46%. Aunque este código fue generado de forma automática al crear la aplicación con Livewire, me gustaría cubrir con tests todas sus líneas. ¿Puedes generar tests (ubicalos donde mejor consideres) para cubrir todas esas líneas?"

**Resultado:**
- Se identificaron componentes con baja cobertura:
  - Profile.php: 76% (faltaba `resendVerificationNotification()`)
  - TwoFactor.php: 21.43% (faltaban 8 métodos)
  - RecoveryCodes.php: 0% (sin cobertura)
- Se añadieron 20 nuevos tests:
  - 2 tests para `Profile::resendVerificationNotification()`
  - 13 tests para `TwoFactor` (enable, confirm, disable, closeModal, etc.)
  - 5 tests para `RecoveryCodes` (mount, regenerate, loadRecoveryCodes)
- Se resolvieron desafíos:
  - Propiedades `#[Locked]` en Livewire
  - Generación de códigos TOTP válidos
  - Manejo de excepciones en desencriptación
- Se alcanzó 100% de cobertura en Livewire:
  - **Líneas**: 111/111 (100%)
  - **Métodos**: 18/18 (100%)
  - **Clases**: 6/6 (100%)
- Total: 30 tests para Livewire, 77 assertions
- Se documentó en `docs/livewire-coverage-100.md`

---

## Resumen Final del Paso 1

### Estadísticas Totales

- **Tests Implementados**: 164 tests
- **Assertions**: 322 assertions
- **Cobertura de Modelos**: 100% (165/165 líneas, 66/66 métodos, 19/19 clases)
- **Cobertura de Livewire**: 100% (111/111 líneas, 18/18 métodos, 6/6 clases)
- **Migraciones Creadas**: 20+ migraciones
- **Modelos Creados**: 19 modelos Eloquent
- **Factories Creadas**: 19 factories
- **Documentación Generada**: 10+ archivos Markdown

### Estructura de Archivos Creados

```
docs/
├── README.md
├── migrations-overview.md
├── migrations-structure.md
├── migrations-calls.md
├── migrations-content.md
├── migrations-system.md
├── models-testing-plan.md
├── models-tests.md
├── testing-summary.md
├── models-coverage-100.md
├── setting-coverage-improvement.md
├── livewire-coverage-100.md
└── pasos/
    └── paso1.md (este archivo)

tests/Feature/
├── Models/ (18 archivos de test)
└── Settings/ (4 archivos de test)
```

### Lecciones Aprendidas

1. **Diseño de Base de Datos**: Siempre considerar librerías externas antes de diseñar tablas propias
2. **Migraciones**: Verificar orden de ejecución cuando hay dependencias entre tablas
3. **Foreign Keys**: Las columnas con `nullOnDelete()` deben ser explícitamente `nullable()`
4. **MySQL 8+**: Ajustar longitudes de columnas en índices únicos para evitar errores de clave demasiado larga
5. **Tests de Relaciones**: Usar `->fresh()` después de operaciones en relaciones `belongsToMany`
6. **Unicidad en Tests**: Asegurar valores únicos en factories para evitar conflictos al ejecutar tests juntos
7. **Cobertura de Código**: Revisar reportes de cobertura regularmente y añadir tests para código no cubierto
8. **Livewire Tests**: Respetar propiedades `#[Locked]` y usar métodos públicos para modificar estado
9. **Documentación**: Mantener documentación actualizada en cada paso del desarrollo

### Comandos Útiles

```bash
# Ejecutar todas las migraciones
php artisan migrate:fresh

# Ejecutar todos los tests
php artisan test

# Ejecutar tests con cobertura
php artisan test --coverage-html=tests/coverage

# Ejecutar tests de modelos
php artisan test tests/Feature/Models/

# Ejecutar tests de Livewire
php artisan test tests/Feature/Settings/

# Formatear código
vendor/bin/pint --dirty
```

---

**Fecha de Finalización**: Diciembre 2025  
**Estado**: ✅ Completado - 100% de cobertura alcanzado en modelos y Livewire

