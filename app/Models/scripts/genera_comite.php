<?php

function limpiarInput($valor)
{
    return empty($valor) ? null : $valor;
}
function generarFolio($conexion, $year)
{
    if (!$year) {
        echo "Error en obtener el año";
    }
    try {
        // Iniciar transacción para evitar concurrencia
        $conexion->beginTransaction();

        // Verificar si existe el año en la tabla
        $stmt = $conexion->prepare("SELECT ultimo_insert FROM comites_year WHERE year = ?");
        $stmt->execute([$year]);
        $row = $stmt->fetch();

        if (!$row) {
            // Si no existe el año, insertarlo con consecutivo 1
            $stmt = $conexion->prepare("INSERT INTO comites_year (year, ultimo_insert) VALUES (?, 1)");
            $stmt->execute([$year]);
            $consecutivo = 1;
        } else {
            // Si existe, incrementar el consecutivo
            $consecutivo = $row['ultimo_insert'] + 1;
            $stmt = $conexion->prepare("UPDATE comites_year SET ultimo_insert = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE year = ?");
            $stmt->execute([$consecutivo, $year]);
        }

        // Generar el folio con formato AÑO-XXX
        $folio = 'CS-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT) . '/' . $year;

        $conexion->commit();
        return $folio;
    } catch (Exception $e) {
        $conexion->rollback();
        throw new Exception("Error al generar folio: " . $e->getMessage());
    }
}


$id_municipio    = $_POST['id_municipio'] ?? null;
$id_colonia = limpiarInput($_POST['id_colonia'] ?? null);
$fecha = $_POST['fecha'] ?? null;
$constituido = 0;
$year = $_POST['year'];
$id_usuario = $_SESSION['id'];
if (empty($fecha)) {
    $fecha = null;
}
$descripcion    = trim($_POST['descripcion'] ?? '');
$folio = generarFolio($conexion, $year);

// Validación básica
if ($id_municipio === '') {
    echo json_encode(['status' => 'error', 'message' => 'Error al crear comite']);
    exit;
}

$stmt = $conexion->prepare("INSERT INTO comites (folio, fk_id_municipio, fk_id_colonia, fecha, descripcion, year) VALUES (?, ?, ?, ?, ?, ?)");


if ($stmt->execute([$folio, $id_municipio, $id_colonia, $fecha, $descripcion, $year])) {
    $idComite = $conexion->lastInsertId();
    // Generar registro en la tabla reunion_comite
    $stmt2 = $conexion->prepare("INSERT INTO reunion_comite (fk_id_comite) VALUES (?)");
    if ($stmt2->execute([$idComite])) {
    } else {
        echo "Error al crear la reunión.";
    }
    $stmt3 = $conexion->prepare("INSERT INTO comites_usuarios (fk_id_comite, fk_id_usuario) VALUES (?, ?)");
    if ($stmt3->execute([$idComite, $id_usuario])) {
        // Redirigir a editar comité
        header("Location: /comite?id=$idComite");
        exit;
    } else {
        echo "Error al ligar usuario.";
    }
    exit;
} else {
    echo "Error al crear el comité.";
}
