<?php

require_once __DIR__ . '/Conexion.php';

class Persona
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function registrar(array $datos): bool
    {
        try {
            $sql = "INSERT INTO persona (nombre, apellido_p, apellido_m, email, estado)
                    VALUES (:nombre, :apellido_p, :apellido_m, :email, :estado)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':apellido_p' => $datos['apellido_p'],
                ':apellido_m' => $datos['apellido_m'],
                ':email' => $datos['email'],
                ':estado' => $datos['estado'] ?? 'Activo'
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerPorEmail(string $email): ?array
    {
        try {
            $sql = "SELECT * FROM persona WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}