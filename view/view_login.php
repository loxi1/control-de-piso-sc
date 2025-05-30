<?php
$base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['REQUEST_URI']); // sube 2 niveles
$api = rtrim(rtrim($base_url . $script_dir, '/')) . '/';
session_start();

$codigo = $_GET['codigo'] ?? null;
$id = intval($_GET['id'] ?? 0);
if(!empty($codigo) && !empty($id)) {
    $host = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'];
    $urls = $_SERVER['REDIRECT_URL'];
    $apix = $host.str_replace("blank_login_operario/index.php", "",$urls);
    $sql = "SELECT datos FROM usuario_timbrado WHERE codigo='$codigo'";
    sc_lookup(rs_data_sybase, $sql);
    $_SESSION["usr_login"]=$codigo;
    $_SESSION["permiso_id"]=$id;
    if (!empty({rs_data_sybase}[0][0])) {
        $_SESSION["usr_name"]={rs_data_sybase}[0][0];
    }
    $direciona = $apix."form_costura_operacion";
    header("Location: $direciona/"); /* Redirección del navegador */
    exit;
}
// CSS y JS de Bootstrap 5
echo "<link rel='stylesheet' href='" . sc_url_library("prj", "bootstrap5", "css/bootstrap.min.css") . "' />";
echo "<link rel='stylesheet' href='../_lib/css/sweetalert2.min.css' />";
echo "<script src='" . sc_url_library("prj", "bootstrap5", "js/bootstrap.bundle.min.js") . "'></script>";
echo "<script src='../_lib/js/js_login.js?rand=" . rand() . "'></script>";
echo "<script src='../_lib/js/sweetalert2.all.min.js'></script>";
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso al control de piso</title>
        <style>
            body {
                background: linear-gradient(135deg, #e9f0f5, #f9fbfd);
                font-family: 'Segoe UI', sans-serif;
            }

            .card-modern {
                border: none;
                border-radius: 2rem;
                overflow: hidden;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                background: white;
            }

            .svg-container {
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(to right, #eef2f7, #f5f9ff);
                padding: 2rem;
            }

            .svg-full {
                max-width: 100%;
                height: auto;
                display: block;
                margin: auto;
            }

            .fade-in-start {
                opacity: 0;
                transform: scale(0.95);
                transition: opacity 1s ease-out, transform 1s ease-out;
            }

            .fade-in-show {
                opacity: 1;
                transform: scale(1);
            }

            .form-label {
                font-weight: 600;
            }

            .btn-primary {
                background-color: #007bff;
                border: none;
                border-radius: 1rem;
            }

            @media (max-width: 768px) {
                .card-modern {
                    flex-direction: column !important; /* Cambia a vertical */
                }

                .svg-container,
                .card-modern > .col-md-6 {
                    width: 100% !important;      /* Fuerza el ancho completo */
                    max-width: 100% !important;
                    flex: 0 0 auto !important;   /* Desactiva comportamiento de columna fija */
                }

                .svg-full {
                    width: 50%;                  /* Ajusta tamaño visual */
                    height: auto;
                    margin: 0 auto;              /* Centra el SVG */
                }

                .border-formulario {
                    border-right: 2px #f5f9ff solid;
                    border-bottom: 2px #f5f9ff solid;
                    border-left: 2px #f5f9ff solid
                }
            }
        </style>
    </head>

    <body class="d-flex align-items-center justify-content-center min-vh-100">

        <!-- Preloader -->
        <div id="preloader" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-50" style="z-index: 1050;">
            <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
        <input type="hidden" name="api" id="api" value="$api">
        <!-- Main Content -->
        <main class="container px-3">
            <div class="row justify-content-center" style="--bs-gutter-x: 0 !important;">
                <div class="col-12 col-md-10 col-lg-8 card-modern d-flex flex-column flex-md-row">                    
                    <!-- SVG Area -->
                    <div class="col-md-6 svg-container">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 190 99" class="img-fluid svg-full animate-svg fade-in-start" role="img" aria-hidden="true">
                            <path fill="#3F3B39"
                                d="M9.722 97.437C3.636 95.264.739 91.64.087 85.194c-.725-6.882 3.115-12.243 10.577-14.779 2.68-.941 12.967-1.159 14.27-.29.943.653 1.884 4.13 1.305 4.71-.29.217-1.377 0-2.536-.58-2.897-1.521-9.055-1.376-12.605.362-3.622 1.739-5.433 4.709-5.433 8.693 0 4.13 1.739 7.462 5.143 9.78 3.695 2.608 7.824 2.97 13.475 1.232l4.419-1.377-1.087 2.391c-.652 1.304-1.81 2.535-2.68 2.753-2.898.797-12.388.362-15.213-.652ZM38.554 96.785c-6.23-3.188-8.91-8.838-7.461-15.793.941-4.274 2.68-6.52 7.027-9.055 2.897-1.739 4.346-2.029 10.287-2.246 6.085-.217 7.244-.073 10.07 1.449 1.81.942 4.274 2.897 5.433 4.419 2.028 2.535 2.245 3.332 2.245 8.041 0 4.492-.29 5.65-1.956 8.186-2.897 4.42-7.389 6.303-15.43 6.593-5.796.217-6.955 0-10.215-1.594Zm17.097-2.246c3.622-1.811 4.636-3.767 4.926-9.055.29-4.202.073-5.434-1.304-7.607-2.318-3.767-6.447-5.65-11.736-5.288-7.172.507-11.084 4.564-11.084 11.446 0 4.564 1.16 6.882 4.854 9.635 3.477 2.608 10.07 2.97 14.344.87ZM133.166 97.872c-6.448-1.956-9.997-6.158-10.649-12.606-.725-6.954 3.115-12.315 10.576-14.85 2.608-.942 12.895-1.16 14.851-.435 1.16.434 1.667 3.912.725 4.853-.29.218-1.377 0-2.536-.58-2.535-1.376-8.693-1.376-11.736-.072-1.304.508-3.115 1.884-4.129 2.97-1.521 1.594-1.811 2.609-1.811 6.376 0 4.998 1.087 7.1 5.216 9.924 3.332 2.246 7.679 2.536 13.04.87l4.419-1.377-1.087 2.391c-.652 1.304-1.811 2.536-2.68 2.753-2.173.58-12.026.435-14.199-.217ZM163.013 97.582c-6.013-2.318-9.563-7.245-9.563-13.402 0-6.593 4.274-11.809 11.374-13.91 4.346-1.303 13.692-.651 17.241 1.16 4.347 2.245 7.028 7.027 7.028 12.46 0 6.23-2.246 9.852-8.114 12.895-3.188 1.594-14.416 2.173-17.966.797Zm13.402-2.318c3.115-.652 6.447-4.564 7.099-8.259.652-3.84-.145-7.1-2.318-10.142-4.781-6.23-15.865-5.94-20.284.652-3.26 4.781-1.739 13.185 2.898 16.3 1.883 1.231 7.389 2.535 9.2 2.173.58-.145 2.101-.435 3.405-.724ZM73.038 95.916c1.231-3.115.941-21.734-.363-24.197l-1.159-2.173H89.7v4.129l-5.434-.362-5.433-.362v8.331l3.984-.29 3.985-.217v4.346l-4.057-.217-3.984-.29.217 6.593.217 6.592H75.79c-3.404 0-3.404 0-2.752-1.883ZM90.207 95.843c2.68-3.912 11.228-23.617 10.866-24.993-.362-1.231-.072-1.376 3.26-1.16l3.622.218 5.289 11.446c2.898 6.303 6.158 12.533 7.244 13.91l1.884 2.535h-4.347c-3.839 0-4.274-.145-3.912-1.376.145-.725-.362-3.043-1.231-5.071l-1.594-3.695H98.393l-1.16 2.753c-.651 1.449-1.23 3.767-1.303 4.998-.073 2.319-.145 2.391-3.55 2.391h-3.477l1.304-1.956Zm19.052-11.953c0-1.811-4.419-10.215-4.998-9.563-.507.652-4.42 9.49-4.42 10.142 0 .145 2.102.29 4.71.29 3.26 0 4.708-.29 4.708-.87Z" />
                            <path fill="#AFCA20"
                                d="M89.7 58.39c-7.897-5.361-12.46-12.75-8.259-13.33 1.956-.29 8.549 6.737 10.504 11.156 2.029 4.492 3.26 3.188 1.45-1.521-2.174-5.795-2.536-9.707-1.015-12.605 1.811-3.478 2.97-3.55 4.274-.29 1.16 2.753 1.16 6.448-.145 13.547-.724 3.912.58 3.188 3.115-1.739 1.449-2.752 3.116-4.708 5.579-6.302 4.419-2.97 5.071-2.97 4.491-.145-.724 3.694-3.26 7.027-6.81 9.128-1.883 1.014-4.346 2.753-5.578 3.84l-2.173 1.955L89.7 58.39Z" />
                            <path fill="#AFCA20"
                                d="M62.75 54.405c-7.678-2.535-13.112-7.896-14.198-13.981-1.232-6.738 1.81-12.533 8.113-15.648l3.333-1.666v-4.782c0-5.795 1.304-8.765 5.65-12.532 6.013-5.361 16.88-6.593 25.066-2.97 3.26 1.376 3.695 1.448 5.216.434C98.32 1.739 105.348 0 109.259 0c1.884 0 5.144.507 7.317 1.16 8.476 2.535 12.968 7.968 13.04 15.792.073 3.912.073 3.984 3.55 6.085 6.81 4.202 9.562 11.664 6.955 18.546-1.667 4.346-7.028 9.273-12.461 11.3-4.419 1.667-13.257 2.681-16.589 1.884l-1.884-.507 1.666-3.042c1.666-2.97 1.739-3.043 6.448-3.55 6.447-.58 11.881-3.043 14.126-6.303 2.246-3.26 2.318-7.172.145-9.852-1.666-2.1-6.52-4.636-8.91-4.709-1.377 0-1.449-.434-1.449-6.013 0-5.505-.218-6.302-1.956-8.62-4.782-6.303-16.517-6.375-22.458-.218l-1.594 1.666-5.143-2.607c-4.13-2.029-6.085-2.609-10.504-2.898-4.854-.29-5.651-.145-7.535 1.376-3.912 3.115-5.07 9.273-3.042 16.083l1.014 3.404h-1.884c-6.302 0-11.736 3.84-11.736 8.187 0 6.012 5.724 10.142 14.707 10.576l5.65.218 1.594 3.187c.797 1.739 1.304 3.333 1.014 3.622-.217.218-3.115.58-6.447.725-4.564.217-7.027 0-10.142-1.087Z" />
                        </svg>                            
                    </div>

                    <!-- Formulario -->
                    <div class="col-12 col-md-6 p-5 border-formulario">
                        <h2 class="text-center text-primary fw-bold mb-4">Bienvenido</h2>

                        <div class="d-flex align-items-center justify-content-between">
                            <hr class="flex-grow-1 me-2" />
                            <small class="text-muted text-uppercase">Ingresar su código</small>
                            <hr class="flex-grow-1 ms-2" />
                        </div>

                        <div class="mb-4">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" name="text" id="codigo" class="form-control" placeholder="Ingrese su código" required />
                        </div>

                        <button type="button" id="senddatos" class="btn btn-primary w-100">
                            Acceder
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
            const svg = document.querySelector(".fade-in-start");
            if (svg) svg.classList.add("fade-in-show");
            });
        </script>
    </body>
</html>
HTML;
