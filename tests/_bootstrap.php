<?php
// This is global bootstrap for autoloading
date_default_timezone_set('Europe/Paris');

// ensure we get report on all possible php errors
error_reporting(E_ALL);
// define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_ENV', 'test');
define('YII_DEBUG', true);
// $_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
// $_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require_once(__DIR__ . '/../vendor/autoload.php');

if (is_dir(__DIR__ . '/_output/assets') === false) {
    mkdir(__DIR__ . '/_output/assets', 0777, true);
}
Yii::setAlias('@tests', __DIR__ );
Yii::setAlias('@tests/unit', __DIR__ . '/unit');
Yii::setAlias('@tests/functional', __DIR__ . '/functional');
Yii::setAlias('@sweelix/oauth2/server', __DIR__ .'/../src');
