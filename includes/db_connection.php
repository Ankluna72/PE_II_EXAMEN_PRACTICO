<?php
$db_host = '34.134.69.177'; 
$db_name = 'gestionti';          
$db_user = 'roberto';      
$db_pass = 'Upt2025@';     

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die('Error de Conexión (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");
?>