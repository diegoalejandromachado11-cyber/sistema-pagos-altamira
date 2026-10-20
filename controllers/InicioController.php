<?php
// CONTROLADOR Inicio: panel principal con totales

class InicioController extends Controller
{
    public function __construct()
    {
        $this->verificarSesion();
    }

    public function index()
    {
        $mes = date("Y-m");

        if (esAdmin()) {
            // Recaudado este mes y cuántos vecinos tienen algo pendiente este mes
            $vecinos    = Usuario::vecinos();
            $pendientes = Deuda::pendientesDelMes($mes, $vecinos);
            $datos = [
                'recaudadoMes' => array_sum(array_column(Pago::totalesPorVecino($mes), 'usd')),
                'sinPagarMes'  => count(array_filter($pendientes)),
                'totalVecinos' => count($vecinos),
                'totalGastos'  => Gasto::total(),
                'miDeuda'      => 0,
            ];
        } else {
            $deuda = Deuda::delVecino($_SESSION['id_usuario'], Rubro::todos(), Pago::clavesPagadas());
            $datos = [
                'miDeuda'  => array_sum(array_column($deuda, 'monto_usd')),
                'misMeses' => count(array_unique(array_column($deuda, 'periodo'))),
            ];
        }

        $this->vista("inicio/index", $datos + [
            'mes'        => $mes,
            'tasa'       => TasaBcv::vigente(date("Y-m-d")),
            'totalPagos' => Pago::totalUsd(esAdmin() ? null : $_SESSION['id_usuario']),
            'ultimos'    => Pago::buscar(esAdmin() ? null : $_SESSION['id_usuario'], "", "", "", 5),
        ]);
    }
}
