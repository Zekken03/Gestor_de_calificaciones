<?php
require_once "check_session.php";
require_once "../conection.php";

// Validar que el id esté en la sesión
if (!isset($_SESSION['user_id'])) {
    error_log("Error de sesión: No se encontró el ID del usuario");
    die("Error de autenticación. Por favor, inicie sesión nuevamente.");
}

// Obtener el idTeacher usando el idUser de la sesión
$idUser = $_SESSION['user_id'];
$sqlTeacher = "SELECT idTeacher FROM teachers WHERE idUser = ?";
$stmtTeacher = $conexion->prepare($sqlTeacher);
$stmtTeacher->bind_param("i", $idUser);
$stmtTeacher->execute();
$resTeacher = $stmtTeacher->get_result();
$rowTeacher = $resTeacher->fetch_assoc();
if (!$rowTeacher) {
    error_log("Error: No se encontró el docente para el usuario ID: " . $_SESSION['user_id']);
    die("No se pudo cargar la información del docente. Por favor, contacte al administrador.");
}
$idTeacher = $rowTeacher['idTeacher'];
$stmtTeacher->close();

// Obtener solo los grupos asignados al docente autenticado
$groups = [];
$sqlGroups = "SELECT g.idGroup, g.grade, g.group_\n              FROM teacherGroupsSubjects tgs\n              JOIN groups g ON tgs.idGroup = g.idGroup\n              WHERE tgs.idTeacher = ?\n              GROUP BY g.idGroup, g.grade, g.group_\n              ORDER BY g.grade, g.group_";
$stmtGroups = $conexion->prepare($sqlGroups);
$stmtGroups->bind_param("i", $idTeacher);
$stmtGroups->execute();
$resGroups = $stmtGroups->get_result();
while ($row = $resGroups->fetch_assoc()) {
    $groups[] = $row;
}
$stmtGroups->close();

// Determinar el grupo seleccionado
$selectedGroup = isset($_GET['grupo']) ? intval($_GET['grupo']) : "";

// Obtener alumnos del grupo seleccionado
$students = [];
if ($selectedGroup) {
    $sqlStudents = "SELECT s.schoolNum, ui.lastnamePa, ui.lastnameMa, ui.names, g.grade, g.group_, s.idStudentStatus, s.curp,
        t.tutorName, t.tutorLastnamePa, t.tutorLastnameMa, t.tutorPhone, t.tutorAddress, t.tutorEmail, t.ine as tutorIne,
        st.nomenclature, st.description
        FROM students s
        JOIN usersInfo ui ON s.idUserInfo = ui.idUserInfo
        JOIN groups g ON s.idGroup = g.idGroup
        LEFT JOIN tutors t ON s.idTutor = t.idTutor
        LEFT JOIN studentStatus st ON s.idStudentStatus = st.idStudentStatus
        WHERE s.idGroup = ?";
    $stmt = $conexion->prepare($sqlStudents);
    $stmt->bind_param("i", $selectedGroup);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Alumnos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/stylesBoot.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/teacher/list.css">
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
    <?php
        include "../layouts/asideTeacher.php"; 
    ?>
    <main class="flex-grow-1 col-9 p-0 ">
        <?php
            include "../layouts/headerTeacher.php"; 
        ?> 
        <section class="container mt-4">
            <div id="contenedor" class="mb-3"style="padding-top:15vh; width:30%;">
                <form method="get" action="">
                    <label id="labelDocente" for="grupo" class="form-label fw-bold">Grupo:</label>
                    <select class="form-select border-dark" id="grupo" name="grupo" onchange="this.form.submit()">
                        <option value="" selected>Seleccionar grupo</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?php echo $g['idGroup']; ?>" <?php if ($selectedGroup == $g['idGroup']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($g['grade'] . '° ' . $g['group_']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <h1>Lista de Alumnos</h1>

            <div id="tabla"class="container mt-4">
                <table class="table table-bordered border-dark">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Paterno</th>
                            <th>Materno</th>
                            <th>Nombres</th>
                            <th>CURP</th>
                            <th>Grado</th>
                            <th>Grupo</th>
                            <th>Estado</th>
                            <th>Boleta</th>
                            <th>Ver</th>
                        </tr>
                    </thead>
                    <tbody id="alumnos-tbody">
                        <?php if ($selectedGroup && count($students) > 0): ?>
                            <?php foreach ($students as $i => $student): ?>
                                <tr>    
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($student['lastnamePa']); ?></td>
                                    <td><?php echo htmlspecialchars($student['lastnameMa']); ?></td>
                                    <td><?php echo htmlspecialchars($student['names']); ?></td>
                                    <td><?php echo htmlspecialchars($student['curp'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($student['grade']); ?>°</td>
                                    <td><?php echo htmlspecialchars($student['group_']); ?></td>
                                    <td>
                                        <?php
                                            $nomen = $student['nomenclature'] ?? '';
                                            $desc = $student['description'] ?? '';
                                            $badge = 'secondary';
                                            switch ($nomen) {
                                                case 'AC': $badge = 'success'; break;
                                                case 'BA': $badge = 'danger'; break;
                                                case 'RE': $badge = 'warning'; break;
                                                case 'EG': $badge = 'primary'; break;
                                                case 'IN': $badge = 'secondary'; break;
                                                case 'TR': $badge = 'info'; break;
                                                case 'RC': $badge = 'dark'; break;
                                                case 'EX': $badge = 'light'; break;
                                            }
                                            if ($nomen && $desc) {
                                                echo '<span class="badge bg-' . $badge . '">' . htmlspecialchars($desc) . '</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">-</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" id="botonVer"
                                            data-bs-toggle="modal" data-bs-target="#modalCamposFormativos"
                                            data-id="<?php echo htmlspecialchars($student['schoolNum']); ?>"
                                            data-nombres="<?php echo htmlspecialchars($student['names']); ?>"
                                            data-paterno="<?php echo htmlspecialchars($student['lastnamePa']); ?>"
                                            data-materno="<?php echo htmlspecialchars($student['lastnameMa']); ?>"
                                            data-grade="<?php echo htmlspecialchars($student['grade']); ?>"
                                            data-grupo="<?php echo htmlspecialchars($student['group_']); ?>"
                                            data-curp="<?php echo htmlspecialchars($student['curp'] ?? ''); ?>"
                                            data-tutornombres="<?php echo htmlspecialchars($student['tutorName'] ?? ''); ?>"
                                            data-tutorpaterno="<?php echo htmlspecialchars($student['tutorLastnamePa'] ?? ''); ?>"
                                            data-tutormaterno="<?php echo htmlspecialchars($student['tutorLastnameMa'] ?? ''); ?>"
                                            data-tutoremail="<?php echo htmlspecialchars($student['tutorEmail'] ?? ''); ?>"
                                            data-tutortelefono="<?php echo htmlspecialchars($student['tutorPhone'] ?? ''); ?>"
                                            data-tutordireccion="<?php echo htmlspecialchars($student['tutorAddress'] ?? ''); ?>"
                                            data-tutorine="<?php echo htmlspecialchars($student['tutorIne'] ?? ''); ?>"
                                        >
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <button type="button" id="botonVer"
                                            data-id="<?php echo htmlspecialchars($student['schoolNum']); ?>"
                                            data-nombres="<?php echo htmlspecialchars($student['names']); ?>"
                                            data-paterno="<?php echo htmlspecialchars($student['lastnamePa']); ?>"
                                            data-materno="<?php echo htmlspecialchars($student['lastnameMa']); ?>"
                                            data-status="<?php echo htmlspecialchars($student['idStudentStatus']); ?>"
                                            data-grupo="<?php echo htmlspecialchars($student['group_']); ?>"
                                            data-grade="<?php echo htmlspecialchars($student['grade']); ?>"
                                            data-curp="<?php echo htmlspecialchars($student['curp'] ?? ''); ?>"
                                            data-bs-toggle="modal" data-bs-target="#showModal"
                                            data-tutornombres="<?php echo htmlspecialchars($student['tutorName'] ?? ''); ?>"
                                            data-tutorpaterno="<?php echo htmlspecialchars($student['tutorLastnamePa'] ?? ''); ?>"
                                            data-tutormaterno="<?php echo htmlspecialchars($student['tutorLastnameMa'] ?? ''); ?>"
                                            data-tutoremail="<?php echo htmlspecialchars($student['tutorEmail'] ?? ''); ?>"
                                            data-tutortelefono="<?php echo htmlspecialchars($student['tutorPhone'] ?? ''); ?>"
                                            data-tutordireccion="<?php echo htmlspecialchars($student['tutorAddress'] ?? ''); ?>"
                                            data-tutorine="<?php echo htmlspecialchars($student['tutorIne'] ?? ''); ?>"
                                        >
                                            <i class="bi bi-person-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php elseif($selectedGroup): ?>
                            <tr><td colspan="17" class="text-center">No hay alumnos en este grupo.</td></tr>
                        <?php else: ?>
                            <tr><td colspan="17" class="text-center">Seleccione un grupo para ver los alumnos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
     </main>
    <!-- MODAL SHOW-->
    <div class="modal fade modal-lg" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 id="tituloModal"class="modal-title fs-5" id="exampleModalLabel">Información Personal</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="#" enctype="multipart/form-data" method="post" class="needs-validation" novalidate id="form">
        <div class="modal-body">
            <div class="row">   
                <div class="col-6"style="padding-right: 0;">
                    <label class="labelAgregar"for="txtName">Nombres:</label>
                    <span id="modal-nombres"></span>
                </div>
                <div class="col-6">
                    <label class="labelAgregar"for="txtLastname">Apellidos:</label>
                    <span id="modal-apellidos"></span>
                </div>
            </div>
            <div class="row pt-3">
                <div class="col-6">
                    <label class="labelAgregar"for="txtCurp">CURP:</label>
                    <span id="modal-curp"></span>
                </div>
                <div class="col-6">
                    <label class="labelAgregar"for="txtGrade">Grado:</label> 
                    <span id="modal-grado"></span>
                </div>
            </div>
            <div class="row pt-3">
                <div class="col-6">
                    <label class="labelAgregar"for="txtGroup">Grupo:</label>
                    <span id="modal-grupo"></span>
                </div>

            </div>

        </div>

        <div class="modal-header">
                    <h1 id="tituloModal" class="modal-title fs-5" id="exampleModalLabel">Información del Tutor</h1>
        </div>
        <div class="modal-body">
            <div class="row">   
                <div class="col-6"style="padding-right: 0;">
                    <label class="labelAgregar"for="txtName">Nombres:</label>
                    <span id="modal-tutornombres"></span>
                </div>
                <div class="col-6">
                    <label class="labelAgregar"for="txtLastname">Apellidos:</label>
                    <span id="modal-tutorapellidos"></span>
                </div>
            </div>
            <div class="row pt-3">
                <div class="col-6">
                    <label class="labelAgregar"for="txtIne">INE:</label>
                    <span id="modal-tutorine"></span>
                </div>
                <div class="col-6">
                    <label class="labelAgregar"for="txtEmail">Correo:</label>
                    <span id="modal-tutoremail"></span>
                </div>
            </div>
            <div class="row pt-3">
                <div class="col-6">
                    <label class="labelAgregar"for="txtPhone">Número de Teléfono:</label>
                    <span id="modal-tutortelefono"></span>
                </div>
                <div class="col-6">
                    <label class="labelAgregar"for="txtAddress">Dirección:</label>
                    <span id="modal-tutordireccion"></span>
                </div>
            </div>

        </div>
    </form>

            </div>
        </div>

    </div>
    <!-- MODAL BOLETA -->
    <div class="modal fade" id="modalCamposFormativos" tabindex="-1" aria-labelledby="modalCamposFormativosLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalCamposFormativosLabel">Boleta</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="cicloFormativo" class="form-label">Ciclo Escolar:</label>
                                            <select class="form-select border-dark" id="cicloFormativo">
                                                <option id="subject"value=""selected disabled>Seleccionar ciclo</option>
                                                <option value="2023">2023 - 2024</option>
                                                <option value="2024">2024 - 2025</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 d-none" id="divTrimestreFormativo">
                                            <label for="trimestreFormativo" class="form-label">Trimestre:</label>
                                            <select class="form-select border-dark" id="trimestreFormativo">
                                                <option id="subject"value=""selected disabled>Seleccionar trimestre</option>
                                                <option value="1">Primer Trimestre</option>
                                                <option value="2">Segundo Trimestre</option>
                                                <option value="3">Tercer Trimestre</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 d-none" id="divCamposFormativos">
                                            <h6 class="fw-bold">Campos Formativos</h6>
                                            <ul class="list-group">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Matemáticas
                                                    <span class="badge bg-success rounded-pill">9.5</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Ciencias Naturales
                                                    <span class="badge bg-success rounded-pill">8.7</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Historia
                                                    <span class="badge bg-warning rounded-pill">7.8</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Lengua Española
                                                    <span class="badge bg-danger rounded-pill">6.2</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button id="button"type="button" class="btn btn-primary">Ver detalles</button>
                                    </div>
                                </div>
                            </div>
                        </div>
    <!-- MODAL SHOW-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/chartScript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.js"></script>
        <!-- MOSTRAR TRIMESTRE AL SELECCIONAR EL CICLO (BOLETA)-->
        <script>
        document.getElementById('trimestreFormativo').addEventListener('change', function () {
            document.getElementById('divCamposFormativos').classList.remove('d-none'); 
        });
    </script>
    <!-- REINICIAR LABEL DE LA BOLETA -->
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
        const modalCamposFormativos = document.getElementById('modalCamposFormativos');
        modalCamposFormativos.addEventListener('hidden.bs.modal', function () {
            document.getElementById('cicloFormativo').selectedIndex = 0;
            document.getElementById('trimestreFormativo').selectedIndex = 0;
            document.getElementById('divTrimestreFormativo').classList.add('d-none');
            document.getElementById('divCamposFormativos').classList.add('d-none');
        });
    </script>
    <script>
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('button[data-bs-target="#showModal"]');
            if (btn) {
                // Llenar campos del modal con los data-attributes
                document.getElementById('modal-nombres').textContent = btn.getAttribute('data-nombres') || '';
                document.getElementById('modal-apellidos').textContent = ((btn.getAttribute('data-paterno') || '') + ' ' + (btn.getAttribute('data-materno') || '')).trim();
                document.getElementById('modal-curp').textContent = btn.getAttribute('data-curp') || '';
                document.getElementById('modal-grado').textContent = btn.getAttribute('data-grade') || '';
                document.getElementById('modal-grupo').textContent = btn.getAttribute('data-grupo') || '';
                document.getElementById('modal-tutornombres').textContent = btn.getAttribute('data-tutornombres') || '';
                document.getElementById('modal-tutorapellidos').textContent = ((btn.getAttribute('data-tutorpaterno') || '') + ' ' + (btn.getAttribute('data-tutormaterno') || '')).trim();
                document.getElementById('modal-tutorine').textContent = btn.getAttribute('data-tutorine') || '';
                document.getElementById('modal-tutoremail').textContent = btn.getAttribute('data-tutoremail') || '';
                document.getElementById('modal-tutortelefono').textContent = btn.getAttribute('data-tutortelefono') || '';
                document.getElementById('modal-tutordireccion').textContent = btn.getAttribute('data-tutordireccion') || '';
            }
            const btnBoleta = e.target.closest('button.btn-boleta');
            if (btnBoleta) {
                // Ejemplo: llenar boleta con data-attributes
                const nombres = btnBoleta.getAttribute('data-nombres') || '';
                const apellidos = ((btnBoleta.getAttribute('data-paterno') || '') + ' ' + (btnBoleta.getAttribute('data-materno') || '')).trim();
                const grado = btnBoleta.getAttribute('data-grade') || '';
                const grupo = btnBoleta.getAttribute('data-grupo') || '';
                const id = btnBoleta.getAttribute('data-id') || '';
                document.getElementById('boleta-datos').innerHTML =
                    `<strong>Nombre:</strong> ${nombres} ${apellidos}<br>` +
                    `<strong>No. Control:</strong> ${id}<br>` +
                    `<strong>Grado:</strong> ${grado} &nbsp; <strong>Grupo:</strong> ${grupo}`;
            }
        });
    </script>
</body>
</html>