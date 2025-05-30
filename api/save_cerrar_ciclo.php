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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, 'Método no permitido. Solo se acepta POST.');
}

// ✅ Leer y validar JSON
$input = file_get_contents('php://input');
$param = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    responder(400, 'JSON inválido.');
}

// ✅ Validar parámetros requeridos
$ciclo   = (int)($param['ciclo'] ?? 0);
$usuario   = ($param['usuario'] ?? 0);
$estado = !empty($param['estado']) ? intval($param['estado']) : null;
$tipo = !empty($param['estado']) ? 1 : null;
$tipo_ = !empty($param['tipo']) ? $param['tipo'] : null;

if (empty($ciclo)) {
    responder(422, 'Se requiere el parámetro "ciclo".');
}

if (empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

$set = [];

if ($estado !== null) {
    $estado--;
    $set[] = "estado_id = $estado";
}

// Campos fijos
$set[] = "tiempo_fin = NOW()";
$set[] = "tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio)";
$set[] = "usuario_modifica = '" . $usuario . "'";


// Armar sentencia SQL
$txt_set = implode(", ", $set);
$sql = "UPDATE ciclo SET $txt_set WHERE ciclo_id = $ciclo";
sc_exec_sql($sql);

/**ACTUALIZAR EFICIENCIA, META Y REPROCESO X COSTURA  */


responder(200, 'Ciclo insertado correctamente.', ['ciclo' => $ciclo]);

function get_eficiencia($usuario): ?float {
	$tiempo_total_min = 516;
    $sql = "SELECT
        co.operacion,
        co.tiempo_estimado_operacion,
        count(ci.ciclo_id) as cant
    FROM ciclo ci
    LEFT JOIN costura co ON co.costura_id = ci.costua_id
    WHERE ci.usuario_registra = '".$usuario."'
    AND DATE(ci.fecha_creacion) = CURDATE()
	AND motivo_id = 0
    AND (ci.tiempo_trascurrido IS NOT NULL OR ci.tiempo_trascurrido <> '00:00:00')
    AND ci.estado_id = 1
    GROUP BY co.operacion, co.tiempo_estimado_operacion";

    sc_lookup(rs_data_sybase, $sql);

    $eficiencia = 0;

    if (isset({rs_data_sybase}[0][0])) {
        foreach ({rs_data_sybase} as $row) {
            $tiempo_estimado = floatval($row[1]);
            $cant = intval($row[2]);

            if ($tiempo_total_min > 0) {
                $valorobtenido = $tiempo_estimado*$cant*100;
                $eficiencia += $valorobtenido;
            }
        }
    }

    return $eficiencia == 0 ? 0 : $eficiencia / $tiempo_total_min;
}