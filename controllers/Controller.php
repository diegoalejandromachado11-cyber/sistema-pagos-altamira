<?php
// =====================================================
// controllers/Controller.php: CONTROLADOR BASE
// Todos los controladores heredan de esta clase y usan
// sus funciones comunes.
// =====================================================

class Controller
{
    // Muestra una vista dentro de la plantilla (menú arriba, scripts abajo).
    // $datos son las variables que la vista va a usar.
    protected function vista($nombre, $datos = [])
    {
        extract($datos);   // ['pagos' => [...]] se convierte en $pagos
        require "views/layout/header.php";
        require "views/$nombre.php";
        require "views/layout/footer.php";
    }

    // Envía al navegador a otra página
    protected function redirigir($controlador, $accion = "index", $extra = "")
    {
        header("Location: index.php?c=$controlador&a=$accion$extra");
        exit;
    }

    // Guarda un mensaje para mostrarlo en la siguiente página
    protected function mensaje($texto, $tipo = "success")
    {
        $_SESSION['mensaje'] = $texto;
        $_SESSION['tipo']    = $tipo;
    }

    // Si no hay sesión iniciada, manda al login
    protected function verificarSesion()
    {
        if (!isset($_SESSION['id_usuario'])) {
            $this->redirigir("auth", "login");
        }
    }

    // Solo deja pasar a administradores
    protected function soloAdmin()
    {
        $this->verificarSesion();
        if (!esAdmin()) {
            $this->redirigir("inicio");
        }
    }
}

// Devuelve true si el usuario conectado es Administrador (id_rol = 1).
// Es una función normal porque también la usan las vistas.
function esAdmin()
{
    return isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1;
}
