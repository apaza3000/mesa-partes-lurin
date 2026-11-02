<?php

namespace App\Models;

use PDO;

class ArchivoAdjunto
{
    public function __construct(private PDO $db) {}

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO archivos_adjuntos
             (id_expediente, id_documento, nombre_original, nombre_sistema, ruta_privada, extension,
              mime_type, tamanio_bytes, hash_sha256, creado_por)
             VALUES (:expediente, :documento, :original, :archivo, :ruta, :extension, :mime, :tamanio,
                     :hash_sha256, :creado_por)"
        );
        $stmt->execute([
            ':expediente' => (int) $datos['id_expediente'],
            ':documento' => (int) $datos['id_documento'],
            ':original' => $datos['nombre_original'],
            ':archivo' => $datos['nombre_archivo'],
            ':ruta' => $datos['ruta_archivo'],
            ':extension' => $datos['extension'] ?: null,
            ':mime' => $datos['mime_type'],
            ':tamanio' => (int) $datos['tamanio'],
            ':hash_sha256' => $datos['hash_sha256'],
            ':creado_por' => $datos['creado_por'],
        ]);
        return (int) $this->db->lastInsertId();
    }
}
