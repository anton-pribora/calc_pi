# Пояснения к example3.php

В этом примере для запуска дочерних процессов используется функция `proc_open()`. 

## Пример запуска

```bash 
% php example3.php      
Результат расчёта PI: 3.14145
Протестировано точек: 40,000,000
Время работы: 18.820 сек.
Скорость работы: 2,125,423 точек в секунду
Количество процессов: 4
```

## Достоинства

+ обработка итоговых данных сделана в родительском процессе;
+ родительский и дочерние процессы могут обмениваться данными;
+ дочерний процесс полностю самостоятельный.

## Недостатки

+ сокеты имеют внутренние ограничения на передачу данных.
