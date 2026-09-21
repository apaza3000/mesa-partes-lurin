<?php
namespace Src\Core;

class Request {
    protected $queryParams;
    protected $postParams;
    protected $jsonParams;
    protected $method;
    protected $headers;
    protected $files;

    public function __construct()
    {
        $this->queryParams = $_GET;
        $this->postParams = $_POST;
        $this->jsonParams = $this->getJsonParams();
        $this->method = $this->getRequestMethod();
        $this->headers = $this->getRequestHeaders();
        $this->files = $_FILES;
    }

    // Obtener un parámetro GET
    public function get($key, $default = null)
    {
        return $this->queryParams[$key] ?? $default;
    }

    // Obtener un parámetro POST
    public function post($key, $default = null)
    {
        return $this->postParams[$key] ?? $default;
    }

    // Obtener un parámetro de cualquier fuente (GET, POST, JSON)
    public function input($key, $default = null)
    {
        return $this->queryParams[$key] ?? $this->postParams[$key] ?? $this->jsonParams[$key] ?? $default;
    }

    // Obtener todos los parámetros de la solicitud
    public function all()
    {
        return array_merge($this->queryParams, $this->postParams, $this->jsonParams);
    }

    // Obtener parámetros JSON
    protected function getJsonParams()
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    // Obtener el método HTTP de la solicitud
    public function method()
    {
        return $this->method;
    }

    // Verificar si el método es GET
    public function isGet()
    {
        return $this->method === 'GET';
    }

    // Verificar si el método es POST
    public function isPost()
    {
        return $this->method === 'POST';
    }

    // Verificar si el método es PUT
    public function isPut()
    {
        return $this->method === 'PUT';
    }

    // Verificar si el método es DELETE
    public function isDelete()
    {
        return $this->method === 'DELETE';
    }

    // Obtener todos los encabezados de la solicitud
    public function getHeaders()
    {
        return $this->headers;
    }

    // Obtener un encabezado específico
    public function getHeader($key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    // Obtener los archivos subidos
    public function files($key = null)
    {
        if ($key) {
            return $this->files[$key] ?? null;
        }
        return $this->files;
    }

    // Obtener información de un archivo específico
    public function file($key)
    {
        return $this->files[$key] ?? null;
    }

    // Guardar un archivo subido
    public function saveFile($key, $destination)
    {
        if (isset($this->files[$key])) {
            $file = $this->files[$key];
            move_uploaded_file($file['tmp_name'], $destination);
            return true;
        }
        return false;
    }

    // Obtener el método de la solicitud
    protected function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    // Obtener los encabezados de la solicitud
    protected function getRequestHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        // Alternativa manual si getallheaders() no está disponible
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('HTTP_', '', $key);
                $header = str_replace('_', '-', strtolower($header));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    // Obtener el token Bearer del encabezado Authorization
    public function bearerToken()
    {
        $authHeader = $this->getHeader('Authorization');

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
