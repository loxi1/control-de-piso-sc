<?php
require_once('../_lib/util/session_check.php');
require_once('../_lib/util/funciones.php');
header('Content-Type: application/json');

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, 'Método no permitido. Solo se acepta GET.');
}

$compania = "02";
$empresa = "COFACO";
$op = $_GET['op'] ?? null;
$linea = $_GET['linea'] ?? null;

if(empty($op)) {
    responder(422, 'Se requiere el parámetro "op".');
}

if(empty($linea)) {
    responder(422, 'Se requiere el parámetro "linea".');
}

$select_sql_sybase = "
    SELECT SUM(cant) AS total
    FROM (
        select COUNT(*) as cant from ordencortetallasmov
        WHERE 
          ccmpn='$compania'
          AND nnope='$op'
          AND cod_equipo='$linea'
          AND fechamodifica >= CAST(GETDATE() AS DATE)
          AND fechamodifica < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
          AND empresa='$empresa'
        UNION ALL
        select COUNT(*) as cant from ordencortetallasmov 
        where 
          ccmpn='$compania' 
          AND nnope='$op' 
          AND cod_equipo='$linea' 
          AND fechSalCostExt2 >= CAST(GETDATE() AS DATE)
          AND fechSalCostExt2 < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
          AND empresa='$empresa'
    ) AS total_prendas
";

sc_lookup(rs_data_sybase, $select_sql_sybase);

if (!isset({rs_data_sybase}) || !is_array({rs_data_sybase})) {
    responder(500, 'Error al ejecutar la consulta.');
}

// ✅ Armar respuesta
$cant = {rs_data_sybase}[0][0] ?? 0;

// ✅ Enviar respuesta final
responder(200, 'Cantidad obtenido correctamente.', ['cant' => $cant]);