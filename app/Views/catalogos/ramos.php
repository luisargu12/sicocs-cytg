<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Ramos</h1>
        </div>

    </div>
    <div id="ramos" class="my-5 container">
        <table id="tabla_ramos" class="mx-auto display estilo_tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ramo</th>
                    <th>Descripcion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</section>


<!-- Agregar Ramo -->
<div id="modal-agregar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Agregar Ramo</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-ramo" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Ramo:</label>
                        <input type="text" class="form-control" name="ramo">
                    </div>
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Descripcion:</label>
                        <input type="text" class="form-control" name="descripcion">
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

<!-- Editar Ramo -->
<div id="modal-editar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Editar Ramo</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-editar-ramo" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input type="text" class="form-control" id="edit-ramo" name="ramo">
                    </div>
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input type="text" class="form-control" id="edit-descripcion" name="descripcion">
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <input type="hidden" id="edit-id_ramo" name="id_ramo">
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
        var tablaRamo = $('#tabla_ramos').DataTable({
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
                "targets": 0,
                "visible": false,
                "searchable": false 
            }],
            "order": [
                [0, "desc"]
            ],
            ajax: {
                url: "/funciones/catalogos_func",
                type: "POST",
                data: {
                    action: "listar-r"
                }
            },
            processing: true,
            serverSide: false,
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                sLoadingRecords: "<div style='height:150px'></div>",
                search: "Buscar:",
                zeroRecords: "No se encontraron ramos"
            }
        });

        // Agregar ramo por AJAX
        $('#form-ramo').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'agregar-r'); // Asegura el parámetro de acción

            $.ajax({
                url: '/funciones/catalogos_func', // Cambia la ruta si es necesario
                type: 'POST',
                data: formData,
                contentType: false, // Importante para enviar archivos
                processData: false, // Importante para enviar archivos
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        alert(resp.message);

                        tablaRamo.ajax.reload();
                        $('#form-ramo')[0].reset();
                    } else {
                        alert(resp.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error en la petición AJAX: ' + error);

                }
            });
        });


        //Editar ramo por AJAX
        $('#form-editar-ramo').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('action', 'editar-r');
            $.ajax({
                url: '/funciones/catalogos_func',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        $('#modal-editar').modal('hide');
                        tablaRamo.ajax.reload();
                        alert(resp.message);
                    } else {
                        alert(resp.message);
                    }
                }
            });


        });

    });


    $('#tabla_ramos').on('click', '.editar-ramo', function() {
        $('#edit-id_ramo').val($(this).data('id'));
        $('#edit-ramo').val($(this).data('ramo'));
        $('#edit-descripcion').val($(this).data('descripcion'));
        $('#modal-editar').modal('show');
    });
</script>