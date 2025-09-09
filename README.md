# Gestor de Calificaciones

Sistema integral para la gestión académica que facilita el seguimiento, evaluación y análisis del rendimiento escolar. Diseñado para instituciones educativas, permite a administradores, profesores y potencialmente estudiantes interactuar con la información académica de manera eficiente y segura.


## Características Principales

### Para Administradores
- Gestión completa de usuarios (profesores y estudiantes)
- Administración de años escolares y periodos académicos
- Configuración de grupos y asignaturas
- Asignación de profesores a materias específicas
- Generación de reportes administrativos

### Para Profesores
- Panel de control con estadísticas y análisis visual
- Registro y modificación de calificaciones
- Gestión de criterios de evaluación personalizados
- Visualización de estudiantes por grupo y asignatura
- Análisis de rendimiento académico con gráficos dinámicos

## Tecnologías Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap
- **Backend**: PHP
- **Base de Datos**: MySQL
- **Gráficos**: Chart.js para visualización de datos
- **Seguridad**: Sistema de autenticación y control de sesiones

## Estructura del Proyecto

```
Gestor_de_calificaciones/
│
├── admin/                 # Funcionalidades administrativas
├── teachers/              # Módulo para profesores
├── css/                   # Estilos CSS organizados por módulo
├── js/                    # Scripts JavaScript
├── layouts/               # Plantillas y componentes reutilizables
├── img/                   # Recursos gráficos
├── demo/                  # Sistema de usuario de demostración
├── GUIA_PROFESORES.md     # Documentación para profesores
└── README.md              # Este archivo
```

## Guías de Usuario

Para facilitar el uso del sistema, se proporcionan guías detalladas para cada tipo de usuario:

- [Guía para Administradores](GUIA_ADMINISTRADORES.md) *(próximamente)*
- [Guía para Profesores](GUIA_PROFESORES.md)

## Modo de Demostración

Para permitir a reclutadores y evaluadores explorar el sistema sin necesidad de configuración, se incluye un **usuario de demostración** con rol de profesor:

- **Usuario**: `profesordemo`
- **Contraseña**: `github123`
Ingrese en: https://games-now.skyzerozx.com/Gestor_de_calificaciones/

Para resetear los cambios del usuario, ingresar a: https://games-now.skyzerozx.com/Gestor_de_calificaciones/demo/restore_demo_data.php

Este usuario cuenta con un sistema de auto-restauración que revierte cualquier cambio realizado después de un periodo determinado, manteniendo la integridad del entorno de demostración.

Para más información sobre el sistema de demostración:
[Documentación del Sistema Demo](demo/README.md)

## Instalación

### Requisitos Previos
- Servidor web con PHP 7.4+
- MySQL 5.7+ o MariaDB 10.3+
- Extensiones PHP: mysqli, json, session

### Pasos de Instalación

1. Clone o descargue este repositorio en su servidor web
   ```
   git clone https://github.com/Zekken03/Gestor_de_calificaciones.git
   ```

2. Importe la estructura de base de datos desde el archivo SQL
   ```
   mysql -u usuario -p nombre_base < db_structure.sql
   ```

3. Configure los parámetros de conexión a la base de datos en `conection.php`
   ```php
   $conexion = new mysqli("localhost", "usuario", "contraseña", "nombre_base");
   ```

4. Acceda al sistema a través de su navegador
   ```
   https://games-now.skyzerozx.com/Gestor_de_calificaciones/
   ```

   O si lo instaló localmente:
   ```
   http://localhost/Gestor_de_calificaciones/
   ```

5. Inicie sesión con el usuario administrador predeterminado
   - Usuario: `admin`
   - Contraseña: `admin123`
   *(Cambie estas credenciales inmediatamente después de iniciar sesión)*

## Funcionalidades Destacadas

### Panel de Control Interactivo
El sistema ofrece paneles de control personalizados según el rol del usuario, con estadísticas en tiempo real y accesos rápidos a las funciones más utilizadas.

### Sistema de Evaluación Flexible
Permite configurar diferentes criterios de evaluación por materia, con ponderaciones personalizables y múltiples periodos de evaluación.

### Análisis Visual de Datos
Incorpora gráficos dinámicos para visualizar tendencias de rendimiento, porcentajes de aprobación y comparativas entre grupos.

### Control de Acceso por Roles
Implementa un sistema de permisos basado en roles que garantiza que cada usuario solo acceda a la información y funciones pertinentes a su posición.

## Seguridad

El sistema implementa diversas medidas de seguridad:

- Autenticación robusta de usuarios
- Protección contra inyección SQL
- Control de sesiones
- Validación de formularios
- Registro de actividades críticas

## Contribuciones

Este proyecto está abierto a contribuciones. Si desea colaborar:

1. Haga fork del repositorio
2. Cree una rama para su función (`git checkout -b feature/nueva-funcion`)
3. Realice sus cambios y haga commit (`git commit -am 'Añadir nueva función'`)
4. Envíe los cambios a su rama (`git push origin feature/nueva-funcion`)
5. Abra un Pull Request

## Licencia

Este proyecto está bajo licencia MIT. Consulte el archivo LICENSE para más detalles.

---

*Desarrollado por Renzo (https://github.com/Zekken03) - © 2025*
