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

if ($idclico == 0) {
    responder(404, 'No existe ciclos.');
}

$eficiencia = "0.00 %";

// Calcular eficiencia total de toda las operaciones
if ($tciclos > 0) {
    $eficiencia = $valorobtenido * $tciclos * $tciclos;
    $eficiencia = number_format($eficiencia, 2, '.', '')." %";
}

$rta = ['eficiencia' => $eficiencia];

// ✅ Enviar respuesta JSON
responder(200, 'Eficiencia obtenida correctamente.', $rta);