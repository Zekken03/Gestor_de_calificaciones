<?php
require_once "check_session.php";
include '../conection.php';

// Consulta para obtener la lista de estudiantes con información relacionada
$sql = "SELECT 
    s.idStudent, 
    s.idStudentStatus, 
    ui.names, 
    ui.lastnamePa, 
    ui.lastnameMa, 
    ui.phone, 
    ui.street, 
    ui.gender, 
    ui.email, 
    s.curp, 
    ss.nomenclature, 
    ss.description as status, 
    u.username, 
    u.password,
    s.idGroup,
    CONCAT(g.grade, g.group_) as grupo,
    s.idSchoolYear, 
    LEFT(sy.startDate, 4) as schoolYear,
    t.idTutor,
    t.tutorName,
    t.tutorLastnamePa,
    t.tutorLastnameMa,
    t.tutorPhone,
    t.tutorEmail,
    t.tutorAddress,
    t.ine,
    t.relative_ as tutorRelationship
        FROM students s
        INNER JOIN usersInfo ui ON s.idUserInfo = ui.idUserInfo
        LEFT JOIN users u ON ui.idUserInfo = u.idUserInfo
        INNER JOIN studentStatus ss ON s.idStudentStatus = ss.idStudentStatus
        LEFT JOIN groups g ON s.idGroup = g.idGroup
LEFT JOIN schoolYear sy ON s.idSchoolYear = sy.idSchoolYear
LEFT JOIN tutors t ON s.idTutor = t.idTutor";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        .table td, .table th {
            text-align: center;
            vertical-align: middle;
        }
        .table tbody tr td {
            padding: 1rem;
        }
        .table tbody tr td ul {
            margin: 0;
            padding: 0;
        }
        .table tbody tr td ul li {
            text-align: left;
            margin-bottom: 0.25rem;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin/student.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
    
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
    <?php include "../layouts/aside.php"; ?>
    <!-- END ASIDEBAR -->

    
    <!-- MAIN CONTENT -->
    <main class="flex-grow-1 col-9 p-0">
        <?php include "../layouts/header.php"; ?>
        
        <div class="container mt-4" style="padding-top:12vh">
            <div class="row">
                <!-- Primera fila de búsquedas -->
                <div class="col-md-6">
                    <!-- BUSCAR POR ALUMNO -->
                    <div class="search-container">
                        <label id="labelAlumno" for="alumno" class="form-label fw-bold">Buscar por Alumno:</label>
                        <div class="input-group search-group">
                            <input type="text" class="form-control border-dark" id="alumno" placeholder="Buscar alumno...">
                            <span class="input-group-text bg-white border-dark">
                                <i id="iBuscar" class="bi bi-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <!-- BUSCAR POR AÑO ESCOLAR -->
                    <div class="search-container">
                        <label id="labelAlumno" for="schoolYear" class="form-label fw-bold">Año Escolar:</label>
                        <select class="form-select search-select border-dark" id="schoolYear" name="schoolYear">
                            <option value="">Todos los años</option>
                            <?php
                            $sqlYears = "SELECT idSchoolYear, CONCAT(LEFT(startDate, 4), '-', LEFT(endDate, 4)) as year FROM schoolYear ORDER BY startDate DESC";
                            $resultYears = $conexion->query($sqlYears);
                            while ($year = $resultYears->fetch_assoc()) {
                                $selected = (isset($_GET['year']) && $_GET['year'] == $year['idSchoolYear']) ? 'selected' : '';
                                echo "<option value='" . $year['idSchoolYear'] . "' $selected>" . $year['year'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <!-- Segunda fila de búsquedas -->
                <div class="col-md-6">
                    <!-- BUSCAR POR GRUPO -->
                    <div class="search-container">
                        <label id="labelAlumno" for="grupo" class="form-label fw-bold">Buscar por Grupo:</label>
                        <select class="form-select search-select border-dark" id="grupo" name="grupo">
                            <option value="">Todos los grupos</option>
                            <?php
                            $sqlGroups = "SELECT idGroup, CONCAT(grade, group_) as grupo FROM groups ORDER BY grade, group_";
                            $resultGroups = $conexion->query($sqlGroups);
                            while ($group = $resultGroups->fetch_assoc()) {
                                $selected = (isset($_GET['group']) && $_GET['group'] == $group['idGroup']) ? 'selected' : '';
                                echo "<option value='" . $group['idGroup'] . "' $selected>" . $group['grupo'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <!-- Botón Agregar Alumno -->
                    <div class="d-flex justify-content-end mt-4">
                        <button class="buttonInscribir" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            Inscribir alumno <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Título y Tabla -->
            <div class="row mt-4">
                <div class="col-12">
                    <h1 id="listaAlumnos">Lista de Alumnos</h1>
                    <div id="tabla" class="mt-4 contenedorTabla">
                        <table class="table table-bordered border-dark">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>A. Paterno</th>
                                    <th>A. Materno</th>
                                    <th>Nombres</th>
                                    <th>Grupo</th>
                                    <th>Año Escolar</th>
                                    <th>Estado</th>
                                    <th>Boleta</th>
                                    <th>Ver</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody id="alumnosBody">
                    <?php while ($row = $resultado->fetch_assoc()) { ?>
                        <tr data-schoolyear="<?php echo htmlspecialchars($row['idSchoolYear']); ?>" data-grupo="<?php echo htmlspecialchars($row['idGroup']); ?>">
                            <td><?php echo htmlspecialchars($row['idStudent']); ?></td>
                            <td><?php echo htmlspecialchars($row['lastnamePa']); ?></td>
                            <td><?php echo htmlspecialchars($row['lastnameMa']); ?></td>
                            <td><?php echo htmlspecialchars($row['names']); ?></td>
                            <td><?php echo htmlspecialchars($row['grupo']); ?></td>
                            <td><?php echo htmlspecialchars($row['schoolYear']); ?></td>
                            <td><?php
                                if ($row['nomenclature'] == 'AC') {
                                    echo '<span class="badge bg-success">' . $row['status'] . '</span>';
                                } elseif ($row['nomenclature'] == 'BA') {
                                    echo '<span class="badge bg-danger">' . $row['status'] . '</span>';
                                } elseif ($row['nomenclature'] == 'RE') {
                                    echo '<span class="badge bg-warning">' . $row['status'] . '</span>';
                                }elseif ($row['nomenclature'] == 'EG') {
                                    echo '<span class="badge bg-primary">' . $row['status'] . '</span>';
                                }elseif ($row['nomenclature'] == 'IN') {
                                    echo '<span class="badge bg-secondary">' . $row['status'] . '</span>';
                                }elseif ($row['nomenclature'] == 'TR') {
                                    echo '<span class="badge bg-info">' . $row['status'] . '</span>';
                                }elseif ($row['nomenclature'] == 'RC') {
                                    echo '<span class="badge bg-dark">' . $row['status'] . '</span>';
                                }elseif ($row['nomenclature'] == 'EX') {
                                    echo '<span class="badge bg-light">' . $row['status'] . '</span>';
                                }
                            ?></td>
                            <td>
                                <button id="botonVer" data-bs-toggle="modal" data-bs-target="#modalCamposFormativos">
                                    <i class="bi bi-file-earmark-text-fill"></i>
                                </button>
                            </td>
                            <td>
                                <button class="botonVer btn-ver" id="botonVer"
                                data-id="<?php echo isset($row['idStudent']) ? htmlspecialchars($row['idStudent']) : ''; ?>"
                                data-nombres="<?php echo isset($row['names']) ? htmlspecialchars($row['names']) : ''; ?>"
                                data-paterno="<?php echo isset($row['lastnamePa']) ? htmlspecialchars($row['lastnamePa']) : ''; ?>"
                                data-materno="<?php echo isset($row['lastnameMa']) ? htmlspecialchars($row['lastnameMa']) : ''; ?>"
                                data-status="<?php echo isset($row['status']) ? htmlspecialchars($row['status']) : ''; ?>"
                                data-grupo="<?php echo isset($row['grupo']) ? htmlspecialchars($row['grupo']) : ''; ?>"
                                data-schoolyear="<?php echo isset($row['schoolYear']) ? htmlspecialchars($row['schoolYear']) : ''; ?>"
                                data-genero="<?php echo isset($row['gender']) ? htmlspecialchars($row['gender']) : ''; ?>"
                                data-direccion="<?php echo isset($row['street']) ? htmlspecialchars($row['street']) : ''; ?>"
                                data-username="<?php echo isset($row['username']) ? htmlspecialchars($row['username']) : ''; ?>"
                                data-email="<?php echo isset($row['email']) ? htmlspecialchars($row['email']) : ''; ?>"
                                data-curp="<?php echo isset($row['curp']) ? htmlspecialchars($row['curp']) : ''; ?>"
                                data-grado="<?php echo isset($row['idGroup']) ? htmlspecialchars($row['idGroup']) : ''; ?>"
                                data-tutornombres="<?php echo isset($row['tutorName']) ? htmlspecialchars($row['tutorName']) : ''; ?>"
                                data-tutorpaterno="<?php echo isset($row['tutorLastnamePa']) ? htmlspecialchars($row['tutorLastnamePa']) : ''; ?>"
                                data-tutormaterno="<?php echo isset($row['tutorLastnameMa']) ? htmlspecialchars($row['tutorLastnameMa']) : ''; ?>"
                                data-tutorine="<?php echo isset($row['ine']) ? htmlspecialchars($row['ine']) : ''; ?>"
                                data-tutortelefono="<?php echo isset($row['tutorPhone']) ? htmlspecialchars($row['tutorPhone']) : ''; ?>"
                                data-tutoremail="<?php echo isset($row['tutorEmail']) ? htmlspecialchars($row['tutorEmail']) : ''; ?>"
                                data-tutordireccion="<?php echo isset($row['tutorAddress']) ? htmlspecialchars($row['tutorAddress']) : ''; ?>"
                                data-tutorparentesco="<?php echo isset($row['tutorRelationship']) ? htmlspecialchars($row['tutorRelationship']) : ''; ?>">
                                <i class="bi bi-person-fill"></i>
                            </button>
                            </td>
                            <td>
                            <button class="botonVer btn-editar" id="botonVer"    
                                data-id="<?php echo isset($row['idStudent']) ? htmlspecialchars($row['idStudent']) : ''; ?>"
                                data-nombres="<?php echo isset($row['names']) ? htmlspecialchars($row['names']) : ''; ?>"
                                data-paterno="<?php echo isset($row['lastnamePa']) ? htmlspecialchars($row['lastnamePa']) : ''; ?>"
                                data-materno="<?php echo isset($row['lastnameMa']) ? htmlspecialchars($row['lastnameMa']) : ''; ?>"
                                data-status="<?php echo isset($row['idStudentStatus']) ? htmlspecialchars($row['idStudentStatus']) : '1'; ?>"
                                data-grupo="<?php echo isset($row['idGroup']) ? htmlspecialchars($row['idGroup']) : ''; ?>"
                                data-schoolyear="<?php echo isset($row['idSchoolYear']) ? htmlspecialchars($row['idSchoolYear']) : ''; ?>"
                                data-telefono="<?php echo isset($row['phone']) ? htmlspecialchars($row['phone']) : ''; ?>"
                                data-tutornombres="<?php echo isset($row['tutorName']) ? htmlspecialchars($row['tutorName']) : ''; ?>"
                                data-tutorpaterno="<?php echo isset($row['tutorLastnamePa']) ? htmlspecialchars($row['tutorLastnamePa']) : ''; ?>"
                                data-tutormaterno="<?php echo isset($row['tutorLastnameMa']) ? htmlspecialchars($row['tutorLastnameMa']) : ''; ?>"
                                data-tutortelefono="<?php echo isset($row['tutorPhone']) ? htmlspecialchars($row['tutorPhone']) : ''; ?>"
                                data-tutorine="<?php echo isset($row['ine']) ? htmlspecialchars($row['ine']) : ''; ?>"
                                data-tutoremail="<?php echo isset($row['tutorEmail']) ? htmlspecialchars($row['tutorEmail']) : ''; ?>"
                                data-tutordireccion="<?php echo isset($row['tutorAddress']) ? htmlspecialchars($row['tutorAddress']) : ''; ?>"
                                data-tutorparentesco="<?php echo isset($row['tutorRelationship']) ? htmlspecialchars($row['tutorRelationship']) : ''; ?>"
                                data-genero="<?php echo isset($row['gender']) ? htmlspecialchars($row['gender']) : ''; ?>"
                                data-direccion="<?php echo isset($row['street']) ? htmlspecialchars($row['street']) : ''; ?>"
                                data-username="<?php echo isset($row['username']) ? htmlspecialchars($row['username']) : ''; ?>"
                                data-email="<?php echo isset($row['email']) ? htmlspecialchars($row['email']) : ''; ?>"
                                data-curp="<?php echo isset($row['curp']) ? htmlspecialchars($row['curp']) : ''; ?>"
                                >
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            </td>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- END MAIN CONTENT -->
    
    <!-- MODAL AGREGAR ALUMNO -->
    <div class="modal fade modal-lg" id="addStudentModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 id="tituloModal" class="modal-title fs-5">Inscribir Alumno</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="addStudent.php" id="formInscribir" method="POST" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="row mb-3">   
                            <div class="col-4">
                                <label class="labelAgregar" for="txtName">Nombres:</label>
                                <input required type="text" name="txtName" class="form-control" placeholder="Nombres">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Campo requerido</div>
                            </div>
                            <div class="col-4">
                                <label class="labelAgregar" for="txtLastnamePa">Apellido Paterno:</label>
                                <input required type="text" name="txtLastnamePa" class="form-control" placeholder="Apellido Paterno">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Campo requerido</div>
                            </div>
                            <div class="col-4">
                                <label class="labelAgregar" for="txtLastnameMa">Apellido Materno:</label>
                                <input required type="text" name="txtLastnameMa" class="form-control" placeholder="Apellido Materno">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Campo requerido</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-4">
                                <label class="labelAgregar" for="txtPhone">Teléfono:</label>
                                <input required type="tel" name="txtPhone" class="form-control" placeholder="Teléfono">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Campo requerido</div>
                            </div>
                            <div class="col-4">
                                <label class="labelAgregar" for="txtEmail">Correo:</label>
                                <input required type="email" name="txtEmail" class="form-control" placeholder="Correo">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Ingrese un correo válido</div>
                            </div>
                            <div class="col-4">
                                <label class="labelAgregar" for="txtGender">Género:</label>
                                <select required name="txtGender" class="form-select">
                                    <option value="">Seleccionar género</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Seleccione una opción</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-4">
                                <label class="labelAgregar" for="txtGroup">Grupo:</label>
                                <select required name="txtGroup" class="form-select">
                                    <option value="">Seleccionar grupo</option>
                                    <?php
                                    $sqlGroups = "SELECT idGroup, CONCAT(grade, group_) as grupo FROM groups ORDER BY grade, group_";
                                    $resultGroups = $conexion->query($sqlGroups);
                                    while ($group = $resultGroups->fetch_assoc()) {
                                        echo "<option value='" . $group['idGroup'] . "'>" . $group['grupo'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Seleccione una opción</div>
                            </div>
                            <div class="col-4">
                                <label class="labelAgregar" for="txtSchoolYear">Año Escolar:</label>
                                <select required name="txtSchoolYear" class="form-select">
                                    <option value="">Seleccionar año</option>
                                    <?php
                                    $sqlYears = "SELECT idSchoolYear, CONCAT(LEFT(startDate, 4), '-', LEFT(endDate, 4)) as year FROM schoolYear ORDER BY startDate DESC";
                                    $resultYears = $conexion->query($sqlYears);
                                    while ($year = $resultYears->fetch_assoc()) {
                                        echo "<option value='" . $year['idSchoolYear'] . "'>" . $year['year'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Seleccione una opción</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtAddress">Dirección:</label>
                                <input required type="text" name="txtAddress" class="form-control" placeholder="Dirección">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Campo requerido</div>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtCurp">CURP:</label>
                                <input required type="text" name="txtCurp" class="form-control" placeholder="CURP">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">Campo requerido</div>
                            </div>
                        </div>

                        <!-- Información del Tutor -->
                        <div class="modal-header border-top">
                            <h5 class="modal-title">Información del Tutor</h5>
                        </div>
                        <div class="modal-body">
                            <div class="row">   
                                <div class="col-4">
                                    <label class="labelAgregar" for="txtTutorName">Nombres:</label>
                                    <input required type="text" name="txtTutorName" class="form-control" placeholder="Nombres del tutor">
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Campo requerido</div>
                                </div>
                                <div class="col-4">
                                    <label class="labelAgregar" for="txtTutorLastnames">Apellidos:</label>
                                    <input required type="text" name="txtTutorLastnames" class="form-control" placeholder="Apellidos del tutor">
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Campo requerido</div>
                                </div>
                                <div class="col-4">
                                    <label class="labelAgregar" for="txtTutorIne">INE:</label>
                                    <input required type="text" name="txtTutorIne" class="form-control" placeholder="INE del tutor">
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Campo requerido</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <label class="labelAgregar" for="txtTutorPhone">Teléfono:</label>
                                    <input required type="tel" name="txtTutorPhone" class="form-control" placeholder="Teléfono del tutor">
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Campo requerido</div>
                                </div>
                                <div class="col-4">
                                    <label class="labelAgregar" for="txtTutorEmail">Correo:</label>
                                    <input required type="email" name="txtTutorEmail" class="form-control" placeholder="Correo del tutor">
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Ingrese un correo válido</div>
                                </div>
                                <div class="col-4">
                                    <label class="labelAgregar" for="txtTutorAddress">Dirección:</label>
                                    <input required type="text" name="txtTutorAddress" class="form-control" placeholder="Dirección del tutor">
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Campo requerido</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="labelAgregar" for="txtTutorRelative">Parentesco:</label>
                                    <input required type="text" name="txtTutorRelative" class="form-control" placeholder="Parentesco del tutor">
                                    <div class="valid-feedback">Correcto</div>
                                    <div class="invalid-feedback">Campo requerido</div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="botonCancelar" data-bs-dismiss="modal">
                            Cancelar <i class="bi bi-x-circle-fill"></i>
                        </button>
                        <button type="submit" class="botonEnter">
                            Inscribir <i class="bi bi-check-circle-fill"></i>
                        </button>
                    </div>
                    <div class="alert alert-danger mx-3 mb-3 d-none" id="divAlerta">
                        Favor de llenar todos los campos correctamente
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL VER ALUMNO -->
    <div class="modal fade modal-lg" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 id="tituloModal" class="modal-title fs-5">Información Personal</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="#" enctype="multipart/form-data" method="post" class="needs-validation" novalidate id="form">
                    <div class="modal-body">
                        <div class="row">   
                            <div class="col-6" style="padding-right: 0;">
                                <label class="labelAgregar" for="txtName">Nombres:</label>
                                <p id="show_nombres"></p>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtLastname">Apellidos:</label>
                                <p><span id="show_paterno"></span> <span id="show_materno"></span></p>

                            </div>
                        </div>
                        <div class="row pt-2">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtCurp">CURP:</label>
                                <p id="show_curp"></p>
                            </div>
                        </div>
                        <div class="row pt-2">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtGroup">Grupo:</label>
                                <p id="show_grupo"></p>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtSchoolYear">Año Escolar:</label>
                                <p id="show_schoolYear"></p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-header">
                        <h1 id="tituloModal" class="modal-title fs-5">Información del Tutor</h1>
                    </div>
                    <div class="modal-body">
                        <div class="row">   
                            <div class="col-6" style="padding-right: 0;">
                                <label class="labelAgregar" for="txtTutorName">Nombres:</label>
                                <p id="show_tutorNombres"></p>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorLastname">Apellidos:</label>
                                <p id="show_tutorApellidos"></p>
                            </div>
                        </div>
                        <div class="row pt-2">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorIne">INE:</label>
                                <p id="show_tutorIne"></p>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorEmail">Correo:</label>
                                <p id="show_tutorEmail"></p>
                            </div>
                        </div>
                        <div class="row pt-2">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorPhone">Número de teléfono:</label>
                                <p id="show_tutorPhone"></p>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorAddress">Dirección:</label>
                                <p id="show_tutorAddress"></p>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between" style="border-top: 0;">
                            <button class="botonCancelar" type="button" style="margin-left: 68vh;" class="btn btn-secondary w-25" data-bs-toggle="modal" data-bs-target="#deleteModal">Eliminar Información
                                <i id="iconoAdd" class="bi bi-trash-fill"></i>
                            </button>
                           
                        </div>
                        <div class="alert alert-danger mt-4 d-none" id="divAlerta" role="alert">
                            Favor de llenar los campos
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR ALUMNO -->
    <div class="modal fade modal-lg" id="editModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 id="tituloModal" class="modal-title fs-5">Información Personal</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="updateStudent.php" enctype="multipart/form-data" method="post" class="needs-validation" novalidate id="formEditStudent">
                    <input type="hidden" name="studentId" id="studentId">
                    <div class="modal-body">
                        <div class="row">   
                            <div class="col-6" style="padding-right: 0;">
                                <label class="labelAgregar" for="txtName">Nombres:</label>
                                <input required type="text" name="txtName" class="form-control" id="editName">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtLastnamePa">Apellido Paterno:</label>
                                <input required type="text" name="txtLastnamePa" class="form-control" id="editLastnamePa">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtLastnameMa">Apellido Materno:</label>
                                <input required type="text" name="txtLastnameMa" class="form-control" id="editLastnameMa">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtCurp">CURP:</label>
                                <input required type="text" name="txtCurp" class="form-control" id="editCurp">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-4">
                                <label class="labelAgregar" for="txtPhone">Teléfono:</label>
                                <input required type="text" name="txtPhone" class="form-control" id="editPhone">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-4">
                                <label class="labelAgregar" for="txtEmail">Email:</label>
                                <input required type="email" name="txtEmail" class="form-control" id="editEmail">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-4">
                                <label class="labelAgregar" for="txtGender">Género:</label>
                                <select required name="txtGender" class="form-select" id="editGender">
                                    <option value="">Seleccionar género</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtAddress">Dirección:</label>
                                <input required type="text" name="txtAddress" class="form-control" id="editAddress">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtStatus">Estado:</label>
                                <select required name="txtStatus" class="form-select" id="editStatus">
                                    <option value="1">Activo, actualmente cursando el ciclo escolar</option>
                                    <option value="2">Dado de baja (por traslado, abandono u otras razones)</option>
                                    <option value="3">Reinscrito después de una baja</option>
                                    <option value="4">Egresado</option>
                                    <option value="5">Inscrito, pendiente de comenzar clases</option>
                                    <option value="6">En trámite de inscripción o documentación</option>
                                    <option value="7">Repetidor de grado</option>
                                    <option value="8">Intercambio temporal</option>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtGroup">Grupo:</label>
                               <select required name="txtGroup" class="form-select" id="editGroup">
                                    <option value="">Seleccionar grupo</option>
                                    <?php
                                    $sqlGroups = "SELECT idGroup, CONCAT(grade, group_) as grupo FROM groups ORDER BY grade, group_";
                                    $resultGroups = $conexion->query($sqlGroups);
                                    while ($group = $resultGroups->fetch_assoc()) {
                                        // La comparación se hará dinámicamente con JavaScript
                                        echo "<option value='" . $group['idGroup'] . "'>" . $group['grupo'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtSchoolYear">Año Escolar:</label>
                                <select required name="txtSchoolYear" class="form-select" id="editSchoolYear">
                                    <option value="">Seleccionar año</option>
                                    <?php
                                    $sqlYears = "SELECT idSchoolYear, CONCAT(LEFT(startDate, 4), '-', LEFT(endDate, 4)) as year FROM schoolYear ORDER BY startDate DESC";
                                    $resultYears = $conexion->query($sqlYears);
                                    while ($year = $resultYears->fetch_assoc()) {
                                        // La comparación se hará dinámicamente con JavaScript
                                        echo "<option value='" . $year['idSchoolYear'] . "'>" . $year['year'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Información del Tutor</h1>
                    </div>
                    <div class="modal-body">
                        <div class="row">   
                            <div class="col-4">
                                <label class="labelAgregar" for="txtTutorName">Nombres:</label>
                                <input required type="text" name="txtTutorName" class="form-control" id="editTutorName">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-4">
                                <label class="labelAgregar" for="txtTutorLastnamePa">Apellido Paterno:</label>
                                <input required type="text" name="txtTutorLastnamePa" class="form-control" id="editTutorLastnamePa">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-4">
                                <label class="labelAgregar" for="txtTutorLastnameMa">Apellido Materno:</label>
                                <input required type="text" name="txtTutorLastnameMa" class="form-control" id="editTutorLastnameMa">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorIne">INE:</label>
                                <input required type="text" name="txtTutorIne" class="form-control" id="editTutorIne">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorEmail">Correo:</label>
                                <input required type="email" name="txtTutorEmail" class="form-control" id="editTutorEmail">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorPhone">Teléfono:</label>
                                <input required type="text" name="txtTutorPhone" class="form-control" id="editTutorPhone">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorAddress">Dirección:</label>
                                <input required type="text" name="txtTutorAddress" class="form-control" id="editTutorAddress">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-6">
                                <label class="labelAgregar" for="txtTutorRelative">Parentesco:</label>
                                <input required type="text" name="txtTutorRelative" class="form-control" id="editTutorRelative">
                                <div class="valid-feedback">Correcto</div>
                                <div class="invalid-feedback">No válido</div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between" style="border-top: 0;">
                            <button class="botonCancelar" type="button" class="btn btn-secondary w-25" data-bs-dismiss="modal">Cancelar
                                <i id="iconoAdd" class="bi bi-x-circle-fill"></i>
                            </button>
                            <button class="botonEnter" type="submit" class="btn btn-primary w-25">Guardar Cambios
                                <i id="iconoAdd" class="bi bi-floppy2-fill"></i>
                            </button>
                        </div>
                        <div class="alert alert-danger mt-4 d-none" id="divAlerta" role="alert">
                            Favor de llenar los campos
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL ELIMINAR ALUMNO -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 id="tituloModal" class="modal-title fs-5">¿Desea eliminar este alumno?</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-footer">
                    <button class="botonCancelar" type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#showModal">Cancelar
                        <i id="iconoAdd" class="bi bi-x-circle-fill"></i>
                    </button>
                    <button class="botonEnter" type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal">Eliminar
                        <i id="iconoAdd" class="bi bi-trash3-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRMAR ELIMINACIÓN -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 id="tituloModal" class="modal-title fs-5">¿Está seguro que desea eliminar este alumno?</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-footer">
                    <button class="botonCancelar" type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#showModal">Cambié de opinión
                        <i id="iconoAdd" class="bi bi-x-circle-fill"></i>
                    </button>
                    <button class="botonEnter" type="submit" class="btn btn-primary btnEliminar" id="eliminar">Eliminar
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL BOLETA -->
    <div class="modal fade" id="modalCamposFormativos" tabindex="-1" aria-labelledby="modalCamposFormativosLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="tituloModalBoleta" class="modal-title">Boleta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="color:white;"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="labelAgregar" for="cicloFormativo">Ciclo Escolar:</label>
                        <select class="form-select border-dark" id="cicloFormativo">
                            <option id="subject" value="" selected disabled>Seleccionar ciclo</option>
                            <option value="2023">2023 - 2024</option>
                            <option value="2024">2024 - 2025</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="divTrimestreFormativo">
                        <label class="labelAgregar" for="trimestreFormativo">Trimestre:</label>
                        <select class="form-select border-dark" id="trimestreFormativo">
                            <option id="subject" value="" selected disabled>Seleccionar trimestre</option>
                            <option value="1">Primer Trimestre</option>
                            <option value="2">Segundo Trimestre</option>
                            <option value="3">Tercer Trimestre</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="divCamposFormativos">
                        <h6 class="labelAgregar fw-bold">Campos Formativos</h6>
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
                    <button id="button" type="button" class="btn btn-primary">Ver detalles</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/chartScript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.js"></script>
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
    <?php
    if (isset($_GET['status'])) {
        $icon = 'success';
        $title = '';

        if ($_GET['status'] == 1 || $_GET['status'] == 'success') {
            $title = "Estudiante agregado correctamente";
        } else if ($_GET['status'] == 2) {
            $title = "Estudiante actualizado correctamente";
        } else if ($_GET['status'] == 3) {
            $title = "Estudiante eliminado correctamente";
        } else if ($_GET['status'] == 'error') {
            $icon = 'error';
            $title = isset($_GET['message']) ? $_GET['message'] : "Error al procesar la solicitud";
        } else if ($_GET['status'] == 0) {
            $icon = 'error';
            $title = "Favor de completar los datos correctamente";
        }

        if ($title) {
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: '<?php echo $icon; ?>',
                        title: "<?php echo $title; ?>",
                        confirmButtonText: 'Aceptar'
                    }).then(function () {
                        // Quitar status y message de la URL usando la API de historial
                        const url = new URL(window.location.href);
                        url.searchParams.delete('status');
                        url.searchParams.delete('message');
                        window.history.replaceState({}, document.title, url.pathname + url.search);
                    });
                });
            </script>
            <?php
        }
    }
    ?>
    <script>
        // Función para buscar en la tabla
        function searchTable() {
            const searchInput = document.getElementById('alumno');
            const searchText = searchInput.value.toLowerCase();
            const table = document.querySelector('.table');
            const rows = Array.from(table.getElementsByTagName('tr')).slice(1); // Ignorar encabezado
            const yearSelect = document.getElementById('schoolYear');
            const selectedYear = yearSelect ? yearSelect.value : '';
            const groupSelect = document.getElementById('grupo');
            const selectedGroup = groupSelect ? groupSelect.value : '';
            // Ordenar filas según coincidencia
            rows.sort((a, b) => {
                const textA = a.textContent.toLowerCase();
                const textB = b.textContent.toLowerCase();
                if (!searchText) return 0;
                const matchA = textA.includes(searchText);
                const matchB = textB.includes(searchText);
                if (matchA && !matchB) return -1;
                if (!matchA && matchB) return 1;
                const indexA = textA.indexOf(searchText);
                const indexB = textB.indexOf(searchText);
                if (indexA === -1 && indexB === -1) return 0;
                if (indexA === -1) return 1;
                if (indexB === -1) return -1;
                return indexA - indexB;
            });
            // Ocultar filas que no coinciden y resaltar
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                let shouldShow = text.includes(searchText);
                // Filtrar por año escolar
                if (selectedYear) {
                    shouldShow = shouldShow && row.dataset.schoolyear === selectedYear;
                }
                // Filtrar por grupo
                if (selectedGroup) {
                    shouldShow = shouldShow && row.dataset.grupo === selectedGroup;
                }
                row.style.display = shouldShow ? '' : 'none';
                // Limpiar resaltado si el input está vacío
                const cells = row.getElementsByTagName('td');
                if (!searchText) {
                    Array.from(cells).forEach(cell => {
                        if (cell.hasAttribute('data-original-html')) {
                            cell.innerHTML = cell.getAttribute('data-original-html');
                        }
                    });
                }
                // Resaltar texto coincidente
                if (shouldShow && searchText) {
                    Array.from(cells).forEach(cell => {
                        const originalText = cell.textContent;
                        const lowerText = originalText.toLowerCase();
                        const index = lowerText.indexOf(searchText);
                        if (index !== -1) {
                            const before = originalText.substring(0, index);
                            const match = originalText.substring(index, index + searchText.length);
                            const after = originalText.substring(index + searchText.length);
                            cell.innerHTML = `${before}<mark>${match}</mark>${after}`;
                        }
                    });
                }
            });
            // Reordenar filas en la tabla
            const tbody = table.getElementsByTagName('tbody')[0];
            rows.forEach(row => tbody.appendChild(row));
        }
        // Event listener para la búsqueda
        document.addEventListener('DOMContentLoaded', function() {
            // Guardar HTML original de cada celda al cargar la página
            document.querySelectorAll('.table tbody tr').forEach(row => {
                Array.from(row.getElementsByTagName('td')).forEach(cell => {
                    cell.setAttribute('data-original-html', cell.innerHTML);
                });
            });
            const searchInput = document.getElementById('alumno');
            const searchButton = document.getElementById('iBuscar');
            if (searchInput) {
                // Buscar al escribir
                searchInput.addEventListener('input', function() {
                    searchTable();
                    if (this.value === '') {
                        // Si se borra todo, restaurar HTML original
                        document.querySelectorAll('.table tbody tr').forEach(row => {
                            Array.from(row.getElementsByTagName('td')).forEach(cell => {
                                if (cell.hasAttribute('data-original-html')) {
                                    cell.innerHTML = cell.getAttribute('data-original-html');
                                }
                            });
                        });
                    }
                });
                // Buscar al presionar Enter
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        searchTable();
                    }
                });
            }
            if (searchButton) {
                searchButton.addEventListener('click', searchTable);
            }
            // Event listener para el selector de año escolar
            const yearSelect = document.getElementById('schoolYear');
            if (yearSelect) {
                yearSelect.addEventListener('change', function() {
                    searchTable(); // Filtra en vivo
                    const url = new URL(window.location.href);
                    url.searchParams.delete('year');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
            // Event listener para el selector de grupo
            const groupSelect = document.getElementById('grupo');
            if (groupSelect) {
                groupSelect.addEventListener('change', function() {
                    searchTable(); // Filtra en vivo
                    const url = new URL(window.location.href);
                    url.searchParams.delete('group');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
            }
        });
    </script>

    <script>
        // Selecciona todos los botones "ver" de estudiantes
        const botonesVer = document.querySelectorAll('.btn-ver');

        botonesVer.forEach(boton => {
            boton.addEventListener('click', function() {
                const modalElement = document.getElementById('showModal');
                if (!modalElement) {
                    return;
                }

                // Obtener datos del botón (alumno)
                const data = {
                    id: this.getAttribute('data-id'),
                    nombres: this.getAttribute('data-nombres'),
                    paterno: this.getAttribute('data-paterno'),
                    materno: this.getAttribute('data-materno'),
                    status: this.getAttribute('data-status'),
                    grupo: this.getAttribute('data-grupo'),
                    schoolYear: this.getAttribute('data-schoolyear'),
                    genero: this.getAttribute('data-genero'),
                    direccion: this.getAttribute('data-direccion'),
                    username: this.getAttribute('data-username'),
                    email: this.getAttribute('data-email'),
                    curp: this.getAttribute('data-curp'),
                    grado: this.getAttribute('data-grado')
                };

                // Obtener datos del tutor
                const tutorData = {
                    nombres: this.getAttribute('data-tutornombres'),
                    paterno: this.getAttribute('data-tutorpaterno'),
                    materno: this.getAttribute('data-tutormaterno'),
                    ine: this.getAttribute('data-tutorine'),
                    telefono: this.getAttribute('data-tutortelefono'),
                    email: this.getAttribute('data-tutoremail'),
                    direccion: this.getAttribute('data-tutordireccion'),
                    parentesco: this.getAttribute('data-tutorparentesco')
                };

                // Llenar el modal show (alumno)
                if(document.getElementById('show_nombres')) document.getElementById('show_nombres').textContent = data.nombres || 'No especificado';
                if(document.getElementById('show_paterno')) document.getElementById('show_paterno').textContent = data.paterno || 'No especificado';
                if(document.getElementById('show_materno')) document.getElementById('show_materno').textContent = data.materno || 'No especificado';
                if(document.getElementById('show_grupo')) document.getElementById('show_grupo').textContent = data.grupo || 'No asignado';
                if(document.getElementById('show_schoolYear')) document.getElementById('show_schoolYear').textContent = data.schoolYear || 'No especificado';
                if(document.getElementById('show_direccion')) document.getElementById('show_direccion').textContent = data.direccion || 'No especificado';
                if(document.getElementById('show_username')) document.getElementById('show_username').textContent = data.username || 'No especificado';
                if(document.getElementById('show_email')) document.getElementById('show_email').textContent = data.email || 'No especificado';
                if(document.getElementById('show_curp')) document.getElementById('show_curp').textContent = data.curp || 'No especificado';
                if(document.getElementById('show_grado')) document.getElementById('show_grado').textContent = data.grado || 'No especificado';
                if(document.getElementById('show_genero')) document.getElementById('show_genero').textContent = data.genero || 'No especificado';

                // Llenar el modal show (tutor)
                if(document.getElementById('show_tutorNombres')) document.getElementById('show_tutorNombres').textContent = tutorData.nombres || 'No registrado';
                if(document.getElementById('show_tutorApellidos')) document.getElementById('show_tutorApellidos').textContent = `${tutorData.paterno || ''} ${tutorData.materno || ''}`.trim();
                if(document.getElementById('show_tutorIne')) document.getElementById('show_tutorIne').textContent = tutorData.ine || 'No registrado';
                if(document.getElementById('show_tutorPhone')) document.getElementById('show_tutorPhone').textContent = tutorData.telefono || 'No registrado';
                if(document.getElementById('show_tutorEmail')) document.getElementById('show_tutorEmail').textContent = tutorData.email || 'No registrado';
                if(document.getElementById('show_tutorAddress')) document.getElementById('show_tutorAddress').textContent = tutorData.direccion || 'No registrado';
                if(document.getElementById('show_tutorRelative')) document.getElementById('show_tutorRelative').textContent = tutorData.parentesco || 'No registrado';

                // Mostrar el modal después de llenar los datos
                var modal = new bootstrap.Modal(modalElement);
                modal.show();
            });
        });
    </script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#btnEditar, .btn-editar').forEach(btn => {
        btn.addEventListener('click', function() {
            // Ocultar el modal de visualización si está abierto
            const showModal = document.getElementById('showModal');
            if (showModal && showModal.classList.contains('show')) {
                var showModalInstance = bootstrap.Modal.getInstance(showModal);
                if (showModalInstance) showModalInstance.hide();
            }

            const editModal = document.getElementById('editModal');
            if (!editModal) {
                return;
            }

            // Obtener los valores de los atributos data
            const grupoId = this.getAttribute('data-grupo');
            const schoolYearId = this.getAttribute('data-schoolyear');

            // Establecer los valores en los select
            const selectGrupo = editModal.querySelector('#editGroup');
            const selectSchoolYear = editModal.querySelector('#editSchoolYear');

            if (selectGrupo) {
                selectGrupo.value = grupoId;
            }

            if (selectSchoolYear) {
                selectSchoolYear.value = schoolYearId;
            }

            // Llenar el resto de los campos
            if(editModal.querySelector('#studentId')) editModal.querySelector('#studentId').value = this.getAttribute('data-id') || '';
            if(editModal.querySelector('#editName')) editModal.querySelector('#editName').value = this.getAttribute('data-nombres') || '';
            if(editModal.querySelector('#editLastnamePa')) editModal.querySelector('#editLastnamePa').value = this.getAttribute('data-paterno') || '';
            if(editModal.querySelector('#editLastnameMa')) editModal.querySelector('#editLastnameMa').value = this.getAttribute('data-materno') || '';
            if(editModal.querySelector('#editCurp')) editModal.querySelector('#editCurp').value = this.getAttribute('data-curp') || '';
            if(editModal.querySelector('#editGender')) editModal.querySelector('#editGender').value = this.getAttribute('data-genero') || '';
            if(editModal.querySelector('#editAddress')) editModal.querySelector('#editAddress').value = this.getAttribute('data-direccion') || '';
            if(editModal.querySelector('#editEmail')) editModal.querySelector('#editEmail').value = this.getAttribute('data-email') || '';
            if(editModal.querySelector('#editPhone')) editModal.querySelector('#editPhone').value = this.getAttribute('data-telefono') || '';
            if(editModal.querySelector('#editStatus')) editModal.querySelector('#editStatus').value = this.getAttribute('data-status') || '1';

            // Llenar campos del tutor
            if(editModal.querySelector('#editTutorName')) editModal.querySelector('#editTutorName').value = this.getAttribute('data-tutornombres') || '';
            if(editModal.querySelector('#editTutorLastnamePa')) editModal.querySelector('#editTutorLastnamePa').value = this.getAttribute('data-tutorpaterno') || '';
            if(editModal.querySelector('#editTutorLastnameMa')) editModal.querySelector('#editTutorLastnameMa').value = this.getAttribute('data-tutormaterno') || '';
            if(editModal.querySelector('#editTutorIne')) editModal.querySelector('#editTutorIne').value = this.getAttribute('data-tutorine') || '';
            if(editModal.querySelector('#editTutorPhone')) editModal.querySelector('#editTutorPhone').value = this.getAttribute('data-tutortelefono') || '';
            if(editModal.querySelector('#editTutorEmail')) editModal.querySelector('#editTutorEmail').value = this.getAttribute('data-tutoremail') || '';
            if(editModal.querySelector('#editTutorAddress')) editModal.querySelector('#editTutorAddress').value = this.getAttribute('data-tutordireccion') || '';
            if(editModal.querySelector('#editTutorRelative')) editModal.querySelector('#editTutorRelative').value = this.getAttribute('data-tutorparentesco') || '';

            var modal = new bootstrap.Modal(editModal);
            modal.show();
        });
    });
});
</script>

<script>
document.getElementById('formEditStudent').addEventListener('submit', function(e) {
                e.preventDefault();
                
    // Validar formulario
                if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
                    return;
                }
                
                // Mostrar carga
                Swal.fire({
        title: 'Actualizando datos',
                    html: 'Por favor espere...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

    // Enviar datos por AJAX
    const formData = new FormData(this);
    fetch('updateStudent.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Si la respuesta no es JSON válida, mostrar error
        return response.json().catch(() => ({success: false, message: 'Respuesta inesperada del servidor'}));
    })
    .then(data => {
                        Swal.close();
        if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                text: data.message,
                confirmButtonText: 'Aceptar'
            }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                text: data.message || 'Ocurrió un error al procesar la solicitud'
                                });
                            }
    })
    .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
            text: 'Ocurrió un error al procesar la solicitud'
                            });
                        });
        });
    </script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variable para almacenar el ID del estudiante a eliminar
    let studentIdToDelete = null;

    // Cuando se abre el modal de eliminación, guardar el ID del estudiante
    document.querySelectorAll('.btn-ver').forEach(btn => {
        btn.addEventListener('click', function() {
            studentIdToDelete = this.getAttribute('data-id');
        });
    });

    // Manejar el clic en el botón de eliminar final
    document.querySelector('#eliminar').addEventListener('click', function() {
        if (!studentIdToDelete) {
            return;
        }

        // Mostrar indicador de carga
        Swal.fire({
            title: 'Eliminando estudiante',
            html: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Realizar la petición AJAX para eliminar
        fetch('deleteStudent.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'studentId=' + encodeURIComponent(studentIdToDelete)
        })
        .then(response => response.json())
        .then(data => {
            // Cerrar todos los modales
            var modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                var modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'El estudiante ha sido eliminado correctamente',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Recargar la página
                    location.reload();
                });
            } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                    text: data.message || 'Ocurrió un error al eliminar el estudiante'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar la solicitud'
            });
                });
            });
        });
    </script>

    <script src="../js/students.js"></script>
</body>
</html>