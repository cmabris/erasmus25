# Plan Detallado: Paso 3.7.4 - Importación de Datos

## Objetivo

Implementar un sistema completo de importación de datos que permita:
- Importar convocatorias desde Excel/CSV con validación completa
- Importar usuarios desde Excel/CSV con validación completa
- Validación de datos importados antes de guardar
- Manejo de errores y reporte de filas con problemas
- Opción de importación en modo "dry-run" (solo validar sin guardar)
- Usar Laravel Excel (maatwebsite/excel) que ya está instalado en la aplicación
- Reutilizar validaciones de Form Requests existentes
- Proporcionar plantillas Excel descargables para facilitar la importación

## Estado Actual

### ✅ Ya Implementado

1. **Laravel Excel**:
   - ✅ Paquete `maatwebsite/excel` v3.1 instalado
   - ✅ Configuración automática de Laravel Excel

2. **Exportaciones Existentes**:
   - ✅ `App\Exports\CallsExport` - Exportación de convocatorias
   - ✅ `App\Exports\ResolutionsExport` - Exportación de resoluciones
   - ✅ `App\Exports\AuditLogsExport` - Exportación de logs de auditoría
   - ✅ `App\Exports\NewsletterSubscriptionsExport` - Exportación de suscriptores newsletter
   - ✅ Patrón establecido para exportaciones

3. **Form Requests y Validaciones**:
   - ✅ `App\Http\Requests\StoreCallRequest` - Validación para crear convocatorias
   - ✅ `App\Http\Requests\StoreUserRequest` - Validación para crear usuarios
   - ✅ Reglas de validación completas y mensajes personalizados

4. **Componentes Index**:
   - ✅ `App\Livewire\Admin\Calls\Index` - Listado de convocatorias
   - ✅ `App\Livewire\Admin\Users\Index` - Listado de usuarios (si existe)

5. **Modelos**:
   - ✅ `App\Models\Call` - Modelo de convocatorias con relaciones
   - ✅ `App\Models\User` - Modelo de usuarios con roles
   - ✅ `App\Models\Program` - Modelo de programas (necesario para relaciones)
   - ✅ `App\Models\AcademicYear` - Modelo de años académicos (necesario para relaciones)

### ⚠️ Pendiente de Implementar

1. **Importación de Convocatorias**:
   - ⚠️ Crear clase `App\Imports\CallsImport`
   - ⚠️ Implementar validación de filas
   - ⚠️ Manejo de errores y reporte
   - ⚠️ Crear componente Livewire `Admin\Calls\Import`
   - ⚠️ Crear vista de importación
   - ⚠️ Añadir botón de importación en Index
   - ⚠️ Crear plantilla Excel descargable

2. **Importación de Usuarios**:
   - ⚠️ Crear clase `App\Imports\UsersImport`
   - ⚠️ Implementar validación de filas
   - ⚠️ Manejo de errores y reporte
   - ⚠️ Crear componente Livewire `Admin\Users\Import`
   - ⚠️ Crear vista de importación
   - ⚠️ Añadir botón de importación en Index
   - ⚠️ Crear plantilla Excel descargable

3. **Tests**:
   - ⚠️ Tests de importación de convocatorias
   - ⚠️ Tests de importación de usuarios
   - ⚠️ Tests de validación de datos
   - ⚠️ Tests de manejo de errores

---

## Plan de Desarrollo

### **Fase 1: Importación de Convocatorias**

#### Paso 1.1: Crear Clase CallsImport

**Objetivo**: Crear la clase de importación para convocatorias.

**Archivo**: `app/Imports/CallsImport.php`

**Características**:
- Implementar `ToModel` o `ToCollection` - Para procesar filas
- Implementar `WithHeadingRow` - Para usar primera fila como encabezados
- Implementar `WithValidation` - Para validar cada fila
- Implementar `WithBatchInserts` - Para optimizar inserción masiva
- Implementar `SkipsOnFailure` - Para continuar con errores
- Implementar `WithProgressBar` - Para mostrar progreso (opcional)
- Validar cada fila usando las reglas de `StoreCallRequest`
- Manejar relaciones (program_id, academic_year_id) mediante búsqueda por código/nombre
- Convertir datos de Excel a formato esperado (fechas, arrays, etc.)
- Generar slug automáticamente si no se proporciona
- Asignar `created_by` y `updated_by` al usuario actual

**Columnas esperadas en Excel**:
1. `programa` - Código o nombre del programa (buscar en tabla programs)
2. `año_academico` - Año académico (buscar en tabla academic_years)
3. `titulo` - Título de la convocatoria (requerido)
4. `slug` - Slug (opcional, se genera automáticamente si está vacío)
5. `tipo` - Tipo: "alumnado" o "personal" (requerido)
6. `modalidad` - Modalidad: "corta" o "larga" (requerido)
7. `numero_plazas` - Número de plazas (requerido, entero)
8. `destinos` - Destinos separados por comas o punto y coma (requerido, array)
9. `fecha_inicio_estimada` - Fecha de inicio (formato: dd/mm/yyyy o yyyy-mm-dd)
10. `fecha_fin_estimada` - Fecha de fin (formato: dd/mm/yyyy o yyyy-mm-dd)
11. `requisitos` - Requisitos (opcional, texto largo)
12. `documentacion` - Documentación (opcional, texto largo)
13. `criterios_seleccion` - Criterios de selección (opcional, texto largo)
14. `estado` - Estado: "borrador", "abierta", "cerrada", etc. (opcional)
15. `fecha_publicacion` - Fecha de publicación (opcional, formato fecha)
16. `fecha_cierre` - Fecha de cierre (opcional, formato fecha)

**Validaciones a implementar**:
- Validar existencia de programa (por código o nombre)
- Validar existencia de año académico
- Validar formato de fechas
- Validar tipos y modalidades permitidos
- Validar que fecha_fin sea posterior a fecha_inicio
- Validar que número de plazas sea positivo
- Validar que destinos no esté vacío

**Manejo de errores**:
- Recopilar errores por fila
- Continuar procesando aunque haya errores
- Retornar colección de errores al finalizar
- Incluir número de fila y mensaje de error

**Archivos a crear**:
- `app/Imports/CallsImport.php`

**Resultado esperado**:
- Clase de importación creada con validación completa
- Manejo de errores por fila
- Conversión correcta de datos de Excel
- Asignación automática de relaciones

---

#### Paso 1.2: Crear Componente Livewire Admin\Calls\Import

**Objetivo**: Crear componente Livewire para la importación de convocatorias.

**Archivo**: `app/Livewire/Admin/Calls/Import.php`

**Características**:
- Autorización: Verificar permiso `create` en Call
- Propiedad para archivo Excel/CSV
- Propiedad para modo "dry-run" (solo validar)
- Método `downloadTemplate()` - Descargar plantilla Excel
- Método `import()` - Procesar archivo de importación
- Propiedades para mostrar resultados:
  - `$importedCount` - Número de registros importados
  - `$failedCount` - Número de registros fallidos
  - `$errors` - Array de errores por fila
  - `$isProcessing` - Estado de procesamiento
- Validación del archivo (tipo, tamaño)
- Mostrar progreso durante importación
- Redirigir a Index después de importación exitosa

**Métodos a implementar**:
```php
public function mount(): void
{
    $this->authorize('create', Call::class);
}

public function downloadTemplate()
{
    // Generar y descargar plantilla Excel
}

public function import()
{
    // Validar archivo
    // Procesar importación
    // Recopilar errores
    // Mostrar resultados
}
```

**Archivos a crear**:
- `app/Livewire/Admin/Calls/Import.php`

**Resultado esperado**:
- Componente Livewire funcional
- Autorización verificada
- Manejo de archivos
- Reporte de resultados

---

#### Paso 1.3: Crear Vista de Importación

**Objetivo**: Crear vista Blade para el componente de importación.

**Archivo**: `resources/views/livewire/admin/calls/import.blade.php`

**Características**:
- Layout de administración
- Breadcrumbs (Convocatorias > Importar)
- Formulario con:
  - Botón para descargar plantilla
  - Campo de subida de archivo (FilePond o input file)
  - Checkbox para modo "dry-run"
  - Botón de importar
  - Indicador de progreso
- Sección de resultados:
  - Resumen de importación (éxitos, fallos)
  - Tabla de errores (si los hay)
  - Botón para volver a Index
- Mensajes de éxito/error
- Validación en frontend (tipo de archivo, tamaño)

**Componentes Flux UI a usar**:
- `flux:heading` - Título
- `flux:breadcrumbs` - Navegación
- `flux:field` - Campos de formulario
- `flux:button` - Botones
- `flux:callout` - Mensajes de resultado
- `flux:table` - Tabla de errores

**Archivos a crear**:
- `resources/views/livewire/admin/calls/import.blade.php`

**Resultado esperado**:
- Vista completa y funcional
- Interfaz intuitiva
- Feedback claro al usuario

---

#### Paso 1.4: Crear Plantilla Excel Descargable

**Objetivo**: Crear clase para generar plantilla Excel de convocatorias.

**Archivo**: `app/Exports/CallsTemplateExport.php`

**Características**:
- Implementar `FromArray` - Para datos estáticos
- Implementar `WithHeadings` - Para encabezados
- Implementar `WithStyles` - Para estilos
- Implementar `WithTitle` - Para nombre de hoja
- Incluir fila de ejemplo con datos de muestra
- Incluir segunda hoja con instrucciones de uso
- Encabezados en español con descripción
- Formato de celdas apropiado (fechas, números)
- Validación de datos en Excel (opcional, usando Data Validation)

**Columnas en plantilla**:
- Mismas columnas que `CallsImport` espera
- Fila de ejemplo con datos válidos
- Comentarios en celdas con instrucciones

**Archivos a crear**:
- `app/Exports/CallsTemplateExport.php`

**Resultado esperado**:
- Plantilla Excel descargable
- Instrucciones incluidas
- Formato profesional

---

#### Paso 1.5: Añadir Ruta y Botón de Importación

**Objetivo**: Añadir ruta de importación y botón en Index.

**Archivos a modificar**:
- `routes/web.php` - Añadir ruta de importación
- `resources/views/livewire/admin/calls/index.blade.php` - Añadir botón

**Ruta a añadir**:
```php
Route::get('/admin/convocatorias/importar', \App\Livewire\Admin\Calls\Import::class)
    ->name('admin.calls.import');
```

**Botón a añadir**:
- En el header del Index, junto a "Crear Convocatoria"
- Icono: `arrow-up-tray` o `document-arrow-up`
- Variante: `secondary` o `outline`
- Mostrar solo si el usuario tiene permiso `create` en Call

**Resultado esperado**:
- Ruta configurada
- Botón visible en Index
- Navegación funcional

---

### **Fase 2: Importación de Usuarios**

#### Paso 2.1: Crear Clase UsersImport

**Objetivo**: Crear la clase de importación para usuarios.

**Archivo**: `app/Imports/UsersImport.php`

**Características**:
- Similar a `CallsImport` pero para usuarios
- Validar email único
- Generar contraseña aleatoria si no se proporciona (y notificar por email)
- Asignar roles si se especifican
- Validar roles existentes
- Hash de contraseñas automático

**Columnas esperadas en Excel**:
1. `nombre` - Nombre del usuario (requerido)
2. `email` - Email del usuario (requerido, único)
3. `contraseña` - Contraseña (opcional, se genera si está vacío)
4. `roles` - Roles separados por comas (opcional, ej: "admin,editor")

**Validaciones a implementar**:
- Validar formato de email
- Validar unicidad de email
- Validar que roles existan
- Validar fortaleza de contraseña (si se proporciona)

**Archivos a crear**:
- `app/Imports/UsersImport.php`

**Resultado esperado**:
- Clase de importación creada
- Validación completa
- Asignación de roles
- Generación de contraseñas

---

#### Paso 2.2: Crear Componente Livewire Admin\Users\Import

**Objetivo**: Crear componente Livewire para la importación de usuarios.

**Archivo**: `app/Livewire/Admin/Users/Import.php`

**Características**:
- Similar a `Admin\Calls\Import`
- Autorización: Verificar permiso `create` en User
- Opción para enviar emails con contraseñas generadas
- Reporte de usuarios creados con contraseñas

**Archivos a crear**:
- `app/Livewire/Admin/Users/Import.php`

**Resultado esperado**:
- Componente funcional
- Manejo de contraseñas
- Notificaciones por email

---

#### Paso 2.3: Crear Vista de Importación de Usuarios

**Objetivo**: Crear vista Blade para importación de usuarios.

**Archivo**: `resources/views/livewire/admin/users/import.blade.php`

**Características**:
- Similar a vista de convocatorias
- Checkbox para enviar emails con contraseñas
- Tabla de resultados con información de contraseñas generadas

**Archivos a crear**:
- `resources/views/livewire/admin/users/import.blade.php`

**Resultado esperado**:
- Vista completa
- Manejo de contraseñas visible

---

#### Paso 2.4: Crear Plantilla Excel de Usuarios

**Objetivo**: Crear plantilla Excel para importación de usuarios.

**Archivo**: `app/Exports/UsersTemplateExport.php`

**Características**:
- Similar a `CallsTemplateExport`
- Ejemplo de usuario con roles
- Instrucciones sobre contraseñas

**Archivos a crear**:
- `app/Exports/UsersTemplateExport.php`

**Resultado esperado**:
- Plantilla descargable
- Instrucciones claras

---

#### Paso 2.5: Añadir Ruta y Botón de Importación de Usuarios

**Objetivo**: Añadir ruta y botón para importación de usuarios.

**Archivos a modificar**:
- `routes/web.php` - Añadir ruta
- `resources/views/livewire/admin/users/index.blade.php` - Añadir botón (si existe)

**Resultado esperado**:
- Ruta configurada
- Botón visible

---

### **Fase 3: Traducciones**

#### Paso 3.1: Añadir Traducciones para Importación

**Objetivo**: Añadir traducciones necesarias para importación.

**Tareas**:
1. Revisar archivos de traducción:
   - `lang/es/common.php`
   - `lang/en/common.php`

2. Añadir traducciones para:
   - "Importar" / "Import"
   - "Importando..." / "Importing..."
   - "Plantilla" / "Template"
   - "Descargar plantilla" / "Download template"
   - "Archivo de importación" / "Import file"
   - "Modo de prueba (solo validar)" / "Dry run (validate only)"
   - "Registros importados" / "Imported records"
   - "Registros fallidos" / "Failed records"
   - "Errores" / "Errors"
   - "Fila" / "Row"
   - "Mensaje" / "Message"
   - Mensajes de error específicos

**Archivos a modificar**:
- `lang/es/common.php`
- `lang/en/common.php`

**Resultado esperado**:
- Todas las traducciones añadidas
- Textos en español e inglés

---

### **Fase 4: Tests**

#### Paso 4.1: Crear Tests de Importación de Convocatorias

**Objetivo**: Crear tests para CallsImport.

**Archivo**: `tests/Feature/Imports/CallsImportTest.php`

**Tests a implementar**:
- Test de importación básica exitosa
- Test de importación con datos válidos
- Test de validación de programa inexistente
- Test de validación de año académico inexistente
- Test de validación de tipos inválidos
- Test de validación de fechas inválidas
- Test de manejo de errores múltiples
- Test de generación automática de slug
- Test de asignación de created_by
- Test de conversión de destinos (string a array)
- Test de importación en modo dry-run
- Test de autorización (solo usuarios con permiso pueden importar)

**Archivos a crear**:
- `tests/Feature/Imports/CallsImportTest.php`

**Resultado esperado**:
- Tests de importación creados y pasando

---

#### Paso 4.2: Crear Tests de Importación de Usuarios

**Objetivo**: Crear tests para UsersImport.

**Archivo**: `tests/Feature/Imports/UsersImportTest.php`

**Tests a implementar**:
- Test de importación básica exitosa
- Test de validación de email duplicado
- Test de validación de email inválido
- Test de generación de contraseña automática
- Test de asignación de roles
- Test de validación de roles inexistentes
- Test de hash de contraseñas
- Test de manejo de errores múltiples
- Test de autorización

**Archivos a crear**:
- `tests/Feature/Imports/UsersImportTest.php`

**Resultado esperado**:
- Tests de importación creados y pasando

---

#### Paso 4.3: Crear Tests de Componentes Livewire

**Objetivo**: Crear tests para componentes de importación.

**Archivos**:
- `tests/Feature/Livewire/Admin/Calls/ImportTest.php`
- `tests/Feature/Livewire/Admin/Users/ImportTest.php`

**Tests a implementar**:
- Test de que el componente requiere autorización
- Test de descarga de plantilla
- Test de validación de archivo
- Test de importación exitosa
- Test de reporte de errores
- Test de modo dry-run
- Test de redirección después de importación

**Archivos a crear**:
- `tests/Feature/Livewire/Admin/Calls/ImportTest.php`
- `tests/Feature/Livewire/Admin/Users/ImportTest.php`

**Resultado esperado**:
- Tests de componentes creados y pasando

---

### **Fase 5: Documentación**

#### Paso 5.1: Crear Documentación Técnica

**Objetivo**: Documentar el sistema de importación.

**Archivo**: `docs/imports-system.md`

**Contenido**:
- Descripción general del sistema de importación
- Clases de importación disponibles
- Cómo usar las importaciones
- Formato de archivos Excel/CSV
- Validaciones aplicadas
- Manejo de errores
- Plantillas disponibles
- Ejemplos de uso

**Archivos a crear**:
- `docs/imports-system.md`

**Resultado esperado**:
- Documentación técnica completa

---

#### Paso 5.2: Actualizar Documentación de Componentes

**Objetivo**: Actualizar documentación de componentes con funcionalidad de importación.

**Archivos a actualizar**:
- `docs/admin-calls-crud.md` - Añadir sección de importación
- `docs/admin-users-crud.md` - Añadir sección de importación (si existe)

**Contenido**:
- Descripción de funcionalidad de importación
- Cómo usar el botón de importación
- Formato de archivos requerido
- Validaciones aplicadas
- Manejo de errores

**Resultado esperado**:
- Documentación de componentes actualizada

---

## Resumen de Archivos

### Archivos a Crear

1. **Importaciones**:
   - `app/Imports/CallsImport.php`
   - `app/Imports/UsersImport.php`

2. **Componentes Livewire**:
   - `app/Livewire/Admin/Calls/Import.php`
   - `app/Livewire/Admin/Users/Import.php`

3. **Vistas**:
   - `resources/views/livewire/admin/calls/import.blade.php`
   - `resources/views/livewire/admin/users/import.blade.php`

4. **Plantillas Excel**:
   - `app/Exports/CallsTemplateExport.php`
   - `app/Exports/UsersTemplateExport.php`

5. **Tests**:
   - `tests/Feature/Imports/CallsImportTest.php`
   - `tests/Feature/Imports/UsersImportTest.php`
   - `tests/Feature/Livewire/Admin/Calls/ImportTest.php`
   - `tests/Feature/Livewire/Admin/Users/ImportTest.php`

6. **Documentación**:
   - `docs/imports-system.md`

### Archivos a Modificar

1. **Rutas**:
   - `routes/web.php` - Añadir rutas de importación

2. **Vistas**:
   - `resources/views/livewire/admin/calls/index.blade.php` - Añadir botón de importación
   - `resources/views/livewire/admin/users/index.blade.php` - Añadir botón de importación (si existe)

3. **Traducciones**:
   - `lang/es/common.php` - Añadir traducciones
   - `lang/en/common.php` - Añadir traducciones

4. **Documentación**:
   - `docs/admin-calls-crud.md` - Añadir sección de importación
   - `docs/admin-users-crud.md` - Añadir sección de importación (si existe)

---

## Notas Importantes

1. **Validación**: Las importaciones deben usar las mismas reglas de validación que los Form Requests para mantener consistencia.

2. **Autorización**: Todas las importaciones deben verificar permisos antes de ejecutarse.

3. **Manejo de Errores**: Continuar procesando aunque haya errores, pero reportar todos los problemas al finalizar.

4. **Relaciones**: Buscar relaciones (programas, años académicos) por código o nombre, no solo por ID.

5. **Conversión de Datos**: Convertir correctamente datos de Excel (fechas, arrays, tipos) al formato esperado por la base de datos.

6. **Plantillas**: Proporcionar plantillas Excel descargables con ejemplos y instrucciones para facilitar la importación.

7. **Modo Dry-Run**: Permitir validar archivos sin guardar datos, útil para verificar formato antes de importar.

8. **Rendimiento**: Para grandes volúmenes de datos, usar `WithBatchInserts` para optimizar inserción masiva.

9. **Slugs**: Generar slugs automáticamente si no se proporcionan en el archivo.

10. **Auditoría**: Los registros importados deben tener `created_by` y `updated_by` asignados al usuario que realiza la importación.

---

## Orden de Implementación Recomendado

1. **Fase 1**: Importación de Convocatorias (Pasos 1.1, 1.2, 1.3, 1.4, 1.5)
2. **Fase 2**: Importación de Usuarios (Pasos 2.1, 2.2, 2.3, 2.4, 2.5)
3. **Fase 3**: Traducciones (Paso 3.1)
4. **Fase 4**: Tests (Pasos 4.1, 4.2, 4.3)
5. **Fase 5**: Documentación (Pasos 5.1, 5.2)

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan completado - Listo para implementación
