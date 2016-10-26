<?php

namespace tests\unit;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\models\AccessToken;
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
        $accessToken = Yii::createObject('sweelix\oauth2\server\models\AccessToken');
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
        $this->assertTrue(empty($insertedAccessToken->scopes)) ;
    }

    public function testUpdate()
    {
    }

    public function testDelete()
    {
    }

    public function testStorage()
    {
    }
}
