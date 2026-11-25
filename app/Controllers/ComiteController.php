<?php

class ComiteController extends Controller
{
    public function todos()
    {
        $this->render('comites/index');
    }

    public function mios()
    {
        $this->render('comites/mios');
    }

    public function porRevisar()
    {
        $this->render('comites/por-revisar');
    }

    public function nuevo()
    {
        $this->render('comites/nuevo');
    }

    public function ver()
    {
        $this->render('comites/ver');
    }

    public function crear()
    {
        (new ComiteModel())->crear();
    }

    public function api()
    {
        (new ComiteModel())->handle();
    }
}
