<?php
// Asegurarnos de que tenemos la conexión a la base de datos
if (!isset($conexion)) {
    require_once "../conection.php";
}

// Obtener la información del usuario
$user_id = $_SESSION['user_id'];
$query = "SELECT ui.names, ui.lastnamePa 
          FROM users u 
          JOIN usersInfo ui ON u.idUserInfo = ui.idUserInfo 
          WHERE u.idUser = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
?>
<link rel="icon" href="../img/logo.ico">
<header class="p-2" style="background-color: #192E4E;">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid text-white">
                    <div class="col-8 row">                    
                        <h5 class="col-6 px-4 pt-3">Escuela Gregorio Torres Quintero No. 2308</h5>
                    </div>                    
                    <div class="collapse navbar-collapse justify-content-end"id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="navbar-item1 mx-3 dropdown text-black">
                                <a href="" style="color: white; text-decoration: none;" class="nav-link1 dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php
                                    if ($user_data) {
                                        echo htmlspecialchars($user_data['names'] . ' ' . $user_data['lastnamePa']);
                                    } else {
                                        echo "Administrador";
                                    }
                                ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li>
                                        <a href="../admin/php/logout.php" class="dropdown-item">
                                            <i class="bi bi-box-arrow-left"></i>&nbsp;&nbsp;Cerrar Sesión</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>       
            </nav>
        </header>