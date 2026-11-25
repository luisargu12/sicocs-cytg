<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /login");
    exit;
}
if (!isset($conexion)) {
    $conexion = Database::connection();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keyword" content="No habilitados, contraloria, gobierno, nuevo leon">

    <title><?php echo $titulo ?></title>

    <link rel="shortcut icon" href="https://recursos.digital.nl.gob.mx/favicon.ico" type="image/vnd.microsoft.icon">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-1.13.8/b-2.4.2/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css" type="text/css">
    <link href="https://recursos.digital.nl.gob.mx/assets/libs/bootstrap/css/bootstrap.min.css" id="bootstrap-style"
        rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://recursos.digital.nl.gob.mx/assets/css/master.css" type="text/css">
    <!--external css-->



</head>

<body class="contraloria">

    <nav class="caja_header navbar navbar-expand-lg">
        <div class="container-fluid">
            <div class="area_logo">
                <a class="logo_header" title="logo Nuevo León" href="#"></a>
                <button id="boton_hamburguesa" class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#scroll_menu" aria-controls="scroll_menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="material-icons">menu</span>
                </button>
            </div>


            <div id="scroll_menu" class="collapse navbar-collapse">
                <ul class="navbar-nav navbar-nav-scroll" style="--bs-scroll-height: 100px;">

                </ul>

                <div class="area_sesion">
                    <div class="dropdown">
                        <a class="dropdown-toggle" href="#" id="avatar_sesion" role="button" data-bs-toggle="dropdown" aria-expanded="false">

                            <div class="datos_usuario">
                                <div class="es_vertical">
                                    <span class="datos_bienvenida">¡Saludos!</span>
                                    <span class="datos_nombre"><?php echo $_SESSION["nombre"] ?></span>
                                </div>
                                <span class="avatar">
                                    <?php if (isset($_SESSION["img-user"]) && $_SESSION["img-user"] != ''): ?>
                                        <img id="avatar-usuario" class="img-fluid" src="./assets/img-user/<?php echo htmlspecialchars($_SESSION["img-user"]); ?>" alt="Foto de perfil">
                                    <?php else: ?>
                                        <img id="avatar-usuario" src="https://recursos.digital.nl.gob.mx/assets/img/elementos/usuario.svg" alt="Foto de perfil">
                                    <?php endif; ?>
                                </span>
                            </div>

                        </a>
                        <ul class="dropdown-menu" aria-labelledby="avatar_sesion">
                            <li><a class="dropdown-item" href="#" id="abrir-mi-cuenta">Mi cuenta</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="/funciones/logout">Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>

            </div>
    </nav>

    <div class="modal fade" id="modalMiCuenta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title mb-2">Mi cuenta</h3>
                        <div class="line"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="form-mi-cuenta" enctype="multipart/form-data">
                    <div class="modal-body text-center">
                        <p class="mb-3"><?php echo htmlspecialchars($_SESSION["nombre"]); ?></p>
                        <?php if (isset($_SESSION["img-user"]) && $_SESSION["img-user"] != ''): ?>
                            <img id="preview-mi-cuenta" class="preview-cuenta mb-4" src="./assets/img-user/<?php echo htmlspecialchars($_SESSION["img-user"]); ?>" alt="Foto de perfil">
                        <?php else: ?>
                            <img id="preview-mi-cuenta" class="preview-cuenta mb-4" src="https://recursos.digital.nl.gob.mx/assets/img/elementos/usuario.svg" alt="Foto de perfil">
                        <?php endif; ?>
                        <div class="input-group">
                            <input type="file" class="form-control" id="imagen-mi-cuenta" name="imagen" accept="image/png, image/jpeg" required>
                            <label class="input-group-text" for="imagen-mi-cuenta" title="Subir archivo">
                                <span class="material-icons">cloud_upload</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="boton boton_primario">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <section class="container-fluid">
        <div class="row">
            <aside class="offcanvas offcanvas-start menu_con_iconos show" tabindex="-1" id="menu_vertical"
                data-bs-keyboard="false" data-bs-backdrop="false" data-bs-scroll="true">
                <!-- <div class="offcanvas-header">
            </div> -->
                <div class="offcanvas-body">
                    <ul>
                        <li>
                            <a href="/">
                                <span class="material-icons">space_dashboard</span> Inicio
                            </a>
                        </li>
                        <li>
                            <a href="#" data-bs-toggle="collapse" data-bs-target="#menu_generar" aria-expanded="false">
                                <span class="material-icons">add</span> Generar
                            </a>
                            <ul class="collapse" id="menu_generar">
                                <li><a href="/agregar-comite">Nuevo Comité</a></li>
                                <li><a href="/agregar-seguimiento">Nuevo Seguimiento</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" data-bs-toggle="collapse" data-bs-target="#menu_comites" aria-expanded="false">
                                <span class="material-icons">groups_3</span> Comités
                            </a>
                            <ul class="collapse" id="menu_comites">
                                <li><a href="/comites">Todos los comités</a></li>
                                <li><a href="/mis-comites">Mis comités</a></li>
                                <?php if ($_SESSION['tipo'] === 1 || $_SESSION['tipo'] === 2) { ?>
                                    <li><a href="/por-revisar">Comités por revisar</a></li>
                                <?php } ?>
                            </ul>
                        </li>
                        <li>
                            <a href="/seguimientos">
                                <span class="material-icons">fact_check</span> Seguimientos
                            </a>
                        </li>
                        <?php
                        if ($_SESSION['tipo'] === 1 || $_SESSION['tipo'] === 2) {
                        ?>
                            <li>
                                <a href="#" data-bs-toggle="collapse" data-bs-target="#menu_1" aria-expanded="false">
                                    <span class="material-icons">view_list</span> Catálogos
                                </a>
                                <ul class="collapse" id="menu_1">
                                    <li>
                                        <a href="#" data-bs-toggle="collapse" data-bs-target="#menu_1_1" aria-expanded="false">
                                            <span class="material-icons">location_on</span>Ubicaciones
                                        </a>
                                        <ul class="collapse" id="menu_1_1">
                                            <li><a href="/catalogos?catalogo=municipio">Municipios</a></li>
                                            <li><a href="/catalogos?catalogo=colonia">Colonias</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="#" data-bs-toggle="collapse" data-bs-target="#menu_1_2" aria-expanded="false">
                                            <span class="material-icons">assignment</span>Programas y más
                                        </a>
                                        <ul class="collapse" id="menu_1_2">
                                            <li><a href="/catalogos?catalogo=programa">Programas</a></li>
                                            <li><a href="/catalogos?catalogo=instancia">Instancias Ejecutoras</a></li>
                                            <li><a href="/catalogos?catalogo=ramo">Ramos</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="#" data-bs-toggle="collapse" data-bs-target="#menu_1_3" aria-expanded="false">
                                            <span class="material-icons">dashboard_customize</span>Aspectos específicos
                                        </a>
                                        <ul class="collapse" id="menu_1_3">
                                            <li><a href="/catalogos?catalogo=ambito">Ámbitos</a></li>
                                            <li><a href="/catalogos?catalogo=accion">Tipos de acción</a></li>
                                            <li><a href="/catalogos?catalogo=reunion">Tipo de reunión - comités</a></li>
                                            <li><a href="/catalogos?catalogo=reunion-seguimiento">Tipo de reunión - seguimiento</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="/catalogos?catalogo=material" aria-expanded="false">
                                            <span class="material-icons">shopping_bag</span>Material de Difusión
                                        </a>
                                    </li>
                                </ul>
                            </li>

                        <?php
                        }
                        ?>
                        <li>
                            <a href="/estadisticas">
                                <span class="material-symbols-outlined">insert_chart</span>Estadísticas
                            </a>
                        </li>
                        <?php
                        if ($_SESSION['tipo'] == 1) {
                        ?>
                            <li>
                                <a href="#" data-bs-toggle="collapse" data-bs-target="#menu_2" aria-expanded="false">
                                    <span class="material-icons">settings</span> Configuración
                                </a>
                                <ul class="collapse" id="menu_2">
                                    <li><a href="/usuarios">Usuarios</a></li>
                                    <li><a href="/iniciar-anio">Iniciar año</a></li>
                                </ul>
                            </li>
                        <?php
                        }
                        ?>
                        <li>
                            <a href="/funciones/logout" aria-expanded="false">
                                <span class="material-icons">logout</span> Salir
                            </a>
                        </li>

                    </ul>
                </div>
            </aside>

            <button type="button" class="boton boton_menu" data-bs-toggle="offcanvas" data-bs-target="#menu_vertical"
                role="button">
                <span class="material-icons" id="mas">add</span>
                <span class="material-icons" id="menos">remove</span>
            </button>
            <main>