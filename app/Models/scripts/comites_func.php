<?php


header('Content-Type: application/json');

// $conexion->exec("SET NAMES utf8mb4");

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// LISTAR Comites
if ($action === 'listar') {
    $year = $_POST['year'] ?? null;
    $where = "";
    $params = [];
    if ($year) {
        $where = " WHERE c.year = ? ";
        $params[] = $year;
    }

    $sql = "SELECT 
        c.id_comite, 
        c.folio, 
        c.constituido, 
        c.fecha, 
        c.status, 
        m.nombre AS nombre_municipio, 
        co.nombre AS nombre_colonia,
        u.id_usuario, 
        u.nombre AS nombre_usuario, 
        u.imagen AS imagen_usuario
    FROM comites c
    LEFT JOIN municipios m ON c.fk_id_municipio = m.id_municipio
    LEFT JOIN colonias co ON c.fk_id_colonia = co.id_colonia
    LEFT JOIN comites_usuarios cu ON c.id_comite = cu.fk_id_comite
    LEFT JOIN usuarios u ON cu.fk_id_usuario = u.id_usuario
    $where
    ORDER BY c.id_comite";
    
    if ($year) {
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
    } else {
        $stmt = $conexion->query($sql);
    }

    // Si hubo error, muestra descripción:
    if (!$stmt) {
        $error = $conexion->errorInfo();
        echo "Error en la consulta SQL: " . $error[2];
        exit;
    }

    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id_comite = $row['id_comite'];

        // Si el comité aún no existe, inicialízalo
        if (!isset($data[$id_comite])) {
            $constituido = ($row['constituido'] == 1) ? 'Si' : 'No';
            $status = ($row['status'] == 0) ? 'btn btn-danger' : (($row['status'] == 1) ? 'btn btn-warning' : (($row['status'] == 2) ? 'btn btn-success' : null));
            $data[$id_comite] = [
                'id_comite' => $id_comite,
                'folio' => '<div class="' . $status . '" >' . htmlspecialchars($row['folio']) . '</div>',
                'nombre_municipio' => htmlspecialchars($row['nombre_municipio']),
                'nombre_colonia' => htmlspecialchars($row['nombre_colonia']),
                'constituido' => $constituido,
                'fecha' => htmlspecialchars($row['fecha']),
                'usuarios' => [], // Aquí juntaremos todos los usuarios
                'acciones' => '<a href="/comite?id=' . $id_comite . '" class="btn boton_terciario me-3"><span class="material-icons">remove_red_eye</span></a>'
            ];
        }

        // Si existe usuario válido, lo agregas al comité
        if ($row['id_usuario']) {
            $data[$id_comite]['usuarios'][] = [
                'id_usuario' => $row['id_usuario'],
                'nombre_usuario' => htmlspecialchars($row['nombre_usuario']),
                'imagen_usuario' => htmlspecialchars($row['imagen_usuario']),
            ];
        }
    }

    // Prepara HTML para usuarios (opcional, puedes hacerlo en JS si prefieres)
    $data = array_values($data); // Turna el array asociativo en indexado
    foreach ($data as &$comite) {
        $usuarios_html = '';
        foreach ($comite['usuarios'] as $u) {
            $u_i = $u['imagen_usuario'];
            if ($u_i == null || $u_i == '') {
                $usuario_imagen = './assets/img-user/default.png';
            } else {
                $usuario_imagen = './assets/img-user/' . $u_i;
            }
            $usuarios_html .= '<img src="' . $usuario_imagen . '" width="55" height="55" class="foto-usuario rounded-circle me-2" title="' . $u['nombre_usuario'] . '">';
        }
        $comite['usuarios_html'] = $usuarios_html;
    }
    unset($comite);

    echo json_encode(['data' => $data]);
    exit;
}


// LISTAR Mis Comites
if ($action === 'listar-mis-comites') {
    $id_usuario = $_POST['id_usuario'];
    $sql = "
    SELECT 
        c.id_comite, 
        c.folio, 
        c.constituido, 
        c.fecha, 
        c.status, 
        m.nombre AS nombre_municipio, 
        co.nombre AS nombre_colonia,
        u.id_usuario, 
        u.nombre AS nombre_usuario, 
        u.imagen AS imagen_usuario
    FROM comites c
    LEFT JOIN municipios m ON c.fk_id_municipio = m.id_municipio
    LEFT JOIN colonias co ON c.fk_id_colonia = co.id_colonia
    INNER JOIN comites_usuarios cu ON c.id_comite = cu.fk_id_comite
    LEFT JOIN usuarios u ON cu.fk_id_usuario = u.id_usuario
    WHERE cu.fk_id_usuario = ?
    ORDER BY c.id_comite 
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);

    // Si hubo error, muestra descripción:
    if (!$stmt) {
        $error = $conexion->errorInfo();
        echo "Error en la consulta SQL: " . $error[2];
        exit;
    }

    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id_comite = $row['id_comite'];

        // Si el comité aún no existe, inicialízalo
        if (!isset($data[$id_comite])) {
            $constituido = ($row['constituido'] == 1) ? 'Si' : 'No';
            $status = ($row['status'] == 0) ? 'btn btn-danger' : (($row['status'] == 1) ? 'btn btn-warning' : (($row['status'] == 2) ? 'btn btn-success' : null));
            $data[$id_comite] = [
                'id_comite' => $id_comite,
                'folio' => '<div class="' . $status . '" >' . htmlspecialchars($row['folio']) . '</div>',
                'nombre_municipio' => htmlspecialchars($row['nombre_municipio']),
                'nombre_colonia' => htmlspecialchars($row['nombre_colonia']),
                'constituido' => $constituido,
                'fecha' => htmlspecialchars($row['fecha']),
                'usuarios' => [], // Aquí juntaremos todos los usuarios
                'acciones' => '<a href="/comite?id=' . $id_comite . '" class="btn boton_terciario me-3"><span class="material-icons">remove_red_eye</span></a>'
            ];
        }

        // Si existe usuario válido, lo agregas al comité
        if ($row['id_usuario']) {
            $data[$id_comite]['usuarios'][] = [
                'id_usuario' => $row['id_usuario'],
                'nombre_usuario' => htmlspecialchars($row['nombre_usuario']),
                'imagen_usuario' => htmlspecialchars($row['imagen_usuario']),
            ];
        }
    }

    // Prepara HTML para usuarios (opcional, puedes hacerlo en JS si prefieres)
    $data = array_values($data); // Turna el array asociativo en indexado
    foreach ($data as &$comite) {
        $usuarios_html = '';
        foreach ($comite['usuarios'] as $u) {
            $u_i = $u['imagen_usuario'];
            if ($u_i == null || $u_i == '') {
                $usuario_imagen = './assets/img-user/default.png';
            } else {
                $usuario_imagen = './assets/img-user/' . $u_i;
            }
            $usuarios_html .= '<img src="' . $usuario_imagen . '" width="55" height="55" class="foto-usuario rounded-circle me-2" title="' . $u['nombre_usuario'] . '">';
        }
        $comite['usuarios_html'] = $usuarios_html;
    }
    unset($comite);

    echo json_encode(['data' => $data]);
    exit;
}

// LISTAR Mis Comites
if ($action === 'listar-por-revisar') {
    $id_usuario = $_POST['id_usuario'];
    $sql = "
    SELECT 
        c.id_comite, 
        c.folio, 
        c.constituido, 
        c.fecha, 
        c.status, 
        m.nombre AS nombre_municipio, 
        u.id_usuario, 
        u.nombre AS nombre_usuario, 
        u.imagen AS imagen_usuario
    FROM comites c
    LEFT JOIN municipios m ON c.fk_id_municipio = m.id_municipio
    INNER JOIN comites_usuarios cu ON c.id_comite = cu.fk_id_comite
    LEFT JOIN usuarios u ON cu.fk_id_usuario = u.id_usuario
    WHERE c.status = 1
    ORDER BY c.id_comite DESC
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    // Si hubo error, muestra descripción:
    if (!$stmt) {
        $error = $conexion->errorInfo();
        echo "Error en la consulta SQL: " . $error[2];
        exit;
    }

    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id_comite = $row['id_comite'];

        // Si el comité aún no existe, inicialízalo
        if (!isset($data[$id_comite])) {
            $constituido = ($row['constituido'] == 1) ? 'Si' : 'No';
            $status = ($row['status'] == 0) ? 'btn btn-danger' : (($row['status'] == 1) ? 'btn btn-warning' : (($row['status'] == 2) ? 'btn btn-success' : null));
            $data[$id_comite] = [
                'id_comite' => $id_comite,
                'folio' => '<div class="' . $status . '" >' . htmlspecialchars($row['folio']) . '</div>',
                'nombre_municipio' => htmlspecialchars($row['nombre_municipio']),
                'constituido' => $constituido,
                'fecha' => htmlspecialchars($row['fecha']),
                'usuarios' => [], // Aquí juntaremos todos los usuarios
                'acciones' => '<a href="/comite?id=' . $id_comite . '" class="btn boton_terciario me-3"><span class="material-icons">remove_red_eye</span></a>'
            ];
        }

        // Si existe usuario válido, lo agregas al comité
        if ($row['id_usuario']) {
            $data[$id_comite]['usuarios'][] = [
                'id_usuario' => $row['id_usuario'],
                'nombre_usuario' => htmlspecialchars($row['nombre_usuario']),
                'imagen_usuario' => htmlspecialchars($row['imagen_usuario']),
            ];
        }
    }

    // Prepara HTML para usuarios (opcional, puedes hacerlo en JS si prefieres)
    $data = array_values($data); // Turna el array asociativo en indexado
    foreach ($data as &$comite) {
        $usuarios_html = '';
        foreach ($comite['usuarios'] as $u) {
            $u_i = $u['imagen_usuario'];
            if ($u_i == null || $u_i == '') {
                $usuario_imagen = './assets/img-user/default.png';
            } else {
                $usuario_imagen = './assets/img-user/' . $u_i;
            }
            $usuarios_html .= '<img src="' . $usuario_imagen . '" width="55" height="55" class="foto-usuario rounded-circle me-2" title="' . $u['nombre_usuario'] . '">';
        }
        $comite['usuarios_html'] = $usuarios_html;
    }
    unset($comite);

    echo json_encode(['data' => $data]);
    exit;
}


// Ver Comite
if ($action === 'ver-comite') {

    $id_comite = $_POST['id_comite'];

    if (!$id_comite) {
        echo json_encode(['status' => 'error', 'message' => 'ID faltante']);
        exit;
    }

    $sql = "SELECT c.*, r_c.* 
        FROM comites c 
        LEFT JOIN reunion_comite r_c ON c.id_comite = r_c.fk_id_comite 
        WHERE c.id_comite = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_comite]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode(['status' => 'ok', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Comité no encontrado']);
    }
    exit;
}

//ACTUALIZAR

if ($action === 'editar-comite') {
    try {
        function limpiarInput($valor)
        {
            return ($valor === null || $valor === '') ? null : $valor;
        }

        $id_municipio = limpiarInput($_POST['id_municipio'] ?? null);
        $id_colonia = limpiarInput($_POST['id_colonia'] ?? null);
        $fecha = limpiarInput($_POST['fecha'] ?? null);
        $id_programa = limpiarInput($_POST['id_programa'] ?? null);
        $id_ambito = limpiarInput($_POST['id_ambito'] ?? null);
        $tipo_accion = limpiarInput($_POST['tipo_accion'] ?? null);
        $inversion = limpiarInput($_POST['inversion'] ?? '');
        $proveedor = limpiarInput($_POST['proveedor'] ?? '');
        $constituido = limpiarInput($_POST['constituido'] ?? null);
        $descripcion = limpiarInput($_POST['descripcion'] ?? '');
        $comentarios = limpiarInput($_POST['comentarios'] ?? '');
        $id_comite = limpiarInput($_POST['id_comite'] ?? null); // Asegúrate de enviar id_comite también en el form si necesitas

        //inputs reunion
        $id_tipo_reunion = limpiarInput($_POST['id_tipo_reunion'] ?? null);
        $modalidad = limpiarInput($_POST['modalidad'] ?? null);
        $horario = limpiarInput($_POST['horario'] ?? null);
        $integrantes_m = limpiarInput($_POST['integrantes_m'] ?? null);
        $integrantes_h = limpiarInput($_POST['integrantes_h'] ?? null);
        $integrantes = limpiarInput($_POST['integrantes'] ?? null);
        $beneficiarios_m = limpiarInput($_POST['beneficiarios_m'] ?? null);
        $beneficiarios_h = limpiarInput($_POST['beneficiarios_h'] ?? null);
        $beneficiarios = limpiarInput($_POST['beneficiarios'] ?? null);
        $id_reunion_comite = limpiarInput($_POST['id_reunion_comite'] ?? null);


        $sql = "UPDATE comites SET
            fk_id_municipio = :id_municipio,
            fk_id_colonia = :id_colonia,
            fecha = :fecha,
            fk_id_programa = :id_programa,
            fk_id_ambito = :id_ambito,
            fk_id_accion = :tipo_accion,
            monto_inversion = :inversion,
            proveedor = :proveedor,
            constituido = :constituido,
            descripcion = :descripcion,
            comentarios = :comentarios
            WHERE id_comite = :id_comite";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_municipio' => $id_municipio,
            ':id_colonia' => $id_colonia,
            ':fecha' => $fecha,
            ':id_programa' => $id_programa,
            ':id_ambito' => $id_ambito,
            ':tipo_accion' => $tipo_accion,
            ':inversion' => $inversion,
            ':proveedor' => $proveedor,
            ':constituido' => $constituido,
            ':descripcion' => $descripcion,
            ':comentarios' => $comentarios,
            ':id_comite' => $id_comite
        ]);


        //update reunion comite

        $sql_reunion = "UPDATE reunion_comite SET
            fk_id_comite = :id_comite,
            fk_id_tipo_reunion = :id_tipo_reunion,
            modalidad = :modalidad,
            horario = :horario,
            integrantes = :integrantes,
            integrantes_m = :integrantes_m,
            integrantes_h = :integrantes_h,
            beneficiarios = :beneficiarios,
            beneficiarios_m = :beneficiarios_m,
            beneficiarios_h = :beneficiarios_h
            WHERE id_reunion_comite = :id_reunion_comite";
        $stmt_reunion = $conexion->prepare($sql_reunion);
        $stmt_reunion->execute([
            ':id_comite' => $id_comite,
            ':id_tipo_reunion' => $id_tipo_reunion,
            ':modalidad' => $modalidad,
            ':horario' => $horario,
            ':integrantes' => $integrantes,
            ':integrantes_m' => $integrantes_m,
            ':integrantes_h' => $integrantes_h,
            ':beneficiarios' => $beneficiarios,
            ':beneficiarios_m' => $beneficiarios_m,
            ':beneficiarios_h' => $beneficiarios_h,
            ':id_reunion_comite' => $id_reunion_comite
        ]);

        echo json_encode(['status' => 'ok', 'message' => 'Comité actualizado correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'cambiar-status') {
    try {
        $id_comite = $_POST['id_comite'] ?? null;
        $nuevo_status = $_POST['status'] ?? null;

        if ($id_comite === null || $nuevo_status === null) {
            throw new Exception("Faltan datos para actualizar status");
        }

        if ((string) $nuevo_status === '1') {
            $revision = $conexion->prepare('SELECT c.fecha, r.fk_id_tipo_reunion, r.modalidad, r.horario
                FROM comites c
                LEFT JOIN reunion_comite r ON r.fk_id_comite = c.id_comite
                WHERE c.id_comite = ?
                ORDER BY r.id_reunion_comite DESC
                LIMIT 1');
            $revision->execute([$id_comite]);
            $row = $revision->fetch(PDO::FETCH_ASSOC) ?: [];
            $faltantes = [];
            if (($row['fecha'] ?? '') === '' || $row['fecha'] === '0000-00-00') {
                $faltantes[] = 'la fecha';
            }
            if (empty($row['fk_id_tipo_reunion'] ?? null)) {
                $faltantes[] = 'el tipo de reunión';
            }
            if (($row['modalidad'] ?? '') === '') {
                $faltantes[] = 'la modalidad';
            }
            if (($row['horario'] ?? '') === '') {
                $faltantes[] = 'el horario';
            }
            if ($faltantes !== []) {
                $ultimo = array_pop($faltantes);
                $lista = $faltantes === [] ? $ultimo : implode(', ', $faltantes) . ' y ' . $ultimo;
                throw new Exception('No se puede mandar a revisión. Falta ' . $lista . '.');
            }
        }

        $sql = "UPDATE comites SET status = :status WHERE id_comite = :id_comite";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':status' => $nuevo_status,
            ':id_comite' => $id_comite
        ]);

        echo json_encode(['status' => 'ok', 'message' => 'Status actualizado correctamente']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

//Get Programa 

if ($action === "get-programa") {
    $id_programa = $_POST['id_programa'] ?? null;
    if (!$id_programa) {
        echo json_encode(['status' => 'error', 'message' => 'ID programa faltante']);
        exit;
    }

    $sql = "SELECT p.id_programa, r.id_ramo, r.ramo, r.descripcion, i.id_instancia_ejecutora, i.nombre_instancia
        FROM programas p
        LEFT JOIN ramos r ON p.fk_id_ramo = r.id_ramo
        LEFT JOIN instancia_ejecutora i ON p.fk_id_instancia_ejecutora = i.id_instancia_ejecutora
        WHERE p.id_programa = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_programa]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode(['status' => 'ok', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Programa no encontrado']);
    }
    exit;
}

//Get Tipos de Accion 

if ($action === "get-tipos") {
    $id_ambito = $_POST['id_ambito'] ?? null;
    if (!$id_ambito) {
        echo json_encode(['status' => 'error', 'message' => 'ID programa faltante']);
        exit;
    }

    $sql = "SELECT id_accion, descripcion FROM tipo_accion WHERE fk_id_ambito = ? ORDER BY descripcion ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_ambito]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode(['status' => 'ok', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tipo de acción no encontrado']);
    }
    exit;
}
