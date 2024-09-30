<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Impresión de Ticket</title>
    <script>
        window.onload = function() {
            // Esperar un momento para que el PDF se cargue completamente
            setTimeout(function() {
                // Abrir el PDF en una nueva ventana
                var pdfWindow = window.open('', '_blank');
                pdfWindow.location.href = 'generar_ticket.php'; // Cambiar a la ruta del archivo PHP que genera el PDF

                // Esperar que el PDF se cargue y luego imprimir
                pdfWindow.onload = function() {
                    pdfWindow.print();
                    pdfWindow.onafterprint = function() {
                        pdfWindow.close(); // Cierra la ventana después de imprimir
                    };
                };
            }, 1000); // Ajustar el tiempo según sea necesario
        };
    </script>
</head>
<body>
    <h1>Por favor, espere mientras se imprime el ticket...</h1>
</body>
</html>
