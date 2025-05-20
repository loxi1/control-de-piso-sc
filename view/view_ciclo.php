<?php
$costura_id = [vg_costura_id];

$op = [vg_op];

$operacion = [vg_operacion];

$vglinea_ = [vg_linea];

$linea = $vglinea_."B";

$usuario = [usr_login];

$usuario_nombre = [usr_name];

$tiempo_estimado = [vg_tiempo_estimado]; //Expresado en minutos
$tiempo_es = !empty($tiempo_estimado) ? number_format($tiempo_estimado, 2, '.', '') : "0.00";
$linea_avance_meta_dia = "50 / 300<br>14.3%";

$aray_uri = explode("/", $_SERVER['REQUEST_URI']);
array_pop($aray_uri); // Eliminar el último elemento (nombre del archivo)
array_pop($aray_uri);
array_push($aray_uri, "");
$uri = implode("/", $aray_uri);

$api = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $uri;

// CSS y JS de Bootstrap 5
echo "<link rel='stylesheet' href='" . sc_url_library("prj", "bootstrap5", "css/bootstrap.min.css") . "' />";
echo "<link rel='stylesheet' href='../_lib/css/css_ciclo.css' />";
echo "<link rel='stylesheet' href='../_lib/css/sweetalert2.min.css' />";

// Nota: Para JS, usa el script tag en lugar de link
echo "<script src='" . sc_url_library("prj", "bootstrap5", "js/bootstrap.bundle.min.js") . "'></script>";
echo "<script src='../_lib/js/js_ciclo.js?rand=" . rand() . "'></script>";
echo "<script src='../_lib/js/sweetalert2.all.min.js'></script>";
//echo "<script src='".sc_url_library("prj", "mantenimiento_control_piso", "js/ciclo.js?rand=".rand())."' />";

// Consulta par obtener ciclos sin terminar, activos menores iguales a la fecha actual
$sql = "select ciclo_id
from ciclo WHERE usuario_registra='$usuario' and estado_id=1
and fecha_creacion <= now() order by ciclo_id desc limit 1;";

sc_lookup(rs_data_sybase, $sql);

$tipo = null;
$evento = null;
$segundos = 0;
$cicloid = 0;

if (!empty({rs_data_sybase}[0])) {
    $sql = "select ciclo_id, motivo_id, motivo_tipo, DATEDIFF(CURDATE(), fecha_creacion) AS dias_diferencia,
    TIMESTAMPDIFF(SECOND, tiempo_inicio, NOW()) AS segundos
    from ciclo WHERE ciclo_id=".{rs_data_sybase}[0][0]."
    AND (tiempo_trascurrido IS NULL OR tiempo_trascurrido = '00:00:00')
    HAVING dias_diferencia IN (0, 1)";
    
    if({extciclo}[0][3] == 0 || {extciclo}[0][3] == 1) {
        $cicloid = {extciclo}[0][0];
        if (!empty({extciclo}[0][1])) {
            //tipo=2 Soporte
            $tipo = {extciclo}[0][2];
            $tb = $tipo == 2 ? "evento_soporte" : "evento_normal";
            $co = $tipo == 2 ? "evento_soporte_id" : "evento_normal_id";
            
            $direciona = ($tipo == 2) ? "blank_soporte_ciclo" : "blank_evento_ciclo_normal";
            $direciona = $api.$direciona;
            if(!empty($cicloid)) {
                $sql = "select $co as evento from $tb where ciclo_id=$cicloid limit 1;";
                sc_lookup(rs_data_evento, $sql);

                if(!empty({rs_data_evento}[0][0])) {
                    header("Location: $direciona/"); /* Redirección del navegador */
                    exit;
                }
            }        
        } else {
            $segundos = intval({extciclo}[0][4]);
        }
    }    
}
/*

 */

echo <<<HTML
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Costura</title>
    </head>

    <body>
        <!-- Spinner -->
        <div id="preloader" class="d-flex d-none justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 1050; opacity:0.8;">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

		<input type="hidden" name="costura_id" id="costura_id" value="$costura_id">
        <input type="hidden" name="ciclo_id" id="ciclo_id" value="$cicloid">
        <input type="hidden" name="api" id="api" value="$api">
        <input type="hidden" name="operacion" id="operacion" value="$operacion">
        <input type="hidden" name="linea" id="linea" value="$linea">
        <input type="hidden" name="usuario" id="usuario" value="$usuario">
        <input type="hidden" name="tiempo_estimado" id="tiempo_estimado" value="$tiempo_estimado">
        <input type="hidden" name="nombre_usuario" id="nombre_usuario" value="$usuario_nombre">
        <input type="hidden" name="es_reproceso" id="es_reproceso" value="0">   
        <input type="hidden" name="es_op" id="es_op" value="$op">
        <input type="hidden" name="segundos" id="segundos" value="$segundos">
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
                <div>$usuario_nombre</div>
            </div>

            <div class="header-section header-bottom">
                <div>Tiempo Estimado: $tiempo_es</div>
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
                    <div class="metric-title">Operario<br>Eficiencia / Día</div>
                    <div class="svg-container">
                        <svg xmlns="http://www.w3.org/2000/svg" class="indicator-svg" xml:space="preserve" viewBox="0 0 73.935 73.935">
                            <path d="M52.279 73.935H21.656L0 52.279V21.655L21.655 0H52.28l21.655 21.655V52.28L52.279 73.935zm-29.381-3h28.139l19.898-19.897v-28.14L51.037 2.999H22.898L3 22.897v28.14l19.898 19.898z" />
                        </svg>
                        <div id="eficienciaxcolaborador" class="indicator-value">0.00 %</div>
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
                        <div id="reprocesos" class="reprocesos-value">0</div>
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
                        <div class="indicator-value" id="indicator-value">$linea_avance_meta_dia</div>
                    </div>
                </div>
            </div>

            <!-- Event Buttons Grid -->
            <div class="event-grid" id="btns-eventos">
                <button class="event-btn" motivoid="1" tipo="1">Desmanche</button>
                <button class="event-btn" motivoid="2" tipo="3">Reproceso</button>
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
                    <div class="footer-link">
                        <strong>T. Improductivo</strong>
                    </div>
                </div>
                <div class="footer-item footer-dark">
                    <strong id="timp">0:00:00 hms</strong>
                </div>
                <div class="footer-item footer-light">
                    <strong id="pimp">0.0 %</strong>
                </div>
            </div>
        </div>
    </body>
</html>
HTML;
