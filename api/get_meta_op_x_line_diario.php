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

$compania = "02";
$empresa = "COFACO";
$op = $_GET['op'] ?? null;
$linea = $_GET['linea'] ?? null;

if (empty($op)) {
    responder(422, 'Se requiere el parámetro "op".');
}

if (empty($linea)) {
    responder(422, 'Se requiere el parámetro "linea".');
}

// 🔗 Armar URLs de los endpoints internos
$base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$script_dir = dirname(dirname($_SERVER['REQUEST_URI']));
$api = rtrim(rtrim($base_url . $script_dir, '/'));

$url_timbradas = $api . '/get_cantidad_timbradas_x_dia/?op=' . urlencode($op) . '&linea=' . urlencode($linea);
$url_meta = $api . '/get_meta_x_op_linea_dia/?op=' . urlencode($op) . '&linea=' . urlencode($linea);

// ✅ Obtener cantidad timbradas
$ch1 = curl_init($url_timbradas);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_TIMEOUT, 2);
$response_timbradas = curl_exec($ch1);
curl_close($ch1);

$canttimbradas = 0;
if (!empty($response_timbradas)) {
    $json1 = json_decode($response_timbradas, true);
    if (is_array($json1) && ($json1['code'] ?? 0) === 200) {
        $canttimbradas = !empty($json1['data']['cant']) ? floatval($json1['data']['cant']) : 0;
    }
}

// ✅ Obtener meta del día
$ch2 = curl_init($url_meta);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 2);
$response_meta = curl_exec($ch2);
curl_close($ch2);

$meta = 0;
if (!empty($response_meta)) {
    $json2 = json_decode($response_meta, true);
    if (is_array($json2) && ($json2['code'] ?? 0) === 200) {
        $meta = !empty($json2['data']['meta']) ? floatval($json2['data']['meta']) : 0;
    }
}

// ✅ Calcular porcentaje
$metaporcentaje = ($meta > 0) ? ($canttimbradas * 100) / $meta : 0;

// ✅ Enviar respuesta final
responder(200, 'Meta obtenido correctamente.', ['meta' => round($metaporcentaje, 2)]);