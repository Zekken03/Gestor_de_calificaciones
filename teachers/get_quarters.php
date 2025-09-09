<?php
require_once "../conection.php";

$idSchoolYear = isset($_GET['idSchoolYear']) ? intval($_GET['idSchoolYear']) : 0;

$quarters = [];
if ($idSchoolYear > 0) {
    $sql = "SELECT idSchoolQuarter, name FROM schoolQuarter WHERE idSchoolYear = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idSchoolYear);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $quarters[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
if ($idSchoolYear > 0) {
    echo json_encode(['success' => true, 'quarters' => $quarters]);
} else {
    echo json_encode(['success' => false, 'quarters' => []]);
}
?>