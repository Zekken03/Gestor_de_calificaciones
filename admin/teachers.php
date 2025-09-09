<?php
require_once "check_session.php";
include '../conection.php';

// Guardar el estado para mostrarlo con SweetAlert2
$showAlert = '';
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    
    if ($status === 'error') {
        $showAlert = 'error';
        $title = 'Error';
        $message = 'Error al agregar el maestro. Por favor, inténtelo de nuevo.';
    } elseif ($status === 'duplicate') {
        $showAlert = 'warning';
        $title = 'Advertencia';
        $message = 'El maestro ya existe en el sistema. No se puede agregar un duplicado.';
    }
}

    // Consulta principal para obtener los datos de los profesores
    $sql = "SELECT 
    t.idTeacher,
    t.profesionalID,
    t.ine,
    t.typeTeacher,
    ui.names,
    ui.lastnamePa,
    ui.lastnameMa,
    ui.gender,
    ui.phone,
    ui.email,
    ui.street,
    ts.description AS status,
    u.username,
    u.password,
    u.raw_password,
    GROUP_CONCAT(DISTINCT CONCAT(g.grade, '°', g.group_)) AS grupos,
    GROUP_CONCAT(DISTINCT s.name) AS materias
FROM teachers t
INNER JOIN users u ON t.idUser = u.idUser
INNER JOIN usersInfo ui ON u.idUserInfo = ui.idUserInfo
INNER JOIN teacherStatus ts ON t.idTeacherStatus = ts.idTeacherStatus
LEFT JOIN teacherGroupsSubjects tgs ON t.idTeacher = tgs.idTeacher
LEFT JOIN groups g ON tgs.idGroup = g.idGroup
LEFT JOIN subjects s ON tgs.idSubject = s.idSubject
GROUP BY t.idTeacher";

    $resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maestros</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.css">

    <link rel="stylesheet" href="../css/admin/teacher.css">
    <link rel="stylesheet" href="../css/styles.css">


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
     <main class="flex-grow-1 col-9 p-0  " >
        <?php
                include "../layouts/header.php"; 
            ?>
        <div class="container mt-4" style="padding-top:10vh">
            <div class="row">
                <div class="col-md-8" style="padding-right: 25vh;">
                    <div class="mb-3">
                            <label id="labelDocente" for="docente" class="form-label fw-bold    ">Docente:</label>
                            <div class="input-group search-group">
                                <input type="text" class="form-control border-dark" id="docente" placeholder="Buscar docente...">
                                <span class="input-group-text bg-white border-dark">
                                    <i id="iBuscar" class="bi bi-search"></i>
                                </span>
                            </div>
                    </div>
                </div>
                <div class="col-md-4 text-end " style="padding-top: 4.5vh; ">
                    <div class="padding-left:5vh;">
                        <button id="button" data-bs-toggle="modal" data-bs-target="#addModal">
                            Añadir Docente / Crear Cuenta  
                            <i id="iconoAdd" class="bi bi-plus"></i>
                        </button>
                    </div>    
                </div>
            </div>
        </div>
   
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
<h1>Lista de Docentes</h1>
<div class="contenedorTabla container mt-4" id="tabla">
    <table class="table table-bordered border-dark">
        <thead>
            <tr>
                <th>No.</th>
                <th>Paterno</th>
                <th>Materno</th>
                <th>Nombres</th>
                <th>Estado</th>
                <th>Grupo</th>
                <th>Materia</th>
                <th>Ver</th>
            </tr>
        </thead>
        <tbody id="teachersBody">
            <?php
                while($fila = mysqli_fetch_array($resultado)){
            ?>
            <tr>
                <td><?php echo $fila['idTeacher']; ?></td>
                <td><?php echo $fila['lastnamePa']; ?></td>
                <td><?php echo $fila['lastnameMa']; ?></td>
                <td><?php echo $fila['names']; ?></td>
                <td><?php
        if ($fila['status'] == 'Activo') {
            echo '<span class="badge bg-success">' . $fila['status'] . '</span>';
        } elseif ($fila['status'] == 'Inactivo') {
            echo '<span class="badge bg-danger">' . $fila['status'] . '</span>';
        } else {
            echo '<span class="badge bg-secondary">' . $fila['status'] . '</span>'; 
        }
    ?></td>
                <td>
                    <ul class="list-unstyled mb-0">
                        <?php
                            $grupos = $fila['grupos']; // Viene como "1° A,2° B,3° C"
                            $gruposArray = explode(',', $grupos);
                            foreach ($gruposArray as $grupo) {
                                echo '<li>' . $grupo . '</li>';
                            }
                        ?>
                    </ul>
                </td>
                <td>
            <ul class="list-unstyled">
                <?php
                    $materias = $fila['materias'];  // Recupera las materias concatenadas
                    $materiasArray = explode(',', $materias);  // Separa las materias en un array

                    foreach ($materiasArray as $materia) {
                        echo '<li>' . $materia . '</li>';  // Muestra cada materia en un <li>
                    }
                ?>
            </ul>
        </td>


                <td><button id="botonVer" class="btn-ver" 
                    data-bs-toggle="modal" 
                    data-bs-target="#showModal" 
                    data-id="<?php echo $fila['idTeacher']; ?>"
                    data-nombres="<?php echo $fila['names']; ?>"
                    data-paterno="<?php echo $fila['lastnamePa']; ?>"
                    data-materno="<?php echo $fila['lastnameMa']; ?>"
                    data-status="<?php echo $fila['status']; ?>"
                    data-grupos="<?php echo $fila['grupos']; ?>"
                    data-materias="<?php echo $fila['materias']; ?>"
                    data-ine="<?php echo $fila['ine']; ?>"
                    data-cedula="<?php echo $fila['profesionalID']; ?>"
                    data-telefono="<?php echo $fila['phone']; ?>"
                    data-tipo="<?php echo $fila['typeTeacher']; ?>"
                    data-genero="<?php echo $fila['gender']; ?>"
                    data-email="<?php echo $fila['email']; ?>"
                    data-direccion="<?php echo $fila['street']; ?>"
                    data-username="<?php echo $fila['username']; ?>"
                    data-password="<?php echo $fila['raw_password']; ?>"
                    style="margin-left: 10vh;">
                    <i id="iVer" class="bi bi-person-fill"></i></button></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
     </main>
    <!-- END MAIN CONTENT --> 
   
   <!-- MODAL AGREGAR -->
<div class="modal fade modal-lg" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Agregar Docente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="addTeacher.php" method="POST" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="addName" class="form-label">Nombre(s)</label>
                            <input type="text" class="form-control" id="addName" name="txtName" required>
                        </div>
                        <div class="col-md-4">
                            <label for="addLastnamePa" class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" id="addLastnamePa" name="txtLastnamePa" required>
                        </div>
                        <div class="col-md-4">
                            <label for="addLastnameMa" class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" id="addLastnameMa" name="txtLastnameMa" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="addGender" class="form-label">Género</label>
                            <select class="form-select" id="addGender" name="txtGender" required>
                                <option value="">Seleccione...</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="addTypeTeacher" class="form-label">Tipo de Docente</label>
                            <select class="form-select" id="addTypeTeacher" name="txtTypeTeacher" required>
                                <option value="">Seleccione...</option>
                                <option value="ME">Maestro Especial</option>
                                <option value="MS">Maestro de Escolarizado</option>
                            </select>
                        </div>
                        
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="addIne" class="form-label">INE</label>
                            <input type="text" class="form-control" id="addIne" name="txtIne" required>
                        </div>
                        <div class="col-md-6">
                            <label for="addProfesional" class="form-label">Cédula Profesional</label>
                            <input type="text" class="form-control" id="addProfesional" name="txtProfesional" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="addPhone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="addPhone" name="txtPhone" required>
                        </div>
                        <div class="col-md-4">
                            <label for="addEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="addEmail" name="txtEmail" required>
                        </div>
                        <div class="col-md-4">
                            <label for="addAddress" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="addAddress" name="txtAddress" required>
                        </div>
                    </div>

                
                    <div class="modal-footer">
                        <button type="button" class="botonCancelar" data-bs-dismiss="modal">Cerrar<i class="bi bi-x-circle-fill"></i></button>
                        <button type="submit" class="botonEnter" id="btnEditar">Guardar <i id="iconoAdd" class="bi bi-floppy2-fill"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- MODAL AGREGAR -->



<!-- MODAL SHOW-->
<div class="modal fade modal-lg" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 id="tituloModal" class="modal-title fs-4">Información General</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-header">
                    <h1 id="tituloModal" class="modal-title fs-5 pt-2">Información del Docente</h1>
                </div>
                <div class="row mb-3">   
                    <div class="col-3 pt-4" style="margin-left: 5vh;">
                        <label class="fw-bold">Nombres:</label>
                        <p id="showName" class="mb-0"></p>
                    </div>
                    <div class="col-3 pt-4" >
                        <label class="fw-bold">Apellido Paterno:</label>
                        <p id="showLastnamePa" class="mb-0"></p>
                    </div>
                    <div class="col-3 pt-4" >
                        <label class="fw-bold">Apellido Materno:</label>
                        <p id="showLastnameMa" class="mb-0"></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-3 pt-2"style="margin-left: 5vh;">
                        <label class="fw-bold">Tipo de Docente:</label>
                        <p id="showTypeTeacher" class="mb-0"></p>
                    </div>
                    <div class="col-3 pt-2">
                        <label class="fw-bold">Estado:</label>
                        <p id="showStatus" class="mb-0"></p>
                    </div>
                    <div class="col-3 pt-2">
                        <label class="fw-bold">Género:</label>
                        <p id="showGender" class="mb-0"></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-3 pt-2" style="margin-left: 5vh;">
                        <label class="fw-bold">INE:</label>
                        <p id="showIne" class="mb-0"></p>
                    </div>
                    <div class="col-3 pt-2">
                        <label class="fw-bold">Cédula Profesional:</label>
                        <p id="showProfesionalID" class="mb-0"></p>
                    </div>
                </div>

                <div class="modal-header">
                    <h1 id="tituloModal" class="modal-title fs-5 pt-2">Información de Contacto</h1>
                </div>
                <div class="row mb-3">
                    <div class="col-3 pt-4" style="margin-left: 5vh;">
                        <label class="fw-bold">Teléfono:</label>
                        <p id="showPhone" class="mb-0"></p>
                    </div>
                    <div class="col-3 pt-4">
                        <label class="fw-bold">Correo Electrónico:</label>
                        <p id="showEmail" class="mb-0"></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-3 pt-2"style="margin-left: 5vh;">
                        <label class="fw-bold">Dirección:</label>
                        <p id="showAddress" class="mb-0"></p>
                    </div>
                </div>

                <div class="modal-header">
                <h1 id="tituloModal" class="modal-title fs-5 pt-2">Cuenta</h1>
                </div>
                <div class="row mb-3">
                    <div class="col-3 pt-4" style="margin-left: 5vh;">
                        <label class="fw-bold">Usuario:</label>
                        <p id="showUsername" class="mb-0"></p>
                    </div>
                    <div class="col-3 pt-4">
                        <label class="fw-bold">Contraseña:</label>
                        <p id="showPassword" class="mb-0"></p>
                    </div>
                </div>

                <div class="modal-header">
                <h1 id="tituloModal" class="modal-title fs-5 pt-2">Asignaciones</h1>
                </div>
                <div class="row mb-3">
                    <div class="col-3 pt-4" style="margin-left: 5vh;">
                        <label class="fw-bold">Grupos asignados:</label>
                        <p id="showGrupos" class="mb-0"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-3 pt-2" style="margin-left: 5vh;">
                        <label class="fw-bold">Materias:</label>
                        <p id="showMaterias" class="mb-0"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            <button class="botonCancelar" type="button" class="btn btn-secondary w-25" data-bs-toggle="modal" data-bs-target="#deleteModal">Eliminar Información
                                <i id="iconoAdd" class="bi bi-trash-fill"></i>
                            </button>
                <button class="botonEnter" type="button" id="btnEditar" data-bs-toggle="modal" data-bs-target="#editModal">
                    Editar
                    <i class="bi bi-pencil-fill"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade modal-lg" id="editModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 id="tituloModal"class="modal-title fs-4">Editar Docente</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="./updateTeacher.php" method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="teacherId" id="editTeacherId">
                    <div class="row">   
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editName">Nombres:</label>
                            <input required type="text" name="txtName" id="editName" class="form-control" placeholder="Nombre del docente">
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor ingrese el nombre</div>
                        </div>
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editLastnamePa">Apellido Paterno:</label>
                            <input required type="text" name="txtLastnamePa" id="editLastnamePa" class="form-control" placeholder="Apellido paterno">
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor ingrese el apellido</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editLastnameMa">Apellido Materno:</label>
                            <input required type="text" name="txtLastnameMa" id="editLastnameMa" class="form-control" placeholder="Apellido materno">
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor ingrese el apellido</div>
                        </div>
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editGender">Género:</label>
                            <select required name="txtGender" id="editGender" class="form-control">
                                <option value="">Seleccione un género</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor seleccione un género</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editIne">INE:</label>
                            <input required type="text" name="txtIne" id="editIne" class="form-control" placeholder="Número de INE">
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor ingrese el INE</div>
                        </div>
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editProfesional">Cédula Profesional:</label>
                            <input required type="text" name="txtProfesional" id="editProfesional" class="form-control" placeholder="Número de cédula">
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor ingrese la cédula</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editTypeTeacher">Tipo de Docente:</label>
                            <select required name="txtTypeTeacher" id="editTypeTeacher" class="form-control">
                                <option value="ME">Maestro Especial</option>
                                <option value="MS">Maestro Escolarizado</option>
                            </select>
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor seleccione el tipo</div>
                        </div>
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editStatus">Estado:</label>
                            <select required name="txtStatus" id="editStatus" class="form-control">
                                <option value="1">Activo</option>
                                <option value="2">Inactivo</option>
                            </select>
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor seleccione el estado</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editPhone">Teléfono:</label>
                            <input required type="text" name="txtPhone" id="editPhone" class="form-control" placeholder="Número de teléfono">
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor ingrese el teléfono</div>
                        </div>
                        <div class="form-group col-6">
                            <label class="labelAgregar" for="editEmail">Correo:</label>
                            <input required type="email" name="txtEmail" id="editEmail" class="form-control" placeholder="Correo electrónico">
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor ingrese un correo válido</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group col-12">
                            <label class="labelAgregar" for="editAddress">Dirección:</label>
                            <input required type="text" name="txtAddress" id="editAddress" class="form-control" placeholder="Dirección completa">
                            <div class="valid-feedback">Correcto</div>
                            <div class="invalid-feedback">Por favor ingrese la dirección</div>
                        </div>
                    </div>
                   
                </div>
                <div class="modal-footer d-flex justify-content-between" style="border-top: 0;">
                            <button class="botonCancelar" type="button" class="btn btn-secondary w-25" data-bs-toggle="modal" data-bs-target="#showModal">Cancelar
                                <i id="iconoAdd" class="bi bi-x-circle-fill"></i>
                            </button>
                            <button class="botonEnter" type="submit" class="btn btn-primary w-25" id="btnGuardarEditr">Guardar Cambios
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
</div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/chartScript.js"></script>
    <script src="./js/students.js"></script>
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
    <?php
    if (isset($_GET['status'])) {
        $icon = 'success';
        $title = '';

        if ($_GET['status'] == 1 || $_GET['status'] == 'success') {
            $title = "Docente agregado correctamente";
        } else if ($_GET['status'] == 2) {
            $title = "Docente actualizado correctamente";
        } else if ($_GET['status'] == 3) {
            $title = "Docente eliminado correctamente";
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

    <!-- Script para manejar el modal show y edit -->
    <script>
        // Mostrar SweetAlert2 si hay un mensaje
        <?php if (!empty($showAlert)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '<?php echo $title; ?>',
                text: '<?php echo $message; ?>',
                icon: '<?php echo $showAlert; ?>',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#3085d6',
            });
        });
        <?php endif; ?>
        
        window.addEventListener('load', function() {
            const botonesVer = document.querySelectorAll('.btn-ver');
            let currentTeacherId = null;
            
            botonesVer.forEach(boton => {
                boton.addEventListener('click', function() {
                    const modalElement = document.getElementById('showModal');
                    if (!modalElement) {
                        return;
                    }

                    // Obtener datos del botón
                    const data = {
                        id: this.getAttribute('data-id'),
                        nombres: this.getAttribute('data-nombres'),
                        paterno: this.getAttribute('data-paterno'),
                        materno: this.getAttribute('data-materno'),
                        status: this.getAttribute('data-status'),
                        grupos: this.getAttribute('data-grupos'),
                        materias: this.getAttribute('data-materias'),
                        ine: this.getAttribute('data-ine'),
                        cedula: this.getAttribute('data-cedula'),
                        telefono: this.getAttribute('data-telefono'),
                        tipo: this.getAttribute('data-tipo'),
                        genero: this.getAttribute('data-genero'),
                        email: this.getAttribute('data-email'),
                        direccion: this.getAttribute('data-direccion'),
                        username: this.getAttribute('data-username'),
                        password: this.getAttribute('data-password')
                    };

                    // Guardar el ID del docente actual
                    currentTeacherId = data.id;

                    // Almacenar datos para el modal de edición
                    const btnEditar = modalElement.querySelector('#btnEditar');
                    Object.entries(data).forEach(([key, value]) => {
                        btnEditar.setAttribute(`data-${key}`, value || '');
                    });

                    // Llenar el modal show
                    modalElement.querySelector('#showName').textContent = data.nombres || 'No especificado';
                    modalElement.querySelector('#showLastnamePa').textContent = data.paterno || 'No especificado';
                    modalElement.querySelector('#showLastnameMa').textContent = data.materno || 'No especificado';
                    modalElement.querySelector('#showTypeTeacher').textContent = data.tipo || 'No especificado';
                    modalElement.querySelector('#showStatus').textContent = data.status || 'No especificado';
                    modalElement.querySelector('#showGender').textContent = data.genero || 'No especificado';
                    modalElement.querySelector('#showIne').textContent = data.ine || 'No especificado';
                    modalElement.querySelector('#showProfesionalID').textContent = data.cedula || 'No especificado';
                    modalElement.querySelector('#showPhone').textContent = data.telefono || 'No especificado';
                    modalElement.querySelector('#showEmail').textContent = data.email || 'No especificado';
                    modalElement.querySelector('#showAddress').textContent = data.direccion || 'No especificado';
                    modalElement.querySelector('#showUsername').textContent = data.username || 'No especificado';
                    modalElement.querySelector('#showPassword').textContent = data.password || 'No especificado';
                    modalElement.querySelector('#showGrupos').textContent = data.grupos || 'No asignados';
                    modalElement.querySelector('#showMaterias').textContent = data.materias || 'No asignadas';
                });
            });

            // Agregar evento al botón eliminar
            document.getElementById('eliminar').addEventListener('click', function() {
                if (currentTeacherId) {
                    window.location.href = `deleteTeacher.php?id=${currentTeacherId}`;
                }
            });

            // Manejar el modal de edición
            const btnEditar = document.getElementById('btnEditar');
            if (btnEditar) {
                btnEditar.addEventListener('click', function() {
                    const editModal = document.getElementById('editModal');
                    if (!editModal) {
                        return;
                    }

                    // Obtener datos almacenados en el botón
                    const data = {
                        id: this.getAttribute('data-id'),
                        nombres: this.getAttribute('data-nombres'),
                        paterno: this.getAttribute('data-paterno'),
                        materno: this.getAttribute('data-materno'),
                        status: this.getAttribute('data-status'),
                        grupos: this.getAttribute('data-grupos'),
                        materias: this.getAttribute('data-materias'),
                        ine: this.getAttribute('data-ine'),
                        cedula: this.getAttribute('data-cedula'),
                        telefono: this.getAttribute('data-telefono'),
                        tipo: this.getAttribute('data-tipo'),
                        genero: this.getAttribute('data-genero'),
                        email: this.getAttribute('data-email'),
                        direccion: this.getAttribute('data-direccion'),
                        username: this.getAttribute('data-username'),
                        password: this.getAttribute('data-password')
                    };

                    // Llenar el formulario de edición
                    editModal.querySelector('#editTeacherId').value = data.id;
                    editModal.querySelector('#editName').value = data.nombres;
                    editModal.querySelector('#editLastnamePa').value = data.paterno;
                    editModal.querySelector('#editLastnameMa').value = data.materno;
                    editModal.querySelector('#editIne').value = data.ine;
                    editModal.querySelector('#editProfesional').value = data.cedula;
                    editModal.querySelector('#editPhone').value = data.telefono;
                    editModal.querySelector('#editEmail').value = data.email;
                    editModal.querySelector('#editAddress').value = data.direccion;

                    // Seleccionar tipo de docente
                    const selectTipo = editModal.querySelector('#editTypeTeacher');
                    if (selectTipo) {
                        Array.from(selectTipo.options).forEach(option => {
                            option.selected = option.value === data.tipo;
                        });
                    }

                    // Seleccionar género
                    const selectGenero = editModal.querySelector('#editGender');
                    if (selectGenero) {
                        Array.from(selectGenero.options).forEach(option => {
                            option.selected = option.value === data.genero;
                        });
                    }

                    // Seleccionar estado
                    const selectStatus = editModal.querySelector('#editStatus');
                    if (selectStatus) {
                        const statusValue = data.status === 'Activo' ? '1' : '2';
                        Array.from(selectStatus.options).forEach(option => {
                            option.selected = option.value === statusValue;
                        });
                    }

                    // Seleccionar grupos
                    const gruposSelect = editModal.querySelector('#editGroups');
                    if (gruposSelect && data.grupos) {
                        const gruposArray = data.grupos.split(',').map(g => g.trim());
                        Array.from(gruposSelect.options).forEach(option => {
                            option.selected = gruposArray.includes(option.text);
                        });
                    }

                    // Seleccionar materias
                    const materiasSelect = editModal.querySelector('#editSubjects');
                    if (materiasSelect && data.materias) {
                        const materiasArray = data.materias.split(',').map(m => m.trim());
                        Array.from(materiasSelect.options).forEach(option => {
                            option.selected = materiasArray.includes(option.text);
                        });
                    }
                });
            }

            // Validación del formulario
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });

            // Función para buscar en la tabla
            function normalizeText(text) {
                return text.normalize('NFD').replace(/\u0300-\u036f/g, '').toLowerCase();
            }
            function highlightMatch(cell, searchText) {
                // Solo resalta si el cell no tiene hijos o solo texto plano
                if (cell.children.length === 0) {
                    const originalHtml = cell.getAttribute('data-original-html') || cell.innerHTML;
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = originalHtml;
                    const originalText = tempDiv.textContent;
                    const lowerText = normalizeText(originalText);
                    const index = lowerText.indexOf(searchText);
                    if (index !== -1 && searchText.length > 0) {
                        const before = originalText.substring(0, index);
                        const match = originalText.substring(index, index + searchText.length);
                        const after = originalText.substring(index + searchText.length);
                        cell.innerHTML = `${before}<mark>${match}</mark>${after}`;
                    } else {
                        cell.innerHTML = originalHtml;
                    }
                }
            }
            function searchTable() {
                const searchInput = document.getElementById('docente');
                const searchText = normalizeText(searchInput.value.trim());
                const table = document.querySelector('.table');
                if (!table) return;
                const rows = Array.from(table.getElementsByTagName('tbody')[0].getElementsByTagName('tr'));
                rows.forEach(row => {
                    let text = '';
                    const cells = row.getElementsByTagName('td');
                    if (cells.length >= 7) {
                        text = normalizeText([
                            cells[1].innerText,
                            cells[2].innerText,
                            cells[3].innerText,
                            cells[5].innerText,
                            cells[6].innerText
                        ].join(' '));
                    } else {
                        text = normalizeText(row.textContent);
                    }
                    // Guardar HTML original si no está guardado
                    Array.from(cells).forEach(cell => {
                        if (!cell.hasAttribute('data-original-html')) {
                            cell.setAttribute('data-original-html', cell.innerHTML);
                        }
                    });
                    if (searchText && text.includes(searchText)) {
                        row.style.display = '';
                        // Solo resalta en celdas de texto plano (no listas ni botones)
                        [1,2,3,5,6].forEach(idx => {
                            if (cells[idx]) highlightMatch(cells[idx], searchText);
                        });
                    } else if (!searchText) {
                        row.style.display = '';
                        Array.from(cells).forEach(cell => {
                            if (cell.hasAttribute('data-original-html')) {
                                cell.innerHTML = cell.getAttribute('data-original-html');
                            }
                        });
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            const searchInput = document.getElementById('docente');
            const searchButton = document.getElementById('iBuscar');
            searchInput.addEventListener('input', searchTable);
            searchButton.addEventListener('click', searchTable);
            // --- DEBUG: Mostrar cantidad de filas al cargar ---
            document.addEventListener('DOMContentLoaded', function() {
                const table = document.querySelector('.table');
                if (table) {
                    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                    console.log('Filas detectadas:', rows.length);
                } else {
                    console.log('No se encontró la tabla');
                }
            });
        });
    </script>

<script>
function normalizeText(text) {
    return text.normalize('NFD').replace(/\u0300-\u036f/g, '').toLowerCase();
}
function highlightMatch(cell, searchText) {
    if (cell.children.length === 0) {
        const originalHtml = cell.getAttribute('data-original-html') || cell.innerHTML;
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = originalHtml;
        const originalText = tempDiv.textContent;
        const lowerText = normalizeText(originalText);
        const index = lowerText.indexOf(searchText);
        if (index !== -1 && searchText.length > 0) {
            const before = originalText.substring(0, index);
            const match = originalText.substring(index, index + searchText.length);
            const after = originalText.substring(index + searchText.length);
            cell.innerHTML = `${before}<mark>${match}</mark>${after}`;
        } else {
            cell.innerHTML = originalHtml;
        }
    }
}
function searchTable() {
    const searchInput = document.getElementById('docente');
    if (!searchInput) { console.log('No se encontró el input docente'); return; }
    const searchText = normalizeText(searchInput.value.trim());
    const table = document.querySelector('.table');
    if (!table) { console.log('No se encontró la tabla'); return; }
    const rows = Array.from(table.getElementsByTagName('tbody')[0].getElementsByTagName('tr'));
    rows.forEach(row => {
        let text = '';
        const cells = row.getElementsByTagName('td');
        if (cells.length >= 7) {
            text = normalizeText([
                cells[1].innerText,
                cells[2].innerText,
                cells[3].innerText,
                cells[5].innerText,
                cells[6].innerText
            ].join(' '));
        } else {
            text = normalizeText(row.textContent);
        }
        // Guardar HTML original si no está guardado
        Array.from(cells).forEach(cell => {
            if (!cell.hasAttribute('data-original-html')) {
                cell.setAttribute('data-original-html', cell.innerHTML);
            }
        });
        console.log('Fila:', text, '| Coincide:', text.includes(searchText));
        if (searchText && text.includes(searchText)) {
            row.style.display = '';
            // Solo resalta en celdas de texto plano (no listas ni botones)
            [1,2,3,5,6].forEach(idx => {
                if (cells[idx]) highlightMatch(cells[idx], searchText);
            });
        } else if (!searchText) {
            row.style.display = '';
            Array.from(cells).forEach(cell => {
                if (cell.hasAttribute('data-original-html')) {
                    cell.innerHTML = cell.getAttribute('data-original-html');
                }
            });
        } else {
            row.style.display = 'none';
        }
    });
    console.log('Buscador ejecutado. Texto:', searchText);
}
document.addEventListener('DOMContentLoaded', function() {
    // Guardar HTML original de todas las celdas al cargar la página
    document.querySelectorAll('.table tbody tr').forEach(row => {
        row.querySelectorAll('td').forEach(cell => {
            if (!cell.hasAttribute('data-original-html')) {
                cell.setAttribute('data-original-html', cell.innerHTML);
            }
        });
    });

    const searchInput = document.getElementById('docente');
    const searchButton = document.getElementById('iBuscar');
    if (searchInput) {
        searchInput.addEventListener('input', searchTable);
    } else {
        console.log('No se encontró el input docente en DOMContentLoaded');
    }
    if (searchButton) {
        searchButton.addEventListener('click', searchTable);
    }
});
</script>
</body>
</html>

<script src="../js/teachers.js"></script>