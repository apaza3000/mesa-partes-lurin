<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private array $config;

    public function __construct()
    {
        $this->config = require \Src\Core\Path::base('config') . DIRECTORY_SEPARATOR . 'mail.php';
    }

    /**
     * Envía un correo HTML con adjuntos opcionales.
     * Devuelve true si se envió (o se simuló en log), false si falló.
     */
    public function enviar(string $para, string $asunto, string $html, array $adjuntos = [], ?string $cc = null, $imagenes = []): bool
    {
        $para = trim($para);
        if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
            error_log('MailService: destinatario inválido: ' . $para);
            return false;
        }

        // Modo simulación.
        if (empty($this->config['habilitado'])) {
            $log = "=== CORREO SIMULADO ===\nPara: $para\nCC: " . ($cc ?? '') . "\nAsunto: $asunto\nAdjuntos: " . count($adjuntos) . "\n" . $html . "\n========================\n";
            error_log($log);
            $dir = \Src\Core\Path::base('storage') . DIRECTORY_SEPARATOR . 'logs';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents($dir . DIRECTORY_SEPARATOR . 'mail-simulado.log', $log, FILE_APPEND);
            return true;
        }

        // Validar credenciales mínimas.
        foreach (['host', 'username', 'password', 'from_address'] as $k) {
            if (empty($this->config[$k])) {
                error_log('MailService: falta configuración ' . $k);
                return false;
            }
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = $this->config['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($this->config['from_address'], $this->config['from_name']);
            $mail->addAddress($para);
            if ($cc && filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                $mail->addCC($cc);
            }

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $html;
            $mail->AltBody = strip_tags($html);

            // Imágenes incrustadas desde Base64 convertido a binario
            foreach ($imagenes as $imagen) {

                $mail->addStringEmbeddedImage(
                    $imagen['contenido'],
                    $imagen['cid'],
                    $imagen['nombre'],
                    'base64',
                    $imagen['mime']
                );
            }

            foreach ($adjuntos as $adj) {
                if (isset($adj['contenido']) && isset($adj['nombre'])) {
                    $mail->addStringAttachment($adj['contenido'], $adj['nombre'], 'base64', $adj['mime'] ?? 'application/pdf');
                } elseif (isset($adj['ruta']) && is_file($adj['ruta'])) {
                    $mail->addAttachment($adj['ruta'], $adj['nombre'] ?? basename($adj['ruta']));
                }
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('MailService error: ' . $e->getMessage());
            $dir = \Src\Core\Path::base('storage') . DIRECTORY_SEPARATOR . 'logs';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents($dir . DIRECTORY_SEPARATOR . 'mail-errors.log', date('c') . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }
}
