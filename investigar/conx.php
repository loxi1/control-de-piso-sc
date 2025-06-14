<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/loader/env_loader.php');

class PDOp
{
    public static function mysql(string $db): ?PDO
    {
        $conf = EnvConfig::getMySQL();
        try {
            $dsn = "mysql:host={$conf['host']};port={$conf['port']};dbname={$db};charset=utf8mb4";
            return new PDO($dsn, $conf['user'], $conf['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
        } catch (PDOException $e) {
            error_log("Error MySQL: " . $e->getMessage());
            return null;
        }
    }

    public static function sybase(): ?PDO
    {
        $conf = EnvConfig::getSybase();
        try {
            $dsn = "dblib:host={$conf['host']}:{$conf['port']};dbname={$conf['dbname']}";
            $pdo = new PDO($dsn, $conf['user'], $conf['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            error_log("Error Sybase: " . $e->getMessage());
            return null;
        }
    }
}