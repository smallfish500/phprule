<?php
/**
 * Configuration constants
 * 
 * PHP version 7
 * 
 * @category Config
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
define('APP', 'Rule');
define('SRC', 'src/');
define('LOG', 'logs/');
define('BASE', '/rule');
define('HOST_URL', 'http://'.$_SERVER['HTTP_HOST']);
define('HTTP_METHODS', 'HEAD,GET,DELETE,POST,PATCH');
define('DATABASE_URL', 'pdo-mysql://root:password@127.0.0.1:3306/rule?charset=utf8');
define('TEMPLATES_DIR', './templates');
define('JSON', false);
define(
    'CONTENT_TYPE',
    JSON ? 'application/json;charset=UTF-8' : 'text/html;charset=UTF-8'
);
define('USER_SECRET', 'mysecret');
define('PASS_SECRET', 'mysecret');
define('TOKEN_SECRET', 'mysecret');
define('DEBUG', true);
define('SRV_PROTO', $_SERVER['SERVER_PROTOCOL']);
define('REQ_METHOD', $_SERVER['REQUEST_METHOD']);
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', '6379');
