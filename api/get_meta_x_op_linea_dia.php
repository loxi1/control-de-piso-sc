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

$compania = "02";
$op = $_GET['op'] ?? null;
$linea = $_GET['linea'] ?? null;
$empesa = "COFACO";
$area = "SALIDA DE COSTURA";

if(empty($op)) {
    responder(422, 'Se requiere el parámetro "op".');
}

if(empty($linea)) {
    responder(422, 'Se requiere el parámetro "linea".');
}

$select_sql_sybase = "SELECT TOP 1 cantmeta
FROM meta_linea_areas
WHERE ccmpn = '02'
AND nnope = '$op'
AND fecha >= CAST(GETDATE() AS DATE)
AND fecha < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
AND linea = '$linea'
AND empresa='$empesa'
AND area='$area'
";

sc_lookup(rs_data_sybase, $select_sql_sybase);

if (!isset({rs_data_sybase}) || !is_array({rs_data_sybase})) {
    responder(500, 'Error al ejecutar la consulta.');
}

if (count({rs_data_sybase}) === 0) {
    responder(404, 'No se encontraron datos para la OP ingresada.');
}

// ✅ Armar respuesta
$meta = {rs_data_sybase}[0][0] ?? 0;

// ✅ Enviar respuesta final
responder(200, 'Meta obtenido correctamente.', ['meta' => $meta]);