<?php
require_once('../_lib/util/session_check.php');
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

$usuario = $_GET['usuario'] ?? null;
$tiempo = $_GET['tiempo'] ?? null;

if(empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

if(empty($tiempo)) {
    responder(422, 'Se requiere el parámetro "tiempo".');
}

$sql = "select ciclo_id from ciclo where usuario_registra='".$usuario."' and date(fecha_creacion) = CURDATE() and estado_id=1 order by ciclo_id asc limit 1";

// Ejecutar consulta en ScriptCase
sc_lookup(rs_data_sybase, $sql);
//Obtener el primer ciclo activo
$idclico = 0;
if (isset({rs_data_sybase}[0][0])) {
    $idclico = intval({rs_data_sybase}[0][0]);
}

if ($idclico == 0) {
    responder(404, 'No se encontraron ciclos activos para el usuario.');
}

// Obtener el tiempo transcurrido en minutos
$sql = "select ROUND(TIMESTAMPDIFF(SECOND, tiempo_inicio, NOW())/60,2) AS MINUTOS from ciclo where ciclo_id=$idclico and estado_id=1";
sc_lookup(rs_data_sybase, $sql);

$tmin = 0;
if (isset({rs_data_sybase}[0][0])) {
    $tmin = floatval({rs_data_sybase}[0][0]);
}

if ($idclico == 0) {
    responder(404, 'No existe tiempo.');
}

$valorobtenido = $tmin/$tiempo;

//Obtener la cantidad de ciclos activos
$sql = "select count(ciclo_id) as cant from ciclo where usuario_registra='".$usuario."' and date(fecha_creacion) = CURDATE() and estado_id=1";
sc_lookup(rs_data_sybase, $sql);

$tciclos = 0;
if (isset({rs_data_sybase}[0][0])) {
    $tciclos = intval({rs_data_sybase}[0][0]);
}

$sql = "select co.operacion
from ciclo ci
left join costura co on co.costura_id=ci.costua_id
where ci.usuario_registra='".$usuario."' and date(ci.fecha_creacion) = CURDATE() and ci.estado_id=1
GROUP BY co.operacion";
sc_lookup(rs_data_sybase, $sql);

$operaciones = 0;
if (isset({rs_data_sybase}[0][0])) {
    $operaciones = count({rs_data_sybase});
}

if ($idclico == 0) {
    responder(404, 'No existe ciclos.');
}

$eficiencia = "0.00 %";

// Calcular eficiencia total de toda las operaciones
if ($tciclos > 0 && $operaciones > 0) {
    $eficiencia = ($tciclos / $valorobtenido) * $operaciones * 100;
    $eficiencia = number_format($eficiencia, 2, '.', '')." %";
}

$rta = ['eficiencia' => $eficiencia];

// ✅ Enviar respuesta JSON
responder(200, 'Eficiencia obtenida correctamente.', $rta);

/*$sql = "SELECT
    co.operacion,
    co.tiempo_estimado_operacion,
    ROUND(SUM(TIME_TO_SEC(ci.tiempo_trascurrido)) / 60, 2) AS tiempo_total_min
FROM ciclo ci
LEFT JOIN costura co ON co.costura_id = ci.costua_id
WHERE ci.usuario_registra = '".$usuario."'
  AND DATE(ci.fecha_creacion) = CURDATE()
  AND ci.estado_id = 1
  AND (ci.motivo_id IS NULL OR ci.motivo_id <= 0)
  AND ci.tiempo_trascurrido IS NOT NULL
  AND ci.tiempo_trascurrido != '00:00:00'
GROUP BY co.operacion, co.tiempo_estimado_operacion";*/