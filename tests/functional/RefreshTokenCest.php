<?php
/**
 * RefreshTokenCest.php
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

use Codeception\Scenario;
use FunctionalTester;
use sweelix\oauth2\server\models\AccessToken;
use sweelix\oauth2\server\models\RefreshToken;
use Yii;
use yii\helpers\Json;

class RefreshTokenCest extends CestCase
{
    public function _before(FunctionalTester $I, Scenario $scenario)
    {
        $this->cleanDatabase($scenario->current('env'));
    }

    public function _after(FunctionalTester $I)
    {
        // Yii::$app->redis->close();
        $this->destroyApplication();
    }

    public function checkWithCorrectClientAndCorrectGrant(FunctionalTester $I)
    {
        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\ClientModelInterface $client */
        $client->id = 'client2';
        $client->secret = 'secret2';
        $client->isPublic = false;
        $client->userId = 'uid';
        $client->name = 'Test client 2';
        $I->assertTrue($client->save());

        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\CypherKeyModelInterface $cypherKey */
        $cypherKey->id = 'client2';
        $cypherKey->generateKeys();
        $I->assertTrue($cypherKey->save());

        $response = $I->requestRoute('POST', 'oauth2/token/index', [], [
            'client_id' => 'client2',
            'client_secret' => 'secret2',
            'grant_type' => 'password',
            'username' => 'user1',
            'password' => 'password1',
        ]);

        $response = Json::decode($response);
        $I->assertArrayHasKey('access_token', $response);
        $I->assertArrayHasKey('refresh_token', $response);

        if (preg_match('/^[^.]+[.]{1}[^.]+[.]{1}[^.]+$/', $response['access_token'])) {
            $payload = (new \OAuth2\Encryption\Jwt())->decode($response['access_token'], $cypherKey->publicKey, true);
            $I->assertTrue(!!$payload);
        } else {
            $accessToken = AccessToken::findOne($response['access_token']);
            $I->assertInstanceOf(AccessToken::class, $accessToken);
        }

        $refreshToken = RefreshToken::findOne($response['refresh_token']);
        $I->assertInstanceOf(RefreshToken::class, $refreshToken);

        $response = $I->requestRoute('POST', 'oauth2/token/index', [], [
            'client_id' => 'client2',
            'client_secret' => 'secret2',
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken->id,
        ]);

        $response = Json::decode($response);
        $I->assertArrayHasKey('access_token', $response);
        $I->assertArrayHasKey('refresh_token', $response);

        if (preg_match('/^[^.]+[.]{1}[^.]+[.]{1}[^.]+$/', $response['access_token'])) {
            $payload = (new \OAuth2\Encryption\Jwt())->decode($response['access_token'], $cypherKey->publicKey, true);
            $I->assertTrue(!!$payload);
        } else {
            $accessToken = AccessToken::findOne($response['access_token']);
            $I->assertInstanceOf(AccessToken::class, $accessToken);
        }

        $refreshToken = RefreshToken::findOne($response['refresh_token']);
        $I->assertInstanceOf(RefreshToken::class, $refreshToken);
    }
}
