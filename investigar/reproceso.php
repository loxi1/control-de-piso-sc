<?php

function getCantReproceso(array $param, PDO $conn): ?int {
    if (!$conn || empty($param)) return 0;

    $where = [];
    foreach ($param as $k => $v) {
        if (!empty($v)) $where[] = "$k = :$k";
    }

    if (empty($where)) return 0;

    $sql = "SELECT
                ci.ingreso_id,
                COUNT(ci.ciclo_id) AS cant
            FROM ciclo ci
            WHERE " . implode(" AND ", $where) . "
              AND ci.estado_id = 1
              AND ci.motivo_id > 0
              AND ci.motivo_tipo = 50
            GROUP BY ci.ingreso_id";

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