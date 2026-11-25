<?php

class EstadisticaController extends Controller
{
    public function index()
    {
        $this->render('estadisticas/index');
    }

    public function api()
    {
        (new EstadisticaModel())->handle();
    }
}
