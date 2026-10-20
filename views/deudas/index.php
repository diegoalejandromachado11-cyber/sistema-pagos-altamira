<h3 class="mb-1"><?= esAdmin() ? "Deudas" : "Mi deuda" ?></h3>
<p class="text-muted">Se revisan los meses desde <strong><?= $corte ?></strong> (enero del año pasado) hasta hoy.
   Los montos usan el precio actual de cada rubro<?= $tasa ? " y la tasa BCV del " . $tasa['fecha'] : "" ?>.</p>

<?php if (esAdmin()) { ?>
<form method="GET" action="index.php" class="row g-2 mb-3">
    <input type="hidden" name="c" value="deudas">
    <div class="col-md-5">
        <input type="text" name="ci" class="form-control" placeholder="Buscar por cédula o nombre" value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <div class="col-md-2"><button class="btn btn-dark w-100">Buscar</button></div>
    <div class="col-md-2"><a href="index.php?c=deudas" class="btn btn-link">Limpiar</a></div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card text-bg-danger"><div class="card-body">
            <div>Vecinos con deuda</div><h4><?= count($deudas) ?></h4>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-dark"><div class="card-body">
            <div>Total adeudado</div>
            <h4>$ <?= number_format(array_sum(array_column($deudas, 'total')), 2, ',', '.') ?></h4>
            <?php if ($tasa) { ?><small><?= number_format(array_sum(array_column($deudas, 'total')) * $tasa['tasa_usd'], 2, ',', '.') ?> Bs</small><?php } ?>
        </div></div>
    </div>
</div>
<?php } ?>

<?php if (!$deudas) { ?>
    <div class="alert alert-success"><?= esAdmin() ? "No hay vecinos con deuda." : "Usted está al día. No tiene pagos pendientes." ?></div>
<?php } ?>

<?php foreach ($deudas as $i => $d) { ?>
<div class="card mb-2">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong><?= htmlspecialchars($d['vecino']['nombre']) ?></strong>
            <span class="text-muted">— <?= htmlspecialchars($d['vecino']['cedula']) ?></span>
        </div>
        <div>
            <span class="badge bg-secondary"><?= $d['meses'] ?> mes(es)</span>
            <span class="badge bg-danger">$ <?= number_format($d['total'], 2, ',', '.') ?></span>
            <?php if ($tasa) { ?><span class="badge bg-dark"><?= number_format($d['total'] * $tasa['tasa_usd'], 2, ',', '.') ?> Bs</span><?php } ?>
            <!-- Botón de Bootstrap que muestra u oculta el detalle -->
            <button class="btn btn-sm btn-outline-secondary ms-2" data-bs-toggle="collapse" data-bs-target="#detalle<?= $i ?>">Ver detalle</button>
        </div>
    </div>
    <div class="collapse <?= esAdmin() ? '' : 'show' ?>" id="detalle<?= $i ?>">
        <div class="card-body table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Mes</th><th>Rubro</th><th class="text-end">Monto $</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($d['items'] as $item) { ?>
                    <tr>
                        <td><?= $item['periodo'] ?></td>
                        <td><?= htmlspecialchars($item['rubro']) ?></td>
                        <td class="text-end"><?= number_format($item['monto_usd'], 2, ',', '.') ?></td>
                        <td class="text-end">
                            <?php if (esAdmin()) { ?>
                            <!-- Abre el formulario de pagos con el vecino, el rubro y el mes ya seleccionados -->
                            <a class="btn btn-sm btn-success"
                               href="index.php?c=pagos&pre_usuario=<?= $d['vecino']['id_usuario'] ?>&pre_rubro=<?= $item['id_rubro'] ?>&pre_periodo=<?= $item['periodo'] ?>">Registrar pago</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php } ?>
