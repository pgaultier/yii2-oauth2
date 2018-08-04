<?php

namespace tests\unit;
use OAuth2\Storage\AuthorizationCodeInterface;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\AuthCodeModelInterface;
use sweelix\oauth2\server\models\AuthCode;
use Yii;
/**
 * ManagerTestCase
 */
class OauthAuthCodeStorageTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
        ]);
        $this->cleanDatabase();
        $this->populateClients();
        $this->populateAccessTokens();
    }
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInsert()
    {
        $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
        /* @var AuthCodeModelInterface $authCode */
        $this->assertInstanceOf(AuthCodeModelInterface::class, $authCode);

        $authCode->id = 'authCode1';
        $authCode->clientId = 'client1';
        $authCode->redirectUri = 'http://www.sweelix.net';
        $authCode->expiry = time() + 30;
        $authCode->tokenId = 'accessToken1';
        $this->assertTrue($authCode->save());

        $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
        /* @var AuthCodeModelInterface $authCode */
        $this->assertInstanceOf(AuthCodeModelInterface::class, $authCode);

        $authCode->id = 'authCode2';
        $authCode->clientId = 'client1';
        $authCode->redirectUri = 'http://www.sweelix.net';
        $authCode->expiry = time() + 30;
        $authCode->scopes = ['basic'];
        $authCode->tokenId = 'accessToken1';
        $this->assertFalse($authCode->save());
        $this->populateScopes();
        $this->assertTrue($authCode->save());

        $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
        /* @var AuthCodeModelInterface $authCode */
        $this->assertInstanceOf(AuthCodeModelInterface::class, $authCode);

        $authCode->id = 'authCode2';
        $authCode->clientId = 'client1';
        $authCode->redirectUri = 'http://www.sweelix.net';
        $authCode->expiry = time() + 30;
        $authCode->scopes = ['basic'];
        $authCode->tokenId = 'accessToken1';
        $this->expectException(DuplicateKeyException::class);
        $authCode->save();

    }

    public function testUpdate()
    {
        $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
        /* @var AuthCodeModelInterface $authCode */
        $this->assertInstanceOf(AuthCodeModelInterface::class, $authCode);

        $authCode->id = 'authCode1';
        $authCode->clientId = 'client1';
        $authCode->redirectUri = 'http://www.sweelix.net';
        $authCode->expiry = time() + 30;
        $authCode->tokenId = 'accessToken1';
        $this->assertTrue($authCode->save());

        $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
        /* @var AuthCodeModelInterface $authCode */
        $this->assertInstanceOf(AuthCodeModelInterface::class, $authCode);

        $authCode->id = 'authCode2';
        $authCode->clientId = 'client1';
        $authCode->redirectUri = 'http://www.sweelix.net';
        $authCode->expiry = time() + 30;
        $authCode->tokenId = 'accessToken1';
        $this->assertTrue($authCode->save());

        $authCode->scopes = ['basic'];
        $this->assertFalse($authCode->save());
        $this->populateScopes();
        $this->assertTrue($authCode->save());

        $authCode = AuthCode::findOne('authCode2');
        $this->assertInstanceOf(AuthCodeModelInterface::class, $authCode);
        $authCode->id = 'authCode3';
        $authCode->redirectUri = null;
        $this->assertTrue($authCode->save());

        $authCode->id = 'authCode1';
        $this->expectException(DuplicateKeyException::class);
        $authCode->save();

    }

    public function testDelete()
    {
        $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
        /* @var AuthCodeModelInterface $authCode */
        $this->assertInstanceOf(AuthCodeModelInterface::class, $authCode);

        $authCode->id = 'authCode1';
        $authCode->clientId = 'client1';
        $authCode->redirectUri = 'http://www.sweelix.net';
        $authCode->expiry = time() + 30;
        $authCode->tokenId = 'accessToken1';
        $this->assertTrue($authCode->save());

        $authCode = AuthCode::findOne('authCode1');
        $this->assertTrue($authCode->delete());
        $authCode = AuthCode::findOne('authCode1');
        $this->assertNull($authCode);
    }

    public function testStorage()
    {
        $this->populateScopes();
        $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
        /* @var AuthorizationCodeInterface $storage */
        $this->assertInstanceOf(AuthorizationCodeInterface::class, $storage);

        $expiry = time() + 30;
        $this->assertTrue($storage->setAuthorizationCode('authCode1', 'client1', 'user1', null, $expiry, 'basic', 'accessToken1'));

        $authCode = AuthCode::findOne('authCode1');
        $this->assertEquals('authCode1', $authCode->id);
        $this->assertEquals('client1', $authCode->clientId);
        $this->assertEquals('user1', $authCode->userId);
        $this->assertNull($authCode->redirectUri);
        $this->assertEquals('accessToken1', $authCode->tokenId);
        $this->assertContains('basic', $authCode->scopes);

        $authCodeData = $storage->getAuthorizationCode('authCode1');
        $this->assertEquals('client1',$authCodeData['client_id']);
        $this->assertEquals('user1',$authCodeData['user_id']);
        $this->assertEquals($expiry,$authCodeData['expires']);
        $this->assertEquals('basic',$authCodeData['scope']);

        $storage->expireAuthorizationCode('authCode1');
        $authCodeData = $storage->getAuthorizationCode('authCode1');
        $this->assertNull($authCodeData);

        $this->assertTrue($storage->setAuthorizationCode('authCode1', 'client1', 'user1', null, $expiry, null, 'accessToken1'));

        $authCode = AuthCode::findOne('authCode1');
        $this->assertEquals('authCode1', $authCode->id);
        $this->assertEquals('client1', $authCode->clientId);
        $this->assertEquals('user1', $authCode->userId);
        $this->assertNull($authCode->redirectUri);
        $this->assertEquals('accessToken1', $authCode->tokenId);
        $this->assertTrue(empty($authCode->scopes));

        $authCodeData = $storage->getAuthorizationCode('authCode1');
        $this->assertEquals('client1',$authCodeData['client_id']);
        $this->assertEquals('user1',$authCodeData['user_id']);
        $this->assertEquals($expiry,$authCodeData['expires']);
        $this->assertEquals('',$authCodeData['scope']);

        $storage->expireAuthorizationCode('authCode1');
        $authCodeData = $storage->getAuthorizationCode('authCode1');
        $this->assertNull($authCodeData);

    }
}
