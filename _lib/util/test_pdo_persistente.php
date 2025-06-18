<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/loader/env_loader.php');

function getPDOConnection(): PDO {
    $conf = EnvConfig::getMySQL();
    $dsn = "mysql:host={$conf['host']};port={$conf['port']};dbname=bd_mes;charset=utf8mb4";

    $pdo = new PDO($dsn, $conf['user'], $conf['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

    return $pdo;
}

$pid = getmypid(); // PID del proceso PHP-FPM

try {
    $pdo = getPDOConnection();
    $stmt = $pdo->query("SELECT NOW() as ahora");

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "PID del proceso PHP-FPM: <b>$pid</b><br>";
    echo "Conexión persistente activa.<br>";
    echo "Hora actual desde MySQL: <b>{$resultado['ahora']}</b><br>";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}