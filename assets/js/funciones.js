// Se ejecuta cuando todo el contenido de la página ha cargado
document.addEventListener("DOMContentLoaded", function () {

    // Verifica si el elemento con id 'detalle_pedido' existe en la página
    // Si existe, llama a la función listar() para cargar el contenido
    if ($('#detalle_pedido').length > 0) {
        listar();
    }
    
    // Inicializa la tabla con id 'tbl' utilizando DataTables con configuración en español
    $('#tbl').DataTable({
        language: {
            "url": "//cdn.datatables.net/plug-ins/1.10.11/i18n/Spanish.json"
        },
        "order": [
            [0, "desc"] // Ordena la tabla por la primera columna en orden descendente
        ]
    });

    // Captura el evento de envío del formulario con clase 'confirmar'
    // Previene el comportamiento por defecto del formulario y muestra una alerta de confirmación
    $(".confirmar").submit(function (e) {
        e.preventDefault(); // Previene el envío del formulario
        Swal.fire({
            title: '¿Está seguro de eliminar?', // Título del mensaje
            icon: 'warning', // Icono de advertencia
            showCancelButton: true, // Muestra el botón de cancelar
            confirmButtonColor: '#3085d6', // Color del botón de confirmar
            cancelButtonColor: '#d33', // Color del botón de cancelar
            confirmButtonText: '¡Sí, eliminar!' // Texto del botón de confirmar
        }).then((result) => {
            if (result.isConfirmed) { // Si el usuario confirma la acción
                this.submit(); // Se envía el formulario
            }
        })
    });

    // Captura el evento click en elementos con clase 'addDetalle'
    $('.addDetalle').click(function () {
        let id_producto = $(this).data('id'); // Obtiene el ID del producto
        registrarDetalle(id_producto); // Llama a la función registrarDetalle con el ID del producto
    });

    // Captura el evento click en el botón con id 'realizar_pedido'
    $('#realizar_pedido').click(function (e) {
        e.preventDefault(); // Previene el comportamiento por defecto del botón
        var action = 'procesarPedido'; // Acción a enviar
        var id_sala = $('#id_sala').val(); // Obtiene el valor de la sala
        var mesa = $('#mesa').val(); // Obtiene el valor de la mesa
        var observacion = $('#observacion').val(); // Obtiene las observaciones

        // Realiza una petición AJAX a 'ajax.php'
        $.ajax({
            url: 'ajax.php', // URL de la petición
            async: true, // Realiza la petición de forma asíncrona
            data: {
                procesarPedido: action,
                id_sala: id_sala,
                mesa: mesa,
                observacion: observacion
            },
            success: function (response) {
                const res = JSON.parse(response); // Parsea la respuesta JSON
                if (response != 'error') { // Si no hay error en la respuesta
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success', // Muestra un mensaje de éxito
                        title: 'Pedido solicitado',
                        showConfirmButton: false,
                        timer: 2000 // Temporizador de 2 segundos
                    })
                    setTimeout(() => {
                        window.location = 'mesas.php?id_sala=' + id_sala + '&mesas=' + res.mensaje; // Redirige a la página de mesas
                    }, 1500); // Espera 1.5 segundos antes de redirigir
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error', // Muestra un mensaje de error
                        title: 'Error al generar',
                        showConfirmButton: false,
                        timer: 2000
                    })
                }
            },
            error: function (error) {
                alert(error); // Muestra una alerta en caso de error
            }
        });
    });

    // Captura el evento click en elementos con clase 'finalizarPedido'
    $('.finalizarPedido').click(function () {
        var action = 'finalizarPedido'; // Acción a enviar
        var id_sala = $('#id_sala').val(); // Obtiene el valor de la sala
        var mesa = $('#mesa').val(); // Obtiene el valor de la mesa

        // Realiza una petición AJAX a 'ajax.php'
        $.ajax({
            url: 'ajax.php',
            async: true,
            data: {
                finalizarPedido: action,
                id_sala: id_sala,
                mesa: mesa
            },
            success: function (response) {
                const res = JSON.parse(response); // Parsea la respuesta JSON
                if (response != 'error') { // Si no hay error en la respuesta
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success', // Muestra un mensaje de éxito
                        title: 'Pedido finalizado',
                        showConfirmButton: false,
                        timer: 2000 // Temporizador de 2 segundos
                    })
                    setTimeout(() => {
                        window.location = 'mesas.php?id_sala=' + id_sala + '&mesas=' + res.mensaje; // Redirige a la página de mesas
                    }, 1500); // Espera 1.5 segundos antes de redirigir
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error', // Muestra un mensaje de error
                        title: 'Error al finalizar',
                        showConfirmButton: false,
                        timer: 2000
                    })
                }
            },
            error: function (error) {
                alert(error); // Muestra una alerta en caso de error
            }
        });

    })
})

// Función para listar los detalles del pedido
function listar() {
    let html = '';
    let detalle = 'detalle';
    
    // Realiza una petición AJAX a 'ajax.php' para obtener los detalles del pedido
    $.ajax({
        url: "ajax.php",
        dataType: "json", // Espera una respuesta en formato JSON
        data: {
            detalle: detalle
        },
        success: function (response) {
            response.forEach(row => { // Itera sobre cada fila de la respuesta
                html += `<div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="col-12">
                            <img src="${ row.imagen }" class="product-image" alt="Product Image"> <!-- Imagen del producto -->
                        </div>
                        <p class="my-3">${row.nombre}</p> <!-- Nombre del producto -->
                        <h2 class="mb-0">${row.precio}</h2> <!-- Precio del producto -->
                        <div class="mt-1">
                            <input type="number" class="form-control addCantidad mb-2" data-id="${row.id}" value="${row.cantidad}"> <!-- Campo para cambiar la cantidad -->
                            <button class="btn btn-danger eliminarPlato" type="button" data-id="${row.id}">Eliminar</button> <!-- Botón para eliminar el plato -->
                        </div>
                    </div>
                </div>
            </div>`;
            });
            document.querySelector("#detalle_pedido").innerHTML = html; // Inserta el HTML generado en el elemento con id 'detalle_pedido'

            // Asigna la función eliminarPlato al evento click en el botón de eliminar
            $('.eliminarPlato').click(function () {
                let id = $(this).data('id'); // Obtiene el ID del plato
                eliminarPlato(id); // Llama a la función eliminarPlato con el ID
            });

            // Asigna la función cantidadPlato al evento change en el campo de cantidad
            $('.addCantidad').change(function (e) {
                let id = $(this).data('id'); // Obtiene el ID del plato
                cantidadPlato(e.target.value, id); // Llama a la función cantidadPlato con la nueva cantidad y el ID
            });
        }
    });
}

// Función para registrar un detalle en el pedido
function registrarDetalle(id_pro) {
    let action = 'regDetalle';
    
    // Realiza una petición AJAX a 'ajax.php' para registrar un nuevo detalle
    $.ajax({
        url: "ajax.php",
        type: 'POST', // Tipo de petición POST
        dataType: "json", // Espera una respuesta en formato JSON
        data: {
            id: id_pro,
            regDetalle: action
        },
        success: function (response) {
            if (response == 'registrado') { // Si el detalle fue registrado con éxito
                listar(); // Llama a la función listar() para actualizar la lista de detalles
            }
            Swal.fire({
                position: 'top-end',
                icon: 'success', // Muestra un mensaje de éxito
                title: 'Producto agregado',
                showConfirmButton: false,
                timer: 2000 // Temporizador de 2 segundos
            })
        }
    });
}
