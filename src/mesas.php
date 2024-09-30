<?php
ob_start();
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    $id_sala = $_GET['id_sala'] ?? null;
    $mesas = $_GET['mesas'] ?? null;

    include_once "includes/header.php";
    include "../conexion.php";

    if ($id_sala && $mesas) {
        $query = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id_sala");
        if ($query) {
            $result = mysqli_num_rows($query);
            
            if ($result > 0) {
                $data = mysqli_fetch_assoc($query);
                if ($data['mesas'] == $mesas) {
                    ?>
                    <div class="card">
                        <div class="card-header text-center">
                            Mesas
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                $item = 1;
                                for ($i = 0; $i < $mesas; $i++) {
                                    $consulta = mysqli_query($conexion, "SELECT * FROM pedidos WHERE id_sala = $id_sala AND num_mesa = $item AND estado = 'PENDIENTE'");
                                    if ($consulta) {
                                        $resultPedido = mysqli_fetch_assoc($consulta);
                                        $bgColor = empty($resultPedido) ? 'success' : 'danger';
                                        ?>
                                        <div class="col-md-3">
                                            <div class="card card-widget widget-user">
                                                <div class="widget-user-header bg-<?php echo $bgColor; ?>">
                                                    <h3 class="widget-user-username">MESA</h3>
                                                    <h5 class="widget-user-desc"><?php echo $item; ?></h5>
                                                </div>
                                                <div class="widget-user-image">
                                                    <img class="img-circle elevation-2" src="../assets/img/mesa.jpg" alt="User Avatar">
                                                </div>
                                                <div class="card-footer">
                                                    <div class="description-block">
                                                        <a class="btn btn-outline-info" href="atender.php?id_sala=<?php echo $id_sala; ?>&mesa=<?php echo $item; ?>">Atender</a>
                                                        <?php if (!empty($resultPedido)) { ?>
                                                            <a class="btn btn-outline-danger" href="cancelar.php?id_sala=<?php echo $id_sala; ?>&mesa=<?php echo $item; ?>" onclick="return confirm('¿Está seguro de que desea cancelar esta orden?');">Cancelar Orden</a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php $item++;
                                    } else {
                                        die("Error en la consulta de pedidos: " . mysqli_error($conexion));
                                    }
                                } ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='alert alert-danger'>No se encontró la sala.</div>";
            }
        } else {
            die("Error en la consulta de salas: " . mysqli_error($conexion));
        }
    } else {
        echo "<div class='alert alert-danger'>Faltan parámetros en la URL.</div>";
    }
    include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
    ob_end_flush(); // Envía el contenido del búfer
    exit;
}
?>
