# Пояснения к example1.php

В этом примере используется запуск дочерних процессов через стандартную функцию `pcntl_fork()`. Подпроцессы никак не взаимодействуют с родительским процессом, а все результаты выводят в стандартный поток вывода. Чтобы получить итоговый расчёт данных, нужно все результаты отправить на входной поток `example1_parse_result.php`.

## Примеры запуска

Запуск расчётов в два процесса 100,000 точек:

```bash
% php example1.php 1e5 2                           
{"pi":3.14608,"progress":100,"pointsPerSec":441394,"total":{"points":100000,"inCircle":78652},"iteration":{"points":100000,"inCircle":78652},"pid":12058}
{"pi":3.13248,"progress":100,"pointsPerSec":444166,"total":{"points":100000,"inCircle":78312},"iteration":{"points":100000,"inCircle":78312},"pid":12059}
```

Запуск расчётов с итоговой обработкой результатов:

```bash
% php example1.php 1e5 2 | php example1_parse_result.php 
Результат расчёта PI: 3.14376
Протестировано точек: 200,000
Время работы: 0.212 сек.
Скорость работы: 942,281 точек в секунду
Количество процессов: 2
```

Запуск без параметров:

```bash
% php example1.php | php example1_parse_result.php
Результат расчёта PI: 3.14201
Протестировано точек: 40,000,000
Время работы: 15.964 сек.
Скорость работы: 2,505,668 точек в секунду
Количество процессов: 4
```

## Достоинства

+ простая реализация;
+ промежуточные результаты работы можно сохранить в файл;
+ нет проблем с сокетами.

Этот подход можно использовать, когда задача позволяет разделить её на части, а количество подпроцессов заранее известно.

## Недостатки

+ функция `pcntl_fork()` полностью копирует рабочее окружение родительского процесса, включая соединения с внешними источниками данных; 
+ подпроцессы не могут передать результаты в родительский процесс.
