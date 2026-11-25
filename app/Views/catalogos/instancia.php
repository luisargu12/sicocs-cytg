<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Instancias Ejecutoras</h1>
        </div>

    </div>
    <div id="instancia" class="my-5 container">
        <table id="tabla_instancia" class="mx-auto display estilo_tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descripcion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</section>


<!-- Agregar Instancia -->
<div id="modal-agregar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Agregar Instancia Ejecutora</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-instancia" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input type="text" class="form-control" name="nombre_instancia">
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

<!-- Editar Instancia -->
<div id="modal-editar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Editar Instancia Ejecutora</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-editar-instancia" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input type="text" class="form-control" id="edit-nombre" name="nombre_instancia">
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <input type="hidden" id="edit-id_instancia" name="id_instancia">
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
        var tablaInstancia = $('#tabla_instancia').DataTable({
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
            "order": [
                [0, "desc"]
            ],
            ajax: {
                url: "/funciones/catalogos_func",
                type: "POST",
                data: {
                    action: "listar-ie"
                }
            },
            processing: true,
            serverSide: false,
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                sLoadingRecords: "<div style='height:150px'></div>",
                search: "Buscar:",
                zeroRecords: "No se encontraron instancias"
            }
        });

        // Agregar instancia por AJAX
        $('#form-instancia').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'agregar-ie'); // Asegura el parámetro de acción

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

                        tablaInstancia.ajax.reload();
                        $('#form-instancia')[0].reset();
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


        //Editar instancia por AJAX
        $('#form-editar-instancia').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('action', 'editar-ie');
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
                        tablaInstancia.ajax.reload();
                        alert(resp.message);
                    } else {
                        alert(resp.message);
                    }
                }
            });


        });

    });


    $('#tabla_instancia').on('click', '.editar-instancia', function() {
        $('#edit-id_instancia').val($(this).data('id'));
        $('#edit-nombre').val($(this).data('nombre'));
        $('#modal-editar').modal('show');
    });
</script>