<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Tipos de Acción</h1>
        </div>

    </div>
    <div id="tipos_accion" class="container my-5">
            <table id="tabla_accion" class="mx-auto display estilo_tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descripcion</th>
                        <th>Ámbito</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
    </div>
</section>


<!-- Agregar Acciones -->
<div id="modal-agregar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Agregar Tipo de Acción</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-accion" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Descripcion:</label>
                        <input type="text" class="form-control" name="descripcion">
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Ámbito:</label>
                        <select id="select-ambito" name="id_ambito" class="form-control" title="Ambito" required>
                            <?php
                            $tab_ambitos = $conexion->query("SELECT * from ambitos");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_ambitos as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] .  '</option>');
                            }
                            ?>
                        </select>
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

<!-- Editar Tipo de Accion -->
<div id="modal-editar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Editar Tipo de Acción</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-editar-accion" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Descripcion:</label>
                        <input type="text" class="form-control" id="edit-descripcion" name="descripcion">
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Ámbito:</label>
                        <select id="select-ambito" name="id_ambito" class="form-control" title="Ambito" required>
                            <?php
                            $tab_ambitos = $conexion->query("SELECT * from ambitos");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_ambitos as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <input type="hidden" id="edit-id_accion" name="id_accion">
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
        var tablaAccion = $('#tabla_accion').DataTable({
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
                    action: "listar-ta"
                }
            },
            processing: true,
            serverSide: false,
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                sLoadingRecords: "<div style='height:150px'></div>"
            }
        });

        // Agregar Tipo de Accion por AJAX
        $('#form-accion').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'agregar-ta');

            $.ajax({
                url: '/funciones/aspectos_func',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        alert(resp.message);

                        tablaAccion.ajax.reload();
                        $('#form-accion')[0].reset();
                    } else {
                        alert(resp.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error en la petición AJAX: ' + error);

                }
            });
        });


        //Editar Tipo de Accion por AJAX
        $('#form-editar-accion').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('action', 'editar-ta');
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
                        tablaAccion.ajax.reload();
                        mostrarAlerta("success", resp.message);
                    } else {
                        mostrarAlerta("error", resp.message);
                    }
                }
            });


        });

    });


    $('#tabla_accion').on('click', '.editar-accion', function() {
        $('#edit-id_accion').val($(this).data('id'));
        $('#edit-descripcion').val($(this).data('descripcion'));
        let idAmbito = $(this).data('id_ambito');
        $('select[name="id_ambito"]').val(idAmbito);
        $('#modal-editar').modal('show');
    });
</script>