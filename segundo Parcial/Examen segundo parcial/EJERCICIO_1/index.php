<?php
require_once 'config.php';

$editar = null;
if (isset($_GET['editar'])) {
    $id_editar = (int) $_GET['editar'];
    $stmt = $conexion->prepare('SELECT * FROM alumnos WHERE id = ?');
    $stmt->bind_param('i', $id_editar);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}

$carreras = $conexion->query('SELECT * FROM carreras ORDER BY nombre');
$cuatrimestres = $conexion->query('SELECT * FROM cuatrimestres ORDER BY id');

$sql = "SELECT a.*, c.nombre AS carrera, cu.nombre AS cuatrimestre
        FROM alumnos a
        INNER JOIN carreras c ON a.id_carrera = c.id
        INNER JOIN cuatrimestres cu ON a.cuatrimestre_actual = cu.id
        ORDER BY a.id";
$alumnos = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de alumnos</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1>Gestor de alumnos</h1>

    <div class="caja">
        <h2><?php echo $editar ? 'Editar alumno' : 'Agregar alumno'; ?></h2>

        <form action="guardar.php" method="POST">
            <input type="hidden" name="accion" value="<?php echo $editar ? 'editar' : 'agregar'; ?>">
            <input type="hidden" name="id" value="<?php echo $editar['id'] ?? ''; ?>">

            <label>Matrícula</label>
            <input type="text" name="matricula" required value="<?php echo htmlspecialchars($editar['matricula'] ?? ''); ?>">

            <label>Nombre</label>
            <input type="text" name="nombre" required value="<?php echo htmlspecialchars($editar['nombre'] ?? ''); ?>">

            <label>Apellido paterno</label>
            <input type="text" name="apellido_paterno" required value="<?php echo htmlspecialchars($editar['apellido_paterno'] ?? ''); ?>">

            <label>Apellido materno</label>
            <input type="text" name="apellido_materno" value="<?php echo htmlspecialchars($editar['apellido_materno'] ?? ''); ?>">

            <label>Correo</label>
            <input type="email" name="correo" required value="<?php echo htmlspecialchars($editar['correo'] ?? ''); ?>">

            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?php echo htmlspecialchars($editar['telefono'] ?? ''); ?>">

            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($editar['fecha_nacimiento'] ?? ''); ?>">

            <label>Carrera</label>
            <select name="id_carrera" required>
                <?php while ($carrera = $carreras->fetch_assoc()) { ?>
                    <option value="<?php echo $carrera['id']; ?>"
                        <?php echo (($editar['id_carrera'] ?? '') == $carrera['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($carrera['nombre']); ?>
                    </option>
                <?php } ?>
            </select>

            <label>Cuatrimestre</label>
            <select name="cuatrimestre_actual" required>
                <?php while ($cuatrimestre = $cuatrimestres->fetch_assoc()) { ?>
                    <option value="<?php echo $cuatrimestre['id']; ?>"
                        <?php echo (($editar['cuatrimestre_actual'] ?? '') == $cuatrimestre['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cuatrimestre['nombre']); ?>
                    </option>
                <?php } ?>
            </select>

            <label>Estatus</label>
            <select name="estatus">
                <?php foreach (['Activo', 'Baja', 'Egresado'] as $estatus) { ?>
                    <option value="<?php echo $estatus; ?>"
                        <?php echo (($editar['estatus'] ?? 'Activo') === $estatus) ? 'selected' : ''; ?>>
                        <?php echo $estatus; ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit">Guardar</button>
            <?php if ($editar) { ?>
                <a class="boton gris" href="index.php">Cancelar</a>
            <?php } ?>
        </form>
    </div>

    <div class="caja">
        <h2>Consultar API GET</h2>
        <form id="formBusqueda">
            <label>ID del alumno</label>
            <input type="number" id="buscarId" min="1" placeholder="Ejemplo: 1">

            <label>Apellido</label>
            <input type="text" id="buscarApellido" placeholder="Ejemplo: Gomez">

            <button type="submit">Buscar en API</button>
            <button type="button" id="mostrarTodos">Mostrar todos</button>
        </form>
        <p id="mensajeApi"></p>
    </div>

    <div class="caja ancho">
        <h2>Alumnos registrados</h2>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Matrícula</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Correo</th>
                        <th>Carrera</th>
                        <th>Cuatrimestre</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaAlumnos">
                    <?php while ($alumno = $alumnos->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $alumno['id']; ?></td>
                            <td><?php echo htmlspecialchars($alumno['matricula']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['apellido_paterno'] . ' ' . $alumno['apellido_materno']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['correo']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['carrera']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['cuatrimestre']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['estatus']); ?></td>
                            <td>
                                <a class="boton" href="index.php?editar=<?php echo $alumno['id']; ?>">Editar</a>
                                <a class="boton rojo eliminar" href="eliminar.php?id=<?php echo $alumno['id']; ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
