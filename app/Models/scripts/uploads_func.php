<?php


header('Content-Type: application/json');

// $conexion->exec("SET NAMES utf8mb4");

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// LISTAR IMAGENES
if ($action === 'listar-imagenes') {
    $id_comite = $_POST['id_comite'] ?? null;

    if (!$id_comite) {
        echo json_encode(['status' => 'error', 'message' => 'ID de comité requerido']);
        exit;
    }

    $sql = "SELECT ruta FROM comites_imagenes WHERE fk_id_comite = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_comite]);
    $imagenes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['status' => 'ok', 'imagenes' => $imagenes]);
    exit;
}

if ($action === 'contar-imagenes') {
    $id_comite = $_POST['id_comite'] ?? null;
    if (!$id_comite) {
        echo json_encode(['status' => 'error', 'message' => 'ID comité faltante']);
        exit;
    }
    $sql = "SELECT COUNT(*) FROM comites_imagenes WHERE fk_id_comite = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_comite]);
    $cantidad = $stmt->fetchColumn();
    echo json_encode(['status' => 'ok', 'cantidad' => intval($cantidad)]);
    exit;
}

if ($action === 'agregar-imagen') {
    $id_comite = $_POST['id_comite'] ?? null;

    // Contar imágenes existentes
    $sql = "SELECT COUNT(*) FROM comites_imagenes WHERE fk_id_comite = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_comite]);
    $cantidad = (int) $stmt->fetchColumn();

    if ($cantidad >= 6) {
        echo json_encode(['status' => 'error', 'message' => 'El comité ya tiene 6 imágenes, no puedes subir más.']);
        exit;
    }

    $sql2 = "SELECT year FROM comites WHERE id_comite = ?";
    $stmt2 = $conexion->prepare($sql2);
    $stmt2->execute([$id_comite]);
    $year = (int) $stmt2->fetchColumn();


    $images = [];
    $rutaImagen = null;

    // SOPORTA PHP 1 archivo por vez (de JS lo mandamos por archivo)
    if (
        isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK
        && $id_comite
    ) {
        // Límite de tamaño
        if ($_FILES['imagen']['size'] > 10 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'La imagen supera el tamaño máximo de 10MB']);
            exit;
        }

        // Extensiones y carpeta igual que antes
        $folio = "CTG-" . str_pad($id_comite, 3, '0', STR_PAD_LEFT) . '_' . $year;
        $directorioDestino = ROOT . '/uploads/imagenes/' . $folio . '/';

        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0775, true);
        }

        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['imagen']['type'], $tiposPermitidos)) {
            echo json_encode(['status' => 'error', 'message' => 'El archivo debe ser una imagen JPG, PNG, GIF o WEBP']);
            exit;
        }

        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid('img_', true) . '.' . $extension;
        $rutaCompleta = $directorioDestino . $nombreArchivo;

        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
            echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen']);
            exit;
        }

        $rutaImagen = 'uploads/imagenes/' . $folio . '/' . $nombreArchivo;
        $images[] = $rutaImagen;
    }

    // Guardar si corresponde
    if ($rutaImagen && $id_comite) {
        $sql = "INSERT INTO comites_imagenes (fk_id_comite, ruta) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql);
        $ok = $stmt->execute([
            $id_comite,
            $rutaImagen
        ]);

        if ($ok) {
            echo json_encode([
                'status' => 'ok',
                'message' => 'Imagen subida y guardada correctamente',
                'images' => $images,
                'ruta' => $rutaImagen
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al guardar en la base de datos'
            ]);
        }
        exit;
    }
}

if ($action === 'delete-imagen') {
    $id_comite = $_POST['id_comite'] ?? null;
    $ruta = $_POST['ruta'] ?? null;

    if (!$id_comite || !$ruta) {
        echo json_encode(['status' => 'error', 'message' => 'Datos insuficientes']);
        exit;
    }

    // Elimina de la base de datos
    $sql = "DELETE FROM comites_imagenes WHERE fk_id_comite = ? AND ruta = ?";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([$id_comite, $ruta]);

    if ($ok) {
        // Elimina archivo físico
        $archivo = ROOT . '/' . $ruta; // Asegura ruta absoluta
        if (is_file($archivo)) {
            unlink($archivo);
        }
        echo json_encode(['status' => 'ok', 'message' => 'Imagen eliminada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar de la base de datos']);
    }
    exit;
}


if ($action === 'mostrar-acta') {
    $id_comite = $_POST['id_comite'] ?? null;

    if (!$id_comite) {
        echo json_encode(['status' => 'error', 'message' => 'ID de comité faltante']);
        exit;
    }

    $sql = "SELECT ruta FROM comites_actas WHERE fk_id_comite = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_comite]);
    $ruta = $stmt->fetchColumn();

    if ($ruta) {
        echo json_encode([
            'status' => 'ok',
            'ruta' => $ruta
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se ha subido ninguna acta'
        ]);
    }
    exit;
}



if ($action === 'agregar-acta') {
    $id_comite = $_POST['id_comite'] ?? null;
    $rutaPDF = null;

    // Solo un PDF por comité
    $sql = "SELECT COUNT(*) FROM comites_actas WHERE fk_id_comite = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_comite]);
    $cantidad = (int) $stmt->fetchColumn();

    if ($cantidad >= 1) {
        echo json_encode(['status' => 'error', 'message' => 'El comité ya tiene un acta PDF registrada.']);
        exit;
    }

    // Validar archivo
    if (isset($_FILES['acta']) && $_FILES['acta']['error'] === UPLOAD_ERR_OK && $id_comite) {
        // Límite de tamaño (10MB)
        if ($_FILES['acta']['size'] > 10 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'El archivo supera el tamaño máximo de 10MB']);
            exit;
        }

        // Validar tipo PDF
        $tipoPermitido = 'application/pdf';
        if ($_FILES['acta']['type'] !== $tipoPermitido) {
            echo json_encode(['status' => 'error', 'message' => 'Solo se permite subir archivos PDF']);
            exit;
        }

        // Carpeta y nombre archivo con año del comité
        $sql2 = "SELECT year FROM comites WHERE id_comite = ?";
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->execute([$id_comite]);
        $year = (int) $stmt2->fetchColumn();
        $folio = "CTG-" . uniqid(str_pad($id_comite, 3, '0', STR_PAD_LEFT) . '_' . $year . '_', true);
        $directorioDestino = ROOT . '/uploads/actas/' . '/';
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0775, true);
        }

        $extension = pathinfo($_FILES['acta']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = $folio . '.' . $extension;
        $rutaCompleta = $directorioDestino . $nombreArchivo;

        if (!move_uploaded_file($_FILES['acta']['tmp_name'], $rutaCompleta)) {
            echo json_encode(['status' => 'error', 'message' => 'Error al subir el PDF']);
            exit;
        }

        $rutaPDF = 'uploads/actas/' . '/' . $nombreArchivo;
    }

    // Guardar en la BD
    if ($rutaPDF && $id_comite) {
        $sql = "INSERT INTO comites_actas (fk_id_comite, ruta) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql);
        $ok = $stmt->execute([$id_comite, $rutaPDF]);

        if ($ok) {
            echo json_encode([
                'status' => 'ok',
                'message' => 'Acta PDF subida correctamente.',
                'ruta' => $rutaPDF
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al guardar en la base de datos'
            ]);
        }
        exit;
    }
}


if ($action === 'delete-acta') {
    $id_comite = $_POST['id_comite'] ?? null;
    $ruta = $_POST['ruta'] ?? null;

    if (!$id_comite || !$ruta) {
        echo json_encode(['status' => 'error', 'message' => 'Datos insuficientes']);
        exit;
    }

    // Elimina de la base de datos
    $sql = "DELETE FROM comites_actas WHERE fk_id_comite = ? AND ruta = ?";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([$id_comite, $ruta]);

    if ($ok) {
        // Elimina archivo físico
        $archivo = ROOT . '/' . $ruta; // Revisa ruta absoluta si lo necesitas ajustar
        if (is_file($archivo)) {
            unlink($archivo);
        }
        echo json_encode(['status' => 'ok', 'message' => 'Acta PDF eliminada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar de la base de datos']);
    }
    exit;
}


if ($action === 'eliminar-acta') {
    $id_comite = $_POST['id_comite'] ?? null;
    $ruta = $_POST['ruta'] ?? null;

    if (!$id_comite || !$ruta) {
        echo json_encode(['status' => 'error', 'message' => 'Datos insuficientes']);
        exit;
    }

    // Elimina de la base de datos
    $sql = "DELETE FROM comites_actas WHERE fk_id_comite = ? AND ruta = ?";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([$id_comite, $ruta]);

    if ($ok) {
        // Elimina archivo físico
        $archivo = ROOT . '/' . $ruta; // Asegura ruta absoluta
        if (is_file($archivo)) {
            unlink($archivo);
        }
        echo json_encode(['status' => 'ok', 'message' => 'Acta eliminada correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar de la base de datos']);
    }
    exit;
}

// ACCIÓN NO VÁLIDA
echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
exit;
