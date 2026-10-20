<h3 class="mb-4"><?= esAdmin() ? "Pagos" : "Mis pagos" ?></h3>

<?php if (esAdmin()) { // Solo el administrador ve el formulario para registrar pagos ?>
<div class="card mb-4">
    <div class="card-header">Registrar pago
        <?php if ($tasaHoy) { ?>
            <span class="badge bg-success float-end">Tasa BCV: <?= number_format($tasaHoy['tasa_usd'], 4, ',', '.') ?> Bs (<?= $tasaHoy['fecha'] ?>)</span>
        <?php } ?>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?c=pagos&a=guardar" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Vecino</label>
                <select name="id_usuario" class="form-select" required>
                    <?php foreach ($usuarios as $u) { ?>
                        <option value="<?= $u['id_usuario'] ?>" <?= $u['id_usuario'] == $pre['id_usuario'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nombre']) ?> - <?= htmlspecialchars($u['cedula']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Rubro</label>
                <!-- data-usd guarda el monto del mes en dólares de cada rubro -->
                <select name="id_rubro" id="rubro" class="form-select" onchange="calcularMonto()" required>
                    <?php foreach ($rubros as $r) { ?>
                        <option value="<?= $r['id_rubro'] ?>" data-usd="<?= $r['monto_base'] ?>" <?= $r['id_rubro'] == $pre['id_rubro'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['nombre']) ?> ($<?= $r['monto_base'] ?><?= $r['mensual'] ? '/mes' : ', único' ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Monto (Bs)</label>
                <input type="number" step="0.01" min="0.01" name="monto" id="monto" class="form-control" required>
                <small class="text-muted" id="ayudaMonto"></small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha de pago</label>
                <input type="date" name="fecha_pago" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mes que paga</label>
                <input type="month" name="periodo" class="form-control" value="<?= htmlspecialchars($pre['periodo']) ?>" required>
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
                <button class="btn btn-primary">Registrar pago y generar factura</button>
                <small class="text-muted ms-2">No se permite pagar dos veces el mismo rubro del mismo mes.</small>
            </div>
        </form>
    </div>
</div>

<?php } else { ?>
<div class="alert alert-info">
    Para registrar un pago, envíe su comprobante a la Junta de Condominio. El administrador lo verifica y lo registra;
    después usted podrá ver aquí el pago y descargar su factura.
</div>
<?php } ?>

<!-- BUSCADOR (usa GET para que la búsqueda quede en la dirección y se pueda recargar) -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="c" value="pagos">
            <?php if (esAdmin()) { ?>
            <div class="col-md-4">
                <label class="form-label">Cédula o nombre</label>
                <input type="text" name="ci" class="form-control" placeholder="Ej.: 31525776" value="<?= htmlspecialchars($filtros['ci']) ?>">
            </div>
            <?php } ?>
            <div class="col-md-3">
                <label class="form-label">Rubro</label>
                <select name="rubro" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($rubros as $r) { ?>
                        <option value="<?= $r['id_rubro'] ?>" <?= $r['id_rubro'] == $filtros['rubro'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mes</label>
                <input type="month" name="periodo" class="form-control" value="<?= htmlspecialchars($filtros['periodo']) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-dark w-100">Buscar</button>
            </div>
            <div class="col-12"><a href="index.php?c=pagos" class="small">Limpiar búsqueda</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Historial de pagos: <?= count($pagos) ?> resultado(s)
        <span class="float-end">
            Total: <?= number_format(array_sum(array_column($pagos, 'monto')), 2, ',', '.') ?> Bs
            / $ <?= number_format(array_sum(array_column($pagos, 'monto_usd')), 2, ',', '.') ?>
        </span>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover table-sm text-nowrap">
            <thead>
                <tr><th>Fecha</th><th>Vecino</th><th>Cédula</th><th>Rubro</th><th>Mes</th><th>Método</th><th>Ref.</th>
                    <th>Monto Bs</th><th>Tasa</th><th>Monto $</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($pagos as $p) { ?>
                <tr>
                    <td><?= $p['fecha_pago'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['cedula']) ?></td>
                    <td><?= htmlspecialchars($p['rubro']) ?></td>
                    <td><?= $p['periodo'] ?></td>
                    <td><?= htmlspecialchars($p['metodo_pago']) ?></td>
                    <td><?= htmlspecialchars($p['referencia'] ?? '') ?></td>
                    <td><?= number_format($p['monto'], 2, ',', '.') ?></td>
                    <td><?= $p['tasa_usd'] ? number_format($p['tasa_usd'], 4, ',', '.') : '-' ?></td>
                    <td><?= $p['monto_usd'] !== null ? number_format($p['monto_usd'], 2, ',', '.') : '-' ?></td>
                    <td class="text-nowrap">
                        <a href="index.php?c=pagos&a=factura&id=<?= $p['id_pago'] ?>" class="btn btn-sm btn-outline-primary">Factura</a>
                        <?php if (esAdmin()) { ?>
                            <a href="index.php?c=pagos&a=eliminar&id=<?= $p['id_pago'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Eliminar este pago?')">Eliminar</a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if (!$pagos) { ?>
                <tr><td colspan="11" class="text-center text-muted">No se encontraron pagos.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (esAdmin()) { ?>
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
<?php } ?>
