<?php
session_start();

// Verificar que el usuario tenga permisos
if ($_SESSION['rol'] != 1) {
    header('Location: permisos.php');
    exit;
}

require("../conexion.php");

// Verificar que se haya iniciado sesión
if (empty($_SESSION['idUser'])) {
    header('Location: ../');
    exit;
}

// Verificar si se reciben los parámetros correctos por GET
if (!empty($_GET['id']) && $_GET['accion'] === 'bebidas_stock' && !empty($_GET['clave'])) {
    $id = (int)$_GET['id']; // Convertir a entero para evitar inyecciones SQL
    $clave = $_GET['clave'];

    // Verificar si la clave es correcta
    if ($clave === 'N3YOrK3du') {
        // Ejecutar la consulta DELETE para eliminar el registro
        $query_delete = mysqli_query($conexion, "DELETE FROM bebidas_stock WHERE id = $id");

        // Verificar si la eliminación fue exitosa
        if ($query_delete) {
            // Redirigir a index.php con un mensaje de éxito
            mysqli_close($conexion);
            header("Location: index.php?status=success");
            exit;
        } else {
            // Redirigir con un mensaje de error si la eliminación falló
            mysqli_close($conexion);
            header("Location: index.php?status=error");
            exit;
        }
    } else {
        // Redirigir si la clave es incorrecta
        header("Location: index.php?status=invalid_key");
        exit;
    }
} else {
    // Redirigir si faltan parámetros o son incorrectos
    header("Location: index.php?status=invalid");
    exit;
}
?>
