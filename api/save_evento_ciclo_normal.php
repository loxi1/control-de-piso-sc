<?php
header('Content-Type: application/json');

function responder(int $code, string $msn, array $data = []): never {
    http_response_code($code);
    echo json_encode([
        'code' => $code,
        'msn'  => $msn,
        'data' => $data
    ]);
    exit;
}

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, 'Método no permitido. Solo se acepta POST.');
}

// ✅ Leer y validar JSON
$input = file_get_contents('php://input');
$param = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    responder(400, 'JSON inválido.');
}

// ✅ Validar parámetros requeridos
$costura = $param['costura'] ?? null;
$ciclo_o = $param['ciclo'] ?? null;
$motivo = !empty($param['motivo']) ? $param['motivo'] : null;
$usuario = $param['usuario'] ?? null;
$nombre = $param['nombre'] ?? null;
$tipo = $param['tipo'] ?? 0;

if (empty($costura)) {
    responder(422, 'Se requiere el parámetro "costura".');
}

if ($ciclo_o === null) {
    responder(500, 'Error al insertar el Ciclo.');
}

if (empty($motivo)) {
    responder(422, 'Se requiere el parámetro "motivo".');
}

if ($tipo === 0) {
    responder(422, 'Se requiere el parámetro "tipo".');
}

if(empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

$insevent = [];
$insevent["costua_id"] = (int)$costura;
$insevent["predecesor_id"] = (int)$ciclo_o;
$insevent["usuario_registra"] = $usuario;
$insevent["usuario_nombre"] = $nombre;
$insevent["motivo_id"] = (int)$motivo;
$insevent["motivo_tipo"] = (int)$tipo;

// ✅ Insertar ciclo
$ciclo = guardar_ciclo($insevent);

if ($ciclo === null) {
    responder(500, 'Error al insertar el Ciclo.');
}

$insevent = [];

$insevent["ciclo_id"] = (int)$ciclo;
$insevent["motivo_id"] = (int)$motivo;
$insevent["usuario_registra"] = $usuario;
$insevent["usuario_nombre"] = $nombre;

// ✅ Insertar evento
$insertedId = guardar_evento_ciclo_normal($insevent, $tipo);

if ($insertedId !== null) {
    responder(200, 'Evento insertado correctamente.', ['evento' => $insertedId]);
} else {
    responder(500, 'Error al insertar el Evento.');
}

// ✅ Función para guardar ciclo
function guardar_ciclo($insevent): ?int {
    if (empty($insevent)) {
        return null;
    }

    // Preparar columnas y valores
    $columnas = implode(", ", array_keys($insevent));

    $valores = implode(", ", array_map(function ($v) {
        return is_numeric($v) ? $v : "'" . addslashes($v) . "'";
    }, array_values($insevent)));
    
    $sql_insert = "INSERT INTO ciclo ($columnas) VALUES ($valores)";
    sc_exec_sql($sql_insert);

    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) {
        return (int){rs_id[0][0]};
    }
    return null;
}

// ✅ Función para guardar evento ciclo normal
function guardar_evento_ciclo_normal($insevent, $tipo): ?int {    
    if (empty($insevent)) {
        return null;
    }

    if($tipo === 0) {
        return null;
    }

    $tabla = $tipo == 2 ? 'evento_soporte' : 'evento_normal';

    // Preparar columnas y valores
    $columnas = implode(", ", array_keys($insevent));

    $valores = implode(", ", array_map(function ($v) {
        return is_numeric($v) ? $v : "'" . addslashes($v) . "'";
    }, array_values($insevent)));

    $sql_insert = "INSERT INTO $tabla ($columnas) VALUES ($valores)";
    sc_exec_sql($sql_insert);

    // Obtener el ID insertado
    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) {
        return (int) {rs_id[0][0]};
    }

    return null;
}