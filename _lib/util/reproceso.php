<?php

/**
 * Calcula la cantidad de reprocesos para un ingreso específico.
 * @param array $param Array con los parámetros necesarios: ingreso_id, costura_id, etc.
 * @param PDO $conn Conexión a la base de datos.
 * 
 * @return int|null Retorna la cantidad de reprocesos o null en caso de error.
 */
function getCantReproceso(array $param, PDO $conn): ?int {
    if (!$conn || empty($param)) return 0;

    $where = [];
    foreach ($param as $k => $v) {
        if (!empty($v)) $where[] = "$k = :$k";
    }

    if (empty($where)) return 0;

    $sql = "SELECT
                ingreso_id,
                COUNT(ciclo_id) AS cant
            FROM ciclo
            WHERE " . implode(" AND ", $where) . "
              AND estado_id = 1
              AND motivo_id > 0
              AND motivo_tipo = 50
            GROUP BY ingreso_id";

    try {
        $stmt = $conn->prepare($sql);
        foreach ($param as $k => $v) {
            if (!empty($v)) {
                $stmt->bindValue(":$k", $v);
            }
        }

        $stmt->execute();
        $rta = $stmt->fetch(PDO::FETCH_ASSOC);

        return intval($rta['cant'] ?? 0);
    } catch (Exception $e) {
        error_log("Error al calcular reproceso: " . $e->getMessage());
        return null;
    }
}