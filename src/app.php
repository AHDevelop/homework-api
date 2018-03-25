<?php

use Silex\Application;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\ServicesLoader;
use App\RoutesLoader;
use Carbon\Carbon;
use Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider;

date_default_timezone_set('Asia/Tokyo');

$app->register(new MonologServiceProvider(), array(
  // 'monolog.logfile' => 'php://stderr',
  "monolog.logfile" => ROOT_PATH . "/storage/logs/" . Carbon::now('Europe/London')->format("Y-m-d") . ".log",
  "monolog.level" => "debug",//$app["log.level"],
  "monolog.name" => "application"
));

// Heroku DBへの参照
$app->register(new Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider('pdo'),
               array(
                'pdo.server' => array(
                   'driver'   => 'pgsql',
                   'user' => "vsdolektutpymq",
                   'password' => "f2246a9cf404f37ace380e749c3c3dc32eb3a9f9dab619a8e8ef8bb164cd05e8",
                   'host' => "ec2-23-23-225-12.compute-1.amazonaws.com",
                   'port' => 5432,
                   'dbname' => ltrim("dde335i9o6dtl9",'/')
                   )
               )
);

// check auth token
$app->before(function (Request $request, Application $app) {
  $token = $request->headers->get('X-HomeWorkToken');
  $app['monolog']->addInfo("token:".$token);
  if ($token == null) {
    $app->abort(401, "auth token error");
  }

  $isAuthOk = $app['users.service']->checkUserToken($token);
  if ($isAuthOk) {
    $app['monolog']->addInfo('auth OK');
  } else {
    $app['monolog']->addInfo('auth NG');
    $app->abort(401, "auth token error");
  }
}, 100);

//accepting JSON
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->register(new \Euskadi31\Silex\Provider\CorsServiceProvider);

$app->register(new ServiceControllerServiceProvider());

// $app->register(new DoctrineServiceProvider(), array(
//   "db.options" => $app["db.options"]
// ));

$app->register(new HttpCacheServiceProvider(), array("http_cache.cache_dir" => ROOT_PATH . "/storage/cache",));

//load services
$servicesLoader = new App\ServicesLoader($app);
$servicesLoader->bindServicesIntoContainer();

//load routes
$routesLoader = new App\RoutesLoader($app);
$routesLoader->bindRoutesToControllers();

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    $app['monolog']->addError("error message:".$e->getMessage());
    $app['monolog']->addError("error code".$code);
    return new JsonResponse(
      array("statusCode" => $code, "message" => $e->getMessage()),
      $code
    );
});

return $app;
