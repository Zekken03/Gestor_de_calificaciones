<?php
// Establecer encabezados para mostrar correctamente en el navegador
header('Content-Type: text/html; charset=utf-8');

// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Eliminar Usuarios Demo Duplicados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .warning {
            color: orange;
            font-weight: bold;
        }
        pre {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 15px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Eliminar Usuarios Demo Duplicados - Versión Simple</h1>";

// Determinar la ruta correcta al archivo de conexión según el entorno
$basePath = dirname(dirname(__FILE__)); // Directorio padre (raíz del proyecto)

try {
    require_once $basePath . "/conection.php";
    
    // Verificar la conexión
    if (!isset($conexion) || $conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . (isset($conexion) ? $conexion->connect_error : 'No se estableció la conexión'));
    }
    
    echo "<h2>1. Identificando usuarios profesordemo</h2>";
    
    // Listar todos los usuarios con el nombre de usuario 'profesordemo'
    $username = "profesordemo";
    $result = $conexion->query("SELECT idUser, idUserInfo FROM users WHERE username = '$username' ORDER BY idUser ASC");
    
    if (!$result) {
        throw new Exception("Error en la consulta: " . $conexion->error);
    }
    
    $count = $result->num_rows;
    echo "<p>Se encontraron $count usuarios con el nombre 'profesordemo'.</p>";
    
    if ($count == 0) {
        echo "<p class='error'>No hay usuarios 'profesordemo' para procesar.</p>";
        exit;
    }
    
    if ($count == 1) {
        echo "<p class='success'>Solo hay un usuario 'profesordemo'. No hay duplicados para eliminar.</p>";
    }
    
    // Guardar el ID del primer usuario (el que conservaremos)
    $firstRow = $result->fetch_assoc();
    $firstUserId = $firstRow['idUser'];
    $firstUserInfoId = $firstRow['idUserInfo'];
    
    echo "<p>El usuario principal tiene ID: $firstUserId (este no se eliminará)</p>";
    
    if ($count > 1) {
        echo "<h2>2. Eliminando usuarios duplicados</h2>";
        
        // Obtener todos los IDs excepto el primero
        $duplicateResult = $conexion->query("SELECT idUser, idUserInfo FROM users WHERE username = '$username' AND idUser != $firstUserId ORDER BY idUser ASC");
        
        if (!$duplicateResult) {
            throw new Exception("Error al buscar duplicados: " . $conexion->error);
        }
        
        $deletedCount = 0;
        $errors = [];
        
        while ($dupRow = $duplicateResult->fetch_assoc()) {
            $dupUserId = $dupRow['idUser'];
            $dupUserInfoId = $dupRow['idUserInfo'];
            
            echo "<p>Procesando usuario duplicado ID: $dupUserId...</p>";
            
            // Intentar eliminar en el orden correcto para evitar problemas de clave externa
            
            // 1. Obtener el ID de profesor
            $teacherResult = $conexion->query("SELECT idTeacher FROM teachers WHERE idUser = $dupUserId");
            
            if (!$teacherResult) {
                $errors[] = "Error al buscar profesor para usuario $dupUserId: " . $conexion->error;
                continue;
            }
            
            if ($teacherResult->num_rows > 0) {
                $teacherRow = $teacherResult->fetch_assoc();
                $teacherId = $teacherRow['idTeacher'];
                
                // 2. Eliminar asignaciones de grupos
                $deleteAssignments = $conexion->query("DELETE FROM teacherGroupsSubjects WHERE idTeacher = $teacherId");
                if (!$deleteAssignments) {
                    echo "<p class='warning'>Error o no hay asignaciones para eliminar: " . $conexion->error . "</p>";
                }
                
                // 3. Eliminar criterios de evaluación
                $deleteCriteria = $conexion->query("DELETE FROM evaluationCriteria WHERE idTeacher = $teacherId");
                if (!$deleteCriteria) {
                    echo "<p class='warning'>Error o no hay criterios para eliminar: " . $conexion->error . "</p>";
                }
                
                // 4. Eliminar calificaciones relacionadas
                $deleteGrades = $conexion->query("DELETE FROM grades WHERE idTeacherGroupSubject IN (SELECT idTeacherGroupSubject FROM teacherGroupsSubjects WHERE idTeacher = $teacherId)");
                if (!$deleteGrades) {
                    echo "<p class='warning'>Error o no hay calificaciones para eliminar: " . $conexion->error . "</p>";
                }
                
                // 5. Eliminar profesor
                $deleteTeacher = $conexion->query("DELETE FROM teachers WHERE idTeacher = $teacherId");
                if (!$deleteTeacher) {
                    $errors[] = "Error al eliminar profesor $teacherId: " . $conexion->error;
                    continue;
                }
            }
            
            // 6. Eliminar usuario
            $deleteUser = $conexion->query("DELETE FROM users WHERE idUser = $dupUserId");
            if (!$deleteUser) {
                $errors[] = "Error al eliminar usuario $dupUserId: " . $conexion->error;
                continue;
            }
            
            // 7. Eliminar información de usuario
            $deleteUserInfo = $conexion->query("DELETE FROM usersInfo WHERE idUserInfo = $dupUserInfoId");
            if (!$deleteUserInfo) {
                $errors[] = "Error al eliminar información de usuario $dupUserInfoId: " . $conexion->error;
            }
            
            $deletedCount++;
            echo "<p class='success'>Usuario ID: $dupUserId eliminado correctamente.</p>";
        }
        
        echo "<h2>3. Resumen</h2>";
        echo "<p>Se eliminaron $deletedCount usuarios duplicados.</p>";
        
        if (count($errors) > 0) {
            echo "<h3>Errores encontrados:</h3>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li class='error'>$error</li>";
            }
            echo "</ul>";
        }
    }
    
    // Verificar y actualizar la contraseña del usuario principal
    echo "<h2>4. Verificando la contraseña del usuario principal</h2>";
    
    $passwordResult = $conexion->query("SELECT raw_password FROM users WHERE idUser = $firstUserId");
    if (!$passwordResult) {
        throw new Exception("Error al verificar la contraseña: " . $conexion->error);
    }
    
    $passwordRow = $passwordResult->fetch_assoc();
    $currentPassword = $passwordRow['raw_password'];
    
    echo "<p>Contraseña actual: $currentPassword</p>";
    
    if ($currentPassword != "github123") {
        echo "<p class='warning'>La contraseña no es 'github123'. Actualizando...</p>";
        
        $updatePassword = $conexion->query("UPDATE users SET raw_password = 'github123' WHERE idUser = $firstUserId");
        if (!$updatePassword) {
            throw new Exception("Error al actualizar la contraseña: " . $conexion->error);
        }
        
        echo "<p class='success'>Contraseña actualizada correctamente a 'github123'.</p>";
    } else {
        echo "<p class='success'>La contraseña ya es correcta ('github123').</p>";
    }
    
    // Mostrar credenciales finales
    echo "<h2>5. Credenciales para iniciar sesión</h2>";
    echo "<div style='background-color: #f0f0f0; border: 1px solid #ccc; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Usuario:</strong> profesordemo</p>";
    echo "<p><strong>Contraseña:</strong> github123</p>";
    echo "</div>";
    
    echo "<p class='success'>¡El proceso ha finalizado! Ahora debería poder iniciar sesión con las credenciales anteriores.</p>";
    echo "<p><a href='/Gestor_de_calificaciones/index.php'>Ir a la página de inicio de sesión</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body>
</html>";
?>
