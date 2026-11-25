<?php

class CatalogoController extends Controller
{
    public function index()
    {
        $this->render('catalogos/index');
    }

    public function api()
    {
        (new CatalogoModel())->handle();
    }
}
