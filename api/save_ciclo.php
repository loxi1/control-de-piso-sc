<?php
require_once('../_lib/util/funciones.php');
header('Content-Type: application/json');

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
$usuario = $param['usuario'] ?? null;
$nombre = $param['nombre'] ?? null;
$idingreso = intval($param['idingreso'] ?? 0);

if (empty($costura)) {
    responder(422, 'Se requiere el parámetro "costura".');
}

if (empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

if ($idingreso <= 0) {
    responder(422, 'Se requiere el parámetro "idingreso" válido.');
}

// 🔄 Si hay ciclo, solo actualiza
if ($ciclo > 0) {
    $sql = "UPDATE ciclo 
            SET tiempo_fin = NOW(), 
                tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio),
                usuario_modifica = '" . $usuario . "'
            WHERE ciclo_id = $ciclo";
    sc_exec_sql($sql);
    /**ACTUALIZAR EFICIENCIA, META Y REPROCESO X COSTURA  */
    $op = $param['op'] ?? null;
    $linea = $param['linea'] ?? null;
    $api = getUrl();
    $api_save = $api . '/save_costura_datos/?usuario=' . urlencode($usuario).'&linea=' . urlencode($linea) . '&costura=' . urlencode($costura) . '&op=' . urlencode($op);
    
    $response = apiGet($api_save);
    if ($response === null) {
        responder(500, 'Error al actualizar los datos de costura.');
    }
}

// 💾 Si no hay ciclo, insertar uno nuevo
$insert['costua_id'] = (int)$costura;
$insert['usuario_registra'] = $usuario;
$insert['usuario_nombre'] = $nombre;
$insert['ingreso_id'] = $idingreso;

$sql = formarSqlInsert("ciclo", $insert);

$insertedId = guardar_ciclo($insert);

if ($insertedId !== null) {
    responder(200, 'Ciclo insertado correctamente.', ['ciclo' => $insertedId]);
} else {
    responder(500, 'Error al insertar el ciclo.');
}

// 💾 Función para guardar ciclo
function guardar_ciclo($sql_insert): ?int {
    if (empty($sql_insert)) {
        return null;
    }

    sc_exec_sql($sql_insert);

    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) {
        return (int){rs_id[0][0]};
    }
    return null;
}