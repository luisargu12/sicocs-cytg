<?php

class AnioController extends Controller
{
    public function index()
    {
        $this->render('configuracion/anio');
    }

    public function iniciar()
    {
        (new AnioModel())->iniciar();
    }
}
