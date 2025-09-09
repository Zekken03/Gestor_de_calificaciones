<?php
require_once "../conection.php";

$idGroup = isset($_GET['idGroup']) ? intval($_GET['idGroup']) : 0;
$idSchoolYear = isset($_GET['idSchoolYear']) ? intval($_GET['idSchoolYear']) : 0;

$students = [];
if ($idGroup > 0 && $idSchoolYear > 0) {
    $sql = "SELECT s.idStudent, ui.lastnamePa, ui.lastnameMa, ui.names, g.grade, g.group_ 
        FROM students s
        JOIN usersInfo ui ON s.idUserInfo = ui.idUserInfo
        JOIN groups g ON s.idGroup = g.idGroup
        WHERE s.idGroup = ? AND s.idSchoolYear = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $idGroup, $idSchoolYear);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

echo json_encode($students);
?> 