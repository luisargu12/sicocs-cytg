<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
  header("location: index");
  exit;
}
$error = null;

if (isset($_GET['error'])) {
  $error = $_GET['error'];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SICOCS | Login</title>
  <link rel="shortcut icon" href="https://recursos.digital.nl.gob.mx/favicon.ico" type="image/vnd.microsoft.icon">
  <link href="https://recursos.digital.nl.gob.mx/assets/libs/bootstrap/css/bootstrap.min.css" id="bootstrap-style"
    rel="stylesheet" type="text/css" />
  <script src="https://recursos.digital.nl.gob.mx/assets/libs/bootstrap/js/bootstrap.bundle.min.js">
  </script>
  <link rel="stylesheet" href="https://recursos.digital.nl.gob.mx/assets/css/master.css" type="text/css">
</head>

<body class="body_login es_izquierda tiene_fondo contraloria"> <!-- tiene_fondo -->
  <header>
    <div class="caja_verificador">
      <div class="accordion caja_acordeon" id="verificador">
        <div class="accordion-item">
          <button id="verificador_boton" class="accordion-header accordion-button collapsed" type="button"
            data-bs-toggle="collapse" data-bs-target="#verificador_contenido" aria-expanded="false"
            aria-controls="verificador_contenido">
            <span>Este es un sitio web oficial del Gobierno del Estado de Nuevo León. <a class="link">Aprende a
                identificarlo</a></span>
          </button>
          <div id="verificador_contenido" class="accordion-collapse collapse" aria-labelledby="verificador_boton"
            data-bs-parent="#verificador">
            <div class="accordion-body">
              <div class="row">
                <div class="col">
                  <!-- <object id="otro_logo" class="logo_verificar" type="image/svg+xml" data="https://recursos.digital.nl.gob.mx/assets/img/logos/logo_circular.svg" title="fondo de caja login"></object> -->

                  <img decoding="async" class="logo_verificar"
                    src="https://recursos.digital.nl.gob.mx/assets/img/logos/logo_circular.svg" alt="">
                  <span>
                    Los sitios web oficiales del Gobierno de Nuevo León están adheridos al subdominio <a class="link"
                      href="https://www.nl.gob.mx/" target="_blank">nl.gob.mx</a>
                  </span>
                </div>
                <div class="col">
                  <img decoding="async" class="logo_sitio_seguro"
                    src="https://recursos.digital.nl.gob.mx/assets/img/iconos/sitio_seguro.svg" alt="">
                  <span>
                    Todos los sitios web oficiales del Gobierno de Nuevo León cuentan con certificado de seguridad.
                  </span>
                </div>
                <div class="col"><img decoding="async" class="logo_via"
                    src="https://recursos.digital.nl.gob.mx/assets/img/logos/logo_via_solo.svg" alt=""><span> Este sitio
                    está conectado a <a class="link" href="https://recursos.digital.nl.gob.mx/" target="_blank">VÍA</a>,
                    el sistema de diseño web del Gobierno de Nuevo León. </span></div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </header>
  <main>

    <div class="area_login">
      <div class="fondo_area_login">
        <img decoding="async" id="fondo_caja"
          src="https://recursos.digital.nl.gob.mx/assets/img/elementos/corchete_vertical.svg"
          title="fondo de caja login">
      </div>
      <div class="caja_login">
        <div class="leon_para_caja">
          <object id="logo_leon" type="image/svg+xml"
            data="https://recursos.digital.nl.gob.mx/assets/img/logos/logo_leon_pie.svg"
            title="Logotipo del león de nuevo nuevo león"></object>
        </div>
        <div class="logo_login">
          <img id="logo_login" class="img-fluid"
            src="./assets/img/logo.png" title="Logo del login"></img>
        </div>
        <h2 class="mb-1">SICOCS</h2>
        <h5 class="mb-4">Sistema de Cómites de Contraloría Social</h5>

        <!-- Mensaje de error de sesion -->
        <?php if ($error): ?>
          <div class="alert alert-danger rounded  alert-dismissible fade show" role="alert">
            <div style="margin-left: 0.5rem;"><?php echo $error; ?></div>
          </div>
        <?php endif; ?>
        <form id="form_login" action="/funciones/login" method="POST">

          <div class="input-group">
            <input id="user" name="user" class="form-control" type="text" placeholder="nombre de usuario"
              aria-label="nombre de usuario" required autofocus>
            <label for="user" class="form-label">Usuario</label>
          </div>

          <div class="input-group">
            <input id="password" name="password" class="form-control" type="password"
              placeholder="contraseña del usuario" aria-label="contraseña del usuario">
            <label for="password" class="form-label">Contraseña</label>
          </div>



          <button type="submit" class="boton boton_primario w-100">Iniciar sesión</button>


        </form>
      </div>
    </div>
  </main>


  <footer>
    <div class="caja_terminos">
      <div class="area_logos_login">
        <div class="logo_secretaria"></div>
        <div class="logo_contraloria es_background"></div>
      </div>
      <p>
        Términos y Condiciones / Aviso de Privacidad <br>
        SOPORTADO POR <a class="link" href="#" target="_blank">VÍA SISTEMA DE DISEÑO</a> © 2024 DE LA <a class="link"
          href="#" target="_blank">SUBSECRETARÍA DE TECNOLOGIAS</a> BAJO LICENCIA <a class="link"
          href="https://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1" target="_blank">CC BY-NC-SA 4.0</a>
      </p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://recursos.digital.nl.gob.mx/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-1.13.8/b-2.4.2/datatables.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"
    integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>

<script>
  //alertas
  setTimeout(function() {
    $('.alert-dismissible').fadeTo(1000, 0).slideUp(500, function() {
      $(this).remove();
    });
  }, 3000);

  if (window.location.search.length > 0) {
    window.history.replaceState({}, document.title, window.location.pathname);
  }
</script>