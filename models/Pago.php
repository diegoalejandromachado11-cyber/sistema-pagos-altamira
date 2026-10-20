<?php
// MODELO Pago: tabla "pago"

class Pago
{
    // Consulta base: pago + nombre y cédula del vecino + rubro + tasa usada
    private static $sqlBase = "SELECT p.*, u.nombre, u.cedula, r.nombre AS rubro, t.tasa_usd
                               FROM pago p
                               JOIN usuario u ON p.id_usuario = u.id_usuario
                               JOIN rubro r ON p.id_rubro = r.id_rubro
                               LEFT JOIN tasa_bcv t ON p.id_tasa = t.id_tasa";

    // BUSCADOR de pagos. Todos los filtros son opcionales:
    //   $id_usuario -> solo los pagos de ese vecino (lo usa el rol Vecino)
    //   $cedula     -> cédula o nombre del vecino (ej. "31525776" o "Ana")
    //   $id_rubro   -> solo ese rubro
    //   $periodo    -> solo ese mes (AAAA-MM)
    public static function buscar($id_usuario = null, $cedula = "", $id_rubro = "", $periodo = "", $limite = null)
    {
        $sql = self::$sqlBase . " WHERE 1 = 1";
        $parametros = [];

        if ($id_usuario !== null) {
            $sql .= " AND p.id_usuario = ?";
            $parametros[] = $id_usuario;
        }
        if ($cedula != "") {
            // Se ignoran puntos, guiones y la letra: "V-31.525.776" encuentra "31525776"
            $soloNumeros = preg_replace('/[^0-9]/', '', $cedula);
            $sql .= " AND (REPLACE(REPLACE(u.cedula, '.', ''), '-', '') LIKE ? OR u.nombre LIKE ?)";
            $parametros[] = "%" . ($soloNumeros != "" ? $soloNumeros : $cedula) . "%";
            $parametros[] = "%$cedula%";
        }
        if ($id_rubro != "") {
            $sql .= " AND p.id_rubro = ?";
            $parametros[] = $id_rubro;
        }
        if ($periodo != "") {
            $sql .= " AND p.periodo = ?";
            $parametros[] = $periodo;
        }
        $sql .= " ORDER BY p.fecha_pago DESC, p.id_pago DESC";
        if ($limite !== null) {
            $sql .= " LIMIT " . (int) $limite;
        }

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    // Un solo pago (para la factura)
    public static function buscarPorId($id)
    {
        $stmt = Conexion::conectar()->prepare(self::$sqlBase . " WHERE p.id_pago = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // BLOQUEO DE DUPLICADOS: ¿este vecino ya pagó este rubro?
    //   Rubro mensual -> se revisa el mismo mes
    //   Rubro único   -> se revisa si lo pagó alguna vez
    public static function yaPagado($id_usuario, $rubro, $periodo)
    {
        if ($rubro['mensual']) {
            $sql = "SELECT COUNT(*) FROM pago WHERE id_usuario = ? AND id_rubro = ? AND periodo = ?";
            $parametros = [$id_usuario, $rubro['id_rubro'], $periodo];
        } else {
            $sql = "SELECT COUNT(*) FROM pago WHERE id_usuario = ? AND id_rubro = ?";
            $parametros = [$id_usuario, $rubro['id_rubro']];
        }
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchColumn() > 0;
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

    // Lo que pagó cada vecino en un mes (por periodo): [id_usuario => [usd, bs]]
    public static function totalesPorVecino($periodo)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT id_usuario, SUM(monto_usd) AS usd, SUM(monto) AS bs FROM pago WHERE periodo = ? GROUP BY id_usuario");
        $stmt->execute([$periodo]);
        $resultado = [];
        foreach ($stmt->fetchAll() as $fila) {
            $resultado[$fila['id_usuario']] = $fila;
        }
        return $resultado;
    }

    // Todos los pagos como "claves" para saber rápido qué está pagado.
    // Ej.: "5-1-2026-10" = vecino 5 pagó el rubro 1 del mes 2026-10
    //      "5-4"         = vecino 5 pagó alguna vez el rubro 4
    public static function clavesPagadas()
    {
        $claves = [];
        foreach (Conexion::conectar()->query("SELECT id_usuario, id_rubro, periodo FROM pago") as $p) {
            $claves[$p['id_usuario'] . "-" . $p['id_rubro'] . "-" . $p['periodo']] = true;
            $claves[$p['id_usuario'] . "-" . $p['id_rubro']] = true;
        }
        return $claves;
    }
}
