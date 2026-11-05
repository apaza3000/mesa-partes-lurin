<?php

namespace App\Services;

use App\Models\ArchivoAdjunto;
use App\Models\Documento;
use App\Models\Expediente;
use PDO;
use RuntimeException;
use Src\Core\Conexion;
use Src\Core\Session;
use Throwable;

class ExpedienteService
{
    private PDO $db;
    private ?int $usuarioActual = null;
    private bool $usuarioResuelto = false;

    public function __construct(?PDO $db = null)
    {
        $conexion = $db ?? Conexion::getConexion();
        if ($conexion === null) {
            throw new RuntimeException('No se pudo establecer la conexión a la base de datos.');
        }

        $this->db = $conexion;
    }

    public function transBegin(): bool
    {
        return $this->db->beginTransaction();
    }

    public function transCommit(): bool
    {
        return $this->db->commit();
    }

    public function transRollback(): bool
    {
        return $this->db->rollBack();
    }

    public function registrar(array $datos, array $archivos): array
    {
        $creados = [];
        $this->db->beginTransaction();

        try {
            if (!empty($datos['id_remitente'])) {
                $idRemitente = (int) $datos['id_remitente'];
            } else {
                $stmt = $this->db->prepare(
                    'SELECT id_persona FROM personas
                     WHERE tipo_documento = :tipo AND numero_documento = :numero LIMIT 1'
                );
                $stmt->execute([
                    ':tipo' => $datos['tipo_documento'],
                    ':numero' => $datos['numero_documento'],
                ]);
                $idRemitente = $stmt->fetchColumn();
                if ($idRemitente) {
                    $idRemitente = (int) $idRemitente;
                    $this->actualizarPersona($idRemitente, $datos);
                } else {
                    $idRemitente = $this->crearPersona($datos);
                }
            }

            $codigo = $this->generarCodigo();
            $expediente = new Expediente($this->db);
            $idExpediente = $expediente->crear([
                'codigo_expediente' => $codigo,
                'id_remitente' => $idRemitente,
                'asunto' => $datos['asunto'],
                'prioridad' => $datos['prioridad'],
                'canal_ingreso' => $datos['canal_ingreso'] ?? 'Virtual',
            ]);
            $codigoGenerado = $this->db->prepare(
                'SELECT codigo_expediente FROM expedientes WHERE id_expediente = :id'
            );
            $codigoGenerado->execute([':id' => $idExpediente]);
            $codigoGenerado = $codigoGenerado->fetchColumn();
            if (is_string($codigoGenerado) && $codigoGenerado !== '') {
                $codigo = $codigoGenerado;
            }

            $documentoModel = new Documento($this->db);
            $idDocumento = $documentoModel->crear([
                'id_expediente' => $idExpediente,
                'id_tipo_doc' => $datos['doc_tipo'],
                'numero_documento' => $datos['doc_numero'] ?: null,
                'asunto' => $datos['doc_asunto'],
                'folios' => $datos['doc_folios'],
                'fecha_documento' => $datos['doc_fecha'] ?: null,
                'creado_por' => $this->usuarioActual(),
            ]);

            foreach ($this->normalizarArchivos($archivos) as $archivo) {
                if (!is_uploaded_file($archivo['tmp_name'])) {
                    throw new RuntimeException('El archivo recibido no es una carga válida.');
                }
                if ($archivo['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('No se pudo cargar uno de los archivos.');
                }
                if ($archivo['size'] > 10 * 1024 * 1024) {
                    throw new RuntimeException('Cada archivo puede pesar como máximo 10 MB.');
                }
                $permitidos = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $permitidos, true)) {
                    throw new RuntimeException('Uno de los archivos tiene un formato no permitido.');
                }
                if (!$this->validarTipoArchivo($archivo['tmp_name'], $extension)) {
                    throw new RuntimeException('El contenido de uno de los archivos no coincide con su formato.');
                }

                $ruta = $this->guardarArchivo($idExpediente, $archivo);
                $creados[] = $ruta;
                $hash = hash_file('sha256', $ruta);
                if ($hash === false) {
                    throw new RuntimeException('No se pudo calcular el hash del archivo.');
                }

                $archivoModel = new ArchivoAdjunto($this->db);
                $archivoModel->crear([
                    'id_expediente' => $idExpediente,
                    'id_documento' => $idDocumento,
                    'nombre_original' => basename($archivo['name']),
                    'nombre_archivo' => basename($ruta),
                    'ruta_archivo' => $ruta,
                    'extension' => strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION)),
                    'mime_type' => $archivo['type'] ?: 'application/octet-stream',
                    'tamanio' => $archivo['size'],
                    'hash_archivo' => $hash,
                    'hash_sha256' => $hash,
                    'creado_por' => $this->usuarioActual(),
                ]);
            }

            $stmt = $this->db->prepare(
                "INSERT INTO historial_expediente
                 (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario)
                 VALUES (:id, NULL, 'Registrado', :usuario, 'Expediente registrado en Mesa de Partes')"
            );
            $stmt->execute([
                ':id' => $idExpediente,
                ':usuario' => $this->usuarioActual(),
            ]);

            $stmt = $this->db->prepare(
                "INSERT INTO auditoria
                 (id_usuario, modulo, accion, tabla_afectada, id_registro, descripcion, ip)
                 VALUES (:usuario, 'Expedientes', 'REGISTRAR', 'expedientes', :id,
                         :descripcion, :ip)"
            );
            $stmt->execute([
                ':usuario' => $this->usuarioActual(),
                ':id' => $idExpediente,
                ':descripcion' => 'Registro del expediente ' . $codigo,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $this->db->commit();
            $constancia = $this->generarConstancia($idExpediente, $codigo, $datos);
            $correo = $this->enviarCorreoExpediente($datos, $codigo, $constancia);
            return [
                'ok' => true,
                'id_expediente' => $idExpediente,
                'id_documento' => $idDocumento,
                'codigo' => $codigo,
                'constancia' => $constancia,
                'correo_enviado' => $correo,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            foreach ($creados as $ruta) {
                if (is_file($ruta)) {
                    @unlink($ruta);
                }
            }
            return ['ok' => false, 'mensaje' => $e->getMessage()];
        }
    }

    private function generarCodigo(): string
    {
        $anio = (int) date('Y');
        $stmt = $this->db->prepare(
            "SELECT id_numerador, ultimo_numero FROM numeradores
             WHERE tipo = 'EXPEDIENTE' AND anio = :anio FOR UPDATE"
        );
        $stmt->execute([':anio' => $anio]);
        $numerador = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($numerador) {
            $ultimo = (int) $numerador['ultimo_numero'] + 1;
            $update = $this->db->prepare(
                'UPDATE numeradores SET ultimo_numero = :ultimo WHERE id_numerador = :id'
            );
            $update->execute([':ultimo' => $ultimo, ':id' => $numerador['id_numerador']]);
        } else {
            $ultimo = 1;
            $insert = $this->db->prepare(
                "INSERT INTO numeradores (tipo, anio, ultimo_numero) VALUES ('EXPEDIENTE', :anio, 1)"
            );
            $insert->execute([':anio' => $anio]);
        }

        return sprintf('EXP-%d-%06d', $anio, $ultimo);
    }

    private function crearPersona(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO personas
             (tipo_persona, tipo_documento, numero_documento, nombres, apellido_paterno, apellido_materno,
              razon_social, email, telefono, direccion, estado)
             VALUES ('Natural', :tipo_documento, :numero_documento, :nombres, :apellido_paterno,
                     :apellido_materno, NULL, :email, :telefono, :direccion, 'Activo')"
        );
        $stmt->execute([
            ':tipo_documento' => $datos['tipo_documento'],
            ':numero_documento' => $datos['numero_documento'],
            ':nombres' => $datos['nombres'],
            ':apellido_paterno' => $datos['apellido_paterno'],
            ':apellido_materno' => $datos['apellido_materno'] ?? null,
            ':email' => $datos['email'] ?: null,
            ':telefono' => $datos['telefono'] ?: null,
            ':direccion' => $datos['direccion'] ?: null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function actualizarPersona(int $id, array $datos): void
    {
        $stmt = $this->db->prepare(
            "UPDATE personas SET nombres = :nombres, apellido_paterno = :apaterno,
             apellido_materno = :amaterno, email = COALESCE(NULLIF(:email, ''), email),
             telefono = COALESCE(NULLIF(:telefono, ''), telefono),
             direccion = COALESCE(NULLIF(:direccion, ''), direccion), actualizado_en = CURRENT_TIMESTAMP
             WHERE id_persona = :id"
        );
        $stmt->execute([
            ':nombres' => $datos['nombres'],
            ':apaterno' => $datos['apellido_paterno'],
            ':amaterno' => $datos['apellido_materno'] ?? null,
            ':email' => $datos['email'] ?? '',
            ':telefono' => $datos['telefono'] ?? '',
            ':direccion' => $datos['direccion'] ?? '',
            ':id' => $id,
        ]);
    }

    private function normalizarArchivos(array $files): array
    {
        if (!isset($files['name']) || !is_array($files['name'])) {
            return [];
        }
        $normalized = [];
        foreach ($files['name'] as $i => $name) {
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => (int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) ($files['size'][$i] ?? 0),
            ];
        }
        return $normalized;
    }

    private function guardarArchivo(int $idExpediente, array $archivo): string
    {
        $directorio = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage'
            . DIRECTORY_SEPARATOR . 'expedientes' . DIRECTORY_SEPARATOR . $idExpediente;
        if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo crear el directorio privado de archivos.');
        }
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $nombre = bin2hex(random_bytes(16)) . ($extension !== '' ? '.' . $extension : '');
        $ruta = $directorio . DIRECTORY_SEPARATOR . $nombre;
        if (!move_uploaded_file($archivo['tmp_name'], $ruta)) {
            throw new RuntimeException('No se pudo almacenar uno de los archivos.');
        }
        return $ruta;
    }

    private function validarTipoArchivo(string $ruta, string $extension): bool
    {
        if (!is_file($ruta)) {
            return false;
        }

        if (class_exists('finfo')) {
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($ruta);
            $permitidos = [
                'pdf' => ['application/pdf'],
                'jpg' => ['image/jpeg'],
                'jpeg' => ['image/jpeg'],
                'png' => ['image/png'],
                'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2-corrupt'],
                'docx' => [
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/zip',
                ],
            ];

            return isset($permitidos[$extension]) && in_array($mime, $permitidos[$extension], true);
        }

        $archivo = fopen($ruta, 'rb');
        if ($archivo === false) {
            return false;
        }
        $cabecera = fread($archivo, 8);
        fclose($archivo);

        if ($cabecera === false) {
            return false;
        }

        if ($extension === 'pdf') {
            return strncmp($cabecera, '%PDF-', 5) === 0;
        }
        if ($extension === 'jpg' || $extension === 'jpeg') {
            return bin2hex(substr($cabecera, 0, 3)) === 'ffd8ff';
        }
        if ($extension === 'png') {
            return bin2hex(substr($cabecera, 0, 8)) === '89504e470d0a1a0a';
        }
        if ($extension === 'doc') {
            return bin2hex(substr($cabecera, 0, 8)) === 'd0cf11e0a1b11ae1';
        }
        if ($extension === 'docx') {
            return bin2hex(substr($cabecera, 0, 4)) === '504b0304';
        }

        return false;
    }

    private function usuarioActual(): ?int
    {
        if ($this->usuarioResuelto) {
            return $this->usuarioActual;
        }

        $usuario = Session::user();
        $idUsuario = (int) (
            Session::get('usuario_id')
            ?? $usuario['id_usuario']
            ?? 0
        );

        if ($idUsuario > 0) {
            $stmt = $this->db->prepare(
                'SELECT 1 FROM usuarios WHERE id_usuario = :id LIMIT 1'
            );
            $stmt->execute([':id' => $idUsuario]);
            if (!$stmt->fetchColumn()) {
                throw new RuntimeException('La sesión contiene un usuario que ya no existe.');
            }

            $this->usuarioActual = $idUsuario;
        } else {
            $this->usuarioActual = null;
        }

        $this->usuarioResuelto = true;
        return $this->usuarioActual;
    }

    private function generarConstancia(int $idExpediente, string $codigo, array $datos): ?string
    {
        $directorio = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage'
            . DIRECTORY_SEPARATOR . 'expedientes' . DIRECTORY_SEPARATOR . $idExpediente;
        if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
            return null;
        }
        $ruta = $directorio . DIRECTORY_SEPARATOR . 'constancia-' . $codigo . '.html';
        $nombre = trim(($datos['nombres'] ?? '') . ' ' . ($datos['apellido_paterno'] ?? '') . ' ' . ($datos['apellido_materno'] ?? ''));
        $contenido = '<!doctype html><meta charset="utf-8"><h1>Constancia de registro</h1>'
            . '<p><strong>Código:</strong> ' . htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Remitente:</strong> ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Asunto:</strong> ' . htmlspecialchars($datos['asunto'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Fecha:</strong> ' . date('d/m/Y H:i:s') . '</p>';
        return @file_put_contents($ruta, $contenido) !== false ? $ruta : null;
    }

    private function enviarCorreoExpediente(array $datos, string $codigo, ?string $constancia): bool
    {
        $correo = trim((string) ($datos['email'] ?? ''));
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $remitente = env('MAIL_FROM', 'no-reply@mesadepartes.local');
        $headers = 'MIME-Version: 1.0' . "\r\n"
            . 'Content-type: text/html; charset=UTF-8' . "\r\n"
            . 'From: Mesa de Partes Virtual <' . $remitente . '>';
        $mensaje = '<p>Su expediente fue registrado con el código <strong>' . htmlspecialchars($codigo) . '</strong>.</p>';
        if ($constancia !== null) {
            $mensaje .= '<p>La constancia se encuentra disponible en Mesa de Partes.</p>';
        }
        $host = trim((string) env('MAIL_HOST', ''));
        if ($host !== '') {
            return $this->enviarSmtp($host, (int) env('MAIL_PORT', 587), (string) env('MAIL_USERNAME', ''),
                (string) env('MAIL_PASSWORD', ''), $remitente, $correo, $codigo, $mensaje);
        }
        return @mail($correo, 'Registro de expediente ' . $codigo, $mensaje, $headers);
    }

    private function enviarSmtp(string $host, int $port, string $username, string $password,
        string $from, string $to, string $codigo, string $mensaje): bool
    {
        $conexion = $port === 465 ? 'ssl://' . $host : $host;
        $socket = @fsockopen($conexion, $port, $errno, $error, 10);
        if (!$socket) {
            return false;
        }

        $leer = function () use ($socket): string {
            $respuesta = '';
            while ($linea = fgets($socket, 515)) {
                $respuesta .= $linea;
                if (strlen($linea) < 4 || $linea[3] !== '-') {
                    break;
                }
            }
            return $respuesta;
        };
        $enviar = function (string $comando) use ($socket, $leer): string {
            fwrite($socket, $comando . "\r\n");
            return $leer();
        };

        try {
            $leer();
            $capabilities = $enviar('EHLO localhost');
            if ($port === 587 && stripos($capabilities, 'STARTTLS') !== false) {
                $respuesta = $enviar('STARTTLS');
                if ((int) substr(trim($respuesta), 0, 3) !== 220
                    || !stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return false;
                }
                $enviar('EHLO localhost');
            }
            if ($username !== '' || $password !== '') {
                $enviar('AUTH LOGIN');
                $enviar(base64_encode($username));
                $enviar(base64_encode($password));
            }
            $enviar('MAIL FROM:<' . $from . '>');
            $enviar('RCPT TO:<' . $to . '>');
            $enviar('DATA');
            $asunto = 'Registro de expediente ' . $codigo;
            $contenido = 'From: Mesa de Partes Virtual <' . $from . ">\r\n"
                . 'To: <' . $to . ">\r\n"
                . "Subject: {$asunto}\r\n"
                . "MIME-Version: 1.0\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
                . $mensaje . "\r\n.\r\n";
            fwrite($socket, $contenido);
            $leer();
            $enviar('QUIT');
            return true;
        } finally {
            fclose($socket);
        }
    }
}
