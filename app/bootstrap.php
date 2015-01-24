<?php

require __DIR__.'/../vendor/autoload.php';

use Knp\Provider\ConsoleServiceProvider;

$app = new Silex\Application();
$app->register(
    new ConsoleServiceProvider(),
    array(
        'console.name' => 'MyConsole',
        'console.version' => '0.1.0',
        'console.project_directory' => __DIR__ . "/.."
    )
);

return $app;

?>