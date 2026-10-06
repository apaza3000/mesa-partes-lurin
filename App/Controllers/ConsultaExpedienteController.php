<?php

namespace App\Controllers;

use App\Models\ConsutlaExpediente;
use Src\Core\Conexion;
use Src\Core\Controller;
use Src\Core\Request;

class ConsultaExpedienteController extends Controller
{

    private $model;

    public function __construct()
    {
        $this->model = new ConsutlaExpediente();
    }

    /* ============================================================
     * FORMULARIO DE BÚSQUEDA
     * ============================================================ */
    public function index()
    {
        return $this->view("consulta/index", [
            'errors'       => [],
            'datos_viejos' => [],
        ]);
    }

    /* ============================================================
     * BUSCAR EXPEDIENTE
     * ============================================================ */
    public function buscar()
    {
        $request = new Request();
        $datos   = $request->all();

        $errors = [];

        $codigo   = trim($datos['codigo'] ?? '');
        $numDoc   = trim($datos['numero_documento'] ?? '');

        if ($codigo === '') {
            $errors['codigo'][] = 'Ingrese el código del expediente.';
        }

        if (!empty($errors)) {
            return $this->view("consulta/index", [
                'errors'       => $errors,
                'datos_viejos' => $datos,
            ]);
        }

        $expediente = $this->model->buscarPorCodigo($codigo, $numDoc ?: null);

        if (!$expediente) {
            return $this->view("consulta/index", [
                'errors'       => ['general' => ['No se encontró ningún expediente con los datos proporcionados.']],
                'datos_viejos' => $datos,
            ]);
        }

        // Cargar data relacionada
        $documentos     = $this->model->obtenerDocumentos($expediente['id_expediente']);
        $historial      = $this->model->obtenerHistorial($expediente['id_expediente']);
        $derivaciones   = $this->model->obtenerDerivaciones($expediente['id_expediente']);
        $observaciones  = $this->model->obtenerObservaciones($expediente['id_expediente']);

        return $this->view("consulta/detalle", [
            'expediente'    => $expediente,
            'documentos'    => $documentos,
            'historial'     => $historial,
            'derivaciones'  => $derivaciones,
            'observaciones' => $observaciones,
        ]);
    }

    /* ============================================================
     * DESCARGAR ADJUNTO
     * ============================================================ */
    public function descargar($idArchivo)
    {
        $db = Conexion::getConexion();
        $stmt = $db->prepare(
            "SELECT nombre_original, ruta_archivo, mime_type
             FROM archivos_adjuntos WHERE id_archivo = ? LIMIT 1"
        );
        $stmt->execute([$idArchivo]);
        $archivo = $stmt->fetch();

        if (!$archivo) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }

        $rutaFisica = __DIR__ . '/../../public/' . $archivo['ruta_archivo'];

        if (!file_exists($rutaFisica)) {
            http_response_code(404);
            exit('El archivo no existe en el servidor.');
        }

        header('Content-Type: ' . ($archivo['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . basename($archivo['nombre_original']) . '"');
        header('Content-Length: ' . filesize($rutaFisica));
        readfile($rutaFisica);
        exit;
    }
}
