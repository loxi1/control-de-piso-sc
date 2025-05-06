$costura_id = [vg_costura_id];

$operacion = [vg_operacion];

$linea = 'L-' . [vg_linea];

$usuario = [usr_login];

$tiempo_estimado = "1:50";


$operario_avance_meta_dia = "20 / 270<br>10.7%";

$linea_avance_meta_dia = "50 / 300<br>14.3%";

// CSS y JS de Bootstrap 5
echo "<link rel='stylesheet' href='".sc_url_library("prj","bootstrap5","css/bootstrap.min.css")."' />";
echo "<link rel='stylesheet' href='".sc_url_library("prj","mantenimiento_control_piso","c/ciclo.css")."' />";
// Nota: Para JS, usa el script tag en lugar de link
echo "<script src='".sc_url_library("prj","bootstrap5","js/bootstrap.bundle.min.js")."'></script>";
echo "<script src='".sc_url_library("prj","mantenimiento_control_piso","js/ciclo.js")."'></script>";
$hidd = '<input type="hidden" name="costura_id" id="costura_id" value=".$costura_id.">';
echo <<<HTML
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Control de Piso</title>
    </head>

    <body>
        <input type="hidden" name="ciclo_id" id="ciclo_id" value="0">
        <!-- Main Layout Structure -->
        <div class="layout-container">
            <!-- Header Information -->





            /*BOTONES VOLVER Y SALIR*/
            <!-- Botones superiores -->
            <div class="top-controls-section">
                <button class="control-btn back-btn" onclick="returnToMenu()">
                    Volver
                </button>
                <button class="control-btn exit-btn" onclick="logoutScriptCase()">
                    Salir
                </button>
            </div>

            <script>
                // Función para volver al menú de confecciones
                function returnToMenu() {
                    try {
                        // Método preferido para ScriptCase
                        if (typeof parent.sc_redirect === 'function') {
                            parent.sc_redirect('http://192.168.150.42:8092/scriptcase/app/eCorporativoM/form_costura_test/index.php');
                        }
                        // Método alternativo
                        else if (typeof window.top.location.href !== 'undefined') {
                            window.top.location.href = 'http://192.168.150.42:8092/scriptcase/app/eCorporativoM/form_costura_test/index.php';
                        }
                        // Último recurso
                        else {
                            window.location.href = 'http://192.168.150.42:8092/scriptcase/app/eCorporativoM/form_costura_test/index.php';
                        }
                    } catch (e) {
                        console.error("Error al redirigir:", e);
                        // Redirección simple como fallback
                        window.location.href = 'http://192.168.150.42:8092/scriptcase/app/eCorporativoM/form_costura_test/index.php';
                    }
                }

                // Función de salir (ya funciona)
                function logoutScriptCase() {
                    window.top.location.href = 'http://192.168.150.42:8092/scriptcase/app/eCorporativoM/app_Login/';
                }
            </script>

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

        <script>
            // Variables para el temporizador
            let timer;
            let seconds = 0;
            let isRunning = false;
            const btnempezar = document.getElementById('btnempezar');
            const timerDisplay = document.getElementById('timerDisplay');

            // Formatear tiempo
            function formatTime(totalSeconds) {
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                return [
                    hours.toString().padStart(2, '0'),
                    minutes.toString().padStart(2, '0'),
                    seconds.toString().padStart(2, '0')
                ].join(':');
            }

            // Actualizar temporizador
            function updateTimer() {
                seconds++;
                timerDisplay.textContent = formatTime(seconds);
            }

            // Iniciar temporizador
            btnempezar.addEventListener('click', function() {
                if (!isRunning) {
                    iniciar();
                } else {
                    parar();
                }
            });
            function iniciar() {
                isRunning = true;
                seconds = 0;
                timerDisplay.textContent = "00:00:00";
                timer = setInterval(updateTimer, 1000);
                btnempezar.textContent = 'FINALIZAR';
            }

            function parar() {
                isRunning = false;
                clearInterval(timer);
                btnempezar.textContent = 'INICIO';
                timerDisplay.textContent = "00:00:00";
            }

            // Función para abrir formularios de ScriptCase
            function openScriptCaseForm(formName) {
                // Adapta esta URL a tu implementación en ScriptCase
                window.location.href = 'app_scriptcase.php?form=' + encodeURIComponent(formName) +
                    '&operator=VIVIANA%20LOPEZ&line=LINEA%201&task=Preparar%203%20etqs%20DOBL+%20PO';
            }

            // Limpiar al cerrar
            window.addEventListener('beforeunload', function() {
                if (isRunning) {
                    clearInterval(timer);
                }
            });
        </script>
    </body>

    </html>
    HTML;