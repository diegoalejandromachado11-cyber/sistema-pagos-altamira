<?php
require "conexion.php";
verificarSesion();
soloAdmin();

$mensaje = "";

// ---------- REGISTRAR GASTO ----------
if (isset($_POST['guardar'])) {
    $comprobante = null;

    // Si se subió un comprobante, se guarda en la carpeta uploads/
    if (!empty($_FILES['comprobante']['name'])) {
        $nombreArchivo = time() . "_" . basename($_FILES['comprobante']['name']);
        move_uploaded_file($_FILES['comprobante']['tmp_name'], "uploads/" . $nombreArchivo);
        $comprobante = "uploads/" . $nombreArchivo;
    }

    $sql = "INSERT INTO gasto (id_rubro, monto, fecha, descripcion, comprobante_url) VALUES (?, ?, ?, ?, ?)";
    $conexion->prepare($sql)->execute([
        $_POST['id_rubro'], $_POST['monto'], $_POST['fecha'], $_POST['descripcion'], $comprobante
    ]);

    registrarAuditoria($conexion, "CREAR", "gasto", $conexion->lastInsertId(), "Gasto de " . $_POST['monto'] . " Bs");
    $mensaje = "Gasto registrado";
}

// ---------- ELIMINAR GASTO ----------
if (isset($_GET['eliminar'])) {
    $conexion->prepare("DELETE FROM gasto WHERE id_gasto = ?")->execute([$_GET['eliminar']]);
    registrarAuditoria($conexion, "ELIMINAR", "gasto", $_GET['eliminar'], "Gasto eliminado");
    $mensaje = "Gasto eliminado";
}

$gastos = $conexion->query("SELECT g.*, r.nombre AS rubro FROM gasto g
                            JOIN rubro r ON g.id_rubro = r.id_rubro
                            ORDER BY g.fecha DESC")->fetchAll(PDO::FETCH_ASSOC);
$rubros = $conexion->query("SELECT * FROM rubro ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

include "header.php";
?>

<h3 class="mb-4">Gastos</h3>

<?php if ($mensaje) { ?>
    <div class="alert alert-success"><?= $mensaje ?></div>
<?php } ?>

<div class="card mb-4">
    <div class="card-header">Registrar gasto</div>
    <div class="card-body">
        <!-- enctype es necesario para poder subir archivos -->
        <form method="POST" enctype="multipart/form-data" class="row g-3">
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
                <button name="guardar" class="btn btn-primary">Registrar gasto</button>
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
                    <td><?= htmlspecialchars($g['descripcion']) ?></td>
                    <td><?= number_format($g['monto'], 2, ',', '.') ?></td>
                    <td><?= $g['comprobante_url'] ? '<a href="' . htmlspecialchars($g['comprobante_url']) . '" target="_blank">Ver</a>' : '-' ?></td>
                    <td><a href="?eliminar=<?= $g['id_gasto'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este gasto?')">Eliminar</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "footer.php"; ?>
