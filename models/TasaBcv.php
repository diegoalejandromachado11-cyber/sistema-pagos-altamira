<?php
// MODELO TasaBcv: tabla "tasa_bcv" y scraping del portal del BCV

class TasaBcv
{
    // Tasa registrada para una fecha, o la más reciente anterior a ella
    public static function vigente($fecha)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM tasa_bcv WHERE fecha <= ? ORDER BY fecha DESC LIMIT 1");
        $stmt->execute([$fecha]);
        return $stmt->fetch();
    }

    public static function todas()
    {
        return Conexion::conectar()->query("SELECT * FROM tasa_bcv ORDER BY fecha DESC")->fetchAll();
    }

    // Si ya hay tasa para esa fecha se actualiza, si no se inserta
    public static function guardar($fecha, $tasa)
    {
        $sql = "INSERT INTO tasa_bcv (fecha, tasa_usd) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE tasa_usd = VALUES(tasa_usd)";
        Conexion::conectar()->prepare($sql)->execute([$fecha, $tasa]);
    }

    // -----------------------------------------------------
    // SCRAPING: lee la página del BCV y extrae la tasa del dólar.
    // En la página del BCV el valor está en:
    //   <div id="dolar"> ... <strong> 39,18130000 </strong> ... </div>
    // Devuelve el número, o null si no se pudo obtener.
    // -----------------------------------------------------
    public static function obtenerDesdeBCV()
    {
        // El certificado SSL del BCV suele dar problemas, por eso se desactiva la verificación
        $opciones = stream_context_create([
            "ssl"  => ["verify_peer" => false, "verify_peer_name" => false],
            "http" => ["timeout" => 15, "header" => "User-Agent: Mozilla/5.0"]
        ]);

        $html = @file_get_contents("https://www.bcv.org.ve/", false, $opciones);
        if ($html === false) {
            return null; // no se pudo conectar
        }

        // Convertimos el HTML en un documento para poder buscar dentro de él
        $documento = new DOMDocument();
        @$documento->loadHTML($html);
        $xpath = new DOMXPath($documento);

        // Buscamos el <strong> que está dentro del div con id="dolar"
        $nodo = $xpath->query('//div[@id="dolar"]//strong')->item(0);
        if (!$nodo) {
            return null;
        }

        // "39,18130000" -> 39.1813
        $texto = trim($nodo->textContent);
        $texto = str_replace(".", "", $texto);   // quita separador de miles
        $texto = str_replace(",", ".", $texto);  // coma decimal -> punto
        return (float) $texto;
    }
}
