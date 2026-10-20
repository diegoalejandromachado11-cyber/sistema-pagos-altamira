<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Urbanización Altamira - Pagos</title>
    <!-- Bootstrap 5 guardado dentro del proyecto: funciona sin internet -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-xl navbar-dark bg-primary mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="index.php">Urb. Altamira</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.php?c=inicio">Inicio</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.php?c=pagos">Pagos</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.php?c=deudas"><?= esAdmin() ? "Deudas" : "Mi deuda" ?></a></li>
                <?php if (esAdmin()) { ?>
                    <li class="nav-item"><a class="nav-link text-nowrap" href="index.php?c=recaudacion">Recaudación</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap" href="index.php?c=gastos">Gastos</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap" href="index.php?c=rubros">Rubros</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap" href="index.php?c=usuarios">Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap" href="index.php?c=tasa">Tasa BCV</a></li>
                    <li class="nav-item"><a class="nav-link text-nowrap" href="index.php?c=auditoria">Auditoría</a></li>
                <?php } ?>
            </ul>
            <span class="navbar-text text-white text-nowrap me-3">
                <?= htmlspecialchars($_SESSION['nombre']) ?>
                <span class="badge bg-light text-primary"><?= htmlspecialchars($_SESSION['nombre_rol']) ?></span>
            </span>
            <a href="index.php?c=auth&a=logout" class="btn btn-outline-light btn-sm">Salir</a>
        </div>
    </div>
</nav>

<div class="container">

<?php
// Mensaje que dejó el controlador antes de redirigir (se muestra una sola vez)
if (isset($_SESSION['mensaje'])) { ?>
    <div class="alert alert-<?= $_SESSION['tipo'] ?>"><?= htmlspecialchars($_SESSION['mensaje']) ?></div>
<?php
    unset($_SESSION['mensaje'], $_SESSION['tipo']);
} ?>
