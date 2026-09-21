<?php

define("DS", DIRECTORY_SEPARATOR);

spl_autoload_register("autoload");


function autoload($class)
{
    $class =__DIR__ . DS . str_replace("\\", DS, $class) . ".php";

    if (file_exists($class)) {
        require_once($class);
    }
}
