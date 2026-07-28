<?php
require_once 'config.php';

$accion = $_POST['accion'] ?? '';

if ($accion === 'agregar') {
    $sql = "INSERT INTO alumnos
            (matricula, nombre, apellido_paterno, apellido_materno, correo, telefono,
             fecha_nacimiento, id_carrera, cuatrimestre_actual, estatus)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        'sssssssiis',
        $_POST['matricula'],
        $_POST['nombre'],
        $_POST['apellido_paterno'],
        $_POST['apellido_materno'],
        $_POST['correo'],
        $_POST['telefono'],
        $_POST['fecha_nacimiento'],
        $_POST['id_carrera'],
        $_POST['cuatrimestre_actual'],
        $_POST['estatus']
    );
    $stmt->execute();
}

if ($accion === 'editar') {
    $sql = "UPDATE alumnos SET
            matricula = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?,
            correo = ?, telefono = ?, fecha_nacimiento = ?, id_carrera = ?,
            cuatrimestre_actual = ?, estatus = ?
            WHERE id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        'sssssssiisi',
        $_POST['matricula'],
        $_POST['nombre'],
        $_POST['apellido_paterno'],
        $_POST['apellido_materno'],
        $_POST['correo'],
        $_POST['telefono'],
        $_POST['fecha_nacimiento'],
        $_POST['id_carrera'],
        $_POST['cuatrimestre_actual'],
        $_POST['estatus'],
        $_POST['id']
    );
    $stmt->execute();
}

header('Location: index.php');
exit;
?>
