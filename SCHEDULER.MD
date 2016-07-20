# Планировщик задач Scheduler

## Описание
Предназначен для вызова заданных Callback'ов по расписанию. Расписание хранится в специальном хранилище в виде 
rql-фильтров и содержит следующие поля:

- **id**;

- **rql**; RQL-выражение, которое и есть режим вызова callback'а;

- **callback**; функция/метод/скрипт, который будет вызываться каждый раз;

- **active**; активность фильтра.

Обычное использование scheduler'а связано с генерацией тактов со стороны [Ticker'а](https://).
Ему нужно в виде зависимостей передать два callback'а: для вызова в начале работы (hop) и вызова каждый такт (tick).
В классе scheduler'а для этих целей есть два специальных метода:

- **processHop**; тут производится выборка активных фильтров из хранилища; они помещаются в переменную класса;

- **processTick**; здесь эти фильтры перебираются, и каждый из фильтров накладывается на 
[Timeline DataStore](https://). Если найдены соответствия, то вызывается указанный в фильтре Callback.

Callback - объект класса, реализующего интерфейс 
[\zaboy\scheduler\Callback\Interfaces\CallbackInterface] (https://).


## Технические характеристики
Конструктор класса принимает три параметра:

- **\zaboy\rest\DataStore\Interfaces\DataStoreInterface $filterDs**; хранилище фильтров; 
объект должен принадлежать классу, реализующем интерфейс
[\zaboy\rest\DataStore\Interfaces\DataStoresInterface] (https://);

- **\zaboy\scheduler\DataStore\Timeline $timelineDs**; объект [Timeline DataStore](https://);

- **\zaboy\scheduler\Callback\CallbackManager $callbackManager**; менеджер Callback'ов, который по своей сути является
обычным serviceManager'ом c той разницей, что он создает и возвращает только объекты классов,
реализующих интерфейс CallbackInterface. Если порожденная им сущность не является объектом такого класса,
то генерируется исключительная ситуация. Scheduler'у этот объект нужен, чтобы порождать Callback'и, указанные в фильтрах.


## Варианты создания объекта расписания (scheduler).

Один из вариантов создания объекта scheduler'а - использовать конструктор напрямую. В этом случае разработчик получает
ответственность по созданию вышеуказанных объектов, которые нужно инъецировать в scheduler.

    // somewhere here the objects $filterDs, $timelineDs and $callbackManager are created
    
    use \zaboy\scheduler\Scheduler\Scheduler;
    $scheduler = new Scheduler($filterDs, $timelineDs, $callbackManager);

Второй вариант для создания расписания - получать его, как сервис. Для этого его (расписание) нужно описать 
в настройках приложения:

    // config/services.global.php
    'factories' => [
        'scheduler' => 'zaboy\scheduler\Scheduler\Factory\SchedulerFactory',
         // ...
         'timeline_datastore' => 'zaboy\scheduler\DataStore\Factory\TimelineFactory',
         'filters_datastore' => 'zaboy\scheduler\DataStore\Factory\FilterDataStoreFactory',
         
         // may absent; will be created from default class
         'callback_manager' => 'zaboy\scheduler\Callback\Factory\CallbackManagerFactory',
     ]
     
     // file.php
     $scheduler = $container->get('scheduler');


## Пример использования в паре с Ticker'ом

### Без описания в конфиге

    // For example, index.php
    
    chdir(dirname(__DIR__));
    require './vendor/autoload.php';
    
    $container = include './config/container.php';
    
    use \zaboy\scheduler\Callback\CallbackManager;
    use \zaboy\scheduler\Scheduler\Scheduler;
    use \zaboy\scheduler\Ticker\Ticker;
    use \zaboy\scheduler\Callback\Instance;
    
    $callbackManager = new CallbackManager($container);
    
    // 'filter_datastore' service must be described in config
    $filterDs = $container->get('filter_datastore');
    
    // 'time_datastore' service must be described in config
    $timelineDs = $container->get('timeline_datastore');
    
    $scheduler = new Scheduler($filterDs, $timelineDs, $callbackManager);
    
    // Now we create the tick- and hop- callbacks
    $tickCallback = new Instance([
        'instance' => $scheduler,
        'method' => 'processTick',
    ]);
    
    $hopCallback = new Instance([
        'instance' => $scheduler,
        'method' => 'processHop',
    ]);
    
    $ticker = new Ticker(
        $tickCallback,
        $hopCallback,
        [
            // you can add here some options which set the Ticker
        ]
    );
    $ticker->start();
    

### Порождение как сервиса из serviceManager'а

    // config.global.php
    return [
    
        'services' => [
            'factories' => [
                'timeline_datastore' => 'zaboy\scheduler\DataStore\Factory\TimelineFactory',
                'ticker' => 'zaboy\scheduler\Ticker\Factory\TickerFactory',
                'scheduler' => 'zaboy\scheduler\Scheduler\Factory\SchedulerFactory',
                'filters_datastore' => 'zaboy\scheduler\DataStore\Factory\FilterDataStoreFactory',
                'callback_manager' => 'zaboy\scheduler\Callback\Factory\CallbackManagerFactory',
            ],
        ],
        
        'ticker' => [
            'total_time' => 60,
            'step' => 1,
            'hop' => [
                'callback' => 'scheduler_hop_callback',
            ],
            'tick' => [
                'callback' => 'scheduler_tick_callback',
            ],
        ],
        
        'callback' => [
            'scheduler_hop_callback' => [
                'class' => 'zaboy\scheduler\Callback\Instance',
                'params' => [
                    'instanceServiceName' => 'scheduler',
                    'instanceMethodName' => 'processHop',
                ],
            ],
            'scheduler_tick_callback' => [
                'class' => 'zaboy\scheduler\Callback\Instance',
                'params' => [
                    'instanceServiceName' => 'scheduler',
                    'instanceMethodName' => 'processTick',
                ],
            ],
        ],
    ];
    
    // For example, index.php
    
    chdir(dirname(__DIR__));
    require './vendor/autoload.php';
    
    $container = include './config/container.php';

    $config = $this->container->get('config')['ticker'];
    $hopCallback = $this->container->get($config['hop']['callback']);
    $tickCallback = $this->container->get($config['tick']['callback']);
    $options = [
        // you can add here some options which set up the Ticker
    ];

    $ticker = new Ticker($tickCallback, $hopCallback, $options);
    return $ticker;

    $ticker = new Ticker($tickCallback, $hopCallback, $options);
    $ticker->start();