<h3 class="mb-4">Pagos</h3>

<div class="card mb-4">
    <div class="card-header">Registrar pago
        <?php if ($tasaHoy) { ?>
            <span class="badge bg-success float-end">Tasa BCV: <?= number_format($tasaHoy['tasa_usd'], 4, ',', '.') ?> Bs</span>
        <?php } ?>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?c=pagos&a=guardar" class="row g-3">
            <?php if (esAdmin()) { ?>
            <div class="col-md-4">
                <label class="form-label">Vecino</label>
                <select name="id_usuario" class="form-select" required>
                    <?php foreach ($usuarios as $u) { ?>
                        <option value="<?= $u['id_usuario'] ?>"><?= htmlspecialchars($u['nombre']) ?> - <?= htmlspecialchars($u['cedula']) ?></option>
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
                <button class="btn btn-primary">Registrar pago</button>
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
                    <td><?= htmlspecialchars($p['referencia'] ?? '') ?></td>
                    <td><?= number_format($p['monto'], 2, ',', '.') ?></td>
                    <td><?= $p['tasa_usd'] ? number_format($p['tasa_usd'], 4, ',', '.') : '-' ?></td>
                    <td><?= $p['monto_usd'] !== null ? number_format($p['monto_usd'], 2, ',', '.') : '-' ?></td>
                    <?php if (esAdmin()) { ?>
                        <td><a href="index.php?c=pagos&a=eliminar&id=<?= $p['id_pago'] ?>" class="btn btn-sm btn-danger"
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
    if (!opcion) return;
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
