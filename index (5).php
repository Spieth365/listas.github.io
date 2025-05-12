<?php
// Mostrar errores en caso de fallo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Rutas de los archivos
$ipsFile = __DIR__ . '/IPS.txt';
$baseFile = __DIR__ . '/SATIPTV_Base.txt';
$logFile = __DIR__ . '/access.log'; // Archivo de log de accesos

// Obtener IP del cliente
$clientIp = $_SERVER['REMOTE_ADDR'];
$accessTime = date('Y-m-d H:i:s');

// Registrar acceso en access.log
file_put_contents($logFile, "[" . $accessTime . "] IP: " . $clientIp . "\n", FILE_APPEND);

// Enviar notificación por correo electrónico
$to = 'javigr77@gmail.com';
$subject = 'Acceso al archivo SATIPTV.m3u';
$message = "Nuevo acceso:\nIP: $clientIp\nHora: $accessTime";
$headers = 'From: notificaciones@satiptv.local' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);

// Leer IPs y seleccionar una aleatoria
$ips = file($ipsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$randomIp = $ips[array_rand($ips)];

// Leer el archivo base
$baseContent = file_get_contents($baseFile);

// Reemplazar "http://IP" por la IP aleatoria seleccionada
$finalContent = str_replace('http://IP', 'http://' . $randomIp, $baseContent);

// Enviar encabezados para descarga del archivo M3U
header('Content-Type: audio/x-mpegurl');
header('Content-Disposition: attachment; filename="SATIPTV.m3u"');

// Salida del contenido modificado
echo $finalContent;
exit;
?>