<?php
require_once "../conection.php";
require_once "check_session.php";

header('Content-Type: application/json');

if (!isset($_GET['idSubject']) || empty($_GET['idSubject'])) {
    echo json_encode(['success' => false, 'error' => 'No subject specified']);
    exit;
}

$idSubject = intval($_GET['idSubject']);
$idSchoolYear = isset($_GET['idSchoolYear']) ? intval($_GET['idSchoolYear']) : 0;

// Obtener solo los alumnos relacionados a la materia seleccionada, siguiendo la cadena correcta
$query = "SELECT s.idStudent, ui.lastnamePa, ui.lastnameMa, ui.names, g.grade, g.group_
          FROM students s
          JOIN usersInfo ui ON s.idUserInfo = ui.idUserInfo
          JOIN groups g ON s.idGroup = g.idGroup
          JOIN teacherGroupsSubjects tgs ON tgs.idGroup = g.idGroup
          WHERE tgs.idSubject = ? ";
if ($idSchoolYear > 0) {
    $query .= " AND s.idSchoolYear = ? ";
}
$query .= " GROUP BY s.idStudent";

if ($idSchoolYear > 0) {
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $idSubject, $idSchoolYear);
} else {
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $idSubject);
}
$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// DEBUG: imprime los alumnos enviados en JSON
file_put_contents(__DIR__.'/debug_students_ajax.txt', json_encode(['params'=>[$idSubject, $idSchoolYear], 'students'=>$students], JSON_PRETTY_PRINT)."\n", FILE_APPEND);

echo json_encode(['success' => true, 'students' => $students]);
