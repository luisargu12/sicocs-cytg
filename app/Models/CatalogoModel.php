<?php

class CatalogoModel
{
    public function handle()
    {
        $conexion = Database::connection();
        require __DIR__ . '/scripts/catalogos_func.php';
    }
}
