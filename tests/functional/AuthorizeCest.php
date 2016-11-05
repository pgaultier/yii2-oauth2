<?php
/**
 * AuthorizeCest.php
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
use Yii;

class AuthorizeCest extends CestCase
{
    public function _before(FunctionalTester $I)
    {
        $this->cleanDatabase();
    }

    public function checkAuthorizeWithBadClient(FunctionalTester $I)
    {
        $I->amOnRoute('oauth2/authorize/index', [
            'response_type' => 'code',
            'client_id' => '20b4da05e9a280008ce76f446c4a1086b711072e',
            'redirect_uri' => 'http://localhost/cb'
        ]);
        $I->see('Bad Request', 'h1');
        $I->see('invalid_client', 'h4');
    }

    public function checkAuthorizeWithCorrectClientAndDecline(FunctionalTester $I)
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

    public function checkAuthorizeWithCorrectClientAndAccept(FunctionalTester $I)
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
            'response_type' => 'code',
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

        // $I->seeInCurrentUrl('code=');
        // $code = $I->grabFromCurrentUrl('~code=([^&]+)~');
        // die($code);
    }
}
