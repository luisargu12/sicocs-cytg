<?php

require __DIR__ . '/app/iniciar.php';

$router = require __DIR__ . '/app/routes.php';
$router->dispatch();
