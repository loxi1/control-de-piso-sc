<?php
require_once('../_lib/util/session_check.php');
require_once('../_lib/util/funciones.php');
header('Content-Type: application/json');

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, 'Método no permitido. Solo se acepta GET.');
}

$usuario = $_GET['usuario'] ?? null;

if(empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

$sql = "select TIMESTAMPDIFF(SECOND, tiempo_inicio, NOW()) as tiempo, tiempo_inicio from ciclo where usuario_registra = '$usuario' and  DATE(fecha_creacion) = CURDATE() order by fecha_creacion asc limit 1";
sc_lookup(rs_data_sybase, $sql);

$tiempo_total_min = !empty({rs_data_sybase}[0][0]) ? floatval({rs_data_sybase}[0][0]/60) : 0; // Si no hay registros, usar 60 minutos como valor por defecto

if($tiempo_total_min <= 0) {
    responder(200, 'No hay ciclos registrados para el usuario en el día de hoy.', ['eficiencia' => 0]);
}

$sql = "SELECT
    co.operacion,
    co.tiempo_estimado_operacion
    count(ci.ciclo_id) as cant
FROM ciclo ci
LEFT JOIN costura co ON co.costura_id = ci.costua_id
WHERE ci.usuario_registra = '".$usuario."'
  AND DATE(ci.fecha_creacion) = CURDATE()
  AND (ci.tiempo_trascurrido IS NOT NULL OR ci.tiempo_trascurrido <> '00:00:00')
  AND ci.estado_id = 1
GROUP BY co.operacion, co.tiempo_estimado_operacion";

sc_lookup(rs_data_sybase, $sql);

$eficiencia = 0;

if (isset({rs_data_sybase}[0][0])) {
    foreach ({rs_data_sybase} as $row) {
        $tiempo_estimado = floatval($row[1]);
        $cant = intval($row[3]);

        if ($tiempo_total_min > 0) {
            $valorobtenido = ($tiempo_estimado*$cant*100);
            $eficiencia += $valorobtenido;
        }
    }
}

$eficiencia = $eficiencia == 0 ? 0 : number_format($eficiencia/$tiempo_total_min, 2, '.', '');

$rta = ['eficiencia' => $eficiencia];

// ✅ Enviar respuesta JSON
responder(200, 'Eficiencia obtenida correctamente.', $rta);