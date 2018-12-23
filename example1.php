<?php

$points = isset($argv[1]) ? $argv[1] : 1e7;  // Первый аргумент - количество точек 
$children = isset($argv[2]) ? $argv[2] : 4;  // Второй аргумент - количество процессов
$calcPi = include 'calc_pi.php';

$pids = [];

for ($i = 0; $i < $children; $i += 1) {
    $pid = pcntl_fork();
    
    if ($pid < 0) {
        die("Произошла какая-то страшная ошибка и форк провалился\n");
    } elseif ($pid) {
        // Мы родитель, наша обязанность считать детей
        $pids[$pid] = $pid;
    } else {
        // Мы потомок, считаем PI
        $calcPi($points, function ($result){
            // Добавляем в результаты свои данные
            $result['pid'] = posix_getpid();
            
            // Выводим результат
            echo json_encode($result), "\n";
        });
        
        // Обязательно завершаем работу
        exit();
    }
}

// Ждём пока все дети завершат работу
while ($pids) {
    $pid = pcntl_wait($status);
    
    if (pcntl_wifexited($status)) {
        unset($pids[$pid]);
    }
}

// Хэппи енд