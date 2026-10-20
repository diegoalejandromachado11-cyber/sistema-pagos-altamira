<?php
// MODELO Auditoria: tabla "auditoria"

class Auditoria
{
    // Guarda quién hizo qué, sobre qué tabla, cuándo y desde qué IP
    public static function registrar($accion, $tabla, $registro_id, $detalle)
    {
        $sql = "INSERT INTO auditoria (id_usuario, accion, tabla_afectada, registro_id, detalle, fecha, ip)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        Conexion::conectar()->prepare($sql)->execute([
            $_SESSION['id_usuario'] ?? null,
            $accion,
            $tabla,
            $registro_id,
            $detalle,
            date("Y-m-d H:i:s"),          // hora de Venezuela
            $_SERVER['REMOTE_ADDR'] ?? 'sistema'
        ]);
    }

    // BUSCADOR: todos los filtros son opcionales.
    // $texto busca en el nombre o cédula del usuario y en el detalle.
    public static function buscar($texto = "", $accion = "", $desde = "", $hasta = "", $limite = 300)
    {
        $sql = "SELECT a.*, u.nombre, u.cedula FROM auditoria a
                LEFT JOIN usuario u ON a.id_usuario = u.id_usuario
                WHERE 1 = 1";
        $parametros = [];

        if ($texto != "") {
            $sql .= " AND (u.nombre LIKE ? OR u.cedula LIKE ? OR a.detalle LIKE ? OR a.tabla_afectada LIKE ?)";
            array_push($parametros, "%$texto%", "%$texto%", "%$texto%", "%$texto%");
        }
        if ($accion != "") {
            $sql .= " AND a.accion = ?";
            $parametros[] = $accion;
        }
        if ($desde != "") {
            $sql .= " AND DATE(a.fecha) >= ?";
            $parametros[] = $desde;
        }
        if ($hasta != "") {
            $sql .= " AND DATE(a.fecha) <= ?";
            $parametros[] = $hasta;
        }
        $sql .= " ORDER BY a.fecha DESC, a.id_auditoria DESC LIMIT " . (int) $limite;

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    // Lista de acciones distintas, para el filtro del buscador
    public static function acciones()
    {
        return Conexion::conectar()->query("SELECT DISTINCT accion FROM auditoria ORDER BY accion")->fetchAll(PDO::FETCH_COLUMN);
    }

    // Fecha del último intento de actualizar la tasa (manual o automático)
    public static function ultimoIntentoTasa()
    {
        $sql = "SELECT MAX(fecha) FROM auditoria WHERE accion IN ('SCRAPING', 'SCRAPING FALLIDO')";
        return (string) Conexion::conectar()->query($sql)->fetchColumn();
    }
}
