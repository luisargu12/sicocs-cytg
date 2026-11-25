<?php


header('Content-Type: application/json');

// $conexion->exec("SET NAMES utf8mb4");

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// LISTAR MUNICIPIOS
if ($action === 'listar-m') {
    $stmt = $conexion->query("SELECT id_municipio, nombre, siglas FROM municipios");
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['id_municipio'],
            htmlspecialchars($row['nombre']),
            htmlspecialchars($row['siglas']),
            '<button class="btn boton_terciario btn-sm editar-municipio me-3" 
                data-id="' . $row['id_municipio'] . '" 
                data-nombre="' . htmlspecialchars($row['nombre']) . '" 
                data-siglas="' . htmlspecialchars($row['siglas']) . '">
                <span class="material-icons">edit</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR MUNICIPIO
if ($action === 'agregar-m') {
    // Recoge y limpia los datos del formulario
    $nombre          = trim($_POST['nombre'] ?? '');
    $siglas    = trim($_POST['siglas'] ?? '');

    // Validación básica
    if ($nombre === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO municipios (nombre, siglas) VALUES (?, ?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $nombre,
        $siglas
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Municipio agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar municipio']);
    }
    exit;
}

// EDITAR MUNICIPIO

if ($action === 'editar-m') {
    try {
        $id_municipio       = $_POST['id_municipio'] ?? '';
        $nombre           = $_POST['nombre'] ?? '';
        $siglas = $_POST['siglas'] ?? '';


        // Update a la base de datos, ejemplo con PDO
        $sql = "UPDATE municipios SET
            nombre = :nombre,
            siglas = :siglas
            WHERE id_municipio = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':siglas', $siglas);
        $stmt->bindParam(':id', $id_municipio);


        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Municipio actualizado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

// LISTAR COLONIAS
if ($action === 'listar-c') {
    $sql = "SELECT c.id_colonia, c.nombre, c.codigopostal, c.direccion_maps, c.fk_id_municipio, m.nombre AS nombre_municipio
            FROM colonias c
            LEFT JOIN municipios m ON c.fk_id_municipio = m.id_municipio";
    $stmt = $conexion->query($sql);
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['id_colonia'],
            htmlspecialchars($row['nombre']),
            htmlspecialchars($row['nombre_municipio']),
            htmlspecialchars($row['codigopostal']),
            '<a href="' . $row['direccion_maps'] . '" class="btn boton_primario" target="_blank"><span class="material-icons">location_on</span></a>',
            '<button class="btn boton_terciario btn-sm editar-colonia me-3" 
                data-id="' . $row['id_colonia'] . '" 
                data-nombre="' . htmlspecialchars($row['nombre']) . '" 
                data-cp="' . htmlspecialchars($row['codigopostal']) . '"
                data-id_municipio="' . htmlspecialchars($row['fk_id_municipio']) . '"
                data-maps="' . htmlspecialchars($row['direccion_maps']) . '">
                <span class="material-icons">edit</span>
            </button>'
        ];
    }

    echo json_encode(['data' => $data]);
    exit;
}

// AGREGAR COLONIA
if ($action === 'agregar-c') {
    // Recoge y limpia los datos del formulario
    $nombre          = trim($_POST['nombre'] ?? '');
    $codigopostal    = trim($_POST['cp'] ?? '');
    $id_municipio    = $_POST['id_municipio'] ?? '';
    $maps    = trim($_POST['maps'] ?? '');

    // Validación básica
    if ($nombre === '' || $id_municipio === '') {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO colonias (nombre, codigopostal, fk_id_municipio, direccion_maps) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([
        $nombre,
        $codigopostal,
        $id_municipio,
        $maps
    ]);

    if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => 'Colonia agregado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al agregar colonia']);
    }
    exit;
}

// EDITAR MUNICIPIO

if ($action === 'editar-c') {
    try {
        $id_colonia   = $_POST['id_colonia'] ?? '';
        $id_municipio = $_POST['id_municipio'] ?? '';
        $nombre       = $_POST['nombre'] ?? '';
        $cp           = $_POST['cp'] ?? '';
        $maps         = $_POST['maps'] ?? '';

        $sql = "UPDATE colonias SET 
                    nombre = :nombre,
                    codigopostal = :cp,
                    fk_id_municipio = :id_municipio,
                    direccion_maps = :maps
                WHERE id_colonia = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':cp', $cp);
        $stmt->bindParam(':id_municipio', $id_municipio);
        $stmt->bindParam(':maps', $maps);
        $stmt->bindParam(':id', $id_colonia);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'ok', 'message' => 'Colonia actualizada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_colonias') {
    $id_municipio = $_POST['id_municipio'] ?? '';

    if (!$id_municipio) {
        echo json_encode([]);
        exit;
    }

    $stmt = $conexion->prepare("SELECT id_colonia, nombre FROM colonias WHERE fk_id_municipio = ? ORDER BY nombre ASC");
    $stmt->execute([$id_municipio]);
    $colonias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devuelve arreglo JSON con colonias
    echo json_encode($colonias);
    exit;
}

// ACCIÓN NO VÁLIDA
echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
exit;
