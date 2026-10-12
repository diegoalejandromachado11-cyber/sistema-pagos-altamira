<?php
// =====================================================
// config/conexion.php: conexión a MySQL con PDO.
// Se crea una sola conexión y todos los modelos la comparten.
// =====================================================

class Conexion
{
    private static $pdo = null;

    public static function conectar()
    {
        if (self::$pdo === null) {
            $servidor = "localhost";
            $usuario  = "root";
            $clave    = "";          // en XAMPP la clave de root viene vacía
            $base     = "altamira";

            try {
                self::$pdo = new PDO("mysql:host=$servidor;dbname=$base;charset=utf8mb4", $usuario, $clave);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
