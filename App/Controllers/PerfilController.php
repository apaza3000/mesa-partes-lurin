<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Support\Csrf;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

class PerfilController extends Controller
{
    public function index(): void
    {
        if (!Session::isAuthenticated()) {
            $this->redirect('/login');
            return;
        }
        $idUsuario = (int) Session::get('usuario_id');
        $usuario = (new Usuario())->obtenerAllPorId($idUsuario);
        $this->view('perfil.index', [
            'titulo' => 'Mi perfil',
            'usuario' => $usuario,
            'csrf_token' => Csrf::token(),
            'flash' => Flash::pull(),
        ], 'app');
    }

    public function actualizar(): void
    {
        if (!Session::isAuthenticated()) {
            $this->redirect('/login');
            return;
        }
        $request = new Request();
        $datos = $request->all();

        $idUsuario = (int) Session::get('usuario_id');
        $passwordActual = (string) ($datos['password_actual'] ?? '');
        $passwordNueva = (string) ($datos['password_nueva'] ?? '');
        $passwordConf = (string) ($datos['password_confirm'] ?? '');

        // Verificar contraseña actual.
        $u = (new Usuario())->obtenerPorId($idUsuario);
        // obtener password hash
        $db = \Src\Core\Conexion::getConexion();
        $stmt = $db->prepare("SELECT password FROM usuarios WHERE id_usuario = :id");
        $stmt->execute([':id' => $idUsuario]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($passwordActual, (string)$hash)) {
            Flash::error('La contraseña actual es incorrecta.');
            $this->redirect('/perfil');
            return;
        }
        if ($passwordNueva !== $passwordConf) {
            Flash::error('Las contraseñas nuevas no coinciden.');
            $this->redirect('/perfil');
            return;
        }
        // Política SEC-02
        if (strlen($passwordNueva) < 8 || !preg_match('/[A-Z]/', $passwordNueva) || !preg_match('/[0-9]/', $passwordNueva) || !preg_match('/[^A-Za-z0-9]/', $passwordNueva)) {
            Flash::error('La nueva contraseña no cumple la política (mín. 8, mayúscula, número y carácter especial).');
            $this->redirect('/perfil');
            return;
        }
        $stmt = $db->prepare("UPDATE usuarios SET password = :p, actualizado_en = NOW() WHERE id_usuario = :id");
        $stmt->execute([':p' => password_hash($passwordNueva, PASSWORD_DEFAULT), ':id' => $idUsuario]);
        Flash::exito('Contraseña actualizada correctamente.');
        $this->redirect('/perfil');
    }
}
