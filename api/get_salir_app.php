<?php
require_once('../_lib/util/session_check.php');
require_once('../_lib/util/util.php');
header('Content-Type: application/json');

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, 'Método no permitido. Solo se acepta GET.');
}

$idingreso = intval($_SESSION['ingreso_id'] ?? 0);

if(!$idingreso)  responder(422, 'Se requiere el parámetro "ingreso.');

$sql = "SELECT ciclo_id, estado_id, ci.tiempo_trascurrido, ci.tiempo_fin
FROM ciclo ci
WHERE ci.usuario_registra = '".$usuario."' 
AND DATE(ci.fecha_creacion) = CURDATE()
ORDER BY ci.ciclo_id desc limit 1";

sc_lookup(rs_data_sybase, $sql);

$rta['ciclo_id'] = 0;

//Existe ultimo registro
if (!empty({rs_data_sybase}[0])) {
    $ciclo_id          = intval({rs_data_sybase}[0][0]);
    $estado_id         = intval({rs_data_sybase}[0][1]);
    $tiempo_fin        = trim({rs_data_sybase}[0][3]);

    // Validar que tiempo_fin sea null y estado_id sea 1
    if (empty($tiempo_fin) && $estado_id === 1) {
        $rta['ciclo_id'] = $ciclo_id;
    }
}

$conn = DB::getConnection();
//select TIMESTAMPDIFF(SECOND, horario_salida, NOW()) tiempo from ingreso where id=62
$cerrar = listarTablaSimple("ingreso", ['id' => $idingreso], $conn,['TIMESTAMPDIFF(SECOND, horario_salida, NOW()) tiempo']);
$cerrarsession = intval($cerrar[0]['tiempo'] ?? 0);

$rta['cerrarsession'] = $cerrarsession > 0 ? true : false;

DB::closeConnection();
// ✅ Enviar respuesta JSON
responder(200, 'Cerrar operacion.', $rta);