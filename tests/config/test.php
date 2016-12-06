<?php
/**
 * test.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @since XXX
 */
return [
    'basePath' => __DIR__ . '/../../src',
    // Force vendor path to avoid misconfiguration
    'vendorPath' => __DIR__.'/../../vendor',
    'id' => 'sweelix/yii2-oauth2-server-testing',
    'timeZone' => 'Europe/Paris',
    'bootstrap' => ['oauth2'],
    'modules' => [
        'oauth2' => [
            'class' => 'sweelix\oauth2\server\Module',
            'backend' => 'redis',
            'db' => 'redis',
            // 'identityClass' => 'app\models\User',
            'enforceState' => false,
            'allowImplicit' => true,
            // 'allowJwtAccesToken' => true,
        ],
    ],
    'components' => [
        'redis' => require('redis.php'),
        'request' => [
            'cookieValidationKey' => 'TyBzAFkUXiTuv4zW6lElWhOVAWVBMqsQ',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        // Force assets publish directory to avoid adding useless directories
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'basePath' => '@tests/_output/assets'
        ],
        'user' => [
            'class' => 'sweelix\oauth2\server\web\User',
            'identityClass' => 'tests\functional\MockUser',
        ],
    ],
];
