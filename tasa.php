<?php
require "conexion.php";
verificarSesion();
soloAdmin();

$mensaje = "";
$tipo = "success";

// Botón "Actualizar desde el BCV": hace el scraping y guarda la tasa de hoy
if (isset($_POST['actualizar'])) {
    $valor = obtenerTasaBCV();

    if ($valor) {
        // Si ya existe la tasa de hoy se actualiza, si no se inserta
        $sql = "INSERT INTO tasa_bcv (fecha, tasa_usd) VALUES (CURDATE(), ?)
                ON DUPLICATE KEY UPDATE tasa_usd = VALUES(tasa_usd)";
        $conexion->prepare($sql)->execute([$valor]);

        registrarAuditoria($conexion, "SCRAPING", "tasa_bcv", null, "Tasa obtenida del BCV: $valor");
        $mensaje = "Tasa obtenida del BCV: " . number_format($valor, 4, ',', '.') . " Bs/USD";
    } else {
        $mensaje = "No se pudo obtener la tasa del BCV. Puede registrarla manualmente.";
        $tipo = "danger";
    }
}

// Registro manual (por si la página del BCV no responde)
if (isset($_POST['manual'])) {
    $sql = "INSERT INTO tasa_bcv (fecha, tasa_usd) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE tasa_usd = VALUES(tasa_usd)";
    $conexion->prepare($sql)->execute([$_POST['fecha'], $_POST['tasa_usd']]);

    registrarAuditoria($conexion, "CREAR", "tasa_bcv", null, "Tasa manual: " . $_POST['tasa_usd']);
    $mensaje = "Tasa registrada manualmente";
}

$tasas = $conexion->query("SELECT * FROM tasa_bcv ORDER BY fecha DESC")->fetchAll(PDO::FETCH_ASSOC);

include "header.php";
?>

<h3 class="mb-4">Tasa BCV</h3>

<?php if ($mensaje) { ?>
    <div class="alert alert-<?= $tipo ?>"><?= $mensaje ?></div>
<?php } ?>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5>Obtener automáticamente</h5>
                <p class="text-muted">Consulta la página oficial del Banco Central de Venezuela y guarda la tasa del dólar de hoy.</p>
                <form method="POST">
                    <button name="actualizar" class="btn btn-success">Actualizar desde el BCV</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5>Registrar manualmente</h5>
                <form method="POST" class="row g-2">
                    <div class="col-6">
                        <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.0001" name="tasa_usd" class="form-control" placeholder="Tasa Bs/USD" required>
                    </div>
                    <div class="col-12">
                        <button name="manual" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Historial de tasas</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Fecha</th><th>Tasa (Bs/USD)</th><th>Registrado</th></tr></thead>
            <tbody>
            <?php foreach ($tasas as $t) { ?>
                <tr>
                    <td><?= $t['fecha'] ?></td>
                    <td><?= number_format($t['tasa_usd'], 4, ',', '.') ?></td>
                    <td><?= $t['fecha_registro'] ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "footer.php"; ?>
