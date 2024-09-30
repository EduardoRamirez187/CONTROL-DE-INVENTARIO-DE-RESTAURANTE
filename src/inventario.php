<?php
session_start();
include "../conexion.php";

$clave_secreta = 'N3y0rK3du'; 

if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $alert = "";
        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'] ?? null;
        $password = $_POST['password'] ?? null;

        // Validación de campos obligatorios
        if (empty($nombre) || empty($precio) || !is_numeric($precio) || $precio < 0 || empty($stock) || !is_numeric($stock)) {
            $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Todos los campos son obligatorios y deben ser válidos.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
        } else {
            // Si se va a actualizar o eliminar, verificamos la contraseña
            if (!empty($id) && $password !== $clave_secreta) {
                $alert = '<div class="alert alert-danger" role="alert">
                            Contraseña incorrecta
                        </div>';
            } else {
                // Si no hay ID, es una nueva bebida
                if (empty($id)) {
                    // Verificar si la bebida ya existe
                    $query = mysqli_prepare($conexion, "SELECT id FROM bebidas_stock WHERE nombre = ?");
                    mysqli_stmt_bind_param($query, 's', $nombre);
                    mysqli_stmt_execute($query);
                    mysqli_stmt_store_result($query);

                    if (mysqli_stmt_num_rows($query) > 0) {
                        $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                            La bebida ya existe
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
                    } else {
                        // Insertar nueva bebida
                        $query_insert = mysqli_prepare($conexion, "INSERT INTO bebidas_stock (nombre, precio, stock, fecha) VALUES (?, ?, ?, NOW())");
                        mysqli_stmt_bind_param($query_insert, 'sii', $nombre, $precio, $stock);
                        $result = mysqli_stmt_execute($query_insert);

                        if ($result) {
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
                    $query_update = mysqli_prepare($conexion, "UPDATE bebidas_stock SET nombre = ?, precio = ?, stock = ? WHERE id = ?");
                    mysqli_stmt_bind_param($query_update, 'siii', $nombre, $precio, $stock, $id);
                    $result = mysqli_stmt_execute($query_update);

                    if ($result) {
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
    }

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $query = mysqli_prepare($conexion, "SELECT * FROM bebidas_stock WHERE id = ?");
        mysqli_stmt_bind_param($query, 'i', $id);
        mysqli_stmt_execute($query);
        $result = mysqli_stmt_get_result($query);
        $data = mysqli_fetch_assoc($result);
    } else {
        $data = []; // Inicializar vacío si no se pasa id
    }

    include_once "includes/header.php";
?>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-body">
                <h4 class="text-center">Administración de Bebidas con Stock</h4>
                <form action="" method="post" autocomplete="off">
                    <?php echo isset($alert) ? $alert : ''; ?>
                    <div class="form-group">
                        <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($data['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <label for="nombre">Nombre</label>
                        <input type="text" placeholder="Ingrese nombre de la bebida" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($data['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="precio">Precio</label>
                        <input type="text" placeholder="Ingrese precio" class="form-control" name="precio" id="precio" value="<?php echo htmlspecialchars($data['precio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" placeholder="Ingrese stock" class="form-control" name="stock" id="stock" value="<?php echo htmlspecialchars($data['stock'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" placeholder="Ingrese contraseña" class="form-control" name="password" id="password">
                    </div>
                    <div class="form-group text-center">
                        <input type="submit" value="Guardar" class="btn btn-primary">
                        <input type="button" value="Nuevo" onclick="limpiar()" class="btn btn-success">
                    </div>
                </form>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="text-center">Listado de Bebidas con Stock</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
   <?php
$query = mysqli_query($conexion, "SELECT * FROM bebidas_stock");
while ($row = mysqli_fetch_assoc($query)) {
    echo '<tr>
        <td>'.htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['precio'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['stock'], ENT_QUOTES, 'UTF-8').'</td>
        <td>'.htmlspecialchars($row['fecha'], ENT_QUOTES, 'UTF-8').'</td>
        <td>
          <a href="#" class="btn btn-danger btn-sm" onclick="eliminarConClave('.$row['id'].')">Eliminar</a>
          <a href="#" class="btn btn-warning btn-sm" onclick="editarStock('.$row['id'].')">Editar Stock</a>
        </td>
    </tr>';
}
?>
<script>
function eliminarConClave(id) {
    var clave = prompt("Por favor, ingresa la clave para eliminar:");
    if (clave != null && clave != "") {
        window.location.href = "eliminar.php?id=" + id + "&accion=bebidas_stock&clave=" + encodeURIComponent(clave);
    } else {
        alert("La clave es requerida para eliminar.");
    }
}

// Función para editar el stock con clave
function editarStock(id) {
    var nuevoStock = prompt("Ingrese el nuevo stock:");
    if (nuevoStock != null && nuevoStock != "") {
        var clave = prompt("Por favor, ingresa la clave para editar el stock:");
        if (clave != null && clave != "") {
            window.location.href = "eliminar.php?id=" + id + "&nuevo_stock=" + encodeURIComponent(nuevoStock) + "&accion=bebidas_stock&clave=" + encodeURIComponent(clave);
        } else {
            alert("La clave es requerida para editar el stock.");
        }
    } else {
        alert("El stock es requerido.");
    }
}
</script>
<?php
    include_once "includes/footer.php";
} else {
    header("Location: ../index.php");
    exit();
}
?>
