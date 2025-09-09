<?php
$preventCache = true;
$sessionStarted = true;
require_once "../admin/php/prevent_cache.php";

// Verificar si es necesario restaurar los datos del usuario demo
if (file_exists(__DIR__ . "/../demo/auto_restore.php")) {
    include_once __DIR__ . "/../demo/auto_restore.php";
}

require_once "check_session.php";
require_once "../conection.php";

$fechaLimite = null;
$res = $conexion->query("SELECT limitDate FROM limitDate WHERE idLimitDate = 1 LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $fechaLimite = $row['limitDate'];
}

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: /Gestor_de_calificaciones/index.php");
    exit();
}

// Obtener la información del usuario actual
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Primero obtener el idTeacher correspondiente al user_id
$sqlTeacher = "SELECT t.idTeacher 
               FROM teachers t 
               INNER JOIN users u ON t.idUser = u.idUser 
               WHERE u.idUser = ?";

// Preparar la consulta
$stmt = $conexion->prepare($sqlTeacher);
if (!$stmt) {
    error_log("Error en la consulta del dashboard: " . $conexion->error);
    die("Error al cargar la información del docente. Por favor, intente más tarde.");
}

// Vincular parámetros
$stmt->bind_param("i", $user_id);

// Ejecutar consulta
if (!$stmt->execute()) {
    error_log("Error al ejecutar consulta en dashboard: " . $stmt->error);
    die("Error al cargar la información. Por favor, intente más tarde.");
}

// Obtener resultados
$resTeacher = $stmt->get_result();

// Verificar si se encontró el profesor
if ($teacherData = $resTeacher->fetch_assoc()) {
    $teacher_id = $teacherData['idTeacher'];
    
    // Contar materias del usuario actual
    $sqlMaterias = "SELECT COUNT(DISTINCT tgs.idSubject) AS total 
                   FROM teacherGroupsSubjects tgs
                   WHERE tgs.idTeacher = ?";
    
    $stmt = $conexion->prepare($sqlMaterias);
    if (!$stmt) {
        error_log("Error en consulta de materias: " . $conexion->error);
        die("Error al cargar las materias. Por favor, intente más tarde.");
    }
    
    $stmt->bind_param("i", $teacher_id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar consulta de materias: " . $stmt->error);
        die("Error al cargar la información de materias.");
    }
    
    $resMaterias = $stmt->get_result();
    $totalMaterias = $resMaterias->fetch_assoc()['total'];
    
    // Contar alumnos del maestro
    $sqlAlumnos = "SELECT COUNT(DISTINCT s.idStudent) AS total
                  FROM students s
                  JOIN groups g ON s.idGroup = g.idGroup
                  JOIN teacherGroupsSubjects tgs ON tgs.idGroup = g.idGroup
                  WHERE tgs.idTeacher = ?";
    
    $stmt = $conexion->prepare($sqlAlumnos);
    if (!$stmt) {
        error_log("Error en consulta de alumnos: " . $conexion->error);
        die("Error al cargar la información de alumnos.");
    }
    
    $stmt->bind_param("i", $teacher_id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar consulta de alumnos: " . $stmt->error);
        die("Error al procesar la información de alumnos.");
    }
    
    $resAlumnos = $stmt->get_result();
    $totalAlumnos = $resAlumnos->fetch_assoc()['total'];
    
    // Obtener información de las materias del usuario
    $sqlMateriasInfo = "SELECT DISTINCT s.name, s.specialSubject
                       FROM teacherGroupsSubjects tgs
                       JOIN subjects s ON tgs.idSubject = s.idSubject
                       WHERE tgs.idTeacher = ?";
    
    $stmt = $conexion->prepare($sqlMateriasInfo);
    if (!$stmt) {
        error_log("Error en consulta de información de materias: " . $conexion->error);
        die("Error al cargar la información académica.");
    }
    
    $stmt->bind_param("i", $teacher_id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar consulta de información de materias: " . $stmt->error);
        die("Error al procesar la información académica.");
    }
    
    $materiasInfo = $stmt->get_result();
} else {
    // No se encontró el profesor
    $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
    header('Location: /Gestor_de_calificaciones/index.php');
    exit();
}

// Contar docentes (solo colegas del mismo departamento o escuela)
$sqlDocentes = "SELECT COUNT(DISTINCT t2.idTeacher) AS total 
               FROM teachers t1
               JOIN teachers t2 ON t1.department = t2.department  -- Asumiendo que hay un campo department
               WHERE t1.idTeacher = ?";

$stmt = $conexion->prepare($sqlDocentes);
if (!$stmt) {
    error_log("Error en consulta de docentes: " . $conexion->error);
    $totalDocentes = 0;
} else {
    $stmt->bind_param("i", $teacher_id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar consulta de docentes: " . $stmt->error);
        $totalDocentes = 0;
    } else {
        $resDocentes = $stmt->get_result();
        $totalDocentes = $resDocentes->fetch_assoc()['total'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.css">
    <link rel="stylesheet" href="../css/teacher/dashboard.css">
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
        include "../layouts/asideTeacher.php"; 
    ?>
    <!-- END ASIDEBAR -->
    <!-- MAIN CONTENT -->
     <main class="flex-grow-1 col-9 p-0 ">
        <?php
            include "../layouts/headerTeacher.php"; 
        ?>
        <section class="container mt-4" style="padding-top:10vh">
            <h2 class="pt-3">BIENVENIDO</h2>
            <!-- Mostrar fecha límite en dashboard -->
 
            <!-- STATS -->
             <div class="row text-center">
                <div class="col-4" >
                    <div class="card" id="card"  style="height: 100px;">
                        <div class="card-body">
                            <label>
                                Materias Asignadas
                            </label>
                            <h5 class="h2" id="totalMaterias"> <?php echo $totalMaterias; ?> </h5>
                            
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card" id="card"  style="height: 100px;">
                        <div class="card-body">
                            <label class="">
                                Total de Alumnos
                            </label>
                            <h5 class="h2" id="totalAlumnos"> <?php echo $totalAlumnos; ?> </h5>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card" id="card" style="height: 100px;">
                        <div class="card-body">
                            <label class="">
                                Fecha límite para subir calificaciones
                            </label>
                            <h5 class="h2" id="fechaLimiteDashboard"><?php echo $fechaLimite ? htmlspecialchars($fechaLimite) : 'No configurada'; ?></h5>
                        </div>
                    </div>
                </div>
             </div>
            <!-- STATS -->
            <!-- CHARTS -->
             <div class="row mt-4">
                <div class="col-5">
                    <div class="card" style="height: 600px;">
                        <div class="card-header text-center">
                            Calendario de Eventos
                        </div>
                        <div class="card-body mt-1 mx-1 p-0 mb-0">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>

                <div class="col-7">
                    <div  class="card" style="height: 600px;">
                        <div class="card-header text-center">
                            Porcentaje de Alumnos Aprobados
                        </div>
                        <div id="chart" class="card-body ms-4 position-absolute top-50 start-50 translate-middle " style="height: 500px; width: 500px;">
                            <canvas id="chartCategorias" style="height: 300px; width: 300px;" class="ms-4"></canvas>
                        </div>
                    </div>
                </div>
             </div> 
             
            <!-- CHARTS -->
        </section>

     </main>
    <!-- END MAIN CONTENT --> 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/chartScript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.js"></script>
    <script>
        // Mostrar la fecha límite en el dashboard (SIEMPRE desde la base de datos)
        function mostrarFechaLimiteDashboard() {
            fetch('get_fecha_limite.php')
                .then(response => response.json())
                .then(data => {
                    const el = document.getElementById('fechaLimiteDashboard');
                    if (data.success && data.fechaLimite) {
                        el.textContent = data.fechaLimite;
                    } else {
                        el.textContent = 'No definida';
                    }
                });
        }
        document.addEventListener('DOMContentLoaded', mostrarFechaLimiteDashboard);
    </script>
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

        document.addEventListener('DOMContentLoaded', function() {
            fetch('get_fecha_limite.php')
                .then(response => response.json())
                .then(data => {
                    const fechaLimite = data.fechaLimite;
                    const eventos = [];
                    if (fechaLimite) {
                        eventos.push({
                            id: 'cierre-calificaciones',
                            title: 'Cierre de calificaciones',
                            start: fechaLimite,
                            color: '#e74c3c'
                        });
                    }
                    const calendarEl = document.getElementById('calendar');
                    window.calendar = new FullCalendar.Calendar(calendarEl, {
                        locale: 'es',
                        headerToolbar: {
                            left: 'prev,next',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        events: eventos
                    });
                    window.calendar.render();
                });
        });
    </script>
</body>
</html>