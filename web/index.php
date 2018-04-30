<?php

require_once __DIR__ . '/../vendor/autoload.php';

define("ROOT_PATH", __DIR__ . "/..");

$app = new Silex\Application();

$HTTP_HOST = $_SERVER['HTTP_HOST'];
if($HTTP_HOST === "dev-homework-api.herokuapp.com"){
  require __DIR__ . '/../resources/config/dev.php';
} else if($HTTP_HOST === "homework-api.herokuapp.com"){
  require __DIR__ . '/../resources/config/prod.php';
} else {  
}

require __DIR__ . '/../src/app.php';

$app['http_cache']->run();
