<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config.php';

function responder(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function leerDatos(): array
{
    $datos = [];
    $rawBody = file_get_contents('php://input');

    if ($rawBody !== '') {
        $json = json_decode($rawBody, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $datos = $json;
        } else {
            parse_str($rawBody, $datos);
        }
    }

    if (empty($datos)) {
        $datos = $_POST;
    }

    return $datos;
}

function obtenerIdRuta(): ?int
{
    $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

    if (preg_match('#/alumnos(?:\.php)?/(\d+)$#i', $ruta, $coincidencias)) {
        return (int) $coincidencias[1];
    }

    return null;
}

function normalizarActivo($valor): string
{
    if (is_bool($valor)) {
        return $valor ? 'Activo' : 'Baja';
    }

    if (is_int($valor) || is_float($valor)) {
        return ((int) $valor === 1) ? 'Activo' : 'Baja';
    }

    $valorTexto = strtolower(trim((string) $valor));

    if ($valorTexto === '1' || $valorTexto === 'true' || $valorTexto === 'si' || $valorTexto === 'sí' || $valorTexto === 'activo') {
        return 'Activo';
    }

    return 'Baja';
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = leerDatos();

    $nombre = trim((string) ($datos['nombre'] ?? $_POST['nombre'] ?? ''));
    $apellido = trim((string) ($datos['apellido'] ?? $datos['apellido_paterno'] ?? $_POST['apellido'] ?? $_POST['apellido_paterno'] ?? ''));
    $correo = trim((string) ($datos['email'] ?? $datos['correo'] ?? $_POST['email'] ?? $_POST['correo'] ?? ''));
    $matricula = trim((string) ($datos['matricula'] ?? $_POST['matricula'] ?? ''));
    $apellidoMaterno = trim((string) ($datos['apellido_materno'] ?? $_POST['apellido_materno'] ?? ''));
    $telefono = trim((string) ($datos['telefono'] ?? $_POST['telefono'] ?? ''));
    $fechaNacimiento = trim((string) ($datos['fecha_nacimiento'] ?? $_POST['fecha_nacimiento'] ?? ''));
    $idCarrera = trim((string) ($datos['id_carrera'] ?? $_POST['id_carrera'] ?? ''));
    $cuatrimestreActual = trim((string) ($datos['cuatrimestre_actual'] ?? $_POST['cuatrimestre_actual'] ?? ''));
    $estatus = trim((string) ($datos['estatus'] ?? $_POST['estatus'] ?? 'Activo'));

    if ($nombre === '') {
        responder(400, [
            'ok' => false,
            'mensaje' => 'El nombre es requerido.'
        ]);
    }

    if ($apellido === '') {
        responder(400, [
            'ok' => false,
            'mensaje' => 'El apellido es requerido.'
        ]);
    }

    if ($correo === '') {
        responder(400, [
            'ok' => false,
            'mensaje' => 'El email es requerido.'
        ]);
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responder(400, [
            'ok' => false,
            'mensaje' => 'El email no tiene un formato válido.'
        ]);
    }

    $verificarCorreo = $conexion->prepare('SELECT id FROM alumnos WHERE correo = ?');
    if (!$verificarCorreo) {
        responder(500, [
            'ok' => false,
            'mensaje' => 'No se pudo verificar el correo.'
        ]);
    }

    $verificarCorreo->bind_param('s', $correo);
    $verificarCorreo->execute();
    $verificarCorreo->store_result();

    if ($verificarCorreo->num_rows > 0) {
        responder(409, [
            'ok' => false,
            'mensaje' => 'El email ya está registrado.'
        ]);
    }

    $sql = 'INSERT INTO alumnos
            (matricula, nombre, apellido_paterno, apellido_materno, correo, telefono, fecha_nacimiento, id_carrera, cuatrimestre_actual, estatus)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        responder(500, [
            'ok' => false,
            'mensaje' => 'No se pudo crear el alumno.'
        ]);
    }

    $matricula = $matricula !== '' ? $matricula : '';
    $idCarrera = $idCarrera !== '' ? $idCarrera : '';
    $cuatrimestreActual = $cuatrimestreActual !== '' ? $cuatrimestreActual : '';
    $estatus = $estatus !== '' ? $estatus : 'Activo';

    $stmt->bind_param(
        'ssssssssss',
        $matricula,
        $nombre,
        $apellido,
        $apellidoMaterno,
        $correo,
        $telefono,
        $fechaNacimiento,
        $idCarrera,
        $cuatrimestreActual,
        $estatus
    );

    if (!$stmt->execute()) {
        responder(500, [
            'ok' => false,
            'mensaje' => 'No se pudo guardar el alumno.',
            'detalle' => $stmt->error
        ]);
    }

    responder(201, [
        'ok' => true,
        'mensaje' => 'Alumno creado correctamente.',
        'id' => $conexion->insert_id,
        'datos' => [
            'matricula' => $matricula,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'apellido_materno' => $apellidoMaterno,
            'correo' => $correo,
            'telefono' => $telefono,
            'fecha_nacimiento' => $fechaNacimiento,
            'id_carrera' => $idCarrera,
            'cuatrimestre_actual' => $cuatrimestreActual,
            'estatus' => $estatus
        ]
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $datos = leerDatos();
    $alumnoId = obtenerIdRuta();

    if ($alumnoId === null) {
        $alumnoId = isset($datos['id']) ? (int) $datos['id'] : 0;
    }

    if ($alumnoId <= 0) {
        responder(400, [
            'ok' => false,
            'mensaje' => 'El id del alumno es requerido.'
        ]);
    }

    $verificarAlumno = $conexion->prepare('SELECT id FROM alumnos WHERE id = ?');
    if (!$verificarAlumno) {
        responder(500, [
            'ok' => false,
            'mensaje' => 'No se pudo verificar el alumno.'
        ]);
    }

    $verificarAlumno->bind_param('i', $alumnoId);
    $verificarAlumno->execute();
    $verificarAlumno->store_result();

    if ($verificarAlumno->num_rows === 0) {
        responder(404, [
            'ok' => false,
            'mensaje' => 'El alumno no existe.'
        ]);
    }

    $campos = [];
    $tipos = '';
    $valores = [];

    if (array_key_exists('nombre', $datos)) {
        $nombre = trim((string) $datos['nombre']);
        if ($nombre === '') {
            responder(400, [
                'ok' => false,
                'mensaje' => 'El nombre no puede ir vacío.'
            ]);
        }
        $campos[] = 'nombre = ?';
        $tipos .= 's';
        $valores[] = $nombre;
    }

    if (array_key_exists('apellido', $datos) || array_key_exists('apellido_paterno', $datos)) {
        $apellido = trim((string) ($datos['apellido'] ?? $datos['apellido_paterno'] ?? ''));
        if ($apellido === '') {
            responder(400, [
                'ok' => false,
                'mensaje' => 'El apellido no puede ir vacío.'
            ]);
        }
        $campos[] = 'apellido_paterno = ?';
        $tipos .= 's';
        $valores[] = $apellido;
    }

    if (array_key_exists('email', $datos) || array_key_exists('correo', $datos)) {
        $correo = trim((string) ($datos['email'] ?? $datos['correo'] ?? ''));
        if ($correo === '') {
            responder(400, [
                'ok' => false,
                'mensaje' => 'El email no puede ir vacío.'
            ]);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            responder(400, [
                'ok' => false,
                'mensaje' => 'El email no tiene un formato válido.'
            ]);
        }

        $verificarCorreo = $conexion->prepare('SELECT id FROM alumnos WHERE correo = ? AND id <> ?');
        if (!$verificarCorreo) {
            responder(500, [
                'ok' => false,
                'mensaje' => 'No se pudo verificar el correo.'
            ]);
        }

        $verificarCorreo->bind_param('si', $correo, $alumnoId);
        $verificarCorreo->execute();
        $verificarCorreo->store_result();

        if ($verificarCorreo->num_rows > 0) {
            responder(409, [
                'ok' => false,
                'mensaje' => 'El email ya está registrado por otro alumno.'
            ]);
        }

        $campos[] = 'correo = ?';
        $tipos .= 's';
        $valores[] = $correo;
    }

    if (array_key_exists('telefono', $datos)) {
        $telefono = trim((string) $datos['telefono']);
        $campos[] = 'telefono = ?';
        $tipos .= 's';
        $valores[] = $telefono;
    }

    if (array_key_exists('fecha_nacimiento', $datos)) {
        $fechaNacimiento = trim((string) $datos['fecha_nacimiento']);
        $campos[] = 'fecha_nacimiento = ?';
        $tipos .= 's';
        $valores[] = $fechaNacimiento;
    }

    if (array_key_exists('activo', $datos) || array_key_exists('estatus', $datos)) {
        $estatus = normalizarActivo($datos['activo'] ?? $datos['estatus'] ?? 'Activo');
        $campos[] = 'estatus = ?';
        $tipos .= 's';
        $valores[] = $estatus;
    }

    if (empty($campos)) {
        responder(400, [
            'ok' => false,
            'mensaje' => 'No se enviaron campos para actualizar.'
        ]);
    }

    $sql = 'UPDATE alumnos SET ' . implode(', ', $campos) . ' WHERE id = ?';
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        responder(500, [
            'ok' => false,
            'mensaje' => 'No se pudo actualizar el alumno.'
        ]);
    }

    $tipos .= 'i';
    $valores[] = $alumnoId;

    $stmt->bind_param($tipos, ...$valores);

    if (!$stmt->execute()) {
        responder(500, [
            'ok' => false,
            'mensaje' => 'No se pudo actualizar el alumno.',
            'detalle' => $stmt->error
        ]);
    }

    responder(200, [
        'ok' => true,
        'mensaje' => 'Alumno actualizado correctamente.',
        'id' => $alumnoId,
        'datos' => [
            'nombre' => $datos['nombre'] ?? null,
            'apellido' => $datos['apellido'] ?? $datos['apellido_paterno'] ?? null,
            'email' => $datos['email'] ?? $datos['correo'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'activo' => $datos['activo'] ?? $datos['estatus'] ?? null
        ]
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, [
        'ok' => false,
        'mensaje' => 'Método no permitido. Utiliza GET, POST o PUT.'
    ]);
}

$id_alumno = isset($_GET['id_alumno']) ? trim($_GET['id_alumno']) : '';
$apellido = isset($_GET['apellido']) ? trim($_GET['apellido']) : '';

$sql = "SELECT
            a.id AS id_alumno,
            a.matricula,
            a.nombre,
            a.apellido_paterno,
            a.apellido_materno,
            a.correo,
            a.telefono,
            a.fecha_nacimiento,
            c.nombre AS carrera,
            cu.nombre AS cuatrimestre,
            a.estatus
        FROM alumnos a
        INNER JOIN carreras c ON a.id_carrera = c.id
        INNER JOIN cuatrimestres cu ON a.cuatrimestre_actual = cu.id
        WHERE 1 = 1";

$tipos = '';
$parametros = [];

if ($id_alumno !== '') {
    if (!ctype_digit($id_alumno)) {
        responder(400, [
            'ok' => false,
            'mensaje' => 'El id_alumno debe ser un número entero.'
        ]);
    }

    $sql .= " AND a.id = ?";
    $tipos .= 'i';
    $parametros[] = (int) $id_alumno;
}

if ($apellido !== '') {
    $sql .= " AND CONCAT(a.apellido_paterno, ' ', IFNULL(a.apellido_materno, '')) LIKE ?";
    $tipos .= 's';
    $parametros[] = '%' . $apellido . '%';
}

$sql .= " ORDER BY a.id ASC";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    responder(500, [
        'ok' => false,
        'mensaje' => 'No se pudo preparar la consulta.'
    ]);
}

if (!empty($parametros)) {
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();
$resultado = $stmt->get_result();
$alumnos = [];

while ($fila = $resultado->fetch_assoc()) {
    $alumnos[] = $fila;
}

echo json_encode([
    'ok' => true,
    'total' => count($alumnos),
    'datos' => $alumnos
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$stmt->close();
$conexion->close();
?>
