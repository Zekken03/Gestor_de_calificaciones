<?php
require_once "../conection.php";
require_once "check_session.php";
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$sqlTeacher = "SELECT idTeacher FROM teachers WHERE idUser = ?";
$stmtTeacher = $conexion->prepare($sqlTeacher);
$stmtTeacher->bind_param("i", $user_id);
$stmtTeacher->execute();
$resTeacher = $stmtTeacher->get_result();
$rowTeacher = $resTeacher->fetch_assoc();
$teacher_id = $rowTeacher ? $rowTeacher['idTeacher'] : null;

$idSchoolYear = isset($_GET['idSchoolYear']) ? intval($_GET['idSchoolYear']) : 0;
$idSchoolQuarter = isset($_GET['idSchoolQuarter']) ? intval($_GET['idSchoolQuarter']) : 0;

// LOG para depuración
file_put_contents(__DIR__.'/debug_get_subjects.txt', "teacher_id=$teacher_id, idSchoolYear=$idSchoolYear, idSchoolQuarter=$idSchoolQuarter\n", FILE_APPEND);

$subjects = [];
if ($teacher_id) {
    $query = "SELECT DISTINCT s.idSubject, s.name
              FROM teacherSubject ts
              JOIN subjects s ON ts.idSubject = s.idSubject
              WHERE ts.idTeacher = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}
// LOG para depuración
file_put_contents(__DIR__.'/debug_get_subjects.txt', "SQL subjects: ".json_encode($subjects)."\n", FILE_APPEND);
echo json_encode(['success' => true, 'subjects' => $subjects]);
