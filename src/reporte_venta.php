<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    require_once "../conexion.php";
    require('../fpdf186/fpdf.php'); // Asegúrate de tener la biblioteca FPDF en la ruta correcta

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

    // Crear instancia de FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);

    // Título principal con color
    $pdf->SetTextColor(0, 0, 128); // Color azul oscuro
    $pdf->Cell(0, 10, 'Reporte de Ventas del ' . date('d-M-Y', strtotime($fecha_inicio)) . ' al ' . date('d-M-Y', strtotime($fecha_fin)), 0, 1, 'C');
    
    // Variables para acumular los datos de los productos y totales
    $ventas_diarias = [];
    $total_general = 0;

    // Procesar los resultados de la consulta
    while ($row = mysqli_fetch_assoc($query)) {
        $fecha = date('d-M-Y', strtotime($row['fecha']));
        $productos = $row['productos'];

        // Regex para dividir la cadena de productos correctamente por comas entre productos
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

    // Imprimir ventas diarias en formato "FECHA - TABLA DE VENTAS"
    foreach ($ventas_diarias as $fecha => $productos) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(255, 69, 0); // Color naranja
        $pdf->Cell(0, 10, 'Ventas del ' . $fecha, 0, 1, 'C');
        $pdf->Ln(2);

        // Encabezado de la tabla
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 8, 'Producto', 1);
        $pdf->Cell(30, 8, 'Cantidad', 1);
        $pdf->Cell(30, 8, 'Precio', 1);
        $pdf->Cell(30, 8, 'Total', 1);
        $pdf->Ln();

        // Imprimir productos de la fecha actual
        $total_diario = 0;
        foreach ($productos as $producto => $datos) {
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(80, 8, $producto, 1);
            $pdf->Cell(30, 8, $datos['cantidad'], 1, 0, 'R');
            $pdf->Cell(30, 8, number_format($datos['precio'], 2), 1, 0, 'R');
            $pdf->Cell(30, 8, number_format($datos['total'], 2), 1, 0, 'R');
            $pdf->Ln();
            
            // Acumulación total diario
            $total_diario += $datos['total'];
        }

        // Imprimir total del día
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 8, 'Total del Dia', 1);
        $pdf->Cell(30, 8, '', 1);
        $pdf->Cell(30, 8, '', 1);
        $pdf->Cell(30, 8, number_format($total_diario, 2), 1, 0, 'R');
        $pdf->Ln(5);
		
    }

    // Imprimir total general
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 128, 0); // Color verde
    $pdf->Cell(0, 10, 'Total General', 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(140, 8, 'Total General de Ventas', 1);
    $pdf->Cell(30, 8, number_format($total_general, 2), 1, 0, 'R');

    // Cerrar y mostrar el PDF
    $pdf->Output();
} else {
    echo "<div class='alert alert-danger'>No tienes permisos para acceder a esta página.</div>";
}
?>
