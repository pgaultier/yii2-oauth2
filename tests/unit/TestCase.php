<?php

namespace tests\unit;

use yii\helpers\ArrayHelper;
use Yii;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }
    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        // $this->destroyApplication();
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
            'bootstrap' => ['oauth2'],
            'modules' => [
                'oauth2' => [
                    'class' => 'sweelix\oauth2\server\Module',
                    'backend' => 'redis',
                    'db' => 'redis',
                    'identityClass' => 'tests\unit\MockUser',
                ],
            ],
            'components' => [
                'redis' => require(dirname(__DIR__).'/config/redis.php'),
            ]
        ], $config));
    }

    protected function getVendorPath()
    {
        $vendor = dirname(dirname(__DIR__)) . '/vendor';
        if (!is_dir($vendor)) {
            $vendor = dirname(dirname(dirname(dirname(__DIR__))));
        }
        return $vendor;
    }
    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }

    /**
     * populate database with scopes basic and email
     * @since XXX
     */
    protected function populateScopes()
    {
        $scope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var \sweelix\oauth2\server\models\Scope $scope */
        $scope->id = 'basic';
        $scope->isDefault = true;
        $scope->definition = 'Basic Scope';
        $scope->save();

        $scope = Yii::createObject('sweelix\oauth2\server\models\Scope');
        /* @var \sweelix\oauth2\server\models\Scope $scope */
        $scope->id = 'email';
        $scope->isDefault = false;
        $scope->definition = 'Email Scope';
        $scope->save();
    }

    /**
     * Clean everything from database
     */
    protected function cleanDatabase()
    {
        $keys = Yii::$app->redis->executeCommand('KEYS', ['oauth2:*']);
        if (empty($keys) === false) {
            Yii::$app->redis->executeCommand('DEL', $keys);
        }
    }

}
