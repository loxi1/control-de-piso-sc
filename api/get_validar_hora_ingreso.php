<?php
session_start();
$ruta_util = sc_url_library("prj", "mantenimiento_control_piso", "php/util.php");
$ruta_acce = sc_url_library("prj", "mantenimiento_control_piso", "php/accesos.php");
$ruta_ingr = sc_url_library("prj", "mantenimiento_control_piso", "php/ingreso.php");
$ruta_efic = sc_url_library("prj", "mantenimiento_control_piso", "php/eficiencia.php");
$ruta_repr = sc_url_library("prj", "mantenimiento_control_piso", "php/reproceso.php");

require_once($ruta_util);
require_once($ruta_acce);
require_once($ruta_ingr);
require_once($ruta_efic);
require_once($ruta_repr);

setApiHeaders();

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

// ✅ Iniciar conexión
$conn = DB::getConnection();

$whereData = blindValueWhereCondiciones(['ing.codigo_operario' => $codigo]);
if (empty($whereData)) {
    responder(422, 'No se encontraron datos para el código proporcionado.');
}

$rta_existe = verificarRegistroIngreso($whereData, $conn);

$msn = "Turno ya activo.";
$rta = [];
if(!empty($rta_existe['id'])) {
    $id = $rta_existe['id'];
    $turno_id = intval($rta_existe['turno_id']);
    $horario_ingreso = strtotime($rta_existe['horario_ingreso'] ?? '');
    $horario_salida = strtotime($rta_existe['salario_salida'] ?? '');
    $fecha_actual = strtotime($rta_existe['fecha_actual']);
    $horario_maximo = strtotime($rta_existe['horario_maximo'] ?? '');
    $horario_minimo = strtotime($rta_existe['horario_minimo'] ?? '');
    $tiempo_trascurrido = intval($rta_existe['tiempo_trascurrido'] ?? 0);
    $estado = intval($rta_existe['estado'] ?? 0);
    $hora_limite_refrigerio = !empty($rta_existe['hora_limite_refrigerio']) ? strtotime($rta_existe['hora_limite_refrigerio']) : null;
    $refrigerio_aplicado = intval($rta_existe['refrigerio_aplicado'] ?? 0);
    $cerro_cession = intval($rta_existe['cerro_cession'] ?? 0);
    $minutos_almuerzo = intval($rta_existe['minutos_almuerzo'] ?? 0);
    
    if($fecha_actual > $horario_minimo && $fecha_actual < $horario_maximo) {
        // Existe registro de ingreso y no cerro        
        updateTable("ingreso", ['fecha_modificacion' => 'NOW()'], ['id' => $id], $conn);

        if(!$cerro_cession && $turno == $turno_id) {
            $cant = existencia_ciclos([
                'usuario_registra' => $codigo,
                'tiempo_inicio' => $rta_existe['horario_minimo'],
                'tiempo_fin' => $rta_existe['horario_minimo']
            ], $conn);

            $rta['code'] = ($cant == 0 && $tiempo_trascurrido>0) ? 2 : 1; // Alerta permisos. 2 Muesra, 1 No muestra
            $rta['id'] = $id;
            $rta['titulo'] = $rta['code'] == 2 ? "¿Tiene Permiso?" : "";
            $rta['descripcion'] = $rta['code'] == 2 ? "!Estas ingresando tarde¡ Turno: " . date('h:i A', $horario_ingreso) . " - " . date('h:i A', $horario_salida) : "";
            $rta['horario_ingreso'] = $rta_existe['horario_ingreso'];

            $refrigerio_aplicado = $refrigerio_aplicado == 0 ? 1 : 2; // Si no se aplicó refrigerio, se aplica
            $refrigerio_aplicado = ($minutos_almuerzo > 0 && !empty($hora_limite_refrigerio)) ? $refrigerio_aplicado : 1;

            $_SESSION["ingreso_id"] = $id;
            $_SESSION["hora_limite_refrigerio"] = $hora_limite_refrigerio;
            $_SESSION["refrigerio_aplicado"] = $refrigerio_aplicado;
            $_SESSION["minutos_almuerzo"] = $minutos_almuerzo;

            responder(200, $msn, $rta);
        }
    }
	
	if($estado == 1) { // Registro anterior que no fue cerrado.
        //Cerrar turno anterior
        cerrarTurno($id, $codigo, $conn);
        $msn = "Turno cerrado correctamente.";
    } else {
        $msn = "Turno ya cerrado.";
    }
}

$empresa_id = $_SESSION['empresa_id'] ?? null;

$whereData = blindValueWhereCondiciones(['h.turno_id' => $turno, 'h.empresa_id' => $empresa_id]);
if (empty($whereData)) {
    responder(422, 'No se encontraron datos para el turno proporcionado.');
}

$allTurno = getTurno($whereData, $conn);

if (isset($allTurno[0][0])) {
    $data = $allTurno[0];
    $dataTurno = $allTurno[0];
    //Registrar ingreso
    $rta = registrarIngreso($dataTurno, $codigo, $turno, $conn);
}

responder(200, $msn, $rta);