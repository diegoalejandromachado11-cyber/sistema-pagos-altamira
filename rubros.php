<?php
require "conexion.php";
verificarSesion();
soloAdmin();

$mensaje = "";
$tipo = "success";

// ---------- CREAR RUBRO ----------
if (isset($_POST['guardar'])) {
    $sql = "INSERT INTO rubro (nombre, monto_base, descripcion) VALUES (?, ?, ?)";
    $conexion->prepare($sql)->execute([$_POST['nombre'], $_POST['monto_base'], $_POST['descripcion']]);
    registrarAuditoria($conexion, "CREAR", "rubro", $conexion->lastInsertId(), "Rubro: " . $_POST['nombre']);
    $mensaje = "Rubro creado";
}

// ---------- ACTUALIZAR RUBRO (cambiar el monto del mes) ----------
if (isset($_POST['actualizar'])) {
    // Guardamos el monto anterior para dejarlo en la auditoría
    $stmt = $conexion->prepare("SELECT monto_base FROM rubro WHERE id_rubro = ?");
    $stmt->execute([$_POST['id_rubro']]);
    $montoAnterior = $stmt->fetchColumn();

    $sql = "UPDATE rubro SET nombre = ?, monto_base = ?, descripcion = ? WHERE id_rubro = ?";
    $conexion->prepare($sql)->execute([
        $_POST['nombre'], $_POST['monto_base'], $_POST['descripcion'], $_POST['id_rubro']
    ]);

    registrarAuditoria($conexion, "MODIFICAR", "rubro", $_POST['id_rubro'],
        $_POST['nombre'] . ": monto cambió de $" . $montoAnterior . " a $" . $_POST['monto_base']);
    $mensaje = "Rubro actualizado: " . $_POST['nombre'] . " ahora cuesta $" . $_POST['monto_base'];
}

// ---------- ELIMINAR RUBRO ----------
if (isset($_GET['eliminar'])) {
    try {
        $conexion->prepare("DELETE FROM rubro WHERE id_rubro = ?")->execute([$_GET['eliminar']]);
        registrarAuditoria($conexion, "ELIMINAR", "rubro", $_GET['eliminar'], "Rubro eliminado");
        $mensaje = "Rubro eliminado";
    } catch (PDOException $e) {
        // No se puede borrar si tiene pagos o gastos asociados (llave foránea)
        $mensaje = "No se puede eliminar: el rubro tiene pagos o gastos asociados";
        $tipo = "danger";
    }
}

$rubros = $conexion->query("SELECT * FROM rubro ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Si se pulsó "Editar", cargamos ese rubro para mostrarlo en el formulario
$editar = null;
if (isset($_GET['editar'])) {
    $stmt = $conexion->prepare("SELECT * FROM rubro WHERE id_rubro = ?");
    $stmt->execute([$_GET['editar']]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

include "header.php";
?>

<h3 class="mb-4">Rubros</h3>

<?php if ($mensaje) { ?>
    <div class="alert alert-<?= $tipo ?>"><?= $mensaje ?></div>
<?php } ?>

<!-- El mismo formulario sirve para crear o para editar -->
<div class="card mb-4 <?= $editar ? 'border-warning' : '' ?>">
    <div class="card-header"><?= $editar ? "Editar rubro: " . htmlspecialchars($editar['nombre']) : "Nuevo rubro" ?></div>
    <div class="card-body">
        <form method="POST" action="rubros.php" class="row g-3">
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
                    <button name="actualizar" class="btn btn-warning">Guardar cambios</button>
                    <a href="rubros.php" class="btn btn-secondary">Cancelar</a>
                <?php } else { ?>
                    <button name="guardar" class="btn btn-primary">Guardar</button>
                <?php } ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Lista de rubros</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Nombre</th><th>Monto del mes</th><th>Descripción</th><th>Creado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rubros as $r) { ?>
                <tr>
                    <td><?= htmlspecialchars($r['nombre']) ?></td>
                    <td>$ <?= number_format($r['monto_base'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($r['descripcion']) ?></td>
                    <td><?= $r['fecha_creacion'] ?></td>
                    <td>
                        <a href="?editar=<?= $r['id_rubro'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="?eliminar=<?= $r['id_rubro'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este rubro?')">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "footer.php"; ?>
