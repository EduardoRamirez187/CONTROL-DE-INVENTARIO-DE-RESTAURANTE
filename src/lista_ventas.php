<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    require_once "../conexion.php";

    // Inicializar la variable de fecha
    $fecha_filtro = isset($_POST['fecha']) ? $_POST['fecha'] : '';

    // Construir la consulta SQL con un filtro opcional
    $query_sql = "SELECT fecha, total, cantidad, productos FROM ventas";
    if (!empty($fecha_filtro)) {
        $fecha_filtro = mysqli_real_escape_string($conexion, $fecha_filtro);
        $query_sql .= " WHERE fecha = '$fecha_filtro'";
    }
    $query = mysqli_query($conexion, $query_sql);

    if (!$query) {
        die("Error en la consulta: " . mysqli_error($conexion));
    }

    // Procesar suma de total de ventas dentro de un rango de fechas
    $total_suma = 0; // Variable para acumular el total de ventas
    if (isset($_POST['fecha_inicio']) && isset($_POST['fecha_fin'])) {
        $fecha_inicio = mysqli_real_escape_string($conexion, $_POST['fecha_inicio']);
        $fecha_fin = mysqli_real_escape_string($conexion, $_POST['fecha_fin']);

        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $query_suma_sql = "SELECT SUM(total) AS total_suma FROM ventas WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            $query_suma = mysqli_query($conexion, $query_suma_sql);

            if ($query_suma) {
                $row_suma = mysqli_fetch_assoc($query_suma);
                $total_suma = $row_suma['total_suma'];
            } else {
                die("Error en la consulta de suma: " . mysqli_error($conexion));
            }
        }
    }

    include_once "includes/header.php";
?>
    <div class="card">
        <div class="card-header">
            Historial de Ventas
        </div>
        <div class="card-body">
            <!-- Formulario para filtrar por fecha -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="fecha">Seleccionar Fecha:</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
            <br>
<!-- Formulario para el reporte de ventas por rango de fechas -->
<form id="formReporte" method="GET" action="reporte_venta.php" target="_blank">
    <div class="form-group">
        <label for="fecha_inicio">Fecha de Inicio:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?php echo isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : ''; ?>">
    </div>
    <div class="form-group">
        <label for="fecha_fin">Fecha de Fin:</label>
        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?php echo isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : ''; ?>">
    </div>

    <!-- Botón para generar el total de ventas -->
    <button type="button" class="btn btn-success" onclick="submitForm('reporte_venta.php')">Generar PDF de ventas</button>

    <!-- Botón para generar el reporte (enviando fechas por GET) -->
    <button type="button" class="btn btn-info" onclick="submitForm('reporte_venta_printer.php')">Generar Ticket</button>
</form>

<script>
function submitForm(action) {
    document.getElementById('formReporte').action = action;
    document.getElementById('formReporte').submit();
}
</script>


            <br>
            <div class="table-responsive">
                <table class="table table-striped" id="tbl">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Cantidad</th>
                            <th>Productos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $row_count = 0;
                        while ($row = mysqli_fetch_assoc($query)) {
                            $row_count++;
                            $fecha = $row['fecha'];
                            $total = $row['total'];
                            $cantidad = $row['cantidad'];
                            $productos_str = $row['productos'];
                        ?>
                            <tr>
                                <td><?php echo $fecha; ?></td>
                                <td><?php echo number_format($total, 2); ?></td>
                                <td><?php echo number_format($cantidad, 2); ?></td>
                                <td><?php echo htmlspecialchars($productos_str); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($row_count == 0) { ?>
                            <tr>
                                <td colspan="4">Ningún dato disponible en esta tabla</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Mostrar total de ventas sumado -->
            <?php if (isset($fecha_inicio) && isset($fecha_fin) && $total_suma > 0) { ?>
                <div class="alert alert-info">
                    <strong>Total de ventas desde <?php echo $fecha_inicio; ?> hasta <?php echo $fecha_fin; ?>:</strong> $<?php echo number_format($total_suma, 2); ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php
    include_once "includes/footer.php";
} else {
    echo "<div class='alert alert-danger'>No tienes permisos para acceder a esta página.</div>";
}
?>
