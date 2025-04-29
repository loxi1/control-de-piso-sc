<?php
//[vg_costura_id] = $costura_id; 

//[vg_operacion] = $operacion;

//[vg_linea] = $linea;

$costura_id = [vg_costura_id]; 

$operacion = [vg_operacion];

$linea = 'L-' . [vg_linea];

$usuario   = [usr_login];

$tiempo_estimado = "1:50";


$operario_avance_meta_dia = "20 / 270<br>10.7%";

$linea_avance_meta_dia = "50 / 300<br>14.3%";

$host = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
$urls = $_SERVER['REDIRECT_URL'];
$api = $host.str_replace("ctrl_costura/index.php", "",$urls);

// CSS y JS de Bootstrap 5
echo "<link rel='stylesheet' href='".sc_url_library("prj","bootstrap5","css/bootstrap.min.css")."' />";
// Nota: Para JS, usa el script tag en lugar de link
echo "<script src='".sc_url_library("prj","bootstrap5","js/bootstrap.bundle.min.js")."'></script>";

echo <<<HTML
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Control de Piso</title>


<style>
    /* Base normalization */
    html, body {
        height: 100%;
        width: 100%;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    .layout-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
        width: 100%;
    }

    /* Header sections */
    .header-section {
        width: 100%;
        padding: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
		font-size: clamp(0.8rem, 2vw, 0.9rem);
    }

    .header-top {
        background-color: #DFECE4;
        border-bottom: 1px solid #cde0d5;
    }

    .header-bottom {
        background-color: #E1F5DA;
        border-bottom: 1px solid #d0e6c8;
    }

    /* Main button section */
    .main-button-section {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        background-color: #3BA873;
		margin-top: 3px; /* Agrega espacio arriba del botón */
	    margin-left: 3px;   /* Margen izquierdo */
        margin-right: 3px;  /* Margen derecho */
		
		
    }

    .start-button {
        width: 100%;
        height: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: transparent;
        border: none;
        color: white;
        font-size: clamp(3rem, 8vw, 6rem);
        font-weight: bold;
        cursor: pointer;
    }

    /* Metrics section */
    .metrics-section {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        border-top: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
    }

    .metric-column {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 5px;
    }

    .metric-title {
        font-size: clamp(0.8rem, 2vw, 1rem);
        font-weight: bold;
        color: #525850;
        text-align: center;
        margin-bottom: 5px;
    }

    .metric-display {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Timer styling */
    .timer-container {
        border: 2px solid #a19685;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: clamp(1.2rem, 4vw, 2rem);
        text-align: center;
        min-width: 120px;
    }

    /* SVG indicators */
    .svg-container {
        position: relative;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .indicator-svg {
        width: clamp(70px, 15vw, 100px);
        height: auto;
    }

    .triangle-svg {
        width: clamp(60px, 12vw, 80px);
        height: auto;
    }

    .indicator-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: clamp(0.8rem, 2vw, 1rem);
        font-weight: bold;
        color: green;
        text-align: center;
    }
    
    .reprocesos-value {
        position: absolute;
        top: 40%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: clamp(1.5rem, 4vw, 2rem);
        font-weight: bold;
        color: red;
    }
    
    .reprocesos-label {
        position: absolute;
        top: 85%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: clamp(0.7rem, 1.8vw, 0.9rem);
        font-weight: bold;
        color: red;
    }

    /* Event buttons grid */
    .event-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding: 10px;
        height: auto;
    }

    .event-btn {
        background-color: #3BA873;
        color: white;
        border: 1px solid #626262;
        border-radius: 8px;
        padding: 8px 5px;
        font-size: clamp(0.7rem, 2vw, 0.9rem);
        font-weight: bold;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        aspect-ratio: 2/1;
    }

    /* Footer section */
    .footer-section {
        display: flex;
        width: 100%;
        min-height: 40px;
    }

    .footer-item {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 8px;
        font-size: clamp(0.6rem, 1.5vw, 0.8rem);
        font-weight: bold;
    }

    .footer-light {
        background-color: #B6D4C5;
    }

    .footer-dark {
        background-color: #3BA873;
        color: white;
    }

    .footer-link {
        text-decoration: none;
        color: inherit;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
	
	
	/* Agrega esto en tu sección de estilos */
.top-controls-section {
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 5px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}



/*BOTONES VOLVER Y SALIR*/
.control-btn {
    padding: 5px 15px;
    border: none;
    border-radius: 4px;
    font-weight: bold;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.back-btn {
    background-color: #6c757d;
    color: white;
}

.back-btn:hover {
    background-color: #5a6268;
}

.exit-btn {
    background-color: #dc3545;
    color: white;
}

.exit-btn:hover {
    background-color: #c82333;
}

/* Ajuste para el header-top para mantener el espacio */
.header-top {
    margin-top: 0; /* Elimina cualquier margen superior existente */
}
	
	
	

	
	
	
	

    /* Mobile optimizations */
    @media (max-width: 576px) {
        .metrics-section {
         padding: 3px;
        }
    
        .event-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
            padding: 5px;
        }
        
        .event-btn {
            padding: 5px 2px;
            aspect-ratio: 1.5/1;
        }
        
        .timer-container {
            padding: 5px 10px;
            min-width: 90px;
        }
		
		.top-controls-section {
        padding: 3px;
    }
    
    .control-btn {
        padding: 3px 10px;
        font-size: 0.8rem;
    }
    
    .header-top, .header-bottom {
        font-size: 0.9rem;
    }
		
		
    }
</style>

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
    // Obtenemos el ID de costura desde una variable de PHP que ya tienes definida
    const costuraId = "<?php echo $costura_id; ?>";
    
    // Construimos la URL de redirección con el parámetro del ID
    const redirectUrl = `http://192.168.150.42:8092/scriptcase/app/eCorporativoM/form_costura_volver/index.php?costura_id=$costura_id`;
    
    // Método preferido para ScriptCase
    if (typeof parent.sc_redirect === 'function') {
      parent.sc_redirect(redirectUrl);
    } 
    // Método alternativo
    else if (typeof window.top.location.href !== 'undefined') {
      window.top.location.href = redirectUrl;
    } 
    // Último recurso
    else {
      window.location.href = redirectUrl;
    }
  } catch(e) {
    console.error("Error al redirigir:", e);
    // Redirección simple como fallback
    window.location.href = 'http://192.168.150.42:8092/scriptcase/app/eCorporativoM/form_costura_volver/index.php';
  }
}


// Función de salir (ya funciona)
function logoutScriptCase() {
    window.top.location.href = 'http://192.168.150.42:8092/scriptcase/app/eCorporativoM/app_Login/';
}
</script>
	
	
	
	</head>
	</body>
		<input type="hidden" name="costura_id" id="costura_id" value="$costura_id">
        <input type="hidden" name="ciclo_id" id="ciclo_id" value="0">
        <input type="hidden" name="api" id="api" value="$api">
	
    <div class="header-section header-top">
        <div>$operacion</div>
        <div>$costura_id</div>
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
                    <path d="M52.279 73.935H21.656L0 52.279V21.655L21.655 0H52.28l21.655 21.655V52.28L52.279 73.935zm-29.381-3h28.139l19.898-19.897v-28.14L51.037 2.999H22.898L3 22.897v28.14l19.898 19.898z"/>
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
                    <path d="M52.279 73.935H21.656L0 52.279V21.655L21.655 0H52.28l21.655 21.655V52.28L52.279 73.935zm-29.381-3h28.139l19.898-19.897v-28.14L51.037 2.999H22.898L3 22.897v28.14l19.898 19.898z"/>
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

