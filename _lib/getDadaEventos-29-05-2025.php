<?php

// Conexión MySQL
define('DB_SERVER_MY', '192.168.150.32');
define('DB_PORT_MY', '3306');
define('DB_NAME_MY', 'bd_scm');
define('DB_USER_MY', 'fjurado');
define('DB_PASSWORD_MY', '987960662');

// ✅ Conectar a base MySQL y devolver conexión activa
function conectar_mysql(): ?PDO
{
    // ✅ Conectar a base Sybase y devolver conexión activa
    $host = DB_SERVER_MY;
    $port = DB_PORT_MY;
    $db = DB_NAME_MY;
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

function getEventos(): string
{
    $btns = "<h1 class='text-center'>Crear eventos</h1>";
    try {

        $connMysql = conectar_mysql();
        //🔎 Buscar informacion

        $sql = "
            select codigo_motivo, motivo, tipo_actividad_id
            from motivo where caracteristica_id=5 and codigo_motivo<>0 order by orden asc;
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
    }
}
