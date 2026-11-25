<?php

define('ROOT', dirname(__DIR__));

spl_autoload_register(function ($class) {
    $directories = ['app/Core', 'app/Controllers', 'app/Models'];
    foreach ($directories as $directory) {
        $file = ROOT . '/' . $directory . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
