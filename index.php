<?php
/**
 * PHP index
 * 
 * PHP version 7
 * 
 * @category Script
 * @package  Rule
 * @author   Jerome Lamartiniere <jerome@lamartiniere.eu>
 * @license  https://github.com/smallfish500/phprule/blob/master/LICENSE MIT
 * @link     https://github.com/smallfish500/phprule
 */
require './config.inc.php';
$loader = include __DIR__.'/vendor/autoload.php';
$loader->addPsr4('Rule\\', SRC);
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
require './bootstrap.php';
