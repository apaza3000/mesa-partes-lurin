<?php

namespace App\Models;

use Exception;
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
    public function obtenerPorEmail(string $email)
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
                    p.nombres,
                    p.apellido_paterno,
                    p.apellido_materno,
                    a.nombre AS area_nombre,
                    u.estado, 
                    u.creado_en,
                    r.nombre AS rol_nombre
            FROM usuarios AS u
            INNER JOIN personas p ON p.id_persona = u.id_persona
            INNER JOIN areas a on a.id_area = u.id_area 
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
    /**
     * Buscar una persona por su tipo y número de documento
     */
    public function obtenerPersonaPorDocumento(string $tipo, string $numero): ?array
    {
        $sql = "SELECT * FROM personas WHERE tipo_documento = :tipo AND numero_documento = :numero LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tipo' => $tipo,
            ':numero' => $numero
        ]);

        $persona = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $persona ? $persona : null;
    }

    /**
     * Verificar si una persona ya cuenta con un usuario registrado en el sistema
     */
    public function tieneUsuarioAsignado(int $idPersona): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE id_persona = :id_persona";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_persona' => $idPersona]);

        return (int) $stmt->fetchColumn() > 0;
    }
    //nuevo ususario
    public function crearUsuarioCompleto(array $datosPersona, array $datosUsuario, int $idRol): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Verificar si la persona ya existe
            $stmtCheck = $this->db->prepare("SELECT id_persona FROM personas WHERE tipo_documento = :tipo_doc AND numero_documento = :num_doc LIMIT 1");
            $stmtCheck->execute([
                ':tipo_doc' => $datosPersona['tipo_documento'],
                ':num_doc' => $datosPersona['numero_documento']
            ]);
            $idPersona = $stmtCheck->fetchColumn();

            if (!$idPersona) {
                // Insertar persona si no existe
                $sqlPersona = "INSERT INTO personas (tipo_documento, numero_documento, nombres, apellido_paterno, apellido_materno, email, telefono, direccion) 
                           VALUES (:tipo_doc, :num_doc, :nombres, :paterno, :materno, :email, :telefono, :direccion)";
                $stmtPersona = $this->db->prepare($sqlPersona);
                $stmtPersona->execute([
                    ':tipo_doc' => $datosPersona['tipo_documento'],
                    ':num_doc' => $datosPersona['numero_documento'],
                    ':nombres' => $datosPersona['nombres'],
                    ':paterno' => $datosPersona['apellido_paterno'],
                    ':materno' => $datosPersona['apellido_materno'],
                    ':email' => $datosPersona['email'],
                    ':telefono' => $datosPersona['telefono'] ?? null,
                    ':direccion' => $datosPersona['direccion'] ?? null,
                ]);
                $idPersona = (int) $this->db->lastInsertId();
            } else {
                // Actualizar datos de persona existente
                $sqlUpPersona = "UPDATE personas SET nombres = :nombres, apellido_paterno = :paterno, apellido_materno = :materno, email = :email, telefono = :telefono, direccion = :direccion WHERE id_persona = :id_persona";
                $stmtUp = $this->db->prepare($sqlUpPersona);
                $stmtUp->execute([
                    ':nombres' => $datosPersona['nombres'],
                    ':paterno' => $datosPersona['apellido_paterno'],
                    ':materno' => $datosPersona['apellido_materno'],
                    ':email' => $datosPersona['email'],
                    ':telefono' => $datosPersona['telefono'] ?? null,
                    ':direccion' => $datosPersona['direccion'] ?? null,
                    ':id_persona' => $idPersona
                ]);
            }

            // 2. Validar si la persona ya tiene cuenta de usuario
            $stmtUserCheck = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE id_persona = :id_persona LIMIT 1");
            $stmtUserCheck->execute([':id_persona' => $idPersona]);
            if ($stmtUserCheck->fetchColumn()) {
                throw new \Exception("Esta persona ya cuenta con un usuario registrado en el sistema.");
            }

            // 3. Crear usuario
            $sqlUsuario = "INSERT INTO usuarios (id_persona, id_area, username, password, estado) VALUES (:id_persona, :id_area, :username, :password, 'Activo')";
            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':id_persona' => $idPersona,
                ':id_area' => $datosUsuario['id_area'],
                ':username' => $datosUsuario['username'],
                ':password' => password_hash($datosUsuario['password'], PASSWORD_BCRYPT),
            ]);
            $idUsuario = (int) $this->db->lastInsertId();

            // 4. Asignar rol
            $sqlRol = "INSERT INTO usuario_roles (id_usuario, id_rol) VALUES (:id_usuario, :id_rol)";
            $stmtRol = $this->db->prepare($sqlRol);
            $stmtRol->execute([
                ':id_usuario' => $idUsuario,
                ':id_rol' => $idRol,
            ]);

            $this->db->commit();

            // Retornar éxito con el ID del usuario creado
            return ['estatus' => true, 'id' => $idUsuario];

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error al crear usuario: " . $e->getMessage());
            return ['estatus' => false, 'error' => $e->getMessage()];
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

    // Obtener un USUARIO por ID
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
    // Obtener un MAS DETALLES DE USUARIO por ID
    public function obtenerAllPorId(int $id)
    {
        $sql =
            "SELECT 
            u.id_usuario,
            u.username,
            u.avatar,
            u.estado AS estado_usuario,
            u.ultimo_acceso,
            u.creado_en AS usuario_creado_en,
            u.actualizado_en AS usuario_actualizado_en,

            -- Datos de la persona
            p.id_persona,
            p.tipo_persona,
            p.tipo_documento,
            p.numero_documento,
            p.nombres,
            p.apellido_paterno,
            p.apellido_materno,
            p.razon_social,
            p.email,
            p.telefono,
            p.direccion,
            p.estado AS estado_persona,

            -- Datos del área
            a.id_area,
            a.nombre AS area_nombre,
            a.siglas AS area_siglas,

            -- Datos del rol
            r.id_rol,
            r.nombre AS rol_nombre,
            r.descripcion AS rol_descripcion

        FROM usuarios u
        INNER JOIN personas p ON u.id_persona = p.id_persona

        LEFT JOIN areas a ON u.id_area = a.id_area

        LEFT JOIN usuario_roles ur ON u.id_usuario = ur.id_usuario

        LEFT JOIN roles r ON ur.id_rol = r.id_rol

        WHERE u.id_usuario = :id_usuario; LIMIT 1";


        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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
