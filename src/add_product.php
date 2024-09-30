$.ajax({
    url: 'add_product.php',
    type: 'POST',  // Asegúrate de que sea POST
    data: formData,
    dataType: 'json',
    success: function (response) {
        if (response.success) {
            alert('Producto añadido al pedido');
            location.reload(); // Recargar la página para actualizar la lista de productos
        } else {
            alert('Error al añadir producto: ' + response.error);
        }
    },
    error: function (xhr, status, error) {
        alert('Error en la petición AJAX: ' + error);
    }
});
