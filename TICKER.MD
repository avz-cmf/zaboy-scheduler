# Генератор тактов TICKER

## Описание

Предназначен для отсчета времени с точностью до 0.1 секунды и вызовов указанной callback-функции, указанной в параметрах.

Представляет собой аналог и своеобразную замену crontab-демона в Unix-системах, но позволяет получать мелкие промежутки
времени (до 0.1 сек).

Работает в однопоточном режиме. Продолжительность работы скрипта определяется настройками; по умолчанию - 60 секунд.

Запускается тикер скриптом www/run.php c указанием параметров. Тикер НЕ оптимизировался под вызов из браузера. Только 
из командной строки.

## Требования
Скрипт будет работать только в тех системах, где допускается отправка процесса в фоновый режим посредством добавления
знака амперсанда (&) в конец команды (напр., Unix-системы).

Требования к версии PHP: >= 5.4.

## Параметры запуска

Скрипт имеет ряд обязательных параметров, которые в случае их отстутствия во входных данных заменяются значениями по умолчанию:

- **total_time** - общее время жизни скрипта; указывается в секундах (по умолчанию = 60).
- **step** - время такта; указывается в секундах (по умолчанию = 1). Секунду можно дробить вплоть до 0.1 сек.
Можно указывать и меньшие значения (тестировалось до  0.01 сек), но в этом случае могут быть проблемы при вызовах callback'ов.
Запуск потока в зависимости от сервера может занимать время. И есть вероятность, что тикер собъется с такта из-за ожидания
окончания процесса запуска.
- **tick_callback** - имя скрипта, который по сути является callback'ом. Скрипт выполняется в каждый такт тикера. 
Этот callback запускается в фоновом режиме (демон) и по умолчанию выполняет запись в лог.
- **hop_callback** - имя скрипта, который запускается один раз в начале работы тикера перед началом генерации тактов.
Этот callback запускается в фоновом режиме (демон) и по умолчанию выполняет запись в лог. 
Необходим в тех случаях, когда нужно выполнить какие-нибудь подготовительные действия. 

Параметры передаются в стиле: `-param1 val1 -param2 val2 ... -paramN valN`. Порядок следования параметров значения не
имеет.

### Описание CALLBACK'ов
Тикер может принимать два названия скриптов для вызова их во время своей работы.
Значения этих параметров представляет из себя название скриптов без указания пути. Путь для них прописан жестко:
*папка **scripts** относительно корня проекта*. НЕ публичной!! Кроме случаев, когда публичная папка приложения и его корневая папка -
одно и то же.

Параметры, которые отдает тикер в скрипты, передаются в том же стиле, как и во время запуска тикера - через командную строку. 
Разработчик должен сам озаботиться их чтением на стороне callback'ов. Для этого можно использовать метод 
`ittech227\ticker\Callback\Callback::parseCommandLineParameters` или любой другой удобный способ.

#### TICK_CALLBACK
Скрипт, который будет вызываться в каждом такте тикера. По умолчанию имеет название 
[tick.php](https://github.com/ittech227/ticker/blob/dev/scripts/tick.php).
По умолчанию выполняет запись в лог. Принимает от тикера два параметра:

- **tick_id**; метка времени генерации текущего тика с десятыми долями секунды или без них. Десятые доли секунды будут добавлены,
если шаг тикера меньше единицы.
- **step**; шаг тикера.

По умолчанию в качестве лога используется хранилище типа 
[\zaboy\rest\DataStore\DataStoreAbstract](https://github.com/avz-cmf/zaboy-rest/blob/master/src/DataStore/DataStoreAbstract.php),
и в нем хранится не более 600 строк журнала. Это поведение можно изменить в настройках приложения, использующего тикер.


#### HOP_CALLBACK
Скрипт, который будет вызываться один раз при старте тикера перед началом генерации тактов. По умолчанию имеет название
[hop.php](https://github.com/ittech227/ticker/blob/dev/scripts/tick.php). 
По умолчанию выполняет запись в лог. Принимает от тикера два параметра:

- **hop_start**; время старта тикера с десятыми долями секунды или без них. Десятые доли секунды будут добавлены, если
шаг тикера меньше единицы.
- **ttl**; общее время жизни/работы тикера.

По умолчанию в качестве лога используется хранилище типа 
[\zaboy\rest\DataStore\DataStoreAbstract](https://github.com/avz-cmf/zaboy-rest/blob/master/src/DataStore/DataStoreAbstract.php),
и в нем хранится не более 600 строк журнала. Это поведение можно изменить в настройках приложения, использующего тикер.

## Ручные настройки работы тикера.
Если Вас по каким-то причинам не устраивают предложенные умолчания для callback'ов, Вы можете легко изменить это поведение,
прописав свои настройки и/или скрипты.

### Вариант 1. Меняем только типы DataStore для логов, их параметры и кол-во хранимых строк в них.
Для этого создаем локальный (в контексте приложения) файл конфигурации, где прописываем нужные параметры. За образец
можно взять оригинальный файл [config.global.php](https://github.com/ittech227/ticker/blob/dev/config/autoload/config.global.php).
Структуру конфига менять, разумеется, нельзя; только переменные величины: имена классов, файлов, таблиц и т.д.

После этого нужно создать два файла скриптов, описанных выше (или создать свои с другими именами; тогда их нужно
будет указывать в параметрах командной строки при запуске тикера) и включить (include) в них оригинальные скрипты 
соответственно в каждый.

### Вариант 2. Полностью меняем поведение callback'ов.
Для этого создаем два скрипта с указанными выше названиями (или с другими именами; тогда их нужно
будет указывать в параметрах командной строки при запуске тикера). А в них пишем нужное поведение.

Чаще всего будет использоваться именно этот вариант.

## Примеры использования
Примеры запуска показаны относительно публичной папки (www) библиотеки тикера. При работе с этой библиотекой Вам нужно
будет указывать реальные пути.

1. `php run.php`<br />
запустит на выполнение тикер с параметрами по умолчанию. Скрипт выполнится в течение 60 секунд с шагом 1 секунда.
Выполняться будут callback'и по умолчанию.

2. `php run.php -total_time 10 -step 0.1`<br />
запустит тикер на 10 секунд с шагом 0.1 секунды. Выполняться будут callback'и по умолчанию.

3. `php run.php -total_time 30 -step 1 -tick_callback mytick.php -hop_callback myhop.php`<br />
запустит тикер на 30 секунд с шагом 1 секунда. Выполняться будут указанные скрипты, которые должны находиться 
в папке script, находящейся в корне приложения, использующего эту библиотеку (!!).

4. Запуск из кода:

        // some php file
        $options = [...];
        
        // somewhere here $tickCallback and $hopCallback are created
        
        $ticker = new Ticker($tickCallback, $hopCallback, $options);
        $ticker->start();