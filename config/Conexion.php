<?php
/**
 * Clase de conexión a la base de datos usando PDO.
 */

class Conexion
{
    private static ?PDO $conexion = null;

    /**
     * Retorna una única conexión PDO activa.
     */
    public static function conectar(): PDO
    {
        if (self::$conexion === null) {
            $dsn = 'mysql:host=' . Config::DB_HOST .
                   ';dbname=' . Config::DB_NAME .
                   ';charset=' . Config::DB_CHARSET;

            self::$conexion = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$conexion;
    }
}