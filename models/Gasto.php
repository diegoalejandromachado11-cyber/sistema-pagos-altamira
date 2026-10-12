<?php
// MODELO Gasto: tabla "gasto"

class Gasto
{
    public static function todos()
    {
        $sql = "SELECT g.*, r.nombre AS rubro FROM gasto g
                JOIN rubro r ON g.id_rubro = r.id_rubro
                ORDER BY g.fecha DESC, g.id_gasto DESC";
        return Conexion::conectar()->query($sql)->fetchAll();
    }

    public static function crear($id_rubro, $monto, $fecha, $descripcion, $comprobante_url)
    {
        $sql = "INSERT INTO gasto (id_rubro, monto, fecha, descripcion, comprobante_url) VALUES (?, ?, ?, ?, ?)";
        $pdo = Conexion::conectar();
        $pdo->prepare($sql)->execute([$id_rubro, $monto, $fecha, $descripcion, $comprobante_url]);
        return $pdo->lastInsertId();
    }

    public static function eliminar($id)
    {
        Conexion::conectar()->prepare("DELETE FROM gasto WHERE id_gasto = ?")->execute([$id]);
    }

    public static function total()
    {
        return Conexion::conectar()->query("SELECT COALESCE(SUM(monto), 0) FROM gasto")->fetchColumn();
    }
}
