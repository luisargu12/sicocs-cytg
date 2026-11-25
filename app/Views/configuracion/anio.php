<?php
if (($_SESSION['loggedin'] ?? false) !== true || (int) ($_SESSION['tipo'] ?? 0) !== 1) {
    header('Location: /');
    exit;
}

$conexion = Database::connection();
$siguiente = AnioModel::siguiente($conexion);
$habilitado = AnioModel::habilitado($siguiente);
$titulo = 'SICOCS | Iniciar año';
$mensaje = isset($_GET['error']) ? $_GET['error'] : null;
$ok = isset($_GET['ok']) ? (int) $_GET['ok'] : 0;
require ROOT . '/app/Views/layouts/header.php';
?>

<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Iniciar año</h1>
            <div class="line"></div>
        </div>
    </div>
    <div class="container my-5">
        <?php if ($ok > 0): ?>
            <div class="alert alert-success" role="alert">El año <?php echo $ok; ?> quedó iniciado. Los folios empiezan en 001.</div>
        <?php endif; ?>
        <?php if ($mensaje): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="col-md-6 col-12">
            <h3 class="mb-3">Año <?php echo (int) $siguiente; ?></h3>
            <p>Se abre el año siguiente al último registrado. Los contadores de comités y seguimientos empiezan en cero.</p>
            <?php if ($habilitado): ?>
                <form action="/funciones/iniciar_anio" method="POST">
                    <input type="hidden" name="year" value="<?php echo (int) $siguiente; ?>">
                    <button class="boton boton_primario" type="submit">Iniciar <?php echo (int) $siguiente; ?></button>
                </form>
            <?php else: ?>
                <p>El botón se habilita el 1 de enero de <?php echo (int) $siguiente; ?>.</p>
                <button class="boton boton_primario" type="button" disabled>Iniciar <?php echo (int) $siguiente; ?></button>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
require ROOT . '/app/Views/layouts/footer.php';
