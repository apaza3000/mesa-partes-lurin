<?php

namespace Src\Core;

use PDO;
use PDOException;



class Conexion
{

    private static $pdo = null;


    // Método para obtener la instancia de PDO
    public static function getConexion(): ?PDO
    {
        $config =require Path::base() . 'config/database.php';
        $dsn = "" . $config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['database'] . ';charset=' . $config['charset'];;
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO($dsn, $config['username'], $config['password']);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw $e;
            }
        }
        return self::$pdo;
    }
}
