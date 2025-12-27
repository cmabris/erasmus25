# Plan de Desarrollo: Paso 3.5.1 - Dashboard de Administración

Este documento establece el plan detallado para desarrollar el Dashboard de Administración del panel de control (Back-office) de la aplicación Erasmus+ Centro (Murcia).

## Objetivo

Crear un dashboard moderno y funcional que proporcione una visión general del estado de la aplicación, estadísticas clave y accesos rápidos a las secciones principales del panel de administración.

---

## Pasos de Desarrollo

### **Paso 1: Estructura Base del Dashboard**

#### 1.1. Crear componente Livewire Admin\Dashboard
- [ ] Crear archivo `app/Livewire/Admin/Dashboard.php`
- [ ] Implementar clase con propiedades públicas para estadísticas
- [ ] Crear método `mount()` para cargar datos iniciales
- [ ] Implementar método `render()` con layout de administración
- [ ] Añadir autorización básica (verificar que el usuario tenga permisos de administración)

#### 1.2. Crear vista del Dashboard
- [ ] Crear archivo `resources/views/livewire/admin/dashboard.blade.php`
- [ ] Estructura base con layout de administración (`layouts.app`)
- [ ] Sección de encabezado con título y breadcrumbs
- [ ] Grid responsive para organizar las secciones

#### 1.3. Configurar ruta de administración
- [ ] Crear grupo de rutas `/admin` en `routes/web.php`
- [ ] Añadir middleware `auth` y verificación de permisos
- [ ] Definir ruta `/admin` que apunte al componente `Admin\Dashboard`
- [ ] Actualizar sidebar para incluir enlace al dashboard

---

### **Paso 2: Componentes de Estadísticas (Stat Cards)**

#### 2.1. Crear componente de tarjeta de estadística mejorado
- [ ] Revisar componente existente `x-ui.stat-card`
- [ ] Mejorar si es necesario para dashboard (añadir animaciones, efectos hover)
- [ ] Añadir variantes específicas para dashboard (más grandes, con gradientes)
- [ ] Implementar soporte para iconos personalizados y colores temáticos

#### 2.2. Implementar cálculo de estadísticas en el componente
- [ ] **Programas activos**: Contar programas con `is_active = true`
- [ ] **Convocatorias abiertas**: Contar convocatorias con `status = 'abierta'` y `published_at IS NOT NULL`
- [ ] **Convocatorias cerradas**: Contar convocatorias con `status = 'cerrada'`
- [ ] **Noticias publicadas este mes**: Contar noticias con `status = 'publicado'` y `published_at` en el mes actual
- [ ] **Documentos disponibles**: Contar documentos con `is_active = true`
- [ ] **Eventos próximos**: Contar eventos con `start_date >= hoy` y `is_public = true`

#### 2.3. Crear métodos de carga optimizados
- [ ] Implementar eager loading donde sea necesario
- [ ] Usar consultas eficientes con `count()` y `whereHas()` cuando aplique
- [ ] Considerar caché para estadísticas que no cambian frecuentemente

---

### **Paso 3: Sección de Estadísticas Principales**

#### 3.1. Grid de tarjetas de estadísticas
- [ ] Crear grid responsive (1 columna móvil, 2 tabletas, 3-4 desktop)
- [ ] Implementar 6 tarjetas de estadísticas principales:
  - Total de programas activos (icono: `academic-cap` o `book-open`)
  - Convocatorias abiertas (icono: `document-text`, color: success)
  - Convocatorias cerradas (icono: `lock-closed`, color: neutral)
  - Noticias publicadas este mes (icono: `newspaper`, color: info)
  - Documentos disponibles (icono: `folder`, color: primary)
  - Eventos próximos (icono: `calendar`, color: warning)
- [ ] Añadir efectos hover y transiciones suaves
- [ ] Implementar colores temáticos según el tipo de estadística

#### 3.2. Añadir tendencias y comparaciones
- [ ] Comparar con el mes anterior (opcional, para noticias)
- [ ] Mostrar indicadores de tendencia (↑, ↓, →) cuando sea relevante
- [ ] Añadir tooltips con información adicional

---

### **Paso 4: Accesos Rápidos (Quick Actions)**

#### 4.1. Crear componente de acceso rápido
- [ ] Crear componente `x-admin.quick-action` o reutilizar `x-ui.card`
- [ ] Diseño con icono grande, título y descripción
- [ ] Efecto hover con elevación
- [ ] Enlaces a secciones principales del admin

#### 4.2. Implementar grid de accesos rápidos
- [ ] Crear sección "Accesos Rápidos" o "Acciones Rápidas"
- [ ] Grid responsive con tarjetas clickeables
- [ ] Incluir accesos a:
  - Crear nueva convocatoria (`/admin/convocatorias/create`)
  - Crear nueva noticia (`/admin/noticias/create`)
  - Crear nuevo documento (`/admin/documentos/create`)
  - Crear nuevo evento (`/admin/eventos/create`)
  - Gestionar programas (`/admin/programas`)
  - Gestionar usuarios (`/admin/usuarios`) - solo para super-admin
- [ ] Mostrar/ocultar según permisos del usuario

---

### **Paso 5: Actividad Reciente**

#### 5.1. Crear componente de lista de actividad reciente
- [ ] Crear componente `x-admin.activity-list` o usar tabla de Flux UI
- [ ] Mostrar últimas acciones realizadas en el sistema
- [ ] Usar modelo `AuditLog` si está disponible, o crear consultas a modelos principales

#### 5.2. Implementar sección de actividad reciente
- [ ] Mostrar últimas 5-10 actividades:
  - Convocatorias creadas/modificadas recientemente
  - Noticias publicadas recientemente
  - Documentos añadidos recientemente
  - Eventos creados recientemente
- [ ] Formato: Tipo de acción, título, usuario, fecha/hora
- [ ] Enlaces a los elementos correspondientes
- [ ] Badge de estado cuando aplique

---

### **Paso 6: Gráficos de Actividad (Opcional pero Recomendado)**

#### 6.1. Evaluar librerías de gráficos
- [ ] Investigar opciones: Chart.js, Alpine.js con Chart.js, Livewire Charts, etc.
- [ ] Decidir librería según compatibilidad con Livewire y Flux UI
- [ ] Considerar rendimiento y tamaño del bundle

#### 6.2. Implementar gráfico de actividad mensual
- [ ] Gráfico de líneas o barras mostrando:
  - Convocatorias creadas por mes (últimos 6-12 meses)
  - Noticias publicadas por mes
  - Documentos añadidos por mes
- [ ] Usar colores temáticos Erasmus+
- [ ] Responsive y accesible

#### 6.3. Implementar gráfico de distribución
- [ ] Gráfico de pastel o barras mostrando:
  - Distribución de convocatorias por programa
  - Distribución de convocatorias por estado
  - Distribución de noticias por programa

---

### **Paso 7: Sección de Alertas y Notificaciones**

#### 7.1. Crear componente de alerta
- [ ] Reutilizar componentes Flux UI de alertas
- [ ] Mostrar alertas importantes:
  - Convocatorias próximas a cerrar (fecha límite < 7 días)
  - Convocatorias sin publicar (borradores antiguos)
  - Eventos próximos sin confirmar
  - Documentos pendientes de revisión

#### 7.2. Implementar sección de alertas
- [ ] Lista de alertas con prioridad visual
- [ ] Enlaces directos a elementos que requieren atención
- [ ] Opción para marcar como vistas (opcional)

---

### **Paso 8: Mejoras de UX y Diseño**

#### 8.1. Añadir animaciones y transiciones
- [ ] Animaciones de entrada para tarjetas (fade-in, slide-up)
- [ ] Transiciones suaves en hover
- [ ] Loading states mientras se cargan datos

#### 8.2. Implementar responsive design completo
- [ ] Verificar en móviles (< 640px)
- [ ] Verificar en tabletas (640px - 1024px)
- [ ] Verificar en desktop (> 1024px)
- [ ] Ajustar grid y espaciados según breakpoints

#### 8.3. Mejorar accesibilidad
- [ ] Añadir aria-labels donde sea necesario
- [ ] Verificar contraste de colores
- [ ] Asegurar navegación por teclado
- [ ] Añadir skip links si es necesario

---

### **Paso 9: Optimización y Rendimiento**

#### 9.1. Optimizar consultas
- [ ] Revisar consultas N+1
- [ ] Implementar eager loading donde sea necesario
- [ ] Usar `select()` para limitar columnas cuando sea posible
- [ ] Considerar índices de base de datos

#### 9.2. Implementar caché
- [ ] Caché para estadísticas que no cambian frecuentemente
- [ ] Invalidar caché cuando se crean/modifican elementos relevantes
- [ ] TTL apropiado (ej: 5-15 minutos para estadísticas)

#### 9.3. Lazy loading para secciones pesadas
- [ ] Considerar lazy loading para gráficos si son pesados
- [ ] Cargar actividad reciente de forma asíncrona si es necesario

---

### **Paso 10: Internacionalización**

#### 10.1. Traducir textos del dashboard
- [ ] Añadir traducciones en `lang/es/common.php` y `lang/en/common.php`
- [ ] Traducir títulos de secciones
- [ ] Traducir etiquetas de estadísticas
- [ ] Traducir textos de accesos rápidos

#### 10.2. Formatear fechas y números
- [ ] Usar helpers de Carbon para fechas
- [ ] Formatear números según locale
- [ ] Asegurar que todo el contenido sea traducible

---

### **Paso 11: Tests**

#### 11.1. Tests del componente Dashboard
- [ ] Test de renderizado básico
- [ ] Test de cálculo de estadísticas
- [ ] Test de autorización (verificar que usuarios sin permisos no puedan acceder)
- [ ] Test de carga de datos

#### 11.2. Tests de integración
- [ ] Test de rutas de administración
- [ ] Test de navegación desde dashboard
- [ ] Test de permisos en accesos rápidos

---

### **Paso 12: Documentación**

#### 12.1. Documentar el componente
- [ ] Crear documentación técnica del componente Dashboard
- [ ] Documentar propiedades y métodos públicos
- [ ] Documentar estructura de la vista
- [ ] Incluir ejemplos de uso

#### 12.2. Actualizar documentación general
- [ ] Actualizar `docs/README.md` con referencia al dashboard
- [ ] Actualizar `docs/planificacion_pasos.md` marcando paso 3.5.1 como completado
- [ ] Crear archivo `docs/pasos/paso-3.5.1.md` con resumen del desarrollo

---

## Estructura de Archivos a Crear/Modificar

```
app/Livewire/Admin/
  └── Dashboard.php (NUEVO)

resources/views/livewire/admin/
  └── dashboard.blade.php (NUEVO)

resources/views/components/admin/
  ├── quick-action.blade.php (NUEVO - opcional)
  └── activity-list.blade.php (NUEVO - opcional)

routes/
  └── web.php (MODIFICAR - añadir rutas admin)

resources/views/components/layouts/app/
  └── sidebar.blade.php (MODIFICAR - añadir menú admin)

lang/
  ├── es/common.php (MODIFICAR - añadir traducciones)
  └── en/common.php (MODIFICAR - añadir traducciones)

tests/Feature/Livewire/Admin/
  └── DashboardTest.php (NUEVO)
```

---

## Consideraciones de Diseño

### Paleta de Colores
- Usar colores Erasmus+ (azul #003399 y dorado) para elementos destacados
- Mantener consistencia con el diseño público
- Usar colores semánticos para estados (success, warning, danger, info)

### Tipografía
- Mantener consistencia con Flux UI
- Títulos grandes y claros
- Jerarquía visual clara

### Espaciado
- Usar sistema de espaciado de Tailwind (gap-4, gap-6, etc.)
- Padding consistente en tarjetas y secciones
- Márgenes apropiados entre secciones

---

## Preparación para Futuras Características

El dashboard debe estar preparado para añadir fácilmente:

1. **Widgets personalizables**: Estructura que permita añadir/quitar widgets
2. **Filtros de fecha**: Para ver estadísticas de períodos específicos
3. **Exportación de reportes**: Botones para exportar datos a PDF/Excel
4. **Notificaciones en tiempo real**: Integración con sistema de notificaciones
5. **Gráficos avanzados**: Más tipos de visualizaciones
6. **Dashboard personalizado por rol**: Diferentes vistas según permisos

---

## Priorización de Implementación

### Fase 1: MVP (Mínimo Producto Viable)
- Pasos 1, 2, 3, 4, 10.1, 11.1
- Dashboard funcional con estadísticas básicas y accesos rápidos

### Fase 2: Mejoras UX
- Pasos 5, 7, 8
- Actividad reciente y alertas

### Fase 3: Visualizaciones Avanzadas
- Paso 6
- Gráficos de actividad

### Fase 4: Optimización y Pulido
- Pasos 9, 10.2, 11.2, 12
- Optimización, tests completos y documentación

---

## Notas Importantes

1. **Reutilización**: Aprovechar componentes UI existentes (`x-ui.card`, `x-ui.stat-card`, `x-ui.button`, etc.)
2. **Consistencia**: Mantener el mismo estilo y estructura que el área pública
3. **Performance**: Considerar rendimiento desde el inicio, especialmente con muchos datos
4. **Seguridad**: Verificar permisos en cada sección del dashboard
5. **Escalabilidad**: Diseñar pensando en que se añadirán más características

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Listo para implementación


