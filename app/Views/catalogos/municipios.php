<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Municipios</h1>
        </div>

    </div>
    <div id="municipios" class="my-5 container">
            <table id="tabla_municipios" class="mx-auto display estilo_tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Siglas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
    </div>
</section>

<!-- Agregar Municipio -->
<div id="modal-agregar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Agregar Municipio</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-municipio" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input type="text" class="form-control" name="nombre">
                    </div>
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Siglas:</label>
                        <input type="text" class="form-control" name="siglas">
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
                    <h3 class="modal-title mb-2">Editar Municipio</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-editar-municipio" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input type="text" class="form-control" id="edit-nombre" name="nombre">
                    </div>
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Siglas:</label>
                        <input type="text" class="form-control" id="edit-siglas" name="siglas">
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <input type="hidden" id="edit-id_municipio" name="id_municipio">
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
        var tablaMunicipios = $('#tabla_municipios').DataTable({
            dom: 'Bfrtip',
            buttons: [{
                className: 'boton boton_secundario btn-table mb-3',
                text: 'Agregar',
                action: function(e, dt, node, config) {
                    $('#modal-agregar').modal('show');
                }
            }],
            initComplete: function() {
                $('.dt-button').removeClass('dt-button');
            },
            ajax: {
                url: "/funciones/ubicaciones_func",
                type: "POST",
                data: {
                    action: "listar-m"
                }
            },
            processing: true,
            serverSide: false,
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                sLoadingRecords: "<div style='height:150px'></div>"
            }
        });

        // Agregar usuario por AJAX
        $('#form-municipio').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'agregar-m');

            $.ajax({
                url: '/funciones/ubicaciones_func',
                type: 'POST',
                data: formData,
                contentType: false, 
                processData: false, 
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        alert(resp.message);
                       
                        tablaMunicipios.ajax.reload();
                        $('#form-municipio')[0].reset();
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
        $('#form-editar-municipio').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('action', 'editar-m');
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
                        tablaMunicipios.ajax.reload();
                        alert(resp.message);
                    } else {
                        alert(resp.message);
                    }
                }
            });


        });

    });


    $('#tabla_municipios').on('click', '.editar-municipio', function() {
        $('#edit-id_municipio').val($(this).data('id'));
        $('#edit-nombre').val($(this).data('nombre'));
        $('#edit-siglas').val($(this).data('siglas'));
        $('#modal-editar').modal('show');
    });
</script>