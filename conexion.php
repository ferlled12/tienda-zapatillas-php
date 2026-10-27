<?php
// conexion.php

$host = 'sql211.infinityfree.com'; // Cambiar si tu servidor web proporciona otro host
$dbname = 'if0_43288759_tienda'; // El nombre que le hayas dado a tu base de datos en MySQL
$username = 'if0_43288759'; // Usuario por defecto en entornos locales (XAMPP/MAMP)
$password = 'XsbmGUCCC8'; // Contraseña por defecto en entornos locales (suele estar vacía o ser 'root')

try {
    // Configuración de la conexión PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Activa el lanzamiento de excepciones en caso de error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve los resultados como arrays asociativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactiva la emulación para usar consultas preparadas reales
    ];

    // Creación de la instancia PDO
    $pdo = new PDO($dsn, $username, $password, $opciones);

} catch (PDOException $e) {
    // Si hay un error, detenemos la ejecución y mostramos el mensaje
    // Nota: en un entorno de producción, esto debería registrarse en un archivo de log en lugar de mostrarse al usuario
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>