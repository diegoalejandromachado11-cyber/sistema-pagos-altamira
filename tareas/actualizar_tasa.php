<?php
// =====================================================
// tareas/actualizar_tasa.php  (OPCIONAL)
//
// El sistema ya actualiza la tasa solo, la primera vez que alguien
// lo usa después de las 9:30 am o de las 3:30 pm.
// Este archivo sirve para que Windows lo haga a la hora exacta
// aunque nadie esté usando el sistema. Se programa con el
// "Programador de tareas" de Windows (ver README.md).
//
// Uso:  C:\xampp\php\php.exe C:\xampp\htdocs\altamira\tareas\actualizar_tasa.php
// =====================================================

// Solo se puede ejecutar desde Windows (consola), no desde el navegador
if (php_sapi_name() !== "cli") {
    exit("Este archivo solo se ejecuta desde el Programador de tareas.");
}

chdir(__DIR__ . "/..");   // trabajar desde la carpeta del sistema
date_default_timezone_set("America/Caracas");

require_once "config/conexion.php";
require_once "models/Auditoria.php";
require_once "models/TasaBcv.php";

TasaBcv::actualizarAutomatica();

$tasa = TasaBcv::vigente(date("Y-m-d"));
echo $tasa ? "Tasa vigente: {$tasa['tasa_usd']} Bs/USD ({$tasa['fecha']})\n" : "No hay tasa registrada\n";
