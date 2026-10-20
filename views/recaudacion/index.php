<h3 class="mb-4">Recaudación del mes</h3>

<form method="GET" action="index.php" class="row g-2 align-items-end mb-4">
    <input type="hidden" name="c" value="recaudacion">
    <div class="col-md-3">
        <label class="form-label">Mes</label>
        <input type="month" name="periodo" class="form-control" value="<?= htmlspecialchars($periodo) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Cédula o nombre</label>
        <input type="text" name="ci" class="form-control" value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <div class="col-md-3">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="solo_pendientes" id="sp" value="1" <?= $soloPend ? 'checked' : '' ?>>
            <label class="form-check-label" for="sp">Solo los que no han pagado</label>
        </div>
    </div>
    <div class="col-md-2"><button class="btn btn-dark w-100">Ver</button></div>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary h-100"><div class="card-body">
            <div>Total recaudado en <?= htmlspecialchars($periodo) ?></div>
            <h4>$ <?= number_format($totalUsd, 2, ',', '.') ?></h4>
            <small><?= number_format($totalBs, 2, ',', '.') ?> Bs</small>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success h-100"><div class="card-body">
            <div>Vecinos al día</div>
            <h4><?= $vecinos - $sinPagar ?> de <?= $vecinos ?></h4>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-warning h-100"><div class="card-body">
            <div>Vecinos que no han pagado</div>
            <h4><?= $sinPagar ?> de <?= $vecinos ?></h4>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Pagado por cada vecino en <?= htmlspecialchars($periodo) ?></div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-sm text-nowrap">
            <thead><tr><th>Vecino</th><th>Cédula</th><th class="text-end">Pagado $</th><th class="text-end">Pagado Bs</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($filas as $f) { ?>
                <tr>
                    <td><?= htmlspecialchars($f['vecino']['nombre']) ?></td>
                    <td><?= htmlspecialchars($f['vecino']['cedula']) ?></td>
                    <td class="text-end"><?= number_format($f['usd'], 2, ',', '.') ?></td>
                    <td class="text-end"><?= number_format($f['bs'], 2, ',', '.') ?></td>
                    <td>
                        <?php if ($f['pendientes']) { ?>
                            <span class="badge bg-warning text-dark">Debe: <?= htmlspecialchars(implode(", ", $f['pendientes'])) ?></span>
                        <?php } else { ?>
                            <span class="badge bg-success">Al día</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <?php if (!$filas) { ?>
                <tr><td colspan="5" class="text-center text-muted">No hay vecinos para mostrar.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
