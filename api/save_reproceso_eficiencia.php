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
$costura = $_GET['costura'] ?? null;
$nombre = $_GET['nombre'] ?? null;

if(empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

if(empty($costura)) {
    responder(422, 'Se requiere el parámetro "costura".');
}

if(empty($nombre)) {
    responder(422, 'Se requiere el parámetro "nombre".');
}

$sql = "SELECT
    co.operacion,
    co.tiempo_estimado_operacion,
    ROUND(SUM(TIME_TO_SEC(ci.tiempo_trascurrido)) / 60, 2) AS tiempo_total_min,
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
        $tiempo_total_min = floatval($row[2]);
        $tiempo_estimado = floatval($row[1]);
        $cant = intval($row[3]);

        if ($tiempo_total_min > 0) {
            $valorobtenido = ($tiempo_estimado*$cant*100)/$tiempo_total_min;
            $eficiencia += $valorobtenido;
        }
    }
}
$efi = $eficiencia == 0 ? 0 : number_format($eficiencia, 2, '.', '');

$sqlcostura = "";
if(isset($_GET['costura'])) {
	if(!empty($_GET['costura'])) {
		$sqlcostura = " ".intval($_GET['costura'])." ";
	}
}

$sql = "SELECT
    ci.usuario_registra,
    count(ci.ciclo_id) as cant
FROM ciclo ci
Where ci.usuario_registra = '".$usuario."' AND costua_id=".$costura."
  AND DATE(ci.fecha_creacion) = CURDATE()
  AND ci.estado_id = 1
  AND ci.motivo_id > 0 AND ci.motivo_tipo = 50
GROUP BY ci.usuario_registra";

sc_lookup(rs_data_sybase, $sql);

$reproceso = (isset({rs_data_sybase}[0][0])) ? intval({rs_data_sybase}[0][1]) : 0;
$upd['usuario_modifica'] = $nombre;

$sql = "update costura set reproceso=$reproceso, operario_meta=$efi, usuario_modifica='$usuario'  where costura_id=$costura";
sc_exec_sql($sql);

responder(200, 'Eficiencia obtenida correctamente.', ["rta"=>1]);