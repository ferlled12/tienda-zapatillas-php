<?php
// conexion.php

$host = 'sql211.infinityfree.com'; // Cambiar si tu servidor web proporciona otro host
$dbname = 'if0_43288759_tienda'; // El nombre que le hayas dado a tu base de datos en MySQL
$username = 'if0_43288759'; // Usuario por defecto en entornos locales (XAMPP/MAMP)
$password = 'XsbmGUCCC8'; // Contraseña por defecto en entornos locales (suele estar vacía o ser 'root')

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar PDO para que lance excepciones si hay errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>