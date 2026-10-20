<h3 class="mb-3">Tasa BCV</h3>

<div class="alert alert-info">
    La tasa se actualiza <strong>automáticamente a las 9:30 am y a las 3:30 pm</strong> (hora de Venezuela)
    cuando hay conexión a internet. Si no hay conexión, el sistema sigue funcionando con la última tasa guardada.
    <br><small>Último intento de actualización: <?= $ultimoIntento ?: "ninguno todavía" ?>
    (el resultado se ve en Auditoría).</small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5>Obtener automáticamente</h5>
                <p class="text-muted">Consulta la página oficial del Banco Central de Venezuela y guarda la tasa del dólar de hoy.</p>
                <form method="POST" action="index.php?c=tasa&a=actualizar">
                    <button class="btn btn-success">Actualizar desde el BCV</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5>Registrar manualmente</h5>
                <form method="POST" action="index.php?c=tasa&a=manual" class="row g-2">
                    <div class="col-6">
                        <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.0001" name="tasa_usd" class="form-control" placeholder="Tasa Bs/USD" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Historial de tasas</div>
    <div class="card-body table-responsive">
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
