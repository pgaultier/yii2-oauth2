<?php

namespace tests\unit;
use OAuth2\Storage\RefreshTokenInterface;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
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
        $this->assertInstanceOf(RefreshToken::className(), $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = 1234;
        $this->assertTrue($refreshToken->save());

        $insertedRefreshToken = RefreshToken::findOne('refreshToken1');
        $this->assertInstanceOf(RefreshToken::className(), $insertedRefreshToken);
        $this->assertEquals($refreshToken->id, $insertedRefreshToken->id);
        $this->assertEquals($refreshToken->clientId, $insertedRefreshToken->clientId);
        $this->assertEquals($refreshToken->userId, $insertedRefreshToken->userId);
        $this->assertEquals($refreshToken->expiry, $insertedRefreshToken->expiry);
        $this->assertTrue(is_array($insertedRefreshToken->scopes)) ;
        $this->assertTrue(empty($insertedRefreshToken->scopes));

        $refreshTokens = RefreshToken::findAllByUserId('user1');
        $this->assertTrue(is_array($refreshTokens));
        $this->assertEquals(1, count($refreshTokens));

        $this->assertInstanceOf(RefreshToken::className(), $refreshTokens[0]);
        $this->assertEquals($refreshToken->id, $refreshTokens[0]->id);
        $this->assertEquals($refreshToken->clientId, $refreshTokens[0]->clientId);
        $this->assertEquals($refreshToken->userId, $refreshTokens[0]->userId);
        $this->assertEquals($refreshToken->expiry, $refreshTokens[0]->expiry);
        $this->assertTrue(is_array($refreshTokens[0]->scopes)) ;
        $this->assertTrue(empty($refreshTokens[0]->scopes));

        $this->populateScopes();

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::className(), $refreshToken);
        $refreshToken->id = 'refreshToken2';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = 1234;
        $refreshToken->scopes = ['basic'];
        $this->assertTrue($refreshToken->save());

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::className(), $refreshToken);
        $refreshToken->id = 'refreshToken3';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = 1234;
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

    public function testUpdate()
    {
        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::className(), $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = 1234;
        $this->assertTrue($refreshToken->save());

        $insertedRefreshToken = RefreshToken::findOne('refreshToken1');
        $this->assertInstanceOf(RefreshToken::className(), $insertedRefreshToken);

        $insertedRefreshToken->id = 'refreshToken2';
        $insertedRefreshToken->expiry = null;
        $this->assertTrue($insertedRefreshToken->save());

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::className(), $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = 1234;
        $this->assertTrue($refreshToken->save());

        $insertedRefreshToken->id = 'refreshToken1';
        $this->expectException(DuplicateKeyException::class);
        $insertedRefreshToken->save();

    }

    public function testDelete()
    {
        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var RefreshToken $refreshToken */
        $this->assertInstanceOf(RefreshToken::className(), $refreshToken);
        $refreshToken->id = 'refreshToken1';
        $refreshToken->clientId = 'client1';
        $refreshToken->userId = 'user1';
        $refreshToken->expiry = 1234;
        $this->assertTrue($refreshToken->save());

        $insertedRefreshToken = RefreshToken::findOne('refreshToken1');
        $this->assertInstanceOf(RefreshToken::className(), $insertedRefreshToken);

        $this->assertTrue($insertedRefreshToken->delete());
    }

    public function testStorage()
    {
        $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
        /* @var RefreshTokenInterface $storage */
        $this->assertInstanceOf(RefreshTokenInterface::class, $storage);
        $this->assertTrue($storage->setRefreshToken('refreshToken1', 'client1', 'user1', 1250));
        $refreshToken = RefreshToken::findOne('refreshToken1');
        $this->assertInstanceOf(RefreshToken::className(), $refreshToken);
        $this->assertEquals('refreshToken1', $refreshToken->id);
        $this->assertEquals('client1', $refreshToken->clientId);
        $this->assertEquals('user1', $refreshToken->userId);
        $this->assertEquals(1250, $refreshToken->expiry);

        $refreshTokenData = $storage->getRefreshToken('refreshToken1');
        $this->assertEquals($refreshToken->id, $refreshTokenData['refresh_token']);
        $this->assertEquals($refreshToken->clientId, $refreshTokenData['client_id']);
        $this->assertEquals($refreshToken->userId, $refreshTokenData['user_id']);
        $this->assertEquals($refreshToken->expiry, $refreshTokenData['expires']);

        $this->populateScopes();
        $this->assertTrue($storage->setRefreshToken('refreshToken2', 'client1', 'user1', 1250, 'basic'));

        $refreshTokenData = $storage->getRefreshToken('refreshToken2');
        $this->assertEquals('refreshToken2', $refreshTokenData['refresh_token']);
        $this->assertEquals('client1', $refreshTokenData['client_id']);
        $this->assertEquals('user1', $refreshTokenData['user_id']);
        $this->assertEquals(1250, $refreshTokenData['expires']);
        $this->assertEquals('basic', $refreshTokenData['scope']);

        $this->assertTrue($storage->unsetRefreshToken('refreshToken2'));
        $refreshTokenData = $storage->getRefreshToken('refreshToken2');
        $this->assertNull($refreshTokenData);

    }
}
