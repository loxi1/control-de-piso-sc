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
$ciclo   = (int)($param['ciclo'] ?? 0);
$estado = !empty($param['estado']) ? 1 : null;

if (empty($costura)) {
    responder(422, 'Se requiere el parámetro "costura".');
}

if (empty($ciclo)) {
    responder(422, 'Se requiere el parámetro "ciclo".');
}

$set = [];

if ($estado !== null) {
    $set[] = "estado_id = 0";
}

// Campos fijos
$set[] = "tiempo_fin = NOW()";
$set[] = "tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio)";

// Armar sentencia SQL
$txt_set = implode(", ", $set);
$sql = "UPDATE ciclo SET $txt_set WHERE ciclo_id = $ciclo";
sc_exec_sql($sql);
responder(200, 'Ciclo insertado correctamente.', ['ciclo' => $ciclo]);