<?php

function calcularEficienciaOnline(array $param, PDO $conn): ?array {
    $idingreso = $param['id'] ?? null;
    $tiempotranscurrido = $param['tiempo'] ?? null; //Tiempo en Segundos
    
    if (empty($idingreso) || empty($tiempotranscurrido)) return null;
    if (!$conn) return null;

    try {
        $sql = "SELECT
                    co.operacion,
                    co.tiempo_estimado_operacion,
                    COUNT(ci.ciclo_id) AS cant
                FROM ciclo ci
                LEFT JOIN costura co ON co.costura_id = ci.costua_id
                WHERE 
                    ci.ingreso_id = :idingreso
                    AND motivo_id = 0 
                    AND (ci.tiempo_trascurrido IS NOT NULL AND ci.tiempo_trascurrido <> '00:00:00')
                    AND ci.estado_id = 1
                GROUP BY co.operacion, co.tiempo_estimado_operacion";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':idingreso', $idingreso, PDO::PARAM_INT);
        $stmt->execute();

        $rta = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $eficiencia = 0;
        $cantidad = 0;
        if(!empty($rta[0]['operacion'])) {
            $valorobtenido = 0;
            foreach ($rta as $row) {
                $tiempo_estandar = floatval($row['tiempo_estimado_operacion']);
                $cant = intval($row['cant']);

                $valorobtenido = $tiempo_estandar*$cant;
                $eficiencia += $valorobtenido;
                $cantidad += $cant;
            }
            $eficiencia = ($eficiencia == 0) ? 0 : number_format(($eficiencia*6000/$tiempotranscurrido), 2, '.', '');
        }
        
        return ['eficiencia' => $eficiencia, 'cantidad'=>$cantidad];
    } catch (Exception $e) {
        error_log("Error al calcular eficiencia: " . $e->getMessage());
        return null;
    } finally {
        $conn = null;
    }
}