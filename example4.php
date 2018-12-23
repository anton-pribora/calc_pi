<?php

$children = isset($argv[1]) ? $argv[1] : 4;  // Первый аргумент - количество процессов
$port = isset($argv[2]) ? $argv[2] : 33333;  // Второй аргумент - порт, на котором запускается сервер

// Запускаем сервер
$socket = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);

if (!$socket) {
  die("$errstr ($errno)\n");
}

// Обработка результатов
$totalPoints = 0;
$pointsInCircle = 0;
$startTime = microtime(true);

$streams = [];
$happyClients = 0;  // Счётчик обслуженных клиентов (чтобы завершить работу сервера без внешней команды)

while (true) {
    $read = [$socket];
    $write = $except = [];
    
    // Проверяем основной сокет на предмет новых подключений
    if (stream_select($read, $write, $except, 0)) {
        if (($fp = stream_socket_accept($socket)) !== false) {
            $streams[] = $fp;
        }
    }
    
    // Если подключений нет, просто ждём
    if (empty($streams)) {
        usleep(2e5);
        continue;
    }
    
    // Проверяем, на каких сокетах появились данные
    $read = $streams;
    $write = $except = [];
    $num = stream_select($read, $write, $except, 1);
    
    if ($num === false) {
        die("Не удалось считать данные из сокетов");
    }
    
    // Построчно читаем входной поток и парсим данные
    foreach ($read as $fp) {
        $line = fgets($fp);
        
        // Если данные закончились, то закрываем сокет
        if ($line === false) {
            $i = array_search($fp, $streams);
            
            fclose($fp);
            unset($streams[$i]);
            
            $happyClients += 1;
            
            // Если обслужили всех клиентов, то избушку на клюшку и домой
            if ($children && $happyClients >= $children && empty($streams)) {
                fclose($socket);
                break 2;
            }
            
            continue;
        }
        
        $data = json_decode($line, true);
        
        $totalPoints += $data['iteration']['points'];
        $pointsInCircle += $data['iteration']['inCircle'];
    }
}

$time = microtime(true) - $startTime;

// Статистика
printf("Результат расчёта PI: %.5f\n", $pointsInCircle / $totalPoints * 4);
printf("Протестировано точек: %s\n", number_format($totalPoints));
printf("Время работы: %.3f сек.\n", $time);
printf("Скорость работы: %s точек в секунду\n", number_format($totalPoints / $time, 0));
printf("Количество процессов: %d\n", $happyClients);