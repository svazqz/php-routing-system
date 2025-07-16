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

// Handle specific controller name mappings and convert to proper PascalCase
$controllerMappings = [
    'landingengine' => 'LandingEngine',
    'dashboard' => 'Dashboard',
    'home' => 'Home',
    'preview' => 'Preview'
];

$controllerName = isset($controllerMappings[strtolower($runnableData->controller)]) 
    ? $controllerMappings[strtolower($runnableData->controller)]
    : str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $runnableData->controller)));

$controllerInstance = $container->build($runnableData->namespace.$controllerName);
$controllerInstance->__run($runnableData);