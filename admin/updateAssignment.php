<?php
require_once "check_session.php";
include '../conection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idTeacher = isset($_POST['docente']) ? intval($_POST['docente']) : null;
    $idGroup = isset($_POST['grupo']) ? intval($_POST['grupo']) : null;
    $idSubject = isset($_POST['materia']) ? intval($_POST['materia']) : null;
    $idSchoolYear = isset($_POST['ciclo']) ? intval($_POST['ciclo']) : null;
    $oldTeacher = isset($_POST['old_docente']) ? intval($_POST['old_docente']) : null;
    $oldGroup = isset($_POST['old_grupo']) ? intval($_POST['old_grupo']) : null;
    $oldSubject = isset($_POST['old_materia']) ? intval($_POST['old_materia']) : null;
    
    if (!$idTeacher || !$idGroup || !$idSubject || !$idSchoolYear || !$oldTeacher || !$oldGroup || !$oldSubject) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos para actualizar la asignación.']);
        exit;
    }
    $conexion->begin_transaction();
    try {
        // Actualizar teacherGroupsSubjects
        $sql1 = "UPDATE teacherGroupsSubjects SET idTeacher=?, idGroup=?, idSubject=? WHERE idTeacher=? AND idGroup=? AND idSubject=?";
        $stmt1 = $conexion->prepare($sql1);
        $stmt1->bind_param('iiiiii', $idTeacher, $idGroup, $idSubject, $oldTeacher, $oldGroup, $oldSubject);
        $stmt1->execute();
        // Actualizar teacherSubject (ciclo escolar)
        $sql2 = "UPDATE teacherSubject SET idTeacher=?, idSubject=?, idSchoolYear=? WHERE idTeacher=? AND idSubject=?";
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->bind_param('iiiii', $idTeacher, $idSubject, $idSchoolYear, $oldTeacher, $oldSubject);
        $stmt2->execute();
        $conexion->commit();
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
