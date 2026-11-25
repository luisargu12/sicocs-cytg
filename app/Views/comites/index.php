<?php
$titulo = "SICOCS | Comites";
require ROOT . '/app/Views/layouts/header.php';
/*
if (!isset($_SESSION["loggedin"]) || $_SESSION["tipo"] != 1) {
    header("location: /");
    exit;
}
    */
?>
<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <div class="d-flex justify-content-between">
                <div>
                    <h1>Comités</h1>
                    <div class="line"></div>
                </div>
                <div class="mb-4 d-flex align-items-center">
                    <label for="year_select" class="form-label me-2">Año:</label>
                    <select id="year_select" class="form-select w-auto">
                        <?php
                        $tab_years = $conexion->query("SELECT * from comites_year ORDER BY year DESC");
                        foreach ($tab_years as $key => $value) {
                            echo ('<option value=' . $value[1] . '>' . $value[1] . '</option>');
                        }
                        ?>
                    </select>
                </div>
            </div>

        </div>

    </div>
    <div id="comites" class="my-5 container">
        <table id="tabla_comites" class="mx-auto display estilo_tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Folio</th>
                    <th>Municipio</th>
                    <th>Colonia</th>
                    <th>Constituida</th>
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


<script type="text/javascript" charset="utf-8">
    function cargarTablaComites() {
        var year = $('#year_select').val();
        var tablaComites = $('#tabla_comites').DataTable({
            destroy: true,
            dom: '<"tabla-barra d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"<"boton-tabla">f>rtip',
            initComplete: function () {
                $(this.api().table().container()).find('.boton-tabla').html('<a class="boton boton_primario" href="/agregar-comite">Nuevo Comité</a>');
            },
            ajax: {
                url: "/funciones/comites_func",
                type: "POST",
                data: {
                    action: "listar",
                    year: year
                }
            },
            columns: [{
                data: "id_comite",
                visible: false,
                searchable: false
            }, // Columna oculta id_comite
            {
                data: "folio"
            },
            {
                data: "nombre_municipio"
            },
            {
                data: "nombre_colonia"
            },
            {
                data: "constituido"
            },
            {
                data: "fecha"
            },
            {
                data: "usuarios_html",
                orderable: false,
                searchable: false
            },
            {
                data: "acciones",
                orderable: false,
                searchable: false
            }
            ],
            "columnDefs": [{
                "targets": 0, // Índice de la columna a ocultar (0 = primera columna)
                "visible": false, // La oculta en la tabla
                "searchable": false // Opcional: evita que se busque en esa columna
            },
            {
                width: "15%",
                targets: 1
            },
            {
                width: "10%",
                targets: 2
            },
            {
                width: "20%",
                targets: 3
            },
            {
                width: "15%",
                targets: 4
            },
            {
                width: "13%",
                targets: 5
            },
            {
                width: "17%",
                targets: 6
            },
            {
                width: "10%",
                targets: 7
            },
            ],
            processing: true,
            serverSide: false,
            order: [[1, 'desc']],
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                sLoadingRecords: "<div style='height:150px'></div>",
                search: "Buscar:",
                zeroRecords: "No se encontraron comités"
            }
        });
    }

    $(document).ready(function () {
        cargarTablaComites();

        $('#year_select').on('change', function () {
            cargarTablaComites();
        });
    });
</script>