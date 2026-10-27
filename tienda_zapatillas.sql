-- 1. Tabla Usuarios
CREATE TABLE Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Recomendado guardar contraseñas hasheadas (con password_hash en PHP)
    email VARCHAR(100) NOT NULL UNIQUE,
    rol ENUM('admin', 'cliente') DEFAULT 'cliente',
    direccion_envio VARCHAR(255)
) ENGINE=InnoDB;

-- 2. Tabla Artículos (catálogo de zapatillas)
CREATE TABLE Articulos (
    id_articulo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_zapatilla VARCHAR(100) NOT NULL,
    marca VARCHAR(50) NOT NULL,
    talla DECIMAL(4,1) NOT NULL, -- Permite tallas con medios, ej: 42.5
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    descripcion TEXT
) ENGINE=InnoDB;

-- 3. Tabla Pedidos (cabecera del pedido / carrito confirmado)
CREATE TABLE Pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado_pedido ENUM('pendiente', 'completado', 'cancelado') DEFAULT 'pendiente',
    precio_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tabla Lineas_Pedido (detalle del carrito)
CREATE TABLE Lineas_Pedido (
    id_linea INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_articulo INT NOT NULL,
    cantidad_comprada INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL, -- Guarda el precio exacto en el momento de la compra
    FOREIGN KEY (id_pedido) REFERENCES Pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_articulo) REFERENCES Articulos(id_articulo) ON DELETE RESTRICT
) ENGINE=InnoDB;