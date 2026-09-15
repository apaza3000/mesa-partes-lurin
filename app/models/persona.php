<?php

namespace App\Models;

use App\Config\Conexion;
use PDO;
use PDOException;

class Persona
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Registrar una nueva persona/administrado
    public function registrar(array $datos): bool
    {
        try {
            $sql = "INSERT INTO persona (nombre, apellido_P, apellido_M, email, estado) 
                    VALUES (:nombre, :apellido_P, :apellido_M, :email, 'Activo')";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre'     => $datos['nombre'],
                ':apellido_P' => $datos['apellido_P'],
                ':apellido_M' => $datos['apellido_M'],
                ':email'      => $datos['email']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Buscar persona por su correo electrónico
    public function obtenerPorEmail(string $email): ?array
    {
        try {
            $sql = "SELECT * FROM persona WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $resultado = $stmt->fetch();
            return $resultado ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}