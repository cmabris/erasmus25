# Sistema de Importación de Datos

Documentación técnica completa del sistema de importación de datos de la aplicación Erasmus+ Centro (Murcia), que permite importar Convocatorias y Usuarios desde archivos Excel/CSV.

## Descripción General

El sistema de importación permite a los administradores importar datos masivos desde archivos Excel (XLSX, XLS) o CSV. Utiliza Laravel Excel (maatwebsite/excel) para procesar archivos, validar datos y manejar errores de manera robusta.

## Características Principales

- ✅ **Importación desde Excel/CSV**: Soporte para archivos .xlsx, .xls y .csv
- ✅ **Validación Robusta**: Usa las mismas reglas de validación que los Form Requests
- ✅ **Manejo de Errores**: Continúa procesando aunque haya errores, reportando todos al final
- ✅ **Modo Dry-Run**: Permite validar archivos sin guardar datos
- ✅ **Plantillas Descargables**: Plantillas Excel con ejemplos y formato correcto
- ✅ **Autorización**: Verificación de permisos antes de importar
- ✅ **Relaciones Inteligentes**: Busca programas y años académicos por código/nombre
- ✅ **Conversión Automática**: Convierte fechas, arrays y tipos automáticamente
- ✅ **Generación Automática**: Slugs y contraseñas generados automáticamente cuando faltan
- ✅ **Auditoría**: Registra `created_by` y `updated_by` para todos los registros importados

---

## Clases de Importación Disponibles

### 1. CallsImport

**Ubicación**: `app/Imports/CallsImport.php`

**Descripción**: Importa convocatorias desde archivos Excel/CSV.

**Características**:
- Implementa `ToCollection` - Procesa filas como colecciones
- Implementa `WithHeadingRow` - Usa primera fila como encabezados
- Implementa `WithValidation` - Valida cada fila
- Implementa `SkipsOnFailure` - Continúa procesando aunque haya errores
- Usa `Importable` y `SkipsFailures` traits para manejo robusto de errores

**Columnas Esperadas** (con encabezados en español o inglés):

| Columna (ES) | Columna (EN) | Requerido | Descripción |
|--------------|--------------|-----------|-------------|
| Programa | Program | ✅ | Código o nombre del programa |
| Año Académico | Academic Year | ✅ | Año académico (ej: 2024-2025) |
| Título | Title | ✅ | Título de la convocatoria |
| Slug | Slug | ❌ | Slug único (se genera automáticamente si está vacío) |
| Tipo | Type | ✅ | `alumnado` o `personal` |
| Modalidad | Modality | ✅ | `corta` o `larga` |
| Número de Plazas | Number of Places | ✅ | Número entero >= 1 |
| Destinos | Destinations | ✅ | Separados por comas o punto y coma |
| Fecha Inicio Estimada | Estimated Start Date | ❌ | Formato: Y-m-d o d/m/Y |
| Fecha Fin Estimada | Estimated End Date | ❌ | Formato: Y-m-d o d/m/Y |
| Requisitos | Requirements | ❌ | Texto libre |
| Documentación | Documentation | ❌ | Texto libre |
| Criterios de Selección | Selection Criteria | ❌ | Texto libre |
| Estado | Status | ❌ | `borrador`, `abierta`, `cerrada`, etc. |
| Fecha Publicación | Published At | ❌ | Formato: Y-m-d o d/m/Y |
| Fecha Cierre | Closed At | ❌ | Formato: Y-m-d o d/m/Y |

**Notas Importantes**:
- Los encabezados se convierten automáticamente a snake_case (ej: "Año Académico" → "ano_academico")
- Los destinos pueden separarse por comas (`,`) o punto y coma (`;`)
- Las fechas aceptan formatos `Y-m-d` (2024-09-01) o `d/m/Y` (01/09/2024)
- El slug se genera automáticamente desde el título si no se proporciona
- `created_by` y `updated_by` se asignan automáticamente al usuario que importa

**Uso**:
```php
use App\Imports\CallsImport;
use Maatwebsite\Excel\Facades\Excel;

// Importación normal
$import = new CallsImport(false, $userId);
Excel::import($import, $file);

// Modo dry-run (solo validar)
$import = new CallsImport(true, $userId);
Excel::import($import, $file);

// Obtener resultados
$imported = $import->getImportedCount();
$failed = $import->getFailedCount();
$errors = $import->getRowErrors();
```

**Métodos Disponibles**:
- `getImportedCount()`: Número de registros importados exitosamente
- `getValidatedCount()`: Número de registros validados (solo en dry-run)
- `getFailedCount()`: Número de registros que fallaron
- `getRowErrors()`: Colección de errores por fila
- `getProcessedCalls()`: Colección de convocatorias procesadas

---

### 2. UsersImport

**Ubicación**: `app/Imports/UsersImport.php`

**Descripción**: Importa usuarios desde archivos Excel/CSV.

**Características**:
- Implementa `ToCollection` - Procesa filas como colecciones
- Implementa `WithHeadingRow` - Usa primera fila como encabezados
- Implementa `WithValidation` - Valida cada fila
- Implementa `SkipsOnFailure` - Continúa procesando aunque haya errores
- Genera contraseñas automáticamente si no se proporcionan
- Asigna roles automáticamente

**Columnas Esperadas**:

| Columna (ES) | Columna (EN) | Requerido | Descripción |
|--------------|--------------|-----------|-------------|
| Nombre | Name | ✅ | Nombre completo del usuario |
| Email | Email | ✅ | Email único del usuario |
| Contraseña | Password | ❌ | Se genera automáticamente si está vacío (12 caracteres) |
| Roles | Roles | ❌ | Separados por comas (ej: `admin,editor`) |

**Roles Disponibles**:
- `super-admin`
- `admin`
- `editor`
- `viewer`

**Notas Importantes**:
- Si la contraseña está vacía, se genera automáticamente una de 12 caracteres
- Los roles pueden separarse por comas (`,`) o punto y coma (`;`)
- Los roles inválidos se filtran automáticamente (solo se asignan los válidos)
- El email se convierte automáticamente a minúsculas
- Las contraseñas se hashean automáticamente antes de guardar

**Uso**:
```php
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

// Importación normal
$import = new UsersImport(false, false);
Excel::import($import, $file);

// Modo dry-run (solo validar)
$import = new UsersImport(true, false);
Excel::import($import, $file);

// Obtener resultados
$imported = $import->getImportedCount();
$failed = $import->getFailedCount();
$errors = $import->getRowErrors();
$usersWithPasswords = $import->getUsersWithPasswords(); // Usuarios con contraseñas generadas
```

**Métodos Disponibles**:
- `getImportedCount()`: Número de usuarios importados exitosamente
- `getValidatedCount()`: Número de usuarios validados (solo en dry-run)
- `getFailedCount()`: Número de usuarios que fallaron
- `getRowErrors()`: Colección de errores por fila
- `getUsersWithPasswords()`: Colección de usuarios con contraseñas generadas automáticamente
- `getProcessedUsers()`: Colección de usuarios procesados

---

## Componentes Livewire

### 1. Admin\Calls\Import

**Ubicación**: `app/Livewire/Admin/Calls/Import.php`

**Ruta**: `/admin/convocatorias/importar`

**Características**:
- Interfaz para subir archivos usando FilePond
- Descarga de plantilla Excel
- Modo dry-run (validar sin guardar)
- Reporte de resultados con errores detallados
- Autorización automática (requiere permiso `calls.create`)

**Propiedades**:
- `$file`: Archivo Excel/CSV a importar
- `$dryRun`: Modo de validación (true = solo validar, false = importar)
- `$results`: Resultados de la importación
- `$isProcessing`: Flag de procesamiento

**Métodos**:
- `mount()`: Verifica autorización
- `downloadTemplate()`: Descarga plantilla Excel
- `validateUploadedFile()`: Valida archivo subido (callback de FilePond)
- `import()`: Ejecuta la importación
- `resetForm()`: Resetea el formulario

**Eventos Dispatched**:
- `import-completed`: Cuando la importación se completa exitosamente
- `import-error`: Cuando ocurre un error durante la importación

---

### 2. Admin\Users\Import

**Ubicación**: `app/Livewire/Admin/Users/Import.php`

**Ruta**: `/admin/usuarios/importar`

**Características**:
- Interfaz para subir archivos usando FilePond
- Descarga de plantilla Excel
- Modo dry-run (validar sin guardar)
- Opción para enviar emails con contraseñas generadas (pendiente de implementar)
- Reporte de resultados con errores detallados
- Tabla de usuarios con contraseñas generadas
- Autorización automática (requiere permiso `users.create`)

**Propiedades**:
- `$file`: Archivo Excel/CSV a importar
- `$dryRun`: Modo de validación (true = solo validar, false = importar)
- `$sendEmails`: Enviar emails con contraseñas generadas (pendiente)
- `$results`: Resultados de la importación
- `$isProcessing`: Flag de procesamiento

**Métodos**:
- `mount()`: Verifica autorización
- `downloadTemplate()`: Descarga plantilla Excel
- `validateUploadedFile()`: Valida archivo subido (callback de FilePond)
- `import()`: Ejecuta la importación
- `resetForm()`: Resetea el formulario

**Eventos Dispatched**:
- `import-completed`: Cuando la importación se completa exitosamente
- `import-error`: Cuando ocurre un error durante la importación

---

## Plantillas Excel

### 1. CallsTemplateExport

**Ubicación**: `app/Exports/CallsTemplateExport.php`

**Descripción**: Genera una plantilla Excel descargable para importar convocatorias.

**Características**:
- Incluye una fila de ejemplo con datos de muestra
- Encabezados estilizados (negrita, fondo azul)
- Anchos de columna optimizados
- Comentarios con instrucciones en la primera celda
- Formato profesional y fácil de usar

**Descarga**: Desde el componente `Admin\Calls\Import` usando el botón "Descargar Plantilla"

**Nombre de Archivo**: `plantilla-convocatorias-YYYY-MM-DD.xlsx`

---

### 2. UsersTemplateExport

**Ubicación**: `app/Exports/UsersTemplateExport.php`

**Descripción**: Genera una plantilla Excel descargable para importar usuarios.

**Características**:
- Incluye dos filas de ejemplo con datos de muestra
- Encabezados estilizados (negrita, fondo azul)
- Anchos de columna optimizados
- Comentarios con instrucciones en la primera celda
- Ejemplos de roles y contraseñas

**Descarga**: Desde el componente `Admin\Users\Import` usando el botón "Descargar Plantilla"

**Nombre de Archivo**: `plantilla-usuarios-YYYY-MM-DD.xlsx`

---

## Validaciones Aplicadas

### CallsImport

Las validaciones se basan en `StoreCallRequest` y incluyen:

- **Programa**: Debe existir en la base de datos (búsqueda por código o nombre)
- **Año Académico**: Debe existir en la base de datos (búsqueda por año)
- **Título**: Requerido, máximo 255 caracteres
- **Slug**: Opcional, único si se proporciona
- **Tipo**: Debe ser `alumnado` o `personal`
- **Modalidad**: Debe ser `corta` o `larga`
- **Número de Plazas**: Requerido, entero, mínimo 1
- **Destinos**: Requerido, array con al menos 1 destino
- **Fechas**: Formato válido, fecha fin debe ser posterior a fecha inicio
- **Estado**: Debe ser uno de los valores permitidos

### UsersImport

Las validaciones incluyen:

- **Nombre**: Requerido, máximo 255 caracteres
- **Email**: Requerido, formato válido, único en la base de datos
- **Contraseña**: Requerido (se genera si está vacío), debe cumplir reglas de `Password::defaults()`
- **Roles**: Opcional, cada rol debe existir en `App\Support\Roles::all()`

---

## Manejo de Errores

### Estrategia de Manejo

1. **SkipsOnFailure**: El importador continúa procesando aunque haya errores en algunas filas
2. **Colección de Errores**: Todos los errores se recopilan en `$rowErrors`
3. **Reporte Detallado**: Cada error incluye:
   - Número de fila
   - Errores de validación específicos
   - Datos de la fila que falló

### Estructura de Errores

```php
[
    'row' => 2, // Número de fila (empezando desde 2, ya que 1 es el encabezado)
    'errors' => [
        'program_id' => ['El programa es obligatorio.'],
        'title' => ['El título es obligatorio.'],
    ],
    'data' => [...], // Datos originales de la fila
]
```

### Errores Comunes

1. **Programa no encontrado**: El código/nombre del programa no existe
2. **Año académico no encontrado**: El año académico no existe
3. **Email duplicado**: El email ya está registrado (solo para usuarios)
4. **Campos requeridos faltantes**: Título, tipo, modalidad, etc.
5. **Valores inválidos**: Tipos, modalidades, estados no permitidos
6. **Fechas inválidas**: Formato incorrecto o fecha fin antes de fecha inicio

---

## Modo Dry-Run

El modo dry-run permite validar archivos sin guardar datos en la base de datos. Es útil para:

- Verificar el formato del archivo antes de importar
- Identificar errores sin afectar la base de datos
- Validar grandes volúmenes de datos de forma segura

**Uso**:
```php
// En el componente Livewire
$import = new CallsImport(true); // dry-run = true
Excel::import($import, $file);

// Obtener resultados de validación
$validated = $import->getValidatedCount();
$errors = $import->getFailedCount();
```

---

## Ejemplos de Uso

### Ejemplo 1: Importar Convocatorias

```php
use App\Imports\CallsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

$file = $request->file('import_file');
$userId = auth()->id();

$import = new CallsImport(false, $userId);
Excel::import($import, $file);

if ($import->getFailedCount() > 0) {
    $errors = $import->getRowErrors();
    // Mostrar errores al usuario
}
```

### Ejemplo 2: Validar Archivo sin Guardar

```php
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

$file = $request->file('import_file');

$import = new UsersImport(true); // dry-run = true
Excel::import($import, $file);

$validated = $import->getValidatedCount();
$errors = $import->getFailedCount();

if ($errors === 0) {
    // Archivo válido, proceder con importación real
}
```

### Ejemplo 3: Obtener Contraseñas Generadas

```php
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

$import = new UsersImport(false, false);
Excel::import($import, $file);

$usersWithPasswords = $import->getUsersWithPasswords();

foreach ($usersWithPasswords as $item) {
    $user = $item['user'];
    $password = $item['password'];
    // Enviar email con contraseña al usuario
}
```

---

## Limitaciones y Consideraciones

### Tamaño de Archivo

- **Límite máximo**: 10MB (10240 KB)
- **Recomendación**: Para archivos grandes, considerar procesamiento en cola

### Rendimiento

- Para grandes volúmenes (>1000 registros), considerar:
  - Procesamiento en cola (jobs)
  - Uso de `WithBatchInserts` (no implementado actualmente)
  - Procesamiento por chunks

### Memoria

- Laravel Excel carga el archivo completo en memoria
- Para archivos muy grandes, considerar procesamiento por chunks

### Validación

- La validación se realiza fila por fila
- Cada fila se valida independientemente
- Los errores no afectan el procesamiento de otras filas

---

## Testing

El sistema de importación incluye tests completos:

- **CallsImportTest**: 23 tests, 67 assertions
- **UsersImportTest**: 23 tests, 73 assertions
- **ImportTest (Calls)**: 12 tests, 23 assertions
- **ImportTest (Users)**: 13 tests, 26 assertions

**Total**: 71 tests, 189 assertions

---

## Troubleshooting

### Problema: "El programa no existe"

**Solución**: Verificar que el código o nombre del programa coincida exactamente con los registros en la base de datos. La búsqueda es case-insensitive pero debe coincidir exactamente.

### Problema: "El año académico no existe"

**Solución**: Verificar que el formato del año académico sea correcto (ej: `2024-2025`). Debe coincidir exactamente con los registros en la base de datos.

### Problema: "Email duplicado"

**Solución**: Verificar que el email no esté ya registrado en la base de datos. Los emails deben ser únicos.

### Problema: "Error durante la subida"

**Solución**: 
- Verificar que el archivo sea Excel (.xlsx, .xls) o CSV (.csv)
- Verificar que el tamaño no exceda 10MB
- Verificar que el archivo no esté corrupto
- Revisar los logs para más detalles

### Problema: Fechas no se importan correctamente

**Solución**: Usar formato `Y-m-d` (2024-09-01) o `d/m/Y` (01/09/2024). Otros formatos pueden no reconocerse.

---

## Mejoras Futuras

1. **Procesamiento en Cola**: Para archivos grandes
2. **Progreso en Tiempo Real**: Mostrar progreso durante la importación
3. **Envío de Emails**: Implementar envío automático de emails con contraseñas generadas
4. **Importación Parcial**: Permitir continuar importación desde donde se quedó
5. **Validación Avanzada**: Validación cruzada entre filas
6. **Plantillas Personalizadas**: Permitir personalizar plantillas según necesidades

---

**Fecha de Creación**: Enero 2026  
**Última Actualización**: Enero 2026  
**Versión**: 1.0
