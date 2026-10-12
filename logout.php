<?php
require "conexion.php";

if (isset($_SESSION['id_usuario'])) {
    registrarAuditoria($conexion, "LOGOUT", "usuario", $_SESSION['id_usuario'], "Cierre de sesión");
}

session_destroy();
header("Location: login.php");
