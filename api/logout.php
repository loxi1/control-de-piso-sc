<?php
header('Content-Type: application/json');

//Borrar las sessiones de usuario
session_start();

$_SESSION = [];
session_destroy();
setcookie(session_name(), '', time() - 3600); // ← borra PHPSESSID

responder(200, 'Cerro sessión.', ['salio' => 'ok']);