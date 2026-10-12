<?php
// CONTROLADOR Pagos: listar, registrar y eliminar pagos

class PagosController extends Controller
{
    public function __construct()
    {
        $this->verificarSesion();
    }

    // index.php?c=pagos
    public function index()
    {
        $this->vista("pagos/index", [
            'pagos'    => Pago::todos(esAdmin() ? null : $_SESSION['id_usuario']),
            'rubros'   => Rubro::todos(),
            'usuarios' => esAdmin() ? Usuario::todos() : [],
            'tasaHoy'  => TasaBcv::vigente(date("Y-m-d")),
        ]);
    }

    // index.php?c=pagos&a=guardar  (POST)
    public function guardar()
    {
        // El vecino solo puede registrar pagos a su nombre; el admin elige el vecino
        $id_usuario = esAdmin() ? $_POST['id_usuario'] : $_SESSION['id_usuario'];
        $monto = $_POST['monto'];  // monto en bolívares

        // Buscamos la tasa BCV de la fecha del pago para convertir a dólares
        $tasa = TasaBcv::vigente($_POST['fecha_pago']);

        $id = Pago::crear([
            'id_usuario'  => $id_usuario,
            'id_rubro'    => $_POST['id_rubro'],
            'id_tasa'     => $tasa ? $tasa['id_tasa'] : null,
            'monto'       => $monto,
            'monto_usd'   => $tasa ? round($monto / $tasa['tasa_usd'], 2) : null,
            'fecha_pago'  => $_POST['fecha_pago'],
            'periodo'     => $_POST['periodo'],
            'metodo_pago' => $_POST['metodo_pago'],
            'referencia'  => $_POST['referencia'],
        ]);

        Auditoria::registrar("CREAR", "pago", $id, "Pago de $monto Bs");

        if ($tasa) {
            $this->mensaje("Pago registrado (" . round($monto / $tasa['tasa_usd'], 2) . " USD)");
        } else {
            $this->mensaje("Pago registrado. Atención: no hay tasa BCV registrada, no se calculó el monto en dólares", "warning");
        }
        $this->redirigir("pagos");
    }

    // index.php?c=pagos&a=eliminar&id=5  (solo administrador)
    public function eliminar()
    {
        if (esAdmin()) {
            Pago::eliminar($_GET['id']);
            Auditoria::registrar("ELIMINAR", "pago", $_GET['id'], "Pago eliminado");
            $this->mensaje("Pago eliminado");
        }
        $this->redirigir("pagos");
    }
}
