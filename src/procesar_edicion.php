<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    include_once "../conexion.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_pedido = $_POST['id_pedido'];
        $productos = $_POST['productos'];

        // Actualizar las cantidades de los productos en el pedido
        foreach ($productos as $producto_id => $cantidad) {
            $update_query = "UPDATE pedido_productos SET cantidad = $cantidad WHERE pedido_id = $id_pedido AND producto_id = $producto_id";
            mysqli_query($conexion, $update_query);
        }

        // Redirigir a la página de finalizar para mostrar los cambios
        header("Location: finalizar.php?id_sala={$_GET['id_sala']}&mesa={$_GET['mesa']}");
        exit;
    } else {
        header('Location: permisos.php');
        exit;
    }
} else {
    header('Location: permisos.php');
    exit;
}
?>
