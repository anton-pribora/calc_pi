<?php

$points = isset($argv[1]) ? $argv[1] : 1e7;  // Первый аргумент - количество точек 
$children = isset($argv[2]) ? $argv[2] : 4;  // Второй аргумент - количество процессов
$calcPi = include 'calc_pi.php';

$pids = [];
$streams = [];

for ($i = 0; $i < $children; $i += 1) {
    // Создаём пару сокетов для передачи данных между процессами
    $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    
    // Создаём подпроцесс
    $pid = pcntl_fork();
    
    if ($pid < 0) {
        die("Произошла какая-то страшная ошибка и форк провалился\n");
    } elseif ($pid) {
        // Мы родитель, наша обязанность считать детей
        $pids[$pid] = $pid;
        $streams[$pid] = $sockets[1];
        fclose($sockets[0]);
    } else {
        // Мы потомок
        fclose($sockets[1]);
        
        // Считаем PI
        $calcPi($points, function ($result) use ($sockets){
            // Выводим результат в поток родителю
            fputs($sockets[0], json_encode($result) . "\n");
        });
        
        // Обязательно завершаем работу
        fclose($sockets[0]);
        exit();
    }
}

// Обработка результатов
$totalPoints = 0;
$pointsInCircle = 0;
$startTime = microtime(true);

while ($pids) {
    // Проверяем, на каких сокетах появились данные
    $read = $streams;
    $write = $except = [];
    $num = stream_select($read, $write, $except, 1);
    
    if ($num === false) {
        die("Не удалось считать данныке из дочерних процессов");
    }
    
    // Построчно читаем входной поток и парсим данные
    foreach ($read as $fp) {
        $line = fgets($fp);
        
        // Если данные закончились, то закрываем сокет и удаляем pid
        if ($line === false) {
            $pid = array_search($fp, $streams);
            
            fclose($fp);
            unset($streams[$pid]);
            unset($pids[$pid]);
            
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
printf("Количество процессов: %d\n", $children);