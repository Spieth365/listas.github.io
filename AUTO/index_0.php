<?php
// Rutas de los archivos
$ipsFile = __DIR__ . '/IPS.txt';
$baseFile = __DIR__ . '/SATIPTV_Base.txt';

// Leer IPs y seleccionar una aleatoria
$ips = file($ipsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$randomIp = $ips[array_rand($ips)];

// Leer el archivo base
$baseContent = file_get_contents($baseFile);

// Reemplazar todas las ocurrencias de "IP" por la IP seleccionada
$finalContent = str_replace('http://IP', 'http://' . $randomIp, $baseContent);

// Enviar encabezados para descarga del archivo .m3u
header('Content-Type: audio/x-mpegurl');
header('Content-Disposition: attachment; filename="SATIPTV.m3u"');

// Imprimir el contenido modificado
echo $finalContent;
exit;
?>