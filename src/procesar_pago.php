<?php
session_start();
include "../conexion.php"; // Ajusta la ruta según la ubicación real de conexion.php

if (isset($_POST['pagar'])) {
    // Sanitizar y validar entradas
    $total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
    $id_pedido = isset($_POST['id_pedido']) ? intval($_POST['id_pedido']) : 0;
    $id_sala = isset($_POST['id_sala']) ? intval($_POST['id_sala']) : 0;
    $mesa = isset($_POST['mesa']) ? intval($_POST['mesa']) : 0;

    // Verificar que las variables están definidas y son válidas
    if ($total <= 0) {
        die("Datos de pago no válidos.");
    }

    // Verificar que el pedido exista y esté en estado 'PENDIENTE'
    $query_verificar_pedido = "SELECT id, id_sala, num_mesa FROM pedidos WHERE id = $id_pedido AND estado = 'PENDIENTE' AND id_sala = $id_sala AND num_mesa = $mesa";
    $result_verificar = mysqli_query($conexion, $query_verificar_pedido);

    if (!$result_verificar) {
        die("Error en la consulta de verificación del pedido: " . mysqli_error($conexion));
    }

    if (mysqli_num_rows($result_verificar) > 0) {
        $pedido = mysqli_fetch_assoc($result_verificar);
        $id_sala = $pedido['id_sala'];
        $num_mesa = $pedido['num_mesa'];

        // Obtener detalles del pedido, incluyendo el precio de cada producto
        $query_detalle_pedido = "SELECT d.cantidad, d.nombre, d.precio
        FROM detalle_pedidos d
        WHERE d.id_pedido = $id_pedido AND d.id_sala = $id_sala AND d.mesa = $num_mesa";
        
        $result_detalle = mysqli_query($conexion, $query_detalle_pedido);
        if (!$result_detalle) {
            die("Error en la consulta de detalles del pedido: " . mysqli_error($conexion));
        }

        $productos = [];
        $total_cantidad = 0;

        while ($row_detalle = mysqli_fetch_assoc($result_detalle)) {
            $nombre_producto = $row_detalle['nombre'];
            $cantidad_producto = $row_detalle['cantidad'];
            $precio_producto = $row_detalle['precio'];
            $productos[] = ['nombre' => $nombre_producto, 'cantidad' => $cantidad_producto, 'precio' => $precio_producto];
            $total_cantidad += $cantidad_producto;

            // Actualizar el stock en bebidas_stock
            $query_actualizar_stock = "UPDATE bebidas_stock SET stock = stock - $cantidad_producto WHERE nombre = '$nombre_producto'";
            $result_actualizar_stock = mysqli_query($conexion, $query_actualizar_stock);

            if (!$result_actualizar_stock) {
                die("Error al actualizar el stock: " . mysqli_error($conexion));
            }
        }

        // Crear una cadena con el resumen de los productos para insertar en la tabla `ventas`
        $productos_str = implode(", ", array_map(function($p) {
            return "{$p['nombre']} ({$p['cantidad']}, {$p['precio']})";
        }, $productos));

        // Insertar el pago en la tabla `ventas`
        $query_insertar_venta = "INSERT INTO ventas (id_pedido, id_sala, num_mesa, fecha, total, cantidad, productos) 
        VALUES ($id_pedido, $id_sala, $num_mesa, NOW(), $total, $total_cantidad, '$productos_str')";
        
        $result_insertar_venta = mysqli_query($conexion, $query_insertar_venta);

        if (!$result_insertar_venta) {
            die("Error al insertar el pago en la tabla ventas: " . mysqli_error($conexion));
        }

        // Obtener el ID de la venta recién insertada
        $id_venta = mysqli_insert_id($conexion);

        // Insertar cada producto en la tabla `detalle_ventas`
        foreach ($productos as $producto) {
            $nombre_producto = $producto['nombre'];
            $cantidad_producto = $producto['cantidad'];
            $precio_producto = $producto['precio'];
            
            $query_insertar_detalle = "INSERT INTO detalle_ventas (id_venta, producto, cantidad, precio) 
            VALUES ($id_venta, '$nombre_producto', $cantidad_producto, $precio_producto)";
            
            mysqli_query($conexion, $query_insertar_detalle);
        }

        // Eliminar detalles del pedido
        $query_eliminar_detalle = "DELETE FROM detalle_pedidos WHERE id_pedido = $id_pedido AND id_sala = $id_sala AND mesa = $num_mesa";
        $result_eliminar_detalle = mysqli_query($conexion, $query_eliminar_detalle);

        if (!$result_eliminar_detalle) {
            die("Error al eliminar los detalles del pedido: " . mysqli_error($conexion));
        }

        // Eliminar el pedido
        $query_eliminar_pedido = "DELETE FROM pedidos WHERE id = $id_pedido AND id_sala = $id_sala AND num_mesa = $num_mesa";
        $result_eliminar_pedido = mysqli_query($conexion, $query_eliminar_pedido);

        if (!$result_eliminar_pedido) {
            die("Error al eliminar el pedido: " . mysqli_error($conexion));
        }

        // Redirigir a generar_ticket.php con el ID de la venta, id_sala y num_mesa
        header("Location: generar_ticket.php?id_venta=" . $id_venta . "&id_sala=" . $id_sala . "&num_mesa=" . $num_mesa);
        exit;
    } else {
        echo "<div class='alert alert-danger'>No se encontró un pedido pendiente con el ID proporcionado.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>No se ha realizado ninguna acción de pago.</div>";
}
?>
