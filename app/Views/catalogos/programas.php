<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Programas</h1>
        </div>

    </div>
    <div id="programas" class="container my-5">
        <table id="tabla_programas" class="mx-auto display estilo_tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descripcion</th>
                    <th>Ramo</th>
                    <th>Instancia Ejecutora</th>
                    <th>Editar</th>
                    <th>Visible</th>
                </tr>
            </thead>
        </table>
    </div>
</section>


<!-- Agregar Programas -->
<div id="modal-agregar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Agregar Programa</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-programa" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Descripcion:</label>
                        <input type="text" class="form-control" name="descripcion">
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Ramo:</label>
                        <select id="select-ramo" name="id_ramo" class="form-control" title="Ramo" required>
                            <?php
                            $tab_ramos = $conexion->query("SELECT * from ramos");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_ramos as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . ' - ' . $value[2] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Instancia Ejecutora:</label>
                        <select id="select-instancia" name="id_instancia" class="form-control" title="Instancia Ejecutora" required>
                            <?php
                            $tab_IE = $conexion->query("SELECT * from instancia_ejecutora");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_IE as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
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

<!-- Editar USUARIO -->
<div id="modal-editar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-s">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Editar Programa</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-editar-programa" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-12">
                        <label class="col-form-label modal-label">Descripcion:</label>
                        <input type="text" class="form-control" id="edit-descripcion" name="descripcion">
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Ramo:</label>
                        <select id="select-ramo" name="id_ramo" class="form-control" title="Ramo" required>
                            <?php
                            $tab_ramos = $conexion->query("SELECT * from ramos");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_ramos as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . ' - ' . $value[2] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-12 mt-2">
                        <label class="col-form-label modal-label">Instancia Ejecutora:</label>
                        <select id="select-instancia" name="id_instancia" class="form-control" title="Instancia Ejecutora" required>
                            <?php
                            $tab_ie = $conexion->query("SELECT * from instancia_ejecutora");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_ie as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <input type="hidden" id="edit-id_programa" name="id_programa">
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
        var tablaPrograma = $('#tabla_programas').DataTable({
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
            "columnDefs": [{
                "targets": 0, // Índice de la columna a ocultar (0 = primera columna)
                "visible": false, // La oculta en la tabla
                "searchable": false // Opcional: evita que se busque en esa columna
            }],
            "order": [
                [0, "desc"]
            ],
            ajax: {
                url: "/funciones/catalogos_func",
                type: "POST",
                data: {
                    action: "listar-p"
                }
            },
            processing: true,
            serverSide: false,
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                sLoadingRecords: "<div style='height:150px'></div>"
            }
        });

        // Agregar programa por AJAX
        $('#form-programa').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'agregar-p');

            $.ajax({
                url: '/funciones/catalogos_func',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        alert(resp.message);

                        tablaPrograma.ajax.reload();
                        $('#form-programa')[0].reset();
                    } else {
                        alert(resp.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error en la petición AJAX: ' + error);

                }
            });
        });


        //Editar programa por AJAX
        $('#form-editar-programa').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('action', 'editar-p');
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
                        tablaPrograma.ajax.reload();
                        alert(resp.message);
                    } else {
                        alert(resp.message);
                    }
                }
            });


        });

        //Cambiar status
        $('#tabla_programas').on('click', '.activar-programa', function() {
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
                    var id_programa = $(this).data('id');
                    var act = $(this).data('act'); // Debes agregar este data-act en el botón del backend
                    $.post('/funciones/catalogos_func', {
                        action: 'activo-programa',
                        id_programa: id_programa,
                        act: act
                    }, function(resp) {
                        if (resp.status === 'ok') {
                            tablaPrograma.ajax.reload(null, false);
                        } else {
                            mostrarAlerta("error", resp.message);
                        }
                    }, 'json');
                }
            });
        });

    });


    $('#tabla_programas').on('click', '.editar-programa', function() {
        $('#edit-id_programa').val($(this).data('id'));
        $('#edit-descripcion').val($(this).data('descripcion'));
        let idRamo = $(this).data('id_ramo');
        $('select[name="id_ramo"]').val(idRamo);
        let idIE = $(this).data('id_instancia');
        $('select[name="id_instancia"]').val(idIE);
        $('#modal-editar').modal('show');
    });



    $('#select-ramo').selectize({
        onInitialize: function() {
            this.$control.addClass('form-control');
        }
    });

    $('#select-instancia').selectize({
        onInitialize: function() {
            this.$control.addClass('form-control');
        }
    });
</script>