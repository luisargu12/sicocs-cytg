<?php
$titulo = "SICOCS | Seguimientos";
require ROOT . '/app/Views/layouts/header.php';
?>
<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <div class="d-flex justify-content-between">
                <div>
                    <h1>Seguimientos</h1>
                    <div class="line"></div>
                </div>
                <div class="mb-4 d-flex align-items-center">
                    <label for="year_select" class="form-label me-2">Año:</label>
                    <select id="year_select" class="form-select w-auto">
                        <?php
                        $tab_years = $conexion->query("SELECT year FROM comites_year ORDER BY year DESC");
                        foreach ($tab_years as $value) {
                            echo '<option value="' . (int) $value['year'] . '">' . (int) $value['year'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div id="seguimientos" class="my-5 container">
        <table id="tabla_seguimientos" class="mx-auto display estilo_tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Folio</th>
                    <th>Municipio</th>
                    <th>Colonia</th>
                    <th>Comité</th>
                    <th>Fecha</th>
                    <th>Responsable(s) de captura</th>
                    <th>Acción</th>
                </tr>
            </thead>
        </table>
    </div>
</section>

<?php
require ROOT . '/app/Views/layouts/footer.php';
?>

<script>
    function cargarTablaSeguimientos() {
        $('#tabla_seguimientos').DataTable({
            destroy: true,
            dom: '<"tabla-barra d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"<"boton-tabla">f>rtip',
            initComplete: function () {
                $(this.api().table().container()).find('.boton-tabla').html('<a class="boton boton_primario" href="/agregar-seguimiento">Nuevo Seguimiento</a>');
            },
            ajax: {
                url: "/funciones/seguimiento_func",
                type: "POST",
                data: {
                    action: "listar",
                    year: $('#year_select').val()
                }
            },
            columns: [
                { data: "id_seguimiento", visible: false, searchable: false },
                { data: "folio" },
                { data: "nombre_municipio" },
                { data: "nombre_colonia" },
                { data: "folio_comite" },
                { data: "fecha" },
                { data: "usuarios_html", orderable: false, searchable: false },
                { data: "acciones", orderable: false, searchable: false }
            ],
            processing: true,
            order: [[1, 'desc']],
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                search: "Buscar:",
                zeroRecords: "No se encontraron seguimientos"
            }
        });
    }

    $(document).ready(function () {
        cargarTablaSeguimientos();
        $('#year_select').on('change', cargarTablaSeguimientos);
    });
</script>
