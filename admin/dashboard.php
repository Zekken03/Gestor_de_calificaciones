<?php
// Debe ser lo PRIMERO en el archivo, sin espacios/blancos antes
require_once "check_session.php";
require_once "php/prevent_cache.php";

// Verificar si es necesario restaurar los datos del usuario demo
if (file_exists(__DIR__ . "/../demo/auto_restore.php")) {
    include_once __DIR__ . "/../demo/auto_restore.php";
}

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'AD') {
    session_destroy();
    header("Location: /Gestor_de_calificaciones/index.php");
    exit();
}

require_once "../conection.php";

// Obtener la información del usuario actual
$user_id = $_SESSION['user_id'];

// Contar alumnos
$resAlumnos = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM students");
$totalAlumnos = mysqli_fetch_assoc($resAlumnos)['total'];

// Contar docentes
$resDocentes = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM teachers");
$totalDocentes = mysqli_fetch_assoc($resDocentes)['total'];

// Contar materias
$resMaterias = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM subjects");
$totalMaterias = mysqli_fetch_assoc($resMaterias)['total'];
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
        include "../layouts/aside.php"; 
    ?>
    <!-- END ASIDEBAR -->
    <!-- MAIN CONTENT -->
     <main class="flex-grow-1 col-9 p-0 ">
        <?php
            include "../layouts/header.php"; 
        ?>
        <section class="container mt-4" style="padding-top:10vh">
            <h2 class="pt-3">BIENVENIDO</h2>
            <div class="alert alert-info text-center my-3" >
                Fecha límite de calificaciones: <strong id="fechaLimiteDashboard">Cargando...</strong>
            </div>
            <!-- STATS -->
             <div class="row text-center">
             <div class="col-4">
    <div class="card" id="card" style="height: 100px;">
                        <div class="card-body" >
                            <label>Total de Alumnos</label>
                            <h5 class="h2"id="totalAlumnos"> <?php echo $totalAlumnos; ?> </h5>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card" id="card" style="height: 100px;">
                        <div class="card-body" >
                            <label>Total de Docentes</label>
                            <h5 class="h2"id="totalDocentes"> <?php echo $totalDocentes; ?> </h5>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card" id="card" style="height: 100px;">
                        <div class="card-body" >
                            <label>Total de Materias</label>
                            <h5 class="h2"id="totalMaterias"> <?php echo $totalMaterias; ?> </h5>
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
                            Porcentaje de Grupos Aprobados
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
    <!-- CALENADARIO DASHBOARD -->
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
</body>
</html>