<?php
require_once('../_lib/util/session_check.php');
require_once('../_lib/util/funciones.php');
header('Content-Type: application/json');

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, 'Método no permitido. Solo se acepta GET.');
}

$costura = $_GET['costura'] ?? null;
$op = $_GET['op'] ?? null;
$linea = $_GET['linea'] ?? null;
$usuario = $_GET['usuario'] ?? null;
$empresa = "COFACO";
$compania = "02";
$area = "SALIDA DE COSTURA";

if (empty($costura) || !ctype_digit($costura)) {
    responder(422, 'Se requiere el parámetro "costura" numérico.');
}

if(empty($op)) {
    responder(422, 'Se requiere el parámetro "op".');
}

if(empty($linea)) {
    responder(422, 'Se requiere el parámetro "linea".');
}

if(empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

try {
    //Conexion a la base de datos
    $conn = conectar_sybase();

    // ==================================
    // 🔎 Obtener eficiencia de la meta
    // ===============================
    $sql = "SELECT SUM(cant) AS total
        FROM (
            SELECT COUNT(*) as cant FROM ordencortetallasmov
            WHERE ccmpn=:ccmpn AND nnope=:nnope AND cod_equipo=:cod_equipo
            AND fechamodifica >= CAST(GETDATE() AS DATE)
            AND fechamodifica < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
            AND empresa=:empresa
            UNION ALL
            SELECT COUNT(*) as cant FROM ordencortetallasmov 
            WHERE ccmpn=:ccmpn AND nnope=:nnope AND cod_equipo=:cod_equipo
            AND fechSalCostExt2 >= CAST(GETDATE() AS DATE)
            AND fechSalCostExt2 < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
            AND empresa=:empresa
        ) AS total_prendas";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nnope', $op);
    $stmt->bindParam(':cod_equipo', $linea);
    $stmt->bindParam(':ccmpn', $compania);
    $stmt->bindParam(':empresa', $empresa); // doble uso en tu SQL

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $canttimbradas = $result['total'] ?? 0;

    $canttimbradas = (int)$canttimbradas;
    if($canttimbradas == 0) {
        responder(200, 'No hay prendas timbradas.');
    }
    
    //Meta del día
    $sqs = "SELECT TOP 1 cantmeta
        FROM meta_linea_areas
        WHERE ccmpn=:ccmpn
            AND nnope=:nnope
            AND fecha >= CAST(GETDATE() AS DATE)
            AND fecha < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
            AND linea=:linea
            AND empresa=:empresa
            AND area=:area";

    $stmr = $conn->prepare($sqs);
    $stmr->bindParam(':nnope', $op);
    $stmr->bindParam(':linea', $linea);
    $stmr->bindParam(':ccmpn', $compania);
    $stmr->bindParam(':area', $area);
    $stmr->bindParam(':empresa', $empresa); // doble uso en tu SQL

    $stmr->execute();
    $resultado = $stmr->fetch(PDO::FETCH_ASSOC);
    $meta = $resultado['cantmeta'] ?? 0;

    $meta = (int) $meta;
    if($meta == 0) {
        responder(200, 'No se registro la meta.');
    }

    $metas = ($meta > 0) ? ($canttimbradas * 100) / $meta : 0;
    $metas = number_format($metas, 2, '.', '');

    $upd = [];
    $upd['linea_meta'] = $metas;

    // ======================================
    // ⚙️ Obtener la eficiencia del operario
    // ====================================

    $sql = "SELECT
        co.operacion,
        co.tiempo_estimado_operacion,
        ROUND(SUM(TIME_TO_SEC(ci.tiempo_trascurrido)) / 60, 2) AS tiempo_total_min,
        count(ci.ciclo_id) as cant
    FROM ciclo ci
    LEFT JOIN costura co ON co.costura_id = ci.costua_id
    WHERE ci.usuario_registra = '".$usuario."'
    AND DATE(ci.fecha_creacion) = CURDATE()
    AND (ci.tiempo_trascurrido IS NOT NULL OR ci.tiempo_trascurrido <> '00:00:00')
    AND ci.estado_id = 1
    GROUP BY co.operacion, co.tiempo_estimado_operacion";

    sc_lookup(rs_data_sybase, $sql);

    $eficiencia = 0;

    if (isset({rs_data_sybase}[0][0])) {
        foreach ({rs_data_sybase} as $row) {
            $tiempo_total_min = floatval($row[2]);
            $tiempo_estimado = floatval($row[1]);
            $cant = intval($row[3]);

            if ($tiempo_total_min > 0) {
                $valorobtenido = ($tiempo_estimado*$cant*100)/$tiempo_total_min;
                $eficiencia += $valorobtenido;
            }
        }
    }

    $eficiencia = $eficiencia == 0 ? 0 : number_format($eficiencia, 2, '.', '');

    $upd['operario_meta'] = $eficiencia;

    // =================================
    // ⚙️ Obtener reprocesos x costura
    // ==============================
    $sql = "SELECT
        ci.usuario_registra,
        count(ci.ciclo_id) as cant
    FROM ciclo ci
    Where ci.usuario_registra = '".$usuario."' AND ci.costua_id = $costura
    AND ci.estado_id = 1
    AND ci.motivo_id > 0 AND ci.motivo_tipo = 50
    GROUP BY ci.usuario_registra";

    sc_lookup(rs_data_sybase, $sql);

    $upd['reproceso'] = (isset({rs_data_sybase}[0][0])) ? intval({rs_data_sybase}[0][1]) : 0;

    // : Agregar usuario que modifica
    $upd['usuario_modifica'] = $usuario;

    // ================================
    // 🔄 Construir y ejecutar UPDATE
    // =============================
    $sql = formarSqlUpdate("costura", $upd, "costura_id = $costura");
    sc_exec_sql($sql);

    // ✅ Responder
    responder(200, 'Actualizó correctamente.', ['rta' => 1]);

} catch (PDOException $e) {
    responder(500, 'Error al consultar la base de datos: ' . $e->getMessage());
} finally {
    $conn = null; // ✅ Cierre de conexión
}