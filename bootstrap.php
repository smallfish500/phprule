<?php
/**
 * PHP boostrap
 * 
 * Prepares : the logger and the routes
 * and dispatches the correct route if it is found
 * 
 * PHP version 7
 * 
 * @category Script
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
$log = new \Monolog\Logger(APP);
$log->pushHandler(
    new \Monolog\Handler\StreamHandler(
        LOG.APP.'.log',
        DEBUG ? \Monolog\Logger::DEBUG : \Monolog\Logger::ERROR
    )
);

$log->info('Bootstrap STARTED');
$log->info('Creating routes...');
$dispatcher = \FastRoute\simpleDispatcher(
    function(FastRoute\RouteCollector $router) use ($log) {
        $routes = [
            'users' => [
                'head' => '/users',
                'get' => [
                    'me' => '/users/me',
                    'one' => '/users/{id:\d+}',
                    'details' => '/users/{id:\d+}/details',
                    'addressbooks' => '/users/{id:\d+}/addressbooks',
                    'addressbook' => '/users/addressbook/{id:\d+}',
                ],
                'delete' => '/users/{id:\d+}',
                'post' => '/users', // https://www.ietf.org/rfc/rfc2616.txt
                'patch' => [// https://www.ietf.org/rfc/rfc5789.txt
                    'modify' => '/users/{id:\d+}',
                    'enable' => '/users/{id:\d+}/enable',
                    'disable' => '/users/{id:\d+}/disable',
                ], 
            ]
        ];
        foreach ($routes as $group => $methods) {
            $namespace = APP.'\\'.
                mb_convert_case($group, MB_CASE_TITLE, 'UTF-8').'::';
            foreach ($methods as $method => $route) {
                if ($method == 'head') {
                    $router->head(
                        $route,
                        function() {
                            header('Allow: '.HTTP_METHODS);
                        }
                    );
                } elseif (is_string($route)) {
                    $router->$method($route, $namespace.$method);
                } elseif (is_array($route)) {
                    foreach ($route as $name => $val) {
                        $router->$method($val, $namespace.$name);
                    }
                } else {
                    $log->error(
                        'BAD ROUTE - Group: '.$group.' Method: '.$method.
                        ' Value: '.json_encode($route)
                    );
                }
            }
        }
    }
);
$log->info('Routes created');

$log->info('Dispatching...');
$uri = substr($_SERVER['REQUEST_URI'], (strlen(BASE)));
if (($pos = strpos($uri, '?')) !== false) {
    $uri = rawurldecode(substr($uri, 0, $pos));
} else {
    $uri = rawurldecode($uri);
}
$route = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $uri);
switch ($route[0]) {
case FastRoute\Dispatcher::FOUND:
    $log->debug(
        $route[1],
        ['GET' => $_GET, 'POST' => $_POST, 'SERVER' => $_SERVER]
    );
    if (!is_callable($route[1])) {
        $log->error('ROUTE NOT CALLABLE - '.json_encode($route));
    } else {
        header('Content-Type: '.CONTENT_TYPE);
        $log->notice(SRV_PROTO.' FOUND');
        if (in_array(REQ_METHOD, ['HEAD', 'GET'])) {
            header('Allow: '.HTTP_METHODS);
        } elseif (REQ_METHOD == 'POST') {
            header(SRV_PROTO.' 201 Created');
        } elseif (REQ_METHOD == 'PATCH') {
            header(SRV_PROTO.' 204 No Content'); // XXX 200
            $location = substr(BASE.$uri, 0, strrpos(BASE.$uri, '/'));
            header('Content-Location: '.HOST_URL.$location);
        } elseif (REQ_METHOD == 'DELETE') {
            header(SRV_PROTO.' 200 OK');
        }
        if (!call_user_func_array($route[1], $route[2])) {
            $header = SRV_PROTO.' 403 Forbidden';
            $log->warning($header);
            header($header);
        }
    }
    break;
case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
    $header = SRV_PROTO.' 405 Method Not Allowed';
    $log->warning($header);
    header($header);
    break;
case FastRoute\Dispatcher::NOT_FOUND:
default:
    $header = SRV_PROTO.' 404 Not Found';
    $log->warning($header);
    header($header);
    break;
}
$log->info('Dispatched');

if (DEBUG) {
    if (!JSON) {
        $usage = round(memory_get_peak_usage()/1048516, 2).'MB';
        /*
        $logger = \Rule\Database::getDatabase()->getConfiguration()->getSqlLogger();
        */
        echo '<pre>';
        echo '<strong>memory_get_peak_usage</strong>: '.$usage;
        echo '<br><strong>cache stats</strong>: ';
        print_r(\Rule\Cache::getDbCache()->getStats());
        //echo '<br>database queries:<br>';
        //print_r($logger->queries); // XXX
        echo '</pre>';
    } else {
        // XXX
    }
}

$log->info('Bootstrap FINISHED');
