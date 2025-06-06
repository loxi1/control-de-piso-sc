<?php
require_once($_SERVER['DOCUMENT_ROOT'].'\loader\env_loader.php'); //Local
//Servidor 45
//require_once($_SERVER['DOCUMENT_ROOT'].'/loader/env_loader.php');

// 🗃️ Configuración de bases de datos usando variables de entorno
$confmysql = EnvConfig::getMySQL();

$confsybase = EnvConfig::getSybase();

echo "<pre>";print_r($confmysql);echo "</pre>";
echo "<pre>";print_r($confsybase);echo "</pre>";