<?php
namespace Src\Core;


class Controller
{
    /**
     * Renderiza una vista y la devuelve como string.
     * Equivalente a view() de Laravel.
     */
    protected function render(string $vista, array $datos = [], ?string $layout = null): string
    {
        $archivo =Path::base('view/pages/') . str_replace('.', '/', $vista) . '.php';

        if (!file_exists($archivo)) {
            throw new \Exception("Vista no encontrada: $vista $archivo");
        }

        // Extraer datos como variables locales
        extract($datos, EXTR_SKIP);

        // Capturar la salida de la vista
        ob_start();
        require $archivo;
        $contenido = ob_get_clean();

        // Si se pidió un layout, lo envolvemos
        if ($layout !== null) {
            $archivoLayout = Path::base('view/') . str_replace('.', '/', $layout) . '.php';

            if (!file_exists($archivoLayout)) {
                throw new \Exception("Layout no encontrado: $layout");
            }

            ob_start();
            require $archivoLayout;   // aquí $contenido está disponible
            return ob_get_clean();
        }

        return $contenido;
    }

    /**
     * Envía la vista directamente al navegador.
     */
    protected function view(string $vista, array $datos = [], ?string $layout = null): void
    {
        echo $this->render($vista, $datos, $layout);
    }

    /**
     * Devuelve JSON (útil para APIs o AJAX).
     */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirige a otra URL.
     */
    protected function redirect(string $url, int $status = 302): void
    {
        header("Location: $url", true, $status);
        exit;
    }

    /**
     * Lee datos de $_POST / $_GET de forma segura.
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Escapa HTML para usar en vistas.
     */
    protected function e(?string $valor): string
    {
        return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
    }
}