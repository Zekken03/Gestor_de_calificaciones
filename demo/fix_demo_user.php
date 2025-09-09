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
    <title>Verificar Usuario Demo</title>
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
        .credentials {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        h1, h2 {
            color: #333;
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
    <h1>Verificación y Corrección de Usuario Demo</h1>";

// Determinar la ruta correcta al archivo de conexión según el entorno
$basePath = dirname(dirname(__FILE__)); // Directorio padre (raíz del proyecto)

try {
    require_once $basePath . "/conection.php";
    
    // Verificar la conexión
    if (!isset($conexion) || $conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . (isset($conexion) ? $conexion->connect_error : 'No se estableció la conexión'));
    }
    
    // Buscar TODOS los usuarios profesordemo (para detectar duplicados)
    $username = "profesordemo";
    $checkSql = "SELECT u.*, r.level_ as role, r.description as role_description,
                 t.idTeacher, ui.names, ui.lastnamePa, ui.lastnameMa
                FROM users u 
                JOIN roles r ON u.idRole = r.idRole 
                LEFT JOIN teachers t ON u.idUser = t.idUser
                LEFT JOIN usersInfo ui ON u.idUserInfo = ui.idUserInfo
                WHERE u.username = ?
                ORDER BY u.idUser";
    $stmtCheck = $conexion->prepare($checkSql);
    $stmtCheck->bind_param("s", $username);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result->num_rows > 0) {
        echo "<p class='success'>Se encontraron " . $result->num_rows . " usuario(s) 'profesordemo' en la base de datos.</p>";
        
        if ($result->num_rows > 1) {
            echo "<p class='error'>¡ALERTA! Se detectaron usuarios duplicados.</p>";
        }
        
        echo "<h2>Lista de usuarios 'profesordemo':</h2>";
        echo "<table>";
        echo "<tr>
                <th>ID Usuario</th>
                <th>ID Profesor</th>
                <th>Nombre Completo</th>
                <th>Username</th>
                <th>Contraseña Raw</th>
                <th>Rol</th>
                <th>Acción</th>
              </tr>";
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
            $fullName = $row['names'] . ' ' . $row['lastnamePa'] . ' ' . $row['lastnameMa'];
            echo "<tr>
                    <td>" . $row['idUser'] . "</td>
                    <td>" . ($row['idTeacher'] ?? 'N/A') . "</td>
                    <td>" . $fullName . "</td>
                    <td>" . $row['username'] . "</td>
                    <td>" . $row['raw_password'] . "</td>
                    <td>" . $row['role_description'] . " (" . $row['role'] . ")</td>
                    <td>
                        <form method='post' style='display:inline'>
                            <input type='hidden' name='fix_password' value='yes'>
                            <input type='hidden' name='user_id' value='" . $row['idUser'] . "'>
                            <input type='submit' value='Corregir Contraseña'>
                        </form>
                        <form method='post' style='display:inline; margin-left: 5px;'>
                            <input type='hidden' name='delete_user' value='yes'>
                            <input type='hidden' name='user_id' value='" . $row['idUser'] . "'>
                            <input type='submit' value='Eliminar' onclick=\"return confirm('¿Está seguro de eliminar este usuario?');\">
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
        
        // Recuperar el primer usuario para operaciones adicionales
        $row = $users[0];
        
        // Verificar si la contraseña es correcta
        $correctPassword = "github123";
        
        if ($row['raw_password'] === $correctPassword) {
            echo "<p class='success'>La contraseña raw_password coincide con 'github123'. Debería funcionar el inicio de sesión.</p>";
        } else {
            echo "<p class='error'>La contraseña raw_password no coincide con 'github123'.</p>";
            echo "<p>Valor actual: '" . $row['raw_password'] . "'</p>";
            
            // Preguntar si desea corregir la contraseña
            echo "<h2>Corregir la contraseña</h2>";
            echo "<p>¿Desea corregir la contraseña raw_password?</p>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='fix_password' value='yes'>";
            echo "<input type='submit' value='Corregir contraseña'>";
            echo "</form>";
        }
    } else {
        echo "<p class='error'>No se encontró el usuario 'profesordemo' en la base de datos.</p>";
        echo "<p>Primero debe crear el usuario ejecutando el script create_demo_teacher.php.</p>";
    }
    
    // Agregar opción para eliminar duplicados de una vez
    if ($result->num_rows > 1) {
        echo "<h2>Limpiar usuarios duplicados</h2>";
        echo "<p>Esta opción eliminará todos los usuarios duplicados excepto el más antiguo (ID más bajo).</p>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='clean_duplicates' value='yes'>";
        echo "<input type='submit' value='Eliminar todos los duplicados' onclick=\"return confirm('¿Está seguro de eliminar TODOS los usuarios duplicados excepto el primero?');\">";
        echo "</form>";
    }

    // Si se envió el formulario para corregir la contraseña
    if (isset($_POST['fix_password']) && $_POST['fix_password'] === 'yes') {
        // Actualizar la contraseña raw_password
        $newRawPassword = "github123";
        $userId = isset($_POST['user_id']) ? $_POST['user_id'] : $row['idUser'];
        
        $updateSql = "UPDATE users SET raw_password = ? WHERE idUser = ?";
        $stmtUpdate = $conexion->prepare($updateSql);
        $stmtUpdate->bind_param("si", $newRawPassword, $userId);
        
        if ($stmtUpdate->execute()) {
            echo "<p class='success'>¡Contraseña corregida exitosamente para el usuario ID: $userId!</p>";
            echo "<p>Ahora debería poder iniciar sesión con:</p>";
            echo "<div class='credentials'>";
            echo "<p><strong>Username:</strong> profesordemo</p>";
            echo "<p><strong>Password:</strong> github123</p>";
            echo "</div>";
            echo "<p><a href='" . $_SERVER['PHP_SELF'] . "'>Actualizar página</a></p>";
        } else {
            echo "<p class='error'>Error al actualizar la contraseña: " . $conexion->error . "</p>";
        }
    }
    
    // Si se envió el formulario para eliminar un usuario
    if (isset($_POST['delete_user']) && $_POST['delete_user'] === 'yes' && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        
        // Eliminar el usuario y datos relacionados (transacción)
        $conexion->begin_transaction();
        
        try {
            // Primero intentamos eliminar cualquier registro en teacherGroupsSubjects
            $sqlDeleteTGS = "DELETE FROM teacherGroupsSubjects WHERE idTeacher IN (SELECT idTeacher FROM teachers WHERE idUser = ?)";
            $stmtDeleteTGS = $conexion->prepare($sqlDeleteTGS);
            $stmtDeleteTGS->bind_param("i", $userId);
            $stmtDeleteTGS->execute();
            
            // Eliminar criterios de evaluación
            $sqlDeleteCriteria = "DELETE FROM evaluationCriteria WHERE idTeacher IN (SELECT idTeacher FROM teachers WHERE idUser = ?)";
            $stmtDeleteCriteria = $conexion->prepare($sqlDeleteCriteria);
            $stmtDeleteCriteria->bind_param("i", $userId);
            $stmtDeleteCriteria->execute();
            
            // Eliminar registro en teachers
            $sqlDeleteTeacher = "DELETE FROM teachers WHERE idUser = ?";
            $stmtDeleteTeacher = $conexion->prepare($sqlDeleteTeacher);
            $stmtDeleteTeacher->bind_param("i", $userId);
            $stmtDeleteTeacher->execute();
            
            // Almacenar idUserInfo antes de eliminar el usuario
            $sqlGetUserInfo = "SELECT idUserInfo FROM users WHERE idUser = ?";
            $stmtGetUserInfo = $conexion->prepare($sqlGetUserInfo);
            $stmtGetUserInfo->bind_param("i", $userId);
            $stmtGetUserInfo->execute();
            $userInfoResult = $stmtGetUserInfo->get_result();
            $idUserInfo = null;
            if ($userInfoRow = $userInfoResult->fetch_assoc()) {
                $idUserInfo = $userInfoRow['idUserInfo'];
            }
            
            // Eliminar de la tabla users
            $sqlDeleteUser = "DELETE FROM users WHERE idUser = ?";
            $stmtDeleteUser = $conexion->prepare($sqlDeleteUser);
            $stmtDeleteUser->bind_param("i", $userId);
            $stmtDeleteUser->execute();
            
            // Eliminar de usersInfo si tenemos el ID
            if ($idUserInfo) {
                $sqlDeleteUserInfo = "DELETE FROM usersInfo WHERE idUserInfo = ?";
                $stmtDeleteUserInfo = $conexion->prepare($sqlDeleteUserInfo);
                $stmtDeleteUserInfo->bind_param("i", $idUserInfo);
                $stmtDeleteUserInfo->execute();
            }
            
            $conexion->commit();
            echo "<p class='success'>Usuario ID: $userId eliminado correctamente.</p>";
            echo "<p><a href='" . $_SERVER['PHP_SELF'] . "'>Actualizar página</a></p>";
            
        } catch (Exception $e) {
            $conexion->rollback();
            echo "<p class='error'>Error al eliminar usuario: " . $e->getMessage() . "</p>";
        }
    }
    
    // Si se envió el formulario para limpiar todos los duplicados
    if (isset($_POST['clean_duplicates']) && $_POST['clean_duplicates'] === 'yes') {
        $conexion->begin_transaction();
        
        try {
            // Obtener todos los IDs de usuario excepto el primero
            $sqlGetDuplicates = "SELECT idUser FROM users WHERE username = ? ORDER BY idUser ASC LIMIT 1, 999";
            $stmtGetDuplicates = $conexion->prepare($sqlGetDuplicates);
            $stmtGetDuplicates->bind_param("s", $username);
            $stmtGetDuplicates->execute();
            $duplicatesResult = $stmtGetDuplicates->get_result();
            
            $deletedCount = 0;
            
            while ($duplicateRow = $duplicatesResult->fetch_assoc()) {
                $duplicateId = $duplicateRow['idUser'];
                
                // Eliminar registros relacionados para este usuario (mismo código que arriba)
                // Primero intentamos eliminar cualquier registro en teacherGroupsSubjects
                $sqlDeleteTGS = "DELETE FROM teacherGroupsSubjects WHERE idTeacher IN (SELECT idTeacher FROM teachers WHERE idUser = ?)";
                $stmtDeleteTGS = $conexion->prepare($sqlDeleteTGS);
                $stmtDeleteTGS->bind_param("i", $duplicateId);
                $stmtDeleteTGS->execute();
                
                // Eliminar criterios de evaluación
                $sqlDeleteCriteria = "DELETE FROM evaluationCriteria WHERE idTeacher IN (SELECT idTeacher FROM teachers WHERE idUser = ?)";
                $stmtDeleteCriteria = $conexion->prepare($sqlDeleteCriteria);
                if ($stmtDeleteCriteria === false) {
                    throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
                }
                $stmtDeleteCriteria->bind_param("i", $duplicateId);
                $stmtDeleteCriteria->execute();
                
                // Eliminar registro en teachers
                $sqlDeleteTeacher = "DELETE FROM teachers WHERE idUser = ?";
                $stmtDeleteTeacher = $conexion->prepare($sqlDeleteTeacher);
                if ($stmtDeleteTeacher === false) {
                    throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
                }
                $stmtDeleteTeacher->bind_param("i", $duplicateId);
                $stmtDeleteTeacher->execute();
                
                // Almacenar idUserInfo antes de eliminar el usuario
                $sqlGetUserInfo = "SELECT idUserInfo FROM users WHERE idUser = ?";
                $stmtGetUserInfo = $conexion->prepare($sqlGetUserInfo);
                if ($stmtGetUserInfo === false) {
                    throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
                }
                $stmtGetUserInfo->bind_param("i", $duplicateId);
                $stmtGetUserInfo->execute();
                $userInfoResult = $stmtGetUserInfo->get_result();
                $idUserInfo = null;
                if ($userInfoRow = $userInfoResult->fetch_assoc()) {
                    $idUserInfo = $userInfoRow['idUserInfo'];
                }
                
                // Eliminar de la tabla users
                $sqlDeleteUser = "DELETE FROM users WHERE idUser = ?";
                $stmtDeleteUser = $conexion->prepare($sqlDeleteUser);
                if ($stmtDeleteUser === false) {
                    throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
                }
                $stmtDeleteUser->bind_param("i", $duplicateId);
                $stmtDeleteUser->execute();
                
                // Eliminar de usersInfo si tenemos el ID
                if ($idUserInfo) {
                    $sqlDeleteUserInfo = "DELETE FROM usersInfo WHERE idUserInfo = ?";
                    $stmtDeleteUserInfo = $conexion->prepare($sqlDeleteUserInfo);
                    if ($stmtDeleteUserInfo === false) {
                        throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
                    }
                    $stmtDeleteUserInfo->bind_param("i", $idUserInfo);
                    $stmtDeleteUserInfo->execute();
                }
                
                $deletedCount++;
            }
            
            $conexion->commit();
            echo "<p class='success'>Se eliminaron $deletedCount usuarios duplicados.</p>";
            echo "<p><a href='" . $_SERVER['PHP_SELF'] . "'>Actualizar página</a></p>";
            
        } catch (Exception $e) {
            $conexion->rollback();
            echo "<p class='error'>Error al eliminar duplicados: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body>
</html>";
?>
