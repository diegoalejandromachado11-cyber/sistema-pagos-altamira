<h3 class="mb-4">Auditoría</h3>

<div class="card">
    <div class="card-header">Registro de acciones de los usuarios</div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-sm">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>ID</th><th>Detalle</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($registros as $a) { ?>
                <tr>
                    <td><?= $a['fecha'] ?></td>
                    <td><?= htmlspecialchars($a['nombre'] ?? '-') ?></td>
                    <td><span class="badge bg-info text-dark"><?= $a['accion'] ?></span></td>
                    <td><?= $a['tabla_afectada'] ?></td>
                    <td><?= $a['registro_id'] ?></td>
                    <td><?= htmlspecialchars($a['detalle'] ?? '') ?></td>
                    <td><?= $a['ip'] ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
