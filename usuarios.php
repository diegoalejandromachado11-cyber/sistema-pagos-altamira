<?php
require "conexion.php";
verificarSesion();
soloAdmin();

$mensaje = "";
$tipo = "success";

// ---------- CREAR USUARIO ----------
if (isset($_POST['guardar'])) {
    try {
        // La contraseña nunca se guarda en texto plano, se guarda su hash
        $hash = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (nombre, cedula, email, contrasena_hash, id_rol) VALUES (?, ?, ?, ?, ?)";
        $conexion->prepare($sql)->execute([
            $_POST['nombre'], $_POST['cedula'], $_POST['email'], $hash, $_POST['id_rol']
        ]);

        registrarAuditoria($conexion, "CREAR", "usuario", $conexion->lastInsertId(), "Usuario: " . $_POST['email']);
        $mensaje = "Usuario creado";
    } catch (PDOException $e) {
        $mensaje = "La cédula o el correo ya están registrados";
        $tipo = "danger";
    }
}

// ---------- ELIMINAR USUARIO ----------
if (isset($_GET['eliminar'])) {
    if ($_GET['eliminar'] == $_SESSION['id_usuario']) {
        $mensaje = "No puede eliminar su propio usuario";
        $tipo = "danger";
    } else {
        try {
            $conexion->prepare("DELETE FROM usuario WHERE id_usuario = ?")->execute([$_GET['eliminar']]);
            registrarAuditoria($conexion, "ELIMINAR", "usuario", $_GET['eliminar'], "Usuario eliminado");
            $mensaje = "Usuario eliminado";
        } catch (PDOException $e) {
            $mensaje = "No se puede eliminar: el usuario tiene pagos o registros asociados";
            $tipo = "danger";
        }
    }
}

$usuarios = $conexion->query("SELECT u.*, r.nombre_rol FROM usuario u
                              JOIN rol r ON u.id_rol = r.id_rol
                              ORDER BY u.nombre")->fetchAll(PDO::FETCH_ASSOC);
$roles = $conexion->query("SELECT * FROM rol")->fetchAll(PDO::FETCH_ASSOC);

include "header.php";
?>

<h3 class="mb-4">Usuarios</h3>

<?php if ($mensaje) { ?>
    <div class="alert alert-<?= $tipo ?>"><?= $mensaje ?></div>
<?php } ?>

<div class="card mb-4">
    <div class="card-header">Nuevo usuario</div>
    <div class="card-body">
        <form method="POST" class="row g-3">
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
                        <option value="<?= $r['id_rol'] ?>" <?= $r['id_rol'] == 2 ? 'selected' : '' ?>><?= $r['nombre_rol'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-12">
                <button name="guardar" class="btn btn-primary">Guardar</button>
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
                    <td><span class="badge bg-<?= $u['id_rol'] == 1 ? 'primary' : 'secondary' ?>"><?= $u['nombre_rol'] ?></span></td>
                    <td><?= $u['fecha_registro'] ?></td>
                    <td><a href="?eliminar=<?= $u['id_usuario'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "footer.php"; ?>
