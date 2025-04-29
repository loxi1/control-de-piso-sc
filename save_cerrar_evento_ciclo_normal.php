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
$evento = $param['evento'] ?? null;
$ciclo = $param['ciclo'] ?? null;

if (empty($evento)) {
    responder(422, 'Se requiere el parámetro "evento".');
}

if(empty($ciclo)) {
    responder(422, 'Se requiere el parámetro "ciclo".');
}

// ✅ Si hay evento, solo actualiza
$sql = "UPDATE evento_normal SET tiempo_fin = NOW(), tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio) WHERE evento_normal_id = $evento";
sc_exec_sql($sql);

$sql = "UPDATE ciclo SET tiempo_fin = NOW(), tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio) WHERE ciclo_id = $ciclo";
sc_exec_sql($sql);

responder(200, 'Evento Actualizado.', ['evento' => $evento]);