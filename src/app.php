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

// ログ設定
$app->register(new MonologServiceProvider(), $MONOLOG_SETTING);
// ローカル環境用
// $app->register(new MonologServiceProvider(), array(
// 	"monolog.logfile" => ROOT_PATH . "/storage/logs/" . Carbon::now('Asia/Tokyo')->format("Y-m-d") . ".log",
//   "monolog.level" => "DEBUG",
//   "monolog.name" => "application"
// ));

// Heroku DBへの参照
$app->register(new Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider('pdo'), $DB_CONN);

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
