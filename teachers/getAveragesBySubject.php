<?php
require_once "../conection.php";
require_once "check_session.php";
header('Content-Type: application/json');

$idSubject = isset($_GET['idSubject']) ? intval($_GET['idSubject']) : 0;
$idSchoolYear = isset($_GET['idSchoolYear']) ? intval($_GET['idSchoolYear']) : 0;
$idSchoolQuarter = isset($_GET['idSchoolQuarter']) ? intval($_GET['idSchoolQuarter']) : 0;

if ($idSubject && $idSchoolYear && $idSchoolQuarter) {
    // Traer todos los alumnos de la materia y su promedio del trimestre seleccionado para ese año escolar
    $sql = "SELECT DISTINCT s.idStudent, ui.lastnamePa, ui.lastnameMa, ui.names, g.grade, g.group_, a.average
            FROM students s
            JOIN usersInfo ui ON s.idUserInfo = ui.idUserInfo
            JOIN groups g ON s.idGroup = g.idGroup
            JOIN teacherGroupsSubjects tgs ON tgs.idGroup = s.idGroup
            LEFT JOIN (
                SELECT idStudent, average, idSchoolYear, idSchoolQuarter, idSubject
                FROM average
                WHERE idSchoolYear = ? AND idSubject = ? AND idSchoolQuarter = ?
            ) a ON a.idStudent = s.idStudent AND a.idSchoolYear = s.idSchoolYear AND a.idSubject = ? AND a.idSchoolQuarter = ?
            WHERE tgs.idSubject = ? AND s.idSchoolYear = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiiiiii", $idSchoolYear, $idSubject, $idSchoolQuarter, $idSubject, $idSchoolQuarter, $idSubject, $idSchoolYear);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    echo json_encode(['success' => true, 'students' => $students]);
} else {
    echo json_encode(['success' => false, 'message' => 'Materia, año escolar o trimestre no especificado']);
}
