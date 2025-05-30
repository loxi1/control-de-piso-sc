<?php

$costura_id = [vg_costura_id];

$operacion = [vg_operacion];

$vglinea_ = [vg_linea];

$linea = 'L-' . $vglinea_;

$usuario = [usr_login];

$usuario_nombre = [usr_name]; 

$evento = $_GET['evento'] ?? 0;

$tiempo_estimado = [vg_tiempo_estimado]; //Expresado en minutos

$operario_avance_meta_dia = "20 / 270<br>10.7%";

$linea_avance_meta_dia = "50 / 300<br>14.3%";

/*$aray_uri = explode("/", $_SERVER['REQUEST_URI']);
array_pop($aray_uri); // Eliminar el último elemento (nombre del archivo)
array_pop($aray_uri);
array_push($aray_uri, "");
$uri = implode("/",$aray_uri);

$api = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$uri;*/

$base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$script_dir = dirname(dirname($_SERVER['REQUEST_URI'])); // sube 2 niveles
$api = rtrim(rtrim($base_url . $script_dir, '/')).'/';

$sql = "select evento_normal_id
from evento_normal WHERE usuario_registra='$usuario' and estado=1
and fecha_creacion <= now() order by evento_normal_id desc limit 1;";

sc_lookup(rta, $sql);

if(!empty({rta}[0][0])) {
    $exec_sql = "SELECT ciclo_id, TIMESTAMPDIFF(SECOND, tiempo_inicio, NOW()) AS segundos,
                DATEDIFF(CURDATE(), fecha_creacion) AS dias_diferencia,
                motivo_tipo
                FROM evento_normal
                WHERE evento_normal_id = ".{rta}[0][0]."
                AND (tiempo_trascurrido IS NULL OR tiempo_trascurrido = '00:00:00')
                HAVING dias_diferencia IN (0, 1)";
    
    sc_lookup(ds, $exec_sql);
    
    $segundos = 0; // Inicializa la variable en caso de que no se obtenga resultado
    $ciclo = 0; // Inicializa la variable en caso de que no se obtenga resultado
    $motivo_tipo = 0;
    if(!empty({ds})) {
        if (isset({ds}[0][0])) {
            $ciclo = {ds}[0][0];
        }

        if (isset({ds}[0][1])) {
            $segundos = {ds}[0][1];
        }

        if (isset({ds}[0][3])) {
            $motivo_tipo= {ds}[0][3];
        }
    } else {
        $nueva_url = $api . "blank_evento_costura/";
        header("Location: $nueva_url");
        exit(); // Detiene la ejecución después de redirigir
    }
} else {
    $nueva_url = $api . "blank_evento_costura/";
    header("Location: $nueva_url");
    exit(); // Detiene la ejecución después de redirigir
}

// CSS y JS de Bootstrap 5
echo "<link rel='stylesheet' href='".sc_url_library("prj","bootstrap5","css/bootstrap.min.css")."' />";
echo "<link rel='stylesheet' href='../_lib/css/css_ciclo.css' />";
//echo "<link rel='stylesheet' href='".sc_url_library("prj", "mantenimiento_control_piso", "css/ciclo.css")."' />";
// Nota: Para JS, usa el script tag en lugar de link
echo "<script src='".sc_url_library("prj","bootstrap5","js/bootstrap.bundle.min.js")."'></script>";
echo "<script src='../_lib/js/js_evento_ciclo.js?rand=".rand()."'></script>";
//echo "<script href='".sc_url_library("prj", "mantenimiento_control_piso", "js/evento.js?rand=".rand())."' />";

echo <<<HTML
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Evento - Normal</title>
    </head>
    <body>
        <!-- Spinner -->
        <div id="preloader" class="d-flex d-none justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 1050; opacity:0.8;">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
        <input type="hidden" name="costura_id" id="costura_id" value="$costura_id">
        <input type="hidden" name="evento_id" id="evento_id" value="$evento">
        <input type="hidden" name="ciclo_id" id="ciclo_id" value="$ciclo">
        <input type="hidden" name="api" id="api" value="$api">
        <input type="hidden" name="operacion" id="operacion" value="$operacion">
        <input type="hidden" name="linea" id="linea" value="$vglinea_">
        <input type="hidden" name="usuario" id="usuario" value="$usuario">
        <input type="hidden" name="segundos" id="segundos" value="$segundos">
        <input type="hidden" name="nombre_usuario" id="nombre_usuario" value="$usuario_nombre">
        <input type="hidden" name="motivo_tipo" id="motivo_tipo" value="$motivo_tipo">
        <!-- Main Layout Structure -->
        <div class="layout-container">
            <!-- Header Information -->
            <div class="top-controls-section">
                <button class="control-btn back-btn invisible" id="btnatras">
                    Volver
                </button>
                <button class="control-btn exit-btn invisible" id="btnsalir">
                    Salir
                </button>
            </div>
            <div class="header-section header-top">
                <div>$operacion</div>
                <div>$usuario_nombre</div>
            </div>

            <div class="header-section header-bottom">
                <div>Tiempo Estimado: $tiempo_estimado</div>
                <div>$linea</div>
            </div>

            <!-- Main Button Area -->
            <div class="main-button-section bg-inicio mb-5" id="btns">
                <button id="btnempezar" class="start-button">INICIO</button>
            </div>

            <div class="metrics-section mb-5"> 
                <!-- Middle Column - Timer -->
                <div class="metric-column">
                    <div class="metric-display">
                        <div class="timer-container">
                            <span id="timerDisplay">00:00:00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer-section mt-5">
                <div class="footer-item footer-light">
                    <a class="footer-link" target="_parent" href="http://192.168.150.42:8092/scriptcase/app/eCorporativoM/form_anexo_cofaco/">
                        <strong>T. Improductivo</strong>
                    </a>
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