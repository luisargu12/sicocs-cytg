<?php

class ArchivoController extends Controller
{
    public function api()
    {
        (new ArchivoModel())->handle();
    }
}
