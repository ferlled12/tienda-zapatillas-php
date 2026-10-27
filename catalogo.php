<?php
session_start();
require_once 'conexion.php';

// Verificamos que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// Obtenemos todos los artículos con stock disponible
$sql = "SELECT * FROM Articulos WHERE stock > 0";
$stmt = $pdo->query($sql);
$articulos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - Tienda Zapatillas</title>
</head>
<body>
    <h2>Bienvenido, <?= $_SESSION['nombre'] ?></h2>
    <a href="carrito.php">Ver mi carrito</a> | <a href="logout.php">Cerrar sesión</a>
    <hr>
    
    <h3>Catálogo de Zapatillas</h3>
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <?php foreach ($articulos as $articulo): ?>
            <div style="border: 1px solid #ccc; padding: 10px; width: 200px;">
                <h4><?= $articulo['nombre_zapatilla'] ?></h4>
                <p>Marca: <?= $articulo['marca'] ?></p>
                <p>Talla: <?= $articulo['talla'] ?></p>
                <p>Precio: <?= $articulo['precio'] ?> €</p>
                <!-- Formulario para enviar el artículo al carrito -->
                <form action="carrito.php" method="POST">
                    <input type="hidden" name="id_articulo" value="<?= $articulo['id_articulo'] ?>">
                    <input type="hidden" name="accion" value="agregar">
                    <label>Cantidad:</label>
                    <input type="number" name="cantidad" value="1" min="1" max="<?= $articulo['stock'] ?>" style="width: 50px;">
                    <br><br>
                    <button type="submit">Agregar al carrito</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>