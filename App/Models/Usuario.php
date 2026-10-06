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


    /*
     Autentica un usuario activo mediante username o correo electrónico.
     */
    public function login(string $identificador, string $password)
    {
        $sql = "SELECT u.id_usuario, u.id_persona, u.id_area, u.username,
                    u.password, u.avatar, u.estado,
                    p.nombres, p.apellido_paterno, p.apellido_materno, p.email,
                    GROUP_CONCAT(DISTINCT r.nombre ORDER BY r.nombre SEPARATOR ', ') AS roles
                FROM usuarios u
                INNER JOIN personas p ON p.id_persona = u.id_persona
                LEFT JOIN usuario_roles ur ON ur.id_usuario = u.id_usuario
                LEFT JOIN roles r ON r.id_rol = ur.id_rol AND r.estado = 'Activo'
                WHERE u.estado = 'Activo'
                    AND (u.username = :username OR p.email = :email)
                GROUP BY u.id_usuario, u.id_persona, u.id_area, u.username,
                    u.password, u.avatar, u.estado,
                    p.nombres, p.apellido_paterno, p.apellido_materno, p.email";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username' => trim($identificador),
                ':email' => trim($identificador),
            ]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            return $usuario;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function registrarLogin(int $idUsuario): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO auditoria
                (id_usuario, modulo, accion, tabla_afectada, id_registro, descripcion, ip)
             VALUES
                (:id_usuario, 'AUTH', 'LOGIN', 'usuarios', :id_registro, :descripcion, :ip)"
        );
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':id_registro' => $idUsuario,
            ':descripcion' => 'Inicio de sesión exitoso.',
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    // 1. Obtener usuario por email para el Login
    public function obtenerPorEmail(string $email): ?array
    {
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


    // Cambiar Estado (Activo / Inactivo / Suspendido)
    public function cambiarEstado(int $idUsuario, string $nuevoEstado): bool
    {
        $sql = "UPDATE usuarios SET estado = :estado, update_at = NOW() WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id' => $idUsuario
        ]);
    }

    public function registrarInvitado($data)
    {
        try {
            $this->db->beginTransaction();

            // 1. Insertar persona
            $sqlPersona = "INSERT INTO personas 
                (tipo_persona, tipo_documento, numero_documento, nombres,
                 apellido_paterno, apellido_materno, email, telefono, direccion, estado)
                VALUES 
                ('Natural', :tipo_documento, :numero_documento, :nombres,
                 :apellido_paterno, :apellido_materno, :email, :telefono, :direccion, 'Activo')";
            $stmt = $this->db->prepare($sqlPersona);
            $stmt->execute([
                ':tipo_documento' => $data['tipo_documento'],
                ':numero_documento' => $data['numero_documento'],
                ':nombres' => $data['nombres'],
                ':apellido_paterno' => $data['apellido_paterno'],
                ':apellido_materno' => $data['apellido_materno'],
                ':email' => $data['email'],
                ':telefono' => $data['telefono'],
                ':direccion' => $data['direccion'],
            ]);
            $idPersona = $this->db->lastInsertId();

            // 2. Insertar usuario (password hasheado)
            $sqlUsuario = "INSERT INTO usuarios 
                (id_persona, id_area, username, password, avatar, estado)
                VALUES (:id_persona, NULL, :username, :password, :imagen , 'Activo')";
            $stmt = $this->db->prepare($sqlUsuario);
            $stmt->execute([
                ':id_persona' => $idPersona,
                ':username' => $data['username'],
                ':password' => $data['password'],
                ":imagen" => $data["imagen_uri"] ?? 'assets/img/default-user.png'
            ]);
            $idUsuario = $this->db->lastInsertId();

            // 3. Obtener o crear rol INVITADO
            $stmt = $this->db->prepare("SELECT id_rol FROM roles WHERE nombre = 'CONSULTA' LIMIT 1");
            $stmt->execute();
            $rol = $stmt->fetch();

            if ($rol) {
                $idRol = $rol['id_rol'];
            } else {
                $stmt = $this->db->prepare(
                    "INSERT INTO roles (nombre, descripcion, estado) 
                     VALUES ('INVITADO', 'Usuario externo con acceso limitado', 'Activo')"
                );
                $stmt->execute();
                $idRol = $this->db->lastInsertId();
            }

            // 4. Asignar rol al usuario
            $stmt = $this->db->prepare(
                "INSERT INTO usuario_roles (id_usuario, id_rol) VALUES (?, ?)"
            );
            $stmt->execute([$idUsuario, $idRol]);

            $this->db->commit();

            return [
                'ok' => true,
                'id_persona' => $idPersona,
                'id_usuario' => $idUsuario,
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'ok' => false,
                'mensaje' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ];
        }
    }

    // Obtener un usuario por ID
    public function obtenerPorId(int $id)
    {
        $sql = "SELECT u.id_usuario, u.id_persona, u.id_area, u.username, u.estado,
                       p.tipo_documento, p.numero_documento, p.nombres, 
                       p.apellido_paterno, p.apellido_materno, p.email,
                       ur.id_rol
                FROM usuarios u
                INNER JOIN personas p ON p.id_persona = u.id_persona
                LEFT JOIN usuario_roles ur ON ur.id_usuario = u.id_usuario
                WHERE u.id_usuario = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    //ACTUALIZAR
    public function actualizarCompleto(int $idUsuario, array $datosPersona, array $datosUsuario, int $idRol): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Obtener id_persona asociado al id_usuario
            $stmtPersonaId = $this->db->prepare("SELECT id_persona FROM usuarios WHERE id_usuario = :id_usuario");
            $stmtPersonaId->execute([':id_usuario' => $idUsuario]);
            $idPersona = $stmtPersonaId->fetchColumn();

            if (!$idPersona) {
                $this->db->rollBack();
                return false;
            }

            // 2. Actualizar la tabla personas
            $sqlPersona = "UPDATE personas 
                       SET tipo_documento = :tipo_doc,
                           numero_documento = :num_doc,
                           nombres = :nombres,
                           apellido_paterno = :paterno,
                           apellido_materno = :materno,
                           email = :email
                       WHERE id_persona = :id_persona";

            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute([
                ':tipo_doc' => $datosPersona['tipo_documento'],
                ':num_doc' => $datosPersona['numero_documento'],
                ':nombres' => $datosPersona['nombres'],
                ':paterno' => $datosPersona['apellido_paterno'],
                ':materno' => $datosPersona['apellido_materno'],
                ':email' => $datosPersona['email'],
                ':id_persona' => $idPersona
            ]);

            // 3. Actualizar la tabla usuarios
            $sqlUsuario = "UPDATE usuarios 
                       SET username = :username,
                           id_area = :id_area,
                           estado = :estado
                       WHERE id_usuario = :id_usuario";

            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':username' => $datosUsuario['username'],
                ':id_area' => !empty($datosUsuario['id_area']) ? $datosUsuario['id_area'] : null,
                ':estado' => $datosUsuario['estado'],
                ':id_usuario' => $idUsuario
            ]);

            // 4. Actualizar el rol en usuario_roles
            if ($idRol > 0) {
                $stmtDelRol = $this->db->prepare("DELETE FROM usuario_roles WHERE id_usuario = :id_usuario");
                $stmtDelRol->execute([':id_usuario' => $idUsuario]);

                $stmtInsRol = $this->db->prepare("INSERT INTO usuario_roles (id_usuario, id_rol) VALUES (:id_usuario, :id_rol)");
                $stmtInsRol->execute([
                    ':id_usuario' => $idUsuario,
                    ':id_rol' => $idRol
                ]);
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    /*
    =====================================================================
    Eliminar usuario y sus relaciones de la base de datos
    =====================================================================
    */
    public function eliminar(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Eliminar relaciones en usuario_roles
            $stmtRol = $this->db->prepare("DELETE FROM usuario_roles WHERE id_usuario = :id");
            $stmtRol->execute([':id' => $id]);

            // 2. Eliminar usuario
            $stmtUser = $this->db->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
            $stmtUser->execute([':id' => $id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return false;
        }
    }
}
