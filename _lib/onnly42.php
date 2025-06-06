<?php

// 🗃️ Conexión MySQL
define('DB_SERVER_MY', '192.168.150.32');
define('DB_PORT_MY', '3306');
define('DB_NAME_SCM', 'bd_scm');
define('DB_NAME_MES', 'bd_mes');
define('DB_USER_MY', 'fjurado');
define('DB_PASSWORD_MY', '987960662');

// 🗃️ Conexión Sybase
define('SERVER_NAME_SY', '10.20.1.32');
define('DB_USER_SY', 'sa');
define('DB_PASSWORD_SY', '');
define('PORT_SY', '6100');
define('DB_NAME_SY', 'nexus');

// ✅ Conectar a base MySQL y devolver conexión activa
function conectar_mysql($dbs): ?PDO {
    $host = DB_SERVER_MY;
    $port = DB_PORT_MY;
    $db = $dbs;
    $user = DB_USER_MY;
    $password = DB_PASSWORD_MY;
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        $conn = new PDO($dsn, $user, $password, $options);
        return $conn;
    } catch (PDOException $e) {
        responder(500, 'Error de conexión MySQL: ' . $e->getMessage());
    }
}

// ✅ Conectar a base Sybase y devolver conexión activa
function conectar_sybase(): ?PDO {
    try {
        $dsn = "dblib:host=" . SERVER_NAME_SY . ":" . PORT_SY . ";dbname=" . DB_NAME_SY;
        $conn = new PDO($dsn, DB_USER_SY, DB_PASSWORD_SY);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        responder(500, 'Error de conexión Sybase: ' . $e->getMessage());
    }
}

function getEventos():string {
    $btns = "<h1 class='text-center'>Crear eventos</h1>";
    $connMysql = null;
    try {

        $connMysql = conectar_mysql(DB_NAME_SCM);
        //🔎 Buscar informacion

        $sql = "
            select codigo_motivo, motivo, tipo_actividad_id
            from motivo where caracteristica_id=5 and visible=2 and codigo_motivo<>0 order by orden asc;
        ";

        $stmt = $connMysql->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$result || count($result) === 0) {
            return $btns;
        }
        $html = "";
        foreach ($result as $row) {
            $motivoId = (int)$row['codigo_motivo'];
            $tipoId   = (int)$row['tipo_actividad_id'];
            $motivo   = $row['motivo'];
            $html .= '<button class="event-btn" motivoid="' . $motivoId . '" tipo="' . $tipoId . '">' . $motivo . '</button>';
        }
        return $html;
    } catch (Exception $e) {
        return $btns;
    } finally {
        if ($connMysql) {
            $connMysql = null; // Cerrar conexión
        }
    }
}

function verificarRegistroIngreso($param):?array {
    $connMysql = null;

    if(count($param) <1) {
        return null;
    }

    $sq = [];

    foreach ($param as $key => $value) {
        if(!empty($value)) {
            $sq[$key] = "$key = :$key";
        }
    }

    if(empty($sq)) {
        return null;
    }
   
    $connMysql = conectar_mysql(DB_NAME_MES);
    if (!$connMysql) {
        return null;
    }

    try {        
        $sql = "";      
        
        $sql = implode(" AND ", $sq);

        $existe = "select id, turno_id, horario_ingreso, horario_salida, now() fecha_actual,
        horario_maximo, TIMESTAMPDIFF(SECOND, horario_ingreso, NOW()) AS tiempo_transcurrido, horario_minimo, estado 
        from ingreso 
        where $sql and estado = 1
        ORDER BY horario_ingreso desc
        limit 1";
        
        $stmt = $connMysql->prepare($existe);

        foreach ($sq as $key => $value) {
            $stmt->bindParam(":$key", $param[$key]);
        }

        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        $code = 0;
        $msn = "No existe registo de login.";
        $rta = [];

        if (!$info) {
            return [
                'code' => $code,
                'msn' => $msn,
                'data' => $rta
            ];
        }

        $msn = "Turno ya activo.";
        
        $id = $info['id'] ?? null;
        $horario_ingreso = strtotime($info['horario_ingreso']);
        $horario_salida = strtotime($info['horario_salida']);
        $fecha_actual = strtotime($info['fecha_actual']);
        $horario_maximo = strtotime($info['horario_maximo']);
        $horario_minimo = strtotime($info['horario_minimo']);
        $tiempo_transcurrido = intval($info['tiempo_transcurrido'] ?? 0);
        $estado = intval($info['estado'] ?? 1);

        $msn = "Cerrar el turno anterior.";
        $code = 1;

        if($fecha_actual > $horario_minimo && $fecha_actual < $horario_maximo) {
            $msn = "Turno ya activo.";
            $code = 2;
            $sqlu  = "UPDATE ingreso SET fecha_modificacion = NOW() WHERE id = ?"; // One token in effect
            $pdos = $connMysql->prepare($sqlu);
            $pdos->execute( [ 'id', $id ] );

            // Ver si existen ciclos activos
            $sendvalores['tiempo_inicio'] = $horario_minimo;
            $sendvalores['tiempo_fin'] = $horario_maximo;

            if(!empty($param['codigo'])) {
                $sendvalores['usuario_registra'] = $param['codigo'];
            }

            $cant = existencia_ciclos($sendvalores);

            $rta['code'] = ($cant == 0 && $tiempo_transcurrido>0) ? 2 : 1; // Alerta permisos. 2 Muesra, 1 No muestra
            $rta['id'] = $id;
            $rta['titulo'] = "";
            $rta['descripcion'] = "";
            $rta['horario_ingreso'] = $info[2];
            if($rta['code'] == 2) {
                $rta['titulo'] = "¿Tiene Permiso?";
                $rta['descripcion'] = "!Estas ingresando tarde¡ Turno: ".date('h:i A', $horario_ingreso)." - ".date('h:i A', $horario_salida);
            }
        } else {
            $code = 1;
            // Registro anterior que no fue cerrado. Se debe calcular la eficiencia
            if($estado == 1) {
                $sqlu  = "UPDATE ingreso SET estado = 2, fecha_modificacion = NOW() WHERE id = ?"; // One token in effect
                $pdos = $connMysql->prepare($sqlu);
                $pdos->execute( [ 'id', $id ] );
                $msn = "Turno cerrado correctamente.";
            } else {
                $msn = "Turno ya cerrado.";
            }
        }

        return [
            'code' => $code,
            'msn' => $msn,
            'data' => $rta
        ];
    } catch (Exception $e) {
        return null;
    } finally {
        if ($connMysql) {
            $connMysql = null; // Cerrar conexión
        }
    }
}

function existencia_ciclos($parm): int {
    if(empty($parm) || !is_array($parm)) {
        return 0;
    }
    
    if (empty($param['tiempo_inicio']) || empty($param['tiempo_fin']) || empty($param['usuario_registra'])) {
        return 0;
    }

    $connMysql = conectar_mysql(DB_NAME_MES);
    if ($connMysql) {
        return 0;
    }

    try {
        $parm['tiempo_inicio'] = date("Y-m-d H:i:s", $parm['tiempo_inicio']);
        $parm['tiempo_fin'] = date("Y-m-d H:i:s", $parm['tiempo_fin']);

        $sqlciclos = "SELECT COUNT(*) AS cantidad
            FROM ciclo
            WHERE estado_id = 1
            AND usuario_registra = :usuario_registra
            AND tiempo_inicio > :tiempo_inicio
            AND tiempo_fin < :tiempo_fin";
        
        $stmt = $connMysql->prepare($sqlciclos);

        foreach ($parm as $key => $vv) {
            $stmt->bindParam(":$key", $vv);
        }

        $stmt->execute();
        $rta = $stmt->fetch(PDO::FETCH_ASSOC);

        return intval($rta['cantidad'] ?? 0);       
    } catch (Exception $e) {
        return 0; // Error en las fechas o usuario
    } finally {
        if ($connMysql) {
            $connMysql = null; // Cerrar conexión
        }
    }
}