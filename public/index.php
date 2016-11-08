<?php
header("Content-Type:text/html;charset=utf-8");
date_default_timezone_set('Asia/Shanghai');
//因为使用了命名空间检测PHP版本
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

define('APPLICATION_PATH', dirname(__DIR__));//dirname(__FILE__);
$application = new Yaf\Application( APPLICATION_PATH . "/conf/application.ini");//加载配置文件
$application->bootstrap()->run();
?>
