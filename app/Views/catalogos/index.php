<?php
$catalogo = null;
$titulo = null;
if (isset($_GET["catalogo"])) {
    $catalogo = $_GET["catalogo"];
}
switch ($catalogo) {
    case "municipio":
        $titulo = "SICOCS | Municipios";
        break;
    case "colonia":
        $titulo = "SICOCS | Colonias";
        break;
    case "ramos":
        $titulo = "SICOCS | Ramos";
        break;
    case "programa":
        $titulo = "SICOCS | Programas";
        break;
    case "instancia":
        $titulo = "SICOCS | Instancias Ejecutoras";
        break;
    case "ramo":
        $titulo = "SICOCS | Ramos";
        break;
    case "ambito":
        $titulo = "SICOCS | Ambitos";
        break;
    case "accion":
        $titulo = "SICOCS | Tipos de Acción";
        break;
    case "reunion":
        $titulo = "SICOCS | Tipo de reunión - comités";
        break;
    case "reunion-seguimiento":
        $titulo = "SICOCS | Tipo de reunión - seguimiento";
        break;
    case "material":
        $titulo = "SICOCS | Material de Difusión";
        break;
}
require ROOT . '/app/Views/layouts/header.php';





switch ($catalogo) {
    case "municipio":
        require ROOT . '/app/Views/catalogos/municipios.php';
        break;
    case "colonia":
        require ROOT . '/app/Views/catalogos/colonias.php';
        break;
    case "programa":
        require ROOT . '/app/Views/catalogos/programas.php';
        break;
    case "instancia":
        require ROOT . '/app/Views/catalogos/instancia.php';
        break;
    case "ramo":
        require ROOT . '/app/Views/catalogos/ramos.php';
        break;
    case "ambito":
        require ROOT . '/app/Views/catalogos/ambito.php';
        break;
    case "accion":
        require ROOT . '/app/Views/catalogos/tipo_accion.php';
        break;
    case "reunion":
        require ROOT . '/app/Views/catalogos/tipo_reunion.php';
        break;
    case "reunion-seguimiento":
        require ROOT . '/app/Views/catalogos/tipo_reunion_seguimiento.php';
        break;
    case "material":
        require ROOT . '/app/Views/catalogos/material_difusion.php';
        break;
}
