<?php

class UsuarioModel
{
    public function handle()
    {
        $conexion = Database::connection();
        require __DIR__ . '/scripts/usuarios_func.php';
    }

    public function login()
    {
        $conexion = Database::connection();
        require __DIR__ . '/scripts/login.php';
    }
}
