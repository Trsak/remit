<?php

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


include '../vendor/autoload.php';

// Configure application
$configurator = new Nette\Configurator;
$configurator->setDebugMode('84.19.71.115');
// Enable Nette Debugger for error visualisation & logging
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config.neon');
$container = $configurator->createContainer();
$container->getService("application")->errorPresenter = 'Front:Error';

// Setup router using mod_rewrite detection
$router = $container->getService('router');
$router[] = new Route('index.php', 'Front:Default:default', Route::ONE_WAY);

$router[] = $frontRouter = new RouteList('Front');
$frontRouter[] = new Route('Nastaveni[/<change>]', array(
    'presenter' => 'Nastaveni',
    'action' => 'default',
    'change' => "email",
));
$frontRouter[] = new Route('Film/<id [0-9]+>[/<page>]', array(
    'presenter' => 'Film',
    'action' => 'default',
    'page' => 'info',
    'id' => 0
));
$frontRouter[] = new Route('Uzivatel/<id [0-9]+>-<name>', array(
    'presenter' => 'Uzivatel',
    'action' => 'default',
    'id' => "",
    'name' => ""
));
$frontRouter[] = new Route('Televize[/<channel>]', array(
    'presenter' => 'Televize',
    'action' => 'default',
    'channel' => ""
));
$frontRouter[] = new Route('<presenter>/<action>[/<id>]', 'Default:default');


return $container;
