<?php
session_start();
include_once "../conexion.php";

// Verificar si se han enviado los datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $producto_id = $_POST['producto_id'];
    $tipo = $_POST['tipo'];
    $id_sala = $_POST['id_sala'];
    $mesa = $_POST['mesa'];

    // Consulta para obtener el pedido pendiente
    $query = mysqli_query($conexion, "SELECT id FROM pedidos WHERE id_sala = $id_sala AND num_mesa = $mesa AND estado = 'PENDIENTE'");
    $result = mysqli_fetch_assoc($query);

    if (!empty($result)) {
        $id_pedido = $result['id'];

        // Eliminar el producto del pedido
        $delete_product_query = "DELETE FROM pedido_productos WHERE pedido_id = $id_pedido AND producto_id = $producto_id";
        $result_product = mysqli_query($conexion, $delete_product_query);

        if ($result_product) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => mysqli_error($conexion)]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "No se encontró el pedido."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido."]);
}
?>
