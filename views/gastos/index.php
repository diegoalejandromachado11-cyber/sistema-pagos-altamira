<h3 class="mb-4">Gastos</h3>

<div class="card mb-4">
    <div class="card-header">Registrar gasto</div>
    <div class="card-body">
        <!-- enctype es necesario para poder subir archivos -->
        <form method="POST" action="index.php?c=gastos&a=guardar" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Rubro</label>
                <select name="id_rubro" class="form-select" required>
                    <?php foreach ($rubros as $r) { ?>
                        <option value="<?= $r['id_rubro'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Monto (Bs)</label>
                <input type="number" step="0.01" name="monto" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Comprobante (imagen o PDF)</label>
                <input type="file" name="comprobante" class="form-control" accept="image/*,.pdf">
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Registrar gasto</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Lista de gastos</div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Fecha</th><th>Rubro</th><th>Descripción</th><th>Monto Bs</th><th>Comprobante</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($gastos as $g) { ?>
                <tr>
                    <td><?= $g['fecha'] ?></td>
                    <td><?= htmlspecialchars($g['rubro']) ?></td>
                    <td><?= htmlspecialchars($g['descripcion'] ?? '') ?></td>
                    <td><?= number_format($g['monto'], 2, ',', '.') ?></td>
                    <td><?= $g['comprobante_url'] ? '<a href="' . htmlspecialchars($g['comprobante_url']) . '" target="_blank">Ver</a>' : '-' ?></td>
                    <td><a href="index.php?c=gastos&a=eliminar&id=<?= $g['id_gasto'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este gasto?')">Eliminar</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
