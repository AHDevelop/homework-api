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

$MODE = "debug";

$app->register(new MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stdout',
  // "monolog.logfile" => ROOT_PATH . "/storage/logs/" . Carbon::now('Europe/London')->format("Y-m-d") . ".log",
  "monolog.level" => 'debug',//$app["log.level"],
  "monolog.name" => "application"
));

$serverName = gethostname();
$log = $app['monolog'];
$log->addInfo('$serverName:'. $serverName);

// Heroku DBへの参照
$app->register(new Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider('pdo'),
               array(
                'pdo.server' => array(
                   'driver'   => 'pgsql',
                   'user' => "ppbprdbespopyj",
                   'password' => "b36627a1901b0da76f45c9d4de7184bd464ec43bab90d57de2d951b45233378e",
                   'host' => "ec2-54-235-109-37.compute-1.amazonaws.com",
                   'port' => 5432,
                   'dbname' => ltrim("dclmcej3udp26l",'/')
                   )
               )
);

// check auth token
if ($MODE !== 'debug') {
	$app->before(function (Request $request, Application $app) {

		$log = $app['monolog'];
		$log->addInfo('getPathInfo:'.$request->getPathInfo());
		$log->addInfo('getMethod:'.$request->getMethod());

		// result ex) /index.php/api/v1/users
		$path = $request->getPathInfo();
		$log->addInfo($path);
		// result ex) users
		$apiPath = preg_replace('#/api/v\d/#', '', $path);
		$apiPaths = explode('/', $apiPath);
		if ($apiPaths[0] == 'users'){

			// 新規ユーザ登録時は認証チェックしない（そもそもtokenは登録されていないため）
			if ($request->getMethod() == 'POST' && count($apiPaths) == 2 && $apiPaths[1] == 'update.json') {
				$log->addInfo('new users no check');
				return;
			}
			// google再認証後、ユーザチェック時（gmailによるユーザ確認）はチェックしない（tokenを自動的に更新する仕組みのため）
			$log->addInfo('key'.$request->headers->get('key'));
			if ($request->getMethod() == 'GET' && $request->headers->get('key') != null && $request->headers->get('authToken') != null) {
				$log->addInfo('update users no check');
				return;
			}
		}
		$token = $request->headers->get('X-HomeWorkToken');
		$log->addInfo("token:".$token);
		if ($token == null) {
			$app->abort(401, "auth token error");
		}

		$isAuthOk = $app['users.service']->checkUserToken($token);
		if ($isAuthOk) {
			$log->addInfo('auth OK');
		} else {
			$log->addInfo('auth NG');
			$app->abort(401, "auth token error");
		}
	}, 100);
}

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
