<?php

namespace tests\unit;
use OAuth2\Storage\RefreshTokenInterface;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\ClientModelInterface;
use sweelix\oauth2\server\models\Client;
use sweelix\oauth2\server\models\RefreshToken;
use Yii;
/**
 * ManagerTestCase
 */
class OauthRefreshTokenStorageTest extends TestCase
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
    }

    public function testInsert()
    {
        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $this->assertTrue($refreshToken->save());

        $insertedRefreshToken = RefreshToken::findOne('refreshToken1');
        $this->assertInstanceOf(RefreshToken::class, $insertedRefreshToken);
        $this->assertEquals($refreshToken->id, $insertedRefreshToken->id);
        $this->assertEquals($refreshToken->clientId, $insertedRefreshToken->clientId);
        $this->assertEquals($refreshToken->userId, $insertedRefreshToken->userId);
        $this->assertEquals($refreshToken->expiry, $insertedRefreshToken->expiry);
        $this->assertTrue(is_array($insertedRefreshToken->scopes)) ;
        $this->assertTrue(empty($insertedRefreshToken->scopes));

        $refreshTokens = RefreshToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(1, count($refreshTokens));

        $this->assertInstanceOf(RefreshToken::class, $refreshTokens[0]);
        $this->assertEquals($refreshToken->id, $refreshTokens[0]->id);
        $this->assertEquals($refreshToken->clientId, $refreshTokens[0]->clientId);
        $this->assertEquals($refreshToken->userId, $refreshTokens[0]->userId);
        $this->assertEquals($refreshToken->expiry, $refreshTokens[0]->expiry);
        $this->assertTrue(is_array($refreshTokens[0]->scopes)) ;
        $this->assertTrue(empty($refreshTokens[0]->scopes));

        $this->populateScopes();

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken2';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $refreshToken->scopes = ['basic'];
        $this->assertTrue($refreshToken->save());

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken3';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $refreshToken->scopes = ['fail'];
        $this->assertFalse($refreshToken->save());

        $refreshTokens = RefreshToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $refreshTokens = RefreshToken::findAllByClientId('client1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $refreshToken->scopes = ['basic'];
        $refreshToken->id = 'refreshToken2';
        $this->expectException(DuplicateKeyException::class);
        $refreshToken->save();

    }

    public function testFindAll()
    {
        $refreshTokens = RefreshToken::findAllByClientId('client1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(0, count($refreshTokens));

        $refreshTokens = RefreshToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(0, count($refreshTokens));

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

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $refreshToken->scopes = ['basic'];
        $this->assertTrue($refreshToken->save());

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken2';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $refreshToken->scopes = ['basic'];
        $this->assertTrue($refreshToken->save());

        $refreshTokens = RefreshToken::findAllByClientId('client1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $this->assertTrue(RefreshToken::deleteAllByClientId('client2'));

        $refreshTokens = RefreshToken::findAllByClientId('client1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $this->assertTrue(RefreshToken::deleteAllByClientId('client1'));

        $refreshTokens = RefreshToken::findAllByClientId('client1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(0, count($refreshTokens));

        $client = Client::findOne('client1');
        $this->assertInstanceOf(Client::class, $client);

        $this->assertTrue(RefreshToken::deleteAllByClientId('client1'));

        $refreshTokens = RefreshToken::findAllByClientId('client1');
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

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $refreshToken->scopes = ['basic'];
        $this->assertTrue($refreshToken->save());

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken2';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $refreshToken->scopes = ['basic'];
        $this->assertTrue($refreshToken->save());

        $refreshTokens = RefreshToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $this->assertTrue(RefreshToken::deleteAllByUserId('user2'));

        $refreshTokens = RefreshToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(2, count($refreshTokens));

        $this->assertTrue(RefreshToken::deleteAllByUserId('user1'));

        $refreshTokens = RefreshToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(0, count($refreshTokens));

        $client = Client::findOne('client1');
        $this->assertInstanceOf(Client::class, $client);

        $this->assertTrue(RefreshToken::deleteAllByUserId('user1'));

        $refreshTokens = RefreshToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(0, count($refreshTokens));

    }

    public function testUpdate()
    {
        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $this->assertTrue($refreshToken->save());

        $insertedRefreshToken = RefreshToken::findOne('refreshToken1');
        $this->assertInstanceOf(RefreshToken::class, $insertedRefreshToken);

        $insertedRefreshToken->id = 'refreshToken2';
        $insertedRefreshToken->expiry = null;
        $this->assertTrue($insertedRefreshToken->save());

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $this->assertTrue($refreshToken->save());

        $insertedRefreshToken->id = 'refreshToken1';
        $this->expectException(DuplicateKeyException::class);
        $insertedRefreshToken->save();

    }

    public function testDelete()
    {
        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = time() + 3600;
        $this->assertTrue($refreshToken->save());

        $insertedRefreshToken = RefreshToken::findOne('refreshToken1');
        $this->assertInstanceOf(RefreshToken::class, $insertedRefreshToken);

        $this->assertTrue($insertedRefreshToken->delete());
    }

    public function testStorage()
    {
        $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
        /* @var RefreshTokenInterface $storage */
        $this->assertInstanceOf(RefreshTokenInterface::class, $storage);
        $expiry = time()+3600;
        $this->assertTrue($storage->setRefreshToken('refreshToken1', 'client1', 'user1', $expiry));
        $refreshToken = RefreshToken::findOne('refreshToken1');
        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $this->assertEquals('refreshToken1', $refreshToken->id);
        $this->assertEquals('client1', $refreshToken->clientId);
        $this->assertEquals('user1', $refreshToken->userId);
        $this->assertEquals($expiry, $refreshToken->expiry);

        $refreshTokenData = $storage->getRefreshToken('refreshToken1');
        $this->assertEquals($refreshToken->id, $refreshTokenData['refresh_token']);
        $this->assertEquals($refreshToken->clientId, $refreshTokenData['client_id']);
        $this->assertEquals($refreshToken->userId, $refreshTokenData['user_id']);
        $this->assertEquals($refreshToken->expiry, $refreshTokenData['expires']);

        $this->populateScopes();
        $this->assertTrue($storage->setRefreshToken('refreshToken2', 'client1', 'user1', $expiry, 'basic'));

        $refreshTokenData = $storage->getRefreshToken('refreshToken2');
        $this->assertEquals('refreshToken2', $refreshTokenData['refresh_token']);
        $this->assertEquals('client1', $refreshTokenData['client_id']);
        $this->assertEquals('user1', $refreshTokenData['user_id']);
        $this->assertEquals($expiry, $refreshTokenData['expires']);
        $this->assertEquals('basic', $refreshTokenData['scope']);

        $this->assertTrue($storage->unsetRefreshToken('refreshToken2'));
        $refreshTokenData = $storage->getRefreshToken('refreshToken2');
        $this->assertNull($refreshTokenData);

    }
}
