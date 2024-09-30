<?php
// Inicia la sesión para verificar si el usuario tiene permisos
session_start();

// Verifica si el usuario tiene el rol 1 o 2, de lo contrario redirige a la página de permisos
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    // Incluye el archivo de conexión a la base de datos
    include "../conexion.php";

    // Verifica si se ha enviado un formulario
    if (!empty($_POST)) {
        $alert = ""; // Inicializa la variable para mostrar mensajes al usuario
        $id = $_POST['id']; // Obtiene el ID del plato (si es que existe)
        $plato = $_POST['plato']; // Obtiene el nombre del plato del formulario
        $precio = $_POST['precio']; // Obtiene el precio del plato del formulario

        // Verifica si los campos de nombre del plato o precio están vacíos, o si el precio es negativo
        if (empty($plato) || empty($precio) || $precio < 0) {
            // Muestra un mensaje de alerta si los campos son inválidos
            $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Todos los campos son obligatorios
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
        } else {
            // Si el ID está vacío, significa que es un nuevo registro
            if (empty($id)) {
                // Consulta si ya existe un plato con el mismo nombre y que esté activo
                $query = mysqli_query($conexion, "SELECT * FROM platos WHERE nombre = '$plato' AND estado = 1");
                $result = mysqli_fetch_array($query);

                // Si el plato ya existe, muestra una alerta
                if ($result > 0) {
                    $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        El plato ya existe
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                } else {
                    // Si no existe, inserta el nuevo plato en la base de datos
                    $query_insert = mysqli_query($conexion, "INSERT INTO platos (nombre, precio) VALUES ('$plato', '$precio')");

                    // Si la inserción fue exitosa, muestra una alerta de éxito
                    if ($query_insert) {
                        $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Plato registrado
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                    } else {
                        // Si ocurrió un error al insertar, muestra una alerta de error
                        $alert = '<div class="alert alert-danger" role="alert">
                    Error al registrar el plato
                  </div>';
                    }
                }
            } else {
                // Si el ID no está vacío, significa que se está actualizando un registro existente
                $query_update = mysqli_query($conexion, "UPDATE platos SET nombre = '$plato', precio = $precio WHERE id = $id");

                // Si la actualización fue exitosa, muestra una alerta de éxito
                if ($query_update) {
                    $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Plato Modificado
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                } else {
                    // Si ocurrió un error al actualizar, muestra una alerta de error
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
    // Incluye el archivo del encabezado de la página
    include_once "includes/header.php";
?>
   <div class="card shadow-lg">
    <!-- Inicia una tarjeta con sombra -->
    <div class="card-body">
        <!-- Cuerpo de la tarjeta -->
        <div class="row">
            <!-- Inicia una fila -->
            <div class="col-md-12">
                <!-- Columna que ocupa todo el ancho en dispositivos medianos -->
                <div class="card">
                    <!-- Otra tarjeta interna -->
                    <div class="card-body">
                        <!-- Cuerpo de la tarjeta interna -->
                        <!-- Inicia el formulario para registrar o modificar platos -->
                        <form action="" method="post" autocomplete="off" id="formulario">
                            <!-- Muestra la alerta si existe -->
                            <?php echo isset($alert) ? $alert : ''; ?>
                            <div class="row">
                                <!-- Inicia una fila dentro del formulario -->
                                <div class="col-md-4">
                                    <!-- Columna para el nombre del plato -->
                                    <div class="form-group">
                                        <!-- Grupo de formulario -->
                                        <!-- Campo oculto para almacenar el ID del plato (si se va a editar) -->
                                        <input type="hidden" id="id" name="id">
                                        <!-- Etiqueta para el campo del plato -->
                                        <label for="plato" class="text-dark font-weight-bold">Plato</label>
                                        <!-- Campo de texto para ingresar el nombre del plato -->
                                        <input type="text" placeholder="Ingrese nombre del plato" name="plato" id="plato" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <!-- Columna para el precio del plato -->
                                    <div class="form-group">
                                        <!-- Grupo de formulario -->
                                        <!-- Etiqueta para el campo de precio -->
                                        <label for="precio" class="text-dark font-weight-bold">Precio</label>
                                        <!-- Campo de texto para ingresar el precio del plato -->
                                        <input type="text" placeholder="Ingrese precio" class="form-control" name="precio" id="precio">
                                    </div>
                                </div>
                                <div class="col-md-3 form-group">
                                    <!-- Columna para las acciones del formulario -->
                                    <!-- Etiqueta para el grupo de acciones -->
                                    <label for="">Acciones</label> <br>
                                    <!-- Botón para registrar o actualizar el plato -->
                                    <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
                                    <!-- Botón para limpiar el formulario -->
                                    <input type="button" value="Nuevo" onclick="limpiar()" class="btn btn-success" id="btnNuevo">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sección para mostrar la tabla de platos registrados -->
        <div class="card">
            <!-- Otra tarjeta para contener la tabla -->
            <div class="card-body">
                <!-- Cuerpo de la tarjeta -->
                <div class="col-md-12">
                    <!-- Columna que ocupa todo el ancho en dispositivos medianos -->
                    <div class="table-responsive">
                        <!-- Contenedor para hacer la tabla responsiva -->
                        <table class="table table-striped table-bordered" id="tbl">
                            <!-- Tabla con estilo de rayas y bordes -->
                            <thead>
                                <!-- Encabezado de la tabla -->
                                <tr>
                                    <th>#</th>
                                    <!-- Columna para el ID del plato -->
                                    <th>Plato</th>
                                    <!-- Columna para el nombre del plato -->
                                    <th>Precio</th>
                                    <!-- Columna para el precio del plato -->
                                    <th></th>
                                    <!-- Columna para las acciones (editar/eliminar) -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Cuerpo de la tabla -->
                                <?php
                                // Consulta para obtener los platos activos de la base de datos
                                $query = mysqli_query($conexion, "SELECT * FROM platos WHERE estado = 1");
                                $result = mysqli_num_rows($query); // Cuenta el número de filas obtenidas
                                if ($result > 0) {
                                    // Si hay resultados, los recorre y muestra en la tabla
                                    while ($data = mysqli_fetch_assoc($query)) { ?>
                                        <tr>
                                            <!-- Fila de la tabla para cada plato -->
                                            <td><?php echo $data['id']; ?></td>
                                            <!-- Muestra el ID del plato -->
                                            <td><?php echo $data['nombre']; ?></td>
                                            <!-- Muestra el nombre del plato -->
                                            <td><?php echo $data['precio']; ?></td>
                                            <!-- Muestra el precio del plato -->
                                            <td>
                                                <!-- Botón para editar el plato -->
                                                <a href="#" onclick="editarPlato(<?php echo $data['id']; ?>)" class="btn btn-primary"><i class='fas fa-edit'></i></a>
                                                <!-- Formulario para eliminar el plato -->
                                                <form action="eliminar.php?id=<?php echo $data['id']; ?>&accion=platos" method="post" class="confirmar d-inline">
                                                    <button class="btn btn-danger" type="submit"><i class='fas fa-trash-alt'></i> </button>
                                                    <!-- Botón para eliminar el plato -->
                                                </form>
                                            </td>
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

<!-- Incluye el pie de página -->
<?php include_once "includes/footer.php"; 
} else {
    // Si el usuario no tiene permisos, lo redirige a la página de permisos
    header('Location: permisos.php');
}
?>
