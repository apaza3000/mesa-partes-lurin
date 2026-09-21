<?php

namespace App\Models;

use PDO;
use PDOException;
<<<<<<< HEAD:App/Models/Usuario.php
<<<<<<< HEAD:App/Models/Usuario.php
<<<<<<< HEAD:App/Models/Usuario.php
use Exception;
use Src\Core\Conexion;
=======
>>>>>>> parent of 25582e8 (creacion del CRUD incompleto):app/models/usuario.php
=======
>>>>>>> parent of 25582e8 (creacion del CRUD incompleto):app/models/usuario.php
=======
>>>>>>> parent of 25582e8 (creacion del CRUD incompleto):app/models/usuario.php

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Obtener usuario por email para el Login
    public function obtenerPorEmail(string $email): ?array
    {
        try {
            $sql = "SELECT u.*, r.nombre AS rol, a.nombre AS area, p.nombre, p.apellido_P, p.email
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
<<<<<<< HEAD:App/Models/Usuario.php
<<<<<<< HEAD:App/Models/Usuario.php
<<<<<<< HEAD:App/Models/Usuario.php

    // 2. Obtener todos los usuarios (para la tabla principal)
    public function obtenerTodos(): array
    {
        $sql = "SELECT u.id_usuario, u.estado, u.creado_en, 
                       p.id_persona, p.dni, p.nombre, p.apellido_P, p.apellido_M, p.email,
                       r.nombre AS rol, a.nombre AS area
                FROM usuarios u
                INNER JOIN persona p ON u.id_persona = p.id_persona
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN areas a ON u.id_area = a.id_area
                ORDER BY u.id_usuario DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Obtener un usuario por ID (para Editar / Ver detalle)
    public function obtenerPorId(int $idUsuario): ?array
    {
        $sql = "SELECT u.id_usuario, u.id_rol, u.id_area, u.id_persona, u.estado, u.avatar,
                       p.dni, p.nombre, p.apellido_P, p.apellido_M, p.email,
                       r.nombre AS rol, a.nombre AS area
                FROM usuarios u
                INNER JOIN persona p ON u.id_persona = p.id_persona
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN areas a ON u.id_area = a.id_area
                WHERE u.id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    // 4. Validar si el Email ya existe
    public function emailExiste(string $email, ?int $excludePersonaId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM persona WHERE email = :email";
        if ($excludePersonaId) {
            $sql .= " AND id_persona != :id_persona";
        }

        $stmt = $this->db->prepare($sql);
        $params = [':email' => $email];
        if ($excludePersonaId) {
            $params[':id_persona'] = $excludePersonaId;
        }

        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // 5. Validar si el DNI ya existe
    public function dniExiste(string $dni, ?int $excludePersonaId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM persona WHERE dni = :dni";
        if ($excludePersonaId) {
            $sql .= " AND id_persona != :id_persona";
        }

        $stmt = $this->db->prepare($sql);
        $params = [':dni' => $dni];
        if ($excludePersonaId) {
            $params[':id_persona'] = $excludePersonaId;
        }

        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // 6. Registrar Usuario con Transacción (Persona + Usuario)
    public function registrarTransaccion(array $datosPersona, array $datosUsuario): int
    {
        $this->db->beginTransaction();
        try {
            // Insertar en la tabla persona
            $sqlPersona = "INSERT INTO persona (dni, nombre, apellido_P, apellido_M, email) 
                           VALUES (:dni, :nombre, :apellido_P, :apellido_M, :email)";
            $stmtP = $this->db->prepare($sqlPersona);
            $stmtP->execute([
                ':dni' => $datosPersona['dni'],
                ':nombre' => $datosPersona['nombre'],
                ':apellido_P' => $datosPersona['apellido_P'],
                ':apellido_M' => $datosPersona['apellido_M'],
                ':email' => $datosPersona['email']
            ]);

            $idPersona = (int) $this->db->lastInsertId();

            // Insertar en la tabla usuarios
            $sqlUsuario = "INSERT INTO usuarios (id_rol, id_area, id_persona, password, estado) 
                           VALUES (:id_rol, :id_area, :id_persona, :password, :estado)";
            $stmtU = $this->db->prepare($sqlUsuario);
            $stmtU->execute([
                ':id_rol' => $datosUsuario['id_rol'],
                ':id_area' => $datosUsuario['id_area'],
                ':id_persona' => $idPersona,
                ':password' => $datosUsuario['password'],
                ':estado' => $datosUsuario['estado'] ?? 'Activo'
            ]);

            $idUsuario = (int) $this->db->lastInsertId();

            $this->db->commit();
            return $idUsuario;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // 7. Actualizar Usuario con Transacción
    public function actualizarTransaccion(int $idUsuario, int $idPersona, array $datosPersona, array $datosUsuario): bool
    {
        $this->db->beginTransaction();
        try {
            // Actualizar tabla persona
            $sqlPersona = "UPDATE persona 
                           SET dni = :dni, nombre = :nombre, apellido_P = :apellido_P, apellido_M = :apellido_M, email = :email, update_at = NOW() 
                           WHERE id_persona = :id_persona";
            $stmtP = $this->db->prepare($sqlPersona);
            $stmtP->execute([
                ':dni' => $datosPersona['dni'],
                ':nombre' => $datosPersona['nombre'],
                ':apellido_P' => $datosPersona['apellido_P'],
                ':apellido_M' => $datosPersona['apellido_M'],
                ':email' => $datosPersona['email'],
                ':id_persona' => $idPersona
            ]);

            // Actualizar tabla usuarios (Rol y Área)
            $sqlUsuario = "UPDATE usuarios 
                           SET id_rol = :id_rol, id_area = :id_area, update_at = NOW() 
                           WHERE id_usuario = :id_usuario";
            $stmtU = $this->db->prepare($sqlUsuario);
            $stmtU->execute([
                ':id_rol' => $datosUsuario['id_rol'],
                ':id_area' => $datosUsuario['id_area'],
                ':id_usuario' => $idUsuario
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // 8. Cambiar Estado (Activo / Inactivo / Suspendido)
    public function cambiarEstado(int $idUsuario, string $nuevoEstado): bool
    {
        $sql = "UPDATE usuarios SET estado = :estado, update_at = NOW() WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id' => $idUsuario
        ]);
    }
}
=======
}
>>>>>>> parent of 25582e8 (creacion del CRUD incompleto):app/models/usuario.php
=======
}
>>>>>>> parent of 25582e8 (creacion del CRUD incompleto):app/models/usuario.php
=======
}
>>>>>>> parent of 25582e8 (creacion del CRUD incompleto):app/models/usuario.php
