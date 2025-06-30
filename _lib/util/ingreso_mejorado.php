<?php
/**
 * 🧠 Verificar si un ingreso está activo,
 * en base a los filtros proporcionados.
 *
 * @param array $whereData  Filtros obligatorios para construir la cláusula WHERE (ej. ['colaborador_id' => 1]).
 * @param PDO $conn         Conexión activa a la base de datos.
 *
 * @return array|null         Retorna el tiempo en segundos o null si ocurre un error o no hay datos.
 */
function verificarRegistroIngreso(array $whereData, PDO $conn): ?array {
    if (!$conn || empty($whereData['condiciones']) || empty($whereData['bindings'])) return null;

    $sqlWhere = implode(" AND ", $whereData['condiciones']);

    $sql = "SELECT
                ing.id,
                ing.turno_id,
                ing.horario_ingreso,
                ing.horario_salida,
                NOW() AS fecha_actual,
                ing.horario_maximo,
                TIMESTAMPDIFF(SECOND, ing.horario_ingreso, NOW()) AS tiempo_trascurrido,
                ing.horario_minimo,
                ing.estado,
                ing.hora_limite_refrigerio,
                ing.refrigerio_aplicado,
                IFNULL(perm.id, 0) AS cerro_sesion,
                ing.minutos_almuerzo
            FROM ingreso ing
            LEFT JOIN (
                SELECT ingreso_id, MIN(id) AS id
                FROM permiso
                WHERE tipo = 'salida' AND estado = 2
                GROUP BY ingreso_id
            ) AS perm ON perm.ingreso_id = ing.id
            WHERE $sqlWhere
            ORDER BY ing.horario_ingreso DESC
            LIMIT 1";

    try {
        $stmt = $conn->prepare($sql);
        foreach ($whereData['bindings'] as $k => $v) {
            $stmt->bindValue(":$k", $v);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        error_log("Error ingreso: " . $e->getMessage());
        return null;
    }
}

/**
 * 📊 Valida si existe un ingreso dentro del horario permitido comparado con la hora actual.
 *
 * @param array $whereData    Array con claves 'condiciones' y 'bindings' generado por blindValueWhereCondiciones().
 * @param string|null $condicionesFijas Condiciones adicionales opcionales (ej: NOW() BETWEEN ...).
 * @param PDO $conn           Conexión activa a la base de datos.
 *
 * @return array|null         Retorna el registro encontrado o null si no hay datos o ocurre un error.
 */
function validarIngresoHorario(array $whereData, PDO $conn): ?array {
    if (!$conn || empty($whereData['condiciones']) || empty($whereData['bindings'])) {
        return null;
    }

    // Armado del WHERE
    $sqlWhere = '';
    if (!empty($whereData['condiciones'])) {
        $sqlWhere .= ' AND ' . implode(' AND ', $whereData['condiciones']);
    }

    // SQL final
    $sql = "SELECT
                i.turno_id,
                t.descripcion AS elturno,
                i.horario_minimo,
                i.horario_maximo,
                i.estado
            FROM ingreso i
            JOIN turno t ON t.id = i.turno_id
            WHERE
                NOW() BETWEEN i.horario_minimo AND i.horario_maximo
                $sqlWhere
            ORDER BY i.id DESC
            LIMIT 1;
    ";

    try {
        $stmt = $conn->prepare($sql);
        foreach ($whereData['bindings'] as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];

    } catch (Exception $e) {
        error_log("❌ Error en validarIngresoHorario: " . $e->getMessage());
        return null;
    }
}

/**
 * 🕒 Cerrar el turno de un operario
 * en base a su ID y código.
 * @param int $id          ID del ingreso.
 * @param string $codigo   Código del operario.
 * @param PDO $conn        Conexión a la base de datos.
 * 
 * Registra el cierre del turno, maneja permisos de refrigerio y salida,
 */
function cerrarTurno(int $id, string $codigo, PDO $conn): void {
    $ingreso = listarTablaSimple("ingreso", ['id' => $id], $conn, [
        'horario_salida','minutos_almuerzo','hora_limite_refrigerio'
    ]);

    $horaSalida = $ingreso[0]['horario_salida'] ?? null;
    $totalMinutos = intval($ingreso[0]['minutos_almuerzo'] ?? 0);
    $horaLimiteRefrigerio = $ingreso[0]['hora_limite_refrigerio'] ?? null;

    $where = ['ingreso_id' => $id];

    if ($totalMinutos && !empty($horaLimiteRefrigerio)) {
        $where['tipo_permiso'] = 'Refrigerio';
        $permisoExistente = listarTablaSimple("permiso", $where, $conn, ['id']);
        $idPermiso = intval($permisoExistente[0]['id'] ?? 0);

        if ($idPermiso === 0) {
            $fechaPermiso = $horaLimiteRefrigerio;
            $datetime = new DateTime($horaLimiteRefrigerio);
            $datetime->sub(new DateInterval('PT' . $totalMinutos . 'M'));
            $fechaCreacion = $datetime->format('Y-m-d H:i:s');

            $save = [
                'codigo'            => $codigo,
                'fecha_permiso'     => $fechaPermiso,
                'fecha_creacion'    => $fechaCreacion,
                'ingreso_id'        => $id,
                'con_permiso'       => 1,
                'tipo'              => 2,
                'tipo_permiso'      => 5,
                'usuario_creacion'  => 'SISTEMA',
                'fecha_modificacion'=> 'NOW()',
                'estado'            => 2
            ];
            saveTable("permiso", $save, $conn);
        }
    }

    // Permiso de salida
    unset($where['tipo_permiso']);
    $where['tipo'] = 'Salida';
    $permisoSalida = listarTablaSimple("permiso", $where, $conn, ['id']);
    $idSalida = intval($permisoSalida[0]['id'] ?? 0);

    if ($idSalida === 0) {
        $permisoIngreso = listarTablaSimple("permiso", ['ingreso_id' => $id, 'tipo' => 'Ingreso'], $conn);
        $fecha_permiso = $horaSalida;

        $save = [
            'codigo'            => $codigo,
            'fecha_permiso'     => $fecha_permiso,
            'ingreso_id'        => $id,
            'con_permiso'       => 1,
            'tipo'              => 3,
            'tipo_permiso'      => 6,
            'usuario_creacion'  => 'SISTEMA',
            'fecha_modificacion'=> 'NOW()',
            'estado'            => 2
        ];

        saveTable("permiso", $save, $conn);
    }

    // Calcular tiempo transcurrido y eficiencia
    $paramh = ['ing.id' => $id, 'salida' => 1];
    $tiempo = tiempoXTurnoXColaborador($paramh, $conn);

    $update = ['estado' => 2];
    if ($tiempo >= 0) {
        $efi = calcularEficienciaOnline(['id' => $id, 'tiempo' => $tiempo], $conn);
        $update['eficiencia'] = $efi['eficiencia'] ?? 0;
        $update['reproceso'] = getCantReproceso(['ingreso_id' => $id], $conn);
    }

    if ($totalMinutos && !empty($horaLimiteRefrigerio)) {
        $update['refrigerio_aplicado'] = 1;
    }

    updateTable("ingreso", $update, ['id' => $id], $conn);
}

/**
 * $dataTurno: Array con datos del turno actual.
 * $codigo: Código del operario.
 * $turno: ID del turno.
 * $conn: Conexión a la base de datos.
 * 
 * Registra un ingreso de un operario en el sistema.
 */
function registrarIngreso(array $dataTurno, string $codigo, int $turno, PDO $conn): array {
    $empresa_id = $_SESSION['empresa_id'] ?? null;

    $hora_limite_refrigerio = !empty($dataTurno['hora_limite_refrigerio']) 
        ? strtotime($dataTurno['hora_limite_refrigerio']) 
        : null;
    
    $minutos_almuerzo = intval($dataTurno['considerar_almuerzo_min'] ?? 0);
    $horario_ingreso = $dataTurno['horario_ingreso'] ?? '';
    $horario_salida = $dataTurno['horario_salida'] ?? '';
    $horario_minimo = $dataTurno['horario_minimo'] ?? '';
    $horario_maximo = $dataTurno['horario_maximo'] ?? '';
    $tiempo_trascurrido = intval($dataTurno['tiempo_trascurrido'] ?? 0);

    $inset = [
        'turno_id'              => $turno,
        'codigo_operario'       => $codigo,
        'empresa_id'            => $empresa_id,
        'minutos_almuerzo'      => $minutos_almuerzo,
        'horario_ingreso'       => $horario_ingreso,
        'horario_salida'        => $horario_salida,
        'dia_de_la_semana'      => intval($dataTurno['numero_dia'] ?? 0),
        'horario_minimo'        => $horario_minimo,
        'horario_maximo'        => $horario_maximo,
        'hora_limite_refrigerio'=> $hora_limite_refrigerio,
        'refrigerio_aplicado'   => 0,
        'fecha'                 => date("Y-m-d", strtotime($horario_ingreso))
    ];

    $id = saveTable("ingreso", $inset, $conn);

    if (empty($id)) {
        responder(500, 'No se pudo registrar el ingreso.');
    }

    $cant = existencia_ciclos([
        'usuario_registra' => $codigo, 
        'tiempo_inicio' => $horario_minimo, 
        'tiempo_fin' => $horario_maximo
    ], $conn);

    $code = ($cant == 0 && $tiempo_trascurrido > 0) ? 2 : 1;

    $rta = [
        'code'              => $code,
        'id'                => $id,
        'titulo'            => $code === 2 ? "¿Tiene Permiso?" : "",
        'descripcion'       => $code === 2 
            ? "!Estas ingresando tarde¡ Turno: ".date('h:i A', strtotime($horario_ingreso)) . " - " . date('h:i A', strtotime($horario_salida)) 
            : "",
        'horario_ingreso'   => $dataTurno['horario_ingreso'] ?? ''
    ];

    $refrigerio_aplicado = ($minutos_almuerzo > 0 && !empty($hora_limite_refrigerio)) ? 1 : 2;

    $_SESSION["ingreso_id"] = $id;
    $_SESSION["hora_limite_refrigerio"] = $hora_limite_refrigerio;
    $_SESSION["refrigerio_aplicado"] = $refrigerio_aplicado;
    $_SESSION["minutos_almuerzo"] = $minutos_almuerzo;

    return $rta;
}


/**
 * 📊 Validar si hay ciclos abiertos,
 * en base a los filtros proporcionados.
 *
 * @param array $p          Parámetros WHERE (ej. ['usuario_registra' => '36104', 'tiempo_inicio'=>'', 'tiempo_fin'=>'']).
 * @param PDO $conn         Conexión activa a la base de datos.
 *
 * @return array|null         Retorna el tiempo en segundos o null si ocurre un error o no hay datos.
 */
function existencia_ciclos(array $p, PDO $conn): int
{
    if (!$conn || empty($p['usuario_registra']) || empty($p['tiempo_inicio']) || empty($p['tiempo_fin'])) return 0;

    try {
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
        error_log("Error existencia_ciclos: " . $e->getMessage());
        return 0;
    }
}

/**
 * 📊 Mostrar el turno,
 * en base a los filtros proporcionados.
 *
 * @param array $whereData   Trame parametro de condiciones y bindings.
 * @param PDO $conn          Conexión activa a la base de datos.
 *
 * @return array|null         Retorna el tiempo en segundos o null si ocurre un error o no hay datos.
 */
function getTurno(array $whereData, PDO $conn): ?array
{
    if (!$conn) return [];

    $bindings = [];
    $sqlx = "";

    if (!empty($whereData)) {
        $condiciones = $whereData['condiciones'] ?? [];
        $bindings = $whereData['bindings'] ?? [];

        if (empty($condiciones) || empty($bindings)) return null;

        $sqlx = " AND ".implode(" AND ", $condiciones) . " AND ";
    }

    try {
        $sql = "SELECT
                    h.id,
                    h.numero_dia,
                    h.turno_id,
                        t.descripcion as nombre_turno,
                    h.considerar_almuerzo_min,

                    TIMESTAMP(CURDATE(), TIME(h.hora_inicio)) AS horario_ingreso,

                    TIMESTAMP(
                        CASE 
                            WHEN TIME(h.hora_fin) < TIME(h.hora_inicio) THEN DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                            ELSE CURDATE()
                        END,
                        TIME(h.hora_fin)
                    ) AS horario_salida,

                    TIMESTAMPDIFF(SECOND, TIMESTAMP(CURDATE(), TIME(h.hora_inicio)), NOW()) AS tiempo_trascurrido,

                    DAYOFWEEK(TIMESTAMP(CURDATE(), TIME(h.hora_inicio))) AS dia,

                    TIMESTAMP(CURDATE(), TIME(h.hora_min)) AS horario_minimo,

                    TIMESTAMP(
                        CASE
                            WHEN TIME(h.hora_max) < TIME(h.hora_min) THEN DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                            ELSE CURDATE()
                        END,
                        TIME(h.hora_max)
                    ) AS horario_maximo,

                    TIMESTAMP(
                        CASE 
                            WHEN TIME(h.hora_inicio) < TIME(h.hora_limite_almuerzo) THEN DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                            ELSE CURDATE()
                        END,
                        TIME(h.hora_limite_almuerzo)
                    ) AS hora_limite_almuerzo

                FROM turno_horario AS h

                LEFT JOIN turno as t ON h.turno_id = t.id

                WHERE
                    h.estado = 1
                    AND DAYOFWEEK(TIMESTAMP(CURDATE(), TIME(h.hora_inicio))) = h.numero_dia
                    $sqlx
                HAVING
                    NOW() BETWEEN horario_minimo AND horario_maximo;";

        $stmt = $conn->prepare($sql);

        if (!empty($bindings)) {
            foreach ($bindings as $k => $v) {
                $stmt->bindValue(":$k", $v);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    } catch (Exception $e) {
        error_log("Error ingreso: " . $e->getMessage());
        return null;
    }
}

//Validar si finalizo su turno
function validarSiFinalizoTurno(array $param, PDO $conn) {
    if (!$conn || count($param) < 1) return 0;
    
}

/**
 * Calcula el tiempo total trabajado por un colaborador en un turno específico,
 * en base a los filtros proporcionados.
 *
 * @param array $whereData  Filtros obligatorios para construir la cláusula WHERE (ej. ['colaborador_id' => 1]).
 * @param PDO $conn         Conexión activa a la base de datos.
 * @param array $param      [Opcional] Parámetros adicionales que pueden influir en la lógica (por ejemplo, tipo de turno, fecha, etc.).
 *
 * @return int|null         Retorna el tiempo en segundos o null si ocurre un error o no hay datos.
 */
function tiempoXTurnoXColaborador(array $whereData, PDO $conn, array $param = []): ?int
{
    if (!$conn || empty($whereData)) return 0;

    $sqltiempofin = "now()";

    if (!empty($param['salida'])) {
        $sqltiempofin = "(SELECT per.fecha_permiso FROM permiso per WHERE per.ingreso_id = ing.id AND per.tipo = 'Salida' LIMIT 1)";
    }

    $condiciones = $whereData['condiciones'] ?? [];
    $bindings = $whereData['bindings'] ?? [];

    if (empty($condiciones) || empty($bindings)) return null;

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
                WHERE " . implode(" AND ", $condiciones);
        
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

// 🕒 Mostrar detalles del último ingreso
/**
 * Calcula el tiempo total trabajado por un colaborador en un turno específico,
 * en base a los filtros proporcionados.
 *
 * @param array $whereData  Filtros obligatorios para construir la cláusula WHERE (ej. ['colaborador_id' => 1]).
 * @param PDO $conn         Conexión activa a la base de datos.
 *
 * @return array|null         Retorna el tiempo en segundos o null si ocurre un error o no hay datos.
 */
function mostrarUltimoIngreso(array $whereData, PDO $conn): ?array {
    if (!$conn || empty($whereData)) return null;

    $condiciones = $whereData['condiciones'] ?? [];
    $bindings = $whereData['bindings'] ?? [];

    if (empty($condiciones) || empty($bindings)) return null;

    $sql = "SELECT 
                ing.id, ing.turno_id, ing.horario_ingreso, ing.horario_salida, now() AS fecha_actual,
                ing.horario_maximo, TIMESTAMPDIFF(SECOND, ing.horario_ingreso, NOW()) AS tiempo_trascurrido, ing.horario_minimo,
                ing.estado, ing.hora_limite_refrigerio, ing.refrigerio_aplicado,
                (SELECT IFNULL(perm.id, 0) 
                 FROM permiso perm 
                 WHERE perm.tipo = 'salida' 
                   AND perm.ingreso_id = ing.id 
                   AND perm.estado = 2 
                 LIMIT 1) AS cerro_cession, 
                minutos_almuerzo
            FROM ingreso ing
            WHERE " . implode(" AND ", $condiciones) . "
            ORDER BY ing.horario_ingreso DESC
            LIMIT 1";

    try {
        $stmt = $conn->prepare($sql);

        foreach ($bindings as $alias => $valor) {
            $stmt->bindValue(":$alias", $valor);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("❌ Error ingreso: " . $e->getMessage());
        return null;
    }
}
