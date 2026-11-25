<?php

class AuthController extends Controller
{
    public function formulario()
    {
        $this->render('auth/login');
    }

    public function entrar()
    {
        (new UsuarioModel())->login();
    }

    public function salir()
    {
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }
}
