<?php
// CONTROLADOR Pagos: buscar, registrar (sin duplicados), eliminar y factura

class PagosController extends Controller
{
    public function __construct()
    {
        $this->verificarSesion();
    }

    // index.php?c=pagos  (con filtros opcionales: &ci=...&rubro=...&periodo=...)
    public function index()
    {
        $filtros = [
            'ci'      => trim($_GET['ci'] ?? ''),
            'rubro'   => $_GET['rubro'] ?? '',
            'periodo' => $_GET['periodo'] ?? '',
        ];

        // El vecino solo ve sus pagos; el administrador puede buscar a cualquiera
        $pagos = Pago::buscar(
            esAdmin() ? null : $_SESSION['id_usuario'],
            esAdmin() ? $filtros['ci'] : '',
            $filtros['rubro'],
            $filtros['periodo']
        );

        // Valores que llegan desde el botón "Pagar" del módulo Deudas
        $pre = [
            'id_usuario' => $_GET['pre_usuario'] ?? '',
            'id_rubro'   => $_GET['pre_rubro'] ?? '',
            'periodo'    => $_GET['pre_periodo'] ?? date('Y-m'),
        ];

        $this->vista("pagos/index", [
            'pagos'    => $pagos,
            'filtros'  => $filtros,
            'pre'      => $pre,
            'rubros'   => Rubro::todos(),
            'usuarios' => esAdmin() ? Usuario::vecinos() : [],   // solo vecinos, no el administrador
            'tasaHoy'  => TasaBcv::vigente(date("Y-m-d")),
        ]);
    }

    // index.php?c=pagos&a=guardar  (POST)
    public function guardar()
    {
        // SOLO EL ADMINISTRADOR registra pagos, después de verificar que el dinero llegó.
        // Si un vecino pudiera registrarlos, podría marcar como pagado algo que no pagó (fraude).
        if (!esAdmin()) {
            $this->mensaje("Solo el administrador puede registrar pagos.", "danger");
            $this->redirigir("pagos");
        }

        $id_usuario = $_POST['id_usuario'];
        $rubro      = Rubro::buscar($_POST['id_rubro']);
        $periodo    = $_POST['periodo'];
        $monto      = $_POST['monto'];  // monto en bolívares

        // BLOQUEO DE DUPLICADOS: no se puede pagar dos veces el mismo rubro del mismo mes
        if (Pago::yaPagado($id_usuario, $rubro, $periodo)) {
            $vecino = Usuario::buscarPorId($id_usuario);
            $cuando = $rubro['mensual'] ? "del mes $periodo" : "(es un pago único)";
            $this->mensaje("Pago bloqueado: {$vecino['nombre']} ya pagó {$rubro['nombre']} $cuando. No se permiten pagos duplicados.", "danger");
            $this->redirigir("pagos");
        }

        // Buscamos la tasa BCV de la fecha del pago para convertir a dólares
        $tasa = TasaBcv::vigente($_POST['fecha_pago']);

        try {
            $id = Pago::crear([
                'id_usuario'  => $id_usuario,
                'id_rubro'    => $rubro['id_rubro'],
                'id_tasa'     => $tasa ? $tasa['id_tasa'] : null,
                'monto'       => $monto,
                'monto_usd'   => $tasa ? round($monto / $tasa['tasa_usd'], 2) : null,
                'fecha_pago'  => $_POST['fecha_pago'],
                'periodo'     => $periodo,
                'metodo_pago' => $_POST['metodo_pago'],
                'referencia'  => $_POST['referencia'],
            ]);
        } catch (PDOException $e) {
            // Segunda protección: la regla UNIQUE de la base de datos
            $this->mensaje("Pago bloqueado: ese rubro ya está pagado para ese mes.", "danger");
            $this->redirigir("pagos");
        }

        $vecino = Usuario::buscarPorId($id_usuario);
        Auditoria::registrar("CREAR", "pago", $id,
            "Pago de $monto Bs - {$rubro['nombre']} $periodo - {$vecino['nombre']} ({$vecino['cedula']})");

        if (!$tasa) {
            $this->mensaje("Pago registrado. Atención: no hay tasa BCV registrada, no se calculó el monto en dólares.", "warning");
        } else {
            $this->mensaje("Pago registrado. Esta es su factura.");
        }

        // FACTURA AUTOMÁTICA: después de guardar se muestra la factura lista para imprimir
        $this->redirigir("pagos", "factura", "&id=$id");
    }

    // index.php?c=pagos&a=factura&id=5
    public function factura()
    {
        $pago = Pago::buscarPorId($_GET['id'] ?? 0);

        // El vecino solo puede ver las facturas de sus propios pagos
        if (!$pago || (!esAdmin() && $pago['id_usuario'] != $_SESSION['id_usuario'])) {
            $this->redirigir("pagos");
        }

        // La factura no usa el menú: es una hoja lista para imprimir
        $mensaje = $_SESSION['mensaje'] ?? '';
        unset($_SESSION['mensaje'], $_SESSION['tipo']);
        require "views/pagos/factura.php";
    }

    // index.php?c=pagos&a=eliminar&id=5  (solo administrador)
    public function eliminar()
    {
        $pago = Pago::buscarPorId($_GET['id'] ?? 0);
        if (esAdmin() && $pago) {
            Pago::eliminar($pago['id_pago']);
            Auditoria::registrar("ELIMINAR", "pago", $pago['id_pago'],
                "Pago eliminado: {$pago['monto']} Bs - {$pago['rubro']} {$pago['periodo']} - {$pago['nombre']} ({$pago['cedula']})");
            $this->mensaje("Pago eliminado");
        }
        $this->redirigir("pagos");
    }
}
