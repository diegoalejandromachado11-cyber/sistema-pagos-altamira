<?php
// CONTROLADOR Usuarios: crear y eliminar vecinos y administradores

class UsuariosController extends Controller
{
    public function __construct()
    {
        $this->soloAdmin();
    }

    public function index()
    {
        $this->vista("usuarios/index", [
            'usuarios' => Usuario::todos(),
            'roles'    => Rol::todos(),
        ]);
    }

    public function guardar()
    {
        try {
            $id = Usuario::crear($_POST['nombre'], $_POST['cedula'], $_POST['email'], $_POST['contrasena'], $_POST['id_rol']);
            Auditoria::registrar("CREAR", "usuario", $id, "Usuario: " . $_POST['email']);
            $this->mensaje("Usuario creado");
        } catch (PDOException $e) {
            $this->mensaje("La cédula o el correo ya están registrados", "danger");
        }
        $this->redirigir("usuarios");
    }

    public function eliminar()
    {
        if ($_GET['id'] == $_SESSION['id_usuario']) {
            $this->mensaje("No puede eliminar su propio usuario", "danger");
            $this->redirigir("usuarios");
        }

        try {
            Usuario::eliminar($_GET['id']);
            Auditoria::registrar("ELIMINAR", "usuario", $_GET['id'], "Usuario eliminado");
            $this->mensaje("Usuario eliminado");
        } catch (PDOException $e) {
            $this->mensaje("No se puede eliminar: el usuario tiene pagos o registros asociados", "danger");
        }
        $this->redirigir("usuarios");
    }
}
