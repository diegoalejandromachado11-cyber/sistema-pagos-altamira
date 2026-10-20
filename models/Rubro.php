<?php
// MODELO Rubro: tabla "rubro" (conceptos de cobro: condominio, vigilancia, etc.)
//   mensual = 1  -> se cobra todos los meses desde "cobrar_desde"
//   mensual = 0  -> se cobra una sola vez (ej. reparación del portón)

class Rubro
{
    // Lista de rubros. $buscar filtra por nombre o descripción.
    public static function todos($buscar = "")
    {
        if ($buscar == "") {
            return Conexion::conectar()->query("SELECT * FROM rubro ORDER BY nombre")->fetchAll();
        }
        $stmt = Conexion::conectar()->prepare("SELECT * FROM rubro WHERE nombre LIKE ? OR descripcion LIKE ? ORDER BY nombre");
        $stmt->execute(["%$buscar%", "%$buscar%"]);
        return $stmt->fetchAll();
    }

    public static function buscar($id)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM rubro WHERE id_rubro = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function crear($nombre, $monto_base, $mensual, $cobrar_desde, $descripcion)
    {
        $pdo = Conexion::conectar();
        $pdo->prepare("INSERT INTO rubro (nombre, monto_base, mensual, cobrar_desde, descripcion) VALUES (?, ?, ?, ?, ?)")
            ->execute([$nombre, $monto_base, $mensual, $cobrar_desde, $descripcion]);
        return $pdo->lastInsertId();
    }

    public static function actualizar($id, $nombre, $monto_base, $mensual, $cobrar_desde, $descripcion)
    {
        Conexion::conectar()
            ->prepare("UPDATE rubro SET nombre = ?, monto_base = ?, mensual = ?, cobrar_desde = ?, descripcion = ? WHERE id_rubro = ?")
            ->execute([$nombre, $monto_base, $mensual, $cobrar_desde, $descripcion, $id]);
    }

    // Falla (lanza excepción) si el rubro tiene pagos o gastos asociados
    public static function eliminar($id)
    {
        Conexion::conectar()->prepare("DELETE FROM rubro WHERE id_rubro = ?")->execute([$id]);
    }
}
