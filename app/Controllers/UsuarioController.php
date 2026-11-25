<?php

class UsuarioController extends Controller
{
    public function index()
    {
        $this->render('usuarios/index');
    }

    public function api()
    {
        (new UsuarioModel())->handle();
    }
}
