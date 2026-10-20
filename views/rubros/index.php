<h3 class="mb-4">Rubros</h3>

<!-- El mismo formulario sirve para crear o para editar -->
<div class="card mb-4 <?= $editar ? 'border-warning' : '' ?>">
    <div class="card-header"><?= $editar ? "Editar rubro: " . htmlspecialchars($editar['nombre']) : "Nuevo rubro" ?></div>
    <div class="card-body">
        <form method="POST" action="index.php?c=rubros&a=<?= $editar ? 'actualizar' : 'guardar' ?>" class="row g-3">
            <input type="hidden" name="id_rubro" value="<?= $editar['id_rubro'] ?? '' ?>">
            <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Monto (USD)</label>
                <input type="number" step="0.01" min="0.01" name="monto_base" class="form-control" value="<?= $editar['monto_base'] ?? '' ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de cobro</label>
                <select name="mensual" class="form-select">
                    <option value="1">Mensual (todos los meses)</option>
                    <option value="0" <?= isset($editar['mensual']) && !$editar['mensual'] ? 'selected' : '' ?>>Pago único (una sola vez)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Se cobra desde</label>
                <input type="month" name="cobrar_desde" class="form-control" value="<?= $editar['cobrar_desde'] ?? date('Y-m') ?>" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($editar['descripcion'] ?? '') ?>">
            </div>
            <div class="col-12">
                <?php if ($editar) { ?>
                    <button class="btn btn-warning">Guardar cambios</button>
                    <a href="index.php?c=rubros" class="btn btn-secondary">Cancelar</a>
                <?php } else { ?>
                    <button class="btn btn-primary">Guardar</button>
                <?php } ?>
            </div>
        </form>
    </div>
</div>

<!-- BUSCADOR -->
<form method="GET" action="index.php" class="row g-2 mb-3">
    <input type="hidden" name="c" value="rubros">
    <div class="col-md-5">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar rubro por nombre o descripción" value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <div class="col-md-2"><button class="btn btn-dark w-100">Buscar</button></div>
    <div class="col-md-2"><a href="index.php?c=rubros" class="btn btn-link">Limpiar</a></div>
</form>

<div class="card">
    <div class="card-header">Lista de rubros: <?= count($rubros) ?> resultado(s)</div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Nombre</th><th>Monto</th><th>Tipo</th><th>Desde</th><th>Descripción</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rubros as $r) { ?>
                <tr>
                    <td><?= htmlspecialchars($r['nombre']) ?></td>
                    <td>$ <?= number_format($r['monto_base'], 2, ',', '.') ?></td>
                    <td><?= $r['mensual'] ? '<span class="badge bg-primary">Mensual</span>' : '<span class="badge bg-secondary">Único</span>' ?></td>
                    <td><?= $r['cobrar_desde'] ?></td>
                    <td><?= htmlspecialchars($r['descripcion'] ?? '') ?></td>
                    <td class="text-nowrap">
                        <a href="index.php?c=rubros&editar=<?= $r['id_rubro'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="index.php?c=rubros&a=eliminar&id=<?= $r['id_rubro'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este rubro?')">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>
            <?php if (!$rubros) { ?>
                <tr><td colspan="6" class="text-center text-muted">No se encontraron rubros.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
