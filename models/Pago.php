<?php
// MODELO Pago: tabla "pago"

class Pago
{
    // Lista de pagos con el nombre del vecino, el rubro y la tasa usada.
    // Si se pasa $id_usuario, solo devuelve los pagos de ese vecino.
    public static function todos($id_usuario = null, $limite = null)
    {
        $sql = "SELECT p.*, u.nombre, u.cedula, r.nombre AS rubro, t.tasa_usd
                FROM pago p
                JOIN usuario u ON p.id_usuario = u.id_usuario
                JOIN rubro r ON p.id_rubro = r.id_rubro
                LEFT JOIN tasa_bcv t ON p.id_tasa = t.id_tasa";
        $parametros = [];
        if ($id_usuario !== null) {
            $sql .= " WHERE p.id_usuario = ?";
            $parametros[] = $id_usuario;
        }
        $sql .= " ORDER BY p.fecha_pago DESC, p.id_pago DESC";
        if ($limite !== null) {
            $sql .= " LIMIT " . (int) $limite;
        }
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public static function crear($datos)
    {
        $sql = "INSERT INTO pago (id_usuario, id_rubro, id_tasa, monto, monto_usd, fecha_pago, periodo, metodo_pago, referencia)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo = Conexion::conectar();
        $pdo->prepare($sql)->execute([
            $datos['id_usuario'], $datos['id_rubro'], $datos['id_tasa'], $datos['monto'], $datos['monto_usd'],
            $datos['fecha_pago'], $datos['periodo'], $datos['metodo_pago'], $datos['referencia']
        ]);
        return $pdo->lastInsertId();
    }

    public static function eliminar($id)
    {
        Conexion::conectar()->prepare("DELETE FROM pago WHERE id_pago = ?")->execute([$id]);
    }

    // Total pagado en dólares (de todos, o de un vecino)
    public static function totalUsd($id_usuario = null)
    {
        if ($id_usuario === null) {
            return Conexion::conectar()->query("SELECT COALESCE(SUM(monto_usd), 0) FROM pago")->fetchColumn();
        }
        $stmt = Conexion::conectar()->prepare("SELECT COALESCE(SUM(monto_usd), 0) FROM pago WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        return $stmt->fetchColumn();
    }
}
