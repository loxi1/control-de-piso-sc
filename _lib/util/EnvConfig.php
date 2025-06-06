<?php
class EnvConfig
{
    public static function getMySQL(): array
    {
        return [
            'host'     => $_ENV['DB_MYSQL_HOST']     ?? '',
            'port'     => $_ENV['DB_MYSQL_PORT']     ?? '3306',
            'user'     => $_ENV['DB_MYSQL_USER']     ?? '',
            'password' => $_ENV['DB_MYSQL_PASSWORD'] ?? '',
            'scm'      => $_ENV['DB_MYSQL_SCM']      ?? '',
            'mes'      => $_ENV['DB_MYSQL_MES']      ?? '',
        ];
    }

    public static function getSybase(): array
    {
        return [
            'host'     => $_ENV['DB_SYBASE_HOST']     ?? '',
            'port'     => $_ENV['DB_SYBASE_PORT']     ?? '',
            'user'     => $_ENV['DB_SYBASE_USER']     ?? '',
            'password' => $_ENV['DB_SYBASE_PASSWORD'] ?? '',
            'dbname'   => $_ENV['DB_SYBASE_DBNAME']   ?? '',
        ];
    }
}