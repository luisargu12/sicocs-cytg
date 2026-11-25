<?php
$titulo = "SICOCS | Mis Comites";
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
            <h1>Mis Comités</h1>
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
    $(document).ready(function() {
        var id_usuario = <?php echo json_encode($id_usuario)?>;
        var tablaComites = $('#tabla_comites').DataTable({
            dom: '<"tabla-barra d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"<"boton-tabla">f>rtip',
            initComplete: function () {
                $(this.api().table().container()).find('.boton-tabla').html('<a class="boton boton_primario" href="/agregar-comite">Nuevo Comité</a>');
            },
            ajax: {
                url: "/funciones/comites_func",
                type: "POST",
                data: {
                    action: "listar-mis-comites",
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
                    width: "20%",
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
                    width: "10%",
                    targets: 4
                },
                {
                    width: "20%",
                    targets: 5
                },
                {
                    width: "10%",
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


    });
</script>