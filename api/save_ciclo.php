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
$usuario = $_SESSION['usr_login'] ?? null;
$nombre = $_SESSION['usr_name'] ?? null;

$idingreso = intval($_SESSION['ingreso_id'] ?? 0);

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
$insertedId = guardar_ciclo([
	'costua_id' => $costura, 
	'usuario_registra' => $usuario, 
	'usuario_nombre' => $nombre, 
	'ingreso_id' => $idingreso
]);

$rta = [];
$rta['ciclo'] = $insertedId;


$conn = DB::getConnection();

// ⌛ Tiempo transcurrido
$tiempo = tiempoXTurnoXColaborador(['ing.id' => $idingreso], $conn);

// Obtener eficiencia 🔎
$efi = calcularEficienciaOnline(['id' => $idingreso, 'tiempo' => $tiempo], $conn);
$eficiencia = $efi['eficiencia'] ?? 0;
$cantidad = intval($efi['cantidad'] ?? 0);
$eficiencia = $eficiencia > 0 ? $eficiencia : 0;
$rta['eficiencia'] = $eficiencia;
$rta['cantidad'] = $cantidad;
$rta['tiempo'] = $tiempo;

$hactual = time();
$horaLimiteRefreigerio = $_SESSION['hora_limite_refrigerio'] ?? NULL;
$refrigerioAplicado = $_SESSION["refrigerio_aplicado"];
$totalMinutos = intval($_SESSION['minutos_almuerzo'] ?? 0);

$update = [];
//Registrar almuerzo
if ($horaLimiteRefreigerio  <  $hactual && $refrigerioAplicado == 1 && $totalMinutos > 0) {
    $update['refrigerio_aplicado'] = 1;
	$fechaPermiso = date("Y-m-d H:i:s", $horaLimiteRefreigerio);
    $datetime = new DateTime($fechaPermiso);
    $datetime->sub(new DateInterval('PT' . $totalMinutos . 'M'));
    $fechaCreacion = $datetime->format('Y-m-d H:i:s');

    saveTable("permiso",[
        'codigo'            => $usuario,
        'fecha_permiso'     => $fechaPermiso,
        'fecha_creacion'    => $fechaCreacion,
        'ingreso_id'        => $idingreso,
        'con_permiso'       => 2,   			// Si tiene permiso
        'tipo'              => 2,   		   // Permiso
        'tipo_permiso'      => 5,   		  // Refrigerio
        'usuario_creacion'  => "SISTEMA",	 // Usuario de sistema
        'estado'            => 2,   		// Activo y aplicado
        'fecha_modificacion' => "NOW()"	   // Fecha y hora actual de modificacion
    ] , $conn);   

    $_SESSION['refrigerio_aplicado'] = 2;
}

DB::closeConnection();

if ($insertedId !== null) {
	$update['fecha_modificacion'] = "NOW()";
    $update['eficiencia'] = $eficiencia;
    $update['cantidad'] = $cantidad;
    $sql = formarSqlUpdate("ingreso", $update, "id=$idingreso");
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