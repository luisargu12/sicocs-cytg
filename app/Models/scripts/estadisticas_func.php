<?php

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// INdex
if ($action === 'index') {
    $year = (int) ($_POST['year'] ?? date('Y'));
    if ($year < 2000 || $year > 2100) {
        $year = (int) date('Y');
    }

    $stmt_comites = $conexion->prepare("SELECT COUNT(*) AS total_comites FROM comites WHERE constituido = 1 AND year = ?");
    $stmt_comites->execute([$year]);
    $total_comites = $stmt_comites->fetchColumn() ?: 0;

    $stmt_integrantes = $conexion->prepare("
    SELECT SUM(rc.integrantes) AS total_integrantes
    FROM reunion_comite rc
    JOIN comites c ON rc.fk_id_comite = c.id_comite
    WHERE c.year = ?
");
    $stmt_integrantes->execute([$year]);
    $total_integrantes = $stmt_integrantes->fetchColumn() ?: 0;

    $stmt_beneficiarios = $conexion->prepare("
    SELECT SUM(rc.beneficiarios) AS total_beneficiarios
    FROM reunion_comite rc
    JOIN comites c ON rc.fk_id_comite = c.id_comite
    WHERE c.year = ?
");
    $stmt_beneficiarios->execute([$year]);
    $total_beneficiarios = $stmt_beneficiarios->fetchColumn() ?: 0;

    $stmt_genero_integrantes = $conexion->prepare("
    SELECT
        SUM(rc.integrantes_m) AS total_mujeres,
        SUM(rc.integrantes_h) AS total_hombres
    FROM reunion_comite rc
    JOIN comites c ON rc.fk_id_comite = c.id_comite
    WHERE c.year = ?
");
    $stmt_genero_integrantes->execute([$year]);
    $result_genero_integrantes = $stmt_genero_integrantes->fetch(PDO::FETCH_ASSOC);

    $stmt_genero_beneficiarios = $conexion->prepare("
    SELECT
        SUM(rc.beneficiarios_m) AS total_mujeres,
        SUM(rc.beneficiarios_h) AS total_hombres
    FROM reunion_comite rc
    JOIN comites c ON rc.fk_id_comite = c.id_comite
    WHERE c.year = ?
");
    $stmt_genero_beneficiarios->execute([$year]);
    $result_genero_beneficiarios = $stmt_genero_beneficiarios->fetch(PDO::FETCH_ASSOC);

    $stmt_status = $conexion->prepare("
    SELECT status, COUNT(*) as total
    FROM comites
     WHERE year = ?
    GROUP BY status
");
    $stmt_status->execute([$year]);
    $result_status = $stmt_status->fetchAll(PDO::FETCH_ASSOC);
    $datos_status = [
        '0' => 0,
        '1' => 0,
        '2' => 0
    ];
    foreach ($result_status as $row) {
        $datos_status[$row['status']] = (int) $row['total'];
    }

    echo json_encode([
        'total_comites' => (int) $total_comites,
        'total_integrantes' => (int) $total_integrantes,
        'total_beneficiarios' => (int) $total_beneficiarios,
        "labels_I" => ["Integrantes Mujeres", "Integrantes Hombres"],
        "data_genero_I" => [
            (int) ($result_genero_integrantes['total_mujeres'] ?? 0),
            (int) ($result_genero_integrantes['total_hombres'] ?? 0),
        ],
        "labels_B" => ["Beneficiarios Mujeres", "Beneficiarios Hombres"],
        "data_genero_B" => [
            (int) ($result_genero_beneficiarios['total_mujeres'] ?? 0),
            (int) ($result_genero_beneficiarios['total_hombres'] ?? 0),
        ],
        "labels_status" => ["En proceso", "En revisión", "Finalizado"],
        "data_status" => [
            $datos_status['0'],
            $datos_status['1'],
            $datos_status['2']
        ]
    ]);


    exit;
}


// Info completa
if ($action === 'completa') {
    $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
    $sql = "
        SELECT
  c.id_comite AS id,
  c.folio AS folio,
  GROUP_CONCAT(DISTINCT u.nombre SEPARATOR ', ') AS usuario,
  
  CASE c.constituido WHEN 1 THEN 'Constituido' ELSE 'No Constituido' END AS estatus,
  YEAR(c.fecha) AS year,
  CASE MONTH(c.fecha)
      WHEN 1 THEN 'Enero'
      WHEN 2 THEN 'Febrero'
      WHEN 3 THEN 'Marzo'
      WHEN 4 THEN 'Abril'
      WHEN 5 THEN 'Mayo'
      WHEN 6 THEN 'Junio'
      WHEN 7 THEN 'Julio'
      WHEN 8 THEN 'Agosto'
      WHEN 9 THEN 'Septiembre'
      WHEN 10 THEN 'Octubre'
      WHEN 11 THEN 'Noviembre'
      WHEN 12 THEN 'Diciembre'
  END AS mes,
  DAY(c.fecha) AS dia,
  m.nombre AS municipio,
  col.nombre AS colonia,
  am.descripcion AS ambito,
  r.ramo AS ramo,
  r.descripcion As ramo_descripcion,
  ta.descripcion AS tipo_accion,
  c.monto_inversion AS presupuesto,
  p.descripcion AS programa,
  ie.nombre_instancia AS ie,
  IFNULL(MAX(rc.integrantes), 0) AS integrantes,
  IFNULL(MAX(rc.integrantes_m), 0) AS integrantes_m,
  IFNULL(MAX(rc.integrantes_h), 0) AS integrantes_h,
  IFNULL(MAX(rc.beneficiarios), 0) AS beneficiarios,
  IFNULL(MAX(rc.beneficiarios_m), 0) AS beneficiarios_m,
  IFNULL(MAX(rc.beneficiarios_h), 0) AS beneficiarios_h,
  SUM(CASE WHEN md.id_material = '1' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Tripticos_CS,
  SUM(CASE WHEN md.id_material = '2' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Tripticos_QyD,
  SUM(CASE WHEN md.id_material = '3' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Bolsa_Eco,
  SUM(CASE WHEN md.id_material = '4' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Playera,
  SUM(CASE WHEN md.id_material = '5' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Gorra,
  SUM(CASE WHEN md.id_material = '6' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Libreta,
  SUM(CASE WHEN md.id_material = '7' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Paraguas,
  SUM(CASE WHEN md.id_material = '8' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Vasos,
  SUM(CASE WHEN md.id_material = '9' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Plumas,
  SUM(CASE WHEN md.id_material NOT IN ('1', '2') THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS total_promocionales,
  CASE MAX(rc.modalidad) WHEN 1 THEN 'Presencial' WHEN 2 THEN 'Virtual'
END AS modalidad,
  c.comentarios AS comentarios,
  CASE MAX(c.status) WHEN 2 THEN 'Sí' WHEN 1 THEN 'No' WHEN 0 THEN 'No' END AS revision 
FROM comites c
LEFT JOIN municipios m ON c.fk_id_municipio = m.id_municipio
LEFT JOIN colonias col ON c.fk_id_colonia = col.id_colonia
LEFT JOIN ambitos am ON c.fk_id_ambito = am.id_ambito
LEFT JOIN programas p ON c.fk_id_programa = p.id_programa
LEFT JOIN ramos r ON p.fk_id_ramo = r.id_ramo
LEFT JOIN instancia_ejecutora ie ON p.fk_id_instancia_ejecutora = ie.id_instancia_ejecutora
LEFT JOIN tipo_accion ta ON c.fk_id_accion = ta.id_accion
LEFT JOIN reunion_comite rc ON c.id_comite = rc.fk_id_comite
LEFT JOIN material_difusion_entregado mde ON c.id_comite = mde.fk_id_comite
LEFT JOIN material_difusion md ON mde.fk_id_material = md.id_material
LEFT JOIN comites_usuarios cu ON c.id_comite = cu.fk_id_comite
LEFT JOIN usuarios u ON cu.fk_id_usuario = u.id_usuario
WHERE YEAR(c.fecha) = :year
GROUP BY c.id_comite, c.folio, c.constituido, c.fecha, m.nombre, col.nombre, r.ramo, ta.descripcion, c.monto_inversion, p.descripcion, ie.nombre_instancia, c.comentarios, c.status
ORDER BY c.id_comite
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // DataTables espera el array bajo la clave "data"
    echo json_encode(['data' => $data]);
    exit;
}

if ($action === 'completa-seguimientos') {
    $year = isset($_POST['year']) ? (string) intval($_POST['year']) : date('Y');
    $sql = "
        SELECT
  s.id_seguimiento AS id,
  s.folio AS folio,
  IFNULL(c.folio, 'Independiente') AS folio_comite,
  GROUP_CONCAT(DISTINCT u.nombre SEPARATOR ', ') AS usuario,
  CASE s.status WHEN 0 THEN 'En proceso' WHEN 1 THEN 'En revisión' WHEN 2 THEN 'Finalizado' END AS estatus,
  s.year AS year,
  CASE MONTH(s.fecha)
      WHEN 1 THEN 'Enero'
      WHEN 2 THEN 'Febrero'
      WHEN 3 THEN 'Marzo'
      WHEN 4 THEN 'Abril'
      WHEN 5 THEN 'Mayo'
      WHEN 6 THEN 'Junio'
      WHEN 7 THEN 'Julio'
      WHEN 8 THEN 'Agosto'
      WHEN 9 THEN 'Septiembre'
      WHEN 10 THEN 'Octubre'
      WHEN 11 THEN 'Noviembre'
      WHEN 12 THEN 'Diciembre'
  END AS mes,
  DAY(s.fecha) AS dia,
  m.nombre AS municipio,
  col.nombre AS colonia,
  am.descripcion AS ambito,
  r.ramo AS ramo,
  r.descripcion AS ramo_descripcion,
  ta.descripcion AS tipo_accion,
  p.descripcion AS programa,
  ie.nombre_instancia AS ie,
  tr.descripcion AS tipo_reunion,
  CASE rs.modalidad WHEN '1' THEN 'Presencial' WHEN '2' THEN 'En línea' WHEN '3' THEN 'Mixta' ELSE rs.modalidad END AS modalidad,
  rs.horario AS horario,
  IFNULL(rs.beneficiarios, 0) AS beneficiarios,
  IFNULL(rs.beneficiarios_m, 0) AS beneficiarios_m,
  IFNULL(rs.beneficiarios_h, 0) AS beneficiarios_h,
  s.descripcion AS descripcion,
  s.comentarios AS comentarios
FROM seguimiento_comite s
LEFT JOIN comites c ON s.fk_id_comite = c.id_comite
LEFT JOIN municipios m ON s.fk_id_municipio = m.id_municipio
LEFT JOIN colonias col ON s.fk_id_colonia = col.id_colonia
LEFT JOIN ambitos am ON s.fk_id_ambito = am.id_ambito
LEFT JOIN programas p ON s.fk_id_programa = p.id_programa
LEFT JOIN ramos r ON p.fk_id_ramo = r.id_ramo
LEFT JOIN instancia_ejecutora ie ON p.fk_id_instancia_ejecutora = ie.id_instancia_ejecutora
LEFT JOIN tipo_accion ta ON s.fk_id_accion = ta.id_accion
LEFT JOIN reunion_seguimiento rs ON rs.fk_id_seguimiento = s.id_seguimiento
LEFT JOIN tipo_reunion_seguimiento tr ON rs.fk_id_tipo_reunion_seg = tr.id_tipo_reunion_seg
LEFT JOIN seguimientos_usuarios su ON su.fk_id_seguimiento = s.id_seguimiento
LEFT JOIN usuarios u ON su.fk_id_usuario = u.id_usuario
WHERE s.year = :year
GROUP BY s.id_seguimiento, s.folio, c.folio, s.status, s.year, s.fecha, m.nombre, col.nombre, am.descripcion, r.ramo, r.descripcion, ta.descripcion, p.descripcion, ie.nombre_instancia, tr.descripcion, rs.modalidad, rs.horario, rs.beneficiarios, rs.beneficiarios_m, rs.beneficiarios_h, s.descripcion, s.comentarios
ORDER BY s.id_seguimiento
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':year', $year);
    $stmt->execute();
    echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

if ($action === 'completa-totales') {
    $year = isset($_POST['year']) ? (string) intval($_POST['year']) : date('Y');
    $sql = "
        SELECT * FROM (
            SELECT
  c.id_comite AS id,
  c.folio AS folio,
  GROUP_CONCAT(DISTINCT u.nombre SEPARATOR ', ') AS usuario,
  CASE c.constituido WHEN 1 THEN 'Constituido' ELSE 'No Constituido' END AS estatus,
  YEAR(c.fecha) AS year,
  CASE MONTH(c.fecha)
      WHEN 1 THEN 'Enero'
      WHEN 2 THEN 'Febrero'
      WHEN 3 THEN 'Marzo'
      WHEN 4 THEN 'Abril'
      WHEN 5 THEN 'Mayo'
      WHEN 6 THEN 'Junio'
      WHEN 7 THEN 'Julio'
      WHEN 8 THEN 'Agosto'
      WHEN 9 THEN 'Septiembre'
      WHEN 10 THEN 'Octubre'
      WHEN 11 THEN 'Noviembre'
      WHEN 12 THEN 'Diciembre'
  END AS mes,
  DAY(c.fecha) AS dia,
  m.nombre AS municipio,
  col.nombre AS colonia,
  am.descripcion AS ambito,
  r.ramo AS ramo,
  r.descripcion AS ramo_descripcion,
  ta.descripcion AS tipo_accion,
  c.monto_inversion AS presupuesto,
  p.descripcion AS programa,
  ie.nombre_instancia AS ie,
  IFNULL(MAX(rc.integrantes), 0) AS integrantes,
  IFNULL(MAX(rc.integrantes_m), 0) AS integrantes_m,
  IFNULL(MAX(rc.integrantes_h), 0) AS integrantes_h,
  IFNULL(MAX(rc.beneficiarios), 0) AS beneficiarios,
  IFNULL(MAX(rc.beneficiarios_m), 0) AS beneficiarios_m,
  IFNULL(MAX(rc.beneficiarios_h), 0) AS beneficiarios_h,
  SUM(CASE WHEN md.id_material = '1' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Tripticos_CS,
  SUM(CASE WHEN md.id_material = '2' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Tripticos_QyD,
  SUM(CASE WHEN md.id_material = '3' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Bolsa_Eco,
  SUM(CASE WHEN md.id_material = '4' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Playera,
  SUM(CASE WHEN md.id_material = '5' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Gorra,
  SUM(CASE WHEN md.id_material = '6' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Libreta,
  SUM(CASE WHEN md.id_material = '7' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Paraguas,
  SUM(CASE WHEN md.id_material = '8' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Vasos,
  SUM(CASE WHEN md.id_material = '9' THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS Plumas,
  SUM(CASE WHEN md.id_material NOT IN ('1', '2') THEN CAST(mde.cantidad AS UNSIGNED) ELSE 0 END) AS total_promocionales,
  CASE MAX(rc.modalidad) WHEN 1 THEN 'Presencial' WHEN 2 THEN 'Virtual' END AS modalidad,
  c.comentarios AS comentarios,
  CASE MAX(c.status) WHEN 2 THEN 'Sí' WHEN 1 THEN 'No' WHEN 0 THEN 'No' END AS revision,
  0 AS orden
FROM comites c
LEFT JOIN municipios m ON c.fk_id_municipio = m.id_municipio
LEFT JOIN colonias col ON c.fk_id_colonia = col.id_colonia
LEFT JOIN ambitos am ON c.fk_id_ambito = am.id_ambito
LEFT JOIN programas p ON c.fk_id_programa = p.id_programa
LEFT JOIN ramos r ON p.fk_id_ramo = r.id_ramo
LEFT JOIN instancia_ejecutora ie ON p.fk_id_instancia_ejecutora = ie.id_instancia_ejecutora
LEFT JOIN tipo_accion ta ON c.fk_id_accion = ta.id_accion
LEFT JOIN reunion_comite rc ON c.id_comite = rc.fk_id_comite
LEFT JOIN material_difusion_entregado mde ON c.id_comite = mde.fk_id_comite
LEFT JOIN material_difusion md ON mde.fk_id_material = md.id_material
LEFT JOIN comites_usuarios cu ON c.id_comite = cu.fk_id_comite
LEFT JOIN usuarios u ON cu.fk_id_usuario = u.id_usuario
WHERE YEAR(c.fecha) = :year
GROUP BY c.id_comite, c.folio, c.constituido, c.fecha, m.nombre, col.nombre, r.ramo, ta.descripcion, c.monto_inversion, p.descripcion, ie.nombre_instancia, c.comentarios, c.status

            UNION ALL

            SELECT
  s.id_seguimiento AS id,
  CASE WHEN c.folio IS NULL OR c.folio = '' THEN s.folio ELSE CONCAT(s.folio, ' - ', c.folio) END AS folio,
  GROUP_CONCAT(DISTINCT u.nombre SEPARATOR ', ') AS usuario,
  NULL AS estatus,
  s.year AS year,
  CASE MONTH(s.fecha)
      WHEN 1 THEN 'Enero'
      WHEN 2 THEN 'Febrero'
      WHEN 3 THEN 'Marzo'
      WHEN 4 THEN 'Abril'
      WHEN 5 THEN 'Mayo'
      WHEN 6 THEN 'Junio'
      WHEN 7 THEN 'Julio'
      WHEN 8 THEN 'Agosto'
      WHEN 9 THEN 'Septiembre'
      WHEN 10 THEN 'Octubre'
      WHEN 11 THEN 'Noviembre'
      WHEN 12 THEN 'Diciembre'
  END AS mes,
  DAY(s.fecha) AS dia,
  m.nombre AS municipio,
  col.nombre AS colonia,
  am.descripcion AS ambito,
  r.ramo AS ramo,
  r.descripcion AS ramo_descripcion,
  ta.descripcion AS tipo_accion,
  NULL AS presupuesto,
  p.descripcion AS programa,
  ie.nombre_instancia AS ie,
  NULL AS integrantes,
  NULL AS integrantes_m,
  NULL AS integrantes_h,
  IFNULL(rs.beneficiarios, 0) AS beneficiarios,
  IFNULL(rs.beneficiarios_m, 0) AS beneficiarios_m,
  IFNULL(rs.beneficiarios_h, 0) AS beneficiarios_h,
  NULL AS Tripticos_CS,
  NULL AS Tripticos_QyD,
  NULL AS Bolsa_Eco,
  NULL AS Playera,
  NULL AS Gorra,
  NULL AS Libreta,
  NULL AS Paraguas,
  NULL AS Vasos,
  NULL AS Plumas,
  NULL AS total_promocionales,
  CASE rs.modalidad WHEN '1' THEN 'Presencial' WHEN '2' THEN 'En línea' WHEN '3' THEN 'Mixta' ELSE rs.modalidad END AS modalidad,
  s.comentarios AS comentarios,
  NULL AS revision,
  1 AS orden
FROM seguimiento_comite s
LEFT JOIN comites c ON s.fk_id_comite = c.id_comite
LEFT JOIN municipios m ON s.fk_id_municipio = m.id_municipio
LEFT JOIN colonias col ON s.fk_id_colonia = col.id_colonia
LEFT JOIN ambitos am ON s.fk_id_ambito = am.id_ambito
LEFT JOIN programas p ON s.fk_id_programa = p.id_programa
LEFT JOIN ramos r ON p.fk_id_ramo = r.id_ramo
LEFT JOIN instancia_ejecutora ie ON p.fk_id_instancia_ejecutora = ie.id_instancia_ejecutora
LEFT JOIN tipo_accion ta ON s.fk_id_accion = ta.id_accion
LEFT JOIN reunion_seguimiento rs ON rs.fk_id_seguimiento = s.id_seguimiento
LEFT JOIN tipo_reunion_seguimiento tr ON rs.fk_id_tipo_reunion_seg = tr.id_tipo_reunion_seg
LEFT JOIN seguimientos_usuarios su ON su.fk_id_seguimiento = s.id_seguimiento
LEFT JOIN usuarios u ON su.fk_id_usuario = u.id_usuario
WHERE s.year = :year_seg
GROUP BY s.id_seguimiento, s.folio, c.folio, s.year, s.fecha, m.nombre, col.nombre, am.descripcion, r.ramo, r.descripcion, ta.descripcion, p.descripcion, ie.nombre_instancia, rs.modalidad, rs.beneficiarios, rs.beneficiarios_m, rs.beneficiarios_h, s.comentarios
        ) totales
        ORDER BY totales.orden, totales.folio
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bindValue(':year', $year);
    $stmt->bindValue(':year_seg', $year);
    $stmt->execute();
    echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}
