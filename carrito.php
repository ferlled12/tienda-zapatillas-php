<?php
session_start();
require_once 'conexion.php';

// Verificamos que el usuario sea un cliente logueado
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit;
}

$mensaje = "";

// Inicializar el carrito en la sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Capa de Lógica de Negocio: Procesar acciones del usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion == 'agregar') {
        $id_articulo = (int)$_POST['id_articulo'];
        $cantidad = (int)$_POST['cantidad'];
        
        // Si el artículo ya está en el carrito, sumamos la cantidad
        if (isset($_SESSION['carrito'][$id_articulo])) {
            $_SESSION['carrito'][$id_articulo] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_articulo] = $cantidad;
        }
        $mensaje = "Artículo añadido al carrito.";
    } 
    elseif ($accion == 'vaciar') {
        $_SESSION['carrito'] = [];
        $mensaje = "El carrito ha sido vaciado.";
    } 
    elseif ($accion == 'comprar') {
        if (!empty($_SESSION['carrito'])) {
            try {
                // Iniciamos una transacción para asegurar la integridad de los datos
                $pdo->beginTransaction();

                // 1. Calcular el precio total consultando los precios actuales en la BD
                $precio_total = 0;
                $lineas = [];
                
                foreach ($_SESSION['carrito'] as $id_art => $cant) {
                    $stmt = $pdo->prepare("SELECT precio FROM Articulos WHERE id_articulo = :id");
                    $stmt->execute([':id' => $id_art]);
                    $articulo = $stmt->fetch();
                    
                    if ($articulo) {
                        $precio_total += $articulo['precio'] * $cant;
                        $lineas[] = [
                            'id_articulo' => $id_art,
                            'cantidad' => $cant,
                            'precio_unitario' => $articulo['precio']
                        ];
                    }
                }

                // 2. Insertar la cabecera del pedido en la tabla Pedidos
                $sqlPedido = "INSERT INTO Pedidos (id_usuario, precio_total, estado_pedido) VALUES (:id_usuario, :precio_total, 'completado')";
                $stmtPedido = $pdo->prepare($sqlPedido);
                $stmtPedido->execute([
                    ':id_usuario' => $_SESSION['id_usuario'],
                    ':precio_total' => $precio_total
                ]);
                
                $id_pedido = $pdo->lastInsertId(); // Recuperamos el ID autogenerado del pedido

                // 3. Insertar los detalles en la tabla Lineas_Pedido y restar stock
                $sqlLinea = "INSERT INTO Lineas_Pedido (id_pedido, id_articulo, cantidad_comprada, precio_unitario) VALUES (:id_pedido, :id_articulo, :cantidad, :precio_unitario)";
                $stmtLinea = $pdo->prepare($sqlLinea);
                
                $sqlStock = "UPDATE Articulos SET stock = stock - :cantidad WHERE id_articulo = :id_articulo";
                $stmtStock = $pdo->prepare($sqlStock);

                foreach ($lineas as $linea) {
                    // Guardar línea de pedido
                    $stmtLinea->execute([
                        ':id_pedido' => $id_pedido,
                        ':id_articulo' => $linea['id_articulo'],
                        ':cantidad' => $linea['cantidad'],
                        ':precio_unitario' => $linea['precio_unitario']
                    ]);
                    // Actualizar stock
                    $stmtStock->execute([
                        ':cantidad' => $linea['cantidad'],
                        ':id_articulo' => $linea['id_articulo']
                    ]);
                }

                // Confirmamos la transacción
                $pdo->commit();
                
                // Vaciamos el carrito tras la compra
                $_SESSION['carrito'] = [];
                $mensaje = "¡Compra realizada con éxito! Tu número de pedido es: " . $id_pedido;

            } catch (Exception $e) {
                $pdo->rollBack(); // Si hay error, deshacemos todos los cambios en la BD
                $mensaje = "Error al procesar la compra: " . $e->getMessage();
            }
        } else {
            $mensaje = "El carrito está vacío.";
        }
    }
}

// Capa de presentación: preparar datos para mostrar el carrito
$datosCarrito = [];
$totalCarrito = 0;

if (!empty($_SESSION['carrito'])) {
    // Obtenemos los IDs de los artículos en el carrito
    $ids = implode(',', array_keys($_SESSION['carrito']));
    // Consultamos los detalles de esos artículos
    $sql = "SELECT id_articulo, nombre_zapatilla, precio FROM Articulos WHERE id_articulo IN ($ids)";
    $articulosCarrito = $pdo->query($sql)->fetchAll();

    foreach ($articulosCarrito as $item) {
        $id = $item['id_articulo'];
        $cantidad = $_SESSION['carrito'][$id];
        $subtotal = $item['precio'] * $cantidad;
        $totalCarrito += $subtotal;
        
        $item['cantidad'] = $cantidad;
        $item['subtotal'] = $subtotal;
        $datosCarrito[] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi carrito - Tienda zapatillas</title>
</head>
<body>
    <h2>Carrito de <?= $_SESSION['nombre'] ?></h2>
    <a href="catalogo.php">Volver al catálogo</a>
    <p style="color: green; font-weight: bold;"><?= $mensaje ?></p>
    <hr>

    <?php if (empty($datosCarrito)): ?>
        <p>Tu carrito está vacío actualmente.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" style="border-collapse: collapse;">
            <tr style="background-color: #f2f2f2;">
                <th>Artículo</th>
                <th>Precio unitario</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($datosCarrito as $item): ?>
            <tr>
                <td><?= $item['nombre_zapatilla'] ?></td>
                <td><?= $item['precio'] ?> €</td>
                <td><?= $item['cantidad'] ?></td>
                <td><?= $item['subtotal'] ?> €</td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong><?= $totalCarrito ?> €</strong></td>
            </tr>
        </table>
        <br>
        
        <!-- Formulario para acciones del carrito (Comprar o Vaciar) -->
        <form action="carrito.php" method="POST" style="display: inline-block;">
            <input type="hidden" name="accion" value="comprar">
            <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px;">Confirmar Compra</button>
        </form>
        
        <form action="carrito.php" method="POST" style="display: inline-block;">
            <input type="hidden" name="accion" value="vaciar">
            <button type="submit" style="background-color: #f44336; color: white; padding: 10px;">Vaciar Carrito</button>
        </form>
    <?php endif; ?>
</body>
</html>