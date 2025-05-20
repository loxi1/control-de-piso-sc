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

if(empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

$sqlcostura = "";
if(isset($_GET['costura'])) {
	if(!empty($_GET['costura'])) {
		$sqlcostura = " AND costua_id=".intval($_GET['costura'])." ";
	}
}

$sql = "SELECT
    ci.usuario_registra,
    count(ci.ciclo_id) as cant
FROM ciclo ci
Where ci.usuario_registra = '".$usuario."'$sqlcostura
  AND DATE(ci.fecha_creacion) = CURDATE()
  AND ci.estado_id = 1
  AND ci.motivo_id > 0 AND ci.motivo_tipo = 50
GROUP BY ci.usuario_registra";

sc_lookup(rs_data_sybase, $sql);

$reprocesos = (isset({rs_data_sybase}[0][0])) ? intval({rs_data_sybase}[0][1]) : 0;

responder(200, 'Reprocesos obtenidos.', ['reprocesos' => $reprocesos]);