<?php

/**
 * Verifica si existe una sesión activa con 'ingreso_id'.
 * Si no existe, destruye la sesión y responde con un error 401.
 */

session_start();

if (!isset($_SESSION['ingreso_id'])) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode([
        'code' => 401,
        'msn' => 'Sesión expirada. Por favor, vuelva a iniciar sesión.'
    ]);
    exit;
}
