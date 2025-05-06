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

// Día de la semana (0=Domingo, ..., 6=Sábado)
$diasemana = date("w");

// Horas objetivo según día de semana
$hort = ($diasemana == 6) ? 5 : 8;

// Consulta MySQL mejorada y segura
$sql = "
    SELECT ROUND(SUM(TIME_TO_SEC(tiempo_transcurrido)) / 3600, 2) AS total_horas
    FROM (
        SELECT tiempo_transcurrido FROM evento_soporte
        WHERE DATE(fecha_creacion) = CURDATE() 
          AND tiempo_transcurrido IS NOT NULL 
          AND usuario_registra = '".$usuario."'
        UNION ALL
        SELECT tiempo_trascurrido AS tiempo_transcurrido FROM evento_normal
        WHERE DATE(fecha_creacion) = CURDATE() 
          AND tiempo_trascurrido IS NOT NULL 
          AND usuario_registra = '".$usuario."'
    ) AS tiempos
";

// Ejecutar consulta en ScriptCase
sc_lookup(rs_data_sybase, $sql);

$timp = 0.0;
if (isset({rs_data_sybase}[0][0])) {
    $timp = floatval({rs_data_sybase}[0][0]);
}

// Calcular porcentaje trabajado
$porcentaje = ($hort > 0) ? round(($timp * 100) / $hort, 2) : 0;

$rta = [
    'hort' => $hort,
    'timp' => $timp . ' hrs',
    'pimp' => $porcentaje . ' %'
];

// ✅ Enviar respuesta JSON
responder(200, 'Datos obtenidos correctamente.', $rta);