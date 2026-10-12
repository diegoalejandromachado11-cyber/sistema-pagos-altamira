<?php
require "conexion.php";
verificarSesion();

// Tasa más reciente registrada
$tasa = buscarTasa($conexion, date("Y-m-d"));

if (esAdmin()) {
    // Totales generales para el administrador
    $totalPagos  = $conexion->query("SELECT COALESCE(SUM(monto_usd),0) FROM pago")->fetchColumn();
    $totalGastos = $conexion->query("SELECT COALESCE(SUM(monto),0) FROM gasto")->fetchColumn();
    $totalVecinos = $conexion->query("SELECT COUNT(*) FROM usuario WHERE id_rol = 2")->fetchColumn();
} else {
    // Totales del vecino conectado
    $stmt = $conexion->prepare("SELECT COALESCE(SUM(monto_usd),0) FROM pago WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['id_usuario']]);
    $totalPagos = $stmt->fetchColumn();
}

// Últimos 5 pagos (todos si es admin, solo los suyos si es vecino)
$sql = "SELECT p.*, u.nombre, r.nombre AS rubro FROM pago p
        JOIN usuario u ON p.id_usuario = u.id_usuario
        JOIN rubro r ON p.id_rubro = r.id_rubro";
if (!esAdmin()) {
    $sql .= " WHERE p.id_usuario = " . (int) $_SESSION['id_usuario'];
}
$sql .= " ORDER BY p.id_pago DESC LIMIT 5";
$ultimos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);

include "header.php";
?>

<h3 class="mb-4">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <div>Tasa BCV</div>
                <h4><?= $tasa ? number_format($tasa['tasa_usd'], 4, ',', '.') . " Bs" : "Sin registrar" ?></h4>
                <small><?= $tasa ? $tasa['fecha'] : "" ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <div><?= esAdmin() ? "Total recaudado" : "Total pagado" ?></div>
                <h4>$ <?= number_format($totalPagos, 2, ',', '.') ?></h4>
            </div>
        </div>
    </div>
    <?php if (esAdmin()) { ?>
    <div class="col-md-3">
        <div class="card text-bg-danger">
            <div class="card-body">
                <div>Total gastos</div>
                <h4><?= number_format($totalGastos, 2, ',', '.') ?> Bs</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-secondary">
            <div class="card-body">
                <div>Vecinos registrados</div>
                <h4><?= $totalVecinos ?></h4>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<div class="card">
    <div class="card-header">Últimos pagos</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Fecha</th><th>Vecino</th><th>Rubro</th><th>Periodo</th><th>Monto Bs</th><th>Monto $</th></tr></thead>
            <tbody>
            <?php foreach ($ultimos as $p) { ?>
                <tr>
                    <td><?= $p['fecha_pago'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['rubro']) ?></td>
                    <td><?= $p['periodo'] ?></td>
                    <td><?= number_format($p['monto'], 2, ',', '.') ?></td>
                    <td><?= number_format($p['monto_usd'], 2, ',', '.') ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "footer.php"; ?>
