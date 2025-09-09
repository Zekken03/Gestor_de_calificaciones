<?php
// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../conection.php";
require_once "check_session.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'No se recibieron datos']);
        exit;
    }

    $idSubject = $data['idSubject'];
    $idSchoolYear = $data['idSchoolYear'];
    $idSchoolQuarter = $data['idSchoolQuarter'];
    $criterias = $data['criterias'];

    try {
        // Primero elimina las calificaciones asociadas a los criterios de este subject, year y quarter
        $stmt = $conexion->prepare("DELETE gs FROM gradesSubject gs
            INNER JOIN evaluationCriteria ec ON gs.idEvalCriteria = ec.idEvalCriteria
            WHERE ec.idSubject = ? AND ec.idSchoolYear = ? AND ec.idSchoolQuarter = ?");
        $stmt->bind_param("iii", $idSubject, $idSchoolYear, $idSchoolQuarter);
        $stmt->execute();

        // Ahora sí elimina los criterios
        $stmt = $conexion->prepare("DELETE FROM evaluationCriteria WHERE idSubject = ? AND idSchoolYear = ? AND idSchoolQuarter = ?");
        $stmt->bind_param("iii", $idSubject, $idSchoolYear, $idSchoolQuarter);
        $stmt->execute();

        // Insertamos los nuevos criterios
        $stmt = $conexion->prepare("INSERT INTO evaluationCriteria (criteria, porcentage, idSubject, idSchoolYear, idSchoolQuarter) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($criterias as $criteria) {
            $criteriaName = $criteria['name'];
            $percentage = $criteria['percentage'];
            $stmt->bind_param("siiii", $criteriaName, $percentage, $idSubject, $idSchoolYear, $idSchoolQuarter);
            $stmt->execute();
        }

        // Obtener los criterios recién guardados con sus IDs
        $stmt = $conexion->prepare("SELECT idEvalCriteria, criteria, porcentage FROM evaluationCriteria WHERE idSubject = ? AND idSchoolYear = ? AND idSchoolQuarter = ?");
        $stmt->bind_param("iii", $idSubject, $idSchoolYear, $idSchoolQuarter);
        $stmt->execute();
        $result = $stmt->get_result();
        $criterias = [];
        while ($row = $result->fetch_assoc()) {
            $criterias[] = [
                'idEvalCriteria' => $row['idEvalCriteria'],
                'name' => $row['criteria'],
                'percentage' => $row['porcentage']
            ];
        }
        echo json_encode(['success' => true, 'message' => 'Criterios guardados correctamente', 'data' => $criterias]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
    }
}
?> 