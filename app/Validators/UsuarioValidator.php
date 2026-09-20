<?php

namespace App\Validators;

use App\Models\Usuario;

class UsuarioValidator
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Valida los datos para crear o actualizar un usuario.
     * 
     * @param array $datos Datos recibidos del formulario ($_POST)
     * @param int|null $idPersona ID de la persona en caso de edición (para ignorar su propio email/dni)
     * @param bool $esEdicion Define si es una actualización o un nuevo registro
     * @return array Retorna un arreglo de errores. Si está vacío, los datos son válidos.
     */
    public function validar(array $datos, ?int $idPersona = null, bool $esEdicion = false): array
    {
        $errores = [];

        // 1. Limpieza de datos básica
        $dni = trim($datos['dni'] ?? '');
        $nombre = trim($datos['nombre'] ?? '');
        $apellidoP = trim($datos['apellido_P'] ?? '');
        $apellidoM = trim($datos['apellido_M'] ?? '');
        $email = trim($datos['email'] ?? '');
        $idRol = $datos['id_rol'] ?? null;
        $idArea = $datos['id_area'] ?? null;
        $password = $datos['password'] ?? '';

        // 2. Validación de campos obligatorios
        if (empty($dni)) {
            $errores['dni'] = 'El DNI es obligatorio.';
        } elseif (!preg_match('/^[0-9]{8}$/', $dni)) {
            $errores['dni'] = 'El DNI debe contener exactamente 8 dígitos numéricos.';
        } elseif ($this->usuarioModel->dniExiste($dni, $idPersona)) {
            $errores['dni'] = 'El DNI ingresado ya se encuentra registrado.';
        }

        if (empty($nombre)) {
            $errores['nombre'] = 'El nombre es obligatorio.';
        }

        if (empty($apellidoP)) {
            $errores['apellido_P'] = 'El apellido paterno es obligatorio.';
        }

        if (empty($apellidoM)) {
            $errores['apellido_M'] = 'El apellido materno es obligatorio.';
        }

        if (empty($email)) {
            $errores['email'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato del correo electrónico no es válido.';
        } elseif ($this->usuarioModel->emailExiste($email, $idPersona)) {
            $errores['email'] = 'El correo electrónico ya está en uso.';
        }

        if (empty($idRol)) {
            $errores['id_rol'] = 'Debe seleccionar un rol para el usuario.';
        }

        if (empty($idArea)) {
            $errores['id_area'] = 'Debe seleccionar un área asignada.';
        }

        // 3. Validación de contraseña (Obligatoria solo al crear)
        if (!$esEdicion) {
            if (empty($password)) {
                $errores['password'] = 'La contraseña es obligatoria.';
            } elseif (strlen($password) < 6) {
                $errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
            }
        } else {
            // Si es edición y enviaron contraseña, validamos su longitud
            if (!empty($password) && strlen($password) < 6) {
                $errores['password'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            }
        }

        return $errores;
    }
}