<?php
// MODELO Rubro: tabla "rubro" (conceptos de cobro: condominio, vigilancia, etc.)

class Rubro
{
    public static function todos()
    {
        return Conexion::conectar()->query("SELECT * FROM rubro ORDER BY nombre")->fetchAll();
    }

    public static function buscar($id)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM rubro WHERE id_rubro = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function crear($nombre, $monto_base, $descripcion)
    {
        $pdo = Conexion::conectar();
        $pdo->prepare("INSERT INTO rubro (nombre, monto_base, descripcion) VALUES (?, ?, ?)")
            ->execute([$nombre, $monto_base, $descripcion]);
        return $pdo->lastInsertId();
    }

    public static function actualizar($id, $nombre, $monto_base, $descripcion)
    {
        Conexion::conectar()
            ->prepare("UPDATE rubro SET nombre = ?, monto_base = ?, descripcion = ? WHERE id_rubro = ?")
            ->execute([$nombre, $monto_base, $descripcion, $id]);
    }

    // Falla (lanza excepción) si el rubro tiene pagos o gastos asociados
    public static function eliminar($id)
    {
        Conexion::conectar()->prepare("DELETE FROM rubro WHERE id_rubro = ?")->execute([$id]);
    }
}
