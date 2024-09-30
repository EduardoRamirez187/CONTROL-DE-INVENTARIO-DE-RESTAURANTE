<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    require_once "../conexion.php";
}

// Las declaraciones 'use' deben estar fuera de cualquier estructura condicional
require __DIR__ . '/../autoload.php';
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    // Obtener fechas del rango
    $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
    $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

    // Validar fechas
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        die("Fecha de inicio o fin no proporcionadas.");
    }

    // Preparar la consulta SQL para el rango de fechas
    $fecha_inicio = mysqli_real_escape_string($conexion, $fecha_inicio);
    $fecha_fin = mysqli_real_escape_string($conexion, $fecha_fin);
    $query_sql = "SELECT * FROM ventas WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' ORDER BY fecha ASC";
    $query = mysqli_query($conexion, $query_sql);

    if (!$query) {
        die("Error en la consulta: " . mysqli_error($conexion));
    }

    // Conectar con la impresora
    $nombre_impresora = "POS-580"; // Cambia esto por el nombre de tu impresora
    $connector = new WindowsPrintConnector($nombre_impresora);

    try {
        $printer = new Printer($connector);

        // Título principal
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2, 1);
        $printer->text("Reporte de Ventas\n");
        $printer->text("Del " . date('d-M-Y', strtotime($fecha_inicio)) . " al " . date('d-M-Y', strtotime($fecha_fin)) . "\n");
        $printer->feed();

        // Variables para acumular los datos de los productos y totales
        $ventas_diarias = [];
        $total_general = 0;

        // Procesar los resultados de la consulta
        while ($row = mysqli_fetch_assoc($query)) {
            $fecha = date('d-M-Y', strtotime($row['fecha']));
            $productos = $row['productos'];

            // Regex para dividir la cadena de productos
            $productos_list = preg_split('/(?<=\))\s*,\s*/', $productos);

            foreach ($productos_list as $producto) {
                $producto = trim($producto);
                if (preg_match('/^(.*?)\s*\(\s*(\d+)\s*,\s*([\d.]+)\s*\)$/', $producto, $matches)) {
                    $nombre_producto = trim($matches[1]);
                    $cantidad = (int) $matches[2];
                    $precio = (float) $matches[3];
                    $total_producto = $cantidad * $precio;

                    // Acumular datos por fecha
                    if (!isset($ventas_diarias[$fecha])) {
                        $ventas_diarias[$fecha] = [];
                    }

                    if (!isset($ventas_diarias[$fecha][$nombre_producto])) {
                        $ventas_diarias[$fecha][$nombre_producto] = ['cantidad' => 0, 'precio' => 0, 'total' => 0];
                    }

                    // Acumular cantidad y total por producto
                    $ventas_diarias[$fecha][$nombre_producto]['cantidad'] += $cantidad;
                    $ventas_diarias[$fecha][$nombre_producto]['precio'] = $precio;
                    $ventas_diarias[$fecha][$nombre_producto]['total'] += $total_producto;

                    // Acumulación total general
                    $total_general += $total_producto;
                }
            }
        }

        // Imprimir ventas diarias
        foreach ($ventas_diarias as $fecha => $productos) {
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(1, 1);
            $printer->text("Ventas del " . $fecha . "\n");
            $printer->text("-------------------------------\n");

            // Encabezado de la tabla
            $printer->text("Producto        Cant.   Precio   Total\n");
            $total_diario = 0;

            // Imprimir productos de la fecha actual
            foreach ($productos as $producto => $datos) {
                $line = sprintf("%-14s %4d %8.2f %8.2f\n", $producto, $datos['cantidad'], $datos['precio'], $datos['total']);
                $printer->text($line);

                // Acumulación total diario
                $total_diario += $datos['total'];
            }

            // Imprimir total del día
            $printer->text("-------------------------------\n");
            $printer->text(sprintf("Total del Dia:            %8.2f\n", $total_diario));
            $printer->feed();
        }

        // Imprimir total general
        $printer->feed();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2, 1);
        $printer->text("Total General\n");
        $printer->text(sprintf("Total: %.2f\n", $total_general));

        // Finalización e impresión
        $printer->feed(4);
        $printer->cut();
        $printer->close();

        mysqli_close($conexion);

        // Redirigir o mensaje de éxito
        header("Location: index.php");
        exit();

    } catch (Exception $e) {
        echo "No se pudo conectar con la impresora: " . $e->getMessage();
    }
} else {
    echo "<div class='alert alert-danger'>No tienes permisos para acceder a esta página.</div>";
}
?>
