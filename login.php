<?php
session_start(); // Fundamental para mantener el estado de la aplicación
require_once 'conexion.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    // Verificamos si el usuario existe y la contraseña es correcta
    if ($usuario && password_verify($password, $usuario['password'])) {
        // Guardamos los datos en la sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre_usuario'];
        $_SESSION['rol'] = $usuario['rol'];

        // Redirección basada en el rol
        if ($usuario['rol'] == 'admin') {
            header("Location: admin_panel.php");
        } else {
            header("Location: catalogo.php");
        }
        exit;
    } else {
        $mensaje = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Tienda Zapatillas</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h2>Acceso de usuarios</h2>
    <p style="color:red;"><?= $mensaje ?></p>
    <form action="login.php" method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Contraseña:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Entrar</button>
    </form>
    <a href="registro.php">¿No tienes cuenta? Regístrate aquí</a>
</body>
</html>