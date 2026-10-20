<?php
// CONTROLADOR Recaudacion: cuánto pagó cada vecino en un mes,
// cuánto se recaudó en total y cuántos vecinos no han pagado

class RecaudacionController extends Controller
{
    public function __construct()
    {
        $this->soloAdmin();
    }

    // index.php?c=recaudacion&periodo=2026-10&ci=...&solo_pendientes=1
    public function index()
    {
        $periodo  = $_GET['periodo'] ?? date("Y-m");
        $buscar   = trim($_GET['ci'] ?? '');
        $soloPend = isset($_GET['solo_pendientes']);

        $vecinos    = Usuario::vecinos($buscar);
        $pagado     = Pago::totalesPorVecino($periodo);           // lo que pagó cada uno
        $pendientes = Deuda::pendientesDelMes($periodo, $vecinos); // lo que le falta a cada uno

        $filas = [];
        $totalUsd = 0;
        $totalBs = 0;
        $sinPagar = 0;

        foreach ($vecinos as $v) {
            $id = $v['id_usuario'];
            $fila = [
                'vecino'     => $v,
                'usd'        => $pagado[$id]['usd'] ?? 0,
                'bs'         => $pagado[$id]['bs'] ?? 0,
                'pendientes' => $pendientes[$id],
            ];
            $totalUsd += $fila['usd'];
            $totalBs  += $fila['bs'];
            if ($fila['pendientes']) {
                $sinPagar++;
            }
            if (!$soloPend || $fila['pendientes']) {
                $filas[] = $fila;
            }
        }

        $this->vista("recaudacion/index", [
            'periodo'  => $periodo,
            'buscar'   => $buscar,
            'soloPend' => $soloPend,
            'filas'    => $filas,
            'totalUsd' => $totalUsd,
            'totalBs'  => $totalBs,
            'sinPagar' => $sinPagar,
            'vecinos'  => count($vecinos),
        ]);
    }
}
