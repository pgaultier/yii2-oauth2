<?php
/**
 * CestCase.php
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

namespace tests\functional;

use Yii;
use yii\helpers\ArrayHelper;

class CestCase
{

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        $baseConfig = require(__DIR__ . '/../config/test.php');
        // $this->destroyApplication();
        new $appClass(ArrayHelper::merge($baseConfig, $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
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
