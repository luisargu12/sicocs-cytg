<?php
$titulo = "SICOCS | Usuarios";
require ROOT . '/app/Views/layouts/header.php';
/*
if (!isset($_SESSION["loggedin"]) || $_SESSION["tipo"] != 1 || $_SESSION["tipo"] != 2) {
    header("location: /");
    exit;
}
    */

?>

<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Usuarios</h1>
        </div>
    </div>
    <div id="usuarios" class="my-5 container ">
        <table id="tabla_usuarios" class="mx-auto display estilo_tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</section>


<!-- Agregar USUARIO -->
<div id="modal-agregar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Agregar Usuario</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-usuario" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-md-4 col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input type="text" class="form-control" name="nombre">
                    </div>
                    <div class="form-group col-md-4 col-12">
                        <label class="col-form-label modal-label">Apellido Paterno:</label>
                        <input type="text" class="form-control" name="apellido_pat">
                    </div>
                    <div class="form-group col-md-4 col-12">
                        <label class="col-form-label modal-label">Apellido Materno:</label>
                        <input type="text" class="form-control" name="apellido_mat">
                    </div>
                    <div class="form-group col-md-6 col-12 mt-4">
                        <label class="col-form-label modal-label" for="imagen">Seleccione una imagen:</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/png, image/jpeg">
                            <label class="input-group-text" for="imagen" title="Subir archivo">
                                <span class="material-icons">cloud_upload</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-4">
                        <label class="col-form-label modal-label">Tipo de Usuario:</label>
                        <select name="tipo_usuario" class="form-control" title="Tipo Usuario" required>
                            <?php
                            $tab_tipo_usuario = $conexion->query("SELECT * from tipo_usuario");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_tipo_usuario as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-4">
                        <label class="col-form-label modal-label">Usuario:</label>
                        <input type="text" class="form-control" name="username">
                    </div>
                    <div class="form-group user-pass col-md-6 col-12 mt-4">
                        <label class="col-form-label modal-label">Contraseña:</label>
                        <input type="password" class="form-control" name="contrasena" id="password">
                        <input class="mt-2" type="checkbox" id="mostrarPassword"> Ver Contraseña
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <input type="hidden" class="form-control" name="agregar_usuario" value="1"></input>
                    <button type="submit" class="boton boton_primario" data-bs-dismiss="modal">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Editar USUARIO -->
<div id="modal-editar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title mb-2">Editar Usuario</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-editar-usuario" enctype="multipart/form-data">
                <div class="modal-body row">
                    <div class="form-group col-md-4 col-12">
                        <label class="col-form-label modal-label">Nombre:</label>
                        <input id="edit-nombre" type="text" class="form-control" name="nombre">
                    </div>
                    <div class="form-group col-md-4 col-12">
                        <label class="col-form-label modal-label">Apellido Paterno:</label>
                        <input id="edit-apellido_paterno" type="text" class="form-control" name="apellido_paterno">
                    </div>
                    <div class="form-group col-md-4 col-12">
                        <label class="col-form-label modal-label">Apellido Materno:</label>
                        <input id="edit-apellido_materno" type="text" class="form-control" name="apellido_materno">
                    </div>
                    <div class="form-group col-md-6 col-12 mt-4">
                        <label class="col-form-label modal-label" for="imagenEditar">Seleccione una imagen:</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="imagenEditar" name="imagenEditar" accept="image/png, image/jpeg">
                            <label class="input-group-text" for="imagenEditar" title="Subir archivo">
                                <span class="material-icons">cloud_upload</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-4">
                        <label class="col-form-label modal-label">Tipo de Usuario:</label>
                        <select name="tipo_usuario" class="form-control" title="Tipo Usuario" required>
                            <?php
                            $tab_tipo_usuario = $conexion->query("SELECT * from tipo_usuario");
                            echo ('<option value="">Seleccionar Opción</option>');
                            foreach ($tab_tipo_usuario as $key => $value) {
                                echo ('<option value=' . $value[0] . '>' . $value[1] . '</option>');
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-12 mt-4">
                        <label class="col-form-label modal-label">Usuario:</label>
                        <input id="edit-usuario" type="text" class="form-control" name="usuario">
                    </div>
                    <div class="form-group user-pass col-md-6 col-12 mt-4">
                        <label class="col-form-label modal-label">Contraseña:</label>
                        <input type="password" class="form-control" name="contrasena" id="passwordEditar">
                        <input class="mt-2" type="checkbox" id="mostrarPasswordEditar"> Ver Contraseña
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <input type="hidden" class="form-control" name="id_usuario" id="edit-id_usuario"></input>
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
        var tablaUsuarios = $('#tabla_usuarios').DataTable({
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
            ajax: {
                url: "/funciones/usuarios_func",
                type: "POST",
                data: {
                    action: "listar"
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
        $('#form-usuario').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'agregar'); // Asegura el parámetro de acción

            $.ajax({
                url: '/funciones/usuarios_func', // Cambia la ruta si es necesario
                type: 'POST',
                data: formData,
                contentType: false, // Importante para enviar archivos
                processData: false, // Importante para enviar archivos
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        mostrarAlerta("success", resp.message);
                        $('#form-usuario')[0].reset();
                        tablaUsuarios.ajax.reload();
                        // Aquí puedes recargar la tabla de usuarios si usas DataTables
                    } else {
                        mostrarAlerta("error", resp.message);
                    }
                },
                error: function(xhr, status, error) {
                    mostrarAlerta("error", 'Error en la petición AJAX: ' + error);

                }
            });
        });


        //Editar usuario por AJAX
        $('#form-editar-usuario').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('action', 'editar');
            $.ajax({
                url: '/funciones/usuarios_func',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 'ok') {
                        $('#modal-modificar-usuario').modal('hide');
                        tablaUsuarios.ajax.reload();
                        mostrarAlerta("success", resp.message);
                    } else {
                        mostrarAlerta("error", resp.message);
                    }
                }
            });


        });


        //Cambiar status
        $('#tabla_usuarios').on('click', '.activar-usuario', function() {
            if (confirm('¿Estás seguro de inhabilitar a este usuario?\nNo podrá acceder de nuevo al sistema cuando este inhabilitado ')) {
                var id_usuario = $(this).data('id');
                var act = $(this).data('act'); // Debes agregar este data-act en el botón del backend
                $.post('/funciones/usuarios_func', {
                    action: 'activo',
                    id_usuario: id_usuario,
                    act: act
                }, function(resp) {
                    if (resp.status === 'ok') {
                        tablaUsuarios.ajax.reload(null, false);
                    } else {
                        mostrarAlerta("error", resp.message);
                    }
                }, 'json');
            }
        });
    });


    $('#tabla_usuarios').on('click', '.editar-usuario', function() {
        $('#edit-id_usuario').val($(this).data('id'));
        $('#edit-nombre').val($(this).data('nombre'));
        $('#edit-apellido_paterno').val($(this).data('apellido_paterno'));
        $('#edit-apellido_materno').val($(this).data('apellido_materno'));
        $('#edit-usuario').val($(this).data('user'));
        let tipoUsuario = $(this).data('tipo_usuario');
        $('select[name="tipo_usuario"]').val(tipoUsuario);
        $('#modal-editar').modal('show');
    });


    //mostrar password
    var passwordInput = document.getElementById("password");
    var mostrarPasswordCheckbox = document.getElementById("mostrarPassword");

    mostrarPasswordCheckbox.addEventListener("change", function() {
        passwordInput.type = mostrarPasswordCheckbox.checked ? "text" : "password";
    });

    //mostrar password Editar
    var passwordInputEditar = document.getElementById("passwordEditar");
    var mostrarPasswordCheckboxEditar = document.getElementById("mostrarPasswordEditar");

    mostrarPasswordCheckboxEditar.addEventListener("change", function() {
        passwordInputEditar.type = mostrarPasswordCheckboxEditar.checked ? "text" : "password";
    });

</script>