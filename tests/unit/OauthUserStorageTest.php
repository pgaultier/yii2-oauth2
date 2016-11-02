<?php

namespace tests\unit;
use OAuth2\Storage\UserCredentialsInterface;
use Yii;
/**
 * ManagerTestCase
 */
class OauthUserStorageTest extends TestCase
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

    public function testStorage()
    {
        $storage = Yii::createObject('sweelix\oauth2\server\storage\OauthStorage');
        /* @var UserCredentialsInterface $storage */
        $this->assertInstanceOf(UserCredentialsInterface::class, $storage);

        $status = $storage->checkUserCredentials('user0', 'password0');
        $this->assertFalse($status);

        $status = $storage->checkUserCredentials('user1', 'password1');
        $this->assertTrue($status);

        $result = $storage->getUserDetails('user0');
        $this->assertFalse($result);

        $result = $storage->getUserDetails('user1');
        $this->assertEquals('user1', $result['user_id']);

        $result = $storage->getUserDetails('user2');
        $this->assertEquals('user2', $result['user_id']);

    }
}
