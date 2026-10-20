<h3 class="mb-4">Auditoría</h3>

<!-- BUSCADOR de auditoría -->
<form method="GET" action="index.php" class="row g-2 align-items-end mb-3">
    <input type="hidden" name="c" value="auditoria">
    <div class="col-md-4">
        <label class="form-label">Usuario, cédula o detalle</label>
        <input type="text" name="buscar" class="form-control" placeholder="Ej.: Condominio" value="<?= htmlspecialchars($filtros['buscar']) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Acción</label>
        <select name="accion" class="form-select">
            <option value="">Todas</option>
            <?php foreach ($acciones as $ac) { ?>
                <option <?= $ac == $filtros['accion'] ? 'selected' : '' ?>><?= htmlspecialchars($ac) ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Desde</label>
        <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($filtros['desde']) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Hasta</label>
        <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($filtros['hasta']) ?>">
    </div>
    <div class="col-md-2"><button class="btn btn-dark w-100">Buscar</button></div>
    <div class="col-12"><a href="index.php?c=auditoria" class="small">Limpiar búsqueda</a></div>
</form>

<div class="card">
    <div class="card-header">Registro de acciones: <?= count($registros) ?> resultado(s)</div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-sm">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>ID</th><th>Detalle</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($registros as $a) { ?>
                <tr>
                    <td class="text-nowrap"><?= $a['fecha'] ?></td>
                    <td><?= htmlspecialchars($a['nombre'] ?? 'Sistema') ?></td>
                    <td><span class="badge <?= $a['accion'] == 'SCRAPING FALLIDO' ? 'bg-warning' : 'bg-info' ?> text-dark"><?= htmlspecialchars($a['accion']) ?></span></td>
                    <td><?= $a['tabla_afectada'] ?></td>
                    <td><?= $a['registro_id'] ?></td>
                    <td><?= htmlspecialchars($a['detalle'] ?? '') ?></td>
                    <td><?= htmlspecialchars($a['ip']) ?></td>
                </tr>
            <?php } ?>
            <?php if (!$registros) { ?>
                <tr><td colspan="7" class="text-center text-muted">No se encontraron registros.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
