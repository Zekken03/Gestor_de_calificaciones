<?php
require_once "check_session.php";
include '../conection.php';

header('Content-Type: text/html; charset=utf-8');

$where = '';
if (isset($_POST['buscar']) && isset($_POST['valor'])) {
    $buscar = $_POST['buscar'];
    $valor = $_POST['valor'];
    if ($buscar === 'grupo') {
        $where = " AND g.idGroup = '" . $conexion->real_escape_string($valor) . "'";
    } else if ($buscar === 'maestro') {
        $where = " AND t.idTeacher = '" . $conexion->real_escape_string($valor) . "'";
    } else if ($buscar === 'materia') {
        $where = " AND sub.idSubject = '" . $conexion->real_escape_string($valor) . "'";
    }
}
$sql = "SELECT 
    syear.idSchoolYear, 
    LEFT(syear.startDate, 4) AS ciclo,
    g.idGroup, CONCAT(g.grade, g.group_) as grupo, 
    sub.idSubject, sub.name as materia,
    tgs.idTeacher,
    ui.lastnamePa, ui.lastnameMa, ui.names
FROM teacherGroupsSubjects tgs
INNER JOIN groups g ON tgs.idGroup = g.idGroup
INNER JOIN subjects sub ON tgs.idSubject = sub.idSubject
INNER JOIN teachers t ON tgs.idTeacher = t.idTeacher
INNER JOIN users u ON t.idUser = u.idUser
INNER JOIN usersInfo ui ON u.idUserInfo = ui.idUserInfo
INNER JOIN teacherSubject ts ON ts.idTeacher = tgs.idTeacher AND ts.idSubject = tgs.idSubject
INNER JOIN schoolYear syear ON ts.idSchoolYear = syear.idSchoolYear
WHERE 1 $where
ORDER BY syear.startDate DESC, grupo, materia, ui.lastnamePa, ui.lastnameMa, ui.names";
$result = $conexion->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr data-idgrupo="' . htmlspecialchars($row['idGroup']) . '" ';
        echo 'data-idsubject="' . htmlspecialchars($row['idSubject']) . '" ';
        echo 'data-idteacher="' . htmlspecialchars($row['idTeacher']) . '" ';
        echo 'data-idyear="' . htmlspecialchars($row['idSchoolYear']) . '">';
        echo '<td>' . htmlspecialchars($row['ciclo']) . '</td>';
        echo '<td>' . htmlspecialchars($row['grupo']) . '</td>';
        echo '<td>' . htmlspecialchars($row['materia']) . '</td>';
        echo '<td>' . htmlspecialchars($row['lastnamePa']) . '</td>';
        echo '<td>' . htmlspecialchars($row['lastnameMa']) . '</td>';
        echo '<td>' . htmlspecialchars($row['names']) . '</td>';
        echo '<td><button class="botonVerEdit" id="botonVer" data-bs-toggle="modal" data-bs-target="#editModal" style="margin-left: 10vh;"><i class="bi bi-pencil-fill"></i></button></td>';
        echo '<td><button class="botonVerDelete" id="botonVer" data-bs-toggle="modal" data-bs-target="#deleteModal" style="margin-left: 10vh;"><i class="bi bi-trash-fill"></i></button></td>';
    }
} else {
    echo '<tr><td colspan="8">No hay asignaciones registradas.</td></tr>';
}
?>
