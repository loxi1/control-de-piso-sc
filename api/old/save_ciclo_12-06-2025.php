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

// ⌛ Leer y validar JSON
$input = file_get_contents('php://input');
$param = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    responder(400, 'JSON inválido.');
}

// ✅ Validar parámetros requeridos
$costura = $param['costura'] ?? null;
$ciclo   = (int)($param['ciclo'] ?? 0);
$usuario = $param['usuario'] ?? null;
$nombre = $param['nombre'] ?? null;
$idingreso = intval($param['idingreso'] ?? 0);

if (empty($costura)) {
    responder(422, 'Se requiere el parámetro "costura".');
}

if (empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

if ($idingreso <= 0) {
    responder(422, 'Se requiere el parámetro "idingreso" válido.');
}

$tiempo_total_min = getTiempoTranscurrido($idingreso);
// ✏️ Si hay ciclo, solo actualiza
if ($ciclo > 0) {
    $sql = "UPDATE ciclo 
            SET tiempo_fin = NOW(), 
                tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio),
                usuario_modifica = '" . $usuario . "', segundos=$tiempo_total_min
            WHERE ciclo_id = $ciclo";
    sc_exec_sql($sql);
}

// 💾 Insertar nuevo ciclo
$insert['costua_id'] = (int)$costura;
$insert['usuario_registra'] = "'" . $usuario . "'";
$insert['usuario_nombre'] = "'" . $nombre . "'";
$insert['ingreso_id'] = $idingreso;

$insertedId = guardar_ciclo($insert);

$rta = [];
$rta['ciclo'] = $insertedId;
// Obtener eficiencia 🔎
$efi = get_eficiencia($idingreso,$tiempo_total_min);
$eficiencia = !empty($efi['eficiencia']) ? number_format($efi['eficiencia'], 2, '.', '') : 0;
$rta['eficiencia'] = $eficiencia;
$rta['cantidad'] = intval($efi['cantidad'] ?? 0);

if ($insertedId !== null) {
	$sql = "UPDATE ingreso  SET fecha_modificacion = NOW(), eficiencia=$eficiencia WHERE id= $idingreso";
    sc_exec_sql($sql);
    responder(200, 'Ciclo insertado correctamente.', $rta);
} else {
    responder(500, 'Error al insertar el ciclo.');
}

// 💾 Función para guardar ciclo
function guardar_ciclo($insert): ?int {
    if (empty($insert)) {
        return null;
    }
    
    $columnas = implode(", ", array_keys($insert));
    $valores = implode(", ", $insert);   

    $sql_insert = "INSERT INTO ciclo ($columnas) VALUES ($valores)";
    sc_exec_sql($sql_insert);

    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) {
        return (int){rs_id[0][0]};
    }
    return null;
}

// 🔎 Obtener eficiencia
function get_eficiencia($idingreso, $tiempo_total_min): ?array {
    if ($tiempo_total_min === null || $tiempo_total_min <= 0) {
        return 0;
    }
    $sql = "SELECT
        co.operacion,
        co.tiempo_estimado_operacion,
        count(ci.ciclo_id) as cant
    FROM ciclo ci
    LEFT JOIN costura co ON co.costura_id = ci.costua_id
    WHERE ci.ingreso_id = $idingreso
	AND motivo_id = 0
    AND (ci.tiempo_trascurrido IS NOT NULL OR ci.tiempo_trascurrido <> '00:00:00')
    AND ci.estado_id = 1
    GROUP BY co.operacion, co.tiempo_estimado_operacion";
    sc_lookup(rs_data_sybase, $sql);

    $eficiencia = 0;
	$cantidad = 0;
    if (isset({rs_data_sybase}[0][0])) {
        foreach ({rs_data_sybase} as $row) {
            $tiempo_estimado = floatval($row[1]);
            $cant = intval($row[2]);

            if ($tiempo_total_min > 0) {
                $valorobtenido = $tiempo_estimado*$cant;
                $eficiencia += $valorobtenido;
            }
			$cantidad += $cant;
        }
    }
	$eficiencia = ($eficiencia == 0) ? 0 : ($eficiencia*100*60) / ($tiempo_total_min);
	$rta = ['eficiencia'=>$eficiencia, 'cantidad'=>$cantidad];
    return $rta;
}

function getTiempoTranscurrido($idingreso): ?float {
    $sql = "select TIMESTAMPDIFF(
		SECOND,
		fecha_permiso,
	NOW()) AS tiempo
	from permiso where ingreso_id=$idingreso and tipo='Ingreso' and estado=2";
	sc_lookup(rs_data_sybase, $sql);

    if (empty({rs_data_sybase}[0][0])) {
        return 0;
    }
    return {rs_data_sybase}[0][0];
}