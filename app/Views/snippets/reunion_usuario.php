<section id="reunion-usuario">
    <div class="row  <?php echo $display_none ?>">
        <div class="col-8">
            <select id="select-usuario" name="id_usuario" class="form-control" title="Usuario">
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
            <button type="button" id="btn-agregar-usuario" class="boton boton_terciario">Agregar</button>
        </div>
    </div>
</section>
<div id="usuarios-reunion" class="my-5 container">
    <table id="tabla_reunion_usuarios" style="width:100%" class="mx-auto display estilo_tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Acción</th>
            </tr>
        </thead>
    </table>
</div>