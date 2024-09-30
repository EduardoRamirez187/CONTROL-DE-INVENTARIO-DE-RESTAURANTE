<?php
ob_start();
session_start();
include_once "includes/header.php";
include "../conexion.php";

if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    $fecha = date('Y-m-d');
    $id_sala = isset($_GET['id_sala']) ? intval($_GET['id_sala']) : 0;
    $mesa = isset($_GET['mesa']) ? intval($_GET['mesa']) : 0;
    echo $id_sala;

    echo $mesa;
    

    // Crear o recuperar el pedido
    $query_pedido = mysqli_query($conexion, "SELECT * FROM pedidos WHERE id_sala = $id_sala AND num_mesa = $mesa AND estado = 'PENDIENTE'");
    if (!$query_pedido) {
        die("Error en la consulta: " . mysqli_error($conexion));
    }
    if (mysqli_num_rows($query_pedido) == 0) {
        // Crear nuevo pedido
        $query_insert_pedido = mysqli_query($conexion, "INSERT INTO pedidos (id_sala, num_mesa, fecha, estado) VALUES ($id_sala, $mesa, '$fecha', 'PENDIENTE')");
        if ($query_insert_pedido) {
            $id_pedido = mysqli_insert_id($conexion);
        } else {
            die("<div class='alert alert-danger'>Error al crear el pedido: " . mysqli_error($conexion) . "</div>");
        }
    } else {
        $data_pedido = mysqli_fetch_assoc($query_pedido);
        $id_pedido = $data_pedido['id'];
    }

    // Procesar el formulario de agregar producto
    if (isset($_POST['agregar_producto'])) {
        $id_producto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
        $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
    
        if ($id_producto > 0 && $cantidad > 0) {
            // Primero consultar en la tabla 'platos'
            $query_precio = mysqli_query($conexion, "SELECT nombre, precio FROM platos WHERE id = $id_producto ");
            if (mysqli_num_rows($query_precio) > 0) {
                $data_precio = mysqli_fetch_assoc($query_precio);
                $nombre = $data_precio['nombre'];
                $precio = $data_precio['precio'];
            } else {
                // Si no se encuentra en 'platos', consultar en 'bebidas_stock'
                $query_precio = mysqli_query($conexion, "SELECT nombre, precio FROM bebidas_stock WHERE id = $id_producto");
                if (mysqli_num_rows($query_precio) > 0) {
                    $data_precio = mysqli_fetch_assoc($query_precio);
                    $nombre = $data_precio['nombre'];
                    $precio = $data_precio['precio'];
                } else {
                    // Si no se encuentra en 'bebidas_stock', consultar en 'bebidas_no_stock'
                    $query_precio = mysqli_query($conexion, "SELECT nombre, precio FROM bebidas_no_stock WHERE id = $id_producto");
                    if (mysqli_num_rows($query_precio) > 0) {
                        $data_precio = mysqli_fetch_assoc($query_precio);
                        $nombre = $data_precio['nombre'];
                        $precio = $data_precio['precio'];
                    } else {
                        // Si no se encuentra en ninguna tabla, mostrar error
                        echo "<div class='alert alert-danger'>Producto no encontrado.</div>";
                        exit;
                    }
                }
            }
    
            // Verificar si el producto ya está en el detalle del pedido
            $query_check = mysqli_query($conexion, "SELECT * FROM detalle_pedidos WHERE id_pedido = $id_pedido AND id = $id_producto AND id_sala = $id_sala AND mesa = $mesa");

            if (mysqli_num_rows($query_check) > 0) {
                // Actualizar cantidad si el producto ya está en el detalle del pedido
                $query_update = mysqli_query($conexion, "UPDATE detalle_pedidos SET cantidad = cantidad + $cantidad, id_sala = $id_sala, mesa = $mesa WHERE id_pedido = $id_pedido AND id = $id_producto");
                
                if ($query_update) {
                    echo "<div class='alert alert-success'>Cantidad del producto actualizada correctamente.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error al actualizar la cantidad del producto: " . mysqli_error($conexion) . "</div>";
                }
            } else {
                // Insertar en detalle_pedidos si no existe
                $query_add = mysqli_query($conexion, "INSERT INTO detalle_pedidos (id_pedido, id, nombre, cantidad, precio, id_sala, mesa) VALUES ($id_pedido, $id_producto, '$nombre', $cantidad, $precio, $id_sala, $mesa)");
                
                if ($query_add) {
                    echo "<div class='alert alert-success'>Producto agregado correctamente.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error al agregar producto: " . mysqli_error($conexion) . "</div>";
                }
            }
        } else {
            echo "<div class='alert alert-danger'>Datos del producto inválidos.</div>";
        }
    }
    

                // Actualizar el total del pedido
                $query_total = mysqli_query($conexion, "SELECT SUM(cantidad * precio) AS total FROM detalle_pedidos WHERE id_pedido = $id_pedido AND id_sala = $id_sala AND mesa = $mesa");

            if ($query_total) {
                    $data_total = mysqli_fetch_assoc($query_total);
                    $total = $data_total['total'] ?? 0;
                    $query_update_total = mysqli_query($conexion, "UPDATE pedidos SET total = $total WHERE id = $id_pedido");
                    if (!$query_update_total) {
                        echo "<div class='alert alert-danger'>Error al actualizar el total del pedido: " . mysqli_error($conexion) . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Error al calcular el total del pedido: " . mysqli_error($conexion) . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Error al obtener el precio del producto: " . mysqli_error($conexion) . "</div>";
            
        }
    

    // Procesar eliminación del producto
    if (isset($_GET['eliminar_producto'])) {
        $id_detalle = intval($_GET['eliminar_producto']);
        $query_delete = mysqli_query($conexion, "DELETE FROM detalle_pedidos WHERE id = $id_detalle AND id_pedido = $id_pedido AND id_sala = $id_sala AND mesa = $mesa");

        if ($query_delete) {
            echo "<div class='alert alert-success'>Producto eliminado correctamente.</div>";

            // Actualizar el total del pedido después de la eliminación
            $query_total = mysqli_query($conexion, "SELECT SUM(cantidad * precio) AS total FROM detalle_pedidos WHERE id_pedido = $id_pedido");
            if ($query_total) {
                $data_total = mysqli_fetch_assoc($query_total);
                $total = $data_total['total'] ?? 0;
                $query_update_total = mysqli_query($conexion, "UPDATE pedidos SET total = $total WHERE id = $id_pedido");
                if (!$query_update_total) {
                    echo "<div class='alert alert-danger'>Error al actualizar el total del pedido: " . mysqli_error($conexion) . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Error al calcular el total del pedido: " . mysqli_error($conexion) . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Error al eliminar producto: " . mysqli_error($conexion) . "</div>";
        }
    }

    

    // Procesar actualización del producto
    if (isset($_POST['editar_producto'])) {
        $id_detalle = intval($_POST['id_detalle']);
        $cantidad = intval($_POST['cantidad']);
        if ($id_detalle > 0 && $cantidad > 0) {
            $query_update = mysqli_query($conexion, "UPDATE detalle_pedidos SET cantidad = $cantidad WHERE id = $id_detalle AND id_pedido = $id_pedido AND id_sala = $id_sala AND mesa = $mesa");

            if ($query_update) {
                echo "<div class='alert alert-success'>Producto actualizado correctamente.</div>";

                // Actualizar el total del pedido después de la actualización
                $query_total = mysqli_query($conexion, "SELECT SUM(cantidad * precio) AS total FROM detalle_pedidos WHERE id_pedido = $id_pedido");
                if ($query_total) {
                    $data_total = mysqli_fetch_assoc($query_total);
                    $total = $data_total['total'] ?? 0;
                    $query_update_total = mysqli_query($conexion, "UPDATE pedidos SET total = $total WHERE id = $id_pedido");
                    if (!$query_update_total) {
                        echo "<div class='alert alert-danger'>Error al actualizar el total del pedido: " . mysqli_error($conexion) . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Error al calcular el total del pedido: " . mysqli_error($conexion) . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Error al actualizar producto: " . mysqli_error($conexion) . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Datos del producto inválidos.</div>";
        }
        
    }

    // Obtener productos de cada categoría
    $query_platos = mysqli_query($conexion, "SELECT * FROM platos");
    $query_bebidas_no_stock = mysqli_query($conexion, "SELECT * FROM bebidas_no_stock");
    $query_bebidas_stock = mysqli_query($conexion, "SELECT * FROM bebidas_stock");

    // Obtener el total del pedido al inicio
    $total = 0; // Inicializar total en caso de que no haya productos
    $query_total = mysqli_query($conexion, "SELECT SUM(cantidad * precio) AS total FROM detalle_pedidos WHERE id_pedido = $id_pedido AND id_sala = $id_sala AND mesa = $mesa");

    if ($query_total) {
        $data_total = mysqli_fetch_assoc($query_total);
        $total = $data_total['total'] ?? 0;
    } else {
        echo "<div class='alert alert-danger'>Error al calcular el total inicial del pedido: " . mysqli_error($conexion) . "</div>";
    }

?>

<div class="container">
    <!-- Agregar Productos -->
    <div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Agregar Producto</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <!-- Selección de Producto -->
            <div class="form-group">
                <label for="id_producto">Producto:</label>
                <select id="id_producto" name="id_producto" class="form-control" required>
                    <option value="">Seleccione un producto</option>

                    <!-- Categoría Platos -->
                    <optgroup label="Platos">
                        <?php while ($row = mysqli_fetch_assoc($query_platos)) { ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>
                        <?php } ?>
                    </optgroup>

                    <!-- Categoría Bebidas en Stock -->
                  <!-- Categoría Bebidas en Stock -->
<optgroup label="Bebidas en Refrigerador">
    <?php while ($row = mysqli_fetch_assoc($query_bebidas_stock)) { ?>
        <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>
    <?php } ?>
</optgroup>

<!-- Categoría Bebidas sin Stock -->
<optgroup label="Bebidas cocteleria">
    <?php while ($row = mysqli_fetch_assoc($query_bebidas_no_stock)) { ?>
        <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre']; ?></option>
    <?php } ?>
</optgroup>

                </select>
            </div>

            <!-- Cantidad -->
            <div class="form-group">
    <label for="cantidad">Cantidad:</label>
    <input type="number" id="cantidad" name="cantidad" class="form-control" min="1" value="1" required>
</div>


            <!-- Botón de Agregar -->
            <button type="submit" name="agregar_producto" class="btn btn-primary">Agregar Producto</button>
        </form>
    </div>
</div>
</div>



    <!-- Detalles del Pedido -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Detalles del Pedido</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                   $query_detalle = mysqli_query($conexion, "SELECT * FROM detalle_pedidos WHERE id_pedido = $id_pedido AND id_sala = $id_sala AND mesa = $mesa");

                    if (mysqli_num_rows($query_detalle) > 0) {
                        while ($row = mysqli_fetch_assoc($query_detalle)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['cantidad']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['precio']) . "</td>";
                            echo "<td>
                                <a href='?eliminar_producto=" . $row['id'] . "' class='btn btn-danger btn-sm'>Eliminar</a>
                                <a href='#' data-toggle='modal' data-target='#editModal" . $row['id'] . "' class='btn btn-warning btn-sm'>Editar</a>
                            </td>";
                            echo "</tr>";

                            // Modal para editar cantidad
                            echo "<div class='modal fade' id='editModal" . $row['id'] . "' tabindex='-1' role='dialog' aria-labelledby='editModalLabel' aria-hidden='true'>
                                <div class='modal-dialog' role='document'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editModalLabel'>Editar Producto</h5>
                                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                                <span aria-hidden='true'>&times;</span>
                                            </button>
                                        </div>
                                        <div class='modal-body'>
                                            <form method='POST' action=''>
                                                <input type='hidden' name='id_detalle' value='" . $row['id'] . "'>
                                                <div class='form-group'>
                                                    <label for='cantidad'>Cantidad:</label>
                                                    <input type='number' id='cantidad' name='cantidad' class='form-control' value='" . $row['cantidad'] . "' min='1' required>
                                                </div>
                                                <button type='submit' name='editar_producto' class='btn btn-primary'>Actualizar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No hay productos en el pedido.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Total y Opciones -->
    <form action="procesar_pago.php" method="post">
    <input type="hidden" name="total" value="<?php echo $total; ?>">
    <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
    <input type="hidden" name="id_sala" value="<?php echo $id_sala; ?>">
    <input type="hidden" name="mesa" value="<?php echo $mesa; ?>">
    <button type="submit" name="pagar" class="btn btn-primary">Pagar</button>
    <a href="index.php" class="btn btn-secondary">Atender otra mesa</a>
</form>





<?php include_once "includes/footer.php"; ?>
