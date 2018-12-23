<?php

// Расчёт PI методом Монте-Карло
$calcPi = function ($points, callable $progress) {
    // Переменные для расчёта PI
    $radius = ceil($points / 4);
    $totalPoints = 0;
    $pointsInCircle = 0;
    
    // Счётчики для подсчёта прогресса
    $startTime = microtime(true);
    $iterations = 1e5;
    $iterationPoints = 0;
    $iterationPointsInCirle = 0;
    
    while ($totalPoints < $points) {
        $x = rand(-$radius, $radius) / $radius;
        $y = rand(-$radius, $radius) / $radius;
        
        if (($y * $y + $x * $x) <= 1) {
            ++$pointsInCircle;
            ++$iterationPointsInCirle;
        }
        
        $totalPoints += 1;
        $iterationPoints += 1;
        
        // Проверка, нужно ли вызывать функцию прогресса
        // Если да, то сбрасываем счётчики и вызываем прогресс
        if (--$iterations <= 0 || $totalPoints >= $points) {
            $elapsed = microtime(true) - $startTime;
            $pointsPerSec = round($iterationPoints / $elapsed);
            $iterations = $pointsPerSec * 0.5;  // Обновление прогресса каждые полсекунды
            
            $progress([
                'pi' => round($pointsInCircle / $totalPoints * 4, 5),
                'progress' => round($totalPoints / $points * 100, 1),
                'pointsPerSec' => $pointsPerSec,
                'total' => [
                    'points' => $totalPoints,
                    'inCircle' => $pointsInCircle,
                ],
                'iteration' => [
                    'points' => $iterationPoints,
                    'inCircle' => $iterationPointsInCirle,
                ],
            ]);
            
            $startTime = microtime(true);
            $iterationPoints = 0;
            $iterationPointsInCirle = 0;
        }
    }
    
    return true;
};

// Если файл подключают из проекта, мним себя библиотекой и возвращаем функцию 
if (count(get_included_files()) > 1) {
    return $calcPi;
}

// Если файл запущен без проекта, считаем PI и выводим результат
$points = isset($argv[1]) ? $argv[1] : 1e7;

$calcPi($points, function ($data) {
    echo json_encode($data), "\n";
});