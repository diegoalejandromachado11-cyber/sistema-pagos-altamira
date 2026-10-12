<?php
// MODELO Auditoria: tabla "auditoria"

class Auditoria
{
    // Guarda quién hizo qué, sobre qué tabla y desde qué IP
    public static function registrar($accion, $tabla, $registro_id, $detalle)
    {
        $sql = "INSERT INTO auditoria (id_usuario, accion, tabla_afectada, registro_id, detalle, ip)
                VALUES (?, ?, ?, ?, ?, ?)";
        Conexion::conectar()->prepare($sql)->execute([
            $_SESSION['id_usuario'] ?? null,
            $accion,
            $tabla,
            $registro_id,
            $detalle,
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    }

    // Últimos movimientos con el nombre del usuario que los hizo
    public static function ultimos($limite = 200)
    {
        $sql = "SELECT a.*, u.nombre FROM auditoria a
                LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
                ORDER BY a.fecha DESC, a.id_auditoria DESC LIMIT " . (int) $limite;
        return Conexion::conectar()->query($sql)->fetchAll();
    }
}
