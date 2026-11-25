<!--footer start-->
<footer class="p-0">
    <section class="seccion_leyenda">
        <div class="primera_leyenda">Gobierno de <strong>nuevo león</strong></div>
        <div class="logo_leyenda">
            <img decoding="async" src="https://recursos.digital.nl.gob.mx/assets/img/logos/logo_leondorado.svg"
                alt="logo de león dorado">
        </div>
        <div class="segunda_leyenda">Un <strong>nuevo</strong> nuevo león</div>
    </section>
    <section class="seccion_licencia">
        <p style="font-size:10px;">SOPORTADO POR <a class="link" href="#" target="_blank">VÍA SISTEMA DE DISEÑO</a> ©
            2023 DE LA <a class="link" href="#" target="_blank">SUBSECRETARÍA DE TECNOLOGIAS</a> BAJO LICENCIA <a
                class="link" href="https://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1" target="_blank">CC
                BY-NC-SA 4.0</a></p>
    </section>
</footer>
<!--footer end-->




<!--<script src="js/form-component.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://recursos.digital.nl.gob.mx/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-1.13.8/b-2.4.2/datatables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"
    integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" charset="utf8" src="./assets/js/app.js"></script>
<script>
    $('#abrir-mi-cuenta').on('click', function (e) {
        e.preventDefault();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMiCuenta')).show();
    });

    $('#modalMiCuenta').on('show.bs.modal', function () {
        $('#preview-mi-cuenta').attr('src', $('#avatar-usuario').attr('src'));
        $('#imagen-mi-cuenta').val('');
    });

    $('#imagen-mi-cuenta').on('change', function () {
        var archivo = this.files && this.files[0];
        if (!archivo) {
            return;
        }
        var lector = new FileReader();
        lector.onload = function (evento) {
            $('#preview-mi-cuenta').attr('src', evento.target.result);
        };
        lector.readAsDataURL(archivo);
    });

    $('#form-mi-cuenta').on('submit', function (e) {
        e.preventDefault();
        var datos = new FormData(this);
        datos.append('action', 'cambiar-imagen');
        $.ajax({
            url: '/funciones/usuarios_func',
            method: 'POST',
            data: datos,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (resp) {
                if (resp.status === 'ok') {
                    var ruta = './assets/img-user/' + resp.imagen + '?t=' + Date.now();
                    $('#avatar-usuario, #preview-mi-cuenta').attr('src', ruta);
                    bootstrap.Modal.getInstance(document.getElementById('modalMiCuenta')).hide();
                    mostrarAlerta('success', 'La foto de perfil se actualizó.', 'Imagen actualizada', 1800);
                } else {
                    mostrarAlerta('error', resp.message || 'No se pudo actualizar la imagen.', 'Error', 0);
                }
            },
            error: function () {
                mostrarAlerta('error', 'No se pudo actualizar la imagen.', 'Error', 0);
            }
        });
    });
</script>




</main>
</body>

</html>