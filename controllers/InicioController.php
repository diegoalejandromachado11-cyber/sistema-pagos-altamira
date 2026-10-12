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
        // El vecino solo ve lo suyo; el administrador ve todo
        $idFiltro = esAdmin() ? null : $_SESSION['id_usuario'];

        $this->vista("inicio/index", [
            'tasa'         => TasaBcv::vigente(date("Y-m-d")),
            'totalPagos'   => Pago::totalUsd($idFiltro),
            'totalGastos'  => esAdmin() ? Gasto::total() : 0,
            'totalVecinos' => esAdmin() ? Usuario::contarVecinos() : 0,
            'ultimos'      => Pago::todos($idFiltro, 5),
        ]);
    }
}
