<?php
    $mysqli = new mysqli("localhost", "root", "", "gestor_restaurante");
    if ($mysqli->connect_error){
        die("Error de conexion" . $mysqli->connect_error);
    }

    $mesa = isset($_POST['mesa']) ? intval($_POST['mesa']) : 0;
    $cliente = isset($_POST['cliente']) ? trim($_POST['cliente']): '';
    $platillos = isset($_POST['platillos']) ? $_POST['platillos'] : [];

    if ($mesa <= 0 || empty($cliente) || empty($platillos)) {
    die("Error: Validación fallida. Verifica la mesa, el cliente y que haya al menos 1 platillo.");
    }
    $sql_pedido = "INSERT INTO pedidos (mesa, cliente, total) VALUES (?,?,0.00)";
    $stmt_pedido = $mysqli->prepare($sql_pedido);

    $stmt_pedido->bind_param("is", $mesa, $cliente);
    $stmt_pedido->execute();

    $pedido_id = $mysqli->insert_id; 
    $stmt_pedido->close();

    $total_acumulado = 0;

    $sql_detalle = "INSERT INTO detalle_pedido (pedido_id, nombre_platillo, precio, cantidad) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $mysqli->prepare($sql_detalle);

    foreach ($platillos as $platillo) {
    $nombre = $platillo['nombre'];
    $precio = floatval($platillo['precio']);
    $cantidad = intval($platillo['cantidad']);

    $total_acumulado += ($precio * $cantidad);

    $stmt_detalle->bind_param("isdi", $pedido_id, $nombre, $precio, $cantidad);
    $stmt_detalle->execute();
    }
    $stmt_detalle->close();

    $sql_update = "UPDATE pedidos SET total = ? WHERE id = ?";
    $stmt_update = $mysqli->prepare($sql_update);

    $stmt_update->bind_param("di", $total_acumulado, $pedido_id);
    $stmt_update->execute();
    $stmt_update->close();

    echo "Pedido registrado con exito. ID del pedido:" . $pedido_id . "- Total: $" . $total_acumulado;

    $mysqli->close();
?>