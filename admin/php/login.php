<?php
// Debe ser lo PRIMERO en el archivo, sin espacios/blancos antes
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluir la conexión
    require_once "../../conection.php";
    
    // Verificar si la conexión se estableció
    if (!$conexion) {
        $_SESSION['error'] = 'Error de conexión a la base de datos';
        header('Location: /Gestor_de_calificaciones/index.php');
        exit();
    }
    
    // Validar datos
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $_SESSION['error'] = 'Por favor, complete todos los campos';
        header('Location: /Gestor_de_calificaciones/index.php');
        exit();
    }

    $username = mysqli_real_escape_string($conexion, $_POST['username']);
    $password = $_POST['password'];

    // Obtener el usuario
    $sql = "SELECT u.*, r.level_ as role, r.description as role_description 
            FROM users u 
            JOIN roles r ON u.idRole = r.idRole 
            WHERE u.username = ?";
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = 'Error en la consulta';
        header('Location: /Gestor_de_calificaciones/index.php');
        exit();
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        $_SESSION['error'] = 'Error al ejecutar la consulta';
        header('Location: /Gestor_de_calificaciones/index.php');
        exit();
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Verificar la contraseña
        if ($password === $row['raw_password']) {
            $_SESSION['user_id'] = $row['idUser'];  
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['role_description'] = $row['role_description'];
            $_SESSION['idRole'] = $row['idRole'];
            
            // Recordar sesión si el usuario marcó "Recordarme"
            if (isset($_POST['rememberMe'])) {
                // Generar un token seguro
                $token = bin2hex(random_bytes(32));
                $userId = $row['idUser'];
                // Guardar el token en la base de datos (tabla user_remember_tokens)
                $conexion->query("INSERT INTO user_remember_tokens (idUser, token, expires) VALUES ($userId, '$token', DATE_ADD(NOW(), INTERVAL 30 DAY)) ON DUPLICATE KEY UPDATE token='$token', expires=DATE_ADD(NOW(), INTERVAL 30 DAY)");
                // Crear cookie por 30 días
                setcookie('rememberMe', $token, time() + (86400 * 30), "/", "", false, true);
            }
            
            // Redirigir según el rol
            if ($_SESSION['role'] === 'AD' || $_SESSION['idRole'] === 3) {
                header("Location: /Gestor_de_calificaciones/admin/dashboard.php");
            } else {
                header("Location: /Gestor_de_calificaciones/teachers/dashboard.php");
            }
            exit();
        } else {
            $_SESSION['error'] = 'Usuario o contraseña incorrectos';
            header('Location: /Gestor_de_calificaciones/index.php');
            exit();
        }
    } else {
        $_SESSION['error'] = 'Usuario o contraseña incorrectos';
        header('Location: /Gestor_de_calificaciones/index.php');
        exit();
    }
} else {
    // Si alguien intenta acceder directamente a login.php
    header('Location: /Gestor_de_calificaciones/index.php');
    exit();
}
?>