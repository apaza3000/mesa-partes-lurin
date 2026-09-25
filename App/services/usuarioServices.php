<?php

namespace App\Services;

use App\Models\Usuario;
use PDO;
use PDOException;
use Src\Core\Conexion;

class UsuarioServices
{
    private Usuario $usuarioModel;
    private PDO $db;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->db = Conexion::getConexion();
    }

    /**
     * Obtiene todos los usuarios formateados para el listado principal.
     */
    public function listarUsuarios(): array
    {
        return $this->usuarioModel->getAll();
    }

    /**
     * Obtiene un usuario específico por su ID.
     */
    public function obtenerPorId(int $id): ?array
    {
        $usuario = $this->usuarioModel->getById($id);
        return !empty($usuario) ? $usuario : null;
    }

    /**
     * Maneja la creación de un nuevo usuario con Hash, Rol y Auditoría.
     */
    public function crearUsuario(array $datos): array
    {
        try {
            // 1. Iniciar transacción para garantizar consistencia entre tablas
            $this->db->beginTransaction();

            // 2. Hash de contraseña obligatorio usando password_hash()
            $datos['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);

            // 3. Crear el usuario en la tabla 'usuarios'
            $idUsuario = $this->usuarioModel->create($datos);

            if (!$idUsuario) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se pudo registrar el usuario.'];
            }

            // 4. Asignar Rol en la tabla pivote 'usuario_roles'
            if (!empty($datos['id_rol'])) {
                $this->asignarRol($idUsuario, (int)$datos['id_rol']);
            }

            // 5. Registrar acción en la tabla 'auditoria'
            $this->registrarAuditoria('CREAR', 'usuarios', $idUsuario);

            $this->db->commit();
            return ['success' => true, 'id' => $idUsuario];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en UsuarioServices::crearUsuario -> " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al procesar el registro.'];
        }
    }

    /**
     * Maneja la actualización de un usuario existente.
     */
    public function actualizarUsuario(int $id, array $datos): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Si enviaron una nueva clave, aplicar hash; si no, mantener la actual
            if (!empty($datos['password'])) {
                $datos['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);
            } else {
                unset($datos['password']);
            }

            // 2. Actualizar datos base del usuario
            $actualizado = $this->usuarioModel->update($datos, $id);

            if (!$actualizado) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se pudo actualizar el usuario.'];
            }

            // 3. Reasignar o actualizar Rol si cambió
            if (isset($datos['id_rol'])) {
                $this->sincronizarRol($id, (int)$datos['id_rol']);
            }

            // 4. Registrar acción en auditoría
            $this->registrarAuditoria('EDITAR', 'usuarios', $id);

            $this->db->commit();
            return ['success' => true];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en UsuarioServices::actualizarUsuario -> " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al actualizar.'];
        }
    }

    /**
     * Eliminación lógica o física de usuario con auditoría.
     */
    public function eliminarUsuario(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            $exito = $this->usuarioModel->delete($id);

            if ($exito) {
                $this->registrarAuditoria('ELIMINAR', 'usuarios', $id);
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en UsuarioServices::eliminarUsuario -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método auxiliar para asignar rol en la tabla pivote 'usuario_roles'
     */
    private function asignarRol(int $idUsuario, int $idRol): void
    {
        $stmt = $this->db->prepare('INSERT INTO usuario_roles (id_usuario, id_rol) VALUES (:usuario_id, :rol_id)');
        $stmt->execute([':usuario_id' => $idUsuario, ':rol_id' => $idRol]);
    }

    /**
     * Actualiza la relación de rol en 'usuario_roles'
     */
    private function sincronizarRol(int $idUsuario, int $idRol): void
    {
        $stmt = $this->db->prepare('DELETE FROM usuario_roles WHERE id_usuario = :usuario_id');
        $stmt->execute([':usuario_id' => $idUsuario]);

        $this->asignarRol($idUsuario, $idRol);
    }

    /**
     * Registra en la tabla 'auditoria' las acciones realizadas.
     */
    private function registrarAuditoria(string $accion, string $tabla, int $registroId): void
    {
        try {
            $idUsuarioSesion = $_SESSION['usuario_id'] ?? 1; // 1 por defecto o id del usuario logueado

            $stmt = $this->db->prepare(
                'INSERT INTO auditoria (id_usuario, accion, tabla, id_registro, fecha)
                 VALUES (:usuario, :accion, :tabla, :registro, NOW())'
            );

            $stmt->execute([
                ':usuario'  => $idUsuarioSesion,
                ':accion'   => $accion,
                ':tabla'    => $tabla,
                ':registro' => $registroId
            ]);
        } catch (PDOException $e) {
            // Se registra en logs de servidor si falla la auditoría sin detener el flujo principal
            error_log("Error en auditoría -> " . $e->getMessage());
        }
    }
}