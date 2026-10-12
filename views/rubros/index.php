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
            <div class="col-md-3">
                <label class="form-label">Monto del mes (USD)</label>
                <input type="number" step="0.01" name="monto_base" class="form-control" value="<?= $editar['monto_base'] ?? '' ?>" required>
            </div>
            <div class="col-md-5">
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

<div class="card">
    <div class="card-header">Lista de rubros</div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Nombre</th><th>Monto del mes</th><th>Descripción</th><th>Creado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rubros as $r) { ?>
                <tr>
                    <td><?= htmlspecialchars($r['nombre']) ?></td>
                    <td>$ <?= number_format($r['monto_base'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($r['descripcion'] ?? '') ?></td>
                    <td><?= $r['fecha_creacion'] ?></td>
                    <td>
                        <a href="index.php?c=rubros&editar=<?= $r['id_rubro'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="index.php?c=rubros&a=eliminar&id=<?= $r['id_rubro'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este rubro?')">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
