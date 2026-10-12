<?php
// CONTROLADOR Auditoria: ver el historial de acciones

class AuditoriaController extends Controller
{
    public function __construct()
    {
        $this->soloAdmin();
    }

    public function index()
    {
        $this->vista("auditoria/index", ['registros' => Auditoria::ultimos(200)]);
    }
}
