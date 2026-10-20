<?php
// MODELO Gasto: tabla "gasto"

class Gasto
{
    // BUSCADOR de gastos. Filtros opcionales:
    //   $texto -> busca en la descripción o en el nombre del rubro
    //   $desde / $hasta -> rango de fechas
    public static function buscar($texto = "", $desde = "", $hasta = "")
    {
        $sql = "SELECT g.*, r.nombre AS rubro FROM gasto g
                JOIN rubro r ON g.id_rubro = r.id_rubro
                WHERE 1 = 1";
        $parametros = [];

        if ($texto != "") {
            $sql .= " AND (g.descripcion LIKE ? OR r.nombre LIKE ?)";
            array_push($parametros, "%$texto%", "%$texto%");
        }
        if ($desde != "") {
            $sql .= " AND g.fecha >= ?";
            $parametros[] = $desde;
        }
        if ($hasta != "") {
            $sql .= " AND g.fecha <= ?";
            $parametros[] = $hasta;
        }
        $sql .= " ORDER BY g.fecha DESC, g.id_gasto DESC";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
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
