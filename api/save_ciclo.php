<?php
require_once('../_lib/util/session_check.php');
require_once('../_lib/util/util.php');
require_once('../_lib/util/accesos.php');
require_once('../_lib/util/ingreso.php');
require_once('../_lib/util/eficiencia.php');
header('Content-Type: application/json');

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, 'Método no permitido. Solo se acepta POST.');
}

// ⌛ Leer y validar JSON
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

if (empty($costura)) responder(422, 'Se requiere el parámetro "costura".');

if (empty($usuario)) responder(422, 'Se requiere el parámetro "usuario".');

if (!$idingreso) responder(422, 'Se requiere el parámetro "idingreso" válido.');

// ✏️ Si hay ciclo, solo actualiza
if ($ciclo > 0) {
    $sql = "UPDATE ciclo 
            SET tiempo_fin = NOW(), 
                tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio),
                usuario_modifica = '" . $usuario . "'
            WHERE ciclo_id = $ciclo";
    sc_exec_sql($sql);
}

// 💾 Insertar nuevo ciclo
$insert['costua_id'] = (int)$costura;
$insert['usuario_registra'] = $usuario;
$insert['usuario_nombre'] = $nombre;
$insert['ingreso_id'] = $idingreso;

$insertedId = guardar_ciclo($insert);

$rta = [];
$rta['ciclo'] = $insertedId;


$conn = DB::getConnection();

// ⌛ Tiempo transcurrido
$tiempo = tiempoXTurnoXColaborador(['ing.id' => $idingreso], $conn);

// Obtener eficiencia 🔎
$efi = calcularEficienciaOnline(['id' => $idingreso, 'tiempo' => $tiempo], $conn);
$eficiencia = $efi['eficiencia'] ?? 0;

$rta['eficiencia'] = $eficiencia;
$rta['cantidad'] = intval($efi['cantidad'] ?? 0);

DB::closeConnection();

if ($insertedId !== null) {
	$sql = "UPDATE ingreso SET fecha_modificacion = NOW(), eficiencia=$eficiencia WHERE id= $idingreso";
    sc_exec_sql($sql);
    responder(200, 'Ciclo insertado correctamente.', $rta);
} else {
    responder(500, 'Error al insertar el ciclo.');
}

// 💾 Función para guardar ciclo
function guardar_ciclo($insert): ?int {
    if (empty($insert)) return null;

    $sql_insert = formarSqlInsert("ciclo", $insert);
    sc_exec_sql($sql_insert);

    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) return (int){rs_id[0][0]};
    
    return null;
}