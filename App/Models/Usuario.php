<?php
namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function getAllRol(): array
    {
        try {
            $stmt = $this->db->query(
                'SELECT id_rol, nombre FROM roles ORDER BY nombre ASC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    public function getAllArea(): array
    {
        try {
            $stmt = $this->db->query(
                'SELECT id_area, nombre FROM areas ORDER BY nombre ASC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    // Obtener el total de usuarios para calcular el total de páginas
    public function getTotalUsuarios(): int
    {
        try {
            $stmt = $this->db->query('SELECT COUNT(*) FROM usuarios');
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Obtener los usuarios de la página actual
    public function getUsuariosPaginados(int $pagina = 1, int $porPagina = 10): array
    {
        try {
            // Calculamos a partir de qué registro empezar
            $offset = ($pagina - 1) * $porPagina;

            $stmt = $this->db->prepare(
                'SELECT 
                    u.id_usuario, 
                    u.avatar,
                    u.username, 
                    u.estado, 
                    u.creado_en,
                    r.nombre AS rol_nombre
            FROM usuarios AS u
            LEFT JOIN usuario_roles ur ON u.id_usuario = ur.id_usuario
            LEFT JOIN roles r ON ur.id_rol = r.id_rol
            ORDER BY u.id_usuario DESC 
            LIMIT :limit OFFSET :offset'
            );

            // PDO requiere asociar LIMIT y OFFSET como ENTEROS
            $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    //nuevo ususario
    public function crearUsuarioCompleto(array $datosPersona, array $datosUsuario, int $idRol): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Insertar en tabla personas
            $sqlPersona = "INSERT INTO personas 
            (tipo_persona, tipo_documento, numero_documento, nombres, apellido_paterno, apellido_materno, email) 
            VALUES ('Natural', :tipo_doc, :num_doc, :nombres, :paterno, :materno, :email)";

            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute([
                ':tipo_doc' => $datosPersona['tipo_documento'],
                ':num_doc' => $datosPersona['numero_documento'],
                ':nombres' => $datosPersona['nombres'],
                ':paterno' => $datosPersona['apellido_paterno'],
                ':materno' => $datosPersona['apellido_materno'],
                ':email' => $datosPersona['email'],
            ]);

            $idPersona = (int) $this->db->lastInsertId();

            // 2. Insertar en tabla usuarios
            $sqlUsuario = "INSERT INTO usuarios (id_persona, id_area, username, password) 
                       VALUES (:id_persona, :id_area, :username, :password)";

            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':id_persona' => $idPersona,
                ':id_area' => $datosUsuario['id_area'],
                ':username' => $datosUsuario['username'],
                ':password' => password_hash($datosUsuario['password'], PASSWORD_BCRYPT),
            ]);

            $idUsuario = (int) $this->db->lastInsertId();

            // 3. Insertar en tabla usuario_roles
            $sqlRol = "INSERT INTO usuario_roles (id_usuario, id_rol) VALUES (:id_usuario, :id_rol)";
            $stmtRol = $this->db->prepare($sqlRol);
            $stmtRol->execute([
                ':id_usuario' => $idUsuario,
                ':id_rol' => $idRol,
            ]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }
}