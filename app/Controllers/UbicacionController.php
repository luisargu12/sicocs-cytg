<?php

class UbicacionController extends Controller
{
    public function api()
    {
        (new UbicacionModel())->handle();
    }
}
