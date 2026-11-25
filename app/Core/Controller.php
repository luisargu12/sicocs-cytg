<?php

class Controller
{
    protected function render($view)
    {
        require ROOT . '/app/Views/' . $view . '.php';
    }
}
