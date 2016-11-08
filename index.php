<?php
date_default_timezone_set('Asia/Shanghai');
use Illuminate\Database\Capsule\Manager as Capsule;
require '/vendor/autoload.php';
if (!ini_get('display_errors')) {
    ini_set('display_errors', '1');
}

$capsule = new Capsule;
var_dump($capsule);exit;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'database',
    'username'  => 'root',
    'password'  => 'password',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

define('APPLICATION_PATH', dirname(__FILE__));
$application = new Yaf\Application( APPLICATION_PATH . "/conf/application.ini");//加载配置文件
$application->bootstrap()->run();
?>
