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
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, 'Método no permitido. Solo se acepta GET.');
}

$id = $_GET['id'] ?? null;

$select_sql_sybase = "select mecanico_asignado from evento_soporte where evento_soporte_id=$id";
sc_lookup(rs_data_sybase, $select_sql_sybase);

if (!isset({rs_data_sybase}) || !is_array({rs_data_sybase})) {
    responder(500, 'Error al ejecutar la consulta.');
}

if (count({rs_data_sybase}) === 0) {
    responder(404, 'No se encontraron datos para la OP ingresada.');
}

// ✅ Armar respuesta
$mecanico_id = {rs_data_sybase}[0][0] ?? null;

if (empty($mecanico_id)) {
    return responder(404, 'No se asginó mecánico.');
}

// ✅ Armar URL del API `get_colaborador`
$base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$script_dir = dirname(dirname($_SERVER['REQUEST_URI'])); // sube 2 niveles
$api_url = rtrim($base_url . $script_dir, '/') . '/get_colaborador/?id=' . urlencode($mecanico_id);

// ✅ Ejecutar llamada al API interna
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2); // 2 segundos máximo
$response_raw = curl_exec($ch);
$curl_errno = curl_errno($ch);
curl_close($ch);

if ($curl_errno || !$response_raw) {
    responder(500, 'Error al comunicarse con el API.');
}

// ✅ Parsear respuesta
$response = json_decode($response_raw, true);

if (!is_array($response) || ($response['code'] ?? 0) !== 200) {
    responder(500, 'Error al obtener datos del colaborador.');
}

$mecanico_nombre = $response['data']['mecanico'] ?? null;
if (!$mecanico_nombre) {
    responder(500, 'Nombre de mecánico no disponible.');
}

// ✅ Enviar respuesta final
responder(200, 'Mecánico obtenido correctamente.', ['mecanico' => $mecanico_nombre]);