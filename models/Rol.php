<?php
// MODELO Rol: tabla "rol"

class Rol
{
    public static function todos()
    {
        return Conexion::conectar()->query("SELECT * FROM rol ORDER BY id_rol")->fetchAll();
    }
}
