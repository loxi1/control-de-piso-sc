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

$sql = "select codigo_motivo, motivo, tipo_actividad_id from motivo where caracteristica_id=5 and codigo_motivo<>0 order by orden asc;";
sc_lookup(rs_data_sybase, $sql);

if (!isset({rs_data_sybase}) || !is_array({rs_data_sybase})) {
    responder(500, 'Error al ejecutar la consulta.');
}

if (count({rs_data_sybase}) === 0) {
    responder(404, 'No se encontraron eventos.');
}

// ✅ Armar respuesta
$rta = [];
foreach ({rs_data_sybase} as $row) {
    $rta[] = [
        'id' => $row[0],
        'motivo'  => mb_convert_encoding($row[1], 'UTF-8', 'CP850'),
        'tipo' => $row[2]
    ];
}

// ✅ Enviar respuesta JSON
responder(200, 'Eventos obtenidos correctamente.', $rta);