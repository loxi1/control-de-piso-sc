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

$codigo = $param['codigo'] ?? null;
if (empty($codigo)) {
    responder(422, 'Se requiere el parámetro "codigo".');
}

$existe = "select id, turno_id, horario_ingreso, horario_salida, now() fecha_actual,
horario_maximo, TIMESTAMPDIFF(SECOND, horario_ingreso, NOW()) AS tiempo_trascurrido 
from ingreso 
where codigo_operario='$codigo' and date(horario_ingreso)<=CURDATE() and date(horario_ingreso)>=DATE_ADD(CURDATE(), INTERVAL -1 DAY) 
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
    $tiempo_trascurrido = intval($info[6] ?? 0);
    
    if($fecha_actual >= $horario_ingreso && $fecha_actual < $horario_maximo) {
        $sql = "UPDATE ingreso  SET fecha_modificacion = NOW() WHERE id = $id";
        sc_exec_sql($sql);

        $cant = existencia_ciclos($horario_ingreso, $horario_maximo, $codigo);

        $rta['code'] = ($cant == 0 && $tiempo_trascurrido>0) ? 2 : 1; // Alerta permisos. 2 Muesra, 1 No muestra
        $rta['id'] = $id;
        $rta['titulo'] = "";
        $rta['descripcion'] = "";
        $rta['horario_ingreso'] = $info[2];
        if($rta['code'] == 2) {
            $rta['titulo'] = "¿Tiene Permiso?";
            $rta['descripcion'] = "!Estas ingresando tarde¡ Turno: ".date('h:i A', $horario_ingreso)." - ".date('h:i A', $horario_salida);
        }

        responder(200, $msn, $rta);
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
	DAYOFWEEK(ingreso) dia,
  (select TIMESTAMP(date(horarios.salida), TIME(tur.hora_inicio)) from turno_horario tur where tur.turno_id != horarios.turno_id and tur.numero_dia=horarios.numero_dia) horario_maximo
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
  FROM turno_horario
) AS horarios
where ingreso<=now() and salida>now() and DAYOFWEEK(ingreso) = numero_dia;
";

sc_lookup(rs_data_sybase, $sql);

if (isset({rs_data_sybase}[0][0])) {
    $data = {rs_data_sybase}[0];
    $inset['turno_id'] = intval($data[2]);
    $inset['codigo_operario'] = "'$codigo'";
    $inset['minutos_almuerzo'] = intval($data[3]);
    $inset['horario_ingreso'] = "'$data[4]'";
    $inset['horario_salida'] = "'$data[5]'";
    $inset['dia_de_la_semana'] = intval($data[7]);
    $inset['horario_maximo'] = "'$data[8]'";
    $tiempo_trascurrido = intval($info[6] ?? 0);
    $fecha_actual = strtotime($info[9]);
    $horario_ingreso = strtotime($info[4]);
    $horario_maximo = strtotime($info[8]);
    
    $id = guardar_ingreso_horario($inset);

    if(empty($id)) {
        responder(500, 'No inserto horario.', $rta);
    }

    $cant = existencia_ciclos($horario_ingreso, $horario_maximo, $codigo);

    $rta['code'] = ($cant == 0 && $tiempo_trascurrido>0) ? 2 : 1; // Alerta permisos. 2 Muesra, 1 No muestra
    $rta['id'] = $id;
    $rta['titulo'] = "";
    $rta['descripcion'] = "";
    $rta['horario_ingreso'] = $info[4];
    if($rta['code'] == 2) {
        $rta['titulo'] = "¿Tiene Permiso?";
        $rta['descripcion'] = "!Estas ingresando tarde¡ Turno: ".date('h:i A', $horario_ingreso)." - ".date('h:i A', $horario_salida);
    }

    $msn = "Usuario ingresado correctamente.";
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

function existencia_ciclos($finicio, $fin, $codigo): int {
    $sqlciclos = "SELECT COUNT(*) AS cantidad
        FROM ciclo
        WHERE estado_id = 1
		AND usuario_registra='$codigo'
        AND tiempo_inicio >= '$finicio'
        AND tiempo_fin <  '$fin'";

    sc_lookup(rs_cant_ciclos, $sqlciclos);
    return intval({rs_cant_ciclos}[0][0] ?? 0);
}