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
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<!-- Cabecera unificada -->
    <header class="tienda-header">
        <div class="logo">SNEAKER <span>SHOP</span></div>
        <nav class="navegacion">
            <a href="index.php" class="btn-nav">Inicio</a>
            <a href="catalogo.php" class="btn-nav">Catálogo</a>
            
            <?php if(isset($_SESSION['id_usuario'])): ?>
                <!-- Si está logueado mostramos su nombre y el botón de salir -->
                <span style="color: var(--texto-gris); font-weight: 600; margin-right: 10px;">
                    Hola, <?= htmlspecialchars($_SESSION['nombre']) ?>
                </span>
                <a href="logout.php" class="btn-nav salir">Cerrar sesión</a>
            <?php else: ?>
                <!-- Si NO está logueado mostramos los enlaces de acceso -->
                <a href="login.php" class="btn-nav">Iniciar sesión</a>
                <a href="registro.php" class="btn-nav">Crear cuenta</a>
            <?php endif; ?>
            
            <!-- El botón azul del carrito siempre a la derecha -->
            <a href="carrito.php" class="btn-nav btn-carrito">🛒 Carrito</a>
        </nav>
    </header>

    <main class="contenedor-tienda">
        <h2>Tu carrito de compra</h2>
        
        <!-- Mensaje de éxito/error con diseño -->
        <?php if (!empty($mensaje)): ?>
            <div style="background-color: #dcfce3; color: #166534; padding: 1rem; border-radius: 6px; margin-bottom: 2rem; text-align: center; font-weight: bold; border: 1px solid #bbf7d0;">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($datosCarrito)): ?>
            <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p style="font-size: 1.2rem; color: #6b7280; margin-bottom: 2rem;">Tu carrito está vacío actualmente.</p>
                <a href="catalogo.php" class="btn-comprar" style="text-decoration: none; padding: 1rem 2rem; display: inline-block; width: auto;">Volver al catálogo</a>
            </div>
        <?php else: ?>
            <div class="contenedor" style="margin: 0 auto; max-width: 900px;">
                <table style="width: 100%;">
                    <tr>
                        <th>Artículo</th>
                        <th>Precio unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                    <?php foreach ($datosCarrito as $item): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($item['nombre_zapatilla']) ?></strong></td>
                        <td><?= number_format($item['precio'], 2) ?> €</td>
                        <td><?= htmlspecialchars($item['cantidad']) ?></td>
                        <td><strong><?= number_format($item['subtotal'], 2) ?> €</strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align: right; font-size: 1.2rem;"><strong>Total a pagar:</strong></td>
                        <td style="font-size: 1.2rem; color: var(--color-primario);"><strong><?= number_format($totalCarrito, 2) ?> €</strong></td>
                    </tr>
                </table>
                
                <!-- Botones de acción alineados a la derecha -->
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <form action="carrito.php" method="POST">
                        <input type="hidden" name="accion" value="vaciar">
                        <button type="submit" style="background-color: #ef4444; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s;">Vaciar Carrito</button>
                    </form>
                    
                    <form action="carrito.php" method="POST">
                        <input type="hidden" name="accion" value="comprar">
                        <button type="submit" class="btn-comprar" style="width: auto; padding: 0.75rem 2rem; background-color: #10b981;">Confirmar Compra ✓</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>