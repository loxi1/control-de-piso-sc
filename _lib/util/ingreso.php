<?php

// 🧠 Verificar si un ingreso está activo
function verificarRegistroIngreso(array $param, PDO $conn): ?array
{
    if (!$conn || !count($param)) return [];

    $where = [];
    foreach ($param as $k => $v) {
        if (!empty($v)) $where[$k] = "$k = :$k";
    }

    if (empty($where)) return [];

    try {
        //Mostrar el ultimo horario que no cerro estado =1.
        $sql = "SELECT id, turno_id, horario_ingreso, horario_salida, NOW() AS fecha_actual,
                       horario_maximo, TIMESTAMPDIFF(SECOND, horario_ingreso, NOW()) AS tiempo_transcurrido,
                       horario_minimo, estado
                FROM ingreso
                WHERE " . implode(" AND ", $where) . " AND estado = 1
                ORDER BY horario_ingreso DESC
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        foreach ($where as $k => $v) {
            $stmt->bindValue(":$k", $param[$k]);
        }

        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        //No existe horario que no cerro(Mostrar horario)
        $code = 0;
        if (!$info) return ['code' => $code, 'msn' => 'No existe login.', 'data' => []];

        $id = $info['id'];
        $estado = (int) $info['estado'];
        $actual = strtotime($info['fecha_actual']);
        $min = strtotime($info['horario_minimo']);
        $max = strtotime($info['horario_maximo']);
        $tiempo = (int) $info['tiempo_transcurrido'];

        $rta = [];
        //Existe registro: puede ser actual o no cerro session
        if ($actual > $min && $actual < $max) {
            $code = 1;
            $conn->prepare("UPDATE ingreso SET fecha_modificacion = NOW() WHERE id = ?")->execute([$id]);

            $rta['code'] = 1;
            $rta['id'] = $id;
            $rta['titulo'] = '';
            $rta['descripcion'] = '';
            $rta['horario_ingreso'] = $info['horario_ingreso'];

            $ciclos = existencia_ciclos([
                'usuario_registra' => $param['codigo'] ?? '',
                'tiempo_inicio' => $min,
                'tiempo_fin' => $max,
            ]);

            if ($ciclos === 0 && $tiempo > 0) {
                $code = 2;
                $rta['code'] = 2;
                $rta['titulo'] = '¿Tiene Permiso?';
                $rta['descripcion'] = "¡Ingreso tarde! Turno: " .
                    date('h:i A', strtotime($info['horario_ingreso'])) . " - " .
                    date('h:i A', strtotime($info['horario_salida']));
            }

            return ['code' => 2, 'msn' => 'Turno activo.', 'data' => $rta];
        }

        if ($estado === 1) {
            $conn->prepare("UPDATE ingreso SET estado = 2, fecha_modificacion = NOW() WHERE id = ?")->execute([$id]);
            return ['code' => 1, 'msn' => 'Turno cerrado correctamente.', 'data' => []];
        }

        return ['code' => 1, 'msn' => 'Turno ya cerrado.', 'data' => []];
    } catch (Exception $e) {
        error_log("Error ingreso: " . $e->getMessage());
        return null;
    }
}

// 📊 Validar si hay ciclos abiertos
function existencia_ciclos(array $p, PDO $conn): int
{
    if (!$conn || !empty($p['usuario_registra']) || !empty($p['tiempo_inicio']) || !empty($p['tiempo_fin'])) return 0;

    try {
        $p['tiempo_inicio'] = date("Y-m-d H:i:s", $p['tiempo_inicio']);
        $p['tiempo_fin'] = date("Y-m-d H:i:s", $p['tiempo_fin']);

        $sql = "SELECT COUNT(*) AS cantidad FROM ciclo
                WHERE estado_id = 1
                AND usuario_registra = :usuario_registra
                AND tiempo_inicio > :tiempo_inicio
                AND tiempo_fin < :tiempo_fin";

        $stmt = $conn->prepare($sql);
        foreach ($p as $k => $v) {
            $stmt->bindValue(":$k", $v);
        }

        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($r['cantidad'] ?? 0);
    } catch (Exception $e) {
        error_log("Error ciclos: " . $e->getMessage());
        return 0;
    }
}

function mostrarTurno(array $param, PDO $conn): ?array
{
    if (!$conn) return [];

    $where = [];
    $sqlx = "";

    if (!empty($param)) {
        foreach ($param as $k => $v) {
            if (!empty($v)) $where[$k] = "$k = :$k";
        }
        $sqlx = implode(" AND ", $where) . " AND ";
    }

    try {
        $sql = "SELECT
                    id,
                    numero_dia,
                    turno_id,
                    (select descripcion from turno trun where trun.id=turno_id) as elturno,
                    considerar_almuerzo_min,
                    ingreso AS horario_ingreso,
                    salida AS horario_salida,
                    TIMESTAMPDIFF(SECOND, ingreso, NOW()) AS tiempo_trascurrido,
                    DAYOFWEEK(ingreso) AS dia,
                    -- Hora fin del turno anterior (turno diferente, dia anterior)
                    (
                        SELECT TIMESTAMP(DATE(horarios.ingreso), TIME(tur.hora_fin))
                        FROM turno_horario tur
                        WHERE tur.turno_id != horarios.turno_id
                        AND tur.numero_dia = horarios.numero_dia
                        LIMIT 1
                    ) AS horario_minimo,                    
                    -- Hora inicio del otro turno del mismo día
                    (
                        SELECT TIMESTAMP(DATE(horarios.salida), TIME(tur.hora_inicio))
                        FROM turno_horario tur
                        WHERE tur.turno_id != horarios.turno_id
                            AND tur.numero_dia =  CASE WHEN horarios.numero_dia = 7 THEN 7 ELSE horarios.numero_dia - 1 END
                        LIMIT 1
                    ) AS horario_maximo
                FROM (
                    SELECT
                        id,
                        numero_dia,
                        turno_id,
                        considerar_almuerzo_min,
                        TIMESTAMP(CURDATE(), TIME(hora_inicio)) AS ingreso,
                        TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL DATEDIFF(hora_fin, hora_inicio) DAY), TIME(hora_fin)) AS salida
                    FROM turno_horario
                ) AS horarios
                WHERE $sqlx DAYOFWEEK(ingreso) = numero_dia
                HAVING now() BETWEEN horario_minimo AND horario_maximo;";

        $stmt = $conn->prepare($sql);

        if (count($where) > 0) {
            foreach ($where as $k => $v) {
                $stmt->bindValue(":$k", $param[$k]);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error ingreso: " . $e->getMessage());
        return null;
    }
}

//Validar si finalizo su turno
function validarSiFinalizoTurno(array $param, PDO $conn) {
    if (!$conn || count($param) < 1) return 0;
    
}

//Permite alias como parameteros Mostrar el tiempo para la efieciencia.
function tiempoXTurnoXColaborador(array $param, PDO $conn): ?int
{
    if (!$conn || count($param) < 1) return 0;

    $where = [];
    $bindings = [];
    $sqltiempofin = "now()";

    if (!empty($param['salida'])) {
        unset($param['salida']);
        $sqltiempofin = "(SELECT per.fecha_permiso FROM permiso per WHERE per.ingreso_id = ing.id AND per.tipo = 'Salida' LIMIT 1)";
    }

    foreach ($param as $columna => $valor) {
        if (!empty($valor)) {
            $aliasReal = str_replace('.', '_', $columna); // ej. ing.id → ing_id
            $where[] = "$columna = :$aliasReal";
            $bindings[$aliasReal] = $valor;
        }
    }

    if (empty($where)) return 0;

    try {
        $sql = "SELECT
                    ing.id,
                    IFNULL(TIMESTAMPDIFF(
                        SECOND,
                        (SELECT per.fecha_permiso FROM permiso per WHERE per.ingreso_id = ing.id AND per.tipo = 'Ingreso' AND per.estado = 2 LIMIT 1),
                        $sqltiempofin
                    ), 0)
                    - IFNULL((SELECT SUM(TIMESTAMPDIFF(SECOND, per.fecha_creacion, per.fecha_permiso))
                              FROM permiso per
                              WHERE per.ingreso_id = ing.id AND per.tipo = 'permiso' AND per.estado = 2
                    ), 0) AS tiempo
                FROM ingreso ing
                WHERE " . implode(" AND ", $where);
        
        $stmt = $conn->prepare($sql);
        foreach ($bindings as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }

        $stmt->execute();
        $rta = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rta && isset($rta['tiempo']) ? intval($rta['tiempo']) : 0;
    } catch (Exception $e) {
        error_log("Error ingreso: " . $e->getMessage());
        return null;
    }
}