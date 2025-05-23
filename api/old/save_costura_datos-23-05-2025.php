<?php
require_once('../_lib/util/funciones.php');
header('Content-Type: application/json');

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
if (empty($costura) || !ctype_digit($costura)) {
    responder(422, 'Se requiere el parámetro "costura" numérico.');
}
if (empty($usuario)) responder(422, 'Se requiere el parámetro "usuario".');
if (empty($op)) responder(422, 'Se requiere el parámetro "op".');
if (empty($linea)) responder(422, 'Se requiere el parámetro "linea".');

$base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$base_url = str_replace(' ', '', $base_url); 
$script_dir = dirname(dirname($_SERVER['REQUEST_URI'])); // subir 2 niveles
echo "que es:-->". rtrim(rtrim($base_url . $script_dir, '/'), '/');
// 🔗 Obtener url de la API
$api = getUrl();

// ==========================
// 🔗 Obtener EndPoinds
// ==========================
$api_eficiencia = $api . '/get_eficiencia/?usuario=' . urlencode($usuario);
$api_reprocesos = $api . '/get_reprocesos/?usuario=' . urlencode($usuario) . '&costura=' . urlencode($costura);
$api_meta = $api . '/get_meta_op_x_line_diario/?op=' . urlencode($op) . '&linea=' . urlencode($linea);

$upd = [];
// ==========================
// 🔹 Obtener Eficiencia
// ==========================
$response_eficiencia = apiGet($api_eficiencia);
$upd['operario_meta'] = extraerDato($response_eficiencia, 'eficiencia');

// ==========================
// 🔹 Obtener reprocesos
// ==========================
$response_reproceso = apiGet($api_reprocesos);
$upd['reproceso'] = extraerDato($response_reproceso, 'reprocesos');

// ==========================
// 🔹 Obtener meta de línea
// ==========================
$response_meta = apiGet($api_meta);
$upd['linea_meta'] = extraerDato($response_meta, 'meta');

// : Agregar usuario que modifica
$upd['usuario_modifica'] = $usuario;

// ==========================
// 🔄 Construir y ejecutar UPDATE
// ==========================
$sql = formarSqlUpdate("costura", $upd, "costura_id = $costura");
sc_exec_sql($sql);

// ✅ Responder
responder(200, 'Actualizó correctamente.', ['rta' => 1]);