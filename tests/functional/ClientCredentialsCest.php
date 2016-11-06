<?php
/**
 * ImplicitCest.php
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

use FunctionalTester;
use sweelix\oauth2\server\models\AccessToken;
use sweelix\oauth2\server\models\RefreshToken;
use Yii;
use yii\helpers\Json;

class ClientCredentialsCest extends CestCase
{
    public function _before(FunctionalTester $I)
    {
        $this->cleanDatabase();
    }

    public function _after(FunctionalTester $I)
    {
        // Yii::$app->redis->close();
        // $this->destroyApplication();
    }

    public function checkWithBadClient(FunctionalTester $I)
    {
        $response = $I->requestRoute('POST', 'oauth2/token/index', [], [
            'client_id' => '20b4da05e9a280008ce76f446c4a1086b711072e',
            'client_secret' => '20b4da05e9a280008ce76f446c4a1086b711072e',
            'grant_type' => 'client_credentials',
        ]);

        $response = Json::decode($response);
        $I->assertEquals('invalid_client', $response['error']);
    }

    public function checkWithCorrectClientInvalidGrant(FunctionalTester $I)
    {
        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\ClientModelInterface $client */
        $client->id = 'client2';
        $client->secret = 'secret2';
        $client->isPublic = false;
        $client->userId = 'uid';
        $client->name = 'Test client 2';
        $I->assertTrue($client->save());

        Yii::$app->getModule('oauth2')->allowClientCredentials = false;

        $response = $I->requestRoute('POST', 'oauth2/token/index', [], [
            'client_id' => 'client2',
            'client_secret' => 'secret2',
            'grant_type' => 'client_credentials',
        ]);

        $response = Json::decode($response);
        $I->assertEquals('invalid_grant', $response['error']);
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

        $response = $I->requestRoute('POST', 'oauth2/token/index', [], [
            'client_id' => 'client2',
            'client_secret' => 'secret2',
            'grant_type' => 'client_credentials',
        ]);

        $response = Json::decode($response);
        $I->assertArrayHasKey('access_token', $response);
        $accessToken = AccessToken::findOne($response['access_token']);
        $I->assertInstanceOf(AccessToken::className(), $accessToken);
    }
}
