<?php
require __DIR__ . '/../autoload.php';

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

// Obtener parámetros del GET
$id_venta = isset($_GET['id_venta']) ? (int)$_GET['id_venta'] : 0;

// Validar ID de la venta
if ($id_venta <= 0) {
    die('ID de venta inválido.');
}

// Conectar a la base de datos
include "../conexion.php";

// Consultar información de la venta
$query_venta = mysqli_prepare($conexion, "SELECT * FROM ventas WHERE id = ?");
mysqli_stmt_bind_param($query_venta, 'i', $id_venta);
mysqli_stmt_execute($query_venta);
$result_venta = mysqli_stmt_get_result($query_venta);
$venta = mysqli_fetch_assoc($result_venta);

if (!$venta) {
    die('Venta no encontrada.');
}

// Consultar detalles de la venta
$query_items = mysqli_prepare($conexion, "SELECT producto, cantidad, precio FROM detalle_ventas WHERE id_venta = ?");
mysqli_stmt_bind_param($query_items, 'i', $id_venta);
mysqli_stmt_execute($query_items);
$result_items = mysqli_stmt_get_result($query_items);

// Conectar con la impresora
$nombre_impresora = "POS-580"; // Cambia esto al nombre de tu impresora
$connector = new WindowsPrintConnector($nombre_impresora);

try {
    $printer = new Printer($connector);

    // Imprimir el nombre del restaurante
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(3, 2);
    $printer->text("LA ESQUINA DE NY\n");

    // Imprimir fecha y mesa
    $printer->setTextSize(1, 1);
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Fecha: " . $venta['fecha'] . "\n");
    $printer->text("Mesa: " . $venta['num_mesa'] . "\n");

    // Espacio
    $printer->feed();

    // Encabezado de detalles de la venta
    $printer->setTextSize(1, 2);
    $printer->text("Producto         Cant.  Precio\n");

    // Agregar los productos al ticket
    $total_calculado = 0; // Inicializar el total del ticket
    while ($item = mysqli_fetch_assoc($result_items)) {
        $subtotal = $item['cantidad'] * $item['precio'];
        $total_calculado += $subtotal; // Sumar al total

        $line = sprintf("%-16s %2d %6.2f\n", $item['producto'], $item['cantidad'], $item['precio']);
        $printer->text($line);
    }

    // Línea de separación
    $printer->text("------------------------\n");

    // Total
    $printer->setTextSize(1, 2);
    $printer->text("\n"); // Espacio antes del total
    $printer->text(sprintf("Total:      %6.2f\n", $total_calculado));

    // Pago (verificar si el campo existe)
    $pago = isset($venta['pago']) ? $venta['pago'] : 0;
    $printer->text("\n"); // Espacio entre total y pago

    // Reducir el espacio antes de "Gracias por su compra"
    $printer->feed();

    // Gracias por su compra
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(1, 2);
    $printer->text("Gracias por su compra\n");
    $printer->text("\n");
    $printer->text("\n");
    $printer->text("\n");
    $printer->text("\n");

    // Cortar el papel
    $printer->cut();

    // Cerrar la conexión con la impresora
    $printer->close();
	header("Location: index.php");
exit();
} catch (Exception $e) {
    echo "No se pudo conectar con la impresora: " . $e->getMessage();
}

mysqli_close($conexion);
?>
