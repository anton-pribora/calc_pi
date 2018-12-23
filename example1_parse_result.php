<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

$totalPoints = 0;
$pointsInCircle = 0;
$startTime = microtime(true);
$pids = [];

// Построчно читаем входной поток и парсим данные
while (($line = fgets(STDIN)) !== false) {
    $data = json_decode($line, true);
    
    $pids[$data['pid']] = $data['pid'];
    
    $totalPoints += $data['iteration']['points'];
    $pointsInCircle += $data['iteration']['inCircle'];
}

$time = microtime(true) - $startTime;

// Статистика
printf("Результат расчёта PI: %.5f\n", $pointsInCircle / $totalPoints * 4);
printf("Протестировано точек: %s\n", number_format($totalPoints));
printf("Время работы: %.3f сек.\n", $time);
printf("Скорость работы: %s точек в секунду\n", number_format($totalPoints / $time, 0));
printf("Количество процессов: %d\n", count($pids));