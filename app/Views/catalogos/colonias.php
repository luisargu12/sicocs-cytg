<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Colonias</h1>
            <div class="line"></div>
        </div>

    </div>
    <div id="colonia" class="my-5 container">
        <table id="tabla_colonias" class="mx-auto display estilo_tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Municipio</th>
                    <th>Código Postal</th>
                    <th>Ubicación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</section>


<!-- Agregar Colonia -->
<div id="modal-agregar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Agregar Colonia</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-colonia" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input type="text" class="form-control" name="nombre">
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Municipio:</label>
                        <select id="select-municipio" name="id_municipio" class="form-control" title="Tipo Usuario" required>
                            <?php
                            $tab_municipios = $conexion->query("SELECT * from municipios");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_municipios as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Código Postal:</label>
                        <input type="text" class="form-control" name="cp">
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Dirección en google maps:</label>
                        <input type="text" class="form-control" name="maps">
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="submit" class="boton boton_primario" data-bs-dismiss="modal">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Editar USUARIO -->
<div id="modal-editar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Editar Colonia</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-editar-colonia" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input type="text" class="form-control" name="nombre" id="edit-nombre">
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Municipio:</label>
                        <select id="select-municipio2" name="id_municipio" class="form-control" title="Tipo Usuario" required>
                            <?php
                            $tab_municipios = $conexion->query("SELECT * from municipios");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_municipios as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Código Postal:</label>
                        <input type="text" class="form-control" name="cp" id="edit-cp">
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Dirección en google maps:</label>
                        <input type="text" class="form-control" name="maps" id="edit-maps">
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <input type="hidden" id="edit-id_colonia" name="id_colonia">
                    <button type="submit" class="boton boton_primario" data-bs-dismiss="modal">Editar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<?php
require ROOT . '/app/Views/layouts/footer.php';
?>


<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        var tablaColonias = $('#tabla_colonias').DataTable({
            dom: 'Bfrtip',
            buttons: [{
                className: 'boton boton_primario btn-table mb-3',
                text: 'Agregar',
                action: function(e, dt, node, config) {
                    $('#modal-agregar').modal('show');
                }
            }],
            initComplete: function() {
                $('.dt-button').removeClass('dt-button');
            },
            "columnDefs": [{
                "targets": 0, // Índice de la columna a ocultar (0 = primera columna)
                "visible": false, // La oculta en la tabla
                "searchable": false // Opcional: evita que se busque en esa columna
            }],
            ajax: {
                url: "/funciones/ubicaciones_func",
                type: "POST",
                data: {
                    action: "listar-c"
                }
            },
            processing: true,
            serverSide: false,
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                sLoadingRecords: "<div style='height:150px'></div>"
            }
        });

        // Agregar colonia por AJAX
        $('#form-colonia').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'agregar-c'); // Asegura el parámetro de acción

            $.ajax({
                url: '/funciones/ubicaciones_func', // Cambia la ruta si es necesario
                type: 'POST',
                data: formData,
                contentType: false, // Importante para enviar archivos
                processData: false, // Importante para enviar archivos
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        alert(resp.message);

                        tablaColonias.ajax.reload();
                        $('#form-colonia')[0].reset();
                        // Aquí puedes recargar la tabla de usuarios si usas DataTables
                    } else {
                        alert(resp.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error en la petición AJAX: ' + error);

                }
            });
        });


        //Editar usuario por AJAX
        $('#form-editar-colonia').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('action', 'editar-c');
            $.ajax({
                url: '/funciones/ubicaciones_func',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        $('#modal-editar').modal('hide');
                        tablaColonias.ajax.reload();
                        alert(resp.message);
                    } else {
                        alert(resp.message);
                    }
                }
            });


        });


        $('#select-municipio').selectize({
            sortField: 'text'
        });





    });


    $('#tabla_colonias').on('click', '.editar-colonia', function() {
        $('#edit-id_colonia').val($(this).data('id'));
        $('#edit-nombre').val($(this).data('nombre'));
        $('#edit-cp').val($(this).data('cp'));
        $('#edit-maps').val($(this).data('maps'));
        let idMunicipio = $(this).data('id_municipio');
        $('select[name="id_municipio"]').val(idMunicipio);
        $('#modal-editar').modal('show');
    });
</script>