<?php
ob_start();
session_start();
include "../conexion.php";

$id_sala = $_GET['id_sala'] ?? null;
$mesa = $_GET['mesa'] ?? null;

if ($id_sala && $mesa) {
    header("Location: finalizar.php?id_sala=$id_sala&mesa=$mesa");
    ob_end_flush(); // Envía el contenido del búfer
    exit;
} else {
    echo "<div class='alert alert-danger'>Faltan parámetros en la URL.</div>";
}
?>
