<?php


header('Content-Type: application/json');

// $conexion->exec("SET NAMES utf8mb4");

$action = $_POST['action'] ?? $_GET['action'] ?? '';


// LISTAR PROGRAMAS
if ($action === 'listar-p') {
    $stmt = $conexion->query("SELECT programas.*, ramos.ramo AS ramo_num, ramos.descripcion AS ramo_desc, instancia_ejecutora.nombre_instancia AS instancia FROM programas
    LEFT JOIN ramos ON programas.fk_id_ramo = ramos.id_ramo LEFT JOIN instancia_ejecutora ON programas.fk_id_instancia_ejecutora = instancia_ejecutora.id_instancia_ejecutora");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $icono = ($row['activo'] == 1) ? 'check_circle_outline' : 'highlight_off';
        $color = ($row['activo'] == 1) ? 'btn-success' : 'btn-danger';
        $data[] = [
            $row['id_programa'],
            htmlspecialchars($row['descripcion']),
            htmlspecialchars($row['ramo_num'] . ' - ' . $row['ramo_desc']),
            htmlspecialchars($row['instancia']),
            '<button class="btn boton_terciario btn-sm editar-programa me-3" 
                data-id="' . $row['id_programa'] . '" 
                data-descripcion="' . htmlspecialchars($row['descripcion']) . '" 
                data-id_ramo="' . htmlspecialchars($row['fk_id_ramo']) . '"
                data-id_instancia="' . htmlspecialchars($row['fk_id_instancia_ejecutora']) . '">
                <span class="material-icons">edit</span>
            </button>',
            '<button class="btn ' . $color . ' btn-sm activar-programa" 
                data-id="' . $row['id_programa'] . '" 
                data-act="' . $row['activo'] . '">
                <span class="material-icons">' . $icono . '</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR PROGRAMA
if ($action === 'agregar-p') {
    // Recoge y limpia los datos del formulario
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_ramo = trim($_POST['id_ramo'] ?? '');
    $id_instancia = $_POST['id_instancia'] ?? '';

    // Validación básica
    if ($descripcion === '' || $id_ramo === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO programas (descripcion, fk_id_ramo, fk_id_instancia_ejecutora) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $descripcion,
        $id_ramo,
        $id_instancia
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Programa agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar programa']);
    }
    exit;
}

// EDITAR PROGRAMA

if ($action === 'editar-p') {
    try {
        $id_programa = $_POST['id_programa'] ?? '';
        $id_instancia = $_POST['id_instancia'] ?? '';
        $id_ramo = $_POST['id_ramo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';


        // Update a la base de datos, ejemplo con PDO
        $sql = "UPDATE programas SET
            descripcion = :descripcion,
            fk_id_ramo = :id_ramo,
            fk_id_instancia_ejecutora = :id_instancia
            WHERE id_programa = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_ramo', $id_ramo);
        $stmt->bindParam(':id_instancia', $id_instancia);
        $stmt->bindParam(':id', $id_programa);


        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Programa actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

// CAMBIAR ESTATUS DE PROGRAMA
if ($action === 'activo-programa') {
    $id_programa = $_POST['id_programa'] ?? '';
    $actual = $_POST['act'] ?? '';

    if ($id_programa === '' || $actual === '') {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    $nuevoEstatus = ($actual == 1) ? 0 : 1;
    $stmt = $conexion->prepare("UPDATE programas SET activo = ? WHERE id_programa = ?");
    if ($stmt->execute([$nuevoEstatus, $id_programa])) {
        echo json_encode(['status' => 'ok', 'message' => 'Estatus actualizado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar estatus']);
    }
    exit;
}


// LISTAR IE
if ($action === 'listar-ie') {
    $stmt = $conexion->query("SELECT * FROM instancia_ejecutora");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['id_instancia_ejecutora'],
            htmlspecialchars($row['nombre_instancia']),
            '<button class="btn boton_terciario btn-sm editar-instancia me-3" 
                data-id="' . $row['id_instancia_ejecutora'] . '" 
                data-nombre="' . htmlspecialchars($row['nombre_instancia']) . '">
                <span class="material-icons">edit</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR INSTANCIA
if ($action === 'agregar-ie') {
    // Recoge y limpia los datos del formulario
    $nombre = trim($_POST['nombre_instancia'] ?? '');

    // Validación básica
    if ($nombre === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO instancia_ejecutora (nombre_instancia) VALUES (?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $nombre
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Instancia agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar programa']);
    }
    exit;
}

// EDITAR INSTANCIA

if ($action === 'editar-ie') {
    try {
        $id_instancia = $_POST['id_instancia'] ?? '';
        $nombre = $_POST['nombre_instancia'] ?? '';


        // Update a la base de datos, ejemplo con PDO
        $sql = "UPDATE instancia_ejecutora SET
            nombre_instancia = :nombre
            WHERE id_instancia_ejecutora = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id', $id_instancia);


        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Instancia actualizada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}



// LISTAR RAMO
if ($action === 'listar-r') {
    $stmt = $conexion->query("SELECT * FROM ramos");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['id_ramo'],
            htmlspecialchars($row['ramo']),
            htmlspecialchars($row['descripcion']),
            '<button class="btn boton_terciario btn-sm editar-ramo me-3" 
                data-id="' . $row['id_ramo'] . '" 
                data-ramo="' . htmlspecialchars($row['ramo']) . '"
                data-descripcion="' . htmlspecialchars($row['descripcion']) . '">
                <span class="material-icons">edit</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR RAMO
if ($action === 'agregar-r') {
    // Recoge y limpia los datos del formulario
    $ramo = trim($_POST['ramo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Validación básica
    if ($ramo === '' || $descripcion === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO ramos (ramo, descripcion) VALUES (?, ?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $ramo,
        $descripcion
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Ramo agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar programa']);
    }
    exit;
}

// EDITAR RAMO

if ($action === 'editar-r') {
    try {
        $id_ramo = $_POST['id_ramo'] ?? '';
        $ramo = $_POST['ramo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';


        // Update a la base de datos, ejemplo con PDO
        $sql = "UPDATE ramos SET
            ramo = :ramo,
            descripcion = :descripcion
            WHERE id_ramo = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':ramo', $ramo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id', $id_ramo);


        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Ramo actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}




// ACCIÓN NO VÁLIDA
echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
exit;
