<?php
$titulo = "SICOCS | Inicio";
require ROOT . '/app/Views/layouts/header.php';
$anioActual = (int) date('Y');
$years = $conexion->query('SELECT year FROM comites_year ORDER BY year DESC')->fetchAll(PDO::FETCH_COLUMN);
$years = array_map('intval', $years);
if ($years === []) {
    $years = [$anioActual];
}
$anioSeleccionado = in_array($anioActual, $years, true) ? $anioActual : $years[0];
?>
<section class="mb-5 p-0 min-h">
    <div class="caja_banner">
        <div class="tiene_logo">
            <img src="./assets/img/logo_color.png" alt="logo contraloría social">
        </div>
        <div class="banner_cover portada_2"></div>
    </div>
    <div class="contenedor_general">
        <div class="d-flex justify-content-center flex-wrap gap-5">
            <a class="boton boton_primario" href="/agregar-comite">Nuevo Comité</a>
            <a class="boton boton_secundario" href="/mis-comites">Mis Comités</a>
            <a class="boton boton_terciario" href="/seguimientos">Seguimientos</a>
        </div>
        <section class="contenido_perfil mb-3">
        </section>

        <section class="panel-inicio">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                <div>
                    <h2 class="panel-inicio-titulo">Resumen</h2>
                    <div class="line"></div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="year_select" class="form-label mb-0">Año</label>
                    <select id="year_select" class="form-select w-auto">
                        <?php foreach ($years as $year) : ?>
                            <option value="<?php echo $year; ?>" <?php echo $year === $anioSeleccionado ? 'selected' : ''; ?>><?php echo $year; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <article class="kpi-inicio">
                        <span class="kpi-inicio-icono">
                            <img src="./assets/img/Social.svg" alt="">
                        </span>
                        <div>
                            <div class="conteo_actividades">
                                <span id="total_comites" class="contador es_redondeado kpi-inicio-numero" data-value="">0</span>
                            </div>
                            <p class="kpi-inicio-etiqueta">Comités constituidos</p>
                        </div>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="kpi-inicio">
                        <span class="kpi-inicio-icono">
                            <span class="material-symbols-outlined">groups_3</span>
                        </span>
                        <div>
                            <div class="conteo_actividades">
                                <span id="total_integrantes" class="contador es_redondeado kpi-inicio-numero" data-value="">0</span>
                            </div>
                            <p class="kpi-inicio-etiqueta">Integrantes</p>
                        </div>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="kpi-inicio">
                        <span class="kpi-inicio-icono">
                            <span class="material-symbols-outlined">groups_2</span>
                        </span>
                        <div>
                            <div class="conteo_actividades">
                                <span id="total_beneficiarios" class="contador es_redondeado kpi-inicio-numero" data-value="">0</span>
                            </div>
                            <p class="kpi-inicio-etiqueta">Beneficiarios</p>
                        </div>
                    </article>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-lg-5">
                    <article class="tarjeta-grafica">
                        <h3>Estatus de comités</h3>
                        <div id="grafica_status_comites" class="grafica-google"></div>
                        <ul id="leyenda_status" class="leyenda-estatus"></ul>
                    </article>
                </div>
                <div class="col-lg-7">
                    <article class="tarjeta-grafica">
                        <h3>Integrantes y beneficiarios por género</h3>
                        <div id="grafica_genero" class="grafica-google"></div>
                    </article>
                </div>
            </div>
        </section>
    </div>
</section>



<?php
require ROOT . '/app/Views/layouts/footer.php';
?>
<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    var estadisticasIniciales = true;
    var graficasListas = false;
    var datosGrafica = null;
    var coloresEstatus = ['#ff8400', '#e7c60a', '#27ae60'];

    google.charts.load('current', { packages: ['corechart'] });
    google.charts.setOnLoadCallback(function () {
        graficasListas = true;
        if (datosGrafica) {
            dibujarGraficas();
        }
    });

    function dibujarGraficas() {
        if (!graficasListas || !datosGrafica) {
            return;
        }

        var status = datosGrafica.data_status;
        var totalStatus = status[0] + status[1] + status[2];
        var cajaStatus = document.getElementById('grafica_status_comites');
        var leyenda = document.getElementById('leyenda_status');

        leyenda.innerHTML = datosGrafica.labels_status.map(function (etiqueta, indice) {
            return '<li><span class="leyenda-punto" style="background:' + coloresEstatus[indice] + '"></span>' +
                etiqueta + ' <strong>' + status[indice] + '</strong></li>';
        }).join('');

        if (totalStatus === 0) {
            cajaStatus.innerHTML = '<p class="grafica-vacia">No hay comités en este año.</p>';
        } else {
            var dataStatus = google.visualization.arrayToDataTable([
                ['Estatus', 'Comités'],
                [datosGrafica.labels_status[0], status[0]],
                [datosGrafica.labels_status[1], status[1]],
                [datosGrafica.labels_status[2], status[2]]
            ]);
            new google.visualization.PieChart(cajaStatus).draw(dataStatus, {
                pieHole: 0.62,
                legend: { position: 'none' },
                colors: coloresEstatus,
                pieSliceText: 'none',
                backgroundColor: 'transparent',
                chartArea: { width: '90%', height: '86%', top: 8 },
                fontName: 'Arial',
                tooltip: { text: 'both' },
                sliceVisibilityThreshold: 0
            });
        }

        var mujeresI = datosGrafica.data_genero_I[0];
        var hombresI = datosGrafica.data_genero_I[1];
        var mujeresB = datosGrafica.data_genero_B[0];
        var hombresB = datosGrafica.data_genero_B[1];
        var cajaGenero = document.getElementById('grafica_genero');

        if (mujeresI + hombresI + mujeresB + hombresB === 0) {
            cajaGenero.innerHTML = '<p class="grafica-vacia">No hay integrantes ni beneficiarios en este año.</p>';
            return;
        }

        var dataGenero = google.visualization.arrayToDataTable([
            ['Género', 'Integrantes', { role: 'annotation' }, 'Beneficiarios', { role: 'annotation' }],
            ['Mujeres', mujeresI, String(mujeresI), mujeresB, String(mujeresB)],
            ['Hombres', hombresI, String(hombresI), hombresB, String(hombresB)]
        ]);
        new google.visualization.ColumnChart(cajaGenero).draw(dataGenero, {
            colors: ['#15212f', '#ff8400'],
            legend: { position: 'bottom' },
            backgroundColor: 'transparent',
            chartArea: { width: '78%', height: '62%', top: 36 },
            bar: { groupWidth: '52%' },
            annotations: { alwaysOutside: true, textStyle: { color: '#15212f', fontSize: 12, bold: true } },
            vAxis: {
                minValue: 0,
                textStyle: { color: '#5c6b7a' },
                gridlines: { color: '#e6ebf0' },
                baselineColor: '#e6ebf0'
            },
            hAxis: { textStyle: { color: '#15212f', bold: true, fontSize: 13 } },
            fontName: 'Arial'
        });
    }

    function cargarEstadisticas() {
        $.ajax({
            url: '/funciones/estadisticas_func',
            method: 'POST',
            data: {
                action: "index",
                year: $('#year_select').val()
            },
            dataType: 'json',
            success: function (data) {
                $('#total_comites')
                    .text(data.total_comites)
                    .attr('data-value', data.total_comites);
                $('#total_integrantes')
                    .text(data.total_integrantes)
                    .attr('data-value', data.total_integrantes);
                $('#total_beneficiarios')
                    .text(data.total_beneficiarios)
                    .attr('data-value', data.total_beneficiarios);
                if (estadisticasIniciales) {
                    $('.conteo_actividades').each(function () {
                        observer.observe(this);
                    });
                    estadisticasIniciales = false;
                }
                datosGrafica = data;
                dibujarGraficas();
            },
            error: function () {
                alert('Error al obtener las estadísticas.');
            }
        });
    }

    var redimensionGraficas;
    window.addEventListener('resize', function () {
        clearTimeout(redimensionGraficas);
        redimensionGraficas = setTimeout(dibujarGraficas, 150);
    });

    $(document).ready(function () {
        cargarEstadisticas();
        $('#year_select').on('change', cargarEstadisticas);
    });
</script>