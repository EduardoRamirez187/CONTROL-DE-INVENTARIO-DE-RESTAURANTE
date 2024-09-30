<?php
include "../conexion.php";

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['action']) && $data['action'] === 'complete_order') {
    // Aquí manejas la lógica para completar el pedido
    // Por ejemplo, insertar el pedido en la base de datos
    $observacion = mysqli_real_escape_string($conexion, $data['observacion']);
    
    // Aquí deberías implementar la lógica para guardar el pedido en la base de datos
    // Por ejemplo:
    // $query = mysqli_query($conexion, "INSERT INTO pedidos (observacion) VALUES ('$observacion')");
    
    echo json_encode(['status' => 'success']);
} else {
    $id = $data['id'];
    $type = $data['type'];
    $table = ($type === 'plato') ? 'platos' : 'bebidas';

    // Obtener información del ítem
    $query = mysqli_query($conexion, "SELECT * FROM $table WHERE id = $id");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Item no encontrado'
        ]);
    }
}
?>
