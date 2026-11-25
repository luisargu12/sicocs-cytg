<?php

$router = new Router();

$router->any('', [InicioController::class, 'index']);
$router->any('index', [InicioController::class, 'index']);

$router->any('login', [AuthController::class, 'formulario']);
$router->any('funciones/login', [AuthController::class, 'entrar']);
$router->any('funciones/logout', [AuthController::class, 'salir']);

$router->any('comites', [ComiteController::class, 'todos']);
$router->any('mis-comites', [ComiteController::class, 'mios']);
$router->any('por-revisar', [ComiteController::class, 'porRevisar']);
$router->any('agregar-comite', [ComiteController::class, 'nuevo']);
$router->any('comite', [ComiteController::class, 'ver']);
$router->any('funciones/genera_comite', [ComiteController::class, 'crear']);
$router->any('funciones/comites_func', [ComiteController::class, 'api']);

$router->any('catalogos', [CatalogoController::class, 'index']);
$router->any('funciones/catalogos_func', [CatalogoController::class, 'api']);
$router->any('funciones/aspectos_func', [AspectoController::class, 'api']);
$router->any('funciones/ubicaciones_func', [UbicacionController::class, 'api']);

$router->any('usuarios', [UsuarioController::class, 'index']);
$router->any('iniciar-anio', [AnioController::class, 'index']);
$router->any('funciones/iniciar_anio', [AnioController::class, 'iniciar']);
$router->any('funciones/usuarios_func', [UsuarioController::class, 'api']);

$router->any('estadisticas', [EstadisticaController::class, 'index']);
$router->any('funciones/estadisticas_func', [EstadisticaController::class, 'api']);
$router->any('funciones/uploads_func', [ArchivoController::class, 'api']);

$router->any('agregar-seguimiento', [SeguimientoController::class, 'nuevo']);
$router->any('seguimientos', [SeguimientoController::class, 'todos']);
$router->any('seguimiento', [SeguimientoController::class, 'ver']);
$router->any('funciones/genera_seguimiento', [SeguimientoController::class, 'crear']);
$router->any('funciones/seguimiento_func', [SeguimientoController::class, 'api']);

return $router;
