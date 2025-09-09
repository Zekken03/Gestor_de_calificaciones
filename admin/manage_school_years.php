<?php
require_once '../conection.php';
header('Content-Type: application/json');
$action = $_POST['action'] ?? '';

if ($action === 'list') {
    $res = mysqli_query($conexion, "SELECT idSchoolYear, startDate, endDate FROM schoolYear ORDER BY startDate DESC");
    $years = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $years[] = $row;
    }
    echo json_encode(['success' => true, 'years' => $years]);
    exit;
}

if ($action === 'add') {
    $start = $_POST['startDate'] ?? '';
    $end = $_POST['endDate'] ?? '';
    if ($start && $end) {
        $stmt = $conexion->prepare('INSERT INTO schoolYear (startDate, endDate) VALUES (?, ?)');
        $stmt->bind_param('ss', $start, $end);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

if ($action === 'edit') {
    $id = $_POST['idSchoolYear'] ?? '';
    $start = $_POST['startDate'] ?? '';
    $end = $_POST['endDate'] ?? '';
    if ($id && $start && $end) {
        $stmt = $conexion->prepare('UPDATE schoolYear SET startDate = ?, endDate = ? WHERE idSchoolYear = ?');
        $stmt->bind_param('ssi', $start, $end, $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

if ($action === 'delete') {
    $id = $_POST['idSchoolYear'] ?? '';
    if ($id) {
        $stmt = $conexion->prepare('DELETE FROM schoolYear WHERE idSchoolYear = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'ID faltante']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción inválida']);
