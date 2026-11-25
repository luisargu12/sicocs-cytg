<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Tipo de reunión - seguimiento</h1>
        </div>

    </div>
    <div id="tipo_reunion_seguimiento" class="container my-5">
        <table id="tabla_tipo_reunion_seguimiento" class="mx-auto display estilo_tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descripcion</th>
                    <th>Acciones</th>
                    <th>Visible</th>
                </tr>
            </thead>
        </table>
    </div>
</section>


<!-- Agregar Tipo de Reunion de Seguimiento -->
<div id="modal-agregar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Agregar Tipo de Reunión</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-tipo-reunion-seguimiento" enctype="multipart/form-data">
                <div class="modal-body row">
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

<!-- Editar Tipo de Reunion de Seguimiento -->
<div id="modal-editar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Editar Tipo de Reunión</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-editar-tipo-reunion-seguimiento" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Descripcion:</label>
                        <input type="text" class="form-control" id="edit-descripcion" name="descripcion">
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <input type="hidden" id="edit-id_tipo_reunion" name="id_tipo_reunion">
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
        var tablaTRS = $('#tabla_tipo_reunion_seguimiento').DataTable({
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
                url: "/funciones/aspectos_func",
                type: "POST",
                data: {
                    action: "listar-trs"
                }
            },
            processing: true,
            serverSide: false,
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                sLoadingRecords: "<div style='height:150px'></div>"
            }
        });

        $('#form-tipo-reunion-seguimiento').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'agregar-trs');

            $.ajax({
                url: '/funciones/aspectos_func',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        mostrarAlerta("success", resp.message);
                        tablaTRS.ajax.reload();
                        $('#form-tipo-reunion-seguimiento')[0].reset();
                    } else {
                        mostrarAlerta("error", resp.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error en la petición AJAX: ' + error);

                }
            });
        });

        $('#form-editar-tipo-reunion-seguimiento').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('action', 'editar-trs');
            $.ajax({
                url: '/funciones/aspectos_func',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        $('#modal-editar').modal('hide');
                        tablaTRS.ajax.reload();
                        mostrarAlerta("success", resp.message);
                    } else {
                        mostrarAlerta("error", resp.message);
                    }
                }
            });
        });

        $('#tabla_tipo_reunion_seguimiento').on('click', '.activar-trs', function() {
            Swal.fire({
                title: "¿Deseas cambiar el status?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Cambiar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#ff8400",
                cancelButtonColor: "#d33",
            }).then((result) => {
                if (result.isConfirmed) {
                    var id_tipo_reunion = $(this).data('id');
                    var act = $(this).data('act');
                    $.post('/funciones/aspectos_func', {
                        action: 'activo-trs',
                        id_tr: id_tipo_reunion,
                        act: act
                    }, function(resp) {
                        if (resp.status === 'ok') {
                            tablaTRS.ajax.reload(null, false);
                        } else {
                            mostrarAlerta("error", resp.message);
                        }
                    }, 'json');
                }
            });
        });
    });

    $('#tabla_tipo_reunion_seguimiento').on('click', '.editar-tipo_reunion_seg', function() {
        $('#edit-id_tipo_reunion').val($(this).data('id'));
        $('#edit-descripcion').val($(this).data('descripcion'));
        $('#modal-editar').modal('show');
    });
</script>
