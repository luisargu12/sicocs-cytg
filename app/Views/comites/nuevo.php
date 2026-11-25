<?php
$titulo = "SICOCS | Agregar Comité";
require ROOT . '/app/Views/layouts/header.php';
/*
if (!isset($_SESSION["loggedin"]) || $_SESSION["tipo"] != 1) {
    header("location: /");
    exit;
}
    */
?>

<section class="container min-h my-5 ">
    <div class="mb-5">
        <div class="row">
            <div class="col-6">
                <h1 class="mb-4">Agregar Comité</h1>
                <div class="line"></div>
            </div>
        </div>

    </div>

    <form action="/funciones/genera_comite" method="POST" class="w-100">
        <div class="row">
            <div class="form-group col-md-4 col-12 mt-3">
                <label class="form-label modal-label">Municipio:</label>
                <select id="select-municipio" name="id_municipio" class="form-control" title="Municipio" required>
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
                <select id="select-colonia" name="id_colonia" disabled>
                    <option value="">Selecciona una colonia</option>
                    <!-- Opciones se cargarán dinámicamente -->
                </select>
            </div>
            <div class="form-group col-md-4 col-12 mt-3">
                <label for="fecha" class="form-label">Fecha:</label>
                <div class="input-group">
                    <input id="fecha" name="fecha" class="form-control" type="date" /><label class="input-group-text"
                        for="fecha" title="Seleccionar fecha"><span class="material-icons">calendar_today</span></label>
                </div>
            </div>
            <div class="form-group col-12 mt-3">
                <div class="form-group">
                    <label for="descripcion" class="form-label mb-2">Descripción específica:</label>
                    <textarea class="form-control" style="height: 150px;" name="descripcion"
                        maxlength="1000"></textarea>
                </div>
            </div>

            <div class="form-group col-2 mt-4">
                <label class="form-label modal-label">Año:</label>
                <select id="select-year" name="year" class="form-control" title="Año" required>
                    <?php
                    $tab_years = $conexion->query("SELECT * from comites_year ORDER BY year DESC");
                    foreach ($tab_years as $key => $value) {
                        echo ('<option value=' . $value[1] . '>' . $value[1] . '</option>');
                    }
                    ?>
                </select>
            </div>


            <div class="col-10 d-flex justify-content-end align-items-end mt-4">
                <div>
                    <button class="boton boton_primario" type="submit" id="crear">Agregar Comité</button>
                </div>
            </div>

    </form>

</section>


<?php
require ROOT . '/app/Views/layouts/footer.php';
?>


<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {


        // Inicializar Selectize en municipio y colonia
        var municipioSelect = $('#select-municipio').selectize({
            create: false,
            sortField: 'text',
            placeholder: 'Selecciona un municipio...'
        })[0].selectize;

        var coloniaSelect = $('#select-colonia').selectize({
            create: false,
            sortField: 'text',
            placeholder: 'Selecciona una colonia...'
        })[0].selectize;

        // Evento al cambiar municipio
        municipioSelect.on('change', function (value) {
            if (!value) {
                coloniaSelect.clearOptions();
                coloniaSelect.disable();
                coloniaSelect.addOption({
                    value: '',
                    text: 'Selecciona primero un municipio'
                });
                coloniaSelect.setValue('');
                return;
            }

            coloniaSelect.disable();
            coloniaSelect.clearOptions();
            coloniaSelect.addOption({
                value: '',
                text: 'Cargando...'
            });

            // Petición AJAX para obtener colonias
            $.ajax({
                url: '/funciones/ubicaciones_func',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_colonias',
                    id_municipio: value
                },
                success: function (data) {
                    coloniaSelect.clearOptions();
                    if (data.length > 0) {
                        coloniaSelect.addOption({
                            value: '',
                            text: 'Selecciona una colonia'
                        });
                        $.each(data, function (i, colonia) {
                            coloniaSelect.addOption({
                                value: colonia.id_colonia,
                                text: colonia.nombre
                            });
                        });
                        coloniaSelect.enable();
                        coloniaSelect.setValue('');
                    } else {
                        coloniaSelect.addOption({
                            value: '',
                            text: 'No hay colonias'
                        });
                        coloniaSelect.disable();
                        coloniaSelect.setValue('');
                    }
                },
                error: function () {
                    coloniaSelect.clearOptions();
                    coloniaSelect.addOption({
                        value: '',
                        text: 'Error al cargar colonias'
                    });
                    coloniaSelect.disable();
                    coloniaSelect.setValue('');
                }
            });
        });

        $('#select-municipio').selectize({
            onInitialize: function () {
                this.$control.addClass('form-control');
            }
        });

    });
</script>