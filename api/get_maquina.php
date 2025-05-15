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

$deviceid = $_GET['deviceid'] ?? null;
$casesql = !empty($deviceid) ? "(case when mac='$deviceid' then 1 else 0 END)" : "0";

// Consulta MySQL mejorada y segura
$sql = "select maquina_id, codigo_interno, $casesql as selected from maquina where area_ubicacion_id = 51";

sc_lookup(rs_data_sybase, $sql);

$rta[] = [
    'id' => 0,
    'maquina' => 'No hay máquinas registradas. Crear una máquina.',
    'mac' => 1
];

if (!isset({rs_data_sybase}) || !is_array({rs_data_sybase})) {
    responder(500, 'Error al ejecutar la consulta.', $rta);
}

if (count({rs_data_sybase}) === 0) {
    responder(404, 'No se encontraron problemas.');
}

// ✅ Armar respuesta
$rta[] = [
    'id' => 0,
    'maquina' => 'Seleccionar una máquina.',
    'mac' => 0
];

foreach ({rs_data_sybase} as $row) {
    $rta[] = [
        'id' => $row[0],
        'maquina'  => mb_convert_encoding($row[1], 'UTF-8', 'CP850'),
        'mac' => $row[2]
    ];
}

// ✅ Enviar respuesta JSON
responder(200, 'Maquinas obtenidos correctamente.', $rta);