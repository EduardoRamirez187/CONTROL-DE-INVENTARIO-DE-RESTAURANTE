<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    include_once "../conexion.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_pedido = $_POST['id_pedido'];
        $producto_id = $_POST['producto_id'];
        $cantidad = $_POST['cantidad'];

        // Añadir el producto al pedido
        $insert_query = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad) VALUES ($id_pedido, $producto_id, $cantidad)";
        mysqli_query($conexion, $insert_query);

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
