<?php

require_once __DIR__ . '/../models/documento.php';

class DocumentoController {
    private Documento $documentoModel;

    public function __construct() {
        $this->documentoModel = new Documento();
    }

    // Listar todos los documentos para mostrar en las tablas
    public function listar(): array
    {
        return $this->documentoModel->obtenerTodos();
    }

    // Procesar el formulario de nuevo trámite / expediente
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'codigo_unico'     => 'EXP-' . time(), // Genera un código automático
                'id_tipo_doc'      => $_POST['id_tipo_doc'] ?? null,
                'numero_documento' => trim($_POST['numero_documento'] ?? ''),
                'asunto'           => trim($_POST['asunto'] ?? ''),
                'folios'           => $_POST['folios'] ?? 1,
                'estado_actual'    => 'Registrado',
                'id_persona'       => $_POST['id_persona'] ?? null
            ];

            if ($this->documentoModel->registrar($datos)) {
                $_SESSION['mensaje'] = 'Documento registrado con éxito.';
                header('Location: index.php?page=gestion-documentaria');
            } else {
                $_SESSION['error'] = 'Error al registrar el documento.';
                header('Location: index.php?page=nuevo-documento');
            }
            exit;
        }
    }
}