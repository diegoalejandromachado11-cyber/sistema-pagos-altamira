<h3 class="mb-4">Usuarios</h3>

<div class="card mb-4">
    <div class="card-header">Nuevo usuario</div>
    <div class="card-body">
        <form method="POST" action="index.php?c=usuarios&a=guardar" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cédula</label>
                <input type="text" name="cedula" class="form-control" placeholder="V-12345678" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="contrasena" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Rol</label>
                <select name="id_rol" class="form-select">
                    <?php foreach ($roles as $r) { ?>
                        <option value="<?= $r['id_rol'] ?>" <?= $r['id_rol'] == 2 ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre_rol']) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Lista de usuarios</div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Nombre</th><th>Cédula</th><th>Correo</th><th>Rol</th><th>Registro</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $u) { ?>
                <tr>
                    <td><?= htmlspecialchars($u['nombre']) ?></td>
                    <td><?= htmlspecialchars($u['cedula']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge bg-<?= $u['id_rol'] == 1 ? 'primary' : 'secondary' ?>"><?= htmlspecialchars($u['nombre_rol']) ?></span></td>
                    <td><?= $u['fecha_registro'] ?></td>
                    <td><a href="index.php?c=usuarios&a=eliminar&id=<?= $u['id_usuario'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
