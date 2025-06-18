<?php
session_start();

// Duración de la sesión (puedes ajustarla en php.ini o aquí manualmente)
$session_lifetime = ini_get("session.gc_maxlifetime");

$elapsed = time() - $sessionStart;
$remaining = $maxLifetime - $elapsed;

// Evitar valores negativos
$remaining = max(0, $remaining);

// Convertir a horas, minutos y segundos
$hours = floor($remaining / 3600);
$minutes = floor(($remaining % 3600) / 60);
$seconds = $remaining % 60;

// Formatear con ceros a la izquierda
$formatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

// Si expiró, destruir y responder
if ($remaining <= 0) {
    session_unset();
    session_destroy();
    echo json_encode([
        "cronometro" => $formatted
    ]);
} else {
    echo json_encode([
        "remaining" => $remaining,
        "elapsed" => $elapsed
    ]);
}
?>