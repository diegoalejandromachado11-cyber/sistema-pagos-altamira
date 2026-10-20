<?php
// CONTROLADOR Deudas: quién debe, cuánto y de qué meses
// (desde la fecha de corte: enero del año pasado)

class DeudasController extends Controller
{
    public function __construct()
    {
        $this->verificarSesion();
    }

    // index.php?c=deudas  (el administrador puede buscar por cédula: &ci=...)
    public function index()
    {
        $buscar = trim($_GET['ci'] ?? '');

        if (esAdmin()) {
            $deudas = Deuda::todas($buscar);
        } else {
            // El vecino solo ve su propia deuda
            $items  = Deuda::delVecino($_SESSION['id_usuario'], Rubro::todos(), Pago::clavesPagadas());
            $deudas = $items ? [[
                'vecino' => Usuario::buscarPorId($_SESSION['id_usuario']),
                'items'  => $items,
                'meses'  => count(array_unique(array_column($items, 'periodo'))),
                'total'  => array_sum(array_column($items, 'monto_usd')),
            ]] : [];
        }

        $this->vista("deudas/index", [
            'deudas' => $deudas,
            'buscar' => $buscar,
            'corte'  => Deuda::corte(),
            'tasa'   => TasaBcv::vigente(date("Y-m-d")),
        ]);
    }
}
