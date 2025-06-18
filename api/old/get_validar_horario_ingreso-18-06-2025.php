<?php
session_start();
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

$codigo = $param['codigo'] ?? null;
$turno = $param['turno'] ?? null;

if (empty($codigo)) {
    responder(422, 'Se requiere el parámetro "codigo".');
}

if (empty($turno)) {
    responder(422, 'Se requiere el parámetro "turno".');
}


$existe = "select id, turno_id, horario_ingreso, horario_salida, now() fecha_actual,
horario_maximo, TIMESTAMPDIFF(SECOND, horario_ingreso, NOW()) AS tiempo_trascurrido, horario_minimo, estado, hora_limite_refrigerio, refrigerio_aplicado 
from ingreso 
where codigo_operario='$codigo' and turno_id=$turno
ORDER BY horario_ingreso desc
limit 1";

sc_lookup(rs_data_sybase, $existe);
$rta_existe = {rs_data_sybase};

$msn = "Turno ya activo.";
$rta = [];
if(!empty($rta_existe[0][0])) {
    $info = $rta_existe[0];
    $id = $info[0] ?? null;
    $turno_id = intval($info[1]);
    $horario_ingreso = strtotime($info[2]);
    $horario_salida = strtotime($info[3]);
    $fecha_actual = strtotime($info[4]);
    $horario_maximo = strtotime($info[5]);
    $horario_minimo = strtotime($info[7]);
    $tiempo_trascurrido = intval($info[6] ?? 0);
    $estado = intval($info[8] ?? 0);
	
	$hora_limite_refrigerio = $info[9] ?? '';
    $hora_limite_refrigerio = isset($info[9]) && $info[9] !== '' ? strtotime($info[9]) : null;

    $refrigerio_aplicado = intval($info[10] ?? 0);	
    
    if($fecha_actual > $horario_minimo && $fecha_actual < $horario_maximo) {
        $sql = "UPDATE ingreso  SET fecha_modificacion = NOW() WHERE id = $id";
        sc_exec_sql($sql);

        $cant = existencia_ciclos($horario_minimo, $horario_maximo, $codigo);

        $rta['code'] = ($cant == 0 && $tiempo_trascurrido>0) ? 2 : 1; // Alerta permisos. 2 Muesra, 1 No muestra
        $rta['id'] = $id;
        $rta['titulo'] = "";
        $rta['descripcion'] = "";
        $rta['horario_ingreso'] = $info[2];
        if($rta['code'] == 2) {
            $rta['titulo'] = "¿Tiene Permiso?";
            $rta['descripcion'] = "!Estas ingresando tarde¡ Turno: ".date('h:i A', $horario_ingreso)." - ".date('h:i A', $horario_salida);
        }
		
		$_SESSION["ingreso_id"] = $id;
		$_SESSION["hora_limite_refrigerio"] = $hora_limite_refrigerio;    
		$_SESSION["refrigerio_aplicado"] = $refrigerio_aplicado;
		
        responder(200, $msn, $rta);
    } else {
        // Registro anterior que no fue cerrado. Se debe calcular la eficiencia
        if($estado == 1) {
            $sql = "UPDATE ingreso SET estado = 2, fecha_modificacion = NOW() WHERE id = $id";
            sc_exec_sql($sql);
            $msn = "Turno cerrado correctamente.";
        } else {
            $msn = "Turno ya cerrado.";
        }
    }
}

$sql = "SELECT
    id,
    numero_dia,
    turno_id,
    considerar_almuerzo_min,
    ingreso AS horario_ingreso,
    salida AS horario_salida,
    TIMESTAMPDIFF(SECOND, ingreso, NOW()) AS tiempo_trascurrido,
    DAYOFWEEK(ingreso) AS dia,
    -- Hora fin del turno anterior (turno diferente, dia anterior)
    (
        SELECT TIMESTAMP(
        DATE(horarios.ingreso), 
        TIME(tur.hora_fin)
        )
        FROM turno_horario tur
        WHERE tur.turno_id != horarios.turno_id
        AND tur.numero_dia = (
            CASE 
                WHEN horarios.numero_dia = 7 AND tur.turno_id = 2 THEN
                    CASE WHEN horarios.numero_dia - 1 = 0 THEN 7 ELSE horarios.numero_dia - 1 END
                ELSE horarios.numero_dia
            END
        )
        LIMIT 1
    ) AS horario_minimo,

    -- Hora inicio del otro turno del mismo día
    (
        SELECT TIMESTAMP(DATE(horarios.salida), TIME(tur.hora_inicio))
        FROM turno_horario tur
        WHERE tur.turno_id != horarios.turno_id
        AND tur.numero_dia =  (
            CASE 
                WHEN horarios.numero_dia = 7 AND tur.turno_id = 2 THEN horarios.numero_dia
                ELSE CASE WHEN horarios.numero_dia + 1 > 7 THEN 1 ELSE horarios.numero_dia + 1 END
            END
        )
        LIMIT 1
    ) AS horario_maximo,
    ,hora_limite_almuerzo

    FROM (
        SELECT
            id,
            numero_dia,
            turno_id,
            considerar_almuerzo_min,
            TIMESTAMP(CURDATE(), TIME(hora_inicio)) AS ingreso,
            TIMESTAMP(
            DATE_ADD(CURDATE(), INTERVAL DATEDIFF(hora_fin, hora_inicio) DAY),
            TIME(hora_fin)
            ) AS salida
            ,hora_limite_almuerzo
        FROM turno_horario
    ) AS horarios
    WHERE 
        DAYOFWEEK(ingreso) = numero_dia and turno_id=$turno
    HAVING 
    NOW() BETWEEN horario_minimo AND horario_maximo";


sc_lookup(rs_data_sybase, $sql);

if (isset({rs_data_sybase}[0][0])) {
    $data = {rs_data_sybase}[0];
    $inset['turno_id'] = intval($data[2]);
    $inset['codigo_operario'] = "'$codigo'";
    $inset['minutos_almuerzo'] = intval($data[3]);
    $inset['horario_ingreso'] = "'$data[4]'";
    $inset['horario_salida'] = "'$data[5]'";
    $inset['dia_de_la_semana'] = intval($data[7]);
    $inset['horario_minimo'] = "'$data[8]'";
    $inset['horario_maximo'] = "'$data[9]'";
	$inset['hora_limite_refrigerio'] = "'$data[10]'";
    $inset['refrigerio_aplicado'] = 0;
	
    $tiempo_trascurrido = intval($data[6] ?? 0);
    $fecha_actual = strtotime($data[9]);
    $horario_ingreso = strtotime($data[4]);
    $horario_salida = strtotime($data[5]);
    $horario_minimo = strtotime($data[8]);
    $horario_maximo = strtotime($data[9]);
	
	$hora_limite_refrigerio = $data[10] ?? '';
    $hora_limite_refrigerio = isset($data[10]) && $data[10] !== '' ? strtotime($data[10]) : null;

    $refrigerio_aplicado = $inset['refrigerio_aplicado'];
	$insert['fecha'] = date("Y-m-d H:i:s", $horario_ingreso);
    
    $id = guardar_ingreso_horario($inset);

    if(empty($id)) {
        responder(500, 'No inserto horario.', $rta);
    }

    $cant = existencia_ciclos($horario_minimo, $horario_maximo, $codigo);
    
    $rta['code'] = ($cant == 0 && $tiempo_trascurrido>0) ? 2 : 1; // Alerta permisos. 2 Muesra, 1 No muestra
    $rta['id'] = $id;
    $rta['titulo'] = "";
    $rta['descripcion'] = "";
    $rta['horario_ingreso'] = $data[4];
    if($rta['code'] == 2) {
        $rta['titulo'] = "¿Tiene Permiso?";
        $rta['descripcion'] = "!Estas ingresando tarde¡ Turno: ".date('h:i A', $horario_ingreso)." - ".date('h:i A', $horario_salida);
    }
	
    $msn = "Usuario ingresado correctamente.";
	$_SESSION["ingreso_id"] = $id;
    $_SESSION["hora_limite_refrigerio"] = $hora_limite_refrigerio;    
    $_SESSION["refrigerio_aplicado"] = $refrigerio_aplicado;
}

responder(200, $msn, $rta);

// 💾 Función para guardar ingreso diario
function guardar_ingreso_horario($insert): ?int {
    if (empty($insert)) {
        return null;
    }
    
    $columnas = implode(", ", array_keys($insert));
    $valores = implode(", ", $insert);   

    $sql_insert = "INSERT INTO ingreso ($columnas) VALUES ($valores)";
    sc_exec_sql($sql_insert);

    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) {
        return (int){rs_id[0][0]};
    }
    return null;
}

function existencia_ciclos($fmin, $fmax, $codigo): int {
    if (empty($fmin) || empty($fmax) || empty($codigo)) {
        return 0;
    }
    $fmmin = date('Y-m-d H:i:s', $fmin);
    $fmmax = date('Y-m-d H:i:s', $fmax);

    $sqlciclos = "SELECT COUNT(*) AS cantidad
        FROM ciclo
        WHERE estado_id = 1
		AND usuario_registra='$codigo'
        AND tiempo_inicio > '$fmmin'
        AND tiempo_fin <  '$fmmax'";

    sc_lookup(rs_cant_ciclos, $sqlciclos);
    return intval({rs_cant_ciclos}[0][0] ?? 0);
}