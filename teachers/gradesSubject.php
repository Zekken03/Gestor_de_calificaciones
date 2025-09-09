<?php
require_once "check_session.php";
require_once "../conection.php";



$idSubject = isset($_GET['idSubject']) ? intval($_GET['idSubject']) : 0;
$subjectName = "";
// Obtener las materias del profesor actual
$user_id = $_SESSION['user_id'];
$query = "SELECT s.name, s.idSubject
          FROM teacherSubject ts
          JOIN subjects s ON ts.idTeacherSubject = s.idSubject
          WHERE ts.idTeacher = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

if ($idSubject > 0) {
    $stmt = $conexion->prepare("SELECT name FROM subjects WHERE idSubject = ?");
    $stmt->bind_param("i", $idSubject);
    $stmt->execute();
    $stmt->bind_result($subjectName);
    $stmt->fetch();
    $stmt->close();
}

// Obtener los años escolares ANTES de cualquier consulta de alumnos
$schoolYears = [];
$query = "SELECT idSchoolYear, startDate FROM schoolYear ORDER BY startDate DESC";
$result = $conexion->query($query);
while ($row = $result->fetch_assoc()) {
    $schoolYears[] = $row;
}

// Determinar el año escolar seleccionado correctamente
$selectedYear = null;
if (isset($_GET['idSchoolYear']) && is_numeric($_GET['idSchoolYear'])) {
    $selectedYear = intval($_GET['idSchoolYear']);
} elseif (!empty($schoolYears)) {
    $selectedYear = $schoolYears[0]['idSchoolYear'];
}

// Obtener TODOS los grupos vinculados a la materia
$groupIds = [];
if ($idSubject > 0) {
    $stmt = $conexion->prepare("SELECT idGroup FROM teacherGroupsSubjects WHERE idSubject = ?");
    $stmt->bind_param("i", $idSubject);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $groupIds[] = $row['idGroup'];
    }
    $stmt->close();
    // Elimina duplicados
    $groupIds = array_values(array_unique($groupIds));
}

// DEBUG: imprime los grupos y el año escolar para verificar qué se consulta
file_put_contents(__DIR__.'/debug_grupos.txt', "Grupos: ".json_encode($groupIds)." | Año: ".$selectedYear."\n", FILE_APPEND);

// Obtener los alumnos de TODOS los grupos vinculados y año escolar seleccionado
$students = [];
if (!empty($groupIds) && $selectedYear !== null) {
    $in = str_repeat('?,', count($groupIds) - 1) . '?';
    $sql = "SELECT s.idStudent, ui.lastnamePa, ui.lastnameMa, ui.names, g.grade, g.group_ 
            FROM students s
            JOIN usersInfo ui ON s.idUserInfo = ui.idUserInfo
            JOIN groups g ON s.idGroup = g.idGroup
            WHERE s.idGroup IN ($in) AND s.idSchoolYear = ?";
    $stmt = $conexion->prepare($sql);
    $params = array_merge($groupIds, [$selectedYear]);
    $stmt->bind_param(str_repeat('i', count($groupIds)) . 'i', ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

// DEBUG: imprime los alumnos encontrados (forzando creación del archivo)
file_put_contents(__DIR__.'/debug_alumnos.txt', var_export($students, true) . "\n", FILE_APPEND);

// Obtener los trimestres escolares
$schoolQuarters = [];
if (isset($_GET['idSchoolYear'])) {
    $idSchoolYear = intval($_GET['idSchoolYear']);
    $query = "SELECT idSchoolQuarter, name FROM schoolQuarter WHERE idSchoolYear = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $idSchoolYear);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $schoolQuarters[] = $row;
    }
    $stmt->close();
}

// --- Obtener promedios guardados para los estudiantes en este trimestre y ciclo ---
$studentAverages = [];
if ($selectedYear !== null && isset($_GET['idSchoolQuarter'])) {
    $idSchoolQuarter = intval($_GET['idSchoolQuarter']);
    $ids = array_column($students, 'idStudent');
    if (count($ids) > 0) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids)) . 'ii';
        $query = "SELECT idStudent, average FROM average WHERE idStudent IN ($in) AND idSchoolYear = ? AND idSchoolQuarter = ?";
        $stmt = $conexion->prepare($query);
        $params = array_merge($ids, [$selectedYear, $idSchoolQuarter]);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $studentAverages[$row['idStudent']] = $row['average'];
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificaciones de la Materia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/teacher/gradeSubject.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.css">

    <!-- TIPOGRAFIA -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&family=Lora:ital,wght@0,400..700;1,400..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <!-- TIPOGRAFIA -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&family=Lora:ital,wght@0,400..700;1,400..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">


    
    <link rel="icon" href="../img/logo.ico">

</head>
<body class="row d-flex" style="height: 100%; width: 100%; margin: 0; padding: 0;">
<div id="preloader">
        <img src="../img/logo.webp" alt="Cargando..." class="logo">
    </div>
    <!-- ASIDEBAR -->
    <?php
        include "../layouts/asideTeacher.php"; 
    ?>
    <!-- END ASIDEBAR -->
    <!-- MAIN CONTENT -->
     <main class="flex-grow-1 col-9 p-0 ">
        <?php
            include "../layouts/headerTeacher.php"; 
        ?>
        <div class="mb-3"style="padding-top:10vh; width:30%;">
           <label id="titulo" for="grupo" class="form-label fw-bold">
               <?php echo $subjectName ? htmlspecialchars($subjectName) : "Materia no encontrada"; ?>
           </label>
        </div>             
        <div class="container" >
            <!-- Dropdown para seleccionar el año escolar -->
        <div class="mb-3" style="width:30%;">
            <label id="label"class="form-label">Seleccionar Año Escolar:</label>
            <select id="schoolYearSelect" class="form-select">
                <option value="" disabled selected>Seleccione un año escolar</option>
                <?php foreach ($schoolYears as $year): ?>
                    <option value="<?php echo $year['idSchoolYear']; ?>">
                        <?php echo htmlspecialchars(substr($year['startDate'], 0, 4)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
         <!-- Dropdown para seleccionar el trimestre escolar -->
         <div class="mb-3" id="quarterSelectContainer" style="display: none; width:30%;">
            <label id="label" class="form-label">Seleccionar Trimestre Escolar:</label>
            <select id="schoolQuarterSelect" class="form-select">
                <option value="" disabled selected>Seleccione un trimestre</option>
                <?php 
                foreach ($schoolQuarters as $quarter): 
                ?>
                    <option value="<?php echo $quarter['idSchoolQuarter']; ?>">
                        <?php echo htmlspecialchars($quarter['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="d-flex justify-content-end mb-3">
            <button class="btn me-2" id="addColumnBtn">
                Añadir 
                <i id="icon" class="bi bi-plus-lg"></i>
            </button>
            <button class="btn" id="removeColumnBtn">
                Eliminar
                 <i id="icon" class="bi bi-trash3-fill"></i>
            </button>
            
        </div>
            <table class="table table-bordered border-dark" id="dataTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th>Nombres</th>
                        <th>Grado</th>
                        <th>Grupo</th>
                        <?php
                            // Determinar cuántos criterios hay por las columnas C en el header
                            $num_criterios = 0;
                            foreach ([183,184,185,/*...*/] as $col) {
                                // Solo cuenta las columnas que empiezan con C
                                // (en producción, deberías obtener esto dinámicamente del backend)
                                $num_criterios++;
                            }
                            // Por ahora, usa 3 como mínimo (C1, C2, C3)
                            $num_criterios = max(3, $num_criterios);
                            for ($c = 1; $c <= $num_criterios; $c++):
                        ?>
                        <th>C<?php echo $c; ?></th>
                        <?php endfor; ?>
                        <th>Promedio</th>
                    </tr>
                    <tr id="percentageRow">
                        <th colspan="4">Porcentajes (%)</th> 
                        <th></th>
                        <th></th>
                        <?php
                            for ($c = 1; $c <= $num_criterios; $c++):
                        ?>
                        <th><select class="form-select percentage-select" id="C<?php echo $c; ?>-percentage"></select></th>
                        <?php endfor; ?>
                        <th>-</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $i => $student): ?>
                    <tr data-student-id="<?php echo htmlspecialchars($student['idStudent']); ?>">
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($student['lastnamePa']); ?></td>
                        <td><?php echo htmlspecialchars($student['lastnameMa']); ?></td>
                        <td><?php echo htmlspecialchars($student['names']); ?></td>
                        <td><?php echo htmlspecialchars($student['grade']); ?>°</td>
                        <td><?php echo htmlspecialchars($student['group_']); ?></td>
                        <?php
                            for ($c = 1; $c <= $num_criterios; $c++):
                        ?>
                        <td style="width:10%"><input type="text" class="form-control grade-input" data-col-index="<?php echo $c; ?>" data-criteria-id=""></td>
                        <?php endfor; ?>
                        <td class="promedio-cell"><?php
                            $avg = isset($studentAverages[$student['idStudent']]) ? $studentAverages[$student['idStudent']] : null;
                            echo ($avg !== null && $avg !== '') ? number_format($avg, 2) : '-';
                        ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            
            </table>
            <div  class="d-flex justify-content-end mb-3">
                <button id="guardar" class="btn">
                    Guardar
                    <i id="icon" class="bi bi-floppy2-fill"></i>
                </button>
            </div>    
        </div>
        
       
    </main>
    <!-- END MAIN CONTENT --> 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/chartScript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Hide preloader when page is fully loaded
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.add('loaded');
                // Remove preloader from DOM after animation completes
                setTimeout(() => {
                    preloader.remove();
                }, 500);
            }
        });
    </script>
    <script>
        // -- OPCIONES DE PORCENTAJE --
    const percentageOptions = [ 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100];

    // -- FUNCIÓN PARA AGREGAR OPCIONES A UN SELECT --
    function fillPercentageSelect(select) {
        select.innerHTML = ''; 
        // Agregar opción vacía por defecto
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Seleccionar';
        select.appendChild(defaultOption);
        
        percentageOptions.forEach(value => {
            const option = document.createElement('option');
            option.value = value.toString(); // <-- valor como string
            option.textContent = `${value}%`;
            select.appendChild(option);
        });
        select.addEventListener('change', validatePercentages); 
    }

    // -- VALIDACIÓN DE PORCENTAJES --
    function validatePercentages() {
    const selects = document.querySelectorAll('.percentage-select');
    let totalPercentage = 0;

    // Sumar todos los valores
    selects.forEach(select => {
        totalPercentage += parseInt(select.value) || 0;
    });

    if (totalPercentage > 100) {
        alert(`El total de los porcentajes es ${totalPercentage}%. Se ajustará automáticamente.`);

        // Determinar el último select modificado
        const lastChanged = [...selects].find(select => select === document.activeElement);

        if (lastChanged) {
            const exceso = totalPercentage - 100;
            lastChanged.value = Math.max(0, parseInt(lastChanged.value) - exceso);
        }
    }
}

    // -- AÑADIR CELDA --
    document.getElementById('addColumnBtn').addEventListener('click', function () {
    const table = document.getElementById('dataTable');
    const headerRow = table.querySelector('thead tr');
    const percentageRow = document.getElementById('percentageRow');
    const bodyRows = table.querySelectorAll('tbody tr');

    const existingColumns = Array.from(headerRow.children).filter(th => th.textContent.startsWith('C')).length;
    const newColIndex = existingColumns + 1;
    const newColumnName = `C${newColIndex}`;

    // Nueva cabecera
    const newHeader = document.createElement('th');
    newHeader.textContent = newColumnName;
    newHeader.style.width = "10%";
    headerRow.insertBefore(newHeader, headerRow.children[headerRow.children.length - 1]);

    // Nueva celda de porcentaje
    const newPercentageCell = document.createElement('th');
    const newPercentageSelect = document.createElement('select');
    newPercentageSelect.className = 'form-select percentage-select';
    newPercentageSelect.id = `C${newColIndex}-percentage`;
    fillPercentageSelect(newPercentageSelect);
    newPercentageSelect.value = ''; // Valor por defecto: "Seleccionar"
    newPercentageCell.appendChild(newPercentageSelect);
    percentageRow.insertBefore(newPercentageCell, percentageRow.children[percentageRow.children.length - 1]);

    // Nueva celda en cada fila del cuerpo
    bodyRows.forEach(row => {
        const newCell = document.createElement('td');
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control grade-input';
        input.setAttribute('data-col-index', newColIndex); // ¡Ahora sí es correlativo!
        input.setAttribute('data-criteria-id', '');
        newCell.appendChild(input);
        row.insertBefore(newCell, row.children[row.children.length - 1]);
    });

    validatePercentages();
    asignarCriteriaIdInputs();
});

    // -- ELIMINAR CELDA --
    document.getElementById('removeColumnBtn').addEventListener('click', function () {
        const table = document.getElementById('dataTable');
        const headerRow = table.querySelector('thead tr');
        const percentageRow = document.getElementById('percentageRow');
        const bodyRows = table.querySelectorAll('tbody tr');

        const columnHeaders = Array.from(headerRow.children).filter(th => th.textContent.startsWith('C'));

        if (columnHeaders.length > 0) {
            const lastColumnIndex = Array.from(headerRow.children).indexOf(columnHeaders[columnHeaders.length - 1]);

            headerRow.removeChild(columnHeaders[columnHeaders.length - 1]);
            percentageRow.removeChild(percentageRow.children[percentageRow.children.length - 2]);

            bodyRows.forEach(row => {
                row.removeChild(row.children[lastColumnIndex]);
            });

            validatePercentages();
            asignarCriteriaIdInputs();
        } else {
            alert("⚠️ No hay más columnas para eliminar.");
        }
    });

    // -- CREAR FILA DE PORCENTAJES SI NO EXISTE --
    function createPercentageRow() {
        const table = document.getElementById('dataTable');
        const thead = table.querySelector('thead');

        const percentageRow = document.createElement('tr');
        percentageRow.id = 'percentageRow';

        const emptyCells = document.createElement('th');
        emptyCells.colSpan = 4;
        percentageRow.appendChild(emptyCells);

        const emptyCells2 = document.createElement('th');
        emptyCells2.colSpan = 1;
        percentageRow.appendChild(emptyCells2);

        const emptyCells3 = document.createElement('th');
        emptyCells3.colSpan = 1;
        percentageRow.appendChild(emptyCells3);

        const promedioCell = document.createElement('th');
        promedioCell.textContent = '-'; 
        percentageRow.appendChild(promedioCell);

        thead.insertBefore(percentageRow, thead.children[1]);

        return percentageRow;
    }

    // -- INICIALIZACIÓN AL CARGAR LA PÁGINA --

    const idGroup = <?php echo json_encode($groupIds); ?>;
    document.addEventListener('DOMContentLoaded', function() {
        const tbody = document.querySelector('#dataTable tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="100%" class="text-center">Seleccione un año escolar y luego un trimestre para ver los estudiantes.</td></tr>';
        }
    });

    document.getElementById('schoolQuarterSelect').addEventListener('change', function() {
        const selectedQuarter = this.value;
        const selectedYear = document.getElementById('schoolYearSelect').value;
        const idSubject = <?php echo $idSubject; ?>;
        
        if (selectedQuarter && selectedYear) {
            fetch(`getStudentsBySubject.php?idSubject=${idSubject}&idSchoolYear=${selectedYear}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#dataTable tbody');
                    tbody.innerHTML = '';
                    if (data.success && Array.isArray(data.students)) {
                        data.students.forEach((student, index) => {
                            const row = document.createElement('tr');
                            row.setAttribute('data-student-id', student.idStudent);
                            row.innerHTML = `
                                <td>${index + 1}</td>
                                <td>${student.lastnamePa}</td>
                                <td>${student.lastnameMa}</td>
                                <td>${student.names}</td>
                                <td>${student.grade}°</td>
                                <td>${student.group_}</td>
                                <td style="width:10%"><input type="text" class="form-control grade-input" data-col-index="1" data-criteria-id=""></td>
                                <td style="width:10%"><input type="text" class="form-control grade-input" data-col-index="2" data-criteria-id=""></td>
                                <td style="width:10%"><input type="text" class="form-control grade-input" data-col-index="3" data-criteria-id=""></td>
                                <td class="promedio-cell">-</td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                });
        } else {
            const tbody = document.querySelector('#dataTable tbody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="100%" class="text-center">Seleccione un año escolar y luego un trimestre para ver los estudiantes.</td></tr>';
            }
        }
    });

    // Función para resetear la estructura de la tabla a 3 columnas
    function resetTableStructure() {
        const table = document.getElementById('dataTable');
        const headerRow = table.querySelector('thead tr:first-child');
        const percentageRow = document.getElementById('percentageRow');
        const bodyRows = table.querySelectorAll('tbody tr');

        // Mantener solo las primeras 5 columnas fijas (No., Paterno, Materno, Nombres, Grupo, Grado)
        while (headerRow.children.length > 11) { // 5 fijas + 3 criterios + promedio
            headerRow.removeChild(headerRow.children[headerRow.children.length - 2]); // Antes del promedio
            percentageRow.removeChild(percentageRow.children[percentageRow.children.length - 2]); // Antes del promedio
            bodyRows.forEach(row => {
                row.removeChild(row.children[row.children.length - 2]); // Antes del promedio
            });
        }

        // Asegurarse de que los selects de porcentaje estén inicializados con valor vacío
        document.querySelectorAll('.percentage-select').forEach(select => {
            fillPercentageSelect(select);
            select.value = '';
        });
    }

    // Función para obtener los criterios guardados
    function loadEvaluationCriteria(idSubject, idSchoolYear, idSchoolQuarter, callback) {
        // Primero resetear la tabla a su estructura base (3 columnas)
        resetTableStructure();
        
        fetch(`getEvaluationCriteria.php?idSubject=${idSubject}&idSchoolYear=${idSchoolYear}&idSchoolQuarter=${idSchoolQuarter}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.data.length > 0) {
                        // Asegurarse de que hay suficientes columnas para los criterios
                        const currentColumns = document.querySelectorAll('.percentage-select').length;
                        const neededColumns = data.data.length;
                        
                        // Añadir columnas si es necesario
                        if (neededColumns > currentColumns) {
                            const difference = neededColumns - currentColumns;
                            for (let i = 0; i < difference; i++) {
                                document.getElementById('addColumnBtn').click();
                            }
                        }

                        // Esperar un momento para que se creen los elementos
                        setTimeout(() => {
                            // Establecer los porcentajes guardados
                            data.data.forEach((criteria, index) => {
                                const columnNumber = index + 1;
                                const percentageSelect = document.querySelector(`#C${columnNumber}-percentage`);
                                if (percentageSelect) {
                                    percentageSelect.value = criteria.percentage.toString(); // <-- valor como string
                                    percentageSelect.setAttribute('data-criteria-id', criteria.idEvalCriteria);
                                    // Asignar data-criteria-id a todos los inputs de la columna correspondiente
                                    document.querySelectorAll(`.grade-input[data-col-index='${columnNumber}']`).forEach(input => {
                                        input.setAttribute('data-criteria-id', criteria.idEvalCriteria);
                                    });
                                }
                            });
                            
                            // Habilitar los inputs
                            const inputs = document.querySelectorAll('.grade-input');
                            inputs.forEach(input => {
                                input.disabled = false;
                            });
                            asignarCriteriaIdInputs();
                            if (typeof callback === 'function') callback();
                        }, 150);
                    }
                }
            })
            .catch(error => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar los criterios de evaluación' });
            });
    }

    // Función para cargar las calificaciones
    function loadGrades(idSubject, idSchoolYear, idSchoolQuarter) {
        // 1. Cargar calificaciones normales
        fetch(`getGrades.php?idSubject=${idSubject}&idSchoolYear=${idSchoolYear}&idSchoolQuarter=${idSchoolQuarter}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rows = document.querySelectorAll('#dataTable tbody tr');
                    rows.forEach((row) => {
                        const idStudent = row.getAttribute('data-student-id');
                        if (!idStudent) return;
                        const inputs = row.querySelectorAll('.grade-input');
                        inputs.forEach(input => input.value = '');
                        const studentGradesData = data.data[idStudent];
                        if (studentGradesData) {
                            inputs.forEach((input) => {
                                const idEvalCriteria = input.getAttribute('data-criteria-id');
                                if (idEvalCriteria && studentGradesData[idEvalCriteria] !== undefined) {
                                    input.value = studentGradesData[idEvalCriteria];
                                }
                            });
                        }
                    });
                }
            });

        // 2. Cargar promedios guardados y actualizar la tabla
        fetch(`getAverages.php?idSubject=${idSubject}&idSchoolYear=${idSchoolYear}&idSchoolQuarter=${idSchoolQuarter}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const averages = data.data;
                    document.querySelectorAll('#dataTable tbody tr').forEach(row => {
                        const idStudent = row.getAttribute('data-student-id');
                        const promedioCell = row.querySelector('.promedio-cell');
                        if (promedioCell) {
                            if (averages[idStudent] !== undefined && averages[idStudent] !== null) {
                                promedioCell.textContent = Number(averages[idStudent]).toFixed(2);
                            } else {
                                promedioCell.textContent = '-';
                            }
                        }
                    });
                }
            });
    }

    // Modificar el event listener del select de trimestre
    document.getElementById('schoolQuarterSelect').addEventListener('change', function() {
        const selectedQuarter = this.value;
        const selectedYear = document.getElementById('schoolYearSelect').value;
        const idSubject = <?php echo $idSubject; ?>;
        
        if (selectedQuarter && selectedYear) {
            loadEvaluationCriteria(idSubject, selectedYear, selectedQuarter, function() {
                loadGrades(idSubject, selectedYear, selectedQuarter);
            });
        } else {
            resetTableStructure();
            document.querySelectorAll('.grade-input').forEach(input => {
                input.value = '';
            });
        }
    });

    // Asegura que el botón de guardar calificaciones siempre tenga el listener
    function asignarListenerGuardarCalificaciones(idSubject, idSchoolYear, idSchoolQuarter) {
        const btn = document.getElementById('guardar');
        if (btn) {
            // Elimina listeners previos para evitar duplicados
            btn.replaceWith(btn.cloneNode(true));
            const newBtn = document.getElementById('guardar');
            newBtn.addEventListener('click', function() {
                const currentYear = document.getElementById('schoolYearSelect').value;
                const currentQuarter = document.getElementById('schoolQuarterSelect').value;
                guardarCalificaciones(idSubject, currentYear, currentQuarter);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Asigna el listener al cargar la página
        asignarListenerGuardarCalificaciones(<?php echo $idSubject; ?>, document.getElementById('schoolYearSelect').value, document.getElementById('schoolQuarterSelect').value);
    });

    // Llama a esta función después de cualquier recarga de tabla o criterios
    // Ejemplo: después de loadEvaluationCriteria(...)
    // asignarListenerGuardarCalificaciones(idSubject, idSchoolYear, idSchoolQuarter);

    // Modificar el botón de guardar
    function guardarCalificaciones(idSubject, idSchoolYear, idSchoolQuarter) {
        if (!idSchoolYear) {
            Swal.fire({ icon: 'warning', title: 'Año escolar requerido', text: 'Por favor selecciona un año escolar antes de guardar.' });
            return;
        }
        if (!idSchoolQuarter) {
            Swal.fire({ icon: 'warning', title: 'Trimestre requerido', text: 'Por favor selecciona un trimestre antes de guardar.' });
            return;
        }
        // Primero guardar criterios
        guardarCriteriosEvaluacion(idSubject, idSchoolYear, idSchoolQuarter)
        .then(res => {
            if (!res.success) throw new Error(res.message || 'Error al guardar criterios');
            // --- ACTUALIZA LOS NUEVOS idEvalCriteria EN LOS SELECTS E INPUTS ---
            if (res.data && Array.isArray(res.data)) {
                res.data.forEach((crit, idx) => {
                    // Actualiza el select
                    const select = document.getElementById(`C${idx + 1}-percentage`);
                    if (select) select.setAttribute('data-criteria-id', crit.idEvalCriteria);
                    // Actualiza los inputs de cada fila de la columna correspondiente
                    document.querySelectorAll(`.grade-input[data-col-index='${idx + 1}']`).forEach(input => {
                        input.setAttribute('data-criteria-id', crit.idEvalCriteria);
                    });
                });
            }
            // Luego guardar calificaciones
            const grades = [];
            const rows = document.querySelectorAll('#dataTable tbody tr');
            rows.forEach(row => {
                const idStudent = row.getAttribute('data-student-id');
                if (!idStudent) return;
                const studentGrades = { idStudent: idStudent, grades: {} };
                const inputs = row.querySelectorAll('.grade-input');
                inputs.forEach((input) => {
                    const select = document.getElementById(`C${input.getAttribute('data-col-index')}-percentage`);
                    const idEvalCriteria = input.getAttribute('data-criteria-id');
                    if (select && select.value && idEvalCriteria) {
                        studentGrades.grades[`C${input.getAttribute('data-col-index')}`] = {
                            grade: input.value,
                            idEvalCriteria: idEvalCriteria,
                            percentage: select.value // <-- AÑADIDO: porcentaje enviado
                        };
                    }
                });
                grades.push(studentGrades);
            });
            if (grades.length === 0 || grades.every(g => Object.keys(g.grades).length === 0)) {
                alert('No se recolectaron calificaciones. Revisa los criterios y los inputs.');
                return;
            }
            // Guardar calificaciones
            return fetch('saveGrades.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    idSubject: idSubject,
                    idSchoolYear: idSchoolYear,
                    idSchoolQuarter: idSchoolQuarter,
                    grades: grades
                })
            });
        })
        .then(response => response ? response.json() : null)
        .then(data => {
            if (!data) return;
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Éxito', text: 'Calificaciones y criterios guardados correctamente' });
                loadGrades(idSubject, idSchoolYear, idSchoolQuarter); // Recarga la tabla y promedios
            } else {
                throw new Error(data.message || 'Error al guardar los datos');
            }
        })
        .catch(error => {
            Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Error al guardar los datos' });
        });
    }

    // --- NUEVA FUNCIÓN: Guardar criterios de evaluación ---
    function guardarCriteriosEvaluacion(idSubject, idSchoolYear, idSchoolQuarter) {
        const criterias = [];
        document.querySelectorAll('.percentage-select').forEach((select, idx) => {
            criterias.push({
                name: `C${idx + 1}`,
                percentage: parseInt(select.value) || 0
            });
        });
        return fetch('saveEvaluationCriteria.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                idSubject: idSubject,
                idSchoolYear: idSchoolYear,
                idSchoolQuarter: idSchoolQuarter,
                criterias: criterias
            })
        }).then(response => response.json());
    }

    // --- MODIFICAR guardarCalificaciones para guardar criterios antes de calificaciones ---
    function guardarCalificaciones(idSubject, idSchoolYear, idSchoolQuarter) {
        if (!idSchoolYear) {
            Swal.fire({ icon: 'warning', title: 'Año escolar requerido', text: 'Por favor selecciona un año escolar antes de guardar.' });
            return;
        }
        if (!idSchoolQuarter) {
            Swal.fire({ icon: 'warning', title: 'Trimestre requerido', text: 'Por favor selecciona un trimestre antes de guardar.' });
            return;
        }
        // Primero guardar criterios
        guardarCriteriosEvaluacion(idSubject, idSchoolYear, idSchoolQuarter)
        .then(res => {
            if (!res.success) throw new Error(res.message || 'Error al guardar criterios');
            // --- ACTUALIZA LOS NUEVOS idEvalCriteria EN LOS SELECTS E INPUTS ---
            if (res.data && Array.isArray(res.data)) {
                res.data.forEach((crit, idx) => {
                    // Actualiza el select
                    const select = document.getElementById(`C${idx + 1}-percentage`);
                    if (select) select.setAttribute('data-criteria-id', crit.idEvalCriteria);
                    // Actualiza los inputs de cada fila de la columna correspondiente
                    document.querySelectorAll(`.grade-input[data-col-index='${idx + 1}']`).forEach(input => {
                        input.setAttribute('data-criteria-id', crit.idEvalCriteria);
                    });
                });
            }
            // Luego guardar calificaciones
            const grades = [];
            const rows = document.querySelectorAll('#dataTable tbody tr');
            rows.forEach(row => {
                const idStudent = row.getAttribute('data-student-id');
                if (!idStudent) return;
                const studentGrades = { idStudent: idStudent, grades: {} };
                const inputs = row.querySelectorAll('.grade-input');
                inputs.forEach((input) => {
                    const select = document.getElementById(`C${input.getAttribute('data-col-index')}-percentage`);
                    const idEvalCriteria = input.getAttribute('data-criteria-id');
                    if (select && select.value && idEvalCriteria) {
                        studentGrades.grades[`C${input.getAttribute('data-col-index')}`] = {
                            grade: input.value,
                            idEvalCriteria: idEvalCriteria,
                            percentage: select.value // <-- AÑADIDO: porcentaje enviado
                        };
                    }
                });
                grades.push(studentGrades);
            });
            if (grades.length === 0 || grades.every(g => Object.keys(g.grades).length === 0)) {
                alert('No se recolectaron calificaciones. Revisa los criterios y los inputs.');
                return;
            }
            // Guardar calificaciones
            return fetch('saveGrades.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    idSubject: idSubject,
                    idSchoolYear: idSchoolYear,
                    idSchoolQuarter: idSchoolQuarter,
                    grades: grades
                })
            });
        })
        .then(response => response ? response.json() : null)
        .then(data => {
            if (!data) return;
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Éxito', text: 'Calificaciones y criterios guardados correctamente' });
                loadGrades(idSubject, idSchoolYear, idSchoolQuarter); // Recarga la tabla y promedios
            } else {
                throw new Error(data.message || 'Error al guardar los datos');
            }
        })
        .catch(error => {
            Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Error al guardar los datos' });
        });
    }

    // --- Cargar calificaciones automáticamente al seleccionar año y trimestre ---
    function checkAndLoadGrades() {
        const idSubject = <?php echo $idSubject; ?>;
        const yearSelect = document.getElementById('schoolYearSelect');
        const quarterSelect = document.getElementById('schoolQuarterSelect');
        const idSchoolYear = yearSelect ? yearSelect.value : '';
        const idSchoolQuarter = quarterSelect ? quarterSelect.value : '';
        if (idSubject && idSchoolYear && idSchoolQuarter) {
            loadGrades(idSubject, idSchoolYear, idSchoolQuarter);
        }
    }

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        checkAndLoadGrades();
    });
    // Ejecutar cada vez que cambie el año escolar o trimestre
    document.getElementById('schoolYearSelect').addEventListener('change', checkAndLoadGrades);
    document.getElementById('schoolQuarterSelect').addEventListener('change', checkAndLoadGrades);

    // Refuerza la asignación de data-criteria-id a los inputs después de cargar criterios y de que la tabla esté lista
    function asignarCriteriaIdInputs() {
        // Obtener criterios actuales del DOM
        document.querySelectorAll('.percentage-select').forEach((select, idx) => {
            const idEvalCriteria = select.getAttribute('data-criteria-id');
            const colIndex = idx + 1;
            document.querySelectorAll(`.grade-input[data-col-index='${colIndex}']`).forEach(input => {
                if (idEvalCriteria) {
                    input.setAttribute('data-criteria-id', idEvalCriteria);
                }
            });
        });
    }

    // Llama a esta función después de cargar criterios y después de generar la tabla
    // Ejemplo: después de loadEvaluationCriteria(...)
    // asignarCriteriaIdInputs();

    // NUEVA FUNCIÓN: Cargar todo
    function cargarTodo(idSubject, idSchoolYear, idSchoolQuarter) {
        loadEvaluationCriteria(idSubject, idSchoolYear, idSchoolQuarter, function() {
            loadGrades(idSubject, idSchoolYear, idSchoolQuarter);
        });
    }

    // Llama a cargarTodo cuando selecciones materia, ciclo y trimestre
    document.getElementById('schoolQuarterSelect').addEventListener('change', function() {
        const selectedQuarter = this.value;
        const selectedYear = document.getElementById('schoolYearSelect').value;
        const idSubject = <?php echo $idSubject; ?>;
        
        if (selectedQuarter && selectedYear) {
            cargarTodo(idSubject, selectedYear, selectedQuarter);
        } else {
            resetTableStructure();
            document.querySelectorAll('.grade-input').forEach(input => {
                input.value = '';
            });
        }
    });

    // Mostrar el select de trimestre cuando se seleccione un año escolar
    document.getElementById('schoolYearSelect').addEventListener('change', function() {
        const tbody = document.querySelector('#dataTable tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="100%" class="text-center">Seleccione un año escolar y luego un trimestre para ver los estudiantes.</td></tr>';
        }
        const selectedYear = this.value;
        const quarterSelectContainer = document.getElementById('quarterSelectContainer');
        const quarterSelect = document.getElementById('schoolQuarterSelect');
        // Limpiar y ocultar el select de trimestre si no hay año escolar seleccionado
        if (!selectedYear) {
            quarterSelectContainer.style.display = 'none';
            quarterSelect.innerHTML = '<option value="" disabled selected>Seleccione un trimestre</option>';
            return;
        }
        // Mostrar y cargar los trimestres para el año escolar seleccionado
        fetch(`get_quarters.php?idSchoolYear=${selectedYear}`)
            .then(response => response.json())
            .then(data => {
                quarterSelect.innerHTML = '<option value="" disabled selected>Seleccione un trimestre</option>';
                if (data.success && data.quarters.length > 0) {
                    data.quarters.forEach(quarter => {
                        const option = document.createElement('option');
                        option.value = quarter.idSchoolQuarter;
                        option.textContent = quarter.name;
                        quarterSelect.appendChild(option);
                    });
                    quarterSelectContainer.style.display = 'block';
                } else {
                    quarterSelectContainer.style.display = 'none';
                }
            });
    });

    // --- FUNCIÓN PARA CALCULAR Y MOSTRAR PROMEDIO DINÁMICO ---
    function calcularPromedioFila(row) {
        let sum = 0;
        let sumPercent = 0;
        const inputs = row.querySelectorAll('.grade-input');
        inputs.forEach(input => {
            const grade = parseFloat(input.value);
            const colIndex = input.getAttribute('data-col-index');
            const select = document.getElementById(`C${colIndex}-percentage`);
            const percent = select ? parseFloat(select.value) : 0;
            if (!isNaN(grade) && !isNaN(percent)) {
                sum += grade * (percent / 100);
                sumPercent += percent;
            }
        });
        const promedioCell = row.querySelector('td:last-child');
        if (sumPercent > 0) {
            promedioCell.textContent = (sum).toFixed(2);
        } else {
            promedioCell.textContent = '-';
        }
    }

    // Escucha cambios en inputs y selects para actualizar promedio dinámico
    // (agrega este listener una sola vez)
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('grade-input') || e.target.classList.contains('percentage-select')) {
            document.querySelectorAll('#dataTable tbody tr').forEach(row => calcularPromedioFila(row));
        }
    });
    </script>

</body>
</html>