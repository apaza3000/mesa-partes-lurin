<?php

namespace App\Support;

use App\Models\Persona;
use Src\Core\Session;

/**
 * TAREA E11-2: acceso centralizado a los datos del usuario autenticado.
 *
 * Expone la persona asociada a la sesión, con fallback a la BD cuando la
 * sesión no la trae (sesiones antiguas) o falta algún campo.
 */
class UsuarioActual
{
    /**
     * Devuelve los datos de la persona del usuario autenticado.
     * Si no hay sesión, devuelve [].
     */
    public static function persona(): array
    {
        if (!Session::isAuthenticated()) {
            return [];
        }

        $persona = Session::get('usuario_persona', []);
        if (!is_array($persona) || empty($persona)) {
            $persona = self::cargarDesdeBD();
            if (!empty($persona)) {
                Session::set('usuario_persona', $persona);
            }
        }

        // Fallback de campos mínimos si la BD no devolvió todo.
        if (empty($persona['id_persona'])) {
            $user = Session::user() ?? [];
            $persona['id_persona'] = Session::get('usuario_id');
        }

        return is_array($persona) ? $persona : [];
    }

    /**
     * ID de la persona del usuario autenticado (o null).
     */
    public static function idPersona(): ?int
    {
        $persona = self::persona();
        $id = $persona['id_persona'] ?? null;
        return $id !== null ? (int) $id : null;
    }

    /**
     * ID del usuario autenticado.
     */
    public static function idUsuario(): ?int
    {
        $id = Session::get('usuario_id');
        if ($id === null) {
            $user = Session::user() ?? [];
            $id = $user['id_usuario'] ?? null;
        }
        return $id !== null ? (int) $id : null;
    }

    private static function cargarDesdeBD(): array
    {
        $idUsuario = self::idUsuario();
        if ($idUsuario === null) {
            return [];
        }

        try {
            // Obtener id_persona desde usuarios.
            $db = \Src\Core\Conexion::getConexion();
            $stmt = $db->prepare('SELECT id_persona FROM usuarios WHERE id_usuario = :id LIMIT 1');
            $stmt->execute([':id' => $idUsuario]);
            $idPersona = $stmt->fetchColumn();
            if (!$idPersona) {
                return [];
            }
            $model = new Persona();
            return $model->getById((int) $idPersona) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
