<?php
$titulo = "SICOCS | Por Revisar";
require ROOT . '/app/Views/layouts/header.php';
/*
if (!isset($_SESSION["loggedin"]) || $_SESSION["tipo"] != 1) {
    header("location: /");
    exit;
}
    */

$id_usuario = $_SESSION['id'];

?>
<section class="min-h">
    <div class="mb-5 pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Comités por Revisar</h1>
            <div class="line"></div>
        </div>

    </div>
    <div id="comites" class="my-5 container">
        <table id="tabla_comites" class="mx-auto display estilo_tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Folio</th>
                    <th>Municipio</th>
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
    $(document).ready(function() {
        var id_usuario = <?php echo json_encode($id_usuario) ?>;
        var tablaComites = $('#tabla_comites').DataTable({
            dom: 'Bfrtip',
            ajax: {
                url: "/funciones/comites_func",
                type: "POST",
                data: {
                    action: "listar-por-revisar",
                    id_usuario: id_usuario,
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
                    width: "25%",
                    targets: 2
                },
                {
                    width: "10%",
                    targets: 3
                },
                {
                    width: "20%",
                    targets: 2
                },
                {
                    width: "20%",
                    targets: 2
                },
                {
                    width: "10%",
                    targets: 2
                },
            ],
            processing: true,
            serverSide: false,
            language: {
                sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
                sLoadingRecords: "<div style='height:150px'></div>",
                search: "Buscar:",
                zeroRecords: "No se encontraron comités"
            }
        });


    });
</script>