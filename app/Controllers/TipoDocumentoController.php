<?php
namespace App\Controllers;

use App\Services\TipoDocumentoService;
use PDO;

class TipoDocumentoController {
    private TipoDocumentoService $service;

    public function __construct(PDO $db) {
        $this->service = new TipoDocumentoService($db);
    }

    private function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['usuario_id'])) $_SESSION['usuario_id'] = 1;
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public function index(): void {
        $this->initSession();
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['data' => $this->service->listAll()]);
            exit;
        }
        require __DIR__ . '/../../views/tipos-documento/index.php';
    }

    public function store(): void {
        $this->initSession();
        header('Content-Type: application/json');

        $data = [
            'codigo' => htmlspecialchars($_POST['codigo'] ?? '', ENT_QUOTES, 'UTF-8'),
            'nombre' => htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8'),
            'descripcion' => htmlspecialchars($_POST['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'),
            'requiere_archivo' => isset($_POST['requiere_archivo']) ? 1 : 0
        ];

        echo json_encode($this->service->create($data, $_SESSION['usuario_id']));
    }

    public function show(int|string $id): void {
        $this->initSession();
        header('Content-Type: application/json');
        $item = $this->service->find($id);
        if ($item) {
            echo json_encode(['success' => true, 'data' => $item]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No encontrado.']);
        }
    }

    public function update(int|string $id): void {
        $this->initSession();
        header('Content-Type: application/json');

        $data = [
            'codigo' => htmlspecialchars($_POST['codigo'] ?? '', ENT_QUOTES, 'UTF-8'),
            'nombre' => htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8'),
            'descripcion' => htmlspecialchars($_POST['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'),
            'requiere_archivo' => isset($_POST['requiere_archivo']) ? 1 : 0
        ];

        echo json_encode($this->service->update($id, $data, $_SESSION['usuario_id']));
    }

    public function toggleState(int|string $id): void {
        $this->initSession();
        header('Content-Type: application/json');
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;
        echo json_encode($this->service->toggleState($id, $estado, $_SESSION['usuario_id']));
    }

    public function delete(int|string $id): void {
        $this->initSession();
        header('Content-Type: application/json');
        echo json_encode($this->service->deleteOrDisable($id, $_SESSION['usuario_id']));
    }
}
