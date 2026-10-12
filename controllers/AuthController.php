<?php
// CONTROLADOR Auth: iniciar y cerrar sesión

class AuthController extends Controller
{
    // index.php?c=auth&a=login  (muestra el formulario y lo procesa)
    public function login()
    {
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user = Usuario::buscarPorEmail($_POST['email']);

            // password_verify compara la clave escrita con el hash guardado
            if ($user && password_verify($_POST['contrasena'], $user['contrasena_hash'])) {
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['nombre']     = $user['nombre'];
                $_SESSION['id_rol']     = $user['id_rol'];
                $_SESSION['nombre_rol'] = $user['nombre_rol'];

                Auditoria::registrar("LOGIN", "usuario", $user['id_usuario'], "Inicio de sesión");
                $this->redirigir("inicio");
            }
            $error = "Correo o contraseña incorrectos";
        }

        // El login no usa la plantilla con menú, por eso se carga directo
        require "views/auth/login.php";
    }

    // index.php?c=auth&a=logout
    public function logout()
    {
        if (isset($_SESSION['id_usuario'])) {
            Auditoria::registrar("LOGOUT", "usuario", $_SESSION['id_usuario'], "Cierre de sesión");
        }
        session_destroy();
        $this->redirigir("auth", "login");
    }
}
