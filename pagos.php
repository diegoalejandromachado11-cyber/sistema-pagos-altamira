<?php
require "conexion.php";
verificarSesion();

$mensaje = "";
$tipo = "success";

// ---------- REGISTRAR PAGO ----------
if (isset($_POST['guardar'])) {
    // El vecino solo puede registrar pagos a su nombre; el admin elige el vecino
    $id_usuario = esAdmin() ? $_POST['id_usuario'] : $_SESSION['id_usuario'];
    $monto = $_POST['monto'];  // monto en bolívares

    // Buscamos la tasa BCV de la fecha del pago para convertir a dólares
    $tasa = buscarTasa($conexion, $_POST['fecha_pago']);
    $id_tasa   = $tasa ? $tasa['id_tasa'] : null;
    $monto_usd = $tasa ? round($monto / $tasa['tasa_usd'], 2) : null;

    $sql = "INSERT INTO pago (id_usuario, id_rubro, id_tasa, monto, monto_usd, fecha_pago, periodo, metodo_pago, referencia)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $conexion->prepare($sql)->execute([
        $id_usuario, $_POST['id_rubro'], $id_tasa, $monto, $monto_usd,
        $_POST['fecha_pago'], $_POST['periodo'], $_POST['metodo_pago'], $_POST['referencia']
    ]);

    registrarAuditoria($conexion, "CREAR", "pago", $conexion->lastInsertId(), "Pago de $monto Bs");
    $mensaje = "Pago registrado" . ($tasa ? " ($monto_usd USD)" : ". Atención: no hay tasa BCV registrada, no se calculó el monto en dólares");
    if (!$tasa) $tipo = "warning";
}

// ---------- ELIMINAR PAGO (solo admin) ----------
if (isset($_GET['eliminar']) && esAdmin()) {
    $conexion->prepare("DELETE FROM pago WHERE id_pago = ?")->execute([$_GET['eliminar']]);
    registrarAuditoria($conexion, "ELIMINAR", "pago", $_GET['eliminar'], "Pago eliminado");
    $mensaje = "Pago eliminado";
}

// ---------- LISTAR ----------
$sql = "SELECT p.*, u.nombre, u.cedula, r.nombre AS rubro, t.tasa_usd
        FROM pago p
        JOIN usuario u ON p.id_usuario = u.id_usuario
        JOIN rubro r ON p.id_rubro = r.id_rubro
        LEFT JOIN tasa_bcv t ON p.id_tasa = t.id_tasa";
if (!esAdmin()) {
    $sql .= " WHERE p.id_usuario = " . (int) $_SESSION['id_usuario'];
}
$sql .= " ORDER BY p.fecha_pago DESC";
$pagos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$rubros   = $conexion->query("SELECT * FROM rubro ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $conexion->query("SELECT * FROM usuario ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$tasaHoy  = buscarTasa($conexion, date("Y-m-d"));

include "header.php";
?>

<h3 class="mb-4">Pagos</h3>

<?php if ($mensaje) { ?>
    <div class="alert alert-<?= $tipo ?>"><?= $mensaje ?></div>
<?php } ?>

<div class="card mb-4">
    <div class="card-header">Registrar pago
        <?php if ($tasaHoy) { ?>
            <span class="badge bg-success float-end">Tasa BCV: <?= number_format($tasaHoy['tasa_usd'], 4, ',', '.') ?> Bs</span>
        <?php } ?>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <?php if (esAdmin()) { ?>
            <div class="col-md-4">
                <label class="form-label">Vecino</label>
                <select name="id_usuario" class="form-select" required>
                    <?php foreach ($usuarios as $u) { ?>
                        <option value="<?= $u['id_usuario'] ?>"><?= htmlspecialchars($u['nombre']) ?> - <?= $u['cedula'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <?php } ?>
            <div class="col-md-4">
                <label class="form-label">Rubro</label>
                <!-- data-usd guarda el monto del mes en dólares de cada rubro -->
                <select name="id_rubro" id="rubro" class="form-select" onchange="calcularMonto()" required>
                    <?php foreach ($rubros as $r) { ?>
                        <option value="<?= $r['id_rubro'] ?>" data-usd="<?= $r['monto_base'] ?>"><?= htmlspecialchars($r['nombre']) ?> ($<?= $r['monto_base'] ?>)</option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Monto (Bs)</label>
                <input type="number" step="0.01" name="monto" id="monto" class="form-control" required>
                <small class="text-muted" id="ayudaMonto"></small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha de pago</label>
                <input type="date" name="fecha_pago" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Periodo</label>
                <input type="month" name="periodo" class="form-control" value="<?= date('Y-m') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Método de pago</label>
                <select name="metodo_pago" class="form-select">
                    <option>Pago móvil</option>
                    <option>Transferencia</option>
                    <option>Efectivo</option>
                    <option>Zelle</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Referencia</label>
                <input type="text" name="referencia" class="form-control">
            </div>
            <div class="col-12">
                <button name="guardar" class="btn btn-primary">Registrar pago</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Historial de pagos</div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr><th>Fecha</th><th>Vecino</th><th>Rubro</th><th>Periodo</th><th>Método</th><th>Ref.</th>
                    <th>Monto Bs</th><th>Tasa</th><th>Monto $</th><?php if (esAdmin()) echo "<th></th>"; ?></tr>
            </thead>
            <tbody>
            <?php foreach ($pagos as $p) { ?>
                <tr>
                    <td><?= $p['fecha_pago'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['rubro']) ?></td>
                    <td><?= $p['periodo'] ?></td>
                    <td><?= htmlspecialchars($p['metodo_pago']) ?></td>
                    <td><?= htmlspecialchars($p['referencia']) ?></td>
                    <td><?= number_format($p['monto'], 2, ',', '.') ?></td>
                    <td><?= $p['tasa_usd'] ? number_format($p['tasa_usd'], 4, ',', '.') : '-' ?></td>
                    <td><?= $p['monto_usd'] !== null ? number_format($p['monto_usd'], 2, ',', '.') : '-' ?></td>
                    <?php if (esAdmin()) { ?>
                        <td><a href="?eliminar=<?= $p['id_pago'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Eliminar este pago?')">Eliminar</a></td>
                    <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Al elegir un rubro, calcula cuánto hay que pagar en Bs: monto en $ x tasa BCV
var tasa = <?= $tasaHoy ? $tasaHoy['tasa_usd'] : 0 ?>;

function calcularMonto() {
    var opcion = document.getElementById("rubro").selectedOptions[0];
    var usd = parseFloat(opcion.dataset.usd);

    if (tasa > 0) {
        document.getElementById("monto").value = (usd * tasa).toFixed(2);
        document.getElementById("ayudaMonto").innerText = "$" + usd + " x " + tasa + " Bs";
    } else {
        document.getElementById("ayudaMonto").innerText = "Monto del mes: $" + usd + " (no hay tasa BCV registrada)";
    }
}

calcularMonto(); // se ejecuta al abrir la página
</script>

<?php include "footer.php"; ?>
