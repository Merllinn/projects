<?php

/**
 * My Application bootstrap file.
 */



// Load Nette Framework
require $params['libsDir'] . '/Nette/loader.php';


// Enable Nette Debugger for error visualisation & logging
NDebugger::$logDirectory = $params['wwwDir'] . '/log';
NDebugger::$strictMode = TRUE;
NDebugger::enable(NDebugger::DEVELOPMENT);


// Load configuration from config.neon file
$configurator = new NConfigurator;
$configurator->container->params += $params;
$container = $configurator->loadConfig($params['appDir'] . '/config.neon');

$container->params['languages'] = array("cz"=>"CZ", "en"=>"EN");  
$container->session->setExpiration("+ 365 days");     

// Setup router
$router = $container->router;
require_once($params['appDir'] . '/router.php');

//connect to database
dibi::connect(NEnvironment::getConfig('database'));

// Datepicker registration
NFormContainer::extensionMethod('addDatePicker', function (NFormContainer $container, $name, $label = NULL) {
    return $container[$name] = new DatePicker($label);
});

// Configure and run the application!
$application = $container->application;
//$application->catchExceptions = TRUE;
$application->errorPresenter = 'Error';
$application->run();
