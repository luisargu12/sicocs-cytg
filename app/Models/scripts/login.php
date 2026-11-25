<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que los campos no estén vacíos
    $u = trim($_POST['user'] ?? '');
    $p = trim($_POST['password'] ?? '');


    if ($u === '' || $p === '') {
        $error = "Debes ingresar usuario y contraseña.";
        header("location: /login?error=$error");
        exit;
    } else {
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = :u LIMIT 1");
        $query->bindParam(":u", $u);
        $query->execute();
        $usuario = $query->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($p, $usuario['password'])) {
            if ($usuario['activo'] == 0) {
                $error = "El usuario esta deshabilitado.";
                header("location: /login?error=$error");
                exit;
            }
            $_SESSION["nombre"] = $usuario["nombre"] . " " . $usuario["apellido_paterno"] . " " . $usuario["apellido_materno"];
            $_SESSION["tipo"] = $usuario["fk_id_tipo_usuario"];
            $_SESSION["img-user"] = $usuario["imagen"];
            $_SESSION["id"] = $usuario["id_usuario"];
            $_SESSION["loggedin"] = true;
            header("location: /");
            exit;
        } else {
            $error = "Error de Autentificación: Usuario o contraseña incorrectos.";
            header("location: /login?error=$error");
        }
    }
}
