<?php
$titulo = "SICOCS | Generar Seguimiento";
require ROOT . '/app/Views/layouts/header.php';
$error = isset($_GET['error']) ? $_GET['error'] : null;
$anioActual = (int) date('Y');
$comites = $conexion->query("SELECT c.id_comite, c.folio, c.year, m.nombre AS municipio FROM comites c LEFT JOIN municipios m ON c.fk_id_municipio = m.id_municipio ORDER BY c.year DESC, c.id_comite DESC")->fetchAll(PDO::FETCH_ASSOC);
$years = $conexion->query("SELECT year FROM comites_year ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
?>

<section class="container min-h my-5">
    <div class="mb-5">
        <div class="row">
            <div class="col-8">
                <h1 class="mb-4">Generar Seguimiento</h1>
                <div class="line"></div>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="/funciones/genera_seguimiento" method="POST" class="w-100" id="form-seguimiento">
        <div class="row">
            <div class="col-12 mt-3">
                <label class="form-label">¿El seguimiento corresponde a un comité ya registrado?</label>
                <div class="d-flex gap-4 mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="origen" id="origen-registrado" value="registrado" required>
                        <label class="form-check-label" for="origen-registrado">Sí</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="origen" id="origen-independiente" value="independiente">
                        <label class="form-check-label" for="origen-independiente">No, comité independiente</label>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-6 col-12 mt-4 d-none" id="bloque-comite">
                <label for="select-comite" class="form-label">Folio del comité:</label>
                <select id="select-comite" name="id_comite" title="Comité">
                    <option value="">Busca por folio</option>
                </select>
            </div>

            <div class="form-group col-md-2 col-12 mt-4">
                <label for="select-year" class="form-label">Año:</label>
                <select id="select-year" name="year" class="form-control" title="Año" required>
                    <?php foreach ($years as $year): ?>
                        <option value="<?php echo (int) $year; ?>" <?php echo (int) $year === $anioActual ? 'selected' : ''; ?>><?php echo (int) $year; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end mt-4">
                <button class="boton boton_primario" type="submit" id="crear">Generar Seguimiento</button>
            </div>
        </div>
    </form>
</section>

<?php
require ROOT . '/app/Views/layouts/footer.php';
?>

<script>
    $(document).ready(function () {
        var comites = <?php echo json_encode($comites, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        var comiteSelect = null;

        function llenarComites() {
            if (!comiteSelect) {
                return;
            }
            var year = String($('#select-year').val());
            var actual = comiteSelect.getValue();
            comiteSelect.clear(true);
            comiteSelect.clearOptions();
            comiteSelect.addOption({ value: '', text: 'Busca por folio' });
            comites.forEach(function (comite) {
                if (String(comite.year) !== year) {
                    return;
                }
                var texto = comite.folio || '';
                if (comite.municipio) {
                    texto += ' — ' + comite.municipio;
                }
                comiteSelect.addOption({ value: String(comite.id_comite), text: texto });
            });
            comiteSelect.refreshOptions(false);
            if (actual && comiteSelect.options[actual]) {
                comiteSelect.setValue(actual, true);
            }
        }

        function mostrarComite(visible) {
            if (visible) {
                $('#bloque-comite').removeClass('d-none');
                if (!comiteSelect) {
                    comiteSelect = $('#select-comite').selectize({
                        create: false,
                        sortField: 'text',
                        placeholder: 'Busca por folio...'
                    })[0].selectize;
                }
                llenarComites();
            } else {
                $('#bloque-comite').addClass('d-none');
                if (comiteSelect) {
                    comiteSelect.clear();
                }
            }
        }

        $('input[name="origen"]').on('change', function () {
            mostrarComite($(this).val() === 'registrado');
        });

        $('#select-year').on('change', llenarComites);

        $('#form-seguimiento').on('submit', function (e) {
            var origen = $('input[name="origen"]:checked').val();
            if (origen === 'registrado' && (!comiteSelect || !comiteSelect.getValue())) {
                e.preventDefault();
                mostrarAlerta('warning', 'Selecciona el folio del comité.');
            }
        });
    });
</script>
