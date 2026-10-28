<?php
// Iniciamos la sesión para poder destruirla
session_start();

// Vaciamos todas las variables de sesión (usuario, carrito, etc.)
$_SESSION = array();

// Destruimos la sesión por completo
session_destroy();

// Redirigimos al usuario a la portada pública
header("Location: index.php");
exit;
?>