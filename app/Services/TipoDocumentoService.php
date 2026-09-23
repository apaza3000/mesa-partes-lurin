<?php
namespace App\Services;

use App\Models\TipoDocumento;
use App\Validators\TipoDocumentoValidator;
use PDO;

class TipoDocumentoService {
    private TipoDocumento $model;
    private TipoDocumentoValidator $validator;
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->model = new TipoDocumento($db);
        $this->validator = new TipoDocumentoValidator($db);
    }

    public function listAll(): array {
        return $this->model->getAll();
    }

    public function find(int|string $id): array|false {
        return $this->model->getById($id);
    }

    public function create(array $data, int $usuarioId): array {
        $errors = $this->validator->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->db->beginTransaction();
        try {
            $id = $this->model->create($data);
            $this->audit($usuarioId, 'tipos_documento', $id, 'CREAR', json_encode($data));
            $this->db->commit();
            return ['success' => true, 'message' => 'Tipo de documento registrado correctamente.'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error en BD: ' . $e->getMessage()];
        }
    }

    public function update(int|string $id, array $data, int $usuarioId): array {
        if (!$this->model->getById($id)) {
            return ['success' => false, 'message' => 'El registro no existe.'];
        }

        $errors = $this->validator->validate($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->db->beginTransaction();
        try {
            $this->model->update($id, $data);
            $this->audit($usuarioId, 'tipos_documento', $id, 'EDITAR', json_encode($data));
            $this->db->commit();
            return ['success' => true, 'message' => 'Tipo de documento actualizado correctamente.'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    public function toggleState(int|string $id, int $estado, int $usuarioId): array {
        $this->db->beginTransaction();
        try {
            $this->model->changeState($id, $estado);
            $this->audit($usuarioId, 'tipos_documento', $id, 'CAMBIAR_ESTADO', "Estado actualizado a $estado");
            $this->db->commit();
            return ['success' => true, 'message' => 'Estado actualizado correctamente.'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al cambiar el estado: ' . $e->getMessage()];
        }
    }

    public function deleteOrDisable(int|string $id, int $usuarioId): array {
        $this->db->beginTransaction();
        try {
            if ($this->model->isUsedInDocuments($id)) {
                $this->model->changeState($id, 0);
                $this->audit($usuarioId, 'tipos_documento', $id, 'CAMBIAR_ESTADO', 'Inactivado dinámicamente');
                $msg = 'Registro en uso. Se inhabilitó correctamente.';
            } else {
                $this->model->softDelete($id);
                $this->audit($usuarioId, 'tipos_documento', $id, 'ELIMINAR', 'Eliminación lógica realizada');
                $msg = 'Tipo de documento eliminado exitosamente.';
            }
            $this->db->commit();
            return ['success' => true, 'message' => $msg];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    private function audit(int $usuarioId, string $tabla, int|string $registroId, string $accion, string $detalles): void {
        $sql = "INSERT INTO auditoria (usuario_id, tabla, registro_id, accion, detalles) 
                VALUES (:usuario_id, :tabla, :registro_id, :accion, :detalles)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':tabla' => $tabla,
            ':registro_id' => $registroId,
            ':accion' => $accion,
            ':detalles' => $detalles
        ]);
    }
}
