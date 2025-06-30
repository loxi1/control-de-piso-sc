<?php
/**
 * Devolver op por autocompletar
 * @param string $op
 * @param PDO $conn Conexión a la base de datos
 * 
 * @rturn array
 */
function get_op_autocomplete(array $param, PDO $conn): array {
    if (!$conn || count($param) < 1) return [];

    $whereData = blindValueWhereCondiciones($param); // Validar y sanitizar los parámetros

    $condiciones = $whereData['condiciones'] ?? [];
    $bindings = $whereData['bindings'] ?? [];
    
    if (empty($condiciones) || empty($bindings)) return [];

    $sqlx = " AND ".implode(" AND ", $condiciones);
    $sql = "SELECT
                al.nnope
            from avsolcs a
            JOIN altopc al ON a.cproto = al.cproto AND a.cversion = al.cversion_proto
            JOIN pctmestc p ON LTRIM(RTRIM(a.des_estilo_rotulo)) = LTRIM(RTRIM(p.cestilo_rotulo))
            WHERE 
                al.sestop <> '9' AND a.sestar <> '9' AND SUBSTRING(al.nnope, 1, 3) = '100' $sqlx 
            LIMIT 10";

    $stmt = $conn->prepare($sql);

    foreach ($bindings as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
}

/**
 * Lineas
 * 
 * @return string
 */
function cbx_lineas(PDO $conn): string {
    $cbx = "<option value=''>Seleccione una línea</option>";
    if (!$conn) return "";

    $sql = "SELECT valor_grabar, valor_mostrar 
            FROM caracteristica 
            WHERE nombre='linea_produccion'
            ORDER BY orden ASC";

    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($data)) {
            foreach ($data as $row) {
                $valor_grabar = htmlspecialchars($row['valor_grabar'], ENT_QUOTES, 'UTF-8');
                $valor_mostrar = htmlspecialchars($row['valor_mostrar'], ENT_QUOTES, 'UTF-8');
                $cbx .= "<option value='$valor_grabar'>$valor_mostrar</option>";
            }
            return $cbx;
        }
    }

    return "<option value=''>Crear línea</option>";
}

/**
 * Devolver estilos
 * @param array ['op' => 'valor']
 * @param PDO $conn Conexión a la base de datos
 * 
 * @return string
 */
function cbx_estilos(array $param, $conn): string {
    $cbx = "<option value=''>Seleccione un estilo</option>";
    if (!$conn || count($param)<1) return "";

    $whereData = blindValueWhereCondiciones($param); // Validar y sanitizar los parámetros

    $condiciones = $whereData['condiciones'] ?? [];
    $bindings = $whereData['bindings'] ?? [];

    if (empty($condiciones) || empty($bindings)) return "";

    $sql = "SELECT 
                LTRIM(RTRIM(avsolcs.des_estilo_rotulo)) estilo
            FROM 
                avsolcs
            INNER JOIN  
                altopc ON avsolcs.cproto = altopc.cproto AND avsolcs.cversion = altopc.cversion_proto
            WHERE ".implode(" AND ", $condiciones);

    $stmt = $conn->prepare($sql);

    foreach ($bindings as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data[0]['valor_grabar'])) {
        foreach ($data as $row) {
            $estilo = htmlspecialchars($row['estilo'], ENT_QUOTES, 'UTF-8');
            $cbx .= "<option value='$estilo'>$estilo</option>";
        }
        return $cbx;
    }    
    return "<option value=''>Crear estilo</option>";
}

/**
 * Devolver tipo de maquina
 * @param array ['estilo' => 'valor']
 * @param PDO $conn Conexión a la base de datos
 * 
 * @return string
 */
function cbx_tipo_maquina(array $param, $conn): string {
    $cbx = "<option value=''>Seleccione un tipo de máquina</option>";
    if (!$conn || count($param)<1) return "";

    $whereData = blindValueWhereCondiciones($param); // Validar y sanitizar los parámetros

    $condiciones = $whereData['condiciones'] ?? [];
    $bindings = $whereData['bindings'] ?? [];

    if (empty($condiciones) || empty($bindings)) return "";

    $sql = "SELECT 
                s.csgl as tipo_maquina
            FROM pctmestc est
            JOIN pctmestd sec ON est.nro_secopr = sec.nro_secopr
            JOIN pcmopr m ON sec.copr = m.copr
            JOIN pcmtm s ON m.ctmqn = s.ctmqn
            WHERE 
                ".implode(" AND ", $condiciones) ."
            GROUP 
                BY s.csgl
            ORDER 
                BY s.csgl ASC ";

    $stmt = $conn->prepare($sql);

    foreach ($bindings as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data[0]['tipo_maquina'])) {
        foreach ($data as $row) {
            $tipo_maquina = htmlspecialchars($row['tipo_maquina'], ENT_QUOTES, 'UTF-8');
            $cbx .= "<option value='$tipo_maquina'>$tipo_maquina</option>";
        }
        return $cbx;
    }    
    return "<option value=''>Crear tipo de máquina</option>";
}

/**
 * Devolver operaciones
 * @param array ['estilo' => 'valor', 'tipo_maquina' => 'valor']
 * @param PDO $conn Conexión a la base de datos
 * 
 * @return string
 */
function cbx_operaciones(array $param, $conn): string {
    $cbx = "<option value=''>Seleccione una operación</option>";
    if (!$conn || empty($param['estilo']) || empty($param['tipo_maquina'])) return "";    

    $estilo = $param['estilo'] ?? [];
    $tipo_maquina = $param['tipo_maquina'] ?? [];

    if (empty($condiciones) || empty($bindings)) return "";

    $sql = "SELECT 
                m.tabrv as operacion
            FROM 
                pctmestc est
            JOIN pctmestd sec ON est.nro_secopr = sec.nro_secopr
            JOIN pcmopr m ON sec.copr = m.copr
            JOIN pcmtm s ON m.ctmqn = s.ctmqn
            WHERE 
                 LTRIM(RTRIM(est.cestilo_rotulo)) = '$estilo' AND s.csgl = '$tipo_maquina'
            ORDER BY 
                s.csgl ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($data[0]['operacion'])) {
        foreach ($data as $row) {
            $operacion = htmlspecialchars($row['operacion'], ENT_QUOTES, 'UTF-8');
            $cbx .= "<option value='$operacion'>$operacion</option>";
        }
        return $cbx;
    }    
    return "<option value=''>Crear operación</option>";
}

/**
 * Devolver codigos de operación
 * @param array ['p.tabrv' => 'operacion', 'nnope' => 'op']
 * 
 * @return string
 */
function get_cod_operacion(array $param, $conn): string {
    if (!$conn || count($param)<1) return "";

    $whereData = blindValueWhereCondiciones($param); // Validar y sanitizar los parámetros

    $condiciones = $whereData['condiciones'] ?? [];
    $bindings = $whereData['bindings'] ?? [];

    if (empty($condiciones) || empty($bindings)) return "";

    $sql = "SELECT
                p.copr as codigo_operacion
            FROM pctbmopd pc
            INNER JOIN pcmopr p ON pc.copr = p.copr
            WHERE 
            ".implode(" AND ", $condiciones);

    $stmt = $conn->prepare($sql);

    foreach ($bindings as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }

    $stmt->execute();

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($data['codigo_operacion'])) {
        return htmlspecialchars($data['codigo_operacion'], ENT_QUOTES, 'UTF-8');
    }    
    return "";
}

/**
 * Devolver el tiempo estandar de una operación
 * @param array ['p.copr' => 'codigo_operacion', 'pc.nnope' => 'op']
 * @param PDO $conn Conexión a la base de datos
 * 
 * @return float
 */
function get_tiempo_estandar(array $param, $conn): float {
    if (!$conn || count($param)<1) return 0.0;

    $whereData = blindValueWhereCondiciones($param); // Validar y sanitizar los parámetros

    $condiciones = $whereData['condiciones'] ?? [];
    $bindings = $whereData['bindings'] ?? [];

    if (empty($condiciones) || empty($bindings)) return 0.0;

    $sql = "SELECT 
                p.qtstd as tiempo_estandar
            FROM pctbmopd pc
            INNER JOIN pctoxc p ON pc.copr = p.copr
            WHERE 
            ".implode(" AND ", $condiciones);

    $stmt = $conn->prepare($sql);

    foreach ($bindings as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }

    $stmt->execute();

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($data['tiempo_estandar'])) {
        return floatval($data['tiempo_estandar']);
    }    
    return 0.0;
}