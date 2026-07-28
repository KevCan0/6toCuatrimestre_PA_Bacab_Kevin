<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {

    $stmt = $conexion->prepare('DELETE FROM inscripciones WHERE id_alumno = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $stmt = $conexion->prepare('DELETE FROM alumnos WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

header('Location: index.php');
exit;
?>
