<?php
// =====================================================
// MODELO Deuda
// No tiene tabla propia: la deuda se CALCULA comparando lo que
// cada vecino debía pagar (rubros) con lo que pagó (tabla pago).
//
// FECHA DE CORTE: solo se revisan los meses desde enero del año
// pasado hasta el mes actual. Así el cálculo nunca crece sin límite
// y el sistema no se pone lento (se evita el "colapso").
// =====================================================

class Deuda
{
    // Enero del año pasado. Ej.: en 2026 el corte es "2025-01"
    public static function corte()
    {
        return (date("Y") - 1) . "-01";
    }

    // Lista de meses entre dos periodos: ("2025-11", "2026-01") -> 2025-11, 2025-12, 2026-01
    public static function meses($desde, $hasta)
    {
        $lista = [];
        $mes = $desde;
        while ($mes <= $hasta) {
            $lista[] = $mes;
            $mes = date("Y-m", strtotime("$mes-01 +1 month"));
        }
        return $lista;
    }

    // Lo que debe UN vecino hasta el mes $hasta (por defecto el mes actual).
    // Devuelve una lista de [rubro, periodo, monto_usd].
    public static function delVecino($id_usuario, $rubros, $pagadas, $hasta = null)
    {
        $hasta = $hasta ?? date("Y-m");
        $deudas = [];

        foreach ($rubros as $r) {
            // Se empieza a cobrar en el mes más reciente entre el corte y "cobrar_desde"
            $inicio = max(self::corte(), $r['cobrar_desde']);
            if ($inicio > $hasta) {
                continue; // todavía no se cobra
            }

            if ($r['mensual']) {
                // Rubro mensual: cada mes sin pago es una deuda
                foreach (self::meses($inicio, $hasta) as $mes) {
                    if (!isset($pagadas["$id_usuario-{$r['id_rubro']}-$mes"])) {
                        $deudas[] = ['rubro' => $r['nombre'], 'id_rubro' => $r['id_rubro'], 'periodo' => $mes, 'monto_usd' => $r['monto_base']];
                    }
                }
            } elseif (!isset($pagadas["$id_usuario-{$r['id_rubro']}"])) {
                // Rubro único: se debe si nunca se pagó
                $deudas[] = ['rubro' => $r['nombre'], 'id_rubro' => $r['id_rubro'], 'periodo' => $r['cobrar_desde'], 'monto_usd' => $r['monto_base']];
            }
        }
        return $deudas;
    }

    // Deudas de todos los vecinos (o de los que coincidan con la búsqueda).
    // Solo se devuelven los que deben algo, ordenados de mayor a menor deuda.
    public static function todas($buscar = "")
    {
        $rubros  = Rubro::todos();
        $pagadas = Pago::clavesPagadas();   // una sola consulta para todos los pagos
        $lista = [];

        foreach (Usuario::vecinos($buscar) as $v) {
            $items = self::delVecino($v['id_usuario'], $rubros, $pagadas);
            if ($items) {
                $lista[] = [
                    'vecino' => $v,
                    'items'  => $items,
                    'meses'  => count(array_unique(array_column($items, 'periodo'))),
                    'total'  => array_sum(array_column($items, 'monto_usd')),
                ];
            }
        }
        usort($lista, fn($a, $b) => $b['total'] <=> $a['total']);
        return $lista;
    }

    // Rubros que cada vecino tiene pendientes en UN mes: [id_usuario => [nombres de rubros]]
    public static function pendientesDelMes($periodo, $vecinos)
    {
        $rubros  = Rubro::todos();
        $pagadas = Pago::clavesPagadas();
        $resultado = [];

        foreach ($vecinos as $v) {
            $pendientes = [];
            foreach (self::delVecino($v['id_usuario'], $rubros, $pagadas, $periodo) as $d) {
                if ($d['periodo'] == $periodo) {
                    $pendientes[] = $d['rubro'];
                }
            }
            $resultado[$v['id_usuario']] = $pendientes;
        }
        return $resultado;
    }
}
