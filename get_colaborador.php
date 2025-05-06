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

$id = $_GET['id'] ?? null;

$select_sql_sybase = "select mecanico_asignado from evento_soporte where evento_soporte_id=".$id;
sc_lookup(rs_data_sybase, $select_sql_sybase);

if (!isset({rs_data_sybase}) || !is_array({rs_data_sybase})) {
    responder(500, 'Error al ejecutar la consulta.');
}

if (count({rs_data_sybase}) === 0) {
    responder(404, 'No se encontraron datos para la OP ingresada.');
}

// ✅ Armar respuesta
$rta = [];
foreach ({rs_data_sybase} as $row) {
    $rta = [
        'mecanico' => $row[0]
    ];
}

// ✅ Enviar respuesta JSON
responder(200, 'OP correcta.', $rta);