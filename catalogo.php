<?php
session_start();
require_once 'conexion.php';

// HEMOS ELIMINADO LA RESTRICCIÓN DE SESIÓN AQUÍ
// Ahora cualquier visitante puede entrar y ver los productos

// Consultamos los artículos disponibles
$sql = "SELECT * FROM Articulos WHERE stock > 0";
$stmt = $pdo->query($sql);
$articulos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - Sneaker Zone</title>
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
        <h2>Catálogo de zapatillas</h2>
        
        <div class="grid-productos">
            <?php foreach ($articulos as $articulo): ?>
                <?php 
                // Diccionario de imágenes reales basadas en la marca
                $imagenes = [
                    'Nike' => 'https://images.unsplash.com/photo-1605348532760-6753d2c43329?auto=format&fit=crop&w=400&q=80',
                    'Adidas' => 'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=400&q=80',
                    'Reebok' => 'https://images.unsplash.com/photo-1579338908476-3a3a1d71a706?auto=format&fit=crop&w=400&q=80',
                    'Puma' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=400&q=80',
                    'Vans' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=400&q=80',
                    'New Balance' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=400&q=80'
                ];
                
                $marca = $articulo['marca'];
                $imagen_real = isset($imagenes[$marca]) ? $imagenes[$marca] : 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80';
                ?>
                <div class="tarjeta-producto">
                    <img src="<?= $imagen_real ?>" alt="Imagen de <?= htmlspecialchars($articulo['nombre_zapatilla']) ?>" class="img-zapatilla">
                    <div class="info-producto">
                        <span class="badge-rosa"><?= htmlspecialchars($articulo['marca']) ?></span>
                        <h3><?= htmlspecialchars($articulo['nombre_zapatilla']) ?></h3>
                        <p class="talla" style="color: var(--texto-gris); margin: 5px 0;">Talla: <?= htmlspecialchars($articulo['talla']) ?></p>
                        <p class="precio"><?= number_format($articulo['precio'], 2) ?> €</p>
                        
                        <?php if(isset($_SESSION['id_usuario'])): ?>
                            <!-- Si está logueado: Mostramos el seleccionador de cantidad y el botón de añadir al carrito -->
                            <form action="carrito.php" method="POST" style="display: flex; flex-direction: column; gap: 15px; margin-top: auto;">
                                <input type="hidden" name="id_articulo" value="<?= $articulo['id_articulo'] ?>">
                                <input type="hidden" name="accion" value="agregar">
                                
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <label style="font-size: 0.95rem; font-weight: 600; color: var(--texto-oscuro);">Cantidad:</label>
                                    <input type="number" name="cantidad" value="1" min="1" max="<?= $articulo['stock'] ?>" style="width: 70px; padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1; text-align: center; background-color: #f8fafc;">
                                </div>
                                
                                <button type="submit" class="btn-comprar" style="width: 100%;">Agregar al carrito 🛒</button>
                            </form>
                        <?php else: ?>
                            <!-- Si NO está logueado: Mostramos un botón que le invita a registrarse/loguearse -->
                            <a href="login.php" class="btn-comprar" style="text-align: center; display: block; margin-top: auto;">Inicia sesión para comprar</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>