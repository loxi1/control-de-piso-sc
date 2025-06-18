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

$sql = "SELECT motivo_id, UPPER(motivo) as motivo FROM motivo WHERE caracteristica_id = 4  AND visible = 2";
sc_lookup(rs_data_sybase, $sql);

if (!isset({rs_data_sybase}) || !is_array({rs_data_sybase})) {
    responder(500, 'Error al ejecutar la consulta.');
}

if (count({rs_data_sybase}) === 0) {
    responder(404, 'No se encontraron problemas.');
}

// ✅ Armar respuesta
$rta = [];

if (!empty({rs_data_sybase})) {
    $rta = array_map(
        fn($row) => ['id' => $row[0], 'motivo' => $row[1]],
        {rs_data_sybase}
    );
}


// ✅ Enviar respuesta JSON
responder(200, 'Colores obtenidos correctamente.', $rta);


