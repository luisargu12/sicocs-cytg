<?php
if (!isset($_GET["id"])) {
    header("location: /seguimientos");
    exit;
}

$conexion = Database::connection();
$id_seguimiento = (int) $_GET["id"];

$reunion = $conexion->prepare("SELECT id_reunion_comite FROM reunion_seguimiento WHERE fk_id_seguimiento = ? ORDER BY id_reunion_comite DESC LIMIT 1");
$reunion->execute([$id_seguimiento]);
$lastReunion = $reunion->fetch(PDO::FETCH_ASSOC);

$estado = $conexion->prepare("SELECT status, folio, fk_id_comite, year FROM seguimiento_comite WHERE id_seguimiento = ?");
$estado->execute([$id_seguimiento]);
$seguimiento = $estado->fetch(PDO::FETCH_ASSOC);
if (!$seguimiento || !$lastReunion) {
    header("location: /seguimientos");
    exit;
}

$titulo = "SICOCS | Seguimiento";
require ROOT . '/app/Views/layouts/header.php';

$tipo_usuario = $_SESSION["tipo"];

$id_reunion_seguimiento = $lastReunion['id_reunion_comite'];
$status = (int) $seguimiento['status'];
$comiteLigado = $conexion->prepare("SELECT folio FROM comites WHERE id_comite = ?");
$comiteLigado->execute([(int) $seguimiento['fk_id_comite']]);
$folioComite = $seguimiento['fk_id_comite'] ? $comiteLigado->fetchColumn() : null;

$asignado = $conexion->prepare('SELECT COUNT(*) FROM seguimientos_usuarios WHERE fk_id_usuario = ? AND fk_id_seguimiento = ?');
$asignado->execute([(int) $_SESSION['id'], $id_seguimiento]);
$estaAsignado = (int) $asignado->fetchColumn() > 0;
$esAdmin = (int) $tipo_usuario === 1 || (int) $tipo_usuario === 2;
$puede_editar = ($status === 0 && $estaAsignado) || ($status === 1 && $estaAsignado && $esAdmin);
$puede_editar_origen = $status === 0 && $estaAsignado;
$deshabilitar = $puede_editar ? '' : 'disabled';
$puede_revisar = $status === 0 && $estaAsignado;
$puede_finalizar = $status === 1 && $esAdmin;
$puede_liberar = $status !== 0 && $esAdmin;
$tipo_siguiente = $puede_editar ? 'submit' : 'button';
$estatusSeguimiento = [
    0 => ['texto' => 'En proceso', 'clase' => 'badge-estatus-proceso'],
    1 => ['texto' => 'En revisión', 'clase' => 'badge-estatus-revision'],
    2 => ['texto' => 'Finalizado', 'clase' => 'badge-estatus-finalizado'],
];
$badgeEstatus = $estatusSeguimiento[$status] ?? $estatusSeguimiento[0];
?>

<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container row m-auto mb-4">
            <div class="col-6">
                <h1 class="mb-2">Seguimiento</h1>
                <div class="line"></div>
            </div>
            <div class="col-6 d-flex align-items-center justify-content-end">
                <p class="mb-0">Folio: <b id="folio"><?php echo htmlspecialchars($seguimiento['folio']); ?></b></p>
            </div>
            <div class="col-12 d-flex flex-column align-items-end">
                <?php if ($folioComite): ?>
                    <p class="mb-0">Comité: <a href="/comite?id=<?php echo (int) $seguimiento['fk_id_comite']; ?>" target="_blank" rel="noopener"><b><?php echo htmlspecialchars($folioComite); ?></b></a></p>
                <?php else: ?>
                    <p class="mb-0">Comité independiente</p>
                <?php endif; ?>
                <?php if ($puede_editar_origen): ?>
                    <span class="editar-origen" role="button" data-bs-toggle="modal" data-bs-target="#modalOrigenSeguimiento">editar</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container progressbar-wrapper">
        <div class="progress-fill" id="progress-fill"></div>
        <ul class="progressbar">
            <li class="active"></li>
            <li></li>
            <li></li>
        </ul>
    </div>
    <section class="container">
        <form id="form-seguimiento-editar" class="w-100 mb-5" method="post">
            <div class="step active" data-step="1">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="mb-0">I. Información del seguimiento</h3>
                    <span class="badge-estatus <?php echo $badgeEstatus['clase']; ?>"><?php echo $badgeEstatus['texto']; ?></span>
                </div>
                <div class="line"></div>
                <div class="row">
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="select-municipio" class="form-label">Municipio:</label>
                        <select id="select-municipio" name="id_municipio" class="form-control" <?php echo $deshabilitar; ?>>
                            <?php
                            $tab_municipios = $conexion->query("SELECT * from municipios");
                            echo '<option value="">Seleccionar Opción</option>';
                            foreach ($tab_municipios as $value) {
                                echo '<option value="' . (int) $value[0] . '">' . htmlspecialchars($value[1]) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="select-colonia" class="form-label">Colonia / Asentamiento:</label>
                        <select id="select-colonia" name="id_colonia" <?php echo $deshabilitar; ?>>
                            <option value="">Selecciona una colonia</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="fecha" class="form-label">Fecha:</label>
                        <div class="input-group">
                            <input id="fecha" name="fecha" class="form-control" type="date" <?php echo $deshabilitar; ?> />
                            <label class="input-group-text" for="fecha"><span class="material-icons">calendar_today</span></label>
                        </div>
                    </div>
                    <div class="form-group col-md-12 col-12 mt-3">
                        <label for="select-programa" class="form-label">Programa:</label>
                        <select id="select-programa" name="id_programa" class="form-control" <?php echo $deshabilitar; ?>>
                            <?php
                            $tab_programa = $conexion->query("SELECT p.*, ie.nombre_instancia AS instancia from programas p LEFT JOIN instancia_ejecutora ie ON p.fk_id_instancia_ejecutora = ie.id_instancia_ejecutora WHERE p.activo <> 0");
                            echo '<option value="">Seleccionar Opción</option>';
                            foreach ($tab_programa as $value) {
                                echo '<option value="' . (int) $value[0] . '">' . htmlspecialchars($value[1] . ' | ' . $value['instancia']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-3">
                        <label for="input-ramo" class="form-label">Ramo/Equivalente:</label>
                        <input id="input-ramo" class="form-control" type="text" readonly <?php echo $deshabilitar; ?> />
                    </div>
                    <div class="form-group col-md-6 col-12 mt-3">
                        <label for="input-instancia" class="form-label">Instancia ejecutora:</label>
                        <input id="input-instancia" class="form-control" type="text" readonly <?php echo $deshabilitar; ?> />
                    </div>
                    <div class="form-group col-md-6 col-12 mt-3">
                        <label for="select-ambito" class="form-label">Ámbito:</label>
                        <select id="select-ambito" name="id_ambito" class="form-control" <?php echo $deshabilitar; ?>>
                            <?php
                            $tab_ambitos = $conexion->query("SELECT * from ambitos");
                            echo '<option value="">Seleccionar Opción</option>';
                            foreach ($tab_ambitos as $value) {
                                echo '<option value="' . (int) $value[0] . '">' . htmlspecialchars($value[1]) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-3">
                        <label for="select-tipo_accion" class="form-label">Tipo de acción:</label>
                        <select id="select-tipo_accion" name="tipo_accion" class="form-control" <?php echo $deshabilitar; ?>>
                            <option value="">Seleccione primero un ámbito</option>
                        </select>
                    </div>
                    <div class="form-group col-6 mt-3">
                        <label for="descripcion" class="form-label mb-2">Descripción específica:</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" style="height: 150px;" maxlength="1000" <?php echo $deshabilitar; ?>></textarea>
                    </div>
                    <div class="form-group col-6 mt-3">
                        <label for="comentarios" class="form-label mb-2">Comentarios:</label>
                        <textarea id="comentarios" name="comentarios" class="form-control" style="height: 150px;" maxlength="755" <?php echo $deshabilitar; ?>></textarea>
                    </div>
                </div>
            </div>

            <div class="step" data-step="2">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="mb-0">II. Reunión del seguimiento</h3>
                    <span class="badge-estatus <?php echo $badgeEstatus['clase']; ?>"><?php echo $badgeEstatus['texto']; ?></span>
                </div>
                <div class="line"></div>
                <div class="row">
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="select-reunion" class="form-label">Tipo de reunión:</label>
                        <select id="select-reunion" name="id_tipo_reunion" class="form-control" <?php echo $deshabilitar; ?>>
                            <?php
                            $tab_tipo_reunion = $conexion->query("SELECT * from tipo_reunion_seguimiento WHERE activo <> 0");
                            echo '<option value="">Selecciona el tipo de reunión</option>';
                            foreach ($tab_tipo_reunion as $value) {
                                echo '<option value="' . (int) $value['id_tipo_reunion_seg'] . '">' . htmlspecialchars($value['descripcion']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="select-modalidad" class="form-label">Modalidad:</label>
                        <select id="select-modalidad" name="modalidad" class="form-control" <?php echo $deshabilitar; ?>>
                            <option value="">Seleccione una modalidad</option>
                            <option value="1">Presencial</option>
                            <option value="2">En linea</option>
                            <option value="3">Mixta</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="horario" class="form-label">Horario:</label>
                        <div class="input-group">
                            <input id="horario" name="horario" class="form-control" type="time" <?php echo $deshabilitar; ?> />
                            <label class="input-group-text" for="horario"><span class="material-icons">timer</span></label>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <h4>Beneficiarios</h4>
                        <div class="row mx-0 pb-3">
                            <div class="form-group col-md-4 col-12">
                                <label for="beneficiarios_m" class="form-label">Mujeres:</label>
                                <input id="beneficiarios_m" name="beneficiarios_m" class="form-control" type="number" <?php echo $deshabilitar; ?> />
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label for="beneficiarios_h" class="form-label">Hombres:</label>
                                <input id="beneficiarios_h" name="beneficiarios_h" class="form-control" type="number" <?php echo $deshabilitar; ?> />
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label for="beneficiarios" class="form-label">Total:</label>
                                <input id="beneficiarios" name="beneficiarios" class="form-control" type="number" readonly <?php echo $deshabilitar; ?> />
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 mt-3">
                        <h4 class="mt-3 mb-3">Capacitadores CS:</h4>
                        <div class="tab-content p-4">
                            <section id="capacitadores-seguimiento-form">
                                <div class="row <?php echo $puede_editar ? '' : 'd-none'; ?>">
                                    <div class="col-8">
                                        <select id="select-usuario-capacitador" class="form-control" title="Usuario">
                                            <?php
                                            $tab_usuarios = $conexion->query("SELECT id_usuario, nombre, apellido_paterno, apellido_materno FROM usuarios WHERE id_usuario <> 1 AND activo != 0");
                                            echo '<option value="">Seleccionar Usuario</option>';
                                            foreach ($tab_usuarios as $value) {
                                                echo '<option value="' . (int) $value['id_usuario'] . '">' . htmlspecialchars($value['nombre'] . ' ' . $value['apellido_paterno'] . ' ' . $value['apellido_materno']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-4 d-flex justify-content-end">
                                        <button type="button" id="btn-agregar-capacitador" class="boton boton_terciario">Agregar</button>
                                    </div>
                                </div>
                            </section>
                            <div id="capacitadores-seguimiento" class="my-5 container">
                                <table id="tabla_capacitadores" style="width:100%" class="mx-auto display estilo_tabla">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Imagen</th>
                                            <th>Nombre</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step" data-step="3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="mb-0">III. Lista de Asistencia y Cédula de Seguimiento</h3>
                    <span class="badge-estatus <?php echo $badgeEstatus['clase']; ?>"><?php echo $badgeEstatus['texto']; ?></span>
                </div>
                <div class="line"></div>
                <div class="row mt-4">
                    <div id="doc-seguimiento" class="col-12 my-3 <?php echo $puede_editar ? '' : 'd-none'; ?>">
                        <label class="col-form-label" for="documento-seguimiento">Seleccione el documento PDF:</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="documento-seguimiento" accept=".pdf" <?php echo $deshabilitar; ?>>
                            <label class="input-group-text" for="documento-seguimiento" title="Subir archivo">
                                <span class="material-icons">cloud_upload</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="documento-preview" class="tab-content" style="padding: 32px;">
                            No se ha subido el documento
                        </div>
                    </div>
                </div>
            </div>

            <div class="botones row justify-content-between mt-5">
                <div class="col-6 d-flex align-items-center">
                    <?php if ($esAdmin): ?>
                        <button type="button" class="btn boton_terciario me-3" data-bs-toggle="modal" data-bs-target="#modalPermisoSeguimiento">
                            <span class="material-icons">manage_accounts</span>
                        </button>
                    <?php endif; ?>
                    <?php if ($puede_revisar): ?>
                        <button class="btn btn-warning d-none" type="button" id="btnRevision">Mandar a Revisión</button>
                    <?php endif; ?>
                    <?php if ($puede_finalizar): ?>
                        <button class="btn btn-success d-none me-2" type="button" id="btnFinalizar">Finalizar</button>
                    <?php endif; ?>
                    <?php if ($puede_liberar): ?>
                        <button class="btn btn-danger d-none" type="button" id="btnLiberar">Liberar</button>
                    <?php endif; ?>
                </div>
                <div class="col-6 d-flex justify-content-end">
                    <button class="boton boton_secundario" type="button" id="prevBtn">Atrás</button>
                    <button class="boton boton_terciario" id="nextBtn" type="<?php echo $tipo_siguiente; ?>">Siguiente</button>
                </div>
            </div>
        </form>
    </section>
    <div id="loading-overlay" style="display:none;">
        <div class="spinner"></div>
    </div>
    <?php if ($esAdmin): ?>
        <div class="modal fade" id="modalPermisoSeguimiento" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="mb-3">
                            <h3 class="modal-title">Usuarios Responsable</h3>
                            <div class="line"></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-8">
                                <select id="select-usuario-permiso" class="form-control" title="Usuario">
                                    <?php
                                    $tab_usuarios_permiso = $conexion->query('SELECT id_usuario, nombre, apellido_paterno, apellido_materno FROM usuarios WHERE id_usuario <> 1 AND activo != 0');
                                    echo '<option value="">Seleccionar Usuario</option>';
                                    foreach ($tab_usuarios_permiso as $value) {
                                        echo '<option value="' . (int) $value['id_usuario'] . '">' . htmlspecialchars($value['nombre'] . ' ' . $value['apellido_paterno'] . ' ' . $value['apellido_materno']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-4 d-flex justify-content-end">
                                <button type="button" id="btn-agregar-permiso-seguimiento" class="boton boton_terciario">Agregar</button>
                            </div>
                        </div>
                        <div id="usuarios-permiso-seguimiento" class="my-5 container">
                            <table id="tabla_permiso_seguimiento" style="width:100%" class="mx-auto display estilo_tabla">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($puede_editar_origen):
        $comitesOrigen = $conexion->query("SELECT c.id_comite, c.folio, c.year, m.nombre AS municipio FROM comites c LEFT JOIN municipios m ON c.fk_id_municipio = m.id_municipio ORDER BY c.year DESC, c.id_comite DESC")->fetchAll(PDO::FETCH_ASSOC);
        $yearsOrigen = $conexion->query("SELECT year FROM comites_year ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
        $anioSeguimiento = (string) $seguimiento['year'];
        $yearsTexto = array_map('strval', $yearsOrigen);
        if (!in_array($anioSeguimiento, $yearsTexto, true)) {
            array_unshift($yearsOrigen, $anioSeguimiento);
        }
    ?>
        <div class="modal fade" id="modalOrigenSeguimiento" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="mb-3">
                            <h3 class="modal-title">Enlace del comité</h3>
                            <div class="line"></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form id="form-origen-seguimiento">
                        <div class="modal-body">
                            <label class="form-label">¿El seguimiento corresponde a un comité ya registrado?</label>
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="origen" id="origen-registrado-modal" value="registrado" required>
                                    <label class="form-check-label" for="origen-registrado-modal">Sí</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="origen" id="origen-independiente-modal" value="independiente">
                                    <label class="form-check-label" for="origen-independiente-modal">No, comité independiente</label>
                                </div>
                            </div>
                            <div class="row d-none" id="bloque-comite-origen">
                                <div class="form-group col-md-8 col-12 mt-4">
                                    <label for="select-comite-origen" class="form-label">Folio del comité:</label>
                                    <select id="select-comite-origen" title="Comité">
                                        <option value="">Busca por folio</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-12 mt-4">
                                    <label for="select-year-origen" class="form-label">Año:</label>
                                    <select id="select-year-origen" class="form-control" title="Año">
                                        <?php foreach ($yearsOrigen as $year): ?>
                                            <option value="<?php echo (int) $year; ?>" <?php echo (string) $year === $anioSeguimiento ? 'selected' : ''; ?>><?php echo (int) $year; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="boton boton_primario">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php
require ROOT . '/app/Views/layouts/footer.php';
?>
<script>
    function mostrarLoading() {
        $('#loading-overlay').show();
    }
    function ocultarLoading() {
        $('#loading-overlay').hide();
    }
    var id_seguimiento = <?php echo json_encode($id_seguimiento); ?>;
    var id_reunion_seguimiento = <?php echo json_encode($id_reunion_seguimiento); ?>;
    var puedeEditarSeguimiento = <?php echo $puede_editar ? 'true' : 'false'; ?>;
    var puedeEditarOrigen = <?php echo $puede_editar_origen ? 'true' : 'false'; ?>;
    var comitesOrigen = <?php echo $puede_editar_origen ? json_encode($comitesOrigen, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : '[]'; ?>;
    var anioSeguimiento = <?php echo json_encode((string) $seguimiento['year']); ?>;
    var comiteSeguimiento = <?php echo json_encode($seguimiento['fk_id_comite'] ? (string) (int) $seguimiento['fk_id_comite'] : ''); ?>;

    function actualizarTotales() {
        var beneficiariosM = parseInt(document.getElementById('beneficiarios_m').value, 10) || 0;
        var beneficiariosH = parseInt(document.getElementById('beneficiarios_h').value, 10) || 0;
        document.getElementById('beneficiarios').value = beneficiariosM + beneficiariosH;
    }
    document.getElementById('beneficiarios_m').addEventListener('input', actualizarTotales);
    document.getElementById('beneficiarios_h').addEventListener('input', actualizarTotales);
</script>
<script src="./assets/js/seguimiento.js"></script>
