<?php

use apex\app\interfaces\LoggerInterface;
use apex\app\interfaces\DebuggerInterface;
use apex\app\interfaces\DBInterface;
use apex\app\interfaces\msg\DispatcherInterface;
use apex\app\interfaces\TemplateInterface;
use apex\app\interfaces\AuthInterface;

return [
    'log' => [ 
        'interface' => LoggerInterface::class, 
        'class' => apex\app\sys\log::class, 
        'params' => ['channel_name' => 'apex'], 
        'autowire' => true
    ], 
    'debug' => [
        'interface' => DebuggerInterface::class, 
        'class' => apex\app\sys\debug::class, 
        'autowire' => true
    ], 
    'db' => [
        'interface' => DBInterface::class, 
        'class' => apex\app\db\mysql::class, 
        'autowire' => true
    ], 
    'msg' => [
        'interface' => DispatcherInterface::class, 
        'class' => apex\app\msg\dispatcher::class, 
    'params' => ['channel_name' => 'apex']
    ], 
    'template' => [
        'interface' => TemplateInterface::class, 
        'class' => apex\app\web\template::class, 
        'autowire' => true
    ], 
    'auth' => [
        'interface' => AuthInterface::class, 
        'class' => apex\app\sys\auth::class
    ], 
    'utils/components' => ['class' => apex\app\sys\components::class], 
    'utils/date' => ['class' => apex\app\utils\date::class], 
    'utils/encrypt' => ['class' => apex\app\sys\encrypt::class], 
    'utils/forms' => ['class' => apex\app\utils\forms::class], 
    'utils/hashes' => ['class' => apex\app\utils\hashes::class], 
    'utils/images' => ['class' => apex\app\utils\images::class], 
    'utils/io' => ['class' => apex\app\io\io::class], 
    'utils/geoip' => ['class' => apex\app\utils\geoip::class] 
];


