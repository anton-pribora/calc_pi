<?php

$points = isset($argv[1]) ? $argv[1] : 1e7;  // Первый аргумент - количество точек 
$address = isset($argv[2]) ? $argv[2] : "127.0.0.1:33333";  // Второй аргумент - IP и порт сервера, который обрабатывает результаты

// Запускаем клиента
$socket = stream_socket_client("tcp://$address", $errno, $errstr);

if (!$socket) {
  die("$errstr ($errno)\n");
}

$calcPi = include 'calc_pi.php';

// Считаем PI с выводом в сокет
$calcPi($points, function ($data) use ($socket) {
    $data['pid'] = posix_getpid();
    fputs($socket, json_encode($data) . "\n");
});

fclose($socket);