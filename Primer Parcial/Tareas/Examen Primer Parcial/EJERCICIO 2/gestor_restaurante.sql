-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS gestor_restaurante;
USE gestor_restaurante;

-- Tabla principal para el registro del pedido
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa INT NOT NULL,
    cliente VARCHAR(100) NOT NULL,
    total DECIMAL(10, 2) DEFAULT 0.00,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla secundaria para los platillos de cada pedido
CREATE TABLE detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    nombre_platillo VARCHAR(100) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad INT NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
);

-- ==========================================
-- INSERTAR DATOS DE PRUEBA
-- ==========================================

-- Insertamos dos pedidos iniciales
-- Nota: El total ya viene calculado para que los alumnos vean el resultado esperado
INSERT INTO pedidos (id, mesa, cliente, total) VALUES
(1, 5, 'Juan Pérez', 90.00),
(2, 2, 'Ana Gómez', 180.00);

-- Insertamos los platillos correspondientes al pedido 1 (Juan Pérez)
-- 2 Tacos al Pastor ($25 c/u = $50) + 2 Refrescos ($20 c/u = $40) -> Total: $90
INSERT INTO detalle_pedido (pedido_id, nombre_platillo, precio, cantidad) VALUES
(1, 'Tacos al Pastor', 25.00, 2),
(1, 'Refresco', 20.00, 2);

-- Insertamos los platillos correspondientes al pedido 2 (Ana Gómez)
-- 1 Hamburguesa ($120) + 1 Papas a la Francesa ($60) -> Total: $180
INSERT INTO detalle_pedido (pedido_id, nombre_platillo, precio, cantidad) VALUES
(2, 'Hamburguesa Especial', 120.00, 1),
(2, 'Papas a la Francesa', 60.00, 1);