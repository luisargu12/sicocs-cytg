<?php

class SeguimientoModel
{
    public function crear()
    {
        $conexion = Database::connection();
        $origen = $_POST['origen'] ?? '';
        $year = $_POST['year'] ?? '';
        $idComite = $_POST['id_comite'] ?? '';

        if ($origen !== 'registrado' && $origen !== 'independiente') {
            $this->regresar('Elige si el seguimiento es de un comité registrado o independiente.');
        }
        if ($year === '' || !ctype_digit((string) $year)) {
            $this->regresar('Elige el año del seguimiento.');
        }
        if ($origen === 'registrado' && ($idComite === '' || !ctype_digit((string) $idComite))) {
            $this->regresar('Selecciona el folio del comité.');
        }

        $comite = null;
        if ($origen === 'registrado') {
            $stmt = $conexion->prepare('SELECT id_comite, fk_id_municipio, fk_id_colonia, fk_id_ambito, fk_id_programa, fk_id_accion, year FROM comites WHERE id_comite = ?');
            $stmt->execute([(int) $idComite]);
            $comite = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$comite) {
                $this->regresar('El comité seleccionado no existe.');
            }
            if ((string) $comite['year'] !== (string) $year) {
                $this->regresar('El comité no corresponde al año seleccionado.');
            }
        }

        $folio = $this->generarFolio($conexion, (int) $year);

        $stmt = $conexion->prepare('INSERT INTO seguimiento_comite (folio, fk_id_comite, fk_id_municipio, fk_id_colonia, fk_id_ambito, fk_id_programa, fk_id_accion, year, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)');
        $ok = $stmt->execute([
            $folio,
            $comite['id_comite'] ?? null,
            $comite['fk_id_municipio'] ?? null,
            $comite['fk_id_colonia'] ?? null,
            $comite['fk_id_ambito'] ?? null,
            $comite['fk_id_programa'] ?? null,
            $comite['fk_id_accion'] ?? null,
            (string) $year,
        ]);

        if (!$ok) {
            echo 'Error al crear el seguimiento.';
            exit;
        }

        $idSeguimiento = $conexion->lastInsertId();
        $reunion = $conexion->prepare('INSERT INTO reunion_seguimiento (fk_id_seguimiento) VALUES (?)');
        $reunion->execute([$idSeguimiento]);

        $idUsuario = (int) ($_SESSION['id'] ?? 0);
        if ($idUsuario > 0) {
            $permiso = $conexion->prepare('INSERT INTO seguimientos_usuarios (fk_id_seguimiento, fk_id_usuario) VALUES (?, ?)');
            $permiso->execute([$idSeguimiento, $idUsuario]);
        }

        header('Location: /seguimiento?id=' . $idSeguimiento);
        exit;
    }

    private function generarFolio($conexion, $year)
    {
        try {
            $conexion->beginTransaction();
            $stmt = $conexion->prepare('SELECT ultimo_seguimiento FROM comites_year WHERE year = ?');
            $stmt->execute([$year]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $stmt = $conexion->prepare('INSERT INTO comites_year (year, ultimo_insert, ultimo_seguimiento) VALUES (?, 0, 1)');
                $stmt->execute([$year]);
                $consecutivo = 1;
            } else {
                $consecutivo = (int) $row['ultimo_seguimiento'] + 1;
                $stmt = $conexion->prepare('UPDATE comites_year SET ultimo_seguimiento = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE year = ?');
                $stmt->execute([$consecutivo, $year]);
            }

            $folio = 'SG-' . str_pad((string) $consecutivo, 3, '0', STR_PAD_LEFT) . '/' . $year;
            $conexion->commit();
            return $folio;
        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            echo 'Error al generar folio: ' . $e->getMessage();
            exit;
        }
    }

    private function esAdmin()
    {
        $tipo = (int) ($_SESSION['tipo'] ?? 0);
        return $tipo === 1 || $tipo === 2;
    }

    private function faltantesParaRevision(PDO $conexion, $idSeguimiento)
    {
        $stmt = $conexion->prepare('SELECT s.fecha, r.fk_id_tipo_reunion_seg, r.modalidad, r.horario
            FROM seguimiento_comite s
            LEFT JOIN reunion_seguimiento r ON r.fk_id_seguimiento = s.id_seguimiento
            WHERE s.id_seguimiento = ?');
        $stmt->execute([$idSeguimiento]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $faltantes = [];
        if (($row['fecha'] ?? '') === '' || $row['fecha'] === '0000-00-00') {
            $faltantes[] = 'la fecha';
        }
        if (empty($row['fk_id_tipo_reunion_seg'] ?? null)) {
            $faltantes[] = 'el tipo de reunión';
        }
        if (($row['modalidad'] ?? '') === '') {
            $faltantes[] = 'la modalidad';
        }
        if (($row['horario'] ?? '') === '') {
            $faltantes[] = 'el horario';
        }
        return $faltantes;
    }

    private function mensajeFaltantes(array $faltantes)
    {
        $ultimo = array_pop($faltantes);
        $lista = $faltantes === [] ? $ultimo : implode(', ', $faltantes) . ' y ' . $ultimo;
        return 'No se puede mandar a revisión. Falta ' . $lista . '.';
    }

    private function puedeEditarOrigen(PDO $conexion, $idSeguimiento)
    {
        $stmt = $conexion->prepare('SELECT status FROM seguimiento_comite WHERE id_seguimiento = ?');
        $stmt->execute([$idSeguimiento]);
        $status = $stmt->fetchColumn();
        if ($status === false || (int) $status !== 0) {
            return false;
        }
        $asignado = $conexion->prepare('SELECT COUNT(*) FROM seguimientos_usuarios WHERE fk_id_usuario = ? AND fk_id_seguimiento = ?');
        $asignado->execute([(int) ($_SESSION['id'] ?? 0), $idSeguimiento]);
        return (int) $asignado->fetchColumn() > 0;
    }

    private function puedeSubirDocumento(PDO $conexion, $idSeguimiento)
    {
        $stmt = $conexion->prepare('SELECT status FROM seguimiento_comite WHERE id_seguimiento = ?');
        $stmt->execute([$idSeguimiento]);
        $status = $stmt->fetchColumn();
        if ($status === false) {
            return false;
        }
        $asignado = $conexion->prepare('SELECT COUNT(*) FROM seguimientos_usuarios WHERE fk_id_usuario = ? AND fk_id_seguimiento = ?');
        $asignado->execute([(int) ($_SESSION['id'] ?? 0), $idSeguimiento]);
        $estaAsignado = (int) $asignado->fetchColumn() > 0;
        $status = (int) $status;
        return ($status === 0 && $estaAsignado) || ($status === 1 && $estaAsignado && $this->esAdmin());
    }

    private function regresar($mensaje)
    {
        header('Location: /agregar-seguimiento?error=' . rawurlencode($mensaje));
        exit;
    }

    public function handle()
    {
        $conexion = Database::connection();
        header('Content-Type: application/json');
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        if ($action === 'listar') {
            $year = $_POST['year'] ?? null;
            $sql = "SELECT s.id_seguimiento, s.folio, s.fecha, s.status, m.nombre AS nombre_municipio, co.nombre AS nombre_colonia, c.folio AS folio_comite,
                    u.id_usuario, u.nombre AS nombre_usuario, u.imagen AS imagen_usuario
                FROM seguimiento_comite s
                LEFT JOIN municipios m ON s.fk_id_municipio = m.id_municipio
                LEFT JOIN colonias co ON s.fk_id_colonia = co.id_colonia
                LEFT JOIN comites c ON s.fk_id_comite = c.id_comite
                LEFT JOIN seguimientos_usuarios su ON su.fk_id_seguimiento = s.id_seguimiento
                LEFT JOIN usuarios u ON u.id_usuario = su.fk_id_usuario";
            $params = [];
            if ($year) {
                $sql .= " WHERE s.year = ?";
                $params[] = $year;
            }
            $sql .= " ORDER BY s.id_seguimiento DESC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = $row['id_seguimiento'];
                if (!isset($data[$id])) {
                    $estado = ((int) $row['status'] === 0) ? 'btn btn-danger' : (((int) $row['status'] === 1) ? 'btn btn-warning' : 'btn btn-success');
                    $data[$id] = [
                        'id_seguimiento' => $id,
                        'folio' => '<div class="' . $estado . '">' . htmlspecialchars((string) $row['folio']) . '</div>',
                        'nombre_municipio' => htmlspecialchars((string) $row['nombre_municipio']),
                        'nombre_colonia' => htmlspecialchars((string) $row['nombre_colonia']),
                        'folio_comite' => $row['folio_comite'] ? htmlspecialchars($row['folio_comite']) : 'Independiente',
                        'fecha' => htmlspecialchars((string) $row['fecha']),
                        'usuarios' => [],
                        'acciones' => '<a href="/seguimiento?id=' . (int) $id . '" class="btn boton_terciario me-3"><span class="material-icons">remove_red_eye</span></a>',
                    ];
                }
                if ($row['id_usuario'] && !isset($data[$id]['usuarios'][$row['id_usuario']])) {
                    $data[$id]['usuarios'][$row['id_usuario']] = [
                        'nombre_usuario' => htmlspecialchars((string) $row['nombre_usuario']),
                        'imagen_usuario' => htmlspecialchars((string) $row['imagen_usuario']),
                    ];
                }
            }
            $data = array_values($data);
            foreach ($data as &$seguimiento) {
                $usuariosHtml = '';
                foreach ($seguimiento['usuarios'] as $usuario) {
                    $imagen = $usuario['imagen_usuario'];
                    $ruta = ($imagen === '') ? './assets/img-user/default.png' : './assets/img-user/' . $imagen;
                    $usuariosHtml .= '<img src="' . $ruta . '" width="55" height="55" class="foto-usuario rounded-circle me-2" title="' . $usuario['nombre_usuario'] . '">';
                }
                $seguimiento['usuarios_html'] = $usuariosHtml;
                unset($seguimiento['usuarios']);
            }
            unset($seguimiento);
            echo json_encode(['data' => $data]);
            exit;
        }

        if ($action === 'ver') {
            $id = (int) ($_POST['id_seguimiento'] ?? 0);
            $sql = "SELECT s.*, r.id_reunion_comite AS id_reunion_seguimiento, r.fk_id_tipo_reunion_seg, r.modalidad, r.horario, r.beneficiarios, r.beneficiarios_m, r.beneficiarios_h, c.folio AS folio_comite
                FROM seguimiento_comite s
                LEFT JOIN reunion_seguimiento r ON r.fk_id_seguimiento = s.id_seguimiento
                LEFT JOIN comites c ON c.id_comite = s.fk_id_comite
                WHERE s.id_seguimiento = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'Seguimiento no encontrado']);
                exit;
            }
            echo json_encode(['status' => 'ok', 'data' => $data]);
            exit;
        }

        if ($action === 'editar') {
            $vacio = function ($valor) {
                return ($valor === null || $valor === '') ? null : $valor;
            };
            try {
                $id = (int) ($_POST['id_seguimiento'] ?? 0);
                $stmt = $conexion->prepare("UPDATE seguimiento_comite SET fk_id_municipio = :municipio, fk_id_colonia = :colonia, fk_id_ambito = :ambito, fk_id_programa = :programa, fk_id_accion = :accion, descripcion = :descripcion, comentarios = :comentarios, fecha = :fecha WHERE id_seguimiento = :id");
                $stmt->execute([
                    ':municipio' => $vacio($_POST['id_municipio'] ?? null),
                    ':colonia' => $vacio($_POST['id_colonia'] ?? null),
                    ':ambito' => $vacio($_POST['id_ambito'] ?? null),
                    ':programa' => $vacio($_POST['id_programa'] ?? null),
                    ':accion' => $vacio($_POST['tipo_accion'] ?? null),
                    ':descripcion' => $vacio($_POST['descripcion'] ?? null),
                    ':comentarios' => $vacio($_POST['comentarios'] ?? null),
                    ':fecha' => $vacio($_POST['fecha'] ?? null),
                    ':id' => $id,
                ]);

                $reunion = $conexion->prepare("UPDATE reunion_seguimiento SET fk_id_tipo_reunion_seg = :tipo, modalidad = :modalidad, horario = :horario, beneficiarios = :beneficiarios, beneficiarios_m = :beneficiarios_m, beneficiarios_h = :beneficiarios_h WHERE id_reunion_comite = :id");
                $reunion->execute([
                    ':tipo' => $vacio($_POST['id_tipo_reunion'] ?? null),
                    ':modalidad' => $vacio($_POST['modalidad'] ?? null),
                    ':horario' => $vacio($_POST['horario'] ?? null),
                    ':beneficiarios' => $vacio($_POST['beneficiarios'] ?? null),
                    ':beneficiarios_m' => $vacio($_POST['beneficiarios_m'] ?? null),
                    ':beneficiarios_h' => $vacio($_POST['beneficiarios_h'] ?? null),
                    ':id' => (int) ($_POST['id_reunion_seguimiento'] ?? 0),
                ]);
                echo json_encode(['status' => 'ok', 'message' => 'Seguimiento actualizado correctamente']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
        }

        if ($action === 'listar-permiso') {
            $idSeguimiento = (int) ($_POST['id_seguimiento'] ?? 0);
            $stmt = $conexion->prepare('SELECT su.id_seguimiento_usuario, u.nombre, u.apellido_paterno, u.apellido_materno
                FROM seguimientos_usuarios su
                LEFT JOIN usuarios u ON su.fk_id_usuario = u.id_usuario
                WHERE su.fk_id_seguimiento = ?');
            $stmt->execute([$idSeguimiento]);
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $nombre = htmlspecialchars(trim($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']));
                $data[] = [
                    $row['id_seguimiento_usuario'],
                    $nombre,
                    '<button type="button" class="btn boton_terciario btn-sm delete-permiso-seguimiento me-3" data-id="' . (int) $row['id_seguimiento_usuario'] . '"><span class="material-icons">delete</span></button>',
                ];
            }
            echo json_encode(['data' => $data]);
            exit;
        }

        if ($action === 'agregar-permiso') {
            if (!$this->esAdmin()) {
                echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para asignar responsables.']);
                exit;
            }
            $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
            $idSeguimiento = (int) ($_POST['id_seguimiento'] ?? 0);
            if ($idUsuario < 1 || $idSeguimiento < 1) {
                echo json_encode(['status' => 'error', 'message' => 'Selecciona un usuario.']);
                exit;
            }
            $existe = $conexion->prepare('SELECT COUNT(*) FROM seguimientos_usuarios WHERE fk_id_usuario = ? AND fk_id_seguimiento = ?');
            $existe->execute([$idUsuario, $idSeguimiento]);
            if ((int) $existe->fetchColumn() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'El usuario ya está asignado en este seguimiento.']);
                exit;
            }
            $stmt = $conexion->prepare('INSERT INTO seguimientos_usuarios (fk_id_seguimiento, fk_id_usuario) VALUES (?, ?)');
            if ($stmt->execute([$idSeguimiento, $idUsuario])) {
                echo json_encode(['status' => 'ok', 'message' => 'Usuario agregado correctamente']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al agregar usuario']);
            }
            exit;
        }

        if ($action === 'eliminar-permiso') {
            if (!$this->esAdmin()) {
                echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para quitar responsables.']);
                exit;
            }
            $id = (int) ($_POST['id_seguimiento_usuario'] ?? 0);
            if ($id < 1) {
                echo json_encode(['status' => 'error', 'message' => 'Permiso no encontrado']);
                exit;
            }
            $stmt = $conexion->prepare('DELETE FROM seguimientos_usuarios WHERE id_seguimiento_usuario = ?');
            if ($stmt->execute([$id])) {
                echo json_encode(['status' => 'ok', 'message' => 'Usuario removido correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al remover usuario.']);
            }
            exit;
        }

        if ($action === 'mostrar-doc') {
            $idSeguimiento = (int) ($_POST['id_seguimiento'] ?? 0);
            $stmt = $conexion->prepare('SELECT ruta FROM seguimiento_docs WHERE fk_id_seguimiento = ?');
            $stmt->execute([$idSeguimiento]);
            $ruta = $stmt->fetchColumn();
            if ($ruta) {
                echo json_encode(['status' => 'ok', 'ruta' => $ruta]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se ha subido el documento']);
            }
            exit;
        }

        if ($action === 'agregar-doc') {
            $idSeguimiento = (int) ($_POST['id_seguimiento'] ?? 0);
            if ($idSeguimiento < 1 || !$this->puedeSubirDocumento($conexion, $idSeguimiento)) {
                echo json_encode(['status' => 'error', 'message' => 'No puedes subir el documento de este seguimiento.']);
                exit;
            }
            $existe = $conexion->prepare('SELECT COUNT(*) FROM seguimiento_docs WHERE fk_id_seguimiento = ?');
            $existe->execute([$idSeguimiento]);
            if ((int) $existe->fetchColumn() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Este seguimiento ya tiene un documento.']);
                exit;
            }
            $archivo = $_FILES['documento'] ?? null;
            if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                echo json_encode(['status' => 'error', 'message' => 'Selecciona un archivo PDF.']);
                exit;
            }
            if ($archivo['size'] > 10 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'El archivo supera el tamaño máximo de 10MB']);
                exit;
            }
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            if ($archivo['type'] !== 'application/pdf' || $extension !== 'pdf') {
                echo json_encode(['status' => 'error', 'message' => 'Solo se permite subir archivos PDF']);
                exit;
            }
            $anio = $conexion->prepare('SELECT year FROM seguimiento_comite WHERE id_seguimiento = ?');
            $anio->execute([$idSeguimiento]);
            $year = (int) $anio->fetchColumn();
            $directorio = ROOT . '/uploads/seguimientos/';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0775, true);
            }
            $nombre = 'SG-' . uniqid(str_pad((string) $idSeguimiento, 3, '0', STR_PAD_LEFT) . '_' . $year . '_', true) . '.pdf';
            $rutaCompleta = $directorio . $nombre;
            if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                echo json_encode(['status' => 'error', 'message' => 'Error al subir el PDF']);
                exit;
            }
            $ruta = 'uploads/seguimientos/' . $nombre;
            $stmt = $conexion->prepare('INSERT INTO seguimiento_docs (fk_id_seguimiento, ruta) VALUES (?, ?)');
            if ($stmt->execute([$idSeguimiento, $ruta])) {
                echo json_encode(['status' => 'ok', 'message' => 'Documento subido correctamente.', 'ruta' => $ruta]);
            } else {
                if (is_file($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
                echo json_encode(['status' => 'error', 'message' => 'Error al guardar el documento']);
            }
            exit;
        }

        if ($action === 'eliminar-doc') {
            $idSeguimiento = (int) ($_POST['id_seguimiento'] ?? 0);
            if ($idSeguimiento < 1 || !$this->puedeSubirDocumento($conexion, $idSeguimiento)) {
                echo json_encode(['status' => 'error', 'message' => 'No puedes eliminar el documento de este seguimiento.']);
                exit;
            }
            $stmt = $conexion->prepare('SELECT ruta FROM seguimiento_docs WHERE fk_id_seguimiento = ?');
            $stmt->execute([$idSeguimiento]);
            $ruta = $stmt->fetchColumn();
            if (!$ruta) {
                echo json_encode(['status' => 'error', 'message' => 'No hay documento para eliminar']);
                exit;
            }
            $borrar = $conexion->prepare('DELETE FROM seguimiento_docs WHERE fk_id_seguimiento = ?');
            if ($borrar->execute([$idSeguimiento])) {
                $archivo = ROOT . '/' . $ruta;
                if (is_file($archivo)) {
                    unlink($archivo);
                }
                echo json_encode(['status' => 'ok', 'message' => 'Documento eliminado correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el documento']);
            }
            exit;
        }

        if ($action === 'listar-capacitadores') {
            $idReunion = (int) ($_POST['id_reunion_seguimiento'] ?? 0);
            $stmt = $conexion->prepare('SELECT sc.id_seguimiento_capacitador, u.nombre, u.apellido_paterno, u.apellido_materno, u.imagen
                FROM seguimiento_capacitadores sc
                LEFT JOIN usuarios u ON sc.fk_id_usuario = u.id_usuario
                WHERE sc.fk_id_reunion_seguimiento = ?');
            $stmt->execute([$idReunion]);
            $data = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $imagen = $row['imagen'];
                $ruta = ($imagen === null || $imagen === '') ? './assets/img-user/default.png' : './assets/img-user/' . $imagen;
                $nombre = htmlspecialchars(trim($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']));
                $data[] = [
                    $row['id_seguimiento_capacitador'],
                    '<img src="' . htmlspecialchars($ruta) . '" width="90px" class="rounded-circle me-2" title="' . $nombre . '">',
                    $nombre,
                    '<button type="button" class="btn boton_terciario btn-sm delete-capacitador me-3" data-id="' . (int) $row['id_seguimiento_capacitador'] . '"><span class="material-icons">delete</span></button>',
                ];
            }
            echo json_encode(['data' => $data]);
            exit;
        }

        if ($action === 'agregar-capacitador') {
            $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
            $idReunion = (int) ($_POST['id_reunion_seguimiento'] ?? 0);
            if ($idUsuario < 1 || $idReunion < 1) {
                echo json_encode(['status' => 'error', 'message' => 'Selecciona un capacitador.']);
                exit;
            }

            $existe = $conexion->prepare('SELECT COUNT(*) FROM seguimiento_capacitadores WHERE fk_id_usuario = ? AND fk_id_reunion_seguimiento = ?');
            $existe->execute([$idUsuario, $idReunion]);
            if ((int) $existe->fetchColumn() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'El usuario ya está asignado en este seguimiento.']);
                exit;
            }

            $stmt = $conexion->prepare('INSERT INTO seguimiento_capacitadores (fk_id_reunion_seguimiento, fk_id_usuario) VALUES (?, ?)');
            if ($stmt->execute([$idReunion, $idUsuario])) {
                echo json_encode(['status' => 'ok', 'message' => 'Capacitador agregado correctamente']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al agregar el capacitador']);
            }
            exit;
        }

        if ($action === 'eliminar-capacitador') {
            $id = (int) ($_POST['id_seguimiento_capacitador'] ?? 0);
            if ($id < 1) {
                echo json_encode(['status' => 'error', 'message' => 'Capacitador no encontrado']);
                exit;
            }
            $stmt = $conexion->prepare('DELETE FROM seguimiento_capacitadores WHERE id_seguimiento_capacitador = ?');
            if ($stmt->execute([$id])) {
                echo json_encode(['status' => 'ok', 'message' => 'Capacitador removido correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al remover el capacitador.']);
            }
            exit;
        }

        if ($action === 'editar-origen') {
            $id = (int) ($_POST['id_seguimiento'] ?? 0);
            if ($id < 1 || !$this->puedeEditarOrigen($conexion, $id)) {
                echo json_encode(['status' => 'error', 'message' => 'Solo el responsable puede cambiar el enlace mientras el seguimiento está en proceso.']);
                exit;
            }

            $origen = $_POST['origen'] ?? '';
            $year = (string) ($_POST['year'] ?? '');
            $idComite = (string) ($_POST['id_comite'] ?? '');

            if ($origen !== 'registrado' && $origen !== 'independiente') {
                echo json_encode(['status' => 'error', 'message' => 'Elige si el seguimiento es de un comité registrado o independiente.']);
                exit;
            }
            if ($year === '' || !ctype_digit($year)) {
                echo json_encode(['status' => 'error', 'message' => 'Elige el año del seguimiento.']);
                exit;
            }

            $anioExiste = $conexion->prepare('SELECT COUNT(*) FROM comites_year WHERE year = ?');
            $anioExiste->execute([(int) $year]);
            if ((int) $anioExiste->fetchColumn() < 1) {
                echo json_encode(['status' => 'error', 'message' => 'El año seleccionado no está iniciado.']);
                exit;
            }

            $comite = null;
            if ($origen === 'registrado') {
                if ($idComite === '' || !ctype_digit($idComite)) {
                    echo json_encode(['status' => 'error', 'message' => 'Selecciona el folio del comité.']);
                    exit;
                }
                $stmt = $conexion->prepare('SELECT id_comite, fk_id_municipio, fk_id_colonia, fk_id_ambito, fk_id_programa, fk_id_accion, year FROM comites WHERE id_comite = ?');
                $stmt->execute([(int) $idComite]);
                $comite = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$comite) {
                    echo json_encode(['status' => 'error', 'message' => 'El comité seleccionado no existe.']);
                    exit;
                }
                if ((string) $comite['year'] !== $year) {
                    echo json_encode(['status' => 'error', 'message' => 'El comité no corresponde al año seleccionado.']);
                    exit;
                }
            }

            $actual = $conexion->prepare('SELECT year, folio, fk_id_comite FROM seguimiento_comite WHERE id_seguimiento = ?');
            $actual->execute([$id]);
            $row = $actual->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                echo json_encode(['status' => 'error', 'message' => 'Seguimiento no encontrado']);
                exit;
            }

            $folio = $row['folio'];
            if ((string) $row['year'] !== $year) {
                $folio = $this->generarFolio($conexion, (int) $year);
            }

            try {
                if ($origen === 'registrado') {
                    $cambiaComite = (string) ($row['fk_id_comite'] ?? '') !== (string) $comite['id_comite'];
                    if ($cambiaComite) {
                        $stmt = $conexion->prepare('UPDATE seguimiento_comite SET folio = ?, year = ?, fk_id_comite = ?, fk_id_municipio = ?, fk_id_colonia = ?, fk_id_ambito = ?, fk_id_programa = ?, fk_id_accion = ? WHERE id_seguimiento = ? AND status = 0');
                        $stmt->execute([
                            $folio,
                            $year,
                            $comite['id_comite'],
                            $comite['fk_id_municipio'],
                            $comite['fk_id_colonia'],
                            $comite['fk_id_ambito'],
                            $comite['fk_id_programa'],
                            $comite['fk_id_accion'],
                            $id,
                        ]);
                    } else {
                        $stmt = $conexion->prepare('UPDATE seguimiento_comite SET folio = ?, year = ? WHERE id_seguimiento = ? AND status = 0');
                        $stmt->execute([$folio, $year, $id]);
                    }
                } else {
                    $stmt = $conexion->prepare('UPDATE seguimiento_comite SET folio = ?, year = ?, fk_id_comite = NULL WHERE id_seguimiento = ? AND status = 0');
                    $stmt->execute([$folio, $year, $id]);
                }
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el enlace del comité.']);
                exit;
            }

            $quedo = $conexion->prepare('SELECT status FROM seguimiento_comite WHERE id_seguimiento = ?');
            $quedo->execute([$id]);
            $estadoFinal = $quedo->fetchColumn();
            if ($estadoFinal === false || (int) $estadoFinal !== 0) {
                echo json_encode(['status' => 'error', 'message' => 'El seguimiento ya no está en proceso.']);
                exit;
            }

            echo json_encode(['status' => 'ok', 'message' => 'Enlace del comité actualizado.']);
            exit;
        }

        if ($action === 'cambiar-status') {
            $id = (int) ($_POST['id_seguimiento'] ?? 0);
            $status = $_POST['status'] ?? null;
            if (!in_array((string) $status, ['0', '1', '2'], true)) {
                echo json_encode(['status' => 'error', 'message' => 'Estatus no válido']);
                exit;
            }
            if ((string) $status === '1' && !$this->puedeSubirDocumento($conexion, $id)) {
                echo json_encode(['status' => 'error', 'message' => 'No puedes mandar este seguimiento a revisión.']);
                exit;
            }
            if ((string) $status === '1') {
                $faltantes = $this->faltantesParaRevision($conexion, $id);
                if ($faltantes !== []) {
                    echo json_encode(['status' => 'error', 'message' => $this->mensajeFaltantes($faltantes)]);
                    exit;
                }
            }
            if (in_array((string) $status, ['0', '2'], true) && !$this->esAdmin()) {
                echo json_encode(['status' => 'error', 'message' => 'Solo un administrador puede finalizar o liberar el seguimiento.']);
                exit;
            }
            $stmt = $conexion->prepare('UPDATE seguimiento_comite SET status = ? WHERE id_seguimiento = ?');
            $stmt->execute([(int) $status, $id]);
            echo json_encode(['status' => 'ok', 'message' => 'Estatus actualizado correctamente']);
            exit;
        }
    }
}
