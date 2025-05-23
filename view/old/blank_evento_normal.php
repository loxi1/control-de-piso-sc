<?php
$base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['REQUEST_URI']); // sube 2 niveles
$api = rtrim(rtrim($base_url . $script_dir, '/')).'/';

$costura_id = 1122;

$operacion = "0000378";

$vglinea_ = "16";

$linea = $vglinea_."B";

$usuario = "ti.desarrollo10"; 

$usuario_nombre = "Anibal Cayetano";

$tiempo_estimado = 4.3; //Expresado en minutos

$operario_avance_meta_dia = "20 / 270<br>10.7%";

$linea_avance_meta_dia = "50 / 300<br>14.3%";
$tiempo_es = !empty($tiempo_estimado) ? number_format($tiempo_estimado, 2, '.', '') : "0.00";

$operario_avance_meta_dia = "";

// CSS y JS de Bootstrap 5
echo "<link rel='stylesheet' href='".sc_url_library("prj","bootstrap5","css/bootstrap.min.css")."' />";
echo "<link rel='stylesheet' href='../_lib/css/css_ciclo.css' />";
echo "<link rel='stylesheet' href='../_lib/css/sweetalert2.min.css' />";

// Nota: Para JS, usa el script tag en lugar de link
echo "<script src='".sc_url_library("prj","bootstrap5","js/bootstrap.bundle.min.js")."'></script>";
echo "<script src='../_lib/js/js_ciclo.js?rand=".rand()."'></script>";
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
    sc_lookup(extciclo, $sql);
    
    if({extciclo}[0][3] == 0 || {extciclo}[0][3] == 1) {
        $cicloid = {extciclo}[0][0];
        if (!empty({extciclo}[0][1])) {
            //tipo=2 Soporte
            $tipo = {extciclo}[0][2];
            $tb = $tipo == 2 ? "evento_soporte" : "evento_normal";
            $co = $tipo == 2 ? "evento_soporte_id" : "evento_normal_id";
            
            $direciona = $tipo == 2 ? "blank_soporte_ciclo" : "blank_evento_ciclo_normal";
            $direciona = $api.$direciona;
            if(!empty($cicloid)) {
                $sql = "select $co as evento from $tb where ciclo_id=$cicloid limit 1;";
                sc_lookup(rs_data_evento, $sql);
				$evento = !empty({rs_data_evento}[0][0]) ? intval({rs_data_evento}[0][0]) : 0;
                if($evento>0) {
					//print_r("<---> <--xxx-->");
					//print_r($direciona);
                    header("Location: $direciona/"); /* Redirección del navegador */
                    exit();
                }
            }        
        } else {
            $segundos = intval({extciclo}[0][4]);
        }
    }   
}

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
        <div id="preloader" class="d-flex d-none justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 1050; opacity:0.5;">
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
                    <div id="eficienciaxcolaborador" class="indicator-value">$operario_avance_meta_dia</div>
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
                    <div class="indicator-meta-value" id="indicator-meta-value">$linea_avance_meta_dia</div>
                </div>
            </div>

            <!-- Event Buttons Grid -->
            <div class="event-grid" id="btns-eventos">
                
            </div>

            <!-- Footer -->
            <div class="footer-section">
                <div class="footer-item footer-light">
                    <div class="footer-link">
						<strong style="font-size: 16px; font-weight: bold;">T. Improductivo</strong>
                    </div>
                </div>
                <div class="footer-item footer-dark">
					<strong id="timp" style="font-size: 18px;">0:00:00 hms</strong>
                </div>
                <div class="footer-item footer-light">
					<strong id="pimp" style="font-size: 18px;">0.0 %</strong>
                </div>
            </div>
        </div>
    </body>
</html>
HTML;