<?php

$points = isset($argv[1]) ? $argv[1] : 1e7;  // Первый аргумент - количество точек 
$children = isset($argv[2]) ? $argv[2] : 4;  // Второй аргумент - количество процессов

// Команда для запуска дочерних процессов
$cmd = join(' ', array_map('escapeshellarg', [PHP_BINARY, __DIR__ . '/calc_pi.php', $points]));

// Спецификация дескрипторов
$descriptorspec = [
   0 => ["pipe", "r"], // stdin
   1 => ["pipe", "w"], // stdout
   2 => ["pipe", "w"]  // stderr
];

// Выходные потоки подпроцессов
$streams = [];

// Процессы
$processes = [];

// Запускаем процессы
for($i = 0; $i < $children; $i += 1) {
    $fp = proc_open($cmd, $descriptorspec, $pipes);
    
    $processes[$i] = $fp;
    $streams[$i] = $pipes[1];  // stdout
    
    // Закрываем неиспользуемые потоки
    fclose($pipes[0]);  // stdin
    fclose($pipes[2]);  // stderr
}

// Обработка результатов
$totalPoints = 0;
$pointsInCircle = 0;
$startTime = microtime(true);

while ($processes) {
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
            $i = array_search($fp, $streams);
            
            fclose($fp);
            proc_close($processes[$i]);
            
            unset($streams[$i]);
            unset($processes[$i]);
            
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