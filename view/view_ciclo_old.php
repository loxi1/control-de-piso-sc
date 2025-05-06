<?php
$tiempo_estimado = "1:50";

$costura_id = isset($_GET['costura_id']) ? $_GET['costura_id'] : 0;

$operario_avance_meta_dia = "20 / 270<br>10.7%";

$linea_avance_meta_dia = "50 / 300<br>14.3%"; 

$host = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
$urls = $_SERVER['REDIRECT_URL'];
$api = $host.str_replace("blank_evento_normal/index.php", "",$urls);
?>
<html>

    <head>
        <!--SC_PAGE_CHARSET-->
        <!--SC_JS_LIB-->
        <link rel="stylesheet" type="text/css" href="jg_costura.css" />
        <script type="text/javascript" src="jg_costura.js"></script>
        <?php $boos = sc_url_library("prj","bootstrap5","css/bootstrap.min.css");?>
        <link rel='stylesheet' href='<?php echo $boos;?>' />
        <title><!--SC_PAGE_TITLE--></title>
    </head>

    <body>
        <input type="hidden" name="costura_id" id="costura_id" value="<?php echo $costura_id;?>">
        <input type="hidden" name="ciclo_id" id="ciclo_id" value="0">
        <input type="hidden" name="api" id="api" value="<?php echo $api;?>">
        <!-- Main Layout Structure -->
        <div class="layout-container">
            <!-- Header Information -->
            <!-- Botones superiores -->
            <div class="top-controls-section">
                <button class="control-btn back-btn" onclick="returnToMenu()">
                    Volver
                </button>
                <button class="control-btn exit-btn" onclick="logoutScriptCase()">
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
            <div class="main-button-section">
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
                        <div class="indicator-value"><?php echo $operario_avance_meta_dia;?></div>
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
                        <div class="indicator-value"><?php echo $linea_avance_meta_dia;?></div>
                    </div>
                </div>
            </div>

            <!-- Event Buttons Grid -->
            <div class="event-grid">
                <button class="event-btn" onclick="openScriptCaseForm('servicios_higienicos')">Servicios<br>Higienicos</button>
                <button class="event-btn" onclick="openScriptCaseForm('pausa_activa')">Pausa<br>Activa</button>
                <button class="event-btn" onclick="openScriptCaseForm('falta_carga')">Falta<br>Carga</button>
                <button class="event-btn" onclick="openScriptCaseForm('falla')">FALLA</button>
                <button class="event-btn" onclick="openScriptCaseForm('reunion_coordinacion')">Reunion<br>Coordinacion</button>
                <button class="event-btn" onclick="openScriptCaseForm('reproceso')">Reproceso</button>
                <button class="event-btn" onclick="openScriptCaseForm('refrigerio')">Refrigerio</button>
                <button class="event-btn" onclick="openScriptCaseForm('sos')">SOS</button>
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
        <form {SC_FORM_ATTR}>
            <!--SC_FORM_HIDDEN-->
            <!--SC_FIELD_LABEL_my_field-->
            <br />
            <!--SC_FIELD_INI_my_field-->
            <input {SC_FIELD_INFO_my_field} class="{SC_FIELD_CLASS}" type="text" />
            <!--SC_FIELD_END_my_field-->
            <br />
            <input type="button" {SC_FORM_SUBMIT_INFO} />
        </form>
    </body>
</html>