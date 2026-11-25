<?php
$titulo = 'SICOCS | Estadísticas';
require ROOT . '/app/Views/layouts/header.php';
/*
if (!isset($_SESSION["loggedin"]) || $_SESSION["tipo"] != 1) {
    header("location: /");
    exit;
}
    */
?>
<section class="min-h">
    <div class=" pt-5 pb-4 titulo-sicocs">
        <div class="container">
            <h1>Estadísticas</h1>
            <div class="line"></div>
        </div>

    </div>
    <div class="caja_navtab">
        <nav>
            <div class="nav nav-tabs es_completo es_landing" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home1" type="button" role="tab" aria-controls="nav-home" aria-selected="false" tabindex="-1">
                    <!-- <span class="material-icons">home</span> -->
                    Estadísticas de Comités</button>
                <button class="nav-link .con_separador" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile2" type="button" role="tab" aria-controls="nav-profile" aria-selected="true">Estadísticas de Seguimientos</button>
                <button class="nav-link .con_separador" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact3" type="button" role="tab" aria-controls="nav-contact" aria-selected="false" tabindex="-1">Estadísticas Totales</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent1">
            <div class="tab-pane fade show active" id="nav-home1" role="tabpanel" aria-labelledby="nav-home-tab">
                <div id="comites-estad">
                    <table id="tabla_comites" class="mx-auto display estilo_tabla"></table>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-profile2" role="tabpanel" aria-labelledby="nav-profile-tab">
                <div id="seguimientos-estad">
                    <table id="tabla_seguimientos_estad" class="mx-auto display estilo_tabla"></table>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-contact3" role="tabpanel" aria-labelledby="nav-contact-tab">
                <div id="totales-estad">
                    <table id="tabla_totales" class="mx-auto display estilo_tabla"></table>
                </div>
            </div>
        </div>
    </div>

    </div>

</section>





<?php
require ROOT . '/app/Views/layouts/footer.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<?php
$aniosEstadistica = $conexion->query('SELECT year FROM comites_year ORDER BY year DESC')->fetchAll(PDO::FETCH_COLUMN);
$aniosEstadistica = array_map('strval', $aniosEstadistica);
if ($aniosEstadistica === []) {
    $aniosEstadistica = [(string) date('Y')];
}
?>
<script type="text/javascript" charset="utf-8">
    var aniosEstadistica = <?php echo json_encode($aniosEstadistica); ?>;
    var anioEstadistica = aniosEstadistica[0];

    function cargarTablaComites() {
        $('#tabla_comites').DataTable({
            destroy: true,
            dom: '<"estad-barra d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"<"estad-controles d-flex align-items-center gap-2"<"#anio-estadistica">B>f>rtip',
            initComplete: function () {
                var opciones = aniosEstadistica.map(function (anio) {
                    var seleccionado = anio === anioEstadistica ? ' selected' : '';
                    return '<option value="' + anio + '"' + seleccionado + '>' + anio + '</option>';
                }).join('');
                $(this.api().table().container()).find('#anio-estadistica').html(
                    '<label class="mb-0" for="year_select">Año</label><select id="year_select" class="form-select w-auto">' + opciones + '</select>'
                );
            },
            ajax: {
                url: '/funciones/estadisticas_func',
                method: 'POST',
                data: {
                    action: "completa",
                    year: anioEstadistica
                },
                dataSrc: 'data'
            },
            columns: [{
                    data: 'id',
                    title: 'ID'
                },
                {
                    data: 'folio',
                    title: 'Folio'
                },
                {
                    data: 'usuario',
                    title: 'Usuario'
                },
                {
                    data: 'estatus',
                    title: 'Estatus'
                },
                {
                    data: 'year',
                    title: 'Año'
                },
                {
                    data: 'mes',
                    title: 'Mes'
                },
                {
                    data: 'dia',
                    title: 'Día'
                },
                {
                    data: 'municipio',
                    title: 'Municipio'
                },
                {
                    data: 'colonia',
                    title: 'Colonia'
                },
                {
                    data: 'ambito',
                    title: 'Ambito'
                },
                {
                    title: 'Ramo / Equivalente',
                    render: function(data, type, row) {
                        if (row.ramo === null || row.ramo === '') {
                            return '';
                        }
                        return row.ramo + ' - ' + row.ramo_descripcion;
                    },
                    exportOptions: {
                        format: {
                            body: function(data, row, column, node) {
                                if (row.ramo === null || row.ramo === '') {
                                    return '';
                                }
                                return row.ramo + ' - ' + row.ramo_descripcion;
                            }
                        }
                    }
                },
                {
                    data: 'tipo_accion',
                    title: 'Tipo de Acción'
                },
                {
                    data: 'presupuesto',
                    title: 'Presupuesto',
                    render: function(data, type, row) {
                        if (data === null || data === '') {
                            return 'N/A'; // muestra N/A si es nulo o vacío
                        }
                        if (type === 'display' || type === 'filter') {
                            // Formato moneda local MXN, con símbolo $
                            return new Intl.NumberFormat('es-MX', {
                                style: 'currency',
                                currency: 'MXN'
                            }).format(data);
                        }
                        return data; // para ordenamiento y otros tipos retorna valor sin formato
                    }
                },
                {
                    data: 'programa',
                    title: 'Programa/Obra'
                },
                {
                    data: 'ie',
                    title: 'IE'
                },
                {
                    data: 'integrantes',
                    title: 'Integrantes'
                },
                {
                    data: 'integrantes_m',
                    title: 'Integrantes Mujeres'
                },
                {
                    data: 'integrantes_h',
                    title: 'Integrantes Hombres'
                },
                {
                    data: 'beneficiarios',
                    title: 'Beneficiarios'
                },
                {
                    data: 'beneficiarios_m',
                    title: 'Beneficiarios Mujeres'
                },
                {
                    data: 'beneficiarios_h',
                    title: 'Beneficiarios Hombres'
                },
                {
                    data: 'Tripticos_CS',
                    title: 'Trípticos CS'
                },
                {
                    data: 'Tripticos_QyD',
                    title: 'Trípticos QyD'
                },
                {
                    data: 'modalidad',
                    title: 'Modalidad'
                },
                {
                    data: 'total_promocionales',
                    title: 'Promocionales'
                },
                {
                    data: 'Bolsa_Eco',
                    title: 'Bolsa Eco'
                },
                {
                    data: 'Playera',
                    title: 'Playera'
                },
                {
                    data: 'Gorra',
                    title: 'Gorra'
                },
                {
                    data: 'Libreta',
                    title: 'Libreta'
                },
                {
                    data: 'Paraguas',
                    title: 'Paraguas'
                },
                {
                    data: 'Vasos',
                    title: 'Vasos'
                },
                {
                    data: 'Plumas',
                    title: 'Plumas'
                },
                {
                    data: 'comentarios',
                    title: 'Comentarios'
                },
                {
                    data: 'revision',
                    title: 'Revisado'
                }
            ],
            order: [
                [1, 'asc']
            ],
            scrollX: true,
            columnDefs: [{
                    target: 0,
                    visible: false
                },
                {
                    targets: [15, 18],
                    createdCell: function(td) {
                        $(td).css('background-color', '#fde7f0'); // rosa tenue
                    }
                },
                {
                    targets: [16, 19],
                    createdCell: function(td) {
                        $(td).css('background-color', '#e7f0fd'); // azul tenue
                    }
                }
            ],
            buttons: [{
                extend: 'excelHtml5',
                className: 'boton',
                text: '<span class="material-symbols-outlined">docs</span>',
                titleAttr: 'Exportar a Excel',
                exportOptions: {
                    columns: ':visible' // exporta solo columnas visibles
                }
            }]
        });
    }

    var anioSeguimiento = anioEstadistica;
    var tablaSeguimientosLista = false;

    function cargarTablaSeguimientos() {
        $('#tabla_seguimientos_estad').DataTable({
            destroy: true,
            dom: '<"estad-barra d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"<"estad-controles d-flex align-items-center gap-2"<"#anio-estadistica-seg">B>f>rtip',
            initComplete: function () {
                var opciones = aniosEstadistica.map(function (anio) {
                    var seleccionado = anio === anioSeguimiento ? ' selected' : '';
                    return '<option value="' + anio + '"' + seleccionado + '>' + anio + '</option>';
                }).join('');
                $(this.api().table().container()).find('#anio-estadistica-seg').html(
                    '<label class="mb-0" for="year_select_seg">Año</label><select id="year_select_seg" class="form-select w-auto">' + opciones + '</select>'
                );
            },
            ajax: {
                url: '/funciones/estadisticas_func',
                method: 'POST',
                data: {
                    action: 'completa-seguimientos',
                    year: anioSeguimiento
                },
                dataSrc: 'data'
            },
            columns: [
                { data: 'id', title: 'ID' },
                { data: 'folio', title: 'Folio' },
                { data: 'folio_comite', title: 'Comité' },
                { data: 'usuario', title: 'Responsable' },
                { data: 'estatus', title: 'Estatus' },
                { data: 'year', title: 'Año' },
                { data: 'mes', title: 'Mes' },
                { data: 'dia', title: 'Día' },
                { data: 'municipio', title: 'Municipio' },
                { data: 'colonia', title: 'Colonia' },
                { data: 'ambito', title: 'Ámbito' },
                {
                    title: 'Ramo / Equivalente',
                    render: function (data, type, row) {
                        if (row.ramo === null || row.ramo === '') {
                            return '';
                        }
                        return row.ramo + ' - ' + row.ramo_descripcion;
                    }
                },
                { data: 'tipo_accion', title: 'Tipo de Acción' },
                { data: 'programa', title: 'Programa/Obra' },
                { data: 'ie', title: 'IE' },
                { data: 'tipo_reunion', title: 'Tipo de reunión' },
                { data: 'modalidad', title: 'Modalidad' },
                { data: 'horario', title: 'Horario' },
                { data: 'beneficiarios', title: 'Beneficiarios' },
                { data: 'beneficiarios_m', title: 'Beneficiarios Mujeres' },
                { data: 'beneficiarios_h', title: 'Beneficiarios Hombres' },
                { data: 'descripcion', title: 'Descripción' },
                { data: 'comentarios', title: 'Comentarios' }
            ],
            order: [[1, 'asc']],
            scrollX: true,
            columnDefs: [
                { target: 0, visible: false },
                {
                    targets: [19],
                    createdCell: function (td) {
                        $(td).css('background-color', '#fde7f0');
                    }
                },
                {
                    targets: [20],
                    createdCell: function (td) {
                        $(td).css('background-color', '#e7f0fd');
                    }
                }
            ],
            buttons: [{
                extend: 'excelHtml5',
                className: 'boton',
                text: '<span class="material-symbols-outlined">docs</span>',
                titleAttr: 'Exportar a Excel',
                exportOptions: {
                    columns: ':visible'
                }
            }]
        });
    }

    $('#comites-estad').on('change', '#year_select', function () {
        anioEstadistica = $(this).val();
        cargarTablaComites();
    });

    $('#seguimientos-estad').on('change', '#year_select_seg', function () {
        anioSeguimiento = $(this).val();
        cargarTablaSeguimientos();
    });

    var anioTotales = anioEstadistica;
    var tablaTotalesLista = false;

    function cargarTablaTotales() {
        $('#tabla_totales').DataTable({
            destroy: true,
            dom: '<"estad-barra d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"<"estad-controles d-flex align-items-center gap-2"<"#anio-estadistica-tot">B>f>rtip',
            initComplete: function () {
                var opciones = aniosEstadistica.map(function (anio) {
                    var seleccionado = anio === anioTotales ? ' selected' : '';
                    return '<option value="' + anio + '"' + seleccionado + '>' + anio + '</option>';
                }).join('');
                $(this.api().table().container()).find('#anio-estadistica-tot').html(
                    '<label class="mb-0" for="year_select_tot">Año</label><select id="year_select_tot" class="form-select w-auto">' + opciones + '</select>'
                );
            },
            ajax: {
                url: '/funciones/estadisticas_func',
                method: 'POST',
                data: {
                    action: 'completa-totales',
                    year: anioTotales
                },
                dataSrc: 'data'
            },
            columns: [{
                    data: 'id',
                    title: 'ID'
                },
                {
                    data: 'folio',
                    title: 'Folio'
                },
                {
                    data: 'usuario',
                    title: 'Usuario'
                },
                {
                    data: 'estatus',
                    title: 'Estatus'
                },
                {
                    data: 'year',
                    title: 'Año'
                },
                {
                    data: 'mes',
                    title: 'Mes'
                },
                {
                    data: 'dia',
                    title: 'Día'
                },
                {
                    data: 'municipio',
                    title: 'Municipio'
                },
                {
                    data: 'colonia',
                    title: 'Colonia'
                },
                {
                    data: 'ambito',
                    title: 'Ambito'
                },
                {
                    title: 'Ramo / Equivalente',
                    render: function(data, type, row) {
                        if (row.ramo === null || row.ramo === '') {
                            return '';
                        }
                        return row.ramo + ' - ' + row.ramo_descripcion;
                    }
                },
                {
                    data: 'tipo_accion',
                    title: 'Tipo de Acción'
                },
                {
                    data: 'presupuesto',
                    title: 'Presupuesto',
                    render: function(data, type, row) {
                        if (data === null || data === '') {
                            if (String(row.orden) === '1') {
                                return '';
                            }
                            return 'N/A';
                        }
                        if (type === 'display' || type === 'filter') {
                            return new Intl.NumberFormat('es-MX', {
                                style: 'currency',
                                currency: 'MXN'
                            }).format(data);
                        }
                        return data;
                    }
                },
                {
                    data: 'programa',
                    title: 'Programa/Obra'
                },
                {
                    data: 'ie',
                    title: 'IE'
                },
                {
                    data: 'integrantes',
                    title: 'Integrantes'
                },
                {
                    data: 'integrantes_m',
                    title: 'Integrantes Mujeres'
                },
                {
                    data: 'integrantes_h',
                    title: 'Integrantes Hombres'
                },
                {
                    data: 'beneficiarios',
                    title: 'Beneficiarios'
                },
                {
                    data: 'beneficiarios_m',
                    title: 'Beneficiarios Mujeres'
                },
                {
                    data: 'beneficiarios_h',
                    title: 'Beneficiarios Hombres'
                },
                {
                    data: 'Tripticos_CS',
                    title: 'Trípticos CS'
                },
                {
                    data: 'Tripticos_QyD',
                    title: 'Trípticos QyD'
                },
                {
                    data: 'modalidad',
                    title: 'Modalidad'
                },
                {
                    data: 'total_promocionales',
                    title: 'Promocionales'
                },
                {
                    data: 'Bolsa_Eco',
                    title: 'Bolsa Eco'
                },
                {
                    data: 'Playera',
                    title: 'Playera'
                },
                {
                    data: 'Gorra',
                    title: 'Gorra'
                },
                {
                    data: 'Libreta',
                    title: 'Libreta'
                },
                {
                    data: 'Paraguas',
                    title: 'Paraguas'
                },
                {
                    data: 'Vasos',
                    title: 'Vasos'
                },
                {
                    data: 'Plumas',
                    title: 'Plumas'
                },
                {
                    data: 'comentarios',
                    title: 'Comentarios'
                },
                {
                    data: 'revision',
                    title: 'Revisado'
                },
                {
                    data: 'orden',
                    title: 'Orden'
                }
            ],
            order: [
                [34, 'asc'],
                [1, 'asc']
            ],
            scrollX: true,
            columnDefs: [{
                    targets: [0, 34],
                    visible: false
                },
                {
                    targets: [15, 18],
                    createdCell: function(td) {
                        $(td).css('background-color', '#fde7f0');
                    }
                },
                {
                    targets: [16, 19],
                    createdCell: function(td) {
                        $(td).css('background-color', '#e7f0fd');
                    }
                }
            ],
            buttons: [{
                extend: 'excelHtml5',
                className: 'boton',
                text: '<span class="material-symbols-outlined">docs</span>',
                titleAttr: 'Exportar a Excel',
                exportOptions: {
                    columns: ':visible'
                }
            }]
        });
    }

    $('#totales-estad').on('change', '#year_select_tot', function () {
        anioTotales = $(this).val();
        cargarTablaTotales();
    });

    $('button[data-bs-target="#nav-profile2"]').on('shown.bs.tab', function () {
        if (!tablaSeguimientosLista) {
            cargarTablaSeguimientos();
            tablaSeguimientosLista = true;
        } else {
            $('#tabla_seguimientos_estad').DataTable().columns.adjust();
        }
    });

    $('button[data-bs-target="#nav-contact3"]').on('shown.bs.tab', function () {
        if (!tablaTotalesLista) {
            cargarTablaTotales();
            tablaTotalesLista = true;
        } else {
            $('#tabla_totales').DataTable().columns.adjust();
        }
    });

    $(document).ready(function() {
        cargarTablaComites();
    });
</script>