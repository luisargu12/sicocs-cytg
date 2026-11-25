<?php

class UbicacionModel
{
    public function handle()
    {
        $conexion = Database::connection();
        require __DIR__ . '/scripts/ubicaciones_func.php';
    }
}
