<?php
session_start();

$autoload = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

$envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';
if (class_exists('Dotenv\Dotenv') && is_file($envFile)) {
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
} elseif (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

$_SESSION["login"] = true;
if ($_SESSION["login"]) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "plantilla.php";
} else {
    require_once __DIR__ . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR . "login.php";
}
