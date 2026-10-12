<?php
// =====================================================
// conexion.php: conecta con la base de datos MySQL
// e inicia la sesión. Se incluye en todas las páginas.
// =====================================================

$servidor = "localhost";
$usuario  = "root";
$clave    = "";          // en XAMPP la clave de root viene vacía
$base     = "altamira";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$base;charset=utf8mb4", $usuario, $clave);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

session_start();

require_once "funciones.php";
