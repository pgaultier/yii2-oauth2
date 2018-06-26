<?php

namespace tests\unit;

use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use Yii;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string module to use (redis or mysql)
     */
    protected $moduleType = null;

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
        if ($this->moduleType === 'redis') {
            $modules = [
                'oauth2' => [
                    'baseEndPoint' => 'redis',
                    'class' => 'sweelix\oauth2\server\Module',
                    'backend' => 'redis',
                    'db' => 'redis',
                    'identityClass' => 'tests\unit\MockUser',
                ],
            ];
        } elseif ($this->moduleType === 'mysql') {
            $modules = [
                'oauth2' => [
                    'baseEndPoint' => 'mysql',
                    'class' => 'sweelix\oauth2\server\Module',
                    'backend' => 'mysql',
                    'db' => 'db',
                    'identityClass' => 'tests\unit\MockUser',
                ],
            ];
        } else {
            $modules = null;
        }
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
            'bootstrap' => ['oauth2'],
            'modules' => $modules,
            'components' => [
                'redis' => require(dirname(__DIR__) . '/config/redis.php'),
                'db' => require(dirname(__DIR__) . '/config/mySql.php'),
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
     * populate database with scopes basic and email
     * @since XXX
     * @throws \yii\base\UnknownClassException
     * @throws \yii\base\InvalidConfigException
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
     * populate database with clients client1 and client2
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownClassException
     */
    protected function populateClients()
    {
        $client = Yii::createObject('sweelix\oauth2\server\models\Client');
        /* @var \sweelix\oauth2\server\models\Client $client */
        $client->id = 'client1';
        $client->secret = 'secret1';
        $client->isPublic = true;
        $client->grantTypes = [];
        $client->redirectUri = 'http://sweelix.net';
        $client->userId = 'uid';
        $client->scopes = [];
        $client->name = 'Test client 1';
        $client->save();

        $client = Yii::createObject('sweelix\oauth2\server\models\Client');
        /* @var \sweelix\oauth2\server\models\Client $client */
        $client->id = 'client2';
        $client->secret = 'secret2';
        $client->isPublic = true;
        $client->grantTypes = [];
        $client->redirectUri = 'http://sweelix.net';
        $client->userId = 'uid';
        $client->scopes = [];
        $client->name = 'Test client 2';
        $client->save();
    }

    /**
     * populate database with accessTokens accessToken1 and accessToken2
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownClassException
     */
    protected function populateAccessTokens()
    {
        $accessToken = Yii::createObject('sweelix\oauth2\server\models\AccessToken');
        /* @var \sweelix\oauth2\server\models\AccessToken $accessToken
         */
        $accessToken->id = 'accessToken1';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = time() + 3600;
        $accessToken->save();

        $accessToken = Yii::createObject('sweelix\oauth2\server\models\AccessToken');
        /* @var \sweelix\oauth2\server\models\AccessToken $accessToken
         */
        $accessToken->id = 'accessToken2';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = time() + 3600;
        $accessToken->save();
    }

    /**
     * Clean everything from database
     * @throws \yii\db\Exception
     */
    protected function cleanDatabase()
    {
        if ($this->moduleType === 'redis') {
            $keys = Yii::$app->redis->executeCommand('KEYS', ['oauth2:*']);
            if (empty($keys) === false) {
                Yii::$app->redis->executeCommand('DEL', $keys);
            }
        } else if ($this->moduleType === 'mysql') {
            try {
                (new Query())
                    ->select('*')
                    ->from('oauthCypherKeys')
                    ->all();
            } catch (Exception $e) {
                $sql = file_get_contents(Yii::getAlias('@tests') . '/_data/oauth2.sql');
                Yii::$app->db->createCommand($sql)->execute();
            }
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
