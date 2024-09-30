<?php
ob_start();
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    $id_sala = $_GET['id_sala'] ?? null;
    $num_mesa = $_GET['mesa'] ?? null;

    include "../conexion.php";

    if ($id_sala && $num_mesa) {
        // Obtener el pedido_id antes de eliminarlo
        $query_get_pedido = "SELECT id FROM pedidos WHERE id_sala = $id_sala AND num_mesa = $num_mesa AND estado = 'PENDIENTE'";
        $result_pedido = mysqli_query($conexion, $query_get_pedido);

        // Verificar si la consulta fue exitosa
        if (!$result_pedido) {
            die("Error en la consulta de selección: " . mysqli_error($conexion));
        }

        if (mysqli_num_rows($result_pedido) > 0) {
            $row_pedido = mysqli_fetch_assoc($result_pedido);
            $id_pedido = $row_pedido['id']; // Aquí ya está definido como id_pedido

            // Eliminar el detalle del pedido usando id_pedido
            $query_delete_detalle = "DELETE FROM detalle_pedidos WHERE id_pedido = $id_pedido";
            $result_delete_detalle = mysqli_query($conexion, $query_delete_detalle);

            if ($result_delete_detalle) {
                // Eliminar el pedido de la base de datos
                $query_delete_pedido = "DELETE FROM pedidos WHERE id_sala = $id_sala AND num_mesa = $num_mesa";
                $result_delete_pedido = mysqli_query($conexion, $query_delete_pedido);

                if ($result_delete_pedido) {
                    header("Location:index.php");
                    ob_end_flush(); // Envía el contenido del búfer
                    exit;
                } else {
                    die("Error al eliminar el pedido: " . mysqli_error($conexion));
                }
            } else {
                die("Error al eliminar el detalle del pedido: " . mysqli_error($conexion));
            }
        } else {
            echo "<div class='alert alert-danger'>No se encontró un pedido pendiente para esta sala y mesa.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Faltan parámetros en la URL.</div>";
    }
} else {
    header('Location: permisos.php');
    ob_end_flush(); // Envía el contenido del búfer
    exit;
}
?>
