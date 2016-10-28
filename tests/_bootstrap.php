<?php
// This is global bootstrap for autoloading
date_default_timezone_set('Europe/Paris');

// ensure we get report on all possible php errors
error_reporting(E_ALL);
define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');


Yii::setAlias('@tests/unit', __DIR__ . '/unit');
Yii::setAlias('@sweelix/oauth2/server', __DIR__ .'/../src');