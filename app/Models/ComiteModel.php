<?php

class ComiteModel
{
    public function handle()
    {
        $conexion = Database::connection();
        require __DIR__ . '/scripts/comites_func.php';
    }

    public function crear()
    {
        $conexion = Database::connection();
        require __DIR__ . '/scripts/genera_comite.php';
    }
}
