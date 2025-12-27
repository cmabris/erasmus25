# Paso 15: Dashboard de Administración (Paso 3.5.1 de la Planificación)

Este documento contiene todos los prompts utilizados y resultados obtenidos en el desarrollo del Paso 15, que corresponde a la implementación del Dashboard de Administración del panel de control (Back-office) de la aplicación "Erasmus+ Centro (Murcia)". Este paso corresponde al paso 3.5.1 de la planificación general.

---

## Prompt 1: Solicitud de Desarrollo del Paso 3.5.1

**Prompt:**
> "En la carpeta docs tienes toda la documentación de la aplicación, con un archivo que hace de índice y otro llamado @planificacion_pasos.md que contiene las tareas a desarrollar en orden. Ahora, siguiendo esa recomendación, vamos a desarrollar el paso 3.5.1 que corresponde al primero relacionado con el panel de administración. Me gustaría que el dashboad tuviera un desarrollo moderno y muy actual. Se pueden reutilizar aquellos componentes que ya tenemos y generar otros nuevos en caso necesario. Debe quedar preparado para añadir más características, que ya aparecen planificadas pero que iremos haciendo en pasos posteriores Establece los pasos a seguir para desarrollar el paso 3.5.1 y así ir poco a poco hasta completarlo."

**Resultado:**
- Se analizó la documentación existente y el sistema actual
- Se revisaron los componentes existentes y la estructura de vistas
- Se propuso un plan estructurado de 12 pasos organizados en 4 fases:
  - **Fase 1:** Fundamentos (MVP) - Estructura base, estadísticas y accesos rápidos
  - **Fase 2:** Funcionalidades Avanzadas - Actividad reciente, gráficos y alertas
  - **Fase 3:** UX y Optimización - Animaciones, responsive, caché e internacionalización
  - **Fase 4:** Calidad - Tests y documentación

**Plan detallado:**
1. Estructura base del Dashboard (componente Livewire, vista, rutas)
2. Componentes de estadísticas (6 estadísticas principales)
3. Sección de estadísticas (grid responsive con tarjetas)
4. Accesos rápidos (tarjetas clickeables según permisos)
5. Actividad reciente (últimas acciones del sistema)
6. Gráficos de actividad (Chart.js con 3 gráficos)
7. Alertas y notificaciones (convocatorias próximas a cerrar, borradores sin publicar, eventos sin ubicación)
8. Mejoras de UX (animaciones, responsive, accesibilidad)
9. Optimización (caché para estadísticas y gráficos)
10. Internacionalización (traducciones y formateo de fechas/números)
11. Tests completos (29 tests)
12. Documentación técnica

**Archivos de plan creados:**
- `docs/pasos/paso-3.5.1-plan.md` (plan detallado)
- `docs/pasos/paso-3.5.1-resumen.md` (resumen ejecutivo)

---

## Prompt 2: Aprobación para Comenzar Fase 1

**Prompt:**
> "Ya he revisado el plan y de momento me parece adecuado. Puedes comenzar a implementar la Fase 1"

**Resultado:**

### Fase 1: Estructura Base del Dashboard

**Archivos creados:**
- `app/Livewire/Admin/Dashboard.php`
- `resources/views/livewire/admin/dashboard.blade.php`

**Archivos modificados:**
- `routes/web.php` - Añadido grupo de rutas `/admin` con middleware `auth` y `verified`
- `resources/views/components/layouts/app/sidebar.blade.php` - Añadido enlace al dashboard en sección "Administration"

**Características implementadas:**
- Componente Livewire `Admin\Dashboard` con propiedades públicas para estadísticas
- Método `mount()` que carga estadísticas iniciales
- Vista con layout de administración y estructura base
- Ruta `/admin` protegida con autenticación y verificación de email
- Enlace en sidebar visible para usuarios con permisos de administración

**Estadísticas implementadas:**
- Programas activos
- Convocatorias abiertas
- Convocatorias cerradas
- Noticias publicadas este mes
- Documentos disponibles
- Eventos próximos

**Accesos rápidos implementados:**
- Crear nueva convocatoria
- Crear nueva noticia
- Crear nuevo documento
- Crear nuevo evento
- Gestionar programas
- Gestionar usuarios (solo super-admin)

**Traducciones añadidas:**
- `lang/es/common.php` - Sección `admin.dashboard` con todas las traducciones
- `lang/en/common.php` - Traducciones en inglés

---

## Prompt 3: Instalación de Login Link para Desarrollo

**Prompt:**
> "Antes de continuar, para poder hacer pruebas fácilmente en el navegador, podemos instalar la librería 'spatie/laravel-login-link' solo para desarrollo, y que nos permite entrar autenticados desde un enlace. Tienes aquí la documentación: https://github.com/spatie/laravel-login-link Podemos crear un enlace para cada rol que puede entrar al dashhboard y en los seeders creamos los usuarios necesarios (en caso de que no lo estén ya)."

**Resultado:**

**Paquete instalado:**
- `spatie/laravel-login-link` (solo para desarrollo)

**Archivos modificados:**
- `database/seeders/AdminUserSeeder.php` - Modificado para crear 4 usuarios con roles distintos:
  - `super-admin@erasmus-murcia.es` (super-admin)
  - `admin@erasmus-murcia.es` (admin)
  - `editor@erasmus-murcia.es` (editor)
  - `viewer@erasmus-murcia.es` (viewer)
- `config/login-link.php` - Configurado para permitir `*.test` (Herd) y redirigir a `admin.dashboard`
- `resources/views/livewire/auth/login.blade.php` - Añadido bloque `@env('local')` con componentes `x-login-link` para cada rol

**Características:**
- Enlaces de login rápido visibles solo en entorno local
- Enlaces para cada rol (super-admin, admin, editor, viewer)
- Redirección automática al dashboard después del login

---

## Prompt 4: Corrección de Icono y Cursor

**Prompt:**
> "Acabo de ejecutar los seeders y funciona correctamente. Pero he accedido a la vista de Login y no veo los enlaces para realizar la autenticación y así entrar al dashboard."

**Resultado:**
- Se verificó que los enlaces estaban correctamente implementados
- El usuario confirmó que ya los veía

**Prompt de seguimiento:**
> "Ya los veo. Pero al situar el ratón sobre ellos, el puntero no cambia a una mano para indicar que es clicable. Y después de hacer click en superadmin me aparece el siguiente error, que debe ser del dashboard, no de la librería que acabamos de instalar: Flux component [icon.calendar-plus] does not exist."

**Correcciones realizadas:**
- Añadida clase `cursor-pointer` a los componentes `x-login-link` en la vista de login
- Reemplazado icono `calendar-plus` (no existe en Flux UI) por `calendar` en el dashboard

---

## Prompt 5: Corrección de Claves de Traducción

**Prompt:**
> "Ahora el dashboard ya luce bien. Podemos pasar a la siguiente fase planificada"

**Resultado:**
- Se continuó con la implementación

**Prompt de seguimiento:**
> "Al acceder al navegador obtengo el siguiente error: TypeError - Internal Server Error Cannot assign Illuminate\Support\Collection to property App\Livewire\Admin\Dashboard::$recentActivities of type Illuminate\Database\Eloquent\Collection"

**Corrección realizada:**
- Cambiado tipo hint de `$recentActivities` y `$alerts` de `Illuminate\Database\Eloquent\Collection` a `Illuminate\Support\Collection`

**Prompt de seguimiento:**
> "me aparecen en la vista textos como: admin.dashboard.welcome_message admin.dashboard.statistics_title admin.dashboard.quick_actions_title Y algunos otros en las tarjetas de contenido."

**Corrección realizada:**
- Corregidas todas las claves de traducción de `__('admin.dashboard...')` a `__('common.admin.dashboard...')` para coincidir con la estructura en los archivos de traducción

---

## Prompt 6: Implementación de Actividad Reciente y Alertas

**Prompt:**
> "Perfecto, ya veo la sección de actividad reciente."

**Resultado:**
- Se implementó la sección de Actividad Reciente (Paso 5)
- Se implementó la sección de Alertas (Paso 7)

**Características implementadas:**

**Actividad Reciente:**
- Método `loadRecentActivities()` que combina:
  - Registros de `AuditLog` (últimas 10 acciones)
  - Convocatorias actualizadas recientemente
  - Noticias publicadas recientemente
  - Documentos creados recientemente
- Métodos helper para formatear actividades:
  - `getActivityType()` - Determina tipo de actividad
  - `getModelTitle()` - Obtiene título del modelo
  - `getModelUrl()` - Genera URL del modelo
  - `getActivityIcon()` - Retorna icono según tipo
  - `getActivityColor()` - Retorna color según tipo

**Alertas:**
- Método `loadAlerts()` que detecta:
  - Convocatorias próximas a cerrar (menos de 7 días)
  - Borradores sin publicar (creados hace más de 7 días)
  - Eventos sin ubicación (públicos y próximos)
- Corrección de cálculo de días restantes usando `max(0, ...)` para evitar valores negativos

**Vista:**
- Sección de actividad reciente con lista de actividades
- Sección de alertas con `flux:callout` para cada tipo de alerta
- Estado vacío cuando no hay actividades o alertas

---

## Prompt 7: Implementación de Gráficos (Fase 3)

**Prompt:**
> "Vayamos en orden y hagamos primero la fase 3"

**Resultado:**

### Fase 3: Gráficos de Actividad

**Dependencias añadidas:**
- `chart.js` añadido a `package.json`
- Importado en `resources/js/app.js` y expuesto globalmente como `window.Chart`

**Métodos implementados:**
- `getMonthlyActivityData()` - Datos de actividad mensual (últimos 6 meses)
- `getCallsByStatusData()` - Distribución de convocatorias por estado
- `getCallsByProgramData()` - Top 5 programas con más convocatorias

**Vista:**
- Sección de gráficos con 3 canvas para Chart.js
- Script JavaScript para inicialización de gráficos
- Manejo de eventos de Livewire (`livewire:init`, `livewire:navigated`)
- Destrucción de instancias previas antes de re-inicializar

**Problemas encontrados y resueltos:**
- Error "Canvas is already in use" - Resuelto almacenando instancias y destruyéndolas antes de recrear
- Error "initCharts is not defined" - Resuelto moviendo función a scope global
- Mensajes repetidos en consola - Resuelto con flag `window.chartsInitializing`

---

## Prompt 8: Generación de Datos para Gráficos

**Prompt:**
> "Ya veo al final del Dashboard la sección de los gráficos, pero solo aparecen los títulos. Puedes generar datos falsos en los seeders para que se vea cómo son los gráficos?"

**Resultado:**

**Archivo creado:**
- `database/seeders/DashboardDataSeeder.php`

**Características:**
- Genera convocatorias, noticias y documentos distribuidos en los últimos 6 meses
- Reutiliza `Program`, `AcademicYear`, `DocumentCategory` y `User` existentes
- Crea modelos directamente (no usa factories) para evitar problemas con relaciones
- Usa `uniqid()` en slugs para evitar violaciones de constraint único

**Archivo modificado:**
- `database/seeders/DatabaseSeeder.php` - Añadido `DashboardDataSeeder::class`

**Correcciones adicionales:**
- `database/seeders/DocumentsSeeder.php` - Añadido `uniqid()` a slugs para evitar duplicados

---

## Prompt 9: Corrección de Errores de Gráficos

**Prompt:**
> "Al entrar en el dashboard como admin, en la última sección con título 'Actividad Mensual' aparecen dos recuadros con los títulos 'Actividad Mensual' y 'Convocatorias por estado', pero están vacíos salvo por el título que muestran."

**Resultado:**
- Se corrigió la inicialización de gráficos
- Se mejoró el manejo de eventos de Livewire

**Prompt de seguimiento:**
> "He vuelto a entrar como superadmin y ya veo los gráficos, pero la consola se ha llenado de mensajes..."

**Correcciones realizadas:**
- Refactorizado script de inicialización de gráficos
- Añadido almacenamiento de instancias en `window.dashboardCharts`
- Implementada destrucción de instancias antes de recrear
- Añadido flag `window.chartsInitializing` para prevenir inicializaciones simultáneas
- Ajustados delays y event listeners para mejor sincronización

**Resultado final:**
- Gráficos funcionando correctamente sin errores en consola

---

## Prompt 10: Completar Fase 4 - Animaciones y Optimización

**Prompt:**
> "Perfecto. Ahora se siguen viendo los gráficos y ya no hay mensajes en la consola, salvo los dos iniciales que no corresponden a lo desarrollado."

**Resultado:**
- Se completaron los Pasos 8 y 9 (animaciones, responsive, accesibilidad y optimización)

### Paso 8: Animaciones, Responsive y Accesibilidad

**Archivo modificado:**
- `resources/css/app.css` - Añadidas animaciones CSS personalizadas:
  - `@keyframes fade-in` - Aparición gradual
  - `@keyframes slide-up` - Deslizamiento desde abajo
  - Clases `animate-fade-in` y `animate-slide-up`
  - Soporte para `prefers-reduced-motion`

**Vista modificada:**
- Añadidas clases de animación con delays escalonados
- Añadidos atributos ARIA para accesibilidad:
  - `role="list"`, `role="listitem"`, `role="region"`
  - `aria-label`, `aria-labelledby`, `aria-hidden`
  - `aria-live="polite"` para alertas
- Asegurados estados de foco visibles para elementos interactivos

### Paso 9: Optimización y Caché

**Archivo modificado:**
- `app/Livewire/Admin/Dashboard.php` - Implementado sistema de caché:
  - Constantes `CACHE_TTL_STATISTICS = 300` (5 minutos)
  - Constante `CACHE_TTL_CHARTS = 900` (15 minutos)
  - Métodos de estadísticas usan `Cache::remember()`
  - Métodos de gráficos usan `Cache::remember()`
  - Método estático `clearCache()` para limpiar todas las cachés

**Cachés implementadas:**
- `dashboard.statistics` - Todas las estadísticas principales
- `dashboard.charts.monthly_activity` - Datos de actividad mensual
- `dashboard.charts.calls_by_status` - Datos de convocatorias por estado
- `dashboard.charts.calls_by_program` - Datos de convocatorias por programa

---

## Prompt 11: Internacionalización Completa

**Prompt:**
> "Pasemos a la fase 4"

**Resultado:**
- Se completó el Paso 10 (Internacionalización)

### Paso 10: Internacionalización

**Archivos modificados:**
- `app/Support/helpers.php` - Añadidas funciones helper:
  - `format_number(int|float $number, int $decimals = 0): string` - Formatea números según locale usando `NumberFormatter`
  - `format_date(\Carbon\Carbon $date, string $format = null): string` - Formatea fechas según locale usando `Carbon::translatedFormat()`
  - `format_datetime(\Carbon\Carbon $datetime, string $format = null): string` - Formatea fecha y hora según locale
  - Manejo de errores con fallback a funciones nativas de PHP

**Archivo modificado:**
- `app/Livewire/Admin/Dashboard.php`:
  - `loadAlerts()` usa `format_date()` para fechas en alertas
  - `getMonthlyActivityData()` usa `translatedFormat('M Y')` para meses en gráficos

**Vista modificada:**
- `resources/views/livewire/admin/dashboard.blade.php`:
  - `aria-label` de elementos `<time>` usa `format_datetime()`

**Traducciones añadidas:**
- `lang/es/common.php` - Añadida clave `messages.by`

---

## Prompt 12: Tests del Dashboard

**Prompt:**
> "Ya he revisado la web y parece adecuada. Continuemos con el paso 10."

**Resultado:**
- Se completó el Paso 10 (confirmado por el usuario)

**Prompt de seguimiento:**
> "Perfecto hasta ahora. Continuemos con el paso 11."

**Resultado:**

### Paso 11: Tests Completos

**Archivo creado:**
- `tests/Feature/Livewire/Admin/DashboardTest.php`

**Tests implementados (29 tests, 50 assertions):**

1. **Control de Acceso** (3 tests):
   - Redirección de usuarios no autenticados
   - Acceso para usuarios con permisos de admin
   - Acceso para super-admin

2. **Estadísticas** (6 tests):
   - Conteo correcto de programas activos
   - Conteo correcto de convocatorias abiertas
   - Conteo correcto de convocatorias cerradas
   - Conteo correcto de noticias del mes
   - Conteo correcto de documentos disponibles
   - Conteo correcto de eventos próximos

3. **Permisos** (3 tests):
   - Mostrar acciones según permisos
   - Ocultar acciones sin permisos
   - Gestión de usuarios solo para super-admin

4. **Actividad Reciente** (2 tests):
   - Mostrar actividades recientes
   - Estado vacío cuando no hay actividades

5. **Alertas** (4 tests):
   - Alertas para convocatorias próximas a cerrar
   - Alertas para borradores sin publicar
   - Alertas para eventos sin ubicación
   - Sin alertas cuando no hay ninguna

6. **Datos de Gráficos** (3 tests):
   - Datos de actividad mensual
   - Datos de convocatorias por estado
   - Datos de convocatorias por programa

7. **Caché** (3 tests):
   - Caché de estadísticas funciona
   - Limpieza de caché funciona
   - Caché de datos de gráficos funciona

8. **Renderizado** (5 tests):
   - Renderizado de todas las secciones principales

**Correcciones realizadas durante desarrollo de tests:**
- Corregido uso de `year` en lugar de `name` para `AcademicYear` factory
- Corregido método `getCallsByProgramData()` para compatibilidad con SQLite (filtrado después de `withCount()`)
- Añadido import de `DocumentCategory` en tests
- Corregidos métodos de llamada a datos de gráficos para usar `instance()` en lugar de `call()`

---

## Prompt 13: Corrección de Tests Existentes

**Prompt:**
> "Al pasar los tests en paralelo me fallan 65 y los he vuelto a ejecutar sin la bandera --parallel y también fallan 65. Ejecútalos con --stop-on-failure y trata de ver los fallos y corregirlos."

**Resultado:**

### Problema Principal

Los tests fallaban porque el locale no estaba configurado en español, causando que las traducciones devolvieran textos en inglés en lugar de español.

### Solución Aplicada

Se añadió `App::setLocale('es')` en el `beforeEach()` de todos los archivos de tests que verifican traducciones:

**Archivos modificados (18 archivos):**

1. **Tests de Componentes:**
   - `tests/Feature/Components/ContentCardsTest.php`
   - `tests/Feature/Components/PublicLayoutTest.php`
   - `tests/Feature/Components/UiComponentsTest.php`

2. **Tests de Livewire Públicos:**
   - `tests/Feature/Livewire/Public/Calls/IndexTest.php`
   - `tests/Feature/Livewire/Public/Calls/ShowTest.php`
   - `tests/Feature/Livewire/Public/Documents/IndexTest.php`
   - `tests/Feature/Livewire/Public/Documents/ShowTest.php`
   - `tests/Feature/Livewire/Public/Events/CalendarTest.php`
   - `tests/Feature/Livewire/Public/Events/IndexTest.php`
   - `tests/Feature/Livewire/Public/Events/ShowTest.php`
   - `tests/Feature/Livewire/Public/HomeTest.php`
   - `tests/Feature/Livewire/Public/News/IndexTest.php`
   - `tests/Feature/Livewire/Public/News/ShowTest.php`
   - `tests/Feature/Livewire/Public/Newsletter/SubscribeTest.php`
   - `tests/Feature/Livewire/Public/Newsletter/UnsubscribeTest.php`
   - `tests/Feature/Livewire/Public/Newsletter/VerifyTest.php`
   - `tests/Feature/Livewire/Public/Programs/IndexTest.php`

**Corrección adicional:**
- `tests/Feature/Livewire/Public/Events/ShowTest.php` - Corregido método de verificación para usar `pluck('id')->contains()` en lugar de `contains('id', ...)`

**Resultado final:**
- ✅ 732 tests pasando
- ✅ 1737 assertions
- ✅ Duración: ~53 segundos

---

## Resumen Ejecutivo

### Objetivos Cumplidos

✅ **Dashboard Moderno y Funcional**
- Visión general del estado de la aplicación
- Estadísticas clave en tiempo real (6 estadísticas principales)
- Accesos rápidos a secciones principales (6 accesos según permisos)
- Actividad reciente del sistema (últimas 10 actividades)
- Alertas importantes (3 tipos de alertas)
- Gráficos interactivos (3 gráficos con Chart.js)

✅ **Optimización y Rendimiento**
- Sistema de caché implementado (5 minutos para estadísticas, 15 minutos para gráficos)
- Consultas optimizadas sin problemas N+1
- Lazy loading preparado para futuras expansiones

✅ **UX y Accesibilidad**
- Animaciones CSS personalizadas con soporte para `prefers-reduced-motion`
- Diseño completamente responsive
- Atributos ARIA para accesibilidad
- Estados de foco visibles

✅ **Internacionalización Completa**
- Todos los textos traducidos (ES/EN)
- Formateo de fechas y números según locale
- Meses en gráficos traducidos

✅ **Calidad y Testing**
- 29 tests completos del dashboard
- Corrección de 65 tests existentes (problema de locale)
- 732 tests pasando en total

✅ **Documentación**
- Documentación técnica completa (`docs/admin-dashboard.md`)
- Archivo con prompts y resumen (`docs/pasos/paso15.md`)
- Actualización del índice de documentación

### Archivos Creados

- `app/Livewire/Admin/Dashboard.php` - Componente Livewire principal
- `resources/views/livewire/admin/dashboard.blade.php` - Vista del dashboard
- `database/seeders/DashboardDataSeeder.php` - Seeder de datos para gráficos
- `tests/Feature/Livewire/Admin/DashboardTest.php` - Tests completos
- `docs/admin-dashboard.md` - Documentación técnica
- `docs/pasos/paso15.md` - Este archivo

### Archivos Modificados

- `routes/web.php` - Añadido grupo de rutas `/admin`
- `resources/views/components/layouts/app/sidebar.blade.php` - Añadido enlace al dashboard
- `resources/views/livewire/auth/login.blade.php` - Añadidos enlaces de login rápido
- `database/seeders/AdminUserSeeder.php` - Modificado para crear usuarios de prueba
- `database/seeders/DatabaseSeeder.php` - Añadido `DashboardDataSeeder`
- `database/seeders/DocumentsSeeder.php` - Corregido para evitar slugs duplicados
- `app/Support/helpers.php` - Añadidas funciones `format_number()`, `format_date()`, `format_datetime()`
- `resources/css/app.css` - Añadidas animaciones CSS
- `resources/js/app.js` - Añadido Chart.js
- `package.json` - Añadido `chart.js`
- `lang/es/common.php` y `lang/en/common.php` - Añadidas traducciones del dashboard
- 18 archivos de tests - Añadido `App::setLocale('es')` en `beforeEach()`

### Estadísticas Finales

- **Componentes creados**: 1 componente Livewire principal
- **Vistas creadas**: 1 vista principal del dashboard
- **Tests implementados**: 29 tests del dashboard + corrección de 65 tests existentes
- **Traducciones añadidas**: ~50 claves de traducción (ES/EN)
- **Gráficos implementados**: 3 gráficos interactivos
- **Cachés implementadas**: 4 cachés diferentes
- **Tiempo de desarrollo**: ~12 prompts principales

### Próximos Pasos

El dashboard está completamente funcional y preparado para futuras expansiones. Los siguientes pasos planificados son:

- **Paso 3.5.2**: Gestión de Programas (CRUD)
- **Paso 3.5.3**: Gestión de Años Académicos (CRUD)
- **Paso 3.5.4**: Gestión de Convocatorias (CRUD Completo)
- Y sucesivos pasos según la planificación general

---

## Referencias

- [Plan de Desarrollo](paso-3.5.1-plan.md)
- [Resumen Ejecutivo](paso-3.5.1-resumen.md)
- [Documentación Técnica](../admin-dashboard.md)
- [Planificación General](../planificacion_pasos.md)

