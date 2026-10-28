<?php
require_once 'conexion.php';

$mensaje = "";

// Capa de lógica de negocio y acceso a datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $direccion = !empty($_POST['direccion']) ? $_POST['direccion'] : null; // Opcional

    // Encriptar la contraseña por seguridad
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO Usuarios (nombre_usuario, email, password, direccion_envio, rol) 
                VALUES (:nombre, :email, :password, :direccion, 'cliente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => $passwordHash,
            ':direccion' => $direccion
        ]);
        $mensaje = "Registro completado con éxito. Ya puedes iniciar sesión.";
    } catch (PDOException $e) {
        $mensaje = "Error al registrar: " . $e->getMessage();
    }
}
?>

<!-- Capa cliente (HTML) -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Tienda Zapatillas</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h2>Registro de nuevo cliente</h2>
    <p><?= $mensaje ?></p>
    <form action="registro.php" method="POST">
        <label>Nombre de usuario (obligatorio):</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Email (obligatorio):</label><br>
        <input type="email" name="email" required><br><br>

        <label>Contraseña (obligatorio):</label><br>
        <input type="password" name="password" required><br><br>

        <label>Dirección de envío (opcional):</label><br>
        <input type="text" name="direccion"><br><br>

        <button type="submit">Registrarse</button>
    </form>
    <a href="login.php">¿Ya tienes cuenta? Inicia sesión aquí</a>
</body>
</html>