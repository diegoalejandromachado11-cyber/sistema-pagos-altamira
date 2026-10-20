<?php
// CONTROLADOR Auditoria: ver y buscar el historial de acciones

class AuditoriaController extends Controller
{
    public function __construct()
    {
        $this->soloAdmin();
    }

    // index.php?c=auditoria  (filtros opcionales: &buscar=...&accion=...&desde=...&hasta=...)
    public function index()
    {
        $filtros = [
            'buscar' => trim($_GET['buscar'] ?? ''),
            'accion' => $_GET['accion'] ?? '',
            'desde'  => $_GET['desde'] ?? '',
            'hasta'  => $_GET['hasta'] ?? '',
        ];

        $this->vista("auditoria/index", [
            'registros' => Auditoria::buscar($filtros['buscar'], $filtros['accion'], $filtros['desde'], $filtros['hasta']),
            'acciones'  => Auditoria::acciones(),
            'filtros'   => $filtros,
        ]);
    }
}
