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

$codigo = $param['codigo'] ?? null;
if (empty($codigo)) {
    responder(422, 'Se requiere el parámetro "codigo".');
}

$sql = "SELECT cod_colaborador, nombres, empresa from colaborador where cod_colaborador='$codigo' and proyecto='control_piso' and aplicacion='form_colaborador_confecciones'";
sc_lookup(rs_data_sybase, $sql);

if (empty({rs_data_sybase})) {
    responder(404, 'Operario no encontrado.');
}

$rta = [];
$rta['codigo'] = {rs_data_sybase}[0][0] ?? null;
$rta['datos'] = {rs_data_sybase}[0][1] ?? null;
$rta['empresa_id'] = {rs_data_sybase}[0][2] ?? null;

responder(200, 'Operario encontrado.', $rta);