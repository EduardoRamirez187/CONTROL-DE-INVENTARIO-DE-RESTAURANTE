<?php
session_start();

// Verificar que el usuario tenga permisos
if ($_SESSION['rol'] != 1) {
    header('Location: permisos.php');
    exit;
}

require("../conexion.php"); // Incluye la conexión a la base de datos

// Verificar que se haya iniciado sesión
if (empty($_SESSION['idUser'])) {
    header('Location: ../');
    exit;
}

// Verificar si se reciben los parámetros correctos por GET
if (!empty($_GET['id']) && $_GET['accion'] === 'bebidas_no_stock') {
    $id = (int)$_GET['id']; // Convertir a entero para evitar inyecciones SQL

    // Preparar la consulta DELETE para eliminar el registro
    $query_delete = "DELETE FROM bebidas_no_stock WHERE id = ?";
    if ($stmt = mysqli_prepare($conexion, $query_delete)) {
        // Vincular el parámetro y ejecutar la consulta
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            // Redirigir a bebidas.php con un mensaje de éxito
            mysqli_stmt_close($stmt);
            mysqli_close($conexion);
            header("Location: bebidas.php?status=success");
            exit;
        } else {
            // Redirigir con un mensaje de error si la eliminación falló
            mysqli_stmt_close($stmt);
            mysqli_close($conexion);
            header("Location: bebidas.php?status=error");
            exit;
        }
    } else {
        // Redirigir con un mensaje de error si la preparación de la consulta falla
        mysqli_close($conexion);
        header("Location: bebidas.php?status=error");
        exit;
    }
} else {
    // Redirigir si faltan parámetros o son incorrectos
    header("Location: bebidas.php?status=invalid");
    exit;
}
?>
