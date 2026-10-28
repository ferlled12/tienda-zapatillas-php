<?php
session_start();
require_once 'conexion.php';

// 1. ELIMINAR USUARIO
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
    $stmt->execute([$id]);
    header("Location: usuarios_admin.php");
    exit;
}

// 2. CREAR USUARIO
if (isset($_POST['crear'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Añade password_hash() si encriptas las contraseñas
    $rol = $_POST['rol'];
    $stmt = $pdo->prepare("INSERT INTO Usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $password, $rol]);
    header("Location: usuarios_admin.php");
    exit;
}

// 3. EDITAR USUARIO (Guardar cambios)
if (isset($_POST['editar'])) {
    $id = $_POST['id_usuario'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $stmt = $pdo->prepare("UPDATE Usuarios SET nombre = ?, email = ?, rol = ? WHERE id_usuario = ?");
    $stmt->execute([$nombre, $email, $rol, $id]);
    header("Location: usuarios_admin.php");
    exit;
}

// LEER TODOS LOS USUARIOS
$usuarios = $pdo->query("SELECT * FROM Usuarios")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de usuarios - admin</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header class="tienda-header">
        <div class="logo">PANEL <span>ADMIN</span></div>
        <nav class="navegacion">
            <a href="admin_panel.php" class="btn-nav">Gestión artículos</a>
            <a href="usuarios_admin.php" class="btn-nav btn-carrito">Gestión usuarios</a>
            <a href="logout.php" class="btn-nav salir">Cerrar sesión</a>
        </nav>
    </header>

    <main class="contenedor-tienda">
        <h2>Gestión de usuarios</h2>
        
        <!-- Formulario para CREAR -->
        <div class="contenedor" style="margin-bottom: 30px;">
            <h3 style="color: var(--azul-principal); margin-top: 0;">Crear nuevo usuario</h3>
            <form action="usuarios_admin.php" method="POST" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <input type="text" name="nombre" placeholder="Nombre completo" required style="flex: 1;">
                <input type="email" name="email" placeholder="Correo electrónico" required style="flex: 1;">
                <input type="password" name="password" placeholder="Contraseña" required style="flex: 1;">
                <select name="rol" required style="padding: 14px; border-radius: 12px; border: 1px solid #cbd5e1; flex: 1;">
                    <option value="cliente">Cliente</option>
                    <option value="administrador">Admin</option>
                </select>
                <button type="submit" name="crear" class="btn-comprar">Crear usuario</button>
            </form>
        </div>

        <!-- Tabla para LEER, ACTUALIZAR Y ELIMINAR -->
        <div class="contenedor">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <form action="usuarios_admin.php" method="POST">
                            <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                            <td><?= $u['id_usuario'] ?></td>
                            <td><input type="text" name="nombre" value="<?= htmlspecialchars($u['nombre']) ?>" style="padding: 8px;"></td>
                            <td><input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" style="padding: 8px;"></td>
                            <td>
                                <select name="rol" style="padding: 8px;">
                                    <option value="cliente" <?= $u['rol'] == 'cliente' ? 'selected' : '' ?>>Cliente</option>
                                    <option value="administrador" <?= $u['rol'] == 'administrador' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </td>
                            <td>
                                <button type="submit" name="editar" style="background-color: #10b981; padding: 8px 15px; font-size: 0.9rem;">Guardar</button>
                                <a href="usuarios_admin.php?eliminar=<?= $u['id_usuario'] ?>" style="background-color: var(--rosa-acento); color: white; padding: 8px 15px; border-radius: 30px; text-decoration: none; font-size: 0.9rem; margin-left: 5px; font-weight: 600;" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>