<?php
// Este archivo verifica si es necesario restaurar los datos del usuario demo
// y lo hace automáticamente si ha pasado el tiempo límite

// Determinar la ruta correcta al archivo de conexión según el entorno
$basePath = dirname(dirname(__FILE__)); // Directorio padre (raíz del proyecto)

// Archivo que almacena la última vez que se restauraron los datos
$lastRestoreFile = __DIR__ . "/last_restore.txt";

// Intervalo de restauración en segundos (1 hora = 3600 segundos)
$restoreInterval = 3600;

// Verificar si es necesario restaurar los datos
$needsRestore = false;

if (file_exists($lastRestoreFile)) {
    $lastRestoreTime = (int)file_get_contents($lastRestoreFile);
    $currentTime = time();
    
    // Si ha pasado el intervalo de restauración
    if (($currentTime - $lastRestoreTime) >= $restoreInterval) {
        $needsRestore = true;
    }
} else {
    // Si no existe el archivo, crearlo con la hora actual
    file_put_contents($lastRestoreFile, time());
}

// Restaurar los datos si es necesario
if ($needsRestore) {
    // Incluir y ejecutar el script de restauración
    require_once __DIR__ . "/restore_demo_data.php";
    
    // Actualizar el tiempo de última restauración
    file_put_contents($lastRestoreFile, time());
}
?>
