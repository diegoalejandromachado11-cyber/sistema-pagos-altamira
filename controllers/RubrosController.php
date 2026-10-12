<?php
// CONTROLADOR Rubros: crear, editar (cambiar el monto del mes) y eliminar

class RubrosController extends Controller
{
    public function __construct()
    {
        $this->soloAdmin();
    }

    // index.php?c=rubros  (con &editar=ID carga ese rubro en el formulario)
    public function index()
    {
        $this->vista("rubros/index", [
            'rubros' => Rubro::todos(),
            'editar' => isset($_GET['editar']) ? Rubro::buscar($_GET['editar']) : null,
        ]);
    }

    // index.php?c=rubros&a=guardar  (POST)
    public function guardar()
    {
        $id = Rubro::crear($_POST['nombre'], $_POST['monto_base'], $_POST['descripcion']);
        Auditoria::registrar("CREAR", "rubro", $id, "Rubro: " . $_POST['nombre']);
        $this->mensaje("Rubro creado");
        $this->redirigir("rubros");
    }

    // index.php?c=rubros&a=actualizar  (POST)
    public function actualizar()
    {
        // Guardamos el monto anterior para dejarlo en la auditoría
        $anterior = Rubro::buscar($_POST['id_rubro']);

        Rubro::actualizar($_POST['id_rubro'], $_POST['nombre'], $_POST['monto_base'], $_POST['descripcion']);

        Auditoria::registrar("MODIFICAR", "rubro", $_POST['id_rubro'],
            $_POST['nombre'] . ": monto cambió de $" . $anterior['monto_base'] . " a $" . $_POST['monto_base']);
        $this->mensaje("Rubro actualizado: " . $_POST['nombre'] . " ahora cuesta $" . $_POST['monto_base']);
        $this->redirigir("rubros");
    }

    // index.php?c=rubros&a=eliminar&id=3
    public function eliminar()
    {
        try {
            Rubro::eliminar($_GET['id']);
            Auditoria::registrar("ELIMINAR", "rubro", $_GET['id'], "Rubro eliminado");
            $this->mensaje("Rubro eliminado");
        } catch (PDOException $e) {
            // No se puede borrar si tiene pagos o gastos asociados (llave foránea)
            $this->mensaje("No se puede eliminar: el rubro tiene pagos o gastos asociados", "danger");
        }
        $this->redirigir("rubros");
    }
}
