<?php

namespace Src\Core;

class Router
{
    private static $routes = [];

    public static function get($uri, $action)
    {
        self::addRoute('GET', $uri, $action);
    }

    public static function post($uri, $action)
    {
        self::addRoute('POST', $uri, $action);
    }
    public static function put($uri, $action)
    {
        self::addRoute('PUT', $uri, $action);
    }
    public static function delete($uri, $action)
    {
        self::addRoute('DELETE', $uri, $action);
    }




    private static function addRoute($method, $uri, $action)
    {
        self::$routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action
        ];
    }

    public static function dispatch($requestUri, $requestMethod, $callbackError)
    {
        foreach (self::$routes as $route) {
            // Convertir el patrón de la ruta en una expresión regular
            //$pattern = preg_replace('/\{[^\}]+\}/', '([^\/]+)', $route['uri']);
            $pattern = preg_replace('/\{[^\}]+\}/', '([^/]+)', $route['uri']);
            $pattern = str_replace('/', '\/', $pattern);


            $pattern = '/^' . $pattern . '$/';


            if (preg_match($pattern, $requestUri, $matches) && $route['method'] == $requestMethod) {

                array_shift($matches); // Eliminar la primera coincidencia que es la URI completa
                $action = $route['action'];


                if (is_callable($action)) {
                    return call_user_func_array($action, $matches);
                } elseif (is_array($action) && count($action) == 2) {
                    return self::callControllerAction($action, $matches);
                }
            }
        }
        $callbackError();
    }

    private static function callControllerAction($action, $params)
    {
        [$controller, $method] = $action;
        $controllerInstance = new $controller();
        return call_user_func_array([$controllerInstance, $method], $params);
    }

}
