<?php
require __DIR__ . '/prod.php';
$app['debug'] = true;
$app['log.level'] = Monolog\Logger::DEBUG;
$MODE = "debug";
$MONOLOG_SETTING = array(
  'monolog.logfile' => 'php://stdout',
  "monolog.level" => 'DEBUG',
  "monolog.name" => "application"
);
$DB_CONN = array(
    'pdo.server' => array(
      'driver'   => 'pgsql',
      'user' => "ppbprdbespopyj",
      'password' => "b36627a1901b0da76f45c9d4de7184bd464ec43bab90d57de2d951b45233378e",
      'host' => "ec2-54-235-109-37.compute-1.amazonaws.com",
      'port' => 5432,
      'dbname' => ltrim("dclmcej3udp26l",'/')
      )
);
