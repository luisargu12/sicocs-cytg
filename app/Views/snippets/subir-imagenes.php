<div id="upload-image" class="col-12 my-3  <?php echo $display_none ?>">
    <label class="col-form-label modal-label" for="imagen">Seleccione una imagen:</label>
    <div class="input-group">
        <input type="file" class="form-control" id="imagen" name="imagen[]" accept="image/png, image/jpeg" multiple>
        <label class="input-group-text" for="imagen" title="Subir archivo">
            <span class="material-icons">cloud_upload</span>
        </label>
    </div>
</div>

<div class="col-12">
    <div id="imagenes" class="tab-content" style="padding: 32px;">
        No hay imagenes para mostrar
    </div>
    <div class="text-center my-3">
        <button id="abrirGaleriaBtn" type="button" class="boton boton_primario">Ver galería</button>
    </div>
</div>


<!-- Modal Bootstrap para galería -->
<div class="modal fade" id="modalGaleria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content bg-dark text-white position-relative">
            <div class="modal-header border-0">
                <h5 class="modal-title">Galería de imágenes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body d-flex flex-wrap gap-3 justify-content-center">
                <!-- Imágenes se insertarán aquí dinámicamente -->
            </div>
        </div>
    </div>
</div>