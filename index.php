<?php
// =====================================================
// index.php: CONTROLADOR FRONTAL (punto de entrada único)
//
// Todas las páginas pasan por aquí. La URL indica qué
// controlador y qué acción ejecutar:
//
//   index.php?c=pagos&a=guardar
//              │         └── acción  (método del controlador)
//              └──────────── controlador (PagosController)
// =====================================================

session_start();

require_once "config/conexion.php";
require_once "controllers/Controller.php";

// Carga automática de clases: al usar "Pago" busca models/Pago.php,
// al usar "PagosController" busca controllers/PagosController.php
spl_autoload_register(function ($clase) {
    foreach (["models/", "controllers/"] as $carpeta) {
        if (file_exists($carpeta . $clase . ".php")) {
            require_once $carpeta . $clase . ".php";
            return;
        }
    }
});

// Si no se indica nada, se va al inicio
$c = $_GET['c'] ?? 'inicio';
$a = $_GET['a'] ?? 'index';

// "pagos" -> "PagosController"
$nombreControlador = ucfirst(strtolower($c)) . "Controller";

// Solo se aceptan letras para evitar que se carguen archivos indebidos
if (!preg_match('/^[a-zA-Z]+$/', $c) || !file_exists("controllers/$nombreControlador.php")) {
    http_response_code(404);
    die("Página no encontrada");
}

$controlador = new $nombreControlador();

// Se ejecuta la acción solo si es un método público del controlador
if (!is_callable([$controlador, $a])) {
    http_response_code(404);
    die("Acción no encontrada");
}

$controlador->$a();
