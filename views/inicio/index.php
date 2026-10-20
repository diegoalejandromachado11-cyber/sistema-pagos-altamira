<h3 class="mb-4">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-success h-100">
            <div class="card-body">
                <div>Tasa BCV</div>
                <h4><?= $tasa ? number_format($tasa['tasa_usd'], 4, ',', '.') . " Bs" : "Sin registrar" ?></h4>
                <small><?= $tasa ? "Del " . $tasa['fecha'] : "" ?></small>
            </div>
        </div>
    </div>

    <?php if (esAdmin()) { ?>
    <div class="col-md-3">
        <a href="index.php?c=recaudacion" class="text-decoration-none">
        <div class="card text-bg-primary h-100">
            <div class="card-body">
                <div>Recaudado en <?= $mes ?></div>
                <h4>$ <?= number_format($recaudadoMes, 2, ',', '.') ?></h4>
                <small>Total histórico: $ <?= number_format($totalPagos, 2, ',', '.') ?></small>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="index.php?c=recaudacion&solo_pendientes=1" class="text-decoration-none">
        <div class="card text-bg-warning h-100">
            <div class="card-body">
                <div>Vecinos que no han pagado <?= $mes ?></div>
                <h4><?= $sinPagarMes ?> de <?= $totalVecinos ?></h4>
                <small>Ver la lista</small>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-danger h-100">
            <div class="card-body">
                <div>Total gastos</div>
                <h4><?= number_format($totalGastos, 2, ',', '.') ?> Bs</h4>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="col-md-3">
        <div class="card text-bg-primary h-100">
            <div class="card-body">
                <div>Total pagado</div>
                <h4>$ <?= number_format($totalPagos, 2, ',', '.') ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <a href="index.php?c=deudas" class="text-decoration-none">
        <div class="card <?= $miDeuda > 0 ? 'text-bg-danger' : 'text-bg-success' ?> h-100">
            <div class="card-body">
                <div>Mi deuda</div>
                <h4>$ <?= number_format($miDeuda, 2, ',', '.') ?></h4>
                <small><?= $miDeuda > 0 ? "$misMeses mes(es) pendiente(s) — ver detalle" : "Está al día" ?></small>
            </div>
        </div>
        </a>
    </div>
    <?php } ?>
</div>

<div class="card">
    <div class="card-header">Últimos pagos</div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-sm text-nowrap">
            <thead><tr><th>Fecha</th><th>Vecino</th><th>Rubro</th><th>Periodo</th><th>Monto Bs</th><th>Monto $</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($ultimos as $p) { ?>
                <tr>
                    <td><?= $p['fecha_pago'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['rubro']) ?></td>
                    <td><?= $p['periodo'] ?></td>
                    <td><?= number_format($p['monto'], 2, ',', '.') ?></td>
                    <td><?= $p['monto_usd'] !== null ? number_format($p['monto_usd'], 2, ',', '.') : '-' ?></td>
                    <td><a href="index.php?c=pagos&a=factura&id=<?= $p['id_pago'] ?>" class="btn btn-sm btn-outline-primary">Factura</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
