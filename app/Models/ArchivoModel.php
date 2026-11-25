<?php

class ArchivoModel
{
    public function handle()
    {
        $conexion = Database::connection();
        require __DIR__ . '/scripts/uploads_func.php';
    }
}
