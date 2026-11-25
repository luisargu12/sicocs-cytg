<?php

class SeguimientoController extends Controller
{
    public function nuevo()
    {
        $this->render('seguimientos/nuevo');
    }

    public function todos()
    {
        $this->render('seguimientos/index');
    }

    public function ver()
    {
        $this->render('seguimientos/ver');
    }

    public function crear()
    {
        (new SeguimientoModel())->crear();
    }

    public function api()
    {
        (new SeguimientoModel())->handle();
    }
}
