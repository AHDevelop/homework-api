<?php
$app['log.level'] = Monolog\Logger::ERROR;
$app['api.version'] = "v1";
$app['api.endpoint'] = "/api";
$MODE = "Production";
$MONOLOG_SETTING = array(
  'monolog.logfile' => 'php://stdout',
  "monolog.level" => 'ERROR',
  "monolog.name" => "application"
);
$DB_CONN = array(
    'pdo.server' => array(
      'driver'   => 'pgsql',
      'user' => "cmpmsbirdyehfw",
      'password' => "3f9362b93dc525450c3a635175a1884618e05f73a4431cab0cc5b9237bd855a9",
      'host' => "ec2-54-243-54-6.compute-1.amazonaws.com",
      'port' => 5432,
      'dbname' => ltrim("d188t54hklh0e5",'/')
      )
);
