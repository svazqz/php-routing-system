<?php
chdir(dirname(__DIR__));
require getcwd() . '/vendor/autoload.php';
require getcwd() . '/core/utils.php';

$whoops = new Whoops\Run();
$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());
$whoops->register();

// Initialize Eloquent ORM
Core\Providers\EloquentServiceProvider::initialize();

$container = new Core\Container();

$runnableData = parseURIAndComponents();

$controllerInstance = $container->build($runnableData->namespace.ucfirst($runnableData->controller));
$controllerInstance->__run($runnableData);