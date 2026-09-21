<?php

namespace Src\Core;

/**
 * Dotenv - Cargador de variables de entorno
 * 
 * Imita el comportamiento de vlucas/phpdotenv
 * 
 * @package Dotenv
 */
class Dotenv
{
    /**
     * Ruta al directorio donde se encuentra el archivo .env
     * @var string
     */
    private $path;

    /**
     * Nombre del archivo .env
     * @var string
     */
    private $filename;

    /**
     * Variables cargadas
     * @var array
     */
    private $variables = [];
    private $mutable = false;

    /**
     * Constructor privado (usar createImmutable o createMutable)
     * 
     * @param string $path Ruta al directorio
     * @param string $filename Nombre del archivo (por defecto .env)
     */
    private function __construct($path, $filename = '.env')
    {
        $this->path = rtrim($path, '/') . '/';
        $this->filename = $filename;
    }

    /**
     * Crea una instancia inmutable (no permite sobrescribir variables existentes)
     * 
     * @param string $path Ruta al directorio
     * @param string $filename Nombre del archivo
     * @return self
     */
    public static function createImmutable($path, $filename = '.env')
    {
        return new self($path, $filename);
    }

    /**
     * Crea una instancia mutable (permite sobrescribir variables existentes)
     * 
     * @param string $path Ruta al directorio
     * @param string $filename Nombre del archivo
     * @return self
     */
    public static function createMutable($path, $filename = '.env')
    {
        $instance = new self($path, $filename);
        $instance->mutable = true;
        return $instance;
    }

    /**
     * Carga las variables de entorno desde el archivo .env
     * 
     * @return self
     * @throws Exception Si el archivo no existe o no se puede leer
     */
    public function load()
    {
        $filePath = $this->path . $this->filename;

        // Verificar si el archivo existe
        if (!file_exists($filePath)) {
            throw new \Exception("El archivo {$this->filename} no existe en: {$this->path}");
        }

        // Verificar si se puede leer
        if (!is_readable($filePath)) {
            throw new \Exception("No se puede leer el archivo {$this->filename} en: {$this->path}");
        }

        // Leer y procesar el archivo
        $this->loadFile($filePath);

        return $this;
    }

    /**
     * Carga el archivo y procesa su contenido
     * 
     * @param string $filePath Ruta completa al archivo
     */
    private function loadFile($filePath)
    {
        // Leer el archivo línea por línea
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $this->parseLine($line);
        }
    }

    /**
     * Procesa una línea del archivo .env
     * 
     * @param string $line Línea a procesar
     */
    private function parseLine($line)
    {
        // Eliminar espacios en blanco
        $line = trim($line);

        // Ignorar líneas vacías y comentarios
        if (empty($line) || strpos($line, '#') === 0) {
            return;
        }

        // Encontrar el signo igual
        $pos = strpos($line, '=');
        if ($pos === false) {
            return;
        }

        // Extraer clave y valor
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        // Validar clave
        if (empty($key)) {
            return;
        }

        // Verificar si la variable ya existe (para modo inmutable)
        if (!$this->isMutable() && $this->isVariableSet($key)) {
            return; // No sobrescribir en modo inmutable
        }

        // Procesar el valor
        $value = $this->parseValue($value);

        // Guardar y establecer la variable
        $this->setVariable($key, $value);
    }

    /**
     * Procesa el valor de la variable
     * 
     * @param string $value Valor crudo
     * @return mixed Valor procesado
     */
    private function parseValue($value)
    {
        // Si está vacío, retornar string vacío
        if ($value === '') {
            return '';
        }

        // Quitar comillas simples
        if (strpos($value, "'") === 0 && substr($value, -1) === "'") {
            return substr($value, 1, -1);
        }

        // Quitar comillas dobles y procesar escapes
        if (strpos($value, '"') === 0 && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
            $value = str_replace('\\"', '"', $value);
            $value = str_replace("\\'", "'", $value);
            $value = str_replace('\\n', "\n", $value);
            $value = str_replace('\\r', "\r", $value);
            $value = str_replace('\\t', "\t", $value);
            $value = str_replace('\\\\', '\\', $value);
            return $value;
        }

        // Valores especiales (booleanos y null)
        if (strtolower($value) === 'true' || strtolower($value) === '(true)') {
            return true;
        }

        if (strtolower($value) === 'false' || strtolower($value) === '(false)') {
            return false;
        }

        if (strtolower($value) === 'null' || strtolower($value) === '(null)') {
            return null;
        }

        // Números
        if (is_numeric($value)) {
            return $value + 0;
        }

        // Expansión de variables (ej: ${VAR_NAME})
        if (preg_match('/^\${([A-Za-z0-9_]+)}$/', $value, $matches)) {
            return $this->getVariable($matches[1], '');
        }

        return $value;
    }

    /**
     * Establece una variable en los diferentes ámbitos
     * 
     * @param string $key Nombre de la variable
     * @param mixed $value Valor
     */
    private function setVariable($key, $value)
    {
        // Guardar en el array interno
        $this->variables[$key] = $value;

        // Establecer en $_ENV
        $_ENV[$key] = $value;

        // Establecer en $_SERVER
        $_SERVER[$key] = $value;

        // Establecer con putenv()
        if (is_string($value)) {
            putenv("$key=$value");
        } else {
            putenv("$key=" . var_export($value, true));
        }
    }

    /**
     * Obtiene el valor de una variable
     * 
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    private function getVariable($key, $default = null)
    {
        if (isset($this->variables[$key])) {
            return $this->variables[$key];
        }

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    /**
     * Verifica si una variable está establecida
     * 
     * @param string $key Nombre de la variable
     * @return bool
     */
    private function isVariableSet($key)
    {
        return isset($this->variables[$key]) ||
            isset($_ENV[$key]) ||
            isset($_SERVER[$key]) ||
            getenv($key) !== false;
    }

    /**
     * Verifica si la instancia es mutable
     * 
     * @return bool
     */
    private function isMutable()
    {
        return isset($this->mutable) && $this->mutable === true;
    }

    /**
     * Obtiene todas las variables cargadas
     * 
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Sobrecarga para acceder a variables como propiedades
     * 
     * @param string $name Nombre de la variable
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getVariable($name);
    }

    /**
     * Verifica si una variable existe como propiedad
     * 
     * @param string $name Nombre de la variable
     * @return bool
     */
    public function __isset($name)
    {
        return $this->isVariableSet($name);
    }
}
