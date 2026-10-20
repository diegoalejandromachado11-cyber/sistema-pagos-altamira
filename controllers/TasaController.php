<?php
// CONTROLADOR Tasa: obtener la tasa del BCV (scraping) o registrarla a mano

class TasaController extends Controller
{
    public function __construct()
    {
        $this->soloAdmin();
    }

    public function index()
    {
        $this->vista("tasa/index", [
            'tasas'         => TasaBcv::todas(),
            'ultimoIntento' => Auditoria::ultimoIntentoTasa(),
        ]);
    }

    // Botón "Actualizar desde el BCV"
    public function actualizar()
    {
        $valor = TasaBcv::obtenerDesdeBCV();

        if ($valor) {
            TasaBcv::guardar(date("Y-m-d"), $valor);
            Auditoria::registrar("SCRAPING", "tasa_bcv", null, "Tasa obtenida del BCV: $valor");
            $this->mensaje("Tasa obtenida del BCV: " . number_format($valor, 4, ',', '.') . " Bs/USD");
        } else {
            $this->mensaje("No se pudo obtener la tasa del BCV. Puede registrarla manualmente.", "danger");
        }
        $this->redirigir("tasa");
    }

    // Registro manual (por si la página del BCV no responde)
    public function manual()
    {
        TasaBcv::guardar($_POST['fecha'], $_POST['tasa_usd']);
        Auditoria::registrar("CREAR", "tasa_bcv", null, "Tasa manual: " . $_POST['tasa_usd']);
        $this->mensaje("Tasa registrada manualmente");
        $this->redirigir("tasa");
    }
}
