<?php
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