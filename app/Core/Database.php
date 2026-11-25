<?php

class Database
{
    private static $conexion = null;

    public static function connection()
    {
        if (self::$conexion instanceof PDO) {
            return self::$conexion;
        }

        $config = require ROOT . '/config/database.php';

        try {
            $conexion = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
                $config['username'],
                $config['password']
            );
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conexion->exec('SET NAMES utf8mb4');
            self::$conexion = $conexion;
            return $conexion;
        } catch (PDOException $e) {
            echo 'Error en la conexión: ' . $e->getMessage();
            exit;
        }
    }
}
