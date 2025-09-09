# Sistema de Usuario Demo para Gestor de Calificaciones

Este módulo implementa una solución profesional para proporcionar un usuario de demostración tipo profesor dentro del sistema Gestor de Calificaciones. El usuario demo permite a reclutadores, evaluadores o visitantes explorar la funcionalidad del sistema sin preocuparse por realizar cambios permanentes, ya que cualquier modificación será revertida automáticamente de manera periódica.

## Características Principales

- **Usuario Profesor Demo Pre-configurado**: Acceso inmediato con credenciales fijas
- **Restauración Automática**: Los cambios realizados por el usuario demo se revierten periódicamente
- **Sin Dependencias Externas**: No requiere configuración de cron jobs o tareas programadas
- **Tolerancia a Fallos**: Sistema de detección y limpieza automática de usuarios duplicados
- **Fácil Instalación**: Proceso sencillo de configuración en cualquier entorno

## Instalación

### Requisitos Previos

- PHP 7.4 o superior
- MySQL/MariaDB
- Acceso de escritura al directorio del proyecto

### Pasos de Instalación

1. Copie la carpeta `demo` completa a la raíz de su proyecto
2. Asegúrese de que la aplicación pueda acceder a la configuración de base de datos
3. Ejecute el script de creación del usuario demo:

```
https://games-now.skyzerozx.com/Gestor_de_calificaciones/GestorDeCalificaciones/demo/create_demo_teacher.php
```

## Funcionamiento Técnico

El sistema opera mediante los siguientes componentes:

1. **create_demo_teacher.php**: 
   - Crea el usuario profesor demo (`profesordemo`)
   - Establece la contraseña predeterminada (`github123`)
   - Registra los identificadores necesarios para el seguimiento y restauración
   - Detecta y elimina usuarios duplicados automáticamente

2. **auto_restore.php**: 
   - Se ejecuta en cada acceso al sistema
   - Verifica si ha transcurrido el intervalo de tiempo configurado (predeterminado: 1 hora)
   - Llama a la función de restauración cuando sea necesario
   - Registra la actividad de restauración

3. **restore_demo_data.php**:
   - Elimina todas las modificaciones realizadas por el usuario demo
   - Restaura el estado original de las tablas afectadas
   - Proporciona registro detallado del proceso

## Credenciales de Acceso

- **Usuario**: `profesordemo`
- **Contraseña**: `github123`
- **Rol**: Maestro de Escolarizado (MS)

## Configuración Avanzada

### Modificar el Intervalo de Restauración

Edite el archivo `auto_restore.php` y ajuste la siguiente variable:

```php
$restoreInterval = 3600; // Tiempo en segundos (predeterminado: 1 hora)
```

### Herramientas de Mantenimiento

- **simple_fix.php**: Herramienta para limpiar usuarios duplicados y solucionar problemas comunes
- **fix_demo_user.php**: Interfaz para diagnosticar y reparar problemas con el usuario demo

## Seguridad

- El sistema utiliza transacciones para garantizar la integridad de la base de datos
- Las consultas SQL utilizan técnicas de preparación para prevenir inyecciones
- Se implementa manejo de errores completo con registro detallado

## Resolución de Problemas

Si experimenta problemas con el inicio de sesión del usuario demo o encuentra duplicados:

1. Ejecute la herramienta de diagnóstico y corrección:
```
https://games-now.skyzerozx.com/Gestor_de_calificaciones/GestorDeCalificaciones/demo/simple_fix.php
```

2. Verifique los registros de restauración para detectar posibles errores:
```
https://games-now.skyzerozx.com/Gestor_de_calificaciones/GestorDeCalificaciones/demo/restore_log.txt
```

## Archivos del Sistema

- `create_demo_teacher.php`: Creación del usuario demo
- `restore_demo_data.php`: Restauración manual de datos
- `auto_restore.php`: Motor de restauración automática
- `simple_fix.php`: Herramienta de reparación simplificada
- `fix_demo_user.php`: Herramienta de diagnóstico avanzado
- `README.md`: Documentación del sistema

---

*Desarrollado por Renzo (https://github.com/Zekken03) - Septiembre 2023*
