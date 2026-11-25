<section id="material-entregado" class="mt-3">

    <div class="row <?php echo $display_none ?>">
        <div class="form-group col-md-4 col-12 ">
            <label for="select-material" class="form-label modal-label">Selecciona el material:</label>
            <select id="select-material" name="id_material" class="form-control" title="Material">
                <?php
                $tab_material = $conexion->query("SELECT * from material_difusion");
                echo ('<option value="">Selecciona material de difusión</option>');
                foreach ($tab_material as $key => $value) {
                    echo ('<option value=' . $value[0] . '>' . $value[1] . ' ' . $value[2] . ' ' . $value[3] . '</option>');
                }
                ?>
            </select>
        </div>
        <div class="form-group col-md-4 col-12">
            <label for="ramo" class="form-label modal-label">Cantidad:</label>
            <input id="cantidad" name="cantidad" class="form-control" type="number" placeholder="Ingrese la cantidad" aria-label="Cantidad" />
        </div>
        <div class="col-md-4 d-flex justify-content-start align-items-end">
            <div>
                <input type="hidden" id="id_comite" name="id_comite" value="<?php echo $id_comite; ?>">
                <button type="button" id="btn-agregar-material" class="boton boton_terciario">Agregar</button>
            </div>

        </div>
    </div>
</section>
<div id="material-difusion-entregado" class="my-5 container">
    <table id="tabla_material" style="width:100%" class="mx-auto display estilo_tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Acción</th>
            </tr>
        </thead>
    </table>
    <div class="form-group col-6 mt-1 ms-auto d-flex align-items-center">
        <label for="total_material" style="width: 65%;" class="form-label">Total de Material Entregado:</label>
        <input id="total_material" name="total_material" class="form-control" type="number" placeholder="Total de material" aria-label="total_material" readonly <?php echo $deshabilitar; ?> />
    </div>
    <div class="d-flex justify-content-end col-6 ms-auto">
        <span style="font-size: 12px;">* No se cuentan los trípticos para el total</span>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="editar-material" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editar-material" aria-hidden="true">
    <div class="modal-dialog modal-xs">
        <div class="modal-content">
            <div class="modal-header">
                <div class="mb-3">
                    <h3 class="modal-title">Editar Cantidad</h3>
                    <div class="line"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="descripcion_me" class="form-label">Material:</label>
                    <input id="descripcion_me" name="descripcion_me" class="form-control" type="text" readonly />
                </div>
                <div class="mb-3">
                    <label for="edit_cantidad" class="form-label">Cantidad:</label>
                    <input id="edit_cantidad" name="edit_cantidad" class="form-control" type="number" min="1" />
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="id_me" name="id_me" value="">
                <button type="button" class="btn boton_primario" id="btn-editar-material">Editar</button>
            </div>
        </div>
    </div>
</div>