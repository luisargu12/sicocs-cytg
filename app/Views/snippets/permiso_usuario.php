<button type="button" class="btn boton_terciario" data-bs-toggle="modal" data-bs-target="#modalUsuario">
    <span class="material-icons">manage_accounts</span>
</button>


<!-- Modal -->
<div class="modal fade" id="modalUsuario" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalUsuario" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="mb-3">
                    <h3 class="modal-title" id="staticBackdropLabel">Usuarios Responsable</h3>
                    <div class="line"></div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <section id="permiso-usuario">
                    <div class="row">
                        <div class="col-8">
                            <select id="select-usuario-permiso" name="id_usuario" class="form-control" title="Usuario">
                                <?php
                                $tab_usuarios = $conexion->query("SELECT id_usuario, nombre, apellido_paterno, apellido_materno from usuarios WHERE id_usuario <> 1 AND activo != 0");
                                echo ('<option value="">Seleccionar Usuario</option>');
                                foreach ($tab_usuarios as $key => $value) {
                                    echo ('<option value=' . $value[0] . '>' . $value[1] . ' ' . $value[2] . ' ' . $value[3] . '</option>');
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-4 d-flex justify-content-end">
                            <input type="hidden" id="id_comite" name="id_comite" value="<?php echo $id_comite; ?>">
                            <button type="button" id="btn-agregar-permiso" class="boton boton_terciario">Agregar</button>
                        </div>
                    </div>
                </section>
                <div id="usuarios-permiso" class="my-5 container">
                    <table id="tabla_usuarios" style="width:100%" class="mx-auto display estilo_tabla">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>