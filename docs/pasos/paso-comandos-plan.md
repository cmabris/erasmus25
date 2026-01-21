# Plan de Trabajo - Comandos de Setup: Developer y Production

## Objetivo

Crear dos comandos Artisan para preparar la aplicación en diferentes entornos:
- `php artisan setup:developer` - Preparar la aplicación para desarrollo (migraciones, seeders completos, limpieza de cachés)
- `php artisan setup:production` - Preparar la aplicación para producción (migraciones, solo seeders esenciales)

---

## Estado Actual

### Seeders Existentes

La aplicación cuenta con 17 seeders organizados en el `DatabaseSeeder`:

#### Seeders Esenciales para Producción (Datos Base del Sistema)

Estos seeders contienen datos necesarios para que la aplicación funcione correctamente en producción:

1. **LanguagesSeeder** ✅ ESENCIAL
   - Crea idiomas base (ES, EN)
   - Establece idioma predeterminado
   - **Razón**: Necesario para el sistema de traducciones

2. **ProgramsSeeder** ✅ ESENCIAL
   - Crea programas Erasmus+ básicos (KA121-SCH, KA121-VET, KA131-HED, etc.)
   - **Razón**: Los programas son entidades fundamentales del sistema

3. **AcademicYearsSeeder** ✅ ESENCIAL
   - Crea años académicos
   - Marca año actual
   - **Razón**: Necesario para asociar convocatorias, noticias, documentos

4. **DocumentCategoriesSeeder** ✅ ESENCIAL
   - Crea categorías básicas de documentos (Convocatorias, Modelos, Seguros, etc.)
   - **Razón**: Necesario para organizar documentos

5. **SettingsSeeder** ✅ ESENCIAL
   - Crea configuración inicial del sistema (nombre del sitio, emails, límites, etc.)
   - **Razón**: Configuración base necesaria para el funcionamiento

6. **RolesAndPermissionsSeeder** ✅ ESENCIAL
   - Crea roles (super-admin, admin, editor, viewer)
   - Crea y asigna permisos
   - **Razón**: Sistema de autorización fundamental

7. **AdminUserSeeder** ❌ SOLO DESARROLLO
   - Crea usuarios de prueba (super-admin, admin, editor, viewer)
   - **Razón**: Solo para desarrollo y testing
   - **Nota**: En producción se usará `ProductionAdminUserSeeder` separado

#### Seeders Solo para Desarrollo (Datos de Prueba)

Estos seeders generan datos de prueba para desarrollo y testing:

8. **CallSeeder** ❌ SOLO DESARROLLO
   - Genera convocatorias de prueba
   - **Razón**: Datos ficticios para probar funcionalidades

9. **CallPhaseSeeder** ❌ SOLO DESARROLLO
   - Genera fases de convocatorias de prueba
   - **Razón**: Datos ficticios para probar funcionalidades

10. **ResolutionSeeder** ❌ SOLO DESARROLLO
    - Genera resoluciones de prueba
    - **Razón**: Datos ficticios para probar funcionalidades

11. **NewsTagSeeder** ✅ ESENCIAL (con etiquetas básicas)
    - Genera etiquetas básicas comunes para el sitio web
    - **Razón**: Etiquetas básicas necesarias para categorizar noticias desde el inicio
    - **Decisión**: Crear etiquetas básicas por defecto (ej: "Noticias", "Eventos", "Convocatorias", "Erasmus+", etc.)

12. **NewsPostSeeder** ❌ SOLO DESARROLLO
    - Genera noticias de prueba
    - **Razón**: Datos ficticios para probar funcionalidades

13. **DocumentsSeeder** ❌ SOLO DESARROLLO
    - Genera documentos de prueba
    - **Razón**: Datos ficticios para probar funcionalidades

14. **ErasmusEventSeeder** ❌ SOLO DESARROLLO
    - Genera eventos de prueba
    - **Razón**: Datos ficticios para probar funcionalidades

15. **NewsletterSubscriptionSeeder** ❌ SOLO DESARROLLO
    - Genera suscripciones de prueba
    - **Razón**: Datos ficticios para probar funcionalidades

16. **DashboardDataSeeder** ❌ SOLO DESARROLLO
    - Genera datos históricos para el dashboard (últimos 6 meses)
    - **Razón**: Datos ficticios para probar visualizaciones y estadísticas

---

## Plan de Trabajo

### Fase 1: Análisis y Preparación

#### 1.1. Revisión de Seeders
- [x] Identificar seeders esenciales vs. desarrollo
- [ ] Revisar dependencias entre seeders
- [ ] Documentar orden de ejecución necesario

#### 1.2. Modificaciones Necesarias en Seeders

**ProductionAdminUserSeeder** - Crear seeder separado:
- [ ] Crear `ProductionAdminUserSeeder` separado
- [ ] Solicitar email del super-admin por terminal (interactivo)
- [ ] Generar contraseña aleatoria segura
- [ ] Mostrar contraseña generada al finalizar
- [ ] El usuario usará "olvidé mi contraseña" para establecer una nueva

**NewsTagSeeder** - Añadir etiquetas básicas:
- [x] Decisión: Crear etiquetas básicas comunes para el sitio web
- [ ] Añadir etiquetas como: "Noticias", "Eventos", "Convocatorias", "Erasmus+", "Movilidad", "Formación", etc.
- [ ] Estas etiquetas estarán disponibles tanto en desarrollo como en producción

---

### Fase 2: Creación del Comando `setup:developer`

**Archivo**: `app/Console/Commands/SetupDeveloper.php`

#### 2.1. Funcionalidades del Comando

El comando `php artisan setup:developer` debe:

1. **Confirmación de Acción Destructiva**
   - [ ] Advertir que se ejecutarán migraciones fresh (elimina datos)
   - [ ] Solicitar confirmación antes de continuar
   - [ ] Opción `--force` para saltar confirmación

2. **Ejecutar Migraciones**
   - [ ] `php artisan migrate:fresh` (elimina y recrea tablas)
   - [ ] Mostrar progreso y resultados
   - [ ] **Nota**: En desarrollo siempre se usa `migrate:fresh` para empezar limpio

3. **Ejecutar Todos los Seeders**
   - [ ] Ejecutar `DatabaseSeeder` completo
   - [ ] Mostrar progreso de cada seeder
   - [ ] Mostrar resumen de datos creados

4. **Limpiar Cachés**
   - [ ] `php artisan config:clear`
   - [ ] `php artisan cache:clear`
   - [ ] `php artisan route:clear`
   - [ ] `php artisan view:clear`
   - [ ] `php artisan permission:cache-reset` (Spatie Permission)

5. **Optimizar Aplicación (Opcional)**
   - [ ] `php artisan config:cache` (opcional, para desarrollo puede no ser necesario)
   - [ ] `php artisan route:cache` (opcional)
   - [ ] `php artisan view:cache` (opcional)

6. **Crear Storage Link**
   - [ ] `php artisan storage:link` (si no existe)

7. **Información Final**
   - [ ] Mostrar credenciales de usuarios de prueba
   - [ ] Mostrar URL de la aplicación
   - [ ] Mostrar comandos útiles para desarrollo

#### 2.2. Estructura del Comando

```php
php artisan setup:developer [--force] [--no-cache]
```

**Opciones:**
- `--force`: Ejecutar sin confirmación
- `--no-cache`: No limpiar cachés (útil para desarrollo rápido)

**Salida esperada:**
```
🚀 Preparando aplicación para desarrollo...

⚠️  ADVERTENCIA: Esto eliminará todos los datos existentes.
¿Deseas continuar? (yes/no) [no]:
> yes

📦 Ejecutando migraciones...
✅ Migraciones ejecutadas correctamente

🌱 Ejecutando seeders...
  → LanguagesSeeder... ✅
  → ProgramsSeeder... ✅
  → AcademicYearsSeeder... ✅
  ...
✅ Todos los seeders ejecutados

🧹 Limpiando cachés...
✅ Cachés limpiados

🔗 Creando enlace de storage...
✅ Enlace creado

✅ Aplicación lista para desarrollo

📋 Credenciales de prueba:
   Super Admin: super-admin@erasmus-murcia.es / password
   Admin: admin@erasmus-murcia.es / password
   Editor: editor@erasmus-murcia.es / password
   Viewer: viewer@erasmus-murcia.es / password

🌐 URL: https://erasmus25.test
```

---

### Fase 3: Creación del Comando `setup:production`

**Archivo**: `app/Console/Commands/SetupProduction.php`

#### 3.1. Funcionalidades del Comando

El comando `php artisan setup:production` debe:

1. **Validaciones de Entorno**
   - [ ] **Errores Críticos (BLOQUEAN)**:
     - [ ] Verificar conexión a base de datos → **BLOQUEAR** si falla
     - [ ] Verificar que existe archivo `.env` configurado → **BLOQUEAR** si no existe
     - [ ] Verificar permisos de escritura en `storage/` y `bootstrap/cache/` → **BLOQUEAR** si no hay permisos
   - [ ] **Advertencias (NO BLOQUEAN)**:
     - [ ] Verificar que `APP_ENV=production` → **ADVERTIR** si no está en producción
     - [ ] Verificar que `APP_DEBUG=false` → **ADVERTIR** si está en true
   - [ ] Opción `--force` para saltar solo advertencias (no errores críticos)
   - [ ] **Nota**: Los errores críticos siempre bloquean, las advertencias permiten continuar

2. **Confirmación de Acción**
   - [ ] Advertir que se ejecutarán migraciones
   - [ ] Solicitar confirmación
   - [ ] Mostrar qué seeders se ejecutarán

3. **Ejecutar Migraciones**
   - [ ] `php artisan migrate:fresh` (elimina y recrea tablas)
   - [ ] **Confirmación obligatoria**: Advertir que se eliminarán todos los datos
   - [ ] Solicitar confirmación explícita antes de ejecutar
   - [ ] Mostrar progreso
   - [ ] **Nota**: Se permite `migrate:fresh` en producción pero con doble confirmación

4. **Ejecutar Solo Seeders Esenciales**
   - [ ] `LanguagesSeeder`
   - [ ] `ProgramsSeeder`
   - [ ] `AcademicYearsSeeder`
   - [ ] `DocumentCategoriesSeeder`
   - [ ] `SettingsSeeder`
   - [ ] `RolesAndPermissionsSeeder`
   - [ ] `NewsTagSeeder` (con etiquetas básicas)
   - [ ] `ProductionAdminUserSeeder` (solicita email por terminal)
   - [ ] Mostrar progreso de cada seeder

5. **Limpiar y Optimizar Cachés**
   - [ ] `php artisan config:clear`
   - [ ] `php artisan cache:clear`
   - [ ] `php artisan route:clear`
   - [ ] `php artisan view:clear`
   - [ ] `php artisan permission:cache-reset`
   - [ ] Luego optimizar:
     - [ ] `php artisan config:cache`
     - [ ] `php artisan route:cache`
     - [ ] `php artisan view:cache`
     - [ ] `php artisan event:cache` (si aplica)

6. **Crear Storage Link**
   - [ ] `php artisan storage:link`

7. **Verificaciones Post-Setup**
   - [ ] Verificar que el usuario super-admin existe
   - [ ] Verificar que los roles existen
   - [ ] Verificar que los idiomas están configurados
   - [ ] Mostrar advertencias si algo falta

8. **Información Final**
   - [ ] Mostrar email del super-admin creado
   - [ ] Mostrar contraseña aleatoria generada (solo en esta ejecución)
   - [ ] Instrucciones: Usar "olvidé mi contraseña" en el primer acceso para establecer una nueva
   - [ ] Mostrar comandos útiles para producción

#### 3.2. Estructura del Comando

```php
php artisan setup:production [--force] [--admin-email=]
```

**Opciones:**
- `--force`: Ejecutar sin confirmación y saltar solo advertencias (no errores críticos)
- `--admin-email=`: Email para el super-admin (opcional, si no se proporciona se solicita por terminal)

**Nota**: La contraseña siempre se genera aleatoriamente. El usuario usará "olvidé mi contraseña" para establecer una nueva.

**Salida esperada:**
```
🚀 Preparando aplicación para producción...

🔍 Validando entorno...
  ✅ Conexión a base de datos... OK
  ✅ Archivo .env... OK
  ✅ Permisos de escritura... OK
  ⚠️  APP_ENV no está en 'production' (actual: local)
  ⚠️  APP_DEBUG está en 'true' (debería ser 'false' en producción)

⚠️  ADVERTENCIA: Esto ejecutará migraciones y seeders esenciales.
⚠️  ADVERTENCIA: Se detectaron problemas en la configuración del entorno.
¿Deseas continuar? (yes/no) [no]:
> yes

📦 Ejecutando migraciones...
✅ Migraciones ejecutadas correctamente

🌱 Ejecutando seeders esenciales...
  → LanguagesSeeder... ✅
  → ProgramsSeeder... ✅
  → AcademicYearsSeeder... ✅
  → DocumentCategoriesSeeder... ✅
  → SettingsSeeder... ✅
  → RolesAndPermissionsSeeder... ✅
  → ProductionAdminUserSeeder... ✅
✅ Seeders esenciales ejecutados

🧹 Limpiando y optimizando cachés...
✅ Cachés optimizados

🔗 Creando enlace de storage...
✅ Enlace creado

✅ Verificaciones post-setup...
✅ Usuario super-admin verificado
✅ Roles y permisos verificados
✅ Idiomas configurados

✅ Aplicación lista para producción

📋 Información importante:
   Super Admin: admin@erasmus-murcia.es
   🔐 Contraseña temporal: [mostrar contraseña generada]
   
⚠️  IMPORTANTE: 
   - Esta contraseña solo se mostrará una vez
   - Usa "Olvidé mi contraseña" en el primer acceso para establecer una nueva
   - No compartas esta contraseña
```

---

### Fase 4: Creación de Seeders de Producción

#### 4.1. ProductionAdminUserSeeder

**Archivo**: `database/seeders/ProductionAdminUserSeeder.php`

- [ ] Crear seeder que solo crea super-admin
- [ ] Solicitar email por terminal (interactivo) si no se proporciona como parámetro
- [ ] Generar contraseña aleatoria segura (mínimo 16 caracteres, mezcla de mayúsculas, minúsculas, números y símbolos)
- [ ] Validar formato de email
- [ ] Validar que el email no existe ya
- [ ] Mostrar email y contraseña generada al finalizar
- [ ] Instrucciones para usar "olvidé mi contraseña"

#### 4.2. Modificar DatabaseSeeder (Opcional)

- [ ] Considerar crear método `runProduction()` y `runDevelopment()`
- [ ] O mantener estructura actual y llamar seeders específicos desde comandos

---

### Fase 5: Tests

#### 5.1. Tests del Comando `setup:developer`

**Archivo**: `tests/Feature/Commands/SetupDeveloperTest.php`

- [ ] Test: Ejecuta migraciones fresh
- [ ] Test: Ejecuta todos los seeders
- [ ] Test: Limpia cachés
- [ ] Test: Crea storage link
- [ ] Test: Muestra credenciales correctas
- [ ] Test: Opción `--force` funciona
- [ ] Test: Opción `--no-cache` funciona
- [ ] Test: Confirmación cancela ejecución

#### 5.2. Tests del Comando `setup:production`

**Archivo**: `tests/Feature/Commands/SetupProductionTest.php`

- [ ] Test: Valida entorno de producción
- [ ] Test: **BLOQUEA** si no hay conexión a base de datos
- [ ] Test: **BLOQUEA** si no existe archivo .env
- [ ] Test: **BLOQUEA** si no hay permisos de escritura
- [ ] Test: **ADVIERTE** pero permite continuar si APP_ENV no es production
- [ ] Test: **ADVIERTE** pero permite continuar si APP_DEBUG es true
- [ ] Test: Opción `--force` salta solo advertencias (no errores críticos)
- [ ] Test: Ejecuta solo seeders esenciales
- [ ] Test: No ejecuta seeders de desarrollo
- [ ] Test: Crea super-admin correctamente
- [ ] Test: Optimiza cachés
- [ ] Test: Verificaciones post-setup
- [ ] Test: Opción `--admin-email` funciona
- [ ] Test: Solicita email por terminal si no se proporciona
- [ ] Test: Genera contraseña aleatoria segura
- [ ] Test: Muestra contraseña solo una vez
- [ ] Test: Confirmación cancela ejecución

#### 5.3. Tests de ProductionAdminUserSeeder

**Archivo**: `tests/Feature/Seeders/ProductionAdminUserSeederTest.php`

- [ ] Test: Crea solo super-admin
- [ ] Test: No crea otros usuarios
- [ ] Test: Solicita email por terminal si no se proporciona
- [ ] Test: Genera contraseña aleatoria segura
- [ ] Test: Valida formato de email
- [ ] Test: No duplica usuarios existentes
- [ ] Test: Muestra credenciales al finalizar

---

### Fase 6: Documentación

#### 6.1. Actualizar README.md

- [ ] Añadir sección "Setup Inicial"
- [ ] Documentar comando `setup:developer`
- [ ] Documentar comando `setup:production`
- [ ] Incluir ejemplos de uso

#### 6.2. Crear Documentación de Comandos

**Archivo**: `docs/comandos-setup.md` (opcional)

- [ ] Documentar ambos comandos
- [ ] Explicar diferencias
- [ ] Casos de uso
- [ ] Troubleshooting

#### 6.3. Actualizar Guía de Administrador

**Archivo**: `docs/guia-usuario/guia-administrador.md`

- [ ] Añadir sección "Configuración Inicial del Sistema"
- [ ] Documentar comando `setup:production` para instalación inicial
- [ ] Explicar qué seeders se ejecutan en producción
- [ ] Documentar creación del usuario super-admin inicial
- [ ] Instrucciones para cambiar contraseña del super-admin
- [ ] Añadir sección sobre comandos de mantenimiento
- [ ] Incluir información sobre `setup:developer` (solo para desarrollo)
- [ ] Añadir capturas de pantalla si es necesario (opcional)

---

### Fase 7: Generar Archivo de Documentación del Chat

#### 7.1. Crear Archivo paso51.md

**Archivo**: `docs/pasos/paso51.md`

- [ ] Crear archivo con estructura similar a otros archivos `paso*.md`
- [ ] Incluir todos los prompts del usuario en orden cronológico
- [ ] Incluir resumen de cada respuesta del asistente
- [ ] Documentar las decisiones tomadas durante la conversación
- [ ] Incluir referencias al plan creado (`paso-comandos-plan.md`)
- [ ] Formato consistente con otros archivos de pasos

#### 7.2. Contenido del Archivo

El archivo debe contener:

1. **Título y Metadatos**
   - Título del paso
   - Fecha de creación
   - Estado (completado)

2. **Resumen Ejecutivo**
   - Objetivo del paso
   - Resultado final

3. **Prompts y Respuestas**
   - Cada prompt del usuario numerado
   - Resumen de la respuesta del asistente
   - Decisiones tomadas

4. **Referencias**
   - Enlace al plan detallado (`paso-comandos-plan.md`)
   - Archivos relacionados

---

## Consideraciones Técnicas

### Orden de Ejecución de Seeders

El orden es crítico debido a las dependencias:

1. `LanguagesSeeder` (sin dependencias)
2. `ProgramsSeeder` (sin dependencias)
3. `AcademicYearsSeeder` (sin dependencias)
4. `DocumentCategoriesSeeder` (sin dependencias)
5. `SettingsSeeder` (sin dependencias)
6. `RolesAndPermissionsSeeder` (sin dependencias)
7. `NewsTagSeeder` (sin dependencias) - ✅ ESENCIAL (con etiquetas básicas)
8. `ProductionAdminUserSeeder` (requiere RolesAndPermissionsSeeder) - SOLO PRODUCCIÓN
8b. `AdminUserSeeder` (requiere RolesAndPermissionsSeeder) - SOLO DESARROLLO
9. `CallSeeder` (requiere Programs, AcademicYears, Users) - SOLO DESARROLLO
10. `CallPhaseSeeder` (requiere Calls) - SOLO DESARROLLO
11. `ResolutionSeeder` (requiere Calls, CallPhases) - SOLO DESARROLLO
12. `NewsPostSeeder` (requiere Programs, AcademicYears, Users, NewsTags) - SOLO DESARROLLO
13. `DocumentsSeeder` (requiere DocumentCategories, Programs, AcademicYears, Users) - SOLO DESARROLLO
14. `ErasmusEventSeeder` (requiere Programs, Calls, Users) - SOLO DESARROLLO
15. `NewsletterSubscriptionSeeder` (requiere Programs) - SOLO DESARROLLO
16. `DashboardDataSeeder` (requiere todo lo anterior) - SOLO DESARROLLO

### Manejo de Errores

- [ ] Capturar excepciones en cada paso
- [ ] Mostrar mensajes de error claros
- [ ] Permitir continuar o abortar según el error
- [ ] Log de errores para debugging

### Seguridad en Producción

- [ ] No mostrar contraseñas en logs
- [ ] Validar formato de email
- [ ] Validar fortaleza de contraseña (si se proporciona)
- [ ] Advertir sobre contraseñas por defecto

### Performance

- [ ] Mostrar tiempo de ejecución
- [ ] Optimizar orden de seeders para minimizar tiempo
- [ ] Considerar transacciones para rollback en caso de error

---

## Cronograma Estimado

| Fase | Descripción | Estimación |
|------|-------------|------------|
| 1 | Análisis y Preparación | 30 min |
| 2 | Comando `setup:developer` | 1-2 horas |
| 3 | Comando `setup:production` | 1-2 horas |
| 4 | Seeders de Producción | 30 min |
| 5 | Tests | 1-2 horas |
| 6 | Documentación | 45 min |
| 7 | Generar archivo paso51.md | 30 min |

**Total estimado**: 5-8 horas

---

## Entregables

1. `app/Console/Commands/SetupDeveloper.php` - Comando para desarrollo
2. `app/Console/Commands/SetupProduction.php` - Comando para producción
3. `database/seeders/ProductionAdminUserSeeder.php` - Seeder de super-admin para producción
4. `tests/Feature/Commands/SetupDeveloperTest.php` - Tests del comando developer
5. `tests/Feature/Commands/SetupProductionTest.php` - Tests del comando production
6. `tests/Feature/Seeders/ProductionAdminUserSeederTest.php` - Tests del seeder
7. `README.md` - Actualizado con documentación de comandos
8. `docs/comandos-setup.md` - Documentación detallada (opcional)
9. `docs/guia-usuario/guia-administrador.md` - Actualizado con sección de setup inicial
10. `docs/pasos/paso51.md` - Archivo con prompts y resúmenes del chat

---

## Decisiones Tomadas

1. **NewsTagSeeder**: ✅ Crear etiquetas básicas comunes para el sitio web
   - Etiquetas como: "Noticias", "Eventos", "Convocatorias", "Erasmus+", "Movilidad", "Formación", etc.
   - Disponibles tanto en desarrollo como en producción

2. **AdminUserSeeder**: ✅ Crear `ProductionAdminUserSeeder` separado
   - Solo crea un usuario super-admin
   - Solicita email por terminal (interactivo)
   - El super-admin será responsable de crear el resto de usuarios

3. **Migraciones en Producción**: ✅ Permitir `migrate:fresh` con confirmación
   - Se permite `migrate:fresh` pero con doble confirmación obligatoria
   - Advertir claramente que se eliminarán todos los datos

4. **Contraseña Super-Admin**: ✅ Generar aleatoria automáticamente
   - Contraseña aleatoria segura (mínimo 16 caracteres)
   - Se muestra solo una vez al finalizar el setup
   - El usuario usará "olvidé mi contraseña" en el primer acceso para establecer una nueva

5. **Validaciones de Entorno**: ✅ Validaciones con bloqueo en errores críticos
   - **Errores Críticos (BLOQUEAN la ejecución)**:
     - Conexión a base de datos → Si falla, **BLOQUEAR** y terminar
     - Existencia y configuración del archivo `.env` → Si no existe, **BLOQUEAR** y terminar
     - Permisos de escritura en `storage/` y `bootstrap/cache/` → Si no hay permisos, **BLOQUEAR** y terminar
   - **Advertencias (NO BLOQUEAN, solo advierten)**:
     - `APP_ENV=production` → Si no está en producción, **ADVERTIR** pero permitir continuar
     - `APP_DEBUG=false` → Si está en true, **ADVERTIR** pero permitir continuar
   - **Opción `--force`**: Permite saltar solo las advertencias (no los errores críticos)
   - **Comportamiento**: Los errores críticos siempre bloquean, las advertencias permiten continuar con confirmación

---

## Progreso

| Fase | Estado | Fecha |
|------|--------|-------|
| 1 | ⏳ Pendiente | - |
| 2 | ⏳ Pendiente | - |
| 3 | ⏳ Pendiente | - |
| 4 | ⏳ Pendiente | - |
| 5 | ⏳ Pendiente | - |
| 6 | ⏳ Pendiente | - |
| 7 | ⏳ Pendiente | - |

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan completado - Pendiente de aprobación para comenzar implementación
