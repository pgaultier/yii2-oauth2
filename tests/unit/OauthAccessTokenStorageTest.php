<?php

namespace tests\unit;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\models\AccessToken;
use sweelix\oauth2\server\storage\AccessTokenStorage;
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

        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var AccessToken $accessToken */
        $this->assertInstanceOf(AccessToken::className(), $accessToken);
        $accessToken->id = 'accessToken3';
        $accessToken->clientId = 'client1';
        $accessToken->userId = 'user1';
        $accessToken->expiry = 1234;
        $accessToken->scopes = ['fail'];
        $this->assertFalse($accessToken->save());

        $accessToken->scopes = ['basic'];
        $accessToken->id = 'accessToken2';
        $this->expectException(DuplicateKeyException::class);
        $accessToken->save();

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
        $storage = Yii::createObject('sweelix\oauth2\server\storage\AccessTokenStorage');
        /* @var AccessTokenStorage $storage */
        $this->assertInstanceOf(AccessTokenStorage::class, $storage);
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
