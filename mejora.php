<?php
$costura_id = [vg_costura_id];

$operacion = [vg_operacion];

$vglinea_ = [vg_linea];

$linea = 'L-' . $vglinea_;

$usuario = [usr_login];

$tiempo_estimado = "1:50";

$operario_avance_meta_dia = "20 / 270<br>10.7%";

$linea_avance_meta_dia = "50 / 300<br>14.3%";

$aray_uri = explode("/", $_SERVER['REQUEST_URI']);
array_pop($aray_uri); // Eliminar el último elemento (nombre del archivo)
array_pop($aray_uri);
array_push($aray_uri, "");
$uri = implode("/",$aray_uri);

$api = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$uri;

// CSS y JS de Bootstrap 5
echo "<link rel='stylesheet' href='".sc_url_library("prj","bootstrap5","css/bootstrap.min.css")."' />";
echo "<link rel='stylesheet' href='../_lib/css/css_ciclo.css' />";
echo "<link rel='stylesheet' href='../_lib/css/sweetalert2.min.css' />";

// Nota: Para JS, usa el script tag en lugar de link
echo "<script src='".sc_url_library("prj","bootstrap5","js/bootstrap.bundle.min.js")."'></script>";
echo "<script src='../_lib/js/js_ciclo.js?rand=".rand()."'></script>";
echo "<script src='../_lib/js/sweetalert2.all.min.js'></script>";
//echo "<script src='".sc_url_library("prj", "mantenimiento_control_piso", "js/ciclo.js?rand=".rand())."' />";

echo <<<HTML
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Control de Piso</title>
    </head>

    <body>
		<input type="hidden" name="costura_id" id="costura_id" value="$costura_id">
        <input type="hidden" name="ciclo_id" id="ciclo_id" value="0">
        <input type="hidden" name="api" id="api" value="$api">
        <input type="hidden" name="operacion" id="operacion" value="$operacion">
        <input type="hidden" name="linea" id="linea" value="$vglinea_">
        <input type="hidden" name="usuario" id="usuario" value="$usuario">
        <!-- Main Layout Structure -->
        <div class="layout-container">
            <!-- Header Information -->
            <!-- Botones superiores -->
            <div class="top-controls-section">
                <button class="control-btn back-btn" id="btnatras">
                    Volver
                </button>
                <button class="control-btn exit-btn" id="btnsalir">
                    Salir
                </button>
            </div>

            <div class="header-section header-top">
                <div>$operacion</div>
                <div>$usuario</div>
            </div>

            <div class="header-section header-bottom">
                <div>Tiempo Estimado: $tiempo_estimado</div>
                <div>$linea</div>
            </div>

            <!-- Main Button Area -->
            <div class="main-button-section bg-inicio" id="btns">
                <button id="btnempezar" class="start-button">INICIO</button>
            </div>

            <!-- Metrics Section -->
            <div class="metrics-section">
                <!-- Left Column - Avance/Meta -->
                <div class="metric-column">
                    <div class="metric-title">Operario<br>Avance / Meta Día</div>
                    <div class="svg-container">
                        <svg xmlns="http://www.w3.org/2000/svg" class="indicator-svg" xml:space="preserve" viewBox="0 0 73.935 73.935">
                            <path d="M52.279 73.935H21.656L0 52.279V21.655L21.655 0H52.28l21.655 21.655V52.28L52.279 73.935zm-29.381-3h28.139l19.898-19.897v-28.14L51.037 2.999H22.898L3 22.897v28.14l19.898 19.898z" />
                        </svg>
                        <div class="indicator-value">$operario_avance_meta_dia</div>
                    </div>
                </div>

                <!-- Middle Column - Timer -->
                <div class="metric-column">
                    <div class="metric-display">
                        <div class="timer-container">
                            <span id="timerDisplay">00:00:00</span>
                        </div>
                    </div>
                    <div class="svg-container">
                        <svg xmlns="" class="triangle-svg" viewBox="0 0 32 32">

                        </svg>
                        <div class="reprocesos-value">6</div>
                        <div class="reprocesos-label">REPROCESOS</div>
                    </div>
                </div>

                <!-- Right Column - Avance Esperado -->
                <div class="metric-column">
                    <div class="metric-title">Linea<br>Avance / Meta Dia</div>
                    <div class="svg-container">
                        <svg xmlns="http://www.w3.org/2000/svg" class="indicator-svg" xml:space="preserve" viewBox="0 0 73.935 73.935">
                            <path d="M52.279 73.935H21.656L0 52.279V21.655L21.655 0H52.28l21.655 21.655V52.28L52.279 73.935zm-29.381-3h28.139l19.898-19.897v-28.14L51.037 2.999H22.898L3 22.897v28.14l19.898 19.898z" />
                        </svg>
                        <div class="indicator-value">$linea_avance_meta_dia</div>
                    </div>
                </div>
            </div>

            <!-- Event Buttons Grid -->
            <div class="event-grid">
                <button class="event-btn" motivoid="1" tipo="1">Desmanche</button>
                <button class="event-btn" motivoid="2" tipo="1">Reproceso</button>
                <button class="event-btn" motivoid="3" tipo="2">Soporte</button>
				<button class="event-btn" motivoid="4" tipo="1">Servicios<br>Higienicos</button>
                <button class="event-btn" motivoid="5" tipo="1">Refrigerio</button>
                <button class="event-btn" motivoid="6" tipo="1">Agua</button>
                <button class="event-btn" motivoid="8" tipo="1">Pausa<br>Activa</button>
				<button class="event-btn" motivoid="9" tipo="1">Actividad</button>
				<button class="event-btn" motivoid="10" tipo="1">Falta<br>Carga</button>
                <button class="event-btn" motivoid="11" tipo="1">Topico</button>
                <button class="event-btn" motivoid="12" tipo="1">Salud</button>
				<button class="event-btn" motivoid="13" tipo="1">Seguridad</button>
            </div>

            <!-- Footer -->
            <div class="footer-section">
                <div class="footer-item footer-light">
                    <a class="footer-link" target="_parent" href="http://192.168.150.42:8092/scriptcase/app/eCorporativoM/form_anexo_cofaco/">
                        <strong>Tiempo Acumulado</strong>
                    </a>
                </div>
                <div class="footer-item footer-dark">
                    <strong>Meta /dia : 35</strong>
                </div>
                <div class="footer-item footer-light">
                    <strong>25/02/2025 04:15</strong>
                </div>
            </div>
        </div>
    </body>
</html>
HTML;