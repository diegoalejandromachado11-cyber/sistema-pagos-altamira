<?php
// CONTROLADOR Gastos: registrar gastos con comprobante y eliminarlos

class GastosController extends Controller
{
    public function __construct()
    {
        $this->soloAdmin();
    }

    public function index()
    {
        $this->vista("gastos/index", [
            'gastos' => Gasto::todos(),
            'rubros' => Rubro::todos(),
        ]);
    }

    // index.php?c=gastos&a=guardar  (POST con archivo)
    public function guardar()
    {
        $comprobante = null;

        // Si se subió un comprobante, se guarda en la carpeta uploads/
        if (!empty($_FILES['comprobante']['name'])) {
            $nombreArchivo = time() . "_" . basename($_FILES['comprobante']['name']);
            move_uploaded_file($_FILES['comprobante']['tmp_name'], "uploads/" . $nombreArchivo);
            $comprobante = "uploads/" . $nombreArchivo;
        }

        $id = Gasto::crear($_POST['id_rubro'], $_POST['monto'], $_POST['fecha'], $_POST['descripcion'], $comprobante);
        Auditoria::registrar("CREAR", "gasto", $id, "Gasto de " . $_POST['monto'] . " Bs");
        $this->mensaje("Gasto registrado");
        $this->redirigir("gastos");
    }

    public function eliminar()
    {
        Gasto::eliminar($_GET['id']);
        Auditoria::registrar("ELIMINAR", "gasto", $_GET['id'], "Gasto eliminado");
        $this->mensaje("Gasto eliminado");
        $this->redirigir("gastos");
    }
}
