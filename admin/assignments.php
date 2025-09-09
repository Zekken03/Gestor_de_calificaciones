<?php
require_once "check_session.php";
include '../conection.php';
// GRUPOS
$sqlGroups = "SELECT idGroup, CONCAT(grade, group_) as grupo FROM groups ORDER BY grade, group_";
$resultGroups1 = $conexion->query($sqlGroups); // Para el primer select
$resultGroups2 = $conexion->query($sqlGroups); // Para el segundo select si lo necesitas
// MATERIAS
$sqlSubjects1 = "SELECT idSubject, name FROM subjects ORDER BY name";
$resultSubjects1 = $conexion->query($sqlSubjects1);
$resultSubjects2 = $conexion->query($sqlSubjects1);
// DOCENTES
$sqlTeachers1 = "SELECT t.idTeacher, CONCAT(ui.names, ' ', ui.lastnamePa, ' ', ui.lastnameMa) AS nombre FROM teachers t INNER JOIN users u ON t.idUser = u.idUser INNER JOIN usersInfo ui ON u.idUserInfo = ui.idUserInfo ORDER BY ui.names, ui.lastnamePa, ui.lastnameMa";
$resultTeachers1 = $conexion->query($sqlTeachers1);
$resultTeachers2 = $conexion->query($sqlTeachers1);
// CICLOS ESCOLARES
$sqlYears1 = "SELECT idSchoolYear, LEFT(startDate, 4) as year FROM schoolYear ORDER BY startDate DESC";
$resultYears1 = $conexion->query($sqlYears1);
$resultYears2 = $conexion->query($sqlYears1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignaciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/stylesBoot.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin/assignment.css">
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
    <!-- Preloader -->
    <div id="preloader">
        <img src="../img/logo.webp" alt="Cargando..." class="logo">
    </div>
    <!-- ASIDEBAR -->
    <?php
        include "../layouts/aside.php"; 
    ?>
    <!-- END ASIDEBAR -->
    <!-- MAIN CONTENT -->
     <main class="flex-grow-1 col-9 p-0 ">
        <?php
            include "../layouts/header.php"; 
        ?>
        <div class="container mt-4" style="padding-top:10vh">
    <div class="row">
        <!-- Sección izquierda (formulario principal) -->
        <form id="form"action="./addAssignment.php" style="width: 65%;" method="POST" class="needs-validation" novalidate>
        <div class="col-md-12 pe-2"> <!-- Añadido padding-right -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label id="labelAlumno" for="grupo" class="form-label fw-bold">Grupo:</label>
                    <?php
                    $sqlGroups = "SELECT idGroup, CONCAT(grade, group_) as grupo FROM groups ORDER BY grade, group_";
                    $resultGroups = $conexion->query($sqlGroups);
                    ?>
                    <select class="form-select border-dark" id="grupo" name="grupo">
                        <option value="" selected>Seleccionar grupo</option>
                        <?php while($group = $resultGroups->fetch_assoc()) { ?>
                            <option value="<?php echo $group['idGroup']; ?>"><?php echo htmlspecialchars($group['grupo']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label id="labelAlumno" for="materia" class="form-label fw-bold">Materia:</label>
                    <?php
                    $sqlSubjects = "SELECT idSubject, name FROM subjects ORDER BY name";
                    $resultSubjects = $conexion->query($sqlSubjects);
                    ?>
                    <select class="form-select border-dark" id="materia" name="materia">
                        <option value="" selected>Seleccionar materia</option>
                        <?php while($subject = $resultSubjects->fetch_assoc()) { ?>
                            <option value="<?php echo $subject['idSubject']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label id="labelAlumno" for="docente" class="form-label fw-bold">Docente:</label>
                    <?php
                    $sqlTeachers = "SELECT t.idTeacher, CONCAT(ui.names, ' ', ui.lastnamePa, ' ', ui.lastnameMa) AS nombre FROM teachers t INNER JOIN users u ON t.idUser = u.idUser INNER JOIN usersInfo ui ON u.idUserInfo = ui.idUserInfo ORDER BY ui.names, ui.lastnamePa, ui.lastnameMa";
                    $resultTeachers = $conexion->query($sqlTeachers);
                    ?>
                    <select class="form-select border-dark" id="docente" name="docente">
                        <option value="" selected>Seleccionar docente</option>
                        <?php while($teacher = $resultTeachers->fetch_assoc()) { ?>
                            <option value="<?php echo $teacher['idTeacher']; ?>"><?php echo htmlspecialchars($teacher['nombre']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label id="labelAlumno" for="ciclo" class="form-label fw-bold">Ciclo Escolar:</label>
                    <?php
                    $sqlYears = "SELECT idSchoolYear, LEFT(startDate, 4) as year FROM schoolYear ORDER BY startDate DESC";
                    $resultYears = $conexion->query($sqlYears);
                    ?>
                    <select class="form-select border-dark" id="ciclo" name="ciclo">
                        <option value="" selected>Seleccionar ciclo escolar</option>
                        <?php while($year = $resultYears->fetch_assoc()) { ?>
                            <option value="<?php echo $year['idSchoolYear']; ?>"><?php echo htmlspecialchars($year['year']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <button id="button" type="submit">
                    Asignar <i id="iconoAdd" class="bi bi-plus"></i>
                </button>
            </div>
        </div>
        </form>
        
        <!-- Sección derecha (búsqueda) -->
        <div class="col-md-4">
            <div class="mb-3">
                <label id="labelAlumno" for="busqueda" class="form-label fw-bold">Búsqueda por:</label>
                <select class="form-select border-dark" id="busqueda">
                    <option value="" selected>Buscar</option>
                    <option value="1">Grupo</option>
                    <option value="2">Maestro</option>
                    <option value="3">Materia</option>
                </select>
            </div>

            <!-- SELECT GRUPO -->
            <div class="mb-3 d-none" id="divGrupo">
                <label id="labelAlumno" for="selectGrupo" class="form-label">Seleccionar Grupo:</label>
                <?php
                $sqlGroups = "SELECT idGroup, CONCAT(grade, group_) as grupo FROM groups ORDER BY grade, group_";
                $resultGroups = $conexion->query($sqlGroups);
                ?>
                <select class="form-select border-dark" id="selectGrupo">
                    <option value="" selected disabled>Seleccionar grupo</option>
                    <?php while($group = $resultGroups->fetch_assoc()) { ?>
                        <option value="<?php echo $group['idGroup']; ?>"><?php echo htmlspecialchars($group['grupo']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- SELECT MAESTRO -->
            <div class="mb-3 d-none" id="divMaestro">
                <label id="labelAlumno" for="selectMaestro" class="form-label">Seleccionar Maestro:</label>
                <?php
                $sqlTeachers = "SELECT t.idTeacher, CONCAT(ui.names, ' ', ui.lastnamePa, ' ', ui.lastnameMa) AS nombre FROM teachers t INNER JOIN users u ON t.idUser = u.idUser INNER JOIN usersInfo ui ON u.idUserInfo = ui.idUserInfo ORDER BY ui.names, ui.lastnamePa, ui.lastnameMa";
                $resultTeachers = $conexion->query($sqlTeachers);
                ?>
                <select class="form-select border-dark" id="selectMaestro">
                    <option value="" selected disabled>Seleccionar maestro</option>
                    <?php while($teacher = $resultTeachers->fetch_assoc()) { ?>
                        <option value="<?php echo $teacher['idTeacher']; ?>"><?php echo htmlspecialchars($teacher['nombre']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- SELECT MATERIA -->
            <div class="mb-3 d-none" id="divMateria">
                <label id="labelAlumno" for="selectMateria" class="form-label">Seleccionar Materia:</label>
                <?php
                $sqlSubjects = "SELECT idSubject, name FROM subjects ORDER BY name";
                $resultSubjects = $conexion->query($sqlSubjects);
                ?>
                <select class="form-select border-dark" id="selectMateria">
                    <option value="" selected disabled>Seleccionar materia</option>
                    <?php while($subject = $resultSubjects->fetch_assoc()) { ?>
                        <option value="<?php echo $subject['idSubject']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            
            <div class="d-flex justify-content-end mb-3">
                <button id="buttonBuscar">
                    Buscar <i id="iBuscar" class="bi bi-search"></i>
                </button>
                <button id="buttonReset" type="button" class="ms-2 btn btn-secondary">
                    Mostrar todo
                </button>
            </div>
            
        </div>
        
    </div>
    
    <h1>Asignaciones</h1>

</div>

        <div id="tabla" class="container mt-4">
            <table class="table table-bordered border-dark">
                <thead>
                    <tr>
                        <th>Ciclo Escolar</th>
                        <th>Grupo</th>
                        <th>Materia</th>
                        <th>Paterno</th>
                        <th>Materno</th>
                        <th>Nombre</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <?php
                    // Filtro PHP para mostrar solo los resultados buscados (solo para carga inicial)
                    $where = '';
                    if (isset($_GET['buscar']) && isset($_GET['valor'])) {
                        $buscar = $_GET['buscar'];
                        $valor = $_GET['valor'];
                        if ($buscar === 'grupo') {
                            $where = " AND g.idGroup = '" . $conexion->real_escape_string($valor) . "'";
                        } else if ($buscar === 'maestro') {
                            $where = " AND t.idTeacher = '" . $conexion->real_escape_string($valor) . "'";
                        } else if ($buscar === 'materia') {
                            $where = " AND sub.idSubject = '" . $conexion->real_escape_string($valor) . "'";
                        }
                    }
                    $sql = "SELECT DISTINCT
                        syear.idSchoolYear, 
                        LEFT(syear.startDate, 4) AS ciclo,
                        g.idGroup, CONCAT(g.grade, g.group_) as grupo, 
                        sub.idSubject, sub.name as materia,
                        ui.lastnamePa, ui.lastnameMa, ui.names,
                        t.idTeacher
                    FROM teacherGroupsSubjects tgs
                    INNER JOIN groups g ON tgs.idGroup = g.idGroup
                    INNER JOIN subjects sub ON tgs.idSubject = sub.idSubject
                    INNER JOIN teachers t ON tgs.idTeacher = t.idTeacher
                    INNER JOIN users u ON t.idUser = u.idUser
                    INNER JOIN usersInfo ui ON u.idUserInfo = ui.idUserInfo
                    INNER JOIN teacherSubject ts ON ts.idTeacher = tgs.idTeacher AND ts.idSubject = tgs.idSubject
                    INNER JOIN schoolYear syear ON ts.idSchoolYear = syear.idSchoolYear
                    WHERE 1 $where
                    GROUP BY syear.idSchoolYear, g.idGroup, sub.idSubject, t.idTeacher
                    ORDER BY syear.startDate DESC, grupo, materia, ui.lastnamePa, ui.lastnameMa, ui.names";
                    $result = $conexion->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $uid = $row['idGroup'] . '-' . $row['idSubject'] . '-' . $row['idTeacher'] . '-' . $row['idSchoolYear'];
                            echo '<tr '
                                . 'data-idgrupo="' . htmlspecialchars($row['idGroup']) . '" '
                                . 'data-idsubject="' . htmlspecialchars($row['idSubject']) . '" '
                                . 'data-idteacher="' . htmlspecialchars($row['idTeacher']) . '" '
                                . 'data-idyear="' . htmlspecialchars($row['idSchoolYear']) . '"'
                                . '>';
                            echo '<td>' . htmlspecialchars($row['ciclo']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['grupo']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['materia']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['lastnamePa']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['lastnameMa']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['names']) . '</td>';
                            echo '<td><button class="botonVerEdit" id="botonVer" data-bs-toggle="modal" data-bs-target="#editModal" data-uid="' . $uid . '" style="margin-left: 10vh;"><i class="bi bi-pencil-fill"></i></button></td>';
                            echo '<td><button class="botonVerDelete" id="botonVer" data-bs-toggle="modal" data-bs-target="#deleteModal" data-uid="' . $uid . '" style="margin-left: 10vh;"><i class="bi bi-trash-fill"></i></button></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="8">No hay asignaciones registradas.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
     </main>
    <!-- END MAIN CONTENT --> 
        <!-- MODAL EDIT-->
        <div class="modal fade modal-m" id="editModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 id="tituloModal" class="modal-title fs-5" id="exampleModalLabel">Editar Asignaciones:</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formEditAssignment">
                    <div class="row px-4">
                        <div class="col-md-12" style="padding-right: 25vh;">
                            <div class="mb-3">
                                <label class="labelAgregar"for="grupo" class="form-label fw-bold">Grupo:</label>
                                <?php
                                $sqlGroups = "SELECT idGroup, CONCAT(grade, group_) as grupo FROM groups ORDER BY grade, group_";
                                $resultGroups = $conexion->query($sqlGroups);
                                ?>
                                <select class="form-select border-dark" name="grupo">
                                    <option value="">Seleccionar Grupo</option>
                                    <?php while($group = $resultGroups->fetch_assoc()) { ?>
                                        <option value="<?php echo $group['idGroup']; ?>"><?php echo htmlspecialchars($group['grupo']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="labelAgregar"for="materia" class="form-label fw-bold">Materia:</label>
                                <?php
                                $sqlSubjects = "SELECT idSubject, name FROM subjects ORDER BY name";
                                $resultSubjects = $conexion->query($sqlSubjects);
                                ?>
                                <select class="form-select border-dark" name="materia">
                                    <option value="">Seleccionar Materia</option>
                                    <?php while($subject = $resultSubjects->fetch_assoc()) { ?>
                                        <option value="<?php echo $subject['idSubject']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="labelAgregar"for="docente" class="form-label fw-bold">Docente:</label>
                                <?php
                                $sqlTeachers = "SELECT t.idTeacher, CONCAT(ui.names, ' ', ui.lastnamePa, ' ', ui.lastnameMa) AS nombre FROM teachers t INNER JOIN users u ON t.idUser = u.idUser INNER JOIN usersInfo ui ON u.idUserInfo = ui.idUserInfo ORDER BY ui.names, ui.lastnamePa, ui.lastnameMa";
                                $resultTeachers = $conexion->query($sqlTeachers);
                                ?>
                                <select class="form-select border-dark" name="docente">
                                    <option value="">Seleccionar Docente</option>
                                    <?php while($teacher = $resultTeachers->fetch_assoc()) { ?>
                                        <option value="<?php echo $teacher['idTeacher']; ?>"><?php echo htmlspecialchars($teacher['nombre']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="labelAgregar"for="ciclo" class="form-label fw-bold">Ciclo Escolar:</label>
                                <?php
                                $sqlYears = "SELECT idSchoolYear, CONCAT(LEFT(startDate, 4)) as year FROM schoolYear ORDER BY startDate DESC";
                                $resultYears = $conexion->query($sqlYears);
                                ?>
                                <select class="form-select border-dark" name="ciclo">
                                    <option value="">Seleccionar Ciclo Escolar</option>
                                    <?php while($year = $resultYears->fetch_assoc()) { ?>
                                        <option value="<?php echo $year['idSchoolYear']; ?>"><?php echo htmlspecialchars($year['year']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="padding-left:5vh;">
                                <button class="botonEnter btn btn-primary fw-bold mb-3">
                                    Reasignar <i id="iconoAdd" class="bi bi-check-circle-fill"></i>
                                </button>
                            </div>  
                        </div>
                    </div> 
                    </form>
                </div>
            </div>
        </div>
        <!-- MODAL EDIT-->
    <!-- MODAL delete-->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 id="tituloModal"class="modal-title fs-5" id="exampleModalLabel">¿Desea Eliminar Esta Asignación?</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-footer">
                        <button class="botonCancelar" type="button" class="btn btn-secondary"data-bs-dismiss="modal">Cancelar
                            <i id="iconoAdd" class="bi bi-x-circle-fill"></i>
                        </button>
                        <button class="botonEnter" type="submit" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#confirmModal">Eliminar<i id="iconoAdd" class="bi bi-trash3-fill"></i></i></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- MODAL delete-->

        <!-- MODAL confirm delete-->
        <div class="modal fade " id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 id="tituloModal" class="modal-title fs-5" id="exampleModalLabel">¿Está Seguro Que Desea Eliminar Esta Asignación?</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-footer">
                        <button class="botonCancelar" type="button" class="btn btn-secondary"data-bs-dismiss="modal">Cambié de Opinión
                            <i id="iconoAdd" class="bi bi-x-circle-fill"></i>
                        </button>
                        <button class="botonEnter" type="submit" class="btn btn-primary btnEliminar"  data-bs-toggle="modal" data-bs-target="" id="eliminar">Eliminar<i id="iconoAdd" class="bi bi-trash3-fill"></i></i></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- MODAL confirm delete-->
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
        document.addEventListener('DOMContentLoaded', function () {
            // Asignar eventos CRUD inicialmente
            asignarEventosCRUD();

            // Limpiar parámetros de búsqueda de la URL (AJAX-first UX)
            if(window.location.search.includes('buscar=') || window.location.search.includes('valor=')){
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Función para asignar eventos CRUD
            function asignarEventosCRUD() {
                // Asignar eventos de edición
                document.querySelectorAll('.botonVerEdit').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const row = this.closest('tr');
                        // Obtener ids de la fila
                        const idGrupo = row.getAttribute('data-idgrupo');
                        const idSubject = row.getAttribute('data-idsubject');
                        const idTeacher = row.getAttribute('data-idteacher');
                        const idYear = row.getAttribute('data-idyear');

                        // Obtener los textos de las celdas
                        const txtGrupo = row.children[1].textContent.trim();
                        const txtMateria = row.children[2].textContent.trim();
                        const txtCiclo = row.children[0].textContent.trim();
                        
                        // Construir el nombre del docente
                        const apellidoPa = row.children[3].textContent.trim();
                        const apellidoMa = row.children[4].textContent.trim();
                        const nombre = row.children[5].textContent.trim();

                        // Rellenar selects del modal
                        const selectGrupo = document.querySelector('#editModal select[name="grupo"]');
                        const selectMateria = document.querySelector('#editModal select[name="materia"]');
                        const selectDocente = document.querySelector('#editModal select[name="docente"]');
                        const selectCiclo = document.querySelector('#editModal select[name="ciclo"]');

                        // Función helper para seleccionar la opción correcta
                        const setSelectedOption = (select, value, text) => {
                            for(let option of select.options) {
                                if(option.value === value || option.textContent.trim() === text) {
                                    option.selected = true;
                                    break;
                                }
                            }
                        };

                        // Establecer los valores seleccionados
                        setSelectedOption(selectGrupo, idGrupo, txtGrupo);
                        setSelectedOption(selectMateria, idSubject, txtMateria);
                        setSelectedOption(selectDocente, idTeacher, `${nombre} ${apellidoPa} ${apellidoMa}`);
                        setSelectedOption(selectCiclo, idYear, txtCiclo);

                        // Guardar valores originales para update
                        const modal = document.querySelector('#editModal');
                        modal.setAttribute('data-old-grupo', idGrupo);
                        modal.setAttribute('data-old-materia', idSubject);
                        modal.setAttribute('data-old-docente', idTeacher);
                        modal.setAttribute('data-old-year', idYear);
                    });
                });

                // Asignar eventos de eliminación
                document.querySelectorAll('.botonVerDelete').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const modal = document.getElementById('confirmModal');
                        // Store the row reference on the modal
                        modal._row = row;
                        // Also store the data attributes for backward compatibility
                        modal.setAttribute('data-idgrupo', row.getAttribute('data-idgrupo'));
                        modal.setAttribute('data-idsubject', row.getAttribute('data-idsubject'));
                        modal.setAttribute('data-idteacher', row.getAttribute('data-idteacher'));
                        modal.setAttribute('data-idyear', row.getAttribute('data-idyear'));
                    });
                });
            }

            // Mostrar SweetAlert si viene status por GET
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('status')) {
                let icon = 'success';
                let title = '';
                let text = '';
                if (urlParams.get('status') === 'success') {
                    title = '¡Asignación creada correctamente!';
                    text = '';
                } else if (urlParams.get('status') === 'error') {
                    icon = 'error';
                    title = 'Error';
                    text = urlParams.get('message') || 'Error al procesar la solicitud';
                }
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Limpia la URL para evitar el mensaje al recargar
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            }

            // Filtro de búsqueda por selección con AJAX
            document.getElementById('buttonBuscar').addEventListener('click', function (e) {
                e.preventDefault();
                const tipoBusqueda = document.getElementById('busqueda').value;
                let valor = null;
                let param = '';
                if (tipoBusqueda === '1') {
                    valor = document.getElementById('selectGrupo').value;
                    param = 'grupo';
                } else if (tipoBusqueda === '2') {
                    valor = document.getElementById('selectMaestro').value;
                    param = 'maestro';
                } else if (tipoBusqueda === '3') {
                    valor = document.getElementById('selectMateria').value;
                    param = 'materia';
                }
                if (valor) {
                    // AJAX fetch
                    fetch('searchAssignments.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `buscar=${encodeURIComponent(param)}&valor=${encodeURIComponent(valor)}`
                    })
                    .then(response => response.text())
                    .then(html => {
                        const tbody = document.querySelector('#tabla tbody');
                        tbody.innerHTML = html;
                        asignarEventosCRUD();
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo obtener los resultados.'
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Seleccione un filtro',
                        text: 'Debes elegir un valor para buscar.'
                    });
                }
            });

            // Botón para mostrar toda la tabla (reset)
            document.getElementById('buttonReset').addEventListener('click', function () {
                fetch('searchAssignments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const tbody = document.querySelector('#tabla tbody');
                    tbody.innerHTML = html;
                    
                    // Reasignar los eventos CRUD
                    asignarEventosCRUD();
                    
                    // Limpiar selects de filtro
                    document.getElementById('busqueda').value = '';
                    if(document.getElementById('divGrupo')) document.getElementById('divGrupo').classList.add('d-none');
                    if(document.getElementById('divMaestro')) document.getElementById('divMaestro').classList.add('d-none');
                    if(document.getElementById('divMateria')) document.getElementById('divMateria').classList.add('d-none');
                })
                .catch((error) => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo obtener los resultados.'
                    });
                });
            });

            // Evento para actualizar asignación
            document.getElementById('formEditAssignment').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const data = new FormData(form);
                // Agregar valores originales
                data.append('old_grupo', document.querySelector('#editModal').getAttribute('data-old-grupo'));
                data.append('old_materia', document.querySelector('#editModal').getAttribute('data-old-materia'));
                data.append('old_docente', document.querySelector('#editModal').getAttribute('data-old-docente'));
                fetch('updateAssignment.php', {
                    method: 'POST',
                    body: data
                })
                .then(res => res.json())
                .then(res => {
                    if(res.success){
                        // Actualizar la fila en la tabla sin recargar
                        const idGrupo = form.querySelector('select[name="grupo"]').value;
                        const idMateria = form.querySelector('select[name="materia"]').value;
                        const idDocente = form.querySelector('select[name="docente"]').value;
                        const idCiclo = form.querySelector('select[name="ciclo"]').value;
                        
                        // Obtener los textos seleccionados
                        const txtGrupo = form.querySelector('select[name="grupo"] option:checked').textContent.trim();
                        const txtMateria = form.querySelector('select[name="materia"] option:checked').textContent.trim();
                        const txtDocente = form.querySelector('select[name="docente"] option:checked').textContent.trim();
                        const txtCiclo = form.querySelector('select[name="ciclo"] option:checked').textContent.trim();
                        
                        // Buscar la fila original por ids antiguos
                        const tr = document.querySelector(`#tabla tbody tr[data-idgrupo='${document.querySelector('#editModal').getAttribute('data-old-grupo')}'][data-idsubject='${document.querySelector('#editModal').getAttribute('data-old-materia')}'][data-idteacher='${document.querySelector('#editModal').getAttribute('data-old-docente')}']`);
                        
                        if(tr) {
                            tr.setAttribute('data-idgrupo', idGrupo);
                            tr.setAttribute('data-idsubject', idMateria);
                            tr.setAttribute('data-idteacher', idDocente);
                            tr.setAttribute('data-idyear', idCiclo);
                            
                            // Actualizar celdas visibles
                            tr.children[0].textContent = txtCiclo;
                            tr.children[1].textContent = txtGrupo;
                            tr.children[2].textContent = txtMateria;
                            
                            // Procesar el nombre del docente correctamente
                            const nombreCompleto = txtDocente.split(' ');
                            // Asumimos que el formato es: nombres apellidoPaterno apellidoMaterno
                            const nombres = nombreCompleto.slice(0, -2).join(' '); // Todo excepto los últimos dos elementos
                            const apellidoPaterno = nombreCompleto[nombreCompleto.length - 2] || '';
                            const apellidoMaterno = nombreCompleto[nombreCompleto.length - 1] || '';
                            
                            tr.children[3].textContent = apellidoPaterno;
                            tr.children[4].textContent = apellidoMaterno;
                            tr.children[5].textContent = nombres;
                        }
                        
                        // Cerrar modal
                        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal'));
                        modal.hide();
                        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: 'La asignación fue actualizada correctamente.' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'No se pudo actualizar.' });
                    }
                })
                .catch(()=>{
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar.' });
                });
            });

            // Evento para eliminar asignación
            document.getElementById('eliminar').addEventListener('click', function() {
                const modal = document.getElementById('confirmModal');
                const row = modal._row; // Get the row reference we stored earlier
                
                let idTeacher, idGroup, idSubject, idSchoolYear;
                
                if (row) {
                    // Get data from the row if available
                    idTeacher = row.getAttribute('data-idteacher');
                    idGroup = row.getAttribute('data-idgrupo');
                    idSubject = row.getAttribute('data-idsubject');
                    idSchoolYear = row.getAttribute('data-idyear');
                } else {
                    // Fallback to data attributes on the modal if row reference is not available
                    idTeacher = modal.getAttribute('data-idteacher');
                    idGroup = modal.getAttribute('data-idgrupo');
                    idSubject = modal.getAttribute('data-idsubject');
                    idSchoolYear = modal.getAttribute('data-idyear');
                }
                
                if (!idTeacher || !idGroup || !idSubject || !idSchoolYear) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo encontrar la asignación a eliminar.' });
                    return;
                }
                
                // Store the row reference for later use in the success callback
                const rowToRemove = row || document.querySelector(`#tabla tbody tr[data-idgrupo='${idGroup}'][data-idsubject='${idSubject}'][data-idteacher='${idTeacher}']`);
                
                // Proceed with the delete
                fetch('deleteAssignment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `idTeacher=${encodeURIComponent(idTeacher)}&idGroup=${encodeURIComponent(idGroup)}&idSubject=${encodeURIComponent(idSubject)}&idSchoolYear=${encodeURIComponent(idSchoolYear)}`
                })
                .then(res => res.json())
                .then(res => {
                    if(res.success){
                        // Remove the row from the table if it exists
                        if (rowToRemove && rowToRemove.parentNode) {
                            rowToRemove.remove();
                        } else {
                            // If we couldn't find the exact row, try to find it again
                            const tr = document.querySelector(`#tabla tbody tr[data-idgrupo='${idGroup}'][data-idsubject='${idSubject}'][data-idteacher='${idTeacher}']`);
                            if (tr && tr.parentNode) {
                                tr.remove();
                            }
                        }
                        
                        // Close the modal
                        const modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                        modalInstance.hide();
                        
                        // Show success message
                        Swal.fire({ 
                            icon: 'success', 
                            title: '¡Eliminado!', 
                            text: 'La asignación fue eliminada correctamente.' 
                        });
                    } else {
                        Swal.fire({ 
                            icon: 'error', 
                            title: 'Error', 
                            text: res.message || 'No se pudo eliminar la asignación.' 
                        });
                    }
                })
                .catch((error) => {
                    console.error('Error al eliminar la asignación:', error);
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error', 
                        text: 'Ocurrió un error al intentar eliminar la asignación. Por favor, inténtalo de nuevo.' 
                    });
                });
            });
        });
    </script>

    <script>
        document.getElementById('busqueda').addEventListener('change', function () {
            document.getElementById('divGrupo').classList.add('d-none');
            document.getElementById('divMaestro').classList.add('d-none');
            document.getElementById('divMateria').classList.add('d-none');
            const seleccion = this.value;
            if (seleccion === '1') {
                document.getElementById('divGrupo').classList.remove('d-none');
            } else if (seleccion === '2') {
                document.getElementById('divMaestro').classList.remove('d-none');
            } else if (seleccion === '3') {
                document.getElementById('divMateria').classList.remove('d-none');
            }
        });
    </script>
</body>
</html>