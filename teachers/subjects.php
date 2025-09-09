<?php
require_once "check_session.php";
require_once "../conection.php";

// --- FECHA LIMITE GLOBAL ---
$fechaLimite = null;
$res = $conexion->query("SELECT limitDate FROM limitDate WHERE idLimitDate = 1 LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $fechaLimite = $row['limitDate'];
}
$hoy = date('Y-m-d');
$fueraDePlazo = ($fechaLimite && $hoy > date('Y-m-d', strtotime($fechaLimite . ' +0 day')));

// Paso 1: Obtener el idTeacher del usuario logueado
$user_id = $_SESSION['user_id'];
$sqlTeacher = "SELECT idTeacher FROM teachers WHERE idUser = ?";
$stmtTeacher = $conexion->prepare($sqlTeacher);
$stmtTeacher->bind_param("i", $user_id);
$stmtTeacher->execute();
$resTeacher = $stmtTeacher->get_result();
$rowTeacher = $resTeacher->fetch_assoc();
$teacher_id = $rowTeacher ? $rowTeacher['idTeacher'] : null;

$subjects = [];
if ($teacher_id) {
    // Paso 2: Obtener las materias asignadas a este docente (sin repetir por idSubject)
    $query = "SELECT 
                s.idSubject, 
                s.name, 
                s.specialSubject, 
                s.description, 
                la.name AS learningAreaName,
                sy.startDate,
                sy.endDate,
                ts.idTeacherSubject
              FROM teacherSubject ts
              JOIN subjects s ON ts.idSubject = s.idSubject
              JOIN learningArea la ON s.idLearningArea = la.idLearningArea
              JOIN schoolYear sy ON ts.idSchoolYear = sy.idSchoolYear
              WHERE ts.idTeacher = ?
              GROUP BY s.idSubject, sy.startDate, sy.endDate
              ORDER BY sy.startDate DESC, s.name ASC";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Formatear ciclo escolar como 'YYYY-YYYY'
        
        $row['schoolYearFormatted'] = $row['startDate'] . '   -   ' . $row['endDate'];
        $subjects[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/teacher/subject.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.2/main.min.css">
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
    <?php include "../layouts/asideTeacher.php"; ?>
    <main class="flex-grow-1 col-9 p-0 ">
        <?php include "../layouts/headerTeacher.php"; ?>
        <h1 id="titulo">Mis Materias</h1>
        <div class="row" style="margin-left:10vh; width:100%;">
            <?php 
            $count = 0;
            foreach ($subjects as $subject): 
                if ($count % 2 == 0 && $count != 0) {
                    echo '</div><div class="row" style="margin-left:10vh; width:100%;">';
                }
            ?>
            <div class="col-5 p-0 my-2 px-2">
                <div class="card" style="margin-top:2vh">
                    <div class="card-body" style="width:100%; background-color:#192E4E; border-radius: 20px; color: white;">
                        <h5 class="card-title"><?php echo htmlspecialchars($subject['name']); ?></h5>
                        <h5><?php echo htmlspecialchars($subject['description']); ?></h5>
                        <p>Materia Especial: <?php echo $subject['specialSubject'] ? 'Sí' : 'No'; ?></p>
                        <p>Campo Formativo: <?php echo htmlspecialchars($subject['learningAreaName']); ?></p>
                        <p>Ciclo: <?php echo htmlspecialchars($subject['schoolYearFormatted']); ?></p>
                        <div id="button" style="margin-top:5%; margin-left:70%;">
                            <a href="./gradesSubject.php?idSubject=<?php echo $subject['idSubject']; ?>"
                               class="btn<?php if($fueraDePlazo) echo ' disabled'; ?>"
                               <?php if($fueraDePlazo) echo 'tabindex="-1" aria-disabled="true" style="pointer-events:none;"'; ?>>
                               <?php echo $fueraDePlazo ? 'Fuera de plazo' : 'Ingresar'; ?>
                            </a>
                        </div>
                        <style>
                            #button .btn {
                                background-color:rgb(38, 75, 130);
                                color: white;
                                
                                padding: 10px 20px;
                                border-radius: 10px;
                                font-weight: 500;
                                text-decoration: none;
                                width: 200px;
                            }
                            #button .btn.disabled {
                                background-color: #435d83;
                                opacity: 0.7;
                            }
                            #button .btn:hover {
                                background-color:rgb(27, 84, 168);
                                color: white;
                                
                                padding: 10px 20px;
                                border-radius: 10px;
                                font-weight: 500;
                                text-decoration: none;
                                width: 200px;
                            }
                        </style> 
                    </div>
                </div>
            </div>
            <?php 
                $count++;
            endforeach; 
            if (empty($subjects)): ?>
            <div class="col-12 text-center mt-4">
                <h3>No tienes materias asignadas actualmente.</h3>
            </div>
            <?php endif; ?>
        </div>    
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
</body>
</html>