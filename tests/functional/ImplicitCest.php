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

use Codeception\Scenario;
use FunctionalTester;
use sweelix\oauth2\server\models\AccessToken;
use Yii;

class ImplicitCest extends CestCase
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

    public function checkWithBadClient(FunctionalTester $I)
    {
        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'token',
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

        Yii::$app->getModule('oauth2')->allowImplicit = false;

        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'token',
            'client_id' => 'client2',
            'redirect_uri' => 'http://localhost/cb'
        ]);
        $I->see('Bad Request', 'h1');
        $I->see('unsupported_response_type', 'h4');
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
        $I->assertTrue($client->save());

        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'token',
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
        $I->assertTrue($client->save());

        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\CypherKeyModelInterface $cypherKey */
        $cypherKey->id = 'client2';
        $cypherKey->generateKeys();
        $I->assertTrue($cypherKey->save());

        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'token',
            'client_id' => 'client2',
            'redirect_uri' => 'http://localhost/cb'
        ]);
        $I->see('Sweelix', 'h1');

        $I->fillField('User[username]', 'user1');
        $I->fillField('User[password]', 'password1');
        $I->click('LOGIN');

        $I->see('Test client 2', 'h1');
        $I->see('AUTHORIZE', 'button');
        $I->see('DECLINE', 'button');

        $I->click('AUTHORIZE');

        $I->seeInCurrentUrl('access_token=');
        $token = $I->grabFromCurrentUrl('~access_token=([^&]+)~');
        $I->seeInCurrentUrl('#');

        if (preg_match('/^[^.]+[.]{1}[^.]+[.]{1}[^.]+$/', $token)) {
            // Fix for https://github.com/bshaffer/oauth2-server-php/issues/918
            $I->seeInCurrentUrl('token_type=bearer');
            $payload = (new \OAuth2\Encryption\Jwt())->decode($token, $cypherKey->publicKey, true);
            $I->assertTrue(!!$payload);
        } else {
            $I->seeInCurrentUrl('token_type=Bearer');
            $accessToken = AccessToken::findOne($token);
            $I->assertInstanceOf(AccessToken::class, $accessToken);
        }

        // check we can skip login and authorize
        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'token',
            'client_id' => 'client2',
            'redirect_uri' => 'http://localhost/cb'
        ]);
        $I->seeInCurrentUrl('access_token=');
        $token = $I->grabFromCurrentUrl('~access_token=([^&]+)~');
        $I->seeInCurrentUrl('#');

        if (preg_match('/^[^.]+[.]{1}[^.]+[.]{1}[^.]+$/', $token)) {
            // Fix for https://github.com/bshaffer/oauth2-server-php/issues/918
            $I->seeInCurrentUrl('token_type=bearer');
            $payload = (new \OAuth2\Encryption\Jwt())->decode($token, $cypherKey->publicKey, true);
            $I->assertTrue(!!$payload);
        } else {
            $I->seeInCurrentUrl('token_type=Bearer');
            $accessToken = AccessToken::findOne($token);
            $I->assertInstanceOf(AccessToken::class, $accessToken);
        }

        $I->assertTrue($client->hasUser('userid1'));

        $client->removeUser('userid1');

        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'token',
            'client_id' => 'client2',
            'redirect_uri' => 'http://localhost/cb'
        ]);

        $I->see('Test client 2', 'h1');
        $I->see('AUTHORIZE', 'button');
        $I->see('DECLINE', 'button');

        $I->click('AUTHORIZE');

        $I->seeInCurrentUrl('access_token=');
        $token = $I->grabFromCurrentUrl('~access_token=([^&]+)~');
        $I->seeInCurrentUrl('#');

        if (preg_match('/^[^.]+[.]{1}[^.]+[.]{1}[^.]+$/', $token)) {
            // Fix for https://github.com/bshaffer/oauth2-server-php/issues/918
            $I->seeInCurrentUrl('token_type=bearer');
            $payload = (new \OAuth2\Encryption\Jwt())->decode($token, $cypherKey->publicKey, true);
            $I->assertTrue(!!$payload);
        } else {
            $I->seeInCurrentUrl('token_type=Bearer');
            $accessToken = AccessToken::findOne($token);
            $I->assertInstanceOf(AccessToken::class, $accessToken);
        }
    }
}
