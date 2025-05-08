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

// ✅ Enviar respuesta JSON
responder(200, 'Cerrar operacion.', $rta);