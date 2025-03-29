<?php
chdir("..");
require getcwd() . '/vendor/autoload.php';
require getcwd() . '/core/utils.php';

$whoops = new Whoops\Run();
$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());
$whoops->register();

$container = new Core\Container();

$container->set(Services\WeatherService::class, function () {
    return new Services\WeatherService();
});

$runnableData = parseURIAndComponents();

$controllerInstance = $container->build($runnableData->namespace.ucfirst($runnableData->controller));
$controllerInstance->__run($runnableData);