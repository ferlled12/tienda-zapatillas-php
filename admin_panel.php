<?php
session_start();
require_once 'conexion.php';

// Control de acceso estricto: solo el admin puede entrar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado. Solo administradores.");
}

$mensaje = "";

// Procesar el formulario de inserción o eliminación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] == 'crear') {
        // Crear nuevo artículo
        $nombre = $_POST['nombre'];
        $marca = $_POST['marca'];
        $talla = $_POST['talla'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];

        $sql = "INSERT INTO Articulos (nombre_zapatilla, marca, talla, precio, stock) VALUES (:nombre, :marca, :talla, :precio, :stock)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nombre' => $nombre, ':marca' => $marca, ':talla' => $talla, ':precio' => $precio, ':stock' => $stock]);
        $mensaje = "Artículo añadido correctamente.";
        
    } elseif (isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
        // Eliminar artículo
        $id_articulo = $_POST['id_articulo'];
        
        try {
            $sql = "DELETE FROM Articulos WHERE id_articulo = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id_articulo]);
            $mensaje = "Artículo eliminado.";
        } catch (PDOException $e) {
            $mensaje = "Error: No se puede eliminar el artículo porque ya está en el pedido de un cliente.";
        }
    }
}

// Obtener todos los artículos para listarlos
$articulos = $pdo->query("SELECT * FROM Articulos")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de administración</title>
</head>
<body>
    <h2>Panel de administración de Artículos</h2>
    <a href="logout.php">Cerrar sesión</a>
    <p style="color: green;"><?= $mensaje ?></p>
    <hr>

    <h3>Añadir nueva zapatilla</h3>
    <form action="admin_panel.php" method="POST">
        <input type="hidden" name="accion" value="crear">
        <label>Nombre:</label> <input type="text" name="nombre" required>
        <label>Marca:</label> <input type="text" name="marca" required>
        <label>Talla:</label> <input type="number" step="0.5" name="talla" required>
        <label>Precio:</label> <input type="number" step="0.01" name="precio" required>
        <label>Stock:</label> <input type="number" name="stock" required>
        <button type="submit">Guardar Artículo</button>
    </form>

    <hr>
    <h3>Listado de artículos</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th><th>Nombre</th><th>Marca</th><th>Talla</th><th>Precio</th><th>Stock</th><th>Acción</th>
        </tr>
        <?php foreach ($articulos as $item): ?>
        <tr>
            <td><?= $item['id_articulo'] ?></td>
            <td><?= $item['nombre_zapatilla'] ?></td>
            <td><?= $item['marca'] ?></td>
            <td><?= $item['talla'] ?></td>
            <td><?= $item['precio'] ?> €</td>
            <td><?= $item['stock'] ?></td>
            <td>
                <!-- Formulario para eliminar -->
                <form action="admin_panel.php" method="POST" style="display:inline;">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_articulo" value="<?= $item['id_articulo'] ?>">
                    <button type="submit" onclick="return confirm('¿Seguro que quieres borrarlo?');">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>