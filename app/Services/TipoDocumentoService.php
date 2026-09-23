<?php
namespace App\Services;

use App\Models\TipoDocumento;
use App\Validators\TipoDocumentoValidator;
use PDO;

class TipoDocumentoService {
    private $model;
    private $validator;
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->model = new TipoDocumento($db);
        $this->validator = new TipoDocumentoValidator($db);
    }

    public function listAll() {
        return $this->model->getAll();
    }

    public function find($id) {
        return $this->model->getById($id);
    }

    public function create($data, $usuarioId) {
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

    public function update($id, $data, $usuarioId) {
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

    public function toggleState($id, $estado, $usuarioId) {
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

    public function deleteOrDisable($id, $usuarioId) {
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

    private function audit($usuarioId, $tabla, $registroId, $accion, $detalles) {
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
