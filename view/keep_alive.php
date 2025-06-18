<?php
session_start();
// Actualiza algo para que PHP considere que hubo actividad
$_SESSION['last_active'] = time();
// Opcional: devuelve algo simple
echo 'OK';