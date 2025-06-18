<?php
// test_pdo_persistente.php

$pid = getmypid();
echo "PID del proceso PHP-FPM: $pid<br>";

try {
    $pdo = new PDO(
        'mysql:host=192.168.150.32;dbname=bd_mes;charset=utf8mb4',
        'fjurado',
        '987960662',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true,
        ]
    );
    echo "Conexión persistente activa.<br>";

    $stmt = $pdo->query("SELECT NOW() AS ahora");
    $hora = $stmt->fetchColumn();
    echo "Hora actual desde MySQL: $hora<br>";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}