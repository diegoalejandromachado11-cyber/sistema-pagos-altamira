<?php
// VISTA de la factura. Se genera sola al registrar un pago.
// El botón "Imprimir" usa la impresora o "Guardar como PDF" del navegador,
// así no se necesita ninguna librería ni conexión a internet.
$numero = str_pad($pago['id_pago'], 6, "0", STR_PAD_LEFT);   // 45 -> 000045
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura N.º <?= $numero ?> - Urbanización Altamira</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hoja { max-width: 720px; margin: 24px auto; background: #fff; padding: 40px; border: 1px solid #dee2e6; }
        /* Al imprimir se ocultan los botones y la hoja ocupa toda la página */
        @media print {
            .no-imprimir { display: none !important; }
            body.bg-light { background: #fff !important; }
            .hoja { border: 0; margin: 0; max-width: 100%; padding: 0; }
        }
    </style>
</head>
<body class="bg-light">

<div class="hoja">
    <div class="no-imprimir mb-4">
        <?php if ($mensaje) { ?>
            <div class="alert alert-success py-2"><?= htmlspecialchars($mensaje) ?></div>
        <?php } ?>
        <button onclick="window.print()" class="btn btn-primary">Imprimir / Guardar PDF</button>
        <a href="index.php?c=pagos" class="btn btn-secondary">Volver a pagos</a>
    </div>

    <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
        <div>
            <h4 class="mb-0">Urbanización Altamira</h4>
            <div class="text-muted">Junta de Condominio</div>
            <div class="text-muted small">San Joaquín, estado Carabobo</div>
        </div>
        <div class="text-end">
            <h4 class="mb-0">FACTURA</h4>
            <div>N.º <strong><?= $numero ?></strong></div>
            <div class="small">Fecha de pago: <?= date("d/m/Y", strtotime($pago['fecha_pago'])) ?></div>
        </div>
    </div>

    <table class="table table-sm table-borderless mb-3">
        <tr><th style="width: 30%">Vecino:</th><td><?= htmlspecialchars($pago['nombre']) ?></td></tr>
        <tr><th>Cédula:</th><td><?= htmlspecialchars($pago['cedula']) ?></td></tr>
        <tr><th>Método de pago:</th><td><?= htmlspecialchars($pago['metodo_pago']) ?></td></tr>
        <tr><th>Referencia:</th><td><?= htmlspecialchars($pago['referencia'] ?: '-') ?></td></tr>
    </table>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr><th>Concepto</th><th>Mes</th><th class="text-end">Monto (Bs)</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($pago['rubro']) ?></td>
                <td><?= $pago['periodo'] ?></td>
                <td class="text-end"><?= number_format($pago['monto'], 2, ',', '.') ?></td>
            </tr>
        </tbody>
        <tfoot>
            <tr><th colspan="2" class="text-end">TOTAL PAGADO</th>
                <th class="text-end"><?= number_format($pago['monto'], 2, ',', '.') ?> Bs</th></tr>
            <?php if ($pago['tasa_usd']) { ?>
            <tr><td colspan="2" class="text-end">Tasa BCV aplicada</td>
                <td class="text-end"><?= number_format($pago['tasa_usd'], 4, ',', '.') ?> Bs/USD</td></tr>
            <tr><td colspan="2" class="text-end">Equivalente en dólares</td>
                <td class="text-end">$ <?= number_format($pago['monto_usd'], 2, ',', '.') ?></td></tr>
            <?php } ?>
        </tfoot>
    </table>

    <div class="row mt-5 pt-4">
        <div class="col-6 text-center"><div class="border-top pt-1 mx-4">Recibido por</div></div>
        <div class="col-6 text-center"><div class="border-top pt-1 mx-4">Vecino</div></div>
    </div>

    <p class="text-center text-muted small mt-4 mb-0">
        Documento sin valor fiscal emitido por la Junta de Condominio de la Urbanización Altamira.<br>
        Generado automáticamente por el Sistema de Gestión de Pagos el <?= date("d/m/Y h:i a") ?>.
    </p>
</div>

</body>
</html>
