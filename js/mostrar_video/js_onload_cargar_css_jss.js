// ✅ Obtener el esquema (http o https)
const scheme = window.location.protocol; // Incluye ":" al final

// ✅ Obtener el host (ejemplo: example.com o localhost)
const host = window.location.host;

// ✅ Construir la URL base
const baseUrl = `${scheme}//${host}`;

// ✅ Obtener la ruta del script (equivalente a $_SERVER['REQUEST_URI'])
const requestUri = window.location.pathname;

// ✅ Subir 2 niveles en la ruta
const scriptDir = requestUri.split('/').slice(0, -2).join('/');

// ✅ Construir la URL final eliminando la última barra si existe
const apiUrl = `${baseUrl}${scriptDir}`.replace(/\/+$/, '');

// ✅ Construir las rutas de los archivos CSS y JS
const cssUrl = `${apiUrl}/_lib/css/modal_video.css`;

// ✅ Agregar el archivo CSS al <head>
$("head").append(`<link rel='stylesheet' href='${cssUrl}' type='text/css' media='screen'>`);

const jsModalUrl = `${apiUrl}/_lib/js/js_modal_video.js`;
const jsSweetAlertUrl = `${apiUrl}/_lib/js/sweetalert2.all.min.js`;
// ✅ Función para agregar un archivo JS dinámicamente
function addScript(jsUrl) {
    const scriptElement = document.createElement("script");
    scriptElement.src = jsUrl;
    scriptElement.type = "text/javascript";
    document.head.appendChild(scriptElement);
}

// ✅ Agregar los archivos JS al <head>
addScript(jsModalUrl);
addScript(jsSweetAlertUrl);

