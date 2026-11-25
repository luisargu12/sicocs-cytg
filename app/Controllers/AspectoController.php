<?php

class AspectoController extends Controller
{
    public function api()
    {
        (new AspectoModel())->handle();
    }
}
