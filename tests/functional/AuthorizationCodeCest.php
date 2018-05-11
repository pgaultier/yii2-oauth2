<?php
/**
 * AuthorizationCodeCest.php
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
use sweelix\oauth2\server\models\AuthCode;
use Yii;
use yii\helpers\Json;

class AuthorizationCodeCest extends CestCase
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

    public function checkBadAccess(FunctionalTester $I)
    {
        $I->amOnRoute('oauth2/authorize/login');
        $I->see('Bad Request', 'h1');
        $I->see('request_invalid', 'h4');
    }

    public function checkWithBadClient(FunctionalTester $I)
    {
        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'code',
            'client_id' => '20b4da05e9a280008ce76f446c4a1086b711072e',
            'redirect_uri' => 'http://localhost/cb'
        ]);
        $I->see('Bad Request', 'h1');
        $I->see('invalid_client', 'h4');
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

        Yii::$app->getModule('oauth2')->allowAuthorizationCode = false;

        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'code',
            'client_id' => 'client2',
            'redirect_uri' => 'http://localhost/cb'
        ]);
        $I->see('Bad Request', 'h1');
        $I->see('invalid_grant', 'h4');
    }

    public function checkWithCorrectClientInvalidRedirectUri(FunctionalTester $I)
    {
        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\ClientModelInterface $client */
        $client->id = 'client2';
        $client->secret = 'secret2';
        $client->isPublic = false;
        $client->userId = 'uid';
        $client->name = 'Test client 2';
        $client->redirectUri = 'http://www.sweelix.com/callback';
        $I->assertTrue($client->save());

        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'code',
            'client_id' => 'client2',
            'redirect_uri' => 'http://localhost/cb'
        ]);
        $I->see('Bad Request', 'h1');
        $I->see('redirect_uri_mismatch', 'h4');
    }

    public function checkWithCorrectClientAndDecline(FunctionalTester $I)
    {
        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\ClientModelInterface $client */
        $client->id = 'client2';
        $client->secret = 'secret2';
        $client->isPublic = false;
        $client->userId = 'uid';
        $client->name = 'Test client 2';
        $client->redirectUri = 'hxxp://www.sweelix.com/callback http://localhost/cb';
        $I->assertFalse($client->save());
        $client->redirectUri = 'http://www.sweelix.com/callback http://localhost/cb';
        $I->assertTrue($client->save());

        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'code',
            'client_id' => 'client2',
            'redirect_uri' => 'http://localhost/cb'
        ]);
        $I->see('Sweelix', 'h1');

        $I->fillField('User[username]', 'user1');
        $I->fillField('User[password]', 'password');
        $I->click('LOGIN');

        $I->see('Sweelix', 'h1');

        $I->seeInField('User[username]', 'user1');
        $I->seeInField('User[password]', '');

        $I->fillField('User[username]', 'user1');
        $I->fillField('User[password]', 'password1');
        $I->click('LOGIN');

        $I->see('Test client 2', 'h1');
        $I->see('AUTHORIZE', 'button');
        $I->see('DECLINE', 'button');

        $I->click('DECLINE');

        $I->seeInCurrentUrl('error=access_denied');
    }

    public function checkWithCorrectClientAndAccept(FunctionalTester $I)
    {
        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\ClientModelInterface $client */
        $client->id = 'client2';
        $client->secret = 'secret2';
        $client->isPublic = false;
        $client->userId = 'uid';
        $client->name = 'Test client 2';
        $client->redirectUri = ['http://www.sweelix.com/callback', 'http://localhost/cb'];
        $I->assertTrue($client->save());

        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'code',
            'client_id' => 'client2',
            'redirect_uri' => 'http://www.sweelix.com/callback'
        ]);
        $I->see('Sweelix', 'h1');

        $I->fillField('User[username]', 'user1');
        $I->fillField('User[password]', 'password1');
        $I->click('LOGIN');

        $I->see('Test client 2', 'h1');
        $I->see('AUTHORIZE', 'button');
        $I->see('DECLINE', 'button');

        $I->click('AUTHORIZE');

        $I->seeInCurrentUrl('code=');
        $code = $I->grabFromCurrentUrl('~code=([^&]+)~');

        $codeModel = AuthCode::findOne($code);
        $I->assertInstanceOf(AuthCode::class, $codeModel);
        $I->assertEquals('userid1', $codeModel->userId);
        $response = $I->requestRoute('POST', 'oauth2/token/index', [], [
            'client_id' => 'client2',
            'client_secret' => 'secret2',
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://www.sweelix.com/callback',
            'code' => $codeModel->id,
        ]);
        $response = Json::decode($response);
        $codeModel = AuthCode::findOne($code);
        $I->assertNull($codeModel);
        $accessToken = AccessToken::findOne($response['access_token']);
        $I->assertInstanceOf(AccessToken::class, $accessToken);
        $refreshToken = RefreshToken::findOne($response['refresh_token']);
        $I->assertInstanceOf(RefreshToken::class, $refreshToken);

    }
}
