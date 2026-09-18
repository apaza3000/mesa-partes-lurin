<?php

require_once __DIR__ . '/../models/derivacion.php';

class DerivacionController {
    private Derivacion $derivacionModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->derivacionModel = new Derivacion();
    }

    public function derivar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'id_documento'     => $_POST['id_documento'] ?? null,
                'id_area_origen'   => $_SESSION['usuario_area'] ?? null,
                'id_area_destino'  => $_POST['id_area_destino'] ?? null,
                'id_usuario_envia' => $_SESSION['usuario_id'] ?? null,
                'observaciones'    => trim($_POST['observaciones'] ?? '')
            ];

            if ($this->derivacionModel->crearDerivacion($datos)) {
                $_SESSION['mensaje'] = 'Documento derivado correctamente.';
            } else {
                $_SESSION['error'] = 'Ocurrió un error al derivar el documento.';
            }

            header('Location: index.php?page=derivaciones');
            exit;
        }
    }

    public function historial(int $idDocumento): array
    {
        return $this->derivacionModel->obtenerPorDocumento($idDocumento);
    }
}