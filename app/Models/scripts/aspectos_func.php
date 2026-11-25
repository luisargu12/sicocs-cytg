<?php


header('Content-Type: application/json');

// $conexion->exec("SET NAMES utf8mb4");

$action = $_POST['action'] ?? $_GET['action'] ?? '';


// LISTAR AMBITOS
if ($action === 'listar-am') {
    $stmt = $conexion->query("SELECT * FROM ambitos");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['id_ambito'],
            htmlspecialchars($row['descripcion']),
            '<button class="btn boton_terciario btn-sm editar-ambito me-3" 
                data-id="' . $row['id_ambito'] . '" 
                data-descripcion="' . htmlspecialchars($row['descripcion']) . '">
                <span class="material-icons">edit</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR AMBITOS
if ($action === 'agregar-am') {
    // Recoge y limpia los datos del formulario
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Validación básica
    if ($descripcion === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO ambitos (descripcion) VALUES (?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $descripcion
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Ambito agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar ambito']);
    }
    exit;
}

// EDITAR AMBITOS

if ($action === 'editar-am') {
    try {
        $id_ambito = $_POST['id_ambito'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';


        // Update a la base de datos, ejemplo con PDO
        $sql = "UPDATE ambitos SET
            descripcion = :descripcion
            WHERE id_ambito = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id', $id_ambito);


        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Ambito actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

// LISTAR TIPOS DE ACCION
if ($action === 'listar-ta') {
    $stmt = $conexion->query("SELECT tipo_accion.*, ambitos.descripcion AS ambito FROM tipo_accion LEFT JOIN ambitos ON tipo_accion.fk_id_ambito = ambitos.id_ambito");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['id_accion'],
            htmlspecialchars($row['descripcion']),
            htmlspecialchars($row['ambito']),
            '<button class="btn boton_terciario btn-sm editar-accion me-3" 
                data-id="' . $row['id_accion'] . '" 
                data-descripcion="' . htmlspecialchars($row['descripcion']) . '"
                data-id_ambito="' . $row['fk_id_ambito'] . '"">
                <span class="material-icons">edit</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR TIPOS DE ACCION
if ($action === 'agregar-ta') {
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_ambito = trim($_POST['id_ambito'] ?? '');

    if ($descripcion === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO tipo_accion (descripcion, fk_id_ambito) VALUES (?, ?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $descripcion,
        $id_ambito
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Tipo de acción agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar tipo de acción']);
    }
    exit;
}

// EDITAR TIPOS DE ACCION   

if ($action === 'editar-ta') {
    try {
        $id_accion = $_POST['id_accion'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $id_ambito = $_POST['id_ambito'] ?? '';

        $sql = "UPDATE tipo_accion SET
            descripcion = :descripcion,
            fk_id_ambito = :id_ambito
            WHERE id_accion = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_ambito', $id_ambito);
        $stmt->bindParam(':id', $id_accion);


        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Tipo de acción actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

// LISTAR TIPOS DE REUNION
if ($action === 'listar-tr') {
    $stmt = $conexion->query("SELECT * FROM tipo_reunion_comite");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $icono = ($row['activo'] == 1) ? 'check_circle_outline' : 'highlight_off';
        $color = ($row['activo'] == 1) ? 'btn-success' : 'btn-danger';
        $data[] = [
            $row['id_tipo_reunion_comite'],
            htmlspecialchars($row['descripcion']),
            '<button class="btn boton_terciario btn-sm editar-tipo_reunion me-3" 
                data-id="' . $row['id_tipo_reunion_comite'] . '" 
                data-descripcion="' . htmlspecialchars($row['descripcion']) . '">
                <span class="material-icons">edit</span>
            </button>',
            '<button class="btn ' . $color . ' btn-sm activar-tr" 
                data-id="' . $row['id_tipo_reunion_comite'] . '" 
                data-act="' . $row['activo'] . '">
                <span class="material-icons">' . $icono . '</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR TIPOS DE REUNION
if ($action === 'agregar-tr') {
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($descripcion === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO tipo_reunion_comite (descripcion) VALUES (?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $descripcion
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Tipo de reunión agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar tipo de reunión']);
    }
    exit;
}

// EDITAR TIPOS DE REUNION

if ($action === 'editar-tr') {
    try {
        $id_tipo_reunion = $_POST['id_tipo_reunion'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        $sql = "UPDATE tipo_reunion_comite SET
            descripcion = :descripcion
            WHERE id_tipo_reunion_comite = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id', $id_tipo_reunion);


        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Tipo de reunión actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

// CAMBIAR ESTATUS DE PROGRAMA
if ($action === 'activo-tr') {
    $id_tr = $_POST['id_tr'] ?? '';
    $actual = $_POST['act'] ?? '';

    if ($id_tr === '' || $actual === '') {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    $nuevoEstatus = ($actual == 1) ? 0 : 1;
    $stmt = $conexion->prepare("UPDATE tipo_reunion_comite SET activo = ? WHERE id_tipo_reunion_comite = ?");
    if ($stmt->execute([$nuevoEstatus, $id_tr])) {
        echo json_encode(['status' => 'ok', 'message' => 'Estatus actualizado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar estatus']);
    }
    exit;
}

// LISTAR TIPOS DE REUNION DE SEGUIMIENTO
if ($action === 'listar-trs') {
    $stmt = $conexion->query("SELECT * FROM tipo_reunion_seguimiento");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $icono = ($row['activo'] == 1) ? 'check_circle_outline' : 'highlight_off';
        $color = ($row['activo'] == 1) ? 'btn-success' : 'btn-danger';
        $data[] = [
            $row['id_tipo_reunion_seg'],
            htmlspecialchars($row['descripcion']),
            '<button class="btn boton_terciario btn-sm editar-tipo_reunion_seg me-3" 
                data-id="' . $row['id_tipo_reunion_seg'] . '" 
                data-descripcion="' . htmlspecialchars($row['descripcion']) . '">
                <span class="material-icons">edit</span>
            </button>',
            '<button class="btn ' . $color . ' btn-sm activar-trs" 
                data-id="' . $row['id_tipo_reunion_seg'] . '" 
                data-act="' . $row['activo'] . '">
                <span class="material-icons">' . $icono . '</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

if ($action === 'agregar-trs') {
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($descripcion === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    $sql = "INSERT INTO tipo_reunion_seguimiento (descripcion) VALUES (?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([$descripcion]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Tipo de reunión agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar tipo de reunión']);
    }
    exit;
}

if ($action === 'editar-trs') {
    try {
        $id_tipo_reunion = $_POST['id_tipo_reunion'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        $sql = "UPDATE tipo_reunion_seguimiento SET
            descripcion = :descripcion
            WHERE id_tipo_reunion_seg = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id', $id_tipo_reunion);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Tipo de reunión actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'activo-trs') {
    $id_tr = $_POST['id_tr'] ?? '';
    $actual = $_POST['act'] ?? '';

    if ($id_tr === '' || $actual === '') {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    $nuevoEstatus = ($actual == 1) ? 0 : 1;
    $stmt = $conexion->prepare("UPDATE tipo_reunion_seguimiento SET activo = ? WHERE id_tipo_reunion_seg = ?");
    if ($stmt->execute([$nuevoEstatus, $id_tr])) {
        echo json_encode(['status' => 'ok', 'message' => 'Estatus actualizado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar estatus']);
    }
    exit;
}

// LISTAR MATERIALES DE DIFUSION
if ($action === 'listar-md') {
    $stmt = $conexion->query("SELECT * FROM material_difusion");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['id_material'],
            htmlspecialchars($row['descripcion']),
            '<button class="btn boton_terciario btn-sm editar-material me-3" 
                data-id="' . $row['id_material'] . '" 
                data-descripcion="' . htmlspecialchars($row['descripcion']) . '">
                <span class="material-icons">edit</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR MATERIALES DE DIFUSION
if ($action === 'agregar-md') {
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($descripcion === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO material_difusion (descripcion) VALUES (?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $descripcion
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Material de difusión agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar material de difusión']);
    }
    exit;
}

// EDITAR MATERIALES DE DIFUSION 

if ($action === 'editar-md') {
    try {
        $id_material = $_POST['id_material'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        $sql = "UPDATE material_difusion SET
            descripcion = :descripcion
            WHERE id_material = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id', $id_material);


        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Material de difusion actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}


// LISTAR MATERIALES DE DIFUSION ENTREGADO
if ($action === 'listar-materialE') {
    $id_comite = $_POST["id_comite"];
    $stmt = $conexion->prepare("
    SELECT me.id_material_entregado, me.fk_id_material, m.descripcion, me.cantidad
    FROM material_difusion_entregado me
    LEFT JOIN material_difusion m ON me.fk_id_material = m.id_material
    WHERE me.fk_id_comite = ?");
    $stmt->execute([$id_comite]);
    $data = [];
    $total = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['id_material_entregado'],
            htmlspecialchars($row['descripcion']),
            htmlspecialchars($row['cantidad']),
            '<button class="btn boton_terciario btn-sm modal-editar me-3" 
                data-id="' . $row['id_material_entregado'] . '" 
                data-material="' . htmlspecialchars($row['descripcion']) . '" 
                data-cantidad="' . htmlspecialchars($row['cantidad']) . '">
                <span class="material-icons">edit</span>
            </button>
            <button class="btn boton_terciario btn-sm delete-material me-3" 
                data-id="' . $row['id_material_entregado'] . '">
                <span class="material-icons">delete</span>
            </button>'
        ];
        if ($row['fk_id_material'] != 1 && $row['fk_id_material'] != 2) {
            $total += floatval($row['cantidad']);
        }
    }

    echo json_encode(['data' => $data, 'total_material' => $total]);
    exit;
}

// AGREGAR MATERIALES DE DIFUSION Entregados
if ($action === 'agregar-materialE') {
    $id_comite =  $_POST['id_comite'] ?? null;
    $id_material = $_POST['id_material'] ?? null;
    $cantidad = $_POST['cantidad'] ?? null;

    if ($id_material === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Validar si la relación ya existe
    $sql_check = "SELECT COUNT(*) FROM material_difusion_entregado WHERE fk_id_material = ? AND fk_id_comite = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->execute([$id_material, $id_comite]);
    $exists = $stmt_check->fetchColumn();

    if ($exists) {
        echo json_encode(['status' => 'error', 'message' => 'El material ya está seleccionado para este comité']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO material_difusion_entregado (fk_id_material, fk_id_comite, cantidad) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $id_material,
        $id_comite,
        $cantidad
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Material de difusión agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar material de difusión']);
    }
    exit;
}

// EDITAR MATERIALES DE DIFUSION ENTREGADOS 

if ($action === 'editar-materialE') {
    try {
        $id_material_entregado = $_POST['id_material_entregado'] ?? '';
        $cantidad = $_POST['cantidad'] ?? '';

        $sql = "UPDATE material_difusion_entregado SET
            cantidad = :cantidad
            WHERE id_material_entregado = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':id', $id_material_entregado);


        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Cantidad actualizada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

//ELIMINAR MATERIAL ENTREGADO

if ($action === 'eliminar-materialE') {
    $id_material_entregado = $_POST['id_material_entregado'] ?? null;

    if (!$id_material_entregado) {
        echo json_encode(['status' => 'error', 'message' => 'ID de permiso requerido']);
        exit;
    }

    // Aquí preparas y ejecutas el DELETE con PDO
    $stmt = $conexion->prepare("DELETE FROM material_difusion_entregado WHERE id_material_entregado = ?");
    if ($stmt->execute([$id_material_entregado])) {
        echo json_encode(['status' => 'ok', 'message' => 'Material removido correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al remover el material.']);
    }
    exit;
}




// ACCIÓN NO VÁLIDA
echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
exit;
