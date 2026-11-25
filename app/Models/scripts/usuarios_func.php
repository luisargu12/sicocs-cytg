<?php


header('Content-Type: application/json');

// $conexion->exec("SET NAMES utf8mb4");

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// LISTAR USUARIOS
if ($action === 'listar') {
    $stmt = $conexion->query("SELECT id_usuario, nombre, apellido_paterno, apellido_materno, usuario, activo, imagen, fk_id_tipo_usuario FROM usuarios");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $icono = ($row['activo'] == 1) ? 'check_circle_outline' : 'highlight_off';
        $color = ($row['activo'] == 1) ? 'btn-success' : 'btn-danger';
        $data[] = [
            $row['id_usuario'],
            '<img src="./assets/img-user/' . htmlspecialchars($row['imagen']) . '" alt="Foto" style="width:50px;height:50px;object-fit:cover;" />',
            htmlspecialchars($row['nombre'] . " " . $row['apellido_paterno'] . " " . $row['apellido_materno']),
            htmlspecialchars($row['usuario']),
            '<button class="btn boton_terciario btn-sm editar-usuario me-3" 
                data-id="' . $row['id_usuario'] . '" 
                data-user="' . htmlspecialchars($row['usuario']) . '" 
                data-nombre="' . htmlspecialchars($row['nombre']) . '" 
                data-apellido_paterno="' . htmlspecialchars($row['apellido_paterno']) . '" 
                data-apellido_materno="' . htmlspecialchars($row['apellido_materno']) . '"
                data-imagen="' . htmlspecialchars($row['imagen']) . '"
                data-tipo_usuario="' . $row['fk_id_tipo_usuario'] . '">
                <span class="material-icons">edit</span>
            </button>
            <button class="btn ' . $color . ' btn-sm activar-usuario" 
                data-id="' . $row['id_usuario'] . '" 
                data-act="' . $row['activo'] . '">
                <span class="material-icons">' . $icono . '</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR USUARIO
if ($action === 'agregar') {
    // Recoge y limpia los datos del formulario
    $nombre          = trim($_POST['nombre'] ?? '');
    $apellido_pat    = trim($_POST['apellido_pat'] ?? '');
    $apellido_mat    = trim($_POST['apellido_mat'] ?? '');
    $tipo_usuario    = trim($_POST['tipo_usuario'] ?? '');
    $username        = trim($_POST['username'] ?? '');
    $contrasena      = $_POST['contrasena'] ?? '';

    // Validación básica
    if ($nombre === '' || $tipo_usuario === '' || $username === '' || $contrasena === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Procesar imagen si se subió
    $rutaImagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $directorioDestino = ROOT . '/assets/img-user/';
        $nombreArchivo = $username . '.' . pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $rutaCompleta = $directorioDestino . $nombreArchivo;

        // Validar tipo de archivo (solo imágenes)
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['imagen']['type'], $tiposPermitidos)) {
            echo json_encode(['status' => 'error', 'message' => 'El archivo debe ser una imagen JPG, PNG, GIF o WEBP']);
            exit;
        }

        // Mover el archivo
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
            echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen']);
            exit;
        }

        // Ruta relativa para guardar en la BD
        $rutaImagen =  $nombreArchivo;
    }

    // Hashea la contraseña antes de guardar
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar en la base de datos
    $sql = "INSERT INTO usuarios 
                (nombre, apellido_paterno, apellido_materno, usuario, password, fk_id_tipo_usuario, imagen, activo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $nombre,
        $apellido_pat,
        $apellido_mat,
        $username,
        $hash,
        $tipo_usuario,
        $rutaImagen
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Usuario agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar usuario']);
    }
    exit;
}

// EDITAR USUARIO

if ($action === 'editar') {
    try {
        $id_usuario       = $_POST['id_usuario'] ?? '';
        $nombre           = $_POST['nombre'] ?? '';
        $apellido_paterno = $_POST['apellido_paterno'] ?? '';
        $apellido_materno = $_POST['apellido_materno'] ?? '';
        $tipo_usuario     = $_POST['tipo_usuario'] ?? '';
        $usuario          = $_POST['usuario'] ?? '';
        $contrasena      = $_POST['contrasena'] ?? '';

        $ruta_imagen = null;

        if (isset($_FILES['imagenEditar']) && $_FILES['imagenEditar']['error'] === 0) {

            // Validaciones de tipo y tamaño
            $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
            $tipo       = $_FILES['imagenEditar']['type'];
            $tamano     = $_FILES['imagenEditar']['size'];

            if (!in_array($tipo, $permitidos)) {
                echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no válido (solo JPG, JPEG o PNG).']);
                exit;
            }

            if ($tamano > 2 * 1024 * 1024) { // 2MB máximo
                echo json_encode(['status' => 'error', 'message' => 'La imagen debe pesar menos de 2MB.']);
                exit;
            }

            // Generación de nombre único y guardado
            $nombre_original = basename($_FILES['imagenEditar']['name']);
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_nuevo =  $usuario . '.' . pathinfo($_FILES['imagenEditar']['name'], PATHINFO_EXTENSION);

            $directorio = ROOT . '/assets/img-user/';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            $ruta_guardado = $directorio . $nombre_nuevo;

            if (!move_uploaded_file($_FILES['imagenEditar']['tmp_name'], $ruta_guardado)) {
                echo json_encode(['status' => 'error', 'message' => 'Fallo al mover la imagen al servidor.']);
                exit;
            }

            $ruta_imagen = $nombre_nuevo; // Solo el nombre o puedes guardar $ruta_guardado completo
        }

        // Update a la base de datos, ejemplo con PDO
        $sql = "UPDATE usuarios SET 
                    nombre = :nombre,
                    apellido_paterno = :apellido_paterno,
                    apellido_materno = :apellido_materno,
                    usuario = :usuario,
                    fk_id_tipo_usuario = :fk_id_tipo_usuario";

        // Si hay imagen, la agregamos al query
        if ($ruta_imagen !== null) {
            $sql .= ", imagen = :imagen";
        }

        //Si se ingreso contraseña
        if ($contrasena != null) {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $sql .= ", password = :contrasena";
        }

        $sql .= " WHERE id_usuario = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido_paterno', $apellido_paterno);
        $stmt->bindParam(':apellido_materno', $apellido_materno);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':fk_id_tipo_usuario', $tipo_usuario);
        $stmt->bindParam(':id', $id_usuario);

        if ($ruta_imagen !== null) {
            $stmt->bindParam(':imagen', $ruta_imagen);
        }

        if ($contrasena != null) {
            $stmt->bindParam(':contrasena', $hash);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Usuario actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

// CAMBIAR ESTATUS DE USUARIO
if ($action === 'activo') {
    $id_usuario = $_POST['id_usuario'] ?? '';
    $actual = $_POST['act'] ?? '';

    if ($id_usuario === '' || $actual === '') {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    $nuevoEstatus = ($actual == 1) ? 0 : 1;
    $stmt = $conexion->prepare("UPDATE usuarios SET activo = ? WHERE id_usuario = ?");
    if ($stmt->execute([$nuevoEstatus, $id_usuario])) {
        echo json_encode(['status' => 'ok', 'message' => 'Estatus actualizado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar estatus']);
    }
    exit;
}

// LISTAR USUARIO REUNION
if ($action === 'listar-reunion-usuarios') {
     $id_reunion = $_POST['id_reunion'];
    $stmt = $conexion->prepare("
    SELECT r_u.id_reunion_miembros, u.nombre, u.apellido_paterno, u.apellido_materno, u.imagen
    FROM reunion_miembros_usuarios r_u
    LEFT JOIN usuarios u ON r_u.fk_id_usuario = u.id_usuario
    WHERE r_u.fk_id_reunion = ?");
    $stmt->execute([$id_reunion]);
    $usuarios_html = '';
    $usuarios_nombre = '';
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $u_i = $row['imagen'];
        if ($u_i == null || $u_i == '') {
            $usuario_imagen = './assets/img-user/default.png';
        } else {
            $usuario_imagen = './assets/img-user/' . $u_i;
        }
        $usuarios_nombre = htmlspecialchars($row['nombre'] . " " . $row['apellido_paterno'] . " " . $row['apellido_materno']);
        $usuarios_html = '<img src="' . $usuario_imagen . '" width="90px" class="rounded-circle me-2" title="' . $usuarios_nombre . '">';

        $data[] = [
            $row['id_reunion_miembros'],
            $usuarios_html,
            $usuarios_nombre,
            '<button type="button" class="btn boton_terciario btn-sm delete-usuario-reunion me-3" 
                data-id="' . $row['id_reunion_miembros'] . '">
                <span class="material-icons">delete</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR USUARIO REUNION
if ($action === 'agregar-reunion-usuarios') {
    // Recoge y limpia los datos del formulario
    $id_usuario          = trim($_POST['id_usuario'] ?? '');
    $id_comite    = $_POST['id_comite'] ?? '';

    // Validación básica
    if ($id_usuario === '' || $id_comite === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Obtener fk_id_reunion desde la tabla comites usando id_comite
    $sql_reunion = "SELECT id_reunion_comite FROM reunion_comite WHERE fk_id_comite = ?";
    $stmt_reunion = $conexion->prepare($sql_reunion);
    $stmt_reunion->execute([$id_comite]);
    $id_reunion = $stmt_reunion->fetchColumn();

    if (!$id_reunion) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró la reunión correspondiente al comité' . $id_comite]);
        exit;
    }

    // Validar si la relación ya existe
    $sql_check = "SELECT COUNT(*) FROM reunion_miembros_usuarios WHERE fk_id_usuario = ? AND fk_id_reunion = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->execute([$id_usuario, $id_reunion]);
    $exists = $stmt_check->fetchColumn();

    if ($exists) {
        echo json_encode(['status' => 'error', 'message' => 'El usuario ya esta asignado']);
        exit;
    }

    $sql = "INSERT INTO reunion_miembros_usuarios (fk_id_reunion, fk_id_usuario) 
            VALUES (?, ?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $id_reunion,
        $id_usuario
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Usuario agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar usuario']);
    }
    exit;
}

//ELIMINAR USUARIO DE REUNION

if ($action === 'eliminar-reunion-usuario') {
    $id_reunion_miembros = $_POST['id_reunion_miembros'] ?? null;

    if (!$id_reunion_miembros) {
        echo json_encode(['status' => 'error', 'message' => 'ID de permiso requerido']);
        exit;
    }

    // Aquí preparas y ejecutas el DELETE con PDO
    $stmt = $conexion->prepare("DELETE FROM reunion_miembros_usuarios WHERE id_reunion_miembros = ?");
    if ($stmt->execute([$id_reunion_miembros])) {
        echo json_encode(['status' => 'ok', 'message' => 'Usuario removido correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al remover usuario.']);
    }
    exit;
}



// LISTAR USUARIO PERMISO
if ($action === 'listar-permiso') {
    $id_comite = $_POST['id_comite'];
    $stmt = $conexion->prepare("
    SELECT c_u.id_comite_usuario, u.nombre AS nombre, u.apellido_paterno AS apellido_paterno, u.apellido_materno AS apellido_materno
    FROM comites_usuarios c_u
    LEFT JOIN usuarios u ON c_u.fk_id_usuario = u.id_usuario
    WHERE c_u.fk_id_comite = ?");
    $stmt->execute([$id_comite]);
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['id_comite_usuario'],
            htmlspecialchars($row['nombre'] . " " . $row['apellido_paterno'] . " " . $row['apellido_materno']),
            '<button type="button" class="btn boton_terciario btn-sm delete-permiso me-3" 
                data-id="' . $row['id_comite_usuario'] . '">
                <span class="material-icons">delete</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}


// AGREGAR USUARIO PERMISO
if ($action === 'agregar-permiso') {
    // Recoge y limpia los datos del formulario
    $id_usuario          = trim($_POST['id_usuario'] ?? '');
    $id_comite    = trim($_POST['id_comite'] ?? '');

    // Validación básica
    if ($id_usuario === '' || $id_comite === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Validar si la relación ya existe
    $sql_check = "SELECT COUNT(*) FROM comites_usuarios WHERE fk_id_usuario = ? AND fk_id_comite = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->execute([$id_usuario, $id_comite]);
    $exists = $stmt_check->fetchColumn();

    if ($exists) {
        echo json_encode(['status' => 'error', 'message' => 'El usuario ya esta asignado en este comité']);
        exit;
    }

    $sql = "INSERT INTO comites_usuarios (fk_id_comite, fk_id_usuario) 
            VALUES (?, ?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $id_comite,
        $id_usuario
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Usuario agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar usuario']);
    }
    exit;
}

//ELIMINAR PERMISO A USUARIO

if ($action === 'eliminar-permiso') {
    $id_permiso = $_POST['id_permiso'] ?? null;

    if (!$id_permiso) {
        echo json_encode(['status' => 'error', 'message' => 'ID de permiso requerido']);
        exit;
    }

    // Aquí preparas y ejecutas el DELETE con PDO
    $stmt = $conexion->prepare("DELETE FROM comites_usuarios WHERE id_comite_usuario = ?");
    if ($stmt->execute([$id_permiso])) {
        echo json_encode(['status' => 'ok', 'message' => 'Usuario removido correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al remover usuario.']);
    }
    exit;
}


if ($action === 'cambiar-imagen') {
    if (empty($_SESSION['loggedin']) || empty($_SESSION['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Sesión no válida']);
        exit;
    }

    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Selecciona una imagen.']);
        exit;
    }

    $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
    $tipo = $_FILES['imagen']['type'] ?? '';
    $tamano = (int) ($_FILES['imagen']['size'] ?? 0);

    if (!in_array($tipo, $permitidos, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no válido (solo JPG, JPEG o PNG).']);
        exit;
    }

    if ($tamano > 2 * 1024 * 1024) {
        echo json_encode(['status' => 'error', 'message' => 'La imagen debe pesar menos de 2MB.']);
        exit;
    }

    if (@getimagesize($_FILES['imagen']['tmp_name']) === false) {
        echo json_encode(['status' => 'error', 'message' => 'El archivo no es una imagen válida.']);
        exit;
    }

    $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
        echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no válido (solo JPG, JPEG o PNG).']);
        exit;
    }

    $id = (int) $_SESSION['id'];
    $stmt = $conexion->prepare('SELECT usuario, imagen FROM usuarios WHERE id_usuario = ? LIMIT 1');
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
        exit;
    }

    $base = preg_replace('/[^A-Za-z0-9._-]/', '', (string) $usuario['usuario']);
    if ($base === '') {
        $base = 'usuario-' . $id;
    }

    $directorio = ROOT . '/assets/img-user/';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
    }

    $nombreNuevo = $base . '.' . $extension;
    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio . $nombreNuevo)) {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen.']);
        exit;
    }

    $anterior = basename((string) $usuario['imagen']);
    if ($anterior !== '' && $anterior !== $nombreNuevo && $anterior !== 'default.png' && is_file($directorio . $anterior)) {
        unlink($directorio . $anterior);
    }

    $update = $conexion->prepare('UPDATE usuarios SET imagen = ? WHERE id_usuario = ?');
    $update->execute([$nombreNuevo, $id]);
    $_SESSION['img-user'] = $nombreNuevo;

    echo json_encode(['status' => 'ok', 'message' => 'Imagen actualizada.', 'imagen' => $nombreNuevo]);
    exit;
}

// ACCIÓN NO VÁLIDA
echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
exit;
