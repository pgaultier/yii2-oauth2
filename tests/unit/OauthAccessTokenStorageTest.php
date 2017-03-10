<?php

namespace tests\unit;
use OAuth2\Storage\AccessTokenInterface;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\ClientModelInterface;
use sweelix\oauth2\server\models\AccessToken;
use sweelix\oauth2\server\models\Client;
use Yii;
/**
 * ManagerTestCase
 */
class OauthAccessTokenStorageTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
        ]);
        $this->cleanDatabase();
    }
    protected function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    public function testInsert()
    {
        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken1';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $this->assertTrue($accessToken->save());

        $insertedAccessToken = AccessToken::findOne('accessToken1');
        $this->assertInstanceOf(AccessToken::className(), $insertedAccessToken);
        $this->assertEquals($accessToken->id, $insertedAccessToken->id);
        $this->assertEquals($accessToken->clientId, $insertedAccessToken->clientId);
        $this->assertEquals($accessToken->userId, $insertedAccessToken->userId);
        $this->assertEquals($accessToken->expiry, $insertedAccessToken->expiry);
        $this->assertTrue(is_array($insertedAccessToken->scopes)) ;
        $this->assertTrue(empty($insertedAccessToken->scopes));

        $accessTokens = AccessToken::findAllByUserId('user1');
        $this->assertTrue(is_array($accessTokens));
        $this->assertEquals(1, count($accessTokens));

        $this->assertInstanceOf(AccessToken::className(), $accessTokens[0]);
        $this->assertEquals($accessToken->id, $accessTokens[0]->id);
        $this->assertEquals($accessToken->clientId, $accessTokens[0]->clientId);
        $this->assertEquals($accessToken->userId, $accessTokens[0]->userId);
        $this->assertEquals($accessToken->expiry, $accessTokens[0]->expiry);
        $this->assertTrue(is_array($accessTokens[0]->scopes)) ;
        $this->assertTrue(empty($accessTokens[0]->scopes));

        $this->populateScopes();

        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken2';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $accessToken->scopes = ['basic'];
        $this->assertTrue($accessToken->save());

        $newAccessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $newAccessToken */
        $this->assertInstanceOf(AccessToken::className(), $newAccessToken);
        $newAccessToken->id = 'accessToken3';
        $newAccessToken->clientId = 'client1';
        $newAccessToken->userId = 'user1';
        $newAccessToken->expiry = 1234;
        $newAccessToken->scopes = ['fail'];
        $this->assertFalse($newAccessToken->save());

        $accessTokens = AccessToken::findAllByUserId('user1');
        $this->assertTrue(is_array($accessTokens));
        $this->assertEquals(2, count($accessTokens));

        $accessTokens = AccessToken::findAllByClientId('client1');
        $this->assertTrue(is_array($accessTokens));
        $this->assertEquals(2, count($accessTokens));

        $newAccessToken->scopes = ['basic'];
        $newAccessToken->id = 'accessToken2';
        $this->expectException(DuplicateKeyException::class);
        $newAccessToken->save();

    }

    public function testFindAll()
    {
        $accessTokens = AccessToken::findAllByClientId('client1');
        $this->assertTrue(is_array($accessTokens));
        $this->assertEquals(0, count($accessTokens));

        $accessTokens = AccessToken::findAllByUserId('user1');
        $this->assertTrue(is_array($accessTokens));
        $this->assertEquals(0, count($accessTokens));

    }

    public function testDeleteAllByClientId()
    {
        $client1 = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client1 */
        $this->assertInstanceOf(ClientModelInterface::class, $client1);
        $client1->id = 'client1';
        $client1->secret = 'secret1';
        $client1->isPublic = true;
        $client1->grantTypes = [];
        $client1->userId = 'uid';
        $client1->scopes = [];
        $client1->name = 'Test client';
        $this->assertTrue($client1->save());

        $this->populateScopes();

        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken1';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $accessToken->scopes = ['basic'];
        $this->assertTrue($accessToken->save());

        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken2';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $accessToken->scopes = ['basic'];
        $this->assertTrue($accessToken->save());

        $refreshTokens = AccessToken::findAllByClientId('client1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $this->assertTrue(AccessToken::deleteAllByClientId('client2'));

        $refreshTokens = AccessToken::findAllByClientId('client1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $this->assertTrue(AccessToken::deleteAllByClientId('client1'));

        $refreshTokens = AccessToken::findAllByClientId('client1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(0, count($refreshTokens));

        $client = Client::findOne('client1');
        $this->assertInstanceOf(Client::className(), $client);

        $this->assertTrue(AccessToken::deleteAllByClientId('client1'));

        $refreshTokens = AccessToken::findAllByClientId('client1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(0, count($refreshTokens));

    }

    public function testDeleteAllByUserId()
    {
        $client1 = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var ClientModelInterface $client1 */
        $this->assertInstanceOf(ClientModelInterface::class, $client1);
        $client1->id = 'client1';
        $client1->secret = 'secret1';
        $client1->isPublic = true;
        $client1->grantTypes = [];
        $client1->userId = 'uid';
        $client1->scopes = [];
        $client1->name = 'Test client';
        $this->assertTrue($client1->save());

        $this->populateScopes();

        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken1';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $accessToken->scopes = ['basic'];
        $this->assertTrue($accessToken->save());

        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken2';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $accessToken->scopes = ['basic'];
        $this->assertTrue($accessToken->save());

        $refreshTokens = AccessToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $this->assertTrue(AccessToken::deleteAllByUserId('user2'));

        $refreshTokens = AccessToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $this->assertTrue(AccessToken::deleteAllByUserId('user1'));

        $refreshTokens = AccessToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(0, count($refreshTokens));

        $client = Client::findOne('client1');
        $this->assertInstanceOf(Client::className(), $client);

        $this->assertTrue(AccessToken::deleteAllByUserId('user1'));

        $refreshTokens = AccessToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(0, count($refreshTokens));

    }

    public function testUpdate()
    {
        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken1';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $this->assertTrue($accessToken->save());

        $insertedAccessToken = AccessToken::findOne('accessToken1');
        $this->assertInstanceOf(AccessToken::className(), $insertedAccessToken);

        $insertedAccessToken->id = 'accessToken2';
        $insertedAccessToken->expiry = null;
        $this->assertTrue($insertedAccessToken->save());

        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken1';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $this->assertTrue($accessToken->save());

        $insertedAccessToken->id = 'accessToken1';
        $this->expectException(DuplicateKeyException::class);
        $insertedAccessToken->save();

    }

    public function testDelete()
    {
        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken1';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $this->assertTrue($accessToken->save());

        $insertedAccessToken = AccessToken::findOne('accessToken1');
        $this->assertInstanceOf(AccessToken::className(), $insertedAccessToken);

        $this->assertTrue($insertedAccessToken->delete());
    }

    public function testStorage()
    {
        $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
        /* @var AccessTokenInterface $storage */
        $this->assertInstanceOf(AccessTokenInterface::class, $storage);
        $this->assertTrue($storage->setAccessToken('accessToken1', 'client1', 'user1', 1250));
        $accessToken = AccessToken::findOne('accessToken1');
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $this->assertEquals('accessToken1', $accessToken->id);
        $this->assertEquals('client1', $accessToken->clientId);
        $this->assertEquals('user1', $accessToken->userId);
        $this->assertEquals(1250, $accessToken->expiry);

        $accessTokenData = $storage->getAccessToken('accessToken1');
        $this->assertEquals($accessToken->id, $accessTokenData['id_token']);
        $this->assertEquals($accessToken->clientId, $accessTokenData['client_id']);
        $this->assertEquals($accessToken->userId, $accessTokenData['user_id']);
        $this->assertEquals($accessToken->expiry, $accessTokenData['expires']);

        $this->populateScopes();
        $this->assertTrue($storage->setAccessToken('accessToken2', 'client1', 'user1', 1250, 'basic'));

        $accessTokenData = $storage->getAccessToken('accessToken2');
        $this->assertEquals('accessToken2', $accessTokenData['id_token']);
        $this->assertEquals('client1', $accessTokenData['client_id']);
        $this->assertEquals('user1', $accessTokenData['user_id']);
        $this->assertEquals(1250, $accessTokenData['expires']);
        $this->assertEquals('basic', $accessTokenData['scope']);

        $this->assertTrue($storage->unsetAccessToken('accessToken2'));
        $accessTokenData = $storage->getAccessToken('accessToken2');
        $this->assertNull($accessTokenData);

    }
}
