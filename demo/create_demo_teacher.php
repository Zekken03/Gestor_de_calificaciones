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
    <title>Crear Usuario Demo</title>
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
    </style>
</head>
<body>
    <h1>Configuración de Usuario Profesor Demo</h1>";

// Determinar la ruta correcta al archivo de conexión según el entorno
$basePath = dirname(dirname(__FILE__)); // Directorio padre (raíz del proyecto)
require_once $basePath . "/conection.php";

// Datos del profesor demo
$name = "Profesor";
$lastnamePa = "Demo";
$lastnameMa = "GitHub";
$gender = "M";
$typeTeacher = 1; // Asumiendo que 1 es para profesor regular
$ine = "DEMO123456789";
$profesionalID = "PROFGITHUB2023";
$phone = "9876543210";
$email = "demo@github.com";
$address = "Demo Street 123";
$username = "profesordemo";
$rawPassword = "github123"; // Contraseña en texto plano
$password = password_hash($rawPassword, PASSWORD_DEFAULT);

try {
    // Verificar si el usuario ya existe Y eliminar duplicados si hay más de uno
    // Primero, contamos cuántos usuarios profesordemo existen
    $countSql = "SELECT COUNT(*) as total FROM users WHERE username = ?";
    $stmtCount = $conexion->prepare($countSql);
    $stmtCount->bind_param("s", $username);
    $stmtCount->execute();
    $countResult = $stmtCount->get_result();
    $countRow = $countResult->fetch_assoc();
    $totalUsers = $countRow['total'];
    
    // Si hay más de uno, eliminar todos excepto el más antiguo
    if ($totalUsers > 1) {
        echo "<p class='error'>Se detectaron " . $totalUsers . " usuarios 'profesordemo' duplicados. Limpiando duplicados...</p>";
        
        // Obtener el ID del usuario más antiguo (el que queremos conservar)
        $oldestSql = "SELECT idUser FROM users WHERE username = ? ORDER BY idUser ASC LIMIT 1";
        $stmtOldest = $conexion->prepare($oldestSql);
        $stmtOldest->bind_param("s", $username);
        $stmtOldest->execute();
        $oldestResult = $stmtOldest->get_result();
        $oldestRow = $oldestResult->fetch_assoc();
        $oldestId = $oldestRow['idUser'];
        
        // Eliminar todos los duplicados excepto el más antiguo
        // Primero obtenemos la lista de IDs a eliminar
        $dupSql = "SELECT idUser FROM users WHERE username = ? AND idUser != ? ORDER BY idUser";
        $stmtDup = $conexion->prepare($dupSql);
        $stmtDup->bind_param("si", $username, $oldestId);
        $stmtDup->execute();
        $dupResult = $stmtDup->get_result();
        
        $deletedCount = 0;
        while ($dupRow = $dupResult->fetch_assoc()) {
            $dupId = $dupRow['idUser'];
            
            // Iniciar una transacción para cada eliminación
            $conexion->begin_transaction();
            
            try {
                // Eliminar relaciones en teacherGroupsSubjects
                $sqlDeleteTGS = "DELETE FROM teacherGroupsSubjects WHERE idTeacher IN (SELECT idTeacher FROM teachers WHERE idUser = ?)";
                $stmtDeleteTGS = $conexion->prepare($sqlDeleteTGS);
                if ($stmtDeleteTGS === false) {
                    throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
                }
                $stmtDeleteTGS->bind_param("i", $dupId);
                $stmtDeleteTGS->execute();
                
                // Eliminar criterios de evaluación
                $sqlDeleteCriteria = "DELETE FROM evaluationCriteria WHERE idTeacher IN (SELECT idTeacher FROM teachers WHERE idUser = ?)";
                $stmtDeleteCriteria = $conexion->prepare($sqlDeleteCriteria);
                if ($stmtDeleteCriteria === false) {
                    throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
                }
                $stmtDeleteCriteria->bind_param("i", $dupId);
                $stmtDeleteCriteria->execute();
                
                // Eliminar registro en teachers
                $sqlDeleteTeacher = "DELETE FROM teachers WHERE idUser = ?";
                $stmtDeleteTeacher = $conexion->prepare($sqlDeleteTeacher);
                if ($stmtDeleteTeacher === false) {
                    throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
                }
                $stmtDeleteTeacher->bind_param("i", $dupId);
                $stmtDeleteTeacher->execute();
                
                // Obtener idUserInfo antes de eliminar el usuario
                $sqlGetUserInfo = "SELECT idUserInfo FROM users WHERE idUser = ?";
                $stmtGetUserInfo = $conexion->prepare($sqlGetUserInfo);
                if ($stmtGetUserInfo === false) {
                    throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
                }
                $stmtGetUserInfo->bind_param("i", $dupId);
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
                $stmtDeleteUser->bind_param("i", $dupId);
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
                
                $conexion->commit();
                $deletedCount++;
                
            } catch (Exception $e) {
                $conexion->rollback();
                echo "<p class='error'>Error al eliminar duplicado: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<p class='success'>Se eliminaron $deletedCount usuarios duplicados.</p>";
    }
    
    // Ahora verificar el usuario (que debe ser único)
    $checkSql = "SELECT u.idUser 
                FROM users u 
                WHERE u.username = ?";
    $stmtCheck = $conexion->prepare($checkSql);
    if ($stmtCheck === false) {
        throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
    }
    $stmtCheck->bind_param("s", $username);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    // Si el usuario ya existe, obtenemos sus datos
    if ($result->num_rows > 0) {
        echo "<p class='success'>El usuario profesor demo existe y es único.</p>";
        $row = $result->fetch_assoc();
        $idUser = $row['idUser'];
        
        // Buscar el ID del profesor
        $sqlTeacher = "SELECT idTeacher, idUserInfo FROM teachers WHERE idUser = ?";
        $stmtTeacher = $conexion->prepare($sqlTeacher);
        $stmtTeacher->bind_param("i", $idUser);
        $stmtTeacher->execute();
        $resultTeacher = $stmtTeacher->get_result();
        
        if ($resultTeacher->num_rows > 0) {
            $rowTeacher = $resultTeacher->fetch_assoc();
            $idTeacher = $rowTeacher['idTeacher'];
            $idUserInfo = $rowTeacher['idUserInfo'];
            
            // Guardar los IDs para la restauración posterior
            file_put_contents(__DIR__ . "/demo_teacher_id.txt", $idTeacher);
            file_put_contents(__DIR__ . "/demo_user_id.txt", $idUser);
            file_put_contents(__DIR__ . "/demo_userinfo_id.txt", $idUserInfo);
            
            echo "<p>Se han actualizado los archivos de referencia para la restauración.</p>";
        }
        
        echo "<div class='credentials'>
            <h3>Credenciales de acceso:</h3>
            <p><strong>Username:</strong> $username</p>
            <p><strong>Password:</strong> $rawPassword</p>
        </div>";
    }

    // Iniciar transacción
    $conexion->begin_transaction();

    // 1. Insertar en usersInfo
    $sqlUserInfo = "INSERT INTO usersInfo (names, lastnamePa, lastnameMa, gender, phone, email, street) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmtUserInfo = $conexion->prepare($sqlUserInfo);
    $stmtUserInfo->bind_param("sssssss", $name, $lastnamePa, $lastnameMa, $gender, $phone, $email, $address);
    $stmtUserInfo->execute();
    $idUserInfo = $conexion->insert_id;

    // 2. Insertar en users (Rol 2 = Profesor)
    $idRole = 2; // Asumiendo que el ID de rol 2 es para profesores
    $sqlUser = "INSERT INTO users (username, password, raw_password, idRole, idUserInfo) 
                VALUES (?, ?, ?, ?, ?)";
    $stmtUser = $conexion->prepare($sqlUser);
    $stmtUser->bind_param("sssii", $username, $password, $rawPassword, $idRole, $idUserInfo);
    $stmtUser->execute();
    $idUser = $conexion->insert_id;

    // 3. Insertar en teachers (idTeacherStatus = 1 = Activo)
    $idTeacherStatus = 1; // Asumiendo que 1 es para estado activo
    $sqlTeacher = "INSERT INTO teachers (profesionalID, ine, typeTeacher, idTeacherStatus, idUserInfo, idUser) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmtTeacher = $conexion->prepare($sqlTeacher);
    $stmtTeacher->bind_param("ssiiii", $profesionalID, $ine, $typeTeacher, $idTeacherStatus, $idUserInfo, $idUser);
    $stmtTeacher->execute();
    $idTeacher = $conexion->insert_id;

    // 4. Asignar grupos y materias (opcional)
    // Buscar un grupo y materia disponible para asignar al profesor demo
    $sqlGrupoMateria = "SELECT g.idGroup, s.idSubject FROM groups g, subjects s LIMIT 1";
    $resultGM = $conexion->query($sqlGrupoMateria);
    
    if($resultGM->num_rows > 0) {
        $rowGM = $resultGM->fetch_assoc();
        $idGroup = $rowGM['idGroup'];
        $idSubject = $rowGM['idSubject'];
        
        // Asignar grupo y materia al profesor
        $sqlAssignment = "INSERT INTO teacherGroupsSubjects (idTeacher, idGroup, idSubject) VALUES (?, ?, ?)";
        $stmtAssignment = $conexion->prepare($sqlAssignment);
        $stmtAssignment->bind_param("iii", $idTeacher, $idGroup, $idSubject);
        $stmtAssignment->execute();
    }

    // Confirmar la transacción
    $conexion->commit();
    
    // Guardar el ID del profesor para la restauración posterior
    // Verificando permisos de escritura primero
    if (!is_writable(__DIR__)) {
        echo "<p class='error'>Error: No hay permisos de escritura en el directorio demo.</p>";
        echo "<p>Solución: Cambia los permisos del directorio con 'chmod 755' o 'chmod 775'.</p>";
    } else {
        // Intentar guardar los archivos
        $result1 = file_put_contents(__DIR__ . "/demo_teacher_id.txt", $idTeacher);
        $result2 = file_put_contents(__DIR__ . "/demo_user_id.txt", $idUser);
        $result3 = file_put_contents(__DIR__ . "/demo_userinfo_id.txt", $idUserInfo);
        $result4 = file_put_contents(__DIR__ . "/last_restore.txt", time());
        
        if ($result1 === false || $result2 === false || $result3 === false || $result4 === false) {
            echo "<p class='error'>Error al escribir archivos de configuración.</p>";
            echo "<p>Verifica los permisos de escritura en el directorio demo.</p>";
        }
    }
    
    echo "<p class='success'>¡Usuario profesor demo creado exitosamente!</p>";
    echo "<div class='credentials'>
        <h3>Credenciales de acceso:</h3>
        <p><strong>Username:</strong> $username</p>
        <p><strong>Password:</strong> $rawPassword</p>
    </div>";
    
    // Mostrar información adicional
    echo "<h2>¿Qué sigue?</h2>
    <p>El sistema está configurado para restaurar automáticamente los cambios realizados por este usuario cada hora.</p>
    <p>Para probar que el sistema funciona correctamente:</p>
    <ol>
        <li>Inicia sesión con el usuario profesor demo</li>
        <li>Realiza algunos cambios (asignar calificaciones, etc.)</li>
        <li>Para forzar una restauración inmediata, visita: <code>" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/restore_demo_data.php</code></li>
        <li>Verifica que los cambios hayan sido revertidos</li>
    </ol>";


} catch (Exception $e) {
    // Revertir la transacción en caso de error
    if (isset($conexion) && $conexion->connect_error === false) {
        $conexion->rollback();
    }
    echo "<p class='error'>Error al crear el usuario: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body>
</html>";
?>
