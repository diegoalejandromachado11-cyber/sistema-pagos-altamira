<?php
// =====================================================
// funciones.php: funciones que se usan en varias páginas
// =====================================================

// Si no hay sesión iniciada, manda al login
function verificarSesion() {
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: login.php");
        exit;
    }
}

// Devuelve true si el usuario conectado es Administrador (id_rol = 1)
function esAdmin() {
    return isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1;
}

// Solo deja pasar a administradores
function soloAdmin() {
    if (!esAdmin()) {
        header("Location: index.php");
        exit;
    }
}

// Guarda en la tabla AUDITORIA quién hizo qué
function registrarAuditoria($conexion, $accion, $tabla, $registro_id, $detalle) {
    $sql = "INSERT INTO auditoria (id_usuario, accion, tabla_afectada, registro_id, detalle, ip)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        $_SESSION['id_usuario'] ?? null,
        $accion,
        $tabla,
        $registro_id,
        $detalle,
        $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
}

// -----------------------------------------------------
// SCRAPING: lee la página del BCV y extrae la tasa del dólar.
// En la página del BCV el valor está en:
//   <div id="dolar"> ... <strong> 39,18130000 </strong> ... </div>
// -----------------------------------------------------
function obtenerTasaBCV() {
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

// Busca la tasa registrada para una fecha (o la más reciente anterior)
function buscarTasa($conexion, $fecha) {
    $stmt = $conexion->prepare("SELECT * FROM tasa_bcv WHERE fecha <= ? ORDER BY fecha DESC LIMIT 1");
    $stmt->execute([$fecha]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
