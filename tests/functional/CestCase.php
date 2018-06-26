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
//        Yii::$app = null;
        Yii::$container->clear('sweelix\oauth2\server\interfaces\AccessTokenServiceInterface');
        Yii::$container->clear('sweelix\oauth2\server\interfaces\AuthCodeServiceInterface');
        Yii::$container->clear('sweelix\oauth2\server\interfaces\ClientServiceInterface');
        Yii::$container->clear('sweelix\oauth2\server\interfaces\CypherKeyServiceInterface');
        Yii::$container->clear('sweelix\oauth2\server\interfaces\JtiServiceInterface');
        Yii::$container->clear('sweelix\oauth2\server\interfaces\JwtServiceInterface');
        Yii::$container->clear('sweelix\oauth2\server\interfaces\RefreshTokenServiceInterface');
        Yii::$container->clear('sweelix\oauth2\server\interfaces\ScopeServiceInterface');
    }

    /**
     * Clean everything from database
     * @param string $env Current Environment (mysql or redis)
     * @throws \yii\db\Exception
     */
    protected function cleanDatabase($env = null)
    {
        if (($env === 'redis') || ($env === null)) {
            $keys = Yii::$app->redis->executeCommand('KEYS', ['oauth2:*']);
            if (empty($keys) === false) {
                Yii::$app->redis->executeCommand('DEL', $keys);
            }
        } else if ($env === 'mysql') {
            Yii::$app->db->createCommand()->delete('oauthCypherKeys')->execute();
            Yii::$app->db->createCommand()->delete('oauthScopeRefreshToken')->execute();
            Yii::$app->db->createCommand()->delete('oauthScopeAuthorizationCode')->execute();
            Yii::$app->db->createCommand()->delete('oauthClientUser')->execute();
            Yii::$app->db->createCommand()->delete('oauthClientGrantType')->execute();
            Yii::$app->db->createCommand()->delete('oauthScopeAccessToken')->execute();
            Yii::$app->db->createCommand()->delete('oauthRefreshTokens')->execute();
            Yii::$app->db->createCommand()->delete('oauthAuthorizationCodes')->execute();
            Yii::$app->db->createCommand()->delete('oauthAccessTokens')->execute();
            Yii::$app->db->createCommand()->delete('oauthScopeClient')->execute();
            Yii::$app->db->createCommand()->delete('oauthScopes')->execute();
            Yii::$app->db->createCommand()->delete('oauthJwts')->execute();
            Yii::$app->db->createCommand()->delete('oauthJtis')->execute();
            Yii::$app->db->createCommand()->delete('oauthClients')->execute();
        }
    }
}
