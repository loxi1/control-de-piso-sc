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

// ✅ Obtener parámetros
$costura = $_GET['costura'] ?? null;
$op = $_GET['op'] ?? null;
$linea = $_GET['linea'] ?? null;
$usuario = $_GET['usuario'] ?? null;

// ✅ Validar parámetros requeridos
if (empty($costura)) {
    responder(422, 'Se requiere el parámetro "costura".');
}
if (empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}
if (empty($op)) {
    responder(422, 'Se requiere el parámetro "op".');
}
if (empty($linea)) {
    responder(422, 'Se requiere el parámetro "linea".');
}

// ✅ Construir base de la URL para llamados internos
$base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$script_dir = dirname(dirname($_SERVER['REQUEST_URI'])); // subir 2 niveles
$api = rtrim(rtrim($base_url . $script_dir, '/'), '/');

// ==========================
// 🔹 Obtener eficiencia
// ==========================
$api_eficiencia = $api . '/get_eficiencia/?usuario=' . urlencode($usuario);
$ch = curl_init($api_eficiencia);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
$response_raw = curl_exec($ch);
curl_close($ch);

$upd = [];

if (!empty($response_raw)) {
    $response = json_decode($response_raw, true);
    if (is_array($response) && ($response['code'] ?? 0) === 200) {
        $upd['operario_meta'] = $response['data']['eficiencia'] ?? null;
    }
}

// ==========================
// 🔹 Obtener reprocesos
// ==========================
$api_reprocesos = $api . '/get_reprocesos/?usuario=' . urlencode($usuario) . '&costura=' . urlencode($costura);
$ch = curl_init($api_reprocesos);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
$response_raw = curl_exec($ch);
curl_close($ch);

if (!empty($response_raw)) {
    $response = json_decode($response_raw, true);
    if (is_array($response) && ($response['code'] ?? 0) === 200) {
        $upd['reproceso'] = $response['data']['reprocesos'] ?? null;
    }
}

// ==========================
// 🔹 Obtener meta de línea
// ==========================
$api_meta = $api . '/get_meta_op_x_line_diario/?op=' . urlencode($op) . '&linea=' . urlencode($linea);
$ch = curl_init($api_meta);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
$response_raw = curl_exec($ch);
curl_close($ch);

if (!empty($response_raw)) {
    $response = json_decode($response_raw, true);
    if (is_array($response) && ($response['code'] ?? 0) === 200) {
        $upd['linea_meta'] = $response['data']['meta'] ?? null;
    }
}

// ✅ Agregar usuario que modifica
$upd['usuario_modifica'] = $usuario;

// ==========================
// 🔹 Construir y ejecutar UPDATE
// ==========================
$set = [];
foreach ($upd as $campo => $valor) {
    $esFuncionSQL = is_string($valor) && preg_match('/^\s*[A-Z_]+\s*\(.*\)\s*$/i', $valor);
    if (is_numeric($valor) || $esFuncionSQL) {
        $set[] = "$campo = $valor";
    } else {
        $valorSanitizado = addslashes($valor);
        $set[] = "$campo = '$valorSanitizado'";
    }
}

$setClause = implode(", ", $set);
$sql_update = "UPDATE costura SET $setClause WHERE costura_id = $costura";
sc_exec_sql($sql_update);

// ✅ Responder
responder(200, 'Ciclo actualizo correctamente.', ['rta' => 1]);