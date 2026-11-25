<?php

class EstadisticaModel
{
    public function handle()
    {
        $conexion = Database::connection();
        require __DIR__ . '/scripts/estadisticas_func.php';
    }
}
