<?php
if (!isset($_GET["id"])) {
    header("location: /comites");
}
$titulo = "SICOCS | Comité";
require ROOT . '/app/Views/layouts/header.php';

//Obtener id_Comite y id_reunon_comite
$id_comite = intval($_GET["id"]);
$id_usuario = $_SESSION["id"];
$tipo_usuario = $_SESSION["tipo"];

$query = $conexion->prepare("SELECT id_reunion_comite FROM reunion_comite WHERE fk_id_comite = ? ORDER BY id_reunion_comite DESC LIMIT 1");
$query->execute([$id_comite]);
$lastReunion = $query->fetch();

$query2 = $conexion->prepare("SELECT status FROM comites WHERE id_comite = ?");
$query2->execute([$id_comite]);
$status_query = $query2->fetch();


$query3 = $conexion->prepare("SELECT id_comite FROM comites WHERE status = 1 AND id_comite > ? ORDER BY id_comite ASC LIMIT 1");
$query3->execute([$id_comite]);
$siguiente = $query3->fetch(PDO::FETCH_ASSOC);

$id_comite_revisar = $siguiente ? $siguiente['id_comite'] : null;

if ($lastReunion && $status_query) {
    $id_reunion_comite = $lastReunion['id_reunion_comite'];
    $status = $status_query["status"];
    // Aquí tienes el último id_reunion_comite relacionado con ese comité
} else {
    // No hay reuniones para ese comité
}

$asignado_comite;

$query3 = $conexion->prepare("SELECT COUNT(*) FROM comites_usuarios WHERE fk_id_usuario = ? AND fk_id_comite = ?");
$query3->execute([$id_usuario, $id_comite]);
$asignado_comite = $query3->fetchColumn() > 0;
$puede_editar = false;
$display_none = "";
$type_boton = "";
$mostrar_revision = 'd-none';
$mostrar_liberar = 'd-none';
$mostrar_finalizar = 'd-none';

function puedeVerBotones($tipo_usuario, $asignado_comite)
{
    return ($tipo_usuario == 1 || $tipo_usuario == 2) || $asignado_comite;
}

switch ($status) {
    case 0:
        if (puedeVerBotones($tipo_usuario, $asignado_comite)) {
            $mostrar_revision = '';
        }
        $puede_editar = $asignado_comite;  // solo asignados pueden editar

        break;
    case 1:
        if (puedeVerBotones($tipo_usuario, $asignado_comite)) {
            if ($tipo_usuario == 1 || $tipo_usuario == 2) {
                $mostrar_liberar = '';
                $mostrar_finalizar = '';
                $type_boton = "button";
                $display_none = "d-none";
            }
            // Para otros, si están asignados no muestran botones
            elseif ($asignado_comite) {
                // En status 1, capturista asignado no ve botones
                $mostrar_revision = 'd-none';
                $mostrar_liberar = 'd-none';
                $mostrar_finalizar = 'd-none';
                $type_boton = "button";
                $display_none = "d-none";
            }
        }
        $puede_editar = $asignado_comite && ($tipo_usuario == 1 || $tipo_usuario == 2);
        break;
    case 2:
        if (puedeVerBotones($tipo_usuario, $asignado_comite)) {
            if ($tipo_usuario == 1 || $tipo_usuario == 2) {
                $mostrar_liberar = '';
                $type_boton = "button";
                $display_none = "d-none";
            }
        }
        $puede_editar = false;
        $type_boton = "button"; // nadie puede editar en status 2
        $display_none = "d-none";
        break;
    default:
        // Otros status no muestran botones ni permiten editar
        $puede_editar = false;
        $mostrar_revision = 'd-none';
        $mostrar_liberar = 'd-none';
        $mostrar_finalizar = 'd-none';
        $type_boton = "button";
        $display_none = "d-none";
        break;
}
if ($asignado_comite == 0) {
    $type_boton = "button";
    $display_none = "d-none";
} elseif ($status) {
}

$deshabilitar = $puede_editar ? '' : 'disabled';
$estatusComite = [
    0 => ['texto' => 'En proceso', 'clase' => 'badge-estatus-proceso'],
    1 => ['texto' => 'En revisión', 'clase' => 'badge-estatus-revision'],
    2 => ['texto' => 'Finalizado', 'clase' => 'badge-estatus-finalizado'],
];
$badgeEstatus = $estatusComite[(int) $status] ?? $estatusComite[0];
/*
$usuario_permiso = $usuario_permiso_query ? '' : 'disabled';
$deshabilitar = ($status == 1 && $usuario_tipo == 3) ? 'disabled' : '';
$display_none = ($status == 1 && $usuario_tipo == 3) ? 'd-none' : '';
$type_boton = ($status == 1 && $usuario_tipo == 3) ? 'button' : 'submit';
*/
?>

<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container row m-auto mb-4">
            <div class="col-6">
                <h1 class="mb-2">Comité</h1>
                <div class="line"></div>
            </div>
            <div class="col-6 d-flex align-items-center justify-content-end">
                <p class="mb-0">Folio: <b id="folio"></b></p>
            </div>
            <?php if (($tipo_usuario == 1 || $tipo_usuario == 2) && $id_comite_revisar != null) { ?>
                <div id="folio" class="col-12 d-flex justify-content-end">
                    <a class="d-flex align-items-center" href="/comite?id=<?php echo $id_comite_revisar; ?>">Siguiente comité
                        <span class="material-symbols-outlined ms-2">
                            arrow_forward
                        </span>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>


    <div class="container progressbar-wrapper">
        <div class="progress-fill" id="progress-fill"></div>
        <ul class="progressbar">
            <li class="active"></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
    </div>
    <section class="container">
        <form id="form-comite" class="w-100 mb-5" action="procesar.php" method="post">
            <div class="step active" data-step="1">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="mb-0">I. Información del Comité</h3>
                    <span class="badge-estatus <?php echo $badgeEstatus['clase']; ?>"><?php echo $badgeEstatus['texto']; ?></span>
                </div>
                <div class="line"></div>
                <div class="row">
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="select-municipio" class="form-label modal-label">Municipio:</label>
                        <select id="select-municipio" name="id_municipio" class="form-control" title="Municipio" <?php echo $deshabilitar; ?>>
                            <?php
                            $tab_municipios = $conexion->query("SELECT * from municipios");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_municipios as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="id_colonia" class="form-label">Colonia / Asentamiento:</label>
                        <select id="select-colonia" name="id_colonia" disabled <?php echo $deshabilitar; ?>>
                            <option value="">Selecciona una colonia</option>
                            <!-- Opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="fecha" class="form-label">Fecha:</label>
                        <div class="input-group">
                            <input id="fecha" name="fecha" class="form-control" type="date" <?php echo $deshabilitar; ?> /><label
                                class="input-group-text"
                                for="fecha"
                                title="Seleccionar fecha"><span class="material-icons">calendar_today</span></label>
                        </div>
                    </div>
                    <div class="form-group col-md-12 col-12 mt-3">
                        <label for="select-programa" class="form-label modal-label">Programa:</label>
                        <select id="select-programa" name="id_programa" class="form-control" title="Programa" <?php echo $deshabilitar; ?>>
                            <?php
                            $tab_programa = $conexion->query("SELECT p.*, ie.nombre_instancia AS instancia from programas p LEFT JOIN instancia_ejecutora ie ON p.fk_id_instancia_ejecutora = ie.id_instancia_ejecutora WHERE p.activo <> 0");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_programa as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . ' | ' . $value["instancia"] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-3">
                        <label for="ramo" class="form-label modal-label">Ramo/Equivalente:</label>
                        <input id="input-ramo" name="ramo" class="form-control" type="text" placeholder="Ramo/Equivalente" aria-label="Ramo/Equivalente" readonly <?php echo $deshabilitar; ?> />
                        <input type="hidden" id="input-ramo-id" name="id_ramo" <?php echo $deshabilitar; ?>>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-3">
                        <label for="instancia" class="form-label">Instancia ejecutora:</label>
                        <input id="input-instancia" name="instancia" class="form-control" type="text" placeholder="Nombre de la instancia" aria-label="instancia" readonly <?php echo $deshabilitar; ?> />
                        <input type="hidden" id="input-instancia-id" name="id_instancia_ejecutora" <?php echo $deshabilitar; ?>>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-3">
                        <label for="select-ambito" class="form-label modal-label">Ambito:</label>
                        <select id="select-ambito" name="id_ambito" class="form-control" title="Ambito" <?php echo $deshabilitar; ?>>
                            <?php
                            $tab_ambitos = $conexion->query("SELECT * from ambitos");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_ambitos as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-3">
                        <label for="select-tipo_accion" class="form-label modal-label">Tipo de acción:</label>
                        <select id="select-tipo_accion" name="tipo_accion" class="form-control" title="Tipo de Acción" <?php echo $deshabilitar; ?>>
                            <option value="">Seleccione primero un ámbito</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="inversion" class="form-label">Monto de Inversión:</label>
                        <input id="inversion" name="inversion" class="form-control" type="hidden" />
                        <input type="text" id="inversionFormat" data-type="currency" class="form-control" placeholder="Monto" aria-label="Inversión" <?php echo $deshabilitar; ?> />
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="proveedor" class="form-label">Proovedor Adjudicado:</label>
                        <input id="proveedor" name="proveedor" class="form-control" type="text" placeholder="Nombre del proveedor" aria-label="proveedor" <?php echo $deshabilitar; ?> />
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label class="form-label modal-label">Constituido:</label>
                        <select name="constituido" class="form-control" title="Tipo de Acción" <?php echo $deshabilitar; ?>>
                            <option value="1">Si</option>
                            <option default value="0">No</option>
                        </select>
                    </div>
                    <div class="form-group col-6 mt-3">
                        <div class="form-group">
                            <label for="descripcion" class="form-label mb-2">Descripción específica:</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" style="height: 150px;"
                               maxlength="1000" <?php echo $deshabilitar; ?>></textarea>
                        </div>
                    </div>
                    <div class="form-group col-6 mt-3">
                        <div class="form-group">
                            <label for="comentarios" class="form-label mb-2">Comentarios:</label>
                            <textarea id="comentarios" name="comentarios" class="form-control" style="height: 150px;"
                                maxlength="755" <?php echo $deshabilitar; ?>></textarea>
                        </div>
                    </div>
                </div>


            </div>

            <div class="step" data-step="2">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="mb-0">II. Reunión del comité</h3>
                    <span class="badge-estatus <?php echo $badgeEstatus['clase']; ?>"><?php echo $badgeEstatus['texto']; ?></span>
                </div>
                <div class="line"></div>
                <div class="row">
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="select-reunion" class="form-label modal-label">Tipo de reunión:</label>
                        <select id="select-reunion" name="id_tipo_reunion" class="form-control" title="Tipo de reunion" <?php echo $deshabilitar; ?>>
                            <?php
                            $tab_tipo_reunion = $conexion->query("SELECT * from tipo_reunion_comite WHERE activo <> 0");
                            echo ('<option value="">Seleccion el tipo de reunión</option>');
                            foreach ($tab_tipo_reunion as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="select-modalidad" class="form-label modal-label">Modalidad:</label>
                        <select id="select-modalidad" name="modalidad" class="form-control" title="Modalidad" <?php echo $deshabilitar; ?>>
                            <option value="">Seleccione una modalidad</option>
                            <option value="1">Presencial</option>
                            <option value="2">En linea</option>
                            <option value="3">Mixta</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 col-12 mt-3">
                        <label for="horario" class="form-label">Horario:</label>
                        <div class="input-group">
                            <input id="horario" name="horario" class="form-control" type="time" <?php echo $deshabilitar; ?> /><label
                                class="input-group-text"
                                for="horario"
                                title="Seleccionar el horario"><span class="material-icons">timer</span></label>
                        </div>
                    </div>
                    <div class="col-12 mt-3 mb-0">
                        <h4>Integrantes del comité</h4>
                        <div class="row mx-0 pb-3 tab-content">
                            <div class="form-group col-md-4 col-12 mt-0">
                                <label for="integrantes_m" class="form-label">Mujeres:</label>
                                <input id="integrantes_m" name="integrantes_m" class="form-control" type="number" placeholder="Integrantes mujeres" aria-label="integrantes_m" <?php echo $deshabilitar; ?> />
                            </div>
                            <div class="form-group col-md-4 col-12 mt-0">
                                <label for="integrantes_h" class="form-label">Hombres:</label>
                                <input id="integrantes_h" name="integrantes_h" class="form-control" type="number" placeholder="Integrantes hombres" aria-label="integrantes_h" <?php echo $deshabilitar; ?> />
                            </div>
                            <div class="form-group col-md-4 col-12 mt-0">
                                <label for="integrantes" class="form-label">Total de Integrantes:</label>
                                <input id="integrantes" name="integrantes" class="form-control" type="number" placeholder="Total de integrantes" aria-label="integrantes" readonly <?php echo $deshabilitar; ?> />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-3 mb-0">
                        <h4>Beneficiarios del comité</h4>
                        <div class="row mx-0 pb-3 tab-content">
                            <div class="form-group col-md-4 col-12 mt-3">
                                <label for="beneficiarios_m" class="form-label">Beneficiarios Mujeres:</label>
                                <input id="beneficiarios_m" name="beneficiarios_m" class="form-control" type="number" placeholder="Beneficiarios mujeres" aria-label="beneficiarios-m" <?php echo $deshabilitar; ?> />
                            </div>
                            <div class="form-group col-md-4 col-12 mt-3">
                                <label for="beneficiarios-h" class="form-label">Beneficiarios Hombres:</label>
                                <input id="beneficiarios_h" name="beneficiarios_h" class="form-control" type="number" placeholder="Beneficiarios hombres" aria-label="beneficiarios-h" <?php echo $deshabilitar; ?> />
                            </div>
                            <div class="form-group col-md-4 col-12 mt-3">
                                <label for="beneficiarios" class="form-label">Total de Beneficiarios:</label>
                                <input id="beneficiarios" name="beneficiarios" class="form-control" type="number" placeholder="Total de beneficiarios" aria-label="beneficiarios" readonly <?php echo $deshabilitar; ?> />
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-12 mt-3">
                        <h4 class="mt-3 mb-3">Capacitadores CS:</h4>
                        <div class="tab-content p-4">
                            <?php require ROOT . '/app/Views/snippets/reunion_usuario.php'; ?>
                        </div>

                    </div>
                </div>
            </div>

            <div class="step" data-step="3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="mb-0">III. Difusión Contraloría Social y Sistema de Atención a Quejas y Denuncias</h3>
                    <span class="badge-estatus <?php echo $badgeEstatus['clase']; ?>"><?php echo $badgeEstatus['texto']; ?></span>
                </div>
                <div class="line"></div>
                <div class="row">
                    <?php require ROOT . '/app/Views/snippets/material-difusion.php'; ?>
                </div>
            </div>

            <div class="step" data-step="4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="mb-0">IV. Evidencias fotográficas</h3>
                    <span class="badge-estatus <?php echo $badgeEstatus['clase']; ?>"><?php echo $badgeEstatus['texto']; ?></span>
                </div>
                <div class="line"></div>
                <div class="row mt-3">
                    <?php require ROOT . '/app/Views/snippets/subir-imagenes.php'; ?>
                </div>
            </div>

            <div class="step" data-step="5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h3 class="mb-0">V. Subir acta escaneada</h3>
                    <span class="badge-estatus <?php echo $badgeEstatus['clase']; ?>"><?php echo $badgeEstatus['texto']; ?></span>
                </div>
                <div class="line"></div>
                <div class="row mt-3">
                    <?php require ROOT . '/app/Views/snippets/subir-acta.php'; ?>
                </div>
            </div>

            <div class="botones row justify-content-between mt-5">
                <div class="col-6 d-flex align-items-center">
                    <div>
                        <?php
                        if ($tipo_usuario  == 1 || $tipo_usuario  == 2) {
                            require ROOT . '/app/Views/snippets/permiso_usuario.php';
                        }
                        ?>
                    </div>
                    <div id="submitBtn" class="ms-3">
                        <button class="btn btn-warning <?php echo $mostrar_revision; ?>" type="submit" id="btnRevision" data-status="1">Mandar a Revisión</button>
                        <button class="btn btn-success <?php echo $mostrar_finalizar; ?>" type="submit" id="btnFinalizar" data-status="2">Finalizar</button>
                        <button class="btn btn-danger <?php echo $mostrar_liberar; ?>" type="submit" id="btnLiberar" data-status="0">Liberar</button>
                    </div>

                </div>
                <div class="col-6 d-flex justify-content-end">
                    <button class="boton boton_secundario" type="button" id="prevBtn">Atrás</button>
                    <button class="boton boton_terciario" id="nextBtn" type="<?php echo $type_boton; ?>">Siguiente</button>
                </div>
            </div>
        </form>
    </section>
    <div id="loading-overlay" style="display:none;">
        <div class="spinner"></div>
    </div>
</section>




<?php
require ROOT . '/app/Views/layouts/footer.php';
?>


<script type="text/javascript" charset="utf-8">
    function mostrarLoading() {
        $('#loading-overlay').show();
    }

    function ocultarLoading() {
        $('#loading-overlay').hide();
    }

    var id_comite = <?php echo json_encode($id_comite); ?>;
    var id_reunion_comite = <?php echo json_encode($id_reunion_comite); ?>;
    var statusComite = <?php echo json_encode($status); ?>;

    // Función que suma y actualiza totales
    function actualizarTotales() {
        // Obtener valores como números, si no hay valor se usa 0
        const integrantesM = parseInt(document.getElementById('integrantes_m').value) || 0;
        const integrantesH = parseInt(document.getElementById('integrantes_h').value) || 0;
        const beneficiariosM = parseInt(document.getElementById('beneficiarios_m').value) || 0;
        const beneficiariosH = parseInt(document.getElementById('beneficiarios_h').value) || 0;

        // Sumar los valores
        const totalIntegrantes = integrantesM + integrantesH;
        const totalBeneficiarios = beneficiariosM + beneficiariosH;

        // Actualizar los campos de totales
        document.getElementById('integrantes').value = totalIntegrantes;
        document.getElementById('beneficiarios').value = totalBeneficiarios;
    }

    // Añadir event listeners para ejecutar la suma al cambiar los inputs
    document.getElementById('integrantes_m').addEventListener('input', actualizarTotales);
    document.getElementById('integrantes_h').addEventListener('input', actualizarTotales);
    document.getElementById('beneficiarios_m').addEventListener('input', actualizarTotales);
    document.getElementById('beneficiarios_h').addEventListener('input', actualizarTotales);
</script>
<script type="text/javascript" charset="utf8" src="./assets/js/comite.js"></script>
<script type="text/javascript" charset="utf8" src="./assets/js/comite-material.js"></script>
<script type="text/javascript" charset="utf8" src="./assets/js/comite-uploads.js"></script>