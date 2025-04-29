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

if (empty($costura)) {
    responder(422, 'Se requiere el parámetro "costura".');
}

// ✅ Si no hay ciclo, insertar uno nuevo ciclo
$ciclo = guardar_ciclo($costura, $ciclo_o);

if ($ciclo === null) {
    responder(500, 'Error al insertar el Ciclo.');
}

// ✅ Si no hay ciclo, insertar uno nuevo
$insertedId = guardar_evento_ciclo_normal($ciclo);

if ($insertedId !== null) {
    responder(200, 'Evento insertado correctamente.', ['evento' => $insertedId]);
} else {
    responder(500, 'Error al insertar el Evento.');
}

// ✅ Función para guardar ciclo
function guardar_ciclo($costura, $ciclo_o = NULL): ?int {
    $colmn = (!empty($ciclo_o)) ? ", predecesor_id" : "";
    $valor = (!empty($ciclo_o)) ? ", " . (int)$ciclo_o : "";
    
    $sql_insert = "INSERT INTO ciclo (costua_id".$colmn.") VALUES (" . (int)$costura . $valor . ")";
    sc_exec_sql($sql_insert);

    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) {
        return (int){rs_id[0][0]};
    }
    return null;
}

// ✅ Función para guardar evento ciclo normal
function guardar_evento_ciclo_normal($ciclo): ?int {
    $sql_insert = "INSERT INTO evento_normal (ciclo_id) VALUES (" . (int)$ciclo . ")";
    sc_exec_sql($sql_insert);

    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) {
        return (int){rs_id[0][0]};
    }
    return null;
}