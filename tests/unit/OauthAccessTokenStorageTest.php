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
