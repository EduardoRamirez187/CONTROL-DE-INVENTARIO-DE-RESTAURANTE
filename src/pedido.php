<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    include_once "includes/header.php";
?>
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit"></i>
                COMIDA Y BEBIDA
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-7 col-sm-9">
                    <div class="tab-content" id="vert-tabs-right-tabContent">
                        <div class="tab-pane fade show active" id="vert-tabs-right-home" role="tabpanel" aria-labelledby="vert-tabs-right-home-tab">
                            <input type="hidden" id="id_sala" value="<?php echo $_GET['id_sala'] ?>">
                            <input type="hidden" id="mesa" value="<?php echo $_GET['mesa'] ?>">

                            <!-- Sección de Comida -->
                            <h4>COMIDA</h4>
                            <div class="row">
                                <?php
                                include "../conexion.php";
                                $query_comida = mysqli_query($conexion, "SELECT * FROM platos WHERE estado = 1 AND tipo = 'comida'");
                                if (mysqli_num_rows($query_comida) > 0) {
                                    while ($data_comida = mysqli_fetch_assoc($query_comida)) { ?>
                                        <div class="col-md-3">
                                            <div class="col-12">
                                                <img src="<?php echo ($data_comida['imagen'] == null) ? '../assets/img/default.png' : $data_comida['imagen']; ?>" class="product-image" alt="Product Image">
                                            </div>
                                            <h6 class="my-3"><?php echo $data_comida['nombre']; ?></h6>

                                            <div class="bg-gray py-2 px-3 mt-4">
                                                <h2 class="mb-0">
                                                    $<?php echo $data_comida['precio']; ?>
                                                </h2>
                                            </div>

                                            <div class="mt-4">
                                                <a class="btn btn-primary btn-block btn-flat addDetalle" href="#" data-id="<?php echo $data_comida['id']; ?>" data-tipo="comida">
                                                    <i class="fas fa-cart-plus mr-2"></i>
                                                    Agregar
                                                </a>
                                            </div>
                                        </div>
                                <?php }
                                } else {
                                    echo "<p>No hay comidas disponibles.</p>";
                                } ?>
                            </div>

                            <hr>

                            <!-- Sección de Bebida -->
                            <h4>BEBIDA</h4>
                            <div class="row">
                                <?php
                                $query_bebida = mysqli_query($conexion, "SELECT * FROM platos WHERE estado = 1 AND tipo = 'bebida'");
                                if (mysqli_num_rows($query_bebida) > 0) {
                                    while ($data_bebida = mysqli_fetch_assoc($query_bebida)) { ?>
                                        <div class="col-md-3">
                                            <div class="col-12">
                                                <img src="<?php echo ($data_bebida['imagen'] == null) ? '../assets/img/default.png' : $data_bebida['imagen']; ?>" class="product-image" alt="Product Image">
                                            </div>
                                            <h6 class="my-3"><?php echo $data_bebida['nombre']; ?></h6>

                                            <div class="bg-gray py-2 px-3 mt-4">
                                                <h2 class="mb-0">
                                                    $<?php echo $data_bebida['precio']; ?>
                                                </h2>
                                            </div>

                                            <div class="mt-4">
                                                <a class="btn btn-primary btn-block btn-flat addDetalle" href="#" data-id="<?php echo $data_bebida['id']; ?>" data-tipo="bebida">
                                                    <i class="fas fa-cart-plus mr-2"></i>
                                                    Agregar
                                                </a>
                                            </div>
                                        </div>
                                <?php }
                                } else {
                                    echo "<p>No hay bebidas disponibles.</p>";
                                } ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pedido" role="tabpanel" aria-labelledby="pedido-tab">
                            <h4>COMIDA</h4>
                            <div class="row" id="detalle_comida"></div>
                            <hr>
                            <h4>BEBIDA</h4>
                            <div class="row" id="detalle_bebida"></div>
                            <hr>
                            <div class="form-group">
                                <label for="observacion">Observaciones</label>
                                <textarea id="observacion" class="form-control" rows="3" placeholder="Observaciones"></textarea>
                            </div>
                            <button class="btn btn-primary" type="button" id="realizar_pedido">Realizar pedido</button>
                        </div>
                    </div>
                </div>
                <div class="col-5 col-sm-3">
                    <div class="nav flex-column nav-tabs nav-tabs-right h-100" id="vert-tabs-right-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="vert-tabs-right-home-tab" data-toggle="pill" href="#vert-tabs-right-home" role="tab" aria-controls="vert-tabs-right-home" aria-selected="true">Platos</a>
                        <a class="nav-link" id="pedido-tab" data-toggle="pill" href="#pedido" role="tab" aria-controls="pedido" aria-selected="false">Pedido</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card -->
    </div>
<?php include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const detallesComida = document.getElementById('detalle_comida');
        const detallesBebida = document.getElementById('detalle_bebida');

        // Capturamos el click en el botón Agregar
        document.querySelectorAll('.addDetalle').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.dataset.id;
                const tipo = this.dataset.tipo;
                const nombre = this.closest('.col-md-3').querySelector('h6').textContent;
                const precio = this.closest('.col-md-3').querySelector('.bg-gray h2').textContent;

                // Creamos el elemento de pedido
                const detalle = document.createElement('div');
                detalle.className = 'col-md-12 detalle-item';
                detalle.dataset.id = id;
                detalle.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>${nombre}</h6>
                        <div>
                            <span>${precio}</span>
                            <button class="btn btn-warning btn-sm mx-2 editDetalle">Editar</button>
                            <button class="btn btn-danger btn-sm deleteDetalle">Eliminar</button>
                        </div>
                    </div>
                `;

                // Agregamos el detalle al contenedor correspondiente
                if (tipo === 'comida') {
                    detallesComida.appendChild(detalle);
                } else if (tipo === 'bebida') {
                    detallesBebida.appendChild(detalle);
                }

                // Asignar eventos a los botones de editar y eliminar
                asignarEventosEdicion(detalle);
            });
        });

        function asignarEventosEdicion(detalle) {
            const editBtn = detalle.querySelector('.editDetalle');
            const deleteBtn = detalle.querySelector('.deleteDetalle');

            editBtn.addEventListener('click', function () {
                const nombre = detalle.querySelector('h6').textContent;
                const nuevoNombre = prompt('Editar nombre:', nombre);
                if (nuevoNombre) {
                    detalle.querySelector('h6').textContent = nuevoNombre;
                }
            });

            deleteBtn.addEventListener('click', function () {
                detalle.remove();
            });
        }
    });
</script>
