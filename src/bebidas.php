<?php
session_start();
include "../conexion.php";

$clave_secreta = 'N3y0rK3du'; // Clave secreta para edición de bebidas con stock

if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $alert = "";
        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $fecha = date('YmdHis');
        $stock = $_POST['stock'] ?? null;
        $tipo_bebida = $_POST['tipo_bebida'];
        $password = $_POST['password'] ?? null;

        if (empty($nombre) || empty($precio) || $precio < 0) {
            $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Todos los campos son obligatorios
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
        } elseif ($tipo_bebida == 'stock' && $password !== $clave_secreta) {
            $alert = '<div class="alert alert-danger" role="alert">
                        Contraseña incorrecta
                    </div>';
        } else {
            $table = $tipo_bebida == 'no_stock' ? 'bebidas_no_stock' : 'bebidas_stock';
            
            if (empty($id)) {
                // Verificar si la bebida ya existe
                $query = mysqli_query($conexion, "SELECT * FROM $table WHERE nombre = '$nombre'");
                $result = mysqli_fetch_array($query);

                if ($result > 0) {
                    $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        La bebida ya existe
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                } else {
                    // Insertar nueva bebida
                    $columns = $tipo_bebida == 'no_stock' ? "(nombre, precio, fecha)" : "(nombre, precio, stock, fecha)";
                    $values = $tipo_bebida == 'no_stock' ? "('$nombre', '$precio', NOW())" : "('$nombre', '$precio', '$stock', NOW())";
                    $query_insert = mysqli_query($conexion, "INSERT INTO $table $columns VALUES $values");

                    if ($query_insert) {
                        $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Bebida registrada
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                    } else {
                        $alert = '<div class="alert alert-danger" role="alert">
                    Error al registrar la bebida
                  </div>';
                    }
                }
            } else {
                // Actualizar bebida existente
                if ($tipo_bebida == 'no_stock') {
                    $query_update = mysqli_query($conexion, "UPDATE bebidas_no_stock SET nombre = '$nombre', precio = '$precio' WHERE id = '$id'");
                } else {
                    $query_update = mysqli_query($conexion, "UPDATE bebidas_stock SET nombre = '$nombre', precio = '$precio', stock = '$stock' WHERE id = '$id'");
                }

                if ($query_update) {
                    $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Bebida modificada
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                } else {
                    $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Error al modificar
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                }
            }
        }
    }

    if (isset($_GET['id']) && isset($_GET['tipo'])) {
        $id = $_GET['id'];
        $tipo = $_GET['tipo'];
        $table = $tipo == 'no_stock' ? 'bebidas_no_stock' : 'bebidas_stock';
        $query = mysqli_query($conexion, "SELECT * FROM $table WHERE id = '$id'");
        $data = mysqli_fetch_assoc($query);
    } else {
        $data = []; // Inicializar vacío si no se pasa id y tipo
    }

    include_once "includes/header.php";
?>
    <div class="card shadow-lg">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="" method="post" autocomplete="off" id="formulario">
                                <?php echo isset($alert) ? $alert : ''; ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="hidden" id="id" name="id" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">
                                            <label for="nombre" class="text-dark font-weight-bold">Nombre</label>
                                            <input type="text" placeholder="Ingrese nombre de la bebida" name="nombre" id="nombre" class="form-control" value="<?php echo isset($data['nombre']) ? $data['nombre'] : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="precio" class="text-dark font-weight-bold">Precio</label>
                                            <input type="text" placeholder="Ingrese precio" class="form-control" name="precio" id="precio" value="<?php echo isset($data['precio']) ? $data['precio'] : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2" id="stock_div" style="display: <?php echo isset($data['stock']) ? 'block' : 'none'; ?>;">
                                        <div class="form-group">
                                            <label for="stock" class="text-dark font-weight-bold">Stock</label>
                                            <input type="number" placeholder="Ingrese stock" class="form-control" name="stock" id="stock" value="<?php echo isset($data['stock']) ? $data['stock'] : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="tipo_bebida" class="text-dark font-weight-bold">Tipo de Bebida</label>
                                            <select name="tipo_bebida" id="tipo_bebida" class="form-control" onchange="toggleStock()">
                                                <option value="no_stock" <?php echo !isset($data['stock']) ? 'selected' : ''; ?>>No Stock</option>
                                                <option value="stock" <?php echo isset($data['stock']) ? 'selected' : ''; ?>>Con Stock</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2" id="password_div" style="display: <?php echo isset($data['stock']) ? 'block' : 'none'; ?>;">
                                        <div class="form-group">
                                            <label for="password" class="text-dark font-weight-bold">Contraseña</label>
                                            <input type="password" placeholder="Ingrese contraseña" class="form-control" name="password" id="password">
                                        </div>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label for="">Acciones</label> <br>
                                        <input type="submit" value="Guardar" class="btn btn-primary" id="btnAccion">
                                        <input type="button" value="Nuevo" onclick="limpiar()" class="btn btn-success" id="btnNuevo">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="col-md-12">
                        <h4>Bebidas Sin Stock</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="tbl_no_stock">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Fecha</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = mysqli_query($conexion, "SELECT * FROM bebidas_no_stock");
                                    $result = mysqli_num_rows($query);
                                    if ($result > 0) {
                                        while ($data = mysqli_fetch_assoc($query)) { ?>
                                            <tr>
                                                <td><?php echo $data['id']; ?></td>
                                                <td><?php echo $data['nombre']; ?></td>
                                                <td><?php echo $data['precio']; ?></td>
                                                <td><?php echo $data['fecha']; ?></td>
                                                <td>
                                                   <a href="?id=<?php echo $data['id']; ?>&tipo=no_stock" class="btn btn-primary">
													<i class='fas fa-edit'></i>
													</a>
												<form action="eliminarBebidas.php?id=<?php echo $data['id']; ?>&accion=bebidas_no_stock" method="post" class="confirmar d-inline">
  <button class="btn btn-danger" type="submit">
    <i class='fas fa-trash-alt'></i>
  </button>
</form>

													

                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <h4>Bebidas Con Stock</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="tbl_stock">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Fecha</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = mysqli_query($conexion, "SELECT * FROM bebidas_stock");
                                    $result = mysqli_num_rows($query);
                                    if ($result > 0) {
                                        while ($data = mysqli_fetch_assoc($query)) { ?>
                                            <tr>
                                                <td><?php echo $data['id']; ?></td>
                                                <td><?php echo $data['nombre']; ?></td>
                                                <td><?php echo $data['precio']; ?></td>
                                                <td><?php echo $data['stock']; ?></td>
                                                <td><?php echo $data['fecha']; ?></td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            toggleStock();
        });

        function toggleStock() {
            const tipoBebida = document.getElementById('tipo_bebida').value;
            const stockDiv = document.getElementById('stock_div');
            const passwordDiv = document.getElementById('password_div');
            if (tipoBebida === 'stock') {
                stockDiv.style.display = 'block';
                passwordDiv.style.display = 'block';
            } else {
                stockDiv.style.display = 'none';
                passwordDiv.style.display = 'none';
            }
        }

        function limpiar() {
            document.getElementById('formulario').reset();
            document.getElementById('id').value = '';
            document.getElementById('tipo_bebida').value = 'no_stock';
            toggleStock();
        }
    </script>
<?php
    include_once "includes/footer.php";
} else {
    header('Location: ../index.php');
}
?>
