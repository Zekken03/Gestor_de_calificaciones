<?php
// Establecer encabezados para mostrar correctamente en el navegador
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Restaurar Datos Demo</title>
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
        pre {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Restauración de Datos del Profesor Demo</h1>";

// Determinar la ruta correcta al archivo de conexión según el entorno
$basePath = dirname(dirname(__FILE__)); // Directorio padre (raíz del proyecto)
require_once $basePath . "/conection.php";

// Leer los IDs del profesor demo
$idTeacher = file_exists(__DIR__ . "/demo_teacher_id.txt") ? file_get_contents(__DIR__ . "/demo_teacher_id.txt") : null;
$idUser = file_exists(__DIR__ . "/demo_user_id.txt") ? file_get_contents(__DIR__ . "/demo_user_id.txt") : null;
$idUserInfo = file_exists(__DIR__ . "/demo_userinfo_id.txt") ? file_get_contents(__DIR__ . "/demo_userinfo_id.txt") : null;

if (!$idTeacher || !$idUser || !$idUserInfo) {
    echo "<p class='error'>No se encontró información del profesor demo para restaurar.</p>";
    echo "<p>Primero debes crear un usuario profesor demo ejecutando el script <code>create_demo_teacher.php</code>.</p>";
    echo "</body></html>";
    exit();
}

try {
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Registrar la fecha y hora de la restauración
    $logMessage = date("Y-m-d H:i:s") . " - Iniciando restauración de datos del profesor demo\n";
    file_put_contents(__DIR__ . "/restore_log.txt", $logMessage, FILE_APPEND);
    
    // 1. Eliminar cualquier registro de calificaciones creado por este profesor
    $sqlDeleteGrades = "DELETE FROM grades WHERE idGrade IN (
                          SELECT g.idGrade FROM grades g
                          JOIN teacherGroupsSubjects tgs ON g.idTeacherGroupSubject = tgs.idTeacherGroupSubject
                          WHERE tgs.idTeacher = ?
                        )";
    $stmtDeleteGrades = $conexion->prepare($sqlDeleteGrades);
    $stmtDeleteGrades->bind_param("i", $idTeacher);
    $stmtDeleteGrades->execute();
    $gradesDeleted = $stmtDeleteGrades->affected_rows;
    
    // 2. Eliminar cualquier criterio de evaluación creado por este profesor
    $sqlDeleteCriteria = "DELETE FROM evaluationCriteria WHERE idTeacher = ?";
    $stmtDeleteCriteria = $conexion->prepare($sqlDeleteCriteria);
    $stmtDeleteCriteria->bind_param("i", $idTeacher);
    $stmtDeleteCriteria->execute();
    $criteriaDeleted = $stmtDeleteCriteria->affected_rows;
    
    // 3. Confirmar la transacción
    $conexion->commit();
    
    // Actualizar el tiempo de la última restauración
    file_put_contents(__DIR__ . "/last_restore.txt", time());
    
    $logMessage = date("Y-m-d H:i:s") . " - Restauración completada exitosamente\n";
    file_put_contents(__DIR__ . "/restore_log.txt", $logMessage, FILE_APPEND);
    
    echo "<p class='success'>Los datos del profesor demo han sido restaurados exitosamente.</p>";
    echo "<ul>";
    echo "<li>Calificaciones eliminadas: $gradesDeleted</li>";
    echo "<li>Criterios de evaluación eliminados: $criteriaDeleted</li>";
    echo "</ul>";
    
    echo "<p>La próxima restauración automática será en aproximadamente 1 hora.</p>";
    echo "<p><a href='../index.php'>Volver al inicio</a></p>";

} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conexion->rollback();
    
    $logMessage = date("Y-m-d H:i:s") . " - Error durante la restauración: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . "/restore_log.txt", $logMessage, FILE_APPEND);
    
    echo "<p class='error'>Error durante la restauración: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
