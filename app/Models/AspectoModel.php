<?php

class AspectoModel
{
    public function handle()
    {
        $conexion = Database::connection();
        require __DIR__ . '/scripts/aspectos_func.php';
    }
}
