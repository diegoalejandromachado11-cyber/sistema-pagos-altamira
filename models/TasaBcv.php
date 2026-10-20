<?php
// MODELO TasaBcv: tabla "tasa_bcv" y scraping del portal del BCV

class TasaBcv
{
    // Tasa registrada para una fecha, o la más reciente anterior a ella.
    // Sin internet, el sistema sigue usando la última tasa guardada.
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
                ON DUPLICATE KEY UPDATE tasa_usd = VALUES(tasa_usd), fecha_registro = NOW()";
        Conexion::conectar()->prepare($sql)->execute([$fecha, $tasa]);
    }

    // -----------------------------------------------------
    // SCRAPING: lee la página del BCV y extrae la tasa del dólar.
    // En la página del BCV el valor está en:
    //   <div id="dolar"> ... <strong> 39,18130000 </strong> ... </div>
    // Devuelve el número, o null si no se pudo obtener (por ejemplo, sin internet).
    // $segundos: tiempo máximo de espera, para que el sistema no se quede
    // "pegado" cuando no hay conexión.
    // -----------------------------------------------------
    public static function obtenerDesdeBCV($segundos = 15)
    {
        ini_set("default_socket_timeout", $segundos);

        // El certificado SSL del BCV suele dar problemas, por eso se desactiva la verificación
        $opciones = stream_context_create([
            "ssl"  => ["verify_peer" => false, "verify_peer_name" => false],
            "http" => ["timeout" => $segundos, "header" => "User-Agent: Mozilla/5.0"]
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

    // -----------------------------------------------------
    // ACTUALIZACIÓN AUTOMÁTICA a las 9:30 am y 3:30 pm (hora de Venezuela).
    // Se llama en cada página. Solo intenta el scraping UNA vez por horario:
    // revisa en la auditoría si ya hubo un intento después de esa hora.
    // -----------------------------------------------------
    public static function actualizarAutomatica()
    {
        $hoy   = date("Y-m-d");
        $ahora = time();

        // ¿Cuál es el último horario que ya pasó?
        if ($ahora >= strtotime("$hoy 15:30")) {
            $horario = "$hoy 15:30:00";
        } elseif ($ahora >= strtotime("$hoy 09:30")) {
            $horario = "$hoy 09:30:00";
        } else {
            $horario = date("Y-m-d", strtotime("-1 day")) . " 15:30:00";
        }

        // Si ya se intentó (con éxito o sin él) después de ese horario, no se hace nada
        if (Auditoria::ultimoIntentoTasa() >= $horario) {
            return;
        }

        $valor = self::obtenerDesdeBCV(5); // máximo 5 segundos de espera

        if ($valor) {
            self::guardar($hoy, $valor);
            Auditoria::registrar("SCRAPING", "tasa_bcv", null, "Actualización automática: $valor Bs/USD");
        } else {
            Auditoria::registrar("SCRAPING FALLIDO", "tasa_bcv", null, "Sin conexión con el BCV. Se mantiene la última tasa guardada");
        }
    }
}
