<?php

require_once __DIR__ . '/Conexion.php';

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Obtener usuario por email para el login
    public function obtenerPorEmail(string $email): ?array
    {
        try {
            $sql = "SELECT u.*, r.nombre AS rol, a.nombre AS area, p.nombre, p.apellido_p, p.email
                    FROM usuarios u
                    INNER JOIN persona p ON u.id_persona = p.id_persona
                    INNER JOIN roles r ON u.id_rol = r.id
                    INNER JOIN areas a ON u.id_area = a.id_area
                    WHERE p.email = :email AND u.estado = 'Activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $resultado = $stmt->fetch();
            return $resultado ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}