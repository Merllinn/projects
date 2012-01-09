<?php

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

$params = array();

// absolute filesystem path to this web root
$params['wwwDir'] = dirname(__FILE__);

// absolute filesystem path to the application root
$params['appDir'] =  $params['wwwDir'] . '/app';

// absolute filesystem path to the libraries
$params['libsDir'] =  $params['wwwDir'] . '/libs';

// absolute filesystem path to the temporary files
$params['tempDir'] =  $params['wwwDir'] . '/temp';

// absolute filesystem path to the download files
$params['downloadDir'] =  $params['wwwDir'] . '/download';
define("DOWNLOAD_DIR", $params['downloadDir']);


// load bootstrap file
require $params['appDir'] . '/bootstrap.php';
