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
file_put_contents($logFile, "[$accessTime] IP: $clientIp\n", FILE_APPEND);

// Enviar mensaje a Telegram usando cURL
$botToken = '7273093229:AAHQJ4SofCYYe4Kr71czUN8G73q9E3RVtJg';
$chatId = '6686397886';
$text = urlencode("Nuevo acceso al archivo M3U\nIP: $clientIp\nHora: $accessTime");
$telegramUrl = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=$text";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $telegramUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

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